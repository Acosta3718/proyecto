<?php
/**
 * CONTROLADOR DE MARCAS
 */

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Marca.php';

class AdminMarcasController extends BaseController
{
    private $marcaModel;
    
    public function __construct()
    {
        AuthController::verificarAutenticacion();
        if (!AuthController::tienePermiso('marcas.ver')) {
            $_SESSION['error'] = 'No tiene permisos';
            header('Location: /admin/dashboard');
            exit();
        }
        $this->marcaModel = new Marca();
    }
    
    public function index()
    {
        $marcas = $this->marcaModel->obtenerTodos();
        foreach ($marcas as &$marca) {
            $stats = $this->marcaModel->obtenerEstadisticas($marca['id']);
            $marca['total_productos'] = $stats['total_productos'];
        }
        
        $this->view('admin/marcas/index', [
            'titulo' => 'Gestión de Marcas',
            'pagina' => 'marcas',
            'breadcrumb' => [['text' => 'Marcas']],
            'marcas' => $marcas
        ]);
    }
    
    public function crear()
    {
        if (!AuthController::tienePermiso('marcas.crear')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/marcas');
            return;
        }
        
        $this->view('admin/marcas/crear', [
            'titulo' => 'Crear Marca',
            'pagina' => 'marcas',
            'breadcrumb' => [
                ['text' => 'Marcas', 'url' => '/admin/marcas'],
                ['text' => 'Crear']
            ]
        ]);
    }
    
    public function guardar()
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('marcas.crear')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'slug' => $this->generarSlug($_POST['nombre']),
                'descripcion' => $this->sanitize($_POST['descripcion'] ?? ''),
                'sitio_web' => $this->sanitize($_POST['sitio_web'] ?? ''),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'orden' => (int)($_POST['orden'] ?? 0),
                'logo' => null
            ];
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $datos['logo'] = $this->subirArchivo($_FILES['logo'], 'marcas');
            }
            
            $id = $this->marcaModel->crear($datos);
            
            if ($id) {
                $_SESSION['success'] = 'Marca creada exitosamente';
                $this->json(['success' => true, 'id' => $id]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function editar($id)
    {
        if (!AuthController::tienePermiso('marcas.editar')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/marcas');
            return;
        }
        
        $marca = $this->marcaModel->obtenerPorId($id);
        if (!$marca) {
            $_SESSION['error'] = 'Marca no encontrada';
            $this->redirect('/admin/marcas');
            return;
        }
        
        $stats = $this->marcaModel->obtenerEstadisticas($id);
        
        $this->view('admin/marcas/editar', [
            'titulo' => 'Editar Marca',
            'pagina' => 'marcas',
            'breadcrumb' => [
                ['text' => 'Marcas', 'url' => '/admin/marcas'],
                ['text' => 'Editar']
            ],
            'marca' => $marca,
            'stats' => $stats
        ]);
    }
    
    public function actualizar($id)
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('marcas.editar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $marcaActual = $this->marcaModel->obtenerPorId($id);
            
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'slug' => $this->generarSlug($_POST['nombre'], $id),
                'descripcion' => $this->sanitize($_POST['descripcion'] ?? ''),
                'sitio_web' => $this->sanitize($_POST['sitio_web'] ?? ''),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'orden' => (int)($_POST['orden'] ?? 0),
                'logo' => $marcaActual['logo']
            ];
            
            if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                if ($marcaActual['logo']) {
                    $this->eliminarArchivo($marcaActual['logo']);
                }
                $datos['logo'] = $this->subirArchivo($_FILES['logo'], 'marcas');
            }
            
            if ($this->marcaModel->actualizar($id, $datos)) {
                $_SESSION['success'] = 'Marca actualizada exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function eliminar($id)
    {
        if (!AuthController::tienePermiso('marcas.eliminar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $stats = $this->marcaModel->obtenerEstadisticas($id);
            if ($stats['total_productos'] > 0) {
                throw new Exception('No se puede eliminar. La marca tiene productos asociados');
            }
            
            if ($this->marcaModel->eliminar($id)) {
                $_SESSION['success'] = 'Marca eliminada exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    private function generarSlug($texto, $excluirId = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $texto), '-'));
        $contador = 1;
        $slugOriginal = $slug;
        
        while ($this->marcaModel->slugExiste($slug, $excluirId)) {
            $slug = $slugOriginal . '-' . $contador++;
        }
        return $slug;
    }
    
    private function subirArchivo($file, $carpeta)
    {
        $uploadDir = PUBLIC_PATH . "/uploads/{$carpeta}/";
        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $nombreArchivo = uniqid() . '_' . time() . '.' . $extension;
        
        if (move_uploaded_file($file['tmp_name'], $uploadDir . $nombreArchivo)) {
            return "/uploads/{$carpeta}/" . $nombreArchivo;
        }
        throw new Exception('Error al subir archivo');
    }
    
    private function eliminarArchivo($ruta)
    {
        if ($ruta && file_exists(PUBLIC_PATH . $ruta)) {
            unlink(PUBLIC_PATH . $ruta);
        }
    }
}

?>