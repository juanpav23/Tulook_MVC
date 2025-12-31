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
    <title>Detalle de Compra - TuLook</title>
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
            <i class="fas fa-file-invoice text-primary me-2"></i>Detalle de Factura
        </h1>
        <div>
            <a href="<?= BASE_URL ?>?c=Checkout&a=historial" class="btn btn-outline-primary me-2">
                <i class="fas fa-arrow-left me-2"></i>Volver al Historial
            </a>
            <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary">
                <i class="fas fa-store me-2"></i>Tienda
            </a>
        </div>
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

    <!-- Información de la factura -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <strong><i class="fas fa-info-circle me-2"></i>Información de la Factura</strong>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Factura #:</strong> <?= $factura['ID_Factura'] ?? '' ?></p>
                    <p><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($factura['Fecha_Factura'] ?? '')) ?></p>
                    <p><strong>Estado:</strong> 
                        <span class="badge bg-success"><?= $factura['Estado'] ?? 'Confirmado' ?></span>
                    </p>
                    <p><strong>Método de Pago:</strong> <?= $factura['T_Pago'] ?? 'No especificado' ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Cliente:</strong> <?= htmlspecialchars(($factura['Nombre'] ?? '') . ' ' . ($factura['Apellido'] ?? '')) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($factura['Correo'] ?? '') ?></p>
                    <?php if (!empty($factura['Direccion'])): ?>
                    <p><strong>Dirección:</strong> 
                        <?= htmlspecialchars($factura['Direccion'] ?? '') ?>, 
                        <?= htmlspecialchars($factura['Ciudad'] ?? '') ?>, 
                        <?= htmlspecialchars($factura['Departamento'] ?? '') ?>
                        <?php if (!empty($factura['CodigoPostal'])): ?>
                            (CP: <?= htmlspecialchars($factura['CodigoPostal']) ?>)
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Productos comprados -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white">
            <strong><i class="fas fa-box me-2"></i>Productos Comprados</strong>
        </div>
        <div class="card-body">
            <?php if (!empty($items)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th>Especificaciones</th>
                                <th>Cantidad</th>
                                <th>Precio Unit.</th>
                                <th>Subtotal</th>
                                <th>Descuento</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subtotal_total = 0;
                            $descuento_total = 0;
                            foreach ($items as $item): 
                                $subtotal_item = floatval($item['Subtotal'] ?? 0);
                                $descuento_item = floatval($item['Descuento_Aplicado'] ?? 0);
                                $subtotal_total += $subtotal_item;
                                $descuento_total += $descuento_item;
                                
                                // Construir especificaciones
                                $especificaciones = [];
                                if (!empty($item['ValorAtributo1'])) $especificaciones[] = $item['ValorAtributo1'];
                                if (!empty($item['ValorAtributo2'])) $especificaciones[] = $item['ValorAtributo2'];
                                if (!empty($item['ValorAtributo3'])) $especificaciones[] = $item['ValorAtributo3'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['N_Articulo'] ?? 'Producto') ?></strong><br>
                                    <small class="text-muted">Código: <?= $item['ID_Producto'] ?? '' ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($especificaciones)): ?>
                                        <?= implode(', ', array_map('htmlspecialchars', $especificaciones)) ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin especificaciones</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $item['Cantidad'] ?? 1 ?></td>
                                <td>$<?= number_format($item['Precio_Unitario'] ?? 0, 0, ',', '.') ?></td>
                                <td>$<?= number_format($subtotal_item, 0, ',', '.') ?></td>
                                <td>
                                    <?php if ($descuento_item > 0): ?>
                                        <span class="text-danger">-$<?= number_format($descuento_item, 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-3">
                    <i class="fas fa-box-open fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No se encontraron productos para esta factura</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Resumen de precios -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <strong><i class="fas fa-calculator me-2"></i>Resumen de Pagos</strong>
        </div>
        <div class="card-body">
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <table class="table table-bordered">
                        <tr>
                            <td><strong>Subtotal:</strong></td>
                            <td class="text-end">$<?= number_format($factura['Subtotal'] ?? $subtotal_total, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td><strong>Descuentos:</strong></td>
                            <td class="text-end text-danger">-$<?= number_format($descuento_total, 0, ',', '.') ?></td>
                        </tr>
                        <tr>
                            <td><strong>IVA (19%):</strong></td>
                            <td class="text-end text-info">$<?= number_format($factura['IVA'] ?? 0, 0, ',', '.') ?></td>
                        </tr>
                        <tr class="table-success">
                            <td><strong>TOTAL PAGADO:</strong></td>
                            <td class="text-end fw-bold">$<?= number_format($factura['Monto_Total'] ?? 0, 0, ',', '.') ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="mt-4 text-center">
        <a href="<?= BASE_URL ?>?c=FacturaPDF&a=generar&id=<?= $factura['ID_Factura'] ?? 0 ?>" 
           class="btn btn-danger btn-lg me-3">
            <i class="fas fa-file-pdf me-2"></i>Descargar PDF
        </a>
        <a href="<?= BASE_URL ?>?c=Checkout&a=exito&id=<?= $factura['ID_Factura'] ?? 0 ?>" 
           class="btn btn-primary btn-lg me-3">
            <i class="fas fa-receipt me-2"></i>Ver Resumen
        </a>
        <a href="<?= BASE_URL ?>?c=Checkout&a=historial" 
           class="btn btn-outline-secondary btn-lg">
            <i class="fas fa-history me-2"></i>Volver al Historial
        </a>
    </div>
</div>

<!-- SCRIPTS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>