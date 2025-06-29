<?php

namespace App\Http\Controllers;

use App\Models\TourDate;
use Illuminate\Http\Request;

class TourDateController extends Controller
{
    public function index($tourId)
    {
        return TourDate::where('tour_id', $tourId)->get();
    }

public function store(Request $request, $tourId)
{
    $request->validate([
        'available_date' => 'required|date',
        'available_time' => 'nullable',
        'seats' => 'required|integer|min:1',
    ]);

    return TourDate::create([
        'tour_id' => $tourId,
        'available_date' => $request->available_date,
        'available_time' => $request->available_time,
        'seats' => $request->seats,
    ]);
}


    public function show($id)
    {
        return TourDate::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $date = TourDate::findOrFail($id);
        $date->update($request->all());
        return $date;
    }

    public function destroy($id)
    {
        TourDate::destroy($id);
        return response()->json(['message' => 'Fecha eliminada']);
    }

}
