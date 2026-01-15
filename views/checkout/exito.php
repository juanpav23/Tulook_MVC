<?php
// views/checkout/exito.php

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que tenemos una factura
if (!isset($pedido)) {
    if (isset($_SESSION['ultima_factura'])) {
        require_once "models/Compra.php";
        $compraModel = new Compra($db);
        $pedido = $compraModel->obtenerFacturaDetalle($_SESSION['ultima_factura']);
        $items = $compraModel->obtenerFacturaItems($_SESSION['ultima_factura']);
    } else {
        header("Location: " . BASE_URL);
        exit;
    }
}

if (!$pedido) {
    $_SESSION['mensaje_error'] = "Factura no encontrada";
    header("Location: " . BASE_URL);
    exit;
}

// Obtener descuentos ganados en esta compra desde la sesión
$descuentos_ganados = $_SESSION['descuentos_ganados_compra'] ?? [];
$codigos_generados = $_SESSION['codigos_descuento_generados'] ?? [];

// Limpiar la sesión después de mostrar
unset($_SESSION['descuentos_ganados_compra']);
unset($_SESSION['codigos_descuento_generados']);
unset($_SESSION['ultima_factura']);

// Obtener método de pago
$metodo_pago = '';
if (isset($pedido['ID_Metodo_Pago'])) {
    $metodos_pago = [
        1 => 'Tarjeta de Crédito/Débito',
        2 => 'Pago en Efectivo',
        3 => 'PSE',
        4 => 'Transferencia Bancaria'
    ];
    $metodo_pago = $metodos_pago[$pedido['ID_Metodo_Pago']] ?? 'No especificado';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>¡Compra Exitosa! - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
        }
        .success-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            animation: bounce 1s infinite alternate;
        }
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-10px); }
        }
        .descuento-card {
            border: 2px solid #28a745;
            border-radius: 10px;
            background: #f8fff9;
            transition: transform 0.3s;
        }
        .descuento-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        .codigo-descuento {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            color: #333;
            font-family: 'Courier New', monospace;
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 6px;
            letter-spacing: 2px;
        }
        .producto-img-exito {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .timeline {
            position: relative;
            padding-left: 30px;
            margin: 20px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #28a745;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #28a745;
            border: 2px solid white;
        }
        .tracking-link-card {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .tracking-link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 123, 255, 0.2);
        }
    </style>
</head>
<body>

<!-- Navbar -->
<?php 
$categorias = [];
include "views/layout/nav.php"; 
?>

