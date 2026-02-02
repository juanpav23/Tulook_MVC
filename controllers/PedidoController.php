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

    // Helper para mostrar badges de estado - USANDO PALETA DE USUARIOS
    private function getEstadoBadge($estado) {
        $badgeClass = '';
        $icon = '';
        
        switch($estado) {
            case 'Emitido':
                $badgeClass = 'badge-estado-emitido';
                $icon = 'fa-clock';
                break;
            case 'Confirmado':
                $badgeClass = 'badge-estado-confirmado';
                $icon = 'fa-check';
                break;
            case 'Preparando':
                $badgeClass = 'badge-estado-preparando';
                $icon = 'fa-cogs';
                break;
            case 'Enviado':
                $badgeClass = 'badge-estado-enviado';
                $icon = 'fa-truck';
                break;
            case 'Retrasado':
                $badgeClass = 'badge-estado-retrasado';
                $icon = 'fa-exclamation-triangle';
                break;
            case 'Devuelto':
                $badgeClass = 'badge-estado-devuelto';
                $icon = 'fa-undo';
                break;
            case 'Entregado':
                $badgeClass = 'badge-estado-entregado';
                $icon = 'fa-box-check';
                break;
            case 'Anulado':
                $badgeClass = 'badge-estado-anulado';
                $icon = 'fa-ban';
                break;
            default:
                $badgeClass = 'badge bg-primary-dark';
                $icon = 'fa-question';
        }
        
        return '<span class="badge ' . $badgeClass . ' d-flex align-items-center justify-content-center gap-1" style="min-width: 120px;">
                    <i class="fas ' . $icon . '"></i>
                    ' . $estado . '
                </span>';
    }

    // 📋 LISTAR TODOS LOS PEDIDOS CON PRIORIDAD Y ALERTAS
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
        $alertasPedidos = $this->pedidoModel->obtenerPedidosConAlertas();

        // Pasar el helper a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        include "views/admin/layout_admin.php";
    }

    // 🚚 PEDIDOS ENVIADOS (PARA SEGUIMIENTO)
    public function enviados() {
        $pedidos = $this->pedidoModel->obtenerEnviados();
        $pedidosAtrasados = $this->pedidoModel->obtenerAtrasados();
        $estadisticasRetrasos = $this->pedidoModel->obtenerEstadisticasRetrasos();
        
        // Verificar y marcar automáticamente pedidos retrasados
        $this->verificarRetrasosAutomaticos();
        
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

    // 🔄 ACTUALIZAR ESTADO DEL PEDIDO CON VALIDACIÓN Y DEVOLUCIÓN DE STOCK
    public function actualizarEstado() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $estado = $_POST['Estado'] ?? '';
            $descripcion = trim($_POST['Descripcion'] ?? '');
            $usuarioId = $_SESSION['ID_Usuario'] ?? null;
            $nuevaFechaEstimada = $_POST['nueva_fecha_estimada'] ?? null;

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

            // Preparar datos adicionales
            $datosAdicionales = [];
            if ($estado === 'Retrasado' && !empty($nuevaFechaEstimada)) {
                $datosAdicionales['nueva_fecha_estimada'] = $nuevaFechaEstimada;
            }

            // Ejecutar el cambio de estado con devolución de stock si es anulado
            $resultado = $this->pedidoModel->actualizarEstado($id, $estado, $descripcion, $usuarioId, $datosAdicionales);

            // Si se anuló el pedido, devolver el stock
            if ($estado === 'Anulado' && $resultado) {
                $stockDevuelto = $this->pedidoModel->devolverStockPedidoAnulado($id);
                if ($stockDevuelto) {
                    $_SESSION['mensaje'] = "✅ Estado del pedido actualizado correctamente y stock devuelto";
                    $_SESSION['mensaje_tipo'] = "success";
                } else {
                    $_SESSION['mensaje'] = "✅ Estado del pedido actualizado correctamente. ❌ Error al devolver stock";
                    $_SESSION['mensaje_tipo'] = "warning";
                }
            } elseif ($resultado) {
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

    // MARCAR COMO ENVIADO CON DETALLES Y FECHA ESTIMADA
    public function marcarEnviado() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $usuarioId = $_SESSION['ID_Usuario'] ?? null;
            $transportadora = trim($_POST['Transportadora'] ?? '');
            $notas = trim($_POST['Notas_Envio'] ?? '');
            $numeroGuiaPersonalizado = trim($_POST['Numero_Guia_Personalizado'] ?? '');
            $fechaEstimadaEntrega = $_POST['fecha_estimada_entrega'] ?? null;
            
            // Opción para generar automáticamente
            $generarAutomatico = isset($_POST['generar_automatico']) && $_POST['generar_automatico'] === '1';
            
            if ($id <= 0) {
                $_SESSION['mensaje'] = "ID de pedido inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=index");
                exit;
            }

            // Validar fecha estimada
            if (empty($fechaEstimadaEntrega)) {
                $_SESSION['mensaje'] = "Debes ingresar una fecha estimada de entrega";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar que la fecha estimada sea en el futuro
            if (strtotime($fechaEstimadaEntrega) < strtotime(date('Y-m-d'))) {
                $_SESSION['mensaje'] = "La fecha estimada de entrega debe ser una fecha futura";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar que el pedido pueda ser enviado
            if (!$this->pedidoModel->puedeCambiarEstado($id, 'Enviado')) {
                $_SESSION['mensaje'] = "No se puede marcar como enviado desde el estado actual";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $resultado = $this->pedidoModel->marcarComoEnviado(
                $id, 
                $usuarioId, 
                $transportadora, 
                $notas,
                $generarAutomatico ? null : $numeroGuiaPersonalizado,
                $fechaEstimadaEntrega
            );

            if ($resultado) {
                $_SESSION['mensaje'] = "Pedido marcado como enviado correctamente. Entrega estimada: " . date('d/m/Y', strtotime($fechaEstimadaEntrega));
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al marcar el pedido como enviado";
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

    // MARCAR COMO ENTREGADO
    public function marcarEntregado() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $usuarioId = $_SESSION['ID_Usuario'] ?? null;
            $descripcion = trim($_POST['Descripcion'] ?? 'Producto entregado satisfactoriamente');

            if ($id <= 0) {
                $_SESSION['mensaje'] = "ID de pedido inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=index");
                exit;
            }

            // Validar que el pedido pueda ser entregado
            if (!$this->pedidoModel->puedeCambiarEstado($id, 'Entregado')) {
                $_SESSION['mensaje'] = "Solo se pueden marcar como entregados los pedidos enviados o retrasados";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $resultado = $this->pedidoModel->actualizarEstado($id, 'Entregado', $descripcion, $usuarioId);

            if ($resultado) {
                $_SESSION['mensaje'] = "Pedido marcado como entregado correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "Error al marcar el pedido como entregado";
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

    // PREPARAR PEDIDO DEVUELTO NUEVAMENTE
    public function prepararDevuelto() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $descripcion = trim($_POST['Descripcion'] ?? '');
            $usuarioId = $_SESSION['ID_Usuario'] ?? null;

            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de pedido inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=index");
                exit;
            }

            // Obtener el pedido actual
            $pedido = $this->pedidoModel->obtenerPorId($id);
            if (!$pedido) {
                $_SESSION['mensaje'] = "❌ Pedido no encontrado";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=index");
                exit;
            }

            // Verificar que el pedido esté en estado "Devuelto"
            if ($pedido['Estado'] !== 'Devuelto') {
                $_SESSION['mensaje'] = "❌ Solo se pueden preparar nuevamente pedidos devueltos";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar descripción
            if (empty($descripcion)) {
                $_SESSION['mensaje'] = "❌ Debes proporcionar una descripción para este cambio de estado";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Actualizar el estado a "Preparando"
            $resultado = $this->pedidoModel->actualizarEstado($id, 'Preparando', $descripcion, $usuarioId);

            if ($resultado) {
                $_SESSION['mensaje'] = "✅ Pedido devuelto preparado nuevamente correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al preparar nuevamente el pedido";
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

    // GENERAR REPORTE DE PEDIDOS
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
        
        // Pasar variables a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        // Pasar tipo y fecha a la vista para mostrar en los filtros
        $viewData = [
            'pedidos' => $pedidos,
            'totalVentas' => $totalVentas,
            'getEstadoBadge' => $getEstadoBadge,
            'tipo' => $tipo,
            'fecha' => $fecha
        ];
        
        // Incluir la vista
        extract($viewData);
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

    // ENVÍO RÁPIDO
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

    // VERIFICAR RETRASOS AUTOMÁTICAMENTE
    private function verificarRetrasosAutomaticos() {
        $pedidosParaRetrasar = $this->pedidoModel->obtenerPedidosParaRetrasar();
        
        if (!empty($pedidosParaRetrasar)) {
            $usuarioId = $_SESSION['ID_Usuario'] ?? 1; // Usuario actual o sistema
            $contador = $this->pedidoModel->marcarRetrasadosAutomaticamente($usuarioId);
            
            if ($contador > 0) {
                $_SESSION['mensaje'] = "✅ $contador pedido(s) marcado(s) automáticamente como retrasado(s) por superar la fecha estimada de entrega";
                $_SESSION['mensaje_tipo'] = "warning";
            }
        }
    }

    // ACTUALIZAR FECHA ESTIMADA DE ENTREGA
    public function actualizarFechaEstimada() {
        try {
            $id = (int)($_POST['ID_Factura'] ?? 0);
            $fechaEstimada = $_POST['fecha_estimada'] ?? null;
            $motivo = trim($_POST['motivo'] ?? '');

            if ($id <= 0) {
                $_SESSION['mensaje'] = "❌ ID de pedido inválido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            if (empty($fechaEstimada)) {
                $_SESSION['mensaje'] = "❌ Debes ingresar una fecha estimada";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar que la fecha estimada sea en el futuro
            if (strtotime($fechaEstimada) < strtotime(date('Y-m-d'))) {
                $_SESSION['mensaje'] = "❌ La fecha estimada debe ser una fecha futura";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $resultado = $this->pedidoModel->actualizarFechaEstimada($id, $fechaEstimada);

            if ($resultado) {
                // Registrar en el historial
                $usuarioId = $_SESSION['ID_Usuario'] ?? null;
                $descripcion = "Fecha estimada de entrega actualizada a " . date('d/m/Y', strtotime($fechaEstimada));
                if (!empty($motivo)) {
                    $descripcion .= ". Motivo: $motivo";
                }
                
                $this->pedidoModel->registrarSeguimiento($id, 'Enviado', $descripcion, $usuarioId);
                
                $_SESSION['mensaje'] = "✅ Fecha estimada de entrega actualizada correctamente";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al actualizar la fecha estimada";
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
}
?>