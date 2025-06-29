<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tour extends Model
{
    use HasFactory;

    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'entrepreneur_id',
        'active',   // Nuevo campo para activar/desactivar el tour
    ];

    // Relación con el emprendedor (dueño del tour)
    public function entrepreneur()
    {
        return $this->belongsTo(Entrepreneur::class);
    }

    // Relación con reservas de este tour
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }
}
