<?php
// models/FavoritoStats.php
class FavoritoStats {
    private $conn;
    private $table = "favorito";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER PRODUCTOS MÁS FAVORITOS CON FILTRO DE ESTADO
    public function obtenerMasFavoritos($limit = 10, $soloActivos = false) {
        $query = "SELECT 
                    a.ID_Articulo as ID,
                    a.N_Articulo as Nombre,
                    a.Foto as Foto,
                    COUNT(f.ID_Favorito) as total_favoritos,
                    COALESCE(pr.Valor, 0) as Precio_Base,
                    0 as Porcentaje,
                    COALESCE(pr.Valor, 0) as Precio_Final,
                    g.N_Genero,
                    c.N_Categoria,
                    a.Activo as Activo,
                    MIN(f.Fecha) as fecha_primer_favorito
                FROM favorito f
                INNER JOIN articulo a ON f.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON a.ID_Precio = pr.ID_Precio
                LEFT JOIN genero g ON a.ID_Genero = g.ID_Genero
                LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
                WHERE 1=1";
        
        // Aplicar filtro de estado
        if ($soloActivos) {
            $query .= " AND a.Activo = 1";
        }

        $query .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, pr.Valor, 
                        g.N_Genero, c.N_Categoria, a.Activo
                HAVING total_favoritos > 0
                ORDER BY total_favoritos DESC, fecha_primer_favorito ASC
                LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PRODUCTOS MENOS FAVORITOS CON FILTRO DE ESTADO
    public function obtenerMenosFavoritos($limit = 10, $soloActivos = false) {
        $query = "SELECT 
                    a.ID_Articulo as ID,
                    a.N_Articulo as Nombre,
                    a.Foto as Foto,
                    COUNT(f.ID_Favorito) as total_favoritos,
                    COALESCE(pr.Valor, 0) as Precio_Base,
                    0 as Porcentaje,
                    COALESCE(pr.Valor, 0) as Precio_Final,
                    g.N_Genero,
                    c.N_Categoria,
                    a.Activo as Activo,
                    COALESCE(MAX(f.Fecha), '2000-01-01') as fecha_ultimo_favorito
                FROM articulo a
                LEFT JOIN favorito f ON a.ID_Articulo = f.ID_Articulo
                LEFT JOIN precio pr ON a.ID_Precio = pr.ID_Precio
                LEFT JOIN genero g ON a.ID_Genero = g.ID_Genero
                LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
                WHERE 1=1";
        
        if ($soloActivos) {
            $query .= " AND a.Activo = 1";
        }

        $query .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, pr.Valor, 
                        g.N_Genero, c.N_Categoria, a.Activo
                ORDER BY total_favoritos ASC, fecha_ultimo_favorito DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER EL PRODUCTO MÁS FAVORITO CON FILTRO DE ESTADO
    public function obtenerProductoMasFavorito($soloActivos = false) {
        $resultados = $this->obtenerMasFavoritos(1, $soloActivos);
        return !empty($resultados) ? $resultados[0] : null;
    }

    // OBTENER EL PRODUCTO MENOS FAVORITO CON FILTRO DE ESTADO
    public function obtenerProductoMenosFavorito($soloActivos = false) {
        $resultados = $this->obtenerMenosFavoritos(1, $soloActivos);
        return !empty($resultados) ? $resultados[0] : null;
    }

    // ESTADÍSTICAS GENERALES CON FILTRO DE ESTADO
    public function obtenerEstadisticasGenerales($soloActivos = false) {
        $query = "SELECT 
                    COUNT(DISTINCT f.ID_Usuario) as total_usuarios_favoritos,
                    COUNT(DISTINCT f.ID_Favorito) as total_favoritos,
                    COUNT(DISTINCT f.ID_Articulo) as total_productos_con_favoritos,
                    (SELECT COUNT(*) FROM articulo " . ($soloActivos ? " WHERE Activo = 1" : "") . ") as total_productos,
                    CASE 
                        WHEN COUNT(DISTINCT f.ID_Articulo) > 0 
                        THEN COUNT(DISTINCT f.ID_Favorito) / COUNT(DISTINCT f.ID_Articulo)
                        ELSE 0 
                    END as promedio_favoritos_por_producto,
                    COUNT(DISTINCT CASE WHEN a.Activo = 0 THEN f.ID_Articulo END) as productos_inactivos_con_favoritos
                FROM favorito f
                LEFT JOIN articulo a ON f.ID_Articulo = a.ID_Articulo
                WHERE 1=1";
        
        if ($soloActivos) {
            $query .= " AND a.Activo = 1";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // DISTRIBUCIÓN POR CATEGORÍA CON FILTRO DE ESTADO
    public function obtenerDistribucionPorCategoria($soloActivos = false) {
        $query = "SELECT 
                    c.N_Categoria as categoria,
                    COUNT(f.ID_Favorito) as total_favoritos,
                    COUNT(DISTINCT a.ID_Articulo) as total_productos,
                    COUNT(DISTINCT CASE WHEN a.Activo = 0 THEN a.ID_Articulo END) as productos_inactivos
                FROM articulo a
                LEFT JOIN favorito f ON a.ID_Articulo = f.ID_Articulo
                LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
                WHERE c.N_Categoria IS NOT NULL";
        
        if ($soloActivos) {
            $query .= " AND a.Activo = 1";
        }

        $query .= " GROUP BY c.ID_Categoria, c.N_Categoria
                ORDER BY total_favoritos DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // DISTRIBUCIÓN POR GÉNERO CON FILTRO DE ESTADO
    public function obtenerDistribucionPorGenero($soloActivos = false) {
        $query = "SELECT 
                    g.N_Genero as genero,
                    COUNT(f.ID_Favorito) as total_favoritos,
                    COUNT(DISTINCT a.ID_Articulo) as total_productos,
                    COUNT(DISTINCT CASE WHEN a.Activo = 0 THEN a.ID_Articulo END) as productos_inactivos
                FROM articulo a
                LEFT JOIN favorito f ON a.ID_Articulo = f.ID_Articulo
                LEFT JOIN genero g ON a.ID_Genero = g.ID_Genero
                WHERE g.N_Genero IS NOT NULL";
        
        if ($soloActivos) {
            $query .= " AND a.Activo = 1";
        }

        $query .= " GROUP BY g.ID_Genero, g.N_Genero
                ORDER BY total_favoritos DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // BUSCAR PRODUCTOS CON FILTROS COMPLETOS
    public function buscar($termino = '', $filtroCategoria = '', $filtroGenero = '', $filtroSubcategoria = '', $soloActivos = false, $filtroEstado = '') {
        $query = "SELECT 
                    a.ID_Articulo as ID,
                    a.N_Articulo as Nombre,
                    a.Foto as Foto,
                    COUNT(f.ID_Favorito) as total_favoritos,
                    COALESCE(pr.Valor, 0) as Precio_Base,
                    0 as Porcentaje,
                    COALESCE(pr.Valor, 0) as Precio_Final,
                    g.N_Genero,
                    c.N_Categoria,
                    s.SubCategoria,
                    a.Activo as Activo,
                    COALESCE(MIN(f.Fecha), '2000-01-01') as fecha_primer_favorito
                FROM articulo a
                LEFT JOIN favorito f ON a.ID_Articulo = f.ID_Articulo
                LEFT JOIN precio pr ON a.ID_Precio = pr.ID_Precio
                LEFT JOIN genero g ON a.ID_Genero = g.ID_Genero
                LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
                LEFT JOIN subcategoria s ON a.ID_SubCategoria = s.ID_SubCategoria
                WHERE 1=1";

        $params = [];

        // Búsqueda por término
        if (!empty($termino)) {
            $query .= " AND (a.N_Articulo LIKE ? OR c.N_Categoria LIKE ? OR s.SubCategoria LIKE ?)";
            $params[] = '%' . $termino . '%';
            $params[] = '%' . $termino . '%';
            $params[] = '%' . $termino . '%';
        }

        // Filtro por categoría
        if (!empty($filtroCategoria)) {
            $query .= " AND c.N_Categoria = ?";
            $params[] = $filtroCategoria;
        }

        // Filtro por género
        if (!empty($filtroGenero)) {
            $query .= " AND g.N_Genero = ?";
            $params[] = $filtroGenero;
        }

        // Filtro por subcategoría
        if (!empty($filtroSubcategoria)) {
            $query .= " AND s.SubCategoria = ?";
            $params[] = $filtroSubcategoria;
        }

        // Filtro por estado (activo/inactivo)
        if (!empty($filtroEstado)) {
            if ($filtroEstado === 'activo') {
                $query .= " AND a.Activo = 1";
            } elseif ($filtroEstado === 'inactivo') {
                $query .= " AND a.Activo = 0";
            }
        } else {
            // Si no hay filtro específico, aplicar el filtro general
            if ($soloActivos) {
                $query .= " AND a.Activo = 1";
            }
        }

        $query .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, pr.Valor, 
                        g.N_Genero, c.N_Categoria, s.SubCategoria, a.Activo
                ORDER BY total_favoritos DESC, fecha_primer_favorito ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER CATEGORÍAS PARA FILTROS
    public function obtenerCategorias($soloActivos = false) {
        try {
            $query = "SELECT DISTINCT c.N_Categoria 
                     FROM categoria c 
                     JOIN articulo a ON c.ID_Categoria = a.ID_Categoria 
                     WHERE c.N_Categoria IS NOT NULL";
            
            if ($soloActivos) {
                $query .= " AND a.Activo = 1";
            }
            
            $query .= " ORDER BY c.N_Categoria";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER SUBCATEGORÍAS PARA FILTROS
    public function obtenerSubcategorias($soloActivos = false) {
        try {
            $query = "SELECT DISTINCT s.SubCategoria 
                    FROM subcategoria s 
                    JOIN articulo a ON s.ID_SubCategoria = a.ID_SubCategoria 
                    WHERE s.SubCategoria IS NOT NULL";
            
            if ($soloActivos) {
                $query .= " AND a.Activo = 1";
            }
            
            $query .= " ORDER BY s.SubCategoria";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error obteniendo subcategorías: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER DISTRIBUCIÓN POR SUBCATEGORÍA
    public function obtenerDistribucionPorSubcategoria($soloActivos = false) {
        $query = "SELECT 
                    s.SubCategoria as subcategoria,
                    COUNT(f.ID_Favorito) as total_favoritos,
                    COUNT(DISTINCT a.ID_Articulo) as total_productos,
                    COUNT(DISTINCT CASE WHEN a.Activo = 0 THEN a.ID_Articulo END) as productos_inactivos
                FROM articulo a
                LEFT JOIN favorito f ON a.ID_Articulo = f.ID_Articulo
                LEFT JOIN subcategoria s ON a.ID_SubCategoria = s.ID_SubCategoria
                WHERE s.SubCategoria IS NOT NULL";
        
        if ($soloActivos) {
            $query .= " AND a.Activo = 1";
        }

        $query .= " GROUP BY s.ID_SubCategoria, s.SubCategoria
                ORDER BY total_favoritos DESC
                LIMIT 10";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER GÉNEROS PARA FILTROS
    public function obtenerGeneros($soloActivos = false) {
        try {
            $query = "SELECT DISTINCT g.N_Genero 
                     FROM genero g 
                     JOIN articulo a ON g.ID_Genero = a.ID_Genero 
                     WHERE g.N_Genero IS NOT NULL";
            
            if ($soloActivos) {
                $query .= " AND a.Activo = 1";
            }
            
            $query .= " ORDER BY g.N_Genero";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException $e) {
            error_log("Error obteniendo géneros: " . $e->getMessage());
            return [];
        }
    }
}
?>