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
 * @method void set_basepath(string $path) Define un directorio base donde se ubica el router.
 * @method string get_basepath() Devuelve el string del directorio base.
 * @method void get(string $path, $callback, ?string $name = null) Agrega una ruta GET.
 * @method void post(string $path, $callback, ?string $name = null) Agrega una ruta POST.
 * @method void with_namespace(string $namespace, Closure $closure) Define grupos de rutas bajo un namespace de ruta en común.
 * @method string generate_uri(string $route_name) Genera una URI de una ruta nombrada.
 * @method void dispatch() Despacha el enrutador.
 */

use ArgumentCountError;
use Closure;
use InvalidArgumentException;
use LogicException;
use OutOfBoundsException;
use RuntimeException;

use function helper\add_leading_slash;
use function helper\glue;
use function helper\is_assoc_array;
use function helper\remove_trailing_slash;
use function helper\str_path;
use function http\setglobal;
use function http\getglobal;
use function http\get_server_params;

/**
 * Métodos aceptados
 * 
 * @var string[]
 */
const ALLOWED_METHODS = ['GET', 'POST'];

/**
 * Colección de rutas
 */
setglobal('ROUTES', array());

/**
 * Colección de URI de cada ruta
 */
setglobal('ROUTE_NAMES', array());

/**
 * Define un namespace para un grupo de rutas
 */
setglobal('NAMESPACE', '');

/**
 * Define el subdirectorio donde se aloja el router
 * 
 * @param string $path Ruta del ubdirectorio
 * @return void
 */
function set_basepath(string $path): void {
    setglobal('BASEPATH', add_leading_slash(remove_trailing_slash($path)));
}

/**
 * Devuelve la ruta del subdirectorio donde se aloja el router
 * 
 * @return string
 */
function get_basepath(): string {
    return getglobal('BASEPATH') ?? '';
}

/**
 * Ejecuta el router
 * 
 * @return void
 * @throws RuntimeException
 */
