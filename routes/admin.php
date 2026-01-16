<?php

use App\Http\Controllers\Admin\FAQController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified', 'permission:admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
    
    Route::resource('services', ServiceController::class)->middleware([
        'index' => 'permission:services.list',
        'show' => 'permission:services.view',
        'create' => 'permission:services.create',
        'store' => 'permission:services.create',
        'edit' => 'permission:services.update',
        'update' => 'permission:services.update',
        'destroy' => 'permission:services.delete',
    ]);

    Route::resource('faqs', FAQController::class)->middleware([
        'index' => 'permission:faqs.list',
        'show' => 'permission:faqs.view',
        'create' => 'permission:faqs.create',
        'store' => 'permission:faqs.create',
        'edit' => 'permission:faqs.update',
        'update' => 'permission:faqs.update',
        'destroy' => 'permission:faqs.delete',
    ]);

    Route::resource('roles', RoleController::class)->except(['show'])->middleware([
        'index' => 'permission:roles.list',
        'create' => 'permission:roles.create',
        'store' => 'permission:roles.create',
        'edit' => 'permission:roles.update',
        'update' => 'permission:roles.update',
        'destroy' => 'permission:roles.delete',
    ]);

    Route::resource('users', UserController::class)->only(['index', 'edit', 'update'])->middleware([
        'index' => 'permission:users.list',
        'edit' => 'permission:users.update',
        'update' => 'permission:users.update',
    ]);
});
