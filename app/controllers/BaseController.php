<?php
/**
 * Controlador Base
 * Todas las funcionalidades comunes a todos los controladores
 */

class BaseController
{
    /**
     * Cargar una vista
     */
    protected function view($view, $data = [])
    {
        // Extraer datos para usar como variables
        extract($data);
        
        // Verificar si existe el archivo de vista
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            // Detectar si es vista de admin
            if (strpos($view, 'admin/') === 0) {
                require_once __DIR__ . '/../views/layouts/admin_header.php';
                require_once $viewFile;
                require_once __DIR__ . '/../views/layouts/admin_footer.php';
            } else {
                require_once __DIR__ . '/../views/layouts/header.php';
                require_once $viewFile;
                require_once __DIR__ . '/../views/layouts/footer.php';
            }
        } else {
            die("Vista no encontrada: " . $view);
        }
    }
    
    /**
     * Cargar una vista sin layout
     */
    protected function viewOnly($view, $data = [])
    {
        extract($data);
        
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            die("Vista no encontrada: " . $view);
        }
    }
    
    /**
     * Redireccionar a una URL
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit();
    }
    
    /**
     * Retornar JSON
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
    
    /**
     * Validar método HTTP
     */
    protected function validateMethod($method)
    {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
            http_response_code(405);
            die('Método no permitido');
        }
    }
    
    /**
     * Sanitizar entrada de datos
     */
    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }
        
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validar CSRF Token
     */
    protected function validateCSRF($token)
    {
        if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            http_response_code(403);
            die('Token CSRF inválido');
        }
    }
    
    /**
     * Generar CSRF Token
     */
    protected function generateCSRF()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
?>