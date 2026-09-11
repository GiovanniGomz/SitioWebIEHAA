<?php

namespace Iehaa\Correspondencia\Models;

use Winter\Storm\Database\Model;

/**
 * Correspondencia — el registro detallado de un préstamo ya aceptado
 * (viene de una Solicitud pública aprobada por el administrador).
 */
class Correspondencia extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.correspondencia';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = [
        'id', 'solicitud_id', 'categoria_correspondencia_id', 'remitente',
        'descripcion_documento', 'fecha_atencion', 'enviado_email', 'detalle_envio',
    ];

    public $rules = [];

    protected $casts = [
        'enviado_email' => 'boolean',
    ];

    protected $jsonable = [];
    protected $appends = [];
    protected $hidden = [];

    protected $dates = ['fecha_atencion', 'created_at', 'updated_at'];

    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];

    public $belongsTo = [
        'solicitud' => [
            \Iehaa\Solicitudes\Models\Solicitud::class,
            'key' => 'solicitud_id',
        ],
        'categoria' => [
            \Iehaa\Categoriacorrespondencia\Models\CategoriaCorrespondencia::class,
            'key' => 'categoria_correspondencia_id',
        ],
    ];

    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
