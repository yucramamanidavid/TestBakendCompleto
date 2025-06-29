<?php

namespace App\Http\Controllers;

use App\Models\ReservationTourExtra;
use Illuminate\Http\Request;

class ReservationTourExtraController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reservation_id' => 'required|exists:reservations,id',
            'tour_extra_id' => 'required|exists:tour_extras,id'
        ]);

        return ReservationTourExtra::create($request->all());
    }

    public function destroy($id)
    {
        ReservationTourExtra::destroy($id);
        return response()->json(['message' => 'Extra quitado de la reserva']);
    }
}
