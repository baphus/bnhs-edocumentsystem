<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RequestManagementController;
use App\Http\Controllers\Admin\Superadmin\DocumentTypeController;
use App\Http\Controllers\Admin\Superadmin\ExportController;
use App\Http\Controllers\Admin\Superadmin\SettingsController;
use App\Http\Controllers\Admin\Superadmin\SuperadminDashboardController;
use App\Http\Controllers\Admin\Superadmin\SuperadminRequestController;
use App\Http\Controllers\Admin\Superadmin\SuperadminUserController;
use App\Http\Controllers\Admin\Superadmin\SystemLogController;
use App\Http\Controllers\Admin\Superadmin\TrashController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DocumentRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\UserDashboardController;
use App\Models\DocumentType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Landing Page
Route::get('/', function () {
    $documentTypes = DocumentType::all()->groupBy('category');
    
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'documentTypes' => $documentTypes,
        'trackingResult' => session('trackingResult'),
    ]);
})->name('home');

/*
|--------------------------------------------------------------------------
| Document Request Flow (Passwordless - OTP Based)
|--------------------------------------------------------------------------
*/
Route::prefix('request')->name('request.')->group(function () {
    // Step 1: Select Document Type
    Route::get('/', [DocumentRequestController::class, 'selectDocument'])->name('select');
    
    // Step 2: Email Verification
    Route::get('/verify', [DocumentRequestController::class, 'showEmailVerification'])->name('verify');
    Route::post('/send-otp', [DocumentRequestController::class, 'sendOtp'])->name('send-otp');
    Route::post('/verify-otp', [DocumentRequestController::class, 'verifyOtp'])->name('verify-otp');
    
    // Step 3: Request Form (requires verified session)
    Route::get('/form', [DocumentRequestController::class, 'showForm'])->name('form');
    Route::post('/submit', [DocumentRequestController::class, 'submit'])->name('submit');
    
    // Step 4: Success Page
    Route::get('/success/{tracking_id}', [DocumentRequestController::class, 'success'])->name('success');
});

/*
|--------------------------------------------------------------------------
| Public Tracking Routes (Passwordless)
|--------------------------------------------------------------------------
*/
Route::prefix('track')->name('track.')->group(function () {
    // Tracking page
    Route::get('/', [TrackingController::class, 'index'])->name('index');
    
    // Quick track (basic status only, no OTP)
    Route::get('/{tracking_id}', [TrackingController::class, 'quickTrack'])->name('quick');
    
    // Full tracking with OTP verification
    Route::post('/send-otp', [TrackingController::class, 'sendOtp'])->name('send-otp');
    Route::post('/verify', [TrackingController::class, 'verify'])->name('verify');
});

/*
||--------------------------------------------------------------------------
|| User Dashboard Routes (Passwordless - Email-based)
||--------------------------------------------------------------------------
*/
Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');

/*
|--------------------------------------------------------------------------
| Admin Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

// Admin Panel
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Request Management
    Route::get('/requests', [RequestManagementController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [RequestManagementController::class, 'create'])->name('requests.create');
    Route::post('/requests', [RequestManagementController::class, 'store'])->name('requests.store');
    Route::get('/requests/{documentRequest}', [RequestManagementController::class, 'show'])->name('requests.show');
    Route::patch('/requests/{documentRequest}/status', [RequestManagementController::class, 'updateStatus'])->name('requests.update-status');
    Route::patch('/requests/{documentRequest}/notes', [RequestManagementController::class, 'updateNotes'])->name('requests.update-notes');
    
    // User Management (Superadmin only)
    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    });
});

// Superadmin-only routes
Route::middleware(['auth', 'role:superadmin'])->prefix('admin/superadmin')->name('admin.superadmin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');
    
    // Users with impersonation
    Route::get('/users', [SuperadminUserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [SuperadminUserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/impersonate', [SuperadminUserController::class, 'impersonate'])->name('users.impersonate');
    Route::post('/users/stop-impersonating', [SuperadminUserController::class, 'stopImpersonating'])->name('users.stop-impersonating');
    Route::post('/users/{user}/reset-password', [SuperadminUserController::class, 'resetPassword'])->name('users.reset-password');
    Route::patch('/users/{user}/status', [SuperadminUserController::class, 'updateStatus'])->name('users.update-status');
    Route::post('/users/bulk-status', [SuperadminUserController::class, 'bulkUpdateStatus'])->name('users.bulk-status');
    Route::delete('/users/{user}', [SuperadminUserController::class, 'destroy'])->name('users.destroy');
    
    // Requests
    Route::get('/requests', [SuperadminRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [SuperadminRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [SuperadminRequestController::class, 'store'])->name('requests.store');
    Route::post('/requests/bulk-action', [SuperadminRequestController::class, 'bulkAction'])->name('requests.bulk');
    Route::delete('/requests/{documentRequest}', [SuperadminRequestController::class, 'destroy'])->name('requests.destroy');
    
    // Document Types CRUD
    Route::resource('document-types', DocumentTypeController::class);
    
    // System Logs
    Route::get('/logs', [SystemLogController::class, 'index'])->name('logs.index');
    
    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    
    // Exports
    Route::get('/export/requests', [ExportController::class, 'requests'])->name('export.requests');
    Route::get('/export/users', [ExportController::class, 'users'])->name('export.users');
    
    // Trash/Restore
    Route::get('/trash/requests', [TrashController::class, 'requests'])->name('trash.requests');
    Route::post('/trash/requests/{id}/restore', [TrashController::class, 'restoreRequest'])->name('trash.requests.restore');
    Route::delete('/trash/requests/{id}/force', [TrashController::class, 'forceDeleteRequest'])->name('trash.requests.force');
});

// Profile routes (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
