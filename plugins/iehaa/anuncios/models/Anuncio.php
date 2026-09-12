<?php

namespace Iehaa\Anuncios\Models;

use Winter\Storm\Database\Model;

class Anuncio extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.anuncios';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = ['id', 'titulo', 'texto', 'imagen', 'video_url', 'orden', 'activo'];

    public $rules = [];

    protected $casts = ['activo' => 'boolean'];
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
     * Ruta pública (relativa) de la imagen subida, o null si el anuncio no
     * tiene ninguna.
     */
    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen
            ? 'storage/app/uploads/public/anuncios/' . $this->imagen
            : null;
    }
}