function dispatch(): void {
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

        // Dependiendo del método de petición se elige el array correspondiente de rutas
        $all_routes = getglobal('ROUTES');
        $routes = $all_routes[$request_method];

        // El slash al final no se toma en cuenta
        $request_uri = ('/' !== $request_uri) ? remove_trailing_slash($request_uri) : $request_uri;

        $found = false;

        foreach($routes as $route) {
            // Prepara el string de la ruta
            $path = $route['path'];
            $path = glue(get_basepath(), str_path($path));
        
            if(preg_match(route_pattern($path), $request_uri, $arguments)) {
                array_shift($arguments);
        
                $found = true;
                $invoke_once = true;
                $callback = $route['callback'];
                
                call_user_func($callback, $arguments);
                return;
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
 * @param string $name Nombre de la ruta
 * @param string $path Definición de ruta
 * @param mixed $callback Controlador de la ruta
 * @return void
 */
function get(string $name, string $path, $callback): void {
    route('GET', $name, $path, $callback);
}

/**
 * Mapea una ruta que solo acepta el método de petición POST
 * 
 * @param string $name Nombre de la ruta
 * @param string $path Definición de ruta
 * @param mixed $callback Controlador de la ruta
 * @return void
 */
function post(string $name, string $path, $callback): void {
    route('POST', $name, $path, $callback);
}

/**
 * Mapea una ruta que solo acepta el método de petición PUT
 * 
 * @param string $name Nombre de la ruta
 * @param string $path Definición de ruta
 * @param mixed $callback Controlador de la ruta
 * @return void
 */
function put(string $name, string $path, $callback): void {
    route('PUT', $name, $path, $callback);
}

/**
 * Mapea una ruta que solo acepta el método de petición DELETE
 * 
 * @param string $name Nombre de la ruta
 * @param string $path Definición de ruta
 * @param mixed $callback Controlador de la ruta
 * @return void
 */
function delete(string $name, string $path, $callback): void {
    route('DELETE', $name, $path, $callback);
}

/**
 * Mapea una ruta
 * 
 * @param string $method Método de petición
 * @param string $name Nombre de la ruta
 * @param string $path Definición de ruta
 * @param mixed $callback Controlador de la ruta
 * @return void
 * @throws RuntimeException
 * @throws LogicException
 */
function route(string $method, string $name, string $path, $callback): void {
    // Valida que el método de petición recibido sea soportado por el router
    if(!in_array($method, ALLOWED_METHODS)) {
        throw new RuntimeException(sprintf('El método de petición %s no está soportado en la definición de la ruta %s:"%s".', $method, $name, $path));
    }

    // Verifica si ya existe una ruta con el mismo nombre
    if(array_key_exists($name, getglobal('ROUTE_NAMES'))) {
        throw new LogicException(sprintf('Ya existe una ruta con el nombre "%s".', $name));
    }

    $path = str_path($path);
    $path = glue(getglobal('NAMESPACE'), $path);
    // Guarda la ruta en la colección de rutas
    $routes = (array) getglobal('ROUTES');
    $routes[$method][] = ['path' => $path, 'callback' => $callback];
    setglobal('ROUTES', $routes);

    // Guarda o genera el nombre de la ruta
    save_route_name($path, $name);
}

/**
 * Define grupos de rutas con namespace
 * 
 * @param string $namespace Namespace del grupo
 * @param Closure $closure Calllback con la definición de rutas
 * @return void
 */
function with_namespace(string $namespace, Closure $closure): void {
    $namespace = str_path($namespace);
    setglobal('NAMESPACE', $namespace);
    $closure();
    setglobal('NAMESPACE', '');
}

/**
 * Guarda las rutas con un nombre definido o automático
 * 
 * @param string $path URI de la ruta
 * @param string $name Nombre de la ruta
 * @return void
 */
function save_route_name(string $path, ?string $name = null): void {
    $name = $name ?? uniqid('fly_', true);
    $routes_path = getglobal('ROUTE_NAMES');
    $routes_path[$name] = $path;
    setglobal('ROUTE_NAMES', $routes_path);
}

/**
 * Genera la URI de una ruta a partir de su nombre y parámetros
 * 
 * @param string $route_name Nombre de la ruta
 * @param array $params Parámetros a ser cazados con los wildcards de la ruta
 * @return string
 * @throws OutOfBoundsException
 * @throws InvalidArgumentException
 * @throws ArgumentCountError
 */
function generate_uri(string $route_name, array $params = []): string {
    $route_names = getglobal('ROUTE_NAMES');

    if(!array_key_exists($route_name, $route_names)) {
        throw new OutOfBoundsException(sprintf('No existe una ruta con el nombre "%s".', $route_name));
    }
    
    $path = $route_names[$route_name];

    if(!empty($params)) {
        if(!is_assoc_array($params)) {
            throw new InvalidArgumentException(sprintf('Se esperaba un array asociativo. Las claves deben coincidir con los wildcards de la ruta "%s".', $route_name));
        }

        $path = preg_replace_callback('#{(\w+)}#', function($match) use($route_name, $path, $params) {
            $key = $match[1];
            if(!array_key_exists($key, $params)) {
                throw new ArgumentCountError(sprintf('Parámetros insuficientes al intentar generar la URI para la ruta %s:"%s".', $route_name, $path));
            }
            
            return $params[$key];
        },$path);
    }

    return $path;
}

/**
 * Construye el patrón regex de la ruta
 * 
 * @param string $path Definición de la ruta
 * @return string
 */
function route_pattern(string $path): string {
    $parse_path = str_path($path);
    $parse_path = str_replace('/', '\/', $parse_path);
    $parse_path = preg_replace('#{(\w+)}#', '(?<$1>\w+)', $parse_path);

    return '#^'.$parse_path.'$#i';
}

?>