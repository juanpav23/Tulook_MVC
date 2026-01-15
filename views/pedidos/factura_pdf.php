<?php
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?= htmlspecialchars($factura['ID_Factura'] ?? '') ?> - TuLook</title>
    <style>
        /* ===== RESET Y BASES ===== */
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'DejaVu Sans', 'Helvetica', 'Arial', sans-serif; 
            font-size: 12px; 
            color: #333; 
            line-height: 1.4;
            padding: 15px;
            background: #fff;
            max-width: 800px;
            margin: 0 auto;
        }
        
        /* ===== ENCABEZADO COMPACTO ===== */
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #28a745;
        }
        
        .logo-section {
            flex: 1;
        }
        
        .logo {
            font-size: 22px;
            font-weight: 800;
            color: #28a745;
            margin-bottom: 2px;
        }
        
        .slogan {
            font-size: 10px;
            color: #6c757d;
            font-style: italic;
            margin-bottom: 5px;
        }
        
        .store-info {
            font-size: 9px;
            color: #555;
            line-height: 1.3;
        }
        
        .invoice-info {
            text-align: right;
            background: #f8f9fa;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            font-size: 10px;
        }
        
        .invoice-title {
            font-size: 16px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }
        
        .invoice-detail {
            margin-bottom: 2px;
            font-size: 10px;
        }
        
        .invoice-detail strong {
            color: #28a745;
            min-width: 85px;
            display: inline-block;
        }
        
        /* ===== INFORMACIÓN DEL CLIENTE COMPACTA ===== */
        .client-section {
            background: #f8fff9;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #d4edda;
            margin-bottom: 15px;
        }
        
        .section-title {
            font-size: 13px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 8px;
            padding-bottom: 3px;
            border-bottom: 1px solid #c3e6cb;
        }
        
        .client-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }
        
        .client-item {
            font-size: 10px;
            line-height: 1.3;
        }
        
        .client-item strong {
            color: #495057;
            display: inline-block;
            margin-right: 5px;
            min-width: 70px;
        }
        
        /* DIRECCIÓN COMPLETA - ESPECIAL */
        .client-direccion {
            grid-column: 1 / -1;
            font-size: 10px;
            line-height: 1.3;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #dee2e6;
        }
        
        .client-direccion strong {
            color: #495057;
            display: block;
            margin-bottom: 3px;
        }
        
        /* ===== TABLA DE PRODUCTOS OPTIMIZADA ===== */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 15px 0;
            font-size: 10px;
        }
        
        .products-table th {
            background: #28a745;
            color: white;
            font-weight: 600;
            text-align: left;
            padding: 8px 5px;
            border: none;
            font-size: 11px;
        }
        
        .products-table td {
            padding: 8px 5px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: top;
        }
        
        /* ANCHOS OPTIMIZADOS */
        .col-producto { width: 30%; }
        .col-especificaciones { width: 25%; }
        .col-cantidad { width: 8%; text-align: center; }
        .col-precio { width: 18%; text-align: right; }
        .col-subtotal { width: 19%; text-align: right; }
        
        .products-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        /* Elementos compactos en tabla */
        .discount-badge {
            background: #dc3545;
            color: white;
            font-size: 8px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 3px;
        }
        
        .original-price {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 9px;
            display: block;
        }
        
        .final-price {
            color: #28a745;
            font-weight: 600;
            font-size: 11px;
            display: block;
        }
        
        /* ===== RESUMEN DE TOTALES COMPACTO ===== */
        .summary-section {
            margin-top: 15px;
            border-top: 2px solid #dee2e6;
            padding-top: 12px;
        }
        
        .totals-table {
            width: 250px;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 11px;
        }
        
        .totals-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .totals-table .label {
            text-align: right;
            color: #6c757d;
        }
        
        .totals-table .value {
            text-align: right;
            font-weight: 600;
            color: #333;
        }
        
        .total-row {
            background: #f8fff9;
            font-size: 12px;
            color: #28a745;
            font-weight: 700;
        }
        
        /* ===== DESCUENTOS GANADOS MINI ===== */
        .discounts-section {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 8px 10px;
            margin-top: 12px;
            font-size: 10px;
        }
        
        .discounts-title {
            color: #856404;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .discount-code {
            background: #28a745;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 9px;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 3px;
        }
        
        /* ===== PIE DE PÁGINA COMPACTO ===== */
        .footer {
            margin-top: 15px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 9px;
            line-height: 1.3;
        }
        
        /* ===== AJUSTES PARA IMPRESIÓN ===== */
        @page {
            margin: 15mm 10mm;
            size: A4 portrait;
        }
        
        @media print {
            body {
                padding: 5mm;
                font-size: 11px;
            }
            
            .header-container {
                margin-bottom: 10px;
            }
            
            .client-section, .summary-section {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== ENCABEZADO ===== -->
    <div class="header-container">
        <div class="logo-section">
            <div class="logo">TuLook</div>
            <div class="slogan">Tu estilo, nuestra pasión</div>
            <div class="store-info">
                <strong>TuLook Moda</strong><br>
                NIT: 901.234.567-8 • Carrera 15 #88-64, Bogotá<br>
                Tel: +57 (1) 234 5678 • contacto@tulook.com
            </div>
        </div>
        
        <div class="invoice-info">
            <div class="invoice-title">FACTURA</div>
            <div class="invoice-detail">
                <strong>Nº:</strong> #<?= str_pad($factura['ID_Factura'] ?? '', 6, '0', STR_PAD_LEFT) ?>
            </div>
            <div class="invoice-detail">
                <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'))) ?>
            </div>
            <div class="invoice-detail">
                <strong>Estado:</strong> <span class="text-success">PAGADA</span>
            </div>
            <div class="invoice-detail">
                <strong>Método:</strong> <?= htmlspecialchars($factura['Metodo_Pago'] ?? $factura['T_Pago'] ?? 'N/A') ?>
            </div>
        </div>
    </div>

    <!-- ===== INFORMACIÓN DEL CLIENTE ===== -->
    <div class="client-section">
        <div class="section-title">INFORMACIÓN DEL CLIENTE</div>
        <div class="client-grid">
            <div class="client-item">
                <strong>Nombre:</strong> <?= htmlspecialchars($factura['Nombre'] ?? '') . ' ' . htmlspecialchars($factura['Apellido'] ?? '') ?>
            </div>
            <div class="client-item">
                <strong>Documento:</strong> 
                <?php 
                    $tipo_doc = $factura['Tipo_Documento'] ?? '';
                    $numero_doc = isset($factura['N_Documento']) ? (string)$factura['N_Documento'] : '';
                    
                    if (!empty($tipo_doc) && !empty($numero_doc)) {
                        echo htmlspecialchars($tipo_doc . ' ' . $numero_doc);
                    } elseif (!empty($numero_doc)) {
                        echo htmlspecialchars($numero_doc);
                    } else {
                        echo 'No especificado';
                    }
                ?>
            </div>
            <div class="client-item">
                <strong>Email:</strong> <?= htmlspecialchars($factura['Correo'] ?? 'N/A') ?>
            </div>
            <div class="client-item">
                <strong>Teléfono:</strong> 
                <?php 
                    $telefono = $factura['Celular'] ?? $factura['Telefono_Cliente'] ?? '';
                    echo !empty($telefono) ? htmlspecialchars($telefono) : 'N/A';
                ?>
            </div>
        </div>
        
        <!-- DIRECCIÓN COMPLETA - BUSCAR EN MÚLTIPLES CAMPOS -->
        <div class="client-direccion">
            <strong>Dirección de envío:</strong>
            <?php
                // BUSCAR DIRECCIÓN EN MÚLTIPLES CAMPOS
                $direccion_text = '';
                
                // 1. Intentar con Direccion_Completa
                if (!empty($factura['Direccion_Completa'])) {
                    $direccion_text = $factura['Direccion_Completa'];
                }
                // 2. Intentar con campos separados
                elseif (!empty($factura['Direccion']) || !empty($factura['Ciudad']) || !empty($factura['Departamento'])) {
                    $direccion_parts = [];
                    if (!empty($factura['Direccion'])) $direccion_parts[] = htmlspecialchars($factura['Direccion']);
                    if (!empty($factura['Ciudad'])) $direccion_parts[] = htmlspecialchars($factura['Ciudad']);
                    if (!empty($factura['Departamento'])) $direccion_parts[] = htmlspecialchars($factura['Departamento']);
                    
                    if (!empty($direccion_parts)) {
                        $direccion_text = implode(', ', $direccion_parts);
                    }
                }
                // 3. Mostrar código postal si hay
                if (!empty($factura['CodigoPostal']) && $factura['CodigoPostal'] != '155201') {
                    $direccion_text .= (!empty($direccion_text) ? ', ' : '') . 'CP: ' . htmlspecialchars($factura['CodigoPostal']);
                }
                
                echo !empty($direccion_text) ? $direccion_text : 'Dirección no especificada';
            ?>
        </div>
    </div>

    <!-- ===== TABLA DE PRODUCTOS ===== -->
    <div class="section-title">DETALLE DE PRODUCTOS</div>
    <table class="products-table">
        <thead>
            <tr>
                <th class="col-producto">PRODUCTO</th>
                <th class="col-especificaciones">ESPECIFICACIONES</th>
                <th class="col-cantidad">CANT</th>
                <th class="col-precio">PRECIO UNIT.</th>
                <th class="col-subtotal">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal_sin_descuento = 0;
            $subtotal_con_descuento = 0;
            $descuentoTotal = 0;
            $total_items = 0;
            
            if (isset($items) && is_array($items)):
            foreach ($items as $index => $it): 
                // CÁLCULO CORRECTO DE PRECIOS
                $precio_final_raw = floatval($it['Precio_Unitario'] ?? $it['Precio'] ?? 0);
                $descuento_item_raw = floatval($it['Descuento_Aplicado'] ?? 0);
                
                $precio_final = $precio_final_raw;
                $descuento_item = $descuento_item_raw;
                
                // Si el precio es mayor a 1,000,000, probablemente esté en centavos * 1000
                if ($precio_final > 1000000) {
                    $precio_final = $precio_final_raw / 1000;
                    $descuento_item = $descuento_item_raw / 1000;
                }
                
                $cantidad = intval($it['Cantidad'] ?? 1);
                
                $precio_original = $precio_final;
                if ($descuento_item > 0 && $cantidad > 0) {
                    $precio_original = $precio_final + ($descuento_item / $cantidad);
                }
                
                $subtotal_sin = $precio_original * $cantidad;
                $subtotal_con = $precio_final * $cantidad;
                
                $tieneDescuento = ($descuento_item > 0);
                $porcentajeDescuento = 0;
                
                if ($tieneDescuento && $precio_original > 0) {
                    $porcentajeDescuento = round((($precio_original - $precio_final) / $precio_original) * 100, 1);
                }
                
                $subtotal_sin_descuento += $subtotal_sin;
                $subtotal_con_descuento += $subtotal_con;
                $descuentoTotal += $descuento_item;
                $total_items += $cantidad;
                
                // =======================================================
                // ✅ CORRECCIÓN DEFINITIVA: NOMBRE DEL PRODUCTO
                // =======================================================
                
                // 1. SIEMPRE usar el nombre de la variante específica (Nombre_Producto)
                $nombre_producto = $it['Nombre_Producto'] ?? '';
                
                // 2. Si no hay nombre de variante, usar nombre genérico
                if (empty(trim($nombre_producto))) {
                    $nombre_producto = $it['Producto'] ?? $it['N_Articulo'] ?? 'Producto ' . ($index + 1);
                }
                
                // 3. Extraer especificaciones si están entre paréntesis
                $especificacionesMostrar = '—';
                
                // Buscar atributos entre paréntesis en el nombre
                if (preg_match('/\((.*?)\)/', $nombre_producto, $matches)) {
                    $especificaciones_texto = $matches[1];
                    $especificacionesMostrar = $especificaciones_texto;
                    // Remover los atributos del nombre para mostrar solo el nombre base
                    $nombre_producto = preg_replace('/\s*\(.*?\)/', '', $nombre_producto);
                }
                
                // 4. Si hay campo 'Especificaciones' explícito, usarlo
                if (isset($it['Especificaciones']) && !empty($it['Especificaciones']) && $it['Especificaciones'] !== '—') {
                    $especificacionesMostrar = $it['Especificaciones'];
                }
                
                // 5. Si aún no hay especificaciones, buscar en campos de atributos
                if ($especificacionesMostrar === '—' || empty(trim($especificacionesMostrar))) {
                    $atributos = [];
                    // Buscar en los 3 campos posibles de atributos
                    for ($i = 1; $i <= 3; $i++) {
                        $valor = $it["ValorAtributo{$i}"] ?? '';
                        if (!empty($valor) && $valor !== 'NULL' && $valor !== '—' && $valor !== 'NO') {
                            $atributos[] = $valor;
                        }
                    }
                    
                    if (!empty($atributos)) {
                        $especificacionesMostrar = implode(' | ', $atributos);
                    }
                }
                
                // 6. Formatear nombre (primera letra en mayúscula)
                $nombre_producto = ucwords(strtolower(trim($nombre_producto)));
                
                // 7. Limitar longitud para buen formato
                if (strlen($nombre_producto) > 30) {
                    $nombre_producto = substr($nombre_producto, 0, 27) . '...';
                }
                if (strlen($especificacionesMostrar) > 35) {
                    $especificacionesMostrar = substr($especificacionesMostrar, 0, 32) . '...';
                }
            ?>
            <tr>
                <td class="col-producto">
                    <?= htmlspecialchars($nombre_producto) ?>
                    <?php if ($tieneDescuento && $porcentajeDescuento > 0): ?>
                        <span class="discount-badge">-<?= $porcentajeDescuento ?>%</span>
                    <?php endif; ?>
                </td>
                
                <td class="col-especificaciones">
                    <?= htmlspecialchars($especificacionesMostrar) ?>
                </td>
                
                <td class="col-cantidad text-center"><?= $cantidad ?></td>
                
                <td class="col-precio text-right">
                    <?php if ($tieneDescuento): ?>
                        <span class="original-price">$<?= number_format($precio_original, 0, ',', '.') ?></span>
                        <span class="final-price">$<?= number_format($precio_final, 0, ',', '.') ?></span>
                    <?php else: ?>
                        <span class="final-price">$<?= number_format($precio_final, 0, ',', '.') ?></span>
                    <?php endif; ?>
                </td>
                
                <td class="col-subtotal text-right">
                    <strong>$<?= number_format($subtotal_con, 0, ',', '.') ?></strong>
                </td>
            </tr>
            <?php endforeach; 
            else: ?>
            <tr>
                <td colspan="5" class="text-center">No hay productos</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ===== RESUMEN DE TOTALES ===== -->
    <div class="summary-section">
        <table class="totals-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="value">$<?= number_format($subtotal_con_descuento, 0, ',', '.') ?></td>
            </tr>
            
            <?php if ($descuentoTotal > 0): ?>
            <tr>
                <td class="label">Descuento:</td>
                <td class="value text-success">-$<?= number_format($descuentoTotal, 0, ',', '.') ?></td>
            </tr>
            <?php endif; ?>
            
            <?php
            // CALCULAR IVA CORRECTO
            $iva_monto = floatval($factura['IVA'] ?? 0);
            $total_final = floatval($factura['Monto_Total'] ?? 0);
            
            // Si el IVA parece muy grande, dividir por 1000
            if ($iva_monto > 1000000) {
                $iva_monto = $iva_monto / 1000;
                $total_final = $total_final / 1000;
            }
            
            // Si no hay IVA en la factura, calcularlo
            if ($iva_monto <= 0) {
                $iva_monto = $subtotal_con_descuento * 0.19;
                $total_final = $subtotal_con_descuento + $iva_monto;
            }
            ?>
            <tr>
                <td class="label">IVA (19%):</td>
                <td class="value">$<?= number_format($iva_monto, 0, ',', '.') ?></td>
            </tr>
            
            <tr class="total-row">
                <td class="label">TOTAL:</td>
                <td class="value">$<?= number_format($total_final, 0, ',', '.') ?></td>
            </tr>
        </table>
    </div>

    <?php if (!empty($descuentos_ganados) && is_array($descuentos_ganados)): ?>
    <div class="discounts-section">
        <div class="discounts-title">🎁 ¡Descuentos Ganados!</div>
        <?php 
        $contador = 0;
        foreach ($descuentos_ganados as $descuento): 
            if ($contador < 2): // Máximo 2 códigos para ahorrar espacio
        ?>
            <span class="discount-code">
                <?= htmlspecialchars($descuento['Codigo'] ?? '') ?> 
                -<?= htmlspecialchars($descuento['Valor'] ?? '') ?><?= $descuento['Tipo'] == 'Porcentaje' ? '%' : '' ?>
            </span>
        <?php 
            endif;
            $contador++;
        endforeach; 
        ?>
    </div>
    <?php endif; ?>

    <!-- ===== PIE DE PÁGINA ===== -->
    <div class="footer">
        <div><strong>Factura electrónica válida para efectos fiscales</strong></div>
        <div>Generada por TuLook • contacto@tulook.com • www.tulook.com</div>
        <div><em>¡Gracias por tu compra!</em></div>
    </div>

</body>
</html>
<?php
ob_end_flush();
?>