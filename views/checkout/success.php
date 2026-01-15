<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Compra Exitosa - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">

<?php 
$categorias = [];
include "views/layout/nav.php"; 
?>

<div class="container mt-5 mb-5">
    <div class="card shadow p-4">

        <div class="text-center mb-4">
            <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
            <h2 class="mt-3">¡Compra Realizada con Éxito!</h2>
            <p class="text-muted">Tu compra ha sido procesada correctamente.</p>
        </div>

        <h4 class="mb-3">
            <i class="fas fa-file-invoice"></i> Factura #<?php echo $pedido['ID_Factura']; ?>
        </h4>

        <div class="row mb-4">
            <div class="col-md-6">
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['Fecha_Factura'])); ?></p>
                <p><strong>Estado:</strong> <span class="badge bg-success"><?php echo $pedido['Estado']; ?></span></p>
            </div>
            <div class="col-md-6">
                <p><strong>Método de Pago:</strong> <?php echo $pedido['ID_Metodo_Pago']; ?></p>
                <p><strong>Dirección de Envío:</strong> <?php echo $pedido['Direccion_Envio']; ?></p>
            </div>
        </div>

        <h5 class="mb-3"><i class="fas fa-box"></i> Productos Comprados</h5>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Foto</th>
                        <th>Producto</th>
                        <th>Talla</th>
                        <th>Color</th>
                        <th>Precio</th>
                        <th>Cant.</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $it): ?>
                    <?php
                    // Preparar especificaciones para success.php también
                    $especificaciones = [];
                    
                    // Usar atributos dinámicos si existen
                    if (!empty($it['Atributos'])) {
                        foreach ($it['Atributos'] as $atributo) {
                            if (!empty($atributo['nombre']) && !empty($atributo['valor'])) {
                                $especificaciones[] = $atributo['nombre'] . ': ' . $atributo['valor'];
                            }
                        }
                    }
                    // Fallback a atributos básicos
                    else {
                        if (!empty($it['ValorAtributo1'])) $especificaciones[] = $it['ValorAtributo1'];
                        if (!empty($it['ValorAtributo2'])) $especificaciones[] = $it['ValorAtributo2'];
                        if (!empty($it['ValorAtributo3'])) $especificaciones[] = $it['ValorAtributo3'];
                    }
                    
                    $especificacionesStr = !empty($especificaciones) ? implode(' | ', $especificaciones) : '—';
                    ?>
                    <tr>
                        <td>
                            <img src="<?php echo $fotoUrl; ?>" 
                                width="70" 
                                class="rounded"
                                alt="<?php echo htmlspecialchars($it['N_Articulo']); ?>"
                                onerror="this.src='<?php echo BASE_URL; ?>assets/img/placeholder.png'">
                        </td>
                        <td><?php echo htmlspecialchars($it['N_Articulo']); ?></td>
                        <td><?php echo htmlspecialchars($especificacionesStr); ?></td>
                        <td>$<?php echo number_format($it['Precio_Unitario'], 0, ',', '.'); ?></td>
                        <td><?php echo $it['Cantidad']; ?></td>
                        <td class="text-success fw-bold">
                            $<?php echo number_format($it['Subtotal'], 0, ',', '.'); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-end">
            <h4 class="fw-bold text-success">
                Total: $<?php echo number_format($pedido['Monto_Total'], 0, ',', '.'); ?>
            </h4>
        </div>

        <div class="text-center mt-4">
            <a href="<?php echo BASE_URL . '?c=FacturaPDF&a=generar&id=' . $pedido['ID_Factura']; ?>" 
               class="btn btn-danger btn-lg me-3">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
            <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-store"></i> Volver a la tienda
            </a>
        </div>

    </div>
</div>

<!-- INCLUIR BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>