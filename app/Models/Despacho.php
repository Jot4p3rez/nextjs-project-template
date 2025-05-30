<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Despacho extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'producto_id',
        'cantidad',
        'cliente',
        'fecha_programada',
        'fecha_salida',
        'direccion_entrega',
        'numero_orden',
        'estado',
        'prioridad',
        'observaciones',
        'usuario_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_programada' => 'datetime',
        'fecha_salida' => 'datetime',
        'cantidad' => 'decimal:2',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'estado' => 'pendiente',
        'prioridad' => 'media',
    ];

    /**
     * Get the inventory item associated with the dispatch.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class, 'producto_id');
    }

    /**
     * Get the user that created/manages the dispatch.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Get the status color for display.
     */
    public function getColorEstadoAttribute(): string
    {
        return match($this->estado) {
            'pendiente' => 'yellow',
            'en_proceso' => 'blue',
            'completado' => 'green',
            'cancelado' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the priority color for display.
     */
    public function getColorPrioridadAttribute(): string
    {
        return match($this->prioridad) {
            'baja' => 'green',
            'media' => 'yellow',
            'alta' => 'orange',
            'urgente' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if the dispatch is delayed.
     */
    public function getEstaRetrasadoAttribute(): bool
    {
        return $this->estado !== 'completado' && 
               $this->estado !== 'cancelado' && 
               now()->gt($this->fecha_programada);
    }

    /**
     * Scope a query to only include pending dispatches.
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    /**
     * Scope a query to only include dispatches for today.
     */
    public function scopeHoy($query)
    {
        return $query->whereDate('fecha_programada', today());
    }

    /**
     * Scope a query to only include delayed dispatches.
     */
    public function scopeRetrasados($query)
    {
        return $query->where('estado', '!=', 'completado')
                    ->where('estado', '!=', 'cancelado')
                    ->where('fecha_programada', '<', now());
    }

    /**
     * Scope a query to only include dispatches by priority.
     */
    public function scopePrioridad($query, $prioridad)
    {
        return $query->where('prioridad', $prioridad);
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($despacho) {
            if (auth()->check()) {
                $despacho->usuario_id = auth()->id();
            }
        });

        static::saving(function ($despacho) {
            if ($despacho->isDirty('estado') && $despacho->estado === 'completado') {
                $despacho->fecha_salida = now();
            }
        });
    }
}
