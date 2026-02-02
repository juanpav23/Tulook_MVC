<?php
class UsuarioAdmin {
    private $conn;
    private $table_name = "usuario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // OBTENER TODOS LOS USUARIOS
    public function obtenerTodos() {
        $query = "SELECT u.*, r.Roles as Nombre_Rol, td.Documento as Tipo_Documento,
                         ud.Nombre as Admin_Nombre, ud.Apellido as Admin_Apellido
                  FROM " . $this->table_name . " u 
                  LEFT JOIN rol r ON u.ID_Rol = r.ID_Rol 
                  LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                  LEFT JOIN usuario ud ON u.ID_Admin_Desactiva = ud.ID_Usuario
                  ORDER BY u.ID_Usuario ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER USUARIO POR ID
    public function obtenerPorId($id) {
        $query = "SELECT u.*, r.Roles as Nombre_Rol, td.Documento as Tipo_Documento,
                         ud.Nombre as Admin_Nombre, ud.Apellido as Admin_Apellido
                  FROM " . $this->table_name . " u 
                  LEFT JOIN rol r ON u.ID_Rol = r.ID_Rol 
                  LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                  LEFT JOIN usuario ud ON u.ID_Admin_Desactiva = ud.ID_Usuario
                  WHERE u.ID_Usuario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // VERIFICAR SI EXISTE UN USUARIO CON EL MISMO DOCUMENTO
    public function existeDocumento($documento, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE N_Documento = ?";
        $params = [$documento];
        
        if ($excluirId) {
            $query .= " AND ID_Usuario != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // VERIFICAR SI EXISTE UN USUARIO CON EL MISMO EMAIL
    public function existeEmail($email, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Correo = ?";
        $params = [$email];
        
        if ($excluirId) {
            $query .= " AND ID_Usuario != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // VERIFICAR SI EXISTE UN USUARIO CON EL MISMO CELULAR
    public function existeCelular($celular, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Celular = ?";
        $params = [$celular];
        
        if ($excluirId) {
            $query .= " AND ID_Usuario != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // VERIFICAR SI EXISTE UN USUARIO CON DOCUMENTO, EMAIL O CELULAR
    public function existeUsuario($documento, $email, $celular, $excluirId = null) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " 
                  WHERE N_Documento = ? OR Correo = ? OR Celular = ?";
        $params = [$documento, $email, $celular];
        
        if ($excluirId) {
            $query .= " AND ID_Usuario != ?";
            $params[] = $excluirId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // OBTENER USUARIO POR DOCUMENTO
    public function obtenerPorDocumento($documento) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE N_Documento = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$documento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER USUARIO POR EMAIL
    public function obtenerPorEmail($email) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Correo = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER USUARIO POR CELULAR
    public function obtenerPorCelular($celular) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE Celular = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$celular]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CREAR NUEVO USUARIO
    public function crear($datos) {
        // Verificar si ya existe un usuario con el mismo documento, email o celular
        if ($this->existeDocumento($datos['N_Documento'])) {
            throw new Exception("documento_existente");
        }
        
        if ($this->existeEmail($datos['Correo'])) {
            throw new Exception("email_existente");
        }
        
        if ($this->existeCelular($datos['Celular'])) {
            throw new Exception("celular_existente");
        }

        // Hash de la contraseña
        $passwordHash = password_hash($datos['Password'], PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table_name . " 
                 (Nombre, Apellido, ID_Rol, ID_TD, N_Documento, Correo, Celular, Contrasena, Activo) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $datos['Nombre'],
            $datos['Apellido'],
            $datos['ID_Rol'],
            $datos['ID_TD'],
            $datos['N_Documento'],
            $datos['Correo'],
            $datos['Celular'],
            $passwordHash
        ]);
    }

    // CAMBIAR ESTADO (ACTIVAR/DESACTIVAR) CON REGISTRO DE MOTIVO
    public function cambiarEstado($id, $estado, $motivo = '', $adminId = null) {
        // Obtener usuario actual
        $usuario = $this->obtenerPorId($id);
        if (!$usuario) {
            return false;
        }
        
        // Si no hay cambio real, retornar false
        if ($usuario['Activo'] == $estado) {
            return false;
        }
        
        // Preparar datos para actualización
        $datosUpdate = [
            'Activo' => $estado
        ];
        
        // Si es desactivación, guardar motivo, fecha y admin
        if ($estado == 0) {
            $datosUpdate['Motivo_Desactivacion'] = $motivo;
            $datosUpdate['Fecha_Desactivacion'] = date('Y-m-d H:i:s');
            $datosUpdate['ID_Admin_Desactiva'] = $adminId;
        } else {
            // Si es activación, limpiar motivo, fecha y admin
            $datosUpdate['Motivo_Desactivacion'] = NULL;
            $datosUpdate['Fecha_Desactivacion'] = NULL;
            $datosUpdate['ID_Admin_Desactiva'] = NULL;
        }
        
        // Construir query dinámica
        $setParts = [];
        $params = [];
        
        foreach ($datosUpdate as $campo => $valor) {
            $setParts[] = "`$campo` = ?";
            $params[] = $valor;
        }
        
        $params[] = $id; // Para el WHERE
        
        $query = "UPDATE " . $this->table_name . " 
                  SET " . implode(', ', $setParts) . "
                  WHERE ID_Usuario = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
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

    // CONTAR USUARIOS INACTIVOS
    public function contarInactivos() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Activo = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // CONTAR USUARIOS POR ROL
    public function contarPorRol($rol) {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE ID_Rol = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$rol]);
        return $stmt->fetchColumn();
    }

    // CONTAR EDITORES (ROL 2) - NUEVO MÉTODO
    public function contarEditores() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE ID_Rol = 2";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // CONTAR CLIENTES (ROL 3) - NUEVO MÉTODO
    public function contarClientes() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE ID_Rol = 3";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // CONTAR CLIENTES ACTIVOS
    public function contarClientesActivos() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE ID_Rol = 3 AND Activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // CONTAR CLIENTES INACTIVOS
    public function contarClientesInactivos() {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE ID_Rol = 3 AND Activo = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // BUSCAR USUARIOS
    public function buscar($termino = '', $filtroEstado = '', $filtroRol = '') {
        $query = "SELECT u.*, r.Roles as Nombre_Rol, td.Documento as Tipo_Documento,
                         ud.Nombre as Admin_Nombre, ud.Apellido as Admin_Apellido
                  FROM " . $this->table_name . " u 
                  LEFT JOIN rol r ON u.ID_Rol = r.ID_Rol 
                  LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                  LEFT JOIN usuario ud ON u.ID_Admin_Desactiva = ud.ID_Usuario
                  WHERE 1=1";
        $params = [];

        // Búsqueda por documento, nombre, apellido o email
        if (!empty($termino)) {
            $query .= " AND (u.N_Documento LIKE ? OR u.Nombre LIKE ? OR u.Apellido LIKE ? OR u.Correo LIKE ?)";
            $likeTerm = '%' . $termino . '%';
            $params[] = $likeTerm;
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