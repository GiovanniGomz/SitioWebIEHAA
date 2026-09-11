<?php

namespace Iehaa\Inventario\Models;

use Winter\Storm\Database\Model;

/**
 * ActivoFijoArchivo — uno de los hasta 5 archivos adjuntos de un bien.
 */
class ActivoFijoArchivo extends Model
{
    public $table = 'data.activo_fijo_archivos';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = ['id', 'activo_fijo_id', 'archivo', 'nombre_original'];

    protected $dates = ['created_at', 'updated_at'];

    public $belongsTo = [
        'activoFijo' => [
            \Iehaa\Inventario\Models\ActivoFijo::class,
            'key' => 'activo_fijo_id',
        ],
    ];

    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
