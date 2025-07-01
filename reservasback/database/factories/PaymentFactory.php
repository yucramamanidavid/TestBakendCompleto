<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'reservation_id' => Reservation::factory(),
            'payment_method' => 'efectivo',
            'status' => 'enviado',
            'is_confirmed' => false,
            'operation_code' => $this->faker->uuid,
            'confirmation_by' => null,
            'confirmation_time' => null,
            'confirmed_at' => null,
            'note' => $this->faker->sentence,
            'image_url' => null,
        ];
    }

}
