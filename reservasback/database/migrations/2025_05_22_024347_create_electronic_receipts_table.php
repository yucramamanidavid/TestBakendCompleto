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
        Schema::create('electronic_receipts', function (Blueprint $table) {
            $table->id();

            // Relaciones
            $table->foreignId('reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('emprendedor_id')->constrained('entrepreneurs')->onDelete('cascade');
            $table->foreignId('cliente_id')->constrained('users')->onDelete('cascade');

            // Datos de boleta
            $table->string('serie', 10)->default('B001');
            $table->string('numero', 20);
            $table->decimal('monto_total', 10, 2);

            // Archivos generados
            $table->string('pdf_url')->nullable();
            $table->string('xml_url')->nullable();

            // Estado SUNAT
            $table->enum('estado_sunat', ['pendiente', 'enviado', 'aceptado', 'rechazado'])->default('pendiente');

            // Timestamps
            $table->timestamp('fecha_emision')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electronic_receipts');
    }
};
