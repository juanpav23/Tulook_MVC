<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-bar me-2"></i>Reportes de Pedidos</h2>
        <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left me-1"></i> Volver a pedidos
        </a>
    </div>

    <!-- Mensajes Globales -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div id="mensajeGlobal" class="alert-message alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?>">
            <div class="alert-content">
                <i class="fas fa-info-circle me-2"></i>
                <span><?= $_SESSION['mensaje'] ?></span>
                <button type="button" class="btn-close-alert" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Filtros de reporte -->
    <div class="card mb-4">
        <div class="card-header bg-primary-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-filter me-2"></i>Filtrar Reporte</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <div class="list-group">
                        <a href="<?= BASE_URL ?>?c=Pedido&a=reporte&tipo=diario" 
                           class="list-group-item list-group-item-action d-flex align-items-center <?= ($tipo ?? 'diario') === 'diario' ? 'active bg-primary-dark border-primary-dark' : '' ?>">
                            <i class="fas fa-calendar-day me-2"></i> Reporte Diario
                        </a>
                        <a href="<?= BASE_URL ?>?c=Pedido&a=reporte&tipo=mensual" 
                           class="list-group-item list-group-item-action d-flex align-items-center <?= ($tipo ?? 'diario') === 'mensual' ? 'active bg-primary-dark border-primary-dark' : '' ?>">
                            <i class="fas fa-calendar-alt me-2"></i> Reporte Mensual
                        </a>
                        <a href="<?= BASE_URL ?>?c=Pedido&a=reporte&tipo=anual" 
                           class="list-group-item list-group-item-action d-flex align-items-center <?= ($tipo ?? 'diario') === 'anual' ? 'active bg-primary-dark border-primary-dark' : '' ?>">
                            <i class="fas fa-calendar me-2"></i> Reporte Anual
                        </a>
                    </div>
                </div>
                
                <div class="col-md-9">
                    <form action="<?= BASE_URL ?>?c=Pedido&a=reporte" method="get" class="row g-3">
                        <input type="hidden" name="c" value="Pedido">
                        <input type="hidden" name="a" value="reporte">
                        <input type="hidden" name="tipo" id="inputTipo" value="<?= $tipo ?? 'diario' ?>">
                        
                        <div class="col-md-6">
                            <label for="fecha" class="form-label">
                                <strong><?= match($tipo ?? 'diario') {
                                    'diario' => 'Fecha',
                                    'mensual' => 'Mes',
                                    'anual' => 'Año',
                                    default => 'Fecha'
                                } ?></strong>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary">
                                    <i class="fas fa-calendar text-white"></i>
                                </span>
                                <input type="<?= match($tipo ?? 'diario') {
                                    'diario' => 'date',
                                    'mensual' => 'month',
                                    'anual' => 'number',
                                    default => 'date'
                                } ?>" 
                                       class="form-control" 
                                       id="fecha" 
                                       name="fecha" 
                                       value="<?= htmlspecialchars($fecha ?? date('Y-m-d')) ?>"
                                       <?= ($tipo ?? 'diario') === 'anual' ? 'min="2020" max="2030"' : '' ?>>
                            </div>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary-dark w-100">
                                <i class="fas fa-filter me-1"></i> Generar Reporte
                            </button>
                        </div>
                        
                        <div class="col-md-3 d-flex align-items-end">
                            <a href="<?= BASE_URL ?>?c=Pedido&a=reporte" class="btn btn-outline-primary w-100">
                                <i class="fas fa-times me-1"></i> Limpiar
                            </a>
                        </div>
                    </form>
                    
                    <!-- Resumen del reporte -->
                    <?php if (!empty($pedidos)): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong class="text-primary-dark">Período:</strong><br>
                                    <span class="text-muted">
                                        <?= match($tipo ?? 'diario') {
                                            'diario' => date('d/m/Y', strtotime($fecha ?? date('Y-m-d'))),
                                            'mensual' => date('F Y', strtotime($fecha ?? date('Y-m-01'))),
                                            'anual' => ($fecha ?? date('Y')),
                                            default => 'N/A'
                                        } ?>
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <strong class="text-primary-dark">Total Pedidos:</strong><br>
                                    <span class="text-primary-dark fw-bold"><?= count($pedidos) ?></span>
                                </div>
                                <div class="col-md-4">
                                    <strong class="text-primary-dark">Total Ventas:</strong><br>
                                    <span class="text-primary-dark fw-bold">$<?= number_format($totalVentas, 2) ?></span>
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
            <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white"><i class="fas fa-table me-2"></i>Detalle del Reporte</h5>
                <span class="badge bg-primary-light"><?= count($pedidos) ?> pedidos</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary-dark">
                            <tr>
                                <th>Código</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr class="hover-shadow-pedido">
                                    <td>
                                        <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($pedido['Fecha_Factura'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('H:i', strtotime($pedido['Fecha_Factura'])) ?></small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($pedido['Correo']) ?></small>
                                    </td>
                                    <td>
                                        <?= $getEstadoBadge($pedido['Estado']) ?>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary-dark">
                                            $<?= number_format($pedido['Monto_Total'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm btn-group-pedidos">
                                            <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary-dark">
                                <td colspan="4" class="text-primary-dark text-white"><strong>TOTAL VENTAS:</strong></td>
                                <td colspan="2"><strong class="text-primary-dark">$<?= number_format($totalVentas, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="no-results text-center py-5">
            <i class="fas fa-chart-bar fa-4x text-primary mb-3"></i>
            <h5 class="text-primary-dark">No hay datos para el reporte seleccionado</h5>
            <p class="text-muted">Selecciona un período diferente o intenta con otro tipo de reporte.</p>
        </div>
    <?php endif; ?>
</div>

<!-- CSS -->
<link rel="stylesheet" href="assets/css/usuario.css">
<link rel="stylesheet" href="assets/css/pedido.css">

<!-- JS -->
<script src="assets/js/pedido.js"></script>

<!-- Script específico para reportes -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar fechas por defecto
    const tipo = '<?= $tipo ?? "diario" ?>';
    const fechaInput = document.getElementById('fecha');
    const inputTipo = document.getElementById('inputTipo');
    
    if (!fechaInput.value) {
        switch(tipo) {
            case 'diario':
                fechaInput.value = '<?= date('Y-m-d') ?>';
                break;
            case 'mensual':
                fechaInput.value = '<?= date('Y-m') ?>';
                break;
            case 'anual':
                fechaInput.value = '<?= date('Y') ?>';
                break;
        }
    }
    
    // Actualizar el tipo cuando se hace clic en los enlaces del sidebar
    const linksTipo = document.querySelectorAll('.list-group-item[href*="tipo="]');
    linksTipo.forEach(link => {
        link.addEventListener('click', function(e) {
            const url = new URL(this.href);
            const tipo = url.searchParams.get('tipo');
            inputTipo.value = tipo;
            
            // Actualizar el campo de fecha según el tipo
            switch(tipo) {
                case 'diario':
                    document.getElementById('fecha').type = 'date';
                    break;
                case 'mensual':
                    document.getElementById('fecha').type = 'month';
                    break;
                case 'anual':
                    document.getElementById('fecha').type = 'number';
                    break;
            }
        });
    });
    
    // Validar año si es reporte anual
    if (tipo === 'anual' && fechaInput) {
        fechaInput.addEventListener('change', function() {
            const year = parseInt(this.value);
            if (year < 2020 || year > 2030) {
                alert('Por favor ingresa un año entre 2020 y 2030');
                this.value = new Date().getFullYear();
            }
        });
    }
});
</script>