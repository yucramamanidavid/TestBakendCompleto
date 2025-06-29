<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourExtra extends Model
{
    protected $fillable = [
        'tour_id', 'name', 'description', 'price'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
