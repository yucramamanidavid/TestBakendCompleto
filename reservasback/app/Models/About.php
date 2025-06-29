<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// app/Models/About.php

class About extends Model

{
    use HasFactory;
    protected $fillable = ['title','content','image','active'];

    protected $casts = [
      'active' => 'boolean',
    ];
public function getImageUrlAttribute()
{
    if (!$this->image) return null;
    return asset('storage/' . $this->image);
}

}

