<?php
class Precio {
    private $conn;
    private $table_name = "precio";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER TODOS LOS PRECIOS
    public function obtenerTodos() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY Valor ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PRECIO POR ID
    public function obtenerPorId($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ID_precio = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    // CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
    public function cambiarEstado($id, $estado) {
        $query = "UPDATE " . $this->table_name . " 
                 SET Activo = ?, FechaAct = NOW() 
                 WHERE ID_precio = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    // OBTENER PRECIOS DUPLICADOS
    public function obtenerDuplicados() {
        $query = "SELECT Valor, COUNT(*) as cantidad, 
                         GROUP_CONCAT(ID_precio) as ids,
                         GROUP_CONCAT(Activo) as estados
                  FROM " . $this->table_name . " 
                  GROUP BY Valor 
                  HAVING COUNT(*) > 1 
                  ORDER BY Valor ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    // VERIFICAR SI UN PRECIO ESTÁ EN USO
    public function estaEnUso($idPrecio) {
        $query = "SELECT COUNT(*) FROM articulo WHERE ID_Precio = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idPrecio]);
        return $stmt->fetchColumn() > 0;
    }

    // MIGRAR PRODUCTOS DE UN PRECIO A OTRO
    public function migrarProductos($idPrecioOrigen, $idPrecioDestino) {
        $query = "UPDATE articulo SET ID_Precio = ? WHERE ID_Precio = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$idPrecioDestino, $idPrecioOrigen]);
    }

    // LIMPIAR PRECIOS DUPLICADOS
    public function limpiarDuplicados() {
        $duplicados = $this->obtenerDuplicados();
        $resultados = [
            'eliminados' => 0,
            'migrados' => 0,
            'errores' => []
        ];

        foreach ($duplicados as $duplicado) {
            $valor = $duplicado['Valor'];
            $ids = explode(',', $duplicado['ids']);
            $estados = explode(',', $duplicado['estados']);

            // Mantener el primer precio activo, o el primero si ninguno está activo
            $precioMantener = null;
            $preciosEliminar = [];

            foreach ($ids as $index => $id) {
                if ($estados[$index] == 1 && !$precioMantener) {
                    $precioMantener = $id;
                } else {
                    $preciosEliminar[] = $id;
                }
            }

            // Si no hay precio activo, mantener el primero
            if (!$precioMantener) {
                $precioMantener = $ids[0];
                array_shift($preciosEliminar);
            }

            // Migrar productos y eliminar duplicados
            foreach ($preciosEliminar as $idEliminar) {
                try {
                    // Migrar productos si es necesario
                    if ($this->estaEnUso($idEliminar)) {
                        $this->migrarProductos($idEliminar, $precioMantener);
                        $resultados['migrados']++;
                    }

                    // Eliminar el precio duplicado
                    $this->eliminar($idEliminar);
                    $resultados['eliminados']++;
                } catch (Exception $e) {
                    $resultados['errores'][] = "Error con precio #$idEliminar: " . $e->getMessage();
                }
            }
        }

        return $resultados;
    }

    // CONTAR PRECIOS ACTIVOS
    public function contarActivos() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // OBTENER PRECIOS ACTIVOS
    public function obtenerActivos() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Activo = 1 ORDER BY Valor ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // BUSCAR PRECIOS POR VALOR O ESTADO
    public function buscar($termino = '', $filtroEstado = '') {
        $query = "SELECT * FROM " . $this->table_name . " WHERE 1=1";
        $params = [];

        // Búsqueda por valor
        if (!empty($termino)) {
            $query .= " AND (Valor = ? OR Valor LIKE ?)";
            $params[] = $termino;
            $params[] = '%' . $termino . '%';
        }

        // Filtro por estado
        if ($filtroEstado !== '') {
            $query .= " AND Activo = ?";
            $params[] = $filtroEstado;
        }

        $query .= " ORDER BY Valor ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>