<div class="container py-5">
    <!-- Tarjeta principal de éxito -->
    <div class="card success-card mb-5">
        <div class="success-header">
            <i class="fas fa-check-circle success-icon mb-3"></i>
            <h1 class="display-5 fw-bold">¡Compra Realizada con Éxito!</h1>
            <p class="lead">Tu pedido ha sido confirmado y está siendo procesado.</p>
        </div>
        
        <div class="card-body p-4">
            <!-- Tarjeta de seguimiento (NUEVA) -->
            <div class="tracking-link-card mb-4 text-center" onclick="window.location.href='<?= BASE_URL ?>views/seguimiento/consultar.php?codigo=<?= $pedido['Codigo_Acceso'] ?>'">
                <div class="row align-items-center">
                    <div class="col-md-2 text-center">
                        <i class="fas fa-truck fa-3x mb-2"></i>
                    </div>
                    <div class="col-md-8">
                        <h3 class="mb-2">📦 ¡Sigue tu pedido en tiempo real!</h3>
                        <p class="mb-1">Usa este código para consultar el estado de tu pedido cuando quieras:</p>
                        <h2 class="mb-0"><code style="background: rgba(255,255,255,0.2); padding: 5px 15px; border-radius: 5px;"><?= htmlspecialchars($pedido['Codigo_Acceso'] ?? '') ?></code></h2>
                    </div>
                    <div class="col-md-2 text-center">
                        <i class="fas fa-arrow-right fa-2x"></i>
                    </div>
                </div>
            </div>

            <!-- Resumen de la factura -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="alert alert-primary">
                        <h5><i class="fas fa-file-invoice me-2"></i> Factura #<?= htmlspecialchars($pedido['ID_Factura'] ?? '') ?></h5>
                        <p class="mb-1"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['Fecha_Factura'] ?? '')) ?></p>
                        <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-success">Confirmado</span></p>
                        <p class="mb-1"><strong>Método de pago:</strong> <?= htmlspecialchars($metodo_pago) ?></p>
                        <p class="mb-0">
                            <strong>Código de seguimiento:</strong> 
                            <code><?= htmlspecialchars($pedido['Codigo_Acceso'] ?? '') ?></code>
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <h5><i class="fas fa-truck me-2"></i> Información de envío</h5>
                        <?php if (!empty($pedido['Direccion'])): ?>
                            <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($pedido['Direccion'] ?? '') ?></p>
                            <p class="mb-1"><strong>Ciudad:</strong> <?= htmlspecialchars($pedido['Ciudad'] ?? '') ?>, <?= htmlspecialchars($pedido['Departamento'] ?? '') ?></p>
                            <p class="mb-0"><strong>Fecha estimada:</strong> <?= date('d/m/Y', strtotime('+3 days', strtotime($pedido['Fecha_Factura'] ?? 'now'))) ?></p>
                        <?php else: ?>
                            <p class="mb-0">Recoger en tienda</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Enlaces rápidos de seguimiento -->
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-primary text-center">
                        <div class="card-body">
                            <i class="fas fa-search fa-2x text-primary mb-2"></i>
                            <h5>Consultar ahora</h5>
                            <p class="small">Ve el estado actual de tu pedido</p>
                            <a href="<?= BASE_URL ?>views/seguimiento/consultar.php?codigo=<?= $pedido['Codigo_Acceso'] ?>" 
                               class="btn btn-outline-primary w-100">
                                Ver seguimiento
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-success text-center">
                        <div class="card-body">
                            <i class="fas fa-envelope fa-2x text-success mb-2"></i>
                            <h5>Notificaciones</h5>
                            <p class="small">Te enviaremos actualizaciones por email</p>
                            <button class="btn btn-outline-success w-100" onclick="copiarCodigo('<?= $pedido['Codigo_Acceso'] ?>')">
                                Copiar código
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 border-info text-center">
                        <div class="card-body">
                            <i class="fas fa-question-circle fa-2x text-info mb-2"></i>
                            <h5>¿Dónde consultar?</h5>
                            <p class="small">En el menú principal: "Seguir Pedido"</p>
                            <a href="<?= BASE_URL ?>views/seguimiento/consultar.php" 
                               class="btn btn-outline-info w-100">
                                Ir a seguimiento
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECCIÓN: DESCUENTOS GANADOS -->
            <?php if (!empty($descuentos_ganados) || !empty($codigos_generados)): ?>
            <div class="alert alert-success alert-dismissible fade show mb-4">
                <h4><i class="fas fa-gift me-2"></i> ¡Felicidades! Has ganado descuentos</h4>
                <p class="mb-0">Por tu compra, has obtenido los siguientes descuentos para futuras compras:</p>
            </div>

            <!-- Descuentos ganados -->
            <div class="row mb-4">
                <?php foreach ($descuentos_ganados as $descuento): ?>
                <div class="col-md-6 mb-3">
                    <div class="descuento-card p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="text-success mb-1">
                                    <i class="fas fa-tag me-2"></i><?= htmlspecialchars($descuento['Codigo'] ?? '') ?>
                                </h5>
                                <p class="mb-1">
                                    <strong><?= htmlspecialchars($descuento['Tipo'] ?? '') == 'Porcentaje' ? $descuento['Valor'] . '%' : '$' . number_format($descuento['Valor'], 0) ?></strong>
                                    de descuento
                                </p>
                                <p class="text-muted small mb-1">
                                    <i class="fas fa-calendar me-1"></i>
                                    Válido hasta: <?= date('d/m/Y', strtotime($descuento['FechaFin'] ?? '')) ?>
                                </p>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Se aplica automáticamente en tu próxima compra
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-warning text-dark">NUEVO</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Códigos generados -->
                <?php foreach ($codigos_generados as $codigo): ?>
                <div class="col-md-6 mb-3">
                    <div class="descuento-card p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="text-primary mb-1">
                                    <i class="fas fa-ticket-alt me-2"></i>Código personal
                                </h5>
                                <div class="codigo-descuento mb-2">
                                    <?= htmlspecialchars($codigo['codigo'] ?? '') ?>
                                </div>
                                <p class="mb-1">
                                    <strong><?= $codigo['tipo'] == 'Porcentaje' ? $codigo['valor'] . '%' : '$' . number_format($codigo['valor'], 0) ?></strong>
                                    de descuento
                                </p>
                                <p class="text-muted small mb-0">
                                    <i class="fas fa-calendar me-1"></i>
                                    Válido hasta: <?= date('d/m/Y', strtotime($codigo['fecha_fin'] ?? '')) ?>
                                </p>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-info">PERSONAL</span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="alert alert-warning mb-4">
                <h6><i class="fas fa-lightbulb me-2"></i> Cómo usar tus descuentos</h6>
                <ul class="mb-0">
                    <li>Los códigos se han enviado a tu correo electrónico</li>
                    <li>Puedes usarlos en tu próxima compra</li>
                    <li>Ingresa el código en el campo "Código de descuento" durante el checkout</li>
                    <li>Cada código tiene fecha de expiración</li>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Resumen de productos -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <strong><i class="fas fa-shopping-bag me-2"></i>Resumen de productos</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th class="text-center">Cantidad</th>
                                    <th class="text-end">Precio Unit.</th>
                                    <th class="text-end">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $subtotal_total = 0;
                                if (isset($items) && is_array($items)):
                                    foreach ($items as $item): 
                                        $precio = floatval($item['Precio_Unitario'] ?? 0);
                                        $cantidad = intval($item['Cantidad'] ?? 1);
                                        $subtotal = $precio * $cantidad;
                                        $subtotal_total += $subtotal;
                                        
                                        // ✅ CORRECCIÓN: OBTENER EL NOMBRE CORRECTO DEL PRODUCTO
                                        $nombre_base = $item['N_Articulo'] ?? 'Producto';         // Ej: "Boxer"
                                        $nombre_variante = $item['Nombre_Producto'] ?? '';       // Ej: "boxer azul calaveras"

                                        // Decidir qué nombre mostrar
                                        if (!empty($nombre_variante) && trim($nombre_variante) !== '') {
                                            // ✅ SIEMPRE mostrar el nombre de la variante si existe
                                            $nombre_producto = $nombre_variante;
                                        } else {
                                            // Si no hay nombre de variante, mostrar el base con atributos
                                            $especificaciones = [];
                                            
                                            // Usar campos específicos de atributos si existen
                                            if (!empty($item['N_Color'])) $especificaciones[] = $item['N_Color'];
                                            if (!empty($item['N_Talla'])) $especificaciones[] = "Talla " . $item['N_Talla'];
                                            
                                            // Usar los campos generales de atributos
                                            for ($i = 1; $i <= 3; $i++) {
                                                $valor = $item["ValorAtributo{$i}"] ?? '';
                                                if (!empty($valor) && $valor !== '—' && $valor !== 'NO' && $valor !== 'NULL') {
                                                    $especificaciones[] = $valor;
                                                }
                                            }
                                            
                                            $nombre_producto = $nombre_base;
                                            if (!empty($especificaciones)) {
                                                $nombre_producto .= " (" . implode(", ", $especificaciones) . ")";
                                            }
                                        }

                                        // ✅ Formatear: Primera letra de cada palabra en mayúscula
                                        $nombre_producto = ucwords(strtolower(trim($nombre_producto)));
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($item['Foto'])): 
                                                $foto = $item['Foto'];
                                                if (!preg_match('/^https?:\\/\\//i', $foto) && !str_starts_with($foto, 'ImgProducto/') && !str_starts_with($foto, 'assets/')) {
                                                    $foto = 'ImgProducto/' . ltrim($foto, '/');
                                                }
                                                $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : rtrim(BASE_URL, '/') . '/' . ltrim($foto, '/');
                                            ?>
                                            <img src="<?= $fotoUrl ?>" 
                                                 class="producto-img-exito me-2"
                                                 alt="<?= htmlspecialchars($nombre_producto) ?>"
                                                 onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.png'">
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($nombre_producto) ?></div>
                                                <?php if (!empty($item['Codigo_Descuento'])): ?>
                                                    <small class="text-success">
                                                        <i class="fas fa-tag me-1"></i>
                                                        Descuento aplicado: <?= htmlspecialchars($item['Codigo_Descuento']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><?= $cantidad ?></td>
                                    <td class="text-end">$<?= number_format($precio, 0, ',', '.') ?></td>
                                    <td class="text-end fw-bold">$<?= number_format($subtotal, 0, ',', '.') ?></td>
                                </tr>
                                <?php endforeach; 
                                endif; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <?php
                                $iva_porcentaje = 19;
                                $iva_total = floatval($pedido['IVA'] ?? 0);
                                $subtotal_final = floatval($pedido['Subtotal'] ?? $subtotal_total);
                                $total_final = floatval($pedido['Monto_Total'] ?? 0);
                                
                                // Si no tenemos IVA en la factura, calcularlo
                                if ($iva_total == 0 && $subtotal_final > 0) {
                                    $iva_total = $subtotal_final * ($iva_porcentaje / 100);
                                    $total_final = $subtotal_final + $iva_total;
                                }
                                ?>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td class="text-end"><strong>$<?= number_format($subtotal_final, 0, ',', '.') ?></strong></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>IVA (<?= $iva_porcentaje ?>%):</strong></td>
                                    <td class="text-end"><strong class="text-info">$<?= number_format($iva_total, 0, ',', '.') ?></strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td colspan="3" class="text-end"><h5 class="mb-0">TOTAL:</h5></td>
                                    <td class="text-end"><h4 class="text-success mb-0">$<?= number_format($total_final, 0, ',', '.') ?></h4></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Timeline del pedido -->
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <strong><i class="fas fa-history me-2"></i>Progreso del pedido</strong>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <h6 class="text-success">✅ Pedido confirmado</h6>
                            <p class="text-muted small mb-0">Tu pedido ha sido confirmado y está siendo procesado.</p>
                            <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
                        </div>
                        <div class="timeline-item">
                            <h6>📦 Preparación</h6>
                            <p class="text-muted small mb-0">Estamos preparando tu pedido para el envío.</p>
                            <small class="text-muted">Próximamente</small>
                        </div>
                        <div class="timeline-item">
                            <h6>🚚 Envío</h6>
                            <p class="text-muted small mb-0">Tu pedido será enviado a la dirección proporcionada.</p>
                            <small class="text-muted">En 24-48 horas</small>
                        </div>
                        <div class="timeline-item">
                            <h6>🏠 Entrega</h6>
                            <p class="text-muted small mb-0">Recibirás tu pedido en la dirección especificada.</p>
                            <small class="text-muted">En 3-5 días hábiles</small>
                        </div>
                    </div>
                    
                    <!-- Nota sobre seguimiento -->
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Recuerda:</strong> Puedes seguir el progreso de tu pedido en cualquier momento usando tu código de acceso <code><?= htmlspecialchars($pedido['Codigo_Acceso'] ?? '') ?></code>
                    </div>
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="d-flex flex-wrap gap-3 justify-content-center">
                <a class="btn btn-primary btn-lg" 
                   href="<?= BASE_URL ?>?c=FacturaPDF&a=generar&id=<?= (int)($pedido['ID_Factura'] ?? 0) ?>">
                    <i class="fas fa-file-pdf me-2"></i>Descargar Factura PDF
                </a>
                <a href="<?= BASE_URL ?>views/seguimiento/consultar.php?codigo=<?= $pedido['Codigo_Acceso'] ?>" 
                   class="btn btn-warning btn-lg">
                    <i class="fas fa-truck me-2"></i>Seguir Pedido
                </a>
                <a class="btn btn-success btn-lg" href="<?= BASE_URL ?>">
                    <i class="fas fa-store me-2"></i>Seguir Comprando
                </a>
                <a class="btn btn-outline-dark btn-lg" href="<?= BASE_URL ?>?c=Checkout&a=historial">
                    <i class="fas fa-history me-2"></i>Ver Historial
                </a>
            </div>

            <!-- Información adicional -->
            <div class="alert alert-light mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="fas fa-envelope text-primary me-2"></i>Confirmación por email</h6>
                        <p class="mb-2">Hemos enviado los detalles de tu compra a <strong><?= htmlspecialchars($pedido['Correo'] ?? 'tu correo') ?></strong></p>
                        <p class="mb-0 small">Incluye tu código de acceso para seguimiento: <code><?= htmlspecialchars($pedido['Codigo_Acceso'] ?? '') ?></code></p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="fas fa-phone text-success me-2"></i>Soporte al cliente</h6>
                        <p class="mb-2">¿Necesitas ayuda? Contáctanos:</p>
                        <p class="mb-1"><strong>Teléfono:</strong> +57 300 123 4567</p>
                        <p class="mb-0"><strong>Email:</strong> soporte@tulook.com</p>
                    </div>
                </div>
            </div>
            
            <!-- Recordatorio importante -->
            <div class="alert alert-warning mt-3">
                <h6><i class="fas fa-bell me-2"></i>¡Importante!</h6>
                <p class="mb-2">
                    <strong>Guarda tu código de acceso:</strong> <code><?= htmlspecialchars($pedido['Codigo_Acceso'] ?? '') ?></code>
                </p>
                <p class="mb-0">
                    Necesitarás este código para:
                    <ul class="mb-0">
                        <li>Seguir el estado de tu pedido</li>
                        <li>Consultar información de envío</li>
                        <li>Contactar a soporte sobre esta compra</li>
                    </ul>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Mostrar alerta de éxito con descuentos si los hay
<?php if (!empty($descuentos_ganados) || !empty($codigos_generados)): ?>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        Swal.fire({
            title: '🎉 ¡Descuentos Obtenidos!',
            html: `<div class="text-start">
                <p>Por tu compra has ganado:</p>
                <ul>
                    <?php foreach ($descuentos_ganados as $descuento): ?>
                    <li><strong><?= htmlspecialchars($descuento['Codigo'] ?? '') ?>:</strong> <?= $descuento['Tipo'] == 'Porcentaje' ? $descuento['Valor'] . '%' : '$' . number_format($descuento['Valor'], 0) ?> de descuento</li>
                    <?php endforeach; ?>
                    <?php foreach ($codigos_generados as $codigo): ?>
                    <li><strong><?= htmlspecialchars($codigo['codigo'] ?? '') ?>:</strong> <?= $codigo['tipo'] == 'Porcentaje' ? $codigo['valor'] . '%' : '$' . number_format($codigo['valor'], 0) ?> de descuento</li>
                    <?php endforeach; ?>
                </ul>
                <p class="small text-muted mt-2">Los códigos han sido enviados a tu correo electrónico.</p>
            </div>`,
            icon: 'success',
            confirmButtonText: '¡Entendido!',
            confirmButtonColor: '#28a745',
            width: 600
        });
    }, 1000);
});
<?php endif; ?>

// Copiar código al portapapeles
function copiarCodigo(codigo) {
    navigator.clipboard.writeText(codigo).then(() => {
        Swal.fire({
            title: '¡Copiado!',
            text: 'Código copiado al portapapeles',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    });
}

// Mostrar alerta de seguimiento
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        Swal.fire({
            title: '📦 ¡Sigue tu pedido!',
            html: `<div class="text-center">
                <p>Tu código de acceso es:</p>
                <h3 class="text-primary mb-3"><code><?= htmlspecialchars($pedido['Codigo_Acceso'] ?? '') ?></code></h3>
                <p class="small text-muted">Guárdalo para consultar el estado de tu pedido cuando quieras.</p>
                <div class="mt-3">
                    <a href="<?= BASE_URL ?>views/seguimiento/consultar.php?codigo=<?= $pedido['Codigo_Acceso'] ?>" 
                       class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Ver seguimiento ahora
                    </a>
                </div>
            </div>`,
            icon: 'info',
            confirmButtonText: 'Entendido',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            confirmButtonColor: '#007bff'
        });
    }, 2000);
});
</script>

</body>
</html>