<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('reservation_tour_extra', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('reservation_id');
        $table->unsignedBigInteger('tour_extra_id');
        $table->timestamps();

        $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
        $table->foreign('tour_extra_id')->references('id')->on('tour_extras')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_tour_extra');
    }
};
