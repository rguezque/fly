# fly
 Simple router PHP No-POO

## Routing

```php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\{get, dispatch};
use function http\{response, get_server_params};
use const http\{HTTP_OK, HTTP_NOT_FOUND, HTTP_STATUS_TEXT};

// Ejemplo de una ruta nombrada, 'homepage'.
get('/', function() {
    echo 'Hola mundo';
}, 'homepage');

get('/foo', function() {
    $data = [
        'name' => 'John',
        'lastname' => 'Doe'
    ];
    response(json_encode($data), HTTP_OK, ['Content-Type', 'application/json']);
}, 'foo_page');

get('/hola/(\w+)/(\w+)', function($name, $lastname) {
    printf('Hola %s %s', $name, $lastname);
});

try {
    dispatch();
} catch(RuntimeException $e) {
    $server = get_server_params();
    response(sprintf('<h1>%s %s %s</h1>', $server['SERVER_PROTOCOL'], HTTP_NOT_FOUND, HTTP_STATUS_TEXT[HTTP_NOT_FOUND]), HTTP_NOT_FOUND);
}

?>
```

### Rutas nombradas

Este parámetro es opcional.

```php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\{get, dispatch};

// Ejemplo de una ruta nombrada, 'homepage'.
get('/', function() {
    echo 'Hola mundo';
}, 'homepage');

// Otra ruta con nombre
get('/foo', function() {
    $data = [
        'name' => 'John',
        'lastname' => 'Doe'
    ];
    response(json_encode($data), HTTP_OK, ['Content-Type', 'application/json']);
}, 'foo_page');

dispatch();
?>
```

### Groups

```php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\dispatch;
use function fly\get;
use function fly\with_prefix;

get('/', function() {
    echo 'hola mundo';
});

with_prefix('/foo', function() {
    get('/', function() {
        echo 'Foo';
    });

    get('/bar', function() {
        echo 'Bar';
    });

    get('/goo', function() {
        echo 'Goo';
    });
});

get('/baz', function() {
    echo 'Baz';
});

dispatch();

?>
```

## Basepath

Define un directorio base para el router si este se aloja en un subdirectorio del *server*. Ejemplo:

```php
use function fly\set_basepath;

set_basepath('/subdirectorio-router');
```

## Redirect

Redirecciona a una ruta específica, a una ruta nombrada o una URI.

```php
use function http\redirect;
use function fly\generate_uri;
use function fly\get;
use function fly\dispatch;

get('/', function() {
    // A una ruta
    redirect('/foo/bar');
    // A una ruta nombrada
    
    redirect(generate_uri('foo_page'));
    // A una URI
    redirect('https://www.github.com/johndoe');
});

get('/foo', function() {
    echo 'Foo';
}, 'foo_page');

dispatch();
```

## Templates

```php
// index.php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\{get, dispatch};
use function template\{set_views_path, template};

set_views_path(__DIR__.'/views');

get('/', function() {
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
use function connection\pdo_mysql;

get('/', function() {
	$db = pdo_mysql('mysql://user:pass@127.0.0.1:3306/dbname?charset=utf8&persistent=true');
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
- `redirect(string $uri)`: Redirecciona a otra ruta o URL.




