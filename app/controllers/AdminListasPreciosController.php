<?php
/**
 * CONTROLADOR DE LISTAS DE PRECIOS
 */

require_once __DIR__ . '/../models/ListaPrecio.php';
require_once __DIR__ . '/../models/Producto.php';

class AdminListasPreciosController extends BaseController
{
    private $listaModel;
    private $productoModel;
    
    public function __construct()
    {
        AuthController::verificarAutenticacion();
        if (!AuthController::tienePermiso('listas_precios.ver')) {
            $_SESSION['error'] = 'No tiene permisos';
            header('Location: /admin/dashboard');
            exit();
        }
        $this->listaModel = new ListaPrecio();
        $this->productoModel = new Producto();
    }
    
    public function index()
    {
        $listas = $this->listaModel->obtenerTodos();
        foreach ($listas as &$lista) {
            $stats = $this->listaModel->obtenerEstadisticas($lista['id']);
            $lista['total_productos'] = $stats['total_productos'];
        }
        
        $this->view('admin/listas-precios/index', [
            'titulo' => 'Listas de Precios',
            'pagina' => 'listas-precios',
            'breadcrumb' => [['text' => 'Listas de Precios']],
            'listas' => $listas
        ]);
    }
    
    public function crear()
    {
        if (!AuthController::tienePermiso('listas_precios.crear')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/listas-precios');
            return;
        }
        
        $this->view('admin/listas-precios/crear', [
            'titulo' => 'Crear Lista de Precios',
            'pagina' => 'listas-precios',
            'breadcrumb' => [
                ['text' => 'Listas de Precios', 'url' => '/admin/listas-precios'],
                ['text' => 'Crear']
            ]
        ]);
    }
    
    public function guardar()
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('listas_precios.crear')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'descripcion' => $this->sanitize($_POST['descripcion'] ?? ''),
                'tipo' => $this->sanitize($_POST['tipo']),
                'descuento_porcentaje' => (float)($_POST['descuento_porcentaje'] ?? 0),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
                'fecha_fin' => $_POST['fecha_fin'] ?? null
            ];
            
            $id = $this->listaModel->crear($datos);
            
            if ($id) {
                $_SESSION['success'] = 'Lista creada exitosamente';
                $this->json(['success' => true, 'id' => $id]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function editar($id)
    {
        if (!AuthController::tienePermiso('listas_precios.editar')) {
            $_SESSION['error'] = 'Sin permisos';
            $this->redirect('/admin/listas-precios');
            return;
        }
        
        $lista = $this->listaModel->obtenerPorId($id);
        if (!$lista) {
            $_SESSION['error'] = 'Lista no encontrada';
            $this->redirect('/admin/listas-precios');
            return;
        }
        
        $stats = $this->listaModel->obtenerEstadisticas($id);
        
        $this->view('admin/listas-precios/editar', [
            'titulo' => 'Editar Lista de Precios',
            'pagina' => 'listas-precios',
            'breadcrumb' => [
                ['text' => 'Listas de Precios', 'url' => '/admin/listas-precios'],
                ['text' => 'Editar']
            ],
            'lista' => $lista,
            'stats' => $stats
        ]);
    }
    
    public function actualizar($id)
    {
        $this->validateMethod('POST');
        if (!AuthController::tienePermiso('listas_precios.editar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $datos = [
                'nombre' => $this->sanitize($_POST['nombre']),
                'descripcion' => $this->sanitize($_POST['descripcion'] ?? ''),
                'tipo' => $this->sanitize($_POST['tipo']),
                'descuento_porcentaje' => (float)($_POST['descuento_porcentaje'] ?? 0),
                'activo' => isset($_POST['activo']) ? 1 : 0,
                'fecha_inicio' => $_POST['fecha_inicio'] ?? null,
                'fecha_fin' => $_POST['fecha_fin'] ?? null
            ];
            
            if ($this->listaModel->actualizar($id, $datos)) {
                $_SESSION['success'] = 'Lista actualizada exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function eliminar($id)
    {
        if (!AuthController::tienePermiso('listas_precios.eliminar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            if ($this->listaModel->eliminar($id)) {
                $_SESSION['success'] = 'Lista eliminada exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function asignarProductos($id)
    {
        $lista = $this->listaModel->obtenerPorId($id);
        if (!$lista) {
            $_SESSION['error'] = 'Lista no encontrada';
            $this->redirect('/admin/listas-precios');
            return;
        }
        
        $productosAsignados = $this->listaModel->obtenerProductos($id);
        $productosDisponibles = $this->listaModel->obtenerProductosNoAsignados($id);
        
        $this->view('admin/listas-precios/asignar-productos', [
            'titulo' => 'Asignar Productos',
            'pagina' => 'listas-precios',
            'breadcrumb' => [
                ['text' => 'Listas de Precios', 'url' => '/admin/listas-precios'],
                ['text' => $lista['nombre'], 'url' => '/admin/listas-precios/editar/' . $id],
                ['text' => 'Asignar Productos']
            ],
            'lista' => $lista,
            'productosAsignados' => $productosAsignados,
            'productosDisponibles' => $productosDisponibles
        ]);
    }
    
    public function guardarAsignacion($id)
    {
        $this->validateMethod('POST');
        
        try {
            $productoId = (int)$_POST['producto_id'];
            $precio = (float)$_POST['precio'];
            
            if ($this->listaModel->asignarProducto($id, $productoId, $precio)) {
                $_SESSION['success'] = 'Producto asignado exitosamente';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function aplicarDescuento($id)
    {
        $this->validateMethod('POST');
        
        try {
            if ($this->listaModel->aplicarDescuentoGeneral($id)) {
                $_SESSION['success'] = 'Descuento aplicado a todos los productos';
                $this->json(['success' => true]);
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
?>