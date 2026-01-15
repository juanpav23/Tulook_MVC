<?php
class Color {
    private $conn;
    private $table_name = "color";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER TODOS LOS COLORES CON INFO DE USO
    public function obtenerTodos() {
        $query = "SELECT c.*, 
                        (SELECT COUNT(*) FROM producto 
                        WHERE ValorAtributo1 = c.N_Color 
                            OR ValorAtributo2 = c.N_Color 
                            OR ValorAtributo3 = c.N_Color) as productos_asociados
                FROM {$this->table_name} c
                ORDER BY LOWER(c.N_Color) ASC"; // <-- ORDENAR POR NOMBRE IGNORANDO MAYÚSCULAS/MINÚSCULAS
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER COLOR POR ID
    public function obtenerPorId($id) {
        $query = "SELECT c.*, 
                         (SELECT COUNT(*) FROM producto 
                          WHERE ValorAtributo1 = c.N_Color 
                             OR ValorAtributo2 = c.N_Color 
                             OR ValorAtributo3 = c.N_Color) as productos_asociados
                  FROM {$this->table_name} c
                  WHERE c.ID_Color = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // VERIFICAR SI EXISTE UN COLOR CON EL MISMO NOMBRE O CÓDIGO
    public function existeColor($nombre, $codigoHex, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM {$this->table_name} 
                  WHERE (N_Color = ? OR CodigoHex = ?)";
        $params = [$nombre, $codigoHex];
        
        if ($excluirId) {
            $query .= " AND ID_Color != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // CREAR NUEVO COLOR
    public function crear($nombre, $codigoHex, $activo = 1) {
        $query = "INSERT INTO {$this->table_name} (N_Color, CodigoHex, Activo) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nombre, $codigoHex, $activo]);
    }

    // ACTUALIZAR COLOR
    public function actualizar($id, $nombre, $codigoHex, $activo = 1) {
        $query = "UPDATE {$this->table_name} 
                  SET N_Color = ?, CodigoHex = ?, Activo = ?
                  WHERE ID_Color = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nombre, $codigoHex, $activo, $id]);
    }

    // ELIMINAR COLOR
    public function eliminar($id) {
        $query = "DELETE FROM {$this->table_name} WHERE ID_Color = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // **NUEVO MÉTODO: FILTRAR COLORES**
    public function filtrarColores($termino = '', $estado = 'todos', $enUso = 'todos') {
        $query = "SELECT c.*, 
                        (SELECT COUNT(*) FROM producto 
                        WHERE ValorAtributo1 = c.N_Color 
                            OR ValorAtributo2 = c.N_Color 
                            OR ValorAtributo3 = c.N_Color) as productos_asociados
                FROM {$this->table_name} c
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($termino)) {
            $query .= " AND (c.N_Color LIKE ? OR c.CodigoHex LIKE ?)";
            $params[] = '%' . $termino . '%';
            $params[] = '%' . $termino . '%';
        }
        
        if ($estado !== 'todos') {
            $query .= " AND c.Activo = ?";
            $params[] = ($estado === 'activos') ? 1 : 0;
        }
        
        $query .= " ORDER BY LOWER(c.N_Color) ASC"; // <-- ORDENAR POR NOMBRE IGNORANDO MAYÚSCULAS/MINÚSCULAS
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filtrar por uso después de obtener los resultados
        if ($enUso !== 'todos') {
            $resultados = array_filter($resultados, function($color) use ($enUso) {
                $enUsoActual = ($color['productos_asociados'] ?? 0) > 0;
                return ($enUso === 'en_uso') ? $enUsoActual : !$enUsoActual;
            });
        }
        
        return array_values($resultados);
    }

    public function actualizarEstado($id, $estado) {
        $query = "UPDATE {$this->table_name} 
                SET Activo = ?
                WHERE ID_Color = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    // VERIFICAR SI UN COLOR ESTÁ EN USO
    public function estaEnUso($idColor) {
        // Obtener el nombre del color
        $query = "SELECT N_Color FROM color WHERE ID_Color = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idColor]);
        $colorNombre = $stmt->fetchColumn();
        
        if (!$colorNombre) return false;
        
        // Verificar en producto
        $query = "SELECT COUNT(*) FROM producto 
                  WHERE ValorAtributo1 = ? 
                     OR ValorAtributo2 = ? 
                     OR ValorAtributo3 = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$colorNombre, $colorNombre, $colorNombre]);
        
        return $stmt->fetchColumn() > 0;
    }

    // OBTENER ESTADÍSTICAS
    public function obtenerEstadisticas() {
        $stats = [];
        
        try {
            // Total colores
            $query = "SELECT COUNT(*) as total FROM {$this->table_name}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['total'] = (int)$stmt->fetchColumn();
            
            // Colores activos
            $query = "SELECT COUNT(*) as activos FROM {$this->table_name} WHERE Activo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['activos'] = (int)$stmt->fetchColumn();
            
            // Colores inactivos
            $query = "SELECT COUNT(*) as inactivos FROM {$this->table_name} WHERE Activo = 0";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['inactivos'] = (int)$stmt->fetchColumn();
            
            // Colores en uso
            $query = "SELECT COUNT(DISTINCT c.ID_Color) as en_uso 
                      FROM {$this->table_name} c
                      WHERE EXISTS (
                          SELECT 1 FROM producto p
                          WHERE p.ValorAtributo1 = c.N_Color 
                             OR p.ValorAtributo2 = c.N_Color 
                             OR p.ValorAtributo3 = c.N_Color
                      )";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $stats['en_uso'] = (int)$stmt->fetchColumn();
            
        } catch (Exception $e) {
            // Valores por defecto en caso de error
            $stats['total'] = 0;
            $stats['activos'] = 0;
            $stats['inactivos'] = 0;
            $stats['en_uso'] = 0;
        }
        
        return $stats;
    }

    // OBTENER PRODUCTOS QUE USAN UN COLOR
    public function obtenerProductosPorColor($idColor) {
        // Obtener el nombre del color
        $query = "SELECT N_Color FROM color WHERE ID_Color = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idColor]);
        $colorNombre = $stmt->fetchColumn();
        
        if (!$colorNombre) return [];
        
        // Consulta simplificada SIN Atributo y Stock
        $query = "SELECT DISTINCT
                    p.Nombre_Producto, 
                    a.N_Articulo as Articulo,
                    c.N_Categoria,
                    s.SubCategoria
                FROM producto p
                INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                INNER JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
                INNER JOIN subcategoria s ON a.ID_SubCategoria = s.ID_SubCategoria
                WHERE p.ValorAtributo1 = ? 
                    OR p.ValorAtributo2 = ? 
                    OR p.ValorAtributo3 = ?
                ORDER BY p.Nombre_Producto";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$colorNombre, $colorNombre, $colorNombre]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>