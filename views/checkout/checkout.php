<?php
// views/checkout/checkout.php

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['ID_Usuario'])) {
    $_SESSION['redirect_url'] = BASE_URL . '?c=Checkout&a=index';
    header("Location: " . BASE_URL . "?c=Usuario&a=login");
    exit;
}

$carrito = $_SESSION['carrito'] ?? [];
if (empty($carrito)) {
    $_SESSION['mensaje_error'] = "❌ Tu carrito está vacío.";
    header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
    exit;
}

// Datos del controlador
$direcciones = $direcciones ?? [];
$metodos = $metodos ?? [
    ['ID_Metodo_Pago' => 1, 'T_Pago' => 'Tarjeta de Crédito/Débito'],
    ['ID_Metodo_Pago' => 2, 'T_Pago' => 'Pago en Efectivo'],
    ['ID_Metodo_Pago' => 3, 'T_Pago' => 'PSE']
];
$descuentos_disponibles = $descuentos_disponibles ?? [];
$descuento_aplicado = $_SESSION['descuento_carrito'] ?? null;

// Calcular totales
$subtotal = 0;
$total_items = 0;
$iva_porcentaje = 19;

foreach ($carrito as $item) {
    $precio = floatval($item['Precio'] ?? 0);
    $cantidad = intval($item['Cantidad'] ?? 0);
    $subtotal += ($precio * $cantidad);
    $total_items += $cantidad;
}

$monto_descuento = 0;
if ($descuento_aplicado) {
    if ($descuento_aplicado['Tipo'] === 'Porcentaje') {
        $monto_descuento = $subtotal * ($descuento_aplicado['Valor'] / 100);
    } else {
        $monto_descuento = min($descuento_aplicado['Valor'], $subtotal);
    }
}

