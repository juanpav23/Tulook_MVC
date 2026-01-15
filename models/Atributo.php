<?php
class Atributo {
    private $conn;
    private $table_name = "atributo_valor";
    private $table_tipo = "tipo_atributo";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER TODOS LOS ATRIBUTOS CON SUS TIPOS (EXCLUYENDO COLOR)
    public function obtenerTodos() {
        $query = "SELECT av.*, ta.Nombre as TipoNombre, ta.Descripcion as TipoDescripcion
                  FROM {$this->table_name} av
                  INNER JOIN {$this->table_tipo} ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                  WHERE ta.Nombre != 'Color'
                  ORDER BY ta.Nombre, av.Valor";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER ATRIBUTO POR ID (EXCLUYENDO COLOR)
    public function obtenerPorId($id) {
        $query = "SELECT av.*, ta.Nombre as TipoNombre, ta.Descripcion as TipoDescripcion
                  FROM {$this->table_name} av
                  INNER JOIN {$this->table_tipo} ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                  WHERE av.ID_AtributoValor = ? AND ta.Nombre != 'Color'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER TIPOS DE ATRIBUTO (EXCLUYENDO COLOR)
    public function obtenerTipos() {
        $query = "SELECT * FROM {$this->table_tipo} 
                  WHERE Activo = 1 AND Nombre != 'Color'
                  ORDER BY Nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VERIFICAR SI EXISTE UN ATRIBUTO CON EL MISMO VALOR Y TIPO
    public function existeAtributo($valor, $tipoId, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM {$this->table_name} av
                  INNER JOIN {$this->table_tipo} ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                  WHERE av.Valor = ? AND av.ID_TipoAtributo = ? AND ta.Nombre != 'Color'";
        $params = [$valor, $tipoId];
        
        if ($excluirId) {
            $query .= " AND av.ID_AtributoValor != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // OBTENER EL SIGUIENTE ORDEN AUTOMÁTICO PARA UN TIPO
    public function obtenerSiguienteOrden($tipoId) {
        $query = "SELECT MAX(Orden) FROM {$this->table_name} 
                  WHERE ID_TipoAtributo = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$tipoId]);
        $maxOrden = $stmt->fetchColumn();
        return ($maxOrden !== null) ? $maxOrden + 1 : 1;
    }

    // CREAR NUEVO ATRIBUTO CON ORDEN AUTOMÁTICO
    public function crear($tipoId, $valor, $activo) {
        if ($this->existeAtributo($valor, $tipoId)) {
            throw new Exception("Ya existe un atributo con el valor '{$valor}' para este tipo");
        }

        $orden = $this->obtenerSiguienteOrden($tipoId);

        $query = "INSERT INTO {$this->table_name} 
                 (ID_TipoAtributo, Valor, Orden, Activo) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$tipoId, $valor, $orden, $activo]);
    }

    // ACTUALIZAR ATRIBUTO
    public function actualizar($id, $tipoId, $valor, $activo) {
        $atributoActual = $this->obtenerPorId($id);
        if ($atributoActual && ($id == 16 || strtolower($atributoActual['Valor']) === 'única')) {
            throw new Exception('El valor "Única" no puede ser editado');
        }

        if ($this->existeAtributo($valor, $tipoId, $id)) {
            throw new Exception("Ya existe otro atributo con el valor '{$valor}' para este tipo");
        }

        $query = "UPDATE {$this->table_name} 
                 SET ID_TipoAtributo = ?, Valor = ?, Activo = ?
                 WHERE ID_AtributoValor = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$tipoId, $valor, $activo, $id]);
    }

    // CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
    public function cambiarEstado($id, $estado) {
        $atributo = $this->obtenerPorId($id);
        if ($atributo && ($id == 16 || strtolower($atributo['Valor']) === 'única')) {
            throw new Exception('El valor "Única" no puede cambiar de estado');
        }
        
        if ($estado == 0 && $this->estaEnUso($id)) {
            throw new Exception("El atributo está siendo usado por productos y no puede desactivarse");
        }
        
        $query = "UPDATE {$this->table_name} SET Activo = ? WHERE ID_AtributoValor = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    // BUSCAR ATRIBUTOS (EXCLUYENDO COLOR) CON FILTRO DE USO
    public function buscar($termino = '', $tipoId = '', $estado = '', $enUso = '') {
        $query = "SELECT av.*, ta.Nombre as TipoNombre, ta.Descripcion as TipoDescripcion
                  FROM {$this->table_name} av
                  INNER JOIN {$this->table_tipo} ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                  WHERE ta.Nombre != 'Color'";
        $params = [];

        if (!empty($termino)) {
            $query .= " AND (av.Valor LIKE ? OR ta.Nombre LIKE ?)";
            $params[] = '%' . $termino . '%';
            $params[] = '%' . $termino . '%';
        }

        if (!empty($tipoId)) {
            $query .= " AND av.ID_TipoAtributo = ?";
            $params[] = $tipoId;
        }

        if ($estado !== '') {
            $query .= " AND av.Activo = ?";
            $params[] = $estado;
        }

        $query .= " ORDER BY ta.Nombre, av.Valor";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $atributos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Aplicar filtro de uso si está especificado
        if ($enUso !== '') {
            $filtrados = [];
            foreach ($atributos as $attr) {
                $estaEnUso = $this->estaEnUso($attr['ID_AtributoValor']);
                if (($enUso === 'si' && $estaEnUso) || ($enUso === 'no' && !$estaEnUso)) {
                    $filtrados[] = $attr;
                }
            }
            return $filtrados;
        }
        
        return $atributos;
    }

    // OBTENER ESTADÍSTICAS (EXCLUYENDO COLORES)
    public function obtenerEstadisticas() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN av.Activo = 1 THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN av.Activo = 0 THEN 1 ELSE 0 END) as inactivos,
                    COUNT(DISTINCT av.ID_TipoAtributo) as tipos_diferentes
                  FROM {$this->table_name} av
                  INNER JOIN {$this->table_tipo} ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                  WHERE ta.Nombre != 'Color'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // VERIFICAR SI UN ATRIBUTO ESTÁ EN USO
    public function estaEnUso($idAtributo) {
        $atributo = $this->obtenerPorId($idAtributo);
        if ($atributo && ($idAtributo == 16 || strtolower($atributo['Valor']) === 'única')) {
            return true;
        }
        
        $query = "SELECT COUNT(*) as total_uso 
                FROM producto 
                WHERE (ID_Atributo1 = ? OR ID_Atributo2 = ? OR ID_Atributo3 = ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idAtributo, $idAtributo, $idAtributo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total_uso'] == 0 && $atributo) {
            $query2 = "SELECT COUNT(*) as total_uso_valor 
                    FROM producto 
                    WHERE (ValorAtributo1 = ? OR ValorAtributo2 = ? OR ValorAtributo3 = ?)";
            
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->execute([$atributo['Valor'], $atributo['Valor'], $atributo['Valor']]);
            $result2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            return ($result2['total_uso_valor'] > 0);
        }
        
        return ($result['total_uso'] > 0);
    }

    // OBTENER PRODUCTOS QUE USAN UN ATRIBUTO
    public function obtenerProductosPorAtributo($idAtributo) {
        $atributo = $this->obtenerPorId($idAtributo);
        if (!$atributo) {
            return [];
        }
        
        $query = "SELECT 
                    p.Nombre_Producto, 
                    a.N_Articulo as Articulo
                FROM producto p
                INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                WHERE (p.ID_Atributo1 = ? OR p.ID_Atributo2 = ? OR p.ID_Atributo3 = ?
                       OR p.ValorAtributo1 = ? OR p.ValorAtributo2 = ? OR p.ValorAtributo3 = ?)
                ORDER BY p.Nombre_Producto";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            $idAtributo, $idAtributo, $idAtributo,
            $atributo['Valor'], $atributo['Valor'], $atributo['Valor']
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // REORGANIZAR ÓRDENES DESPUÉS DE ELIMINAR
    private function reorganizarOrdenes($tipoId) {
        $query = "SELECT ID_AtributoValor, Orden 
                FROM {$this->table_name} 
                WHERE ID_TipoAtributo = ? 
                ORDER BY Orden";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$tipoId]);
        $atributos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $nuevoOrden = 1;
        foreach ($atributos as $atributo) {
            $updateQuery = "UPDATE {$this->table_name} SET Orden = ? WHERE ID_AtributoValor = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([$nuevoOrden, $atributo['ID_AtributoValor']]);
            $nuevoOrden++;
        }
    }

    // ELIMINAR ATRIBUTO
    public function eliminar($id) {
        $atributo = $this->obtenerPorId($id);
        if (!$atributo) {
            throw new Exception('Atributo no encontrado');
        }
        
        if ($id == 16 || strtolower($atributo['Valor']) === 'única') {
            throw new Exception('El valor "Única" es un valor universal del sistema y no puede eliminarse');
        }
        
        if ($this->estaEnUso($id)) {
            throw new Exception('El atributo está siendo usado por productos y no puede eliminarse');
        }
        
        $query = "DELETE FROM {$this->table_name} WHERE ID_AtributoValor = ?";
        $stmt = $this->conn->prepare($query);
        
        if ($stmt->execute([$id])) {
            $this->reorganizarOrdenes($atributo['ID_TipoAtributo']);
            return true;
        }
        
        return false;
    }
}
?>