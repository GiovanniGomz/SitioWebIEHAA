<?php

namespace Iehaa\Inventario\Models;

use Winter\Storm\Database\Model;

/**
 * ActivoFijo — un bien del inventario de activo fijo del instituto.
 */
class ActivoFijo extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.activo_fijo';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = [
        'id', 'numero_inventario', 'descripcion_bien', 'marca', 'modelo', 'serie',
        'responsable', 'estado_bien', 'estado', 'descripcion', 'observacion',
        'forma_adquisicion', 'fecha_adquisicion', 'precio',
    ];

    public $rules = [];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    protected $jsonable = [];
    protected $appends = [];
    protected $hidden = [];

    protected $dates = ['fecha_adquisicion', 'created_at', 'updated_at'];

    public $hasOne = [];

    public $hasMany = [
        'archivos' => [
            \Iehaa\Inventario\Models\ActivoFijoArchivo::class,
            'key' => 'activo_fijo_id',
            'delete' => true,
        ],
    ];

    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public const ESTADOS_BIEN = ['Bueno', 'Regular', 'Malo'];

    public const ESTADOS = ['Activo', 'Descargado', 'En reparación', 'Trasladado', 'De baja'];

    public const FORMAS_ADQUISICION = ['Compra', 'Donación', 'Otro'];

    public function getPrecioFormateadoAttribute(): string
    {
        return $this->precio !== null ? '$' . number_format((float) $this->precio, 2) : '—';
    }

    /**
     * JSON de los archivos adjuntos [{id, nombre}], para pintar el modal
     * de "ver archivos" en el listado sin depender de filtros Twig raros.
     */
    public function getArchivosJsonAttribute(): string
    {
        return $this->archivos->map(fn ($a) => [
            'id' => $a->id,
            'nombre' => $a->nombre_original ?: $a->archivo,
        ])->values()->toJson();
    }
}
