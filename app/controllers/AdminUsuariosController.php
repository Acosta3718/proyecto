<?php
/**
 * CONTROLADOR DE USUARIOS
 */

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Usuario.php';

class AdminUsuariosController extends BaseController
{
    private $usuarioModel;
    
    public function __construct()
    {
        AuthController::verificarAutenticacion();
        if (!AuthController::tienePermiso('usuarios.ver')) {
            $_SESSION['error'] = 'No tiene permisos';
            header('Location: /admin/dashboard');
            exit();
        }
        $this->usuarioModel = new Usuario();
    }
    
    public function index()
    {
        $usuarios = $this->usuarioModel->obtenerTodos();
        
        $this->view('admin/usuarios/index', [
            'titulo' => 'Gestión de Usuarios',
            'pagina' => 'usuarios',
            'breadcrumb' => [['text' => 'Usuarios']],
            'usuarios' => $usuarios
        ]);
    }
    
    public function crear()
    {
        if (!AuthController::tienePermiso('usuarios.crear')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/usuarios');
            return;
        }
        
        $this->view('admin/usuarios/crear', [
            'titulo' => 'Crear Usuario',
            'pagina' => 'usuarios',
            'breadcrumb' => [
                ['text' => 'Usuarios', 'url' => '/admin/usuarios'],
                ['text' => 'Crear']
            ],
            'roles' => ['superadmin', 'admin', 'vendedor', 'cliente']
        ]);
    }
    
    public function guardar()
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('usuarios.crear')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            // Validar
            if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['password'])) {
                throw new Exception('Complete todos los campos requeridos');
            }
            
            if ($this->usuarioModel->emailExiste($_POST['email'])) {
                throw new Exception('El email ya está registrado');
            }
            
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'apellido' => $this->sanitize($_POST['apellido']),
                'email' => $this->sanitize($_POST['email']),
                'password' => $_POST['password'],
                'telefono' => $this->sanitize($_POST['telefono'] ?? ''),
                'direccion' => $this->sanitize($_POST['direccion'] ?? ''),
                'rol' => $this->sanitize($_POST['rol']),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'avatar' => null
            ];
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $datos['avatar'] = $this->subirImagen($_FILES['avatar'], 'avatars');
            }
            
            $id = $this->usuarioModel->crear($datos);
            
            if ($id) {
                $_SESSION['success'] = 'Usuario creado exitosamente';
                $this->json(['success' => true, 'id' => $id]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function editar($id)
    {
        if (!AuthController::tienePermiso('usuarios.editar')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/usuarios');
            return;
        }
        
        $usuario = $this->usuarioModel->obtenerPorId($id);
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado';
            $this->redirect('/admin/usuarios');
            return;
        }
        
        $permisos = $this->usuarioModel->obtenerPermisos($id);
        
        $this->view('admin/usuarios/editar', [
            'titulo' => 'Editar Usuario',
            'pagina' => 'usuarios',
            'breadcrumb' => [
                ['text' => 'Usuarios', 'url' => '/admin/usuarios'],
                ['text' => 'Editar']
            ],
            'usuario' => $usuario,
            'permisos' => $permisos,
            'roles' => ['superadmin', 'admin', 'vendedor', 'cliente']
        ]);
    }
    
    public function actualizar($id)
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('usuarios.editar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            if ($this->usuarioModel->emailExiste($_POST['email'], $id)) {
                throw new Exception('El email ya está en uso');
            }
            
            $usuarioActual = $this->usuarioModel->obtenerPorId($id);
            
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'apellido' => $this->sanitize($_POST['apellido']),
                'email' => $this->sanitize($_POST['email']),
                'telefono' => $this->sanitize($_POST['telefono'] ?? ''),
                'direccion' => $this->sanitize($_POST['direccion'] ?? ''),
                'rol' => $this->sanitize($_POST['rol']),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'avatar' => $usuarioActual['avatar']
            ];
            
            if (!empty($_POST['password'])) {
                $datos['password'] = $_POST['password'];
            }
            
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                if ($usuarioActual['avatar']) {
                    $this->eliminarArchivo($usuarioActual['avatar']);
                }
                $datos['avatar'] = $this->subirImagen($_FILES['avatar'], 'avatars');
            }
            
            if ($this->usuarioModel->actualizar($id, $datos)) {
                $_SESSION['success'] = 'Usuario actualizado exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function eliminar($id)
    {
        if (!AuthController::tienePermiso('usuarios.eliminar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            // No permitir eliminar al usuario actual
            if ($id == $_SESSION['usuario_id']) {
                throw new Exception('No puede eliminar su propio usuario');
            }
            
            if ($this->usuarioModel->eliminar($id)) {
                $_SESSION['success'] = 'Usuario eliminado exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function subirImagen($file, $carpeta)
    {
        $uploadDir = PUBLIC_PATH . "/uploads/{$carpeta}/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $nombreArchivo)) {
            return "/uploads/{$carpeta}/" . $nombreArchivo;
        }
        throw new Exception('Error al subir imagen');
    }
    
    private function eliminarArchivo($ruta)
    {
        if ($ruta && file_exists(PUBLIC_PATH . $ruta)) {
            unlink(PUBLIC_PATH . $ruta);
        }
    }
}
?>