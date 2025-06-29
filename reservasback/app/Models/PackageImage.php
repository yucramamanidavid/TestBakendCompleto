<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackageImage extends Model
{
    use HasFactory;

    protected $fillable = ['package_id', 'image_path'];

    /* 🔹  NUEVO  */
    protected $appends = ['image_url'];      //  👈 fuerza a incluir la url
    protected $hidden  = ['image_path'];     //  (opcional) oculta la ruta interna

    /*----------- Relaciones ------------*/
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /*----------- Accessor --------------*/
    public function getImageUrlAttribute(): string
    {
        // http://localhost:8000/storage/package_images/xyz.jpg
        return asset('storage/' . ltrim($this->image_path, '/'));
    }

}
