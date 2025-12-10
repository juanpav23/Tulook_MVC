<?php
require_once "models/Database.php";
require_once "models/Pedido.php";
require_once "models/Usuario.php";

class PedidoController {
    private $db;
    private $pedidoModel;
    private $usuarioModel;

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            $dbObj = new Database();
            $this->db = $dbObj->getConnection();
        }
        $this->pedidoModel = new Pedido($this->db);
        $this->usuarioModel = new Usuario($this->db);

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

        // Permitir rol 1 (Administrador) y rol 2 (Editor)
        if (!$rol || (strtolower($rol) !== 'administrador' && strtolower($rol) !== 'editor')) {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    // Helper para mostrar badges de estado
    private function getEstadoBadge($estado) {
        $badgeClass = match($estado) {
            'Emitido' => 'bg-secondary',
            'Confirmado' => 'bg-primary',
            'Preparando' => 'bg-info',
            'Enviado' => 'bg-warning',
            'Retrasado' => 'bg-danger',
            'Devuelto' => 'bg-dark',
            'Entregado' => 'bg-success',
            'Anulado' => 'bg-secondary',
            default => 'bg-secondary'
        };
        
        return '<span class="badge ' . $badgeClass . '">' . $estado . '</span>';
    }

    // 📋 LISTAR TODOS LOS PEDIDOS CON PRIORIDAD
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $estado = $_GET['estado'] ?? '';
        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';

        if (!empty($termino)) {
            $pedidos = $this->pedidoModel->buscar($termino);
            $modoBusqueda = true;
        } elseif (!empty($estado)) {
            $pedidos = $this->pedidoModel->obtenerPorEstado($estado);
            $modoBusqueda = true;
        } elseif (!empty($fechaInicio) && !empty($fechaFin)) {
            $pedidos = $this->pedidoModel->obtenerPorFecha($fechaInicio, $fechaFin);
            $modoBusqueda = true;
        } else {
            $pedidos = $this->pedidoModel->obtenerTodos();
            $modoBusqueda = false;
        }

        $estadisticas = $this->pedidoModel->obtenerEstadisticas();
        $resumenDiario = $this->pedidoModel->obtenerResumenDiario();
        $pedidosAtrasados = $this->pedidoModel->obtenerAtrasados();

        // Pasar el helper a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        include "views/admin/layout_admin.php";
    }

    // 🚚 PEDIDOS ENVIADOS (PARA SEGUIMIENTO)
    public function enviados() {
        $pedidos = $this->pedidoModel->obtenerEnviados();
        $pedidosAtrasados = $this->pedidoModel->obtenerAtrasados();
        
        // Pasar el helper a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        include "views/admin/layout_admin.php";
    }

    // 👁 VER DETALLE DE PEDIDO CON SEGUIMIENTO
    public function detalle() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de pedido inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=index");
            exit;
        }

        $pedido = $this->pedidoModel->obtenerPorId($id);
        
        if (!$pedido) {
            $_SESSION['mensaje'] = "❌ Pedido no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=index");
            exit;
        }

        // Obtener transportadoras frecuentes para sugerencias
        $transportadorasFrecuentes = $this->pedidoModel->obtenerTransportadorasFrecuentes();
        
        // Generar número de guía de ejemplo (solo para mostrar)
        $ejemploNumeroGuia = $this->pedidoModel->generarNumeroGuia($id);
        
        // Pasar el helper a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        include "views/admin/layout_admin.php";
    }

    // 🔄 ACTUALIZAR ESTADO DEL PEDIDO CON VALIDACIÓN
    public function actualizarEstado() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $estado = $_POST['Estado'] ?? '';
            $descripcion = trim($_POST['Descripcion'] ?? '');
            $usuarioId = $_SESSION['ID_Usuario'] ?? null;

            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de pedido inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=index");
                exit;
            }

            // Validar que el pedido no esté anulado
            $pedido = $this->pedidoModel->obtenerPorId($id);
            if ($pedido['Estado'] === 'Anulado') {
                $_SESSION['mensaje'] = "❌ No se puede modificar un pedido anulado";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar transición de estado
            if (!$this->pedidoModel->puedeCambiarEstado($id, $estado)) {
                $_SESSION['mensaje'] = "❌ No se puede cambiar al estado '$estado' desde el estado actual";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar descripción para ciertos estados
            if (in_array($estado, ['Retrasado', 'Devuelto', 'Anulado']) && empty($descripcion)) {
                $_SESSION['mensaje'] = "❌ Debes proporcionar una descripción para este cambio de estado";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $resultado = $this->pedidoModel->actualizarEstado($id, $estado, $descripcion, $usuarioId);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Estado del pedido actualizado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al actualizar el estado del pedido";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ Error: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
            exit;
        }
    }

    // 📦 MARCAR COMO ENVIADO CON DETALLES
    public function marcarEnviado() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $usuarioId = $_SESSION['ID_Usuario'] ?? null;
            $transportadora = trim($_POST['Transportadora'] ?? '');
            $notas = trim($_POST['Notas_Envio'] ?? '');
            $numeroGuiaPersonalizado = trim($_POST['Numero_Guia_Personalizado'] ?? '');
            
            // Opción para generar automáticamente
            $generarAutomatico = isset($_POST['generar_automatico']) && $_POST['generar_automatico'] === '1';
            
            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de pedido inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=index");
                exit;
            }

            // Validar que el pedido pueda ser enviado
            if (!$this->pedidoModel->puedeCambiarEstado($id, 'Enviado')) {
                $_SESSION['mensaje'] = "❌ No se puede marcar como enviado desde el estado actual";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $resultado = $this->pedidoModel->marcarComoEnviado(
                $id, 
                $usuarioId, 
                $transportadora, 
                $notas,
                $generarAutomatico ? null : $numeroGuiaPersonalizado
            );

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Pedido marcado como enviado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al marcar el pedido como enviado";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ Error: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
            exit;
        }
    }

    // ✅ MARCAR COMO ENTREGADO
    public function marcarEntregado() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $usuarioId = $_SESSION['ID_Usuario'] ?? null;
            $descripcion = trim($_POST['Descripcion'] ?? 'Producto entregado satisfactoriamente');

            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de pedido inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=index");
                exit;
            }

            // Validar que el pedido pueda ser entregado
            if (!$this->pedidoModel->puedeCambiarEstado($id, 'Entregado')) {
                $_SESSION['mensaje'] = "❌ Solo se pueden marcar como entregados los pedidos enviados o retrasados";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $resultado = $this->pedidoModel->actualizarEstado($id, 'Entregado', $descripcion, $usuarioId);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Pedido marcado como entregado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al marcar el pedido como entregado";
                $_SESSION['mensaje_tipo'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
            exit;

        } catch (Exception $e) {
            $_SESSION['mensaje'] = "⚠ Error: " . $e->getMessage();
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
            exit;
        }
    }

    // 📊 GENERAR REPORTE DE PEDIDOS
    public function reporte() {
        $tipo = $_GET['tipo'] ?? 'diario';
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        
        switch ($tipo) {
            case 'diario':
                $pedidos = $this->pedidoModel->obtenerPorFecha($fecha, $fecha);
                break;
            case 'mensual':
                $fechaInicio = date('Y-m-01', strtotime($fecha));
                $fechaFin = date('Y-m-t', strtotime($fecha));
                $pedidos = $this->pedidoModel->obtenerPorFecha($fechaInicio, $fechaFin);
                break;
            case 'anual':
                $fechaInicio = date('Y-01-01', strtotime($fecha));
                $fechaFin = date('Y-12-31', strtotime($fecha));
                $pedidos = $this->pedidoModel->obtenerPorFecha($fechaInicio, $fechaFin);
                break;
            default:
                $pedidos = [];
                break;
        }

        $totalVentas = array_sum(array_column($pedidos, 'Monto_Total'));
        
        // Pasar el helper a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        include "views/admin/layout_admin.php";
    }

    // 🖨 GENERAR FACTURA PDF
    public function generarFactura() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de pedido inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=index");
            exit;
        }

        $pedido = $this->pedidoModel->obtenerPorId($id);
        
        if (!$pedido) {
            $_SESSION['mensaje'] = "❌ Pedido no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=index");
            exit;
        }

        // Redirigir al controlador de PDF existente
        header("Location: " . BASE_URL . "?c=FacturaPDF&a=generarFactura&id=" . $id);
        exit;
    }

    public function envioRapido() {
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            $_SESSION['mensaje'] = "❌ ID de pedido inválido";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=index");
            exit;
        }

        $pedido = $this->pedidoModel->obtenerPorId($id);
        
        if (!$pedido) {
            $_SESSION['mensaje'] = "❌ Pedido no encontrado";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=index");
            exit;
        }

        // Verificar que el pedido pueda ser enviado
        if (!$this->pedidoModel->puedeCambiarEstado($id, 'Enviado')) {
            $_SESSION['mensaje'] = "❌ Este pedido no puede ser enviado desde su estado actual";
            $_SESSION['mensaje_tipo'] = "danger";
            header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
            exit;
        }

        // Obtener transportadoras frecuentes
        $transportadorasFrecuentes = $this->pedidoModel->obtenerTransportadorasFrecuentes();
        
        // Generar número de guía de ejemplo
        $ejemploNumeroGuia = $this->pedidoModel->generarNumeroGuia($id);
        
        // Pasar el helper a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        include "views/admin/layout_admin.php";
    }
}
?>