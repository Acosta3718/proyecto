<?php
/**
 * Controlador de Categorías para Admin
 * CRUD completo de categorías
 */

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Categoria.php';

class AdminCategoriasController extends BaseController
{
    private $categoriaModel;
    
    public function __construct()
    {
        AuthController::verificarAutenticacion();
        
        if (!AuthController::tienePermiso('categorias.ver')) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección';
            header('Location: /admin/dashboard');
            exit();
        }
        
        $this->categoriaModel = new Categoria();
    }
    
    /**
     * Listado de categorías
     */
    public function index()
    {
        $categorias = $this->categoriaModel->obtenerTodos();

        // Agregar estadísticas para evitar llamadas directas al modelo en la vista
        foreach ($categorias as &$categoria) {
            $stats = $this->categoriaModel->obtenerEstadisticas($categoria['id']);
            $categoria['total_productos'] = $stats['total_productos'];
        }
        unset($categoria);
        
        $data = [
            'titulo' => 'Gestión de Categorías',
            'pagina' => 'categorias',
            'breadcrumb' => [
                ['text' => 'Categorías']
            ],
            'categorias' => $categorias
        ];
        
        $this->view('admin/categorias/index', $data);
    }
    
    /**
     * API para obtener categorías (JSON)
     */
    public function obtenerCategoriasJson()
    {
        header('Content-Type: application/json');
        
        try {
            $categorias = $this->categoriaModel->obtenerTodos();
            
            // Agregar estadísticas
            foreach ($categorias as &$categoria) {
                $stats = $this->categoriaModel->obtenerEstadisticas($categoria['id']);
                $categoria['total_productos'] = $stats['total_productos'];
            }
            
            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Formulario de creación
     */
    public function crear()
    {
        if (!AuthController::tienePermiso('categorias.crear')) {
            $_SESSION['error'] = 'No tiene permisos para crear categorías';
            $this->redirect('/admin/categorias');
            return;
        }
        
        $data = [
            'titulo' => 'Crear Categoría',
            'pagina' => 'categorias',
            'breadcrumb' => [
                ['text' => 'Categorías', 'url' => '/admin/categorias'],
                ['text' => 'Crear']
            ]
        ];
        
        $this->view('admin/categorias/crear', $data);
    }
    
    /**
     * Guardar nueva categoría
     */
    public function guardar()
    {
        $this->validateMethod('POST');
        
        if (!AuthController::tienePermiso('categorias.crear')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $datos = $this->validarDatos($_POST);
            
            // Procesar imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $datos['imagen'] = $this->subirImagen($_FILES['imagen']);
            } else {
                $datos['imagen'] = null;
            }
            
            $categoriaId = $this->categoriaModel->crear($datos);
            
            if ($categoriaId) {
                $_SESSION['success'] = 'Categoría creada exitosamente';
                $this->json(['success' => true, 'message' => 'Categoría creada', 'id' => $categoriaId]);
            } else {
                throw new Exception('Error al crear la categoría');
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Formulario de edición
     */
    public function editar($id)
    {
        if (!AuthController::tienePermiso('categorias.editar')) {
            $_SESSION['error'] = 'No tiene permisos para editar categorías';
            $this->redirect('/admin/categorias');
            return;
        }
        
        $categoria = $this->categoriaModel->obtenerPorId($id);
        
        if (!$categoria) {
            $_SESSION['error'] = 'Categoría no encontrada';
            $this->redirect('/admin/categorias');
            return;
        }
        
        $stats = $this->categoriaModel->obtenerEstadisticas($id);
        
        $data = [
            'titulo' => 'Editar Categoría',
            'pagina' => 'categorias',
            'breadcrumb' => [
                ['text' => 'Categorías', 'url' => '/admin/categorias'],
                ['text' => 'Editar']
            ],
            'categoria' => $categoria,
            'stats' => $stats
        ];
        
        $this->view('admin/categorias/editar', $data);
    }
    
    /**
     * Actualizar categoría
     */
    public function actualizar($id)
    {
        $this->validateMethod('POST');
        
        if (!AuthController::tienePermiso('categorias.editar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $datos = $this->validarDatos($_POST, $id);
            
            // Procesar nueva imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $categoriaAnterior = $this->categoriaModel->obtenerPorId($id);
                if ($categoriaAnterior['imagen']) {
                    $this->eliminarImagen($categoriaAnterior['imagen']);
                }
                $datos['imagen'] = $this->subirImagen($_FILES['imagen']);
            } else {
                $categoriaActual = $this->categoriaModel->obtenerPorId($id);
                $datos['imagen'] = $categoriaActual['imagen'];
            }
            
            $resultado = $this->categoriaModel->actualizar($id, $datos);
            
            if ($resultado) {
                $_SESSION['success'] = 'Categoría actualizada exitosamente';
                $this->json(['success' => true, 'message' => 'Categoría actualizada']);
            } else {
                throw new Exception('Error al actualizar la categoría');
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Eliminar categoría
     */
    public function eliminar($id)
    {
        if (!AuthController::tienePermiso('categorias.eliminar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $categoria = $this->categoriaModel->obtenerPorId($id);
            
            if (!$categoria) {
                throw new Exception('Categoría no encontrada');
            }
            
            // Verificar si tiene productos
            $stats = $this->categoriaModel->obtenerEstadisticas($id);
            if ($stats['total_productos'] > 0) {
                throw new Exception('No se puede eliminar. La categoría tiene productos asociados');
            }
            
            $resultado = $this->categoriaModel->eliminar($id);
            
            if ($resultado) {
                $_SESSION['success'] = 'Categoría eliminada exitosamente';
                $this->json(['success' => true, 'message' => 'Categoría eliminada']);
            } else {
                throw new Exception('Error al eliminar la categoría');
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Cambiar orden de categorías
     */
    public function cambiarOrden()
    {
        $this->validateMethod('POST');
        
        if (!AuthController::tienePermiso('categorias.editar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $orden = $_POST['orden'] ?? [];
            
            foreach ($orden as $index => $id) {
                $this->categoriaModel->cambiarOrden($id, $index + 1);
            }
            
            $this->json(['success' => true, 'message' => 'Orden actualizado']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Validar datos
     */
    private function validarDatos($datos, $id = null)
    {
        $errores = [];
        
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        }
        
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        $slug = $this->generarSlug($datos['nombre'], $id);
        
        return [
            'nombre' => $this->sanitize($datos['nombre']),
            'slug' => $slug,
            'descripcion' => $this->sanitize($datos['descripcion'] ?? ''),
            'icono' => $this->sanitize($datos['icono'] ?? ''),
            'activo' => isset($datos['activo']) ? 1 : 0,
            'orden' => (int)($datos['orden'] ?? 0)
        ];
    }
    
    /**
     * Generar slug único
     */
    private function generarSlug($texto, $excluirId = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $texto), '-'));
        
        $contador = 1;
        $slugOriginal = $slug;
        
        while ($this->categoriaModel->slugExiste($slug, $excluirId)) {
            $slug = $slugOriginal . '-' . $contador;
            $contador++;
        }
        
        return $slug;
    }
    
    /**
     * Subir imagen
     */
    private function subirImagen($file)
    {
        $uploadDir = PUBLIC_PATH . '/uploads/categorias/';
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido');
        }
        
        if ($file['size'] > MAX_FILE_SIZE) {
            throw new Exception('El archivo es demasiado grande');
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        $rutaDestino = $uploadDir . $nombreArchivo;
        
        if (move_uploaded_file($file['tmp_name'], $rutaDestino)) {
            return '/uploads/categorias/' . $nombreArchivo;
        }
        
        throw new Exception('Error al subir la imagen');
    }
    
    /**
     * Eliminar imagen
     */
    private function eliminarImagen($ruta)
    {
        if ($ruta) {
            $rutaCompleta = PUBLIC_PATH . $ruta;
            if (file_exists($rutaCompleta)) {
                unlink($rutaCompleta);
            }
        }
    }
}
?>