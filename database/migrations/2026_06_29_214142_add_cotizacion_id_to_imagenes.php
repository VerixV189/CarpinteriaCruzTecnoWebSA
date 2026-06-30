<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('imagenes', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable()->change();
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('imagenes', function (Blueprint $table) {
            $table->foreignId('producto_id')->nullable(false)->change();
            $table->dropForeign(['cotizacion_id']);
            $table->dropColumn('cotizacion_id');
        });
    }
};
