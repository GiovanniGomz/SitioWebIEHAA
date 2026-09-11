<?php

namespace Iehaa\Mensajescontacto\Models;

use Winter\Storm\Database\Model;

class MensajeContacto extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.mensajes_contacto';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = ['id', 'nombre', 'email', 'asunto', 'mensaje', 'leido'];

    public $rules = [];

    protected $casts = ['leido' => 'boolean'];
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
}
