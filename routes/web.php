<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LogbookPrintController;
use App\Http\Controllers\Auth\LoginPageController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')->name('home');
Route::get('/login', LoginPageController::class)->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::view('/applications', 'public.applications')->name('applications.index');

Route::prefix('admin')->group(function (): void {
    Route::get('/', DashboardController::class)->name('admin.dashboard');
    Route::view('/departments', 'admin.departments')->name('admin.departments');
    Route::view('/forms', 'admin.forms')->name('admin.forms');
    Route::view('/registrations', 'admin.registrations')->name('admin.registrations');
    Route::view('/users', 'admin.users')->name('admin.users');
    Route::view('/classification-rules', 'admin.classification-rules')->name('admin.classification-rules');
    Route::view('/routing-templates', 'admin.routing-templates')->name('admin.routing-templates');
    Route::view('/evaluation-queue', 'admin.evaluation-queue')->name('admin.evaluation-queue');
    Route::view('/fee-rules', 'admin.fee-rules')->name('admin.fee-rules');
    Route::view('/inspections', 'admin.inspections')->name('admin.inspections');
    Route::view('/orders-of-payment', 'admin.orders-of-payment')->name('admin.orders-of-payment');
    Route::view('/compliance-notices', 'admin.compliance-notices')->name('admin.compliance-notices');
    Route::view('/logbooks', 'admin.logbooks')->name('admin.logbooks');
    Route::get('/logbooks/{logbook}/print', LogbookPrintController::class)
        ->middleware('signed')
        ->name('admin.logbooks.print');
    Route::view('/archives', 'admin.archives')->name('admin.archives');
    Route::view('/notifications', 'admin.notifications')->name('admin.notifications');
    Route::view('/audit', 'admin.audit')->name('admin.audit');
});
