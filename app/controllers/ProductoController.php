<?php
/**
 * Controlador de Productos
 * Maneja todas las operaciones relacionadas con productos
 */

require_once __DIR__ . '/../models/Producto.php';

class ProductoController extends BaseController
{
    private $productoModel;
    
    public function __construct()
    {
        $this->productoModel = new Producto();
    }
    
    /**
     * Muestra la vista principal con el listado de productos
     */
    public function index()
    {
        // Obtener parámetros de la URL
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $porPagina = isset($_GET['porPagina']) ? (int)$_GET['porPagina'] : 12;
        $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
        $ordenar = isset($_GET['ordenar']) ? $_GET['ordenar'] : '';
        
        // Obtener filtros
        $filtros = [
            'marca' => isset($_GET['marca']) ? $_GET['marca'] : [],
            'categoria' => isset($_GET['categoria']) ? $_GET['categoria'] : [],
            'sector' => isset($_GET['sector']) ? $_GET['sector'] : []
        ];
        
        // Validar página
        if ($pagina < 1) $pagina = 1;
        
        // Calcular offset
        $offset = ($pagina - 1) * $porPagina;
        
        // Obtener productos con filtros
        $resultado = $this->productoModel->obtenerProductos([
            'busqueda' => $busqueda,
            'filtros' => $filtros,
            'ordenar' => $ordenar,
            'limite' => $porPagina,
            'offset' => $offset
        ]);
        
        $productos = $resultado['productos'];
        $totalProductos = $resultado['total'];
        $totalPaginas = ceil($totalProductos / $porPagina);

        // Convertir valores numéricos
        foreach ($productos as &$p) {
            $p['precio'] = isset($p['precio']) ? (float)$p['precio'] : 0;
            $p['precio_costo'] = isset($p['precio_costo']) ? (float)$p['precio_costo'] : 0;
            $p['stock'] = isset($p['stock']) ? (int)$p['stock'] : 0;
            $p['imagen'] = $this->construirUrlImagen($p['imagen'] ?? '');
        }
        unset($p);
        
        // Obtener datos para los filtros
        $marcas = $this->productoModel->obtenerMarcas();
        $categorias = $this->productoModel->obtenerCategorias();
        $sectores = $this->productoModel->obtenerSectores();
        
        // Preparar datos para la vista
        $data = [
            'productos' => $productos,
            'totalProductos' => $totalProductos,
            'paginaActual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'porPagina' => $porPagina,
            'marcas' => $marcas,
            'categorias' => $categorias,
            'sectores' => $sectores,
            'filtrosActivos' => $filtros,
            'busqueda' => $busqueda,
            'ordenar' => $ordenar
        ];
        
        // Renderizar vista
        $this->view('productos/index', $data);
    }
    
    /**
     * API para obtener productos (AJAX)
     */
    public function obtenerProductosAjax()
    {
        header('Content-Type: application/json');
        
        try {
            // Obtener parámetros POST
            $pagina = isset($_POST['pagina']) ? (int)$_POST['pagina'] : 1;
            $porPagina = isset($_POST['porPagina']) ? (int)$_POST['porPagina'] : 12;
            $busqueda = isset($_POST['busqueda']) ? $_POST['busqueda'] : '';
            $ordenar = isset($_POST['ordenar']) ? $_POST['ordenar'] : '';
            
            // Obtener filtros
            $filtros = [
                'marca' => isset($_POST['marca']) ? $_POST['marca'] : [],
                'categoria' => isset($_POST['categoria']) ? $_POST['categoria'] : [],
                'sector' => isset($_POST['sector']) ? $_POST['sector'] : []
            ];
            
            // Validaciones
            if ($pagina < 1) $pagina = 1;
            if ($porPagina < 1 || $porPagina > 100) $porPagina = 12;
            
            $offset = ($pagina - 1) * $porPagina;
            
            // Obtener productos
            $resultado = $this->productoModel->obtenerProductos([
                'busqueda' => $busqueda,
                'filtros' => $filtros,
                'ordenar' => $ordenar,
                'limite' => $porPagina,
                'offset' => $offset
            ]);

            // Normalizar URLs de imágenes para el cliente
            $productos = $resultado['productos'];
            foreach ($productos as &$producto) {
                $producto['imagen'] = $this->construirUrlImagen($producto['imagen'] ?? '');
            }
            unset($producto);
            
            echo json_encode([
                'success' => true,
                'data' => [
                    'productos' => $productos,
                    'total' => $resultado['total'],
                    'pagina' => $pagina,
                    'totalPaginas' => ceil($resultado['total'] / $porPagina)
                ]
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener productos: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Muestra el detalle de un producto
     */
    public function detalle($id)
    {
        $producto = $this->productoModel->obtenerPorId($id);
        
        if (!$producto) {
            $this->redirect('/productos');
            return;
        }
        
        $producto['imagen'] = $this->construirUrlImagen($producto['imagen'] ?? '');

        $productosRelacionados = $this->productoModel->obtenerRelacionados(
            $producto['categoria_id'],
            $producto['id'],
            4
        );

        foreach ($productosRelacionados as &$relacionado) {
            $relacionado['imagen'] = $this->construirUrlImagen($relacionado['imagen'] ?? '');
        }
        unset($relacionado);
        
        $data = [
            'producto' => $producto,
            'relacionados' => $productosRelacionados
        ];
        
        $this->view('productos/detalle', $data);
    }
    
    /**
     * Obtiene el detalle del producto en formato JSON con galería
     */
    public function detalleJson($id)
    {
        header('Content-Type: application/json');

        try {
            $producto = $this->productoModel->obtenerDetalleConRelaciones($id);

            if (!$producto) {
                return $this->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
            }

            $producto['imagen'] = $this->construirUrlImagen($producto['imagen'] ?? '');

            if (!empty($producto['galeria'])) {
                foreach ($producto['galeria'] as &$imagen) {
                    $imagen['imagen'] = $this->construirUrlImagen($imagen['imagen'] ?? '');
                }
                unset($imagen);
            }

            return $this->json(['success' => true, 'producto' => $producto]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'No se pudo obtener el detalle del producto'
            ], 500);
        }
    }
    
    /**
     * Buscar productos (autocompletado)
     */
    public function buscarAutocompletado()
    {
        header('Content-Type: application/json');
        
        $termino = isset($_GET['q']) ? $_GET['q'] : '';
        
        if (strlen($termino) < 2) {
            echo json_encode([]);
            return;
        }
        
        $resultados = $this->productoModel->buscarAutocompletado($termino);
        
        echo json_encode($resultados);
    }
    
    /**
     * Obtener filtros disponibles
     */
    public function obtenerFiltros()
    {
        header('Content-Type: application/json');
        
        try {
            $data = [
                'marcas' => $this->productoModel->obtenerMarcas(),
                'categorias' => $this->productoModel->obtenerCategorias(),
                'sectores' => $this->productoModel->obtenerSectores()
            ];
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener filtros'
            ]);
        }
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
}
?>