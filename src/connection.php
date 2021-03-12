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
 * @method ?PDO pdo_mysql(string $string_url, ?array $options = null)
 */

use PDO;
use PDOException;

/**
 * Devuelve una conexión MYSQL
 * 
 * @param string $string_url Una cadena con la siguiente estructura: 'mysql://user:pass@127.0.0.1:3306/dbname?charset=utf8&persistent=true'
 * @param array $options Opciones adicionales de la conexión
 * @return PDO
 * @throws PDOException
 */
function pdo_mysql(string $string_url, ?array $options = null): ?PDO {
    $connection = null;
    
    $params = parse_url($string_url);
    
    $dsn = isset($params['scheme']) ? sprintf('%s:', $params['scheme']) : 'mysql:';

    // Host
    if(isset($params['host'])) {
        $dsn .= sprintf('host=%s;', $params['host']);
    }

    // Port
    if(isset($params['port'])) {
        $dsn .= sprintf('port=%s;', $params['port']);
    }

    // Database name
    if(isset($params['path'])) {
        $dsn .= sprintf('dbname=%s;', trim($params['path'], '/\\'));
    }

    if(isset($params['query'])) {
        parse_str($params['query'], $result);
        
        // Charset
        if(isset($result['charset'])) {
            $dsn .= sprintf('charset=%s;', $result['charset']);
        }

        // Persistent connection option
        if(isset($result['persistent'])) {
            $persistent = [PDO::ATTR_PERSISTENT => $result['persistent']];
            
            if(!array_key_exists(PDO::ATTR_PERSISTENT, (array)$options)) {
                $options = array_merge((array)$options, $persistent);
            }
        } 
    }

    // Username
    $username = (string) $params['user'];
    // Password
    $password = (string) $params['pass'];

    try {
        $connection = new PDO($dsn, $username, $password, $options);
    } catch(PDOException $e) {
        printf('%s in file <b>%s</b> on line <b>%s</b><pre>%s</pre>', $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    }
    
    return $connection;
}

?>