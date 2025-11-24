<?php
// models/Compra.php

class Compra {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear factura (Dirección_Envio guarda ID de dirección)
    public function crearFactura($id_usuario, $direccion_id, $metodo_pago, $total, $codigo_acceso) {
        $sql = "INSERT INTO factura 
                (ID_Usuario, Fecha_Factura, Monto_Total, Direccion_Envio, Estado, ID_Metodo_Pago, Codigo_Acceso)
                VALUES (?, NOW(), ?, ?, 'Confirmado', ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $ok = $stmt->execute([
            $id_usuario,
            $total,
            $direccion_id,
            $metodo_pago,
            $codigo_acceso
        ]);

        return $ok ? $this->conn->lastInsertId() : 0;
    }

    // Guardar item de factura
    public function agregarItem($id_factura, $id_producto, $id_articulo, $cantidad, $precio_unitario, $subtotal, $descuento_aplicado = 0) {

        $sql = "INSERT INTO factura_producto
                (ID_Factura, ID_Articulo, ID_Producto, Cantidad, Precio_Unitario, Precio, Subtotal, Descuento_Aplicado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $id_factura,
            $id_articulo ?: null,
            $id_producto ?: null,
            $cantidad,
            $precio_unitario,
            $precio_unitario * $cantidad,
            $subtotal,
            $descuento_aplicado
        ]);
    }

    // Actualizar stock
    public function descontarStock($id, $cantidad, $tipo = 'variante') {
        if ($tipo === "base") {
            $sql = "UPDATE articulo 
                    SET Cantidad = GREATEST(Cantidad - ?, 0) 
                    WHERE ID_Articulo = ? AND Cantidad >= ?";
        } else {
            $sql = "UPDATE producto 
                    SET Cantidad = GREATEST(Cantidad - ?, 0) 
                    WHERE ID_Producto = ? AND Cantidad >= ?";
        }

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$cantidad, $id, $cantidad]);
    }

    // Verificar stock
    public function stockDisponible($id, $cant, $tipo = 'variante') {
        if ($tipo === "base") {
            $sql = "SELECT Cantidad FROM articulo WHERE ID_Articulo = ?";
        } else {
            $sql = "SELECT Cantidad FROM producto WHERE ID_Producto = ?";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return $r && $r['Cantidad'] >= $cant;
    }

    // DATOS COMPLETOS DE FACTURA (con dirección incluida) - SIN TELEFONO
    public function obtenerFacturaDetalle($id_factura) {

        $sql = "SELECT 
                f.*, 
                u.Nombre, u.Apellido, u.Correo,
                mp.T_Pago,
                d.Direccion, d.Ciudad, d.Departamento, d.CodigoPostal
            FROM factura f
            LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
            LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
            LEFT JOIN direccion d ON f.Direccion_Envio = d.ID_Direccion
            WHERE f.ID_Factura = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_factura]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Items de factura - CONSULTA CORREGIDA CON LA ESTRUCTURA REAL
    public function obtenerFacturaItems($id_factura) {

        $sql = "SELECT 
                fp.ID_FacturaProducto,
                fp.Cantidad,
                fp.ID_Producto,
                fp.ID_Articulo,
                fp.Precio_Unitario,
                fp.Subtotal,
                fp.Descuento_Aplicado,
                COALESCE(p.Nombre_Producto, a.N_Articulo) as Nombre_Producto,
                a.N_Articulo,
                COALESCE(p_col.N_Color, a_col.N_Color) as N_Color,
                COALESCE(p_talla.N_Talla, a_talla.N_Talla) as N_Talla
            FROM factura_producto fp
            LEFT JOIN producto p ON fp.ID_Producto = p.ID_Producto
            LEFT JOIN articulo a ON fp.ID_Articulo = a.ID_Articulo
            LEFT JOIN color p_col ON p.ID_Color = p_col.ID_Color
            LEFT JOIN color a_col ON a.ID_Color = a_col.ID_Color
            LEFT JOIN talla p_talla ON p.ID_Talla = p_talla.ID_Talla
            LEFT JOIN talla a_talla ON a.ID_Talla = a_talla.ID_Talla
            WHERE fp.ID_Factura = ?
            ORDER BY fp.ID_FacturaProducto ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_factura]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Historial
    public function obtenerCompras($id_usuario) {
        $stmt = $this->conn->prepare("SELECT * FROM factura WHERE ID_Usuario = ? ORDER BY Fecha_Factura DESC");
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}