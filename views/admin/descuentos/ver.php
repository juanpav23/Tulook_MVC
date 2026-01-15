<?php
if (!isset($descuento) || !$descuento) {
    header("Location: " . BASE_URL . "?c=Descuento&a=index");
    exit;
}

// Calcular estado actual
$now = date('Y-m-d H:i:s');
$isActive = $descuento['Activo'];
$isCurrent = $isActive && $descuento['FechaInicio'] <= $now && $descuento['FechaFin'] >= $now;
$isFuture = $isActive && $descuento['FechaInicio'] > $now;
$isExpired = $descuento['FechaFin'] < $now;

// Determinar texto y color del estado
if (!$isActive) {
    $estadoTexto = 'Inactivo';
    $estadoColor = 'secondary';
} elseif ($isExpired) {
    $estadoTexto = 'Expirado';
    $estadoColor = 'warning';
} elseif ($isFuture) {
    $estadoTexto = 'Programado';
    $estadoColor = 'info';
} else {
    $estadoTexto = 'Activo';
    $estadoColor = 'success';
}

// Determinar aplicación
$aplicacionTexto = 'No definido';
$aplicacionColor = 'secondary';
$aplicacionIcono = 'question-circle';

if ($descuento['ID_Articulo'] && $descuento['ArticuloNombre']) {
    $aplicacionTexto = 'Artículo: ' . htmlspecialchars($descuento['ArticuloNombre']);
    $aplicacionColor = 'primary';
    $aplicacionIcono = 'cube';
} elseif ($descuento['ID_Producto'] && $descuento['ProductoNombre']) {
    $aplicacionTexto = 'Producto: ' . htmlspecialchars($descuento['ProductoNombre']);
    $aplicacionColor = 'success';
    $aplicacionIcono = 'palette';
} elseif ($descuento['ID_Categoria'] && $descuento['CategoriaNombre']) {
    $aplicacionTexto = 'Categoría: ' . htmlspecialchars($descuento['CategoriaNombre']);
    $aplicacionColor = 'secondary';
    $aplicacionIcono = 'layer-group';
}

