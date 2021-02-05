# fly
 Simple router PHP No-POO

## Routing

```php
<?php

require __DIR__.'/vendor/autoload.php';

use function fly\{get, dispatch};
use function http\{response, get_server_params};
use const http\{HTTP_OK, HTTP_NOT_FOUND, HTTP_STATUS_TEXT};


get('/', function() {
    echo 'Hola mundo';
});

get('/foo', function() {
    $data = [
        'name' => 'John',
        'lastname' => 'Doe'
    ];
    response(json_encode($data), HTTP_OK, ['Content-Type', 'application/json']);
});

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

## Template

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
use connection/pdo_mysql;

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




