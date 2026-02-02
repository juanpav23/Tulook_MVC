<?php
// Helper para mostrar badges de estado con la paleta de colores de usuarios
function getEstadoBadgePedido($estado)
{
    $badgeClass = '';
    $icon = '';
    
    switch($estado) {
        case 'Emitido':
            $badgeClass = 'badge-estado-emitido';
            $icon = 'fa-clock';
            break;
        case 'Confirmado':
            $badgeClass = 'badge-estado-confirmado';
            $icon = 'fa-check';
            break;
        case 'Preparando':
            $badgeClass = 'badge-estado-preparando';
            $icon = 'fa-cogs';
            break;
        case 'Enviado':
            $badgeClass = 'badge-estado-enviado';
            $icon = 'fa-truck';
            break;
        case 'Retrasado':
            $badgeClass = 'badge-estado-retrasado';
            $icon = 'fa-exclamation-triangle';
            break;
        case 'Devuelto':
            $badgeClass = 'badge-estado-devuelto';
            $icon = 'fa-undo';
            break;
        case 'Entregado':
            $badgeClass = 'badge-estado-entregado';
            $icon = 'fa-box-check';
            break;
        case 'Anulado':
            $badgeClass = 'badge-estado-anulado';
            $icon = 'fa-ban';
            break;
        default:
            $badgeClass = 'badge bg-primary-dark';
            $icon = 'fa-question';
    }
    
    return '<span class="badge ' . $badgeClass . ' d-flex align-items-center justify-content-center gap-1" style="min-width: 120px;">
                <i class="fas ' . $icon . '"></i>
                ' . $estado . '
            </span>';
}

// Acceder al helper desde el controlador
$getEstadoBadge = $getEstadoBadge ?? 'getEstadoBadgePedido';

