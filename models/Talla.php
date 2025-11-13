<?php
// models/Talla.php

class Talla {
    private $db;
    private $table = 'talla';

    public function __construct($db) {
        $this->db = $db;
    }

    public function obtenerTodas($buscar = '', $estado = '') {
        $sql = "SELECT t.*, 
                       COALESCE(st.Sobrecosto, 0) as Sobrecosto,
                       st.Fecha_Actualizacion as FechaActualizacionSobrecosto
                FROM {$this->table} t 
                LEFT JOIN sobrecosto_talla st ON t.ID_Talla = st.ID_Talla 
                WHERE 1=1";
        
        $params = [];

        if (!empty($buscar)) {
            $sql .= " AND t.N_Talla LIKE ?";
            $params[] = "%{$buscar}%";
        }

        if ($estado === 'activo') {
            $sql .= " AND t.ID_Talla > 1";
        } elseif ($estado === 'inactivo') {
            $sql .= " AND t.ID_Talla = 1";
        }

        $sql .= " ORDER BY t.ID_Talla ASC";

        $stmt = $this->db->prepare($sql);
        
        if (!empty($params)) {
            $stmt->execute($params);
        } else {
            $stmt->execute();
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT t.*, 
                       COALESCE(st.Sobrecosto, 0) as Sobrecosto,
                       st.Fecha_Actualizacion as FechaActualizacionSobrecosto
                FROM {$this->table} t 
                LEFT JOIN sobrecosto_talla st ON t.ID_Talla = st.ID_Talla 
                WHERE t.ID_Talla = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerPorNombre($nombre) {
        $sql = "SELECT * FROM {$this->table} WHERE N_Talla = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeTalla($nombre) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE N_Talla = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$nombre]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }

    public function crear($nombre, $activo = 1) {
        $sql = "INSERT INTO {$this->table} (N_Talla) VALUES (?)";
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([$nombre]);
            $nuevoId = $this->db->lastInsertId();
            
            // Crear registro en sobrecosto_talla
            $sqlSobrecosto = "INSERT INTO sobrecosto_talla (ID_Talla, Sobrecosto) VALUES (?, 0)";
            $stmtSobrecosto = $this->db->prepare($sqlSobrecosto);
            $stmtSobrecosto->execute([$nuevoId]);
            
            return $nuevoId;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizar($id, $nombre, $activo = 1) {
        $sql = "UPDATE {$this->table} SET N_Talla = ? WHERE ID_Talla = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            return $stmt->execute([$nombre, $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function cambiarEstado($id, $estado) {
        // En este caso, no tenemos campo Activo en la tabla talla
        return true;
    }

    public function contarActivas() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE ID_Talla > 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }

    public function contarTotal() {
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'];
    }
}
?>