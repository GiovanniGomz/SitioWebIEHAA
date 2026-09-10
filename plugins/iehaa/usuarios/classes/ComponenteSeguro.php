<?php

namespace Iehaa\Usuarios\Classes;

use Winter\Storm\Exception\ValidationException;
use Winter\Storm\Exception\ApplicationException;

/**
 * Envuelve la lógica de los handlers AJAX de los componentes del panel para
 * que cualquier error se convierta en un mensaje claro para el usuario en
 * lugar de una pantalla de error 500 que "rompe" el sistema.
 *
 * Uso dentro de un componente:
 *
 *   use Iehaa\Usuarios\Classes\ComponenteSeguro;
 *
 *   class MiComponente extends ComponentBase
 *   {
 *       use ComponenteSeguro;
 *
 *       public function onRegistrar()
 *       {
 *           return $this->seguro(function () {
 *               // ... lógica normal ...
 *           });
 *       }
 *   }
 *
 * Las ValidationException se dejan pasar tal cual para que el framework de
 * Winter pinte los errores campo por campo en el formulario.
 */
trait ComponenteSeguro
{
    protected function seguro(callable $accion)
    {
        try {
            return $accion();
        } catch (ValidationException $e) {
            throw $e;
        } catch (ApplicationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('[IEHAA] ' . static::class . ' :: ' . $e->getMessage(), [
                'archivo' => $e->getFile() . ':' . $e->getLine(),
            ]);

            throw new ApplicationException($this->mensajeAmigable($e));
        }
    }

    protected function mensajeAmigable(\Throwable $e): string
    {
        $msg = strtolower($e->getMessage());

        if (str_contains($msg, 'duplicate') || str_contains($msg, 'unique') || str_contains($msg, 'llave duplicada')) {
            return 'Ya existe un registro con esos datos. Revisá los campos que deben ser únicos.';
        }

        if (str_contains($msg, 'foreign key') || str_contains($msg, 'foreign-key') || str_contains($msg, 'llave foránea') || str_contains($msg, 'still referenced')) {
            return 'No se puede completar la acción porque este registro está siendo utilizado por otra información del sistema.';
        }

        if (str_contains($msg, 'sqlstate') || str_contains($msg, 'column') || str_contains($msg, 'relation')) {
            return 'Ocurrió un problema al guardar la información. Intentá de nuevo o contactá al administrador.';
        }

        return 'No se pudo completar la acción. Verificá los datos e intentá nuevamente.';
    }
}
