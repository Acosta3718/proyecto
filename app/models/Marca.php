<?php
/**
 * Modelo de Marca
 * Gestiona todas las operaciones de marcas
 */

require_once __DIR__ . '/Database.php';

class Marca
{
    private $db;
    private $tabla = 'marcas';
    
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtener todas las marcas
     */
    public function obtenerTodos($filtros = [])
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE 1=1";
        $params = [];
        
        if (isset($filtros['activo'])) {
            $sql .= " AND activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (nombre LIKE :busqueda OR descripcion LIKE :busqueda)";
            $params[':busqueda'] = "%{$filtros['busqueda']}%";
        }
        
        $sql .= " ORDER BY orden ASC, nombre ASC";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener marca por ID
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
     * Obtener solo marcas activas
     */
    public function obtenerActivos()
    {
        return $this->obtenerTodos(['activo' => 1]);
    }
    
    /**
     * Crear nueva marca
     */
    public function crear($datos)
    {
        $sql = "INSERT INTO {$this->tabla} 
                (nombre, slug, descripcion, logo, sitio_web, activo, orden) 
                VALUES (:nombre, :slug, :descripcion, :logo, :sitio_web, :activo, :orden)";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':slug', $datos['slug']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':logo', $datos['logo']);
        $stmt->bindParam(':sitio_web', $datos['sitio_web']);
        $stmt->bindParam(':activo', $datos['activo']);
        $stmt->bindParam(':orden', $datos['orden']);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Actualizar marca
     */
    public function actualizar($id, $datos)
    {
        $sql = "UPDATE {$this->tabla} SET 
                nombre = :nombre,
                slug = :slug,
                descripcion = :descripcion,
                logo = :logo,
                sitio_web = :sitio_web,
                activo = :activo,
                orden = :orden
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':slug', $datos['slug']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':logo', $datos['logo']);
        $stmt->bindParam(':sitio_web', $datos['sitio_web']);
        $stmt->bindParam(':activo', $datos['activo']);
        $stmt->bindParam(':orden', $datos['orden']);
        
        return $stmt->execute();
    }
    
    /**
     * Eliminar marca (soft delete)
     */
    public function eliminar($id)
    {
        $sql = "UPDATE {$this->tabla} SET activo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Verificar si slug existe
     */
    public function slugExiste($slug, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->tabla} WHERE slug = :slug";
        
        if ($excluirId) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':slug', $slug);
        
        if ($excluirId) {
            $stmt->bindParam(':id', $excluirId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }
    
    /**
     * Cambiar orden
     */
    public function cambiarOrden($id, $orden)
    {
        $sql = "UPDATE {$this->tabla} SET orden = :orden WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Obtener estadísticas
     */
    public function obtenerEstadisticas($id)
    {
        $sql = "SELECT COUNT(*) as total_productos 
                FROM productos 
                WHERE marca_id = :id AND activo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>