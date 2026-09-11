<?php

namespace Iehaa\Solicitudes\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Solicitudes\Models\Solicitud;
use Iehaa\Usuarios\Classes\CpanelAuth;

class SolicitudesComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'solicitudesComponent',
            'description' => 'Panel de solicitudes de préstamo (CEDJAG)'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (!CpanelAuth::check()) {
            return redirect('/login');
        }

        // Esta pantalla fue reemplazada por el módulo de Correspondencia
        // (aceptar/rechazar + historial detallado + envío de correo).
        return redirect('/correspondencia');
    }

    public function obtenerTodas()
    {
        return Solicitud::orderByRaw("CASE WHEN estado = 'pendiente' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->get()
            ->map(function ($solicitud) {
                return [
                    'id' => $solicitud->id,
                    'tipo' => $solicitud->tipo,
                    'coleccion' => $solicitud->tipo === 'fabio' ? 'Fabio Castillo' : 'Fondo Bibliográfico',
                    'documento' => $solicitud->documento()?->nombre ?? 'Documento eliminado',
                    'ubicacion' => $solicitud->ubicacion(),
                    'nombre_solicitante' => $solicitud->nombre_solicitante,
                    'email_solicitante' => $solicitud->email_solicitante,
                    'telefono_solicitante' => $solicitud->telefono_solicitante,
                    'mensaje' => $solicitud->mensaje,
                    'estado' => $solicitud->estado,
                    'fecha' => $solicitud->created_at?->format('d/m/Y H:i'),
                ];
            });
    }

    public function onAprobar()
    {
        $this->cambiarEstado(post('id'), 'aprobado');

        return $this->respuesta();
    }

    public function onRechazar()
    {
        $this->cambiarEstado(post('id'), 'rechazado');

        return $this->respuesta();
    }

    private function cambiarEstado($id, $estado)
    {
        $solicitud = Solicitud::find(intval($id));

        if ($solicitud) {
            $solicitud->estado = $estado;
            $solicitud->save();
        }
    }

    private function respuesta()
    {
        return [
            '#listado-solicitudes' => $this->renderPartial('@listado', [
                'solicitudes' => $this->obtenerTodas(),
            ]),
            'estado' => 'exito',
            'mensaje' => '¡Actualizado correctamente!',
        ];
    }

    /**
     * Cantidad de solicitudes pendientes, usado por la campanita del sidebar.
     */
    public static function pendientes(): int
    {
        return Solicitud::where('estado', 'pendiente')->count();
    }

    /**
     * Últimas solicitudes pendientes, para el dropdown de notificaciones.
     */
    public static function ultimasPendientes(int $limite = 5)
    {
        return Solicitud::where('estado', 'pendiente')->orderByDesc('id')->limit($limite)->get();
    }
}
