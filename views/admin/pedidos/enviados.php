<?php
// Acceder al helper desde el controlador
$getEstadoBadge = $getEstadoBadge ?? 'getEstadoBadgePedido';

// Estadísticas generales de seguimiento (todos los pedidos en seguimiento)
$totalSeguimiento = count($pedidos);
$enTransito = 0;
$retrasados = 0;
$proximosVencer = 0;
$prioridadAlta = 0;

foreach ($pedidos as $pedido) {
    if ($pedido['Estado'] === 'Enviado') $enTransito++;
    if ($pedido['Estado'] === 'Retrasado') $retrasados++;
    
    // Proximos a vencer (2 días o menos)
    if (!empty($pedido['Fecha_Estimada_Entrega'])) {
        $diasFaltantes = floor((strtotime($pedido['Fecha_Estimada_Entrega']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
        if ($diasFaltantes >= 0 && $diasFaltantes <= 2) $proximosVencer++;
    }
    
    // Prioridad alta (retraso >7 días)
    if (isset($pedido['dias_retraso']) && $pedido['dias_retraso'] > 5) {
        $prioridadAlta++;
    }
}
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck me-2 text-primary-dark"></i>Seguimiento de Pedidos Enviados</h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver a Pedidos
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

    <!-- Barra de Búsqueda y Filtros (igual que index.php) -->
    <div class="card mb-4">
        <div class="card-header bg-primary-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-search me-2"></i>Buscar y Filtrar Pedidos en Seguimiento</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Pedido&a=enviados" method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="Pedido">
                <input type="hidden" name="a" value="enviados">
                
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
                    <label for="transportadora" class="form-label">Transportadora</label>
                    <select class="form-select" id="transportadora" name="transportadora">
                        <option value="">Todas</option>
                        <?php
                        // Obtener transportadoras únicas
                        $transportadorasUnicas = [];
                        foreach ($pedidos as $pedido) {
                            if (!empty($pedido['Transportadora']) && !in_array($pedido['Transportadora'], $transportadorasUnicas)) {
                                $transportadorasUnicas[] = $pedido['Transportadora'];
                            }
                        }
                        sort($transportadorasUnicas);
                        foreach ($transportadorasUnicas as $transp):
                        ?>
                            <option value="<?= htmlspecialchars($transp) ?>" <?= ($_GET['transportadora'] ?? '') === $transp ? 'selected' : '' ?>>
                                <?= htmlspecialchars($transp) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="fecha_inicio" class="form-label">Fecha envío desde</label>
                    <input type="date" 
                        class="form-control" 
                        id="fecha_inicio" 
                        name="fecha_inicio" 
                        value="<?= htmlspecialchars($_GET['fecha_inicio'] ?? '') ?>">
                </div>
                
                <div class="col-md-2">
                    <label for="fecha_fin" class="form-label">Fecha envío hasta</label>
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
                    <a href="<?= BASE_URL ?>?c=Pedido&a=enviados" class="btn btn-outline-primary btn-limpiar-filtros" title="Limpiar filtros">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas de Seguimiento Mejoradas -->
    <div class="row mb-4">
        <!-- Estadística General de Seguimiento -->
        <div class="col-md-3">
            <div class="card stats-card-pedido estadistica-seguimiento">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">En Seguimiento</h5>
                    <h3 class="card-text"><?= $totalSeguimiento ?></h3>
                    <small><i class="fas fa-truck-loading text-primary-dark"></i> Total pedidos monitoreados</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card-pedido estadistica-enviados">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">En Tránsito</h5>
                    <h3 class="card-text"><?= $enTransito ?></h3>
                    <small><i class="fas fa-truck text-primary-dark"></i> Pedidos en camino</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card-pedido estadistica-retrasados">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Retrasados</h5>
                    <h3 class="card-text"><?= $retrasados ?></h3>
                    <small><i class="fas fa-clock text-primary-dark"></i> Fuera de fecha</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card stats-card-pedido estadistica-atrasados">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Atrasados</h5>
                    <h3 class="card-text"><?= count($pedidosAtrasados ?? []) ?></h3>
                    <small><i class="fas fa-exclamation-triangle text-primary-dark"></i> >3 días enviados</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Pedidos Enviados -->
    <div class="card">
        <div class="card-header bg-primary-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-truck-loading me-2"></i>Pedidos en Seguimiento</h5>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-primary-light"><?= count($pedidos) ?> pedidos</span>
                <?php if ($proximosVencer > 0): ?>
                    <span class="badge bg-primary-light">
                        <i class="fas fa-calendar-exclamation me-1"></i>
                        <?= $proximosVencer ?> próximo(s) a vencer
                    </span>
                <?php endif; ?>
                <?php if ($prioridadAlta > 0): ?>
                    <span class="badge bg-danger">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        <?= $prioridadAlta ?> prioridad alta
                    </span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($pedidos)): ?>
                <div class="no-results text-center py-5">
                    <i class="fas fa-truck fa-4x text-primary mb-3"></i>
                    <h5 class="text-primary-dark">No hay pedidos en seguimiento</h5>
                    <p class="text-muted">Todos los pedidos han sido entregados o aún no han sido enviados.</p>
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
                                <th width="20%">Cliente</th>
                                <th width="12%">Fecha Envío</th>
                                <th width="10%">Tiempo</th>
                                <th width="15%">Fecha Estimada</th>
                                <th width="10%">Estado</th>
                                <th width="16%">Transportadora</th>
                                <th width="10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 1;
                            // Ordenar pedidos por prioridad (primero retrasados, luego por fecha más corta)
                            usort($pedidos, function($a, $b) {
                                // Primero por estado: Retrasado tiene mayor prioridad
                                if ($a['Estado'] === 'Retrasado' && $b['Estado'] !== 'Retrasado') return -1;
                                if ($a['Estado'] !== 'Retrasado' && $b['Estado'] === 'Retrasado') return 1;
                                
                                // Luego por días de retraso (mayor primero)
                                $diasRetrasoA = $a['dias_retraso'] ?? 0;
                                $diasRetrasoB = $b['dias_retraso'] ?? 0;
                                if ($diasRetrasoA !== $diasRetrasoB) return $diasRetrasoB <=> $diasRetrasoA;
                                
                                // Luego por fecha estimada (más cercana primero)
                                if (!empty($a['Fecha_Estimada_Entrega']) && !empty($b['Fecha_Estimada_Entrega'])) {
                                    return strtotime($a['Fecha_Estimada_Entrega']) <=> strtotime($b['Fecha_Estimada_Entrega']);
                                }
                                
                                // Finalmente por fecha de envío (más antigua primero)
                                return strtotime($a['Fecha_Envio'] ?? '') <=> strtotime($b['Fecha_Envio'] ?? '');
                            });
                            
                            foreach ($pedidos as $pedido): 
                                $diasTranscurridos = $pedido['dias_transcurridos'] ?? 0;
                                $diasRetraso = $pedido['dias_retraso'] ?? 0;
                                
                                // Determinar nivel de prioridad
                                $nivelPrioridad = '';
                                $colorPrioridad = '';
                                
                                if ($pedido['Estado'] === 'Retrasado') {
                                    if ($diasRetraso > 7) {
                                        $nivelPrioridad = 'Alta';
                                        $colorPrioridad = 'danger';
                                    } elseif ($diasRetraso > 3) {
                                        $nivelPrioridad = 'Media';
                                        $colorPrioridad = 'warning';
                                    } else {
                                        $nivelPrioridad = 'Baja';
                                        $colorPrioridad = 'primary-light';
                                    }
                                } else {
                                    if (isset($pedido['Fecha_Estimada_Entrega'])) {
                                        $diasFaltantes = floor((strtotime($pedido['Fecha_Estimada_Entrega']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
                                        if ($diasFaltantes <= 2 && $diasFaltantes >= 0) {
                                            $nivelPrioridad = 'Próximo';
                                            $colorPrioridad = 'primary';
                                        }
                                    }
                                }
                            ?>
                                <tr class="hover-shadow-pedido <?= $pedido['Estado'] === 'Retrasado' ? 'table-danger' : '' ?>"
                                    data-prioridad="<?= $nivelPrioridad ?>"
                                    data-dias-retraso="<?= $diasRetraso ?>">
                                    <td class="text-center">
                                        <span class="badge bg-primary-dark p-1"><?=$contador++?></span>
                                    </td>
                                    <td>
                                        <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong>
                                        <?php if ($nivelPrioridad): ?>
                                            <br>
                                            <small class="badge bg-<?= $colorPrioridad ?>">
                                                <i class="fas fa-flag me-1"></i><?= $nivelPrioridad ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($pedido['Correo']) ?></small>
                                    </td>
                                    <td>
                                        <?= !empty($pedido['Fecha_Envio']) ? date('d/m/Y', strtotime($pedido['Fecha_Envio'])) : 'N/A' ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= !empty($pedido['NombreEnvio']) ? 'Por: ' . $pedido['NombreEnvio'] : '' ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($diasTranscurridos > 0): ?>
                                            <span class="badge bg-<?= $diasTranscurridos > 3 ? 'primary-dark' : 'primary' ?>">
                                                <?= $diasTranscurridos ?> día<?= $diasTranscurridos != 1 ? 's' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary-dark">Hoy</span>
                                        <?php endif; ?>
                                        
                                        <?php if ($pedido['Estado'] === 'Retrasado' && $diasRetraso > 0): ?>
                                            <br>
                                            <small class="text-danger">
                                                <i class="fas fa-clock"></i> <?= $diasRetraso ?> día<?= $diasRetraso != 1 ? 's' : '' ?> retraso
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pedido['Fecha_Estimada_Entrega'])): ?>
                                            <?php 
                                            $fechaEstimada = strtotime($pedido['Fecha_Estimada_Entrega']);
                                            $hoy = strtotime(date('Y-m-d'));
                                            $diasFaltantes = floor(($fechaEstimada - $hoy) / (60 * 60 * 24));
                                            ?>
                                            <div class="d-flex flex-column">
                                                <strong class="<?= $diasFaltantes < 0 ? 'text-danger' : ($diasFaltantes <= 2 ? 'text-primary-dark' : 'text-primary-light') ?>">
                                                    <?= date('d/m/Y', $fechaEstimada) ?>
                                                </strong>
                                                <small class="text-muted">
                                                    <?php if ($diasFaltantes > 0): ?>
                                                        <i class="far fa-calendar-check"></i> Faltan <?= $diasFaltantes ?> días
                                                    <?php elseif ($diasFaltantes == 0): ?>
                                                        <i class="fas fa-exclamation-circle text-primary"></i> Hoy vence
                                                    <?php else: ?>
                                                        <i class="fas fa-exclamation-triangle text-danger"></i> Vencido hace <?= abs($diasFaltantes) ?> días
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No especificada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $getEstadoBadge($pedido['Estado']) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($pedido['Transportadora'])): ?>
                                            <div class="d-flex flex-column">
                                                <strong class="text-primary-dark small">
                                                    <i class="fas fa-truck me-1"></i> <?= $pedido['Transportadora'] ?>
                                                </strong>
                                                <?php if (!empty($pedido['Numero_Guia'])): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-barcode me-1"></i> <?= $pedido['Numero_Guia'] ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Sin información</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm btn-group-pedidos">
                                            <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                                               class="btn btn-outline-primary" title="Ver detalle">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($pedido['Estado'] === 'Enviado'): ?>
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEntregado<?= $pedido['ID_Factura'] ?>"
                                                        title="Marcar como entregado">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalActualizarFecha<?= $pedido['ID_Factura'] ?>"
                                                        title="Actualizar fecha estimada">
                                                    <i class="fas fa-calendar-alt"></i>
                                                </button>
                                                <?php if ($pedido['Estado'] !== 'Retrasado'): ?>
                                                    <button type="button" class="btn btn-outline-primary"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalRetrasado<?= $pedido['ID_Factura'] ?>"
                                                            title="Marcar como retrasado">
                                                        <i class="fas fa-clock"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="button" class="btn btn-outline-primary" disabled
                                                            title="Ya está marcado como retrasado">
                                                        <i class="fas fa-clock"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ($pedido['Estado'] === 'Retrasado'): ?>
                                                <button type="button" class="btn btn-outline-primary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#modalEntregado<?= $pedido['ID_Factura'] ?>"
                                                        title="Marcar como entregado">
                                                    <i class="fas fa-check-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" disabled
                                                        title="Ya está marcado como retrasado">
                                                    <i class="fas fa-clock"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Modal para marcar como entregado -->
                                <div class="modal fade" id="modalEntregado<?= $pedido['ID_Factura'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary-dark text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-check-circle me-2"></i>Marcar como Entregado
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>?c=Pedido&a=marcarEntregado" method="post">
                                                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                                                <div class="modal-body">
                                                    <div class="alert alert-primary-light mb-3">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        <strong>Información:</strong> El pedido será marcado como entregado y finalizado.
                                                    </div>
                                                    <div class="text-center mb-3">
                                                        <div class="avatar-circle-sm mx-auto mb-2">
                                                            <i class="fas fa-truck"></i>
                                                        </div>
                                                        <p>¿Confirmar entrega del pedido <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong>?</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="descripcion<?= $pedido['ID_Factura'] ?>" class="form-label text-primary-dark">
                                                            <strong>Descripción de la entrega:</strong>
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="descripcion<?= $pedido['ID_Factura'] ?>" 
                                                                  name="Descripcion" 
                                                                  rows="3"
                                                                  placeholder="Ej: Producto entregado satisfactoriamente, cliente confirmó recepción, etc.">Producto entregado satisfactoriamente. Firma recibida y sin novedades en la entrega.</textarea>
                                                        <div class="form-text text-primary-dark">Esta descripción quedará registrada en el historial.</div>
                                                    </div>
                                                    
                                                    <!-- Casillas de verificación para entregado -->
                                                    <div class="casillas-verificacion-entregado mb-4">
                                                        <div class="alert alert-primary-light mb-3">
                                                            <i class="fas fa-shield-alt me-2"></i>
                                                            <strong>Verificación de seguridad:</strong> Marque las siguientes casillas para confirmar la entrega.
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-entregado-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla1_entregado_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla1_entregado_<?= $pedido['ID_Factura'] ?>">
                                                                Confirmo que el pedido fue entregado satisfactoriamente al cliente
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-entregado-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla2_entregado_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla2_entregado_<?= $pedido['ID_Factura'] ?>">
                                                                He verificado que el producto está en buen estado y sin daños
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-entregado-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla3_entregado_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla3_entregado_<?= $pedido['ID_Factura'] ?>">
                                                                Entiendo que al confirmar la entrega, el pedido se marcará como finalizado
                                                            </label>
                                                        </div>
                                                        
                                                        <!-- Contador de verificación -->
                                                        <div class="verificacion-contador-entregado mt-3">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <small class="text-primary-dark">Verificación completada:</small>
                                                                <small><span id="contadorCasillasEntregado<?= $pedido['ID_Factura'] ?>">0</span>/3</small>
                                                            </div>
                                                            <div class="progress" style="height: 8px;">
                                                                <div id="barraProgresoEntregado<?= $pedido['ID_Factura'] ?>" 
                                                                     class="progress-bar" 
                                                                     role="progressbar" 
                                                                     style="width: 0%" 
                                                                     aria-valuenow="0" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" 
                                                            id="btnConfirmarEntregado<?= $pedido['ID_Factura'] ?>" 
                                                            class="btn btn-primary-dark" 
                                                            disabled>
                                                        <i class="fas fa-check-circle me-1"></i> Confirmar Entrega
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal para actualizar fecha estimada -->
                                <div class="modal fade" id="modalActualizarFecha<?= $pedido['ID_Factura'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary-dark text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-calendar-alt me-2"></i>Actualizar Fecha Estimada
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarFechaEstimada" method="post">
                                                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                                                <div class="modal-body">
                                                    <div class="alert alert-primary-light mb-3">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        <strong>Información:</strong> Actualiza la fecha estimada de entrega para este pedido.
                                                    </div>
                                                    <p>Actualizar fecha estimada para <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong></p>
                                                    
                                                    <div class="mb-3">
                                                        <label for="fecha_estimada<?= $pedido['ID_Factura'] ?>" class="form-label text-primary-dark">
                                                            <strong>Nueva fecha estimada:</strong>
                                                        </label>
                                                        <input type="date" 
                                                               class="form-control" 
                                                               id="fecha_estimada<?= $pedido['ID_Factura'] ?>" 
                                                               name="fecha_estimada" 
                                                               value="<?= !empty($pedido['Fecha_Estimada_Entrega']) ? date('Y-m-d', strtotime($pedido['Fecha_Estimada_Entrega'])) : date('Y-m-d', strtotime('+3 days')) ?>"
                                                               min="<?= date('Y-m-d') ?>"
                                                               required>
                                                        <div class="form-text text-primary-dark">Selecciona una fecha futura.</div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="motivo<?= $pedido['ID_Factura'] ?>" class="form-label text-primary-dark">
                                                            <strong>Motivo del cambio (opcional):</strong>
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="motivo<?= $pedido['ID_Factura'] ?>" 
                                                                  name="motivo" 
                                                                  rows="2"
                                                                  placeholder="Ej: Retraso por la transportadora, clima, etc."></textarea>
                                                    </div>
                                                    
                                                    <!-- Casillas de verificación para actualizar fecha -->
                                                    <div class="casillas-verificacion-fecha mb-4">
                                                        <div class="alert alert-primary-light mb-3">
                                                            <i class="fas fa-shield-alt me-2"></i>
                                                            <strong>Verificación de seguridad:</strong> Marque las siguientes casillas para confirmar el cambio de fecha.
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-fecha-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla1_fecha_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla1_fecha_<?= $pedido['ID_Factura'] ?>">
                                                                Confirmo que he verificado la nueva fecha estimada de entrega
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-fecha-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla2_fecha_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla2_fecha_<?= $pedido['ID_Factura'] ?>">
                                                                He considerado los tiempos de entrega de la transportadora
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-fecha-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla3_fecha_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla3_fecha_<?= $pedido['ID_Factura'] ?>">
                                                                Entiendo que esta información será comunicada al cliente
                                                            </label>
                                                        </div>
                                                        
                                                        <!-- Contador de verificación -->
                                                        <div class="verificacion-contador-fecha mt-3">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <small class="text-primary-dark">Verificación completada:</small>
                                                                <small><span id="contadorCasillasFecha<?= $pedido['ID_Factura'] ?>">0</span>/3</small>
                                                            </div>
                                                            <div class="progress" style="height: 8px;">
                                                                <div id="barraProgresoFecha<?= $pedido['ID_Factura'] ?>" 
                                                                     class="progress-bar" 
                                                                     role="progressbar" 
                                                                     style="width: 0%" 
                                                                     aria-valuenow="0" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" 
                                                            id="btnConfirmarFecha<?= $pedido['ID_Factura'] ?>" 
                                                            class="btn btn-primary-dark" 
                                                            disabled>
                                                        <i class="fas fa-calendar-check me-1"></i> Actualizar Fecha
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal para marcar como retrasado -->
                                <div class="modal fade" id="modalRetrasado<?= $pedido['ID_Factura'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-clock me-2"></i>Marcar como Retrasado
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post">
                                                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                                                <input type="hidden" name="Estado" value="Retrasado">
                                                <div class="modal-body">
                                                    <div class="alert alert-warning-pedidos mb-3">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        <strong>Atención:</strong> Marcar como retrasado notificará al cliente sobre el retardo en la entrega.
                                                    </div>
                                                    
                                                    <p>¿Marcar pedido <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong> como retrasado?</p>
                                                    
                                                    <div class="mb-3">
                                                        <label for="nueva_fecha_estimada<?= $pedido['ID_Factura'] ?>" class="form-label text-primary-dark">
                                                            <strong>Nueva fecha estimada (opcional):</strong>
                                                        </label>
                                                        <input type="date" 
                                                               class="form-control" 
                                                               id="nueva_fecha_estimada<?= $pedido['ID_Factura'] ?>" 
                                                               name="nueva_fecha_estimada" 
                                                               value="<?= date('Y-m-d', strtotime('+3 days')) ?>"
                                                               min="<?= date('Y-m-d') ?>">
                                                        <div class="form-text text-primary-dark">Puedes establecer una nueva fecha estimada para el cliente.</div>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="descripcionRetraso<?= $pedido['ID_Factura'] ?>" class="form-label text-primary-dark">
                                                            <strong>Motivo del retraso:</strong>
                                                        </label>
                                                        <textarea class="form-control" 
                                                                  id="descripcionRetraso<?= $pedido['ID_Factura'] ?>" 
                                                                  name="Descripcion" 
                                                                  rows="3" 
                                                                  required
                                                                  placeholder="Ej: Problemas con la transportadora, mal clima, dirección incorrecta, etc."></textarea>
                                                        <div class="form-text text-primary-dark">Esta información quedará registrada en el historial.</div>
                                                    </div>
                                                    
                                                    <!-- Casillas de verificación para retrasado -->
                                                    <div class="casillas-verificacion-retrasado mb-4">
                                                        <div class="alert alert-danger mb-3">
                                                            <i class="fas fa-shield-alt me-2"></i>
                                                            <strong>Verificación de seguridad crítica:</strong> Esta acción notificará al cliente sobre el retraso.
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-retrasado-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla1_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla1_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                                Confirmo que el pedido está realmente retrasado y no se ha entregado
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-retrasado-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla2_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla2_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                                He verificado con la transportadora el estado actual del envío
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-retrasado-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla3_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla3_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                                Entiendo que esta acción notificará automáticamente al cliente
                                                            </label>
                                                        </div>
                                                        
                                                        <div class="form-check mb-2">
                                                            <input class="form-check-input casilla-retrasado-<?= $pedido['ID_Factura'] ?>" 
                                                                   type="checkbox" 
                                                                   value="1" 
                                                                   id="casilla4_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                            <label class="form-check-label text-primary-dark" for="casilla4_retrasado_<?= $pedido['ID_Factura'] ?>">
                                                                He considerado todas las alternativas antes de marcar como retrasado
                                                            </label>
                                                        </div>
                                                        
                                                        <!-- Contador de verificación -->
                                                        <div class="verificacion-contador-retrasado mt-3">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <small class="text-primary-dark">Verificación completada:</small>
                                                                <small><span id="contadorCasillasRetrasado<?= $pedido['ID_Factura'] ?>">0</span>/4</small>
                                                            </div>
                                                            <div class="progress" style="height: 8px;">
                                                                <div id="barraProgresoRetrasado<?= $pedido['ID_Factura'] ?>" 
                                                                     class="progress-bar" 
                                                                     role="progressbar" 
                                                                     style="width: 0%" 
                                                                     aria-valuenow="0" 
                                                                     aria-valuemin="0" 
                                                                     aria-valuemax="100"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" 
                                                            id="btnConfirmarRetrasado<?= $pedido['ID_Factura'] ?>" 
                                                            class="btn btn-danger" 
                                                            disabled>
                                                        <i class="fas fa-clock me-1"></i> Marcar como Retrasado
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Resumen y estadísticas -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-chart-line me-2"></i>Estadísticas de Seguimiento</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Total en seguimiento:</th>
                                                <td class="text-end"><?= $totalSeguimiento ?></td>
                                            </tr>
                                            <tr>
                                                <th>En tránsito:</th>
                                                <td class="text-end"><?= $enTransito ?></td>
                                            </tr>
                                            <tr>
                                                <th>Con fecha estimada:</th>
                                                <td class="text-end">
                                                    <?php
                                                    $conFechaEstimada = 0;
                                                    foreach ($pedidos as $p) {
                                                        if (!empty($p['Fecha_Estimada_Entrega'])) $conFechaEstimada++;
                                                    }
                                                    echo $conFechaEstimada;
                                                    ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <th>Retrasados:</th>
                                                <td class="text-end"><?= $retrasados ?></td>
                                            </tr>
                                            <tr>
                                                <th>Atrasados (>3 días):</th>
                                                <td class="text-end"><?= count($pedidosAtrasados ?? []) ?></td>
                                            </tr>
                                            <tr>
                                                <th>Proximos a vencer (≤2 días):</th>
                                                <td class="text-end"><?= $proximosVencer ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-primary-dark">
                            <div class="card-header bg-primary-dark text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Instrucciones</h6>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2">
                                        <i class="fas fa-flag text-primary-dark me-2"></i>
                                        <span class="text-primary-dark">Prioridad Alta:</span> Retraso >7 días
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-flag text-primary-light me-2"></i>
                                        <span class="text-primary-dark">Prioridad Media:</span> Retraso 3-7 días
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-calendar-exclamation text-primary me-2"></i>
                                        <span class="text-primary-dark">Próximo a vencer:</span> ≤2 días para fecha estimada
                                    </li>
                                    <li>
                                        <i class="fas fa-truck text-primary-dark me-2"></i>
                                        <span class="text-primary-dark">Atrasado:</span> >3 días enviado sin fecha estimada
                                    </li>
                                </ul>
                                <hr class="my-2">
                                <small class="text-muted d-block mt-2">
                                    <i class="fas fa-sort-numeric-down me-1"></i>
                                    La tabla está ordenada por prioridad: retrasados primero, luego por fecha más corta.
                                </small>
                            </div>
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

<!-- Script específico para seguimiento de pedidos enviados -->
<script>
// Función para añadir estilos dinámicos
function agregarEstilosEnviados() {
    // Verificar si ya existe un estilo con estos selectores para evitar duplicados
    const estilosExistentes = document.querySelectorAll('style[data-enviados-styles]');
    if (estilosExistentes.length > 0) return;
    
    const styleElement = document.createElement('style');
    styleElement.setAttribute('data-enviados-styles', 'true');
    styleElement.textContent = `
        @keyframes pulse-high-priority {
            0% { 
                background-color: rgba(220, 53, 69, 0.05);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.3);
            }
            70% { 
                background-color: rgba(220, 53, 69, 0.15);
                box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
            }
            100% { 
                background-color: rgba(220, 53, 69, 0.05);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }
        
        /* Colores de estados usando la paleta de root */
        .badge-estado-emitido {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary)) !important;
            color: white !important;
        }
        
        .badge-estado-confirmado {
            background: linear-gradient(135deg, var(--primary-light), var(--secondary)) !important;
            color: white !important;
        }
        
        .badge-estado-preparando {
            background: linear-gradient(135deg, var(--warning), #2d3138) !important;
            color: white !important;
        }
        
        .badge-estado-enviado {
            background: linear-gradient(135deg, var(--primary), var(--primary-light)) !important;
            color: white !important;
        }
        
        .badge-estado-retrasado {
            background: linear-gradient(135deg, var(--danger), #433766) !important;
            color: white !important;
        }
        
        .badge-estado-devuelto {
            background: linear-gradient(135deg, var(--light), var(--secondary)) !important;
            color: white !important;
        }
        
        .badge-estado-entregado {
            background: linear-gradient(135deg, var(--success), #273050) !important;
            color: white !important;
        }
        
        .badge-estado-anulado {
            background: linear-gradient(135deg, var(--gray-dark), #5a6268) !important;
            color: white !important;
            text-decoration: line-through;
        }
        
        /* Estadísticas específicas */
        .stats-card-pedido.estadistica-seguimiento {
            border-left: 4px solid var(--primary-dark) !important;
        }
        
        .stats-card-pedido.estadistica-enviados {
            border-left: 4px solid var(--primary) !important;
        }
        
        .stats-card-pedido.estadistica-retrasados {
            border-left: 4px solid var(--danger) !important;
        }
        
        .stats-card-pedido.estadistica-atrasados {
            border-left: 4px solid var(--warning) !important;
        }
        
        /* Hover effects */
        .hover-shadow-pedido:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(27, 32, 45, 0.1);
            transition: all 0.3s ease;
            background-color: rgba(27, 32, 45, 0.02) !important;
        }
        
        /* Botón deshabilitado para retrasados */
        .btn-outline-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            border-color: #6c757d !important;
            color: #6c757d !important;
        }
        
        /* Icono de check para entregado */
        .btn-outline-primary .fa-check-circle {
            color: var(--primary);
        }
        
        .btn-outline-primary:hover .fa-check-circle {
            color: white;
        }
        
        /* Avatar circle en modal */
        .avatar-circle-sm {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: white;
            font-size: 1.2rem;
        }
        
        /* Table styles */
        .table-warning {
            background-color: rgba(255, 193, 7, 0.05) !important;
            border-left: 2px solid var(--warning) !important;
        }
        
        .table-danger {
            border-left: 3px solid var(--danger) !important;
            background-color: rgba(52, 42, 86, 0.05) !important;
        }
        
        /* Estilos para casillas de verificación */
        .casillas-verificacion-entregado .form-check-input:checked,
        .casillas-verificacion-fecha .form-check-input:checked,
        .casillas-verificacion-retrasado .form-check-input:checked {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .casillas-verificacion-entregado .form-check-input:focus,
        .casillas-verificacion-fecha .form-check-input:focus,
        .casillas-verificacion-retrasado .form-check-input:focus {
            box-shadow: 0 0 0 0.25rem rgba(27, 32, 45, 0.25);
        }
        
        /* Barra de progreso de verificación */
        .verificacion-contador-entregado .progress-bar,
        .verificacion-contador-fecha .progress-bar,
        .verificacion-contador-retrasado .progress-bar {
            transition: width 0.3s ease;
        }
        
        .verificacion-contador-entregado .progress-bar {
            background-color: var(--primary-dark);
        }
        
        .verificacion-contador-fecha .progress-bar {
            background-color: var(--primary);
        }
        
        .verificacion-contador-retrasado .progress-bar {
            background-color: var(--danger);
        }
        
        /* Botón deshabilitado en modales */
        .modal-footer .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        /* Estilo para las casillas de verificación en listas */
        .casillas-verificacion-entregado .form-check-label,
        .casillas-verificacion-fecha .form-check-label,
        .casillas-verificacion-retrasado .form-check-label {
            cursor: pointer;
            user-select: none;
        }
    `;
    document.head.appendChild(styleElement);
}

// Función para manejar la verificación de casillas
function configurarVerificacionCasillas() {
    // Configurar para cada modal de entregado
    document.querySelectorAll('.modal[id^="modalEntregado"]').forEach(modal => {
        const idFactura = modal.id.replace('modalEntregado', '');
        const casillas = modal.querySelectorAll(`.casilla-entregado-${idFactura}`);
        const btnConfirmar = document.getElementById(`btnConfirmarEntregado${idFactura}`);
        const contadorSpan = document.getElementById(`contadorCasillasEntregado${idFactura}`);
        const barraProgreso = document.getElementById(`barraProgresoEntregado${idFactura}`);
        
        if (casillas.length && btnConfirmar && contadorSpan && barraProgreso) {
            casillas.forEach(casilla => {
                casilla.addEventListener('change', function() {
                    const casillasMarcadas = modal.querySelectorAll(`.casilla-entregado-${idFactura}:checked`);
                    const totalCasillas = casillas.length;
                    const marcadas = casillasMarcadas.length;
                    
                    // Actualizar contador
                    contadorSpan.textContent = marcadas;
                    
                    // Actualizar barra de progreso
                    const porcentaje = (marcadas / totalCasillas) * 100;
                    barraProgreso.style.width = `${porcentaje}%`;
                    barraProgreso.setAttribute('aria-valuenow', porcentaje);
                    
                    // Cambiar color de la barra según el progreso
                    if (porcentaje < 50) {
                        barraProgreso.className = 'progress-bar bg-danger';
                    } else if (porcentaje < 100) {
                        barraProgreso.className = 'progress-bar bg-warning';
                    } else {
                        barraProgreso.className = 'progress-bar bg-success';
                    }
                    
                    // Habilitar/deshabilitar botón
                    btnConfirmar.disabled = marcadas !== totalCasillas;
                });
            });
        }
    });
    
    // Configurar para cada modal de actualizar fecha
    document.querySelectorAll('.modal[id^="modalActualizarFecha"]').forEach(modal => {
        const idFactura = modal.id.replace('modalActualizarFecha', '');
        const casillas = modal.querySelectorAll(`.casilla-fecha-${idFactura}`);
        const btnConfirmar = document.getElementById(`btnConfirmarFecha${idFactura}`);
        const contadorSpan = document.getElementById(`contadorCasillasFecha${idFactura}`);
        const barraProgreso = document.getElementById(`barraProgresoFecha${idFactura}`);
        
        if (casillas.length && btnConfirmar && contadorSpan && barraProgreso) {
            casillas.forEach(casilla => {
                casilla.addEventListener('change', function() {
                    const casillasMarcadas = modal.querySelectorAll(`.casilla-fecha-${idFactura}:checked`);
                    const totalCasillas = casillas.length;
                    const marcadas = casillasMarcadas.length;
                    
                    // Actualizar contador
                    contadorSpan.textContent = marcadas;
                    
                    // Actualizar barra de progreso
                    const porcentaje = (marcadas / totalCasillas) * 100;
                    barraProgreso.style.width = `${porcentaje}%`;
                    barraProgreso.setAttribute('aria-valuenow', porcentaje);
                    
                    // Cambiar color de la barra según el progreso
                    if (porcentaje < 50) {
                        barraProgreso.className = 'progress-bar bg-danger';
                    } else if (porcentaje < 100) {
                        barraProgreso.className = 'progress-bar bg-warning';
                    } else {
                        barraProgreso.className = 'progress-bar bg-success';
                    }
                    
                    // Habilitar/deshabilitar botón
                    btnConfirmar.disabled = marcadas !== totalCasillas;
                });
            });
        }
    });
    
    // Configurar para cada modal de retrasado
    document.querySelectorAll('.modal[id^="modalRetrasado"]').forEach(modal => {
        const idFactura = modal.id.replace('modalRetrasado', '');
        const casillas = modal.querySelectorAll(`.casilla-retrasado-${idFactura}`);
        const btnConfirmar = document.getElementById(`btnConfirmarRetrasado${idFactura}`);
        const contadorSpan = document.getElementById(`contadorCasillasRetrasado${idFactura}`);
        const barraProgreso = document.getElementById(`barraProgresoRetrasado${idFactura}`);
        
        if (casillas.length && btnConfirmar && contadorSpan && barraProgreso) {
            casillas.forEach(casilla => {
                casilla.addEventListener('change', function() {
                    const casillasMarcadas = modal.querySelectorAll(`.casilla-retrasado-${idFactura}:checked`);
                    const totalCasillas = casillas.length;
                    const marcadas = casillasMarcadas.length;
                    
                    // Actualizar contador
                    contadorSpan.textContent = marcadas;
                    
                    // Actualizar barra de progreso
                    const porcentaje = (marcadas / totalCasillas) * 100;
                    barraProgreso.style.width = `${porcentaje}%`;
                    barraProgreso.setAttribute('aria-valuenow', porcentaje);
                    
                    // Cambiar color de la barra según el progreso
                    if (porcentaje < 50) {
                        barraProgreso.className = 'progress-bar bg-danger';
                    } else if (porcentaje < 100) {
                        barraProgreso.className = 'progress-bar bg-warning';
                    } else {
                        barraProgreso.className = 'progress-bar bg-success';
                    }
                    
                    // Habilitar/deshabilitar botón
                    btnConfirmar.disabled = marcadas !== totalCasillas;
                });
            });
        }
    });
}

// Función para resetear casillas al cerrar modal
function configurarResetCasillas() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            // Resetear todas las casillas dentro de este modal
            const casillas = modal.querySelectorAll('.form-check-input');
            casillas.forEach(casilla => {
                casilla.checked = false;
            });
            
            // Resetear botones de confirmación
            const botones = modal.querySelectorAll('button[type="submit"]');
            botones.forEach(boton => {
                boton.disabled = true;
            });
            
            // Resetear contadores
            const contadores = modal.querySelectorAll('[id*="contadorCasillas"]');
            contadores.forEach(contador => {
                contador.textContent = '0';
            });
            
            // Resetear barras de progreso
            const barras = modal.querySelectorAll('[id*="barraProgreso"]');
            barras.forEach(barra => {
                barra.style.width = '0%';
                barra.setAttribute('aria-valuenow', '0');
                barra.className = 'progress-bar';
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Añadir estilos dinámicos
    agregarEstilosEnviados();
    
    // Configurar verificación de casillas
    configurarVerificacionCasillas();
    
    // Configurar reset de casillas al cerrar modales
    configurarResetCasillas();
    
    // Auto-ocultar mensajes globales después de 5 segundos
    setTimeout(function() {
        const mensajeGlobal = document.getElementById('mensajeGlobal');
        if (mensajeGlobal) {
            mensajeGlobal.style.display = 'none';
        }
    }, 5000);
    
    // Configurar fechas mínimas en los modales y filtros
    const today = new Date().toISOString().split('T')[0];
    
    // Configurar filtros de fecha
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    
    if (fechaInicioInput && !fechaInicioInput.value) {
        // Establecer fecha de inicio como hace 30 días
        const fecha = new Date();
        fecha.setDate(fecha.getDate() - 30);
        fechaInicioInput.value = fecha.toISOString().split('T')[0];
    }
    
    if (fechaFinInput && !fechaFinInput.value) {
        fechaFinInput.value = today;
    }
    
    // Configurar fechas mínimas en los modales
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.value) {
            input.value = today;
        }
    });
    
    // Resaltar filas según prioridad
    document.querySelectorAll('tr[data-prioridad]').forEach(row => {
        const prioridad = row.getAttribute('data-prioridad');
        const diasRetraso = parseInt(row.getAttribute('data-dias-retraso') || 0);
        
        if (prioridad === 'Alta' || diasRetraso > 7) {
            row.style.animation = 'pulse-high-priority 2s infinite';
        } else if (prioridad === 'Media') {
            row.classList.add('table-warning');
        }
    });
    
    // Tooltips para botones
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Botones deshabilitados para retrasados
    document.querySelectorAll('.btn[disabled][title*="Ya está marcado como retrasado"]').forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            this.setAttribute('data-bs-toggle', 'tooltip');
            this.setAttribute('data-bs-placement', 'top');
            this.setAttribute('data-bs-title', 'Este pedido ya está marcado como retrasado');
        });
    });
    
    // Actualizar automáticamente los contadores de días cada hora
    function actualizarContadoresDias() {
        document.querySelectorAll('td:nth-child(5)').forEach(td => {
            const badge = td.querySelector('.badge');
            if (badge) {
                const texto = badge.textContent.trim();
                const match = texto.match(/(\d+)/);
                if (match) {
                    const dias = parseInt(match[1]);
                    if (texto.includes('Hoy')) {
                        // Después de medianoche, actualizar a "1 día"
                        const ahora = new Date();
                        if (ahora.getHours() === 0 && ahora.getMinutes() < 5) {
                            badge.textContent = '1 día';
                            badge.className = 'badge bg-primary';
                        }
                    }
                }
            }
        });
    }
    
    // Verificar fechas vencidas cada minuto
    setInterval(function() {
        const hoy = new Date().toISOString().split('T')[0];
        document.querySelectorAll('td:nth-child(6)').forEach(td => {
            const fechaText = td.querySelector('strong')?.textContent;
            if (fechaText) {
                const [dia, mes, ano] = fechaText.split('/');
                const fechaEstimada = `${ano}-${mes}-${dia}`;
                
                if (fechaEstimada < hoy && !td.classList.contains('table-danger')) {
                    td.classList.add('table-danger');
                    
                    // Actualizar estado visualmente
                    const estadoBadge = td.parentElement.querySelector('.badge-estado-enviado');
                    if (estadoBadge) {
                        estadoBadge.className = 'badge badge-estado-retrasado d-flex align-items-center justify-content-center gap-1';
                        estadoBadge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Retrasado';
                    }
                }
            }
        });
    }, 60000); // Cada minuto
    
    // Inicializar contadores
    actualizarContadoresDias();
    
    // Validación de fechas en filtros
    if (fechaInicioInput && fechaFinInput) {
        fechaInicioInput.addEventListener('change', function() {
            if (this.value && fechaFinInput.value && this.value > fechaFinInput.value) {
                fechaFinInput.value = this.value;
            }
        });
        
        fechaFinInput.addEventListener('change', function() {
            if (this.value && fechaInicioInput.value && this.value < fechaInicioInput.value) {
                fechaInicioInput.value = this.value;
            }
        });
    }
});
</script>