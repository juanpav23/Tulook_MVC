<?php
class Factura {
    private $conn;
    private $table = "factura";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        return $this->conn->query("SELECT * FROM factura");
    }

    public function getById($id) {
        $sql = "SELECT * FROM factura WHERE ID_Factura = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        // Solo usar campos que realmente existen en tu base de datos
        $sql = "INSERT INTO factura (ID_Usuario) VALUES (?)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$data['ID_Usuario']]);
    }

    public function update($id, $data) {
        // Solo actualizar campos que existen
        $sql = "UPDATE factura SET ID_Usuario = ? WHERE ID_Factura = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$data['ID_Usuario'], $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM factura WHERE ID_Factura = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>