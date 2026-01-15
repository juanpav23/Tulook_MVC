<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i>Reportes de Pedidos</h2>
        <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a pedidos
        </a>
    </div>

    <!-- Filtros de reporte -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrar Reporte</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="list-group">
                        <a href="<?= BASE_URL ?>?c=Pedido&a=reporte&tipo=diario&fecha=<?= date('Y-m-d') ?>" 
                           class="list-group-item list-group-item-action <?= ($tipo ?? '') === 'diario' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-day me-2"></i> Reporte Diario
                        </a>
                        <a href="<?= BASE_URL ?>?c=Pedido&a=reporte&tipo=mensual&fecha=<?= date('Y-m') ?>-01" 
                           class="list-group-item list-group-item-action <?= ($tipo ?? '') === 'mensual' ? 'active' : '' ?>">
                            <i class="fas fa-calendar-alt me-2"></i> Reporte Mensual
                        </a>
                        <a href="<?= BASE_URL ?>?c=Pedido&a=reporte&tipo=anual&fecha=<?= date('Y') ?>-01-01" 
                           class="list-group-item list-group-item-action <?= ($tipo ?? '') === 'anual' ? 'active' : '' ?>">
                            <i class="fas fa-calendar me-2"></i> Reporte Anual
                        </a>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <form action="<?= BASE_URL ?>?c=Pedido&a=reporte" method="get" class="row g-3">
                        <input type="hidden" name="c" value="Pedido">
                        <input type="hidden" name="a" value="reporte">
                        <input type="hidden" name="tipo" value="<?= $tipo ?? 'diario' ?>">
                        
                        <div class="col-md-6">
                            <label for="fecha" class="form-label">
                                <?= match($tipo ?? 'diario') {
                                    'diario' => 'Fecha',
                                    'mensual' => 'Mes',
                                    'anual' => 'Año',
                                    default => 'Fecha'
                                } ?>
                            </label>
                            <input type="<?= match($tipo ?? 'diario') {
                                'diario' => 'date',
                                'mensual' => 'month',
                                'anual' => 'number',
                                default => 'date'
                            } ?>" 
                                   class="form-control" 
                                   id="fecha" 
                                   name="fecha" 
                                   value="<?= htmlspecialchars($_GET['fecha'] ?? date('Y-m-d')) ?>"
                                   <?= ($tipo ?? '') === 'anual' ? 'min="2020" max="2030"' : '' ?>>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-1"></i> Generar Reporte
                            </button>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <a href="<?= BASE_URL ?>?c=Pedido&a=reporte" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i> Limpiar
                            </a>
                        </div>
                    </form>
                    
                    <!-- Resumen del reporte -->
                    <?php if (!empty($pedidos)): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <h6>Resumen del Reporte:</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Período:</strong><br>
                                    <?= match($tipo ?? 'diario') {
                                        'diario' => date('d/m/Y', strtotime($fecha ?? date('Y-m-d'))),
                                        'mensual' => date('F Y', strtotime($fecha ?? date('Y-m-01'))),
                                        'anual' => ($fecha ?? date('Y')),
                                        default => 'N/A'
                                    } ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Pedidos:</strong><br>
                                    <?= count($pedidos) ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total Ventas:</strong><br>
                                    $<?= number_format($totalVentas, 2) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados del reporte -->
    <?php if (!empty($pedidos)): ?>
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-table me-2"></i>Detalle del Reporte</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID Pedido</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>#<?= $pedido['ID_Factura'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($pedido['Fecha_Factura'])) ?></td>
                                    <td><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></td>
                                    <td><?= $getEstadoBadge($pedido['Estado']) ?></td>
                                    <td class="text-success">$<?= number_format($pedido['Monto_Total'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="4" class="text-end"><strong>TOTAL VENTAS:</strong></td>
                                <td><strong>$<?= number_format($totalVentas, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay datos para el reporte seleccionado</h5>
            <p class="text-muted">Selecciona un período diferente o intenta con otro tipo de reporte.</p>
        </div>
    <?php endif; ?>
</div>