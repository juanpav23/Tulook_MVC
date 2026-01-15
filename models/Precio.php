<?php
class Precio {
    private $conn;
    private $table_name = "precio";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER TODOS LOS PRECIOS CON INFORMACIÓN DE USO
    public function obtenerTodos() {
        $query = "SELECT p.*, 
                         (SELECT COUNT(*) FROM articulo a WHERE a.ID_Precio = p.ID_precio) as uso_articulos,
                         (SELECT COUNT(*) FROM producto pr WHERE pr.Porcentaje = p.ID_precio) as uso_variantes
                  FROM " . $this->table_name . " p 
                  ORDER BY Valor ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular total de uso para cada precio
        foreach ($resultados as &$precio) {
            $precio['total_uso'] = (int)$precio['uso_articulos'] + (int)$precio['uso_variantes'];
            $precio['en_uso'] = $precio['total_uso'] > 0;
        }
        
        return $resultados;
    }

    // OBTENER PRECIO POR ID CON INFORMACIÓN DE USO
    public function obtenerPorId($id) {
        $query = "SELECT p.*, 
                         (SELECT COUNT(*) FROM articulo a WHERE a.ID_Precio = p.ID_precio) as uso_articulos,
                         (SELECT COUNT(*) FROM producto pr WHERE pr.Porcentaje = p.ID_precio) as uso_variantes
                  FROM " . $this->table_name . " p 
                  WHERE ID_precio = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $precio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($precio) {
            $precio['total_uso'] = (int)$precio['uso_articulos'] + (int)$precio['uso_variantes'];
            $precio['en_uso'] = $precio['total_uso'] > 0;
        }
        
        return $precio;
    }

    // VERIFICAR SI UN PRECIO ESTÁ EN USO (método directo)
    public function estaEnUso($idPrecio) {
        $query = "SELECT (SELECT COUNT(*) FROM articulo WHERE ID_Precio = ?) + 
                         (SELECT COUNT(*) FROM producto WHERE Porcentaje = ?) as total";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idPrecio, $idPrecio]);
        $total = (int)$stmt->fetchColumn();
        
        return $total > 0;
    }

