<?php
/**
 * Modelo de Producto
 * Gestiona todas las operaciones de base de datos relacionadas con productos
 */

require_once __DIR__ . '/Database.php';

class Producto
{
    private $db;
    private $tabla = 'productos';
    
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtener productos con filtros, búsqueda y paginación
     */
    /*public function obtenerProductos($params = [])
    {
        $busqueda = isset($params['busqueda']) ? $params['busqueda'] : '';
        $filtros = isset($params['filtros']) ? $params['filtros'] : [];
        $ordenar = isset($params['ordenar']) ? $params['ordenar'] : '';
        $limite = isset($params['limite']) ? (int)$params['limite'] : 12;
        $offset = isset($params['offset']) ? (int)$params['offset'] : 0;
        
        // Construir query base
        $sql = "SELECT * FROM {$this->tabla} WHERE activo = 1";
        $sqlCount = "SELECT COUNT(*) as total FROM {$this->tabla} WHERE activo = 1";
        $parametros = [];
        
        // Aplicar búsqueda
        if (!empty($busqueda)) {
            $condicionBusqueda = " AND (nombre LIKE :busqueda OR marca LIKE :busqueda OR referencia LIKE :busqueda OR descripcion LIKE :busqueda)";
            $sql .= $condicionBusqueda;
            $sqlCount .= $condicionBusqueda;
            $parametros[':busqueda'] = "%{$busqueda}%";
        }
        
        // Aplicar filtros
        if (!empty($filtros['marca'])) {
            $placeholders = [];
            foreach ($filtros['marca'] as $i => $marca) {
                $key = ":marca{$i}";
                $placeholders[] = $key;
                $parametros[$key] = $marca;
            }
            $inClause = " AND marca IN (" . implode(',', $placeholders) . ")";
            $sql .= $inClause;
            $sqlCount .= $inClause;
        }
        
        if (!empty($filtros['categoria'])) {
            $placeholders = [];
            foreach ($filtros['categoria'] as $i => $categoria) {
                $key = ":categoria{$i}";
                $placeholders[] = $key;
                $parametros[$key] = $categoria;
            }
            $inClause = " AND categoria IN (" . implode(',', $placeholders) . ")";
            $sql .= $inClause;
            $sqlCount .= $inClause;
        }
        
        if (!empty($filtros['sector'])) {
            $placeholders = [];
            foreach ($filtros['sector'] as $i => $sector) {
                $key = ":sector{$i}";
                $placeholders[] = $key;
                $parametros[$key] = $sector;
            }
            $inClause = " AND sector IN (" . implode(',', $placeholders) . ")";
            $sql .= $inClause;
            $sqlCount .= $inClause;
        }
        
        // Obtener total de registros
        $stmtCount = $this->db->prepare($sqlCount);
        foreach ($parametros as $key => $value) {
            $stmtCount->bindValue($key, $value);
        }
        $stmtCount->execute();
        $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Aplicar ordenamiento
        switch ($ordenar) {
            case 'precio_asc':
                $sql .= " ORDER BY precio ASC";
                break;
            case 'precio_desc':
                $sql .= " ORDER BY precio DESC";
                break;
            case 'nombre_asc':
                $sql .= " ORDER BY nombre ASC";
                break;
            case 'nombre_desc':
                $sql .= " ORDER BY nombre DESC";
                break;
            default:
                $sql .= " ORDER BY id DESC";
        }
        
        // Aplicar límite y offset
        $sql .= " LIMIT :limite OFFSET :offset";
        
        // Ejecutar query
        $stmt = $this->db->prepare($sql);
        
        foreach ($parametros as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'productos' => $productos,
            'total' => $total
        ];
    }*/
    public function obtenerProductos($params = [])
{
    $busqueda = isset($params['busqueda']) ? $params['busqueda'] : '';
    $filtros = isset($params['filtros']) ? $params['filtros'] : [];
    $ordenar = isset($params['ordenar']) ? $params['ordenar'] : '';
    $limite = isset($params['limite']) ? (int)$params['limite'] : 12;
    $offset = isset($params['offset']) ? (int)$params['offset'] : 0;

    // Query base con joins para obtener nombres legibles
    $sql = "SELECT 
                p.id,
                p.nombre,
                p.referencia,
                p.precio,
                p.imagen,
                m.nombre AS marca,
                c.nombre AS categoria,
                s.nombre AS sector
            FROM productos p
            LEFT JOIN marcas m ON p.marca_id = m.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            LEFT JOIN sectores s ON p.sector_id = s.id
            WHERE p.activo = 1";

    $sqlCount = "SELECT COUNT(*) as total
                 FROM productos p
                 LEFT JOIN marcas m ON p.marca_id = m.id
                 LEFT JOIN categorias c ON p.categoria_id = c.id
                 LEFT JOIN sectores s ON p.sector_id = s.id
                 WHERE p.activo = 1";

    $parametros = [];

    // 🔍 Búsqueda
    if (!empty($busqueda)) {
        $condicionBusqueda = " AND (
            p.nombre LIKE :busqueda OR 
            p.referencia LIKE :busqueda OR 
            m.nombre LIKE :busqueda OR 
            c.nombre LIKE :busqueda OR 
            s.nombre LIKE :busqueda
        )";
        $sql .= $condicionBusqueda;
        $sqlCount .= $condicionBusqueda;
        $parametros[':busqueda'] = "%{$busqueda}%";
    }

