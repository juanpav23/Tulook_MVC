<?php
// Helper para mostrar badges de estado
function getEstadoBadge($estado)
{
    $badgeClass = match ($estado) {
        'Emitido' => 'bg-secondary',
        'Confirmado' => 'bg-primary',
        'Preparando' => 'bg-info',
        'Enviado' => 'bg-warning',
        'Retrasado' => 'bg-danger',
        'Devuelto' => 'bg-dark',
        'Entregado' => 'bg-success',
        'Anulado' => 'bg-secondary',
        default => 'bg-secondary'
    };

    return '<span class="badge ' . $badgeClass . '">' . $estado . '</span>';
}

// Acceder al helper desde el controlador
$getEstadoBadge = $getEstadoBadge ?? 'getEstadoBadge';
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-invoice me-2"></i>Detalle del Pedido #<?= $pedido['ID_Factura'] ?></h2>
        <div>
            <a href="<?= BASE_URL ?>?c=Pedido&a=index" class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="<?= BASE_URL ?>?c=Pedido&a=generarFactura&id=<?= $pedido['ID_Factura'] ?>"
                class="btn btn-info" target="_blank">
                <i class="fas fa-print me-1"></i> Imprimir Factura
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Información del Pedido -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Pedido</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">ID Pedido:</th>
                            <td>#<?= $pedido['ID_Factura'] ?></td>
                        </tr>
                        <tr>
                            <th>Código de acceso:</th>
                            <td><code><?= $pedido['Codigo_Acceso'] ?></code></td>
                        </tr>
                        <tr>
                            <th>Fecha creación:</th>
                            <td><?= date('d/m/Y H:i:s', strtotime($pedido['Fecha_Factura'])) ?></td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td><?= $getEstadoBadge($pedido['Estado']) ?></td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td class="h5 text-success">$<?= number_format($pedido['Monto_Total'], 2) ?></td>
                        </tr>
                        <tr>
                            <th>Método de pago:</th>
                            <td><?= $pedido['MetodoPago'] ?? 'No especificado' ?></td>
                        </tr>
                        <?php if ($pedido['Estado'] === 'Enviado' || $pedido['Estado'] === 'Entregado' || $pedido['Estado'] === 'Retrasado'): ?>
                            <tr>
                                <th>Fecha envío:</th>
                                <td>
                                    <?= !empty($pedido['Fecha_Envio']) ? date('d/m/Y H:i', strtotime($pedido['Fecha_Envio'])) : 'N/A' ?>
                                    <?php if (!empty($pedido['NombreEnvio'])): ?>
                                        <br>
                                        <small class="text-muted">Por: <?= $pedido['NombreEnvio'] ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>N° Guía:</th>
                                <td><?= $pedido['Numero_Guia'] ?? 'No especificado' ?></td>
                            </tr>
                            <tr>
                                <th>Transportadora:</th>
                                <td><?= $pedido['Transportadora'] ?? 'No especificado' ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ($pedido['Estado'] === 'Entregado'): ?>
                            <tr>
                                <th>Fecha entrega:</th>
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
                                <th>Motivo anulación:</th>
                                <td class="text-danger"><?= htmlspecialchars($pedido['Motivo_Anulacion'] ?? 'No especificado') ?></td>
                            </tr>
                            <tr>
                                <th>Fecha anulación:</th>
                                <td><?= !empty($pedido['Fecha_Anulacion']) ? date('d/m/Y H:i', strtotime($pedido['Fecha_Anulacion'])) : 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Anulado por:</th>
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
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Información del Cliente</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="40%">Nombre:</th>
                            <td><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td><?= htmlspecialchars($pedido['Correo']) ?></td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td><?= htmlspecialchars($pedido['Celular']) ?></td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
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
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Seguimiento</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pedido['seguimiento'])): ?>
                <div class="text-center py-3">
                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No hay historial de seguimiento para este pedido.</p>
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
                                        text-<?= match ($registro['Estado']) {
                                                    'Emitido' => 'secondary',
                                                    'Confirmado' => 'primary',
                                                    'Preparando' => 'info',
                                                    'Enviado' => 'warning',
                                                    'Entregado' => 'success',
                                                    'Retrasado' => 'danger',
                                                    'Devuelto' => 'dark',
                                                    'Anulado' => 'secondary',
                                                    default => 'muted'
                                                } ?>"
                                        style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong><?= $registro['Estado'] ?></strong>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($registro['Fecha'])) ?>
                                        </small>
                                    </div>
                                    <?php if (!empty($registro['Descripcion'])): ?>
                                        <p class="mb-1"><?= htmlspecialchars($registro['Descripcion']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($registro['UsuarioNombre'])): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-user"></i>
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
                <hr>
                <h6 class="mt-4"><i class="fas fa-sync me-2"></i>Actualizar Estado del Pedido</h6>

                <?php if ($pedido['Estado'] === 'Preparando'): ?>
                    <!-- Formulario para marcar como enviado -->
                    <div class="card mt-3">
                        <div class="card-header bg-warning">
                            <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Marcar como Enviado</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= BASE_URL ?>?c=Pedido&a=marcarEnviado" method="post" id="formEnviarPedido">
                                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">

                                <!-- Opción de generación automática -->
                                <div class="alert alert-info mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                            id="generar_automatico" name="generar_automatico" value="1" checked>
                                        <label class="form-check-label fw-bold" for="generar_automatico">
                                            <i class="fas fa-robot me-1"></i> Generar número de guía automáticamente
                                        </label>
                                        <div class="form-text">
                                            Se generará un código único: <code id="ejemplo-guia"><?= $ejemploNumeroGuia ?></code>
                                        </div>
                                    </div>
                                </div>

                                <!-- Campo para número de guía personalizado (oculto por defecto) -->
                                <div class="mb-3" id="campo-guia-personalizado" style="display: none;">
                                    <label for="Numero_Guia_Personalizado" class="form-label">
                                        <i class="fas fa-barcode me-1"></i> Número de Guía Personalizado
                                    </label>
                                    <input type="text" class="form-control" id="Numero_Guia_Personalizado"
                                        name="Numero_Guia_Personalizado"
                                        placeholder="Ej: TLL-20241225-123-4567">
                                    <div class="form-text">
                                        Formato sugerido: TLL-AAAAMMDD-ID-RANDOM (ej: <?= $ejemploNumeroGuia ?>)
                                    </div>
                                </div>

                                <!-- Transportadora con sugerencias -->
                                <div class="mb-3">
                                    <label for="Transportadora" class="form-label">
                                        <i class="fas fa-shipping-fast me-1"></i> Transportadora
                                    </label>
                                    <input type="text" class="form-control" id="Transportadora" name="Transportadora"
                                        list="transportadoras-sugerencias"
                                        placeholder="Ej: Servientrega, Interrapidisimo, DHL, Coordinadora">
                                    <datalist id="transportadoras-sugerencias">
                                        <?php foreach ($transportadorasFrecuentes as $transp): ?>
                                            <option value="<?= htmlspecialchars($transp['Transportadora']) ?>">
                                                (<?= $transp['total_envios'] ?> envíos)
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="Servientrega">
                                        <option value="Interrapidisimo">
                                        <option value="DHL">
                                        <option value="Coordinadora">
                                        <option value="Envía">
                                        <option value="Deprisa">
                                        <option value="FedEx">
                                        <option value="TCC">
                                    </datalist>
                                    <div class="form-text">
                                        <?php if (!empty($transportadorasFrecuentes)): ?>
                                            Sugerencias:
                                            <?php foreach ($transportadorasFrecuentes as $index => $transp): ?>
                                                <span class="badge bg-light text-dark me-1">
                                                    <?= htmlspecialchars($transp['Transportadora']) ?> (<?= $transp['total_envios'] ?>)
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Notas -->
                                <div class="mb-3">
                                    <label for="Notas_Envio" class="form-label">
                                        <i class="fas fa-sticky-note me-1"></i> Notas del Envío
                                    </label>
                                    <textarea class="form-control" id="Notas_Envio" name="Notas_Envio" rows="2"
                                        placeholder="Ej: Se entregó a la transportadora, tiempo estimado 3 días, cliente notificado, etc."></textarea>
                                    <div class="form-text">Información adicional sobre el envío</div>
                                </div>

                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-truck me-1"></i> Marcar como Enviado
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Botón de Envío Rápido -->
                    <?php if ($pedido['Estado'] === 'Preparando'): ?>
                        <div class="mt-3">
                            <a href="<?= BASE_URL ?>?c=Pedido&a=envioRapido&id=<?= $pedido['ID_Factura'] ?>"
                                class="btn btn-success btn-lg">
                                <i class="fas fa-bolt me-2"></i> Envío Rápido
                            </a>
                            <small class="text-muted ms-2">Genera automáticamente número de guía y configura envío con un clic</small>
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
                                if (!confirm('¿Confirmar envío del pedido #<?= $pedido['ID_Factura'] ?>?')) {
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
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-box-check me-2"></i>Marcar como Entregado</h6>
                                </div>
                                <div class="card-body">
                                    <form action="<?= BASE_URL ?>?c=Pedido&a=marcarEntregado" method="post">
                                        <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                                        <div class="mb-3">
                                            <label for="Descripcion" class="form-label">Descripción</label>
                                            <textarea class="form-control" id="Descripcion" name="Descripcion" rows="2"
                                                placeholder="Ej: Producto entregado satisfactoriamente"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-check me-1"></i> Confirmar Entrega
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Otros Estados</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-warning"
                                            data-bs-toggle="modal" data-bs-target="#modalRetrasado">
                                            <i class="fas fa-clock me-1"></i> Marcar como Retrasado
                                        </button>
                                        <button type="button" class="btn btn-outline-dark"
                                            data-bs-toggle="modal" data-bs-target="#modalDevuelto">
                                            <i class="fas fa-undo me-1"></i> Marcar como Devuelto
                                        </button>
                                        <button type="button" class="btn btn-outline-danger"
                                            data-bs-toggle="modal" data-bs-target="#modalAnulado">
                                            <i class="fas fa-times me-1"></i> Anular Pedido
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Formulario general para otros estados -->
                    <div class="card mt-3">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-sync me-2"></i>Cambiar Estado</h6>
                        </div>
                        <div class="card-body">
                            <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post">
                                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="Estado" class="form-label">Nuevo Estado</label>
                                        <select class="form-select" id="Estado" name="Estado" required>
                                            <option value="">Seleccionar estado...</option>
                                            <?php if ($pedido['Estado'] === 'Emitido'): ?>
                                                <option value="Confirmado">Confirmado</option>
                                                <option value="Anulado">Anulado</option>
                                            <?php elseif ($pedido['Estado'] === 'Confirmado'): ?>
                                                <option value="Preparando">Preparando</option>
                                                <option value="Anulado">Anulado</option>
                                            <?php elseif ($pedido['Estado'] === 'Devuelto'): ?>
                                                <option value="Preparando">Preparar nuevamente</option>
                                                <option value="Anulado">Anular</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="Descripcion" class="form-label">Descripción</label>
                                        <textarea class="form-control" id="Descripcion" name="Descripcion" rows="2"
                                            placeholder="Descripción del cambio de estado"></textarea>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Actualizar Estado
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Este pedido está <strong><?= $pedido['Estado'] ?></strong> y no se puede modificar.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Productos del Pedido -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Productos del Pedido</h5>
        </div>
        <div class="card-body">
            <?php if (empty($pedido['productos'])): ?>
                <div class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No hay productos en este pedido</h5>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Variantes</th>
                                <th>Cantidad</th>
                                <th>Precio Unitario</th>
                                <th>Descuento</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedido['productos'] as $producto): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if (!empty($producto['Foto'])): ?>
                                                <img src="<?= BASE_URL . $producto['Foto'] ?>"
                                                    alt="<?= htmlspecialchars($producto['NombreProducto']) ?>"
                                                    style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= htmlspecialchars($producto['NombreProducto'] ?? 'Producto') ?></strong><br>
                                                <small class="text-muted">ID: <?= $producto['ID_Producto'] ?? $producto['ID_Articulo'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($producto['Atributo1']) || !empty($producto['Atributo2']) || !empty($producto['Atributo3'])): ?>
                                            <small>
                                                <?= htmlspecialchars($producto['Atributo1'] ?? '') ?>
                                                <?= !empty($producto['Atributo2']) ? ' | ' . htmlspecialchars($producto['Atributo2']) : '' ?>
                                                <?= !empty($producto['Atributo3']) ? ' | ' . htmlspecialchars($producto['Atributo3']) : '' ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Sin variantes</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $producto['Cantidad'] ?></span>
                                    </td>
                                    <td>
                                        $<?= number_format($producto['Precio_Unitario'], 2) ?>
                                    </td>
                                    <td>
                                        <?php if ($producto['Descuento_Aplicado'] > 0): ?>
                                            <span class="badge bg-success">
                                                <?= number_format($producto['Descuento_Aplicado'], 2) ?>%
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Ninguno</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="fw-bold">
                                        $<?= number_format($producto['Subtotal'], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <td colspan="5" class="text-end"><strong>TOTAL:</strong></td>
                                <td><strong>$<?= number_format($pedido['Monto_Total'], 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para retrasado -->
    <div class="modal fade" id="modalRetrasado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-clock me-2"></i>Marcar como Retrasado
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Retrasado">
                    <div class="modal-body">
                        <p>¿Marcar pedido <strong>#<?= $pedido['ID_Factura'] ?></strong> como retrasado?</p>
                        <div class="mb-3">
                            <label for="descripcionRetraso" class="form-label">Motivo del retraso:</label>
                            <textarea class="form-control" id="descripcionRetraso" name="Descripcion" rows="3" required
                                placeholder="Ej: Problemas con la transportadora, mal clima, dirección incorrecta, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-clock me-1"></i> Marcar como Retrasado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para devuelto -->
    <div class="modal fade" id="modalDevuelto" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-undo me-2"></i>Marcar como Devuelto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Devuelto">
                    <div class="modal-body">
                        <p>¿Marcar pedido <strong>#<?= $pedido['ID_Factura'] ?></strong> como devuelto?</p>
                        <div class="mb-3">
                            <label for="descripcionDevuelto" class="form-label">Motivo de la devolución:</label>
                            <textarea class="form-control" id="descripcionDevuelto" name="Descripcion" rows="3" required
                                placeholder="Ej: Cliente no aceptó el producto, producto dañado, dirección incorrecta, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark">
                            <i class="fas fa-undo me-1"></i> Marcar como Devuelto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para anular pedido -->
    <div class="modal fade" id="modalAnulado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Anular Pedido
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?= BASE_URL ?>?c=Pedido&a=actualizarEstado" method="post">
                    <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                    <input type="hidden" name="Estado" value="Anulado">
                    <div class="modal-body">
                        <p class="text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>¡Atención! Esta acción no se puede deshacer.</strong>
                        </p>
                        <p>¿Estás seguro de anular el pedido <strong>#<?= $pedido['ID_Factura'] ?></strong>?</p>
                        <div class="mb-3">
                            <label for="motivoAnulacion" class="form-label">Motivo de la anulación:</label>
                            <textarea class="form-control" id="motivoAnulacion" name="Descripcion" rows="3" required
                                placeholder="Ej: Cliente canceló la compra, error en el pedido, stock insuficiente, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-1"></i> Anular Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>