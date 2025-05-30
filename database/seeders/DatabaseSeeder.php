<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Inventario;
use App\Models\Mercaderia;
use App\Models\Almacenamiento;
use App\Models\Despacho;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create test products in inventory
        $productos = [
            [
                'producto' => 'Laptop HP ProBook',
                'descripcion' => 'Laptop HP ProBook 450 G8, Intel Core i5, 8GB RAM, 256GB SSD',
                'cantidad' => 50,
                'cantidad_minima' => 10,
                'unidad_medida' => 'unidad',
                'categoria' => 'Electrónicos',
                'codigo_producto' => 'LAP-HP-001',
                'precio_unitario' => 899.99,
            ],
            [
                'producto' => 'Monitor Dell 24"',
                'descripcion' => 'Monitor Dell P2419H 24" Full HD',
                'cantidad' => 30,
                'cantidad_minima' => 5,
                'unidad_medida' => 'unidad',
                'categoria' => 'Electrónicos',
                'codigo_producto' => 'MON-DEL-001',
                'precio_unitario' => 199.99,
            ],
            [
                'producto' => 'Teclado Logitech',
                'descripcion' => 'Teclado Mecánico Logitech G Pro',
                'cantidad' => 100,
                'cantidad_minima' => 20,
                'unidad_medida' => 'unidad',
                'categoria' => 'Periféricos',
                'codigo_producto' => 'TEC-LOG-001',
                'precio_unitario' => 129.99,
            ],
        ];

        foreach ($productos as $producto) {
            $inv = Inventario::create($producto);

            // Create storage location for each product
            Almacenamiento::create([
                'ubicacion' => 'RACK-A' . $inv->id,
                'producto_id' => $inv->id,
                'cantidad' => $producto['cantidad'],
                'capacidad_maxima' => $producto['cantidad'] * 2,
                'tipo_almacenamiento' => 'Standard',
                'condiciones_especiales' => 'Temperatura ambiente',
                'usuario_id' => 1,
            ]);

            // Create sample incoming goods record
            Mercaderia::create([
                'producto' => $producto['producto'],
                'cantidad' => $producto['cantidad'],
                'proveedor' => 'Proveedor Principal',
                'fecha_ingreso' => now(),
                'numero_guia' => 'GR-' . str_pad($inv->id, 6, '0', STR_PAD_LEFT),
                'observaciones' => 'Ingreso inicial',
                'usuario_id' => 1,
            ]);

            // Create sample dispatch
            Despacho::create([
                'producto_id' => $inv->id,
                'cantidad' => 5,
                'cliente' => 'Cliente Test',
                'fecha_programada' => now()->addDays(rand(1, 7)),
                'direccion_entrega' => 'Av. Principal 123',
                'numero_orden' => 'OD-' . str_pad($inv->id, 6, '0', STR_PAD_LEFT),
                'estado' => 'pendiente',
                'prioridad' => 'media',
                'usuario_id' => 1,
            ]);
        }
    }
}