// Calcular subtotal de productos para verificación
$subtotalProductos = 0;
if (!empty($pedido['productos'])) {
    foreach ($pedido['productos'] as $producto) {
        $subtotalProductos += $producto['Subtotal'];
    }
}
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice me-2"></i>Detalle del Pedido <?= $pedido['Codigo_Acceso'] ?></h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="<?= BASE_URL ?>?c=Pedido&a=generarFactura&id=<?= $pedido['ID_Factura'] ?>"
                class="btn btn-info" target="_blank">
                <i class="fas fa-print me-1"></i> Imprimir Factura
            </a>
        </div>
    </div>

    <!-- Mensajes Globales -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div id="mensajeGlobal" class="alert-message alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?>">
            <div class="alert-content">
                <i class="fas fa-info-circle me-2"></i>
                <span><?= $_SESSION['mensaje'] ?></span>
                <button type="button" class="btn-close-alert" onclick="this.parentElement.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Información del Pedido -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Pedido</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%" class="text-primary-dark">Código del Pedido:</th>
                            <td><strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Fecha creación:</th>
                            <td><?= date('d/m/Y H:i:s', strtotime($pedido['Fecha_Factura'])) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Estado:</th>
                            <td><?= $getEstadoBadge($pedido['Estado']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Total:</th>
                            <td class="h5 text-primary-dark">$<?= number_format($pedido['Monto_Total'], 2) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Subtotal:</th>
                            <td>$<?= number_format($pedido['Subtotal'] ?? $subtotalProductos, 2) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">IVA (19%):</th>
                            <td>$<?= number_format($pedido['IVA'] ?? ($pedido['Monto_Total'] - ($pedido['Subtotal'] ?? $subtotalProductos)), 2) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Método de pago:</th>
                            <td><?= $pedido['MetodoPago'] ?? 'No especificado' ?></td>
                        </tr>
                        <?php if ($pedido['Estado'] === 'Enviado' || $pedido['Estado'] === 'Entregado' || $pedido['Estado'] === 'Retrasado'): ?>
                            <tr>
                                <th class="text-primary-dark">Fecha envío:</th>
                                <td>
                                    <?= !empty($pedido['Fecha_Envio']) ? date('d/m/Y H:i', strtotime($pedido['Fecha_Envio'])) : 'N/A' ?>
                                    <?php if (!empty($pedido['NombreEnvio'])): ?>
                                        <br>
                                        <small class="text-muted">Por: <?= $pedido['NombreEnvio'] ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-primary-dark">N° Guía:</th>
                                <td class="text-primary-dark"><?= $pedido['Numero_Guia'] ?? 'No especificado' ?></td>
                            </tr>
                            <tr>
                                <th class="text-primary-dark">Transportadora:</th>
                                <td><?= $pedido['Transportadora'] ?? 'No especificado' ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($pedido['Estado'] === 'Entregado'): ?>
                            <tr>
                                <th class="text-primary-dark">Fecha entrega:</th>
                                <td>
                                    <?= !empty($pedido['Fecha_Entrega']) ? date('d/m/Y H:i', strtotime($pedido['Fecha_Entrega'])) : 'N/A' ?>
                                    <?php if (!empty($pedido['NombreEntrega'])): ?>
                                        <br>
                                        <small class="text-muted">Por: <?= $pedido['NombreEntrega'] ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($pedido['Estado'] === 'Anulado'): ?>
                            <tr>
                                <th class="text-primary-dark">Motivo anulación:</th>
                                <td class="text-primary-dark"><?= htmlspecialchars($pedido['Motivo_Anulacion'] ?? 'No especificado') ?></td>
                            </tr>
                            <tr>
                                <th class="text-primary-dark">Fecha anulación:</th>
                                <td><?= !empty($pedido['Fecha_Anulacion']) ? date('d/m/Y H:i', strtotime($pedido['Fecha_Anulacion'])) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th class="text-primary-dark">Anulado por:</th>
                                <td><?= $pedido['Usuario_Anulacion'] ?? 'No especificado' ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información del Cliente</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%" class="text-primary-dark">Nombre:</th>
                            <td><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Email:</th>
                            <td><?= htmlspecialchars($pedido['Correo']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Teléfono:</th>
                            <td><?= htmlspecialchars($pedido['Celular']) ?></td>
                        </tr>
                        <tr>
                            <th class="text-primary-dark">Dirección:</th>
                            <td>
                                <?php if (!empty($pedido['Direccion'])): ?>
                                    <?= htmlspecialchars($pedido['Direccion']) ?><br>
                                    <?= htmlspecialchars($pedido['Ciudad']) ?>,
                                    <?= htmlspecialchars($pedido['Departamento']) ?><br>
                                    Código Postal: <?= htmlspecialchars($pedido['CodigoPostal']) ?>
                                <?php else: ?>
                                    <span class="text-muted">No especificada</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de seguimiento -->
    <div class="card mb-4" id="seguimiento">
        <div class="card-header bg-primary-light text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Seguimiento</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pedido['seguimiento'])): ?>
                <div class="no-results text-center py-4">
                    <i class="fas fa-history fa-4x text-primary mb-3"></i>
                    <h5 class="text-primary-dark">No hay historial de seguimiento</h5>
                    <p class="text-muted">Aún no hay movimientos registrados para este pedido.</p>
                </div>
            <?php else: ?>
                <div class="timeline">
                    <?php foreach ($pedido['seguimiento'] as $registro): ?>
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon me-3">
                                    <i class="fas 
                                        <?= match ($registro['Estado']) {
                                            'Emitido' => 'fa-file-invoice',
                                            'Confirmado' => 'fa-check-circle',
                                            'Preparando' => 'fa-box-open',
                                            'Enviado' => 'fa-truck',
                                            'Entregado' => 'fa-box-check',
                                            'Retrasado' => 'fa-clock',
                                            'Devuelto' => 'fa-undo',
                                            'Anulado' => 'fa-times-circle',
                                            default => 'fa-circle'
                                        } ?> 
                                        text-primary"
                                        style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="text-primary-dark"><?= $registro['Estado'] ?></strong>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($registro['Fecha'])) ?>
                                        </small>
                                    </div>
                                    <?php if (!empty($registro['Descripcion'])): ?>
                                        <p class="mb-1 text-muted"><?= htmlspecialchars($registro['Descripcion']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($registro['UsuarioNombre'])): ?>
                                        <small class="text-primary-dark">
                                            <i class="fas fa-user me-1"></i>
                                            <?= htmlspecialchars($registro['UsuarioNombre'] . ' ' . $registro['UsuarioApellido']) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario para cambiar estado (solo si no está anulado o entregado) -->
            <?php if ($pedido['Estado'] !== 'Anulado' && $pedido['Estado'] !== 'Entregado'): ?>
                <hr class="my-4">
                <h5 class="mb-3 text-primary-dark"><i class="fas fa-sync me-2"></i>Actualizar Estado del Pedido</h5>

                <?php if ($pedido['Estado'] === 'Preparando'): ?>

                    <!-- Botón de Envío Rápido y Cancelar -->
                    <?php if ($pedido['Estado'] === 'Preparando'): ?>
                        <div class="row mt-3">
                            <div class="col-md-8">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary-dark text-white">
                                        <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Envío Rápido</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-primary-dark mb-3">Marca este pedido como enviado de forma rápida y sencilla:</p>
                                        <a href="<?= BASE_URL ?>?c=Pedido&a=envioRapido&id=<?= $pedido['ID_Factura'] ?>"
                                            class="btn btn-primary-dark">
                                            <i class="fas fa-bolt me-2"></i> Enviar Pedido
                                        </a>
                                        <small class="text-muted ms-2">Genera automáticamente número de guía y configura envío con un clic</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-times-circle me-2"></i>Cancelar Proceso</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="text-primary-dark mb-3">Si necesitas detener el proceso actual:</p>
                                        <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                                           class="btn btn-outline-danger w-100">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </a>
                                        <small class="text-muted mt-2 d-block">
                                            <i class="fas fa-info-circle me-1"></i> Vuelve al detalle sin realizar cambios
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const checkbox = document.getElementById('generar_automatico');
                            const campoPersonalizado = document.getElementById('campo-guia-personalizado');
                            const ejemploGuia = document.getElementById('ejemplo-guia');

                            // Generar nuevo ejemplo cada vez que se carga la página
                            function generarEjemploGuia() {
                                const fecha = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                                const idFactura = <?= $pedido['ID_Factura'] ?>;
                                const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
                                const nuevoEjemplo = `TLL-${fecha}-${idFactura}-${random}`;
                                ejemploGuia.textContent = nuevoEjemplo;
                            }

                            // Generar ejemplo inicial
                            generarEjemploGuia();

                            // Actualizar ejemplo cada 30 segundos (por si la página está abierta)
                            setInterval(generarEjemploGuia, 30000);

                            // Mostrar/ocultar campo personalizado
                            checkbox.addEventListener('change', function() {
                                if (this.checked) {
                                    campoPersonalizado.style.display = 'none';
                                } else {
                                    campoPersonalizado.style.display = 'block';
                                }
                            });

                            // Validar formulario
                            document.getElementById('formEnviarPedido').addEventListener('submit', function(e) {
                                const transportadora = document.getElementById('Transportadora').value.trim();
                                const generarAuto = document.getElementById('generar_automatico').checked;
                                const guiaPersonalizada = document.getElementById('Numero_Guia_Personalizado').value.trim();

                                // Validar transportadora
                                if (!transportadora) {
                                    e.preventDefault();
                                    alert('Por favor ingresa el nombre de la transportadora');
                                    document.getElementById('Transportadora').focus();
                                    return;
                                }

                                // Si no es automático, validar guía personalizada
                                if (!generarAuto && !guiaPersonalizada) {
                                    e.preventDefault();
                                    alert('Por favor ingresa un número de guía o selecciona "Generar automáticamente"');
                                    document.getElementById('Numero_Guia_Personalizado').focus();
                                    return;
                                }

                                // Mostrar confirmación
                                if (!confirm('¿Confirmar envío del pedido <?= $pedido['Codigo_Acceso'] ?>?')) {
                                    e.preventDefault();
                                }
                            });
                        });
                    </script>
                <?php elseif ($pedido['Estado'] === 'Enviado' || $pedido['Estado'] === 'Retrasado'): ?>
                    <!-- Opciones para pedidos enviados -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary-dark text-white">
                                    <h6 class="mb-0"><i class="fas fa-box-check me-2"></i>Marcar como Entregado</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= BASE_URL ?>?c=Pedido&a=marcarEntregado" method="post" id="formEntregado">
                                        <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                                        <div class="mb-3">
                                            <label for="Descripcion" class="form-label text-primary-dark">
                                                <strong>Motivo de entrega:</strong>
                                            </label>
                                            <textarea class="form-control" id="Descripcion" name="Descripcion" rows="2" required
                                                placeholder="Ej: Producto entregado satisfactoriamente al cliente, firma recibida, sin novedades...">Producto entregado satisfactoriamente al cliente. Firma recibida y sin novedades en la entrega.</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary-dark w-100" onclick="return confirmarCambioEstado('entregado')">
                                            <i class="fas fa-check me-1"></i> Confirmar Entrega
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary-light text-white">
                                    <h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Otros Estados</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modalRetrasado">
                                            <i class="fas fa-clock me-1"></i> Marcar como Retrasado
                                        </button>
                                        <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modalDevuelto">
                                            <i class="fas fa-undo me-1"></i> Marcar como Devuelto
                                        </button>
                                        <button type="button" class="btn btn-outline-primary"
                                            data-bs-toggle="modal" data-bs-target="#modalAnulado">
                                            <i class="fas fa-times me-1"></i> Anular Pedido
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                            onclick="window.location.href='<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>'">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Sistema de botones para cambiar estado -->
                    <div class="card mt-3">
                        <div class="card-header bg-primary-dark text-white">
                            <h6 class="mb-0"><i class="fas fa-sync me-2"></i>Seleccionar Nuevo Estado</h6>
                        </div>
                        <div class="card-body">
                            <?php if ($pedido['Estado'] === 'Emitido'): ?>
                                <!-- Opciones para pedido Emitido -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="card h-100 border-primary">
                                            <div class="card-body text-center">
                                                <i class="fas fa-check-circle fa-3x text-primary mb-3"></i>
                                                <h5 class="text-primary-dark">Confirmar Pedido</h5>
                                                <p class="text-muted small">El pedido ha sido validado y está listo para preparación.</p>
                                                <button type="button" class="btn btn-primary-dark w-100"
                                                        data-bs-toggle="modal" data-bs-target="#modalConfirmado">
                                                    <i class="fas fa-check me-1"></i> Confirmar Pedido
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body text-center">
                                                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                                                <h5 class="text-primary-dark">Anular Pedido</h5>
                                                <p class="text-muted small">Cancelar el pedido por cualquier motivo.</p>
                                                <button type="button" class="btn btn-outline-danger w-100"
                                                        data-bs-toggle="modal" data-bs-target="#modalAnulado">
                                                    <i class="fas fa-times me-1"></i> Anular Pedido
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            <?php elseif ($pedido['Estado'] === 'Confirmado'): ?>
                                <!-- Opciones para pedido Confirmado -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body text-center">
                                                <i class="fas fa-box-open fa-3x text-primary-dark mb-3"></i>
                                                <h5 class="text-primary-dark">Preparar Pedido</h5>
                                                <p class="text-muted small">El producto está siendo preparado y empaquetado en nuestras oficinas.</p>
                                                <button type="button" class="btn btn-warning w-100"
                                                        data-bs-toggle="modal" data-bs-target="#modalPreparando">
                                                    <i class="fas fa-cogs me-1"></i> Iniciar Preparación
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body text-center">
                                                <i class="fas fa-times-circle fa-3x text-primary-light mb-3"></i>
                                                <h5 class="text-primary-dark">Anular Pedido</h5>
                                                <p class="text-muted small">Cancelar el pedido antes de la preparación.</p>
                                                <button type="button" class="btn btn-outline-danger w-100"
                                                        data-bs-toggle="modal" data-bs-target="#modalAnulado">
                                                    <i class="fas fa-times me-1"></i> Anular Pedido
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                            <?php elseif ($pedido['Estado'] === 'Devuelto'): ?>
                                <!-- Opciones para pedido Devuelto -->
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <div class="card h-100 border-warning">
                                            <div class="card-body text-center">
                                                <i class="fas fa-redo fa-3x text-primary-dark mb-3"></i>
                                                <h5 class="text-primary-dark">Preparar Nuevamente</h5>
                                                <p class="text-muted small">Reiniciar el proceso de preparación para este pedido devuelto.</p>
                                                <button type="button" class="btn btn-warning w-100"
                                                        data-bs-toggle="modal" data-bs-target="#modalPrepararDevuelto">
                                                    <i class="fas fa-redo me-1"></i> Preparar Nuevamente
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card h-100 border-danger">
                                            <div class="card-body text-center">
                                                <i class="fas fa-times-circle fa-3x text-primary-dark mb-3"></i>
                                                <h5 class="text-primary-dark">Anular Definitivamente</h5>
                                                <p class="text-muted small">Cancelar el pedido después de ser devuelto.</p>
                                                <button type="button" class="btn btn-outline-danger w-100"
                                                        data-bs-toggle="modal" data-bs-target="#modalAnulado">
                                                    <i class="fas fa-times me-1"></i> Anular Pedido
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Botón Cancelar -->
                            <div class="mt-3 pt-3 border-top">
                                <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                                   class="btn btn-outline-danger w-100">
                                    <i class="fas fa-times me-1"></i> Cancelar y Volver al Detalle
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-primary-dark mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Este pedido está <strong><?= $pedido['Estado'] ?></strong> y no se puede modificar.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Productos del Pedido - CORREGIDO -->
    <div class="card">
        <div class="card-header bg-primary-light text-white">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Productos del Pedido</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pedido['productos'])): ?>
                <div class="no-results text-center py-4">
                    <i class="fas fa-box-open fa-4x text-primary mb-3"></i>
                    <h5 class="text-primary-dark">No hay productos en este pedido</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary-dark">
                            <tr>
                                <th width="5%">#</th>
                                <th>Producto</th>
                                <th>Variantes</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Descuento</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $contador = 1;
                            $totalSubtotal = 0;
                            foreach ($pedido['productos'] as $producto): 
                                $precioUnitario = $producto['Precio_Unitario'] ?? 0;
                                $cantidad = $producto['Cantidad'] ?? 1;
                                $descuentoPorcentaje = $producto['Descuento_Aplicado'] ?? 0;
                                
                                // Calcular el precio con descuento si aplica
                                $precioConDescuento = $precioUnitario;
                                if ($descuentoPorcentaje > 0) {
                                    $precioConDescuento = $precioUnitario * (1 - ($descuentoPorcentaje / 100));
                                }
                                
                                $subtotalProducto = $precioConDescuento * $cantidad;
                                $totalSubtotal += $subtotalProducto;
                                
                                // Obtener información de variantes
                                $variantes = [];
                                if (!empty($producto['Atributo1'])) {
                                    $variantes[] = $producto['Atributo1'];
                                }
                                if (!empty($producto['Atributo2'])) {
                                    $variantes[] = $producto['Atributo2'];
                                }
                                if (!empty($producto['Atributo3'])) {
                                    $variantes[] = $producto['Atributo3'];
                                }
                                
                                // Determinar qué tipo de atributo es cada uno
                                $variantesDetalladas = [];
                                foreach ($variantes as $variante) {
                                    // Basado en los datos, podemos determinar si es color, talla, etc.
                                    if (in_array(strtolower($variante), ['amarillo', 'azul', 'negro', 'blanco', 'rojo', 'verde', 'morado', 'gris'])) {
                                        $variantesDetalladas[] = '<span class="badge bg-primary-light text-white">Color: ' . htmlspecialchars($variante) . '</span>';
                                    } elseif (in_array(strtolower($variante), ['xs', 's', 'm', 'l', 'xl', 'xxl', '28', '30', '32', '34', '36', '38', '40', '42'])) {
                                        $variantesDetalladas[] = '<span class="badge bg-primary text-white">Talla: ' . htmlspecialchars($variante) . '</span>';
                                    } elseif (in_array(strtolower($variante), ['19', '20', '28', '30', '32', '34', '36'])) {
                                        $variantesDetalladas[] = '<span class="badge bg-warning text-white">Medida: ' . htmlspecialchars($variante) . '</span>';
                                    } else {
                                        $variantesDetalladas[] = '<span class="badge bg-secondary text-white">' . htmlspecialchars($variante) . '</span>';
                                    }
                                }
                            ?>
                                <tr>
                                    <td class="text-primary-dark"><?= $contador++ ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($producto['Foto'])): ?>
                                                <img src="<?= BASE_URL . $producto['Foto'] ?>"
                                                    alt="<?= htmlspecialchars($producto['NombreProducto']) ?>"
                                                    style="width: 60px; height: 60px; object-fit: cover; margin-right: 12px; border-radius: 6px; border: 1px solid #dee2e6;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; background: #f8f9fa; border-radius: 6px; display: flex; align-items: center; justify-content: center; margin-right: 12px; border: 1px solid #dee2e6;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong class="text-primary-dark d-block"><?= htmlspecialchars($producto['NombreProducto'] ?? 'Producto sin nombre') ?></strong>
                                                <?php if (!empty($producto['Nombre_Producto'])): ?>
                                                    <small class="text-muted"><?= htmlspecialchars($producto['Nombre_Producto']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($variantesDetalladas)): ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?= implode(' ', $variantesDetalladas) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Sin variantes especificadas</span>
                                        <?php endif; ?>
                                        <?php if (!empty($producto['StockActual'])): ?>
                                            <div class="mt-1">
                                                <small class="text-primary-dark">
                                                    <i class="fas fa-cubes me-1"></i>Stock disponible: <?= $producto['StockActual'] ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary" style="font-size: 1rem; min-width: 40px;">
                                            <?= $cantidad ?>
                                        </span>
                                    </td>
                                    <td class="text-end text-primary-dark">
                                        <div class="fw-semibold">$<?= number_format($precioUnitario, 2) ?></div>
                                        <?php if ($descuentoPorcentaje > 0): ?>
                                            <div class="text-primary-dark small">
                                                <s class="text-muted">$<?= number_format($precioUnitario, 2) ?></s>
                                                <br>
                                                $<?= number_format($precioConDescuento, 2) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($descuentoPorcentaje > 0): ?>
                                            <span class="badge bg-primary-light text-white" style="font-size: 0.9rem;">
                                                <?= number_format($descuentoPorcentaje, 1) ?>%
                                            </span>
                                            <div class="text-primary-dark small mt-1">
                                                -$<?= number_format(($precioUnitario - $precioConDescuento) * $cantidad, 2) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Sin descuento</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-bold text-primary-dark">
                                        $<?= number_format($subtotalProducto, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary-dark">
                                <td colspan="6" class="text-end text-primary-dark"><strong>SUBTOTAL:</strong></td>
                                <td class="text-primary-dark"><strong>$<?= number_format($totalSubtotal, 2) ?></strong></td>
                            </tr>
                            <tr class="table-primary">
                                <td colspan="6" class="text-end text-primary-dark"><strong>IVA (19%):</strong></td>
                                <td class="text-primary-dark"><strong>$<?= number_format($totalSubtotal * 0.19, 2) ?></strong></td>
                            </tr>
                            <tr class="table-primary-dark">
                                <td colspan="6" class="text-end text-primary-dark"><strong>TOTAL:</strong></td>
                                <td class="text-primary-dark"><strong>$<?= number_format($totalSubtotal * 1.19, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Resumen de facturación -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen de Facturación</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Subtotal Productos:</th>
                                        <td class="text-end">$<?= number_format($totalSubtotal, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <th>IVA (19%):</th>
                                        <td class="text-end">$<?= number_format($totalSubtotal * 0.19, 2) ?></td>
                                    </tr>
                                    <tr class="border-top">
                                        <th class="h5">Total Factura:</th>
                                        <td class="h5 text-end text-primary-dark">$<?= number_format($totalSubtotal * 1.19, 2) ?></td>
                                    </tr>
                                    <?php if (isset($pedido['Monto_Total'])): ?>
                                    <tr class="border-top">
                                        <th>Total Registrado:</th>
                                        <td class="text-end">
                                            $<?= number_format($pedido['Monto_Total'], 2) ?>
                                            <?php if (abs(($totalSubtotal * 1.19) - $pedido['Monto_Total']) > 0.01): ?>
                                                <br>
                                                <small class="text-<?= (($totalSubtotal * 1.19) > $pedido['Monto_Total']) ? 'primary' : 'primary-dark' ?>">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Diferencia: $<?= number_format(abs(($totalSubtotal * 1.19) - $pedido['Monto_Total']), 2) ?>
                                                </small>
                                            <?php else: ?>
                                                <br>
                                                <small class="text-primary-dark">
                                                    <i class="fas fa-check-circle me-1"></i>Coincide
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-primary-dark">
                            <div class="card-header bg-primary-dark text-white">
                                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Pedido</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6">
                                        <strong class="text-primary-dark d-block">Productos:</strong>
                                        <span class="h5"><?= count($pedido['productos']) ?></span>
                                    </div>
                                    <div class="col-6">
                                        <strong class="text-primary-dark d-block">Items Totales:</strong>
                                        <span class="h5">
                                            <?php 
                                            $totalItems = 0;
                                            foreach ($pedido['productos'] as $producto) {
                                                $totalItems += $producto['Cantidad'] ?? 1;
                                            }
                                            echo $totalItems;
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-12">
                                        <strong class="text-primary-dark d-block">Descuentos Aplicados:</strong>
                                        <?php
                                        $descuentosAplicados = [];
                                        foreach ($pedido['productos'] as $producto) {
                                            if (!empty($producto['Descuento_Aplicado']) && $producto['Descuento_Aplicado'] > 0) {
                                                $descuentosAplicados[] = number_format($producto['Descuento_Aplicado'], 1) . '%';
                                            }
                                        }
                                        if (!empty($descuentosAplicados)): ?>
                                            <div class="mt-2">
                                                <?php foreach (array_unique($descuentosAplicados) as $descuento): ?>
                                                    <span class="badge bg-primary-light text-white me-1"><?= $descuento ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No se aplicaron descuentos</span>
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

    <!-- Modales para cambio de estado -->
    <!-- Modal para Confirmado -->
    <div class="modal fade" id="modalConfirmado" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Confirmar Pedido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post" id="formConfirmado">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Confirmado">
                    <div class="modal-body">
                        <div class="alert alert-primary-light mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Información:</strong> El pedido será marcado como confirmado y procederá a preparación.
                        </div>
                        <p>¿Confirmar el pedido <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong>?</p>
                        <div class="mb-3">
                            <label for="descripcionConfirmado" class="form-label text-primary-dark">
                                <strong>Descripción (Opcional):</strong>
                            </label>
                            <textarea class="form-control" id="descripcionConfirmado" name="Descripcion" rows="2"
                                placeholder="Ej: Pedido validado correctamente, pago confirmado, información verificada...">Pedido validado correctamente. Pago confirmado e información del cliente verificada.</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-dark" onclick="return confirmarCambioEstado('confirmado')">
                            <i class="fas fa-check me-1"></i> Confirmar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Preparando -->
    <div class="modal fade" id="modalPreparando" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-box-open me-2"></i>Iniciar Preparación
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post" id="formPreparando">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Preparando">
                    <div class="modal-body">
                        <div class="alert alert-primary-light mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Información:</strong> El producto será preparado y empaquetado en nuestras oficinas.
                        </div>
                        <p>¿Iniciar preparación del pedido <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong>?</p>
                        <div class="mb-3">
                            <label for="descripcionPreparando" class="form-label text-primary-dark">
                                <strong>Descripción (Obligatoria):</strong>
                            </label>
                            <textarea class="form-control" id="descripcionPreparando" name="Descripcion" rows="3" required
                                placeholder="Ej: Producto en proceso de preparación, empaquetado estándar, verificación de calidad...">Producto en proceso de preparación y empaquetado en nuestras oficinas. Se realiza verificación de calidad antes del envío.</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" onclick="return confirmarCambioEstado('preparando')">
                            <i class="fas fa-cogs me-1"></i> Iniciar Preparación
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Preparar Devuelto -->
    <div class="modal fade" id="modalPrepararDevuelto" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-redo me-2"></i>Preparar Nuevamente Pedido Devuelto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=prepararDevuelto" method="post" id="formPrepararDevuelto">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <div class="modal-body">
                        <div class="alert alert-primary-light mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Información:</strong> Este pedido fue devuelto y será preparado nuevamente para un nuevo envío.
                        </div>
                        <p>¿Preparar nuevamente el pedido <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong>?</p>
                        <div class="mb-3">
                            <label for="descripcionPrepararDevuelto" class="form-label text-primary-dark">
                                <strong>Descripción (Obligatoria):</strong>
                            </label>
                            <textarea class="form-control" id="descripcionPrepararDevuelto" name="Descripcion" rows="3" required
                                placeholder="Ej: Producto devuelto en proceso de re-preparación, verificación de estado, nuevo empaque...">Producto devuelto en proceso de re-preparación. Se verifica el estado del producto y se realiza nuevo empaque para reenvío.</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" onclick="return confirmarCambioEstado('preparar_devuelto')">
                            <i class="fas fa-redo me-1"></i> Preparar Nuevamente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para retrasado -->
    <div class="modal fade" id="modalRetrasado" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-clock me-2"></i>Marcar como Retrasado
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post" id="formRetrasado">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Retrasado">
                    <div class="modal-body">
                        <div class="alert alert-primary-light mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atención:</strong> Marcar como retrasado notificará al cliente sobre el retardo en la entrega.
                        </div>
                        <p>¿Marcar pedido <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong> como retrasado?</p>
                        <div class="mb-3">
                            <label for="descripcionRetraso" class="form-label text-primary-dark">
                                <strong>Motivo del retraso (Obligatorio):</strong>
                            </label>
                            <textarea class="form-control" id="descripcionRetraso" name="Descripcion" rows="3" required
                                placeholder="Ej: Problemas con la transportadora, mal clima, dirección incorrecta, etc."></textarea>
                            <div class="mt-2">
                                <small class="text-primary-dark"><strong>Sugerencias rápidas:</strong></small>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Retraso por condiciones climáticas adversas que afectan el transporte.">Clima</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Problemas operativos con la transportadora asignada.">Transportadora</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Dirección incompleta o incorrecta proporcionada por el cliente.">Dirección</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Alta demanda que afecta los tiempos de procesamiento.">Alta demanda</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning" onclick="return confirmarCambioEstado('retrasado')">
                            <i class="fas fa-clock me-1"></i> Marcar como Retrasado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para devuelto -->
    <div class="modal fade" id="modalDevuelto" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-undo me-2"></i>Marcar como Devuelto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post" id="formDevuelto">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Devuelto">
                    <div class="modal-body">
                        <div class="alert alert-primary-light mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Atención:</strong> El producto será devuelto al almacén para su revisión.
                        </div>
                        <p>¿Marcar pedido <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong> como devuelto?</p>
                        <div class="mb-3">
                            <label for="descripcionDevuelto" class="form-label text-primary-dark">
                                <strong>Motivo de la devolución (Obligatorio):</strong>
                            </label>
                            <textarea class="form-control" id="descripcionDevuelto" name="Descripcion" rows="3" required
                                placeholder="Ej: Cliente no aceptó el producto, producto dañado, dirección incorrecta, etc."></textarea>
                            <div class="mt-2">
                                <small class="text-primary-dark"><strong>Sugerencias rápidas:</strong></small>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Cliente no aceptó el producto al momento de la entrega.">No aceptado</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Producto llegó dañado durante el transporte.">Dañado</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Cliente no se encontraba en la dirección en el momento de entrega.">Ausente</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Dirección incorrecta o incompleta imposibilitó la entrega.">Dirección errónea</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary-dark" onclick="return confirmarCambioEstado('devuelto')">
                            <i class="fas fa-undo me-1"></i> Marcar como Devuelto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para anular pedido -->
    <div class="modal fade" id="modalAnulado" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Anular Pedido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post" id="formAnulado">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Anulado">
                    <div class="modal-body">
                        <div class="alert alert-warning-pedidos mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>¡Atención! Esta acción no se puede deshacer.</strong>
                            <div class="mt-2">
                                <i class="fas fa-boxes me-2 text-primary-dark"></i>
                                <span class="text-primary-dark fw-bold">El stock de los productos será devuelto automáticamente.</span>
                            </div>
                        </div>
                        
                        <!-- Mostrar productos que serán devueltos -->
                        <?php if (!empty($pedido['productos'])): ?>
                        <div class="card mb-3 border-primary">
                            <div class="card-header bg-primary text-white py-2">
                                <h6 class="mb-0"><i class="fas fa-boxes me-2"></i>Productos a devolver al stock</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="table-responsive">
                                    <table class="table table-sm table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-primary-dark small">Producto</th>
                                                <th class="text-primary-dark small text-center">Cantidad</th>
                                                <th class="text-primary-dark small text-center">Stock Actual</th>
                                                <th class="text-primary-dark small text-center">Stock Nuevo</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $totalUnidades = 0;
                                            foreach ($pedido['productos'] as $producto): 
                                                $cantidad = $producto['Cantidad'] ?? 1;
                                                $stockActual = $producto['StockActual'] ?? 0;
                                                $stockNuevo = $stockActual + $cantidad;
                                                $totalUnidades += $cantidad;
                                            ?>
                                                <tr class="border-bottom border-light">
                                                    <td class="small">
                                                        <span class="text-primary-dark fw-semibold"><?= htmlspecialchars($producto['NombreProducto'] ?? 'Producto') ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary-dark"><?= $cantidad ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary"><?= $stockActual ?></span> 
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-primary-light text-white"><?= $stockNuevo ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr class="border-top border-light">
                                                <td class="text-primary-dark fw-bold small">Total:</td>
                                                <td class="text-center">
                                                    <span class="badge bg-primary-dark"><?= $totalUnidades ?></span>
                                                </td>
                                                <td colspan="2" class="text-primary-dark small text-center">
                                                    <i class="fas fa-info-circle me-1"></i> Todas las unidades serán devueltas
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label for="motivoAnulacion" class="form-label text-primary-dark">
                                <strong>Motivo de la anulación (Obligatorio):</strong>
                            </label>
                            <textarea class="form-control" id="motivoAnulacion" name="Descripcion" rows="3" required
                                placeholder="Ej: Cliente canceló la compra, error en el pedido, stock insuficiente, etc."></textarea>
                            <div class="form-text text-primary-dark">
                                <i class="fas fa-info-circle me-1"></i> El stock de todos los productos será devuelto automáticamente.
                            </div>
                            
                            <!-- Sugerencias rápidas (MANTENIDAS) -->
                            <div class="mt-2">
                                <small class="text-primary-dark"><strong>Sugerencias rápidas:</strong></small>
                                <div class="d-flex flex-wrap gap-2 mt-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Cliente solicitó la cancelación de su pedido.">Cancelación cliente</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Error en el proceso de pedido que imposibilita su cumplimiento.">Error en pedido</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Stock insuficiente para cumplir con el pedido.">Sin stock</button>
                                    <button type="button" class="btn btn-sm btn-outline-primary sugerencia-btn" data-text="Problemas con el método de pago proporcionado por el cliente.">Pago fallido</button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Casillas de verificación para confirmar anulación -->
                        <div class="casillas-verificacion-anulacion mb-4 p-3 border rounded bg-light">
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-shield-alt me-2"></i>
                                <strong>Verificación de seguridad crítica:</strong> Marque las siguientes casillas para confirmar la anulación.
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input casilla-anulacion" 
                                    type="checkbox" 
                                    value="1" 
                                    id="casilla1_anulacion">
                                <label class="form-check-label text-primary-dark" for="casilla1_anulacion">
                                    <strong>Confirmo que deseo anular este pedido permanentemente</strong><br>
                                    <small class="text-muted">Esta acción no se puede deshacer y el pedido será marcado como anulado.</small>
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input casilla-anulacion" 
                                    type="checkbox" 
                                    value="1" 
                                    id="casilla2_anulacion">
                                <label class="form-check-label text-primary-dark" for="casilla2_anulacion">
                                    <strong>Entiendo que el stock será devuelto automáticamente</strong><br>
                                    <small class="text-muted"><?= $totalUnidades ?> unidades serán sumadas al stock de los productos.</small>
                                </label>
                            </div>
                            
                            <!-- Contador de verificación -->
                            <div class="verificacion-contador-anulacion mt-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="text-primary-dark">Verificación completada:</small>
                                    <small><span id="contadorCasillasAnulacion">0</span>/2</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div id="barraProgresoAnulacion" 
                                        class="progress-bar" 
                                        role="progressbar" 
                                        style="width: 0%" 
                                        aria-valuenow="0" 
                                        aria-valuemin="0" 
                                        aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">El botón de anulación se habilitará cuando marques ambas casillas.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-danger" id="btnAnularPedido" disabled>
                            <i class="fas fa-times me-1"></i> Anular Pedido y Devolver Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="assets/css/usuario.css">
<link rel="stylesheet" href="assets/css/pedido.css">

<!-- JS -->
<script src="assets/js/pedido.js"></script>

<!-- Script específico para detalle de pedido -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-ocultar mensajes globales después de 5 segundos
    setTimeout(function() {
        const mensajeGlobal = document.getElementById('mensajeGlobal');
        if (mensajeGlobal) {
            mensajeGlobal.style.display = 'none';
        }
    }, 5000);
    
    // Efecto smooth scroll para anclas
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Botones de sugerencias rápidas
    document.querySelectorAll('.sugerencia-btn').forEach(button => {
        button.addEventListener('click', function() {
            const text = this.getAttribute('data-text');
            const textarea = this.closest('.modal-body').querySelector('textarea');
            textarea.value = text;
        });
    });

    // Validar formularios antes de enviar
    document.querySelectorAll('form[id^="form"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const textareas = this.querySelectorAll('textarea[required]');
            let isValid = true;
            
            textareas.forEach(textarea => {
                if (!textarea.value.trim()) {
                    isValid = false;
                    textarea.classList.add('is-invalid');
                    // Crear mensaje de error si no existe
                    if (!textarea.nextElementSibling || !textarea.nextElementSibling.classList.contains('invalid-feedback')) {
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.textContent = 'Este campo es obligatorio';
                        textarea.parentNode.appendChild(errorDiv);
                    }
                } else {
                    textarea.classList.remove('is-invalid');
                    const errorDiv = textarea.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.remove();
                    }
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                // Mostrar alerta de error
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert-message alert-danger';
                alertDiv.innerHTML = `
                    <div class="alert-content">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span>Por favor completa todos los campos obligatorios antes de continuar.</span>
                        <button type="button" class="btn-close-alert" onclick="this.parentElement.parentElement.style.display='none'">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                document.body.appendChild(alertDiv);
                
                // Auto-ocultar después de 5 segundos
                setTimeout(() => {
                    alertDiv.style.display = 'none';
                }, 5000);
            }
        });
    });
    
    // Configurar verificación de casillas para modal de anulación
    configurarVerificacionAnulacion();
    
    // Configurar reset de casillas al cerrar modal de anulación
    const modalAnulado = document.getElementById('modalAnulado');
    if (modalAnulado) {
        modalAnulado.addEventListener('hidden.bs.modal', function() {
            resetearCasillasAnulacion();
        });
    }
});

// Función para configurar verificación de casillas de anulación
function configurarVerificacionAnulacion() {
    const casillas = document.querySelectorAll('.casilla-anulacion');
    const btnAnular = document.getElementById('btnAnularPedido');
    const contadorSpan = document.getElementById('contadorCasillasAnulacion');
    const barraProgreso = document.getElementById('barraProgresoAnulacion');
    
    if (casillas.length && btnAnular && contadorSpan && barraProgreso) {
        casillas.forEach(casilla => {
            casilla.addEventListener('change', function() {
                const casillasMarcadas = document.querySelectorAll('.casilla-anulacion:checked');
                const totalCasillas = casillas.length;
                const marcadas = casillasMarcadas.length;
                
                // Actualizar contador
                contadorSpan.textContent = marcadas;
                
                // Actualizar barra de progreso
                const porcentaje = (marcadas / totalCasillas) * 100;
                barraProgreso.style.width = `${porcentaje}%`;
                barraProgreso.setAttribute('aria-valuenow', porcentaje);
                
                // Cambiar color de la barra según el progreso
                if (porcentaje < 50) {
                    barraProgreso.className = 'progress-bar bg-danger';
                } else if (porcentaje < 100) {
                    barraProgreso.className = 'progress-bar bg-warning';
                } else {
                    barraProgreso.className = 'progress-bar bg-success';
                }
                
                // Habilitar/deshabilitar botón
                btnAnular.disabled = marcadas !== totalCasillas;
            });
        });
    }
}

// Función para resetear casillas de anulación
function resetearCasillasAnulacion() {
    const casillas = document.querySelectorAll('.casilla-anulacion');
    const btnAnular = document.getElementById('btnAnularPedido');
    const contadorSpan = document.getElementById('contadorCasillasAnulacion');
    const barraProgreso = document.getElementById('barraProgresoAnulacion');
    
    // Resetear todas las casillas
    casillas.forEach(casilla => {
        casilla.checked = false;
    });
    
    // Resetear botón
    if (btnAnular) {
        btnAnular.disabled = true;
    }
    
    // Resetear contador
    if (contadorSpan) {
        contadorSpan.textContent = '0';
    }
    
    // Resetear barra de progreso
    if (barraProgreso) {
        barraProgreso.style.width = '0%';
        barraProgreso.setAttribute('aria-valuenow', '0');
        barraProgreso.className = 'progress-bar';
    }
}

// Función para confirmar cambio de estado (MODIFICADA PARA ANULACIÓN)
function confirmarCambioEstado(accion) {
    // Si es anulación, mostrar el modal de Bootstrap directamente
    if (accion === 'anulado') {
        // Primero, verificar que el textarea tenga contenido
        const motivoAnulacion = document.getElementById('motivoAnulacion');
        if (motivoAnulacion && !motivoAnulacion.value.trim()) {
            mostrarMensaje('Por favor ingresa el motivo de la anulación antes de continuar', 'danger');
            motivoAnulacion.focus();
            motivoAnulacion.classList.add('is-invalid');
            return false;
        }
        
        // Resetear casillas antes de mostrar
        resetearCasillasAnulacion();
        
        // Mostrar modal de anulación
        const modalAnulado = new bootstrap.Modal(document.getElementById('modalAnulado'));
        modalAnulado.show();
        
        return false; // Prevenir envío directo
    }
    
    // Para otros estados, usar el sistema de confirmación anterior
    const mensajes = {
        'confirmado': '¿Estás seguro de confirmar este pedido?',
        'preparando': '¿Iniciar preparación del pedido? El producto será empaquetado en nuestras oficinas.',
        'preparar_devuelto': '¿Preparar nuevamente este pedido devuelto? Se reiniciará el proceso de preparación.',
        'entregado': '¿Marcar este pedido como entregado?',
        'retrasado': '¿Marcar este pedido como retrasado? Se notificará al cliente.',
        'devuelto': '¿Marcar este pedido como devuelto? Volverá al almacén para revisión.',
        'anulado': '¿ESTÁS COMPLETAMENTE SEGURO DE ANULAR ESTE PEDIDO? Esta acción NO se puede deshacer. El stock de todos los productos será devuelto automáticamente.'
    };

    const iconos = {
        'confirmado': 'fas fa-check-circle',
        'preparando': 'fas fa-cogs',
        'preparar_devuelto': 'fas fa-redo',
        'entregado': 'fas fa-box-check',
        'retrasado': 'fas fa-clock',
        'devuelto': 'fas fa-undo',
        'anulado': 'fas fa-exclamation-triangle'
    };

    const colores = {
        'confirmado': 'primary-dark',
        'preparando': 'warning',
        'preparar_devuelto': 'warning',
        'entregado': 'success',
        'retrasado': 'warning',
        'devuelto': 'primary-dark',
        'anulado': 'danger'
    };

    // Crear modal de confirmación personalizado
    const modalId = 'modalConfirmacionEstado';
    let existingModal = document.getElementById(modalId);
    if (existingModal) {
        existingModal.remove();
    }

    const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary-dark text-white">
                        <h5 class="modal-title">
                            <i class="${iconos[accion]} me-2"></i>Confirmar Acción
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <i class="${iconos[accion]} fa-4x text-primary-dark mb-3"></i>
                        <h5 class="text-primary-dark">${mensajes[accion]}</h5>
                        <p class="text-muted mt-3">Pedido: <strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-${colores[accion]}" id="btnConfirmarAccion">
                            <i class="${iconos[accion]} me-1"></i> Confirmar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    const modal = new bootstrap.Modal(document.getElementById(modalId));
    modal.show();

    // Manejar confirmación
    document.getElementById('btnConfirmarAccion').addEventListener('click', function() {
        modal.hide();
        
        // Enviar el formulario correspondiente
        let formId;
        
        switch(accion) {
            case 'preparar_devuelto':
                formId = 'formPrepararDevuelto';
                break;
            case 'confirmado':
                formId = 'formConfirmado';
                break;
            case 'preparando':
                formId = 'formPreparando';
                break;
            case 'entregado':
                formId = 'formEntregado';
                break;
            case 'retrasado':
                formId = 'formRetrasado';
                break;
            case 'devuelto':
                formId = 'formDevuelto';
                break;
            default:
                formId = 'form' + accion.charAt(0).toUpperCase() + accion.slice(1);
        }
        
        const form = document.getElementById(formId);
        if (form) {
            form.submit();
        }
    });

    return false; // Prevenir envío directo
}

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'primary') {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert-message alert-' + tipo;
    alertDiv.innerHTML = `
        <div class="alert-content">
            <i class="fas ${tipo === 'success' ? 'fa-check-circle' : tipo === 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle'} me-2"></i>
            <span>${mensaje}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 4000);
}
</script>