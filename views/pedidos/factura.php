<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Factura</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
.factura-box {
    max-width: 800px;
    margin: auto;
    padding: 30px;
    border: 1px solid #ddd;
    background: #fff;
}
.qr-box { text-align:right; }
</style>

</head>
<body>

<div class="factura-box">

<h2 class="mb-4">Factura #<?= $factura['ID_Factura'] ?></h2>

<div class="d-flex justify-content-between">

    <div>
        <strong>Cliente:</strong><br>
        <?= $factura['Nombre'] ?> <?= $factura['Apellido'] ?><br>
        <?= $factura['Correo'] ?>
    </div>

    <div class="qr-box">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= BASE_URL ?>?c=Pedido&a=factura&id=<?= $factura['ID_Factura'] ?>"
             alt="QR">
    </div>

</div>

<hr>

<h5>Productos</h5>

<table class="table">
<thead>
<tr>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Total</th>
</tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr>
    <td><?= $item['N_Articulo'] ?></td>
    <td><?= $item['Cantidad'] ?></td>
    <td>$<?= number_format($item['PrecioTotal'],0,',','.') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<h4 class="text-end">Total: $<?= number_format($factura['Monto_Total'],0,',','.') ?></h4>

<div class="text-center mt-4">
    <a class="btn btn-dark" href="<?= BASE_URL ?>?c=FacturaPDF&a=generar&id=<?= $factura['ID_Factura'] ?>">Descargar PDF</a>
</div>

</div>

</body>
</html>
