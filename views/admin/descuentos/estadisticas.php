<?php
// views/admin/descuento/estadisticas.php
if (!isset($descuento) || !$descuento) {
    header("Location: " . BASE_URL . "?c=Descuento&a=index");
    exit;
}

$usuarios = $descuento['usuarios'] ?? [];
$estadisticas = $descuento['estadisticas'] ?? [];

// Calcular porcentajes
$porcentajeUsosGlobal = $descuento['Max_Usos_Global'] > 0 
    ? min(100, ($descuento['Usos_Globales'] / $descuento['Max_Usos_Global']) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas del Descuento</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }
        
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .progress {
            height: 1rem;
            border-radius: 0.5rem;
        }
        
        .progress-bar {
            border-radius: 0.5rem;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="page-header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-chart-bar text-primary me-2"></i>
                        Estadísticas del Descuento
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-tag me-1"></i>
                        <?= htmlspecialchars($descuento['Codigo']) ?> - 
                        <?= $descuento['Tipo'] == 'Porcentaje' ? $descuento['Valor'] . '%' : '$' . number_format($descuento['Valor'], 2) ?>
                    </p>
                </div>
                <div>
                    <a href="<?= BASE_URL ?>?c=Descuento&a=editar&id=<?= $descuento['ID_Descuento'] ?>" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>Editar
                    </a>
                    <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <!-- Resumen Estadístico -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card border-left-primary h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted small fw-bold">Usos Totales</div>
                                <div class="h2 mb-0"><?= $descuento['Usos_Globales'] ?></div>
                            </div>
                            <div class="icon-circle bg-primary text-white">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <?php if ($descuento['Max_Usos_Global'] > 0): ?>
                        <div class="mt-2">
                            <div class="progress">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: <?= $porcentajeUsosGlobal ?>%"
                                     aria-valuenow="<?= $porcentajeUsosGlobal ?>" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                    <?= number_format($porcentajeUsosGlobal, 1) ?>%
                                </div>
                            </div>
                            <small class="text-muted">
                                <?= $descuento['Usos_Globales'] ?> de <?= $descuento['Max_Usos_Global'] ?>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-left-success h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted small fw-bold">Usuarios Únicos</div>
                                <div class="h2 mb-0"><?= count($usuarios) ?></div>
                            </div>
                            <div class="icon-circle bg-success text-white">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-user-check me-1"></i>
                                <?= $estadisticas['PromedioUsosPorUsuario'] ?? 0 ?> usos promedio
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-left-info h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted small fw-bold">Tasa de Uso</div>
                                <div class="h2 mb-0"><?= number_format($estadisticas['PromedioUsosPorUsuario'] ?? 0, 1) ?></div>
                            </div>
                            <div class="icon-circle bg-info text-white">
                                <i class="fas fa-percentage"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-chart-pie me-1"></i>
                                Usos por usuario
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card border-left-warning h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <div class="text-uppercase text-muted small fw-bold">Máximo Usos</div>
                                <div class="h2 mb-0"><?= $estadisticas['MaxUsosUsuario'] ?? 0 ?></div>
                            </div>
                            <div class="icon-circle bg-warning text-white">
                                <i class="fas fa-crown"></i>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-user-tie me-1"></i>
                                Máximo usos por un usuario
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row mb-4">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Distribución de Usos
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="usoChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>Uso por Fecha
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="fechaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Usuarios -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>Usuarios que han usado este descuento
                </h6>
                <span class="badge bg-primary"><?= count($usuarios) ?> usuarios</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Usuario</th>
                                <th>Correo</th>
                                <th class="text-center">Usos</th>
                                <th class="text-center">Último Uso</th>
                                <th class="text-center">% del Total</th>
                                <th class="pe-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-users-slash fa-2x mb-3"></i>
                                        <p class="mb-0">No hay usuarios registrados para este descuento</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $usuario): 
                                    $porcentajeUsuario = $descuento['Usos_Globales'] > 0 
                                        ? ($usuario['Usos'] / $descuento['Usos_Globales']) * 100 
                                        : 0;
                                    $alcanzoLimite = $descuento['Max_Usos_Usuario'] > 0 && $usuario['Usos'] >= $descuento['Max_Usos_Usuario'];
                                ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="user-avatar me-3">
                                                    <?= strtoupper(substr($usuario['Nombre'], 0, 1)) ?>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold"><?= htmlspecialchars($usuario['Nombre'] . ' ' . $usuario['Apellido']) ?></div>
                                                    <small class="text-muted">ID: <?= $usuario['ID_Usuario'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= $usuario['Correo'] ?>" class="text-decoration-none">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?= htmlspecialchars($usuario['Correo']) ?>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-<?= $alcanzoLimite ? 'danger' : 'primary' ?>">
                                                <?= $usuario['Usos'] ?> 
                                                <?php if ($descuento['Max_Usos_Usuario'] > 0): ?>
                                                    /<?= $descuento['Max_Usos_Usuario'] ?>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                <?php 
                                                if ($usuario['Fecha_Ultimo_Uso'] && $usuario['Fecha_Ultimo_Uso'] !== 'Nunca' && $usuario['Fecha_Ultimo_Uso'] !== 'NULL') {
                                                    echo date('d/m/Y H:i', strtotime($usuario['Fecha_Ultimo_Uso']));
                                                } else {
                                                    echo 'Nunca';
                                                }
                                                ?>
                                            </small>
                                        </td>
                                        <td class="text-center">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar bg-info" 
                                                     role="progressbar" 
                                                     style="width: <?= $porcentajeUsuario ?>%"
                                                     aria-valuenow="<?= $porcentajeUsuario ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small><?= number_format($porcentajeUsuario, 1) ?>%</small>
                                        </td>
                                        <td class="pe-4">
                                            <?php if ($alcanzoLimite): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-ban me-1"></i>Límite alcanzado
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>Activo
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de distribución de usos
        const usoCtx = document.getElementById('usoChart').getContext('2d');
        const usoChart = new Chart(usoCtx, {
            type: 'doughnut',
            data: {
                labels: ['Usados', 'Disponibles'],
                datasets: [{
                    data: [
                        <?= $descuento['Usos_Globales'] ?>,
                        <?= max(0, $descuento['Max_Usos_Global'] - $descuento['Usos_Globales']) ?>
                    ],
                    backgroundColor: [
                        'rgba(78, 115, 223, 0.8)',
                        'rgba(220, 220, 220, 0.5)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.raw + ' usos';
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de uso por fecha (ejemplo estático)
        const fechaCtx = document.getElementById('fechaChart').getContext('2d');
        const fechaChart = new Chart(fechaCtx, {
            type: 'line',
            data: {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                datasets: [{
                    label: 'Usos por mes',
                    data: [12, 19, 8, 15, 22, 18],
                    borderColor: 'rgba(28, 200, 138, 1)',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de usos'
                        }
                    }
                }
            }
        });
    });
    </script>
</body>
</html>