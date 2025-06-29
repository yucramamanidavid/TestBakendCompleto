<?php

namespace App\Http\Controllers;

use App\Models\TourExtra;
use Illuminate\Http\Request;

class TourExtraController extends Controller
{
    // Listar todos los extras de un tour
    public function index($tourId)
    {
        return response()->json(
            TourExtra::where('tour_id', $tourId)->get()
        );
    }

    // Crear un nuevo extra para un tour
    public function store(Request $request, $tourId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric'
        ]);

        $extra = TourExtra::create([
            'tour_id' => $tourId,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);

        return response()->json($extra, 201);
    }

    // Ver detalles de un extra
    public function show($id)
    {
        return response()->json(TourExtra::findOrFail($id));
    }

    // Editar un extra
    public function update(Request $request, $id)
    {
        $extra = TourExtra::findOrFail($id);
        $extra->update($request->only(['name', 'description', 'price']));
        return response()->json($extra);
    }

    // Eliminar un extra
    public function destroy($id)
    {
        TourExtra::destroy($id);
        return response()->json(['message' => 'Extra eliminado']);
    }
}
