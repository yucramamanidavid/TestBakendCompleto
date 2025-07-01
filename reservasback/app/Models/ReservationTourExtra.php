<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ReservationTourExtra extends Model
{
    use HasFactory;
    protected $fillable = [
        'reservation_id', 'tour_extra_id'
    ];

    public function extra()
    {
        return $this->belongsTo(TourExtra::class, 'tour_extra_id');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

}
