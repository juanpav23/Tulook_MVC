<?php

class Pedido {
    private $conn;
    private $table = "factura";

    public $ID_Factura;
    public $ID_Usuario;
    public $Fecha_Factura;
    public $Monto_Total;
    public $Direccion_Envio;
    public $Estado;
    public $ID_Metodo_Pago;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crearFactura() {
        $sql = "INSERT INTO $this->table 
        (ID_Usuario, Fecha_Factura, Monto_Total, Direccion_Envio, Estado, ID_Metodo_Pago)
        VALUES (:usuario, NOW(), :monto, :direccion, 'PENDIENTE', :metodo)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":usuario", $this->ID_Usuario);
        $stmt->bindParam(":monto", $this->Monto_Total);
        $stmt->bindParam(":direccion", $this->Direccion_Envio);
        $stmt->bindParam(":metodo", $this->ID_Metodo_Pago);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function obtenerFactura($id) {
        $sql = "SELECT * FROM $this->table WHERE ID_Factura = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
