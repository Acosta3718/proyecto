<?php
/**
 * Router Principal
 * Punto de entrada de la aplicación
 */

// Cargar configuración
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Cargar controlador base
require_once __DIR__ . '/../app/controllers/BaseController.php';

// Obtener la URL solicitada
$request = $_SERVER['REQUEST_URI'];

// Remover dinámicamente la base del proyecto según BASE_URL
$basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '';
$basePath = rtrim($basePath, '/');
if (!empty($basePath) && strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}

// Obtener solo el path (sin query string)
$request = parse_url($request, PHP_URL_PATH);
$request = trim($request, '/');

// Separar la ruta en segmentos
$segments = explode('/', $request);
$controller = !empty($segments[0]) ? $segments[0] : 'producto';
$action = !empty($segments[1]) ? $segments[1] : 'index';
$params = array_slice($segments, 2);

// Manejo especial para la sección de administración con subcontroladores
if ($controller === 'admin') {
    // Ruta base: /admin -> dashboard
    $controllerName = 'AdminController';
    $controllerFile = __DIR__ . '/../app/controllers/AdminController.php';
    $action = !empty($segments[1]) ? $segments[1] : 'dashboard';
    $params = array_slice($segments, 2);

    // Si existe un submódulo (ej. /admin/productos), intentar cargar su controlador dedicado
    if (!empty($segments[1])) {
        $moduleSlug = $segments[1];
        $moduleName = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $moduleSlug)));
        $adminModule = 'Admin' . $moduleName . 'Controller';
        $adminModuleFile = __DIR__ . '/../app/controllers/' . $adminModule . '.php';

        if (file_exists($adminModuleFile)) {
            $controllerName = $adminModule;
            $controllerFile = $adminModuleFile;
            $action = !empty($segments[2]) ? $segments[2] : 'index';
            $params = array_slice($segments, 3);
        }
    }
} else {
    // Convertir nombre del controlador a formato clase
    $controllerName = ucfirst($controller) . 'Controller';
    $controllerFile = __DIR__ . '/../app/controllers/' . $controllerName . '.php';
}

// Verificar si existe el controlador
if (file_exists($controllerFile)) {
    require_once $controllerFile;
    
    if (class_exists($controllerName)) {
        $controllerInstance = new $controllerName();
        
        // Verificar si existe el método
        if (method_exists($controllerInstance, $action)) {
            // Llamar al método con los parámetros
            call_user_func_array([$controllerInstance, $action], $params);
        } else {
            // Método no encontrado
            http_response_code(404);
            echo "Error 404: Acción no encontrada";
        }
    } else {
        http_response_code(500);
        echo "Error 500: Clase de controlador no encontrada";
    }
} else {
    // Controlador no encontrado
    http_response_code(404);
    echo "Error 404: Controlador no encontrado - " . $controllerName;
}
?>