    // OBTENER PRODUCTOS QUE USAN UN PRECIO (artículos base y variantes)
    public function obtenerProductosPorPrecio($idPrecio) {
        $resultados = [];
        
        // Obtener artículos base que usan este precio
        $queryArticulos = "SELECT a.ID_Articulo, a.N_Articulo, a.Activo, 
                                  c.N_Categoria as Categoria,
                                  s.SubCategoria,
                                  g.N_Genero as Genero
                           FROM articulo a
                           LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
                           LEFT JOIN subcategoria s ON a.ID_SubCategoria = s.ID_SubCategoria
                           LEFT JOIN genero g ON a.ID_Genero = g.ID_Genero
                           WHERE a.ID_Precio = ?";
        $stmt = $this->conn->prepare($queryArticulos);
        $stmt->execute([$idPrecio]);
        $resultados['articulos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener variantes (productos) que usan este precio
        $queryVariantes = "SELECT p.ID_Producto, 
                                  p.Nombre_Producto, 
                                  p.ValorAtributo1, 
                                  p.ValorAtributo2, 
                                  p.ValorAtributo3,
                                  p.Cantidad,
                                  a.N_Articulo as ArticuloNombre,
                                  a.ID_Articulo,
                                  c.N_Categoria as Categoria,
                                  s.SubCategoria
                           FROM producto p
                           INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                           LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
                           LEFT JOIN subcategoria s ON a.ID_SubCategoria = s.ID_SubCategoria
                           WHERE p.Porcentaje = ?
                           ORDER BY p.Nombre_Producto";
        $stmt = $this->conn->prepare($queryVariantes);
        $stmt->execute([$idPrecio]);
        $resultados['variantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $resultados;
    }

    // OBTENER PRECIOS ACTIVOS (para migración)
    public function obtenerPreciosActivos($excluirId = null) {
        $query = "SELECT ID_precio, Valor FROM " . $this->table_name . " 
                  WHERE Activo = 1";
        
        $params = [];
        if ($excluirId) {
            $query .= " AND ID_precio != ?";
            $params[] = $excluirId;
        }
        
        $query .= " ORDER BY Valor ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        
        $precios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        
        foreach ($precios as $precio) {
            $resultado[$precio['ID_precio']] = '$' . number_format($precio['Valor'], 2);
        }
        
        return $resultado;
    }

    // MIGRAR PRODUCTOS DE UN PRECIO A OTRO
    public function migrarProductos($idPrecioOrigen, $idPrecioDestino) {
        $this->conn->beginTransaction();
        
        try {
            // Migrar artículos base
            $queryArticulos = "UPDATE articulo SET ID_Precio = ? WHERE ID_Precio = ?";
            $stmt = $this->conn->prepare($queryArticulos);
            $stmt->execute([$idPrecioDestino, $idPrecioOrigen]);
            $articulosMigrados = $stmt->rowCount();
            
            // Migrar variantes (productos)
            $queryProductos = "UPDATE producto SET Porcentaje = ? WHERE Porcentaje = ?";
            $stmt = $this->conn->prepare($queryProductos);
            $stmt->execute([$idPrecioDestino, $idPrecioOrigen]);
            $variantesMigradas = $stmt->rowCount();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'articulos_migrados' => $articulosMigrados,
                'variantes_migradas' => $variantesMigradas,
                'total_migrados' => $articulosMigrados + $variantesMigradas,
                'message' => 'Migración completada exitosamente'
            ];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw new Exception("Error al migrar productos: " . $e->getMessage());
        }
    }

    // ELIMINAR PRECIO (solo si no está en uso)
    public function eliminar($id) {
        // Verificar si el precio está siendo usado
        if ($this->estaEnUso($id)) {
            throw new Exception("No se puede eliminar el precio porque está siendo usado por productos");
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE ID_precio = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // CAMBIAR ESTADO (con validación de uso)
    public function cambiarEstado($id, $estado) {
        // Si se quiere desactivar, verificar si está en uso
        if ($estado == 0) {
            if ($this->estaEnUso($id)) {
                throw new Exception("No se puede desactivar un precio que está siendo usado por productos");
            }
        }

        $query = "UPDATE " . $this->table_name . " 
                 SET Activo = ?, FechaAct = NOW() 
                 WHERE ID_precio = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    // CREAR NUEVO PRECIO
    public function crear($valor, $activo) {
        // Verificar si ya existe un precio con el mismo valor
        if ($this->existePrecio($valor)) {
            throw new Exception("Ya existe un precio con el valor $" . number_format($valor, 2));
        }

        $query = "INSERT INTO " . $this->table_name . " 
                 (Valor, Activo, FechaAct) 
                 VALUES (?, ?, NOW())";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$valor, $activo]);
    }

    // ACTUALIZAR PRECIO
    public function actualizar($id, $valor, $activo) {
        // Si se quiere desactivar, verificar si está en uso
        if ($activo == 0 && $this->estaEnUso($id)) {
            throw new Exception("No se puede desactivar un precio que está siendo usado por productos");
        }

        // Verificar si ya existe otro precio con el mismo valor
        if ($this->existePrecio($valor, $id)) {
            throw new Exception("Ya existe otro precio con el valor $" . number_format($valor, 2));
        }

        $query = "UPDATE " . $this->table_name . " 
                 SET Valor = ?, Activo = ?, FechaAct = NOW() 
                 WHERE ID_precio = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$valor, $activo, $id]);
    }

    // VERIFICAR SI EXISTE UN PRECIO CON EL MISMO VALOR
    public function existePrecio($valor, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Valor = ?";
        $params = [$valor];
        
        if ($excluirId) {
            $query .= " AND ID_precio != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // BUSCAR PRECIOS
    public function buscar($termino = '', $estado = '') {
        $query = "SELECT p.*, 
                         (SELECT COUNT(*) FROM articulo a WHERE a.ID_Precio = p.ID_precio) as uso_articulos,
                         (SELECT COUNT(*) FROM producto pr WHERE pr.Porcentaje = p.ID_precio) as uso_variantes
                  FROM " . $this->table_name . " p 
                  WHERE 1=1";
        
        $params = [];
        
        if (!empty($termino)) {
            if (is_numeric($termino)) {
                $query .= " AND (p.Valor = ? OR p.Valor LIKE ?)";
                $params[] = $termino;
                $params[] = '%' . $termino . '%';
            } else {
                $query .= " AND (p.Valor LIKE ?)";
                $params[] = '%' . $termino . '%';
            }
        }
        
        if ($estado !== '') {
            $query .= " AND p.Activo = ?";
            $params[] = $estado;
        }
        
        $query .= " ORDER BY p.Valor ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular total de uso para cada precio
        foreach ($resultados as &$precio) {
            $precio['total_uso'] = (int)$precio['uso_articulos'] + (int)$precio['uso_variantes'];
            $precio['en_uso'] = $precio['total_uso'] > 0;
        }
        
        return $resultados;
    }

    // FILTRAR PRECIOS (como en el ejemplo de colores)
    public function filtrarPrecios($termino = '', $estado = 'todos', $enUso = 'todos') {
        $resultados = $this->buscar($termino, $estado !== 'todos' ? ($estado === 'activos' ? 1 : 0) : '');
        
        // Filtrar por uso si es necesario
        if ($enUso !== 'todos') {
            $resultados = array_filter($resultados, function($precio) use ($enUso) {
                return ($enUso === 'si') ? $precio['en_uso'] : !$precio['en_uso'];
            });
        }
        
        return array_values($resultados);
    }

    // OBTENER ESTADÍSTICAS
    public function obtenerEstadisticas() {
        $estadisticas = [];
        
        try {
            // Total precios
            $query = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $estadisticas['total'] = (int)$stmt->fetchColumn();
            
            // Precios activos
            $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Activo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $estadisticas['activos'] = (int)$stmt->fetchColumn();
            
            // Precios inactivos
            $estadisticas['inactivos'] = $estadisticas['total'] - $estadisticas['activos'];
            
            // Precios en uso
            $query = "SELECT COUNT(DISTINCT ID_precio) FROM articulo WHERE ID_Precio IS NOT NULL
                      UNION
                      SELECT COUNT(DISTINCT Porcentaje) FROM producto WHERE Porcentaje IS NOT NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $estadisticas['en_uso'] = (int)$stmt->fetchColumn();
            
            // Valor promedio
            $query = "SELECT AVG(Valor) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $estadisticas['valor_promedio'] = round($stmt->fetchColumn(), 2);
            
            // Valor mínimo y máximo
            $query = "SELECT MIN(Valor), MAX(Valor) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $minMax = $stmt->fetch(PDO::FETCH_NUM);
            $estadisticas['valor_minimo'] = $minMax[0] ?? 0;
            $estadisticas['valor_maximo'] = $minMax[1] ?? 0;
            
        } catch (Exception $e) {
            // Valores por defecto en caso de error
            $estadisticas['total'] = 0;
            $estadisticas['activos'] = 0;
            $estadisticas['inactivos'] = 0;
            $estadisticas['en_uso'] = 0;
            $estadisticas['valor_promedio'] = 0;
            $estadisticas['valor_minimo'] = 0;
            $estadisticas['valor_maximo'] = 0;
        }
        
        return $estadisticas;
    }

    // ORDENAR PRECIOS POR VALOR (ignorando mayúsculas/minúsculas)
    public function obtenerTodosOrdenados() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY Valor ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>