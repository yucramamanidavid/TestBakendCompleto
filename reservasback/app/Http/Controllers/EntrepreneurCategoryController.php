<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\EntrepreneurCategory;
use Illuminate\Http\Request;

class EntrepreneurCategoryController extends Controller
{
    public function index()
    {
        return response()->json(EntrepreneurCategory::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'entrepreneur_id' => 'required|exists:entrepreneurs,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $ec = EntrepreneurCategory::create($data);

        return response()->json($ec, 201);
    }

    public function show($entrepreneur_id, $category_id)
    {
        $assignment = EntrepreneurCategory::where('entrepreneur_id', $entrepreneur_id)
            ->where('category_id', $category_id)
            ->firstOrFail();

        return response()->json($assignment);
    }

    public function update(Request $request, $entrepreneur_id, $category_id)
    {
        $data = $request->validate([
            'entrepreneur_id' => 'required|exists:entrepreneurs,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        // Hacer update directamente con where
        EntrepreneurCategory::where('entrepreneur_id', $entrepreneur_id)
            ->where('category_id', $category_id)
            ->update($data);

        // Devolver el nuevo registro actualizado
        $updated = EntrepreneurCategory::where('entrepreneur_id', $data['entrepreneur_id'])
            ->where('category_id', $data['category_id'])
            ->first();

        return response()->json($updated);
    }

    public function destroy($entrepreneur_id, $category_id)
    {
        EntrepreneurCategory::where('entrepreneur_id', $entrepreneur_id)
            ->where('category_id', $category_id)
            ->delete();

        return response()->json([
            'message' => 'Categoría desasignada del emprendedor'
        ]);
    }

    public function count()
    {
        return response()->json([
            'count' => Category::count()
        ]);
    }
}
// This controller manages the many-to-many relationship between entrepreneurs and categories.