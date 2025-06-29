<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;

class TourController extends Controller
{
    // Obtener todos los tours
public function index(Request $request)
{
    $user = $request->user();

    if ($user->hasRole('super-admin')) {
        // Super-admin ve todos los tours
        $tours = Tour::all();
    } elseif ($user->hasRole('emprendedor') && $user->entrepreneur) {
        // Emprendedor ve sus propios tours
        $tours = Tour::where('entrepreneur_id', $user->entrepreneur->id)->get();
    } else {
        // Otros usuarios ven solo tours activos
        $tours = Tour::where('active', true)->get();
    }

    return response()->json($tours);
}


    // Obtener un tour específico
    public function show($id)
    {
        $tour = Tour::findOrFail($id);
        return response()->json($tour);
    }

    // Crear un nuevo tour
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'image' => 'nullable|string',
            'active' => 'sometimes|boolean', // Nuevo campo
        ]);

        $entrepreneur = $request->user()->entrepreneur;

        $tour = Tour::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $request->image,
            'entrepreneur_id' => $entrepreneur->id,
            'active' => $request->input('active', true), // Por defecto activo
        ]);

        return response()->json($tour, 201);
    }

    // Actualizar un tour
    public function update(Request $request, $id)
    {
        $tour = Tour::findOrFail($id);
        $entrepreneur = $request->user()->entrepreneur;

        if ($tour->entrepreneur_id !== $entrepreneur->id && !$request->user()->hasRole('super-admin')) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'image' => 'nullable|string',
            'active' => 'sometimes|boolean',
        ]);

        $tour->update($request->all());

        return response()->json($tour);
    }

    // Eliminar un tour
    public function destroy($id)
    {
        $tour = Tour::findOrFail($id);
        $tour->delete();

        return response()->json(null, 204);
    }
}
