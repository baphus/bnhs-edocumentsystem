<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display list of all users with search and filters.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Search
        if ($request->has("search")) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where("name", "like", "%{$search}%")
                    ->orWhere("email", "like", "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has("role") && $request->role !== "") {
            $query->where("role", $request->role);
        }

        // Filter by status
        if ($request->has("status") && $request->status !== "") {
            $query->where("status", $request->status);
        }

        $users = $query->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn($user) => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
                "status" => $user->status ?? "active",
                "last_login_at" => $user->last_login_at,
                "created_at" => $user->created_at,
            ]);

        return Inertia::render("Admin/Users/Index", [
            "users" => $users,
            "filters" => $request->only(["search", "role", "status"]),
        ]);
    }

    /**
     * Show create user form.
     */
    public function create(): Response
    {
        return Inertia::render("Admin/Users/Create", [
            "roles" => [User::ROLE_ADMIN, User::ROLE_REGISTRAR],
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|email|unique:users,email",
            "password" => ["required", "confirmed", Password::defaults()],
            "role" => ["required", Rule::in([User::ROLE_ADMIN, User::ROLE_REGISTRAR])],
        ]);

        $user = User::create([
            "name" => $validated["name"],
            "email" => $validated["email"],
            "password" => Hash::make($validated["password"]),
            "role" => $validated["role"],
            "status" => "active",
        ]);

        // Log the action
        RequestLog::create([
            "document_request_id" => null,
            "user_id" => $request->user()->id,
            "action" => "user_created",
            "description" => "Admin {$request->user()->name} created user {$user->name} with role {$user->role}",
        ]);

        return redirect()->route("admin.users.index")
            ->with("success", "User created successfully.");
    }

    /**
     * Show user details.
     */
    public function show(User $user): Response
    {
        return Inertia::render("Admin/Users/Show", [
            "user" => $user->load("logs"),
        ]);
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user): Response
    {
        return Inertia::render("Admin/Users/Edit", [
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $user->role,
            ],
            "roles" => [User::ROLE_ADMIN, User::ROLE_REGISTRAR],
        ]);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "email" => ["required", "email", Rule::unique("users")->ignore($user->id)],
            "role" => ["required", Rule::in([User::ROLE_ADMIN, User::ROLE_REGISTRAR])],
            "password" => ["nullable", "confirmed", Password::defaults()],
        ]);

        $user->name = $validated["name"];
        $user->email = $validated["email"];
        $user->role = $validated["role"];

        if (!empty($validated["password"])) {
            $user->password = Hash::make($validated["password"]);
        }

        $user->save();

        // Log the action
        RequestLog::create([
            "document_request_id" => null,
            "user_id" => $request->user()->id,
            "action" => "user_updated",
            "description" => "Admin {$request->user()->name} updated user {$user->name}",
        ]);

        return redirect()->route("admin.users.index")
            ->with("success", "User updated successfully.");
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            "password" => ["required", "string", "min:8", "confirmed"],
        ]);

        $user->update([
            "password" => Hash::make($request->password),
        ]);

        // Log the action
        RequestLog::create([
            "document_request_id" => null,
            "user_id" => $request->user()->id,
            "action" => "password_reset",
            "description" => "Admin {$request->user()->name} reset password for user {$user->name}",
        ]);

        return back()->with("success", "Password reset successfully.");
    }

    /**
     * Update user status (active/suspended).
     */
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            "status" => ["required", Rule::in(["active", "suspended"])],
        ]);

        // Prevent suspending yourself
        if ($user->id === $request->user()->id && $request->status === "suspended") {
            return back()->withErrors(["error" => "You cannot suspend your own account."]);
        }

        $oldStatus = $user->status ?? "active";
        $user->update(["status" => $request->status]);

        // Log the action
        RequestLog::create([
            "document_request_id" => null,
            "user_id" => $request->user()->id,
            "action" => "status_change",
            "old_value" => $oldStatus,
            "new_value" => $request->status,
            "description" => "Admin {$request->user()->name} changed status for user {$user->name}",
        ]);

        return back()->with("success", "User status updated successfully.");
    }

    /**
     * Bulk update user status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            "user_ids" => ["required", "array"],
            "user_ids.*" => ["exists:users,id"],
            "status" => ["required", Rule::in(["active", "suspended"])],
        ]);

        $users = User::whereIn("id", $request->user_ids)
            ->where("id", "!=", $request->user()->id) // Exclude self
            ->get();

        foreach ($users as $user) {
            $user->update(["status" => $request->status]);
        }

        // Log the action
        RequestLog::create([
            "document_request_id" => null,
            "user_id" => $request->user()->id,
            "action" => "bulk_status_change",
            "description" => "Admin {$request->user()->name} bulk updated status for " . count($users) . " users",
        ]);

        return back()->with("success", count($users) . " users updated successfully.");
    }

    /**
     * Delete user (soft delete).
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return back()->withErrors(["error" => "You cannot delete your own account."]);
        }

        $userName = $user->name;
        $user->delete();

        // Log the action
        RequestLog::create([
            "document_request_id" => null,
            "user_id" => $request->user()->id,
            "action" => "user_deleted",
            "description" => "Admin {$request->user()->name} deleted user {$userName}",
        ]);

        return redirect()->route("admin.users.index")
            ->with("success", "User deleted successfully.");
    }
}
