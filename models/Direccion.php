<?php
class Direccion {
    private $conn;
    private $table = "direccion";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerDireccionesUsuario($id_usuario) {
        $sql = "SELECT * FROM direccion WHERE ID_Usuario = ? ORDER BY Predeterminada DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ AGREGAR ESTE MÉTODO NUEVO
    public function obtenerDireccionPorId($id_direccion) {
        $sql = "SELECT * FROM direccion WHERE ID_Direccion = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_direccion]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ CREAR NUEVA DIRECCIÓN
    public function crear($data) {
        $sql = "INSERT INTO direccion (ID_Usuario, Direccion, Ciudad, Departamento, CodigoPostal, Predeterminada) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        // Si es la primera dirección, establecer como predeterminada
        $direcciones = $this->obtenerDireccionesUsuario($data['ID_Usuario']);
        $predeterminada = empty($direcciones) ? 1 : 0;
        
        return $stmt->execute([
            $data['ID_Usuario'],
            $data['Direccion'],
            $data['Ciudad'],
            $data['Departamento'],
            $data['CodigoPostal'],
            $predeterminada
        ]);
    }

    // ✅ ACTUALIZAR DIRECCIÓN EXISTENTE
    public function actualizar($data) {
        $sql = "UPDATE direccion 
                SET Direccion = ?, Ciudad = ?, Departamento = ?, CodigoPostal = ? 
                WHERE ID_Direccion = ? AND ID_Usuario = ?";
        $stmt = $this->conn->prepare($sql);
        
        return $stmt->execute([
            $data['Direccion'],
            $data['Ciudad'],
            $data['Departamento'],
            $data['CodigoPostal'],
            $data['ID_Direccion'],
            $data['ID_Usuario']
        ]);
    }

    // ✅ ELIMINAR DIRECCIÓN
    public function eliminar($id_direccion) {
        $sql = "DELETE FROM direccion WHERE ID_Direccion = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id_direccion]);
    }

    // ✅ VERIFICAR SI LA DIRECCIÓN PERTENECE AL USUARIO
    public function perteneceAUsuario($id_direccion, $id_usuario) {
        $sql = "SELECT COUNT(*) FROM direccion WHERE ID_Direccion = ? AND ID_Usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_direccion, $id_usuario]);
        return $stmt->fetchColumn() > 0;
    }

    // ✅ ESTABLECER DIRECCIÓN COMO PREDETERMINADA
    public function establecerPredeterminada($id_direccion, $id_usuario) {
        try {
            // Iniciar transacción
            $this->conn->beginTransaction();
            
            // Quitar todas las predeterminadas del usuario
            $sql1 = "UPDATE direccion SET Predeterminada = 0 WHERE ID_Usuario = ?";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->execute([$id_usuario]);
            
            // Establecer la nueva dirección como predeterminada
            $sql2 = "UPDATE direccion SET Predeterminada = 1 WHERE ID_Direccion = ? AND ID_Usuario = ?";
            $stmt2 = $this->conn->prepare($sql2);
            $result = $stmt2->execute([$id_direccion, $id_usuario]);
            
            // Confirmar transacción
            $this->conn->commit();
            return $result;
            
        } catch (Exception $e) {
            // Revertir en caso de error
            $this->conn->rollBack();
            return false;
        }
    }

    // ✅ OBTENER DIRECCIÓN PREDETERMINADA DEL USUARIO
    public function obtenerPredeterminada($id_usuario) {
        $sql = "SELECT * FROM direccion WHERE ID_Usuario = ? AND Predeterminada = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ CONTAR DIRECCIONES DEL USUARIO
    public function contarDireccionesUsuario($id_usuario) {
        $sql = "SELECT COUNT(*) FROM direccion WHERE ID_Usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchColumn();
    }
}
?>