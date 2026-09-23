<?php

namespace Iehaa\Registrocorrespondencia\Models;

use Winter\Storm\Database\Model;

/**
 * Un documento enviado o recibido por el instituto.
 */
class RegistroCorrespondencia extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.registro_correspondencia';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = ['id', 'nombre', 'facultad_id', 'tipo', 'fecha', 'archivo', 'categoria_correspondencia_id'];

    /** Tipos de movimiento válidos. */
    const TIPOS = ['enviado', 'recibido'];

    public $rules = [];

    protected $casts = [];
    protected $jsonable = [];
    protected $appends = [];
    protected $hidden = [];

    protected $dates = ['fecha', 'created_at', 'updated_at'];

    public $hasOne = [];
    public $hasMany = [];
    public $hasOneThrough = [];
    public $hasManyThrough = [];
    public $belongsTo = [
        'facultad' => [
            'Iehaa\Facultades\Models\Facultad',
            'key' => 'facultad_id',
        ],
        'categoria' => [
            'Iehaa\Categoriacorrespondencia\Models\CategoriaCorrespondencia',
            'key' => 'categoria_correspondencia_id',
        ],
    ];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function getTipoEtiquetaAttribute(): string
    {
        return $this->tipo === 'recibido' ? 'Recibido' : 'Enviado';
    }

    public function getArchivoUrlAttribute(): ?string
    {
        return $this->archivo
            ? 'storage/app/uploads/public/registro-correspondencia/' . $this->archivo
            : null;
    }
}
