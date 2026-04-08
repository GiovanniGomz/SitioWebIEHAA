<?php

namespace IEHAA\Documentos\Components;

use Cms\Classes\ComponentBase;
use IEHAA\Documentos\Models\Documento;

class DocumentoComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'documentoComponent',
            'description' => 'Modulo de documentos'
        ];
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->page['documentos'] = Documento::all();
    }

    public function onCreate()
    {
        $documento = new Documento();
        $documento->nombre = input('nombre');
        $documento->archivo = input('archivo');
        $documento->save();
    }
}
