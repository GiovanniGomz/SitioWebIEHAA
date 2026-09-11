<?php

namespace Iehaa\Correspondencia\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Categoriacorrespondencia\Models\CategoriaCorrespondencia;
use Iehaa\Correspondencia\Models\Correspondencia;
use Iehaa\Reportes\Classes\ReporteModulo;
use Iehaa\Solicitudes\Models\Solicitud;
use Illuminate\Support\Facades\Mail;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class CorrespondenciaComponent extends ComponentBase
{
    use ReporteModulo;

    public function componentDetails()
    {
        return [
            'name'        => 'correspondenciaComponent',
            'description' => 'Correspondencia — aceptar/rechazar solicitudes y llevar el historial de préstamos'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['pendientes'] = $this->obtenerPendientes();
        $this->page['historial'] = $this->obtenerHistorial();
        $this->page['categorias'] = CategoriaCorrespondencia::orderBy('nombre')->get();
    }

    public function obtenerPendientes()
    {
        return Solicitud::where('estado', 'pendiente')
            ->orderByDesc('id')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'tipo' => $s->tipo,
                    'coleccion' => $s->tipo === 'fabio' ? 'Fabio Castillo' : 'Fondo Bibliográfico',
                    'documento' => optional($s->documento())->nombre ?? 'Documento eliminado',
                    'ubicacion' => $s->ubicacion(),
                    'nombre_solicitante' => $s->nombre_solicitante,
                    'email_solicitante' => $s->email_solicitante,
                    'telefono_solicitante' => $s->telefono_solicitante,
                    'mensaje' => $s->mensaje,
                    'fecha' => $s->created_at?->format('d/m/Y H:i'),
                ];
            });
    }

    public function obtenerHistorial()
    {
        return Correspondencia::with(['solicitud', 'categoria'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($c) {
                $s = $c->solicitud;

                return [
                    'id' => $c->id,
                    'solicitud_id' => $c->solicitud_id,
                    'documento' => $s ? (optional($s->documento())->nombre ?? 'Documento eliminado') : '—',
                    'coleccion' => $s ? ($s->tipo === 'fabio' ? 'Fabio Castillo' : 'Fondo Bibliográfico') : '—',
                    'categoria_id' => $c->categoria_correspondencia_id,
                    'categoria' => optional($c->categoria)->nombre ?? '—',
                    'remitente' => $c->remitente,
                    'descripcion_documento' => $c->descripcion_documento,
                    'fecha_atencion' => $c->fecha_atencion?->format('Y-m-d\TH:i'),
                    'fecha_atencion_legible' => $c->fecha_atencion?->format('d/m/Y H:i'),
                    'nombre_solicitante' => $s->nombre_solicitante ?? '—',
                    'email_solicitante' => $s->email_solicitante ?? '—',
                    'telefono_solicitante' => $s->telefono_solicitante ?? '—',
                    'enviado_email' => (bool) $c->enviado_email,
                    'detalle_envio' => $c->detalle_envio,
                ];
            });
    }

    /**
     * Datos de una solicitud pendiente, para precargar el formulario de
     * "Aceptar" (remitente y descripción se prellenan, editables).
     */
    public function onGetSolicitud()
    {
        $solicitud = Solicitud::find(post('id'));

        if (!$solicitud) {
            throw new ValidationException(['remitente' => 'La solicitud ya no existe.']);
        }

        return [
            'solicitud' => [
                'id' => $solicitud->id,
                'nombre_solicitante' => $solicitud->nombre_solicitante,
                'email_solicitante' => $solicitud->email_solicitante,
                'telefono_solicitante' => $solicitud->telefono_solicitante,
                'mensaje' => $solicitud->mensaje,
                'documento' => optional($solicitud->documento())->nombre,
                'coleccion' => $solicitud->tipo === 'fabio' ? 'Fabio Castillo' : 'Fondo Bibliográfico',
                'ubicacion' => $solicitud->ubicacion(),
                'tiene_digital' => !empty(optional($solicitud->documento())->archivo),
            ],
        ];
    }

    public function onRechazar()
    {
        $solicitud = Solicitud::find(intval(post('id')));

        if (!$solicitud) {
            return ['estado' => 'error', 'mensaje' => 'La solicitud ya no existe.'];
        }

        $solicitud->estado = 'rechazado';
        $solicitud->admin_notas = trim((string) post('motivo', ''));
        $solicitud->save();

        return $this->respuesta('¡Solicitud rechazada!');
    }

    public function onGetCorrespondencia()
    {
        $c = Correspondencia::find(post('id'));

        if (!$c) {
            throw new ValidationException(['remitente' => 'El registro ya no existe.']);
        }

        return [
            'correspondencia' => [
                'id' => $c->id,
                'categoria_correspondencia_id' => $c->categoria_correspondencia_id,
                'remitente' => $c->remitente,
                'descripcion_documento' => $c->descripcion_documento,
                'fecha_atencion' => $c->fecha_atencion?->format('Y-m-d\TH:i'),
            ],
        ];
    }

    /**
     * Crea (aceptando una solicitud) o edita una correspondencia. Al crear,
     * marca la solicitud como aprobada y envía el correo al solicitante.
     */
    public function onRegistrar()
    {
        $data = Input::all();
        $id = $data['id'] ?? null;

        $this->validaciones($data, $id);

        if ($id) {
            $correspondencia = Correspondencia::find($id);

            if (!$correspondencia) {
                throw new ValidationException(['remitente' => 'El registro ya no existe.']);
            }
        } else {
            $solicitud = Solicitud::find($data['solicitud_id'] ?? null);

            if (!$solicitud) {
                throw new ValidationException(['remitente' => 'La solicitud ya no existe o ya fue procesada.']);
            }

            if (Correspondencia::where('solicitud_id', $solicitud->id)->exists()) {
                throw new ValidationException(['remitente' => 'Esta solicitud ya tiene una correspondencia registrada.']);
            }

            $correspondencia = new Correspondencia();
            $correspondencia->solicitud_id = $solicitud->id;
        }

        $correspondencia->categoria_correspondencia_id = $data['categoria_correspondencia_id'];
        $correspondencia->remitente = trim($data['remitente']);
        $correspondencia->descripcion_documento = trim($data['descripcion_documento']);
        $correspondencia->fecha_atencion = $this->normalizarFechaHora($data['fecha_atencion']);
        $correspondencia->save();

        $mensaje = $id ? '¡Correspondencia modificada correctamente!' : '¡Solicitud aceptada!';

        if (!$id) {
            $solicitud->estado = 'aprobado';
            $solicitud->save();

            $envio = $this->enviarCorreoDocumento($correspondencia);
            $mensaje .= $envio['enviado']
                ? ' Se envió el documento al correo del solicitante.'
                : ' No se pudo enviar el correo automáticamente (' . $envio['detalle'] . '); podés reenviarlo desde el historial.';
        }

        return $this->respuesta($mensaje);
    }

    public function onEliminar()
    {
        $correspondencia = Correspondencia::find(intval(post('id')));

        if (!$correspondencia) {
            return $this->respuesta('El registro ya no existe.', 'error');
        }

        $correspondencia->delete();

        return $this->respuesta('¡Eliminado con exito!');
    }

    public function onReenviarCorreo()
    {
        $correspondencia = Correspondencia::find(intval(post('id')));

        if (!$correspondencia) {
            return ['estado' => 'error', 'mensaje' => 'El registro ya no existe.'];
        }

        $envio = $this->enviarCorreoDocumento($correspondencia);

        return [
            'estado' => $envio['enviado'] ? 'exito' : 'error',
            'mensaje' => $envio['enviado']
                ? '¡Correo reenviado correctamente!'
                : 'No se pudo enviar el correo: ' . $envio['detalle'],
        ];
    }

    /**
     * El input <datetime-local> manda "2026-09-10T21:20" (sin segundos, con
     * "T"); Carbon/Eloquent necesitan un formato estricto para castear.
     */
    private function normalizarFechaHora(string $valor): string
    {
        $valor = str_replace('T', ' ', trim($valor));

        return strlen($valor) === 16 ? $valor . ':00' : $valor;
    }

    private function respuesta(string $mensaje, string $estado = 'exito'): array
    {
        return [
            '#listado-pendientes' => $this->renderPartial('@listado_pendientes', ['pendientes' => $this->obtenerPendientes()]),
            '#listado-historial' => $this->renderPartial('@listado_historial', ['historial' => $this->obtenerHistorial()]),
            'estado' => $estado,
            'mensaje' => $mensaje,
        ];
    }

    public function validaciones($data, $id = null)
    {
        $rules = [
            'categoria_correspondencia_id' => ['required'],
            'remitente' => ['required', 'string', 'max:150', 'regex:/^[\pL\s.\'\-]+$/u'],
            'descripcion_documento' => ['required', 'string', 'min:3', 'regex:/^(?=.*[\pL\pN]).+$/us'],
            'fecha_atencion' => ['required', 'date'],
        ];

        if (!$id) {
            $rules['solicitud_id'] = ['required'];
        }

        $validator = Validator::make($data, $rules, [
            'categoria_correspondencia_id.required' => '* Campo obligatorio.',
            'remitente.required' => '* Campo obligatorio.',
            'remitente.regex' => 'El remitente solo admite letras y espacios.',
            'descripcion_documento.required' => '* Campo obligatorio.',
            'descripcion_documento.min' => 'Mínimo 3 caracteres.',
            'descripcion_documento.regex' => 'Debe contener texto o números.',
            'fecha_atencion.required' => '* Campo obligatorio.',
            'fecha_atencion.date' => 'Fecha inválida.',
            'solicitud_id.required' => 'No se encontró la solicitud a aceptar.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (!CategoriaCorrespondencia::find($data['categoria_correspondencia_id'])) {
            throw new ValidationException(['categoria_correspondencia_id' => 'La categoría seleccionada ya no existe.']);
        }
    }

    /**
     * Envía por correo el documento solicitado (adjunto si es digital) al
     * solicitante. Nunca lanza: cualquier error se captura y se informa,
     * para que aceptar una solicitud nunca falle por un problema de correo.
     */
    private function enviarCorreoDocumento(Correspondencia $correspondencia): array
    {
        $solicitud = $correspondencia->solicitud;

        if (!$solicitud || !$solicitud->email_solicitante) {
            $this->marcarEnvio($correspondencia, false, 'La solicitud no tiene un correo válido.');

            return ['enviado' => false, 'detalle' => 'sin correo del solicitante'];
        }

        $documento = $solicitud->documento();
        $carpeta = $solicitud->tipo === 'fabio' ? 'fabio' : 'fondo';
        $rutaAdjunto = null;

        if ($documento && $documento->archivo) {
            $ruta = base_path('storage/app/uploads/public/' . $carpeta . '/' . $documento->archivo);

            if (is_file($ruta)) {
                $rutaAdjunto = $ruta;
            }
        }

        $datos = [
            'nombre_solicitante' => $solicitud->nombre_solicitante,
            'documento_nombre' => optional($documento)->nombre ?? 'Documento solicitado',
            'coleccion' => $solicitud->tipo === 'fabio' ? 'Fabio Castillo' : 'Fondo Bibliográfico',
            'ubicacion' => $solicitud->ubicacion(),
            'categoria' => optional($correspondencia->categoria)->nombre ?? '—',
            'fecha_atencion' => $correspondencia->fecha_atencion?->format('d/m/Y H:i'),
            'mensaje_admin' => $correspondencia->descripcion_documento,
            'tiene_adjunto' => (bool) $rutaAdjunto,
        ];

        try {
            Mail::send('iehaa.correspondencia::mail.documento', $datos, function ($message) use ($solicitud, $rutaAdjunto, $documento) {
                $message->to($solicitud->email_solicitante, $solicitud->nombre_solicitante);
                $message->subject('Tu solicitud de préstamo fue aprobada — IEHAA');

                if ($rutaAdjunto) {
                    $message->attach($rutaAdjunto, [
                        'as' => $documento->nombre . '.' . pathinfo($documento->archivo, PATHINFO_EXTENSION),
                    ]);
                }
            });

            $this->marcarEnvio($correspondencia, true, null);

            return ['enviado' => true, 'detalle' => null];
        } catch (\Throwable $e) {
            \Log::error('[IEHAA] No se pudo enviar el correo de correspondencia #' . $correspondencia->id . ': ' . $e->getMessage());

            $this->marcarEnvio($correspondencia, false, $e->getMessage());

            return ['enviado' => false, 'detalle' => 'revisá la configuración de correo del sistema'];
        }
    }

    private function marcarEnvio(Correspondencia $correspondencia, bool $enviado, ?string $detalle): void
    {
        $correspondencia->enviado_email = $enviado;
        $correspondencia->detalle_envio = $detalle;
        $correspondencia->timestamps = false;
        $correspondencia->save();
        $correspondencia->timestamps = true;
    }

    protected function datosReporte(): array
    {
        $filas = [];

        foreach ($this->obtenerHistorial() as $i => $c) {
            $filas[] = [
                $i + 1,
                $c['nombre_solicitante'],
                $c['email_solicitante'],
                $c['telefono_solicitante'],
                $c['documento'],
                $c['coleccion'],
                $c['categoria'],
                $c['remitente'],
                $c['fecha_atencion_legible'] ?: '—',
                $c['enviado_email'] ? 'Sí' : 'No',
            ];
        }

        return [
            'Historial de correspondencia',
            ['#', 'Solicitante', 'Correo', 'Teléfono', 'Documento', 'Colección', 'Categoría', 'Remitente', 'Fecha de atención', 'Correo enviado'],
            $filas,
            'correspondencia',
        ];
    }
}
