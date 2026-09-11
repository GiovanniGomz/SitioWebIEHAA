<?php

namespace Iehaa\Exploradorarchivos\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Anaquel\Models\Anaquel;
use Iehaa\Archiveros\Models\Archivero;
use Iehaa\Carpetas\Models\Carpeta;
use Iehaa\Coleccion\Models\Coleccion;
use Iehaa\Estante\Models\Estante;
use Iehaa\Fabio\Models\Fabio;
use Iehaa\Folders\Models\Folder;
use Iehaa\Fondo\Models\Fondo;
use Iehaa\Gavetas\Models\Gaveta;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

/**
 * Explorador unificado para las dos jerarquías documentales de IEHAA:
 * Fabio Castillo (Archivero > Gaveta > Carpeta > Folder > documento) y
 * Fondo Bibliográfico (Estante > Anaquel > Colección > documento).
 *
 * Reutiliza los modelos Eloquent ya existentes de cada plugin, solo
 * agrega una capa de navegación AJAX de un solo componente.
 */
class ExploradorComponent extends ComponentBase
{
    /**
     * Mapa de configuración por cadena. Cada nivel intermedio define el
     * modelo, el campo visible, el ícono y la llave foránea hacia su padre.
     * El último elemento ('documentos') es el nivel hoja donde se suben archivos.
     */
    public static function cadenas(): array
    {
        return [
            'fabio' => [
                'titulo' => 'Fabio Castillo',
                'niveles' => [
                    ['modelo' => Archivero::class, 'campo' => 'codigo', 'etiqueta' => 'Archivero', 'icono' => 'bi-archive-fill', 'fk' => null, 'auto' => true],
                    ['modelo' => Gaveta::class, 'campo' => 'codigo', 'etiqueta' => 'Gaveta', 'icono' => 'bi-inboxes-fill', 'fk' => 'archivero_id', 'auto' => true],
                    ['modelo' => Carpeta::class, 'campo' => 'nombre', 'etiqueta' => 'Carpeta', 'icono' => 'bi-folder2', 'fk' => 'gaveta_id', 'unico_por_padre' => true, 'alfanumerico' => true],
                    ['modelo' => Folder::class, 'campo' => 'nombre', 'etiqueta' => 'Folder', 'icono' => 'bi-folder-fill', 'fk' => 'carpeta_id', 'unico_por_padre' => true, 'alfanumerico' => true],
                ],
                'documentos' => ['modelo' => Fabio::class, 'campo' => 'nombre', 'fk' => 'folder_id', 'carpeta' => 'fabio', 'unico_por_padre' => true],
            ],
            'fondo' => [
                'titulo' => 'Fondo Bibliográfico',
                'niveles' => [
                    ['modelo' => Estante::class, 'campo' => 'codigo', 'etiqueta' => 'Estante', 'icono' => 'bi-bookshelf', 'fk' => null, 'auto' => true],
                    ['modelo' => Anaquel::class, 'campo' => 'codigo', 'etiqueta' => 'Anaquel', 'icono' => 'bi-archive-fill', 'fk' => 'estante_id', 'auto' => true],
                    ['modelo' => Coleccion::class, 'campo' => 'nombre', 'etiqueta' => 'Colección', 'icono' => 'bi-folder2', 'fk' => 'anaquel_id', 'unico_por_padre' => true, 'alfanumerico' => true],
                ],
                'documentos' => ['modelo' => Fondo::class, 'campo' => 'nombre', 'fk' => 'coleccion_id', 'carpeta' => 'fondo', 'unico_por_padre' => true],
            ],
        ];
    }

    public function componentDetails()
    {
        return [
            'name'        => 'exploradorComponent',
            'description' => 'Explorador unificado de archivos (Fabio Castillo / Fondo Bibliográfico)'
        ];
    }

    public function defineProperties()
    {
        return [
            'modo' => [
                'title' => 'Modo',
                'description' => 'fabio o fondo',
                'default' => 'fabio',
                'type' => 'string',
            ],
        ];
    }

    public function onRun()
    {
        $modo = $this->property('modo', 'fabio');
        $cadena = self::cadenas()[$modo] ?? null;

        $this->page['modo'] = $modo;
        $this->page['titulo'] = $cadena['titulo'] ?? '';
        $this->page['totalNiveles'] = $cadena ? count($cadena['niveles']) : 0;
        $this->page['assetVersion'] = @filemtime(base_path('themes/web/assets/js/explorador/explorador.js')) ?: time();
    }

