<?php

namespace Iehaa\Gestiondocumental\Models;

use Winter\Storm\Database\Model;

/**
 * Expediente — una unidad documental del inventario de gestión documental.
 */
class Expediente extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.expedientes';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = [
        'id', 'serie', 'subserie', 'asunto',
        'fecha_inicial', 'fecha_final',
        'unidad_instalacion', 'unidad_instalacion_otro',
        'cantidad_folios', 'volumen', 'soporte',
        'formato', 'archivo',
        'estado_conservacion',
        'sig_fila', 'sig_estante', 'sig_anaquel', 'sig_posicion',
    ];

    public $rules = [];

    protected $casts = [
        'cantidad_folios' => 'integer',
    ];

    protected $jsonable = [];
    protected $appends = [];
    protected $hidden = [];

    protected $dates = ['fecha_inicial', 'fecha_final', 'created_at', 'updated_at'];

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

    public const UNIDADES = [
        'Caja',
        'Folder manila o kraft',
        'Folder de palanca',
        'Otro',
    ];

    public const FORMATOS = ['impreso', 'digital'];

    /**
     * Texto legible de la unidad de instalación (usa el campo "otro" si aplica).
     */
    public function getUnidadTextoAttribute(): string
    {
        if ($this->unidad_instalacion === 'Otro' && $this->unidad_instalacion_otro) {
            return $this->unidad_instalacion_otro;
        }

        return (string) $this->unidad_instalacion;
    }

    /**
     * Rango de fechas extremas legible.
     */
    public function getFechasExtremasAttribute(): string
    {
        $ini = $this->fecha_inicial ? $this->fecha_inicial->format('d/m/Y') : null;
        $fin = $this->fecha_final ? $this->fecha_final->format('d/m/Y') : null;

        if ($ini && $fin) {
            return $ini . ' — ' . $fin;
        }

        return $ini ?: ($fin ?: '—');
    }

    /**
     * Signatura topográfica en una sola línea.
     */
    public function getSignaturaAttribute(): string
    {
        $partes = array_filter([
            $this->sig_fila ? 'Fila ' . $this->sig_fila : null,
            $this->sig_estante ? 'Estante ' . $this->sig_estante : null,
            $this->sig_anaquel ? 'Anaquel ' . $this->sig_anaquel : null,
            $this->sig_posicion ? 'Pos. ' . $this->sig_posicion : null,
        ]);

        return $partes ? implode(' · ', $partes) : '—';
    }
}
