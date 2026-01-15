<?php
require_once "models/Database.php";
require_once "models/Color.php";

class ColorController {
    private $db;
    private $colorModel;

    public function __construct($db) {
        $this->db = $db;
        $this->colorModel = new Color($this->db);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->ensureAdmin();
    }

    private function ensureAdmin() {
        if (!isset($_SESSION['rol'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $stmt = $this->db->prepare("SELECT Roles FROM rol WHERE ID_Rol = ?");
        $stmt->execute([(int)$_SESSION['rol']]);
        $rol = $stmt->fetchColumn();

        if (!$rol || (strtolower($rol) !== 'administrador' && strtolower($rol) !== 'editor')) {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    // 📋 LISTAR COLORES CON BÚSQUEDA
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        
        if (!empty($termino)) {
            $colores = $this->colorModel->buscar($termino);
            $modoBusqueda = true;
        } else {
            $colores = $this->colorModel->obtenerTodos();
            $modoBusqueda = false;
        }

        $estadisticas = $this->colorModel->obtenerEstadisticas();
        
        // CAMBIO AQUÍ: Usar layout_admin.php en lugar de la vista directa
        require "views/admin/layout_admin.php";
    }

    // ➕ FORMULARIO NUEVO COLOR
    public function crear() {
        // CAMBIO AQUÍ: Usar layout_admin.php
        require "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR NUEVO COLOR
    public function guardar() {
        try {
            $nombre = trim($_POST['N_Color'] ?? '');
            $codigoHex = trim($_POST['CodigoHex'] ?? '');

            // 🔍 VALIDACIONES
            if (empty($nombre)) {
                throw new Exception("❌ El nombre del color es requerido");
            }

            // ========== CORRECCIÓN AQUÍ ==========
            // Asegurar que el código HEX tenga el formato correcto
            if (empty($codigoHex)) {
                throw new Exception("❌ El código hexadecimal es requerido");
            }

            // Si no empieza con #, agregarlo
            if (strpos($codigoHex, '#') !== 0) {
                $codigoHex = '#' . $codigoHex;
            }

            // Validar formato (ahora con #)
            if (!preg_match('/^#[0-9A-F]{6}$/i', $codigoHex)) {
                throw new Exception("❌ El código hexadecimal es inválido. Use formato #RRGGBB o RRGGBB");
            }
            // ========== FIN CORRECCIÓN ==========

            // Validar que no contenga caracteres inválidos
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/', $nombre)) {
                throw new Exception("❌ El nombre solo puede contener letras, espacios y guiones");
            }

            // Verificar si ya existe
            if ($this->colorModel->existeColor($nombre, $codigoHex)) {
                throw new Exception("❌ Ya existe un color con este nombre o código hexadecimal");
            }

            $resultado = $this->colorModel->crear($nombre, $codigoHex);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Color creado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al crear el color";
                $_SESSION['mensaje_tipo'] = "danger";
            }

        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Color&a=index");
        exit;
    }

    // ✏ FORMULARIO EDITAR COLOR
    public function editar() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header("Location: " . BASE_URL . "?c=Color&a=index");
            exit;
        }

        $color = $this->colorModel->obtenerPorId($id);
        
        if (!$color) {
            $_SESSION['mensaje'] = "❌ Color no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Color&a=index");
            exit;
        }

        // CAMBIO AQUÍ: Usar layout_admin.php
        require "views/admin/layout_admin.php";
    }

    // 💾 ACTUALIZAR COLOR
    public function actualizar() {
        try {
            $id = (int)($_POST['ID_Color'] ?? 0);
            $nombre = trim($_POST['N_Color'] ?? '');
            $codigoHex = trim($_POST['CodigoHex'] ?? '');

            if ($id <= 0) {
                throw new Exception("❌ ID de color inválido");
            }

            // 🔍 VALIDACIONES
            if (empty($nombre)) {
                throw new Exception("❌ El nombre del color es requerido");
            }

            // ========== CORRECCIÓN AQUÍ ==========
            // Asegurar que el código HEX tenga el formato correcto
            if (empty($codigoHex)) {
                throw new Exception("❌ El código hexadecimal es requerido");
            }

            // Si no empieza con #, agregarlo
            if (strpos($codigoHex, '#') !== 0) {
                $codigoHex = '#' . $codigoHex;
            }

            // Validar formato (ahora con #)
            if (!preg_match('/^#[0-9A-F]{6}$/i', $codigoHex)) {
                throw new Exception("❌ El código hexadecimal es inválido. Use formato #RRGGBB o RRGGBB");
            }
            // ========== FIN CORRECCIÓN ==========

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/', $nombre)) {
                throw new Exception("❌ El nombre solo puede contener letras, espacios y guiones");
            }

            $resultado = $this->colorModel->actualizar($id, $nombre, $codigoHex);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Color actualizado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al actualizar el color";
                $_SESSION['mensaje_tipo'] = "danger";
            }

        } catch (Exception $e) {
            $_SESSION['mensaje'] = $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Color&a=index");
        exit;
    }

    // 🗑️ ELIMINAR COLOR
    public function eliminar() {
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de color inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Color&a=index");
            exit;
        }

        // Verificar si el color está en uso
        if ($this->colorModel->estaEnUso($id)) {
            $_SESSION['mensaje'] = "⚠ No se puede eliminar: El color está en uso por productos";
            $_SESSION['mensaje_tipo'] = "warning";
            header("Location: " . BASE_URL . "?c=Color&a=index");
            exit;
        }

        $resultado = $this->colorModel->eliminar($id);

        if ($resultado) {
            $_SESSION['mensaje'] = "✅ Color eliminado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al eliminar el color";
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Color&a=index");
        exit;
    }

    // 📊 OBTENER COLORES (AJAX PARA FORMULARIOS)
    public function obtenerColores() {
        $colores = $this->colorModel->obtenerTodos();
        header('Content-Type: application/json');
        echo json_encode($colores);
    }
}
?>