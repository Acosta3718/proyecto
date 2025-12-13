<?php
/**
 * Clase Database
 * Gestiona la conexión a la base de datos usando PDO
 */

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $charset;
    private $conn;
    
    public function __construct()
    {
        // Cargar configuración
        require_once __DIR__ . '/../../config/database.php';
        
        $this->host = DB_HOST;
        $this->db_name = DB_NAME;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->charset = DB_CHARSET;
    }
    
    /**
     * Obtener conexión PDO
     */
    public function getConnection()
    {
        $this->conn = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
            die();
        }
        
        return $this->conn;
    }
    
    /**
     * Cerrar conexión
     */
    public function closeConnection()
    {
        $this->conn = null;
    }
    
    /**
     * Iniciar transacción
     */
    public function beginTransaction()
    {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Confirmar transacción
     */
    public function commit()
    {
        return $this->conn->commit();
    }
    
    /**
     * Revertir transacción
     */
    public function rollback()
    {
        return $this->conn->rollBack();
    }
}
?>