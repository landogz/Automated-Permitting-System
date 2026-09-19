<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EvaluationPrintController;
use App\Http\Controllers\Admin\InspectionPrintController;
use App\Http\Controllers\Admin\LogbookPrintController;
use App\Http\Controllers\Admin\RoutingSlipPrintController;
use App\Http\Controllers\Auth\LoginPageController;
use App\Http\Controllers\PublicSite\PrivilegeDocumentationController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');
Route::get('/login', LoginPageController::class)->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/applications', 'public.applications')->name('applications.index');

Route::view('/transparency', 'public.policy.transparency')->name('transparency');
Route::view('/citizens-charter', 'public.policy.citizens-charter')->name('citizens-charter');
Route::view('/privacy', 'public.policy.privacy')->name('privacy');
Route::view('/accessibility', 'public.policy.accessibility')->name('accessibility');
Route::view('/contact', 'public.policy.contact')->name('contact');
Route::view('/sitemap', 'public.policy.sitemap')->name('sitemap');
Route::view('/faqs', 'public.policy.faqs')->name('faqs');
Route::view('/downloads', 'public.policy.downloads')->name('downloads');
Route::view('/archives', 'public.policy.archives')->name('archives');
Route::view('/intellectual-property', 'public.policy.intellectual-property')->name('intellectual-property');
Route::view('/security-policy', 'public.policy.security-policy')->name('security-policy');

Route::redirect('/documentation/privileges', '/documentation/privileges/en');
Route::get('/documentation/privileges/{locale}', PrivilegeDocumentationController::class)
    ->whereIn('locale', ['en', 'tl'])
    ->name('documentation.privileges');

Route::prefix('admin')->group(function (): void {
    Route::get('/', DashboardController::class)->name('admin.dashboard');
    Route::view('/departments', 'admin.departments')->name('admin.departments');
    Route::view('/forms', 'admin.forms')->name('admin.forms');
    Route::view('/registrations', 'admin.registrations')->name('admin.registrations');
    Route::view('/users', 'admin.users')->name('admin.users');
    Route::view('/classification-rules', 'admin.classification-rules')->name('admin.classification-rules');
    Route::view('/routing-templates', 'admin.routing-templates')->name('admin.routing-templates');
    Route::view('/evaluation-queue', 'admin.evaluation-queue')->name('admin.evaluation-queue');
    Route::get('/routing-slips/{slip}/print', RoutingSlipPrintController::class)
        ->middleware('signed')
        ->name('admin.routing-slips.print');
    Route::get('/evaluations/{evaluation}/print', EvaluationPrintController::class)
        ->middleware('signed')
        ->name('admin.evaluations.print');
    Route::view('/fee-rules', 'admin.fee-rules')->name('admin.fee-rules');
    Route::view('/inspections', 'admin.inspections')->name('admin.inspections');
    Route::get('/inspections/{inspection}/print', InspectionPrintController::class)
        ->middleware('signed')
        ->name('admin.inspections.print');
    Route::view('/orders-of-payment', 'admin.orders-of-payment')->name('admin.orders-of-payment');
    Route::view('/compliance-notices', 'admin.compliance-notices')->name('admin.compliance-notices');
    Route::view('/logbooks', 'admin.logbooks')->name('admin.logbooks');
    Route::get('/logbooks/{logbook}/print', LogbookPrintController::class)
        ->middleware('signed')
        ->name('admin.logbooks.print');
    Route::view('/archives', 'admin.archives')->name('admin.archives');
    Route::view('/notifications', 'admin.notifications')->name('admin.notifications');
    Route::view('/audit', 'admin.audit')->name('admin.audit');
    Route::view('/project-plan', 'admin.project-plan')->name('admin.project-plan');
});
