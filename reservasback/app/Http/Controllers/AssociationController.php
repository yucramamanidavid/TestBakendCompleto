<?php

namespace App\Http\Controllers;

use App\Models\Association;
use Illuminate\Http\Request;

class AssociationController extends Controller
{
    protected $association;

    public function __construct(Association $association)
    {
        $this->association = $association;
    }

    public function index()
    {
        $associations = $this->association->all();
        return response()->json($associations);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:associations',
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100',
        ]);

        $association = $this->association->create($validated);
        return response()->json($association, 201);
    }

    public function show($id)
    {
        $association = $this->association->with('entrepreneurs')->findOrFail($id);
        return response()->json($association);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255|unique:associations,name,' . $id,
            'description' => 'nullable|string',
            'region' => 'nullable|string|max:100',
        ]);

        $association = $this->association->findOrFail($id);
        $association->update($validated);

        return response()->json($association);
    }

    public function destroy($id)
    {
        $association = $this->association->findOrFail($id);
        $association->entrepreneurs()->delete();
        $association->delete();

        return response()->json(['message' => 'Asociación y emprendedores eliminados']);
    }

    public function count()
    {
        $count = $this->association->count();
        return response()->json(['count' => $count]);
    }

}