    /**
     * Devuelve el contenido de un nivel: hijos (nodos) o documentos si es el nivel hoja.
     * data: modo, nivel (0-index), parent_id (null en la raíz)
     */
    public function onNavegar()
    {
        $data = Input::all();
        $cadena = $this->obtenerCadena($data['modo'] ?? '');
        $nivel = (int) ($data['nivel'] ?? 0);
        $parentId = $data['parent_id'] ?? null;

        $totalNiveles = count($cadena['niveles']);
        $esHoja = $nivel >= $totalNiveles;

        if ($esHoja) {
            $config = $cadena['documentos'];
            $query = $config['modelo']::query();

            if ($parentId) {
                $query->where($config['fk'], $parentId);
            }

            $items = $query->get()->map(function ($doc) use ($config) {
                return [
                    'id' => $doc->id,
                    'nombre' => $doc->{$config['campo']},
                    'archivo' => $doc->archivo,
                    'url' => 'storage/app/uploads/public/' . $config['carpeta'] . '/' . $doc->archivo,
                    'icono' => $this->iconoParaArchivo($doc->archivo),
                ];
            });

            return [
                'esHoja' => true,
                'nivel' => $nivel,
                'items' => $items,
            ];
        }

        $nivelConfig = $cadena['niveles'][$nivel];
        $query = $nivelConfig['modelo']::query();

        if ($nivelConfig['fk']) {
            $query->where($nivelConfig['fk'], $parentId);
        }

        $siguienteFk = $esHoja
            ? null
            : ($cadena['niveles'][$nivel + 1]['fk'] ?? $cadena['documentos']['fk']);

        $siguienteModelo = ($nivel + 1) < $totalNiveles
            ? $cadena['niveles'][$nivel + 1]['modelo']
            : $cadena['documentos']['modelo'];

        $items = $query->get()->map(function ($nodo) use ($nivelConfig, $siguienteModelo, $siguienteFk) {
            $hijos = $siguienteFk ? $siguienteModelo::where($siguienteFk, $nodo->id)->count() : 0;

            return [
                'id' => $nodo->id,
                'nombre' => $nodo->{$nivelConfig['campo']},
                'hijos' => $hijos,
            ];
        });

        return [
            'esHoja' => false,
            'nivel' => $nivel,
            'etiqueta' => $nivelConfig['etiqueta'],
            'icono' => $nivelConfig['icono'],
            'items' => $items,
        ];
    }

    /**
     * Crea o edita (si viene "id") un nodo intermedio: archivero, gaveta,
     * carpeta, folder, estante, anaquel o colección.
     */
    public function onCrearNodo()
    {
        $data = Input::all();
        $cadena = $this->obtenerCadena($data['modo'] ?? '');
        $nivel = (int) ($data['nivel'] ?? 0);
        $nivelConfig = $cadena['niveles'][$nivel] ?? null;

        if (!$nivelConfig) {
            throw new ValidationException(['nombre' => 'Nivel inválido.']);
        }

        $campo = $nivelConfig['campo'];
        $modeloClase = $nivelConfig['modelo'];
        $id = $data['id'] ?? null;
        $parentId = $data['parent_id'] ?? null;

        if ($nivelConfig['fk'] && !$id && !$parentId) {
            throw new ValidationException([$campo => 'No se encontró el nodo padre.']);
        }

        // Niveles automáticos (archivero, gaveta, estante, anaquel): se crean sin
        // pedir datos, con un código correlativo (1, 2, 3...) dentro de su padre.
        if (!empty($nivelConfig['auto']) && !$id) {
            $nodo = new $modeloClase();

            if ($nivelConfig['fk']) {
                $nodo->{$nivelConfig['fk']} = $parentId;
            }

            if (in_array('url', $nodo->getFillable(), true)) {
                $nodo->url = $this->generarURL();
            }

            $nodo->{$campo} = (string) $this->siguienteCorrelativo($modeloClase, $nivelConfig['fk'], $parentId);
            $nodo->save();

            return [
                'estado' => 'exito',
                'mensaje' => '¡' . $nivelConfig['etiqueta'] . ' ' . $nodo->{$campo} . ' creado!',
                'id' => $nodo->id,
                'nombre' => $nodo->{$campo},
            ];
        }

        $this->validar($data, $campo, $nivelConfig);

        if ($id) {
            $nodo = $modeloClase::find($id);

            if (!$nodo) {
                throw new ValidationException([$campo => 'El registro ya no existe.']);
            }

            $parentId = $nodo->{$nivelConfig['fk']} ?? $parentId;
        } else {
            $nodo = new $modeloClase();

            if ($nivelConfig['fk']) {
                $nodo->{$nivelConfig['fk']} = $parentId;
            }

            if (in_array('url', $nodo->getFillable(), true)) {
                $nodo->url = $this->generarURL();
            }
        }

        $valor = trim($data[$campo]);

        if (!empty($nivelConfig['unico_por_padre'])) {
            $this->verificarUnicoPorPadre($modeloClase, $campo, $valor, $nivelConfig['fk'], $parentId, $id, strtolower($nivelConfig['etiqueta']));
        }

        $nodo->{$campo} = $valor;
        $nodo->save();

        return [
            'estado' => 'exito',
            'mensaje' => $id ? '¡Actualizado correctamente!' : '¡Creado correctamente!',
            'id' => $nodo->id,
            'nombre' => $nodo->{$campo},
        ];
    }

