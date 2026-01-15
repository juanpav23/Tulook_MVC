<?php
if (!isset($descuentos)) $descuentos = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Descuentos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 1rem 1.25rem;
        }
        
        .stat-card {
            border-left: 0.25rem solid !important;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .border-start-primary { border-left-color: var(--primary-color) !important; }
        .border-start-success { border-left-color: var(--success-color) !important; }
        .border-start-info { border-left-color: var(--info-color) !important; }
        .border-start-warning { border-left-color: var(--warning-color) !important; }
        
        .table {
            margin-bottom: 0;
        }
        
        .table > :not(caption) > * > * {
            padding: 1rem 0.75rem;
            vertical-align: middle;
        }
        
        .table thead th {
            background-color: #f8f9fc;
            color: #6e707e;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e3e6f0;
        }
        
        .table tbody tr {
            transition: background-color 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 0.35rem;
            font-size: 0.8rem;
        }
        
        .btn {
            border-radius: 0.35rem;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
        }
        
        .btn-group .btn {
            border-radius: 0.35rem;
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 0.5rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .page-header {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .text-primary { color: var(--primary-color) !important; }
        .bg-primary { background-color: var(--primary-color) !important; }
        
        .text-success { color: var(--success-color) !important; }
        .bg-success { background-color: var(--success-color) !important; }
        
        .text-info { color: var(--info-color) !important; }
        .bg-info { background-color: var(--info-color) !important; }
        
        .text-warning { color: var(--warning-color) !important; }
        .bg-warning { background-color: var(--warning-color) !important; }
        
        .text-danger { color: var(--danger-color) !important; }
        .bg-danger { background-color: var(--danger-color) !important; }
        
        .text-secondary { color: var(--secondary-color) !important; }
        .bg-secondary { background-color: var(--secondary-color) !important; }
        
        .modal-content {
            border-radius: 0.5rem;
            border: none;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid #e3e6f0;
        }
        
        .modal-footer {
            border-top: 1px solid #e3e6f0;
        }
        
        .empty-state {
            padding: 3rem 1rem;
        }
        
        .empty-state i {
            opacity: 0.5;
        }
        
        .table-warning {
            background-color: rgba(255, 193, 7, 0.05);
        }
        
        .table-info {
            background-color: rgba(23, 162, 184, 0.05);
        }
        
        .table-secondary {
            background-color: rgba(108, 117, 125, 0.05);
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-tag text-primary me-2"></i>
                        Gestión de Descuentos
                    </h1>
                    <p class="text-muted mb-0">Administra los descuentos de productos y categorías</p>
                </div>
                <a href="<?= BASE_URL ?>?c=Descuento&a=crear" class="btn btn-primary btn-lg shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Crear Descuento
                </a>
            </div>
        </div>

        <!-- Mensajes de Alerta -->
        <?php if (isset($_SESSION['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
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
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
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
                <div class="card stat-card border-start-primary border-3 border-0 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-primary text-uppercase mb-1">
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
                <div class="card stat-card border-start-success border-3 border-0 shadow h-100 py-2">
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
                <div class="card stat-card border-start-info border-3 border-0 shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs fw-bold text-info text-uppercase mb-1">
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
                <div class="card stat-card border-start-warning border-3 border-0 shadow h-100 py-2">
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
        <div class="card shadow-sm border-0">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Lista de Descuentos
                    </h5>
                    <div class="text-muted small">
                        Mostrando <?= count($descuentos) ?> descuentos
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($descuentos)): ?>
                    <div class="text-center py-5 empty-state">
                        <div class="mb-4">
                            <i class="fas fa-tags fa-4x text-muted opacity-50"></i>
                        </div>
                        <h4 class="text-muted mb-3">No hay descuentos registrados</h4>
                        <p class="text-muted mb-4">Comienza creando tu primer descuento para ofrecer promociones a tus clientes</p>
                        <a href="<?= BASE_URL ?>?c=Descuento&a=crear" class="btn btn-primary btn-lg px-4">
                            <i class="fas fa-plus-circle me-2"></i>Crear Primer Descuento
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 border-0">Código</th>
                                    <th class="border-0">Aplicación</th>
                                    <th class="border-0">Tipo</th>
                                    <th class="border-0">Valor</th>
                                    <th class="border-0">Vigencia</th>
                                    <th class="border-0">Estado</th>
                                    <th class="text-center pe-4 border-0">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($descuentos as $d): 
                                    $now = date('Y-m-d H:i:s');
                                    $isActive = $d['Activo'];
                                    $isCurrent = $isActive && $d['FechaInicio'] <= $now && $d['FechaFin'] >= $now;
                                    $isFuture = $isActive && $d['FechaInicio'] > $now;
                                    $isExpired = $d['FechaFin'] < $now;
                                    
                                    // Determinar clase de estado
                                    $estadoClase = '';
                                    $estadoTexto = '';
                                    if (!$isActive) {
                                        $estadoClase = 'table-secondary';
                                        $estadoTexto = 'Inactivo';
                                    } elseif ($isExpired) {
                                        $estadoClase = 'table-warning';
                                        $estadoTexto = 'Expirado';
                                    } elseif ($isFuture) {
                                        $estadoClase = 'table-info';
                                        $estadoTexto = 'Programado';
                                    } else {
                                        $estadoTexto = 'Activo';
                                    }
                                ?>
                                    <tr class="<?= $estadoClase ?>">
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border fs-6 fw-normal">
                                                    <i class="fas fa-tag me-1 text-muted"></i>
                                                    <?= htmlspecialchars($d['Codigo']) ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">ID: <?= $d['ID_Descuento'] ?></small>
                                        </td>
                                        <td>
                                            <?php if ($d['ArticuloNombre']): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-cube text-primary me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold"><?= htmlspecialchars($d['ArticuloNombre']) ?></div>
                                                        <small class="text-muted">Artículo</small>
                                                    </div>
                                                </div>
                                            <?php elseif ($d['ProductoNombre']): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-palette text-success me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold"><?= htmlspecialchars($d['ProductoNombre']) ?></div>
                                                        <small class="text-muted">Producto/Variante</small>
                                                    </div>
                                                </div>
                                            <?php elseif ($d['CategoriaNombre']): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-layer-group text-secondary me-2"></i>
                                                    <div>
                                                        <div class="fw-semibold"><?= htmlspecialchars($d['CategoriaNombre']) ?></div>
                                                        <small class="text-muted">Categoría</small>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <span class="badge bg-light text-dark">No definido</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $d['Tipo'] == 'Porcentaje' ? 'info' : 'warning' ?>">
                                                <i class="fas fa-<?= $d['Tipo'] == 'Porcentaje' ? 'percent' : 'dollar-sign' ?> me-1"></i>
                                                <?= $d['Tipo'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-<?= $d['Tipo'] == 'Porcentaje' ? 'info' : 'warning' ?>">
                                                <?= $d['Tipo'] == 'Porcentaje' ? $d['Valor'] . '%' : '$' . number_format($d['Valor'], 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <div class="fw-semibold <?= $isExpired ? 'text-danger' : 'text-success' ?>">
                                                    <?= date('d/m/Y', strtotime($d['FechaInicio'])) ?> - <?= date('d/m/Y', strtotime($d['FechaFin'])) ?>
                                                </div>
                                                <?php if ($isExpired): ?>
                                                    <small class="text-danger">
                                                        <i class="fas fa-clock me-1"></i>Expirado
                                                    </small>
                                                <?php elseif ($isFuture): ?>
                                                    <small class="text-info">
                                                        <i class="fas fa-clock me-1"></i>Programado
                                                    </small>
                                                <?php else: ?>
                                                    <small class="text-success">
                                                        <i class="fas fa-play-circle me-1"></i>En curso
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $isActive ? ($isCurrent ? 'success' : ($isFuture ? 'info' : 'warning')) : 'danger' ?>">
                                                <i class="fas fa-<?= $isActive ? ($isCurrent ? 'play-circle' : ($isFuture ? 'clock' : 'exclamation-triangle')) : 'pause-circle' ?> me-1"></i>
                                                <?= $estadoTexto ?>
                                            </span>
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="<?= BASE_URL ?>?c=Descuento&a=ver&id=<?= $d['ID_Descuento'] ?>" 
                                                   class="btn btn-outline-info" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="<?= BASE_URL ?>?c=Descuento&a=editar&id=<?= $d['ID_Descuento'] ?>" 
                                                   class="btn btn-outline-warning" 
                                                   data-bs-toggle="tooltip" 
                                                   title="Editar descuento">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button onclick="confirmarEliminacion(<?= $d['ID_Descuento'] ?>, '<?= htmlspecialchars(addslashes($d['Codigo'])) ?>')" 
                                                        class="btn btn-outline-danger"
                                                        data-bs-toggle="tooltip"
                                                        title="Eliminar descuento"
                                                        <?= $isCurrent ? 'disabled' : '' ?>>
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
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
</body>
</html>