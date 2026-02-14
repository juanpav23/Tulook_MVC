<?php
// controllers/ReporteVentaController.php
require_once "models/Database.php";
require_once "models/ReporteVenta.php";

class ReporteVentaController {
    private $db;
    private $reporteVentaModel;

    public function __construct($db = null) {
        date_default_timezone_set('America/Bogota');
        
        if ($db) {
            $this->db = $db;
        } else {
            $dbObj = new Database();
            $this->db = $dbObj->getConnection();
        }
        $this->reporteVentaModel = new ReporteVenta($this->db);

        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->ensureAdmin();
    }

    private function ensureAdmin() {
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }
    }

    // 📊 DASHBOARD PRINCIPAL DE REPORTES (MEJORADO)
    public function index() {
        // Obtener filtros por defecto
        $periodo = $_GET['periodo'] ?? 'hoy';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        // Ajustar fechas según período (SOLO PARA MÉTRICAS Y TABLAS)
        list($fechaInicioMetricas, $fechaFinMetricas) = $this->ajustarFechasPorPeriodo($periodo, $fechaInicio, $fechaFin);
        
        // PARA GRÁFICOS DE TENDENCIAS: Usar rangos fijos independientes del filtro
        // Tendencias diarias: últimos 30 días
        $fechaInicioDiarias = date('Y-m-d', strtotime('-30 days'));
        $fechaFinDiarias = date('Y-m-d');
        
        // Tendencias semanales: últimas 12 semanas
        $fechaInicioSemanales = date('Y-m-d', strtotime('-12 weeks'));
        $fechaFinSemanales = date('Y-m-d');
        
        // Tendencias mensuales: últimos 12 meses
        $fechaInicioMensuales = date('Y-m-d', strtotime('-12 months'));
        $fechaFinMensuales = date('Y-m-d');
        
        // Tendencias trimestrales: últimos 4 años (16 trimestres)
        $fechaInicioTrimestrales = date('Y-m-d', strtotime('-4 years'));
        $fechaFinTrimestrales = date('Y-m-d');
        
        // Tendencias anuales: últimos 5 años
        $fechaInicioAnuales = date('Y-m-d', strtotime('-5 years'));
        $fechaFinAnuales = date('Y-m-d');
        
        // Obtener métricas principales (USANDO EL FILTRO DEL PERÍODO)
        $metricas = $this->reporteVentaModel->obtenerMetricasPrincipales($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener ventas por día para gráfico (ÚLTIMOS 30 DÍAS, INDEPENDIENTE DEL FILTRO)
        $ventasDiarias = $this->reporteVentaModel->obtenerVentasPorDia($fechaInicioDiarias, $fechaFinDiarias);
        
        // Obtener ventas por semana (ÚLTIMAS 12 SEMANAS, INDEPENDIENTE DEL FILTRO)
        $ventasSemanales = $this->reporteVentaModel->obtenerVentasPorSemana($fechaInicioSemanales, $fechaFinSemanales);
        
        // Obtener ventas por mes (ÚLTIMOS 12 MESES, INDEPENDIENTE DEL FILTRO)
        $ventasMensuales = $this->reporteVentaModel->obtenerVentasPorMes($fechaInicioMensuales, $fechaFinMensuales);
        
        // Obtener ventas por trimestre (ÚLTIMOS 4 AÑOS, INDEPENDIENTE DEL FILTRO)
        $ventasTrimestrales = $this->reporteVentaModel->obtenerVentasPorTrimestre($fechaInicioTrimestrales, $fechaFinTrimestrales);
        
        // Obtener ventas por año (ÚLTIMOS 5 AÑOS, INDEPENDIENTE DEL FILTRO)
        $ventasAnuales = $this->reporteVentaModel->obtenerVentasPorAno($fechaInicioAnuales, $fechaFinAnuales);
        
        // Obtener ventas por categoría (USANDO EL FILTRO DEL PERÍODO)
        $ventasPorCategoria = $this->reporteVentaModel->obtenerVentasPorCategoria($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener ventas por género (USANDO EL FILTRO DEL PERÍODO)
        $ventasPorGenero = $this->reporteVentaModel->obtenerVentasPorGenero($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener ventas por color (USANDO EL FILTRO DEL PERÍODO)
        $ventasPorColor = $this->reporteVentaModel->obtenerVentasPorColor($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener productos más vendidos (con género) (USANDO EL FILTRO DEL PERÍODO)
        $productosMasVendidos = $this->reporteVentaModel->obtenerProductosMasVendidosConDetalles($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener pérdidas por descuentos (USANDO EL FILTRO DEL PERÍODO)
        $perdidasDescuentos = $this->reporteVentaModel->obtenerPerdidasPorDescuento($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener ventas por método de pago (USANDO EL FILTRO DEL PERÍODO)
        $ventasPorMetodoPago = $this->reporteVentaModel->obtenerVentasPorMetodoPago($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener clientes frecuentes (USANDO EL FILTRO DEL PERÍODO)
        $clientesFrecuentes = $this->reporteVentaModel->obtenerClientesFrecuentes($fechaInicioMetricas, $fechaFinMetricas);
        
        // Obtener datos para comparativas (ÚLTIMOS 2 AÑOS PARA COMPARATIVA)
        $fechaInicioComparativa = date('Y-m-d', strtotime('-2 years'));
        $fechaFinComparativa = date('Y-m-d');
        $comparativaMensual = $this->reporteVentaModel->obtenerComparativaMensual($fechaInicioComparativa, $fechaFinComparativa);
        $comparativaAnual = $this->reporteVentaModel->obtenerComparativaAnual($fechaInicioComparativa, $fechaFinComparativa);
        
        // Preparar datos para gráficos (PASANDO LAS FECHAS DE TENDENCIAS)
        $datosGrafico = $this->prepararDatosParaGraficos(
            $ventasDiarias, 
            $ventasSemanales, 
            $ventasMensuales, 
            $ventasTrimestrales, 
            $ventasAnuales,
            $ventasPorCategoria,
            $ventasPorGenero,
            $ventasPorColor,
            $comparativaMensual,
            $comparativaAnual
        );
        
        // Pasar las fechas del filtro a la vista
        $fechaInicio = $fechaInicioMetricas;
        $fechaFin = $fechaFinMetricas;
        
        include "views/admin/layout_admin.php";
    }

    public function clientes_todos() {
        // NO aplicar filtros de fecha - obtener TODOS los clientes
        $periodo = 'todo';
        $orden = $_GET['orden'] ?? 'monto_desc';
        
        // Usar fechas muy amplias para cubrir todo el historial
        $fechaInicio = '2000-01-01'; // Fecha muy antigua
        $fechaFin = date('Y-m-d');    // Hasta hoy
        
        // Obtener TODOS los clientes sin límite de fecha
        $clientes = $this->reporteVentaModel->obtenerVentasPorCliente($fechaInicio, $fechaFin, $orden);
        
        // Calcular totales globales
        $totales = [
            'ventas_totales' => 0,
            'clientes_unicos' => count($clientes),
            'pedidos_totales' => 0
        ];
        
        foreach ($clientes as $cliente) {
            $totales['ventas_totales'] += $cliente['Valor_Total'] ?? 0;
            $totales['pedidos_totales'] += $cliente['Total_Pedidos'] ?? 0;
        }
        
        // Pasar variables a la vista
        $fechaInicio = null;
        $fechaFin = null;
        $periodo = 'todo';
        
        include "views/admin/layout_admin.php";
    }

    /**
     * REPORTE DE PRODUCTOS - SIN FILTRO DE FECHA (TODOS LOS PRODUCTOS)
     */
    public function productos_todos() {
        // NO aplicar filtros de fecha - obtener TODOS los productos
        $periodo = 'todo';
        $categoria = $_GET['categoria'] ?? '';
        $orden = $_GET['orden'] ?? 'ventas_desc';
        
        // Usar fechas muy amplias para cubrir todo el historial
        $fechaInicio = '2000-01-01';
        $fechaFin = date('Y-m-d');
        
        // Obtener TODOS los productos
        $productos = $this->reporteVentaModel->obtenerVentasPorProducto($fechaInicio, $fechaFin, $categoria, $orden);
        $categorias = $this->reporteVentaModel->obtenerCategorias();
        
        // VERIFICAR Y CONVERTIR DATOS NUEVAMENTE (doble seguridad)
        foreach ($productos as &$producto) {
            $producto['Cantidad_Vendida'] = (float)($producto['Cantidad_Vendida'] ?? 0);
            $producto['Total_Ventas'] = (float)($producto['Total_Ventas'] ?? 0);
            $producto['Precio_Promedio'] = (float)($producto['Precio_Promedio'] ?? 0);
            $producto['Pedidos'] = (float)($producto['Pedidos'] ?? 0);
            $producto['Descuentos_Otorgados'] = (float)($producto['Descuentos_Otorgados'] ?? 0);
            $producto['Ventas_Netas'] = (float)($producto['Ventas_Netas'] ?? 0);
        }
        
        // Calcular totales globales
        $totales = [
            'ventas_totales' => 0,
            'productos_vendidos' => 0,
            'pedidos' => 0
        ];
        
        foreach ($productos as $producto) {
            $totales['ventas_totales'] += $producto['Total_Ventas'] ?? 0;
            $totales['productos_vendidos'] += $producto['Cantidad_Vendida'] ?? 0;
            $totales['pedidos'] += $producto['Pedidos'] ?? 0;
        }
        
        // Pasar variables a la vista
        $fechaInicio = null;
        $fechaFin = null;
        $periodo = 'todo';
        
        include "views/admin/layout_admin.php";
    }

    // 📈 REPORTE DETALLADO DE VENTAS
    public function detallado() {
        $periodo = $_GET['periodo'] ?? 'hoy';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        // Ajustar fechas según período
        list($fechaInicio, $fechaFin) = $this->ajustarFechasPorPeriodo($periodo, $fechaInicio, $fechaFin);
        
        // Obtener todas las ventas del período
        $ventas = $this->reporteVentaModel->obtenerVentasDetalladas($fechaInicio, $fechaFin);
        
        // Calcular totales
        $totales = [
            'ventas_brutas' => 0,
            'descuentos' => 0,
            'iva' => 0,
            'ventas_netas' => 0,
            'cantidad_pedidos' => count($ventas),
            'productos_vendidos' => 0
        ];
        
        foreach ($ventas as $venta) {
            $totales['ventas_brutas'] += $venta['Monto_Total'];
            $totales['descuentos'] += $venta['Descuento_Aplicado'] ?? 0;
            $totales['iva'] += $venta['IVA'] ?? 0;
            $totales['ventas_netas'] += ($venta['Monto_Total'] - ($venta['Descuento_Aplicado'] ?? 0));
            $totales['productos_vendidos'] += $venta['Cantidad_Productos'] ?? 0;
        }
        
        // NUEVO: Calcular ganancia neta real y métricas adicionales
        $totales['ganancia_neta_real'] = $totales['ventas_netas'] - $totales['iva'];
        $totales['margen_ganancia'] = $totales['ventas_brutas'] > 0 ? 
            ($totales['ganancia_neta_real'] / $totales['ventas_brutas'] * 100) : 0;
        $totales['ganancia_por_pedido'] = $totales['cantidad_pedidos'] > 0 ? 
            ($totales['ganancia_neta_real'] / $totales['cantidad_pedidos']) : 0;
        $totales['porcentaje_descuento'] = $totales['ventas_brutas'] > 0 ? 
            ($totales['descuentos'] / $totales['ventas_brutas'] * 100) : 0;
        $totales['porcentaje_iva'] = $totales['ventas_brutas'] > 0 ? 
            ($totales['iva'] / $totales['ventas_brutas'] * 100) : 0;
        $totales['ticket_bruto'] = $totales['cantidad_pedidos'] > 0 ? 
            ($totales['ventas_brutas'] / $totales['cantidad_pedidos']) : 0;
        $totales['ticket_neto'] = $totales['ganancia_por_pedido'];
        
        include "views/admin/layout_admin.php";
    }

    // 📦 REPORTE POR PRODUCTO
    public function productos() {
        $periodo = $_GET['periodo'] ?? 'mes_actual';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $categoria = $_GET['categoria'] ?? '';
        $orden = $_GET['orden'] ?? 'ventas_desc';
        
        list($fechaInicio, $fechaFin) = $this->ajustarFechasPorPeriodo($periodo, $fechaInicio, $fechaFin);
        
        $productos = $this->reporteVentaModel->obtenerVentasPorProducto($fechaInicio, $fechaFin, $categoria, $orden);
        $categorias = $this->reporteVentaModel->obtenerCategorias();
        
        // Calcular totales
        $totales = [
            'ventas_totales' => 0,
            'productos_vendidos' => 0,
            'pedidos' => 0
        ];
        
        foreach ($productos as $producto) {
            $totales['ventas_totales'] += $producto['Total_Ventas'] ?? 0;
            $totales['productos_vendidos'] += $producto['Cantidad_Vendida'] ?? 0;
            $totales['pedidos'] += $producto['Pedidos'] ?? 0;
        }
        
        include "views/admin/layout_admin.php";
    }

    // 👥 REPORTE POR CLIENTE
    public function clientes() {
        $periodo = $_GET['periodo'] ?? 'mes_actual';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        $orden = $_GET['orden'] ?? 'monto_desc';
        
        list($fechaInicio, $fechaFin) = $this->ajustarFechasPorPeriodo($periodo, $fechaInicio, $fechaFin);
        
        $clientes = $this->reporteVentaModel->obtenerVentasPorCliente($fechaInicio, $fechaFin, $orden);
        
        // Calcular totales
        $totales = [
            'ventas_totales' => 0,
            'clientes_unicos' => count($clientes),
            'pedidos_totales' => 0
        ];
        
        foreach ($clientes as $cliente) {
            $totales['ventas_totales'] += $cliente['Valor_Total'] ?? 0;
            $totales['pedidos_totales'] += $cliente['Total_Pedidos'] ?? 0;
        }
        
        include "views/admin/layout_admin.php";
    }

    // 💰 REPORTE FINANCIERO (CORREGIDO)
    public function financiero() {
        $periodo = $_GET['periodo'] ?? 'mes_actual';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-t');
        
        list($fechaInicio, $fechaFin) = $this->ajustarFechasPorPeriodo($periodo, $fechaInicio, $fechaFin);
        
        // Obtener datos financieros (usando la función que SÍ existe)
        $financiero = $this->reporteVentaModel->obtenerReporteFinanciero($fechaInicio, $fechaFin);
        
        // Obtener evolución mensual (esta función SÍ existe)
        $evolucionMensual = $this->reporteVentaModel->obtenerEvolucionMensual(date('Y'), $fechaInicio, $fechaFin);
        
        // Obtener ventas por categoría para gráfico (esta función SÍ existe)
        $ventasPorCategoria = $this->reporteVentaModel->obtenerVentasPorCategoria($fechaInicio, $fechaFin);
        
        // Obtener ventas por método de pago (esta función SÍ existe)
        $ventasPorMetodoPago = $this->reporteVentaModel->obtenerVentasPorMetodoPago($fechaInicio, $fechaFin);
        
        // Preparar datos para gráficos (sin comparativa)
        $datosGrafico = $this->prepararDatosParaGraficosFinanciero($ventasPorCategoria, $ventasPorMetodoPago, $evolucionMensual);
        
        include "views/admin/layout_admin.php";
    }

    // 📤 EXPORTAR REPORTES (CORREGIDO - SIN PDF)
    public function exportar() {
        $tipo = $_GET['tipo'] ?? 'ventas';
        $formato = $_GET['formato'] ?? 'excel';
        $periodo = $_GET['periodo'] ?? 'hoy';
        $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-d');
        $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
        
        // ELIMINAR OPCIÓN PDF - SIEMPRE EXPORTAR A EXCEL
        $formato = 'excel';
        
        list($fechaInicio, $fechaFin) = $this->ajustarFechasPorPeriodo($periodo, $fechaInicio, $fechaFin);
        
        switch ($tipo) {
            case 'ventas':
                $datos = $this->reporteVentaModel->obtenerVentasDetalladas($fechaInicio, $fechaFin);
                $nombre = "reporte_ventas_detalladas_" . date('Ymd_His');
                $columnas = [
                    'ID_Factura' => 'ID Factura',
                    'Codigo_Acceso' => 'Código',
                    'Cliente' => 'Cliente',
                    'Fecha_Factura' => 'Fecha',
                    'Monto_Total' => 'Total Bruto',
                    'Subtotal' => 'Subtotal',
                    'IVA' => 'IVA',
                    'Metodo_Pago' => 'Método Pago',
                    'Estado' => 'Estado',
                    'Cantidad_Productos' => 'Productos',
                    'Descuento_Aplicado' => 'Descuento',
                    'Neto' => 'Total Neto'
                ];
                break;
                
            case 'productos':
                $datos = $this->reporteVentaModel->obtenerVentasPorProducto($fechaInicio, $fechaFin, '', 'ventas_desc');
                $nombre = "reporte_productos_" . date('Ymd_His');
                $columnas = [
                    'Producto' => 'Producto',
                    'Categoria' => 'Categoría',
                    'Genero' => 'Género',
                    'Cantidad_Vendida' => 'Cantidad Vendida',
                    'Total_Ventas' => 'Ventas Totales',
                    'Precio_Promedio' => 'Precio Promedio',
                    'Pedidos' => 'Pedidos',
                    'Descuentos_Otorgados' => 'Descuentos',
                    'Ventas_Netas' => 'Ventas Netas'
                ];
                break;
                
            case 'clientes':
                $datos = $this->reporteVentaModel->obtenerVentasPorCliente($fechaInicio, $fechaFin, 'monto_desc');
                $nombre = "reporte_clientes_" . date('Ymd_His');
                $columnas = [
                    'Cliente' => 'Cliente',
                    'Correo' => 'Correo',
                    'Celular' => 'Celular',
                    'Total_Pedidos' => 'Total Pedidos',
                    'Valor_Total' => 'Valor Total',
                    'Ticket_Promedio' => 'Ticket Promedio',
                    'Ultima_Compra' => 'Última Compra',
                    'Primera_Compra' => 'Primera Compra',
                    'Productos_Comprados' => 'Productos Comprados',
                    'Descuentos_Obtenidos' => 'Descuentos Obtenidos'
                ];
                break;
                
            case 'financiero':
                $datos = $this->reporteVentaModel->obtenerReporteFinancieroCompleto($fechaInicio, $fechaFin);
                $nombre = "reporte_financiero_" . date('Ymd_His');
                $columnas = [
                    'Concepto' => 'Concepto',
                    'Monto' => 'Monto',
                    'Detalle' => 'Detalle'
                ];
                break;
                
            case 'categorias':
                $datos = $this->reporteVentaModel->obtenerVentasPorCategoria($fechaInicio, $fechaFin);
                $nombre = "reporte_categorias_" . date('Ymd_His');
                $columnas = [
                    'N_Categoria' => 'Categoría',
                    'Pedidos' => 'Pedidos',
                    'Productos_Vendidos' => 'Productos Vendidos',
                    'Total_Ventas' => 'Ventas Totales',
                    'Precio_Promedio' => 'Precio Promedio'
                ];
                break;
                
            case 'generos':
                $datos = $this->reporteVentaModel->obtenerVentasPorGenero($fechaInicio, $fechaFin);
                $nombre = "reporte_generos_" . date('Ymd_His');
                $columnas = [
                    'Genero' => 'Género',
                    'Pedidos' => 'Pedidos',
                    'Productos_Vendidos' => 'Productos Vendidos',
                    'Total_Ventas' => 'Ventas Totales',
                    'Precio_Promedio' => 'Precio Promedio',
                    'Clientes_Unicos' => 'Clientes Únicos'
                ];
                break;
                
            case 'colores':
                $datos = $this->reporteVentaModel->obtenerVentasPorColor($fechaInicio, $fechaFin, 50);
                $nombre = "reporte_colores_" . date('Ymd_His');
                $columnas = [
                    'Color' => 'Color',
                    'CodigoHex' => 'Código HEX',
                    'Pedidos' => 'Pedidos',
                    'Productos_Vendidos' => 'Productos Vendidos',
                    'Total_Ventas' => 'Ventas Totales',
                    'Precio_Promedio' => 'Precio Promedio',
                    'Clientes_Unicos' => 'Clientes Únicos',
                    'Articulos_Distintos' => 'Artículos Distintos'
                ];
                break;
                
            default:
                $datos = [];
                $columnas = [];
                $nombre = "reporte_" . date('Ymd_His');
        }
        
        // SIEMPRE EXPORTAR A EXCEL (SE ELIMINÓ PDF)
        $this->exportarExcelMejorado($datos, $nombre, $tipo, $fechaInicio, $fechaFin, $columnas);
    }

    // 🔄 AJUSTAR FECHAS SEGÚN PERIODO (ACTUALIZADO)
    private function ajustarFechasPorPeriodo($periodo, $fechaInicio, $fechaFin) {
        date_default_timezone_set('America/Bogota');
        
        $hoy = date('Y-m-d');
        $ayer = date('Y-m-d', strtotime('-1 day'));
        
        switch ($periodo) {
            case 'hoy':
                return [$hoy, $hoy];
                
            case 'ayer':
                return [$ayer, $ayer];
                
            case 'semana_actual':
                // Lunes de esta semana (considerando que la semana empieza el lunes)
                $lunes = date('Y-m-d', strtotime('monday this week'));
                return [$lunes, $hoy];
                
            case 'semana_pasada':
                $lunesPasado = date('Y-m-d', strtotime('monday last week'));
                $domingoPasado = date('Y-m-d', strtotime('sunday last week'));
                return [$lunesPasado, $domingoPasado];
                
            case 'mes_actual':
                $inicioMes = date('Y-m-01');
                return [$inicioMes, $hoy];
                
            case 'mes_pasado':
                $mes = date('n') - 1;
                $ano = date('Y');
                
                if ($mes == 0) {
                    $mes = 12;
                    $ano = $ano - 1;
                }
                
                $inicio = sprintf('%04d-%02d-01', $ano, $mes);
                $ultimoDia = date('t', strtotime($inicio));
                $fin = sprintf('%04d-%02d-%02d', $ano, $mes, $ultimoDia);
                return [$inicio, $fin];
                
            case 'trimestre_actual':
                $mesActual = date('n');
                $trimestre = ceil($mesActual / 3);
                $mesInicio = (($trimestre - 1) * 3) + 1;
                $mesFin = $mesActual; // Solo hasta el mes actual
                
                $inicio = date('Y') . '-' . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . '-01';
                return [$inicio, $hoy];
                
            case 'semestre_actual':
                $mesActual = date('n');
                $semestre = ceil($mesActual / 6);
                $mesInicio = (($semestre - 1) * 6) + 1;
                
                $inicio = date('Y') . '-' . str_pad($mesInicio, 2, '0', STR_PAD_LEFT) . '-01';
                return [$inicio, $hoy];
                
            case 'ano_actual':
                $inicio = date('Y-01-01');
                return [$inicio, $hoy];
                
            case 'ano_pasado':
                $anoPasado = date('Y') - 1;
                return [$anoPasado . '-01-01', $anoPasado . '-12-31'];
                
            case 'personalizado':
                // Validar y formatear fechas personalizadas
                if (empty($fechaInicio)) $fechaInicio = $hoy;
                if (empty($fechaFin)) $fechaFin = $hoy;
                
                $fechaInicio = date('Y-m-d', strtotime($fechaInicio));
                $fechaFin = date('Y-m-d', strtotime($fechaFin));
                
                return [$fechaInicio, $fechaFin];
                
            default:
                return [$hoy, $hoy];
        }
    }

    // 📊 PREPARAR DATOS PARA GRÁFICOS (COMPLETO)
    private function prepararDatosParaGraficos($ventasDiarias, $ventasSemanales, $ventasMensuales, $ventasTrimestrales, $ventasAnuales,
                                              $ventasPorCategoria, $ventasPorGenero, $ventasPorColor,
                                              $comparativaMensual, $comparativaAnual) {
        
        // Datos para gráfico de ventas diarias
        $datosDiarios = $this->prepararDatosVentasDiarias($ventasDiarias);
        
        // Datos para gráfico de ventas semanales
        $datosSemanales = $this->prepararDatosVentasSemanales($ventasSemanales);
        
        // Datos para gráfico de ventas mensuales
        $datosMensuales = $this->prepararDatosVentasMensuales($ventasMensuales);
        
        // Datos para gráfico de ventas trimestrales
        $datosTrimestrales = $this->prepararDatosVentasTrimestrales($ventasTrimestrales);
        
        // Datos para gráfico de ventas anuales
        $datosAnuales = $this->prepararDatosVentasAnuales($ventasAnuales);
        
        // Datos para gráfico de categorías
        $datosCategoria = $this->prepararDatosCategoria($ventasPorCategoria);
        
        // Datos para gráfico de géneros
        $datosGenero = $this->prepararDatosGenero($ventasPorGenero);
        
        // Datos para gráfico de colores
        $datosColor = $this->prepararDatosColor($ventasPorColor);
        
        // Datos para gráfico de comparativa
        $datosComparativa = $this->prepararDatosComparativa($comparativaMensual, $comparativaAnual);
        
        // Agregar información de período a los datos
        $fechaInicioDiarias = count($ventasDiarias) > 0 ? reset($ventasDiarias)['Fecha'] : date('Y-m-d');
        $fechaFinDiarias = count($ventasDiarias) > 0 ? end($ventasDiarias)['Fecha'] : date('Y-m-d');
        
        $datosDiarios['periodo'] = 'Últimos 30 días';
        $datosSemanales['periodo'] = 'Últimas 12 semanas';
        $datosMensuales['periodo'] = 'Últimos 12 meses';
        $datosTrimestrales['periodo'] = 'Últimos 4 años';
        $datosAnuales['periodo'] = 'Últimos 5 años';
        
        return [
            'linea' => $datosDiarios,
            'semanal' => $datosSemanales,
            'mensual' => $datosMensuales,
            'trimestral' => $datosTrimestrales,
            'anual' => $datosAnuales,
            'categoria' => $datosCategoria,
            'genero' => $datosGenero,
            'color' => $datosColor,
            'comparativa' => $datosComparativa
        ];
    }

    private function prepararDatosVentasDiarias($ventasDiarias) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas Diarias',
                    'data' => [],
                    'borderColor' => '#3A4A6B',
                    'backgroundColor' => 'rgba(58, 74, 107, 0.1)',
                    'tension' => 0.4,
                    'fill' => true,
                    'borderWidth' => 2
                ],
                [
                    'label' => 'Pedidos',
                    'data' => [],
                    'borderColor' => '#1B202D',
                    'backgroundColor' => 'rgba(27, 32, 45, 0.1)',
                    'tension' => 0.4,
                    'fill' => false,
                    'borderWidth' => 2,
                    'yAxisID' => 'y1'
                ]
            ]
        ];
        
        foreach ($ventasDiarias as $dia) {
            $datos['labels'][] = date('d/m', strtotime($dia['Fecha']));
            $datos['datasets'][0]['data'][] = (float)$dia['Monto_Total'];
            $datos['datasets'][1]['data'][] = (float)$dia['Pedidos'];
        }
        
        return $datos;
    }

    private function prepararDatosVentasSemanales($ventasSemanales) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas Semanales',
                    'data' => [],
                    'borderColor' => '#4A5B7D',
                    'backgroundColor' => 'rgba(74, 91, 125, 0.2)',
                    'tension' => 0.3,
                    'fill' => true,
                    'borderWidth' => 3
                ]
            ]
        ];
        
        foreach ($ventasSemanales as $semana) {
            $datos['labels'][] = 'Sem ' . date('W', strtotime($semana['Fecha_Inicio_Semana']));
            $datos['datasets'][0]['data'][] = (float)$semana['Monto_Total'];
        }
        
        return $datos;
    }

    private function prepararDatosVentasMensuales($ventasMensuales) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas Mensuales',
                    'data' => [],
                    'borderColor' => '#2A3448',
                    'backgroundColor' => 'rgba(42, 52, 72, 0.2)',
                    'tension' => 0.2,
                    'fill' => true,
                    'borderWidth' => 3
                ]
            ]
        ];
        
        foreach ($ventasMensuales as $mes) {
            $datos['labels'][] = date('M Y', strtotime($mes['Mes'] . '-01'));
            $datos['datasets'][0]['data'][] = (float)$mes['Monto_Total'];
        }
        
        return $datos;
    }

    private function prepararDatosVentasTrimestrales($ventasTrimestrales) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas Trimestrales',
                    'data' => [],
                    'borderColor' => '#1B202D',
                    'backgroundColor' => 'rgba(27, 32, 45, 0.2)',
                    'tension' => 0.2,
                    'fill' => true,
                    'borderWidth' => 3
                ]
            ]
        ];
        
        foreach ($ventasTrimestrales as $trimestre) {
            $datos['labels'][] = 'T' . $trimestre['Trimestre'] . ' ' . $trimestre['Ano'];
            $datos['datasets'][0]['data'][] = (float)$trimestre['Monto_Total'];
        }
        
        return $datos;
    }

    private function prepararDatosVentasAnuales($ventasAnuales) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas Anuales',
                    'data' => [],
                    'borderColor' => '#1B202D',
                    'backgroundColor' => 'rgba(27, 32, 45, 0.2)',
                    'tension' => 0.1,
                    'fill' => true,
                    'borderWidth' => 4
                ]
            ]
        ];
        
        foreach ($ventasAnuales as $ano) {
            $datos['labels'][] = $ano['Ano'];
            $datos['datasets'][0]['data'][] = (float)$ano['Monto_Total'];
        }
        
        return $datos;
    }

    private function prepararDatosCategoria($ventasPorCategoria) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [
                        '#1B202D', '#2A3448', '#3A4A6B', '#4A5B7D', '#5B6E8F',
                        '#6C819F', '#7D94AF', '#8EA7BF', '#9FBACF', '#B0CDDF'
                    ],
                    'borderWidth' => 2,
                    'borderColor' => '#fff'
                ]
            ]
        ];
        
        foreach ($ventasPorCategoria as $categoria) {
            if ($categoria['Total_Ventas'] > 0) {
                $datos['labels'][] = $categoria['N_Categoria'];
                $datos['datasets'][0]['data'][] = (float)$categoria['Total_Ventas'];
            }
        }
        
        return $datos;
    }

    private function prepararDatosGenero($ventasPorGenero) {
        $coloresGenero = [
            'Hombre' => '#3498db',
            'Mujer' => '#e83e8c',
            'Niños' => '#f39c12'
        ];
        
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [],
                    'borderWidth' => 2,
                    'borderColor' => '#fff'
                ]
            ]
        ];
        
        foreach ($ventasPorGenero as $genero) {
            if ($genero['Total_Ventas'] > 0) {
                $datos['labels'][] = $genero['Genero'];
                $datos['datasets'][0]['data'][] = (float)$genero['Total_Ventas'];
                $datos['datasets'][0]['backgroundColor'][] = $coloresGenero[$genero['Genero']] ?? '#6c757d';
            }
        }
        
        return $datos;
    }

    private function prepararDatosColor($ventasPorColor) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [],
                    'borderWidth' => 2,
                    'borderColor' => '#fff'
                ]
            ]
        ];
        
        foreach ($ventasPorColor as $color) {
            if ($color['Total_Ventas'] > 0) {
                $datos['labels'][] = $color['Color'];
                $datos['datasets'][0]['data'][] = (float)$color['Total_Ventas'];
                $datos['datasets'][0]['backgroundColor'][] = $color['CodigoHex'];
            }
        }
        
        return $datos;
    }

    private function prepararDatosComparativa($comparativaMensual, $comparativaAnual) {
        return [
            'mes_vs_mes' => $this->prepararComparativaMensual($comparativaMensual),
            'ano_vs_ano' => $this->prepararComparativaAnual($comparativaAnual)
        ];
    }

    private function prepararComparativaMensual($comparativaMensual) {
        $datos = [
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            'datasets' => []
        ];
        
        $anos = array_unique(array_column($comparativaMensual, 'Ano'));
        $colores = ['#3A4A6B', '#4A5B7D', '#5B6E8F', '#6C819F'];
        
        foreach ($anos as $index => $ano) {
            $dataset = [
                'label' => $ano,
                'data' => array_fill(0, 12, 0),
                'backgroundColor' => $colores[$index % count($colores)],
                'borderColor' => $colores[$index % count($colores)],
                'borderWidth' => 2
            ];
            
            foreach ($comparativaMensual as $mes) {
                if ($mes['Ano'] == $ano) {
                    $dataset['data'][$mes['Mes'] - 1] = (float)$mes['Monto_Total'];
                }
            }
            
            $datos['datasets'][] = $dataset;
        }
        
        return $datos;
    }

    private function prepararComparativaAnual($comparativaAnual) {
        $datos = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas Anuales',
                    'data' => [],
                    'backgroundColor' => '#3A4A6B',
                    'borderColor' => '#2A3448',
                    'borderWidth' => 2
                ]
            ]
        ];
        
        foreach ($comparativaAnual as $ano) {
            $datos['labels'][] = $ano['Ano'];
            $datos['datasets'][0]['data'][] = (float)$ano['Monto_Total'];
        }
        
        return $datos;
    }

    // 📊 PREPARAR DATOS PARA GRÁFICOS FINANCIEROS
    private function prepararDatosParaGraficosFinanciero($ventasPorCategoria, $ventasPorMetodoPago, $evolucionMensual) {
        // Datos para gráfico de barras (ventas por categoría)
        $datosBarras = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas por Categoría',
                    'data' => [],
                    'backgroundColor' => '#3A4A6B',
                    'borderColor' => '#2A3448',
                    'borderWidth' => 1
                ]
            ]
        ];
        
        foreach ($ventasPorCategoria as $categoria) {
            if ($categoria['Total_Ventas'] > 0) {
                $datosBarras['labels'][] = $categoria['N_Categoria'];
                $datosBarras['datasets'][0]['data'][] = (float)$categoria['Total_Ventas'];
            }
        }
        
        // Datos para gráfico de torta (ventas por método de pago)
        $datosMetodoPago = [
            'labels' => [],
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [
                        '#1B202D', '#2A3448', '#3A4A6B', '#4A5B7D', '#5B6E8F'
                    ],
                    'borderWidth' => 1,
                    'borderColor' => '#fff'
                ]
            ]
        ];
        
        foreach ($ventasPorMetodoPago as $metodo) {
            $datosMetodoPago['labels'][] = $metodo['Metodo_Pago'];
            $datosMetodoPago['datasets'][0]['data'][] = (float)$metodo['Valor_Total'];
        }
        
        // Datos para gráfico de líneas (evolución mensual)
        $datosEvolucion = [
            'labels' => [],
            'datasets' => [
                [
                    'label' => 'Ventas Mensuales',
                    'data' => [],
                    'borderColor' => '#4A5B7D',
                    'backgroundColor' => 'rgba(74, 91, 125, 0.1)',
                    'tension' => 0.4,
                    'fill' => true
                ]
            ]
        ];
        
        foreach ($evolucionMensual as $mes) {
            $datosEvolucion['labels'][] = $mes['Nombre_Mes'];
            $datosEvolucion['datasets'][0]['data'][] = (float)$mes['Ventas_Netas'];
        }
        
        return [
            'barras' => $datosBarras,
            'metodo_pago' => $datosMetodoPago,
            'evolucion' => $datosEvolucion
        ];
    }

    // 📄 EXPORTAR A EXCEL MEJORADO (CORREGIDO EL TOTAL GENERAL)
    private function exportarExcelMejorado($datos, $nombreArchivo, $tipo, $fechaInicio, $fechaFin, $columnas) {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.xls"');
        
        echo '<!DOCTYPE html>';
        echo '<html>';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">';
        echo '<title>' . $nombreArchivo . '</title>';
        echo '<style>';
        echo '@page { margin: 0.5in; }';
        echo 'body { font-family: Arial, sans-serif; margin: 0; padding: 0; color: #000000; }';
        echo '.header { background-color: #1B202D; padding: 15px; margin-bottom: 10px; }';
        echo '.header h2 { margin: 0; color: #000000; font-size: 20px; font-weight: bold; }';
        echo '.header p { margin: 5px 0 0 0; color: #000000; }';
        echo '.periodo { background-color: #2A3448; padding: 12px; margin: 10px 0; }';
        echo '.periodo strong { color: #000000; }';
        echo 'table { border-collapse: collapse; width: 100%; margin-bottom: 15px; }';
        echo 'th { background-color: #2A3448; color: #ffffff; font-weight: bold; padding: 12px 10px; text-align: left; border: 1px solid #000000; font-size: 12px; }';
        echo 'td { padding: 10px; border: 1px solid #000000; font-size: 11px; vertical-align: top; color: #000000; }';
        echo '.total-row { background-color: #3A4A6B; color: #000000; font-weight: bold; }';
        echo '.total-row td { border: 1px solid #000000; }';
        echo '.resumen { background-color: #ffffff; padding: 15px; margin-top: 20px; border: 2px solid #000000; }';
        echo '.resumen h4 { color: #000000; margin-top: 0; border-bottom: 2px solid #000000; padding-bottom: 5px; }';
        echo '.estadistica { background-color: #3A4A6B; padding: 15px; margin: 15px 0; }';
        echo '.valor-destacado { color: #000000; font-weight: bold; font-size: 18px; margin: 5px 0; }';
        echo '.titulo-seccion { background-color: #2A3448; color: #000000; padding: 12px; margin: 20px 0 10px 0; font-weight: bold; font-size: 14px; text-align: left; }';
        echo '.comentario { font-style: italic; color: #000000; font-size: 10px; padding: 10px; background-color: #ffffff; border: 1px solid #000000; }';
        echo '.nota { background-color: #ffffff; padding: 10px; margin: 10px 0; border-left: 4px solid #000000; color: #000000; }';
        echo '.tabla-datos { background-color: #ffffff; }';
        echo '.tabla-datos tr:nth-child(even) { background-color: #f0f0f0; }';
        echo '.celda-moneda { text-align: right; font-family: "Courier New", monospace; color: #000000; }';
        echo '.celda-numero { text-align: center; color: #000000; }';
        echo '.celda-fecha { text-align: center; color: #000000; }';
        echo '.pie-pagina { margin-top: 20px; padding-top: 10px; border-top: 3px solid #000000; color: #000000; font-size: 10px; }';
        echo '.pie-pagina td { padding: 5px; }';
        echo '.campo-destacado { background-color: #3A4A6B; color: #000000; padding: 3px 6px; border-radius: 3px; font-weight: bold; }';
        echo '.color-codigo { font-family: monospace; font-weight: bold; color: #000000; background-color: #f0f0f0; padding: 2px 5px; border-radius: 3px; }';
        echo '.badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; color: #000000; }';
        echo '.badge-entregado { background-color: #2A3448; }';
        echo '.badge-preparando { background-color: #3A4A6B; }';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        
        // ============================================
        // ENCABEZADO DEL REPORTE
        // ============================================
        echo '<div class="header">';
        echo '<table width="100%">';
        echo '<tr>';
        echo '<td width="70%">';
        echo '<h2>📊 TuLook - Sistema de Gestión Comercial</h2>';
        echo '<p>Reporte de ' . ucfirst($tipo) . ' - Exportado a Excel</p>';
        echo '</td>';
        echo '<td width="30%" style="text-align: right; vertical-align: top;">';
        echo '<p style="margin: 0;"><strong>Generado:</strong> ' . date('d/m/Y H:i:s') . '</p>';
        echo '<p style="margin: 0;"><strong>Usuario:</strong> ' . ($_SESSION['nombre'] ?? 'Administrador') . '</p>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
        
        // ============================================
        // PERÍODO Y FILTROS
        // ============================================
        $diasAnalizados = max(1, round((strtotime($fechaFin) - strtotime($fechaInicio)) / (60 * 60 * 24)) + 1);
        
        echo '<div class="periodo">';
        echo '<table width="100%" cellpadding="5" border="0">';
        echo '<tr>';
        echo '<td width="50%" valign="top">';
        echo '<strong>📅 PERÍODO ANALIZADO:</strong><br>';
        echo '• Inicio: ' . date('d/m/Y', strtotime($fechaInicio)) . '<br>';
        echo '• Fin: ' . date('d/m/Y', strtotime($fechaFin)) . '<br>';
        echo '• Días: ' . $diasAnalizados . ' días<br>';
        echo '</td>';
        echo '<td width="50%" valign="top">';
        echo '<strong>📋 CONFIGURACIÓN:</strong><br>';
        echo '• Tipo: ' . ucfirst($tipo) . '<br>';
        echo '• Formato: Excel (.xls)<br>';
        echo '• Exportado: ' . date('d/m/Y H:i:s') . '<br>';
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
        
        // ============================================
        // ESTADÍSTICAS RÁPIDAS
        // ============================================
        if (!empty($datos)) {
            $totalRegistros = count($datos);
            $totalGeneral = 0;
            $totalProductos = 0;
            $totalPedidos = 0;
            $totalDescuentos = 0;
            
            // Calcular totales según el tipo de reporte
            foreach ($datos as $fila) {
                if ($tipo === 'ventas' && isset($fila['Monto_Total']) && is_numeric($fila['Monto_Total'])) {
                    $totalGeneral += $fila['Monto_Total'];
                    $totalPedidos++;
                    if (isset($fila['Cantidad_Productos'])) $totalProductos += $fila['Cantidad_Productos'];
                    if (isset($fila['Descuento_Aplicado'])) $totalDescuentos += $fila['Descuento_Aplicado'];
                } elseif ($tipo === 'productos' && isset($fila['Total_Ventas'])) {
                    $totalGeneral += $fila['Total_Ventas'];
                    if (isset($fila['Cantidad_Vendida'])) $totalProductos += $fila['Cantidad_Vendida'];
                    if (isset($fila['Pedidos'])) $totalPedidos += $fila['Pedidos'];
                } elseif ($tipo === 'clientes' && isset($fila['Valor_Total'])) {
                    $totalGeneral += $fila['Valor_Total'];
                    if (isset($fila['Total_Pedidos'])) $totalPedidos += $fila['Total_Pedidos'];
                } elseif (in_array($tipo, ['categorias', 'generos', 'colores']) && isset($fila['Total_Ventas'])) {
                    $totalGeneral += $fila['Total_Ventas'];
                    if (isset($fila['Pedidos'])) $totalPedidos += $fila['Pedidos'];
                    if (isset($fila['Productos_Vendidos'])) $totalProductos += $fila['Productos_Vendidos'];
                }
            }
            
            echo '<div class="estadistica">';
            echo '<table width="100%" cellpadding="10" border="0">';
            echo '<tr>';
            
            echo '<td width="25%" style="text-align: center; border-right: 2px solid #000000;">';
            echo '<div class="valor-destacado">' . number_format($totalRegistros) . '</div>';
            echo '<div style="font-size: 12px;">REGISTROS TOTALES</div>';
            echo '</td>';
            
            echo '<td width="25%" style="text-align: center; border-right: 2px solid #000000;">';
            echo '<div class="valor-destacado">$' . number_format($totalGeneral, 2, ',', '.') . '</div>';
            echo '<div style="font-size: 12px;">VALOR TOTAL</div>';
            echo '</td>';
            
            if ($totalProductos > 0) {
                echo '<td width="25%" style="text-align: center; border-right: 2px solid #000000;">';
                echo '<div class="valor-destacado">' . number_format($totalProductos) . '</div>';
                echo '<div style="font-size: 12px;">PRODUCTOS</div>';
                echo '</td>';
                
                echo '<td width="25%" style="text-align: center;">';
                echo '<div class="valor-destacado">' . ($totalRegistros > 0 ? number_format($totalGeneral / $totalRegistros, 2, ',', '.') : '0') . '</div>';
                echo '<div style="font-size: 12px;">PROMEDIO POR REGISTRO</div>';
                echo '</td>';
            } else {
                echo '<td width="50%" style="text-align: center;" colspan="2">';
                echo '<div class="valor-destacado">' . ($totalRegistros > 0 ? number_format($totalGeneral / $totalRegistros, 2, ',', '.') : '0') . '</div>';
                echo '<div style="font-size: 12px;">PROMEDIO POR REGISTRO</div>';
                echo '</td>';
            }
            
            echo '</tr>';
            echo '</table>';
            echo '</div>';
        }
        
        // ============================================
        // TABLA DE DATOS PRINCIPAL (OCULTANDO ID_FACTURA)
        // ============================================
        if (!empty($datos)) {
            // TÍTULO CENTRADO
            echo '<div class="titulo-seccion">📋 DATOS DETALLADOS DEL REPORTE</div>';
            
            echo '<table border="1" class="tabla-datos" style="border: 2px solid #000000; width: 100%;">';
            
            // Encabezados (excluyendo ID_Factura) - ÚNICOS con texto blanco
            echo '<tr>';
            $columnasVisibles = 0;
            foreach ($columnas as $key => $nombre) {
                // Ocultar ID_Factura del usuario
                if ($key === 'ID_Factura') continue;
                
                echo '<th>' . htmlspecialchars($nombre) . '</th>';
                $columnasVisibles++;
            }
            echo '</tr>';
            
            // Datos
            foreach ($datos as $fila) {
                echo '<tr>';
                foreach ($columnas as $key => $nombre) {
                    // Ocultar ID_Factura del usuario
                    if ($key === 'ID_Factura') continue;
                    
                    $valor = $fila[$key] ?? '';
                    $claseCelda = '';
                    
                    // Formatear valores numéricos
                    if (in_array($key, ['Monto_Total', 'Subtotal', 'IVA', 'Descuento_Aplicado', 'Neto', 
                                        'Total_Ventas', 'Precio_Promedio', 'Ventas_Netas', 'Valor_Total', 
                                        'Ticket_Promedio', 'Descuentos_Obtenidos', 'Monto', 'Valor_Total'])) {
                        if (is_numeric($valor)) {
                            $valor = '$' . number_format($valor, 2, ',', '.');
                            $claseCelda = 'celda-moneda';
                        }
                    }
                    // Formatear cantidades
                    elseif (in_array($key, ['Cantidad_Productos', 'Cantidad_Vendida', 'Productos_Vendidos', 
                                        'Pedidos', 'Total_Pedidos', 'Articulos_Distintos', 'Clientes_Unicos'])) {
                        if (is_numeric($valor)) {
                            $valor = number_format($valor, 0, '', '.');
                            $claseCelda = 'celda-numero';
                        }
                    }
                    // Formatear fechas
                    elseif (in_array($key, ['Fecha_Factura', 'Fecha', 'Ultima_Compra', 'Primera_Compra']) && !empty($valor)) {
                        $valor = date('d/m/Y H:i', strtotime($valor));
                        $claseCelda = 'celda-fecha';
                    }
                    // Formatear porcentajes
                    elseif (strpos(strtolower($nombre), '%') !== false && is_numeric($valor)) {
                        $valor = number_format($valor, 2) . '%';
                        $claseCelda = 'celda-numero';
                    }
                    
                    // Formato especial para códigos
                    if ($key === 'Codigo_Acceso' || $key === 'Código') {
                        $valor = '<span class="color-codigo">' . htmlspecialchars($valor) . '</span>';
                    }
                    
                    // Formato especial para estados
                    if ($key === 'Estado') {
                        if ($valor === 'Entregado') {
                            $valor = '<span class="badge badge-entregado">' . htmlspecialchars($valor) . '</span>';
                        } elseif ($valor === 'Preparando') {
                            $valor = '<span class="badge badge-preparando">' . htmlspecialchars($valor) . '</span>';
                        }
                    }
                    
                    // Formato especial para colores HEX
                    if ($key === 'CodigoHex' && !empty($valor)) {
                        $valor = '<span style="font-family: monospace; font-weight: bold;">' . htmlspecialchars($valor) . '</span>';
                    }
                    
                    echo '<td class="' . $claseCelda . '">' . $valor . '</td>';
                }
                echo '</tr>';
            }
            
            // Fila de total si aplica
            if ($totalGeneral > 0) {
                echo '<tr class="total-row">';
                $colspan = $columnasVisibles - 1; // Dejar espacio para la última columna
                
                echo '<td colspan="' . $colspan . '" style="text-align: right; font-size: 13px;">';
                echo '<strong>TOTAL GENERAL DEL REPORTE:</strong>';
                echo '</td>';
                echo '<td style="text-align: right; font-size: 14px;">';
                echo '<strong>$' . number_format($totalGeneral, 2, ',', '.') . '</strong>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
            
            // ============================================
            // ANÁLISIS ESTADÍSTICO (EN TABLA ORGANIZADA)
            // ============================================
            echo '<div class="titulo-seccion">📈 ANÁLISIS ESTADÍSTICO</div>';
            
            echo '<table border="1" style="width: 100%; border: 2px solid #000000; background-color: #ffffff;">';
            echo '<tr>';
            echo '<td style="padding: 15px; border: 1px solid #000000;">';
            
            if ($tipo === 'ventas') {
                echo '<table width="100%" border="0" cellpadding="5">';
                echo '<tr>';
                echo '<td width="50%" valign="top">';
                echo '<strong>ESTADÍSTICAS DE VENTAS:</strong><br>';
                
                // Calcular estadísticas específicas para ventas
                $ventasPorDia = [];
                $ventasPorMetodo = [];
                $ventasPorEstado = [];
                
                foreach ($datos as $venta) {
                    $fecha = date('Y-m-d', strtotime($venta['Fecha_Factura'] ?? ''));
                    if (!isset($ventasPorDia[$fecha])) {
                        $ventasPorDia[$fecha] = 0;
                    }
                    $ventasPorDia[$fecha] += $venta['Monto_Total'] ?? 0;
                    
                    $metodo = $venta['Metodo_Pago'] ?? 'N/A';
                    if (!isset($ventasPorMetodo[$metodo])) {
                        $ventasPorMetodo[$metodo] = 0;
                    }
                    $ventasPorMetodo[$metodo] += $venta['Monto_Total'] ?? 0;
                    
                    $estado = $venta['Estado'] ?? 'N/A';
                    if (!isset($ventasPorEstado[$estado])) {
                        $ventasPorEstado[$estado] = 0;
                    }
                    $ventasPorEstado[$estado] += $venta['Monto_Total'] ?? 0;
                }
                
                echo '• Total días con ventas: ' . count($ventasPorDia) . '<br>';
                echo '• Promedio diario: $' . number_format($totalGeneral / max(1, count($ventasPorDia)), 2, ',', '.') . '<br>';
                echo '• Venta promedio: $' . number_format($totalGeneral / max(1, $totalRegistros), 2, ',', '.') . '<br>';
                
                if (!empty($ventasPorMetodo)) {
                    echo '• Distribución por método de pago:<br>';
                    foreach ($ventasPorMetodo as $metodo => $valor) {
                        $porcentaje = ($valor / $totalGeneral) * 100;
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;' . htmlspecialchars($metodo) . ': $' . number_format($valor, 2, ',', '.') . 
                            ' (' . number_format($porcentaje, 1) . '%)<br>';
                    }
                }
                
                echo '</td>';
                echo '<td width="50%" valign="top">';
                echo '<strong>ANÁLISIS POR ESTADO:</strong><br>';
                
                if (!empty($ventasPorEstado)) {
                    foreach ($ventasPorEstado as $estado => $valor) {
                        $porcentaje = ($valor / $totalGeneral) * 100;
                        echo '• ' . htmlspecialchars($estado) . ': $' . number_format($valor, 2, ',', '.') . 
                            ' (' . number_format($porcentaje, 1) . '%)<br>';
                    }
                }
                
                echo '<br><strong>DESGLOSE TEMPORAL:</strong><br>';
                if (!empty($ventasPorDia)) {
                    ksort($ventasPorDia);
                    $primerDia = key($ventasPorDia);
                    $ultimoDia = array_key_last($ventasPorDia);
                    echo '• Primera venta: ' . date('d/m/Y', strtotime($primerDia)) . '<br>';
                    echo '• Última venta: ' . date('d/m/Y', strtotime($ultimoDia)) . '<br>';
                    echo '• Días con actividad: ' . count($ventasPorDia) . ' de ' . $diasAnalizados . '<br>';
                }
                
                echo '</td>';
                echo '</tr>';
                echo '</table>';
                
            } elseif ($tipo === 'productos') {
                echo '<strong>ESTADÍSTICAS DE PRODUCTOS:</strong><br>';
                
                // Calcular estadísticas para productos
                $productosPorCategoria = [];
                $productosPorGenero = [];
                
                foreach ($datos as $producto) {
                    $categoria = $producto['Categoria'] ?? 'N/A';
                    if (!isset($productosPorCategoria[$categoria])) {
                        $productosPorCategoria[$categoria] = 0;
                    }
                    $productosPorCategoria[$categoria] += $producto['Total_Ventas'] ?? 0;
                    
                    $genero = $producto['Genero'] ?? 'N/A';
                    if (!isset($productosPorGenero[$genero])) {
                        $productosPorGenero[$genero] = 0;
                    }
                    $productosPorGenero[$genero] += $producto['Total_Ventas'] ?? 0;
                }
                
                echo '• Total productos diferentes: ' . $totalRegistros . '<br>';
                echo '• Total unidades vendidas: ' . number_format($totalProductos) . '<br>';
                echo '• Ventas promedio por producto: $' . number_format($totalGeneral / max(1, $totalRegistros), 2, ',', '.') . '<br>';
                echo '• Unidades promedio por producto: ' . number_format($totalProductos / max(1, $totalRegistros), 1) . '<br>';
                
                if (!empty($productosPorCategoria)) {
                    echo '<br><strong>DISTRIBUCIÓN POR CATEGORÍA:</strong><br>';
                    foreach ($productosPorCategoria as $categoria => $valor) {
                        $porcentaje = ($valor / $totalGeneral) * 100;
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;' . htmlspecialchars($categoria) . ': $' . number_format($valor, 2, ',', '.') . 
                            ' (' . number_format($porcentaje, 1) . '%)<br>';
                    }
                }
                
                if (!empty($productosPorGenero)) {
                    echo '<br><strong>DISTRIBUCIÓN POR GÉNERO:</strong><br>';
                    foreach ($productosPorGenero as $genero => $valor) {
                        $porcentaje = ($valor / $totalGeneral) * 100;
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;' . htmlspecialchars($genero) . ': $' . number_format($valor, 2, ',', '.') . 
                            ' (' . number_format($porcentaje, 1) . '%)<br>';
                    }
                }
                
            } elseif ($tipo === 'clientes') {
                echo '<strong>ESTADÍSTICAS DE CLIENTES:</strong><br>';
                
                echo '• Total clientes únicos: ' . $totalRegistros . '<br>';
                echo '• Total pedidos realizados: ' . number_format($totalPedidos) . '<br>';
                echo '• Pedidos promedio por cliente: ' . number_format($totalPedidos / max(1, $totalRegistros), 1) . '<br>';
                echo '• Ticket promedio por cliente: $' . number_format($totalGeneral / max(1, $totalRegistros), 2, ',', '.') . '<br>';
                echo '• Valor promedio por pedido: $' . number_format($totalGeneral / max(1, $totalPedidos), 2, ',', '.') . '<br>';
                
                // Identificar cliente top
                if (!empty($datos)) {
                    $clienteTop = $datos[0];
                    $clienteUltimo = end($datos);
                    
                    echo '<br><strong>CLIENTE DESTACADO:</strong><br>';
                    echo '• Nombre: ' . htmlspecialchars($clienteTop['Cliente'] ?? 'N/A') . '<br>';
                    echo '• Valor total: $' . number_format($clienteTop['Valor_Total'] ?? 0, 2, ',', '.') . '<br>';
                    echo '• Pedidos realizados: ' . ($clienteTop['Total_Pedidos'] ?? 0) . '<br>';
                    echo '• Porcentaje del total: ' . 
                        number_format((($clienteTop['Valor_Total'] ?? 0) / $totalGeneral) * 100, 1) . '%<br>';
                }
                
            } else {
                // Para otros tipos de reportes
                echo '<strong>ESTADÍSTICAS DEL REPORTE:</strong><br>';
                echo '• Total registros: ' . $totalRegistros . '<br>';
                echo '• Valor total: $' . number_format($totalGeneral, 2, ',', '.') . '<br>';
                
                if ($totalProductos > 0) {
                    echo '• Productos totales: ' . number_format($totalProductos) . '<br>';
                }
                
                if ($totalPedidos > 0) {
                    echo '• Pedidos totales: ' . number_format($totalPedidos) . '<br>';
                }
                
                echo '• Promedio por registro: $' . number_format($totalGeneral / max(1, $totalRegistros), 2, ',', '.') . '<br>';
                echo '• Promedio diario: $' . number_format($totalGeneral / max(1, $diasAnalizados), 2, ',', '.') . '<br>';
                
                // Distribución por elemento principal
                if ($tipo === 'categorias') {
                    echo '<br><strong>DISTRIBUCIÓN POR CATEGORÍA:</strong><br>';
                    foreach ($datos as $item) {
                        $porcentaje = (($item['Total_Ventas'] ?? 0) / $totalGeneral) * 100;
                        echo '• ' . htmlspecialchars($item['N_Categoria'] ?? 'N/A') . ': $' . 
                            number_format($item['Total_Ventas'] ?? 0, 2, ',', '.') . 
                            ' (' . number_format($porcentaje, 1) . '%)<br>';
                    }
                } elseif ($tipo === 'generos') {
                    echo '<br><strong>DISTRIBUCIÓN POR GÉNERO:</strong><br>';
                    foreach ($datos as $item) {
                        $porcentaje = (($item['Total_Ventas'] ?? 0) / $totalGeneral) * 100;
                        echo '• ' . htmlspecialchars($item['Genero'] ?? 'N/A') . ': $' . 
                            number_format($item['Total_Ventas'] ?? 0, 2, ',', '.') . 
                            ' (' . number_format($porcentaje, 1) . '%)<br>';
                    }
                } elseif ($tipo === 'colores') {
                    echo '<br><strong>DISTRIBUCIÓN POR COLOR:</strong><br>';
                    foreach ($datos as $item) {
                        $porcentaje = (($item['Total_Ventas'] ?? 0) / $totalGeneral) * 100;
                        echo '• ' . htmlspecialchars($item['Color'] ?? 'N/A') . ' (' . 
                            htmlspecialchars($item['CodigoHex'] ?? '') . '): $' . 
                            number_format($item['Total_Ventas'] ?? 0, 2, ',', '.') . 
                            ' (' . number_format($porcentaje, 1) . '%)<br>';
                    }
                }
            }
            
            echo '</td>';
            echo '</tr>';
            echo '</table>';
            
            // ============================================
            // RESUMEN ESTADÍSTICO COMPLETO (EN TABLA ORGANIZADA)
            // ============================================
            echo '<div class="titulo-seccion" style="margin-top: 20px;">📋 RESUMEN ESTADÍSTICO COMPLETO</div>';
            
            echo '<table border="1" style="width: 100%; border: 2px solid #000000; background-color: #ffffff;">';
            echo '<tr>';
            echo '<td style="padding: 15px; border: 1px solid #000000;">';
            
            echo '<table width="100%" border="0">';
            echo '<tr>';
            echo '<td width="50%" valign="top">';
            echo '<strong>📊 DATOS GENERALES:</strong><br>';
            echo '• Registros totales: ' . number_format($totalRegistros) . '<br>';
            echo '• Valor total reportado: $' . number_format($totalGeneral, 2, ',', '.') . '<br>';
            
            if ($totalProductos > 0) {
                echo '• Productos totales: ' . number_format($totalProductos) . '<br>';
            }
            
            if ($totalPedidos > 0) {
                echo '• Pedidos totales: ' . number_format($totalPedidos) . '<br>';
            }
            
            if ($totalDescuentos > 0) {
                echo '• Descuentos aplicados: $' . number_format($totalDescuentos, 2, ',', '.') . '<br>';
            }
            
            echo '• Promedio por registro: $' . number_format($totalGeneral / max(1, $totalRegistros), 2, ',', '.') . '<br>';
            echo '• Promedio diario: $' . number_format($totalGeneral / max(1, $diasAnalizados), 2, ',', '.') . '<br>';
            echo '</td>';
            
            echo '<td width="50%" valign="top">';
            echo '<strong>📅 INFORMACIÓN DEL PERÍODO:</strong><br>';
            echo '• Fecha inicio: ' . date('d/m/Y', strtotime($fechaInicio)) . '<br>';
            echo '• Fecha fin: ' . date('d/m/Y', strtotime($fechaFin)) . '<br>';
            echo '• Días analizados: ' . $diasAnalizados . ' días<br>';
            echo '• Fecha de generación: ' . date('d/m/Y H:i:s') . '<br>';
            echo '• Período completo: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin)) . '<br>';
            echo '</td>';
            echo '</tr>';
            
            echo '<tr>';
            echo '<td colspan="2" style="padding-top: 15px;">';
            echo '<strong>ℹ️ METADATOS DEL REPORTE:</strong><br>';
            echo '• Tipo de reporte: ' . ucfirst($tipo) . '<br>';
            echo '• Formato de exportación: Excel (.xls)<br>';
            echo '• Usuario generador: ' . ($_SESSION['nombre'] ?? 'Administrador') . '</br>';
            echo '• Sistema origen: TuLook - Sistema de Gestión Comercial<br>';
            echo '• Versión del reporte: 2.0 - Estadísticas mejoradas<br>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';
            
            echo '<div class="nota" style="margin-top: 15px;">';
            echo '<strong>📝 NOTAS IMPORTANTES:</strong><br>';
            echo '1. Este reporte fue generado automáticamente por el sistema TuLook.<br>';
            echo '2. Los valores monetarios están expresados en pesos colombianos (COP $).<br>';
            echo '3. Las fechas y horas están en zona horaria America/Bogotá.<br>';
            echo '4. Para consultas, aclaraciones o reporte de errores, contacte al administrador del sistema.<br>';
            echo '5. Los datos están sujetos a verificación y pueden variar según ajustes posteriores.<br>';
            echo '</div>';
            
            echo '</td>';
            echo '</tr>';
            echo '</table>';
            
        } else {
            echo '<table border="1" style="width: 100%; border: 2px solid #000000; background-color: #ffffff;">';
            echo '<tr>';
            echo '<td style="padding: 30px; text-align: center;">';
            echo '<h3>⚠️ No hay datos para el período seleccionado</h3>';
            echo '<p>No se encontraron registros para el período solicitado:</p>';
            echo '<p><strong>' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin)) . '</strong></p>';
            echo '<p>Por favor, intente con un período diferente o verifique los filtros aplicados.</p>';
            echo '</td>';
            echo '</tr>';
            echo '</table>';
        }
        
        // ============================================
        // PIE DE PÁGINA
        // ============================================
        echo '<div class="pie-pagina">';
        echo '<table width="100%">';
        echo '<tr>';
        echo '<td width="60%">';
        echo '<strong>TuLook - Sistema de Gestión Comercial</strong><br>';
        echo 'Reporte generado automáticamente | Todos los derechos reservados';
        echo '</td>';
        echo '<td width="40%" align="right">';
        echo 'Página 1 de 1<br>';
        echo 'Documento: ' . $nombreArchivo . '.xls<br>';
        echo date('d/m/Y H:i:s');
        echo '</td>';
        echo '</tr>';
        echo '</table>';
        echo '</div>';
        
        echo '</body>';
        echo '</html>';
        exit;
    }

    // 📄 EXPORTAR A PDF (ELIMINADO - NO FUNCIONA)
    private function exportarPDF($datos, $nombreArchivo, $tipo, $fechaInicio, $fechaFin) {
        // ESTA FUNCIÓN HA SIDO ELIMINADA PORQUE NO FUNCIONA
        // Mostrar mensaje de error
        echo '<script>alert("La exportación a PDF no está disponible. Por favor, use la opción Excel."); window.history.back();</script>';
        exit;
    }
}
?>