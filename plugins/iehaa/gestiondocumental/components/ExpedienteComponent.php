<?php

namespace Iehaa\Gestiondocumental\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Gestiondocumental\Models\Expediente;
use Iehaa\Reportes\Classes\ReporteModulo;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ApplicationException;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class ExpedienteComponent extends ComponentBase
{
    use ReporteModulo;

    protected $rutaSubida = 'storage/app/uploads/public/expedientes/';

    public function componentDetails()
    {
        return [
            'name'        => 'expedienteComponent',
            'description' => 'Gestión documental — inventario de expedientes'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['expedientes'] = $this->obtenerTodos();
        $this->page['unidades'] = Expediente::UNIDADES;
    }

    public function obtenerTodos()
    {
        return Expediente::orderByDesc('id')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $archivo = Input::file('archivo');
        $id = $data['id'] ?? null;

        $limpio = function ($clave) use ($data) {
            $valor = trim((string) ($data[$clave] ?? ''));
            return $valor !== '' ? $valor : null;
        };

        $this->validaciones($data, $archivo, $id);

        $expediente = $id ? Expediente::find($id) : new Expediente();

        if (!$expediente) {
            throw new ValidationException(['asunto' => 'El expediente ya no existe.']);
        }

        $unidad = (string) ($data['unidad_instalacion'] ?? '');

        $expediente->serie = $limpio('serie');
        $expediente->subserie = $limpio('subserie');
        $expediente->asunto = $limpio('asunto');
        $expediente->fecha_inicial = $limpio('fecha_inicial');
        $expediente->fecha_final = $limpio('fecha_final');
        $expediente->unidad_instalacion = $unidad;
        $expediente->unidad_instalacion_otro = $unidad === 'Otro' ? $limpio('unidad_instalacion_otro') : null;
        $expediente->cantidad_folios = $limpio('cantidad_folios') !== null ? (int) $data['cantidad_folios'] : null;
        $expediente->volumen = $limpio('volumen');
        $expediente->soporte = $limpio('soporte');
        $expediente->formato = ($data['formato'] ?? 'impreso') === 'digital' ? 'digital' : 'impreso';
        $expediente->estado_conservacion = $limpio('estado_conservacion');
        $expediente->sig_fila = $limpio('sig_fila');
        $expediente->sig_estante = $limpio('sig_estante');
        $expediente->sig_anaquel = $limpio('sig_anaquel');
        $expediente->sig_posicion = $limpio('sig_posicion');

        if ($archivo) {
            $expediente->archivo = $this->guardarArchivo($archivo, $expediente->archivo);
        }

        $expediente->save();

        return [
            '#listado' => $this->renderPartial('@listado', ['expedientes' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Expediente modificado correctamente!' : '¡Expediente registrado correctamente!'
        ];
    }

    public function onGetExpediente()
    {
        return ['expediente' => Expediente::find(post('id'))];
    }

    public function onEliminar()
    {
        $expediente = Expediente::find(intval(post('id')));

        if (!$expediente) {
            return [
                '#listado' => $this->renderPartial('@listado', ['expedientes' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El expediente ya no existe.'
            ];
        }

        $this->eliminarArchivo($expediente->archivo);
        $expediente->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['expedientes' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!'
        ];
    }

    /**
     * Descarga real del archivo del expediente (ruta en routes.php).
     */
    public function descargar($id)
    {
        $expediente = Expediente::find(intval($id));

        if (!$expediente || !$expediente->archivo) {
            throw new ApplicationException('Este expediente no tiene un documento adjunto.');
        }

        $ruta = base_path($this->rutaSubida . $expediente->archivo);

        if (!is_file($ruta)) {
            throw new ApplicationException('El documento de este expediente no se encuentra en el servidor.');
        }

        $extension = pathinfo($expediente->archivo, PATHINFO_EXTENSION);
        $nombre = \Str::slug($expediente->serie . ' ' . $expediente->asunto) . ($extension ? '.' . $extension : '');

        return response()->download($ruta, $nombre);
    }

    public function validaciones($data, $archivo, $id = null)
    {
        $alfanumerico = 'regex:/^[\pL\pN\s.,;:()\-\/#°"\']+$/u';

        $rules = [
            'serie'               => ['required', 'string', 'max:120', $alfanumerico],
            'subserie'            => ['nullable', 'string', 'max:120', $alfanumerico],
            'asunto'              => ['required', 'string', 'min:3', 'max:255', 'regex:/^(?=.*[\pL\pN]).+$/us'],
            'fecha_inicial'       => ['nullable', 'date'],
            'fecha_final'         => ['nullable', 'date', 'after_or_equal:fecha_inicial'],
            'unidad_instalacion'  => ['required', 'in:' . implode(',', Expediente::UNIDADES)],
            'cantidad_folios'     => ['nullable', 'integer', 'min:0', 'max:9999999'],
            'volumen'             => ['nullable', 'string', 'max:60', $alfanumerico],
            'soporte'             => ['nullable', 'string', 'max:120', $alfanumerico],
            'formato'             => ['required', 'in:' . implode(',', Expediente::FORMATOS)],
            'estado_conservacion' => ['nullable', 'string', 'max:120', $alfanumerico],
            'sig_fila'            => ['nullable', 'string', 'max:20'],
            'sig_estante'         => ['nullable', 'string', 'max:20'],
            'sig_anaquel'         => ['nullable', 'string', 'max:20'],
            'sig_posicion'        => ['nullable', 'string', 'max:20'],
        ];

        if (($data['unidad_instalacion'] ?? '') === 'Otro') {
            $rules['unidad_instalacion_otro'] = ['required', 'string', 'max:120'];
        }

        // El archivo es obligatorio si el formato es digital y todavía no hay uno.
        $tieneArchivoPrevio = $id
            ? (bool) optional(Expediente::find($id))->archivo
            : false;

        if (($data['formato'] ?? '') === 'digital' && !$archivo && !$tieneArchivoPrevio) {
            $rules['archivo'] = ['required'];
        }

        $validator = Validator::make($data, $rules, [
            'serie.required'   => '* Campo obligatorio.',
            'serie.regex'      => 'La serie solo admite texto y números.',
            'subserie.regex'   => 'La subserie solo admite texto y números.',
            'asunto.required'  => '* Campo obligatorio.',
            'asunto.min'       => 'Mínimo 3 caracteres.',
            'asunto.regex'     => 'El asunto debe contener texto o números.',
            'fecha_final.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            'unidad_instalacion.required' => '* Campo obligatorio.',
            'unidad_instalacion.in'       => 'Opción no válida.',
            'unidad_instalacion_otro.required' => '* Campo obligatorio.',
            'cantidad_folios.integer' => 'Ingresá un número entero.',
            'cantidad_folios.min'     => 'No puede ser negativo.',
            'volumen.regex'           => 'El volumen solo admite texto y números.',
            'soporte.regex'           => 'El soporte solo admite texto y números.',
            'formato.required'        => '* Campo obligatorio.',
            'formato.in'              => 'Formato no válido.',
            'estado_conservacion.regex' => 'El estado de conservación solo admite texto y números.',
            'archivo.required'        => '* Campo obligatorio.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function guardarArchivo($archivo, $anterior = false)
    {
        $uploadPath = base_path($this->rutaSubida);

        if ($anterior) {
            $this->eliminarArchivo($anterior);
        }

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombre = time() . '_' . preg_replace('/[^\w.\- ]+/u', '_', $archivo->getClientOriginalName());
        $archivo->move($uploadPath, $nombre);

        return $nombre;
    }

    public function eliminarArchivo($nombreArchivo)
    {
        if (!$nombreArchivo) {
            return;
        }

        $ruta = base_path($this->rutaSubida . $nombreArchivo);

        if (is_file($ruta)) {
            @unlink($ruta);
        }

        Storage::delete('uploads/public/expedientes/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $filas = [];

        foreach ($this->obtenerTodos()->reverse()->values() as $i => $e) {
            $filas[] = [
                $i + 1,
                $e->serie,
                $e->subserie ?: '—',
                $e->asunto,
                $e->fechas_extremas,
                $e->unidad_texto,
                $e->cantidad_folios ?? '—',
                $e->volumen ?: '—',
                $e->soporte ?: '—',
                ucfirst($e->formato),
                $e->estado_conservacion ?: '—',
                $e->signatura,
            ];
        }

        return [
            'Inventario de gestión documental',
            ['#', 'Serie', 'Subserie', 'Asunto / título', 'Fechas extremas', 'Unidad de instalación',
                'Folios', 'Volumen (m.l.)', 'Soporte', 'Formato', 'Estado de conservación', 'Signatura topográfica'],
            $filas,
            'gestion_documental',
        ];
    }
}
