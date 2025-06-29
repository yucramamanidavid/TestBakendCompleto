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
    Schema::table('tours', function (Blueprint $table) {
        $table->unsignedBigInteger('entrepreneur_id')->nullable()->after('id');
        $table->foreign('entrepreneur_id')->references('id')->on('entrepreneurs')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
public function down(): void
{
    Schema::table('tours', function (Blueprint $table) {
        $table->dropForeign(['entrepreneur_id']);
        $table->dropColumn('entrepreneur_id');
    });
}

};
