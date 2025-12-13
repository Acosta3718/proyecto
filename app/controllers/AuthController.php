<?php
/**
 * Controlador de Autenticación
 * Maneja login, logout y sesiones
 */

require_once __DIR__ . '/../models/Usuario.php';

class AuthController extends BaseController
{
    private $usuarioModel;
    
    public function __construct()
    {
        $this->usuarioModel = new Usuario();
        
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Mostrar formulario de login
     */
    public function login()
    {
        // Si ya está autenticado, redirigir al dashboard
        if ($this->estaAutenticado()) {
            $this->redirect('/admin/dashboard');
            return;
        }
        
        $data = [
            'error' => isset($_SESSION['error_login']) ? $_SESSION['error_login'] : null
        ];
        
        unset($_SESSION['error_login']);
        
        $this->viewOnly('auth/login', $data);
    }
    
    /**
     * Procesar login
     */
    public function procesarLogin()
    {
        $this->validateMethod('POST');
        
        $email = $this->sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $recordar = isset($_POST['recordar']);
        
        // Validaciones
        if (empty($email) || empty($password)) {
            $_SESSION['error_login'] = 'Por favor complete todos los campos';
            $this->redirect('/proyecto/public/auth/login');
            return;
        }
        
        // Intentar autenticar
        $usuario = $this->usuarioModel->autenticar($email, $password);
            /*echo "<pre>";
            var_dump($usuario);
            echo "</pre>";
            exit;*/
        if ($usuario) {
            // Verificar que sea admin, superadmin o vendedor
            if (!in_array($usuario['rol'], ['superadmin', 'admin', 'vendedor'])) {
                $_SESSION['error_login'] = 'No tiene permisos para acceder al panel de administración';
                $this->redirect('/proyecto/public/auth/login');
                return;
            }
            
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['usuario_email'] = $usuario['email'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            $_SESSION['usuario_avatar'] = $usuario['avatar'];
            $_SESSION['autenticado'] = true;
            $_SESSION['ultimo_acceso'] = time();
            
            // Generar token CSRF
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            
            // Si marcó "Recordarme"
            if ($recordar) {
                $token = bin2hex(random_bytes(32));
                setcookie('recordar_token', $token, time() + (86400 * 30), "/"); // 30 días
                // Aquí podrías guardar el token en la BD
            }
            
            // Redirigir al dashboard
            $this->redirect('/proyecto/public/admin/dashboard');
        } else {
            $_SESSION['error_login'] = 'Email o contraseña incorrectos';
            $this->redirect('/proyecto/public/auth/login');
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout()
    {
        // Limpiar sesión
        $_SESSION = [];
        
        // Destruir cookie de sesión
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir sesión
        session_destroy();
        
        // Redirigir al login
        $this->redirect('/proyecto/public/auth/login');
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public static function estaAutenticado()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === true;
    }
    
    /**
     * Verificar si el usuario tiene un rol específico
     */
    public static function tieneRol($roles)
    {
        if (!self::estaAutenticado()) {
            return false;
        }
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        return in_array($_SESSION['usuario_rol'], $roles);
    }
    
    /**
     * Verificar permisos
     */
    public static function tienePermiso($permiso)
    {
        if (!self::estaAutenticado()) {
            return false;
        }
        
        // Superadmin tiene todos los permisos
        if ($_SESSION['usuario_rol'] === 'superadmin') {
            return true;
        }
        
        $usuarioModel = new Usuario();
        return $usuarioModel->tienePermiso($_SESSION['usuario_id'], $permiso);
    }
    
    /**
     * Middleware para proteger rutas
     */
    public static function verificarAutenticacion()
    {
        if (!self::estaAutenticado()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            header('Location: /proyecto/public/auth/login');
            exit();
        }
        
        // Verificar timeout de sesión (30 minutos)
        if (isset($_SESSION['ultimo_acceso'])) {
            $inactividad = time() - $_SESSION['ultimo_acceso'];
            if ($inactividad > 1800) { // 30 minutos
                session_destroy();
                header('Location: /proyecto/public/auth/login?timeout=1');
                exit();
            }
        }
        
        $_SESSION['ultimo_acceso'] = time();
    }
    
    /**
     * Obtener usuario actual
     */
    public static function usuarioActual()
    {
        if (!self::estaAutenticado()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['usuario_id'] ?? null,
            'nombre' => $_SESSION['usuario_nombre'] ?? '',
            'email' => $_SESSION['usuario_email'] ?? '',
            'rol' => $_SESSION['usuario_rol'] ?? '',
            'avatar' => $_SESSION['usuario_avatar'] ?? ''
        ];
    }
}
?>