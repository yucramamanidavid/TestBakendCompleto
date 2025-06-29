<?php

namespace App\Http\Controllers;

use App\Models\CustomPackage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomPackageController extends Controller
{
    /**
     * Mostrar todos los paquetes personalizados del usuario autenticado.
     */
    public function index()
    {
        $userId = $this->getUserId();
        $packages = CustomPackage::with('products')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        return response()->json($packages);
    }

    /**
     * Mostrar un solo paquete.
     */
    public function show($id)
    {
        $package = CustomPackage::with(['products'])->find($id);

        if (!$package || $package->user_id !== $this->getUserId()) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        return response()->json($package);
    }

    /**
     * Crear un nuevo paquete personalizado.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'status' => 'in:borrador,confirmado',
            'products' => 'required|array|min:1',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package = CustomPackage::create([
            'user_id' => $this->getUserId(),
            'name' => $request->name,
            'status' => $request->status ?? 'borrador',
        ]);

        $total = $this->attachProductsAndCalculateTotal($package, $request->products);

        $package->total_amount = $total;
        $package->save();

        return response()->json(['message' => 'Paquete personalizado creado', 'data' => $package], 201);
    }

    /**
     * Actualizar un paquete personalizado.
     */
    public function update(Request $request, $id)
    {
        $package = CustomPackage::where('user_id', $this->getUserId())->find($id);

        if (!$package) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'status' => 'in:borrador,confirmado',
            'products' => 'nullable|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $package->update([
            'name' => $request->name ?? $package->name,
            'status' => $request->status ?? $package->status,
        ]);

        if ($request->has('products')) {
            $package->products()->detach();

            $total = $this->attachProductsAndCalculateTotal($package, $request->products);

            $package->total_amount = $total;
            $package->save();
        }

        return response()->json(['message' => 'Paquete actualizado', 'data' => $package]);
    }

    /**
     * Eliminar un paquete personalizado.
     */
    public function destroy($id)
    {
        $package = CustomPackage::where('user_id', $this->getUserId())->find($id);

        if (!$package) {
            return response()->json(['error' => 'Paquete no encontrado'], 404);
        }

        $package->delete();

        return response()->json(['message' => 'Paquete eliminado']);
    }

    /**
     * Obtener el ID del usuario autenticado (permite mock en tests).
     */
    protected function getUserId()
    {
        return Auth::id();
    }

    /**
     * Adjuntar productos y calcular monto total.
     */
    private function attachProductsAndCalculateTotal($package, $products)
    {
        $total = 0;

        foreach ($products as $productData) {
            $product = Product::find($productData['id']);
            $quantity = $productData['quantity'];

            $package->products()->attach($product->id, ['quantity' => $quantity]);

            $total += $product->price * $quantity;
        }

        return $total;
    }
}
