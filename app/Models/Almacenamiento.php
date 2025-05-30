<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Almacenamiento extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ubicacion',
        'producto_id',
        'cantidad',
        'capacidad_maxima',
        'tipo_almacenamiento',
        'condiciones_especiales',
        'estado',
        'usuario_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad' => 'decimal:2',
        'capacidad_maxima' => 'decimal:2',
    ];

    /**
     * Get the inventory record associated with the storage.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'producto_id');
    }

    /**
     * Get the user that manages this storage.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Get the percentage of space used.
     */
    public function getPorcentajeOcupadoAttribute(): float
    {
        if ($this->capacidad_maxima > 0) {
            return ($this->cantidad / $this->capacidad_maxima) * 100;
        }
        return 0;
    }

    /**
     * Get the available space.
     */
    public function getEspacioDisponibleAttribute(): float
    {
        return max(0, $this->capacidad_maxima - $this->cantidad);
    }

    /**
     * Check if the storage location is full.
     */
    public function getEstaLlenoAttribute(): bool
    {
        return $this->cantidad >= $this->capacidad_maxima;
    }

    /**
     * Scope a query to only include storage locations that are not full.
     */
    public function scopeDisponible($query)
    {
        return $query->whereRaw('cantidad < capacidad_maxima');
    }

    /**
     * Scope a query to only include storage locations of a specific type.
     */
    public function scopeTipoAlmacenamiento($query, $tipo)
    {
        return $query->where('tipo_almacenamiento', $tipo);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($almacenamiento) {
            if (auth()->check()) {
                $almacenamiento->usuario_id = auth()->id();
            }
        });

        static::saving(function ($almacenamiento) {
            if ($almacenamiento->cantidad > $almacenamiento->capacidad_maxima) {
                throw new \Exception('La cantidad excede la capacidad máxima de almacenamiento.');
            }
        });
    }
}
