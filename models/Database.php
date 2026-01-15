<?php
// models/Database.php
class Database {
    private $host = 'localhost';
    private $db_name = 'tulook';
    private $username = 'root';
    private $password = '';
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }

    public function __call($name, $arguments) {
        error_log("⚠️ Intento de acceder a acción no definida: $name");
        
        // Redirigir al dashboard principal
        $_SESSION['msg'] = "La página solicitada no existe. Redirigiendo al dashboard.";
        $_SESSION['msg_type'] = "warning";
        
        header("Location: " . BASE_URL . "?c=Admin&a=index");
        exit;
    }
}
?>