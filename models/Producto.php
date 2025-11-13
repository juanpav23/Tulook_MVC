<?php
class Producto {
    private $conn;
    private $table = "producto";

    public function __construct($db) {
        $this->conn = $db;
    }

    // =======================================================
    // 🛍️ LISTAR PRODUCTOS BASE (Catálogo general)
    // =======================================================
    public function read() {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    COALESCE(MIN(aci.Foto), a.Foto) AS Foto,
                    c.N_Categoria,
                    g.N_Genero,
                    pr.Valor AS Precio,
                    a.Cantidad AS StockBase,
                    COALESCE(SUM(p.Cantidad), 0) AS StockVariantes,
                    (a.Cantidad + COALESCE(SUM(p.Cantidad), 0)) AS Stock
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN articulo_color_imagen aci ON aci.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                WHERE a.Activo = 1
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor, a.Cantidad
                ORDER BY a.N_Articulo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // =======================================================
    // 🔍 OBTENER DETALLE POR ID PRODUCTO
    // =======================================================
    public function obtenerPorId($idProducto) {
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    a.N_Articulo,
                    p.Nombre_Producto,
                    COALESCE(p.Foto, a.Foto) AS Foto,
                    a.ID_Categoria,
                    a.ID_SubCategoria,
                    a.ID_Genero,
                    pr.Valor AS Precio,
                    c.N_Categoria,
                    g.N_Genero,
                    sc.SubCategoria
                FROM producto p
                INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                LEFT JOIN subcategoria sc ON sc.ID_SubCategoria = a.ID_SubCategoria
                WHERE p.ID_Producto = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idProducto]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 🔎 OBTENER UN PRODUCTO COMPLETO (con color/talla)
    // =======================================================
    public function readOne($idProducto) {
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    p.Nombre_Producto,
                    a.N_Articulo AS Nombre_Articulo,
                    p.ID_Color,
                    col.N_Color AS Nombre_Color,
                    col.CodigoHex,
                    p.ID_Talla,
                    t.N_Talla AS Nombre_Talla,
                    COALESCE(p.Foto, a.Foto) AS Foto,
                    pr.Valor AS Precio_Base,
                    (pr.Valor + (pr.Valor * (p.Porcentaje / 100))) AS Precio,
                    p.Cantidad,
                    p.Porcentaje
                FROM producto p
                INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN color col ON col.ID_Color = p.ID_Color
                LEFT JOIN talla t ON t.ID_Talla = p.ID_Talla
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                WHERE p.ID_Producto = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idProducto]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $row['Nombre_Completo'] = trim(
                ($row['Nombre_Producto'] ?: $row['Nombre_Articulo']) . ' ' .
                ($row['Nombre_Color'] ?? '') . ' ' .
                ($row['Nombre_Talla'] ?? '')
            );
        }

