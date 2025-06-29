<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Entrepreneur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * GET /api/products
     * Listar todos los productos (con imágenes).
     */

    public function index(): JsonResponse
{
    $user = auth()->user();

    // Si no hay usuario autenticado (ej. API pública sin token)
    if (!$user) {
        // Solo productos activos visibles públicamente
        $products = Product::with(['images', 'place', 'entrepreneur','categories'])
            ->where('is_active', true)
            ->get();
        return response()->json($products, 200);
    }

    // Si es un cliente, mostrar solo productos activos
    if ($user->hasRole('cliente')) {
        $products = Product::with(['images', 'place', 'entrepreneur','categories'])
            ->where('is_active', true)
            ->get();
        return response()->json($products, 200);
    }

    // Si es emprendedor, mostrar solo sus productos
    if ($user->hasRole('emprendedor')) {
        $entrepreneur = $user->entrepreneur;
        if (!$entrepreneur) {
            return response()->json(['message' => 'Perfil de emprendedor no encontrado'], 403);
        }

        $products = Product::with(['images', 'place', 'entrepreneur','categories','entrepreneur.user'])
            ->where('entrepreneur_id', $entrepreneur->id)
            ->get();
        return response()->json($products, 200);
    }

    // Si es super admin, mostrar todo
    if ($user->hasRole('super-admin')) {
        $products = Product::with(['images', 'place', 'entrepreneur','categories'])->get();
        return response()->json($products, 200);
    }

    return response()->json([], 403);
}

    /**
     * GET /api/products/{id}
     * Mostrar un producto específico.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::with(['images','place','categories','entrepreneur','entrepreneur.user'])->find($id);
        return $product
            ? response()->json($product, 200)
            : response()->json(['message' => 'Producto no encontrado'], 404);
    }
    /**
     * POST /api/products
     * Crear un nuevo producto (y sus imágenes si vienen en el payload).
     */
public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entrepreneur_id' => 'required|exists:entrepreneurs,id',
            'place_id'        => 'nullable|exists:places,id',
            'name'            => 'required|string|max:150',
            'description'     => 'nullable|string',
            'price'           => 'required|numeric|min:0',
            'stock'           => 'nullable|integer|min:0',
            'duration'        => 'nullable|string',
            'main_image'      => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'       => 'nullable|boolean',
            'images'          => 'nullable|array',
            'images.*'        => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_ids'    => 'required|array',
            'category_ids.*'  => 'exists:categories,id',
        ]);

        /*---- crear producto sin imágenes ----*/
        $productData = collect($validated)->except('main_image','images','category_ids')->toArray();
        $product     = Product::create($productData);

        /*---- main_image (opcional) ----*/
        if ($request->hasFile('main_image')) {
            $path = $request->file('main_image')->store('products', 'public');
            $product->update(['main_image' => Storage::url($path)]);
        }

        /*---- galería ----*/
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $order => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_url' => Storage::url($path),
                    'order'     => $order,
                ]);
            }
        }

        /*---- categorías ----*/
        $product->categories()->sync($validated['category_ids']);

        return response()->json([
            'message' => 'Producto creado exitosamente',
            'data'    => $product->load(['images','categories', 'place', 'entrepreneur.user']),
        ], 201);
    }
    /**
     * PUT/PATCH /api/products/{id}
     * Actualizar un producto. Si se envía `images`, reemplaza la galería completa.
     */
 public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $validated = $request->validate([
            'place_id'     => 'nullable|exists:places,id',
            'name'         => 'sometimes|required|string|max:150',
            'description'  => 'nullable|string',
            'price'        => 'sometimes|required|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'duration'     => 'nullable|string',
            'main_image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active'    => 'nullable|boolean',
            'images'       => 'nullable|array',
            'images.*'     => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
        ]);

        /*---- datos básicos ----*/
        $product->update(collect($validated)->except('main_image','images','category_ids')->toArray());

        /*---- main_image ----*/
        if ($request->hasFile('main_image')) {
            // borrar la anterior si existía
            if ($product->main_image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $product->main_image));
            }
            $path = $request->file('main_image')->store('products', 'public');
            $product->update(['main_image' => Storage::url($path)]);
        }

        /*---- galería ----*/
        if ($request->hasFile('images')) {
            // eliminar todas las anteriores
            foreach ($product->images as $img) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $img->image_url));
                $img->delete();
            }
            foreach ($request->file('images') as $order => $file) {
                $path = $file->store('products', 'public');
                $product->images()->create([
                    'image_url' => Storage::url($path),
                    'order'     => $order,
                ]);
            }
        }

        /*---- categorías ----*/
        if (array_key_exists('category_ids', $validated)) {
            $product->categories()->sync($validated['category_ids']);
        }

        return response()->json([
            'message' => 'Producto actualizado',
            'data'    => $product->load(['images','categories']),
        ], 200);
    }

    /**
     * DELETE /api/products/{id}
     * Eliminar un producto (cascade borra imágenes).
     */
    public function destroy(int $id): JsonResponse
    {
        $product = Product::with('images')->find($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        // borrar archivos físicos
        if ($product->main_image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $product->main_image));
        }
        foreach ($product->images as $img) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $img->image_url));
        }

        $product->delete();
        return response()->json(['message' => 'Producto eliminado'], 200);
    }

    /**
     * POST /api/products/{id}/images
     * Agregar una imagen individual a un producto.
     */
   public function addImage(Request $request, int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $data = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order' => 'nullable|integer',
        ]);

        $path  = $data['image']->store('products', 'public');
        $image = $product->images()->create([
            'image_url' => Storage::url($path),
            'order'     => $data['order'] ?? 0,
        ]);

        return response()->json($image, 201);
    }

    /**
     * DELETE /api/products/{product_id}/images/{image_id}
     * Eliminar una imagen específica de un producto.
     */
    public function deleteImage(int $product_id, int $image_id): JsonResponse
    {
        $image = ProductImage::where('product_id', $product_id)->where('id', $image_id)->first();
        if (!$image) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }

        Storage::disk('public')->delete(str_replace('/storage/', '', $image->image_url));
        $image->delete();

        return response()->json(null, 204);
    }
    public function myProducts()
    {
        $user = auth()->user();


        if (!$user || !$user->entrepreneur) {
            return response()->json(['message' => 'No autorizado o sin perfil de emprendedor'], 403);
        }

        $entrepreneurId = $user->entrepreneur->id;

        $products = Product::with(['categories', 'place'])
            ->where('entrepreneur_id', $entrepreneurId)
            ->get();

        return response()->json($products);
    }
// ProductController.php
public function byEntrepreneur($id)
{
    $products = Product::where('entrepreneur_id', $id)->get();
    return response()->json($products);
}


}
