<?php
if (!isset($descuentos)) $descuentos = [];
?>
<!-- CSS Compartido -->
<link rel="stylesheet" href="assets/css/usuario.css">
<style>
    /* Estilos específicos para index de descuentos */
    .stat-card {
        border-left: 0.25rem solid !important;
        transition: var(--transition);
        border-radius: var(--border-radius);
        height: 100%;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }
    
    .border-start-primary { border-left-color: var(--primary-dark) !important; }
    .border-start-success { border-left-color: var(--success) !important; }
    .border-start-primary-light { border-left-color: var(--primary-light) !important; }
    .border-start-warning { border-left-color: var(--warning) !important; }
    
    .table-warning {
        background-color: rgba(21, 23, 29, 0.05);
    }
    
    .table-primary-light {
        background-color: rgba(58, 74, 107, 0.05);
    }
    
    .table-secondary {
        background-color: rgba(108, 117, 125, 0.05);
    }
    
    .table-danger {
        background-color: rgba(52, 42, 86, 0.05);
    }
    
    .code-column {
        min-width: 180px !important;
        max-width: 200px !important;
    }

    .app-column {
        min-width: 200px !important;
        max-width: 220px !important;
    }

    .app-compact .d-flex.align-items-center {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 10px !important;
        flex-wrap: nowrap !important;
    }

    .app-compact i {
        font-size: 1rem !important;
        flex-shrink: 0 !important;
    }

    .app-compact .text-truncate-custom {
        max-width: 150px !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        display: block !important;
    }

    .app-compact small.text-muted {
        font-size: 0.75rem !important;
        display: block !important;
        margin-top: 2px !important;
    }
    
    .type-column {
        min-width: 90px;
        max-width: 100px;
    }
    
    .value-column {
        min-width: 80px;
        max-width: 100px;
    }
    
    .conditions-column {
        min-width: 140px;
        max-width: 180px;
    }
    
    .usage-column {
        min-width: 100px;
        max-width: 120px;
    }
    
    .date-column {
        min-width: 140px;
        max-width: 160px;
    }
    
    .status-column {
        min-width: 90px;
        max-width: 110px;
    }
    
    .actions-column {
        min-width: 120px;
        max-width: 130px;
    }
    
    .text-truncate-custom {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        display: inline-block;
        max-width: 150px;
    }
    
    .conditions-compact .small {
        font-size: 0.8rem;
        line-height: 1.2;
    }
    
    .conditions-compact .small div {
        margin-bottom: 0.2rem;
    }
    
    .dates-compact {
        font-size: 0.85rem;
    }
    
    .dates-compact .small {
        font-size: 0.8rem;
    }
    
    .usage-compact .small {
        font-size: 0.85rem;
    }
    
    .app-compact .fw-semibold {
        font-size: 0.9rem;
        margin-bottom: 0.1rem;
    }
    
    .app-compact small {
        font-size: 0.75rem;
    }
    
    .badge.bg-info {
        background-color: var(--primary-light) !important;
    }
    
    .badge.bg-warning {
        background-color: var(--warning) !important;
        color: white !important;
    }
    
    .badge.bg-success {
        background-color: var(--success) !important;
    }
    
    .badge.bg-danger {
        background-color: var(--danger) !important;
    }
    
    .badge.bg-dark {
        background-color: var(--primary-dark) !important;
    }
    
    .btn-outline-warning {
        color: var(--warning) !important;
        border-color: var(--warning) !important;
    }
    
    .btn-outline-warning:hover {
        background-color: var(--warning) !important;
        border-color: var(--warning) !important;
        color: white !important;
    }
    
    .text-info {
        color: var(--primary-light) !important;
    }
    
    .text-warning {
        color: var(--warning) !important;
    }
    
    .text-success {
        color: var(--success) !important;
    }
    
    /* Responsive específico */
    @media (max-width: 1400px) and (min-width: 1200px) {
        .text-truncate-custom {
            max-width: 120px !important;
        }
        
        .app-compact .text-truncate-custom {
            max-width: 130px !important;
        }
    }
    
    @media (max-width: 1200px) {
        .code-column {
            min-width: 160px !important;
        }
        
        .app-column {
            min-width: 180px !important;
        }
        
        .conditions-column {
            min-width: 130px !important;
        }
        
        .date-column {
            min-width: 130px !important;
        }
    }
    
    @media (max-width: 992px) {
        .text-truncate-custom {
            max-width: 120px;
        }
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-tag text-primary-dark me-2"></i>
                    Gestión de Descuentos
                </h2>
                <p class="text-muted mb-0">Administra los descuentos de productos y categorías</p>
            </div>
            <a href="<?= BASE_URL ?>?c=Descuento&a=crear" class="btn btn-primary-dark">
                <i class="fas fa-plus-circle me-2"></i>Crear Descuento
            </a>
        </div>
    </div>

    <!-- Mensajes de Alerta -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle me-2 fs-5"></i>
                <div>
                    <strong class="me-2">¡Éxito!</strong> <?= $_SESSION['mensaje'] ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                <div>
                    <strong class="me-2">¡Error!</strong> <?= $_SESSION['error'] ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start-primary border-3 border-0 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary-dark text-uppercase mb-1">
                                Total Descuentos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800"><?= count($descuentos) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tags fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start-success border-3 border-0 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                Descuentos Activos
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count(array_filter($descuentos, fn($d) => $d['Activo'] && $d['FechaFin'] >= date('Y-m-d H:i:s'))) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bolt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start-primary-light border-3 border-0 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-primary-light text-uppercase mb-1">
                                Vigentes (Ahora)
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count(array_filter($descuentos, function($d) {
                                    $now = date('Y-m-d H:i:s');
                                    return $d['Activo'] && $d['FechaInicio'] <= $now && $d['FechaFin'] >= $now;
                                })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card border-start-warning border-3 border-0 h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs fw-bold text-warning text-uppercase mb-1">
                                Por Vencer (7 días)
                            </div>
                            <div class="h5 mb-0 fw-bold text-gray-800">
                                <?= count(array_filter($descuentos, function($d) {
                                    $weekFromNow = date('Y-m-d H:i:s', strtotime('+7 days'));
                                    return $d['Activo'] && $d['FechaFin'] <= $weekFromNow && $d['FechaFin'] >= date('Y-m-d H:i:s');
                                })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Descuentos -->
    <div class="card">
        <div class="card-header bg-primary-dark">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">
                    <i class="fas fa-list me-2"></i>Lista de Descuentos
                </h5>
                <div class="text-white small">
                    Mostrando <?= count($descuentos) ?> descuentos
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($descuentos)): ?>
                <div class="text-center py-5 no-results">
                    <div class="mb-4">
                        <i class="fas fa-tags fa-4x"></i>
                    </div>
                    <h5 class="text-primary-dark mb-3">No hay descuentos registrados</h5>
                    <p class="text-muted mb-4">Comienza creando tu primer descuento para ofrecer promociones a tus clientes</p>
                    <a href="<?= BASE_URL ?>?c=Descuento&a=crear" class="btn btn-primary-dark px-4">
                        <i class="fas fa-plus-circle me-2"></i>Crear Primer Descuento
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-primary-dark">
                            <tr>
                                <th class="ps-4 border-0 code-column">Código</th>
                                <th class="border-0 app-column">Aplicación</th>
                                <th class="border-0 type-column">Tipo</th>
                                <th class="border-0 value-column">Valor</th>
                                <th class="border-0 conditions-column">Condiciones</th>
                                <th class="border-0 usage-column">Usos</th>
                                <th class="border-0 date-column">Vigencia</th>
                                <th class="border-0 status-column">Estado</th>
                                <th class="text-center pe-4 border-0 actions-column">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($descuentos as $d): 
                                $now = date('Y-m-d H:i:s');
                                $isActive = $d['Activo'];
                                $isCurrent = $isActive && $d['FechaInicio'] <= $now && $d['FechaFin'] >= $now;
                                $isFuture = $isActive && $d['FechaInicio'] > $now;
                                $isExpired = $d['FechaFin'] < $now;
                                $isExhausted = $d['Max_Usos_Global'] > 0 && $d['Usos_Globales'] >= $d['Max_Usos_Global'];
                                $esAutomatico = $d['Monto_Minimo'] > 0;
                                
                                // Determinar clase de estado
                                $estadoClase = '';
                                $estadoTexto = '';
                                if (!$isActive) {
                                    $estadoClase = 'table-secondary';
                                    $estadoTexto = 'Inactivo';
                                } elseif ($isExhausted) {
                                    $estadoClase = 'table-danger';
                                    $estadoTexto = 'Agotado';
                                } elseif ($isExpired) {
                                    $estadoClase = 'table-warning';
                                    $estadoTexto = 'Expirado';
                                } elseif ($isFuture) {
                                    $estadoClase = 'table-primary-light';
                                    $estadoTexto = 'Programado';
                                } else {
                                    $estadoTexto = 'En Uso';
                                    $estadoClase = '';
                                }
                            ?>
                                <tr class="<?= $estadoClase ?> hover-shadow-detalle">
                                    <!-- Código -->
                                    <td class="ps-4">
                                        <div class="d-flex flex-column align-items-start">
                                            <div class="mb-1">
                                                <span class="badge bg-light text-dark border">
                                                    <i class="fas fa-<?= $esAutomatico ? 'bolt' : 'tag' ?> me-1 text-<?= $esAutomatico ? 'warning' : 'muted' ?>"></i>
                                                    <span class="text-truncate-custom" title="<?= htmlspecialchars($d['Codigo']) ?>">
                                                        <?= htmlspecialchars($d['Codigo']) ?>
                                                    </span>
                                                </span>
                                            </div>
                                            <?php if ($esAutomatico): ?>
                                            <div>
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">
                                                    <i class="fas fa-bolt me-1"></i>Automático
                                                </span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Aplicación -->
                                    <td>
                                        <div class="app-compact">
                                            <?php if ($d['ArticuloNombre']): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-cube text-primary-dark me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold text-truncate-custom" title="<?= htmlspecialchars($d['ArticuloNombre']) ?>">
                                                            <?= htmlspecialchars($d['ArticuloNombre']) ?>
                                                        </div>
                                                        <small class="text-muted">Artículo</small>
                                                    </div>
                                                </div>
                                            <?php elseif ($d['ProductoNombre']): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-palette text-success me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold text-truncate-custom" title="<?= htmlspecialchars($d['ProductoNombre']) ?>">
                                                            <?= htmlspecialchars($d['ProductoNombre']) ?>
                                                        </div>
                                                        <small class="text-muted">Producto/Variante</small>
                                                    </div>
                                                </div>
                                            <?php elseif ($d['CategoriaNombre']): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-layer-group text-secondary me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold text-truncate-custom" title="<?= htmlspecialchars($d['CategoriaNombre']) ?>">
                                                            <?= htmlspecialchars($d['CategoriaNombre']) ?>
                                                        </div>
                                                        <small class="text-muted">Categoría</small>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">No definido</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Tipo -->
                                    <td>
                                        <span class="badge bg-<?= $d['Tipo'] == 'Porcentaje' ? 'primary-light' : 'warning' ?>">
                                            <i class="fas fa-<?= $d['Tipo'] == 'Porcentaje' ? 'percent' : 'dollar-sign' ?> me-1"></i>
                                            <?= $d['Tipo'] ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Valor -->
                                    <td>
                                        <span class="fw-bold text-<?= $d['Tipo'] == 'Porcentaje' ? 'primary-light' : 'warning' ?>">
                                            <?= $d['Tipo'] == 'Porcentaje' ? $d['Valor'] . '%' : '$' . number_format($d['Valor'], 1) ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Condiciones -->
                                    <td>
                                        <div class="conditions-compact">
                                            <?php if ($d['Monto_Minimo'] > 0): ?>
                                                <div class="text-success small">
                                                    <i class="fas fa-trophy me-1"></i>Mín: $<?= number_format($d['Monto_Minimo'], 1) ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($d['Max_Usos_Global'] > 0): ?>
                                                <div class="text-primary-light small">
                                                    <i class="fas fa-globe me-1"></i>Global: <?= $d['Usos_Globales'] ?>/<?= $d['Max_Usos_Global'] ?>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($d['Max_Usos_Usuario'] > 0): ?>
                                                <div class="text-warning small">
                                                    <i class="fas fa-user me-1"></i>Usuario: <?= $d['Max_Usos_Usuario'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Usos -->
                                    <td>
                                        <div class="usage-compact">
                                            <div class="small">
                                                <div class="fw-semibold">
                                                    <i class="fas fa-chart-bar me-1 text-primary-dark"></i>
                                                    <?= $d['Usos_Globales'] ?> usos
                                                </div>
                                                <div class="text-muted">
                                                    <i class="fas fa-users me-1"></i>
                                                    <?= $d['UsuariosUnicos'] ?? 0 ?> usuarios
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Vigencia -->
                                    <td>
                                        <div class="dates-compact">
                                            <div class="fw-semibold <?= $isExpired ? 'text-danger' : 'text-success' ?>">
                                                <?= date('d/m/Y', strtotime($d['FechaInicio'])) ?> - <?= date('d/m/Y', strtotime($d['FechaFin'])) ?>
                                            </div>
                                            <?php if ($isExhausted): ?>
                                                <small class="text-danger">
                                                    <i class="fas fa-ban me-1"></i>Agotado
                                                </small>
                                            <?php elseif ($isExpired): ?>
                                                <small class="text-danger">
                                                    <i class="fas fa-clock me-1"></i>Expirado
                                                </small>
                                            <?php elseif ($isFuture): ?>
                                                <small class="text-primary-light">
                                                    <i class="fas fa-clock me-1"></i>Programado
                                                </small>
                                            <?php else: ?>
                                                <small class="text-success">
                                                    <i class="fas fa-play-circle me-1"></i>En curso
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Estado -->
                                    <td>
                                        <span class="badge bg-<?= 
                                            !$isActive ? 'danger' : 
                                            ($isExhausted ? 'dark' : 
                                            ($isExpired ? 'warning' : 
                                            ($isFuture ? 'primary-light' : 'success'))) 
                                        ?>">
                                            <i class="fas fa-<?= 
                                                !$isActive ? 'pause-circle' : 
                                                ($isExhausted ? 'ban' : 
                                                ($isExpired ? 'exclamation-triangle' : 
                                                ($isFuture ? 'clock' : 'play-circle'))) 
                                            ?> me-1"></i>
                                            <?= $estadoTexto ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Acciones -->
                                    <td class="text-center pe-4">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?= BASE_URL ?>?c=Descuento&a=editar&id=<?= $d['ID_Descuento'] ?>" 
                                            class="btn btn-outline-warning" 
                                            data-bs-toggle="tooltip" 
                                            title="Editar descuento">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>?c=Descuento&a=estadisticas&id=<?= $d['ID_Descuento'] ?>" 
                                            class="btn btn-outline-primary" 
                                            data-bs-toggle="tooltip" 
                                            title="Ver estadísticas">
                                                <i class="fas fa-chart-bar"></i>
                                            </a>
                                            <button onclick="confirmarEliminacion(<?= $d['ID_Descuento'] ?>, '<?= htmlspecialchars(addslashes($d['Codigo'])) ?>')" 
                                                    class="btn btn-outline-danger"
                                                    data-bs-toggle="tooltip"
                                                    title="Eliminar descuento"
                                                    <?= ($isCurrent && $d['Usos_Globales'] > 0) ? 'disabled' : '' ?>>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark border-0 pb-0">
                <h5 class="modal-title text-white">
                    <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="text-center mb-3">
                    <i class="fas fa-trash-alt fa-3x text-danger opacity-50 mb-3"></i>
                </div>
                <p class="text-center mb-1">¿Estás seguro de que deseas eliminar el descuento?</p>
                <h5 class="text-center text-danger fw-bold" id="codigoDescuento"></h5>
                <p class="text-muted small text-center mt-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Esta acción no se puede deshacer y afectará los productos asociados.
                </p>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <a href="#" id="btnEliminarConfirm" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i>Eliminar Descuento
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmarEliminacion(id, codigo) {
    document.getElementById('codigoDescuento').textContent = codigo;
    document.getElementById('btnEliminarConfirm').href = '<?= BASE_URL ?>?c=Descuento&a=eliminar&id=' + id;
    
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>