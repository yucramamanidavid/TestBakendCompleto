<?php

namespace Database\Factories;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Product;
use App\Models\Package;
use App\Models\CustomPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'product_id' => Product::factory(),
            'package_id' => null,          // Opcional, pon valores o fábricas si usas paquetes
            'custom_package_id' => null,   // Opcional, igual que arriba
            'reservation_code' => $this->faker->unique()->regexify('RES-[A-Z0-9]{10}'),
            'quantity' => $this->faker->numberBetween(1, 5),
            'reservation_date' => $this->faker->date(),
            'total_amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
