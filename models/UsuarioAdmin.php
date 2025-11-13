<?php
class UsuarioAdmin {
    private $conn;
    private $table_name = "usuario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER TODOS LOS USUARIOS
    public function obtenerTodos() {
        $query = "SELECT u.*, r.Roles as Nombre_Rol, td.Documento as Tipo_Documento
                  FROM " . $this->table_name . " u 
                  LEFT JOIN rol r ON u.ID_Rol = r.ID_Rol 
                  LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                  ORDER BY u.ID_Usuario ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER USUARIO POR ID
    public function obtenerPorId($id) {
        $query = "SELECT u.*, r.Roles as Nombre_Rol, td.Documento as Tipo_Documento
                  FROM " . $this->table_name . " u 
                  LEFT JOIN rol r ON u.ID_Rol = r.ID_Rol 
                  LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                  WHERE u.ID_Usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // VERIFICAR SI EXISTE UN USUARIO CON EL MISMO DOCUMENTO O EMAIL
    public function existeUsuario($documento, $email, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                  WHERE N_Documento = ? OR Correo = ?";
        $params = [$documento, $email];
        
        if ($excluirId) {
            $query .= " AND ID_Usuario != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // CREAR NUEVO USUARIO
    public function crear($datos) {
        // Verificar si ya existe un usuario con el mismo documento o email
        if ($this->existeUsuario($datos['N_Documento'], $datos['Correo'])) {
            throw new Exception("Ya existe un usuario con ese documento o email");
        }

        // Hash de la contraseña
        $passwordHash = password_hash($datos['Contrasena'], PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table_name . " 
                 (Nombre_Completo, ID_Rol, ID_TD, N_Documento, Correo, Celular, Contrasena, Activo) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $datos['Nombre_Completo'],
            $datos['ID_Rol'],
            $datos['ID_TD'],
            $datos['N_Documento'],
            $datos['Correo'],
            $datos['Celular'],
            $passwordHash
        ]);
    }

    // CAMBIAR ESTADO (ACTIVAR/DESACTIVAR)
    public function cambiarEstado($id, $estado) {
        $query = "UPDATE " . $this->table_name . " 
                 SET Activo = ?
                 WHERE ID_Usuario = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$estado, $id]);
    }

    // CAMBIAR ROL
    public function cambiarRol($id, $rol) {
        $query = "UPDATE " . $this->table_name . " 
                 SET ID_Rol = ?
                 WHERE ID_Usuario = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$rol, $id]);
    }

    // OBTENER ROLES DISPONIBLES
    public function obtenerRoles() {
        $query = "SELECT * FROM rol ORDER BY ID_Rol ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER TIPOS DE DOCUMENTO
    public function obtenerTiposDocumento() {
        $query = "SELECT * FROM tipo_documento ORDER BY ID_TD ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CONTAR USUARIOS ACTIVOS
    public function contarActivos() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // CONTAR USUARIOS POR ROL
    public function contarPorRol($rol) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE ID_Rol = ? AND Activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$rol]);
        return $stmt->fetchColumn();
    }

    // BUSCAR USUARIOS
    public function buscar($termino = '', $filtroEstado = '', $filtroRol = '') {
        $query = "SELECT u.*, r.Roles as Nombre_Rol, td.Documento as Tipo_Documento
                  FROM " . $this->table_name . " u 
                  LEFT JOIN rol r ON u.ID_Rol = r.ID_Rol 
                  LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                  WHERE 1=1";
        $params = [];

        // Búsqueda por documento, nombre o email
        if (!empty($termino)) {
            $query .= " AND (u.N_Documento LIKE ? OR u.Nombre_Completo LIKE ? OR u.Correo LIKE ?)";
            $likeTerm = '%' . $termino . '%';
            $params[] = $likeTerm;
            $params[] = $likeTerm;
            $params[] = $likeTerm;
        }

        // Filtro por estado
        if ($filtroEstado !== '') {
            $query .= " AND u.Activo = ?";
            $params[] = $filtroEstado;
        }

        // Filtro por rol
        if (!empty($filtroRol)) {
            $query .= " AND u.ID_Rol = ?";
            $params[] = $filtroRol;
        }

        $query .= " ORDER BY u.ID_Usuario ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>