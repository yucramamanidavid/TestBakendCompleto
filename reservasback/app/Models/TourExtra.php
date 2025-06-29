<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; // ✅ AGREGA ESTO
use Illuminate\Database\Eloquent\Model;

class TourExtra extends Model
{
    use HasFactory; // ✅ AGREGA ESTO

    protected $fillable = [
        'tour_id', 'name', 'description', 'price'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
