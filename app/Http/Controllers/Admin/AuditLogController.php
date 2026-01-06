<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs.
     */
    public function index(Request $request): Response
    {
        $query = AuditLog::with('user:id,name,email')
            ->latest();

        // Filter by Action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by Role
        if ($request->filled('role')) {
            $query->where('user_role', $request->role);
        }

        // Filter by Model Type
        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Global Search (User name, Description, User ID)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('user', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['action', 'role', 'model_type', 'start_date', 'end_date', 'search']),
            // Provide unique options for dropdowns if needed, or hardcode in frontend
            'actions' => AuditLog::select('action')->distinct()->pluck('action'),
        ]);
    }
}
