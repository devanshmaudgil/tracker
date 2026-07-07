<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $query = Region::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('city', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%");
            });
        }

        $regions = $query->orderBy('region', 'asc')
            ->orderBy('city', 'asc')
            ->paginate(20);
        
        if ($request->ajax()) {
            return response()->json([
                'table' => view('regions._table', compact('regions'))->render(),
                'pagination' => $regions->appends(request()->query())->links('vendor.pagination.custom')->render(),
                'count_text' => $regions->total() > 0
                    ? "Showing {$regions->firstItem()} to {$regions->lastItem()} of {$regions->total()} entries"
                    : 'No entries found',
            ]);
        }

        $stats = [
            'total' => Region::count(),
            'states' => Region::distinct('region')->count('region'),
            'cities' => Region::whereNotNull('city')->where('city', '!=', '')->count(),
        ];

        return view('regions.index', compact('regions', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'region' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        Region::create($request->all());

        return redirect()->route('locations.index')->with('success', 'Location added successfully.');
    }

    public function edit(string $id)
    {
        $region = Region::findOrFail($id);
        return view('regions.edit', compact('region'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'region' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        $region = Region::findOrFail($id);
        $region->update($request->all());

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(string $id)
    {
        $region = Region::findOrFail($id);
        $region->delete();

        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
}
