<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;

class ExportController extends Controller
{
    /**
     * Export requests to CSV.
     */
    public function requests(Request $request)
    {
        $query = DocumentRequest::with('documentType');

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_id', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%");
            });
        }

        if ($request->has('status') && $request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('document_type') && $request->document_type) {
            $query->where('document_type_id', $request->document_type);
        }

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $requests = $query->get();

        $filename = 'document_requests_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($requests) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Tracking ID',
                'Email',
                'First Name',
                'Middle Name',
                'Last Name',
                'LRN',
                'Grade Level',
                'Section',
                'Track/Strand',
                'School Year',
                'Document Type',
                'Category',
                'Purpose',
                'Status',
                'OTP Verified',
                'Created At',
                'Updated At',
            ]);

            // Data
            foreach ($requests as $req) {
                fputcsv($file, [
                    $req->tracking_id,
                    $req->email,
                    $req->first_name,
                    $req->middle_name ?? '',
                    $req->last_name,
                    $req->lrn,
                    $req->grade_level,
                    $req->section,
                    $req->track_strand ?? '',
                    $req->school_year_last_attended,
                    $req->documentType->name ?? 'N/A',
                    $req->documentType->category ?? 'N/A',
                    $req->purpose,
                    $req->status,
                    $req->otp_verified ? 'Yes' : 'No',
                    $req->created_at,
                    $req->updated_at,
                ]);
            }

            fclose($file);
        };

        return ResponseFacade::stream($callback, 200, $headers);
    }

    /**
     * Export users to CSV.
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->get();

        $filename = 'users_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['ID', 'Name', 'Email', 'Role', 'Status', 'Last Login', 'Created At']);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->status ?? 'active',
                    $user->last_login_at ?? '',
                    $user->created_at,
                ]);
            }

            fclose($file);
        };

        return ResponseFacade::stream($callback, 200, $headers);
    }
}
