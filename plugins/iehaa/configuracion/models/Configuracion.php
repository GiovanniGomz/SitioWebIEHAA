<?php

namespace Iehaa\Configuracion\Models;

use Winter\Storm\Database\Model;

/**
 * Configuracion Model — fila única con los datos generales del sitio.
 */
class Configuracion extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.configuracion';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = [
        'id', 'nombre_sitio', 'descripcion_sitio', 'email_contacto',
        'telefono_contacto', 'direccion', 'logo',
        'texto_nosotros', 'titulo_patrimonio', 'texto_patrimonio', 'video_url',
        'mapa_embed', 'facebook_url', 'instagram_url',
    ];

    public $rules = [
        'nombre_sitio' => 'required|min:3',
        'email_contacto' => 'nullable|email',
    ];

    protected $casts = [];
    protected $jsonable = [];
    protected $appends = [];
    protected $hidden = [];

    protected $dates = ['created_at', 'updated_at'];

    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    /**
     * Devuelve la única fila de configuración (la crea con valores por
     * defecto si todavía no existe).
     */
    public static function actual(): self
    {
        return static::query()->first() ?? static::create([
            'nombre_sitio' => 'IEHAA',
            'descripcion_sitio' => 'Instituto de Estudios Históricos, Antropológicos y Arqueológicos',
            'email_contacto' => 'contacto@iehaa.com',
            'telefono_contacto' => '',
            'direccion' => '',
        ]);
    }
}
