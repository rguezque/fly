<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace fly;
/**
 * Funciones del router
 * 
 * @function set_basepath(string $path): void Define un directorio base donde se ubica el router.
 * @function get_basepath(): string Devuelve el string del directorio base.
 * @function get(string $path, $callback, ?string $name = null): void Agrega una ruta GET.
 * @function post(string $path, $callback, ?string $name = null): void Agrega una ruta POST.
 * @function with_prefix(string $prefix, Closure $closure): void Define grupos de rutas bajo un prefijo de ruta en común.
 * @function generate_uri(string $route_name): ?string Genera una URI de una ruta nombrada.
 * @function dispatch() Despacha el enrutador.
 */

use Closure;
use OutOfBoundsException;
use RuntimeException;

use function helper\glue;
use function http\setglobal;
use function http\getglobal;
use function http\get_server_params;

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
 * Define un prefijo para un grupo de rutas
 */
setglobal('actual_prefix', '');

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
    return getglobal('BASEPATH') ?? '';
}

/**
 * Ejecuta el router
 * 
 * @param void
 * @return void
 * @throws RuntimeException
 * @throws OutOfBoundsException
 */
function dispatch() {
    // Variable bandera que asegura una sola ejecución de la función fly\dispatch()
    static $invoke_once = false;

    if(!$invoke_once) {
        $server         = get_server_params();
        $request_uri    = $server['REQUEST_URI'];
        $request_method = $server['REQUEST_METHOD'];

        // Valida que el método de petición recibido sea soportado por el router
        if(!in_array($request_method, ALLOWED_METHODS)) {
            throw new RuntimeException(sprintf('El método de petición %s no está soportado.', $request_method));
        }

        // Dependiendo del método de petición se elige el array (GET o POST) correspondiente de rutas
        $all_routes = getglobal('routes');
        $routes = $all_routes[$request_method];

        $found = false;

        foreach($routes as $route) {
            // El slash al final no se toma en cuenta
            if($request_uri != '/') {
                $request_uri = rtrim($request_uri, '/');
            }
            
            // Prepara el string de la ruta
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
            throw new OutOfBoundsException(sprintf('No se encontró la ruta solicitada "%s"', $request_uri));
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
    $path = glue('/', trim($path, '/\\'));
    $path = glue(getglobal('actual_prefix'), $path);
    // Guarda la ruta en la colección de rutas
    $routes = (array) getglobal('routes');
    $routes['GET'][] = ['path' => $path, 'callback' => $callback];
    setglobal('routes', $routes);

    // Guarda o genera el nombre de la ruta
    save_route_name($path, $name);
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
    $path = glue('/', trim($path, '/\\'));
    $path = glue(getglobal('actual_prefix'), $path);
    // Guarda la ruta en la colección de rutas
    $routes = (array) getglobal('routes');
    $routes['POST'][] = ['path' => $path, 'callback' => $callback];
    setglobal('routes', $routes);

    // Guarda o genera el nombre de la ruta
    save_route_name($path, $name);
}

/**
 * Define grupos de rutas bajo un prefijo de ruta en común
 * 
 * @param string $prefix Prefijo del grupo
 * @param Closure $closure Calllback con la definición de rutas
 * @return void
 */
function with_prefix(string $prefix, Closure $closure): void {
    $prefix = glue('/', trim($prefix, '/\\'));
    setglobal('actual_prefix', $prefix);
    $closure();
    setglobal('actual_prefix', '');
}

/**
 * Guarda las rutas con un nombre definido o automático
 * 
 * @param string $path URI de la ruta
 * @param string $name Nombre de la ruta
 * @return void
 */
function save_route_name(string $path, ?string $name): void {
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

    return isset($path[$route_name]) ? glue(get_basepath(), $path[$route_name]) : null;
}

/**
 * Construye el patrón regex de la ruta
 * 
 * @param string $path Definición de la ruta
 * @return string
 */
function pattern(string $path): string {
    $parse_path = glue('/', trim($path, '/\\'));
    $parse_path = str_replace('/', '\/', $parse_path);

    return '#^' . $parse_path . '$#i';
}

?>