<?php

namespace App\Http\Controllers;

use App\Models\CustomPackage;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Product;  // Cambié Tour a Product
use App\Models\ReservationTourExtra;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Crear una nueva reserva.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    // Mostrar todas las reservas del usuario
    public function userReservations()
    {
        $reservations = auth()->user()
            ->reservations()
            ->with(
                'product.entrepreneur',
                'customPackage.products.entrepreneur',
                'package.products.entrepreneur','package.entrepreneur', 'package.entrepreneur', 'payment','electronicReceipt'

            )
            ->latest()
            ->take(10)
            ->get();
\Log::info('RESERVAS DEL USUARIO', $reservations->toArray());

        return response()->json($reservations);
    }
    // Mostrar todas las reservas de un emprendedor
    public function entrepreneurReservations($entrepreneurId)
    {
        $reservations = Reservation::with([
                'product.entrepreneur',
                'customPackage.products.entrepreneur',
                'package.products.entrepreneur',
                'user','electronicReceipt'
            ])
            ->where(function ($query) use ($entrepreneurId) {
                $query->whereHas('product', function ($q) use ($entrepreneurId) {
                    $q->where('entrepreneur_id', $entrepreneurId);
                })
                ->orWhereHas('customPackage.products', function ($q) use ($entrepreneurId) {
                    $q->where('entrepreneur_id', $entrepreneurId);
                })
                ->orWhereHas('package', function ($q) use ($entrepreneurId) {
                    $q->where('entrepreneur_id', $entrepreneurId);
                });
            })
            ->get();

        return response()->json($reservations);
    }

    // Crear una nueva reserva
public function store(Request $request)
{
    $request->validate([
        'product_id'       => 'nullable|exists:products,id',
        'package_id'       => 'nullable|exists:packages,id',
        'custom_package_id'=> 'nullable|exists:custom_packages,id',
        'quantity'         => 'required|integer|min:1',
        'reservation_date' => 'required|date',
    ]);
\Log::info('Reservando custom_package_id', ['id' => $request->custom_package_id]);
    if (!$request->product_id && !$request->custom_package_id && !$request->package_id) {
        return response()->json(['message' => 'Debes enviar product_id, package_id o custom_package_id'], 422);
    }

    $totalPrice = 0;

    if ($request->product_id) {
        $product = Product::findOrFail($request->product_id);
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stock insuficiente'], 400);
        }
        $totalPrice = $product->price * $request->quantity;
        $product->decrement('stock', $request->quantity);
    }

    if ($request->custom_package_id) {
        $customPackage = CustomPackage::with('products')->findOrFail($request->custom_package_id);
        \Log::info('CustomPackage cargado', $customPackage->toArray());
        $totalPrice = $customPackage->total_amount;

        // ✅ ACTIVAR el paquete si está siendo reservado
         $customPackage->update(['status' => 'confirmado']);
    }

    if ($request->package_id) {
        $package = Package::findOrFail($request->package_id);
        $totalPrice = $package->price * $request->quantity;
    }

    $reservation = Reservation::create([
        'user_id'           => auth()->id(),
        'product_id'        => $request->product_id,
        'package_id'        => $request->package_id,
        'custom_package_id' => $request->custom_package_id,
        'reservation_code'  => uniqid('RES-', true),
        'quantity'          => $request->quantity,
        'reservation_date'  => $request->reservation_date,
        'total_amount'      => $totalPrice,
        'status'            => 'pendiente'
    ]);

    $reservation->load('product.entrepreneur', 'customPackage', 'package');

    return response()->json($reservation, 201);
}



    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'package_id' => 'nullable|exists:packages,id',
            'quantity' => 'required|integer|min:1',
            'reservation_date' => 'required|date',
            'status' => 'required|string'
        ]);

        $reservation->update([
            'product_id' => $request->product_id,
            'package_id' => $request->package_id,
            'quantity' => $request->quantity,
            'reservation_date' => $request->reservation_date,
            'status' => $request->status
        ]);

        return response()->json($reservation);
    }

    public function show($id)
    {
        $reservation = Reservation::with([
            'product.place',
            'product.entrepreneur.user',
            'customPackage.products.entrepreneur.user',
            'package.products.entrepreneur.user',
            'user',
            'payment','electronicReceipt'
        ])->findOrFail($id);

        return response()->json($reservation);
    }
    /**
     * Listar todas las reservas.
     */
    public function index()
    {
        $reservations = Reservation::with(['product', 'user', 'package'])->get();
        return response()->json($reservations);
    }

    /**
     * Eliminar una reserva.
     */
    public function destroy($id)
    {
        $reservation = Reservation::find($id);

        if (!$reservation) {
            return response()->json(['message' => 'Reserva no encontrada'], 404);
        }

        $user = auth()->user();
        \Log::info('Intento de eliminar reserva', [
            'auth_id' => $user?->id,
            'res_user_id' => $reservation->user_id
        ]);

        if ($reservation->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $reservation->delete();
        return response()->json(['message' => 'Reserva eliminada correctamente']);
    }
    public function directSale(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:15',
            'client_email' => 'nullable|email',
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|string',
            'operation_code' => 'nullable|string',
            'note' => 'nullable|string',
            'image_file' => 'nullable|image|max:2048',
        ]);

        $product = Product::findOrFail($request->product_id);
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stock insuficiente.'], 400);
        }

        $user = User::where('phone', $request->client_phone)
            ->orWhere('email', $request->client_email)
            ->first();

        $clientCreated = false;

        if (!$user) {
            $user = User::create([
                'name' => $request->client_name,
                'phone' => $request->client_phone,
                'email' => $request->client_email,
                'password' => bcrypt('temporal123'),
            ]);
            $clientCreated = true;
        }

        $reservation = Reservation::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'reservation_code' => uniqid('RES-'),
            'quantity' => $request->quantity,
            'total_amount' => $product->price * $request->quantity,
            'reservation_date' => now(),
            'status' => 'confirmada'
        ]);

        $imageUrl = null;
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('payments', 'public');
            $imageUrl = asset("storage/{$path}");
        }

        $payment = Payment::create([
            'reservation_id' => $reservation->id,
            'payment_method' => $request->payment_method,
            'payment_type' => 'presencial',
            'operation_code' => $request->operation_code,
            'note' => $request->note,
            'image_url' => $imageUrl,
            'status' => 'confirmado',
            'is_confirmed' => true,
            'confirmation_time' => now(),
            'confirmation_by' => Auth::id()
        ]);

        $product->decrement('stock', $request->quantity);

        return response()->json([
            'message' => 'Reserva y pago registrados correctamente.',
            'reservation' => $reservation,
            'payment' => $payment,
            'client_created' => $clientCreated,
        ], 201);
    }

