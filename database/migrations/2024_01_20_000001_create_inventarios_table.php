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
        Schema::create('inventarios', function (Blueprint $table) {
            $table->id();
            $table->string('producto')->unique();
            $table->text('descripcion');
            $table->decimal('cantidad', 10, 2)->default(0);
            $table->decimal('cantidad_minima', 10, 2)->default(0);
            $table->string('unidad_medida');
            $table->string('categoria');
            $table->string('codigo_producto')->unique();
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->string('ubicacion_principal')->nullable();
            $table->string('estado')->default('activo');
            $table->timestamps();

            // Indexes for better performance
            $table->index('categoria');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
