<?php
class MetodoPago {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerMetodosPago() {
        $sql = "SELECT * FROM metodo_pago";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
