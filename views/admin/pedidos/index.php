<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-shopping-cart me-2"></i>Gestión de Pedidos</h2>
        <div>
            <a href="<?= BASE_URL ?>?c=Pedido&a=enviados" class="btn btn-warning me-2">
                <i class="fas fa-truck me-1"></i> Pedidos Enviados
            </a>
            <a href="<?= BASE_URL ?>?c=Pedido&a=reporte" class="btn btn-info">
                <i class="fas fa-chart-bar me-1"></i> Reportes
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Barra de Búsqueda y Filtros -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtrar Pedidos</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Pedido&a=index" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Pedido">
                <input type="hidden" name="a" value="index">
                
                <div class="col-md-3">
                    <label for="buscar" class="form-label">Buscar pedido</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               class="form-control" 
                               id="buscar" 
                               name="buscar" 
                               value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>" 
                               placeholder="ID, código, nombre, email...">
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select class="form-select" id="estado" name="estado">
                        <option value="">Todos</option>
                        <option value="Emitido" <?= ($_GET['estado'] ?? '') === 'Emitido' ? 'selected' : '' ?>>Emitido</option>
                        <option value="Confirmado" <?= ($_GET['estado'] ?? '') === 'Confirmado' ? 'selected' : '' ?>>Confirmado</option>
                        <option value="Preparando" <?= ($_GET['estado'] ?? '') === 'Preparando' ? 'selected' : '' ?>>Preparando</option>
                        <option value="Enviado" <?= ($_GET['estado'] ?? '') === 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                        <option value="Retrasado" <?= ($_GET['estado'] ?? '') === 'Retrasado' ? 'selected' : '' ?>>Retrasado</option>
                        <option value="Devuelto" <?= ($_GET['estado'] ?? '') === 'Devuelto' ? 'selected' : '' ?>>Devuelto</option>
                        <option value="Entregado" <?= ($_GET['estado'] ?? '') === 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                        <option value="Anulado" <?= ($_GET['estado'] ?? '') === 'Anulado' ? 'selected' : '' ?>>Anulado</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha inicio</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_inicio" 
                           name="fecha_inicio" 
                           value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>">
                </div>
                
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha fin</label>
                    <input type="date" 
                           class="form-control" 
                           id="fecha_fin" 
                           name="fecha_fin" 
                           value="<?= htmlspecialchars($_GET['fecha_fin'] ?? '') ?>">
                </div>
                
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
            
            <!-- Alertas de pedidos atrasados -->
            <?php if (!empty($pedidosAtrasados)): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Tienes <?= count($pedidosAtrasados) ?> pedido(s) atrasado(s)</strong> 
                    (más de 3 días en estado "Enviado" sin entregar)
                    <a href="<?= BASE_URL ?>?c=Pedido&a=enviados" class="alert-link ms-2">Ver pedidos enviados</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estadísticas Mejoradas -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Emitidos</h5>
                    <h3 class="card-text"><?= $estadisticas['emitidos'] ?? 0 ?></h3>
                    <small><i class="fas fa-clock"></i> Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Confirmados</h5>
                    <h3 class="card-text"><?= $estadisticas['confirmados'] ?? 0 ?></h3>
                    <small><i class="fas fa-check"></i> Validados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Enviados</h5>
                    <h3 class="card-text"><?= $estadisticas['enviados'] ?? 0 ?></h3>
                    <small><i class="fas fa-truck"></i> En tránsito</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Entregados</h5>
                    <h3 class="card-text"><?= $estadisticas['entregados'] ?? 0 ?></h3>
                    <small><i class="fas fa-box-check"></i> Completados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Anulados</h5>
                    <h3 class="card-text"><?= $estadisticas['anulados'] ?? 0 ?></h3>
                    <small><i class="fas fa-times"></i> Cancelados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <h5 class="card-title">Ventas Hoy</h5>
                    <h3 class="card-text">$<?= number_format($resumenDiario['ventas_hoy'] ?? 0, 2) ?></h3>
                    <small><i class="fas fa-calendar-day"></i> <?= date('d/m/Y') ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Pedidos -->
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Pedidos (Ordenados por Prioridad)</h5>
            <span class="badge bg-primary">Total: <?= count($pedidos) ?> pedidos</span>
        </div>
        <div class="card-body">
            <?php if (empty($pedidos)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay pedidos</h5>
                    <?php if ($modoBusqueda): ?>
                        <p class="text-muted">No se encontraron resultados para la búsqueda.</p>
                        <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i> Ver todos los pedidos
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Envío</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <?php 
                                // Clase CSS según prioridad
                                $rowClass = '';
                                if ($pedido['Estado'] === 'Emitido') $rowClass = 'table-warning';
                                if ($pedido['Estado'] === 'Confirmado') $rowClass = 'table-info';
                                if ($pedido['Estado'] === 'Enviado') $rowClass = 'table-primary';
                                if ($pedido['Estado'] === 'Retrasado') $rowClass = 'table-danger';
                                if ($pedido['Estado'] === 'Anulado') $rowClass = 'table-secondary';
                                ?>
                                <tr class="<?= $rowClass ?>">
                                    <td>
                                        <strong>#<?= $pedido['ID_Factura'] ?></strong>
                                        <br>
                                        <small class="text-muted"><?= $pedido['Codigo_Acceso'] ?></small>
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
                                        <span class="fw-bold text-success">
                                            $<?= number_format($pedido['Monto_Total'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $getEstadoBadge($pedido['Estado']) ?>
                                        <?php if ($pedido['Estado'] === 'Enviado' && !empty($pedido['Fecha_Envio'])): ?>
                                            <br>
                                            <small class="text-muted">
                                                <?= date('d/m', strtotime($pedido['Fecha_Envio'])) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pedido['Numero_Guia'])): ?>
                                            <small>
                                                <i class="fas fa-barcode"></i> <?= $pedido['Numero_Guia'] ?>
                                                <?php if (!empty($pedido['Transportadora'])): ?>
                                                    <br>
                                                    <i class="fas fa-truck"></i> <?= $pedido['Transportadora'] ?>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">No enviado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($pedido['Estado'] !== 'Anulado' && $pedido['Estado'] !== 'Entregado'): ?>
                                                <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>#seguimiento" 
                                                   class="btn btn-outline-info" title="Actualizar estado">
                                                    <i class="fas fa-sync"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Total de ventas mostradas -->
                <div class="mt-3 p-3 bg-light rounded">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Pedidos mostrados:</strong> <?= count($pedidos) ?>
                        </div>
                        <div class="col-md-4 text-center">
                            <strong>Suma total:</strong> 
                            $<?= number_format(array_sum(array_column($pedidos, 'Monto_Total')), 2) ?>
                        </div>
                        <div class="col-md-4 text-end">
                            <strong>Ventas entregadas:</strong> 
                            $<?= number_format($estadisticas['ventas_entregadas'] ?? 0, 2) ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>