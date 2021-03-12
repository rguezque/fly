<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace template;
/**
 * Funciones de template
 * 
 * @method void set_views_path(string $path)
 * @method string get_views_path()
 * @method void template(string $template, array $arguments = [])
 */

use RuntimeException;

use function helper\add_leading_slash;
use function http\setglobal;
use function http\getglobal;

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
    return getglobal('VIEWS_PATH') ?? '';
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
    $template = add_leading_slash($template);
    $template = get_views_path() . $template;

    if(!file_exists($template)) {
        throw new RuntimeException(sprintf('No se encontró el archivo de plantilla "%s"', $template));
    }
    
    extract($arguments);
    include $template;
}

?>