<?php

namespace Iehaa\Inventario\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Inventario\Models\ActivoFijo;
use Iehaa\Inventario\Models\ActivoFijoArchivo;
use Iehaa\Reportes\Classes\ReporteModulo;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class ActivoFijoComponent extends ComponentBase
{
    use ReporteModulo;

    protected $rutaSubida = 'storage/app/uploads/public/activo_fijo/';

    protected const MAX_ARCHIVOS = 5;

    public function componentDetails()
    {
        return [
            'name'        => 'activoFijoComponent',
            'description' => 'Inventario de Activo Fijo'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['bienes'] = $this->obtenerTodos();
        $this->page['estadosBien'] = ActivoFijo::ESTADOS_BIEN;
        $this->page['estados'] = ActivoFijo::ESTADOS;
        $this->page['formasAdquisicion'] = ActivoFijo::FORMAS_ADQUISICION;
        $this->page['responsables'] = $this->obtenerResponsables();

        if (session()->has('error_descarga')) {
            $this->page['errorDescarga'] = session()->pull('error_descarga');
        }
    }

    public function obtenerTodos()
    {
        return ActivoFijo::with('archivos')->orderByDesc('id')->get();
    }

    public function onRegistrar()
    {
        $data = Input::all();
        $archivos = Input::file('archivos') ?? [];
        $archivos = array_filter(is_array($archivos) ? $archivos : [$archivos]);
        $id = $data['id'] ?? null;

        $bien = $id ? ActivoFijo::find($id) : new ActivoFijo();

        if (!$bien) {
            throw new ValidationException(['descripcion_bien' => 'El bien ya no existe.']);
        }

        $this->validaciones($data, $archivos, $bien);

        $bien->numero_inventario = trim($data['numero_inventario']);
        $bien->descripcion_bien = trim($data['descripcion_bien']);
        $bien->marca = $this->limpio($data, 'marca');
        $bien->modelo = $this->limpio($data, 'modelo');
        $bien->serie = $this->limpio($data, 'serie');
        $bien->responsable = trim($data['responsable']);
        $bien->estado_bien = $data['estado_bien'];
        $bien->estado = $data['estado'];
        $bien->descripcion = $this->limpio($data, 'descripcion');
        $bien->observacion = $this->limpio($data, 'observacion');
        $bien->forma_adquisicion = $data['forma_adquisicion'];
        $bien->fecha_adquisicion = $this->limpio($data, 'fecha_adquisicion');
        $bien->precio = $this->limpio($data, 'precio');
        $bien->save();

        foreach ($archivos as $archivo) {
            if (!$archivo) {
                continue;
            }

            ActivoFijoArchivo::create([
                'activo_fijo_id' => $bien->id,
                'archivo' => $this->guardarArchivo($archivo),
                'nombre_original' => $archivo->getClientOriginalName(),
            ]);
        }

        return [
            '#listado' => $this->renderPartial('@listado', ['bienes' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => $id ? '¡Bien modificado correctamente!' : '¡Bien registrado correctamente!',
            'responsables' => $this->obtenerResponsables(),
        ];
    }

    /**
     * Lista de responsables distintos, usada para repintar el selector de
     * "reporte por responsable" sin esperar a que se recargue la página.
     */
    private function obtenerResponsables()
    {
        return ActivoFijo::query()
            ->select('responsable')
            ->distinct()
            ->orderBy('responsable')
            ->pluck('responsable');
    }

    public function onGetActivoFijo()
    {
        $bien = ActivoFijo::with('archivos')->find(post('id'));

        return ['bien' => $bien];
    }

    public function onEliminar()
    {
        $bien = ActivoFijo::with('archivos')->find(intval(post('id')));

        if (!$bien) {
            return [
                '#listado' => $this->renderPartial('@listado', ['bienes' => $this->obtenerTodos()]),
                'estado' => 'error',
                'mensaje' => 'El bien ya no existe.',
            ];
        }

        foreach ($bien->archivos as $archivo) {
            $this->eliminarArchivoFisico($archivo->archivo);
        }

        $bien->delete();

        return [
            '#listado' => $this->renderPartial('@listado', ['bienes' => $this->obtenerTodos()]),
            'estado' => 'exito',
            'mensaje' => '¡Eliminado con exito!',
            'responsables' => $this->obtenerResponsables(),
        ];
    }

    /**
     * Elimina un solo archivo adjunto (desde el formulario de edición).
     */
    public function onEliminarArchivo()
    {
        $archivo = ActivoFijoArchivo::find(intval(post('archivo_id')));

        if ($archivo) {
            $this->eliminarArchivoFisico($archivo->archivo);
            $archivo->delete();
        }

        return ['estado' => 'exito', 'mensaje' => 'Archivo eliminado.'];
    }

    /**
     * Descarga real de uno de los archivos adjuntos.
     */
    public function descargar($archivoId)
    {
        $archivo = ActivoFijoArchivo::find(intval($archivoId));

        if (!$archivo) {
            return redirect('/inventario-activo-fijo')->with('error_descarga', 'El archivo no está disponible.');
        }

        $ruta = base_path($this->rutaSubida . $archivo->archivo);

        if (!is_file($ruta)) {
            return redirect('/inventario-activo-fijo')->with('error_descarga', 'El archivo no se encuentra en el servidor.');
        }

        return response()->download($ruta, $archivo->nombre_original ?: $archivo->archivo);
    }

    private function limpio($data, $campo)
    {
        $valor = trim((string) ($data[$campo] ?? ''));

        return $valor !== '' ? $valor : null;
    }

    public function validaciones($data, $archivos, $bien)
    {
        $alfanumerico = 'regex:/^[\pL\pN\s.,;:()\-\/#°"\']+$/u';
        $id = $bien->id ?? null;

        $rules = [
            'numero_inventario' => ['required', 'string', 'max:60', 'regex:/^[\pL\pN\s.\-\/]+$/u'],
            'descripcion_bien'  => ['required', 'string', 'min:3', 'max:255', $alfanumerico],
            'marca'             => ['nullable', 'string', 'max:100', $alfanumerico],
            'modelo'            => ['nullable', 'string', 'max:100', $alfanumerico],
            'serie'             => ['nullable', 'string', 'max:100', $alfanumerico],
            'responsable'       => ['required', 'string', 'max:120', 'regex:/^[\pL\s.\'\-]+$/u'],
            'estado_bien'       => ['required', 'in:' . implode(',', ActivoFijo::ESTADOS_BIEN)],
            'estado'            => ['required', 'in:' . implode(',', ActivoFijo::ESTADOS)],
            'descripcion'       => ['nullable', 'string', $alfanumerico],
            'observacion'       => ['nullable', 'string', $alfanumerico],
            'forma_adquisicion' => ['required', 'in:' . implode(',', ActivoFijo::FORMAS_ADQUISICION)],
            'fecha_adquisicion' => ['nullable', 'date'],
            'precio'            => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];

        $validator = Validator::make($data, $rules, [
            'numero_inventario.required' => '* Campo obligatorio.',
            'numero_inventario.regex'    => 'Solo letras, números, espacios, puntos, guiones y "/".',
            'descripcion_bien.required'  => '* Campo obligatorio.',
            'descripcion_bien.min'       => 'Mínimo 3 caracteres.',
            'descripcion_bien.regex'     => 'Formato no válido.',
            'marca.regex'                => 'Formato no válido.',
            'modelo.regex'               => 'Formato no válido.',
            'serie.regex'                => 'Formato no válido.',
            'responsable.required'       => '* Campo obligatorio.',
            'responsable.regex'          => 'El responsable solo admite letras y espacios.',
            'estado_bien.required'       => '* Campo obligatorio.',
            'estado_bien.in'             => 'Opción no válida.',
            'estado.required'            => '* Campo obligatorio.',
            'estado.in'                  => 'Opción no válida.',
            'forma_adquisicion.required' => '* Campo obligatorio.',
            'forma_adquisicion.in'       => 'Opción no válida.',
            'precio.numeric'             => 'Ingresá un monto válido.',
            'precio.min'                 => 'No puede ser negativo.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $duplicado = ActivoFijo::whereRaw('LOWER(TRIM(numero_inventario)) = ?', [mb_strtolower(trim($data['numero_inventario']))])
            ->when($id, fn ($q) => $q->where('id', '!=', $id))
            ->exists();

        if ($duplicado) {
            throw new ValidationException(['numero_inventario' => 'Este valor ya existe.']);
        }

        $actuales = $id ? ActivoFijoArchivo::where('activo_fijo_id', $id)->count() : 0;

        if (($actuales + count($archivos)) > self::MAX_ARCHIVOS) {
            throw new ValidationException(['archivos' => 'Máximo ' . self::MAX_ARCHIVOS . ' archivos por bien (ya hay ' . $actuales . ').']);
        }
    }

    private function guardarArchivo($archivo): string
    {
        $uploadPath = base_path($this->rutaSubida);

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombre = time() . '_' . uniqid() . '_' . preg_replace('/[^\w.\- ]+/u', '_', $archivo->getClientOriginalName());
        $archivo->move($uploadPath, $nombre);

        return $nombre;
    }

    private function eliminarArchivoFisico($nombreArchivo)
    {
        if (!$nombreArchivo) {
            return;
        }

        $ruta = base_path($this->rutaSubida . $nombreArchivo);

        if (is_file($ruta)) {
            @unlink($ruta);
        }

        Storage::delete('uploads/public/activo_fijo/' . $nombreArchivo);
    }

    protected function datosReporte(): array
    {
        $responsable = trim((string) request()->query('responsable', ''));

        $query = ActivoFijo::orderBy('descripcion_bien');

        if ($responsable !== '') {
            $query->where('responsable', $responsable);
        }

        $filas = [];
        foreach ($query->get() as $i => $b) {
            $filas[] = [
                $i + 1,
                $b->numero_inventario,
                $b->descripcion_bien,
                $b->marca ?: '—',
                $b->modelo ?: '—',
                $b->serie ?: '—',
                $b->responsable,
                $b->estado_bien,
                $b->estado,
                $b->forma_adquisicion,
                $b->fecha_adquisicion ? $b->fecha_adquisicion->format('d/m/Y') : '—',
                $b->precio_formateado,
            ];
        }

        $titulo = $responsable !== ''
            ? 'Inventario de activo fijo — Responsable: ' . $responsable
            : 'Inventario general de activo fijo';

        $columnas = ['#', 'N.º inventario', 'Descripción del bien', 'Marca', 'Modelo', 'Serie', 'Responsable',
            'Estado del bien', 'Estado', 'Forma de adquisición', 'Fecha adquisición', 'Precio'];

        $archivo = $responsable !== '' ? 'activo_fijo_' . $responsable : 'activo_fijo_general';

        return [$titulo, $columnas, $filas, $archivo];
    }
}
