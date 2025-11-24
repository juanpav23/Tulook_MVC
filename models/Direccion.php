<?php
class Direccion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerDireccionesUsuario($id_usuario) {
        $sql = "SELECT * FROM direccion WHERE ID_Usuario = ? ORDER BY Predeterminada DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ AGREGAR ESTE MÉTODO NUEVO
    public function obtenerDireccionPorId($id_direccion) {
        $sql = "SELECT * FROM direccion WHERE ID_Direccion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_direccion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>