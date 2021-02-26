<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace helper;

/**
 * Concatena cadenas de texto o caracteres
 * 
 * @param string $vars Cadenas de texto separadas por comas
 * @return string
 */
function glue(string ...$vars): string {
    return implode('', $vars);
}

/**
 * Preformatea una cadena de texto
 * 
 * @param string $string Texto a preformatear
 * @return string
 */
function preformat(string $string): string {
    return sprintf('<pre>%s</pre>', $string);
}

/**
 * Vuelca una variable.
 * 
 * Vuelca información de una variable, en texto preformateado para
 * una mejor lectura de su cóntenido y opcionalmente termina el 
 * script actual.
 * 
 * @param mixed $var Variable a volcar
 * @param bool $exit Determina si se debe terminar el script después de volcar a variable
 * @return void
 */
function dump($var, bool $exit = true): void {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';

    if($exit) {exit();}
}

?>