<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        $tables = [
            'abouts', 'additional_services', 'associations', 'categories', 'contacts',
            'custom_packages', 'custom_package_items', 'electronic_receipts', 'entrepreneurs',
            'experiences', 'failed_jobs', 'galleries', 'homepage_settings', 'migrations',
            'opening_hours', 'packages', 'package_images', 'package_product', 'payments',
            'permissions', 'personal_access_tokens', 'places', 'products', 'product_category',
            'product_images', 'reservations', 'roles', 'testimonios', 'tours', 'users'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                if (Schema::hasColumn($table, 'id')) {
                    $tableBlueprint->id()->change(); // Cambia a AUTO_INCREMENT
                }
            });
        }
    }

    public function down(): void {
        // No implementamos rollback porque revertir auto_increment requiere cuidado especial
    }
};
