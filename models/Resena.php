<?php
class Resena {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear($id_usuario, $id_producto, $calificacion, $comentario) {
        $sql = "INSERT INTO resena (ID_Usuario, ID_Producto, Calificacion, Comentario, Fecha)
                VALUES (?, ?, ?, ?, NOW())";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_usuario, $id_producto, $calificacion, $comentario]);
    }

    public function obtenerPorProducto($id_producto) {
        $sql = "SELECT r.*, u.Nombre, u.Apellido
                FROM resena r
                INNER JOIN usuario u ON r.ID_Usuario = u.ID_Usuario
                WHERE r.ID_Producto = ?
                ORDER BY r.Fecha DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_producto]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
