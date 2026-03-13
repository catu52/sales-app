<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Get all clients
        $clients = \App\Models\Client::all();
        //Return clients as JSON
        return response()->json($clients);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:clients',
        ]);

        $client = \App\Models\Client::create([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json($client, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //Find client by ID
        $client = \App\Models\Client::find($id);
        //Client not found
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        //Return client data
        return response()->json($client);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Validate request
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:clients,email,' . $id,
        ]);

        //Find client by ID
        $client = \App\Models\Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        //Update client with validated data
        $client->update($request->only(['name', 'email']));

        return response()->json($client);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Find client by ID
        $client = \App\Models\Client::find($id);
        if (!$client) {
            return response()->json(['message' => 'Client not found'], 404);
        }
        //Delete client
        $client->delete();

        return response()->json(['message' => 'Client deleted successfully']);
    }
}
