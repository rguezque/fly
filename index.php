<?php

require __DIR__.'/vendor/autoload.php';

use function fly\dispatch;
use function fly\get;
use function fly\with_prefix;
use function helper\preformatting;

get('/', function() {
    echo 'hola mundo';
}, 'homepage');

with_prefix('/foo', function() {
    get('/', function() {
        echo 'Foo';
    });

    get('/bar', function() {
        echo 'Bar';
    }, 'foo_bar_page');

    get('/goo', function() {
        echo 'Goo';
    });
});

get('/baz', function() {
    echo 'Baz';
});

get('/hola/(\w+)', function($name) {
    printf('Hola %s.', $name);
}, 'hola_page');

try {
    dispatch();
} catch(OutOfBoundsException $e) {
    echo preformatting($e->getMessage());
}

?>