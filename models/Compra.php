<?php
class Compra {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // =======================================================
    // 🧾 CREAR FACTURA CON IVA
    // =======================================================
    public function crearFacturaConIVA($id_usuario, $id_direccion, $id_metodo_pago, $subtotal, $iva, $total_con_iva, $codigo_acceso = '') {
        try {
            // Agregar fecha actual automáticamente
            $fecha_actual = date('Y-m-d H:i:s');
            
            $sql = "INSERT INTO factura (ID_Usuario, ID_Direccion, ID_Metodo_Pago, Subtotal, IVA, Monto_Total, Estado, Codigo_Acceso, Fecha_Factura) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Confirmado', ?, ?)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $id_usuario, 
                $id_direccion, 
                $id_metodo_pago, 
                $subtotal, 
                $iva, 
                $total_con_iva, 
                $codigo_acceso,
                $fecha_actual  // Agregar fecha actual
            ]);
            
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creando factura: " . $e->getMessage());
            return false;
        }
    }
    
    // =======================================================
    // 🧾 CREAR FACTURA (COMPATIBILIDAD)
    // =======================================================
    public function crearFactura($id_usuario, $id_direccion, $id_metodo_pago, $total, $codigo_acceso = '') {
        // Calcular IVA (19%)
        $iva = $total * 0.19;
        $subtotal = $total - $iva;
        
        return $this->crearFacturaConIVA($id_usuario, $id_direccion, $id_metodo_pago, $subtotal, $iva, $total, $codigo_acceso);
    }
    
    // =======================================================
    // 📝 AGREGAR ITEM A FACTURA
    // =======================================================
    public function agregarItem($id_factura, $id_producto, $cantidad, $precio_unitario, $subtotal, $descuento_aplicado = 0) {
    try {
        // DEBUG DETALLADO
        error_log("DEBUG agregarItem:");
        error_log("  - Factura ID: " . $id_factura);
        error_log("  - Producto ID: " . $id_producto);
        error_log("  - Cantidad: " . $cantidad);
        error_log("  - Precio Unitario: " . $precio_unitario);
        error_log("  - Subtotal: " . $subtotal);
        error_log("  - Descuento: " . $descuento_aplicado);
        
        // Verificar si el producto existe
        $check_sql = "SELECT ID_Producto, Cantidad FROM producto WHERE ID_Producto = ?";
        $check_stmt = $this->conn->prepare($check_sql);
        $check_stmt->execute([$id_producto]);
        $producto = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$producto) {
            error_log("ERROR: Producto ID $id_producto no existe");
            return false;
        }
        
        // Verificar stock
        if ($producto['Cantidad'] < $cantidad) {
            error_log("ERROR: Stock insuficiente. Disponible: " . $producto['Cantidad']);
            return false;
        }
        
        // Insertar item (columnas según tu tabla)
        $sql = "INSERT INTO factura_producto 
                (ID_Factura, ID_Producto, Cantidad, Precio_Unitario, Precio, Subtotal, Descuento_Aplicado) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        // El campo "Precio" es el mismo que Precio_Unitario según tu estructura
        $precio = $precio_unitario;
        
        $stmt = $this->conn->prepare($sql);
        $params = [
            $id_factura, 
            $id_producto,
            $cantidad, 
            $precio_unitario,
            $precio, // Agregar campo Precio
            $subtotal, 
            $descuento_aplicado
        ];
        
        error_log("  - Parámetros SQL: " . print_r($params, true));
        
        $result = $stmt->execute($params);
        
        if (!$result) {
            $errorInfo = $stmt->errorInfo();
            error_log("ERROR SQL: " . implode(", ", $errorInfo));
            return false;
        }
        
        // Descontar stock inmediatamente
        $this->descontarStock($id_producto, $cantidad);
        
        $lastId = $this->conn->lastInsertId();
        error_log("  - Item insertado con ID: " . $lastId);
        
        return true;
    } catch (PDOException $e) {
        error_log("EXCEPCIÓN en agregarItem: " . $e->getMessage());
        return false;
    }
}
    
    // =======================================================
    // 📦 VERIFICAR STOCK DISPONIBLE
    // =======================================================
    public function stockDisponible($id_producto, $cantidad_solicitada) {
        try {
            $sql = "SELECT Cantidad FROM producto WHERE ID_Producto = ? AND Activo = 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_producto]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? ($result['Cantidad'] >= $cantidad_solicitada) : false;
        } catch (PDOException $e) {
            error_log("Error verificando stock: " . $e->getMessage());
            return false;
        }
    }
    
    // =======================================================
    // 📉 DESCONTAR STOCK
    // =======================================================
    public function descontarStock($id_producto, $cantidad) {
        try {
            $sql = "UPDATE producto SET Cantidad = Cantidad - ? WHERE ID_Producto = ? AND Cantidad >= ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$cantidad, $id_producto, $cantidad]);
        } catch (PDOException $e) {
            error_log("Error descontando stock: " . $e->getMessage());
            return false;
        }
    }
    
   public function obtenerFacturaDetalle($id_factura) {
    try {
        // ✅ CORREGIR LA CONSULTA PARA OBTENER DIRECCIÓN CORRECTAMENTE
        $sql = "SELECT 
                    f.*,
                    u.Nombre,
                    u.Apellido,
                    u.Correo,
                    u.Celular,
                    u.N_Documento,
                    u.ID_TD,
                    COALESCE(d.Direccion, 'No especificada') as Direccion,
                    COALESCE(d.Ciudad, 'No especificada') as Ciudad,
                    COALESCE(d.Departamento, 'No especificada') as Departamento,
                    COALESCE(d.CodigoPostal, '') as CodigoPostal,
                    mp.T_Pago
                FROM factura f
                JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                LEFT JOIN direccion d ON f.ID_Direccion = d.ID_Direccion
                LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                WHERE f.ID_Factura = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_factura]);
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$factura) {
            return [];
        }
        
        // ✅ SI LOS CAMPOS TIENEN "NO", REEMPLAZAR CON VALORES POR DEFECTO
        if (isset($factura['Direccion']) && $factura['Direccion'] === 'NO') {
            $factura['Direccion'] = 'No especificada';
        }
        if (isset($factura['Ciudad']) && $factura['Ciudad'] === 'NO') {
            $factura['Ciudad'] = 'No especificada';
        }
        if (isset($factura['Departamento']) && $factura['Departamento'] === 'NO') {
            $factura['Departamento'] = 'No especificada';
        }
        
        // ✅ OBTENER TIPO DE DOCUMENTO
        $factura['Tipo_Documento'] = 'No especificado';
        
        if (!empty($factura['ID_TD']) && $factura['ID_TD'] > 0) {
            try {
                $sql_tipo = "SELECT Documento FROM tipo_documento WHERE ID_TD = ?";
                $stmt_tipo = $this->conn->prepare($sql_tipo);
                $stmt_tipo->execute([$factura['ID_TD']]);
                $tipo_doc = $stmt_tipo->fetch(PDO::FETCH_ASSOC);
                
                if ($tipo_doc && !empty($tipo_doc['Documento'])) {
                    $factura['Tipo_Documento'] = $tipo_doc['Documento'];
                }
            } catch (PDOException $e) {
                error_log("Error obteniendo tipo documento: " . $e->getMessage());
            }
        }
        
        return $factura;
        
    } catch (PDOException $e) {
        error_log("Error obteniendo detalle de factura: " . $e->getMessage());
        return [];
    }
}
    
    
    // =======================================================
    // 🛍️ OBTENER ITEMS DE FACTURA
    // =======================================================
    public function obtenerFacturaItems($id_factura) {
        try {
            $sql = "SELECT 
                        fp.*,
                        p.ID_Producto,
                        p.ID_Articulo,
                        p.Nombre_Producto,
                        p.ID_Atributo1,
                        p.ValorAtributo1,
                        p.ID_Atributo2,
                        p.ValorAtributo2,
                        p.ID_Atributo3,
                        p.ValorAtributo3,
                        a.N_Articulo,
                        COALESCE(p.Foto, a.Foto) as Foto
                    FROM factura_producto fp
                    JOIN producto p ON fp.ID_Producto = p.ID_Producto
                    JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                    WHERE fp.ID_Factura = ?
                    ORDER BY fp.ID_FacturaProducto ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_factura]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo items de factura: " . $e->getMessage());
            return [];
        }
    }
    
    // =======================================================
    // 🔍 OBTENER HISTORIAL DE COMPRAS DEL USUARIO
    // =======================================================
    public function obtenerHistorialCompras($id_usuario) {
        try {
            $sql = "SELECT 
                        f.ID_Factura,
                        f.Fecha_Factura,
                        f.Subtotal,
                        f.IVA,
                        f.Monto_Total,
                        f.Estado,
                        mp.T_Pago,
                        COUNT(fp.ID_FacturaProducto) as Total_Items
                    FROM factura f
                    LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                    LEFT JOIN factura_producto fp ON f.ID_Factura = fp.ID_Factura
                    WHERE f.ID_Usuario = ?
                    GROUP BY f.ID_Factura, f.Fecha_Factura, f.Subtotal, f.IVA, f.Monto_Total, f.Estado, mp.T_Pago
                    ORDER BY f.Fecha_Factura DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo historial de compras: " . $e->getMessage());
            return [];
        }
    }
    
    // =======================================================
    // 📊 OBTENER ESTADÍSTICAS DE COMPRAS
    // =======================================================
    public function obtenerEstadisticasCompras($id_usuario) {
        try {
            $sql = "SELECT 
                        COUNT(*) as Total_Compras,
                        SUM(Monto_Total) as Total_Gastado,
                        AVG(Monto_Total) as Promedio_Compra,
                        MIN(Fecha_Factura) as Primera_Compra,
                        MAX(Fecha_Factura) as Ultima_Compra
                    FROM factura
                    WHERE ID_Usuario = ? AND Estado = 'Confirmado'";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_usuario]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo estadísticas de compras: " . $e->getMessage());
            return [];
        }
    }
    
    // =======================================================
    // 🔄 ACTUALIZAR ESTADO DE FACTURA
    // =======================================================
    public function actualizarEstadoFactura($id_factura, $estado) {
        try {
            $sql = "UPDATE factura SET Estado = ? WHERE ID_Factura = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$estado, $id_factura]);
        } catch (PDOException $e) {
            error_log("Error actualizando estado de factura: " . $e->getMessage());
            return false;
        }
    }
    
    // =======================================================
    // 📧 OBTENER DATOS PARA ENVÍO DE CORREO
    // =======================================================
    public function obtenerDatosFacturaParaCorreo($id_factura) {
    try {
        $sql = "SELECT 
                    f.ID_Factura,
                    f.Fecha_Factura,
                    f.Subtotal,
                    f.IVA,
                    f.Monto_Total,
                    f.Estado,
                    u.Nombre,
                    u.Apellido,
                    u.Correo,
                    u.Celular,
                    d.Direccion,
                    d.Ciudad,
                    d.Departamento,
                    d.CodigoPostal,
                    mp.T_Pago
                FROM factura f
                JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                LEFT JOIN direccion d ON f.ID_Direccion = d.ID_Direccion
                LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                WHERE f.ID_Factura = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_factura]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error obteniendo datos para correo: " . $e->getMessage());
        return [];
    }
}
    
    // =======================================================
    // 🧮 CALCULAR IVA PARA UN MONTO
    // =======================================================
    public static function calcularIVA($subtotal, $porcentaje_iva = 19) {
        $iva = $subtotal * ($porcentaje_iva / 100);
        $total_con_iva = $subtotal + $iva;
        
        return [
            'subtotal' => $subtotal,
            'iva_porcentaje' => $porcentaje_iva,
            'iva_monto' => $iva,
            'total_con_iva' => $total_con_iva
        ];
    }
    
    // =======================================================
    // 📋 VALIDAR CÓDIGO DE ACCESO
    // =======================================================
    public function validarCodigoAcceso($id_factura, $codigo_acceso) {
        try {
            $sql = "SELECT ID_Factura FROM factura WHERE ID_Factura = ? AND Codigo_Acceso = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_factura, $codigo_acceso]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            error_log("Error validando código de acceso: " . $e->getMessage());
            return false;
        }
    }
}
?>