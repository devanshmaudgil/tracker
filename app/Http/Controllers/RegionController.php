<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $regions = Region::orderBy('region', 'asc')
            ->orderBy('city', 'asc')
            ->get();
        
        return view('regions.index', compact('regions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'region' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
        ]);

        Region::create($request->all());

        return redirect()->route('regions.index')->with('success', 'Region added successfully.');
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

        return redirect()->route('regions.index')->with('success', 'Region updated successfully.');
    }

    public function destroy(string $id)
    {
        $region = Region::findOrFail($id);
        $region->delete();

        return redirect()->route('regions.index')->with('success', 'Region deleted successfully.');
    }
}
