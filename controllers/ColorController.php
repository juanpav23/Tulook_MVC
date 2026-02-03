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

    // 📋 LISTAR COLORES CON BÚSQUEDA Y FILTROS
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $estado = $_GET['estado'] ?? 'todos';
        $enUso = $_GET['en_uso'] ?? 'todos';
        
        // Obtener colores según filtros
        $colores = $this->colorModel->filtrarColores($termino, $estado, $enUso);
        $modoBusqueda = (!empty($termino) || $estado !== 'todos' || $enUso !== 'todos');
        
        // Obtener estadísticas generales (sin filtros)
        $estadisticas = $this->colorModel->obtenerEstadisticas();
        
        // Calcular estadísticas para la vista
        $totalColores = $estadisticas['total'] ?? 0;
        $coloresActivos = $estadisticas['activos'] ?? 0;
        $coloresInactivos = $estadisticas['inactivos'] ?? 0;
        $coloresEnUso = $estadisticas['en_uso'] ?? 0;
        
        // Incluir vista con todas las variables necesarias
        require "views/admin/layout_admin.php";
    }

    // ➕ FORMULARIO NUEVO COLOR
    public function crear() {
        // No necesita datos adicionales
        require "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR NUEVO COLOR
    public function guardar() {
        try {
            $nombre = trim($_POST['N_Color'] ?? '');
            $codigoHex = trim($_POST['CodigoHex'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            // 🔍 VALIDACIONES
            if (empty($nombre)) {
                throw new Exception("❌ El nombre del color es requerido");
            }

            if (empty($codigoHex)) {
                throw new Exception("❌ El código hexadecimal es requerido");
            }

            if (strpos($codigoHex, '#') !== 0) {
                $codigoHex = '#' . $codigoHex;
            }

            if (!preg_match('/^#[0-9A-F]{6}$/i', $codigoHex)) {
                throw new Exception("El código hexadecimal es inválido. Use formato RRGGBB");
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/', $nombre)) {
                throw new Exception("❌ El nombre solo puede contener letras, espacios y guiones");
            }

            if ($this->colorModel->existeColor($nombre, $codigoHex)) {
                throw new Exception("❌ Ya existe un color con este nombre o código hexadecimal");
            }

            $resultado = $this->colorModel->crear($nombre, $codigoHex, $activo);

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

        require "views/admin/layout_admin.php";
    }

    // 💾 ACTUALIZAR COLOR
    public function actualizar() {
        try {
            $id = (int)($_POST['ID_Color'] ?? 0);
            $nombre = trim($_POST['N_Color'] ?? '');
            $codigoHex = trim($_POST['CodigoHex'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if ($id <= 0) {
                throw new Exception("❌ ID de color inválido");
            }

            if (empty($nombre)) {
                throw new Exception("❌ El nombre del color es requerido");
            }

            if (empty($codigoHex)) {
                throw new Exception("❌ El código hexadecimal es requerido");
            }

            if (strpos($codigoHex, '#') !== 0) {
                $codigoHex = '#' . $codigoHex;
            }

            if (!preg_match('/^#[0-9A-F]{6}$/i', $codigoHex)) {
                throw new Exception("El código hexadecimal es inválido. Use formato RRGGBB");
            }

            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/', $nombre)) {
                throw new Exception("❌ El nombre solo puede contener letras, espacios y guiones");
            }

            $resultado = $this->colorModel->actualizar($id, $nombre, $codigoHex, $activo);

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

    // ACTIVAR/DESACTIVAR COLOR (AJAX) - CORREGIDO
    public function cambiarEstado() {
        $id = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0); // 0 para desactivar, 1 para activar
        
        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de color inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Color&a=index");
            exit;
        }

        // NUEVA RESTRICCIÓN: Verificar si se intenta desactivar un color en uso
        if ($estado === 0) { // Solo aplica cuando se intenta DESACTIVAR
            if ($this->colorModel->estaEnUso($id)) {
                $_SESSION['mensaje'] = "⚠ No se puede desactivar: El color está en uso por productos";
                $_SESSION['mensaje_tipo'] = "warning";
                header("Location: " . BASE_URL . "?c=Color&a=index");
                exit;
            }
        }

        // Actualizar estado directamente
        $resultado = $this->colorModel->actualizarEstado($id, $estado);

        if ($resultado) {
            $_SESSION['mensaje'] = $estado ? "✅ Color activado correctamente" : "✅ Color desactivado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al cambiar el estado del color";
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Color&a=index");
        exit;
    }

    // 📊 OBTENER PRODUCTOS QUE USAN UN COLOR (AJAX)
    public function obtenerProductosPorColor() {
        header('Content-Type: application/json');
        
        try {
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID inválido']);
                exit;
            }

            $productos = $this->colorModel->obtenerProductosPorColor($id);
            
            if ($productos) {
                echo json_encode(['success' => true, 'productos' => $productos]);
            } else {
                echo json_encode(['success' => true, 'productos' => []]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>