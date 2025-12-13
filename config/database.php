<?php
/**
 * Configuración de la Base de Datos
 */

// Configuración para desarrollo
define('DB_HOST', 'localhost');
define('DB_NAME', 'tienda_db');
define('DB_USER', 'root');
define('DB_PASS', '123456');
define('DB_CHARSET', 'utf8mb4');

// Configuración para producción (comentado por defecto)
/*
define('DB_HOST', 'tu_servidor_produccion');
define('DB_NAME', 'tu_base_datos');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_contraseña');
define('DB_CHARSET', 'utf8mb4');
*/
?>