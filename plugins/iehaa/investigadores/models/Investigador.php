<?php

namespace Iehaa\Investigadores\Models;

use Winter\Storm\Database\Model;

/**
 * Investigador Model
 */
class Investigador extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'data.investigadores';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $timestamps = false;

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['id', 'nombre', 'apellido', 'carnet', 'email', 'telefono', 'facultad', 'grado', 'facultad_id', 'tipo_investigador_id', 'categoria_investigador_id', 'sexo', 'publicaciones', 'descripcion'];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'facultad' => [
            'IEHAA\Facultades\Models\Facultad',
            'key' => 'facultad_id'
        ],
        'tipo_investigador' => [
            'IEHAA\Tipoinvestigadores\Models\TipoInvestigador',
            'key' => 'tipo_investigador_id'
        ],
        'categoria_investigador' => [
            'IEHAA\Categoriainvestigadores\Models\CategoriaInvestigador',
            'key' => 'categoria_investigador_id'
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
