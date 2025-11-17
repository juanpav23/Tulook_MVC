<?php
require_once "models/Database.php";
require_once "models/UsuarioAdmin.php";

class UsuarioAdminController {
    private $db;
    private $usuarioModel;

    public function __construct() {
        $dbObj = new Database();
        $this->db = $dbObj->getConnection();
        $this->usuarioModel = new UsuarioAdmin($this->db);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->ensureAdmin();
    }

    private function ensureAdmin() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) { // Solo rol 1
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }
    }

    // 📋 LISTAR USUARIOS CON BÚSQUEDA
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $rol = $_GET['rol'] ?? '';
        
        // Validar y convertir estado
        if ($estado === 'activo') {
            $filtroEstado = 1;
        } elseif ($estado === 'inactivo') {
            $filtroEstado = 0;
        } else {
            $filtroEstado = '';
        }

        if (!empty($termino) || $filtroEstado !== '' || !empty($rol)) {
            $usuarios = $this->usuarioModel->buscar($termino, $filtroEstado, $rol);
            $modoBusqueda = true;
        } else {
            $usuarios = $this->usuarioModel->obtenerTodos();
            $modoBusqueda = false;
        }

        $roles = $this->usuarioModel->obtenerRoles();
        include "views/admin/layout_admin.php";
    }

    // ➕ FORMULARIO NUEVO USUARIO
    public function crear() {
        $roles = $this->usuarioModel->obtenerRoles();
        $tiposDocumento = $this->usuarioModel->obtenerTiposDocumento();
        include "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR NUEVO USUARIO
    public function guardar() {
        try {
            $datos = [
                'N_Documento' => trim($_POST['N_Documento'] ?? ''),
                'Nombre_Completo' => trim($_POST['Nombre_Completo'] ?? ''),
                'Correo' => trim($_POST['Correo'] ?? ''),
                'Celular' => trim($_POST['Celular'] ?? ''),
                'Contrasena' => $_POST['Contrasena'] ?? '',
                'ID_Rol' => (int)($_POST['ID_Rol'] ?? 2),
                'ID_TD' => (int)($_POST['ID_TD'] ?? 1) // Cédula de ciudadanía por defecto
            ];

            // Validaciones básicas
            if (empty($datos['N_Documento']) || empty($datos['Nombre_Completo']) || 
                empty($datos['Correo']) || empty($datos['Contrasena']) || empty($datos['Celular'])) {
                $_SESSION['mensaje'] = "❌ Todos los campos son obligatorios";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=crear");
                exit;
            }

            $resultado = $this->usuarioModel->crear($datos);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Usuario creado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al crear el usuario";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=crear");
            exit;
        }
    }

    // 🔄 ACTIVAR/DESACTIVAR USUARIO
    public function cambiarEstado() {
        $id = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de usuario inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // No permitir desactivarse a sí mismo
        if ($id == $_SESSION['ID_Usuario']) {
            $_SESSION['mensaje'] = "❌ No puedes desactivar tu propio usuario";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // Si es super admin (ID 1), puede desactivar a cualquiera
        // Si no es super admin, no puede desactivar a otros admins
        if ($_SESSION['ID_Usuario'] != 1) {
            $usuario = $this->usuarioModel->obtenerPorId($id);
            if ($usuario && $usuario['ID_Rol'] == 1 && $estado == 0) {
                $_SESSION['mensaje'] = "❌ Solo el super administrador puede desactivar otros administradores";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
        }

        $resultado = $this->usuarioModel->cambiarEstado($id, $estado);

        if ($resultado) {
            $_SESSION['mensaje'] = $estado ? "✅ Usuario activado correctamente" : "✅ Usuario desactivado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al cambiar el estado del usuario";
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
        exit;
    }

    // 👑 CAMBIAR ROL DE USUARIO
    public function cambiarRol() {
        $id = (int)($_POST['ID_Usuario'] ?? 0);
        $rol = (int)($_POST['ID_Rol'] ?? 2);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de usuario inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // No permitir cambiar el rol de sí mismo
        if ($id == $_SESSION['ID_Usuario']) {
            $_SESSION['mensaje'] = "❌ No puedes cambiar tu propio rol";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
            exit;
        }

        // Si es super admin (ID 1), puede cambiar cualquier rol
        // Si no es super admin, no puede quitar rol de admin a otros admins
        if ($_SESSION['ID_Usuario'] != 1) {
            $usuario = $this->usuarioModel->obtenerPorId($id);
            if ($usuario && $usuario['ID_Rol'] == 1 && $rol != 1) {
                $_SESSION['mensaje'] = "❌ Solo el super administrador puede quitar el rol de administrador";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
                exit;
            }
        }

        $resultado = $this->usuarioModel->cambiarRol($id, $rol);

        if ($resultado) {
            $_SESSION['mensaje'] = "✅ Rol de usuario actualizado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al cambiar el rol del usuario";
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=UsuarioAdmin&a=index");
        exit;
    }
}
?>