    /**
     * Siguiente número correlativo para un nivel automático, dentro del padre.
     */
    private function siguienteCorrelativo(string $modeloClase, ?string $fk, $parentId): int
    {
        $query = $modeloClase::query();

        if ($fk) {
            $query->where($fk, $parentId);
        }

        $max = (int) $query->max(DB::raw("COALESCE(NULLIF(regexp_replace(codigo, '[^0-9]', '', 'g'), ''), '0')::int"));

        return $max + 1;
    }

    private function verificarUnicoPorPadre(string $modeloClase, string $campo, string $valor, ?string $fk, $parentId, $id, string $etiqueta): void
    {
        $query = $modeloClase::whereRaw('LOWER(TRIM(' . $campo . ')) = ?', [mb_strtolower($valor)]);

        if ($fk) {
            $query->where($fk, $parentId);
        }

        if ($id) {
            $query->where('id', '!=', $id);
        }

        if ($query->exists()) {
            throw new ValidationException([
                $campo => 'Este valor ya existe.',
            ]);
        }
    }

    /**
     * Sube un documento en el nivel hoja.
     */
    public function onSubirArchivo()
    {
        $data = Input::all();
        $cadena = $this->obtenerCadena($data['modo'] ?? '');
        $config = $cadena['documentos'];
        $archivo = Input::file('archivo');

        $validator = Validator::make($data, [
            'nombre' => 'required|min:3',
            'parent_id' => 'required',
        ], [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min' => 'Mínimo 3 caracteres.',
            'parent_id.required' => 'No se encontró la carpeta destino.',
        ]);

        if (!$archivo) {
            $validator->errors()->add('archivo', 'Debés seleccionar un archivo.');
        }

        if ($validator->fails() || !$archivo) {
            throw new ValidationException($validator);
        }

        $modeloClase = $config['modelo'];

        $this->verificarUnicoPorPadre($modeloClase, $config['campo'], trim($data['nombre']), $config['fk'], $data['parent_id'], null, 'documento');

        $documento = new $modeloClase();
        $documento->{$config['campo']} = trim($data['nombre']);
        $documento->{$config['fk']} = $data['parent_id'];
        $documento->archivo = $this->guardarArchivo($archivo, $config['carpeta']);
        $documento->save();

        return [
            'estado' => 'exito',
            'mensaje' => '¡Documento subido correctamente!',
            'id' => $documento->id,
            'nombre' => $documento->{$config['campo']},
            'archivo' => $documento->archivo,
            'url' => 'storage/app/uploads/public/' . $config['carpeta'] . '/' . $documento->archivo,
            'icono' => $this->iconoParaArchivo($documento->archivo),
        ];
    }

