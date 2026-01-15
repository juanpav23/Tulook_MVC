<?php
// models/Usuario.php
class Usuario {
    private $conn;
    private $table_name = "usuario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ===========================
    // LOGIN (actualizado para verificar activo)
    // ===========================
    public function login($correo, $contrasena) {
        $sql = "SELECT * FROM {$this->table_name} WHERE Correo = ? AND activo = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$correo]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($contrasena, $user['Contrasena'])) {
            return $user; // devuelve los datos del usuario
        }
        return false;
    }

    // ===========================
    // VERIFICAR SI EXISTE CORREO
    // ===========================
    public function existeCorreo($correo) {
        $sql = "SELECT ID_Usuario FROM {$this->table_name} WHERE Correo = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->rowCount() > 0;
    }

    // ===========================
    // VERIFICAR SI EXISTE DOCUMENTO
    // ===========================
    public function existeDocumento($documento) {
        $sql = "SELECT ID_Usuario FROM {$this->table_name} WHERE N_Documento = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$documento]);
        return $stmt->rowCount() > 0;
    }

    // ===========================
    // VERIFICAR SI EXISTE CELULAR
    // ===========================
    public function existeCelular($celular) {
        $sql = "SELECT ID_Usuario FROM {$this->table_name} WHERE Celular = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$celular]);
        return $stmt->rowCount() > 0;
    }

    // ===========================
    // REGISTRAR NUEVO USUARIO (actualizado)
    // ===========================
    public function registrar($data) {
        $sql = "INSERT INTO {$this->table_name}
                (Nombre, Apellido, ID_TD, N_Documento, Correo, Celular, Contrasena, ID_Rol, activo)
                VALUES (:Nombre, :Apellido, :ID_TD, :N_Documento, :Correo, :Celular, :Contrasena, :ID_Rol, 1)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    // ===========================
    // OBTENER TIPOS DE DOCUMENTO
    // ===========================
    public function getTipoDocumentos() {
        $stmt = $this->conn->query("SELECT * FROM tipo_documento ORDER BY Documento ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===========================
    // CONTAR USUARIOS (opcional)
    // ===========================
    public function contarUsuarios() {
        $stmt = $this->conn->query("SELECT COUNT(*) AS total FROM {$this->table_name}");
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    public function actualizarContrasena($id_usuario, $nueva_contrasena_hash) {
        try {
            $sql = "UPDATE usuario SET Contrasena = ? WHERE ID_Usuario = ?";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$nueva_contrasena_hash, $id_usuario]);
        } catch (PDOException $e) {
            error_log("Error al actualizar contraseña: " . $e->getMessage());
            return false;
        }
    }
}