        return $row ?: null;
    }

    // =======================================================
    // 🔹 OBTENER INFORMACIÓN BASE DE UN ARTÍCULO
    // =======================================================
    public function readBase($idArticulo) {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    a.Foto,
                    a.ID_Categoria,
                    a.ID_SubCategoria,
                    a.ID_Color,
                    a.ID_Talla,
                    a.ID_Genero,
                    pr.Valor AS Precio,
                    a.Cantidad,
                    c.N_Categoria,
                    g.N_Genero
                FROM articulo a
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                WHERE a.ID_Articulo = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$articulo) {
            return null;
        }

        return $articulo;
    }

    // =======================================================
    // 🎨 VARIANTES (COLORES Y TALLAS) DE UN ARTÍCULO
    // =======================================================
    public function getVariantesByArticulo($idArticulo) {
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    p.Nombre_Producto,
                    p.ID_Color,
                    col.N_Color,
                    col.CodigoHex,
                    p.ID_Talla,
                    t.N_Talla,
                    COALESCE(aci.Foto, p.Foto, a.Foto) AS Foto,
                    p.Porcentaje,
                    p.Cantidad,
                    pr.Valor AS Precio_Base,
                    (pr.Valor + (pr.Valor * (p.Porcentaje / 100))) AS Precio_Final
                FROM producto p
                INNER JOIN articulo a ON a.ID_Articulo = p.ID_Articulo
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN (
                    SELECT ID_Articulo, ID_Color, MIN(Foto) AS Foto
                    FROM articulo_color_imagen
                    GROUP BY ID_Articulo, ID_Color
                ) aci ON aci.ID_Articulo = p.ID_Articulo AND aci.ID_Color = p.ID_Color
                LEFT JOIN color col ON col.ID_Color = p.ID_Color
                LEFT JOIN talla t ON t.ID_Talla = p.ID_Talla
                WHERE p.ID_Articulo = ?
                GROUP BY p.ID_Producto
                ORDER BY col.N_Color ASC, t.N_Talla ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 👕 OBTENER TALLAS DISPONIBLES (ARTÍCULO BASE + VARIANTES)
    // =======================================================
    public function getTallasDisponiblesByArticulo($idArticulo) {
        $tallas = [];
        
        // 🔸 Tallas del artículo base
        $sqlBase = "SELECT 
                        a.ID_Talla,
                        t.N_Talla,
                        a.Cantidad,
                        'base' AS Tipo,
                        a.ID_Articulo AS ID_Producto
                    FROM articulo a
                    LEFT JOIN talla t ON t.ID_Talla = a.ID_Talla
                    WHERE a.ID_Articulo = ? AND a.ID_Talla IS NOT NULL";
        
        $stmtBase = $this->conn->prepare($sqlBase);
        $stmtBase->execute([$idArticulo]);
        $tallasBase = $stmtBase->fetchAll(PDO::FETCH_ASSOC);
        
        // 🔸 Tallas de las variantes (productos)
        $sqlVariantes = "SELECT 
                            p.ID_Talla,
                            t.N_Talla,
                            p.Cantidad,
                            'variante' AS Tipo,
                            p.ID_Producto
                        FROM producto p
                        INNER JOIN talla t ON t.ID_Talla = p.ID_Talla
                        WHERE p.ID_Articulo = ?
                        ORDER BY t.N_Talla ASC";
        
        $stmtVariantes = $this->conn->prepare($sqlVariantes);
        $stmtVariantes->execute([$idArticulo]);
        $tallasVariantes = $stmtVariantes->fetchAll(PDO::FETCH_ASSOC);
        
        // Combinar ambas fuentes
        $tallas = array_merge($tallasBase, $tallasVariantes);
        
        return $tallas;
    }

    // =======================================================
    // 🔍 OBTENER STOCK TOTAL DEL ARTÍCULO
    // =======================================================
    public function getStockTotal($idArticulo) {
        $sql = "SELECT 
                    COALESCE(a.Cantidad, 0) + 
                    COALESCE(SUM(p.Cantidad), 0) AS Stock_Total
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                WHERE a.ID_Articulo = ?
                GROUP BY a.ID_Articulo, a.Cantidad";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['Stock_Total'] : 0;
    }

    // =======================================================
    // ✅ DESCONTAR STOCK AL COMPRAR (ARTÍCULO BASE Y VARIANTES)
    // =======================================================
    public function actualizarStock($idProducto, $cantidadComprada, $tipo = 'variante') {
        if ($tipo === 'base') {
            // Descontar stock del artículo base
            $sql = "UPDATE articulo 
                    SET Cantidad = GREATEST(Cantidad - ?, 0)
                    WHERE ID_Articulo = ? AND Cantidad >= ?";
        } else {
            // Descontar stock de variante (producto)
            $sql = "UPDATE producto 
                    SET Cantidad = GREATEST(Cantidad - ?, 0)
                    WHERE ID_Producto = ? AND Cantidad >= ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$cantidadComprada, $idProducto, $cantidadComprada]);
    }

    // =======================================================
    // 🔄 VERIFICAR STOCK DISPONIBLE
    // =======================================================
    public function verificarStock($idProducto, $cantidad, $tipo = 'variante') {
        if ($tipo === 'base') {
            $sql = "SELECT Cantidad FROM articulo WHERE ID_Articulo = ?";
        } else {
            $sql = "SELECT Cantidad FROM producto WHERE ID_Producto = ?";
        }
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idProducto]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? ($result['Cantidad'] >= $cantidad) : false;
    }

    // =======================================================
    // 🎯 OBTENER PRODUCTOS DESTACADOS
    // =======================================================
    public function getDestacados($limit = 8) {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    COALESCE(MIN(aci.Foto), a.Foto) AS Foto,
                    c.N_Categoria,
                    pr.Valor AS Precio,
                    (COALESCE(a.Cantidad, 0) + COALESCE(SUM(p.Cantidad), 0)) AS Stock
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN articulo_color_imagen aci ON aci.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                WHERE a.Activo = 1 AND a.Destacado = 1
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, pr.Valor, a.Cantidad
                ORDER BY a.Fecha_Creacion DESC
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 🔎 BUSCAR PRODUCTOS
    // =======================================================
    public function buscar($termino, $idCategoria = null, $idGenero = null) {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    COALESCE(MIN(aci.Foto), a.Foto) AS Foto,
                    c.N_Categoria,
                    g.N_Genero,
                    pr.Valor AS Precio,
                    (COALESCE(a.Cantidad, 0) + COALESCE(SUM(p.Cantidad), 0)) AS Stock
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN articulo_color_imagen aci ON aci.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                WHERE a.Activo = 1 
                AND (a.N_Articulo LIKE ? OR c.N_Categoria LIKE ? OR g.N_Genero LIKE ?)";
        
        $params = ["%{$termino}%", "%{$termino}%", "%{$termino}%"];
        
        if ($idCategoria) {
            $sql .= " AND a.ID_Categoria = ?";
            $params[] = $idCategoria;
        }
        
        if ($idGenero) {
            $sql .= " AND a.ID_Genero = ?";
            $params[] = $idGenero;
        }
        
        $sql .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor, a.Cantidad
                  ORDER BY a.N_Articulo ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 🎨 OBTENER INFORMACIÓN DE COLOR POR ID
    // =======================================================
    public function getColorInfo($idColor) {
        $sql = "SELECT N_Color, CodigoHex FROM color WHERE ID_Color = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idColor]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 📏 OBTENER INFORMACIÓN DE TALLA POR ID
    // =======================================================
    public function getTallaInfo($idTalla) {
        $sql = "SELECT N_Talla FROM talla WHERE ID_Talla = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idTalla]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>














