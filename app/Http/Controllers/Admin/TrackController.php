<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Track;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Track::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%')
                  ->orWhere('category', 'like', '%' . $request->search . '%');
            });
        }

        $tracks = $query->latest()->get();

        return Inertia::render('Admin/Tracks/Index', [
            'tracks' => $tracks,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Tracks/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tracks,code',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Track::create($validated);

        return redirect()->route('admin.tracks.index')->with('success', 'Track created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Track $track): Response
    {
        return Inertia::render('Admin/Tracks/Edit', [
            'track' => $track,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Track $track)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:tracks,code,' . $track->id,
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $track->update($validated);

        return redirect()->route('admin.tracks.index')->with('success', 'Track updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Track $track)
    {
        $track->delete();

        return redirect()->back()->with('success', 'Track deleted successfully.');
    }
}

