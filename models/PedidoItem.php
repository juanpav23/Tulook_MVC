<?php

class PedidoItem {
    private $conn;
    private $table = "factura_producto";

    public $ID_Factura;
    public $ID_Producto; 
    public $ID_Articulo;
    public $Cantidad;
    public $Precio_Unitario;
    public $Subtotal;
    public $Descuento_Aplicado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function agregarItem() {
        $sql = "INSERT INTO $this->table 
        (ID_Factura, ID_Producto, ID_Articulo, Cantidad, Precio_Unitario, Subtotal, Descuento_Aplicado)
        VALUES (:factura, :producto, :articulo, :cantidad, :precio, :subtotal, :descuento)";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(":factura", $this->ID_Factura);
        $stmt->bindParam(":producto", $this->ID_Producto);
        $stmt->bindParam(":articulo", $this->ID_Articulo);
        $stmt->bindParam(":cantidad", $this->Cantidad);
        $stmt->bindParam(":precio", $this->Precio_Unitario);
        $stmt->bindParam(":subtotal", $this->Subtotal);
        $stmt->bindParam(":descuento", $this->Descuento_Aplicado);

        return $stmt->execute();
    }
}
