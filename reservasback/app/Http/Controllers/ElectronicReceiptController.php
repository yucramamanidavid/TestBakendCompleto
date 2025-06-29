<?php

namespace App\Http\Controllers;

use App\Mail\EnviarBoletaPDF;
use App\Models\ElectronicReceipt;
use App\Models\Reservation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ElectronicReceiptController extends Controller
{
    /**
     * Generar boleta para una reserva confirmada.
     */
    public function generar($reservationId)
    {
        $reserva = Reservation::with(['product', 'user'])->findOrFail($reservationId);

        // Validaciones básicas
        if ($reserva->status !== 'confirmada') {
            return response()->json(['error' => 'La reserva no está confirmada.'], 422);
        }

        if (!$reserva->product && !$reserva->custom_package_id) {
            return response()->json(['error' => 'No hay producto o paquete asociado a esta reserva.'], 422);
        }

        // Verifica si ya existe boleta
        if ($reserva->electronicReceipt) {
            return response()->json(['error' => 'Ya existe una boleta para esta reserva.'], 409);
        }

        // Determinar datos del producto o paquete
        $descripcion = $reserva->product ? $reserva->product->name : $reserva->customPackage->name;
        $emprendedorId = $reserva->product ? $reserva->product->entrepreneur_id : $reserva->customPackage->products()->first()->entrepreneur_id;

        // Generar correlativo (simple, se puede mejorar)
        $ultimo = ElectronicReceipt::orderBy('id', 'desc')->first();
        $numero = str_pad(($ultimo->id ?? 0) + 1, 6, '0', STR_PAD_LEFT);

        // Crear boleta
        $boleta = ElectronicReceipt::create([
            'reservation_id' => $reserva->id,
            'emprendedor_id' => $emprendedorId,
            'cliente_id' => $reserva->user_id,
            'serie' => 'B001',
            'numero' => $numero,
            'monto_total' => $reserva->total_amount,
            'estado_sunat' => 'pendiente',
        ]);

        return response()->json([
            'message' => 'Boleta generada correctamente.',
            'boleta' => $boleta
        ], 201);
    }

    /**
     * Mostrar boletas de un cliente
     */
    public function indexCliente($userId)
    {
        $boletas = ElectronicReceipt::where('cliente_id', $userId)->with('reservation')->get();
        return response()->json($boletas);
    }

    /**
     * Mostrar boletas de un emprendedor
     */
    public function indexEmprendedor($emprendedorId)
    {
        $boletas = ElectronicReceipt::where('emprendedor_id', $emprendedorId)->with('reservation')->get();
        return response()->json($boletas);
    }
public function descargarPDF($id)
{
    $boleta = ElectronicReceipt::with(['reservation.product', 'cliente', 'emprendedor', 'reservation.customPackage'])->findOrFail($id);

    $reserva = $boleta->reservation;

    // Validaciones adicionales de relaciones
    if (!$reserva || !$boleta->cliente || !$boleta->emprendedor) {
        return response()->json(['error' => 'Faltan datos obligatorios para generar la boleta.'], 422);
    }

    // Asignar descripción y precio unitario
    $detalle = $reserva->product
        ? $reserva->product->name
        : ($reserva->customPackage->name ?? 'Paquete personalizado');

    $precio_unitario = $reserva->quantity > 0
        ? $reserva->total_amount / $reserva->quantity
        : 0;

    $fecha_emision = $boleta->created_at
        ? $boleta->created_at->format('d/m/Y')
        : now()->format('d/m/Y');

    // Generar PDF
    $pdf = Pdf::loadView('pdf.boleta', compact(
        'boleta',
        'reserva',
        'detalle',
        'precio_unitario',
        'fecha_emision'
    ));

    return $pdf->stream("boleta-{$boleta->serie}-{$boleta->numero}.pdf");
}

public function enviarCorreo($id)
{
    $boleta =$boleta = ElectronicReceipt::with([
    'reservation.product',
    'reservation.customPackage',
    'cliente',
    'emprendedor'
])->findOrFail($id);

    $reserva = $boleta->reservation;
    $detalle = $reserva->product ? $reserva->product->name : $reserva->customPackage->name ?? 'Paquete personalizado';
    $precio_unitario = $reserva->total_amount / $reserva->quantity;

    $pdf = Pdf::loadView('pdf.boleta', compact('boleta', 'reserva', 'detalle', 'precio_unitario'));
    $filename = "boleta-{$boleta->serie}-{$boleta->numero}.pdf";

    Mail::to($boleta->cliente->email)->send(new EnviarBoletaPDF($pdf->output(), $filename));

    return response()->json(['message' => 'Correo enviado correctamente']);
}
}
