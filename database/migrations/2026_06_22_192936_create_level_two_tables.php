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
        Schema::create('insumo_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
        });

        Schema::create('imagenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->string('URL');
            $table->timestamps();
        });

        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->string('descripcion');
            $table->string('estado');
            $table->timestamps();
        });

        Schema::create('detalle_cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
            $table->foreignId('carpintero_id')->constrained('carpinteros')->onDelete('cascade');
            $table->decimal('precio', 10, 2);
            $table->string('descripcion');
            $table->timestamps();
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->onDelete('cascade');
            $table->foreignId('cotizacion_id')->nullable()->constrained('cotizaciones')->onDelete('cascade');
            $table->string('codigo');
            $table->decimal('precio', 10, 2);
            $table->date('fecha_entrega');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('detalle_cotizaciones');
        Schema::dropIfExists('cotizaciones');
        Schema::dropIfExists('imagenes');
        Schema::dropIfExists('insumo_producto');
    }
};
