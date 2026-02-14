<?php
// views/admin/reporte/index.php
?>
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-line me-2"></i>Reportes de Ventas</h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>?c=ReporteVenta&a=detallado" class="btn btn-primary-dark">
                <i class="fas fa-list me-1"></i> Detalle de Ventas del Día
            </a>
            <button type="button" class="btn btn-primary-dark" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="fas fa-file-export me-1"></i> Exportar como Excel
            </button>
        </div>
    </div>

    <!-- Filtros de período -->
    <div class="card mb-4">
        <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white"><i class="fas fa-filter me-2"></i>Filtrar por Período</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=ReporteVenta&a=index" method="get" class="row g-3 align-items-end" id="filtroForm">
                <input type="hidden" name="c" value="ReporteVenta">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-4">
                    <label for="periodo" class="form-label">Período</label>
                    <select class="form-select" id="periodo" name="periodo" onchange="toggleCustomDates()">
                        <option value="hoy" <?= ($periodo ?? '') === 'hoy' ? 'selected' : '' ?>>Hoy</option>
                        <option value="ayer" <?= ($periodo ?? '') === 'ayer' ? 'selected' : '' ?>>Ayer</option>
                        <option value="semana_actual" <?= ($periodo ?? '') === 'semana_actual' ? 'selected' : '' ?>>Semana Actual</option>
                        <option value="semana_pasada" <?= ($periodo ?? '') === 'semana_pasada' ? 'selected' : '' ?>>Semana Pasada</option>
                        <option value="mes_actual" <?= ($periodo ?? '') === 'mes_actual' ? 'selected' : '' ?>>Mes Actual</option>
                        <option value="mes_pasado" <?= ($periodo ?? '') === 'mes_pasado' ? 'selected' : '' ?>>Mes Pasado</option>
                        <option value="trimestre_actual" <?= ($periodo ?? '') === 'trimestre_actual' ? 'selected' : '' ?>>Trimestre Actual</option>
                        <option value="semestre_actual" <?= ($periodo ?? '') === 'semestre_actual' ? 'selected' : '' ?>>Semestre Actual</option>
                        <option value="ano_actual" <?= ($periodo ?? '') === 'ano_actual' ? 'selected' : '' ?>>Año Actual</option>
                        <option value="ano_pasado" <?= ($periodo ?? '') === 'ano_pasado' ? 'selected' : '' ?>>Año Pasado</option>
                        <option value="personalizado" <?= ($periodo ?? '') === 'personalizado' ? 'selected' : '' ?>>Personalizado</option>
                    </select>
                </div>
                
                <div class="col-md-3 custom-date" style="<?= ($periodo ?? '') === 'personalizado' ? '' : 'display: none;' ?>">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" 
                        class="form-control" 
                        id="fecha_inicio" 
                        name="fecha_inicio" 
                        value="<?= htmlspecialchars($fechaInicio ?? date('Y-m-d')) ?>">
                </div>
                
                <div class="col-md-3 custom-date" style="<?= ($periodo ?? '') === 'personalizado' ? '' : 'display: none;' ?>">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" 
                        class="form-control" 
                        id="fecha_fin" 
                        name="fecha_fin" 
                        value="<?= htmlspecialchars($fechaFin ?? date('Y-m-d')) ?>">
                </div>
                
                <div class="col-md-2">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary-dark flex-grow-1">
                            <i class="fas fa-search me-1"></i> Aplicar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="limpiarFiltros()" title="Limpiar filtros">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </form>
            
            <div class="mt-3">
                <span class="badge bg-primary-dark">Período: <?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?></span>
                <span class="badge bg-info ms-2"><?= ucfirst(str_replace('_', ' ', $periodo ?? 'hoy')) ?></span>
            </div>
            
            <!-- Menú de navegación rápida -->
            <div class="mt-4">
                <div class="card">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap justify-content-center gap-2" id="menu-navegacion-rapida">
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="navegarASeccion('top-productos')">
                                <i class="fas fa-trophy me-1"></i> Top Productos
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="navegarASeccion('top-clientes')">
                                <i class="fas fa-user-friends me-1"></i> Top Clientes
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="navegarASeccion('perdidas-descuentos')">
                                <i class="fas fa-percentage me-1"></i> Descuentos
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="navegarASeccion('metodos-pago')">
                                <i class="fas fa-credit-card me-1"></i> Métodos Pago
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="navegarASeccion('analisis-genero')">
                                <i class="fas fa-venus-mars me-1"></i> Análisis Género
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill" onclick="navegarASeccion('top-colores')">
                                <i class="fas fa-palette me-1"></i> Top Colores
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- views/admin/reporte/index.php - REPARACIÓN COMPLETA -->
    <!-- PRIMERA FILA DE MÉTRICAS - CON GANANCIA NETA REAL Y VENTAS NETAS -->
    <div class="row mb-4">
        <!-- GANANCIA NETA REAL - DESTACADA -->
        <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--success); background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="card-body d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center">
                    <div class="w-100">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge" style="background-color: var(--success) !important; color: white; padding: 6px 12px;">
                                <i class="fas fa-crown me-1"></i> MÉTRICA CLAVE
                            </span>
                            <span class="badge" style="background-color: var(--primary-dark) !important; color: white;">
                                <?= ucfirst(str_replace('_', ' ', $periodo ?? 'hoy')) ?>
                            </span>
                        </div>
                        <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px;">
                            <i class="fas fa-chart-line me-2"></i>GANANCIA NETA REAL
                        </h5>
                        <h2 class="card-text" style="font-size: 2.5rem; font-weight: 700; color: var(--success); line-height: 1.2; word-break: break-word;">
                            $<?= number_format($metricas['ganancia_neta_real'] ?? 0, 2) ?>
                        </h2>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                            <small class="badge" style="background-color: rgba(42, 52, 72, 0.1); color: var(--primary-dark); padding: 5px 10px; font-size: 0.75rem;">
                                <i class="fas fa-arrow-down me-1" style="color: var(--danger);"></i> - Descuentos
                            </small>
                            <small class="badge" style="background-color: rgba(42, 52, 72, 0.1); color: var(--primary-dark); padding: 5px 10px; font-size: 0.75rem;">
                                <i class="fas fa-percentage me-1" style="color: var(--warning);"></i> - IVA
                            </small>
                            <small class="badge" style="background-color: var(--success); color: white; padding: 5px 10px; font-size: 0.75rem;">
                                <i class="fas fa-check-circle me-1"></i> = Ingreso Real
                            </small>
                        </div>
                        <?php if (($metricas['margen_neto'] ?? 0) > 0): ?>
                        <div class="mt-2">
                            <span style="font-size: 0.85rem; color: var(--primary-dark);">
                                <i class="fas fa-chart-pie me-1"></i> Margen neto: <strong style="color: var(--success);"><?= number_format($metricas['margen_neto'] ?? 0, 1) ?>%</strong>
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="d-none d-lg-block text-center ms-3">
                        <div style="width: 80px; height: 80px; background: rgba(42, 52, 72, 0.05); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px dashed var(--success);">
                            <i class="fas fa-coins" style="font-size: 2.5rem; color: var(--success);"></i>
                        </div>
                        <small class="d-block mt-2" style="color: var(--gray-dark); font-size: 0.7rem;">Ganancia/pedido</small>
                        <strong style="color: var(--success); font-size: 1rem;">$<?= number_format($metricas['ganancia_promedio'] ?? 0, 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ventas Totales -->
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Ventas Totales</h5>
                        <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary); word-break: break-word;">
                            $<?= number_format($metricas['ventas_totales'] ?? 0, 2) ?>
                        </h3>
                        <small style="color: var(--gray-dark);"><i class="fas fa-money-bill-wave" style="color: var(--primary);"></i> Brutas</small>
                    </div>
                    <div class="rounded-circle p-2" style="background: rgba(42, 52, 72, 0.1);">
                        <i class="fas fa-sack-dollar" style="font-size: 1.3rem; color: var(--primary-dark);"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Ventas Netas (CORREGIDO: ESTO SÍ ESTABA ELIMINADO) -->
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Ventas Netas</h5>
                        <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary); word-break: break-word;">
                            $<?= number_format($metricas['ventas_netas'] ?? 0, 2) ?>
                        </h3>
                        <small style="color: var(--gray-dark);"><i class="fas fa-calculator" style="color: var(--primary);"></i> Después de descuentos</small>
                    </div>
                    <div class="rounded-circle p-2" style="background: rgba(42, 52, 72, 0.1);">
                        <i class="fas fa-file-invoice-dollar" style="font-size: 1.3rem; color: var(--primary-dark);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SEGUNDA FILA DE MÉTRICAS - CORREGIDA CON VARIABLES CSS -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Pedidos</h5>
                        <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary);">
                            <?= $metricas['total_pedidos'] ?? 0 ?>
                        </h3>
                        <small style="color: var(--gray-dark);"><i class="fas fa-shopping-cart" style="color: var(--primary);"></i> Procesados</small>
                    </div>
                    <div>
                        <span class="badge" style="background-color: var(--primary-light) !important; color: white; padding: 6px 10px;">
                            <i class="fas fa-box me-1"></i> <?= $metricas['productos_vendidos'] ?? 0 ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Ticket Promedio</h5>
                    <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary); word-break: break-word;">
                        $<?= number_format($metricas['ticket_promedio'] ?? 0, 2) ?>
                    </h3>
                    <small style="color: var(--gray-dark);"><i class="fas fa-chart-bar" style="color: var(--primary);"></i> Por pedido</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Descuentos Otorgados</h5>
                    <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--danger); word-break: break-word;">
                        -$<?= number_format($metricas['total_descuentos'] ?? 0, 2) ?>
                    </h3>
                    <small style="color: var(--gray-dark);"><i class="fas fa-tag" style="color: var(--danger);"></i> Pérdida por promociones</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">IVA Recaudado</h5>
                    <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--warning); word-break: break-word;">
                        $<?= number_format($metricas['total_iva'] ?? 0, 2) ?>
                    </h3>
                    <small style="color: var(--gray-dark);"><i class="fas fa-percentage" style="color: var(--warning);"></i> Impuestos</small>
                </div>
            </div>
        </div>
    </div>

    <!-- TERCERA FILA - CLIENTES ÚNICOS Y PRODUCTOS VENDIDOS -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Productos Vendidos</h5>
                    <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary);">
                        <?= $metricas['productos_vendidos'] ?? 0 ?>
                    </h3>
                    <small style="color: var(--gray-dark);"><i class="fas fa-box" style="color: var(--primary);"></i> Unidades totales</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Clientes Únicos</h5>
                    <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary);">
                        <?= $metricas['clientes_unicos'] ?? 0 ?>
                    </h3>
                    <small style="color: var(--gray-dark);"><i class="fas fa-users" style="color: var(--primary);"></i> Compradores distintos</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Productos x Pedido</h5>
                    <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary);">
                        <?= number_format($metricas['productos_por_pedido'] ?? 0, 1) ?>
                    </h3>
                    <small style="color: var(--gray-dark);"><i class="fas fa-layer-group" style="color: var(--primary);"></i> Promedio</small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="card stats-card-pedido h-100">
                <div class="card-body">
                    <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.85rem; text-transform: uppercase;">Tasa de Conversión</h5>
                    <h3 class="card-text" style="font-size: 1.8rem; font-weight: 600; color: var(--primary);">
                        <?= number_format($metricas['tasa_conversion'] ?? 0, 1) ?>
                    </h3>
                    <small style="color: var(--gray-dark);"><i class="fas fa-exchange-alt" style="color: var(--primary);"></i> Pedidos/cliente</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-line me-2"></i>Tendencia de Ventas</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-light active" onclick="cambiarVistaGrafico('diaria')">
                            <i class="fas fa-calendar-day me-1"></i> Diaria
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cambiarVistaGrafico('semanal')">
                            <i class="fas fa-calendar-week me-1"></i> Semanal
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cambiarVistaGrafico('mensual')">
                            <i class="fas fa-calendar-alt me-1"></i> Mensual
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cambiarVistaGrafico('trimestral')">
                            <i class="fas fa-chart-bar me-1"></i> Trimestral
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cambiarVistaGrafico('anual')">
                            <i class="fas fa-calendar me-1"></i> Anual
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="ventasChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-pie me-2"></i>Análisis por Categoría</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-light active" onclick="cambiarTipoGrafico('categoria')">
                            <i class="fas fa-tags me-1"></i> Categorías
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cambiarTipoGrafico('genero')">
                            <i class="fas fa-venus-mars me-1"></i> Géneros
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cambiarTipoGrafico('color')">
                            <i class="fas fa-palette me-1"></i> Colores
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="categoriaChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparativas -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-chart-bar me-2"></i>Comparativa de Ventas</h5>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cargarComparativa('dia_vs_dia')">
                            <i class="fas fa-calendar-day me-1"></i> Día vs Día
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cargarComparativa('semana_vs_semana')">
                            <i class="fas fa-calendar-week me-1"></i> Semana vs Semana
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cargarComparativa('mes_vs_mes')">
                            <i class="fas fa-calendar-alt me-1"></i> Mes vs Mes
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="cargarComparativa('ano_vs_ano')">
                            <i class="fas fa-calendar me-1"></i> Año vs Año
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="comparativaChart" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!-- TABLAS UNA DEBAJO DE LA OTRA - 100% DE ANCHO -->
    <!-- ======================================================= -->

    <!-- 1. Top 10 Productos Más Vendidos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card h-100" id="top-productos">
                <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-trophy me-2"></i>Top 10 Productos Más Vendidos</h5>
                    <a href="<?= BASE_URL ?>?c=ReporteVenta&a=productos" class="btn btn-sm btn-outline-light">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-primary-dark">
                                <tr>
                                    <th class="ps-3">Producto</th>
                                    <th>Categoría</th>
                                    <th>Género</th>
                                    <th class="text-end">Cantidad</th>
                                    <th class="text-end">Ventas</th>
                                    <th class="text-end pe-3">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $totalVentasProductos = array_sum(array_column($productosMasVendidos, 'Ventas_Totales'));
                                foreach (array_slice($productosMasVendidos, 0, 10) as $producto): 
                                    $porcentaje = $totalVentasProductos > 0 ? ($producto['Ventas_Totales'] / $totalVentasProductos * 100) : 0;
                                ?>
                                <tr>
                                    <td class="ps-3"><?= htmlspecialchars($producto['Producto']) ?></td>
                                    <td><?= htmlspecialchars($producto['Categoria']) ?></td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($producto['Genero'] ?? 'N/A') ?></span>
                                    </td>
                                    <td class="text-end"><?= number_format($producto['Cantidad_Vendida']) ?></td>
                                    <td class="text-end text-primary-dark fw-bold">
                                        $<?= number_format($producto['Ventas_Totales'], 2) ?>
                                    </td>
                                    <td class="text-end pe-3">
                                        <span class="badge bg-success"><?= number_format($porcentaje, 1) ?>%</span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($productosMasVendidos)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="ps-3 text-end fw-bold">Total:</td>
                                    <td class="text-end fw-bold"><?= number_format(array_sum(array_column(array_slice($productosMasVendidos, 0, 10), 'Cantidad_Vendida'))) ?></td>
                                    <td class="text-end fw-bold text-primary-dark">$<?= number_format($totalVentasProductos, 2) ?></td>
                                    <td class="pe-3 text-end">100%</td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Top 10 Clientes Frecuentes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card h-100" id="top-clientes">
                <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-white"><i class="fas fa-user-friends me-2"></i>Top 10 Clientes Frecuentes</h5>
                    <a href="<?= BASE_URL ?>?c=ReporteVenta&a=clientes" class="btn btn-sm btn-outline-light">Ver todos</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-primary-dark">
                                <tr>
                                    <th class="ps-3">Cliente</th>
                                    <th class="text-end">Pedidos</th>
                                    <th class="text-end">Valor Total</th>
                                    <th class="text-end">Ticket Promedio</th>
                                    <th class="text-end pe-3">Última Compra</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($clientesFrecuentes, 0, 10) as $cliente): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold"><?= htmlspecialchars($cliente['Cliente']) ?></div>
                                        <small class="text-muted d-block"><?= htmlspecialchars($cliente['Correo']) ?></small>
                                    </td>
                                    <td class="text-end align-middle">
                                        <span class="badge bg-primary rounded-pill"><?= $cliente['Total_Pedidos'] ?></span>
                                    </td>
                                    <td class="text-end align-middle text-primary-dark fw-bold">
                                        $<?= number_format($cliente['Valor_Total'], 2) ?>
                                    </td>
                                    <td class="text-end align-middle">
                                        $<?= number_format($cliente['Ticket_Promedio'], 2) ?>
                                    </td>
                                    <td class="text-end align-middle pe-3">
                                        <?= date('d/m/Y', strtotime($cliente['Ultima_Compra'])) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <?php if (!empty($clientesFrecuentes)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="ps-3 text-end fw-bold">Totales:</td>
                                    <td class="text-end fw-bold"><?= array_sum(array_column(array_slice($clientesFrecuentes, 0, 10), 'Total_Pedidos')) ?></td>
                                    <td class="text-end fw-bold text-primary-dark">$<?= number_format(array_sum(array_column(array_slice($clientesFrecuentes, 0, 10), 'Valor_Total')), 2) ?></td>
                                    <td class="text-end fw-bold">$<?= number_format(array_sum(array_column(array_slice($clientesFrecuentes, 0, 10), 'Valor_Total')) / max(1, array_sum(array_column(array_slice($clientesFrecuentes, 0, 10), 'Total_Pedidos'))), 2) ?></td>
                                    <td class="pe-3"></td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Pérdidas por Descuentos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card h-100" id="perdidas-descuentos">
                <div class="card-header bg-primary-dark">
                    <h5 class="mb-0 text-white"><i class="fas fa-percentage me-2"></i>Pérdidas por Descuentos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-primary-dark">
                                <tr>
                                    <th class="ps-3">Código</th>
                                    <th>Tipo</th>
                                    <th class="text-end">Pedidos</th>
                                    <th class="text-end">Total Descuento</th>
                                    <th class="text-end pe-3">% Pérdida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($perdidasDescuentos)): 
                                    $totalDescuentos = array_sum(array_column($perdidasDescuentos, 'Total_Descuento'));
                                    ?>
                                    <?php foreach ($perdidasDescuentos as $descuento): 
                                        $porcentajePerdida = $totalDescuentos > 0 ? ($descuento['Total_Descuento'] / $totalDescuentos * 100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3"><?= htmlspecialchars($descuento['Codigo_Descuento'] ?? 'N/A') ?></td>
                                        <td>
                                            <span class="badge <?= $descuento['Tipo_Descuento'] == 'Porcentaje' ? 'bg-info' : 'bg-warning' ?>">
                                                <?= htmlspecialchars($descuento['Tipo_Descuento']) ?>
                                            </span>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($descuento['Alcance']) ?></small>
                                        </td>
                                        <td class="text-end"><?= $descuento['Pedidos_Afectados'] ?></td>
                                        <td class="text-end text-danger fw-bold">
                                            -$<?= number_format($descuento['Total_Descuento'], 2) ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-danger"><?= number_format($porcentajePerdida, 1) ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-tag fa-2x mb-2"></i>
                                            <p>No hay descuentos aplicados en este período</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($perdidasDescuentos)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="3" class="ps-3 text-end fw-bold">Total Pérdida:</td>
                                    <td class="text-end fw-bold text-danger">-$<?= number_format($totalDescuentos, 2) ?></td>
                                    <td class="pe-3 text-end">100%</td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Ventas por Método de Pago -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card h-100" id="metodos-pago">
                <div class="card-header bg-primary-dark">
                    <h5 class="mb-0 text-white"><i class="fas fa-credit-card me-2"></i>Ventas por Método de Pago</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-primary-dark">
                                <tr>
                                    <th class="ps-3">Método</th>
                                    <th class="text-end">Pedidos</th>
                                    <th class="text-end">Valor Total</th>
                                    <th class="text-end">% Valor</th>
                                    <th class="text-end pe-3">% Pedidos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ventasPorMetodoPago)): 
                                    $totalPedidosMetodo = array_sum(array_column($ventasPorMetodoPago, 'Pedidos'));
                                    $totalValorMetodo = array_sum(array_column($ventasPorMetodoPago, 'Valor_Total'));
                                    ?>
                                    <?php foreach ($ventasPorMetodoPago as $metodo): 
                                        $porcentajePedidos = $totalPedidosMetodo > 0 ? ($metodo['Pedidos'] / $totalPedidosMetodo * 100) : 0;
                                        $porcentajeValor = $totalValorMetodo > 0 ? ($metodo['Valor_Total'] / $totalValorMetodo * 100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <i class="fas fa-<?= $metodo['Metodo_Pago'] == 'Tarjeta' ? 'credit-card' : ($metodo['Metodo_Pago'] == 'Efectivo' ? 'money-bill' : 'university') ?> me-2"></i>
                                            <?= htmlspecialchars($metodo['Metodo_Pago']) ?>
                                        </td>
                                        <td class="text-end"><?= $metodo['Pedidos'] ?></td>
                                        <td class="text-end text-primary-dark fw-bold">
                                            $<?= number_format($metodo['Valor_Total'], 2) ?>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-info"><?= number_format($porcentajeValor, 1) ?>%</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-secondary"><?= number_format($porcentajePedidos, 1) ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-credit-card fa-2x mb-2"></i>
                                            <p>No hay datos de métodos de pago</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($ventasPorMetodoPago)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="ps-3 text-end fw-bold">Totales:</td>
                                    <td class="text-end fw-bold"><?= $totalPedidosMetodo ?></td>
                                    <td class="text-end fw-bold text-primary-dark">$<?= number_format($totalValorMetodo, 2) ?></td>
                                    <td class="text-end fw-bold">100%</td>
                                    <td class="pe-3 text-end fw-bold">100%</td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Análisis por Género -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card h-100" id="analisis-genero">
                <div class="card-header bg-primary-dark">
                    <h5 class="mb-0 text-white"><i class="fas fa-venus-mars me-2"></i>Análisis por Género</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-primary-dark">
                                <tr>
                                    <th class="ps-3">Género</th>
                                    <th class="text-end">Pedidos</th>
                                    <th class="text-end">Productos Vendidos</th>
                                    <th class="text-end">Ventas Totales</th>
                                    <th class="text-end pe-3">% Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ventasPorGenero)): 
                                    $totalVentasGenero = array_sum(array_column($ventasPorGenero, 'Total_Ventas'));
                                    ?>
                                    <?php foreach ($ventasPorGenero as $genero): 
                                        $porcentajeGenero = $totalVentasGenero > 0 ? ($genero['Total_Ventas'] / $totalVentasGenero * 100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge <?= $genero['Genero'] == 'Hombre' ? 'bg-primary' : ($genero['Genero'] == 'Mujer' ? 'bg-pink' : 'bg-warning') ?>">
                                                <?= htmlspecialchars($genero['Genero']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?= $genero['Pedidos'] ?></td>
                                        <td class="text-end"><?= $genero['Productos_Vendidos'] ?></td>
                                        <td class="text-end text-primary-dark fw-bold">
                                            $<?= number_format($genero['Total_Ventas'], 2) ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success"><?= number_format($porcentajeGenero, 1) ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-venus-mars fa-2x mb-2"></i>
                                            <p>No hay datos de géneros</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($ventasPorGenero)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="ps-3 text-end fw-bold">Totales:</td>
                                    <td class="text-end fw-bold"><?= array_sum(array_column($ventasPorGenero, 'Pedidos')) ?></td>
                                    <td class="text-end fw-bold"><?= array_sum(array_column($ventasPorGenero, 'Productos_Vendidos')) ?></td>
                                    <td class="text-end fw-bold text-primary-dark">$<?= number_format($totalVentasGenero, 2) ?></td>
                                    <td class="pe-3 text-end fw-bold">100%</td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Top 10 Colores Más Vendidos -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card h-100" id="top-colores">
                <div class="card-header bg-primary-dark">
                    <h5 class="mb-0 text-white"><i class="fas fa-palette me-2"></i>Top 10 Colores Más Vendidos</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-container">
                        <table class="table table-striped table-hover mb-0">
                            <thead class="table-primary-dark">
                                <tr>
                                    <th class="ps-3">Color</th>
                                    <th class="text-end">Productos Vendidos</th>
                                    <th class="text-end">Ventas Totales</th>
                                    <th class="text-end pe-3">% Ventas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($ventasPorColor)): 
                                    $totalVentasColor = array_sum(array_column($ventasPorColor, 'Total_Ventas'));
                                    ?>
                                    <?php foreach (array_slice($ventasPorColor, 0, 10) as $color): 
                                        $porcentajeColor = $totalVentasColor > 0 ? ($color['Total_Ventas'] / $totalVentasColor * 100) : 0;
                                    ?>
                                    <tr>
                                        <td class="ps-3">
                                            <span class="badge" style="background-color: <?= htmlspecialchars($color['CodigoHex']) ?> !important; color: <?= (hexdec(substr($color['CodigoHex'], 1, 2)) > 127 || hexdec(substr($color['CodigoHex'], 3, 2)) > 127 || hexdec(substr($color['CodigoHex'], 5, 2)) > 127) ? '#000' : '#fff' ?>">
                                                <i class="fas fa-square me-1"></i> <?= htmlspecialchars($color['Color']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end"><?= $color['Productos_Vendidos'] ?></td>
                                        <td class="text-end text-primary-dark fw-bold">
                                            $<?= number_format($color['Total_Ventas'], 2) ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-info"><?= number_format($porcentajeColor, 1) ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-palette fa-2x mb-2"></i>
                                            <p>No hay datos de colores</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                            <?php if (!empty($ventasPorColor)): ?>
                            <tfoot class="table-light">
                                <tr>
                                    <td class="ps-3 text-end fw-bold">Totales:</td>
                                    <td class="text-end fw-bold"><?= array_sum(array_column(array_slice($ventasPorColor, 0, 10), 'Productos_Vendidos')) ?></td>
                                    <td class="text-end fw-bold text-primary-dark">$<?= number_format($totalVentasColor, 2) ?></td>
                                    <td class="pe-3 text-end fw-bold">100%</td>
                                </tr>
                            </tfoot>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exportación - ACTUALIZADO SIN PDF -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark text-white">
                <h5 class="modal-title text-black" id="exportModalLabel">
                    <i class="fas fa-file-export me-2"></i>Exportar Reporte a Excel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>?c=ReporteVenta&a=exportar" method="get">
                <input type="hidden" name="c" value="ReporteVenta">
                <input type="hidden" name="a" value="exportar">
                <input type="hidden" name="periodo" value="<?= $periodo ?? 'hoy' ?>">
                <input type="hidden" name="fecha_inicio" value="<?= $fechaInicio ?? date('Y-m-d') ?>">
                <input type="hidden" name="fecha_fin" value="<?= $fechaFin ?? date('Y-m-d') ?>">
                <input type="hidden" name="formato" value="excel">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="exportTipo" class="form-label">Tipo de Reporte</label>
                        <select class="form-select" id="exportTipo" name="tipo" required>
                            <option value="ventas">Ventas Detalladas</option>
                            <option value="productos">Productos Más Vendidos</option>
                            <option value="clientes">Clientes Frecuentes</option>
                            <option value="financiero">Reporte Financiero</option>
                            <option value="categorias">Ventas por Categoría</option>
                            <option value="generos">Ventas por Género</option>
                            <option value="colores">Ventas por Color</option>
                        </select>
                    </div>
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Formato:</strong> Excel (.xls)
                    </div>
                    <div class="alert alert-secondary">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Período a exportar:
                        <strong><?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?></strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary-dark">
                        <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/reporte.css">

<!-- JS y Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>assets/js/reporte.js"></script>

<script>
// Preparar datos para gráficos
var ventasDiariasData = <?= json_encode($datosGrafico['linea'] ?? []) ?>;
var ventasSemanalesData = <?= json_encode($datosGrafico['semanal'] ?? []) ?>;
var ventasMensualesData = <?= json_encode($datosGrafico['mensual'] ?? []) ?>;
var ventasTrimestralesData = <?= json_encode($datosGrafico['trimestral'] ?? []) ?>;
var ventasAnualesData = <?= json_encode($datosGrafico['anual'] ?? []) ?>;

var categoriaData = <?= json_encode($datosGrafico['categoria'] ?? []) ?>;
var generoData = <?= json_encode($datosGrafico['genero'] ?? []) ?>;
var colorData = <?= json_encode($datosGrafico['color'] ?? []) ?>;

var comparativaData = <?= json_encode($datosGrafico['comparativa'] ?? []) ?>;

// Variables globales para control de gráficos
var chartVentas = null;
var chartCategoria = null;
var chartComparativa = null;
var vistaGraficoActual = 'diaria';
var tipoGraficoActual = 'categoria';
var tipoComparativaActual = 'mes_vs_mes';

// Función para mostrar/ocultar fechas personalizadas
function toggleCustomDates() {
    var periodo = document.getElementById('periodo').value;
    var customDates = document.querySelectorAll('.custom-date');
    
    customDates.forEach(function(el) {
        el.style.display = periodo === 'personalizado' ? 'block' : 'none';
    });
}

// Función para limpiar todos los filtros
function limpiarFiltros() {
    // Redirigir a la página sin parámetros de filtro
    window.location.href = "<?= BASE_URL ?>?c=ReporteVenta&a=index";
}

// Función para resetear al período "Hoy"
function resetearAPeriodoHoy() {
    window.location.href = "<?= BASE_URL ?>?c=ReporteVenta&a=index&periodo=hoy";
}

// Configurar fechas por defecto
document.addEventListener('DOMContentLoaded', function() {
    console.log('Inicializando reporte...');
    
    // Configurar fechas mínimas/máximas
    var fechaInicio = document.getElementById('fecha_inicio');
    var fechaFin = document.getElementById('fecha_fin');
    
    if (fechaInicio && fechaFin) {
        fechaInicio.max = fechaFin.value;
        fechaFin.min = fechaInicio.value;
        
        fechaInicio.addEventListener('change', function() {
            fechaFin.min = this.value;
        });
        
        fechaFin.addEventListener('change', function() {
            fechaInicio.max = this.value;
        });
    }
    
    // Inicializar gráficos (con retardo para asegurar que el DOM esté listo)
    setTimeout(function() {
        inicializarGraficos();
    }, 100);
    
    // Inicializar navegación rápida
    inicializarNavegacionRapida();
});

// Función para navegar a secciones específicas
function navegarASeccion(idSeccion) {
    const elemento = document.getElementById(idSeccion);
    if (elemento) {
        // Remover activo de todos los botones
        document.querySelectorAll('#menu-navegacion-rapida .btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-outline-secondary');
        });
        
        // Agregar activo al botón clickeado
        event.target.classList.remove('btn-outline-secondary');
        event.target.classList.add('btn-secondary', 'active');
        
        // Scroll suave a la sección
        elemento.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Agregar efecto visual a la tarjeta
        elemento.classList.add('card-highlight');
        setTimeout(() => {
            elemento.classList.remove('card-highlight');
        }, 2000);
    }
}

// Inicializar navegación rápida
function inicializarNavegacionRapida() {
    // Agregar eventos a los botones del menú
    document.querySelectorAll('#menu-navegacion-rapida .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remover clase activa de todos los botones
            document.querySelectorAll('#menu-navegacion-rapida .btn').forEach(b => {
                b.classList.remove('active', 'btn-secondary');
                b.classList.add('btn-outline-secondary');
            });
            
            // Agregar clase activa al botón clickeado
            this.classList.remove('btn-outline-secondary');
            this.classList.add('btn-secondary', 'active');
        });
    });
}
</script>