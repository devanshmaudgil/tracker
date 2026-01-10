<?php

namespace App\Http\Controllers;

use App\Models\Month;
use Illuminate\Http\Request;

class MonthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $months = Month::orderBy('id', 'desc')->get();
        return view('months.index', compact('months'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('months.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|string|unique:months,month',
        ]);

        Month::create($request->all());

        return redirect()->route('months.index')->with('success', 'Month added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $month = Month::findOrFail($id);
        return view('months.edit', compact('month'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'month' => 'required|string|unique:months,month,' . $id,
        ]);

        $month = Month::findOrFail($id);
        $month->update($request->all());

        return redirect()->route('months.index')->with('success', 'Month updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $month = Month::findOrFail($id);
        $month->delete();

        return redirect()->route('months.index')->with('success', 'Month deleted successfully.');
    }
}
