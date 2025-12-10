<?php
// models/Compra.php

class Compra {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear factura
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

    // Guardar item de factura - SOLO PRODUCTOS
    public function agregarItem($id_factura, $id_producto, $cantidad, $precio_unitario, $subtotal, $descuento_aplicado = 0) {
        // Obtener el ID_Articulo del producto
        $sql_articulo = "SELECT ID_Articulo FROM producto WHERE ID_Producto = ?";
        $stmt_articulo = $this->conn->prepare($sql_articulo);
        $stmt_articulo->execute([$id_producto]);
        $producto = $stmt_articulo->fetch(PDO::FETCH_ASSOC);
        
        $id_articulo = $producto ? $producto['ID_Articulo'] : null;

        $sql = "INSERT INTO factura_producto
                (ID_Factura, ID_Articulo, ID_Producto, Cantidad, Precio_Unitario, Precio, Subtotal, Descuento_Aplicado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            $id_factura,
            $id_articulo,
            $id_producto,
            $cantidad,
            $precio_unitario,
            $precio_unitario * $cantidad,
            $subtotal,
            $descuento_aplicado
        ]);
    }

    // Actualizar stock - SOLO PARA PRODUCTOS
    public function descontarStock($id_producto, $cantidad) {
        $sql = "UPDATE producto 
                SET Cantidad = GREATEST(Cantidad - ?, 0) 
                WHERE ID_Producto = ? AND Cantidad >= ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$cantidad, $id_producto, $cantidad]);
    }

    // Verificar stock - SOLO PARA PRODUCTOS
    public function stockDisponible($id_producto, $cant) {
        $sql = "SELECT Cantidad FROM producto WHERE ID_Producto = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_producto]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return $r && $r['Cantidad'] >= $cant;
    }

    // DATOS COMPLETOS DE FACTURA
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

    // Items de factura - CON ATRIBUTOS DINÁMICOS COMPLETOS
    public function obtenerFacturaItems($id_factura) {
        $sql = "SELECT 
                fp.ID_FacturaProducto,
                fp.Cantidad,
                fp.ID_Producto,
                fp.ID_Articulo,
                fp.Precio_Unitario,
                fp.Subtotal,
                fp.Descuento_Aplicado,
                
                -- Información del producto
                p.Nombre_Producto,
                a.N_Articulo,
                COALESCE(p.Nombre_Producto, a.N_Articulo) as Nombre_Final,
                
                -- ✅ ATRIBUTOS DINÁMICOS: OBTENER TODOS LOS ATRIBUTOS DEL PRODUCTO
                p.ID_Atributo1,
                p.ValorAtributo1,
                p.ID_Atributo2,
                p.ValorAtributo2,
                p.ID_Atributo3,
                p.ValorAtributo3,
                
                -- ✅ OBTENER NOMBRES DE LOS ATRIBUTOS
                ta1.Nombre as Nombre_Atributo1,
                ta2.Nombre as Nombre_Atributo2,
                ta3.Nombre as Nombre_Atributo3,
                
                -- ✅ INFORMACIÓN ESPECÍFICA DE COLOR (si existe)
                (SELECT 
                    CASE 
                        WHEN p.ID_Atributo1 = 2 THEN p.ValorAtributo1
                        WHEN p.ID_Atributo2 = 2 THEN p.ValorAtributo2
                        WHEN p.ID_Atributo3 = 2 THEN p.ValorAtributo3
                        ELSE NULL
                    END
                ) as Color_Valor,
                
                -- ✅ INFORMACIÓN ESPECÍFICA DE TALLA (si existe)
                (SELECT 
                    CASE 
                        WHEN p.ID_Atributo1 = 3 THEN p.ValorAtributo1
                        WHEN p.ID_Atributo2 = 3 THEN p.ValorAtributo2
                        WHEN p.ID_Atributo3 = 3 THEN p.ValorAtributo3
                        ELSE NULL
                    END
                ) as Talla_Valor,
                
                -- Foto (priorizar producto, luego artículo)
                COALESCE(p.Foto, a.Foto) as Foto

            FROM factura_producto fp
            
            LEFT JOIN producto p ON fp.ID_Producto = p.ID_Producto
            LEFT JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
            
            -- ✅ JOIN CON TABLAS DE TIPO_ATRIBUTO PARA OBTENER NOMBRES
            LEFT JOIN tipo_atributo ta1 ON p.ID_Atributo1 = ta1.ID_TipoAtributo
            LEFT JOIN tipo_atributo ta2 ON p.ID_Atributo2 = ta2.ID_TipoAtributo
            LEFT JOIN tipo_atributo ta3 ON p.ID_Atributo3 = ta3.ID_TipoAtributo
            
            WHERE fp.ID_Factura = ?
            ORDER BY fp.ID_FacturaProducto ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_factura]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método auxiliar para formatear atributos de un item
    public function formatearEspecificacionesItem($item) {
        $especificaciones = [];
        
        // Verificar si hay atributos dinámicos
        if (!empty($item['ID_Atributo1']) && !empty($item['ValorAtributo1'])) {
            $nombre = !empty($item['Nombre_Atributo1']) ? $item['Nombre_Atributo1'] : 'Atributo';
            $especificaciones[] = $nombre . ': ' . $item['ValorAtributo1'];
        }
        
        if (!empty($item['ID_Atributo2']) && !empty($item['ValorAtributo2'])) {
            $nombre = !empty($item['Nombre_Atributo2']) ? $item['Nombre_Atributo2'] : 'Atributo';
            $especificaciones[] = $nombre . ': ' . $item['ValorAtributo2'];
        }
        
        if (!empty($item['ID_Atributo3']) && !empty($item['ValorAtributo3'])) {
            $nombre = !empty($item['Nombre_Atributo3']) ? $item['Nombre_Atributo3'] : 'Atributo';
            $especificaciones[] = $nombre . ': ' . $item['ValorAtributo3'];
        }
        
        return $especificaciones;
    }

    // Historial de compras
    public function obtenerCompras($id_usuario) {
        $stmt = $this->conn->prepare("SELECT * FROM factura WHERE ID_Usuario = ? ORDER BY Fecha_Factura DESC");
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener información completa de un producto
    public function obtenerProductoCompleto($id_producto) {
        $sql = "SELECT 
                p.*,
                a.N_Articulo,
                a.Foto as Foto_Articulo,
                a.ID_Precio,
                pr.Valor as Precio_Base,
                c.N_Color,
                c.CodigoHex,
                t.N_Talla,
                st.Sobrecosto as Sobrecosto_Talla,
                COALESCE(p.Foto, a.Foto) as Foto_Final,
                COALESCE(p.Nombre_Producto, a.N_Articulo) as Nombre_Final
            FROM producto p
            LEFT JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
            LEFT JOIN precio pr ON a.ID_Precio = pr.ID_precio
            LEFT JOIN color c ON p.ID_Color = c.ID_Color
            LEFT JOIN talla t ON p.ID_Talla = t.ID_Talla
            LEFT JOIN sobrecosto_talla st ON p.ID_Talla = st.ID_Talla
            WHERE p.ID_Producto = ?";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_producto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}