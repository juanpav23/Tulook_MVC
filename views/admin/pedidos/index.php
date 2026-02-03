<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shopping-cart me-2"></i>Gestión de Pedidos</h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>?c=Pedido&a=enviados" class="btn btn-warning">
                <i class="fas fa-truck me-1"></i> Pedidos Enviados
            </a>
            <a href="<?= BASE_URL ?>?c=Pedido&a=reporte" class="btn btn-info">
                <i class="fas fa-chart-bar me-1"></i> Reportes
            </a>
        </div>
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

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-primary-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-search me-2"></i>Buscar y Filtrar Pedidos</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Pedido&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Pedido">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar pedido</label>
                    <div class="input-group">
                        <span class="input-group-text bg-primary">
                            <i class="fas fa-search text-white"></i>
                        </span>
                        <input type="text" 
                            class="form-control" 
                            id="buscar" 
                            name="buscar" 
                            value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                            placeholder="Código, nombre, Email">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="Confirmado" <?= ($_GET['estado'] ?? '') === 'Confirmado' ? 'selected' : '' ?>>Confirmado</option>
                        <option value="Preparando" <?= ($_GET['estado'] ?? '') === 'Preparando' ? 'selected' : '' ?>>Preparando</option>
                        <option value="Enviado" <?= ($_GET['estado'] ?? '') === 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                        <option value="Retrasado" <?= ($_GET['estado'] ?? '') === 'Retrasado' ? 'selected' : '' ?>>Retrasado</option>
                        <option value="Devuelto" <?= ($_GET['estado'] ?? '') === 'Devuelto' ? 'selected' : '' ?>>Devuelto</option>
                        <option value="Entregado" <?= ($_GET['estado'] ?? '') === 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                        <option value="Anulado" <?= ($_GET['estado'] ?? '') === 'Anulado' ? 'selected' : '' ?>>Anulado</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" 
                        class="form-control" 
                        id="fecha_inicio" 
                        name="fecha_inicio" 
                        value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" 
                        class="form-control" 
                        id="fecha_fin" 
                        name="fecha_fin" 
                        value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>">
                </div>
                
                <div class="col-md-3 d-flex gap-2 align-items-end">
                    <button type="submit" class="btn btn-primary-dark flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Aplicar Filtros
                    </button>
                    <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-outline-primary btn-limpiar-filtros" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
            
            <!-- Alertas de pedidos atrasados y con tiempo -->
            <?php if (!empty($pedidosAtrasados) || (isset($alertasPedidos) && ($alertasPedidos['alerta_count'] > 0 || $alertasPedidos['mega_alerta_count'] > 0))): ?>
                <div class="mt-3">
                    <?php if (!empty($pedidosAtrasados)): ?>
                        <div class="alert alert-warning-pedidos mb-2 d-flex justify-content-between align-items-center">
                            <div>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Tienes <?= count($pedidosAtrasados) ?> pedido(s) atrasado(s)</strong> 
                                (más de 3 días en estado "Enviado" sin entregar)
                            </div>
                            <a href="<?= BASE_URL ?>?c=Pedido&a=enviados" class="btn btn-warning btn-sm">
                                <i class="fas fa-truck me-1"></i> Ver pedidos enviados
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($alertasPedidos)): ?>
                        <?php if ($alertasPedidos['mega_alerta_count'] > 0): ?>
                            <div class="alert alert-danger mb-2 d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-exclamation-circle me-2"></i>
                                    <strong>MEGA ALERTA: <?= $alertasPedidos['mega_alerta_count'] ?> pedido(s) con más de 24 horas sin atender</strong>
                                    <small class="d-block">¡Atención inmediata requerida!</small>
                                </div>
                                <a href="#tablaPedidos" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-arrow-down me-1"></i> Ver pedidos
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estadísticas - SIN EMITIDOS -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card stats-card-pedido estadistica-confirmados">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Confirmados</h5>
                    <h3 class="card-text"><?= $estadisticas['confirmados'] ?? 0 ?></h3>
                    <small><i class="fas fa-check text-primary"></i> Validados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card-pedido estadistica-preparando">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Preparando</h5>
                    <h3 class="card-text"><?= $estadisticas['preparando'] ?? 0 ?></h3>
                    <small><i class="fas fa-cogs text-primary"></i> En proceso</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card-pedido estadistica-enviados">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Enviados</h5>
                    <h3 class="card-text"><?= $estadisticas['enviados'] ?? 0 ?></h3>
                    <small><i class="fas fa-truck text-primary"></i> En tránsito</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card-pedido estadistica-retrasados">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Retrasados</h5>
                    <h3 class="card-text"><?= $estadisticas['retrasados'] ?? 0 ?></h3>
                    <small><i class="fas fa-exclamation-triangle text-primary"></i> Urgentes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card-pedido estadistica-devuelto">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Devueltos</h5>
                    <h3 class="card-text"><?= $estadisticas['devueltos'] ?? 0 ?></h3>
                    <small><i class="fas fa-undo text-primary"></i> Por revisar</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card stats-card-pedido estadistica-entregados">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Entregados</h5>
                    <h3 class="card-text"><?= $estadisticas['entregados'] ?? 0 ?></h3>
                    <small><i class="fas fa-box-check text-primary"></i> Completados</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="card" id="tablaPedidos">
        <div class="card-header bg-primary-dark d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-white"><i class="fas fa-list me-2"></i>Lista de Pedidos (Ordenados por prioridad)</h5>
            <span class="badge bg-primary-light">Total: <?= count($pedidos) ?> pedidos</span>
        </div>
        <div class="card-body">
            <?php if (empty($pedidos)): ?>
                <div class="no-results text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-primary mb-3"></i>
                    <h5 class="text-primary-dark">No hay pedidos</h5>
                    <?php if ($modoBusqueda): ?>
                        <p class="text-muted">No se encontraron resultados para la búsqueda.</p>
                    <?php else: ?>
                        <p class="text-muted">Comienza recibiendo el primer pedido.</p>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-primary-dark mt-2">
                        <i class="fas fa-list me-1"></i> Ver todos los pedidos
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th width="12%">Código</th>
                                <th width="18%">Cliente</th>
                                <th width="12%">Fecha</th>
                                <th width="10%">Total</th>
                                <th width="12%">Estado</th>
                                <th width="15%">Tiempo</th>
                                <th width="16%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 1;
                            foreach ($pedidos as $pedido): 
                                // Determinar alerta basada en horas desde creación (solo para pedidos activos)
                                $horasDesdeCreacion = $pedido['horas_desde_creacion'] ?? 0;
                                $estadoAlerta = $pedido['estado_alerta'] ?? 'normal';
                                $estado = $pedido['Estado'];
                                
                                // Solo mostrar alertas para pedidos que NO estén entregados o anulados
                                $mostrarAlerta = !in_array($estado, ['Entregado', 'Anulado']);
                                
                                // Clases CSS basadas en alerta
                                $claseFila = '';
                                $iconoAlerta = '';
                                $textoAlerta = '';
                                
                                if ($mostrarAlerta && $estadoAlerta === 'mega_alerta') {
                                    $claseFila = 'table-danger';
                                    $iconoAlerta = 'fa-exclamation-circle text-danger';
                                    $textoAlerta = 'MEGA ALERTA: Más de 24 horas';
                                } elseif ($mostrarAlerta && $estadoAlerta === 'alerta') {
                                    $claseFila = 'table-primary-ligth';
                                    $iconoAlerta = 'fa-clock text-primary-dark';
                                    $textoAlerta = 'Alerta: Más de 5 horas';
                                }
                            ?>
                                <tr class="hover-shadow-pedido <?= $claseFila ?>">
                                    <td class="text-center"><?= $contador++ ?></td>
                                    <td>
                                        <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($pedido['Correo']) ?></small>
                                    </td>
                                    <td>
                                        <?= date('d/m/Y', strtotime($pedido['Fecha_Factura'])) ?>
                                        <br>
                                        <small class="text-muted"><?= date('H:i', strtotime($pedido['Fecha_Factura'])) ?></small>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary-dark">
                                            $<?= number_format($pedido['Monto_Total'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $estado = $pedido['Estado'];
                                        $badgeClass = '';
                                        $icon = '';
                                        
                                        switch($estado) {
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
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?> d-flex align-items-center justify-content-center gap-1" style="min-width: 120px;">
                                            <i class="fas <?= $icon ?>"></i>
                                            <?= $estado ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($mostrarAlerta && $horasDesdeCreacion > 0 && $estadoAlerta !== 'normal'): ?>
                                            <div class="d-flex align-items-center">
                                                <i class="fas <?= $iconoAlerta ?> me-1"></i>
                                                <small class="<?= $estadoAlerta === 'mega_alerta' ? 'text-danger fw-bold' : 'text-primary-dark' ?>">
                                                    <?= $textoAlerta ?>
                                                    <br>
                                                    <span class="text-muted"><?= $horasDesdeCreacion ?> horas</span>
                                                </small>
                                            </div>
                                        <?php elseif ($mostrarAlerta && $horasDesdeCreacion > 0): ?>
                                            <span class="text-muted">
                                                <i class="fas fa-check-circle text-primary-dark me-1"></i>
                                                <?= $horasDesdeCreacion ?> horas
                                            </span>
                                        <?php elseif (in_array($estado, ['Entregado', 'Anulado'])): ?>
                                            <span class="text-muted">
                                                <i class="fas fa-flag-checkered text-primary me-1"></i>
                                                Proceso finalizado
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">
                                                <i class="fas fa-clock text-primary-dark me-1"></i>
                                                Reciente
                                            </span>
                                        <?php endif; ?>
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
                    </table>
                </div>
                
                <!-- Resumen de ventas -->
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Pedidos mostrados:</strong> <?= count($pedidos) ?>
                        </div>
                        <div class="col-md-4 text-center">
                            <strong>Suma total:</strong> 
                            <span class="text-primary-dark fw-bold">
                                $<?= number_format(array_sum(array_column($pedidos, 'Monto_Total')), 2) ?>
                            </span>
                        </div>
                        <div class="col-md-4 text-center">
                            <strong>Ventas entregadas:</strong> 
                            <span class="text-primary-dark fw-bold">
                                $<?= number_format($estadisticas['ventas_entregadas'] ?? 0, 2) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="assets/css/usuario.css">
<link rel="stylesheet" href="assets/css/pedido.css">

<!-- JS -->
<script src="assets/js/pedido.js"></script>