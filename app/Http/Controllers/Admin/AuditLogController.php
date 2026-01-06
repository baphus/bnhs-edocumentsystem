<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
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

    /**
     * Export audit logs to CSV.
     */
    public function export(Request $request): HttpResponse
    {
        $query = AuditLog::with('user:id,name,email')
            ->latest();

        // Apply same filters as index
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('role')) {
            $query->where('user_role', $request->role);
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', 'like', '%' . $request->model_type . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

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

        $logs = $query->get();

        // Generate CSV
        $filename = 'audit_logs_' . date('Y-m-d_His') . '.csv';
        $handle = fopen('php://temp', 'r+');
        
        // CSV Headers
        fputcsv($handle, [
            'ID',
            'Date & Time',
            'User',
            'User Role',
            'Action',
            'Model Type',
            'Model ID',
            'Description',
            'IP Address',
            'User Agent',
        ]);

        // CSV Data
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->created_at->format('Y-m-d H:i:s'),
                $log->user ? $log->user->name : 'N/A',
                $log->user_role ?? 'N/A',
                $log->action,
                $log->model_type ?? 'N/A',
                $log->model_id ?? 'N/A',
                $log->description,
                $log->ip_address ?? 'N/A',
                $log->user_agent ?? 'N/A',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Clear audit logs (optionally by date range or all).
     */
    public function clear(Request $request)
    {
        $request->validate([
            'clear_type' => 'required|in:all,range',
            'start_date' => 'required_if:clear_type,range|date',
            'end_date' => 'required_if:clear_type,range|date|after_or_equal:start_date',
        ]);

        DB::beginTransaction();
        try {
            $query = AuditLog::query();

            if ($request->clear_type === 'range') {
                $query->whereBetween('created_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59',
                ]);
            }

            $count = $query->count();
            $query->delete();

            // Log this action
            AuditLog::create([
                'user_id' => auth()->id(),
                'user_role' => auth()->user()->role ?? 'unknown',
                'action' => 'cleared_audit_logs',
                'model_type' => 'AuditLog',
                'description' => $request->clear_type === 'all' 
                    ? "Cleared all audit logs ({$count} records)" 
                    : "Cleared audit logs from {$request->start_date} to {$request->end_date} ({$count} records)",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', "Successfully cleared {$count} audit log(s).");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to clear audit logs: ' . $e->getMessage());
        }
    }
}
