<?php
/**
 * Controlador de Productos para Admin
 * CRUD completo de productos
 */

require_once __DIR__ . '/AuthController.php';

class AdminProductosController extends BaseController
{
    private $productoModel;
    private $categoriaModel;
    private $marcaModel;
    private $sectorModel;
    
    public function __construct()
    {
        AuthController::verificarAutenticacion();
        
        if (!AuthController::tienePermiso('productos.ver')) {
            $_SESSION['error'] = 'No tiene permisos para acceder a esta sección';
            header('Location: /admin/dashboard');
            exit();
        }
        
        // Cargar modelos necesarios
        require_once __DIR__ . '/../models/Producto.php';
        require_once __DIR__ . '/../models/Categoria.php';
        require_once __DIR__ . '/../models/Marca.php';
        require_once __DIR__ . '/../models/Sector.php';
        
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->marcaModel = new Marca();
        $this->sectorModel = new Sector();
    }
    
    /**
     * Listado de productos
     */
    public function index()
    {
        $data = [
            'titulo' => 'Gestión de Productos',
            'pagina' => 'productos',
            'breadcrumb' => [
                ['text' => 'Productos']
            ],
            'categorias' => $this->categoriaModel->obtenerActivos(),
            'marcas' => $this->marcaModel->obtenerActivos(),
            'sectores' => $this->sectorModel->obtenerActivos()
        ];
        
        $this->view('admin/productos/index', $data);
    }
    
