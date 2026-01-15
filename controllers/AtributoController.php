<?php
require_once "models/Database.php";
require_once "models/Atributo.php";

class AtributoController {
    private $db;
    private $atributoModel;

    public function __construct($db) {
        $this->db = $db;
        $this->atributoModel = new Atributo($this->db);

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

    // 📋 LISTAR ATRIBUTOS CON BÚSQUEDA Y FILTROS
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $tipo = $_GET['tipo'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $enUso = $_GET['en_uso'] ?? '';
        
        if ($estado === 'activo') {
            $filtroEstado = 1;
        } elseif ($estado === 'inactivo') {
            $filtroEstado = 0;
        } else {
            $filtroEstado = '';
        }

        if (!empty($termino) || !empty($tipo) || $filtroEstado !== '' || !empty($enUso)) {
            $atributos = $this->atributoModel->buscar($termino, $tipo, $filtroEstado, $enUso);
            $modoBusqueda = true;
        } else {
            $atributos = $this->atributoModel->obtenerTodos();
            $modoBusqueda = false;
        }

        // Para cada atributo, verificar si está en uso
        foreach ($atributos as &$attr) {
            $attr['en_uso'] = $this->atributoModel->estaEnUso($attr['ID_AtributoValor']);
        }

        $tipos = $this->atributoModel->obtenerTipos();
        $estadisticas = $this->atributoModel->obtenerEstadisticas();
        
        require "views/admin/layout_admin.php";
    }

    // ➕ FORMULARIO NUEVO ATRIBUTO
    public function crear() {
        $tipos = $this->atributoModel->obtenerTipos();
        require "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR NUEVO ATRIBUTO
    public function guardar() {
        try {
            $tipoId = (int)($_POST['ID_TipoAtributo'] ?? 0);
            $valor = trim($_POST['Valor'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if ($tipoId <= 0) {
                $_SESSION['mensaje'] = "❌ Debe seleccionar un tipo de atributo";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Atributo&a=crear");
                exit;
            }

            if (empty($valor)) {
                $_SESSION['mensaje'] = "❌ El valor del atributo es requerido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Atributo&a=crear");
                exit;
            }

            $resultado = $this->atributoModel->crear($tipoId, $valor, $activo);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Atributo creado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al crear el atributo";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Atributo&a=index");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Atributo&a=crear");
            exit;
        }
    }

    // ✏ FORMULARIO EDITAR ATRIBUTO
    public function editar() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header("Location: " . BASE_URL . "?c=Atributo&a=index");
            exit;
        }

        $atributo = $this->atributoModel->obtenerPorId($id);
        
        if (!$atributo) {
            $_SESSION['mensaje'] = "❌ Atributo no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Atributo&a=index");
            exit;
        }

        $esUnica = ($id == 16 || strtolower($atributo['Valor']) === 'única');
        $tipos = $this->atributoModel->obtenerTipos();
        require "views/admin/layout_admin.php";
    }

    // 💾 ACTUALIZAR ATRIBUTO
    public function actualizar() {
        try {
            $id = (int)($_POST['ID_AtributoValor'] ?? 0);
            $tipoId = (int)($_POST['ID_TipoAtributo'] ?? 0);
            $valor = trim($_POST['Valor'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de atributo inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Atributo&a=index");
                exit;
            }

            if ($tipoId <= 0) {
                $_SESSION['mensaje'] = "❌ Debe seleccionar un tipo de atributo";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Atributo&a=editar&id=" . $id);
                exit;
            }

            if (empty($valor)) {
                $_SESSION['mensaje'] = "❌ El valor del atributo es requerido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Atributo&a=editar&id=" . $id);
                exit;
            }

            $resultado = $this->atributoModel->actualizar($id, $tipoId, $valor, $activo);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Atributo actualizado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al actualizar el atributo";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Atributo&a=index");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Atributo&a=editar&id=" . $id);
            exit;
        }
    }

    // 🔄 ACTIVAR/DESACTIVAR ATRIBUTO
    public function cambiarEstado() {
        $id = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de atributo inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Atributo&a=index");
            exit;
        }

        try {
            $resultado = $this->atributoModel->cambiarEstado($id, $estado);

            if ($resultado) {
                $_SESSION['mensaje'] = $estado ? "✅ Atributo activado correctamente" : "✅ Atributo desactivado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al cambiar el estado del atributo";
                $_SESSION['mensaje_tipo'] = "danger";
            }

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Atributo&a=index");
        exit;
    }

    // 📊 VER DETALLES DEL ATRIBUTO
    public function detalle() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header("Location: " . BASE_URL . "?c=Atributo&a=index");
            exit;
        }

        $atributo = $this->atributoModel->obtenerPorId($id);
        
        if (!$atributo) {
            $_SESSION['mensaje'] = "❌ Atributo no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Atributo&a=index");
            exit;
        }

        $productos = $this->atributoModel->obtenerProductosPorAtributo($id);
        require "views/admin/layout_admin.php";
    }

    // 🗑️ ELIMINAR ATRIBUTO
    public function eliminar() {
        try {
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de atributo inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Atributo&a=index");
                exit;
            }
            
            $atributo = $this->atributoModel->obtenerPorId($id);
            $nombreAtributo = $atributo ? $atributo['Valor'] : '';

            $resultado = $this->atributoModel->eliminar($id);
            
            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Atributo '{$nombreAtributo}' eliminado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al eliminar el atributo";
                $_SESSION['mensaje_tipo'] = "danger";
            }
            
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }
        
        header("Location: " . BASE_URL . "?c=Atributo&a=index");
        exit;
    }
}
?>