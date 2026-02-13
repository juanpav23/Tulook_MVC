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
    
    // Prioridad alta (retraso >5 días)
    if (isset($pedido['dias_retraso']) && $pedido['dias_retraso'] > 5) {
        $prioridadAlta++;
    }
}

// Transportadoras populares predefinidas
$transportadorasPopulares = [
    'Servientrega' => 'primary-dark',
    'Interrapidisimo' => 'primary-dark',
    'DHL' => 'primary-dark',
    'Coordinadora' => 'primary-dark',
    'Envía' => 'primary-dark',
    'Deprisa' => 'primary-dark',
    'FedEx' => 'primary-dark',
    'TCC' => 'primary-dark',
    'Otro' => 'primary-light'
];

// Obtener transportadoras únicas de la base de datos
$transportadorasUnicas = [];
foreach ($pedidos as $pedido) {
    if (!empty($pedido['Transportadora']) && !in_array($pedido['Transportadora'], $transportadorasUnicas)) {
        $transportadorasUnicas[] = $pedido['Transportadora'];
    }
}

// Combinar transportadoras de la base de datos con las populares
$todasTransportadoras = array_unique(array_merge(array_keys($transportadorasPopulares), $transportadorasUnicas));
sort($todasTransportadoras);
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

    <!-- Barra de Búsqueda y Filtros MEJORADA -->
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
                        <?php foreach ($todasTransportadoras as $transp): 
                            $colorClase = $transportadorasPopulares[$transp] ?? 'primary-light';
                        ?>
                            <option value="<?= htmlspecialchars($transp) ?>" 
                                    <?= ($_GET['transportadora'] ?? '') === $transp ? 'selected' : '' ?>
                                    data-color="<?= $colorClase ?>">
                                <?= htmlspecialchars($transp) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="estado_filtro" class="form-label">Estado de seguimiento</label>
                    <select class="form-select" id="estado_filtro" name="estado_filtro">
                        <option value="">Todos</option>
                        <option value="retrasados" <?= ($_GET['estado_filtro'] ?? '') === 'retrasados' ? 'selected' : '' ?>>Solo Retrasados</option>
                        <option value="en_transito" <?= ($_GET['estado_filtro'] ?? '') === 'en_transito' ? 'selected' : '' ?>>Solo En Tránsito</option>
                        <option value="proximos_vencer" <?= ($_GET['estado_filtro'] ?? '') === 'proximos_vencer' ? 'selected' : '' ?>>Próximos a Vencer</option>
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

    <!-- Estadísticas de Seguimiento Mejoradas - CORREGIDAS -->
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
            <div class="card stats-card-pedido estadistica-proximos-vencer">
                <div class="card-body">
                    <h5 class="card-title text-primary-dark">Próximos a Vencer</h5>
                    <h3 class="card-text"><?= $proximosVencer ?></h3>
                    <small><i class="fas fa-calendar-exclamation text-primary-dark"></i> ≤2 días para entrega</small>
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
        <div class="card-body p-0">
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
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-primary-dark">
                            <tr>
                                <th width="50px">#</th>
                                <th width="110px">Código</th>
                                <th width="160px">Cliente</th>
                                <th width="110px">Fecha Envío</th>
                                <th width="90px">Tiempo</th>
                                <th width="130px">Fecha Estimada</th>
                                <th width="120px">Estado</th>
                                <th width="140px">Transportadora</th>
                                <th width="100px" class="text-center">Acciones</th>
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
                                        $colorPrioridad = 'primary';
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
                                            <small class="badge bg-<?= $colorPrioridad ?> mt-1">
                                                <i class="fas fa-flag me-1"></i><?= $nivelPrioridad ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="cliente-info">
                                            <strong class="d-block text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?>">
                                                <?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?>
                                            </strong>
                                            <small class="text-muted text-truncate d-block" style="max-width: 150px;" title="<?= htmlspecialchars($pedido['Correo']) ?>">
                                                <?= htmlspecialchars($pedido['Correo']) ?>
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <?= !empty($pedido['Fecha_Envio']) ? date('d/m/Y', strtotime($pedido['Fecha_Envio'])) : 'N/A' ?>
                                        <?php if (!empty($pedido['NombreEnvio'])): ?>
                                            <small class="d-block text-muted text-truncate" style="max-width: 100px;" title="Por: <?= $pedido['NombreEnvio'] ?>">
                                                <?= 'Por: ' . $pedido['NombreEnvio'] ?>
                                            </small>
                                        <?php endif; ?>
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
                                            <small class="d-block text-danger mt-1">
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
                                                        <i class="fas fa-exclamation-triangle text-primary"></i> Vencido hace <?= abs($diasFaltantes) ?> días
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
                                            <div class="transporte-info">
                                                <?php 
                                                $transpColor = $transportadorasPopulares[$pedido['Transportadora']] ?? 'primary-light';
                                                ?>
                                                <strong class="small d-block">
                                                    <span class="badge-transportadora bg-<?= $transpColor ?> me-1" style="width: 8px; height: 8px; display: inline-block; border-radius: 50%;"></span>
                                                    <?= $pedido['Transportadora'] ?>
                                                </strong>
                                                <?php if (!empty($pedido['Numero_Guia'])): ?>
                                                    <small class="text-muted text-truncate d-block" style="max-width: 130px;" title="Guía: <?= $pedido['Numero_Guia'] ?>">
                                                        <i class="fas fa-barcode me-1"></i> <?= $pedido['Numero_Guia'] ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Sin información</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm btn-group-pedidos d-flex justify-content-center">
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
                <div class="row mt-4 p-3">
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
                                                <th>Con guía de envío:</th>
                                                <td class="text-end">
                                                    <?php
                                                    $conGuia = 0;
                                                    foreach ($pedidos as $p) {
                                                        if (!empty($p['Numero_Guia'])) $conGuia++;
                                                    }
                                                    echo $conGuia;
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Próximos a vencer (≤2 días):</th>
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
                                        <span class="text-primary-dark">Retrasado:</span> Fecha estimada de entrega vencida
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
<link rel="stylesheet" href="assets/css/enviados.css">

<!-- JS -->
<script src="assets/js/pedido.js"></script>
<script src="assets/js/enviados.js"></script>