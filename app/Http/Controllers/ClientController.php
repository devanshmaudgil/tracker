<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display the clients info page.
     */
    public function info()
    {
        $clients = Client::withCount('trackers')->orderBy('client')->get();
        return view('clients.info', compact('clients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client' => 'required|string|max:255|unique:clients,client',
            'type' => 'required|in:Direct,End',
        ]);

        Client::create($request->only(['client', 'type']));

        return redirect()->route('clients.info')->with('success', 'Client added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $client = Client::findOrFail($id);
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'client' => 'required|string|max:255|unique:clients,client,' . $id,
            'type' => 'required|in:Direct,End',
        ]);

        $client = Client::findOrFail($id);
        $client->update($request->only(['client', 'type']));

        return redirect()->route('clients.info')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return redirect()->route('clients.info')->with('success', 'Client deleted successfully.');
    }
}
