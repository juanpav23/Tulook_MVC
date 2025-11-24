<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial de compras</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "views/layout/nav.php"; ?>

<div class="container my-5">

<h2 class="mb-4">📦 Historial de compras</h2>

<?php if (empty($compras)): ?>
    <div class="alert alert-info">No tienes compras registradas.</div>
<?php else: ?>

    <div class="list-group">

        <?php foreach ($compras as $c): ?>
            <a href="<?= BASE_URL ?>?c=Pedido&a=factura&id=<?= $c['ID_Factura'] ?>"
               class="list-group-item list-group-item-action">

                <div class="d-flex justify-content-between">
                    <strong>Factura #<?= $c['ID_Factura'] ?></strong>
                    <span>$<?= number_format($c['Monto_Total'],0,',','.') ?></span>
                </div>

                <small class="text-muted">
                    <?= date("d/m/Y h:i A", strtotime($c['Fecha_Factura'])) ?>
                </small>

            </a>
        <?php endforeach; ?>

    </div>

<?php endif; ?>

</div>

</body>
</html>
