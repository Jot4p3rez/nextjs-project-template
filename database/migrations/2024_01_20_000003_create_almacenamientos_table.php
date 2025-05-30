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
        Schema::create('almacenamientos', function (Blueprint $table) {
            $table->id();
            $table->string('ubicacion');
            $table->foreignId('producto_id')->constrained('inventarios');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('capacidad_maxima', 10, 2);
            $table->string('tipo_almacenamiento');
            $table->text('condiciones_especiales')->nullable();
            $table->string('estado')->default('activo');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint to prevent duplicate locations for the same product
            $table->unique(['ubicacion', 'producto_id']);

            // Indexes for better performance
            $table->index('ubicacion');
            $table->index('tipo_almacenamiento');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('almacenamientos');
    }
};
