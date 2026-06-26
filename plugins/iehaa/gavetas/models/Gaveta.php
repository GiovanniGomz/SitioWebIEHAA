<?php

namespace Iehaa\Gavetas\Models;

use Winter\Storm\Database\Model;

/**
 * Gaveta Model
 */
class Gaveta extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'data.gavetas';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $timestamps = false;

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['id', 'codigo', 'url', 'archivero_id'];

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
        'Archivero' => [
            'IEHAA\Archiveros\Models\Archivero',
            'key' => 'archivero_id'
        ]
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];
}
