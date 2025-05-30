<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'producto',
        'descripcion',
        'cantidad',
        'cantidad_minima',
        'unidad_medida',
        'categoria',
        'codigo_producto',
        'precio_unitario',
        'ubicacion_principal',
        'estado',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'decimal:2',
        'cantidad_minima' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
    ];

    /**
     * Get the mercaderias (incoming goods) for this inventory item.
     */
    public function mercaderias(): HasMany
    {
        return $this->hasMany(Mercaderia::class, 'producto', 'producto');
    }

    /**
     * Get the storage locations for this inventory item.
     */
    public function almacenamientos(): HasMany
    {
        return $this->hasMany(Almacenamiento::class, 'producto_id');
    }

    /**
     * Get the dispatches for this inventory item.
     */
    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class, 'producto_id');
    }

    /**
     * Check if the inventory level is low.
     */
    public function getNivelBajoAttribute(): bool
    {
        return $this->cantidad <= $this->cantidad_minima;
    }

    /**
     * Get the total value of inventory.
     */
    public function getValorTotalAttribute(): float
    {
        return $this->cantidad * ($this->precio_unitario ?? 0);
    }

    /**
     * Get the stock status.
     */
    public function getEstadoStockAttribute(): string
    {
        if ($this->cantidad <= 0) {
            return 'Sin Stock';
        } elseif ($this->cantidad <= $this->cantidad_minima) {
            return 'Stock Bajo';
        } elseif ($this->cantidad <= ($this->cantidad_minima * 2)) {
            return 'Stock Moderado';
        } else {
            return 'Stock Óptimo';
        }
    }

    /**
     * Scope a query to only include products with low stock.
     */
    public function scopeStockBajo($query)
    {
        return $query->whereRaw('cantidad <= cantidad_minima');
    }

    /**
     * Scope a query to only include products of a specific category.
     */
    public function scopeCategoria($query, $categoria)
    {
        return $query->where('categoria', $categoria);
    }

    /**
     * Get the movement history for this inventory item.
     */
    public function getMovimientosAttribute()
    {
        $movimientos = collect();

        // Add incoming goods
        $this->mercaderias->each(function ($mercaderia) use ($movimientos) {
            $movimientos->push([
                'fecha' => $mercaderia->fecha_ingreso,
                'tipo' => 'Ingreso',
                'cantidad' => $mercaderia->cantidad,
                'referencia' => $mercaderia->numero_guia,
            ]);
        });

        // Add dispatches
        $this->despachos->each(function ($despacho) use ($movimientos) {
            $movimientos->push([
                'fecha' => $despacho->fecha_salida,
                'tipo' => 'Salida',
                'cantidad' => -$despacho->cantidad,
                'referencia' => $despacho->numero_orden,
            ]);
        });

        return $movimientos->sortByDesc('fecha');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($inventario) {
            if ($inventario->cantidad < 0) {
                throw new \Exception('La cantidad en inventario no puede ser negativa.');
            }
        });
    }
}
