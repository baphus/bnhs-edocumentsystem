<?php

use App\Http\Controllers\Registrar\DashboardController as RegistrarDashboardController;
use App\Http\Controllers\Registrar\RequestController as RegistrarRequestController;
use App\Http\Controllers\Admin\DocumentTypeController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RequestController as AdminRequestController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\SystemLogController;
use App\Http\Controllers\Admin\TrashController;
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
Route::get("/", function () {
    $documentTypes = DocumentType::all()->groupBy("category");
    
    return Inertia::render("Welcome", [
        "canLogin" => Route::has("login"),
        "documentTypes" => $documentTypes,
        "trackingResult" => session("trackingResult"),
    ]);
})->name("home");

/*
|--------------------------------------------------------------------------
| Document Request Flow (Passwordless - OTP Based)
|--------------------------------------------------------------------------
*/
Route::prefix("request")->name("request.")->group(function () {
    // Step 1: Select Document Type
    Route::get("/", [DocumentRequestController::class, "selectDocument"])->name("select");
    
    // Step 2: Email Verification
    Route::get("/verify", [DocumentRequestController::class, "showEmailVerification"])->name("verify");
    Route::post("/send-otp", [DocumentRequestController::class, "sendOtp"])->name("send-otp");
    Route::post("/verify-otp", [DocumentRequestController::class, "verifyOtp"])->name("verify-otp");
    
    // Step 3: Request Form (requires verified session)
    Route::get("/form", [DocumentRequestController::class, "showForm"])->name("form");
    Route::post("/submit", [DocumentRequestController::class, "submit"])->name("submit");
    
    // Step 4: Success Page
    Route::get("/success/{tracking_id}", [DocumentRequestController::class, "success"])->name("success");
});

/*
|--------------------------------------------------------------------------
| Public Tracking Routes (Passwordless)
|--------------------------------------------------------------------------
*/
Route::prefix("track")->name("track.")->group(function () {
    // Tracking page
    Route::get("/", [TrackingController::class, "index"])->name("index");
    
    // Quick track (basic status only, no OTP)
    Route::get("/{tracking_id}", [TrackingController::class, "quickTrack"])->name("quick");
    
    // Direct tracking (no OTP required - email already verified during request creation)
    Route::post("/", [TrackingController::class, "track"])->name("track");
    
    // Legacy OTP routes (kept for backward compatibility)
    Route::post("/send-otp", [TrackingController::class, "sendOtp"])->name("send-otp");
    Route::post("/verify", [TrackingController::class, "verify"])->name("verify");
});

/*
||--------------------------------------------------------------------------
|| User Dashboard Routes (Passwordless - Email-based)
||--------------------------------------------------------------------------
*/
// Redirect /my-requests to verification page - no direct URL access
Route::get("/my-requests", function () {
    return redirect()->route("user.dashboard.verify");
})->name("user.dashboard");

