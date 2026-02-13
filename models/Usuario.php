<?php
// models/Usuario.php
class Usuario {
    private $conn;
    private $table_name = "usuario";

    public function __construct($db) {
        $this->conn = $db;
    }

    // LOGIN (actualizado para verificar activo)
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

    // VERIFICAR SI EXISTE CORREO
    public function existeCorreo($correo) {
        $sql = "SELECT ID_Usuario FROM {$this->table_name} WHERE Correo = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->rowCount() > 0;
    }

    // VERIFICAR SI EXISTE DOCUMENTO
    public function existeDocumento($documento) {
        $sql = "SELECT ID_Usuario FROM {$this->table_name} WHERE N_Documento = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$documento]);
        return $stmt->rowCount() > 0;
    }

    // VERIFICAR SI EXISTE CELULAR
    public function existeCelular($celular) {
        $sql = "SELECT ID_Usuario FROM {$this->table_name} WHERE Celular = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$celular]);
        return $stmt->rowCount() > 0;
    }

    // REGISTRAR NUEVO USUARIO (actualizado)
    public function registrar($data) {
        $sql = "INSERT INTO {$this->table_name}
                (Nombre, Apellido, ID_TD, N_Documento, Correo, Celular, Contrasena, ID_Rol, activo)
                VALUES (:Nombre, :Apellido, :ID_TD, :N_Documento, :Correo, :Celular, :Contrasena, :ID_Rol, 1)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }


    // REGISTRAR CON TÉRMINOS Y CONDICIONES
    public function registrarConTerminos($data, $ip_address = null) {
        try {
            $sql = "INSERT INTO {$this->table_name}
                    (Nombre, Apellido, ID_TD, N_Documento, Correo, Celular, Contrasena, 
                     ID_Rol, activo, Acepto_Terminos, Fecha_Aceptacion_Terminos, IP_Aceptacion_Terminos)
                    VALUES (:Nombre, :Apellido, :ID_TD, :N_Documento, :Correo, :Celular, :Contrasena, 
                            :ID_Rol, 1, 1, NOW(), :IP_Aceptacion_Terminos)";
            
            $stmt = $this->conn->prepare($sql);
            
            $data['IP_Aceptacion_Terminos'] = $ip_address;
            
            return $stmt->execute($data);
        } catch (PDOException $e) {
            error_log("Error en registrarConTerminos: " . $e->getMessage());
            return false;
        }
    }

    // VERIFICAR SI EL USUARIO ACEPTÓ TÉRMINOS
    public function verificarAceptacionTerminos($id_usuario) {
        $sql = "SELECT Acepto_Terminos, Fecha_Aceptacion_Terminos, IP_Aceptacion_Terminos 
                FROM {$this->table_name} 
                WHERE ID_Usuario = ? 
                LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ACTUALIZAR ACEPTACIÓN DE TÉRMINOS (para usuarios existentes)
    public function actualizarAceptacionTerminos($id_usuario, $ip_address = null) {
        $sql = "UPDATE {$this->table_name} 
                SET Acepto_Terminos = 1, 
                    Fecha_Aceptacion_Terminos = NOW(), 
                    IP_Aceptacion_Terminos = ? 
                WHERE ID_Usuario = ?";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$ip_address, $id_usuario]);
    }

    // CONTAR USUARIOS QUE NO HAN ACEPTADO TÉRMINOS
    public function contarUsuariosSinTerminos() {
        $sql = "SELECT COUNT(*) AS total FROM {$this->table_name} WHERE Acepto_Terminos = 0";
        $stmt = $this->conn->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    }

    // OBTENER ESTADÍSTICAS DE ACEPTACIÓN DE TÉRMINOS
    public function getEstadisticasTerminos() {
        $stats = [];
        
        // Total de usuarios que han aceptado
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM {$this->table_name} WHERE Acepto_Terminos = 1");
        $stats['aceptaron'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Total de usuarios que NO han aceptado
        $stmt = $this->conn->query("SELECT COUNT(*) as total FROM {$this->table_name} WHERE Acepto_Terminos = 0");
        $stats['no_aceptaron'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        
        // Últimas 10 aceptaciones
        $stmt = $this->conn->query("
            SELECT ID_Usuario, Correo, Fecha_Aceptacion_Terminos, IP_Aceptacion_Terminos 
            FROM {$this->table_name} 
            WHERE Acepto_Terminos = 1 AND Fecha_Aceptacion_Terminos IS NOT NULL
            ORDER BY Fecha_Aceptacion_Terminos DESC 
            LIMIT 10
        ");
        $stats['ultimas_aceptaciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $stats;
    }

    // OBTENER TIPOS DE DOCUMENTO
    public function getTipoDocumentos() {
        $stmt = $this->conn->query("SELECT * FROM tipo_documento ORDER BY Documento ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CONTAR USUARIOS (opcional)
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

    public function obtenerDatosCompletos($correo) {
        $sql = "SELECT u.*, td.Documento as Tipo_Documento,
                    admin.Nombre as Admin_Nombre, admin.Apellido as Admin_Apellido
                FROM usuario u
                LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                LEFT JOIN usuario admin ON u.ID_Admin_Desactiva = admin.ID_Usuario
                WHERE u.Correo = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}