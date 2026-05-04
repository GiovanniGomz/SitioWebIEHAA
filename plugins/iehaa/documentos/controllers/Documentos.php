<?php

namespace IEHAA\Documentos\Controllers;

use Backend\Classes\Controller;
use Backend\Facades\BackendMenu;

/**
 * Documentos Backend Controller
 */
class Documentos extends Controller
{
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    /**
     * @var array Behaviors that are implemented by this controller.
     */
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class,
    ];

    /**
     * @var array Permissions required to view this page.
     */
    protected $requiredPermissions = [
        'iehaa.documentos.documentos.manage_all',
    ];
}
