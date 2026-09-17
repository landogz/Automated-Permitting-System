<?php

declare(strict_types=1);

namespace App\Services\Registration;

use App\Enums\RegistrationApprovalStatus;
use App\Mail\Registration\RegistrationApprovedMail;
use App\Mail\Registration\RegistrationDeclinedMail;
use App\Mail\Registration\RegistrationPendingAdminMail;
use App\Mail\Registration\RegistrationReceivedMail;
use App\Models\User;
use App\Repositories\Registration\RegistrationRepository;
use App\Services\Audit\AuditLogger;
use App\Services\Notification\WorkflowNotifier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

final class RegistrationService
{
    public function __construct(
        private readonly RegistrationRepository $repository,
        private readonly AuditLogger $audit,
        private readonly WorkflowNotifier $notifier,
    ) {
    }

    /**
     * @param  array{name: string, email: string, phone?: string|null, password: string}  $data
     */
    public function register(array $data): User
    {
        $user = DB::transaction(function () use ($data): User {
            $user = $this->repository->createApplicant([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => false,
                'approval_status' => RegistrationApprovalStatus::Pending->value,
                'registered_at' => now(),
            ]);

            $applicantRole = Role::findOrCreate('applicant');
            $user->syncRoles([$applicantRole]);

            return $user;
        });

        Mail::to($user->email)->send(new RegistrationReceivedMail($user));

        foreach ($this->repository->adminRecipients() as $admin) {
            Mail::to($admin->email)->send(new RegistrationPendingAdminMail($user));
        }

        $this->notifier->registrationSubmitted($user);

        $this->audit->log('registration.submitted', [
            'user_id' => $user->uuid,
            'email' => $user->email,
        ]);

        return $user;
    }

    /**
     * @return LengthAwarePaginator<int, User>
     */
    public function list(?string $status, string $search = '', int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginateByStatus($status, $search, $perPage);
    }

    public function approve(User $applicant, User $reviewer): User
    {
        $this->assertPendingApplicant($applicant);

        $applicant->fill([
            'approval_status' => RegistrationApprovalStatus::Approved->value,
            'is_active' => true,
            'approval_notes' => null,
            'approved_at' => now(),
            'declined_at' => null,
            'reviewed_by' => $reviewer->id,
        ]);
        $applicant->save();

        Mail::to($applicant->email)->send(new RegistrationApprovedMail($applicant));

        $this->notifier->registrationApproved($applicant);

        $this->audit->log('registration.approved', [
            'user_id' => $applicant->uuid,
            'email' => $applicant->email,
            'reviewed_by' => $reviewer->uuid,
        ]);

        return $applicant->fresh(['reviewedBy']) ?? $applicant;
    }

    public function decline(User $applicant, User $reviewer, string $reason): User
    {
        $this->assertPendingApplicant($applicant);

        $applicant->fill([
            'approval_status' => RegistrationApprovalStatus::Declined->value,
            'is_active' => false,
            'approval_notes' => $reason,
            'declined_at' => now(),
            'approved_at' => null,
            'reviewed_by' => $reviewer->id,
        ]);
        $applicant->save();

        Mail::to($applicant->email)->send(new RegistrationDeclinedMail($applicant, $reason));

        $this->notifier->registrationDeclined($applicant, $reason);

        $this->audit->log('registration.declined', [
            'user_id' => $applicant->uuid,
            'email' => $applicant->email,
            'reviewed_by' => $reviewer->uuid,
        ]);

        return $applicant->fresh(['reviewedBy']) ?? $applicant;
    }

    private function assertPendingApplicant(User $applicant): void
    {
        if (! $applicant->hasRole('applicant')) {
            throw ValidationException::withMessages([
                'user' => ['Only applicant registrations can be reviewed.'],
            ]);
        }

        if ($applicant->approval_status !== RegistrationApprovalStatus::Pending) {
            throw ValidationException::withMessages([
                'user' => ['This registration is no longer pending.'],
            ]);
        }
    }
}
