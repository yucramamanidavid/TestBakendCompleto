<?php

namespace App\Http\Controllers;

use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AboutController extends Controller
{
public function index()
{
    $abouts = About::all();
    // Mapear para agregar 'image_url' a cada About
    $abouts->each(function ($about) {
        $about->image_url = $about->image ? asset('storage/' . $about->image) : null;
    });
    return response()->json($abouts);
}

// app/Http/Controllers/AboutController.php

    public function show($id)
    {
        $about = About::findOrFail($id);
        if ($about->image) {
            // IMPORTANTE: SOLO 'storage/...'
            $about->image_url = asset('storage/' . $about->image);
        }
        return response()->json($about);
    }


    public function store(Request $request)
    {
        $data = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'image'   => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('abouts', 'public');
        }

        $about = About::create($data);
        return response()->json($about, 201);
    }

    public function update(Request $request, $id)
{
    $about = About::findOrFail($id);

    $data = $request->validate([
        'title'        => 'required|string|max:255',
        'content'      => 'required|string',
        'image'        => 'nullable|image|max:2048',
        'remove_image' => 'nullable|in:1,true,0,false',
    ]);

    $removeImage = filter_var($request->input('remove_image'), FILTER_VALIDATE_BOOLEAN);

    if ($removeImage && $about->image) {
        Storage::disk('public')->delete($about->image);
        $data['image'] = null;
    }

    if ($request->hasFile('image')) {
        if ($about->image) {
            Storage::disk('public')->delete($about->image);
        }
        $data['image'] = $request->file('image')->store('abouts', 'public');
    }

    $about->update($data);
    return response()->json($about);
}


    public function destroy($id)
    {
        $about = About::findOrFail($id);
        if ($about->image) {
            Storage::disk('public')->delete($about->image);
        }
        $about->delete();
        return response()->json(null, 204);
    }

    public function activate($id)
    {
        About::query()->update(['active' => false]);
        $about = About::findOrFail($id);
        $about->active = true;
        $about->save();

        return response()->json($about);
    }

public function active()
{
    $about = About::where('active', true)->first();
    if ($about) {
        $about->image_url = $about->image ? asset('storage/' . $about->image) : null;
    }
    return response()->json($about, 200);
}
}
