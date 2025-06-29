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
    Schema::create('tour_dates', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('tour_id');
        $table->date('available_date');
        $table->time('available_time')->nullable();
        $table->integer('seats');
        $table->integer('reserved')->default(0);
        $table->timestamps();

        $table->foreign('tour_id')->references('id')->on('tours')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_dates');
    }
};
