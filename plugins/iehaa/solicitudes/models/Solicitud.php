<?php

namespace Iehaa\Solicitudes\Models;

use Iehaa\Fabio\Models\Fabio;
use Iehaa\Fondo\Models\Fondo;
use Winter\Storm\Database\Model;

/**
 * Solicitud Model — solicitudes públicas de préstamo de documentos
 * físicos de Fabio Castillo o Fondo Bibliográfico.
 */
class Solicitud extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    public $table = 'data.solicitudes';

    protected $guarded = ['*'];

    public $timestamps = true;

    protected $fillable = [
        'id', 'tipo', 'documento_id', 'nombre_solicitante', 'email_solicitante',
        'telefono_solicitante', 'mensaje', 'estado', 'admin_notas',
    ];

    public $rules = [
        'tipo' => 'required|in:fabio,fondo',
        'documento_id' => 'required',
        'nombre_solicitante' => 'required|min:3',
        'email_solicitante' => 'required|email',
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
     * Devuelve el documento (Fabio o Fondo) al que apunta la solicitud.
     */
    public function documento()
    {
        return $this->tipo === 'fabio'
            ? Fabio::find($this->documento_id)
            : Fondo::find($this->documento_id);
    }

    /**
     * Arma la ubicación física legible subiendo la cadena de relaciones,
     * ej. "Archivero 3 > Gaveta 2 > Carpeta Actas > Folder 2024".
     */
    public function ubicacion(): string
    {
        $documento = $this->documento();

        if (!$documento) {
            return 'Documento no encontrado';
        }

        $partes = [];

        if ($this->tipo === 'fabio') {
            $folder = $documento->Folder;
            $carpeta = $folder?->Carpeta;
            $gaveta = $carpeta?->Gaveta;
            $archivero = $gaveta?->Archivero;

            if ($archivero) $partes[] = 'Archivero ' . $archivero->codigo;
            if ($gaveta) $partes[] = 'Gaveta ' . $gaveta->codigo;
            if ($carpeta) $partes[] = 'Carpeta ' . $carpeta->nombre;
            if ($folder) $partes[] = 'Folder ' . $folder->nombre;
        } else {
            $coleccion = $documento->Coleccion;
            $anaquel = $coleccion?->Anaquel;
            $estante = $anaquel?->Estante;

            if ($estante) $partes[] = 'Estante ' . $estante->codigo;
            if ($anaquel) $partes[] = 'Anaquel ' . $anaquel->codigo;
            if ($coleccion) $partes[] = 'Colección ' . $coleccion->nombre;
        }

        return $partes ? implode(' › ', $partes) : 'Ubicación no disponible';
    }
}
