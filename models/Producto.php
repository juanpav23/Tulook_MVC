<?php
class Producto {
    private $conn;
    private $table = "producto";

    public function __construct($db) {
        $this->conn = $db;
    }

    // =======================================================
    // 🛍️ LISTAR PRODUCTOS BASE (SOLO CON VARIANTES)
    // =======================================================
    public function read() {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    a.Foto,
                    c.N_Categoria,
                    g.N_Genero,
                    pr.Valor AS Precio,
                    a.ID_Categoria,
                    COALESCE(SUM(p.Cantidad), 0) AS Stock,
                    COUNT(p.ID_Producto) AS Total_Variantes
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo AND p.Activo = 1
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                WHERE a.Activo = 1
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor, a.ID_Categoria
                HAVING COUNT(p.ID_Producto) > 0
                ORDER BY a.N_Articulo ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt;
    }

    // =======================================================
    // 🔍 OBTENER INFORMACIÓN DE SUBCATEGORÍA
    // =======================================================
    public function getSubcategoriaInfo($idSubcategoria) {
        try {
            $sql = "SELECT s.* FROM subcategoria s WHERE s.ID_SubCategoria = ?";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idSubcategoria]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result && !empty($result['AtributosRequeridos'])) {
                $atributosIds = array_filter(
                    array_map('trim', explode(',', $result['AtributosRequeridos']))
                );
                $result['AtributosRequeridosArray'] = $atributosIds;
            } else {
                $result['AtributosRequeridosArray'] = [];
            }
            
