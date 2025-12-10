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
                  WHERE ta.Nombre != 'Color'  -- Excluir colores
                  ORDER BY ta.Nombre, av.Valor";  // Ordenar por tipo y luego valor
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

    // OBTENER TIPO ESPECÍFICO
    public function obtenerTipoPorId($tipoId) {
        $query = "SELECT * FROM {$this->table_tipo} 
                  WHERE ID_TipoAtributo = ? AND Nombre != 'Color'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$tipoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
        // Verificar si ya existe un atributo con el mismo valor y tipo
        if ($this->existeAtributo($valor, $tipoId)) {
            throw new Exception("Ya existe un atributo con el valor '{$valor}' para este tipo");
        }

        // Obtener orden automático
        $orden = $this->obtenerSiguienteOrden($tipoId);

        $query = "INSERT INTO {$this->table_name} 
                 (ID_TipoAtributo, Valor, Orden, Activo) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$tipoId, $valor, $orden, $activo]);
    }

    // ACTUALIZAR ATRIBUTO
    public function actualizar($id, $tipoId, $valor, $activo) {
        // Verificar si ya existe otro atributo con el mismo valor y tipo
        if ($this->existeAtributo($valor, $tipoId, $id)) {
            throw new Exception("Ya existe otro atributo con el valor '{$valor}' para este tipo");
        }

        // Mantener el orden actual (no lo actualizamos, es automático)
        $query = "UPDATE {$this->table_name} 
                 SET ID_TipoAtributo = ?, Valor = ?, Activo = ?
                 WHERE ID_AtributoValor = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$tipoId, $valor, $activo, $id]);
    }

    // CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
    public function cambiarEstado($id, $estado) {
        $query = "UPDATE {$this->table_name} SET Activo = ? WHERE ID_AtributoValor = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    // BUSCAR ATRIBUTOS (EXCLUYENDO COLOR)
    public function buscar($termino = '', $tipoId = '', $estado = '') {
        $query = "SELECT av.*, ta.Nombre as TipoNombre, ta.Descripcion as TipoDescripcion
                  FROM {$this->table_name} av
                  INNER JOIN {$this->table_tipo} ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                  WHERE ta.Nombre != 'Color'";
        $params = [];

        // Búsqueda por valor
        if (!empty($termino)) {
            $query .= " AND (av.Valor LIKE ? OR ta.Nombre LIKE ?)";
            $params[] = '%' . $termino . '%';
            $params[] = '%' . $termino . '%';
        }

        // Filtro por tipo
        if (!empty($tipoId)) {
            $query .= " AND av.ID_TipoAtributo = ?";
            $params[] = $tipoId;
        }

        // Filtro por estado
        if ($estado !== '') {
            $query .= " AND av.Activo = ?";
            $params[] = $estado;
        }

        $query .= " ORDER BY ta.Nombre, av.Valor";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    // OBTENER ATRIBUTOS POR TIPO
    public function obtenerPorTipo($tipoId, $soloActivos = true) {
        $query = "SELECT av.* FROM {$this->table_name} av
                  INNER JOIN {$this->table_tipo} ta ON av.ID_TipoAtributo = ta.ID_TipoAtributo
                  WHERE av.ID_TipoAtributo = ? AND ta.Nombre != 'Color'";
        
        if ($soloActivos) {
            $query .= " AND av.Activo = 1";
        }
        
        $query .= " ORDER BY av.Valor";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$tipoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER INPUT TYPE SEGÚN TIPO DE ATRIBUTO
    public function obtenerInputTypePorTipo($tipoId) {
        $tipo = $this->obtenerTipoPorId($tipoId);
        if (!$tipo) return 'text';
        
        $tipoNombre = strtolower($tipo['Nombre']);
        
        switch ($tipoNombre) {
            case 'talla':
                return 'select'; // Select con opciones predefinidas
            case 'medida':
                return 'measurements'; // Inputs especiales para medidas
            case 'volumen':
                return 'volume'; // Input especial para volumen
            case 'tamaño':
                return 'size'; // Select con tamaños predefinidos
            default:
                return 'text';
        }
    }

    // VERIFICAR SI UN ATRIBUTO ESTÁ EN USO
    public function estaEnUso($idAtributo) {
        $query = "SELECT COUNT(*) FROM producto 
                  WHERE ID_Atributo1 = ? OR ID_Atributo2 = ? OR ID_Atributo3 = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idAtributo, $idAtributo, $idAtributo]);
        
        return $stmt->fetchColumn() > 0;
    }

    // OBTENER PRODUCTOS QUE USAN UN ATRIBUTO
    public function obtenerProductosPorAtributo($idAtributo) {
        $query = "SELECT p.Nombre_Producto, a.N_Articulo as Articulo
                  FROM producto p
                  INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                  WHERE p.ID_Atributo1 = ? OR p.ID_Atributo2 = ? OR p.ID_Atributo3 = ?
                  ORDER BY p.Nombre_Producto";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idAtributo, $idAtributo, $idAtributo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER OPCIONES PREGENERADAS POR TIPO DE ATRIBUTO (BASADO EN TUS DATOS)
    public function obtenerOpcionesPorTipo($tipoId) {
        $tipo = $this->obtenerTipoPorId($tipoId);
        if (!$tipo) return [];
        
        $tipoNombre = strtolower($tipo['Nombre']);
        
        // Basado en tus datos existentes en la tabla
        switch ($tipoNombre) {
            case 'talla':
                return [
                    'XS', 'S', 'M', 'L', 'XL', 'XXL',
                    '28', '30', '32', '34', '36', '38', '40', '42', '44',
                    'Única'
                ];
            case 'medida':
                return [
                    '16', '17', '18', '19', '20', '28', '30', '32', '34', '36',
                    'Ajuste Estándar', 'Correa Corta', 'Correa Larga'
                ];
            case 'tamaño':
                return [
                    'Pequeño', 'Mediano', 'Grande', 'Extra Grande'
                ];
            case 'volumen':
                return [
                    '30 ml', '50 ml', '75 ml', '100 ml', '150 ml'
                ];
            default:
                return [];
        }
    }
}
?>