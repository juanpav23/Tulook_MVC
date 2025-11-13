<?php
// ==========================================
// MODELO PRODUCTO - TuLook MVC (versión corregida y unificada)
// ==========================================
class Producto {
    private $conn;
    private $table = "producto";

    public function __construct($db) {
        $this->conn = $db;
    }

    // =======================================================
    // 🛍️ LISTAR PRODUCTOS BASE (para catálogo principal)
    // =======================================================
    public function read() {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    COALESCE(MIN(aci.Foto), a.Foto) AS Foto,
                    c.N_Categoria,
                    g.N_Genero,
                    pr.Valor AS Precio,
                    COALESCE(SUM(p.Cantidad), a.Cantidad, 0) AS Stock
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
    // 🔍 OBTENER DETALLE POR ID_Producto (vista o edición)
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
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    // =======================================================
    // 🔎 OBTENER UN PRODUCTO COMPLETO (para carrito o vista)
    // =======================================================
    public function readOne($idProducto) {
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    p.Nombre_Producto,
                    a.N_Articulo AS Nombre_Articulo,
                    p.ID_Color,
                    col.N_Color AS Nombre_Color,
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
    // 🔹 OBTENER PRODUCTO BASE POR ID_ARTICULO (sin variantes)
    // =======================================================
    public function readBase($idArticulo) {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    a.Foto,
                    a.ID_Categoria,
                    a.ID_SubCategoria,
                    a.ID_Genero,
                    a.ID_Talla,
                    t.N_Talla,
                    a.ID_Color,
                    col.N_Color,
                    col.CodigoHex,
                    pr.Valor AS Precio,
                    a.Cantidad,
                    c.N_Categoria,
                    g.N_Genero
                FROM articulo a
                LEFT JOIN talla t ON t.ID_Talla = a.ID_Talla
                LEFT JOIN color col ON col.ID_Color = a.ID_Color
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                WHERE a.ID_Articulo = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 🎨 OBTENER VARIANTES POR ARTÍCULO (color + talla + precio)
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
                GROUP BY p.ID_Producto, p.ID_Color, p.ID_Talla, p.Nombre_Producto, p.Porcentaje
                ORDER BY col.N_Color ASC, 
                         FIELD(t.N_Talla, 'XS','S','M','L','XL','XXL','Única') ASC,
                         p.Porcentaje ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $variantes = [];
        foreach ($rows as $r) {
            $variantes[] = [
                'ID_Producto'     => $r['ID_Producto'],
                'ID_Articulo'     => $r['ID_Articulo'],
                'Nombre_Producto' => $r['Nombre_Producto'] ?: null,
                'ID_Color'        => $r['ID_Color'],
                'N_Color'         => $r['N_Color'] ?: 'Sin color',
                'CodigoHex'       => $r['CodigoHex'] ?: '#cccccc',
                'ID_Talla'        => $r['ID_Talla'],
                'N_Talla'         => $r['N_Talla'] ?: 'Única',
                'Foto'            => $r['Foto'],
                'Porcentaje'      => (float)$r['Porcentaje'],
                'Cantidad'        => (int)$r['Cantidad'],
                'Precio_Base'     => (float)$r['Precio_Base'],
                'Precio_Final'    => (float)$r['Precio_Final']
            ];
        }
        return $variantes;
    }

    // =======================================================
    // 🔎 OBTENER VARIANTE ESPECÍFICA (por color + talla)
    // =======================================================
    public function getVarianteEspecifica($idArticulo, $idColor, $idTalla) {
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    p.Nombre_Producto,
                    p.ID_Color,
                    p.ID_Talla,
                    COALESCE(aci.Foto, p.Foto, a.Foto) AS Foto,
                    p.Porcentaje,
                    p.Cantidad,
                    pr.Valor AS Precio_Base,
                    (pr.Valor + (pr.Valor * (p.Porcentaje / 100))) AS Precio_Final
                FROM producto p
                INNER JOIN articulo a ON a.ID_Articulo = p.ID_Articulo
                LEFT JOIN (
                    SELECT ID_Articulo, ID_Color, MIN(Foto) AS Foto
                    FROM articulo_color_imagen
                    GROUP BY ID_Articulo, ID_Color
                ) aci ON aci.ID_Articulo = p.ID_Articulo AND aci.ID_Color = p.ID_Color
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                WHERE p.ID_Articulo = ? AND p.ID_Color = ? AND p.ID_Talla = ?
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo, $idColor, $idTalla]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 📦 ACTUALIZAR STOCK AL COMPRAR
    // =======================================================
    public function actualizarStock($idProducto, $cantidadComprada) {
        $sql = "UPDATE producto 
                SET Cantidad = GREATEST(Cantidad - ?, 0)
                WHERE ID_Producto = ? AND Cantidad >= ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$cantidadComprada, $idProducto, $cantidadComprada]);
    }
}
?>












