<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mercaderia extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'producto',
        'cantidad',
        'proveedor',
        'fecha_ingreso',
        'numero_guia',
        'observaciones',
        'estado',
        'usuario_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_ingreso' => 'datetime',
        'cantidad' => 'decimal:2',
    ];

    /**
     * Get the user that created the mercaderia.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Get the inventory record associated with the mercaderia.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'producto', 'producto');
    }

    /**
     * Scope a query to only include mercaderia from a specific date range.
     */
    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('fecha_ingreso', [$start, $end]);
    }

    /**
     * Scope a query to only include mercaderia from a specific provider.
     */
    public function scopeProveedor($query, $proveedor)
    {
        return $query->where('proveedor', $proveedor);
    }

    /**
     * Get the total value of the mercaderia.
     */
    public function getValorTotalAttribute()
    {
        return $this->cantidad * ($this->inventario->precio_unitario ?? 0);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($mercaderia) {
            if (auth()->check()) {
                $mercaderia->usuario_id = auth()->id();
            }
        });
    }
}
