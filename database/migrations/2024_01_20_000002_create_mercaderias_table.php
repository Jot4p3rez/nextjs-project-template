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
        Schema::create('mercaderias', function (Blueprint $table) {
            $table->id();
            $table->string('producto');
            $table->decimal('cantidad', 10, 2);
            $table->string('proveedor');
            $table->datetime('fecha_ingreso');
            $table->string('numero_guia')->unique();
            $table->text('observaciones')->nullable();
            $table->string('estado')->default('recibido');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Foreign key to inventarios
            $table->foreign('producto')
                  ->references('producto')
                  ->on('inventarios')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            // Indexes for better performance
            $table->index('fecha_ingreso');
            $table->index('proveedor');
            $table->index('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercaderias');
    }
};