$subtotal_con_descuento = $subtotal - $monto_descuento;
$iva_monto = $subtotal_con_descuento * ($iva_porcentaje / 100);
$total_con_iva = $subtotal_con_descuento + $iva_monto;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Compra - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { padding-top: 80px; background: #f8f9fa; }
        .checkout-card { border: none; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .producto-img { width: 60px; height: 60px; object-fit: cover; border-radius: 5px; }
        .btn-pagar { background: #28a745; color: white; font-weight: 600; padding: 12px; }
        .btn-pagar:hover { background: #218838; }
        .direccion-card { border: 2px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 10px; cursor: pointer; }
        .direccion-card:hover { border-color: #28a745; }
        .direccion-card.selected { border-color: #28a745; background: #f8fff9; }
        .metodo-form { display: none; padding: 15px; background: #f8f9fa; border-radius: 8px; margin-top: 10px; }
        .nueva-direccion-section { display: none; }
    </style>
</head>
<body>

<?php include "views/layout/nav.php"; ?>

<div class="container my-4">
    <h2 class="mb-4">Finalizar Compra</h2>
    
    <?php if (!empty($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <?= htmlspecialchars($_SESSION['mensaje_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>?c=Checkout&a=procesar" method="POST" id="formCheckout">
        <div class="row">
            <!-- Columna izquierda -->
            <div class="col-lg-8">
                
                <!-- DIRECCIÓN - VERSIÓN SIMPLIFICADA -->
                <div class="card checkout-card mb-3">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Dirección de Envío</h5>
                    </div>
                    <div class="card-body">
                        
                        <?php if (empty($direcciones)): ?>
                            <div class="alert alert-warning mb-3">
                                No tienes direcciones guardadas. Completa los datos:
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Dirección Completa *</label>
                                <input type="text" class="form-control" name="nueva_direccion" required>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Ciudad *</label>
                                    <input type="text" class="form-control" name="nueva_ciudad" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Departamento *</label>
                                    <input type="text" class="form-control" name="nueva_departamento" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Código Postal</label>
                                <input type="text" class="form-control" name="nueva_postal">
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="guardar_direccion" value="1" checked>
                                <label class="form-check-label">Guardar esta dirección</label>
                            </div>
                            
                        <?php else: ?>
                            <!-- Seleccionar dirección existente -->
                            <div class="mb-3">
                                <label class="form-label">Selecciona una dirección:</label>
                                <div class="row">
                                    <?php foreach ($direcciones as $dir): ?>
                                        <div class="col-md-6 mb-2">
                                            <label class="direccion-card">
                                                <input type="radio" name="direccion" 
                                                       value="<?= $dir['ID_Direccion'] ?>" 
                                                       class="d-none direccion-radio"
                                                       <?= $dir['Predeterminada'] == 1 ? 'checked' : '' ?>>
                                                <div>
                                                    <strong><?= htmlspecialchars($dir['Ciudad']) ?></strong>
                                                    <p class="mb-1 small"><?= htmlspecialchars($dir['Direccion']) ?></p>
                                                    <p class="mb-0 small text-muted"><?= htmlspecialchars($dir['Departamento']) ?></p>
                                                    <?php if ($dir['Predeterminada'] == 1): ?>
                                                        <span class="badge bg-primary">Predeterminada</span>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Botón para nueva dirección -->
                                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="mostrarNuevaDireccion()">
                                    <i class="fas fa-plus me-1"></i>Usar una dirección diferente
                                </button>
                            </div>
                            
                            <!-- Formulario para nueva dirección (oculto inicialmente) -->
                            <div id="nuevaDireccionSection" class="nueva-direccion-section mt-3">
                                <h6>Nueva Dirección</h6>
                                <div class="mb-3">
                                    <label class="form-label">Dirección Completa *</label>
                                    <input type="text" class="form-control" name="nueva_direccion">
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Ciudad *</label>
                                        <input type="text" class="form-control" name="nueva_ciudad">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Departamento *</label>
                                        <input type="text" class="form-control" name="nueva_departamento">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Código Postal</label>
                                    <input type="text" class="form-control" name="nueva_postal">
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="guardar_direccion" value="1" checked>
                                    <label class="form-check-label">Guardar esta dirección</label>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                </div>

                <!-- MÉTODO DE PAGO -->
                <div class="card checkout-card mb-3">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Método de Pago</h5>
                    </div>
                    <div class="card-body">
                        
                        <?php foreach ($metodos as $i => $m): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input metodo-pago" type="radio" 
                                       name="metodo_pago" 
                                       value="<?= $m['ID_Metodo_Pago'] ?>" 
                                       id="metodo<?= $m['ID_Metodo_Pago'] ?>"
                                       <?= $i === 0 ? 'checked' : '' ?>
                                       data-tipo="<?= strtolower($m['T_Pago']) ?>">
                                <label class="form-check-label" for="metodo<?= $m['ID_Metodo_Pago'] ?>">
                                    <strong><?= htmlspecialchars($m['T_Pago']) ?></strong>
                                    <?php if (strpos(strtolower($m['T_Pago']), 'tarjeta') !== false): ?>
                                        <small class="text-muted d-block">Visa, Mastercard, Amex</small>
                                    <?php elseif (strpos(strtolower($m['T_Pago']), 'efectivo') !== false): ?>
                                        <small class="text-muted d-block">Paga al recibir</small>
                                    <?php elseif (strpos(strtolower($m['T_Pago']), 'pse') !== false): ?>
                                        <small class="text-muted d-block">Pago electrónico seguro</small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Formularios por método de pago -->
                        <div id="formTarjeta" class="metodo-form">
                            <div class="mb-3">
                                <label class="form-label">Número de tarjeta</label>
                                <input type="text" class="form-control" placeholder="4242 4242 4242 4242">
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre del titular</label>
                                    <input type="text" class="form-control" placeholder="Como aparece en la tarjeta">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Expiración</label>
                                    <input type="text" class="form-control" placeholder="MM/AA">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">CVV</label>
                                    <input type="password" class="form-control" placeholder="123">
                                </div>
                            </div>
                        </div>
                        
                        <div id="formPSE" class="metodo-form">
                            <div class="mb-3">
                                <label class="form-label">Banco</label>
                                <select class="form-select">
                                    <option value="">Selecciona tu banco</option>
                                    <option value="bancolombia">Bancolombia</option>
                                    <option value="davivienda">Davivienda</option>
                                    <option value="bbva">BBVA</option>
                                </select>
                            </div>
                        </div>
                        
                        <div id="formEfectivo" class="metodo-form">
                            <div class="alert alert-success">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                <strong>Paga al recibir tu pedido</strong>
                                <p class="mb-0 small">El mensajero traerá el cambio exacto</p>
                            </div>
                        </div>
                        
                    </div>
                </div>

            </div>

            <!-- Columna derecha: Resumen -->
            <div class="col-lg-4">
                <div class="card checkout-card sticky-top" style="top: 90px;">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Resumen</h5>
                    </div>
                    <div class="card-body">
                        
                        <!-- Productos -->
                        <!-- Productos -->
<h6 class="mb-3 border-bottom pb-2">Productos (<?= $total_items ?>)</h6>
<div class="mb-3" style="max-height: 300px; overflow-y: auto;">
    <?php if (!empty($carrito)): ?>
        <?php foreach ($carrito as $item): 
            $precio = floatval($item['Precio'] ?? 0);
            $cantidad = intval($item['Cantidad'] ?? 1);
            $subtotalItem = $precio * $cantidad;
            
            // ========== CORRECCIÓN DE IMAGEN ==========
            // 1. Obtener la foto del producto
            $foto = $item['Foto'] ?? 'assets/img/placeholder.png';
            
            // 2. Limpiar la ruta
            $foto = trim($foto);
            
            // 3. Si la foto no empieza con http, assets o ImgProducto, asumir que está en ImgProducto/
            if (!empty($foto) && $foto !== 'assets/img/placeholder.png') {
                if (strpos($foto, 'http') === 0) {
                    // Es URL completa
                    $fotoUrl = $foto;
                } elseif (strpos($foto, 'ImgProducto/') === 0) {
                    // Ya tiene la ruta correcta
                    $fotoUrl = BASE_URL . $foto;
                } elseif (strpos($foto, 'assets/') === 0) {
                    // Es un asset
                    $fotoUrl = BASE_URL . $foto;
                } elseif (strpos($foto, '/') === 0) {
                    // Empieza con slash
                    $fotoUrl = BASE_URL . ltrim($foto, '/');
                } else {
                    // Nombre de archivo simple, asumir que está en ImgProducto/
                    $fotoUrl = BASE_URL . 'ImgProducto/' . $foto;
                }
            } else {
                // Imagen por defecto
                $fotoUrl = BASE_URL . 'assets/img/placeholder.png';
            }
            
            // 4. Asegurar que la URL sea válida
            $fotoUrl = filter_var($fotoUrl, FILTER_SANITIZE_URL);
            
            // ========== ESPECIFICACIONES DEL PRODUCTO ==========
            $especificaciones = [];
            if (!empty($item['Atributos'])) {
                foreach ($item['Atributos'] as $atributo) {
                    if (!empty($atributo['nombre']) && !empty($atributo['valor'])) {
                        $especificaciones[] = htmlspecialchars($atributo['valor']);
                    }
                }
            } else {
                if (!empty($item['ValorAtributo1'])) $especificaciones[] = htmlspecialchars($item['ValorAtributo1']);
                if (!empty($item['ValorAtributo2'])) $especificaciones[] = htmlspecialchars($item['ValorAtributo2']);
                if (!empty($item['ValorAtributo3'])) $especificaciones[] = htmlspecialchars($item['ValorAtributo3']);
            }
            
            $especificacionesStr = !empty($especificaciones) ? '(' . implode(', ', $especificaciones) . ')' : '';
            
            // ========== VERIFICAR DESCUENTO EN ESTE PRODUCTO ==========
            $tieneDescuento = isset($item['Descuento']) && 
                             isset($item['Descuento']['Aplicado']) && 
                             $item['Descuento']['Aplicado'] === true;
            
            $precioOriginal = $item['Precio_Original'] ?? $precio;
            $descuentoProducto = 0;
            
            if ($tieneDescuento && $precioOriginal > $precio) {
                $descuentoProducto = $precioOriginal - $precio;
                $porcentajeDescuento = round(($descuentoProducto / $precioOriginal) * 100, 0);
            }
        ?>
            <div class="d-flex mb-3 pb-2 border-bottom">
                <!-- IMAGEN CON FALLBACK -->
                <img src="<?= $fotoUrl ?>" 
                     class="producto-img-checkout me-2"
                     alt="<?= htmlspecialchars($item['N_Articulo'] ?? 'Producto') ?>"
                     onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/placeholder.png'"
                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                
                <div class="flex-grow-1">
                    <div class="fw-bold small"><?= htmlspecialchars($item['N_Articulo'] ?? 'Producto') ?></div>
                    
                    <?php if ($especificacionesStr): ?>
                        <div class="text-muted x-small"><?= $especificacionesStr ?></div>
                    <?php endif; ?>
                    
                    <div class="text-muted x-small">Cant: <?= $cantidad ?></div>
                    
                    <?php if ($tieneDescuento && $descuentoProducto > 0): ?>
                        <div class="mt-1">
                            <span class="badge bg-success" style="font-size: 0.7rem;">
                                <i class="fas fa-tag me-1"></i>
                                -<?= $porcentajeDescuento ?>% dto.
                            </span>
                            <div class="text-muted x-small mt-1">
                                <s class="text-danger">$<?= number_format($precioOriginal, 0, ',', '.') ?> c/u</s>
                                <span class="text-success ms-1">$<?= number_format($precio, 0, ',', '.') ?> c/u</span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-muted x-small">$<?= number_format($precio, 0, ',', '.') ?> c/u</div>
                    <?php endif; ?>
                </div>
                
                <div class="text-end">
                    <div class="fw-bold">
                        $<?= number_format($subtotalItem, 0, ',', '.') ?>
                        <?php if ($tieneDescuento && $descuentoProducto > 0): ?>
                            <div class="text-success x-small">
                                Ahorraste: $<?= number_format($descuentoProducto * $cantidad, 0, ',', '.') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="text-center py-3">
            <i class="fas fa-shopping-cart fa-2x text-muted mb-2"></i>
            <p class="text-muted">No hay productos en el carrito</p>
        </div>
    <?php endif; ?>
</div>
                        
                        <!-- Totales -->
                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?= number_format($subtotal, 0) ?></span>
                            </div>
                            
                            <?php if ($monto_descuento > 0): ?>
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span>Descuento:</span>
                                <span>-$<?= number_format($monto_descuento, 0) ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>IVA (19%):</span>
                                <span>$<?= number_format($iva_monto, 0) ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Envío:</span>
                                <span class="text-success">Gratis</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Total:</h5>
                                <h4 class="text-success mb-0">$<?= number_format($total_con_iva, 0) ?></h4>
                            </div>
                            
                            <button type="submit" class="btn btn-pagar w-100">
                                <i class="fas fa-lock me-2"></i>Confirmar Compra
                            </button>
                            
                            <div class="text-center mt-3">
                                <a href="<?= BASE_URL ?>?c=Carrito&a=carrito" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i>Volver al carrito
                                </a>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Manejar selección de dirección
document.querySelectorAll('.direccion-radio').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.direccion-card').forEach(card => {
            card.classList.remove('selected');
        });
        if (this.closest('.direccion-card')) {
            this.closest('.direccion-card').classList.add('selected');
        }
        
        // Ocultar sección de nueva dirección si se selecciona dirección guardada
        document.getElementById('nuevaDireccionSection').style.display = 'none';
    });
    
    if (radio.checked && radio.closest('.direccion-card')) {
        radio.closest('.direccion-card').classList.add('selected');
    }
});

// Función para mostrar nueva dirección
function mostrarNuevaDireccion() {
    const nuevaSection = document.getElementById('nuevaDireccionSection');
    nuevaSection.style.display = 'block';
    
    // Desmarcar direcciones guardadas
    document.querySelectorAll('.direccion-radio').forEach(radio => {
        radio.checked = false;
        radio.closest('.direccion-card')?.classList.remove('selected');
    });
    
    // Enfocar primer campo
    nuevaSection.querySelector('input[name="nueva_direccion"]')?.focus();
}

// Mostrar formularios según método de pago
document.querySelectorAll('.metodo-pago').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.metodo-form').forEach(form => {
            form.style.display = 'none';
        });
        
        const tipo = this.getAttribute('data-tipo');
        let formId = '';
        
        if (tipo.includes('tarjeta')) formId = 'formTarjeta';
        else if (tipo.includes('pse')) formId = 'formPSE';
        else if (tipo.includes('efectivo')) formId = 'formEfectivo';
        
        if (formId) {
            document.getElementById(formId).style.display = 'block';
        }
    });
    
    if (radio.checked) radio.dispatchEvent(new Event('change'));
});

