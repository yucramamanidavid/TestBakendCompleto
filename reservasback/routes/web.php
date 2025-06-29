<?php

use App\Models\ElectronicReceipt;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);
// routes/web.php
Route::get('sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF token set'])->withCookie(cookie('XSRF-TOKEN', csrf_token()));
});
Route::get('/boleta-test/{id}', function ($id) {
    $boleta = ElectronicReceipt::with(['reservation', 'cliente', 'emprendedor'])->findOrFail($id);
    $reserva = $boleta->reservation;
    $detalle = $reserva->product ? $reserva->product->name : ($reserva->customPackage->name ?? 'Paquete personalizado');
    $precio_unitario = $reserva->total_amount / $reserva->quantity;
    $fecha_emision = $boleta->created_at->format('d/m/Y');

    return view('pdf.boleta', compact('boleta', 'reserva', 'detalle', 'precio_unitario', 'fecha_emision'));
});
