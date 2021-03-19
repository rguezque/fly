# fly
 Simple router PHP No-POO

## Routing

Cada ruta se compone de un nombre único, el *string* de la ruta y un callback. Dependiendo del método de petición que aceptará la ruta será la función a utilizar. Los métodos aceptados son `GET`, `POST`, `PUT` y `DELETE` que corresponden con las funciones `get()`, `post()`, `put()` y `delete()`.

```php
?php

require __DIR__.'/vendor/autoload.php';

use function fly\dispatch;
use function fly\get;
use function fly\with_namespace;
use function helper\preformat;
use function http\json_response;

get('homepage', '/', function() {
    $data = [
        'greeting' => 'Hola',
        'name' => 'John',
        'lastname' => 'Doe'
    ];
    
    json_response($data);
});

with_namespace('/foo', function() {
    get('foo_index', '/', function() {
        echo 'Foo';
    });

    get('foo_bar', '/bar', function() {
        echo 'Bar';
    }, 'foo_bar_page');

    get('foo_goo', '/goo', function() {
        echo 'Goo';
    });
});

get('baz_route', '/baz', function() {
    echo 'Baz';
});

get('hola_route', '/hola/{nombre}', function(array $args) {
    printf('Hola %s.', $args['nombre']);
});

try {
    dispatch();
} catch(RuntimeException $e) {
    echo preformat($e->getMessage());
}

?>
```

### Groups

Agrupa rutas con un *namespace*.

```php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\dispatch;
use function fly\get;
use function fly\with_namespace ;

get('index', '/', function() {
    echo 'hola mundo';
});

with_namespace('/foo', function() {
    get('index_foo', '/', function() {
        echo 'Foo';
    });

    get('foo_bar', '/bar', function() {
        echo 'Bar';
    });

    get('foo_goo', '/goo', function() {
        echo 'Goo';
    });
});

dispatch();

?>
```

Lo anterior genera las rutas:

```
/
/foo/
/foo/bar
/foo/goo
```

## Basepath

Define un directorio base para el router si este se aloja en un subdirectorio del *server*.

```php
use function fly\set_basepath;

set_basepath('/subdirectorio-router');
```

## Redirect

Devuelve una redireccion a otra URI.

```php
use function http\redirect_response;
use function fly\generate_uri;
use function fly\get;
use function fly\dispatch;

get('index', '/', function() {
    // A una ruta según su nombre
    redirect_response(generate_uri('foo_page'));
    // A una URI
    redirect_response('https://www.fakesite.foo');
});

get('foo_page', '/foo', function() {
    echo 'Foo';
});

dispatch();
```

## Generate URI

Genera la URI correspondiente de una ruta según su nombre.

```php
// Ruta '/'
generate_uri('homepage');
// Enviando parámetros para la ruta '/show/{id}'
generate_uri('show_page', ['id' => 9]);
```

## Templates

```php
// index.php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\{get, dispatch};
use function template\{set_views_path, template};

set_views_path(__DIR__.'/views');

get('index', '/', function() {
    template('homepage', ['mensaje' => 'Hola mundo']);
});

dispatch();
?>
```

```php
// /views/homepage.php
<?php
    echo $mensaje;
?>
```

## PDO

```php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\{get, dispatch};
use function connection\pdo_connection;

get('index', '/', function() {
	$db = pdo_connection('mysql:host=localhost;port=3306;dbname=nombre_bd;charset=utf8', 'nombre_usuario', 'clave_acceso', [/*...opciones...*/]);
    //...
});

dispatch();
```

## Request/Response

- `get_server_params()`: Devuelve el array $_SERVER
- `get_query_params()`: Devuelve el array $_GET
- `get_cookie_params()`: Devuelve el array $_COOKIE
- `unsetcookie(string $name)`: Elimina una cookie.
- `get_request_params()`: Devuelve el array $_POST
- `get_files_params()`: Devuelve el array $_FILES
- `get_globals_params()`: Devuelve el array $GLOBALS
- `setglobal(string $name, $value)`: Crea una variable global.
- `getglobal(string $name)`: Devuelve una variable global.
- `response(string $body = '', int $code = HTTP_OK, ?array $header = null)`: Devuelve una respuesta HTTP.
- `json_response(array $data)`: Devuelve una respuesta HTTP en formato json
- `redirect_response(string $uri)`: Redirecciona a otra ruta o URL.




