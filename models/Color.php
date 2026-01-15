<?php
class Color {
    private $conn;
    private $table_name = "color";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER TODOS LOS COLORES
    public function obtenerTodos() {
        $query = "SELECT * FROM {$this->table_name} ORDER BY N_Color ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER COLOR POR ID
    public function obtenerPorId($id) {
        $query = "SELECT * FROM {$this->table_name} WHERE ID_Color = ?";
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
    public function crear($nombre, $codigoHex) {
        $query = "INSERT INTO {$this->table_name} (N_Color, CodigoHex) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nombre, $codigoHex]);
    }

    // ACTUALIZAR COLOR
    public function actualizar($id, $nombre, $codigoHex) {
        $query = "UPDATE {$this->table_name} 
                  SET N_Color = ?, CodigoHex = ?
                  WHERE ID_Color = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$nombre, $codigoHex, $id]);
    }

    // ELIMINAR COLOR
    public function eliminar($id) {
        $query = "DELETE FROM {$this->table_name} WHERE ID_Color = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // BUSCAR COLORES
    public function buscar($termino = '') {
        $query = "SELECT * FROM {$this->table_name} 
                  WHERE N_Color LIKE ? OR CodigoHex LIKE ?
                  ORDER BY N_Color ASC";
        $params = ['%' . $termino . '%', '%' . $termino . '%'];
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // VERIFICAR SI UN COLOR ESTÁ EN USO
    public function estaEnUso($idColor) {
        // Verificar en atributos (ValorAtributo1, ValorAtributo2, ValorAtributo3)
        $query = "SELECT COUNT(*) FROM producto 
                  WHERE ValorAtributo1 = (SELECT N_Color FROM color WHERE ID_Color = ?)
                     OR ValorAtributo2 = (SELECT N_Color FROM color WHERE ID_Color = ?)
                     OR ValorAtributo3 = (SELECT N_Color FROM color WHERE ID_Color = ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idColor, $idColor, $idColor]);
        
        return $stmt->fetchColumn() > 0;
    }

    // OBTENER ESTADÍSTICAS
    public function obtenerEstadisticas() {
        $query = "SELECT COUNT(*) as total FROM {$this->table_name}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER PRODUCTOS QUE USAN UN COLOR
    public function obtenerProductosPorColor($idColor) {
        $query = "SELECT 
                    p.Nombre_Producto, 
                    a.N_Articulo as Articulo,
                    p.ValorAtributo1,
                    p.ValorAtributo2,
                    p.ValorAtributo3
                  FROM producto p
                  INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                  WHERE p.ValorAtributo1 = (SELECT N_Color FROM color WHERE ID_Color = ?)
                     OR p.ValorAtributo2 = (SELECT N_Color FROM color WHERE ID_Color = ?)
                     OR p.ValorAtributo3 = (SELECT N_Color FROM color WHERE ID_Color = ?)
                  ORDER BY p.Nombre_Producto";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idColor, $idColor, $idColor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>