Route::prefix("my-requests")->group(function () {
    // Email verification (entry point)
    Route::get("/verify", [UserDashboardController::class, "showEmailVerification"])->name("user.dashboard.verify");
    Route::post("/send-otp", [UserDashboardController::class, "sendOtp"])->name("user.dashboard.send-otp");
    Route::post("/verify-otp", [UserDashboardController::class, "verifyOtp"])->name("user.dashboard.verify-otp");
    
    // Dashboard (only accessible after OTP verification via session)
    Route::get("/dashboard", [UserDashboardController::class, "index"])->name("user.dashboard.index");
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

// Dashboard redirect based on role
Route::get("/dashboard", function () {
    if (auth()->user()->role === "admin") {
        return redirect()->route("admin.dashboard");
    } elseif (auth()->user()->role === "registrar") {
        return redirect()->route("registrar.dashboard");
    }
    return redirect()->route("home");
})->middleware(["auth"])->name("dashboard");

// Registrar Panel
Route::middleware(["auth", "role:registrar"])->prefix("registrar")->name("registrar.")->group(function () {
    // Dashboard
    Route::get("/dashboard", [RegistrarDashboardController::class, "index"])->name("dashboard");
    
    // Request Management
    Route::get("/requests", [RegistrarRequestController::class, "index"])->name("requests.index");
    Route::get("/requests/create", [RegistrarRequestController::class, "create"])->name("requests.create");
    Route::post("/requests", [RegistrarRequestController::class, "store"])->name("requests.store");
    Route::get("/requests/export", [RegistrarRequestController::class, "export"])->name("requests.export");
    Route::get("/requests/{documentRequest}", [RegistrarRequestController::class, "show"])->name("requests.show");
    Route::patch("/requests/{documentRequest}", [RegistrarRequestController::class, "update"])->name("requests.update");
    Route::patch("/requests/{documentRequest}/status", [RegistrarRequestController::class, "updateStatus"])->name("requests.update-status");
    Route::patch("/requests/{documentRequest}/notes", [RegistrarRequestController::class, "updateNotes"])->name("requests.update-notes");
    Route::post("/requests/bulk-update", [RegistrarRequestController::class, "bulkUpdate"])->name("requests.bulk-update");
    Route::post("/requests/bulk-delete", [RegistrarRequestController::class, "bulkDelete"])->name("requests.bulk-delete");
});

// Admin-only routes (formerly Superadmin)
Route::middleware(["auth", "role:admin"])->prefix("admin")->name("admin.")->group(function () {
    // Dashboard
    Route::get("/dashboard", [AdminDashboardController::class, "index"])->name("dashboard");
    
    // Users with impersonation
    Route::get("/users", [AdminUserController::class, "index"])->name("users.index");
    Route::get("/users/create", [AdminUserController::class, "create"])->name("users.create");
    Route::post("/users", [AdminUserController::class, "store"])->name("users.store");
    Route::get("/users/{user}", [AdminUserController::class, "show"])->name("users.show");
    Route::get("/users/{user}/edit", [AdminUserController::class, "edit"])->name("users.edit");
    Route::patch("/users/{user}", [AdminUserController::class, "update"])->name("users.update");
    Route::post("/users/{user}/reset-password", [AdminUserController::class, "resetPassword"])->name("users.reset-password");
    Route::patch("/users/{user}/status", [AdminUserController::class, "updateStatus"])->name("users.update-status");
    Route::post("/users/bulk-status", [AdminUserController::class, "bulkUpdateStatus"])->name("users.bulk-status");
    Route::delete("/users/{user}", [AdminUserController::class, "destroy"])->name("users.destroy");
    
    // Requests
    Route::get("/requests", [AdminRequestController::class, "index"])->name("requests.index");
    Route::get("/requests/create", [AdminRequestController::class, "create"])->name("requests.create");
    Route::post("/requests", [AdminRequestController::class, "store"])->name("requests.store");
    Route::get("/requests/export", [AdminRequestController::class, "export"])->name("requests.export");
    Route::get("/requests/{documentRequest}", [AdminRequestController::class, "show"])->name("requests.show");
    Route::patch("/requests/{documentRequest}", [AdminRequestController::class, "update"])->name("requests.update");
    Route::patch("/requests/{documentRequest}/status", [AdminRequestController::class, "updateStatus"])->name("requests.update-status");
    Route::patch("/requests/{documentRequest}/notes", [AdminRequestController::class, "updateNotes"])->name("requests.update-notes");
    Route::post("/requests/bulk-action", [AdminRequestController::class, "bulkAction"])->name("requests.bulk");
    Route::delete("/requests/{documentRequest}", [AdminRequestController::class, "destroy"])->name("requests.destroy");
    
    // Document Types CRUD
    Route::resource("document-types", DocumentTypeController::class);

    // Audit Logs (New)
    Route::get("/audit-logs", [\App\Http\Controllers\Admin\AuditLogController::class, "index"])->name("audit-logs.index");
    
    // System Logs
    Route::get("/logs", [SystemLogController::class, "index"])->name("logs.index");
    
    // Settings
    Route::get("/settings", [SettingsController::class, "index"])->name("settings.index");
    Route::patch("/settings", [SettingsController::class, "update"])->name("settings.update");
    
    // Exports
    Route::get("/export/requests", [ExportController::class, "requests"])->name("export.requests");
    Route::get("/export/users", [ExportController::class, "users"])->name("export.users");
    
    // Trash/Restore
    Route::get("/trash/requests", [TrashController::class, "requests"])->name("trash.requests");
    Route::post("/trash/requests/{id}/restore", [TrashController::class, "restoreRequest"])->name("trash.requests.restore");
    Route::delete("/trash/requests/{id}/force", [TrashController::class, "forceDeleteRequest"])->name("trash.requests.force");
});

// Profile routes (authenticated users)
Route::middleware("auth")->group(function () {
    Route::get("/profile", [ProfileController::class, "edit"])->name("profile.edit");
    Route::patch("/profile", [ProfileController::class, "update"])->name("profile.update");
    Route::delete("/profile", [ProfileController::class, "destroy"])->name("profile.destroy");
});

require __DIR__."/auth.php";
