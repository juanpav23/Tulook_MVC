<?php
require_once "models/Database.php";
require_once "models/Precio.php";

class PrecioController {
    private $db;
    private $precioModel;

    public function __construct() {
        $dbObj = new Database();
        $this->db = $dbObj->getConnection();
        $this->precioModel = new Precio($this->db);

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

        if (!$rol || strtolower($rol) !== 'administrador') {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    // 📋 LISTAR PRECIOS CON BÚSQUEDA
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        // Validar y convertir estado
        if ($estado === 'activo') {
            $filtroEstado = 1;
        } elseif ($estado === 'inactivo') {
            $filtroEstado = 0;
        } else {
            $filtroEstado = '';
        }

        if (!empty($termino) || $filtroEstado !== '') {
            $precios = $this->precioModel->buscar($termino, $filtroEstado);
            $modoBusqueda = true;
        } else {
            $precios = $this->precioModel->obtenerTodos();
            $modoBusqueda = false;
        }

        include "views/admin/layout_admin.php";
    }

    // ➕ FORMULARIO NUEVO PRECIO
    public function crear() {
        include "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR NUEVO PRECIO
    public function guardar() {
        try {
            $valor = floatval($_POST['Valor'] ?? 0);
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if ($valor <= 0) {
                $_SESSION['mensaje'] = "❌ El valor debe ser mayor a 0";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Precio&a=crear");
                exit;
            }

            $resultado = $this->precioModel->crear($valor, $activo);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Precio creado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al crear el precio";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=crear");
            exit;
        }
    }

    // ✏ FORMULARIO EDITAR PRECIO
    public function editar() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;
        }

        $precio = $this->precioModel->obtenerPorId($id);
        
        if (!$precio) {
            $_SESSION['mensaje'] = "❌ Precio no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;
        }

        include "views/admin/layout_admin.php";
    }

    // 💾 ACTUALIZAR PRECIO
    public function actualizar() {
        try {
            $id = (int)($_POST['ID_precio'] ?? 0);
            $valor = floatval($_POST['Valor'] ?? 0);
            $activo = isset($_POST['Activo']) ? 1 : 0;

            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de precio inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Precio&a=index");
                exit;
            }

            if ($valor <= 0) {
                $_SESSION['mensaje'] = "❌ El valor debe ser mayor a 0";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Precio&a=editar&id=" . $id);
                exit;
            }

            $resultado = $this->precioModel->actualizar($id, $valor, $activo);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Precio actualizado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al actualizar el precio";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ Error: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=editar&id=" . $id);
            exit;
        }
    }

    // 🔄 ACTIVAR/DESACTIVAR PRECIO
    public function cambiarEstado() {
        $id = (int)($_GET['id'] ?? 0);
        $estado = (int)($_GET['estado'] ?? 0);

        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de precio inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Precio&a=index");
            exit;
        }

        $resultado = $this->precioModel->cambiarEstado($id, $estado);

        if ($resultado) {
            $_SESSION['mensaje'] = $estado ? "✅ Precio activado correctamente" : "✅ Precio desactivado correctamente";
            $_SESSION['mensaje_tipo'] = "success";
        } else {
            $_SESSION['mensaje'] = "❌ Error al cambiar el estado del precio";
            $_SESSION['mensaje_tipo'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Precio&a=index");
        exit;
    }

    // 🗑 LIMPIAR PRECIOS DUPLICADOS
    public function limpiarDuplicados() {
        try {
            $resultados = $this->precioModel->limpiarDuplicados();
            
            $_SESSION['mensaje'] = "✅ Limpieza completada: " . 
                                 $resultados['eliminados'] . " precios eliminados, " . 
                                 $resultados['migrados'] . " productos migrados";
            $_SESSION['mensaje_tipo'] = "success";
            
            if (!empty($resultados['errores'])) {
                $_SESSION['mensaje_errores'] = $resultados['errores'];
            }
            
        } catch (Exception $e) {
            $_SESSION['mensaje'] = "❌ Error durante la limpieza: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
        }
        
        header("Location: " . BASE_URL . "?c=Precio&a=index");
        exit;
    }

    // 📊 VER DUPLICADOS (vista de diagnóstico)
    public function duplicados() {
        $duplicados = $this->precioModel->obtenerDuplicados();
        include "views/admin/layout_admin.php";
    }
}
?>