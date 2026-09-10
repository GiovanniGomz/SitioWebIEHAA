<?php

namespace Iehaa\Publicaciones\Models;

use Winter\Storm\Database\Model;

/**
 * Publicacion Model
 */
class Publicacion extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'data.publicaciones';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $timestamps = true;

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['id', 'titulo', 'descripcion', 'archivo', 'url', 'fecha', 'tipo_publicacion_id', 'investigador_id'];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'titulo' => 'required|min:3',
        'descripcion' => 'required',
        'tipo_publicacion_id' => 'required',
        'investigador_id' => 'required',
    ];

    protected $casts = [];
    protected $jsonable = [];
    protected $appends = [];
    protected $hidden = [];

    protected $dates = [
        'fecha',
        'created_at',
        'updated_at',
    ];

    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'investigador' => [
            'Iehaa\Investigadores\Models\Investigador',
            'key' => 'investigador_id'
        ],
        'tipo_publicacion' => [
            'Iehaa\Tipopublicaciones\Models\TipoPublicaciones',
            'key' => 'tipo_publicacion_id'
        ],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
