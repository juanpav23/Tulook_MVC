<?php
// views/seguimiento/consultar.php - VERSIÓN CORREGIDA

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// 🔹 DEFINIR RUTAS BASE CORRECTAMENTE
// =============================================
define('BASE_URL', 'http://localhost/Tulook_MVC/');
$base_path = dirname(dirname(dirname(__FILE__))); // Raíz del proyecto

// =============================================
// 🔹 CARGAR MODELOS CON RUTAS ABSOLUTAS
// =============================================
require_once $base_path . "/models/Database.php";
require_once $base_path . "/models/Pedido.php";

// =============================================
// 🔹 LOGICA DEL SEGUIMIENTO
// =============================================
$codigo_acceso = $_GET['codigo'] ?? $_POST['codigo'] ?? '';
$mostrar_formulario = true;
$pedido = null;
$seguimientos = [];
$items = [];

if ($codigo_acceso) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $pedidoModel = new Pedido($db);
        
        // Buscar el pedido por código de acceso
        $resultados = $pedidoModel->buscar($codigo_acceso);
        
        if (!empty($resultados)) {
            $pedido = $resultados[0];
            $mostrar_formulario = false;
            
            // Obtener el pedido completo
            $pedidoCompleto = $pedidoModel->obtenerPorId($pedido['ID_Factura']);
            
            if ($pedidoCompleto) {
                $seguimientos = $pedidoCompleto['seguimiento'] ?? [];
                $items = $pedidoCompleto['productos'] ?? [];
                $pedido = array_merge($pedido, $pedidoCompleto);
                
                // Obtener dirección
                $query = "SELECT d.* FROM direccion d WHERE d.ID_Direccion = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$pedido['ID_Direccion']]);
                $direccion = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($direccion) {
                    $pedido['Direccion'] = $direccion['Direccion'];
                    $pedido['Ciudad'] = $direccion['Ciudad'];
                    $pedido['Departamento'] = $direccion['Departamento'];
                    $pedido['CodigoPostal'] = $direccion['CodigoPostal'];
                }
            }
        } else {
            $_SESSION['mensaje_error'] = "Código de acceso no válido";
        }
    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Error al consultar el pedido: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento de Pedido - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>

        :root {
            --primary-dark: #1B202D;
            --primary-blue: #2A3448;
            --accent-blue: #3A4A6B;
            --secondary-blue: #4A5B7D;
            --light-blue: #5B6E8F;
            --light-bg: #f5f7fa;
            --text-dark: #2c3e50;
            --text-light: #6c757d;
            --border-radius: 12px;
            --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --card-shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            padding-top: 0;
        }
        .main-content {
            padding-top: 80px;
        }
        .tracking-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .tracking-header {
            background: linear-gradient(135deg, #3A4A6B 0%, #2A3448 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 20px;
        }
        .status-completed { background-color: #28a745; color: white; }
        .status-pending { background-color: #ffc107; color: #212529; }
        .status-processing { background-color: #17a2b8; color: white; }
        .status-delayed { background-color: #dc3545; color: white; }
        .timeline {
            position: relative;
            padding-left: 40px;
            margin: 30px 0;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 20px;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #3A4A6B;
        }
        .timeline-item {
            position: relative;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .timeline-item:last-child { border-bottom: none; }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -25px;
            top: 5px;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            background: white;
            border: 3px solid #3A4A6B;
        }
        .timeline-item.completed::before { background: #28a745; border-color: #28a745; }
        .timeline-item.current::before {
            background: #ffc107;
            border-color: #ffc107;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .product-img-tracking {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
        .code-display {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            border: 2px dashed #3A4A6B;
            font-size: 1.2rem;
            letter-spacing: 2px;
        }
        .section-title {
            color: #2A3448;
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 10px;
        }
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: #3A4A6B;
            border-radius: 3px;
        }
        .info-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
        }
        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        .btn-primary-custom {
            background: linear-gradient(135deg, #3A4A6B 0%, #2A3448 100%);
            border: none;
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #4A5B7D 0%, #3A4A6B 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(58, 74, 107, 0.3);
        }
    </style>
</head>
<body>

<!-- Incluir navbar -->
<?php include __DIR__ . "/../layout/nav.php"; ?>

<div class="main-content">
    <div class="container py-5">
        
        <?php if ($mostrar_formulario): ?>
        <!-- Formulario para consultar pedido -->
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card tracking-card">
                    <div class="tracking-header">
                        <i class="fas fa-box fa-3x mb-3"></i>
                        <h1 class="h2">Seguimiento de Pedido</h1>
                        <p class="mb-0">Ingresa tu código de acceso para ver el estado</p>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['mensaje_error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['mensaje_error'] ?>
                                <?php unset($_SESSION['mensaje_error']); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label for="codigo" class="form-label">
                                    <i class="fas fa-key me-2"></i>Código de Acceso
                                </label>
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="codigo" 
                                       name="codigo" 
                                       placeholder="Ej: 1444D269"
                                       required
                                       value="<?= htmlspecialchars($codigo_acceso) ?>">
                                <div class="form-text">
                                    Encuentra este código en tu correo de confirmación o factura
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary-custom btn-lg">
                                    <i class="fas fa-search me-2"></i>Consultar Pedido
                                </button>
                            </div>
                            
                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    ¿No tienes tu código? 
                                    <a href="<?= BASE_URL ?>index.php?c=Checkout&a=historial" class="text-decoration-none">
                                        Revisa tu historial de compras
                                    </a>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Información adicional -->
                <div class="card info-card mt-4">
                    <div class="card-body">
                        <h5 class="section-title mb-3"><i class="fas fa-question-circle me-2"></i>¿Dónde encuentro mi código?</h5>
                        <ul class="mb-0">
                            <li class="mb-2"><i class="fas fa-envelope pprimary-blue me-2"></i>En el correo electrónico de confirmación de compra</li>
                            <li class="mb-2"><i class="fas fa-file-pdf text-danger me-2"></i>En tu factura PDF descargable</li>
                            <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>En la página de confirmación después de tu compra</li>
                            <li><i class="fas fa-history text-info me-2"></i>En tu historial de pedidos en tu cuenta</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Resultado del seguimiento -->
        <div class="card tracking-card mb-4">
            <div class="tracking-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-2 mb-md-0">
                        <h1 class="h2 mb-1">Seguimiento del Pedido</h1>
                        <p class="mb-0">Código: <strong><?= htmlspecialchars($codigo_acceso) ?></strong></p>
                    </div>
                    <div class="code-display">
                        <?= htmlspecialchars($pedido['Codigo_Acceso']) ?>
                    </div>
                </div>
            </div>
            
            <div class="card-body p-4">
                <!-- Resumen del pedido -->
                <h3 class="section-title">Resumen del Pedido</h3>
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <div class="card info-card border-primary h-100">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-info-circle me-2"></i>Información del Pedido
                                </h6>
                                <p class="mb-1"><strong>Factura:</strong> #<?= $pedido['ID_Factura'] ?></p>
                                <p class="mb-1"><strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($pedido['Fecha_Factura'])) ?></p>
                                <p class="mb-1"><strong>Total:</strong> $<?= number_format($pedido['Monto_Total'], 0, ',', '.') ?></p>
                                <p class="mb-0"><strong>Método:</strong> <?= htmlspecialchars($pedido['MetodoPago'] ?? 'No especificado') ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card info-card border-success h-100">
                            <div class="card-body">
                                <h6 class="card-title text-success">
                                    <i class="fas fa-user me-2"></i>Cliente
                                </h6>
                                <p class="mb-1"><strong>Nombre:</strong> <?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></p>
                                <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($pedido['Correo']) ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <div class="card info-card border-info h-100">
                            <div class="card-body">
                                <h6 class="card-title text-info">
                                    <i class="fas fa-map-marker-alt me-2"></i>Envío
                                </h6>
                                <?php if (!empty($pedido['Direccion'])): ?>
                                    <p class="mb-1"><strong>Dirección:</strong> <?= htmlspecialchars($pedido['Direccion']) ?></p>
                                    <p class="mb-0">
                                        <strong>Ciudad:</strong> 
                                        <?= htmlspecialchars($pedido['Ciudad'] ?? '') ?>, 
                                        <?= htmlspecialchars($pedido['Departamento'] ?? '') ?>
                                    </p>
                                <?php else: ?>
                                    <p class="mb-0"><i class="fas fa-store me-2"></i>Recoger en tienda</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Estado actual -->
                <div class="alert alert-secondary mb-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="mb-2 mb-md-0">
                            <h5 class="mb-1">Estado actual del pedido</h5>
                            <p class="mb-0">
                                Última actualización: 
                                <?= !empty($seguimientos) ? date('d/m/Y H:i', strtotime($seguimientos[0]['Fecha'])) : date('d/m/Y H:i', strtotime($pedido['Fecha_Factura'])) ?>
                            </p>
                        </div>
                        <div>
                            <?php 
                            $estado_actual = !empty($seguimientos) ? $seguimientos[0]['Estado'] : $pedido['Estado'];
                            $badge_class = [
                                'Emitido' => 'status-pending',
                                'Confirmado' => 'status-completed',
                                'Preparando' => 'status-processing',
                                'Enviado' => 'status-processing',
                                'Entregado' => 'status-completed',
                                'Retrasado' => 'status-delayed',
                                'Devuelto' => 'status-delayed',
                                'Anulado' => 'status-delayed'
                            ];
                            $badge_class = $badge_class[$estado_actual] ?? 'status-pending';
                            ?>
                            <span class="status-badge <?= $badge_class ?>">
                                <?= $estado_actual ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline de seguimiento -->
                <h3 class="section-title">Historial del Pedido</h3>
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if (empty($seguimientos)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay actualizaciones de seguimiento aún.</p>
                                <p>Tu pedido está <strong><?= $pedido['Estado'] ?></strong> y pronto comenzará el proceso.</p>
                            </div>
                        <?php else: ?>
                            <div class="timeline">
                                <?php 
                                $first = true;
                                foreach ($seguimientos as $seguimiento): 
                                    $clase_item = $first ? 'current' : 'completed';
                                    $first = false;
                                ?>
                                <div class="timeline-item <?= $clase_item ?>">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                                        <div class="mb-2 mb-md-0">
                                            <h6 class="mb-1"><?= htmlspecialchars($seguimiento['Estado']) ?></h6>
                                            <?php if (!empty($seguimiento['Descripcion'])): ?>
                                                <p class="mb-1"><?= htmlspecialchars($seguimiento['Descripcion']) ?></p>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <i class="far fa-clock me-1"></i>
                                                <?= date('d/m/Y H:i', strtotime($seguimiento['Fecha'])) ?>
                                            </small>
                                        </div>
                                        <?php if (!empty($seguimiento['UsuarioNombre'])): ?>
                                            <div class="text-end">
                                                <small class="text-muted">
                                                    Actualizado por:<br>
                                                    <strong><?= htmlspecialchars($seguimiento['UsuarioNombre'] . ' ' . $seguimiento['UsuarioApellido']) ?></strong>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Productos del pedido -->
                <?php if (!empty($items)): ?>
                <h3 class="section-title">Productos del Pedido</h3>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Variantes</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio Unit.</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php 
                                                if (!empty($item['Foto'])):
                                                    $foto = $item['Foto'];
                                                    // CORRECCIÓN: str_starts_with reemplazado por substr
                                                    if (strpos($foto, 'http') !== 0 && 
                                                        substr($foto, 0, 11) !== 'ImgProducto/' && 
                                                        substr($foto, 0, 7) !== 'assets/') {
                                                        $foto = 'ImgProducto/' . ltrim($foto, '/');
                                                    }
                                                    // Construir URL correcta
                                                    $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : BASE_URL . ltrim($foto, '/');
                                                ?>
                                                <img src="<?= $fotoUrl ?>" 
                                                     class="product-img-tracking me-3"
                                                     alt="Producto"
                                                     onerror="this.onerror=null; this.src='<?= BASE_URL ?>assets/img/no-image.png';">
                                                <?php endif; ?>
                                                <div>
                                                    <div class="fw-bold"><?= htmlspecialchars($item['NombreProducto'] ?? 'Producto') ?></div>
                                                    <small class="text-muted">ID: <?= $item['ID_Producto'] ?? $item['ID_Articulo'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php 
                                            $especificaciones = [];
                                            if (!empty($item['Atributo1']) && $item['Atributo1'] != 'NO') $especificaciones[] = $item['Atributo1'];
                                            if (!empty($item['Atributo2']) && $item['Atributo2'] != 'NO') $especificaciones[] = $item['Atributo2'];
                                            if (!empty($item['Atributo3']) && $item['Atributo3'] != 'NO') $especificaciones[] = $item['Atributo3'];
                                            if (!empty($especificaciones)): 
                                            ?>
                                                <small><?= implode(', ', $especificaciones) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Sin variantes</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= $item['Cantidad'] ?></td>
                                        <td class="text-end">$<?= number_format($item['Precio_Unitario'], 0, ',', '.') ?></td>
                                        <td class="text-end fw-bold">$<?= number_format($item['Subtotal'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>TOTAL:</strong></td>
                                        <td class="text-end fw-bold text-success">$<?= number_format($pedido['Monto_Total'], 0, ',', '.') ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Botones de acción -->
                <h3 class="section-title">Acciones</h3>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="<?= BASE_URL ?>index.php?c=FacturaPDF&a=generarFactura&id=<?= $pedido['ID_Factura'] ?>" 
                       class="btn btn-primary-custom btn-lg" target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>Descargar Factura
                    </a>
                    <a href="<?= $_SERVER['REQUEST_URI'] ?>" 
                       class="btn btn-outline-dark btn-lg">
                        <i class="fas fa-redo me-2"></i>Consultar Otro
                    </a>
                    <a href="<?= BASE_URL ?>index.php" 
                       class="btn btn-primary-custom btn-lg">
                        <i class="fas fa-store me-2"></i>Seguir Comprando
                    </a>
                </div>
                
                <!-- Información de contacto -->
                <div class="card info-card mt-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="section-title mb-3"><i class="fas fa-headset me-2"></i>Soporte al Cliente</h6>
                                <p class="mb-2"><strong>Teléfono:</strong> +57 300 123 4567</p>
                                <p class="mb-2"><strong>Email:</strong> soporte@tulook.com</p>
                                <p class="mb-0"><strong>Horario:</strong> Lunes a Viernes 8am - 6pm</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="section-title mb-3"><i class="fas fa-shipping-fast me-2"></i>Información de Envío</h6>
                                <?php if ($pedido['Estado'] === 'Enviado' || $pedido['Estado'] === 'Retrasado'): ?>
                                    <?php if (!empty($pedido['Numero_Guia'])): ?>
                                        <p class="mb-2"><strong>Número de guía:</strong> <?= htmlspecialchars($pedido['Numero_Guia']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($pedido['Transportadora'])): ?>
                                        <p class="mb-2"><strong>Transportadora:</strong> <?= htmlspecialchars($pedido['Transportadora']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($pedido['Fecha_Envio'])): ?>
                                        <p class="mb-0"><strong>Fecha envío:</strong> <?= date('d/m/Y', strtotime($pedido['Fecha_Envio'])) ?></p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p class="mb-2"><strong>Tiempo estimado:</strong> 3-5 días hábiles</p>
                                    <p class="mb-2"><strong>Cobertura:</strong> Toda Colombia</p>
                                    <p class="mb-0"><strong>Seguimiento:</strong> Actualizaciones por email</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Función para copiar código
function copiarCodigo() {
    const codigo = "<?= htmlspecialchars($codigo_acceso) ?>";
    navigator.clipboard.writeText(codigo).then(() => {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
        alert.style.zIndex = '9999';
        alert.innerHTML = '<i class="fas fa-check me-2"></i>Código copiado al portapapeles';
        document.body.appendChild(alert);
        
        setTimeout(() => {
            alert.remove();
        }, 2000);
    });
}

// Actualizar automáticamente cada 60 segundos si está viendo seguimiento
<?php if (!$mostrar_formulario): ?>
setTimeout(() => {
    location.reload();
}, 60000);
<?php endif; ?>

// Si viene con código en la URL, procesar automáticamente
<?php if (!empty($_GET['codigo']) && empty($pedido)): ?>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('form').submit();
});
<?php endif; ?>
</script>
</body>
</html>