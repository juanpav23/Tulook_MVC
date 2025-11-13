<?php
// controllers/UsuarioController.php
require_once "models/Usuario.php";
require_once "models/Database.php";

class UsuarioController {
    private $usuario;
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->usuario = new Usuario($this->db);
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // Registro de usuario
    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [];
            $data['Nombre_Completo'] = $_POST['Nombre_Completo'] ?? '';
            $data['ID_TD'] = (int)($_POST['ID_TD'] ?? 0);
            $data['N_Documento'] = $_POST['N_Documento'] ?? '';
            $data['Correo'] = $_POST['Correo'] ?? '';
            $data['Celular'] = $_POST['Celular'] ?? '';
            $data['Contrasena'] = password_hash($_POST['Contrasena'], PASSWORD_BCRYPT);
            $data['ID_Rol'] = 3; // cliente por defecto

            if ($this->usuario->existeCorreo($data['Correo'])) {
                $_SESSION['error_registro'] = "El correo ya está registrado.";
                header("Location: " . BASE_URL . "?c=Usuario&a=login");
                exit;
            }

            if ($this->usuario->registrar($data)) {
                $_SESSION['success_registro'] = "Usuario registrado con éxito. Ahora inicia sesión.";
                header("Location: " . BASE_URL . "?c=Usuario&a=login");
                exit;
            } else {
                $_SESSION['error_registro'] = "Error al registrar usuario.";
                header("Location: " . BASE_URL . "?c=Usuario&a=login");
                exit;
            }
        }
    }

    // Login
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = $_POST['Correo'] ?? '';
            $contrasena = $_POST['Contrasena'] ?? '';
            $user = $this->usuario->login($correo, $contrasena);

            if ($user) {
                // Guardar datos del usuario en sesión
                $_SESSION['ID_Usuario'] = $user['ID_Usuario'];
                $_SESSION['usuario'] = $user['Correo'];
                $_SESSION['rol'] = $user['ID_Rol'];
                $_SESSION['Nombre_Completo'] = $user['Nombre_Completo'];

                // Obtener nombre del rol
                $stmtRol = $this->db->prepare("SELECT Roles FROM rol WHERE ID_Rol = ?");
                $stmtRol->execute([ (int)$user['ID_Rol'] ]);
                $rolName = $stmtRol->fetchColumn();
                $_SESSION['RolName'] = $rolName;

                // Redirección según el rol
                if (strtolower($rolName) === 'administrador') {
                    header("Location: " . BASE_URL . "?c=Admin&a=index");
                    exit;
                } else {
                    header("Location: " . BASE_URL);
                    exit;
                }

            } else {
                $_SESSION['error_login'] = "Correo o contraseña incorrectos";
                header("Location: " . BASE_URL . "?c=Usuario&a=login");
                exit;
            }
        } else {
            $tipo_docs = $this->usuario->getTipoDocumentos();
            include "views/usuario/login.php";
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL);
    }

    // Perfil de usuario
    public function perfil() {
        if (!isset($_SESSION['usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $correo = $_SESSION['usuario'];
        $stmt = $this->db->prepare("SELECT u.*, td.Documento 
                                    FROM usuario u
                                    INNER JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                                    WHERE u.Correo = :correo LIMIT 1");
        $stmt->bindParam(":correo", $correo, PDO::PARAM_STR);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        include "views/usuario/perfil.php";
    }
}
?>