// Validación básica
document.getElementById('formCheckout').addEventListener('submit', function(e) {
    const tieneDirecciones = <?= !empty($direcciones) ? 'true' : 'false' ?>;
    
    if (tieneDirecciones) {
        // Verificar si se seleccionó dirección guardada O se completó nueva dirección
        const direccionSeleccionada = document.querySelector('input[name="direccion"]:checked');
        const nuevaDireccionSection = document.getElementById('nuevaDireccionSection');
        const mostrarNuevaDireccion = nuevaDireccionSection.style.display === 'block';
        
        if (!direccionSeleccionada && !mostrarNuevaDireccion) {
            e.preventDefault();
            alert('Por favor selecciona o crea una dirección de envío.');
            return false;
        }
        
        if (mostrarNuevaDireccion) {
            const nuevaDir = document.querySelector('input[name="nueva_direccion"]');
            const nuevaCiudad = document.querySelector('input[name="nueva_ciudad"]');
            const nuevaDepto = document.querySelector('input[name="nueva_departamento"]');
            
            if (!nuevaDir.value || !nuevaCiudad.value || !nuevaDepto.value) {
                e.preventDefault();
                alert('Por favor completa todos los campos de la nueva dirección.');
                return false;
            }
        }
    }
    
    // Validar método de pago
    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked');
    if (!metodoPago) {
        e.preventDefault();
        alert('Por favor selecciona un método de pago.');
        return false;
    }
    
    // Confirmar compra
    const confirmar = confirm('¿Estás seguro de realizar la compra?');
    if (!confirmar) {
        e.preventDefault();
        return false;
    }
    
    // Deshabilitar botón para evitar doble envío
    const btn = document.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
});
</script>
</body>
</html>