            return $result;
            
        } catch (PDOException $e) {
            return ['AtributosRequeridosArray' => []];
        }
    }

    public function getNombreTipoAtributo($idTipoAtributo) {
        try {
            $sql = "SELECT Nombre FROM tipo_atributo WHERE ID_TipoAtributo = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idTipoAtributo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['Nombre'] : 'Atributo ' . $idTipoAtributo;
            
        } catch (PDOException $e) {
            return 'Atributo';
        }
    }

    // =======================================================
    // 🎯 OBTENER ATRIBUTOS POR TIPO
    // =======================================================
    public function getAtributosByTipo($idTipoAtributo) {
        try {
            if ($idTipoAtributo == 2) {
                $sql = "SELECT 
                            c.ID_Color as ID_AtributoValor, 
                            c.N_Color as Valor,
                            c.CodigoHex,
                            'Color' as Nombre
                        FROM color c 
                        ORDER BY c.N_Color ASC";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
            } else {
                $sql = "SELECT 
                            av.ID_AtributoValor, 
                            av.Valor,
                            NULL as CodigoHex,
                            ta.Nombre
                        FROM atributo_valor av 
                        INNER JOIN tipo_atributo ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                        WHERE av.ID_TipoAtributo = ? AND av.Activo = 1 
                        ORDER BY av.Orden ASC, av.Valor ASC";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idTipoAtributo]);
            }
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultados;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    // =======================================================
    // 🔍 OBTENER DETALLE POR ID PRODUCTO - ACTUALIZADO
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
                    sc.SubCategoria,
                    p.ID_Atributo1,
                    p.ID_Atributo2,
                    p.ID_Atributo3,
                    p.ValorAtributo1,
                    p.ValorAtributo2,
                    p.ValorAtributo3
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
    // 🔎 OBTENER UN PRODUCTO COMPLETO - ACTUALIZADO
    // =======================================================
    public function readOne($idProducto) {
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    p.Nombre_Producto,
                    a.N_Articulo AS Nombre_Articulo,
                    p.ID_Atributo1,
                    p.ValorAtributo1,
                    p.ID_Atributo2,
                    p.ValorAtributo2,
                    p.ID_Atributo3,
                    p.ValorAtributo3,
                    COALESCE(p.Foto, a.Foto) AS Foto,
                    pr.Valor AS Precio_Base,
                    (pr.Valor + (pr.Valor * (p.Porcentaje / 100))) AS Precio,
                    p.Cantidad,
                    p.Porcentaje,
                    a.ID_SubCategoria,
                    sc.AtributosRequeridos
                FROM producto p
                INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN subcategoria sc ON sc.ID_SubCategoria = a.ID_SubCategoria
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                WHERE p.ID_Producto = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idProducto]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nombrePartes = [$row['Nombre_Producto'] ?: $row['Nombre_Articulo']];
            
            if (!empty($row['ValorAtributo1'])) $nombrePartes[] = $row['ValorAtributo1'];
            if (!empty($row['ValorAtributo2'])) $nombrePartes[] = $row['ValorAtributo2'];
            if (!empty($row['ValorAtributo3'])) $nombrePartes[] = $row['ValorAtributo3'];
            
            $row['Nombre_Completo'] = trim(implode(' ', $nombrePartes));
        }

        return $row ?: null;
    }

    // =======================================================
    // 🎨 VARIANTES DE UN ARTÍCULO - ACTUALIZADO
    // =======================================================
    public function getVariantesByArticulo($idArticulo) {
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    p.Nombre_Producto,
                    p.ID_Atributo1,
                    p.ValorAtributo1,
                    p.ID_Atributo2,
                    p.ValorAtributo2,
                    p.ID_Atributo3,
                    p.ValorAtributo3,
                    COALESCE(p.Foto, a.Foto) AS Foto,
                    p.Porcentaje,
                    p.Cantidad,
                    p.Activo,
                    pr.Valor AS Precio_Base,
                    (pr.Valor + (pr.Valor * (p.Porcentaje / 100))) AS Precio_Final,
                    a.ID_SubCategoria,
                    sc.AtributosRequeridos
                FROM producto p
                INNER JOIN articulo a ON a.ID_Articulo = p.ID_Articulo
                LEFT JOIN subcategoria sc ON sc.ID_SubCategoria = a.ID_SubCategoria
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                WHERE p.ID_Articulo = ? AND p.Activo = 1
                ORDER BY p.ID_Producto ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        $variantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($variantes as &$variante) {
            $nombrePartes = [$variante['Nombre_Producto']];
            
            if (!empty($variante['ValorAtributo1'])) $nombrePartes[] = $variante['ValorAtributo1'];
            if (!empty($variante['ValorAtributo2'])) $nombrePartes[] = $variante['ValorAtributo2'];
            if (!empty($variante['ValorAtributo3'])) $nombrePartes[] = $variante['ValorAtributo3'];
            
            $variante['Nombre_Descriptivo'] = trim(implode(' ', $nombrePartes));
        }

        return $variantes;
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
                    a.ID_Genero,
                    pr.Valor AS Precio,
                    c.N_Categoria,
                    g.N_Genero,
                    sc.SubCategoria,
                    sc.AtributosRequeridos
                FROM articulo a
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                LEFT JOIN subcategoria sc ON sc.ID_SubCategoria = a.ID_SubCategoria
                WHERE a.ID_Articulo = ?
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // ✅ DESCONTAR STOCK AL COMPRAR
    // =======================================================
    public function actualizarStock($idProducto, $cantidadComprada) {
        $sql = "UPDATE producto 
                SET Cantidad = GREATEST(Cantidad - ?, 0)
                WHERE ID_Producto = ? AND Cantidad >= ?";
        
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$cantidadComprada, $idProducto, $cantidadComprada]);
    }

    // =======================================================
    // 🔄 VERIFICAR STOCK DISPONIBLE
    // =======================================================
    public function verificarStock($idProducto, $cantidad) {
        $sql = "SELECT Cantidad FROM producto WHERE ID_Producto = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idProducto]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? ($result['Cantidad'] >= $cantidad) : false;
    }

    // =======================================================
    // 🔍 OBTENER STOCK TOTAL DEL ARTÍCULO
    // =======================================================
    public function getStockTotal($idArticulo) {
        $sql = "SELECT 
                    COALESCE(SUM(p.Cantidad), 0) AS Stock_Total
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                WHERE a.ID_Articulo = ? AND p.Activo = 1
                GROUP BY a.ID_Articulo";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$idArticulo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['Stock_Total'] : 0;
    }

    // =======================================================
    // 🎯 OBTENER PRODUCTOS DESTACADOS
    // =======================================================
    public function getDestacados($limit = 8) {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    a.Foto,
                    c.N_Categoria,
                    pr.Valor AS Precio,
                    COALESCE(SUM(p.Cantidad), 0) AS Stock
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                WHERE a.Activo = 1
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, pr.Valor
                ORDER BY a.ID_Articulo DESC
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
                    a.Foto,
                    c.N_Categoria,
                    g.N_Genero,
                    pr.Valor AS Precio,
                    COALESCE(SUM(p.Cantidad), 0) AS Stock
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
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
        
        $sql .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor
                  ORDER BY a.N_Articulo ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 🎨 OBTENER INFORMACIÓN DE COLOR DESDE ATRIBUTOS
    // =======================================================
    public function getColorInfo($id_atributo_color) {
        try {
            // Buscar en atributo_valor
            $sql = "SELECT av.Valor as N_Color, NULL as CodigoHex 
                    FROM atributo_valor av 
                    WHERE av.ID_AtributoValor = ? 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_atributo_color]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            }
            
            // Si no encuentra, podría ser de la tabla color
            $sql2 = "SELECT N_Color, CodigoHex FROM color WHERE ID_Color = ? LIMIT 1";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([$id_atributo_color]);
            $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return $result2 ?: ['N_Color' => 'Color no encontrado', 'CodigoHex' => null];
            
        } catch (Exception $e) {
            error_log("Error obteniendo color: " . $e->getMessage());
            return ['N_Color' => 'Error', 'CodigoHex' => null];
        }
    }

    // =======================================================
    // 📏 OBTENER INFORMACIÓN DE TALLA DESDE ATRIBUTOS
    // =======================================================
    public function getTallaInfo($id_atributo_talla) {
        try {
            // Buscar en atributo_valor
            $sql = "SELECT av.Valor as N_Talla 
                    FROM atributo_valor av 
                    WHERE av.ID_AtributoValor = ? 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_atributo_talla]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                return $result;
            }
            
            // Si no encuentra, podría ser de la tabla talla
            $sql2 = "SELECT N_Talla FROM talla WHERE ID_Talla = ? LIMIT 1";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute([$id_atributo_talla]);
            $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return $result2 ?: ['N_Talla' => 'Talla no encontrada'];
            
        } catch (Exception $e) {
            error_log("Error obteniendo talla: " . $e->getMessage());
            return ['N_Talla' => 'Error'];
        }
    }

    // =======================================================
    // 🔍 OBTENER COLOR Y TALLA DE UN PRODUCTO
    // =======================================================
    public function getColorYTalla($id_producto) {
        try {
            $sql = "SELECT 
                        p.ID_Atributo1,
                        p.ValorAtributo1,
                        p.ID_Atributo2,
                        p.ValorAtributo2,
                        p.ID_Atributo3,
                        p.ValorAtributo3
                    FROM producto p 
                    WHERE p.ID_Producto = ? 
                    LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$id_producto]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $color = 'Sin color';
            $talla = 'Única';
            
            if ($producto) {
                // Buscar color (ID_TipoAtributo = 2)
                // Buscar talla (ID_TipoAtributo = 3)
                // Nota: Esto depende de cómo tengas configurados tus IDs de tipo atributo
                
                // Suponiendo que ID_TipoAtributo=2 es color y ID_TipoAtributo=3 es talla
                if ($producto['ID_Atributo1'] == 2) $color = $producto['ValorAtributo1'];
                elseif ($producto['ID_Atributo2'] == 2) $color = $producto['ValorAtributo2'];
                elseif ($producto['ID_Atributo3'] == 2) $color = $producto['ValorAtributo3'];
                
                if ($producto['ID_Atributo1'] == 3) $talla = $producto['ValorAtributo1'];
                elseif ($producto['ID_Atributo2'] == 3) $talla = $producto['ValorAtributo2'];
                elseif ($producto['ID_Atributo3'] == 3) $talla = $producto['ValorAtributo3'];
            }
            
            return [
                'color' => $color,
                'talla' => $talla
            ];
            
        } catch (Exception $e) {
            error_log("Error obteniendo color y talla: " . $e->getMessage());
            return ['color' => 'Error', 'talla' => 'Error'];
        }
    }

}
?>