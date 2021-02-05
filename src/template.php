<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

/**
 * Funciones de template
 * 
 * @function set_views_path(string $path): void
 * @function get_views_path(): string
 * @function template(string $template, array $arguments = []): void
 */

namespace template;

use RuntimeException;
use function http\{setglobal, get_globals_params};

/**
 * Asigna la ruta al directorio de plantillas
 * 
 * @param string $path Ruta al directorio
 * @return void
 */
function set_views_path(string $path): void {
    setglobal('VIEWS_PATH', rtrim($path, '/\\'));
}

/**
 * Devuelve la ruta al directorio de plantillas
 * 
 * @param void
 * @return string
 */
function get_views_path(): string {
    $globals = get_globals_params();

    return isset($globals['VIEWS_PATH']) ? $globals['VIEWS_PATH'] : '';
}

/**
 * Renderiza una plantilla
 * 
 * @param string $template Nombre de la plantilla
 * @param array $arguments Parámetros pasados a la plantilla
 * @return void
 * @throws RuntimeException
 */
function template(string $template, array $arguments = []): void {
    if($extension_start = strpos($template, '.php')) {
        $template = substr($template, 0, $extension_start);
    }

    $template .= '.php';
    $template = '/'.ltrim($template, '/\\');
    $template = get_views_path() . $template;

    if(!file_exists($template)) {
        throw new RuntimeException(sprintf('No se encontró el archivo de plantilla "%s"', $template));
    }
    
    extract($arguments);
    include $template;
}

?>