// Calcular progreso de vigencia
$totalDuration = strtotime($descuento['FechaFin']) - strtotime($descuento['FechaInicio']);
$elapsedDuration = time() - strtotime($descuento['FechaInicio']);
$progressPercentage = $totalDuration > 0 ? min(max(($elapsedDuration / $totalDuration) * 100, 0), 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Descuento</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }
        
        .progress {
            height: 10px;
            border-radius: 10px;
        }
        
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.5rem 1.25rem;
            transition: all 0.2s ease;
        }
        
        .btn-group .btn {
            border-radius: 8px;
        }
        
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .bg-primary {
            background-color: var(--primary-color) !important;
        }
        
        .text-success {
            color: var(--success-color) !important;
        }
        
        .bg-success {
            background-color: var(--success-color) !important;
        }
        
        .text-warning {
            color: var(--warning-color) !important;
        }
        
        .bg-warning {
            background-color: var(--warning-color) !important;
        }
        
        .text-info {
            color: var(--info-color) !important;
        }
        
        .bg-info {
            background-color: var(--info-color) !important;
        }
        
        .text-danger {
            color: var(--danger-color) !important;
        }
        
        .bg-danger {
            background-color: var(--danger-color) !important;
        }
        
        .text-secondary {
            color: var(--secondary-color) !important;
        }
        
        .bg-secondary {
            background-color: var(--secondary-color) !important;
        }
        
        .page-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .progress-bar {
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="page-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h1 class="h3 mb-1">
                                <i class="fas fa-eye text-info me-2"></i>
                                Detalles del Descuento
                            </h1>
                            <p class="text-muted mb-0">Información completa del descuento seleccionado</p>
                        </div>
                        <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la lista
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12">
                <!-- Tarjeta Principal -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-tag me-2"></i>Información General
                            </h5>
                            <span class="badge bg-<?= $estadoColor ?> status-badge">
                                <i class="fas fa-<?= $isActive ? ($isCurrent ? 'play-circle' : ($isFuture ? 'clock' : 'exclamation-triangle')) : 'pause-circle' ?> me-1"></i>
                                <?= $estadoTexto ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Código -->
                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-tag text-primary me-2 fs-5"></i>
                                    <label class="form-label fw-bold mb-0">Código del Descuento</label>
                                </div>
                                <div class="ps-4">
                                    <h4 class="text-primary fw-bold"><?= htmlspecialchars($descuento['Codigo']) ?></h4>
                                    <small class="text-muted">ID: <?= $descuento['ID_Descuento'] ?></small>
                                </div>
                            </div>

                            <!-- Aplicación -->
                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-<?= $aplicacionIcono ?> text-<?= $aplicacionColor ?> me-2 fs-5"></i>
                                    <label class="form-label fw-bold mb-0">Aplicación</label>
                                </div>
                                <div class="ps-4">
                                    <span class="badge bg-<?= $aplicacionColor ?> bg-opacity-10 text-<?= $aplicacionColor ?> border border-<?= $aplicacionColor ?> border-opacity-25 p-2">
                                        <?= $aplicacionTexto ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Tipo y Valor -->
                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-percentage text-info me-2 fs-5"></i>
                                    <label class="form-label fw-bold mb-0">Tipo y Valor</label>
                                </div>
                                <div class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-<?= $descuento['Tipo'] == 'Porcentaje' ? 'info' : 'warning' ?> me-2">
                                            <i class="fas fa-<?= $descuento['Tipo'] == 'Porcentaje' ? 'percent' : 'dollar-sign' ?> me-1"></i>
                                            <?= $descuento['Tipo'] ?>
                                        </span>
                                        <span class="fw-bold fs-5 text-<?= $descuento['Tipo'] == 'Porcentaje' ? 'info' : 'warning' ?>">
                                            <?= $descuento['Tipo'] == 'Porcentaje' ? $descuento['Valor'] . '%' : '$' . number_format($descuento['Valor'], 2) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado Activo/Inactivo -->
                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-power-off text-<?= $descuento['Activo'] ? 'success' : 'danger' ?> me-2 fs-5"></i>
                                    <label class="form-label fw-bold mb-0">Estado del Descuento</label>
                                </div>
                                <div class="ps-4">
                                    <span class="badge bg-<?= $descuento['Activo'] ? 'success' : 'danger' ?>">
                                        <i class="fas fa-<?= $descuento['Activo'] ? 'check' : 'times' ?> me-1"></i>
                                        <?= $descuento['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Vigencia -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Período de Vigencia
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="text-center p-3 border rounded bg-light feature-card">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-play-circle text-success me-2"></i>
                                        <label class="form-label fw-bold mb-0">Fecha de Inicio</label>
                                    </div>
                                    <div class="fw-bold fs-5 text-success">
                                        <?= date('d/m/Y', strtotime($descuento['FechaInicio'])) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('H:i', strtotime($descuento['FechaInicio'])) ?> horas
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="text-center p-3 border rounded bg-light feature-card">
                                    <div class="d-flex align-items-center justify-content-center mb-2">
                                        <i class="fas fa-stop-circle text-danger me-2"></i>
                                        <label class="form-label fw-bold mb-0">Fecha de Fin</label>
                                    </div>
                                    <div class="fw-bold fs-5 text-danger">
                                        <?= date('d/m/Y', strtotime($descuento['FechaFin'])) ?>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('H:i', strtotime($descuento['FechaFin'])) ?> horas
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Barra de Progreso de Vigencia -->
                        <div class="mt-4">
                            <div class="d-flex justify-content-between mb-1">
                                <small class="text-muted">Progreso de vigencia</small>
                                <small class="text-muted"><?= number_format($progressPercentage, 1) ?>%</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-<?= $estadoColor ?>" 
                                     role="progressbar" 
                                     style="width: <?= $progressPercentage ?>%"
                                     aria-valuenow="<?= $progressPercentage ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Inicio: <?= date('d/m/Y', strtotime($descuento['FechaInicio'])) ?></small>
                                <small class="text-muted">Fin: <?= date('d/m/Y', strtotime($descuento['FechaFin'])) ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta de Detalles Específicos -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Detalles Específicos
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <!-- Artículo -->
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded h-100 feature-card">
                                    <i class="fas fa-cube fa-2x text-primary mb-2"></i>
                                    <div class="fw-bold">Artículo</div>
                                    <?php if ($descuento['ID_Articulo'] && $descuento['ArticuloNombre']): ?>
                                        <div class="text-success fw-semibold"><?= htmlspecialchars($descuento['ArticuloNombre']) ?></div>
                                        <small class="text-muted">ID: <?= $descuento['ID_Articulo'] ?></small>
                                    <?php else: ?>
                                        <div class="text-muted">No aplica</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Producto -->
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded h-100 feature-card">
                                    <i class="fas fa-palette fa-2x text-success mb-2"></i>
                                    <div class="fw-bold">Producto/Variante</div>
                                    <?php if ($descuento['ID_Producto'] && $descuento['ProductoNombre']): ?>
                                        <div class="text-success fw-semibold"><?= htmlspecialchars($descuento['ProductoNombre']) ?></div>
                                        <small class="text-muted">ID: <?= $descuento['ID_Producto'] ?></small>
                                    <?php else: ?>
                                        <div class="text-muted">No aplica</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Categoría -->
                            <div class="col-md-4 mb-3">
                                <div class="text-center p-3 border rounded h-100 feature-card">
                                    <i class="fas fa-layer-group fa-2x text-secondary mb-2"></i>
                                    <div class="fw-bold">Categoría</div>
                                    <?php if ($descuento['ID_Categoria'] && $descuento['CategoriaNombre']): ?>
                                        <div class="text-success fw-semibold"><?= htmlspecialchars($descuento['CategoriaNombre']) ?></div>
                                        <small class="text-muted">ID: <?= $descuento['ID_Categoria'] ?></small>
                                    <?php else: ?>
                                        <div class="text-muted">No aplica</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card mt-4">
                    <div class="card-body text-center py-4">
                        <div class="btn-group" role="group">
                            <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver a la Lista
                            </a>
                            <a href="<?= BASE_URL ?>?c=Descuento&a=editar&id=<?= $descuento['ID_Descuento'] ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Editar Descuento
                            </a>
                            <button onclick="confirmarEliminacion(<?= $descuento['ID_Descuento'] ?>, '<?= htmlspecialchars(addslashes($descuento['Codigo'])) ?>')" 
                                    class="btn btn-danger"
                                    <?= $isCurrent ? 'disabled' : '' ?>>
                                <i class="fas fa-trash me-2"></i>Eliminar Descuento
                            </button>
                        </div>
                        <?php if ($isCurrent): ?>
                            <div class="mt-2">
                                <small class="text-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    No se puede eliminar un descuento que está actualmente activo
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
    </script>
</body>
</html>