<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entrepreneur;
use App\Models\Place;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlaceController extends Controller
{
    protected $place;

    public function __construct(Place $place)
    {
        $this->place = $place;
    }

    public function index()
    {
        return response()->json(
            $this->place->orderBy('created_at', 'desc')->get()
        );
    }

    public function show($id)
    {
        return response()->json(Place::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'excerpt'    => 'required|string',
            'activities' => 'nullable|array',
            'stats'      => 'nullable|array',
            'image_file' => 'nullable|image|max:2048',
            'image_url'  => 'nullable|url|max:2048',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'category'   => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('places', 'public');
            $data['image_url'] = asset("storage/$path");
        }

        $place = Place::create($data);
        return response()->json($place, 201);
    }

    public function update(Request $request, $id)
    {
        $place = Place::findOrFail($id);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'excerpt'    => 'required|string',
            'activities' => 'nullable|array',
            'stats'      => 'nullable|array',
            'image_file' => 'nullable|image|max:2048',
            'image_url'  => 'nullable|url|max:2048',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'category'   => 'nullable|string|max:100',
        ]);

        if ($request->hasFile('image_file')) {
            if ($place->image_url && str_contains($place->image_url, '/storage/')) {
                $old = str_replace(asset('storage/') . '', '', $place->image_url);
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('image_file')->store('places', 'public');
            $data['image_url'] = asset("storage/$path");
        }

        $place->update($data);
        return response()->json($place);
    }

    public function destroy($id)
    {
        $place = Place::findOrFail($id);

        if ($place->image_url && str_contains($place->image_url, '/storage/')) {
            $old = str_replace(asset('storage/') . '', '', $place->image_url);
            Storage::disk('public')->delete($old);
        }

        $place->delete();
        return response()->json(null, 204);
    }

    private function normalizeImageUrl($url)
    {
        if (!$url) return null;
        if (str_starts_with($url, '/')) return url($url);
        return $url;
    }

    public function entrepreneurs($id)
    {
        $entrepreneurs = Entrepreneur::where('place_id', $id)
            ->with(['user', 'categories'])
            ->get();

        return response()->json($entrepreneurs);
    }
}
