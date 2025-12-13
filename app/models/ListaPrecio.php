<?php
/**
 * Modelo de Lista de Precios
 * Gestiona listas de precios y precios por producto
 */

require_once __DIR__ . '/Database.php';

class ListaPrecio
{
    private $db;
    private $tabla = 'listas_precios';
    private $tablaProductoPrecios = 'producto_precios';
    
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtener todas las listas
     */
    public function obtenerTodos($filtros = [])
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE 1=1";
        $params = [];
        
        if (isset($filtros['activo'])) {
            $sql .= " AND activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }
        
        if (!empty($filtros['tipo'])) {
            $sql .= " AND tipo = :tipo";
            $params[':tipo'] = $filtros['tipo'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda)";
            $params[':busqueda'] = "%{$filtros['busqueda']}%";
        }
        
        $sql .= " ORDER BY nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener lista por ID
     */
    public function obtenerPorId($id)
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener solo listas activas
     */
    public function obtenerActivos()
    {
        return $this->obtenerTodos(['activo' => 1]);
    }
    
    /**
     * Crear nueva lista
     */
    public function crear($datos)
    {
        $sql = "INSERT INTO {$this->tabla} 
                (nombre, descripcion, tipo, descuento_porcentaje, activo, fecha_inicio, fecha_fin) 
                VALUES (:nombre, :descripcion, :tipo, :descuento_porcentaje, :activo, :fecha_inicio, :fecha_fin)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo', $datos['tipo']);
        $stmt->bindParam(':descuento_porcentaje', $datos['descuento_porcentaje']);
        $stmt->bindParam(':activo', $datos['activo']);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualizar lista
     */
    public function actualizar($id, $datos)
    {
        $sql = "UPDATE {$this->tabla} SET 
                nombre = :nombre,
                descripcion = :descripcion,
                tipo = :tipo,
                descuento_porcentaje = :descuento_porcentaje,
                activo = :activo,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':tipo', $datos['tipo']);
        $stmt->bindParam(':descuento_porcentaje', $datos['descuento_porcentaje']);
        $stmt->bindParam(':activo', $datos['activo']);
        $stmt->bindParam(':fecha_inicio', $datos['fecha_inicio']);
        $stmt->bindParam(':fecha_fin', $datos['fecha_fin']);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar lista (soft delete)
     */
    public function eliminar($id)
    {
        $sql = "UPDATE {$this->tabla} SET activo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Asignar producto a lista con precio específico
     */
    public function asignarProducto($listaId, $productoId, $precio)
    {
        $sql = "INSERT INTO {$this->tablaProductoPrecios} 
                (lista_precio_id, producto_id, precio) 
                VALUES (:lista_id, :producto_id, :precio)
                ON DUPLICATE KEY UPDATE precio = :precio_update";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lista_id', $listaId, PDO::PARAM_INT);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':precio_update', $precio);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener productos de una lista
     */
    public function obtenerProductos($listaId)
    {
        $sql = "SELECT p.*, pp.precio as precio_lista, pp.id as precio_id
                FROM productos p
                INNER JOIN {$this->tablaProductoPrecios} pp ON p.id = pp.producto_id
                WHERE pp.lista_precio_id = :lista_id
                ORDER BY p.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lista_id', $listaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener productos NO asignados a una lista
     */
    public function obtenerProductosNoAsignados($listaId)
    {
        $sql = "SELECT p.*
                FROM productos p
                WHERE p.activo = 1
                AND p.id NOT IN (
                    SELECT producto_id 
                    FROM {$this->tablaProductoPrecios} 
                    WHERE lista_precio_id = :lista_id
                )
                ORDER BY p.nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lista_id', $listaId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar precio de producto en lista
     */
    public function actualizarPrecioProducto($listaId, $productoId, $precio)
    {
        $sql = "UPDATE {$this->tablaProductoPrecios} 
                SET precio = :precio 
                WHERE lista_precio_id = :lista_id AND producto_id = :producto_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':lista_id', $listaId, PDO::PARAM_INT);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar producto de lista
     */
    public function eliminarProducto($listaId, $productoId)
    {
        $sql = "DELETE FROM {$this->tablaProductoPrecios} 
                WHERE lista_precio_id = :lista_id AND producto_id = :producto_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lista_id', $listaId, PDO::PARAM_INT);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Aplicar descuento a todos los productos de la lista
     */
    public function aplicarDescuentoGeneral($listaId)
    {
        $lista = $this->obtenerPorId($listaId);
        
        if (!$lista || $lista['descuento_porcentaje'] == 0) {
            return false;
        }
        
        $descuento = $lista['descuento_porcentaje'];
        
        $sql = "INSERT INTO {$this->tablaProductoPrecios} (lista_precio_id, producto_id, precio)
                SELECT :lista_id, id, precio * (1 - :descuento / 100)
                FROM productos
                WHERE activo = 1
                ON DUPLICATE KEY UPDATE precio = productos.precio * (1 - :descuento_update / 100)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':lista_id', $listaId, PDO::PARAM_INT);
        $stmt->bindParam(':descuento', $descuento);
        $stmt->bindParam(':descuento_update', $descuento);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas
     */
    public function obtenerEstadisticas($id)
    {
        $sql = "SELECT COUNT(*) as total_productos 
                FROM {$this->tablaProductoPrecios} 
                WHERE lista_precio_id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>