public function myEntrepreneurReservations() {
    $entrepreneur = auth()->user()->entrepreneur;

    if (!$entrepreneur) {
        return response()->json(['message' => 'No tienes perfil de emprendedor'], 403);
    }

    $reservations = Reservation::with([
            'product.entrepreneur',
            'customPackage.products.entrepreneur',
            'package.products.entrepreneur',
            'package.entrepreneur',
            'user',
            'payment',
            'electronicReceipt'
        ])
        ->where(function ($query) use ($entrepreneur) {
            $query->whereHas('product', fn($q) => $q->where('entrepreneur_id', $entrepreneur->id))
                  ->orWhereHas('customPackage.products', fn($q) => $q->where('entrepreneur_id', $entrepreneur->id))
                  ->orWhereHas('package', fn($q) => $q->where('entrepreneur_id', $entrepreneur->id));
        })
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($reservations);
}

public function entrepreneurCount()
{
    $entrepreneur = auth()->user()->entrepreneur;

    if (!$entrepreneur) {
        return response()->json(['message' => 'No tienes perfil de emprendedor'], 403);
    }

    $count = Reservation::where(function ($query) use ($entrepreneur) {
        $query->whereHas('product', fn($q) => $q->where('entrepreneur_id', $entrepreneur->id))
              ->orWhereHas('customPackage.products', fn($q) => $q->where('entrepreneur_id', $entrepreneur->id))
              ->orWhereHas('package', fn($q) => $q->where('entrepreneur_id', $entrepreneur->id));
    })->count();

    return response()->json(['count' => $count]);
}
public function checkoutCart(Request $request)
{
    $request->validate([
        'items' => 'required|array|min:1',
        'items.*.type' => 'required|in:product,tour',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.reservation_date' => 'required|date',
        // Producto o tour, según tipo:
        'items.*.product_id' => 'required_if:items.*.type,product|exists:products,id',
        'items.*.tour_id' => 'required_if:items.*.type,tour|exists:tours,id',
        'items.*.extras' => 'array', // solo para tours, es opcional
        'items.*.extras.*' => 'integer|exists:tour_extras,id'
    ]);
    $user = $request->user();
    $reservations = [];

    DB::beginTransaction();
    try {
        foreach ($request->items as $item) {
            if ($item['type'] === 'product') {
                // --- PRODUCTO ---
                $product = Product::findOrFail($item['product_id']);
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para {$product->name}");
                }
                $reservation = Reservation::create([
                    'user_id'          => $user->id,
                    'product_id'       => $item['product_id'],
                    'quantity'         => $item['quantity'],
                    'reservation_code' => uniqid('RES-', true),
                    'reservation_date' => $item['reservation_date'],
                    'total_amount'     => $product->price * $item['quantity'],
                    'status'           => 'pendiente',
                ]);
                $product->decrement('stock', $item['quantity']);
            } else {
                // --- TOUR ---
                $tour = Tour::findOrFail($item['tour_id']);
                // Aquí podrías chequear disponibilidad en la fecha si tienes fechas/disponibilidad
                $reservation = Reservation::create([
                    'user_id'          => $user->id,
                    'tour_id'          => $item['tour_id'],
                    'quantity'         => $item['quantity'],
                    'reservation_code' => uniqid('RES-', true),
                    'reservation_date' => $item['reservation_date'],
                    'total_amount'     => $tour->price * $item['quantity'],
                    'status'           => 'pendiente',
                ]);
                // Extras
                if (!empty($item['extras']) && is_array($item['extras'])) {
                    foreach ($item['extras'] as $extraId) {
                        ReservationTourExtra::create([
                            'reservation_id' => $reservation->id,
                            'tour_extra_id' => $extraId
                        ]);
                    }
                }
            }
            $reservations[] = $reservation;
        }
        DB::commit();
        return response()->json(['reservations' => $reservations], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => $e->getMessage()], 400);
    }
}
}

