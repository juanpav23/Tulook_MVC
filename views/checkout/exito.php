<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compra Exitosa - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<!-- ✅ NAVBAR CORREGIDO -->
<?php 
// Pasar variables necesarias al nav
$categorias = []; // O cargar desde tu modelo si es necesario
include "views/layout/nav.php"; 
?>

<div class="container mt-5">
    <div class="card p-4 text-center shadow">
        <div class="card-body">
            <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
            <h2 class="card-title text-success">¡Compra Realizada con Éxito!</h2>
            <p class="card-text">Gracias por tu compra en TuLook. Tu pedido ha sido procesado correctamente.</p>
            
            <div class="alert alert-info mt-3">
                <h5><i class="fas fa-file-invoice"></i> Factura #<?= htmlspecialchars($pedido['ID_Factura'] ?? '') ?></h5>
                <p class="mb-1"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['Fecha_Factura'] ?? '')) ?></p>
                <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-success"><?= $pedido['Estado'] ?? 'Confirmado' ?></span></p>
                <p class="mb-1"><strong>Método de pago:</strong> <?= htmlspecialchars($pedido['T_Pago'] ?? 'No especificado') ?></p>
            </div>

            <!-- Resumen de la compra -->
            <div class="row justify-content-center mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <strong>Resumen de la compra</strong>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio Unit.</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $subtotal_factura = 0;
                                        foreach ($items as $item): 
                                            $precio_unitario = floatval($item['Precio_Unitario'] ?? 0);
                                            $cantidad = intval($item['Cantidad'] ?? 1);
                                            $subtotal_item = $precio_unitario * $cantidad;
                                            $subtotal_factura += $subtotal_item;
                                            
                                            // Construir nombre del producto con atributos
                                            $nombre_producto = $item['N_Articulo'] ?? 'Producto';
                                            $especificaciones = [];
                                            
                                            if (!empty($item['N_Color'])) {
                                                $especificaciones[] = $item['N_Color'];
                                            }
                                            if (!empty($item['N_Talla'])) {
                                                $especificaciones[] = "Talla " . $item['N_Talla'];
                                            }
                                            if (!empty($item['ValorAtributo1'])) {
                                                $especificaciones[] = $item['ValorAtributo1'];
                                            }
                                            if (!empty($item['ValorAtributo2'])) {
                                                $especificaciones[] = $item['ValorAtributo2'];
                                            }
                                            if (!empty($item['ValorAtributo3'])) {
                                                $especificaciones[] = $item['ValorAtributo3'];
                                            }
                                            
                                            if (!empty($especificaciones)) {
                                                $nombre_producto .= " (" . implode(", ", $especificaciones) . ")";
                                            }
                                        ?>
                                        <tr>
                                            <td class="text-start"><?= htmlspecialchars($nombre_producto) ?></td>
                                            <td><?= $cantidad ?></td>
                                            <td>$<?= number_format($precio_unitario, 0, ',', '.') ?></td>
                                            <td>$<?= number_format($subtotal_item, 0, ',', '.') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <?php
                                        // Calcular IVA si no viene en la factura
                                        $iva_porcentaje = 19;
                                        $iva_factura = floatval($pedido['IVA'] ?? 0);
                                        $subtotal_factura = floatval($pedido['Subtotal'] ?? $subtotal_factura);
                                        $total_factura = floatval($pedido['Monto_Total'] ?? 0);
                                        
                                        // Si no tenemos IVA en la factura, calcularlo
                                        if ($iva_factura == 0 && $subtotal_factura > 0) {
                                            $iva_factura = $subtotal_factura * ($iva_porcentaje / 100);
                                            $total_factura = $subtotal_factura + $iva_factura;
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                            <td><strong>$<?= number_format($subtotal_factura, 0, ',', '.') ?></strong></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>IVA (<?= $iva_porcentaje ?>%):</strong></td>
                                            <td><strong class="text-info">$<?= number_format($iva_factura, 0, ',', '.') ?></strong></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td colspan="3" class="text-end"><strong>TOTAL:</strong></td>
                                            <td><strong class="text-success">$<?= number_format($total_factura, 0, ',', '.') ?></strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de envío -->
            <?php if (!empty($pedido['Direccion'])): ?>
            <div class="alert alert-secondary mt-4">
                <h6><i class="fas fa-truck me-2"></i>Información de envío</h6>
                <p class="mb-0">
                    <strong>Dirección:</strong> 
                    <?= htmlspecialchars($pedido['Direccion'] ?? '') ?>, 
                    <?= htmlspecialchars($pedido['Ciudad'] ?? '') ?> - 
                    <?= htmlspecialchars($pedido['Departamento'] ?? '') ?>
                    <?php if (!empty($pedido['CodigoPostal'])): ?>
                        (CP: <?= htmlspecialchars($pedido['CodigoPostal']) ?>)
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>

            <!-- Mensajes de sesión -->
            <?php if (isset($_SESSION['mensaje_ok'])): ?>
                <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= $_SESSION['mensaje_ok'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_ok']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['mensaje_info'])): ?>
                <div class="alert alert-info alert-dismissible fade show mt-3" role="alert">
                    <i class="fas fa-info-circle me-2"></i><?= $_SESSION['mensaje_info'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['mensaje_info']); ?>
            <?php endif; ?>

            <div class="mt-4">
                <a class="btn btn-dark btn-lg me-3" href="<?= BASE_URL ?>?c=FacturaPDF&a=generar&id=<?= (int)($pedido['ID_Factura'] ?? 0) ?>">
                    <i class="fas fa-file-pdf"></i> Descargar Factura PDF
                </a>
                <a class="btn btn-outline-primary btn-lg me-3" href="<?= BASE_URL ?>?c=Checkout&a=historial">
                    <i class="fas fa-history"></i> Ver Historial
                </a>
                <a class="btn btn-outline-secondary btn-lg" href="<?= BASE_URL ?>">
                    <i class="fas fa-store"></i> Volver a la Tienda
                </a>
            </div>
            
            <div class="mt-4 text-muted small">
                <p>
                    <i class="fas fa-envelope me-1"></i>
                    Hemos enviado los detalles de tu compra a 
                    <strong><?= htmlspecialchars($pedido['Correo'] ?? 'tu correo') ?></strong>
                </p>
                <p class="mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Puedes revisar el estado de tu pedido en cualquier momento desde tu perfil.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- ✅ INCLUIR BOOTSTRAP JS PARA QUE EL NAVBAR FUNCIONE -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>