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

// Obtener datos del carrito
$carrito = $_SESSION['carrito'] ?? [];

// Verificar que haya productos en el carrito
if (empty($carrito)) {
    $_SESSION['mensaje_error'] = "❌ Tu carrito está vacío. Agrega productos antes de proceder al pago.";
    header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
    exit;
}

// Calcular totales con IVA
$subtotal = 0;
$total_items = 0;
$iva_porcentaje = 19;

foreach ($carrito as $item) {
    $precio = floatval($item['Precio'] ?? 0);
    $cantidad = intval($item['Cantidad'] ?? 0);
    $subtotal += ($precio * $cantidad);
    $total_items += $cantidad;
}

$iva_monto = $subtotal * ($iva_porcentaje / 100);
$total_con_iva = $subtotal + $iva_monto;

// Asegurar que $metodos esté definido
if (!isset($metodos) || !is_array($metodos)) {
    // Valores por defecto si no vienen del controlador
    $metodos = [
        ['ID_MetodoPago' => 1, 'T_Pago' => 'Tarjeta'],
        ['ID_MetodoPago' => 2, 'T_Pago' => 'Efectivo'],
        ['ID_MetodoPago' => 3, 'T_Pago' => 'PSE']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .card-summary { 
            border-left: 4px solid #212529; 
            position: sticky;
            top: 100px;
        }
        .metodo-form { 
            display: none; 
            animation: fade .3s ease-in-out; 
            background: #f8f9fa;
            border-radius: 8px;
        }
        .iva-badge {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .producto-img-checkout {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        @keyframes fade {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        .step {
            text-align: center;
            position: relative;
            z-index: 2;
        }
        .step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-weight: bold;
            border: 2px solid white;
        }
        .step.active .step-number {
            background: #212529;
            color: white;
        }
        .step.completed .step-number {
            background: #28a745;
            color: white;
        }
        .step-label {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .step.active .step-label {
            color: #212529;
            font-weight: 500;
        }
    </style>
</head>
<body>

<!-- INCLUIR NAVBAR -->
<?php 
$categorias = [];
include "views/layout/nav.php"; 
?>

<div class="container my-5" style="margin-top: 100px !important;">
    
    <!-- Indicador de pasos -->
    <div class="step-indicator">
        <div class="step completed">
            <div class="step-number">1</div>
            <div class="step-label">Carrito</div>
        </div>
        <div class="step active">
            <div class="step-number">2</div>
            <div class="step-label">Envío & Pago</div>
        </div>
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-label">Confirmación</div>
        </div>
    </div>

    <h2 class="mb-4">
        <i class="fas fa-shipping-fast text-primary me-2"></i>Finalizar compra
        <span class="badge bg-primary ms-2"><?php echo $total_items; ?> productos</span>
    </h2>

    <?php if (!empty($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($_SESSION['mensaje_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['mensaje_ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($_SESSION['mensaje_ok']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje_ok']); ?>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>?c=Checkout&a=procesar" method="POST" id="formCheckout">

        <div class="row">

            <!-- ======================
                 SECCIÓN IZQUIERDA
            ======================= -->
            <div class="col-lg-8">
                
                <!-- DIRECCIÓN DE ENVÍO -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <strong><i class="fas fa-map-marker-alt me-2"></i>Dirección de envío</strong>
                    </div>
                    <div class="card-body">

                        <?php if (empty($direcciones)): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No tienes direcciones guardadas. Agrega una para continuar.
                            </div>

                            <!-- FORMULARIO PARA AGREGAR DIRECCIÓN -->
                            <div class="p-3 border rounded bg-light">
                                <h5 class="mb-3">Nueva dirección</h5>

                                <div class="mb-3">
                                    <label class="form-label">Dirección completa*</label>
                                    <input type="text" class="form-control" name="nueva_direccion" required 
                                           placeholder="Calle, número, barrio, etc.">
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Ciudad*</label>
                                        <input type="text" class="form-control" name="nueva_ciudad" required 
                                               placeholder="Ej: Bogotá">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Departamento*</label>
                                        <input type="text" class="form-control" name="nueva_departamento" required 
                                               placeholder="Ej: Cundinamarca">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Código Postal</label>
                                    <input type="text" class="form-control" name="nueva_postal" 
                                           placeholder="Opcional">
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="guardar_direccion" checked>
                                    <label class="form-check-label">
                                        Guardar esta dirección para futuras compras
                                    </label>
                                </div>

                                <input type="hidden" name="crear_direccion" value="1">
                            </div>

                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($direcciones as $i => $dir): ?>
                                    <label class="list-group-item list-group-item-action">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="direccion"
                                                value="<?= htmlspecialchars($dir['ID_Direccion'] ?? '') ?>" 
                                                <?= $i === 0 ? 'checked' : '' ?>
                                                id="dir_<?= $dir['ID_Direccion'] ?? '' ?>">
                                            
                                            <div class="ms-2">
                                                <strong class="form-check-label" for="dir_<?= $dir['ID_Direccion'] ?? '' ?>">
                                                    <?php if (!empty($dir['Predeterminada'])): ?>
                                                        <span class="badge bg-primary">Predeterminada</span>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($dir['Direccion'] ?? '') ?>
                                                </strong>
                                                <div class="text-muted small">
                                                    <?= htmlspecialchars($dir['Ciudad'] ?? '') ?>, 
                                                    <?= htmlspecialchars($dir['Departamento'] ?? '') ?>
                                                    <?php if (!empty($dir['CodigoPostal'])): ?>
                                                        - CP: <?= htmlspecialchars($dir['CodigoPostal']) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <div class="mt-3">
                                <a href="<?= BASE_URL ?>?c=Usuario&a=perfil#direcciones" 
                                   class="btn btn-outline-dark btn-sm">
                                    <i class="fas fa-edit me-1"></i>Administrar direcciones
                                </a>
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        data-bs-toggle="collapse" data-bs-target="#nuevaDireccionCollapse">
                                    <i class="fas fa-plus me-1"></i>Agregar nueva dirección
                                </button>
                            </div>

                            <!-- Formulario para nueva dirección (colapsable) -->
                            <div class="collapse mt-3" id="nuevaDireccionCollapse">
                                <div class="card card-body">
                                    <h6>Agregar nueva dirección</h6>
                                    
                                    <div class="mb-2">
                                        <label>Dirección</label>
                                        <input type="text" class="form-control" name="nueva_direccion_extra">
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col">
                                            <label>Ciudad</label>
                                            <input type="text" class="form-control" name="nueva_ciudad_extra">
                                        </div>
                                        <div class="col">
                                            <label>Departamento</label>
                                            <input type="text" class="form-control" name="nueva_departamento_extra">
                                        </div>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <label>Código Postal</label>
                                        <input type="text" class="form-control" name="nueva_postal_extra">
                                    </div>
                                    
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="guardar_direccion_extra" checked>
                                        <label class="form-check-label">
                                            Guardar esta dirección
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- MÉTODO DE PAGO -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <strong><i class="fas fa-credit-card me-2"></i>Método de pago</strong>
                    </div>
                    <div class="card-body">
                        <div class="list-group mb-3">
                            <?php if (!empty($metodos) && is_array($metodos)): ?>
                                <?php foreach ($metodos as $m): 
                                    $id_metodo = $m['ID_Metodo_Pago'] ?? $m['ID_MetodoPago'] ?? 0;
                                    $nombre_metodo = $m['T_Pago'] ?? '';
                                    $unique_id = 'metodo_' . $id_metodo . '_' . uniqid();
                                    $nombre_lower = strtolower($nombre_metodo);
                                ?>
                                    <label class="list-group-item list-group-item-action">
                                        <div class="form-check">
                                            <input class="form-check-input metodo-radio" type="radio" 
                                                   name="metodo_pago"
                                                   value="<?= $id_metodo ?>" 
                                                   required
                                                   id="<?= $unique_id ?>"
                                                   data-metodo-nombre="<?= htmlspecialchars($nombre_lower) ?>">
                                            
                                            <div class="ms-2">
                                                <strong class="form-check-label" for="<?= $unique_id ?>">
                                                    <?= htmlspecialchars($nombre_metodo) ?>
                                                </strong>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    No hay métodos de pago disponibles.
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- FORMULARIO TARJETA -->
                        <div id="form_tarjeta" class="metodo-form p-4 mt-3 border rounded">
                            <h5 class="mb-3">
                                <i class="fas fa-credit-card text-primary me-2"></i>Pago con tarjeta
                            </h5>

                            <div class="mb-3">
                                <label class="form-label">Nombre del titular*</label>
                                <input type="text" class="form-control" name="tarjeta_titular" 
                                       placeholder="Como aparece en la tarjeta">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Número de tarjeta*</label>
                                <div class="input-group">
                                    <input type="text" maxlength="16" class="form-control" name="tarjeta_numero" 
                                           placeholder="1234 5678 9012 3456">
                                    <span class="input-group-text">
                                        <i class="fab fa-cc-visa text-primary me-1"></i>
                                        <i class="fab fa-cc-mastercard text-danger"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fecha de expiración*</label>
                                    <input type="month" class="form-control" name="tarjeta_expiracion">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">CVV*</label>
                                    <div class="input-group">
                                        <input type="password" maxlength="3" class="form-control" name="tarjeta_cvv" 
                                               placeholder="123">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-2"></i>
                                Tus datos de pago se procesan de forma segura mediante cifrado SSL.
                            </div>
                        </div>

                        <!-- EFECTIVO -->
                        <div id="form_efectivo" class="metodo-form p-4 mt-3 border rounded">
                            <h5 class="mb-3">
                                <i class="fas fa-money-bill-wave text-success me-2"></i>Pago en efectivo
                            </h5>
                            <p class="text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                Pagarás en efectivo cuando recibas el producto. El mensajero traerá el cambio necesario.
                            </p>
                            <div class="alert alert-warning">
                                <strong>Nota:</strong> El producto se enviará una vez confirmado el pago.
                            </div>
                        </div>

                        <!-- PSE -->
                        <div id="form_pse" class="metodo-form p-4 mt-3 border rounded">
                            <h5 class="mb-3">
                                <i class="fas fa-university text-info me-2"></i>Pago por PSE
                            </h5>

                            <div class="mb-3">
                                <label class="form-label">Banco*</label>
                                <select class="form-control" name="pse_banco">
                                    <option value="">Selecciona tu banco</option>
                                    <option>Bancolombia</option>
                                    <option>Banco de Bogotá</option>
                                    <option>Davivienda</option>
                                    <option>BBVA</option>
                                    <option>Banco Popular</option>
                                    <option>Scotiabank Colpatria</option>
                                    <option>Banco de Occidente</option>
                                    <option>Banco Caja Social</option>
                                    <option>Nequi</option>
                                    <option>Daviplata</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nombre del titular*</label>
                                <input type="text" class="form-control" name="pse_titular" 
                                       placeholder="Nombre completo">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de documento*</label>
                                    <select class="form-control" name="pse_tipo_doc">
                                        <option value="">Seleccionar</option>
                                        <option>CC - Cédula de Ciudadanía</option>
                                        <option>CE - Cédula de Extranjería</option>
                                        <option>TI - Tarjeta de Identidad</option>
                                        <option>PA - Pasaporte</option>
                                        <option>NIT - Número de Identificación Tributaria</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Número de documento*</label>
                                    <input type="text" class="form-control" name="pse_documento" 
                                           placeholder="Ej: 123456789">
                                </div>
                            </div>

                            <div class="alert alert-info small">
                                <i class="fas fa-shield-alt me-2"></i>
                                Serás redirigido al portal seguro de tu banco para completar la transacción.
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- ======================
                 RESUMEN DE COMPRA
            ======================= -->
            <div class="col-lg-4">
                <div class="card card-summary shadow-lg mb-4">
                    <div class="card-header bg-dark text-white">
                        <strong><i class="fas fa-receipt me-2"></i>Resumen de compra</strong>
                        <span class="iva-badge float-end">IVA <?= $iva_porcentaje ?>%</span>
                    </div>
                    
                    <div class="card-body">
                        <!-- Productos -->
                        <h6 class="mb-3">Productos</h6>
                        <div class="mb-3" style="max-height: 300px; overflow-y: auto;">
                            <?php if (!empty($carrito)): ?>
                                <?php foreach ($carrito as $item): 
                                    $precio = floatval($item['Precio'] ?? 0);
                                    $cantidad = intval($item['Cantidad'] ?? 1);
                                    $subtotalItem = $precio * $cantidad;
                                    
                                    // Preparar especificaciones
                                    $especificaciones = [];
                                    
                                    // Usar atributos dinámicos si existen
                                    if (!empty($item['Atributos'])) {
                                        foreach ($item['Atributos'] as $atributo) {
                                            if (!empty($atributo['nombre']) && !empty($atributo['valor'])) {
                                                $especificaciones[] = $atributo['nombre'] . ': ' . $atributo['valor'];
                                            }
                                        }
                                    }
                                    // Fallback a atributos básicos
                                    else {
                                        if (!empty($item['ValorAtributo1'])) $especificaciones[] = $item['ValorAtributo1'];
                                        if (!empty($item['ValorAtributo2'])) $especificaciones[] = $item['ValorAtributo2'];
                                        if (!empty($item['ValorAtributo3'])) $especificaciones[] = $item['ValorAtributo3'];
                                    }
                                    
                                    $especificacionesStr = !empty($especificaciones) ? implode(' | ', $especificaciones) : 'Sin especificaciones';
                                    
                                    // Obtener URL de imagen
                                    $foto = $item['Foto'] ?? 'assets/img/placeholder.png';
                                    if (!preg_match('/^https?:\\/\\//i', $foto) && !str_starts_with($foto, 'ImgProducto/') && !str_starts_with($foto, 'assets/')) {
                                        $foto = 'ImgProducto/' . ltrim($foto, '/');
                                    }
                                    $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : rtrim(BASE_URL, '/') . '/' . ltrim($foto, '/');
                                ?>
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div class="d-flex align-items-center">
                                            <img src="<?= $fotoUrl ?>" 
                                                 class="producto-img-checkout me-2"
                                                 alt="<?= htmlspecialchars($item['N_Articulo'] ?? 'Producto') ?>"
                                                 onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.png'">
                                            <div>
                                                <div class="fw-bold small"><?= htmlspecialchars($item['N_Articulo'] ?? 'Producto') ?></div>
                                                <div class="text-muted x-small"><?= $especificacionesStr ?></div>
                                                <div class="text-muted x-small">Cant: <?= $cantidad ?></div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold">$<?= number_format($subtotalItem, 0, ',', '.') ?></div>
                                            <div class="text-muted x-small">$<?= number_format($precio, 0, ',', '.') ?> c/u</div>
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
                        
                        <!-- Resumen de precios -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="fw-bold">$<?= number_format($subtotal, 0, ',', '.') ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Envío:</span>
                                <span class="text-success">Gratis</span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>IVA (<?= $iva_porcentaje ?>%):</span>
                                <span class="text-info fw-bold">$<?= number_format($iva_monto, 0, ',', '.') ?></span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <h5 class="mb-0">Total:</h5>
                                <h3 class="text-success mb-0">$<?= number_format($total_con_iva, 0, ',', '.') ?></h3>
                            </div>
                            
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Incluye <?= $iva_porcentaje ?>% de IVA
                            </small>
                        </div>
                        
                        <!-- Botón de pago -->
                        <button type="submit" class="btn btn-dark btn-lg w-100 mt-4 py-3" id="btnPagar">
                            <i class="fas fa-lock me-2"></i>Pagar ahora
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="<?= BASE_URL ?>?c=Carrito&a=carrito" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Volver al carrito
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Información de seguridad -->
                <div class="card border-success mb-4">
                    <div class="card-body text-center py-3">
                        <i class="fas fa-shield-alt text-success fa-2x mb-2"></i>
                        <h6 class="mb-2">Compra 100% segura</h6>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-lock me-1"></i>Datos protegidos
                            <span class="mx-2">•</span>
                            <i class="fas fa-truck me-1"></i>Envío garantizado
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </form>
</div>

<!-- INCLUIR BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scripts para checkout -->
<script>
// Configuración
const BASE_URL = '<?= BASE_URL ?>';

// MOSTRAR FORMULARIOS SEGÚN MÉTODO SELECCIONADO
document.querySelectorAll(".metodo-radio").forEach(radio => {
    radio.addEventListener("change", () => {
        // Ocultar todos los formularios
        const formTarjeta = document.getElementById("form_tarjeta");
        const formEfectivo = document.getElementById("form_efectivo");
        const formPse = document.getElementById("form_pse");
        
        if (formTarjeta) formTarjeta.style.display = "none";
        if (formEfectivo) formEfectivo.style.display = "none";
        if (formPse) formPse.style.display = "none";

        // Obtener el nombre del método desde el atributo data
        const metodoNombre = radio.getAttribute('data-metodo-nombre');
        
        switch (metodoNombre) {
            case "tarjeta":
                if (formTarjeta) formTarjeta.style.display = "block";
                break;
            case "efectivo":
                if (formEfectivo) formEfectivo.style.display = "block";
                break;
            case "pse":
                if (formPse) formPse.style.display = "block";
                break;
        }
    });
    
    // Mostrar formulario inicial si ya está seleccionado
    if (radio.checked) {
        radio.dispatchEvent(new Event('change'));
    }
});

// VALIDACIÓN DEL FORMULARIO
document.getElementById('formCheckout').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevenir envío inmediato
    
    const btnPagar = document.getElementById('btnPagar');
    const originalText = btnPagar.innerHTML;
    
    // Mostrar loading
    btnPagar.disabled = true;
    btnPagar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando pago...';
    
    // Validar método de pago seleccionado
    const metodoSeleccionado = document.querySelector('input[name="metodo_pago"]:checked');
    if (!metodoSeleccionado) {
        Swal.fire({
            icon: 'warning',
            title: 'Método de pago requerido',
            text: 'Por favor selecciona un método de pago.',
            confirmButtonText: 'Entendido'
        });
        btnPagar.disabled = false;
        btnPagar.innerHTML = originalText;
        return;
    }
    
    // Validar dirección
    const direccionSeleccionada = document.querySelector('input[name="direccion"]:checked');
    const crearDireccion = document.querySelector('input[name="crear_direccion"]');
    
    if (!direccionSeleccionada && !crearDireccion) {
        Swal.fire({
            icon: 'warning',
            title: 'Dirección requerida',
            text: 'Por favor selecciona o crea una dirección de envío.',
            confirmButtonText: 'Entendido'
        });
        btnPagar.disabled = false;
        btnPagar.innerHTML = originalText;
        return;
    }
    
    // Si está creando nueva dirección, validar campos
    if (crearDireccion && crearDireccion.value === '1') {
        const nuevaDireccion = document.querySelector('input[name="nueva_direccion"]');
        const nuevaCiudad = document.querySelector('input[name="nueva_ciudad"]');
        const nuevaDepartamento = document.querySelector('input[name="nueva_departamento"]');
        
        if (!nuevaDireccion?.value || !nuevaCiudad?.value || !nuevaDepartamento?.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                html: 'Por favor completa todos los campos obligatorios de la dirección:<br>' +
                      '<ul class="text-start mt-2">' +
                      '<li>Dirección completa</li>' +
                      '<li>Ciudad</li>' +
                      '<li>Departamento</li>' +
                      '</ul>',
                confirmButtonText: 'Entendido'
            });
            btnPagar.disabled = false;
            btnPagar.innerHTML = originalText;
            return;
        }
    }
    
    // Confirmación final
    const subtotal = <?= $subtotal ?>;
    const iva = <?= $iva_monto ?>;
    const total = <?= $total_con_iva ?>;
    
    Swal.fire({
        title: '¿Confirmar compra?',
        html: `
            <div class="text-start">
                <p><strong>Resumen final:</strong></p>
                <div class="mb-2">
                    <small class="text-muted">Subtotal:</small>
                    <div class="fw-bold">$${subtotal.toLocaleString('es-CO')}</div>
                </div>
                <div class="mb-2">
                    <small class="text-muted">IVA (19%):</small>
                    <div class="fw-bold text-info">$${iva.toLocaleString('es-CO')}</div>
                </div>
                <div class="mt-3 pt-2 border-top">
                    <small class="text-muted">Total a pagar:</small>
                    <div class="h4 text-success fw-bold">$${total.toLocaleString('es-CO')}</div>
                </div>
                <div class="alert alert-warning small mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Al confirmar, se procesará el pago y tu pedido será enviado.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, confirmar compra',
        cancelButtonText: 'Revisar datos',
        reverseButtons: true,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Enviar formulario
            document.getElementById('formCheckout').submit();
        } else {
            // Restaurar botón
            btnPagar.disabled = false;
            btnPagar.innerHTML = originalText;
        }
    });
});

// Formatear número de tarjeta
document.querySelector('input[name="tarjeta_numero"]')?.addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = value.substring(0, 19);
});

// Formatear CVV
document.querySelector('input[name="tarjeta_cvv"]')?.addEventListener('input', function(e) {
    e.target.value = e.target.value.replace(/\D/g, '').substring(0, 3);
});

// Validar fecha de expiración
document.querySelector('input[name="tarjeta_expiracion"]')?.addEventListener('change', function(e) {
    if (!e.target.value) return;
    
    const selectedDate = new Date(e.target.value + '-01');
    const currentDate = new Date();
    
    if (selectedDate < currentDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Fecha expirada',
            text: 'La fecha de expiración no puede ser anterior a la fecha actual.',
            confirmButtonText: 'Entendido'
        });
        e.target.value = '';
    }
});
</script>

</body>
</html>