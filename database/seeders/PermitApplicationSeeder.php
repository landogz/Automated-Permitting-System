<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ApplicationDocument;
use App\Models\FormDefinition;
use App\Models\NumberingSeries;
use App\Models\PermitApplication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Demo permit applications across the sequential Operations pipeline.
 */
class PermitApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $applicant = User::query()->where('email', 'applicant@csfp.local')->first();
        $applicant2 = User::query()->where('email', 'applicant2@csfp.local')->first();
        $form = FormDefinition::query()->where('code', 'QMS-36')->first();
        $series = NumberingSeries::query()->where('key', 'permit_application')->first();

        if (! $applicant || ! $form || ! $series) {
            return;
        }

        $samples = [
            [
                'user' => $applicant,
                'status' => 'draft',
                'classification' => null,
                'project_title' => 'Two-Storey Residential Renovation',
                'project_location' => 'Brgy. San Jose, City of San Fernando, Pampanga',
                'latitude' => 15.0352,
                'longitude' => 120.6848,
                'payload' => $this->payload([
                    'owner_name' => 'Demo Applicant',
                    'owner_address' => 'San Jose, CSFP',
                    'owner_contact' => '09171230001',
                    'barangay' => 'San Jose',
                    'street_address' => 'Purok 3',
                    'tax_declaration_no' => 'TD-SJ-001',
                    'scope_of_work' => 'renovation',
                    'occupancy' => 'Residential',
                    'lot_area' => 120,
                    'floor_area' => 95,
                    'number_of_storeys' => 2,
                    'estimated_cost' => 850000,
                    'engineer_name' => 'Engr. Juan Dela Cruz',
                    'engineer_prc' => '0012345',
                ]),
                'submitted_at' => null,
            ],
            [
                'user' => $applicant,
                'status' => 'submitted',
                'classification' => 'simple',
                'project_title' => 'Warehouse Expansion',
                'project_location' => 'Brgy. Sindalan, City of San Fernando, Pampanga',
                'latitude' => 15.0681,
                'longitude' => 120.6784,
                'payload' => $this->payload([
                    'owner_name' => 'Demo Applicant',
                    'owner_address' => 'Sindalan, CSFP',
                    'owner_contact' => '09171230001',
                    'barangay' => 'Sindalan',
                    'street_address' => 'Industrial Park Rd',
                    'tax_declaration_no' => 'TD-SD-014',
                    'scope_of_work' => 'addition',
                    'occupancy' => 'Storage',
                    'lot_area' => 850,
                    'floor_area' => 720,
                    'number_of_storeys' => 1,
                    'estimated_cost' => 4200000,
                    'engineer_name' => 'Engr. Juan Dela Cruz',
                    'engineer_prc' => '0012345',
                ]),
                'submitted_at' => now()->subDays(2),
            ],
            [
                'user' => $applicant2,
                'status' => 'under_evaluation',
                'classification' => 'complex',
                'project_title' => 'Commercial Mixed-Use Building',
                'project_location' => 'Dolores, City of San Fernando, Pampanga',
                'latitude' => 15.0405,
                'longitude' => 120.6548,
                'payload' => $this->payload([
                    'owner_name' => 'Maria Santos',
                    'owner_address' => 'Dolores, CSFP',
                    'owner_contact' => '09171230002',
                    'barangay' => 'Dolores',
                    'street_address' => 'McArthur Highway',
                    'tax_declaration_no' => 'TD-DL-088',
                    'scope_of_work' => 'new_construction',
                    'occupancy' => 'Mixed-Use',
                    'lot_area' => 450,
                    'floor_area' => 980,
                    'number_of_storeys' => 4,
                    'estimated_cost' => 18500000,
                    'engineer_name' => 'Engr. Ana Reyes',
                    'engineer_prc' => '0098765',
                    'architect_name' => 'Ar. Paolo Lim',
                    'architect_prc' => '0044221',
                ]),
                'submitted_at' => now()->subDay(),
            ],
            [
                'user' => $applicant,
                'status' => 'for_inspection',
                'classification' => 'simple',
                'project_title' => 'Neighborhood Clinic Fit-Out',
                'project_location' => 'Brgy. Del Pilar, City of San Fernando, Pampanga',
                'latitude' => 15.0298,
                'longitude' => 120.6952,
                'payload' => $this->payload([
                    'owner_name' => 'Demo Applicant',
                    'owner_address' => 'Del Pilar, CSFP',
                    'owner_contact' => '09171230001',
                    'barangay' => 'Del Pilar',
                    'street_address' => 'Health Center Compound',
                    'tax_declaration_no' => 'TD-DP-021',
                    'scope_of_work' => 'alteration',
                    'occupancy' => 'Institutional',
                    'lot_area' => 180,
                    'floor_area' => 160,
                    'number_of_storeys' => 1,
                    'estimated_cost' => 2100000,
                    'engineer_name' => 'Engr. Juan Dela Cruz',
                    'engineer_prc' => '0012345',
                ]),
                'submitted_at' => now()->subDays(5),
            ],
            [
                'user' => $applicant2,
                'status' => 'for_inspection',
                'classification' => 'complex',
                'project_title' => 'School Covered Court',
                'project_location' => 'Brgy. Lara, City of San Fernando, Pampanga',
                'latitude' => 15.0146,
                'longitude' => 120.7104,
                'payload' => $this->payload([
                    'owner_name' => 'Maria Santos',
                    'owner_address' => 'Lara, CSFP',
                    'owner_contact' => '09171230002',
                    'barangay' => 'Lara',
                    'street_address' => 'Public School Grounds',
                    'tax_declaration_no' => 'TD-LR-033',
                    'scope_of_work' => 'new_construction',
                    'occupancy' => 'Educational',
                    'lot_area' => 600,
                    'floor_area' => 540,
                    'number_of_storeys' => 1,
                    'building_height' => 9.5,
                    'estimated_cost' => 7800000,
                    'engineer_name' => 'Engr. Ana Reyes',
                    'engineer_prc' => '0098765',
                ]),
                'submitted_at' => now()->subDays(6),
            ],
            [
                'user' => $applicant,
                'status' => 'for_payment',
                'classification' => 'complex',
                'project_title' => 'Office Fit-Out Plaza Miranda',
                'project_location' => 'Sto. Rosario, City of San Fernando, Pampanga',
                'latitude' => 15.0286,
                'longitude' => 120.6921,
                'payload' => $this->payload([
                    'owner_name' => 'Demo Applicant',
                    'owner_address' => 'Sto. Rosario, CSFP',
                    'owner_contact' => '09171230001',
                    'barangay' => 'Sto. Rosario',
                    'street_address' => 'Plaza Miranda Wing B',
                    'tax_declaration_no' => 'TD-SR-055',
                    'scope_of_work' => 'renovation',
                    'occupancy' => 'Commercial',
                    'lot_area' => 320,
                    'floor_area' => 410,
                    'number_of_storeys' => 2,
                    'estimated_cost' => 3500000,
                    'engineer_name' => 'Engr. Juan Dela Cruz',
                    'engineer_prc' => '0012345',
                    'electrical_engineer_name' => 'Engr. Liza Cruz',
                    'electrical_prc' => '0077112',
                ]),
                'submitted_at' => now()->subDays(8),
            ],
            [
                'user' => $applicant2,
                'status' => 'for_payment',
                'classification' => 'highly_technical',
                'project_title' => 'Retail Strip Mall Annex',
                'project_location' => 'Brgy. Baliti, City of San Fernando, Pampanga',
                'latitude' => 15.0554,
                'longitude' => 120.6652,
                'payload' => $this->payload([
                    'owner_name' => 'Maria Santos',
                    'owner_address' => 'Baliti, CSFP',
                    'owner_contact' => '09171230002',
                    'barangay' => 'Baliti',
                    'street_address' => 'Olongapo-Gapan Road',
                    'tax_declaration_no' => 'TD-BL-101',
                    'scope_of_work' => 'new_construction',
                    'occupancy' => 'Commercial',
                    'lot_area' => 1200,
                    'floor_area' => 2100,
                    'number_of_storeys' => 3,
                    'estimated_cost' => 42000000,
                    'engineer_name' => 'Engr. Ana Reyes',
                    'engineer_prc' => '0098765',
                    'architect_name' => 'Ar. Paolo Lim',
                    'architect_prc' => '0044221',
                ]),
                'submitted_at' => now()->subDays(10),
            ],
            [
                'user' => $applicant,
                'status' => 'for_compliance',
                'classification' => 'simple',
                'project_title' => 'Hardware Store Renovation',
                'project_location' => 'Brgy. Quebiawan, City of San Fernando, Pampanga',
                'latitude' => 15.0448,
                'longitude' => 120.6705,
                'payload' => $this->payload([
                    'owner_name' => 'Demo Applicant',
                    'owner_address' => 'Quebiawan, CSFP',
                    'owner_contact' => '09171230001',
                    'barangay' => 'Quebiawan',
                    'street_address' => 'Market Road',
                    'tax_declaration_no' => 'TD-QB-009',
                    'scope_of_work' => 'renovation',
                    'occupancy' => 'Commercial',
                    'lot_area' => 95,
                    'floor_area' => 80,
                    'number_of_storeys' => 1,
                    'estimated_cost' => 650000,
                    'engineer_name' => 'Engr. Juan Dela Cruz',
                    'engineer_prc' => '0012345',
                ]),
                'submitted_at' => now()->subDays(7),
            ],
            [
                'user' => $applicant2,
                'status' => 'released',
                'classification' => 'simple',
                'project_title' => 'Barangay Hall Extension',
                'project_location' => 'Brgy. Calulut, City of San Fernando, Pampanga',
                'latitude' => 15.0802,
                'longitude' => 120.6898,
                'payload' => $this->payload([
                    'owner_name' => 'Maria Santos',
                    'owner_address' => 'Calulut, CSFP',
                    'owner_contact' => '09171230002',
                    'barangay' => 'Calulut',
                    'street_address' => 'Barangay Hall Compound',
                    'tax_declaration_no' => 'TD-CL-002',
                    'scope_of_work' => 'addition',
                    'occupancy' => 'Institutional',
                    'lot_area' => 150,
                    'floor_area' => 220,
                    'number_of_storeys' => 2,
                    'estimated_cost' => 1900000,
                    'engineer_name' => 'Engr. Ana Reyes',
                    'engineer_prc' => '0098765',
                ]),
                'submitted_at' => now()->subDays(14),
            ],
        ];

        foreach ($samples as $sample) {
            $exists = PermitApplication::query()
                ->where('user_id', $sample['user']->id)
                ->where('project_title', $sample['project_title'])
                ->exists();

            if ($exists) {
                continue;
            }

            $series->refresh();
            $number = $series->next_number;
            $applicationNo = $series->prefix.str_pad((string) $number, $series->pad_length, '0', STR_PAD_LEFT);
            $series->increment('next_number');

            $application = PermitApplication::query()->create([
                'application_no' => $applicationNo,
                'user_id' => $sample['user']->id,
                'form_definition_id' => $form->id,
                'status' => $sample['status'],
                'classification' => $sample['classification'],
                'project_title' => $sample['project_title'],
                'project_location' => $sample['project_location'],
                'latitude' => $sample['latitude'] ?? null,
                'longitude' => $sample['longitude'] ?? null,
                'payload' => $sample['payload'],
                'submitted_at' => $sample['submitted_at'],
            ]);

            $relativePath = 'seed/documents/'.$application->uuid.'/lot-plan.txt';
            Storage::disk('local')->put($relativePath, "Seed placeholder document for {$application->application_no}\n");

            ApplicationDocument::query()->create([
                'permit_application_id' => $application->id,
                'label' => 'lot_plan',
                'disk' => 'local',
                'path' => $relativePath,
                'original_name' => 'lot-plan.txt',
                'mime_type' => 'text/plain',
                'size' => Storage::disk('local')->size($relativePath),
            ]);
        }

        // Backfill pins for already-seeded apps missing coordinates (demo map UX).
        $pinByLocation = [
            'Brgy. San Jose, City of San Fernando, Pampanga' => [15.0352, 120.6848],
            'Brgy. Sindalan, City of San Fernando, Pampanga' => [15.0681, 120.6784],
            'Dolores, City of San Fernando, Pampanga' => [15.0405, 120.6548],
            'Brgy. Del Pilar, City of San Fernando, Pampanga' => [15.0298, 120.6952],
            'Brgy. Lara, City of San Fernando, Pampanga' => [15.0146, 120.7104],
            'Sto. Rosario, City of San Fernando, Pampanga' => [15.0286, 120.6921],
            'Brgy. Baliti, City of San Fernando, Pampanga' => [15.0554, 120.6652],
            'Brgy. Quebiawan, City of San Fernando, Pampanga' => [15.0448, 120.6705],
            'Brgy. Calulut, City of San Fernando, Pampanga' => [15.0802, 120.6898],
        ];
        foreach ($pinByLocation as $location => [$lat, $lng]) {
            PermitApplication::query()
                ->where('project_location', $location)
                ->whereNull('latitude')
                ->update(['latitude' => $lat, 'longitude' => $lng]);
        }
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides): array
    {
        return array_merge([
            'owner_email' => 'owner@example.local',
            'character_of_occupancy' => null,
            'number_of_units' => 1,
            'notes' => 'Seeded complete QMS-36 payload for demo.',
        ], $overrides);
    }
}
