<?php
/**
 * Modelo de Usuario
 * Gestiona autenticación, usuarios y permisos
 */

require_once __DIR__ . '/Database.php';

class Usuario
{
    private $db;
    private $tabla = 'usuarios';
    
    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Autenticar usuario
     */
    public function autenticar($email, $password)
    {
        $sql = "SELECT * FROM {$this->tabla} WHERE email = :email AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        /*if ($usuario && password_verify($password, $usuario['password'])) {
            
            // Actualizar último acceso
            $this->actualizarUltimoAcceso($usuario['id']);
            
            // Registrar auditoría
            $this->registrarAuditoria($usuario['id'], 'login', 'autenticacion', 'Inicio de sesión exitoso');
            
            return $usuario;
        }*/
        
        return $usuario;
    }
    
    /**
     * Obtener usuario por ID
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
     * Obtener todos los usuarios con filtros
     */
    public function obtenerTodos($filtros = [])
    {
        $sql = "SELECT id, nombre, apellido, email, telefono, rol, activo, 
                ultimo_acceso, fecha_registro FROM {$this->tabla} WHERE 1=1";
        $params = [];
        
        if (!empty($filtros['rol'])) {
            $sql .= " AND rol = :rol";
            $params[':rol'] = $filtros['rol'];
        }
        
        if (isset($filtros['activo'])) {
            $sql .= " AND activo = :activo";
            $params[':activo'] = $filtros['activo'];
        }
        
        if (!empty($filtros['busqueda'])) {
            $sql .= " AND (nombre LIKE :busqueda OR apellido LIKE :busqueda OR email LIKE :busqueda)";
            $params[':busqueda'] = "%{$filtros['busqueda']}%";
        }
        
        $sql .= " ORDER BY fecha_registro DESC";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crear nuevo usuario
     */
    public function crear($datos)
    {
        $sql = "INSERT INTO {$this->tabla} 
                (nombre, apellido, email, password, telefono, direccion, rol, activo, avatar) 
                VALUES (:nombre, :apellido, :email, :password, :telefono, :direccion, :rol, :activo, :avatar)";
        
        $stmt = $this->db->prepare($sql);
        
        // Hashear contraseña
        $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
        
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido', $datos['apellido']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':rol', $datos['rol']);
        $stmt->bindParam(':activo', $datos['activo']);
        $stmt->bindParam(':avatar', $datos['avatar']);
        
        if ($stmt->execute()) {
            $usuarioId = $this->db->lastInsertId();
            $this->registrarAuditoria($usuarioId, 'crear', 'usuarios', 'Usuario creado: ' . $datos['email']);
            return $usuarioId;
        }
        
        return false;
    }
    
    /**
     * Actualizar usuario
     */
    public function actualizar($id, $datos)
    {
        $sql = "UPDATE {$this->tabla} SET 
                nombre = :nombre,
                apellido = :apellido,
                email = :email,
                telefono = :telefono,
                direccion = :direccion,
                rol = :rol,
                activo = :activo";
        
        // Solo actualizar password si se proporciona
        if (!empty($datos['password'])) {
            $sql .= ", password = :password";
        }
        
        if (!empty($datos['avatar'])) {
            $sql .= ", avatar = :avatar";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':apellido', $datos['apellido']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':telefono', $datos['telefono']);
        $stmt->bindParam(':direccion', $datos['direccion']);
        $stmt->bindParam(':rol', $datos['rol']);
        $stmt->bindParam(':activo', $datos['activo']);
        
        if (!empty($datos['password'])) {
            $passwordHash = password_hash($datos['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $passwordHash);
        }
        
        if (!empty($datos['avatar'])) {
            $stmt->bindParam(':avatar', $datos['avatar']);
        }
        
        $resultado = $stmt->execute();
        
        if ($resultado) {
            $this->registrarAuditoria($id, 'actualizar', 'usuarios', 'Usuario actualizado: ' . $datos['email']);
        }
        
        return $resultado;
    }
    
    /**
     * Eliminar usuario (soft delete)
     */
    public function eliminar($id)
    {
        $sql = "UPDATE {$this->tabla} SET activo = 0 WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        $resultado = $stmt->execute();
        
        if ($resultado) {
            $this->registrarAuditoria($id, 'eliminar', 'usuarios', 'Usuario eliminado ID: ' . $id);
        }
        
        return $resultado;
    }
    
    /**
     * Verificar si email ya existe
     */
    public function emailExiste($email, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->tabla} WHERE email = :email";
        
        if ($excluirId) {
            $sql .= " AND id != :id";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        
        if ($excluirId) {
            $stmt->bindParam(':id', $excluirId, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }
    
    /**
     * Verificar permisos del usuario
     */
    public function tienePermiso($usuarioId, $permisoClave)
    {
        $usuario = $this->obtenerPorId($usuarioId);
        
        if (!$usuario) {
            return false;
        }
        
        // Superadmin tiene todos los permisos
        if ($usuario['rol'] === 'superadmin') {
            return true;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM rol_permisos rp 
                INNER JOIN permisos p ON rp.permiso_id = p.id 
                WHERE rp.rol = :rol AND p.clave = :permiso AND p.activo = 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':rol', $usuario['rol']);
        $stmt->bindParam(':permiso', $permisoClave);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['total'] > 0;
    }
    
    /**
     * Obtener permisos del usuario
     */
    public function obtenerPermisos($usuarioId)
    {
        $usuario = $this->obtenerPorId($usuarioId);
        
        if (!$usuario) {
            return [];
        }
        
        // Superadmin tiene todos los permisos
        if ($usuario['rol'] === 'superadmin') {
            $sql = "SELECT * FROM permisos WHERE activo = 1";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        $sql = "SELECT p.* 
                FROM rol_permisos rp 
                INNER JOIN permisos p ON rp.permiso_id = p.id 
                WHERE rp.rol = :rol AND p.activo = 1
                ORDER BY p.modulo, p.nombre";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':rol', $usuario['rol']);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Actualizar último acceso
     */
    private function actualizarUltimoAcceso($usuarioId)
    {
        $sql = "UPDATE {$this->tabla} SET ultimo_acceso = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    /**
     * Registrar auditoría
     */
    private function registrarAuditoria($usuarioId, $accion, $modulo, $descripcion)
    {
        $sql = "INSERT INTO auditoria (usuario_id, accion, modulo, descripcion, ip_address) 
                VALUES (:usuario_id, :accion, :modulo, :descripcion, :ip)";
        
        $stmt = $this->db->prepare($sql);
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        $stmt->bindParam(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindParam(':accion', $accion);
        $stmt->bindParam(':modulo', $modulo);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':ip', $ip);
        
        $stmt->execute();
    }
    
    /**
     * Cambiar contraseña
     */
    public function cambiarPassword($usuarioId, $passwordActual, $passwordNuevo)
    {
        $usuario = $this->obtenerPorId($usuarioId);
        
        if (!$usuario) {
            return false;
        }
        
        // Verificar contraseña actual
        if (!password_verify($passwordActual, $usuario['password'])) {
            return false;
        }
        
        $sql = "UPDATE {$this->tabla} SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $passwordHash = password_hash($passwordNuevo, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':id', $usuarioId, PDO::PARAM_INT);
        
        $resultado = $stmt->execute();
        
        if ($resultado) {
            $this->registrarAuditoria($usuarioId, 'cambio_password', 'usuarios', 'Contraseña cambiada');
        }
        
        return $resultado;
    }
}
?>