    /**
     * API para obtener productos (DataTables)
     */
    public function obtenerProductosJson()
    {
        header('Content-Type: application/json');
        
        try {
            $resultado = $this->productoModel->obtenerTodosConRelaciones();
            
            echo json_encode([
                'success' => true,
                'data' => $resultado
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
        if (!AuthController::tienePermiso('productos.crear')) {
            $_SESSION['error'] = 'No tiene permisos para crear productos';
            $this->redirect('/admin/productos');
            return;
        }
        
        $data = [
            'titulo' => 'Crear Producto',
            'pagina' => 'productos',
            'breadcrumb' => [
                ['text' => 'Productos', 'url' => '/admin/productos'],
                ['text' => 'Crear']
            ],
            'categorias' => $this->categoriaModel->obtenerActivos(),
            'marcas' => $this->marcaModel->obtenerActivos(),
            'sectores' => $this->sectorModel->obtenerActivos()
        ];
        
        $this->view('admin/productos/crear', $data);
    }
    
    /**
     * Guardar nuevo producto
     */
    public function guardar()
    {
        $this->validateMethod('POST');
        
        if (!AuthController::tienePermiso('productos.crear')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            // Validar datos
            $datos = $this->validarDatosProducto($_POST);
            
            // Procesar imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $datos['imagen'] = $this->subirImagen($_FILES['imagen']);
            }
            
            // Crear slug
            $datos['slug'] = $this->generarSlug($datos['nombre']);
            
            // Crear producto
            $productoId = $this->productoModel->crear($datos);
            
            if ($productoId) {
                // Procesar imágenes adicionales
                if (isset($_FILES['imagenes_adicionales'])) {
                    $this->procesarImagenesAdicionales($productoId, $_FILES['imagenes_adicionales']);
                }
                
                $_SESSION['success'] = 'Producto creado exitosamente';
                $this->json(['success' => true, 'message' => 'Producto creado', 'id' => $productoId]);
            } else {
                throw new Exception('Error al crear el producto');
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
        if (!AuthController::tienePermiso('productos.editar')) {
            $_SESSION['error'] = 'No tiene permisos para editar productos';
            $this->redirect('/admin/productos');
            return;
        }
        
        $producto = $this->productoModel->obtenerPorId($id);
        
        if (!$producto) {
            $_SESSION['error'] = 'Producto no encontrado';
            $this->redirect('/admin/productos');
            return;
        }
        
        $data = [
            'titulo' => 'Editar Producto',
            'pagina' => 'productos',
            'breadcrumb' => [
                ['text' => 'Productos', 'url' => '/admin/productos'],
                ['text' => 'Editar']
            ],
            'producto' => $producto,
            'categorias' => $this->categoriaModel->obtenerActivos(),
            'marcas' => $this->marcaModel->obtenerActivos(),
            'sectores' => $this->sectorModel->obtenerActivos(),
            'imagenes' => $this->productoModel->obtenerImagenes($id)
        ];
        
        $this->view('admin/productos/editar', $data);
    }
    
    /**
     * Actualizar producto
     */
    public function actualizar($id)
    {
        $this->validateMethod('POST');
        
        if (!AuthController::tienePermiso('productos.editar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            // Validar datos
            $datos = $this->validarDatosProducto($_POST, $id);
            
            // Procesar nueva imagen si se subió
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                // Eliminar imagen anterior
                $productoAnterior = $this->productoModel->obtenerPorId($id);
                if ($productoAnterior['imagen']) {
                    $this->eliminarImagen($productoAnterior['imagen']);
                }
                $datos['imagen'] = $this->subirImagen($_FILES['imagen']);
            }
            
            // Actualizar slug
            $datos['slug'] = $this->generarSlug($datos['nombre'], $id);
            
            // Actualizar producto
            $resultado = $this->productoModel->actualizar($id, $datos);
            
            if ($resultado) {
                // Procesar imágenes adicionales
                if (isset($_FILES['imagenes_adicionales'])) {
                    $this->procesarImagenesAdicionales($id, $_FILES['imagenes_adicionales']);
                }
                
                $_SESSION['success'] = 'Producto actualizado exitosamente';
                $this->json(['success' => true, 'message' => 'Producto actualizado']);
            } else {
                throw new Exception('Error al actualizar el producto');
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Eliminar producto
     */
    public function eliminar($id)
    {
        if (!AuthController::tienePermiso('productos.eliminar')) {
            $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
            return;
        }
        
        try {
            $producto = $this->productoModel->obtenerPorId($id);
            
            if (!$producto) {
                throw new Exception('Producto no encontrado');
            }
            
            // Eliminar imágenes adicionales para evitar residuos y llamadas a funciones no compatibles en el motor
            try {
                $galeria = $this->productoModel->obtenerImagenes($id);
                foreach ($galeria as $img) {
                    $this->eliminarImagen($img['imagen']);
                }
                $this->productoModel->eliminarImagenesProducto($id);
            } catch (Exception $e) {
                // Registrar pero continuar con el borrado lógico del producto
                error_log('No se pudieron limpiar las imágenes adicionales: ' . $e->getMessage());
            }

            $resultado = $this->productoModel->eliminar($id);
            
            if ($resultado) {
                $_SESSION['success'] = 'Producto eliminado exitosamente';
                $this->json(['success' => true, 'message' => 'Producto eliminado']);
            } else {
                throw new Exception('Error al eliminar el producto');
            }
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Vista rápida de producto para el panel
     */
    public function ver($id)
    {
        if (!AuthController::tienePermiso('productos.ver')) {
            return $this->json(['success' => false, 'message' => 'Sin permisos'], 403);
        }

        try {
            $producto = $this->productoModel->obtenerDetalleConRelaciones($id);

            if (!$producto) {
                throw new Exception('Producto no encontrado');
            }

            return $this->json([
                'success' => true,
                'producto' => $producto
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Validar datos del producto
     */
    private function validarDatosProducto($datos, $id = null)
    {
        $errores = [];
        
        if (empty($datos['nombre'])) {
            $errores[] = 'El nombre es requerido';
        }
        
        if (empty($datos['referencia'])) {
            $errores[] = 'La referencia es requerida';
        } else {
            // Verificar referencia única
            if ($this->productoModel->referenciaExiste($datos['referencia'], $id)) {
                $errores[] = 'La referencia ya está en uso';
            }
        }
        
        if (empty($datos['marca_id'])) {
            $errores[] = 'La marca es requerida';
        }
        
        if (empty($datos['categoria_id'])) {
            $errores[] = 'La categoría es requerida';
        }
        
        if (!isset($datos['precio']) || $datos['precio'] < 0) {
            $errores[] = 'El precio debe ser mayor o igual a 0';
        }
        
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }
        
        return [
            'nombre' => $this->sanitize($datos['nombre']),
            'descripcion' => $this->sanitize($datos['descripcion'] ?? ''),
            'descripcion_corta' => $this->sanitize($datos['descripcion_corta'] ?? ''),
            'marca_id' => (int)$datos['marca_id'],
            'categoria_id' => (int)$datos['categoria_id'],
            'sector_id' => isset($datos['sector_id']) ? (int)$datos['sector_id'] : null,
            'referencia' => $this->sanitize($datos['referencia']),
            'codigo_barras' => $this->sanitize($datos['codigo_barras'] ?? ''),
            'precio' => (float)$datos['precio'],
            'precio_costo' => (float)($datos['precio_costo'] ?? 0),
            'stock' => (int)($datos['stock'] ?? 0),
            'stock_minimo' => (int)($datos['stock_minimo'] ?? 0),
            'peso' => isset($datos['peso']) ? (float)$datos['peso'] : null,
            'dimensiones' => $this->sanitize($datos['dimensiones'] ?? ''),
            'garantia' => $this->sanitize($datos['garantia'] ?? ''),
            'activo' => isset($datos['activo']) ? 1 : 0,
            'destacado' => isset($datos['destacado']) ? 1 : 0,
            'nuevo' => isset($datos['nuevo']) ? 1 : 0
        ];
    }
    
    /**
     * Subir imagen
     */
    private function subirImagen($file)
    {
        $uploadDir = PUBLIC_PATH . '/uploads/productos/';
        
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
            return '/uploads/productos/' . $nombreArchivo;
        }
        
        throw new Exception('Error al subir la imagen');
    }
    
    /**
     * Eliminar imagen
     */
    private function eliminarImagen($ruta)
    {
        $rutaCompleta = PUBLIC_PATH . $ruta;
        if (file_exists($rutaCompleta)) {
            unlink($rutaCompleta);
        }
    }
    
    /**
     * Procesar imágenes adicionales
     */
    private function procesarImagenesAdicionales($productoId, $files)
    {
        $totalArchivos = count($files['name']);
        
        for ($i = 0; $i < $totalArchivos; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                try {
                    $rutaImagen = $this->subirImagen($file);
                    $this->productoModel->agregarImagen($productoId, $rutaImagen, $i);
                } catch (Exception $e) {
                    // Log error pero continuar con las demás imágenes
                    error_log('Error subiendo imagen adicional: ' . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * Generar slug único
     */
    private function generarSlug($texto, $excluirId = null)
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $texto), '-'));
        
        $contador = 1;
        $slugOriginal = $slug;
        
        while ($this->productoModel->slugExiste($slug, $excluirId)) {
            $slug = $slugOriginal . '-' . $contador;
            $contador++;
        }
        
        return $slug;
    }
}
?>