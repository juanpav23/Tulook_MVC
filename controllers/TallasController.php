<?php
// controllers/TallasController.php
require_once "models/Database.php";
require_once "models/Talla.php";

class TallasController {
    private $db;
    private $tallaModel;

    public function __construct($db) {
        // Iniciar sesión si no está iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = $db;
        $this->tallaModel = new Talla($db);
        
        // Verificar permisos de administrador
        $this->verificarPermisos();
    }

    private function verificarPermisos() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['ID_Usuario']) && !isset($_SESSION['id_usuario'])) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            header('Location: ' . BASE_URL . '?c=Usuario&a=login');
            exit();
        }
        
        $rol = null;
        
        if (isset($_SESSION['ID_Rol'])) {
            $rol = $_SESSION['ID_Rol'];
        } elseif (isset($_SESSION['rol'])) {
            $rol = $_SESSION['rol'];
        } elseif (isset($_SESSION['id_rol'])) {
            $rol = $_SESSION['id_rol'];
        }
        
        // Permitir rol 1 (Administrador) y rol 2 (Editor)
        if ($rol != 1 && $rol != 2) {
            $_SESSION['mensaje'] = 'No tienes permisos de administrador para acceder a esta sección';
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: ' . BASE_URL);
            exit();
        }
    }

    public function index() {
        $buscar = $_GET['buscar'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        $tallas = $this->tallaModel->obtenerTodas($buscar, $estado);
        $modoBusqueda = !empty($buscar) || !empty($estado);
        
        include 'views/admin/layout_admin.php';
    }

    public function crear() {
        include 'views/admin/layout_admin.php';
    }

    public function editar() {
        $id = $_GET['id'] ?? 0;
        
        // Prevenir edición de talla indefinida (ID 1)
        if ($id == 1) {
            $_SESSION['mensaje'] = 'No se puede editar la talla "Indefinida"';
            $_SESSION['mensaje_tipo'] = 'warning';
            header('Location: ' . BASE_URL . '?c=Tallas&a=index');
            exit();
        }
        
        $talla = $this->tallaModel->obtenerPorId($id);
        
        if (!$talla) {
            $_SESSION['mensaje'] = 'Talla no encontrada';
            $_SESSION['mensaje_tipo'] = 'danger';
            header('Location: ' . BASE_URL . '?c=Tallas&a=index');
            exit();
        }
        
        include 'views/admin/layout_admin.php';
    }

    public function guardar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['N_Talla'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if (empty($nombre)) {
                $_SESSION['mensaje'] = 'El nombre de la talla es obligatorio';
                $_SESSION['mensaje_tipo'] = 'danger';
                header('Location: ' . BASE_URL . '?c=Tallas&a=crear');
                exit();
            }

            if ($this->tallaModel->existeTalla($nombre)) {
                $_SESSION['mensaje'] = 'Ya existe una talla con ese nombre';
                $_SESSION['mensaje_tipo'] = 'danger';
                header('Location: ' . BASE_URL . '?c=Tallas&a=crear');
                exit();
            }

            if ($this->tallaModel->crear($nombre, $activo)) {
                $_SESSION['mensaje'] = 'Talla creada exitosamente';
                $_SESSION['mensaje_tipo'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al crear la talla';
                $_SESSION['mensaje_tipo'] = 'danger';
            }
            
            header('Location: ' . BASE_URL . '?c=Tallas&a=index');
            exit();
        }
    }

    public function actualizar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['ID_Talla'] ?? 0;
            
            // Prevenir actualización de talla indefinida (ID 1)
            if ($id == 1) {
                $_SESSION['mensaje'] = 'No se puede actualizar la talla "Indefinida"';
                $_SESSION['mensaje_tipo'] = 'warning';
                header('Location: ' . BASE_URL . '?c=Tallas&a=index');
                exit();
            }
            
            $nombre = trim($_POST['N_Talla'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if (empty($nombre)) {
                $_SESSION['mensaje'] = 'El nombre de la talla es obligatorio';
                $_SESSION['mensaje_tipo'] = 'danger';
                header('Location: ' . BASE_URL . '?c=Tallas&a=editar&id=' . $id);
                exit();
            }

            $tallaExistente = $this->tallaModel->obtenerPorNombre($nombre);
            if ($tallaExistente && $tallaExistente['ID_Talla'] != $id) {
                $_SESSION['mensaje'] = 'Ya existe otra talla con ese nombre';
                $_SESSION['mensaje_tipo'] = 'danger';
                header('Location: ' . BASE_URL . '?c=Tallas&a=editar&id=' . $id);
                exit();
            }

            if ($this->tallaModel->actualizar($id, $nombre, $activo)) {
                $_SESSION['mensaje'] = 'Talla actualizada exitosamente';
                $_SESSION['mensaje_tipo'] = 'success';
            } else {
                $_SESSION['mensaje'] = 'Error al actualizar la talla';
                $_SESSION['mensaje_tipo'] = 'danger';
            }
            
            header('Location: ' . BASE_URL . '?c=Tallas&a=index');
            exit();
        }
    }

    public function cambiarEstado() {
        $id = $_GET['id'] ?? 0;
        $estado = $_GET['estado'] ?? 0;

        // Prevenir cambio de estado de talla indefinida (ID 1)
        if ($id == 1) {
            $_SESSION['mensaje'] = 'No se puede cambiar el estado de la talla "Indefinida"';
            $_SESSION['mensaje_tipo'] = 'warning';
            header('Location: ' . BASE_URL . '?c=Tallas&a=index');
            exit();
        }

        if ($this->tallaModel->cambiarEstado($id, $estado)) {
            $_SESSION['mensaje'] = 'Estado de la talla actualizado exitosamente';
            $_SESSION['mensaje_tipo'] = 'success';
        } else {
            $_SESSION['mensaje'] = 'Error al cambiar el estado de la talla';
            $_SESSION['mensaje_tipo'] = 'danger';
        }
        
        header('Location: ' . BASE_URL . '?c=Tallas&a=index');
        exit();
    }
}
?>