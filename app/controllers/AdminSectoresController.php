<?php
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Sector.php';

class AdminSectoresController extends BaseController
{
    private $sectorModel;
    
    public function __construct()
    {
        AuthController::verificarAutenticacion();
        if (!AuthController::tienePermiso('sectores.ver')) {
            $_SESSION['error'] = 'No tiene permisos';
            header('Location: /admin/dashboard');
            exit();
        }
        $this->sectorModel = new Sector();
    }
    
    public function index()
    {
        $sectores = $this->sectorModel->obtenerTodos();
        foreach ($sectores as &$sector) {
            $stats = $this->sectorModel->obtenerEstadisticas($sector['id']);
            $sector['total_productos'] = $stats['total_productos'];
        }
        
        $this->view('admin/sectores/index', [
            'titulo' => 'Gestión de Sectores',
            'pagina' => 'sectores',
            'breadcrumb' => [['text' => 'Sectores']],
            'sectores' => $sectores
        ]);
    }
    
    public function crear()
    {
        if (!AuthController::tienePermiso('sectores.crear')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/sectores');
            return;
        }
        
        $this->view('admin/sectores/crear', [
            'titulo' => 'Crear Sector',
            'pagina' => 'sectores',
            'breadcrumb' => [
                ['text' => 'Sectores', 'url' => '/admin/sectores'],
                ['text' => 'Crear']
            ]
        ]);
    }
    
    public function guardar()
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('sectores.crear')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'slug' => $this->generarSlug($_POST['nombre']),
                'descripcion' => $this->sanitize($_POST['descripcion'] ?? ''),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'orden' => (int)($_POST['orden'] ?? 0)
            ];
            
            $id = $this->sectorModel->crear($datos);
            
            if ($id) {
                $_SESSION['success'] = 'Sector creado exitosamente';
                $this->json(['success' => true, 'id' => $id]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function editar($id)
    {
        if (!AuthController::tienePermiso('sectores.editar')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/sectores');
            return;
        }
        
        $sector = $this->sectorModel->obtenerPorId($id);
        if (!$sector) {
            $_SESSION['error'] = 'Sector no encontrado';
            $this->redirect('/admin/sectores');
            return;
        }
        
        $stats = $this->sectorModel->obtenerEstadisticas($id);
        
        $this->view('admin/sectores/editar', [
            'titulo' => 'Editar Sector',
            'pagina' => 'sectores',
            'breadcrumb' => [
                ['text' => 'Sectores', 'url' => '/admin/sectores'],
                ['text' => 'Editar']
            ],
            'sector' => $sector,
            'stats' => $stats
        ]);
    }
    
    public function actualizar($id)
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('sectores.editar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'slug' => $this->generarSlug($_POST['nombre'], $id),
                'descripcion' => $this->sanitize($_POST['descripcion'] ?? ''),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'orden' => (int)($_POST['orden'] ?? 0)
            ];
            
            if ($this->sectorModel->actualizar($id, $datos)) {
                $_SESSION['success'] = 'Sector actualizado exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function eliminar($id)
    {
        if (!AuthController::tienePermiso('sectores.eliminar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $stats = $this->sectorModel->obtenerEstadisticas($id);
            if ($stats['total_productos'] > 0) {
                throw new Exception('No se puede eliminar. El sector tiene productos asociados');
            }
            
            if ($this->sectorModel->eliminar($id)) {
                $_SESSION['success'] = 'Sector eliminado exitosamente';
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
        
        while ($this->sectorModel->slugExiste($slug, $excluirId)) {
            $slug = $slugOriginal . '-' . $contador++;
        }
        return $slug;
    }
}
?>