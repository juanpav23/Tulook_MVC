<?php
// views/pedidos/factura_pdf.php
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<style>
body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#222; }
.header { display:flex; justify-content:space-between; align-items:flex-start; }
.header .brand { font-size:18px; font-weight:700; margin-bottom:10px; }
.table { width:100%; border-collapse: collapse; margin-top:15px; }
.table th, .table td { border: 1px solid #ddd; padding: 8px; }
.total { text-align:right; margin-top:15px; font-size:16px; font-weight:700; }
</style>
</head>

<body>

<div class="header">
    <div>
        <div class="brand">TuLook</div>

        <div><strong>Dirección:</strong> 
            <?= htmlspecialchars($factura['Direccion_Completa'] ?? 
                ($factura['Direccion'] ?? '') . ', ' . 
                ($factura['Ciudad'] ?? '') . ', ' . 
                ($factura['Departamento'] ?? '')
            ) ?>
        </div>

        <div><strong>Código Postal:</strong> 
            <?= htmlspecialchars($factura['CodigoPostal'] ?? '-') ?>
        </div>

        <div><strong>Cliente:</strong> 
            <?= htmlspecialchars($factura['Nombre_Cliente'] ?? 
                ($factura['Nombre'] ?? '') . ' ' . ($factura['Apellido'] ?? '')
            ) ?>
        </div>

        <div><strong>Correo:</strong> <?= htmlspecialchars($factura['Email_Cliente'] ?? $factura['Correo'] ?? '') ?></div>
    </div>

    <div>
        <div><strong>Factura #</strong><?= htmlspecialchars($factura['ID_Factura'] ?? '') ?></div>
        <div><strong>Fecha:</strong> 
            <?= date('d/m/Y H:i', strtotime($factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'))) ?>
        </div>
        <div><strong>Método Pago:</strong> <?= htmlspecialchars($factura['Metodo_Pago'] ?? $factura['T_Pago'] ?? '') ?></div>
    </div>
</div>


<table class="table">
<thead>
<tr>
    <th>Producto</th>
    <th>Especificaciones</th>
    <th>Cantidad</th>
    <th>Precio Unit.</th>
    <th>Subtotal</th>
</tr>
</thead>

<tbody>
<?php 
$totalGeneral = 0;
foreach ($items as $it): 
    // CALCULAR PRECIOS CORRECTAMENTE
    $precioUnitario = floatval($it['Precio_Unitario'] ?? $it['Precio'] ?? 0);
    $cantidad = intval($it['Cantidad'] ?? 1);
    $subtotal = floatval($it['Subtotal'] ?? ($precioUnitario * $cantidad));
    $totalGeneral += $subtotal;
    
    // ✅ SIMPLIFICADO: USAR SOLO LAS ESPECIFICACIONES COMPLETAS
    $especificacionesMostrar = '';
    
    // Si tiene Color y no es "No especificado", mostrarlo
    if (!empty($it['Color']) && $it['Color'] !== 'No especificado') {
        $especificacionesMostrar = $it['Color'];
        
        // Si también tiene Talla específica, agregarla
        if (!empty($it['Talla']) && $it['Talla'] !== 'Única' && $it['Talla'] !== '—') {
            $especificacionesMostrar .= ' | Talla: ' . $it['Talla'];
        }
    }
    
    // ✅ PARA PRODUCTOS COMO PERFUME: Usar el campo Especificaciones
    if (empty($especificacionesMostrar) && !empty($it['Especificaciones'])) {
        $especificacionesMostrar = $it['Especificaciones'];
    }
    
    // ✅ LIMPIAR EL "| —" que aparece
    $especificacionesMostrar = str_replace(' | —', '', $especificacionesMostrar);
    $especificacionesMostrar = str_replace('— | ', '', $especificacionesMostrar);
    
    // Si aún está vacío, mostrar "—"
    if (empty($especificacionesMostrar) || trim($especificacionesMostrar) === '—') {
        $especificacionesMostrar = '—';
    }
?>
<tr>
    <td><?= htmlspecialchars($it['Nombre_Producto'] ?? $it['Producto'] ?? 'Producto') ?></td>
    <td><?= htmlspecialchars($especificacionesMostrar) ?></td>
    <td><?= $cantidad ?></td>
    <td>$<?= number_format($precioUnitario, 0, ',', '.') ?></td>
    <td>$<?= number_format($subtotal, 0, ',', '.') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

<div class="total">
    Total: $<?= number_format($factura['Total'] ?? $factura['Monto_Total'] ?? $totalGeneral, 0, ',', '.') ?>
</div>

</body>
</html>