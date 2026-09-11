<?php

namespace Iehaa\Categoriacorrespondencia\Models;

use Winter\Storm\Database\Model;

class CategoriaCorrespondencia extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.categorias_correspondencia';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = ['id', 'nombre'];

    public $rules = [];

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
}