    /**
     * Edita el nombre de un documento (y, opcionalmente, reemplaza su archivo).
     */
    public function onEditarDocumento()
    {
        $data = Input::all();
        $cadena = $this->obtenerCadena($data['modo'] ?? '');
        $config = $cadena['documentos'];
        $archivo = Input::file('archivo');

        $documento = $config['modelo']::find(intval($data['id'] ?? 0));

        if (!$documento) {
            throw new ValidationException(['nombre' => 'El documento ya no existe.']);
        }

        $validator = Validator::make($data, [
            'nombre' => 'required|min:3',
        ], [
            'nombre.required' => '* Campo obligatorio.',
            'nombre.min' => 'Mínimo 3 caracteres.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->verificarUnicoPorPadre($config['modelo'], $config['campo'], trim($data['nombre']), $config['fk'], $documento->{$config['fk']}, $documento->id, 'documento');

        $documento->{$config['campo']} = trim($data['nombre']);

        if ($archivo) {
            $documento->archivo = $this->guardarArchivo($archivo, $config['carpeta'], $documento->archivo);
        }

        $documento->save();

        return [
            'estado' => 'exito',
            'mensaje' => '¡Documento actualizado!',
            'id' => $documento->id,
            'nombre' => $documento->{$config['campo']},
            'archivo' => $documento->archivo,
            'url' => 'storage/app/uploads/public/' . $config['carpeta'] . '/' . $documento->archivo,
            'icono' => $this->iconoParaArchivo($documento->archivo),
        ];
    }

    /**
     * Elimina un nodo intermedio. Se bloquea si todavía tiene hijos, para
     * evitar el error de llave foránea y darle al usuario un mensaje claro.
     */
    public function onEliminarNodo()
    {
        $data = Input::all();
        $cadena = $this->obtenerCadena($data['modo'] ?? '');
        $nivel = (int) ($data['nivel'] ?? 0);
        $nivelConfig = $cadena['niveles'][$nivel] ?? null;

        if (!$nivelConfig) {
            return ['estado' => 'error', 'mensaje' => 'Nivel inválido.'];
        }

        $totalNiveles = count($cadena['niveles']);
        $siguienteFk = ($nivel + 1) < $totalNiveles
            ? $cadena['niveles'][$nivel + 1]['fk']
            : $cadena['documentos']['fk'];
        $siguienteModelo = ($nivel + 1) < $totalNiveles
            ? $cadena['niveles'][$nivel + 1]['modelo']
            : $cadena['documentos']['modelo'];

        $id = intval($data['id']);
        $tieneHijos = $siguienteModelo::where($siguienteFk, $id)->exists();

        if ($tieneHijos) {
            return [
                'estado' => 'error',
                'mensaje' => 'Este ' . strtolower($nivelConfig['etiqueta']) . ' todavía tiene contenido adentro. Vaciálo primero.',
            ];
        }

        $nivelConfig['modelo']::find($id)?->delete();

        return ['estado' => 'exito', 'mensaje' => '¡Eliminado correctamente!'];
    }

    /**
     * Elimina un documento (nivel hoja) y su archivo físico.
     */
    public function onEliminarDocumento()
    {
        $data = Input::all();
        $cadena = $this->obtenerCadena($data['modo'] ?? '');
        $config = $cadena['documentos'];

        $documento = $config['modelo']::find(intval($data['id']));

        if ($documento) {
            Storage::delete('uploads/public/' . $config['carpeta'] . '/' . $documento->archivo);
            $documento->delete();
        }

        return ['estado' => 'exito', 'mensaje' => '¡Documento eliminado!'];
    }

    private function obtenerCadena(string $modo): array
    {
        $cadena = self::cadenas()[$modo] ?? null;

        if (!$cadena) {
            throw new ValidationException(['modo' => 'Modo inválido.']);
        }

        return $cadena;
    }

    private function validar($data, $campo, $nivelConfig = [])
    {
        $reglas = [$campo => ['required', 'string', 'min:2', 'max:100']];
        $mensajes = [
            $campo . '.required' => '* Campo obligatorio.',
            $campo . '.min' => 'Mínimo 2 caracteres.',
            $campo . '.max' => 'Máximo 100 caracteres.',
        ];

        if (!empty($nivelConfig['alfanumerico'])) {
            $reglas[$campo][] = 'regex:/^[\pL\pN\s._\-]+$/u';
            $mensajes[$campo . '.regex'] = 'Solo se permiten letras, números y espacios.';
        }

        $validator = Validator::make($data, $reglas, $mensajes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Búsqueda global: encuentra documentos en cualquier parte de la cadena,
     * con su ruta completa y la información para navegar hasta su carpeta.
     */
    public function onBuscarGlobal()
    {
        $data = Input::all();
        $cadena = $this->obtenerCadena($data['modo'] ?? '');
        $termino = trim($data['q'] ?? '');

        if (mb_strlen($termino) < 2) {
            return ['items' => []];
        }

        $config = $cadena['documentos'];
        $modelo = $config['modelo'];

        // Relaciones para reconstruir la ruta (nombres tal cual en los modelos viejos).
        $relaciones = $data['modo'] === 'fabio'
            ? ['Folder.Carpeta.Gaveta.Archivero']
            : ['Coleccion.Anaquel.Estante'];

        $documentos = $modelo::with($relaciones)
            ->where($config['campo'], 'ilike', '%' . $termino . '%')
            ->limit(60)
            ->get();

        $items = $documentos->map(function ($doc) use ($data, $config) {
            if ($data['modo'] === 'fabio') {
                $folder = $doc->Folder;
                $carpeta = optional($folder)->Carpeta;
                $gaveta = optional($carpeta)->Gaveta;
                $archivero = optional($gaveta)->Archivero;

                $ruta = array_filter([
                    $archivero ? 'Archivero ' . $archivero->codigo : null,
                    $gaveta ? 'Gaveta ' . $gaveta->codigo : null,
                    $carpeta ? $carpeta->nombre : null,
                    $folder ? $folder->nombre : null,
                ]);

                $navegacion = [
                    ['nivel' => 0, 'id' => optional($archivero)->id, 'nombre' => optional($archivero)->codigo],
                    ['nivel' => 1, 'id' => optional($gaveta)->id, 'nombre' => optional($gaveta)->codigo],
                    ['nivel' => 2, 'id' => optional($carpeta)->id, 'nombre' => optional($carpeta)->nombre],
                    ['nivel' => 3, 'id' => optional($folder)->id, 'nombre' => optional($folder)->nombre],
                ];
                $nivelHoja = 4;
                $parentId = optional($folder)->id;
            } else {
                $coleccion = $doc->Coleccion;
                $anaquel = optional($coleccion)->Anaquel;
                $estante = optional($anaquel)->Estante;

                $ruta = array_filter([
                    $estante ? 'Estante ' . $estante->codigo : null,
                    $anaquel ? 'Anaquel ' . $anaquel->codigo : null,
                    $coleccion ? $coleccion->nombre : null,
                ]);

                $navegacion = [
                    ['nivel' => 0, 'id' => optional($estante)->id, 'nombre' => optional($estante)->codigo],
                    ['nivel' => 1, 'id' => optional($anaquel)->id, 'nombre' => optional($anaquel)->codigo],
                    ['nivel' => 2, 'id' => optional($coleccion)->id, 'nombre' => optional($coleccion)->nombre],
                ];
                $nivelHoja = 3;
                $parentId = optional($coleccion)->id;
            }

            return [
                'id' => $doc->id,
                'nombre' => $doc->{$config['campo']},
                'ruta' => implode('  ›  ', $ruta),
                'url' => 'storage/app/uploads/public/' . $config['carpeta'] . '/' . $doc->archivo,
                'icono' => $this->iconoParaArchivo($doc->archivo),
                'navegacion' => array_values($navegacion),
                'nivelHoja' => $nivelHoja,
                'parentId' => $parentId,
            ];
        })->filter(fn ($i) => $i['parentId'])->values();

        return ['items' => $items];
    }

    private function guardarArchivo($archivo, $carpeta, $anterior = null)
    {
        $uploadPath = 'storage/app/uploads/public/' . $carpeta . '/';

        if ($anterior) {
            Storage::delete('uploads/public/' . $carpeta . '/' . $anterior);
        }

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $nombreArchivo = time() . '_' . $archivo->getClientOriginalName();
        $archivo->move($uploadPath, $nombreArchivo);

        return $nombreArchivo;
    }

    private function generarURL()
    {
        return password_hash(md5(uniqid()), PASSWORD_BCRYPT);
    }

    private function iconoParaArchivo($archivo)
    {
        $ext = strtolower(pathinfo($archivo ?? '', PATHINFO_EXTENSION));

        return match ($ext) {
            'pdf' => 'bi-file-earmark-pdf-fill text-danger',
            'doc', 'docx' => 'bi-file-earmark-word-fill text-primary',
            'xls', 'xlsx' => 'bi-file-earmark-excel-fill text-success',
            'ppt', 'pptx' => 'bi-file-earmark-ppt-fill text-warning',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'bi-file-earmark-image-fill text-info',
            'zip', 'rar', '7z' => 'bi-file-earmark-zip-fill text-secondary',
            default => 'bi-file-earmark-fill text-muted',
        };
    }
}
