<?php

namespace Iehaa\Respaldos\Components;

use Cms\Classes\ComponentBase;
use Iehaa\Bitacora\Models\Bitacora;
use Iehaa\Respaldos\Classes\ServicioRespaldo;
use Iehaa\Usuarios\Classes\CpanelAuth;
use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Support\Facades\Input;
use Winter\Storm\Support\Facades\Validator;

class RespaldoComponent extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'respaldoComponent',
            'description' => 'Respaldos de la base de datos'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        if (session()->has('error_descarga')) {
            $this->page['errorDescarga'] = session()->pull('error_descarga');
        }

        $this->cargarVista($this->page);
    }

    protected function cargarVista($destino): void
    {
        $destino['respaldos'] = ServicioRespaldo::listar();
        $destino['ajustes'] = ServicioRespaldo::ajustes();
        $destino['frecuencias'] = ServicioRespaldo::FRECUENCIAS;
        $destino['ultimoAutomatico'] = ServicioRespaldo::ultimoAutomatico();
    }

    protected function respuestaListado(array $extra = []): array
    {
        return array_merge([
            '#listado' => $this->renderPartial('@listado', [
                'respaldos' => ServicioRespaldo::listar(),
            ]),
            '#estado-automatico' => $this->renderPartial('@estado', [
                'ajustes' => ServicioRespaldo::ajustes(),
                'frecuencias' => ServicioRespaldo::FRECUENCIAS,
                'ultimoAutomatico' => ServicioRespaldo::ultimoAutomatico(),
            ]),
        ], $extra);
    }

    public function onRespaldar()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        try {
            $nombre = ServicioRespaldo::crear('manual', CpanelAuth::usuario());
        } catch (\RuntimeException $e) {
            return $this->respuestaListado(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }

        return $this->respuestaListado([
            'estado' => 'exito',
            'mensaje' => '¡Respaldo creado correctamente!',
            'archivo' => $nombre,
        ]);
    }

    public function onEliminar()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $nombre = (string) post('archivo');
        $ruta = ServicioRespaldo::rutaDe($nombre);

        if (!$ruta) {
            return $this->respuestaListado(['estado' => 'error', 'mensaje' => 'El respaldo ya no existe.']);
        }

        @unlink($ruta);
        Bitacora::registrar('eliminar', 'Respaldos', "Eliminó el respaldo {$nombre}");

        return $this->respuestaListado(['estado' => 'exito', 'mensaje' => '¡Eliminado con exito!']);
    }

    public function onGuardarAjustes()
    {
        if (!CpanelAuth::esAdmin()) {
            return redirect('/dashboard');
        }

        $data = Input::all();

        $validator = Validator::make($data, [
            'frecuencia' => ['required', 'integer', 'in:' . implode(',', array_keys(ServicioRespaldo::FRECUENCIAS))],
            'retencion' => ['required', 'integer', 'min:1', 'max:365'],
        ], [
            'frecuencia.required' => '* Campo obligatorio.',
            'frecuencia.in' => 'Elegí una de las opciones disponibles.',
            'retencion.required' => '* Campo obligatorio.',
            'retencion.integer' => 'Ingresá un número entero.',
            'retencion.min' => 'Debe conservar al menos 1 respaldo.',
            'retencion.max' => 'El máximo es 365 respaldos.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $anterior = ServicioRespaldo::ajustes();
        $automatico = isset($data['automatico']);

        ServicioRespaldo::guardarAjustes($automatico, (int) $data['frecuencia'], (int) $data['retencion']);
        ServicioRespaldo::aplicarRetencion();

        $nuevo = ServicioRespaldo::ajustes();
        if ($anterior !== $nuevo) {
            $texto = $nuevo['automatico']
                ? 'automático ' . mb_strtolower(ServicioRespaldo::FRECUENCIAS[$nuevo['frecuencia']]) . ', conserva ' . $nuevo['retencion']
                : 'automático desactivado';
            Bitacora::registrar('modificar', 'Respaldos', "Cambió la configuración de respaldos ({$texto})");
        }

        return $this->respuestaListado(['estado' => 'exito', 'mensaje' => '¡Modificado correctamente!']);
    }
}
