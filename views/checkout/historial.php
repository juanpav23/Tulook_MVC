<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: " . BASE_URL . "?c=Usuario&a=login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Compras - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<!-- NAVBAR -->
<?php 
$categorias = [];
include "views/layout/nav.php"; 
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            <i class="fas fa-history text-primary me-2"></i>Historial de Compras
        </h1>
        <a href="<?= BASE_URL ?>" class="btn btn-outline-primary">
            <i class="fas fa-store me-2"></i>Volver a la Tienda
        </a>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['mensaje_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['mensaje_ok']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_ok']); ?>
    <?php endif; ?>

    <!-- Estadísticas -->
    <?php if (!empty($estadisticas)): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <strong><i class="fas fa-chart-line me-2"></i>Estadísticas de Compras</strong>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Compras</h5>
                                    <h2 class="text-primary"><?= $estadisticas['Total_Compras'] ?? 0 ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-muted">Total Gastado</h5>
                                    <h2 class="text-success">$<?= number_format($estadisticas['Total_Gastado'] ?? 0, 0, ',', '.') ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-muted">Promedio por Compra</h5>
                                    <h2 class="text-info">$<?= number_format($estadisticas['Promedio_Compra'] ?? 0, 0, ',', '.') ?></h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="text-muted">Primera Compra</h5>
                                    <h6 class="text-dark"><?= !empty($estadisticas['Primera_Compra']) ? date('d/m/Y', strtotime($estadisticas['Primera_Compra'])) : 'N/A' ?></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Lista de Compras -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <strong><i class="fas fa-receipt me-2"></i>Todas tus Compras</strong>
        </div>
        <div class="card-body">
            <?php if (!empty($compras)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Factura #</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Método Pago</th>
                                <th>Subtotal</th>
                                <th>IVA</th>
                                <th>Total</th>
                                <th>Items</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $compra): ?>
                            <tr>
                                <td>
                                    <strong>#<?= $compra['ID_Factura'] ?></strong>
                                </td>
                                <td>
                                    <?= date('d/m/Y H:i', strtotime($compra['Fecha_Factura'])) ?>
                                </td>
                                <td>
                                    <?php 
                                    $estado_class = [
                                        'Emitido' => 'warning',
                                        'Confirmado' => 'success',
                                        'Preparando' => 'info',
                                        'Enviado' => 'primary',
                                        'Entregado' => 'success',
                                        'Retrasado' => 'danger',
                                        'Devuelto' => 'secondary',
                                        'Anulado' => 'dark'
                                    ][$compra['Estado']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $estado_class ?>">
                                        <?= $compra['Estado'] ?>
                                    </span>
                                </td>
                                <td>
                                    <i class="fas fa-credit-card me-1 text-muted"></i>
                                    <?= htmlspecialchars($compra['T_Pago'] ?? 'No especificado') ?>
                                </td>
                                <td>
                                    $<?= number_format($compra['Subtotal'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td>
                                    $<?= number_format($compra['IVA'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td class="fw-bold">
                                    $<?= number_format($compra['Monto_Total'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?= $compra['Total_Items'] ?? 0 ?> items
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>?c=Checkout&a=detalleCompra&id=<?= $compra['ID_Factura'] ?>" 
                                           class="btn btn-outline-info" title="Ver Detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>?c=FacturaPDF&a=generar&id=<?= $compra['ID_Factura'] ?>" 
                                           class="btn btn-outline-danger" title="Descargar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="<?= BASE_URL ?>?c=Checkout&a=exito&id=<?= $compra['ID_Factura'] ?>" 
                                           class="btn btn-outline-success" title="Ver Resumen">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No tienes compras registradas</h4>
                    <p class="text-muted">Realiza tu primera compra para ver tu historial aquí.</p>
                    <a href="<?= BASE_URL ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-store me-2"></i>Ir de compras
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="alert alert-info mt-4">
        <h6><i class="fas fa-info-circle me-2"></i>Información importante</h6>
        <ul class="mb-0">
            <li>Las facturas están disponibles para descargar en formato PDF</li>
            <li>Puedes ver el detalle de cada compra haciendo clic en el botón <i class="fas fa-eye text-info"></i></li>
            <li>El estado de tus pedidos se actualiza automáticamente</li>
            <li>Para consultas sobre pedidos específicos, contacta con nuestro servicio al cliente</li>
        </ul>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>