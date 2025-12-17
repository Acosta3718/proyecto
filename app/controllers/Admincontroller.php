<?php
/**
 * Controlador del Panel de Administración
 * Gestiona el dashboard y funciones administrativas
 */

require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Usuario.php';

class AdminController extends BaseController
{
    private $productoModel;
    private $usuarioModel;
    
    public function __construct()
    {
        // Verificar autenticación
        AuthController::verificarAutenticacion();
        
        $this->productoModel = new Producto();
        $this->usuarioModel = new Usuario();
    }
    
    /**
     * Dashboard principal
     */
    public function dashboard()
    {
        // Obtener estadísticas
        $data = [
            'totalProductos' => $this->obtenerTotalProductos(),
            'totalUsuarios' => $this->obtenerTotalUsuarios(),
            'productosAgotados' => $this->obtenerProductosAgotados(),
            'productosDestacados' => $this->obtenerProductosDestacados(),
            'ultimosProductos' => $this->obtenerUltimosProductos(5),
            'estadisticasPorCategoria' => $this->obtenerEstadisticasPorCategoria()
        ];
        
        $this->view('admin/dashboard', $data);
    }
    
    /**
     * Obtener total de productos
     */
    private function obtenerTotalProductos()
    {
        try {
            $resultado = $this->productoModel->obtenerProductos([
                'limite' => 1,
                'offset' => 0
            ]);
            return $resultado['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtener total de usuarios
     */
    private function obtenerTotalUsuarios()
    {
        try {
            $usuarios = $this->usuarioModel->obtenerTodos();
            return count($usuarios);
        } catch (Exception $e) {
            return 0;
        }
    }
    
    /**
     * Obtener productos agotados
     */
    private function obtenerProductosAgotados()
    {
        // Aquí implementarías la lógica para contar productos con stock 0
        return 0;
    }
    
    /**
     * Obtener productos destacados
     */
    private function obtenerProductosDestacados()
    {
        // Aquí implementarías la lógica para contar productos destacados
        return 0;
    }
    
    /**
     * Obtener últimos productos creados
     */
    private function obtenerUltimosProductos($limite = 5)
    {
        try {
            $productos = $this->productoModel->obtenerUltimosProductos($limite);

            foreach ($productos as &$producto) {
                $producto['imagen'] = $this->construirUrlImagen($producto['imagen'] ?? '');
            }
            unset($producto);

            return $productos;
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Obtener estadísticas por categoría
     */
    private function obtenerEstadisticasPorCategoria()
    {
        // Aquí implementarías lógica para agrupar por categoría
        return [];
    }

    /**
     * Devuelve la URL absoluta de una imagen almacenada en /public/uploads
     */
    private function construirUrlImagen($ruta)
    {
        if (empty($ruta)) {
            return '';
        }

        if (preg_match('/^https?:\/\//', $ruta)) {
            return $ruta;
        }

        return asset(ltrim($ruta, '/'));
    }
    
    public function productos()
    {
        try {
            // Obtener los productos desde el modelo
            $resultado = $this->productoModel->obtenerProductos([
                'limite' => 50, // o lo que necesites
                'offset' => 0,
                'ordenar' => 'nombre_asc'
            ]);

            $productos = $resultado['productos'] ?? [];

            // Renderizar la vista del panel
            $this->view('admin/productos/index', [
                'titulo' => 'Gestión de Productos',
                'pagina' => 'productos',
                'productos' => $productos
            ]);
        } catch (Exception $e) {
            echo "Error al cargar productos: " . $e->getMessage();
        }
    }

    public function obtenerProductosJson()
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            $productoModel = new Producto();
            $productos = $productoModel->obtenerTodosConRelaciones();

            echo json_encode(['data' => $productos]);
        } catch (Exception $e) {
            echo json_encode(['data' => [], 'error' => $e->getMessage()]);
        }
    }
}
?>