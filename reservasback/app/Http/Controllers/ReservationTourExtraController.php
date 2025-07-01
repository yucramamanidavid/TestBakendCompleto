<?php

namespace App\Http\Controllers;

use App\Models\ReservationTourExtra;
use Illuminate\Http\Request;

class ReservationTourExtraController extends Controller
{
    // Almacena un nuevo extra asociado a una reserva
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'tour_extra_id' => 'required|exists:tour_extras,id'
        ]);

        $extra = ReservationTourExtra::create($validated);

        return response()->json($extra, 201); // Retorna el recurso creado con código 201
    }

    // Elimina un extra de una reserva
    public function destroy($id)
    {
        $deleted = ReservationTourExtra::destroy($id);

        if ($deleted) {
            return response()->json(['message' => 'Extra quitado de la reserva']);
        } else {
            return response()->json(['message' => 'No se encontró el extra'], 404);
        }
    }
}
