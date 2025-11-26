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
            <p class="card-text">Gracias por tu compra en TuLook.</p>
            
            <div class="alert alert-info mt-3">
                <h5><i class="fas fa-file-invoice"></i> Factura #<?= htmlspecialchars($pedido['ID_Factura'] ?? '') ?></h5>
                <p class="mb-1"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['Fecha_Factura'] ?? '')) ?></p>
                <p class="mb-1"><strong>Total:</strong> $<?= number_format($pedido['Monto_Total'] ?? 0, 0, ',', '.') ?></p>
                <p class="mb-0"><strong>Estado:</strong> <span class="badge bg-success"><?= $pedido['Estado'] ?? 'Confirmado' ?></span></p>
            </div>

            <div class="mt-4">
                <a class="btn btn-dark btn-lg me-3" href="<?= BASE_URL ?>?c=FacturaPDF&a=generar&id=<?= (int)($pedido['ID_Factura'] ?? 0) ?>">
                    <i class="fas fa-file-pdf"></i> Descargar Factura PDF
                </a>
                <a class="btn btn-outline-secondary btn-lg" href="<?= BASE_URL ?>">
                    <i class="fas fa-store"></i> Volver a la Tienda
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ✅ INCLUIR BOOTSTRAP JS PARA QUE EL NAVBAR FUNCIONE -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>