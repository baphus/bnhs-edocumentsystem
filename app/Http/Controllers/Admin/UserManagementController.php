<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display list of admin users.
     */
    public function index(): Response
    {
        $users = User::latest()
            ->paginate(20)
            ->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->formatted_name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show create user form.
     */
    public function create(): Response
    {
        // Superadmins can only be created through seeding, not through the UI
        return Inertia::render('Admin/Users/Create', [
            'roles' => ['registrar'],
        ]);
    }

    /**
     * Store a new user.
     */
    public function store(Request $request)
    {
        // Superadmins can only be created through seeding, not through the UI
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['registrar'])],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show edit user form.
     */
    public function edit(User $user): Response
    {
        // Prevent editing superadmin users - they should only be managed through seeding
        if ($user->isSuperadmin()) {
            abort(403, 'Superadmin users cannot be edited through the UI.');
        }

        // Superadmins can only be created through seeding, not through the UI
        return Inertia::render('Admin/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'roles' => ['registrar'],
        ]);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user)
    {
        // Prevent editing superadmin users - they should only be managed through seeding
        if ($user->isSuperadmin()) {
            return back()->withErrors(['role' => 'Superadmin users cannot be edited through the UI.']);
        }

        // Superadmins can only be created through seeding, not through the UI
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['registrar'])],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Delete a user.
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Prevent deletion of superadmin users - they should only be managed through seeding
        if ($user->isSuperadmin()) {
            return back()->withErrors(['error' => 'Superadmin users cannot be deleted through the UI.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}

