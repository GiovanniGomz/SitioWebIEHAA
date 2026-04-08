<?php namespace Iehaa\Inventario\Components;

use Cms\Classes\ComponentBase;

class InventarioComponent extends ComponentBase
{
    /**
     * Gets the details for the component
     */
    public function componentDetails()
    {
        return [
            'name'        => 'InventarioComponent Component',
            'description' => 'No description provided yet...'
        ];
    }

    /**
     * Returns the properties provided by the component
     */
    public function defineProperties()
    {
        return [];
    }
}
