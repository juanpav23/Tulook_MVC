<?php
require_once "models/Database.php";
require_once "models/Pedido.php";
require_once "models/Usuario.php";
require_once "services/Mailer.php";

class PedidoController {
    private $db;
    private $pedidoModel;
    private $usuarioModel;
    private $mailer;

    public function __construct($db = null) {
        if ($db) {
            $this->db = $db;
        } else {
            $dbObj = new Database();
            $this->db = $dbObj->getConnection();
        }
        $this->pedidoModel = new Pedido($this->db);
        $this->usuarioModel = new Usuario($this->db);
        $this->mailer = new Mailer(); // Inicializar Mailer

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

        // CORRECCIÓN: Validar y procesar fechas correctamente
        $fechaInicio = !empty($fechaInicio) ? $fechaInicio : '';
        $fechaFin = !empty($fechaFin) ? $fechaFin : '';

        if (!empty($termino)) {
            $pedidos = $this->pedidoModel->buscar($termino);
            $modoBusqueda = true;
        } elseif (!empty($estado)) {
            $pedidos = $this->pedidoModel->obtenerPorEstado($estado);
            $modoBusqueda = true;
        } elseif (!empty($fechaInicio) || !empty($fechaFin)) {
            // CORRECCIÓN: Si solo una fecha está presente, usar la misma para ambas
            if (!empty($fechaInicio) && empty($fechaFin)) {
                $fechaFin = $fechaInicio;
            } elseif (empty($fechaInicio) && !empty($fechaFin)) {
                $fechaInicio = $fechaFin;
            }
            
            // Validar que las fechas sean válidas
            if (strtotime($fechaInicio) > strtotime($fechaFin)) {
                // Si la fecha inicio es mayor que la fin, intercambiarlas
                $temp = $fechaInicio;
                $fechaInicio = $fechaFin;
                $fechaFin = $temp;
            }
            
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
        // Obtener parámetros de filtro
        $buscar = $_GET['buscar'] ?? '';
        $transportadora = $_GET['transportadora'] ?? '';
        $estadoFiltro = $_GET['estado_filtro'] ?? '';
        $fechaInicio = $_GET['fecha_inicio'] ?? '';
        $fechaFin = $_GET['fecha_fin'] ?? '';
        
        // Obtener todos los pedidos enviados primero
        $pedidos = $this->pedidoModel->obtenerEnviados();
        
        // Aplicar filtros si existen
        $pedidosFiltrados = [];
        
        foreach ($pedidos as $pedido) {
            $cumpleFiltros = true;
            
            // Filtro de búsqueda
            if (!empty($buscar)) {
                $termino = strtolower($buscar);
                $cumpleBusqueda = false;
                
                // Buscar en código de acceso
                if (stripos($pedido['Codigo_Acceso'], $termino) !== false) {
                    $cumpleBusqueda = true;
                }
                
                // Buscar en nombre del cliente
                $nombreCompleto = strtolower($pedido['Nombre'] . ' ' . $pedido['Apellido']);
                if (stripos($nombreCompleto, $termino) !== false) {
                    $cumpleBusqueda = true;
                }
                
                // Buscar en email
                if (stripos($pedido['Correo'], $termino) !== false) {
                    $cumpleBusqueda = true;
                }
                
                if (!$cumpleBusqueda) {
                    $cumpleFiltros = false;
                }
            }
            
            // Filtro de transportadora
            if (!empty($transportadora) && $cumpleFiltros) {
                if ($pedido['Transportadora'] !== $transportadora) {
                    $cumpleFiltros = false;
                }
            }
            
            // Filtro de estado de seguimiento
            if (!empty($estadoFiltro) && $cumpleFiltros) {
                switch ($estadoFiltro) {
                    case 'retrasados':
                        if ($pedido['Estado'] !== 'Retrasado') {
                            $cumpleFiltros = false;
                        }
                        break;
                        
                    case 'en_transito':
                        if ($pedido['Estado'] !== 'Enviado') {
                            $cumpleFiltros = false;
                        }
                        break;
                        
                    case 'proximos_vencer':
                        if (!empty($pedido['Fecha_Estimada_Entrega'])) {
                            $diasFaltantes = floor((strtotime($pedido['Fecha_Estimada_Entrega']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
                            if ($diasFaltantes > 2 || $diasFaltantes < 0) {
                                $cumpleFiltros = false;
                            }
                        } else {
                            $cumpleFiltros = false;
                        }
                        break;
                }
            }
            
            // Filtro de fecha de envío
            if (!empty($fechaInicio) && $cumpleFiltros && !empty($pedido['Fecha_Envio'])) {
                $fechaEnvio = date('Y-m-d', strtotime($pedido['Fecha_Envio']));
                if ($fechaEnvio < $fechaInicio) {
                    $cumpleFiltros = false;
                }
            }
            
            if (!empty($fechaFin) && $cumpleFiltros && !empty($pedido['Fecha_Envio'])) {
                $fechaEnvio = date('Y-m-d', strtotime($pedido['Fecha_Envio']));
                if ($fechaEnvio > $fechaFin) {
                    $cumpleFiltros = false;
                }
            }
            
            if ($cumpleFiltros) {
                $pedidosFiltrados[] = $pedido;
            }
        }
        
        // Usar pedidos filtrados o todos si no hay filtros
        $pedidosMostrar = !empty($buscar) || !empty($transportadora) || !empty($estadoFiltro) || !empty($fechaInicio) || !empty($fechaFin) 
            ? $pedidosFiltrados 
            : $pedidos;
        
        // Obtener estadísticas de retrasos (ya no usamos atrasados)
        $estadisticasRetrasos = $this->pedidoModel->obtenerEstadisticasRetrasos();
        
        // Verificar y marcar automáticamente pedidos retrasados
        $this->verificarRetrasosAutomaticos();
        
        // Pasar el helper a la vista
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        // Pasar pedidos filtrados a la vista
        $pedidos = $pedidosMostrar;
        
        include "views/admin/layout_admin.php";
    }

    private function enviarNotificacionCorreo($idFactura, $estadoAnterior, $estadoNuevo, $motivo = '', $datosAdicionales = []) {
        try {
            // Obtener datos del pedido y cliente
            $pedido = $this->pedidoModel->obtenerPorId($idFactura);
            
            if (!$pedido) {
                error_log("Pedido no encontrado para notificación: {$idFactura}");
                return false;
            }

            // Preparar datos del cliente
            $cliente = [
                'nombre' => $pedido['Nombre'] . ' ' . $pedido['Apellido'],
                'email' => $pedido['Correo']
            ];

            // Preparar datos de la factura
            $factura = [
                'ID_Factura' => $pedido['ID_Factura'],
                'Codigo_Acceso' => $pedido['Codigo_Acceso']
            ];

            // Agregar más datos del pedido a la factura
            foreach (['Fecha_Factura', 'Monto_Total', 'Metodo_Pago', 'Direccion_Completa'] as $campo) {
                if (isset($pedido[$campo])) {
                    $factura[$campo] = $pedido[$campo];
                }
            }

            // Enviar notificación
            $resultado = $this->mailer->enviarNotificacionEstado(
                $cliente,
                $factura,
                $estadoAnterior,
                $estadoNuevo,
                $motivo,
                $datosAdicionales
            );

            if (!$resultado['success']) {
                error_log("Error enviando notificación: " . $resultado['message']);
            }

            return $resultado['success'];

        } catch (Exception $e) {
            error_log("Excepción al enviar notificación: " . $e->getMessage());
            return false;
        }
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

            // Obtener el pedido actual para conocer el estado anterior
            $pedido = $this->pedidoModel->obtenerPorId($id);
            if ($pedido['Estado'] === 'Anulado') {
                $_SESSION['mensaje'] = "❌ No se puede modificar un pedido anulado";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $estadoAnterior = $pedido['Estado'];

            // Validar transición de estado
            if (!$this->pedidoModel->puedeCambiarEstado($id, $estado)) {
                $_SESSION['mensaje'] = "❌ No se puede cambiar al estado '$estado' desde el estado actual";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar descripción para ciertos estados
            if (in_array($estado, ['Retrasado', 'Devuelto', 'Anulado', 'Preparando']) && empty($descripcion)) {
                $_SESSION['mensaje'] = "❌ Debes proporcionar una descripción para este cambio de estado";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Preparar datos adicionales
            $datosAdicionales = [];
            if ($estado === 'Retrasado' && !empty($nuevaFechaEstimada)) {
                $datosAdicionales['fecha_estimada'] = $nuevaFechaEstimada;
            }

            // Si estamos regresando a Preparando desde Enviado, limpiar datos de envío
            if ($estado === 'Preparando' && in_array($pedido['Estado'], ['Enviado', 'Retrasado'])) {
                $datosAdicionales['limpiar_envio'] = true;
            }

            // Si estamos anulando desde Enviado, limpiar datos de envío
            if ($estado === 'Anulado' && in_array($pedido['Estado'], ['Enviado', 'Retrasado'])) {
                $datosAdicionales['limpiar_envio'] = true;
            }

            // Ejecutar el cambio de estado con devolución de stock si es anulado
            $resultado = $this->pedidoModel->actualizarEstado($id, $estado, $descripcion, $usuarioId, $datosAdicionales);

            if ($resultado) {
                // ============================================
                // ENVIAR NOTIFICACIÓN POR CORREO CON PRODUCTOS
                // ============================================
                
                // Obtener los productos para adjuntar al correo si es necesario
                $pedidoCompleto = $this->pedidoModel->obtenerPorId($id);
                $datosAdicionales['items'] = $pedidoCompleto['productos'] ?? [];
                
                // También agregar datos de envío si están disponibles
                $datosAdicionales['transportadora'] = $pedidoCompleto['Transportadora'] ?? '';
                $datosAdicionales['numero_guia'] = $pedidoCompleto['Numero_Guia'] ?? '';
                $datosAdicionales['fecha_estimada'] = $pedidoCompleto['Fecha_Estimada_Entrega'] ?? '';
                
                $this->enviarNotificacionCorreo($id, $estadoAnterior, $estado, $descripcion, $datosAdicionales);
                // ============================================

                // Si se anuló el pedido, devolver el stock
                if ($estado === 'Anulado') {
                    $stockDevuelto = $this->pedidoModel->devolverStockPedidoAnulado($id);
                    if ($stockDevuelto) {
                        $_SESSION['mensaje'] = "✅ Estado del pedido actualizado correctamente y stock devuelto";
                        $_SESSION['mensaje_tipo'] = "success";
                    } else {
                        $_SESSION['mensaje'] = "✅ Estado del pedido actualizado correctamente. ❌ Error al devolver stock";
                        $_SESSION['mensaje_tipo'] = "warning";
                    }
                } else {
                    $_SESSION['mensaje'] = "✅ Estado del pedido actualizado correctamente";
                    $_SESSION['mensaje_tipo'] = "success";
                }
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

    // NUEVO MÉTODO: CANCELAR PROCESO (Regresar a Confirmado)
    public function cancelarProceso() {
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

            $estadoAnterior = $pedido['Estado'];

            // Verificar que el pedido esté en estado "Preparando"
            if ($pedido['Estado'] !== 'Preparando') {
                $_SESSION['mensaje'] = "❌ Solo se puede cancelar el proceso de pedidos en estado 'Preparando'";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar descripción
            if (empty($descripcion)) {
                $_SESSION['mensaje'] = "❌ Debes proporcionar una descripción para cancelar el proceso";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Actualizar el estado a "Confirmado"
            $resultado = $this->pedidoModel->actualizarEstado($id, 'Confirmado', $descripcion, $usuarioId);

            if ($resultado) {
                // ENVIAR NOTIFICACIÓN POR CORREO CON DATOS COMPLETOS
                $pedidoCompleto = $this->pedidoModel->obtenerPorId($id);
                $datosAdicionales = [
                    'items' => $pedidoCompleto['productos'] ?? []
                ];
                
                $this->enviarNotificacionCorreo($id, $estadoAnterior, 'Confirmado', $descripcion, $datosAdicionales);
                
                $_SESSION['mensaje'] = "✅ Proceso de preparación cancelado. Pedido regresado a estado Confirmado";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al cancelar el proceso de preparación";
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

            // Obtener el pedido actual
            $pedido = $this->pedidoModel->obtenerPorId($id);
            $estadoAnterior = $pedido['Estado'];

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
                // Preparar datos adicionales para la notificación
                $datosAdicionales = [
                    'fecha_estimada' => $fechaEstimadaEntrega,
                    'transportadora' => $transportadora
                ];
                
                // Obtener número de guía si existe
                $pedidoActualizado = $this->pedidoModel->obtenerPorId($id);
                if (!empty($pedidoActualizado['Numero_Guia'])) {
                    $datosAdicionales['numero_guia'] = $pedidoActualizado['Numero_Guia'];
                }
                
                // ENVIAR NOTIFICACIÓN POR CORREO
                $descripcion = "Tu pedido ha sido enviado. " . (!empty($notas) ? "Notas: {$notas}" : "");
                $this->enviarNotificacionCorreo($id, $estadoAnterior, 'Enviado', $descripcion, $datosAdicionales);
                
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

    // CANCELAR ENVÍO (Regresar a Preparando)
    public function regresarAPreparando() {
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

            $estadoAnterior = $pedido['Estado'];

            // Verificar que el pedido esté en estado "Enviado" o "Retrasado"
            if (!in_array($pedido['Estado'], ['Enviado', 'Retrasado'])) {
                $_SESSION['mensaje'] = "❌ Solo se puede cancelar el envío de pedidos en estado 'Enviado' o 'Retrasado'";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar descripción
            if (empty($descripcion)) {
                $_SESSION['mensaje'] = "❌ Debes proporcionar una descripción para cancelar el envío";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Usar el nuevo método del modelo
            $resultado = $this->pedidoModel->regresarAPreparando($id, $descripcion, $usuarioId);

            if ($resultado) {
                // ENVIAR NOTIFICACIÓN POR CORREO CON DATOS COMPLETOS
                $pedidoCompleto = $this->pedidoModel->obtenerPorId($id);
                $datosAdicionales = [
                    'items' => $pedidoCompleto['productos'] ?? [],
                    'transportadora' => $pedidoCompleto['Transportadora'] ?? '',
                    'numero_guia' => $pedidoCompleto['Numero_Guia'] ?? '',
                    'fecha_estimada' => $pedidoCompleto['Fecha_Estimada_Entrega'] ?? ''
                ];
                
                $this->enviarNotificacionCorreo($id, $estadoAnterior, 'Preparando', $descripcion, $datosAdicionales);
                
                $_SESSION['mensaje'] = "✅ Envío cancelado. Pedido regresado a estado Preparando";
                $_SESSION['mensaje_tipo'] = "success";
            } else {
                $_SESSION['mensaje'] = "❌ Error al cancelar el envío";
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

    // Anular pedido enviado (con devolución de stock y eliminación de datos de envío)
    public function anularPedidoEnviado() {
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

            $estadoAnterior = $pedido['Estado'];

            // Verificar que el pedido esté en estado "Enviado" o "Retrasado"
            if (!in_array($pedido['Estado'], ['Enviado', 'Retrasado'])) {
                $_SESSION['mensaje'] = "❌ Solo se puede anular pedidos en estado 'Enviado' o 'Retrasado'";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Validar descripción
            if (empty($descripcion)) {
                $_SESSION['mensaje'] = "❌ Debes proporcionar una descripción para anular el pedido";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            // Usar el nuevo método del modelo
            $resultado = $this->pedidoModel->anularPedidoEnviado($id, $descripcion, $usuarioId);

            if ($resultado) {
                // ENVIAR NOTIFICACIÓN POR CORREO
                $this->enviarNotificacionCorreo($id, $estadoAnterior, 'Anulado', $descripcion);
                
                // Devolver el stock
                $stockDevuelto = $this->pedidoModel->devolverStockPedidoAnulado($id);
                if ($stockDevuelto) {
                    $_SESSION['mensaje'] = "✅ Pedido anulado correctamente, stock devuelto y datos de envío eliminados";
                    $_SESSION['mensaje_tipo'] = "success";
                } else {
                    $_SESSION['mensaje'] = "✅ Pedido anulado correctamente. ❌ Error al devolver stock";
                    $_SESSION['mensaje_tipo'] = "warning";
                }
            } else {
                $_SESSION['mensaje'] = "❌ Error al anular el pedido";
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

            // Obtener el pedido actual
            $pedido = $this->pedidoModel->obtenerPorId($id);
            $estadoAnterior = $pedido['Estado'];

            // Validar que el pedido pueda ser entregado
            if (!$this->pedidoModel->puedeCambiarEstado($id, 'Entregado')) {
                $_SESSION['mensaje'] = "Solo se pueden marcar como entregados los pedidos enviados o retrasados";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }

            $resultado = $this->pedidoModel->actualizarEstado($id, 'Entregado', $descripcion, $usuarioId);

            if ($resultado) {
                // ENVIAR NOTIFICACIÓN POR CORREO
                $this->enviarNotificacionCorreo($id, $estadoAnterior, 'Entregado', $descripcion);
                
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
            $estadoAnterior = $pedido['Estado'];

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
                // ENVIAR NOTIFICACIÓN POR CORREO CON DATOS COMPLETOS
                $pedidoCompleto = $this->pedidoModel->obtenerPorId($id);
                $datosAdicionales = [
                    'items' => $pedidoCompleto['productos'] ?? []
                ];
                
                $this->enviarNotificacionCorreo($id, $estadoAnterior, 'Preparando', $descripcion, $datosAdicionales);
                
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
        header("Location: " . BASE_URL . "?c=FacturaPDF&a=generar&id=" . $id);
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
        
        // Obtener la fecha del DÍA SIGUIENTE para mostrar por defecto
        $fechaManana = date('Y-m-d', strtotime('+1 day'));
        
        // Pasar el helper a la vista junto con la fecha por defecto
        $getEstadoBadge = [$this, 'getEstadoBadge'];
        
        // Variables adicionales para la vista
        $fechaPorDefecto = $fechaManana;
        
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

            // ============================================
            // OBTENER FECHA ANTERIOR ANTES DE ACTUALIZAR
            // ============================================
            $pedidoAntes = $this->pedidoModel->obtenerPorId($id);
            if (!$pedidoAntes) {
                $_SESSION['mensaje'] = "❌ Pedido no encontrado";
                $_SESSION['mensaje_tipo'] = "danger";
                header("Location: " . BASE_URL . "?c=Pedido&a=detalle&id=" . $id);
                exit;
            }
            
            // Obtener fecha anterior (si no tiene, usar fecha por defecto)
            $fechaAnterior = $pedidoAntes['Fecha_Estimada_Entrega'] ?? null;
            if (!$fechaAnterior) {
                // Si no tenía fecha estimada, calcular una basada en la fecha de envío
                if (!empty($pedidoAntes['Fecha_Envio'])) {
                    $fechaAnterior = date('Y-m-d', strtotime($pedidoAntes['Fecha_Envio'] . ' + 3 days'));
                } else {
                    $fechaAnterior = date('Y-m-d', strtotime('+3 days'));
                }
            }
            // ============================================

            $resultado = $this->pedidoModel->actualizarFechaEstimada($id, $fechaEstimada);

            if ($resultado) {
                // Registrar en el historial
                $usuarioId = $_SESSION['ID_Usuario'] ?? null;
                $descripcion = "Fecha estimada de entrega actualizada a " . date('d/m/Y', strtotime($fechaEstimada));
                if (!empty($motivo)) {
                    $descripcion .= ". Motivo: $motivo";
                }
                
                $this->pedidoModel->registrarSeguimiento($id, 'Enviado', $descripcion, $usuarioId);
                
                // ============================================
                // ENVIAR NOTIFICACIÓN DE CAMBIO DE FECHA POR CORREO
                // ============================================
                $pedido = $this->pedidoModel->obtenerPorId($id);
                if ($pedido) {
                    $cliente = [
                        'nombre' => $pedido['Nombre'] . ' ' . $pedido['Apellido'],
                        'email' => $pedido['Correo']
                    ];
                    
                    // Preparar datos adicionales
                    $datosAdicionales = [
                        'transportadora' => $pedido['Transportadora'] ?? '',
                        'numero_guia' => $pedido['Numero_Guia'] ?? '',
                        'items' => $pedido['productos'] ?? [] // Agregar productos para el PDF
                    ];
                    
                    // Enviar notificación de cambio de fecha
                    $resultadoCorreo = $this->mailer->enviarNotificacionCambioFecha(
                        $cliente,
                        $pedido,
                        $fechaAnterior, // Usar la fecha anterior que guardamos ANTES del update
                        $fechaEstimada, // La nueva fecha
                        $motivo,
                        $datosAdicionales
                    );
                    
                    if (!$resultadoCorreo['success']) {
                        error_log("Error enviando notificación de cambio de fecha: " . $resultadoCorreo['message']);
                        // No mostrar error al usuario, solo log
                    }
                }
                // ============================================
                
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