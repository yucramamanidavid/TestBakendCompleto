<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourDate extends Model
{
    protected $fillable = [
        'tour_id', 'available_date', 'available_time', 'seats', 'reserved'
    ];

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }
}
