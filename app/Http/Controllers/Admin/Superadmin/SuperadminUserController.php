<?php

namespace App\Http\Controllers\Admin\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SuperadminUserController extends Controller
{
    /**
     * Display list of all users with search and filters.
     */
    public function index(Request $request): Response
    {
        $query = User::query();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role !== '') {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $users = $query->latest()
            ->paginate(20)
            ->withQueryString()
            ->through(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status ?? 'active',
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
            ]);

        return Inertia::render('Admin/Superadmin/Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Show user details.
     */
    public function show(User $user): Response
    {
        $user->load(['requestLogs', 'processedRequests']);

        return Inertia::render('Admin/Superadmin/Users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status ?? 'active',
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
                'request_logs_count' => $user->requestLogs->count(),
                'processed_requests_count' => $user->processedRequests->count(),
            ],
        ]);
    }

    /**
     * Impersonate a user.
     */
    public function impersonate(Request $request, User $user)
    {
        // Prevent impersonating yourself
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot impersonate yourself.']);
        }

        // Store original user ID in session
        $request->session()->put('impersonating', $request->user()->id);
        
        // Log the impersonation
        RequestLog::create([
            'user_id' => $request->user()->id,
            'action' => 'impersonate',
            'description' => "Superadmin {$request->user()->name} impersonated user {$user->name}",
        ]);

        Auth::login($user);

        return redirect()->route('admin.dashboard')
            ->with('success', "Now impersonating {$user->name}");
    }

    /**
     * Stop impersonating and return to original user.
     */
    public function stopImpersonating(Request $request)
    {
        $originalUserId = $request->session()->get('impersonating');

        if (!$originalUserId) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'No active impersonation session.');
        }

        $originalUser = User::find($originalUserId);

        if (!$originalUser) {
            $request->session()->forget('impersonating');
            return redirect()->route('login')
                ->with('error', 'Original user account not found.');
        }

        $request->session()->forget('impersonating');

        Auth::login($originalUser);

        return redirect()->route('admin.superadmin.users.index')
            ->with('success', 'Stopped impersonating. Returned to your account.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Log the action
        RequestLog::create([
            'user_id' => $request->user()->id,
            'action' => 'password_reset',
            'description' => "Superadmin {$request->user()->name} reset password for user {$user->name}",
        ]);

        return back()->with('success', 'Password reset successfully.');
    }

    /**
     * Update user status (active/suspended).
     */
    public function updateStatus(Request $request, User $user)
    {
        $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        // Prevent suspending yourself
        if ($user->id === $request->user()->id && $request->status === 'suspended') {
            return back()->withErrors(['error' => 'You cannot suspend your own account.']);
        }

        $oldStatus = $user->status ?? 'active';
        $user->update(['status' => $request->status]);

        // Log the action
        RequestLog::create([
            'user_id' => $request->user()->id,
            'action' => 'status_change',
            'old_value' => $oldStatus,
            'new_value' => $request->status,
            'description' => "Superadmin {$request->user()->name} changed status for user {$user->name}",
        ]);

        return back()->with('success', 'User status updated successfully.');
    }

    /**
     * Bulk update user status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id'],
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        $users = User::whereIn('id', $request->user_ids)
            ->where('id', '!=', $request->user()->id) // Exclude self
            ->get();

        foreach ($users as $user) {
            $user->update(['status' => $request->status]);
        }

        // Log the action
        RequestLog::create([
            'user_id' => $request->user()->id,
            'action' => 'bulk_status_change',
            'description' => "Superadmin {$request->user()->name} bulk updated status for " . count($users) . " users",
        ]);

        return back()->with('success', count($users) . ' users updated successfully.');
    }

    /**
     * Delete user (soft delete).
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $userName = $user->name;
        $user->delete();

        // Log the action
        RequestLog::create([
            'user_id' => $request->user()->id,
            'action' => 'user_deleted',
            'description' => "Superadmin {$request->user()->name} deleted user {$userName}",
        ]);

        return redirect()->route('admin.superadmin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
