<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Zona horaria
date_default_timezone_set('America/Asuncion');

// Configuración de errores (desarrollo)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// URL base de la aplicación
define('BASE_URL', 'http://localhost:8080/proyecto/public');
define('PUBLIC_URL', BASE_URL);

// Rutas del sistema
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('CONFIG_PATH', ROOT_PATH . '/config');

// Configuración de uploads
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');
define('MAX_FILE_SIZE', 5242880); // 5MB en bytes

// Configuración de paginación
define('DEFAULT_PER_PAGE', 12);
define('MAX_PER_PAGE', 100);

// Nombre de la aplicación
define('APP_NAME', 'Mi Tienda Online');
define('APP_VERSION', '1.0.0');

// Función helper para generar URLs
function url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

// Función helper para assets
function asset($path = '') {
    return PUBLIC_URL . '/' . ltrim($path, '/');
}
?>