    // 🏷️ Filtro por marca
    if (!empty($filtros['marca'])) {
        $placeholders = [];
        foreach ($filtros['marca'] as $i => $marca) {
            $key = ":marca{$i}";
            $placeholders[] = $key;
            $parametros[$key] = $marca;
        }
        $sql .= " AND m.nombre IN (" . implode(',', $placeholders) . ")";
        $sqlCount .= " AND m.nombre IN (" . implode(',', $placeholders) . ")";
    }

    // 📦 Filtro por categoría
    if (!empty($filtros['categoria'])) {
        $placeholders = [];
        foreach ($filtros['categoria'] as $i => $categoria) {
            $key = ":categoria{$i}";
            $placeholders[] = $key;
            $parametros[$key] = $categoria;
        }
        $sql .= " AND c.nombre IN (" . implode(',', $placeholders) . ")";
        $sqlCount .= " AND c.nombre IN (" . implode(',', $placeholders) . ")";
    }

    // 🏭 Filtro por sector
    if (!empty($filtros['sector'])) {
        $placeholders = [];
        foreach ($filtros['sector'] as $i => $sector) {
            $key = ":sector{$i}";
            $placeholders[] = $key;
            $parametros[$key] = $sector;
        }
        $sql .= " AND s.nombre IN (" . implode(',', $placeholders) . ")";
        $sqlCount .= " AND s.nombre IN (" . implode(',', $placeholders) . ")";
    }

    // 🔢 Total de registros
    $stmtCount = $this->db->prepare($sqlCount);
    foreach ($parametros as $key => $value) {
        $stmtCount->bindValue($key, $value);
    }
    $stmtCount->execute();
    $total = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    // ⚙️ Ordenamiento
    switch ($ordenar) {
        case 'precio_asc':
            $sql .= " ORDER BY p.precio ASC";
            break;
        case 'precio_desc':
            $sql .= " ORDER BY p.precio DESC";
            break;
        case 'nombre_asc':
            $sql .= " ORDER BY p.nombre ASC";
            break;
        case 'nombre_desc':
            $sql .= " ORDER BY p.nombre DESC";
            break;
        default:
            $sql .= " ORDER BY p.id DESC";
    }

    // 🔄 Límite y offset
    $sql .= " LIMIT :limite OFFSET :offset";

