<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use App\Models\Category;
use App\Models\Entrepreneur;
use App\Models\Association;
use App\Models\Reservation;
use App\Models\Place;

class DashboardStatsController extends Controller
{
    public function countUsers(): JsonResponse
    {
        return response()->json(['count' => User::count()]);
    }

    public function countCategories(): JsonResponse
    {
        return response()->json(['count' => Category::count()]);
    }

    public function countEntrepreneurs(): JsonResponse
    {
        return response()->json(['count' => Entrepreneur::count()]);
    }

    public function countAssociations(): JsonResponse
    {
        return response()->json(['count' => Association::count()]);
    }

    public function countReservations(): JsonResponse
    {
        return response()->json(['count' => Reservation::where('status', '!=', 'cancelada')->count()]);
    }

    public function countPlaces(): JsonResponse
    {
        return response()->json(['count' => Place::count()]);
    }
}
