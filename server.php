<?php

/**
 * Enrutador para el servidor de desarrollo (php artisan serve).
 *
 * `artisan serve` usa este archivo si existe. El enrutador por defecto de
 * Laravel entrega como estático CUALQUIER archivo que exista (incluido .env,
 * los respaldos de la base de datos o el código de vendor/). Aquí se replican
 * las reglas del .htaccess del proyecto: solo se sirven directamente los
 * archivos públicos (assets de temas/plugins/módulos y los uploads públicos);
 * todo lo demás lo resuelve el CMS (y responde 404 si no corresponde).
 */

$publicPath = getcwd();

// Se normalizan las barras repetidas (el helper |theme genera /themes/web//assets/...).
$uri = preg_replace('#/{2,}#', '/', str_replace('\\', '/', urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
)));

$esPublico = !str_contains($uri, '..') && preg_match(
    '#^/(?:'
    . 'storage/app/(?:uploads/public|media|resized)/'
    . '|storage/temp/public/'
    . '|themes/[^/]+/(?:assets|resources)/'
    . '|plugins/.+/(?:assets|resources)/'
    . '|modules/.+/(?:assets|resources)/'
    . '|\.well-known/'
    . ')#',
    $uri
);

if ($uri !== '/' && $esPublico && is_file($publicPath . $uri)) {
    return false;
}

// Cuando se pide un archivo existente que NO se sirve (p. ej. /.env), PHP
// fija SCRIPT_NAME a esa ruta y el CMS calcularía mal la URL base de los
// assets (y la dejaría grabada en la caché de plantillas). Se fuerza siempre
// el front controller.
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $publicPath . '/index.php';

require_once $publicPath . '/index.php';