    // Ejecutar query principal
    $stmt = $this->db->prepare($sql);
    foreach ($parametros as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
        'productos' => $productos,
        'total' => $total
    ];
}

    public function obtenerUltimosProductos($limite = 5)
    {
        try {
            $sql = "SELECT 
                        p.id,
                        p.nombre,
                        p.referencia,
                        p.precio,
                        p.stock,
                        p.activo,
                        p.imagen,
                        m.nombre AS marca,
                        c.nombre AS categoria,
                        s.nombre AS sector
                    FROM productos p
                    LEFT JOIN marcas m ON p.marca_id = m.id
                    LEFT JOIN categorias c ON p.categoria_id = c.id
                    LEFT JOIN sectores s ON p.sector_id = s.id
                    WHERE p.activo = 1
                    ORDER BY p.id DESC
                    LIMIT :limite";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en obtenerUltimosProductos: " . $e->getMessage());
            return [];
        }
    }

    
    /**
     * Obtener producto por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE id = :id AND activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener productos relacionados
     */
    public function obtenerRelacionados($categoria, $idExcluir, $limite = 4)
    {
        $sql = "SELECT * FROM {$this->tabla} 
                WHERE categoria = :categoria 
                AND id != :idExcluir 
                AND activo = 1 
                ORDER BY RAND() 
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':idExcluir', $idExcluir, PDO::PARAM_INT);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerTodosConRelaciones()
    {
        $sql = "SELECT
                    p.*,
                    m.nombre AS marca_nombre,
                    c.nombre AS categoria_nombre,
                    s.nombre AS sector_nombre
                FROM productos p
                LEFT JOIN marcas m ON p.marca_id = m.id
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN sectores s ON p.sector_id = s.id
                ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener detalle de producto con relaciones y galería
     */
    public function obtenerDetalleConRelaciones($id)
    {
        $sql = "SELECT
                    p.*,
                    m.nombre AS marca_nombre,
                    c.nombre AS categoria_nombre,
                    s.nombre AS sector_nombre
                FROM productos p
                LEFT JOIN marcas m ON p.marca_id = m.id
                LEFT JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN sectores s ON p.sector_id = s.id
                WHERE p.id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            return null;
        }

        // Adjuntar imágenes adicionales sin depender de funciones JSON del motor
        $producto['galeria'] = $this->obtenerImagenes($id);

        return $producto;
    }

    /**
     * Obtener todas las marcas disponibles
     */
    public function obtenerMarcas()
    {
        /*$sql = "SELECT DISTINCT marca_id FROM {$this->tabla} 
                WHERE activo = 1 
                ORDER BY marca_id ASC";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);*/
        $sql = "SELECT id, nombre FROM marcas WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todas las categorías disponibles
     */
    public function obtenerCategorias()
    {
        /*$sql = "SELECT DISTINCT categoria_id FROM {$this->tabla} 
                WHERE activo = 1 
                ORDER BY categoria_id ASC";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);*/
        $sql = "SELECT id, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener todos los sectores disponibles
     */
    public function obtenerSectores()
    {
        /*$sql = "SELECT DISTINCT sector_id FROM {$this->tabla} 
                WHERE activo = 1 
                ORDER BY sector_id ASC";
        $stmt = $this->db->query($sql);
        
        return $stmt->fetchAll(PDO::FETCH_COLUMN);*/
        $sql = "SELECT id, nombre FROM sectores WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Buscar productos para autocompletado
     */
    public function buscarAutocompletado($termino, $limite = 10)
    {
        $sql = "SELECT id, nombre, marca, referencia, precio, imagen 
                FROM {$this->tabla} 
                WHERE activo = 1 
                AND (nombre LIKE :termino OR marca LIKE :termino OR referencia LIKE :termino) 
                LIMIT :limite";
        
        $stmt = $this->db->prepare($sql);
        $terminoLike = "%{$termino}%";
        $stmt->bindParam(':termino', $terminoLike);
        $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo producto
     */
    public function crear($datos)
    {
        $sql = "INSERT INTO {$this->tabla} 
                (nombre, descripcion, marca, categoria, sector, referencia, precio, stock, imagen, activo) 
                VALUES (:nombre, :descripcion, :marca, :categoria, :sector, :referencia, :precio, :stock, :imagen, :activo)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':marca', $datos['marca']);
        $stmt->bindParam(':categoria', $datos['categoria']);
        $stmt->bindParam(':sector', $datos['sector']);
        $stmt->bindParam(':referencia', $datos['referencia']);
        $stmt->bindParam(':precio', $datos['precio']);
        $stmt->bindParam(':stock', $datos['stock']);
        $stmt->bindParam(':imagen', $datos['imagen']);
        $stmt->bindParam(':activo', $datos['activo']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualizar producto
     */
    public function actualizar($id, $datos)
    {
        $sql = "UPDATE {$this->tabla} SET 
                nombre = :nombre,
                descripcion = :descripcion,
                marca = :marca,
                categoria = :categoria,
                sector = :sector,
                referencia = :referencia,
                precio = :precio,
                stock = :stock,
                imagen = :imagen,
                activo = :activo
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':marca', $datos['marca']);
        $stmt->bindParam(':categoria', $datos['categoria']);
        $stmt->bindParam(':sector', $datos['sector']);
        $stmt->bindParam(':referencia', $datos['referencia']);
        $stmt->bindParam(':precio', $datos['precio']);
        $stmt->bindParam(':stock', $datos['stock']);
        $stmt->bindParam(':imagen', $datos['imagen']);
        $stmt->bindParam(':activo', $datos['activo']);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar producto (soft delete)
     */
    public function eliminar($id)
    {
        $sql = "UPDATE {$this->tabla} SET activo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Obtener imágenes adicionales de un producto
     */
    public function obtenerImagenes($productoId)
    {
        $sql = "SELECT id, imagen, orden
                FROM producto_imagenes
                WHERE producto_id = :producto_id
                ORDER BY orden ASC, id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Registrar una nueva imagen adicional para un producto
     */
    public function agregarImagen($productoId, $rutaImagen, $orden = 0)
    {
        $sql = "INSERT INTO producto_imagenes (producto_id, imagen, orden)
                VALUES (:producto_id, :imagen, :orden)";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->bindParam(':imagen', $rutaImagen);
        $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);

        return $stmt->execute();
    }

     /**
      * Eliminar todas las imágenes adicionales de un producto
      */ 
    public function eliminarImagenesProducto($productoId)
    {
        $sql = "DELETE FROM producto_imagenes WHERE producto_id = :producto_id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
?>