<?php

/*
|--------------------------------------------------------------------------
| Configuración de dompdf (barryvdh/laravel-dompdf)
|--------------------------------------------------------------------------
|
| Winter CMS no tiene una carpeta "public/" estándar de Laravel, por lo que
| dompdf no puede resolver su "public path" y lanza:
|   RuntimeException: Cannot resolve public path
|
| Aquí partimos de la config por defecto del paquete y solo ajustamos la ruta
| pública a la raíz del proyecto (donde viven los assets del tema).
|
*/

$config = require base_path('vendor/barryvdh/laravel-dompdf/config/dompdf.php');

$config['public_path'] = base_path();

// Permitir cargar imágenes locales del tema (logo UES) en los reportes.
$config['options']['enable_remote'] = true;
$config['options']['chroot'] = realpath(base_path());
$config['options']['default_font'] = 'DejaVu Sans';

return $config;
