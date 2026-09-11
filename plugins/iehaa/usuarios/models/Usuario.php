<?php

namespace Iehaa\Usuarios\Models;

use Winter\Storm\Database\Model;

/**
 * Usuario Model
 */
class Usuario extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Hashable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'data.usuarios';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $timestamps = true;

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['id', 'nombre', 'email', 'password', 'rol', 'activo', 'foto'];

    /**
     * @var array Attribute names which should be hashed using Bcrypt.
     */
    protected $hashable = ['password'];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [
        'nombre' => 'required|min:3',
        'email' => 'required|email',
        'rol' => 'required|in:admin,editor',
    ];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [
        'activo' => 'boolean',
    ];

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
    protected $hidden = ['password', 'reset_token', 'reset_token_expira'];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'reset_token_expira',
    ];

    /**
     * @var array Relations
     */
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

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Ruta pública (relativa) de la foto de perfil, o null si el usuario
     * todavía no subió ninguna (el panel muestra un ícono por defecto).
     */
    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto
            ? 'storage/app/uploads/public/perfiles/' . $this->foto
            : null;
    }
}
