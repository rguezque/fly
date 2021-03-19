<?php declare(strict_types = 1);
/**
 * @author    Luis Arturo Rodríguez
 * @copyright Copyright (c) 2021 Luis Arturo Rodríguez <rguezque@gmail.com>
 * @license   https://opensource.org/licenses/MIT    MIT License
 */

namespace connection;
/**
 * Funciones de conexión PDO MySQL
 * 
 * @method PDO pdo_connection(string $dsn, string $username, string $password, array $options = []) Devuelve una conexión PDO
 */

use PDO;
use PDOException;

/**
 * Devuelve una conexión PDO
 * 
 * @param string $dsn Parámetros de conexión
 * @param string $username Nombre de usuario de la BD
 * @param string $password Contraseña de acceso a la BD
 * @param array $options Opciones adicionales de la conexión
 * @return PDO
 * @throws PDOException
 */
function pdo_connection(string $dsn, string $username, string $password, array $options = []): PDO {
    $options = !empty($options) ?: [PDO::ATTR_PERSISTENT => true];

    try {
        return new PDO($dsn, $username, $password, $options);
    } catch(PDOException $e) {
        exit(utf8_encode(print_r($e, true)));
    }
}

?>