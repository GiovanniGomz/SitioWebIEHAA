<?php

namespace Iehaa\Solicitudes\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Fabio\Models\Fabio;
use Iehaa\Fondo\Models\Fondo;
use Iehaa\Solicitudes\Models\Solicitud;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

/**
 * Catálogo público de CEDJAG: permite navegar los documentos de
 * Fabio Castillo y Fondo Bibliográfico (con su ubicación física) y
 * enviar una solicitud de préstamo sin necesidad de iniciar sesión.
 */
class CedjagComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'cedjagComponent',
            'description' => 'Catálogo público CEDJAG y solicitudes de préstamo'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['catalogo'] = $this->obtenerCatalogo();
        $this->page['assetVersion'] = @filemtime(base_path('themes/web/assets/js/cedjag/cedjag.js')) ?: time();
    }

    public function obtenerCatalogo()
    {
        $fabio = Fabio::with('Folder.Carpeta.Gaveta.Archivero')->get()->map(function ($doc) {
            return $this->mapear($doc, 'fabio');
        });

        $fondo = Fondo::with('Coleccion.Anaquel.Estante')->get()->map(function ($doc) {
            return $this->mapear($doc, 'fondo');
        });

        return $fabio->concat($fondo)->sortBy('nombre')->values();
    }

    private function mapear($documento, $tipo)
    {
        $solicitud = new Solicitud();
        $solicitud->tipo = $tipo;
        $solicitud->documento_id = $documento->id;

        return [
            'id' => $documento->id,
            'tipo' => $tipo,
            'coleccion' => $tipo === 'fabio' ? 'Fabio Castillo' : 'Fondo Bibliográfico',
            'nombre' => $documento->nombre,
            'ubicacion' => $solicitud->ubicacion(),
            'tieneDigital' => !empty($documento->archivo),
            'urlDigital' => $documento->archivo
                ? 'storage/app/uploads/public/' . $tipo . '/' . $documento->archivo
                : null,
        ];
    }

    public function onEnviarSolicitud()
    {
        $data = Input::all();

        $validator = Validator::make($data, [
            'tipo' => 'required|in:fabio,fondo',
            'documento_id' => 'required',
            'nombre_solicitante' => 'required|min:3',
            'email_solicitante' => 'required|email',
            'telefono_solicitante' => 'nullable',
        ], [
            'tipo.required' => 'Documento inválido.',
            'documento_id.required' => 'Documento inválido.',
            'nombre_solicitante.required' => '* Campo obligatorio.',
            'nombre_solicitante.min' => 'Mínimo 3 caracteres.',
            'email_solicitante.required' => '* Campo obligatorio.',
            'email_solicitante.email' => 'Correo inválido.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $modelo = $data['tipo'] === 'fabio' ? Fabio::class : Fondo::class;

        if (!$modelo::find($data['documento_id'])) {
            throw new ValidationException(['documento_id' => 'El documento seleccionado ya no existe.']);
        }

        $solicitud = new Solicitud();
        $solicitud->tipo = $data['tipo'];
        $solicitud->documento_id = $data['documento_id'];
        $solicitud->nombre_solicitante = $data['nombre_solicitante'];
        $solicitud->email_solicitante = $data['email_solicitante'];
        $solicitud->telefono_solicitante = $data['telefono_solicitante'] ?? '';
        $solicitud->mensaje = $data['mensaje'] ?? '';
        $solicitud->estado = 'pendiente';
        $solicitud->save();

        return [
            'estado' => 'exito',
            'mensaje' => '¡Solicitud enviada! El instituto se pondrá en contacto con usted pronto.',
        ];
    }
}
