<?php
// views/admin/reporte/clientes.php
?>
<div class="container-fluid">
    <!-- Encabezado con indicador de HISTORIAL COMPLETO -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <h2 class="mb-0"><i class="fas fa-user-friends me-2" style="color: var(--primary-dark);"></i>Reporte de Clientes</h2>
                <span class="badge" style="background-color: var(--success); color: white; padding: 8px 16px; font-size: 0.9rem;">
                    <i class="fas fa-history me-1"></i> HISTORIAL COMPLETO
                </span>
            </div>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1" style="color: var(--primary);"></i> 
                <strong>Todos los clientes desde el inicio del sistema</strong>
            </p>
            <p class="text-muted mb-0 mt-1">
                <i class="fas fa-info-circle me-1" style="color: var(--info);"></i>
                Mostrando <strong><?= count($clientes) ?></strong> clientes que han realizado compras
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>?c=ReporteVenta&a=index" class="btn" style="background-color: var(--primary-dark); color: white;">
                <i class="fas fa-arrow-left me-1"></i> Volver a Reportes
            </a>
        </div>
    </div>

    <!-- Tarjetas de métricas GLOBALES -->
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--primary-dark);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">TOTAL CLIENTES</small>
                        <h2 class="card-text mt-1" style="font-size: 2.2rem; font-weight: 700; color: var(--primary-dark);">
                            <?= number_format($totales['clientes_unicos'] ?? 0) ?>
                        </h2>
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-users me-1" style="color: var(--primary);"></i> Con compras realizadas
                        </small>
                    </div>
                    <div style="background: rgba(27, 32, 45, 0.08); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-users" style="font-size: 1.8rem; color: var(--primary-dark);"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--success);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">VENTAS TOTALES</small>
                        <h3 class="card-text mt-1" style="font-size: 1.8rem; font-weight: 700; color: var(--success); word-break: break-word;">
                            $<?= number_format($totales['ventas_totales'] ?? 0, 2) ?>
                        </h3>
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-shopping-cart me-1" style="color: var(--success);"></i> Historial completo
                        </small>
                    </div>
                    <div style="background: rgba(40, 167, 69, 0.08); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-dollar-sign" style="font-size: 1.8rem; color: var(--success);"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--info);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">PEDIDOS TOTALES</small>
                        <h2 class="card-text mt-1" style="font-size: 2.2rem; font-weight: 700; color: var(--info);">
                            <?= number_format($totales['pedidos_totales'] ?? 0) ?>
                        </h2>
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-box me-1" style="color: var(--info);"></i> Promedio: <?= number_format(($totales['pedidos_totales'] ?? 0) / max(1, ($totales['clientes_unicos'] ?? 1)), 1) ?> x cliente
                        </small>
                    </div>
                    <div style="background: rgba(23, 162, 184, 0.08); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-shopping-cart" style="font-size: 1.8rem; color: var(--info);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Ordenamiento (SOLO ORDEN, SIN FECHAS) -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: var(--primary-dark); color: white;">
            <h5 class="mb-0 text-white"><i class="fas fa-sort me-2"></i>Ordenar Clientes</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="ReporteVenta">
                <input type="hidden" name="a" value="clientes_todos">
                
                <div class="col-md-6">
                    <label for="orden" class="form-label" style="color: var(--primary-dark); font-weight: 600;">Ordenar por:</label>
                    <select class="form-select" id="orden" name="orden" onchange="this.form.submit()">
                        <option value="monto_desc" <?= ($orden ?? 'monto_desc') === 'monto_desc' ? 'selected' : '' ?>>Valor Total (Mayor a menor)</option>
                        <option value="monto_asc" <?= ($orden ?? '') === 'monto_asc' ? 'selected' : '' ?>>Valor Total (Menor a mayor)</option>
                        <option value="pedidos_desc" <?= ($orden ?? '') === 'pedidos_desc' ? 'selected' : '' ?>>Pedidos (Mayor a menor)</option>
                        <option value="pedidos_asc" <?= ($orden ?? '') === 'pedidos_asc' ? 'selected' : '' ?>>Pedidos (Menor a mayor)</option>
                        <option value="reciente" <?= ($orden ?? '') === 'reciente' ? 'selected' : '' ?>>Más reciente</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <div class="d-flex justify-content-end gap-2">
                        <span class="badge p-3" style="background-color: var(--primary-dark); color: white; font-size: 0.9rem;">
                            <i class="fas fa-users me-1"></i> <?= number_format($totales['clientes_unicos'] ?? 0) ?> Clientes
                        </span>
                        <span class="badge p-3" style="background-color: var(--success); color: white; font-size: 0.9rem;">
                            <i class="fas fa-shopping-cart me-1"></i> <?= number_format($totales['pedidos_totales'] ?? 0) ?> Pedidos
                        </span>
                        <span class="badge p-3" style="background-color: var(--info); color: white; font-size: 0.9rem;">
                            <i class="fas fa-dollar-sign me-1"></i> $<?= number_format($totales['ventas_totales'] ?? 0, 2) ?>
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de TODOS los clientes -->
    <div class="card">
        <div class="card-header" style="background-color: var(--primary-dark); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="fas fa-list me-2"></i>Historial Completo de Clientes</h5>
                <span class="badge p-2" style="background-color: var(--primary-light); color: white; font-size: 0.9rem;">
                    <i class="fas fa-database me-1"></i> Total: <?= count($clientes) ?> registros
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($clientes)): ?>
                <div class="table-container">
                    <table class="table table-striped table-hover mb-0">
                        <thead style="background-color: var(--primary-dark); color: white;">
                            <tr>
                                <th class="ps-3">Cliente</th>
                                <th>Contacto</th>
                                <th class="text-end">Pedidos</th>
                                <th class="text-end">Valor Total</th>
                                <th class="text-end">Ticket Promedio</th>
                                <th>Última Compra</th>
                                <th class="text-center pe-3">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalGeneral = 0;
                            $totalPedidos = 0;
                            foreach ($clientes as $cliente): 
                                $totalGeneral += $cliente['Valor_Total'] ?? 0;
                                $totalPedidos += $cliente['Total_Pedidos'] ?? 0;
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold" style="color: var(--primary-dark);"><?= htmlspecialchars($cliente['Cliente'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope me-1" style="color: var(--primary);"></i> <?= htmlspecialchars($cliente['Correo'] ?? '') ?></div>
                                    <?php if (!empty($cliente['Celular'])): ?>
                                        <small style="color: var(--gray-dark);"><i class="fas fa-phone me-1"></i> <?= htmlspecialchars($cliente['Celular']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold" style="color: var(--primary);"><?= number_format($cliente['Total_Pedidos'] ?? 0) ?></span>
                                    <br>
                                    <small style="color: var(--gray-dark);"><?= number_format($cliente['Productos_Comprados'] ?? 0) ?> productos</small>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold" style="color: var(--success);">
                                        $<?= number_format($cliente['Valor_Total'] ?? 0, 2) ?>
                                    </span>
                                    <?php if (($cliente['Descuentos_Obtenidos'] ?? 0) > 0): ?>
                                        <br><small style="color: var(--danger);">
                                            <i class="fas fa-tag me-1"></i> -$<?= number_format($cliente['Descuentos_Obtenidos'], 2) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold">$<?= number_format($cliente['Ticket_Promedio'] ?? 0, 2) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($cliente['Ultima_Compra'])): ?>
                                        <div><?= date('d/m/Y', strtotime($cliente['Ultima_Compra'])) ?></div>
                                        <small style="color: var(--gray-dark);"><?= date('H:i', strtotime($cliente['Ultima_Compra'])) ?></small>
                                    <?php else: ?>
                                        <span style="color: var(--gray-dark);">Sin compras</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-3">
                                    <?php if (($cliente['Activo'] ?? 0) == 1): ?>
                                        <span class="badge" style="background-color: var(--success); color: white;">
                                            <i class="fas fa-check-circle me-1"></i> Activo
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: var(--danger); color: white;">
                                            <i class="fas fa-times-circle me-1"></i> Inactivo
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background-color: rgba(42, 52, 72, 0.05);">
                            <tr>
                                <th colspan="2" class="ps-3 text-end" style="color: var(--primary-dark);">TOTALES HISTÓRICOS:</th>
                                <th class="text-end" style="color: var(--primary-dark);"><?= number_format($totalPedidos) ?></th>
                                <th class="text-end" style="color: var(--success);">$<?= number_format($totalGeneral, 2) ?></th>
                                <th class="text-end" style="color: var(--primary-dark);">$<?= number_format($totalGeneral / max(1, count($clientes)), 2) ?></th>
                                <th colspan="2" class="pe-3"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div><br>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-4x mb-3" style="color: var(--primary);"></i>
                    <h5 style="color: var(--primary-dark);">No hay clientes registrados</h5>
                    <p style="color: var(--gray-dark);">No se encontraron clientes con compras en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/reporte.css">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>