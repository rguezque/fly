<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

/**
 * Funciones del router
 * 
 * @function set_basepath(string $path): void
 * @function get_basepath(): string
 * @function get(string $path, $callback): void
 * @function post(string $path, $callback): void
 * @function generate_uri(string $route_name): ?string
 * @function dispatch()
 */

namespace fly;

use RuntimeException;
use function http\{setglobal, getglobal, get_globals_params, get_server_params};

/**
 * Métodos aceptados
 * 
 * @global string[]
 */
const ALLOWED_METHODS = ['GET', 'POST'];

/**
 * Colección de rutas
 */
setglobal('routes', array());

/**
 * Colección de URI de cada ruta
 */
setglobal('routes_namepath', array());

/**
 * Define el subdirectorio donde se aloja el router
 * 
 * @param string $path Ruta del ubdirectorio
 * @return void
 */
function set_basepath(string $path): void {
    setglobal('BASEPATH', sprintf('/%s', trim($path, '/\\')));
}

/**
 * Devuelve la ruta del subdirectorio donde se aloja el router
 * 
 * @param void
 * @return string
 */
function get_basepath(): string {
    $globals = get_globals_params();

    return isset($globals['BASEPATH']) ? $globals['BASEPATH'] : '';
}

/**
 * Ejecuta el router
 * 
 * @param void
 * @return void
 * @throws RuntimeException
 */
function dispatch() {
    // Variable bandera que asegura una sola ejecución de la función dispatch()
    static $invoke_once = false;

    if(!$invoke_once) {
        $server         = get_server_params();
        $request_uri    = $server['REQUEST_URI'];
        $request_method = $server['REQUEST_METHOD'];

        // Valida que el método de petición recibido sea soportado por el router
        if(!in_array($request_method, ALLOWED_METHODS)) {
            throw new RuntimeException(sprintf('El método de petición %s no está soportado.', $request_method));
        }

        $all_routes = getglobal('routes');
        $routes = $all_routes[$request_method];

        $found = false;

        foreach($routes as $route) {
            if($request_uri != '/') {
                $request_uri = rtrim($request_uri, '/');
            }
            
            $path = $route['path'];
            $path = glue(get_basepath(), '/', trim($path, '/\\'));
        
            if(preg_match(pattern($path), $request_uri, $arguments)) {
                array_shift($arguments);
        
                $found = true;
                $invoke_once = true;
                $callback = $route['callback'];
                return call_user_func($callback, ...$arguments);
            }
        }

        if(!$found) {
            throw new RuntimeException(sprintf('No se encontró la ruta solicitada "%s"', $request_uri));
        }
    }
}

/**
 * Mapea una ruta que solo acepta el método de petición GET
 * 
 * @param string $path Definición de ruta
 * @param mixed $callback Controlador de la ruta
 * @param string $name Nombre de la ruta
 * @return void
 */
function get(string $path, $callback, ?string $name = null): void {
    // Guarda la ruta en la colección de rutas
    $routes = (array) getglobal('routes');
    $routes['GET'][] = ['path' => $path, 'callback' => $callback];
    setglobal('routes', $routes);

    // Guarda o genera el nombre de la ruta
    save_route_name($name, $path);
}

/**
 * Mapea una ruta que solo acepta el método de petición POST
 * 
 * @param string $path Definición de ruta
 * @param mixed $callback Controlador de la ruta
 * @param string $name Nombre de la ruta
 * @return void
 */
function post(string $path, $callback, ?string $name = null): void {
    // Guarda la ruta en la colección de rutas
    $routes = (array) getglobal('routes');
    $routes['POST'][] = ['path' => $path, 'callback' => $callback];
    setglobal('routes', $routes);

    // Guarda o genera el nombre de la ruta
    save_route_name($name, $path);
}

/**
 * Guarda las rutas con un nombre definido o automático
 * 
 * @param string $name Nombre de la ruta
 * @param string $path URI de la ruta
 * @return void
 */
function save_route_name(?string $name, string $path): void {
    $name = $name ?? uniqid('fly_', true);
    $routes_path = getglobal('routes_namepath');
    $routes_path[$name] = $path;
    setglobal('routes_namepath', $routes_path);
}

/**
 * Recupera la URI de una ruta a partir de su nombre
 * 
 * @param string $route_name Nombre de la ruta
 * @return string
 */
function generate_uri(string $route_name): ?string {
    $path = getglobal('routes_namepath');

    return isset($path[$route_name]) ? glue(get_basepath(), '/', trim($path[$route_name], '/\\')) : null;
}

/**
 * Construye el patrón regex de la ruta
 * 
 * @param string $path Definición de la ruta
 * @return string
 */
function pattern(string $path): string {
    $parse_path = sprintf('/%s', trim($path, '/\\'));
    $parse_path = str_replace('/', '\/', $parse_path);

    return '#^' . $parse_path . '$#i';
}

/**
 * Une varias cadenas de texto o caracteres
 * 
 * @param mixed
 * @return string
 */
function glue(): string {
    $result = '';

    foreach(func_get_args() as $string) {
        $result .= $string;
    }

    return $result;
}

?>