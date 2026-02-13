<!-- views/admin/reporte/detallado.php - CORREGIDO COMPLETAMENTE -->
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h2><i class="fas fa-list-alt me-2" style="color: var(--primary-dark);"></i>Reporte Detallado de Ventas del día</h2>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1" style="color: var(--primary);"></i> 
                Período: <?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?>
            </p>
            <p class="text-muted mb-0">
                <i class="fas fa-shopping-cart me-1" style="color: var(--primary);"></i> 
                <?= $totales['cantidad_pedidos'] ?? 0 ?> pedidos 
                | <i class="fas fa-box me-1" style="color: var(--primary);"></i> 
                <?= $totales['productos_vendidos'] ?? 0 ?> productos
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-sm-0">
            <a href="<?= BASE_URL ?>?c=ReporteVenta&a=index" class="btn" style="background-color: var(--primary-dark); color: white; border: none;">
                <i class="fas fa-arrow-left me-1"></i>Volver
            </a>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- PRIMERA FILA: GANANCIA NETA REAL + VENTAS BRUTAS + DESCUENTOS + IVA/NETAS -->
    <!-- ======================================================== -->
    <div class="row mb-4 g-3">
        <!-- GANANCIA NETA REAL - DESTACADA -->
        <div class="col-xl-6 col-lg-12">
            <div class="card stats-card-pedido h-100" style="border-left: 6px solid var(--success); background: linear-gradient(145deg, #ffffff 0%, #f8fff8 100%);">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-start justify-content-between">
                        <div class="w-100">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                <span class="badge" style="background-color: var(--success) !important; color: white; padding: 6px 12px;">
                                    <i class="fas fa-crown me-1"></i> INGRESO REAL
                                </span>
                                <span class="badge" style="background-color: var(--primary-dark) !important; color: white;">
                                    <?= ucfirst(str_replace('_', ' ', $periodo ?? 'hoy')) ?>
                                </span>
                            </div>
                            <h5 class="card-title" style="color: var(--primary-dark); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem;">
                                <i class="fas fa-hand-holding-usd me-2"></i>GANANCIA NETA REAL
                            </h5>
                            <h2 class="card-text" style="font-size: 2.5rem; font-weight: 700; color: var(--success); line-height: 1.1; margin-bottom: 0.5rem;">
                                $<?= number_format($totales['ganancia_neta_real'] ?? 0, 2) ?>
                            </h2>
                            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                <span style="background: rgba(42, 52, 72, 0.08); padding: 4px 10px; border-radius: 30px; font-size: 0.75rem; color: var(--primary-dark);">
                                    <i class="fas fa-check-circle me-1" style="color: var(--success);"></i> Después de descuentos e IVA
                                </span>
                                <span style="background: rgba(42, 52, 72, 0.08); padding: 4px 10px; border-radius: 30px; font-size: 0.75rem; color: var(--primary-dark);">
                                    <i class="fas fa-percentage me-1" style="color: var(--success);"></i> Margen: <?= number_format($totales['margen_ganancia'] ?? 0, 1) ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top" style="border-color: rgba(40, 167, 69, 0.2) !important;">
                        <small style="color: var(--gray-dark);">Ganancia por pedido:</small>
                        <strong style="color: var(--success); font-size: 1.2rem;">$<?= number_format($totales['ganancia_por_pedido'] ?? 0, 2) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas Brutas -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--primary-dark);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">VENTAS BRUTAS</small>
                        <h3 class="card-text mt-1" style="font-size: 1.6rem; font-weight: 700; color: var(--primary-dark); word-break: break-word;">
                            $<?= number_format($totales['ventas_brutas'] ?? 0, 2) ?>
                        </h3>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-arrow-up" style="color: var(--primary-dark);"></i> Antes de descuentos
                        </small>
                        <div style="background: rgba(27, 32, 45, 0.08); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-money-bill-wave" style="font-size: 1.2rem; color: var(--primary-dark);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Descuentos - DANGER -->
        <div class="col-xl-2 col-lg-4 col-md-6">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--danger);">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">DESCUENTOS</small>
                        <h3 class="card-text mt-1" style="font-size: 1.6rem; font-weight: 700; color: var(--danger); word-break: break-word;">
                            -$<?= number_format($totales['descuentos'] ?? 0, 2) ?>
                        </h3>
                    </div>
                    <div class="d-flex justify-content-between align-items-end mt-2">
                        <div>
                            <small style="color: var(--gray-dark);">
                                <i class="fas fa-arrow-down" style="color: var(--danger);"></i> Pérdida
                            </small>
                            <span class="badge ms-1" style="background-color: rgba(220, 53, 69, 0.1); color: var(--danger); font-size: 0.65rem;">
                                <?= number_format($totales['porcentaje_descuento'] ?? 0, 1) ?>%
                            </span>
                        </div>
                        <div style="background: rgba(220, 53, 69, 0.08); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-tag" style="font-size: 1.2rem; color: var(--danger);"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- IVA + Ventas Netas en columna -->
        <div class="col-xl-2 col-lg-4 col-md-12">
            <div class="row g-2 h-100">
                <div class="col-12" style="height: 50%;">
                    <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--warning);">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div>
                                <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.65rem; font-weight: 600;">IVA</small>
                                <h4 class="mb-0 mt-1" style="font-size: 1.2rem; font-weight: 700; color: var(--warning);">
                                    $<?= number_format($totales['iva'] ?? 0, 2) ?>
                                </h4>
                                <small style="color: var(--gray-dark); font-size: 0.65rem;">
                                    <?= number_format($totales['porcentaje_iva'] ?? 0, 1) ?>% de ventas
                                </small>
                            </div>
                            <div style="background: rgba(255, 193, 7, 0.08); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-percentage" style="font-size: 1.1rem; color: var(--warning);"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12" style="height: 50%;">
                    <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--primary);">
                        <div class="card-body d-flex justify-content-between align-items-center p-3">
                            <div>
                                <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.65rem; font-weight: 600;">VENTAS NETAS</small>
                                <h4 class="mb-0 mt-1" style="font-size: 1.2rem; font-weight: 700; color: var(--primary);">
                                    $<?= number_format($totales['ventas_netas'] ?? 0, 2) ?>
                                </h4>
                                <small style="color: var(--gray-dark); font-size: 0.65rem;">
                                    Después de descuentos
                                </small>
                            </div>
                            <div style="background: rgba(42, 52, 72, 0.08); width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-calculator" style="font-size: 1.1rem; color: var(--primary);"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- SEGUNDA FILA: PEDIDOS, PRODUCTOS, TICKET BRUTO, TICKET NETO -->
    <!-- ======================================================== -->
    <div class="row mb-4 g-3">
        <div class="col-lg-3 col-md-6">
            <div class="card stats-card-pedido h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">PEDIDOS</small>
                            <h2 class="card-text mt-1" style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                                <?= $totales['cantidad_pedidos'] ?? 0 ?>
                            </h2>
                        </div>
                        <div style="background: rgba(42, 52, 72, 0.08); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shopping-cart" style="font-size: 1.3rem; color: var(--primary-dark);"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-box me-1" style="color: var(--primary);"></i> 
                            <?= number_format($totales['productos_vendidos'] / max(1, $totales['cantidad_pedidos']), 1) ?> productos/pedido
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stats-card-pedido h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">PRODUCTOS</small>
                            <h2 class="card-text mt-1" style="font-size: 2rem; font-weight: 700; color: var(--primary);">
                                <?= $totales['productos_vendidos'] ?? 0 ?>
                            </h2>
                        </div>
                        <div style="background: rgba(42, 52, 72, 0.08); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-cubes" style="font-size: 1.3rem; color: var(--primary-dark);"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="badge" style="background-color: var(--primary-light); color: white; font-size: 0.7rem; padding: 5px 10px;">
                            <i class="fas fa-tag me-1"></i> Unidades vendidas
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stats-card-pedido h-100">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">TICKET BRUTO</small>
                            <h3 class="card-text mt-1" style="font-size: 1.5rem; font-weight: 700; color: var(--primary-dark); word-break: break-word;">
                                $<?= number_format($totales['ventas_brutas'] / max(1, $totales['cantidad_pedidos']), 2) ?>
                            </h3>
                        </div>
                        <div style="background: rgba(27, 32, 45, 0.08); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-receipt" style="font-size: 1.3rem; color: var(--primary-dark);"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-arrow-up me-1" style="color: var(--primary-dark);"></i> Por pedido
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--success);">
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">TICKET NETO</small>
                            <h3 class="card-text mt-1" style="font-size: 1.5rem; font-weight: 700; color: var(--success); word-break: break-word;">
                                $<?= number_format($totales['ganancia_por_pedido'] ?? 0, 2) ?>
                            </h3>
                        </div>
                        <div style="background: rgba(40, 167, 69, 0.08); width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-coins" style="font-size: 1.3rem; color: var(--success);"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-check-circle me-1" style="color: var(--success);"></i> Ganancia real por pedido
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TABLA DE VENTAS DETALLADAS -->
    <!-- ======================================================== -->
    <div class="card">
        <div class="card-header" style="background-color: var(--primary-dark); color: white;">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="mb-0 text-white"><i class="fas fa-receipt me-2"></i>Detalle de Ventas</h5>
                    <small class="text-white-50"><?= $totales['cantidad_pedidos'] ?? 0 ?> pedidos procesados</small>
                </div>
                <div>
                    <span class="badge me-2" style="background-color: var(--primary-light); color: white; padding: 6px 12px;">
                        <i class="fas fa-box me-1"></i> <?= $totales['productos_vendidos'] ?? 0 ?> productos
                    </span>
                    <button type="button" class="btn btn-sm" style="background-color: transparent; color: white; border: 1px solid rgba(255,255,255,0.3);" onclick="toggleFilters()">
                        <i class="fas fa-filter me-1"></i> Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card-body border-bottom" id="filtersSection" style="display: none;">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label" style="color: var(--primary-dark); font-size: 0.8rem;">Filtrar por estado:</label>
                    <select class="form-select form-select-sm" onchange="filterByEstado(this.value)">
                        <option value="">Todos los estados</option>
                        <option value="Confirmado">Confirmado</option>
                        <option value="Preparando">Preparando</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Entregado">Entregado</option>
                        <option value="Anulado">Anulado</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="color: var(--primary-dark); font-size: 0.8rem;">Filtrar por método de pago:</label>
                    <select class="form-select form-select-sm" onchange="filterByMetodoPago(this.value)">
                        <option value="">Todos los métodos</option>
                        <option value="Tarjeta">Tarjeta</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="color: var(--primary-dark); font-size: 0.8rem;">Búsqueda por cliente:</label>
                    <input type="text" class="form-control form-control-sm" placeholder="Nombre del cliente..." onkeyup="searchByCliente(this.value)">
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <?php if (!empty($ventas)): ?>
                <div class="table-container">
                    <table class="table table-striped table-hover mb-0" id="ventasTable">
                        <thead style="background-color: var(--primary-dark); color: white;">
                            <tr>
                                <th class="ps-3">Factura</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Método Pago</th>
                                <th class="text-end">Bruto</th>
                                <th class="text-end">Descuento</th>
                                <th class="text-end">Neto</th>
                                <th class="text-center pe-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                            <tr class="venta-row" 
                                data-estado="<?= $venta['Estado'] ?>" 
                                data-metodo="<?= $venta['Metodo_Pago'] ?>"
                                data-cliente="<?= htmlspecialchars($venta['Cliente'] ?? '') ?>">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-invoice me-2" style="color: var(--primary-dark);"></i>
                                        <span style="font-family: monospace; font-weight: 600;"><?= htmlspecialchars($venta['Codigo_Acceso'] ?? '') ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($venta['Cliente'] ?? '') ?></div>
                                    <small style="color: var(--gray-dark);">
                                        <i class="fas fa-box me-1" style="font-size: 0.7rem;"></i> <?= $venta['Cantidad_Productos'] ?? 0 ?> productos
                                    </small>
                                </td>
                                <td>
                                    <div><?= date('d/m/Y', strtotime($venta['Fecha_Factura'] ?? '')) ?></div>
                                    <small style="color: var(--gray-dark);"><?= date('H:i', strtotime($venta['Fecha_Factura'] ?? '')) ?></small>
                                </td>
                                <td>
                                    <?php
                                    $iconoMetodo = match($venta['Metodo_Pago'] ?? '') {
                                        'Tarjeta' => 'fa-credit-card',
                                        'Efectivo' => 'fa-money-bill',
                                        'Transferencia' => 'fa-university',
                                        default => 'fa-wallet'
                                    };
                                    ?>
                                    <span class="badge d-inline-flex align-items-center" style="background-color: var(--info); color: white;">
                                        <i class="fas <?= $iconoMetodo ?> me-1"></i>
                                        <?= htmlspecialchars($venta['Metodo_Pago'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold" style="color: var(--primary-dark);">
                                    $<?= number_format($venta['Monto_Total'] ?? 0, 2) ?>
                                </td>
                                <td class="text-end" style="color: var(--danger);">
                                    <?php if (($venta['Descuento_Aplicado'] ?? 0) > 0): ?>
                                        -$<?= number_format($venta['Descuento_Aplicado'], 2) ?>
                                        <br><small style="color: var(--gray-dark);"><?= htmlspecialchars($venta['Codigo_Descuento'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span style="color: var(--gray-dark);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold" style="color: var(--success);">
                                    $<?= number_format(($venta['Monto_Total'] ?? 0) - ($venta['Descuento_Aplicado'] ?? 0), 2) ?>
                                </td>
                                <td class="text-center pe-3">
                                    <?php 
                                    $estado = $venta['Estado'] ?? '';
                                    $badgeColor = match($estado) {
                                        'Confirmado' => 'var(--info)',
                                        'Preparando' => 'var(--warning)',
                                        'Enviado' => 'var(--primary)',
                                        'Entregado' => 'var(--success)',
                                        'Anulado' => 'var(--danger)',
                                        default => 'var(--gray-dark)'
                                    };
                                    $iconoEstado = match($estado) {
                                        'Confirmado' => 'fa-check-circle',
                                        'Preparando' => 'fa-clock',
                                        'Enviado' => 'fa-truck',
                                        'Entregado' => 'fa-box-check',
                                        'Anulado' => 'fa-times-circle',
                                        default => 'fa-question-circle'
                                    };
                                    ?>
                                    <span class="badge d-inline-flex align-items-center" style="background-color: <?= $badgeColor ?>; color: white;">
                                        <i class="fas <?= $iconoEstado ?> me-1"></i>
                                        <?= $estado ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background-color: rgba(42, 52, 72, 0.05);">
                            <tr>
                                <td colspan="4" class="ps-3 text-end fw-bold" style="color: var(--primary-dark);">TOTALES DEL PERÍODO:</td>
                                <td class="text-end fw-bold" style="color: var(--primary-dark);">$<?= number_format($totales['ventas_brutas'] ?? 0, 2) ?></td>
                                <td class="text-end fw-bold" style="color: var(--danger);">-$<?= number_format($totales['descuentos'] ?? 0, 2) ?></td>
                                <td class="text-end fw-bold" style="color: var(--success);">$<?= number_format($totales['ventas_netas'] ?? 0, 2) ?></td>
                                <td class="pe-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-4x mb-3" style="color: var(--primary);"></i>
                    <h5 style="color: var(--primary-dark);">No hay ventas en este período</h5>
                    <p style="color: var(--gray-dark);">No se encontraron ventas para el período seleccionado.</p>
                    <a href="<?= BASE_URL ?>?c=ReporteVenta&a=index" class="btn mt-3" style="background-color: var(--primary-dark); color: white;">
                        <i class="fas fa-calendar-alt me-1"></i> Cambiar período
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/reporte.css">

<script>
// Función para mostrar/ocultar filtros
function toggleFilters() {
    const section = document.getElementById('filtersSection');
    if (section) {
        section.style.display = section.style.display === 'none' ? 'block' : 'none';
    }
}

// Funciones de filtrado
function filterByEstado(estado) {
    const rows = document.querySelectorAll('.venta-row');
    rows.forEach(row => {
        if (!estado || row.dataset.estado === estado) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function filterByMetodoPago(metodo) {
    const rows = document.querySelectorAll('.venta-row');
    rows.forEach(row => {
        if (!metodo || row.dataset.metodo === metodo) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function searchByCliente(query) {
    const rows = document.querySelectorAll('.venta-row');
    const searchTerm = query.toLowerCase();
    rows.forEach(row => {
        const cliente = (row.dataset.cliente || '').toLowerCase();
        if (!searchTerm || cliente.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Asegurar que las fechas personalizadas funcionen
    if (typeof toggleCustomDates === 'function') {
        toggleCustomDates();
    }
    
    // Inicializar tooltips de Bootstrap si existen
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    }
});
</script>