<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ElectronicReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'emprendedor_id',
        'cliente_id',
        'serie',
        'numero',
        'monto_total',
        'pdf_url',
        'xml_url',
        'estado_sunat',
        'fecha_emision',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function emprendedor()
    {
        return $this->belongsTo(Entrepreneur::class, 'emprendedor_id');
    }

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }
}

