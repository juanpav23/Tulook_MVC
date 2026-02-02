<?php
// Acceder al helper desde el controlador
$getEstadoBadge = $getEstadoBadge ?? 'getEstadoBadgePedido';
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bolt me-2 text-primary-dark"></i>Envío Rápido - Pedido <?= $pedido['Codigo_Acceso'] ?></h2>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
               class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Volver al detalle
            </a>
        </div>
    </div>

    <!-- Resumen del pedido -->
    <div class="row mb-4">
        <!-- Información del Pedido -->
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-primary-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Resumen del Pedido</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%" class="text-primary-dark">Código:</th>
                                    <td><strong class="text-primary-dark"><?= $pedido['Codigo_Acceso'] ?></strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%" class="text-primary-dark">Cliente:</th>
                                    <td><?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-primary-dark">Email:</th>
                                    <td><?= htmlspecialchars($pedido['Correo']) ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-borderless">
                                <tr>
                                    <th class="text-primary-dark">Total:</th>
                                    <td class="h5 text-primary-dark">$<?= number_format($pedido['Monto_Total'], 2) ?></td>
                                </tr>
                                <tr>
                                    <th class="text-primary-dark">Estado:</th>
                                    <td><?= $getEstadoBadge($pedido['Estado']) ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acción Rápida</h5>
                </div>
                <div class="card-body d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <i class="fas fa-truck fa-3x text-primary-dark mb-3"></i>
                        <p class="text-muted">Marca este pedido como enviado en un solo paso.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de envío rápido -->
    <div class="card">
        <div class="card-header bg-primary-dark text-white">
            <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Configuración de Envío Rápido</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Pedido&a=marcarEnviado" method="post" id="formEnvioRapido">
                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                <input type="hidden" name="generar_automatico" value="1">
                
                <!-- Número de guía generado -->
                <div class="alert alert-primary-light mb-4">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-barcode text-primary-dark me-2"></i>
                        <h6 class="mb-0 text-primary-dark"><strong>Número de Guía Generado Automáticamente</strong></h6>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0 text-primary-dark"><code id="numero-guia-generado"><?= $ejemploNumeroGuia ?></code></h4><br>
                            <small class="text-primary-dark">Formato: TLL-FECHA-ID-RANDOM</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary" onclick="generarNuevoNumero()">
                            <i class="fas fa-redo me-1"></i> Regenerar
                        </button>
                    </div>
                </div>
                
                <!-- Transportadora -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-primary-dark">
                        <i class="fas fa-shipping-fast me-1"></i> Seleccionar Transportadora <span class="text-danger">*</span>
                    </label>
                    
                    <!-- Transportadoras populares -->
                    <div class="row g-2 mb-3" id="transportadoras-container">
                        <?php 
                        $transportadorasPopulares = [
                            'Servientrega' => 'primary-dark',
                            'Interrapidisimo' => 'primary-dark',
                            'DHL' => 'primary-dark',
                            'Coordinadora' => 'primary-dark',
                            'Envía' => 'primary-dark',
                            'Deprisa' => 'primary-dark',
                            'FedEx' => 'primary-dark',
                            'TCC' => 'primary-dark'
                        ];
                        
                        foreach ($transportadorasPopulares as $nombre => $color):
                            $imagenPath = BASE_URL . "assets/img/Img_Servicio/{$nombre}.png";
                            $imagenExists = file_exists(str_replace(BASE_URL, '', $imagenPath));
                        ?>
                            <div class="col-md-3 col-6">
                                <label class="d-block w-100 m-0">
                                    <input class="d-none transportadora-radio" type="radio" 
                                           name="Transportadora" value="<?= $nombre ?>"
                                           <?= $nombre === 'Servientrega' ? 'checked' : '' ?>>
                                    <div class="card card-hover border-primary-dark transportadora-card" data-value="<?= $nombre ?>">
                                        <div class="card-body text-center py-2">
                                            <div class="d-flex align-items-center justify-content-center">
                                                <?php if ($imagenExists): ?>
                                                    <img src="<?= $imagenPath ?>" 
                                                         alt="<?= $nombre ?>"
                                                         style="width: 40px; height: 40px; object-fit: contain; margin-right: 8px; border-radius: 4px;">
                                                <?php endif; ?>
                                                <strong class="text-primary-dark"><?= $nombre ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Transportadora personalizada -->
                    <div class="mt-3">
                        <label for="TransportadoraPersonalizada" class="form-label text-primary-dark">
                            <i class="fas fa-edit me-1"></i> Otra transportadora:
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-primary">
                                <i class="fas fa-truck text-white"></i>
                            </span>
                            <input type="text" class="form-control" id="TransportadoraPersonalizada" 
                                   placeholder="Ingresa el nombre de otra transportadora">
                        </div>
                        <div class="form-text text-primary-dark">
                            <i class="fas fa-info-circle me-1"></i> Al escribir aquí, se deseleccionará cualquier opción anterior.
                        </div>
                    </div>
                    <div id="transportadora-error" class="invalid-feedback d-none">
                        Por favor selecciona o ingresa una transportadora
                    </div>
                </div>
                
                <!-- Fecha estimada de entrega -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-primary-dark">
                        <i class="fas fa-calendar-alt me-1"></i> Fecha Estimada de Entrega <span class="text-danger">*</span>
                    </label>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-primary">
                                    <i class="fas fa-calendar-check text-white"></i>
                                </span>
                                <input type="date" 
                                       class="form-control" 
                                       id="fecha_estimada_entrega" 
                                       name="fecha_estimada_entrega" 
                                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                       value="<?= date('Y-m-d', strtotime('+3 days')) ?>"
                                       required>
                            </div>
                            <div class="form-text text-primary-dark">
                                <i class="fas fa-info-circle me-1"></i> Selecciona la fecha en la que estimas que el pedido será entregado.
                            </div>
                            <div id="fecha-error" class="invalid-feedback d-none">
                                Por favor selecciona una fecha estimada de entrega
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-primary-light">
                                <div class="card-body">
                                    <h6 class="text-primary-dark"><i class="fas fa-lightbulb me-2"></i>Sugerencias rápidas:</h6>
                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary fecha-sugerencia" data-dias="1">
                                            Mañana
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary fecha-sugerencia" data-dias="3">
                                            En 3 días
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary fecha-sugerencia" data-dias="5">
                                            En 5 días
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary fecha-sugerencia" data-dias="7">
                                            En 1 semana
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Notas del envío -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-primary-dark">
                        <i class="fas fa-sticky-note me-1"></i> Notas del Envío
                    </label>
                    
                    <!-- Notas esenciales (no editables) -->
                    <div class="card mb-3 border-primary">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Notas Esenciales del Envío</h6>
                        </div>
                        <div class="card-body">
                            <div class="notas-esenciales">
                                <div class="nota-item d-flex align-items-start mb-2">
                                    <i class="fas fa-check text-success mt-1 me-2"></i>
                                    <span>Se entregó a transportadora, tiempo estimado 3 días.</span>
                                    <input type="hidden" name="notas_esenciales[]" value="Se entregó a transportadora, tiempo estimado 3 días.">
                                </div>
                                <div class="nota-item d-flex align-items-start mb-2">
                                    <i class="fas fa-check text-success mt-1 me-2"></i>
                                    <span>Cliente notificado por correo electrónico.</span>
                                    <input type="hidden" name="notas_esenciales[]" value="Cliente notificado por correo electrónico.">
                                </div>
                                <div class="nota-item d-flex align-items-start">
                                    <i class="fas fa-check text-success mt-1 me-2"></i>
                                    <span>Seguro incluido en el envío.</span>
                                    <input type="hidden" name="notas_esenciales[]" value="Seguro incluido en el envío.">
                                </div>
                            </div>
                            <small class="text-primary-dark mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i> Estas notas se incluirán automáticamente en el historial.
                            </small>
                        </div>
                    </div>
                    
                    <!-- Notas adicionales (editables) -->
                    <div class="card border-primary-light">
                        <div class="card-header bg-primary-light text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-edit me-2"></i>Notas Adicionales (Opcional)</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="Notas_Adicionales" class="form-label text-primary-dark">
                                    <i class="fas fa-plus-circle me-1"></i> Agregar notas adicionales:
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary">
                                        <i class="fas fa-comment text-white"></i>
                                    </span>
                                    <textarea class="form-control" id="Notas_Adicionales" name="Notas_Adicionales" 
                                              rows="3" placeholder="Ej: Instrucciones especiales, observaciones, contacto alternativo..."></textarea>
                                </div>
                                <div class="form-text text-primary-dark">
                                    <i class="fas fa-info-circle me-1"></i> Estas notas se agregarán a las notas esenciales.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botón Cancelar Envío -->
                <div class="mb-4">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white py-2">
                            <h6 class="mb-0"><i class="fas fa-times-circle me-2"></i>Cancelar Envío</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-primary-dark mb-3">
                                Si necesitas cancelar esta acción y volver al detalle del pedido:
                            </p>
                            <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                               class="btn btn-outline-danger w-100">
                                <i class="fas fa-times me-1"></i> Cancelar Envío y Volver al Detalle
                            </a>
                            <small class="text-muted mt-2 d-block">
                                <i class="fas fa-info-circle me-1"></i> Esta acción cancelará el proceso de envío rápido sin guardar cambios.
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Información de confirmación -->
                <div class="alert alert-primary-light mb-4">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-exclamation-circle fa-2x text-primary-dark"></i>
                        </div>
                        <div>
                            <h6 class="text-primary-dark"><strong>Confirmación requerida</strong></h6>
                            <p class="mb-0 text-primary-dark">Al confirmar, el pedido será marcado automáticamente como "Enviado" con el número de guía generado y los detalles proporcionados.</p>
                        </div>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                       class="btn btn-outline-primary">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="button" class="btn btn-primary-dark" id="btnConfirmarEnvio">
                        <i class="fas fa-check-circle me-1"></i> Confirmar Envío
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmacionEnvio" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary-dark text-white">
                <h5 class="modal-title">
                    <i class="fas fa-truck me-2"></i>Confirmar Envío
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="icono-confirmacion mb-3">
                        <i class="fas fa-truck fa-4x text-primary-dark"></i>
                    </div>
                    <h5 class="text-primary-dark mb-3">¿Confirmar envío del pedido <strong><?= $pedido['Codigo_Acceso'] ?></strong>?</h5>
                </div>
                
                <div class="alert alert-primary-light mb-4">
                    <div class="mb-2">
                        <strong class="text-primary-dark">Resumen del envío:</strong>
                    </div>
                    <div class="detalle-envio">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-primary-dark">Transportadora:</span>
                            <strong class="text-primary-dark" id="modalTransportadora"></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-primary-dark">Número de guía:</span>
                            <code id="modalNumeroGuia"></code>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-primary-dark">Fecha estimada:</span>
                            <strong class="text-primary-dark" id="modalFechaEstimada"></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-primary-dark">Notas esenciales:</span>
                            <span class="text-primary-dark">3 incluidas</span>
                        </div>
                    </div>
                </div>
                
                <!-- Checkbox de confirmación mejorado -->
                <div class="confirmacion-final mb-4 p-3 border rounded bg-light">
                    <div class="form-check m-0">
                        <input class="form-check-input" type="checkbox" id="confirmacionEnvioCheckbox" style="width: 20px; height: 20px;">
                        <label class="form-check-label ms-3 d-block" for="confirmacionEnvioCheckbox">
                            <div>
                                <strong class="text-primary-dark d-block mb-1">✓ Confirmo el envío del pedido</strong>
                                <small class="text-primary-dark d-block">He verificado que todos los datos del envío son correctos y deseo proceder con el marcado como "Enviado".</small>
                            </div>
                        </label>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">El botón de confirmación se habilitará cuando marques la casilla.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary-dark" id="btnModalConfirmarEnvio" disabled>
                    <i class="fas fa-check me-1"></i> Confirmar Envío
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Variables globales
let transportadoraSeleccionada = 'Servientrega';
let procesandoEnvio = false;

// FUNCIÓN PRINCIPAL QUE SIEMPRE FUNCIONA
function initEnvioRapido() {
    // Elementos principales
    const btnConfirmarEnvio = document.getElementById('btnConfirmarEnvio');
    const form = document.getElementById('formEnvioRapido');
    const modal = document.getElementById('modalConfirmacionEnvio');
    const btnModalConfirmar = document.getElementById('btnModalConfirmarEnvio');
    const checkboxConfirmacion = document.getElementById('confirmacionEnvioCheckbox');
    const fechaEstimadaInput = document.getElementById('fecha_estimada_entrega');
    
    // Si no existe el botón, salir
    if (!btnConfirmarEnvio) return;
    
    // 1. CONFIGURAR BOTÓN PRINCIPAL (SIMPLE Y DIRECTA)
    btnConfirmarEnvio.onclick = function(e) {
        e.preventDefault();
        
        // Evitar múltiples clics
        if (procesandoEnvio) return;
        
        // Validar formulario
        if (!validarFormulario()) {
            return;
        }
        
        // Actualizar información en el modal
        document.getElementById('modalTransportadora').textContent = transportadoraSeleccionada;
        document.getElementById('modalNumeroGuia').textContent = 
            document.getElementById('numero-guia-generado').textContent;
        
        // Mostrar fecha estimada en modal
        if (fechaEstimadaInput) {
            const fecha = new Date(fechaEstimadaInput.value);
            const fechaFormateada = fecha.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            document.getElementById('modalFechaEstimada').textContent = fechaFormateada;
        }
        
        // Resetear checkbox
        if (checkboxConfirmacion) {
            checkboxConfirmacion.checked = false;
        }
        
        // Deshabilitar botón del modal
        if (btnModalConfirmar) {
            btnModalConfirmar.disabled = true;
        }
        
        // Mostrar modal con Bootstrap
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    };
    
    // 2. CONFIGURAR TRANSPORTADORAS
    configurarTransportadoras();
    
    // 3. CONFIGURAR CHECKBOX DEL MODAL
    if (checkboxConfirmacion && btnModalConfirmar) {
        checkboxConfirmacion.onchange = function() {
            btnModalConfirmar.disabled = !this.checked;
        };
    }
    
    // 4. CONFIGURAR BOTÓN DE CONFIRMACIÓN DEL MODAL
    if (btnModalConfirmar) {
        btnModalConfirmar.onclick = function() {
            if (!checkboxConfirmacion || !checkboxConfirmacion.checked || procesandoEnvio) return;
            
            // Cambiar estado del botón para mostrar confirmación
            this.innerHTML = '<i class="fas fa-check me-1"></i> ¡Envío Confirmado!';
            this.classList.remove('btn-primary-dark');
            this.classList.add('btn-success');
            this.disabled = true;
            
            // Cerrar modal después de mostrar confirmación
            setTimeout(() => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) modalInstance.hide();
                
                // Procesar envío
                procesarEnvio();
            }, 1000);
        };
    }
    
    // 5. CONFIGURAR BOTÓN REGENERAR
    window.generarNuevoNumero = function() {
        const fecha = new Date().toISOString().slice(0,10).replace(/-/g, '');
        const idFactura = <?= $pedido['ID_Factura'] ?>;
        const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        const nuevoNumero = `TLL-${fecha}-${idFactura}-${random}`;
        
        document.getElementById('numero-guia-generado').textContent = nuevoNumero;
        mostrarMensaje('✓ Número de guía regenerado correctamente', 'success');
    };
    
    // 6. CONFIGURAR BOTONES DE FECHA SUGERIDA
    document.querySelectorAll('.fecha-sugerencia').forEach(button => {
        button.addEventListener('click', function() {
            const dias = parseInt(this.getAttribute('data-dias'));
            const nuevaFecha = new Date();
            nuevaFecha.setDate(nuevaFecha.getDate() + dias);
            
            // Formatear como YYYY-MM-DD
            const fechaFormateada = nuevaFecha.toISOString().split('T')[0];
            
            if (fechaEstimadaInput) {
                fechaEstimadaInput.value = fechaFormateada;
                mostrarMensaje(`✓ Fecha estimada establecida: ${dias} día(s)`, 'success');
            }
        });
    });
    
    // 7. AUTO-GENERAR NÚMERO DE GUÍA CADA 30 SEGUNDOS
    setInterval(() => {
        if (!document.hidden) {
            const fecha = new Date().toISOString().slice(0,10).replace(/-/g, '');
            const idFactura = <?= $pedido['ID_Factura'] ?>;
            const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
            const nuevoNumero = `TLL-${fecha}-${idFactura}-${random}`;
            
            document.getElementById('numero-guia-generado').textContent = nuevoNumero;
        }
    }, 30000);
}

// FUNCIÓN PARA CONFIGURAR TRANSPORTADORAS
function configurarTransportadoras() {
    const transportadoraPersonalizada = document.getElementById('TransportadoraPersonalizada');
    const radiosTransportadora = document.querySelectorAll('.transportadora-radio');
    
    // Manejar selección de transportadora
    document.querySelectorAll('.transportadora-card').forEach(card => {
        card.onclick = function() {
            const value = this.getAttribute('data-value');
            
            // Actualizar radios
            radiosTransportadora.forEach(radio => {
                radio.checked = (radio.value === value);
            });
            
            // Actualizar visual
            document.querySelectorAll('.transportadora-card').forEach(c => {
                c.classList.remove('selected');
            });
            this.classList.add('selected');
            
            // Actualizar variable global
            transportadoraSeleccionada = value;
            
            // Limpiar campo personalizado
            if (transportadoraPersonalizada) {
                transportadoraPersonalizada.value = '';
            }
        };
    });
    
    // Manejar campo personalizado
    if (transportadoraPersonalizada) {
        transportadoraPersonalizada.oninput = function() {
            const valor = this.value.trim();
            
            // Desmarcar radios
            radiosTransportadora.forEach(radio => {
                radio.checked = false;
            });
            
            // Quitar selección de tarjetas
            document.querySelectorAll('.transportadora-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Actualizar variable global
            transportadoraSeleccionada = valor;
        };
    }
    
    // Seleccionar primera transportadora por defecto
    const primeraTarjeta = document.querySelector('.transportadora-card');
    const primerRadio = document.querySelector('.transportadora-radio');
    if (primeraTarjeta && primerRadio) {
        primeraTarjeta.classList.add('selected');
        primerRadio.checked = true;
        transportadoraSeleccionada = primerRadio.value;
    }
}

// FUNCIÓN PARA VALIDAR FORMULARIO
function validarFormulario() {
    const transportadoraPersonalizada = document.getElementById('TransportadoraPersonalizada');
    const radiosTransportadora = document.querySelectorAll('.transportadora-radio');
    const errorElement = document.getElementById('transportadora-error');
    const fechaInput = document.getElementById('fecha_estimada_entrega');
    const fechaError = document.getElementById('fecha-error');
    
    // Verificar transportadora seleccionada
    let tieneTransportadora = false;
    
    // Buscar radio seleccionado
    radiosTransportadora.forEach(radio => {
        if (radio.checked) {
            tieneTransportadora = true;
            transportadoraSeleccionada = radio.value;
        }
    });
    
    // Si no hay radio, verificar campo personalizado
    if (!tieneTransportadora && transportadoraPersonalizada) {
        transportadoraSeleccionada = transportadoraPersonalizada.value.trim();
        tieneTransportadora = transportadoraSeleccionada !== '';
    }
    
    // Validar fecha estimada
    let fechaValida = false;
    if (fechaInput && fechaInput.value) {
        const fechaSeleccionada = new Date(fechaInput.value);
        const fechaHoy = new Date();
        fechaHoy.setHours(0, 0, 0, 0);
        
        fechaValida = fechaSeleccionada >= fechaHoy;
    }
    
    // Mostrar/ocultar errores
    if (errorElement) {
        if (!tieneTransportadora) {
            errorElement.classList.remove('d-none');
            mostrarMensaje('Por favor selecciona o ingresa una transportadora', 'danger');
        } else {
            errorElement.classList.add('d-none');
        }
    }
    
    if (fechaError) {
        if (!fechaInput.value || !fechaValida) {
            fechaError.classList.remove('d-none');
            mostrarMensaje('Por favor selecciona una fecha estimada de entrega válida (futura)', 'danger');
            if (fechaInput) fechaInput.focus();
        } else {
            fechaError.classList.add('d-none');
        }
    }
    
    return tieneTransportadora && fechaValida;
}

// FUNCIÓN PARA PROCESAR EL ENVÍO
function procesarEnvio() {
    if (procesandoEnvio) return;
    
    procesandoEnvio = true;
    
    const btnConfirmarEnvio = document.getElementById('btnConfirmarEnvio');
    const form = document.getElementById('formEnvioRapido');
    
    // Cambiar estado del botón principal
    if (btnConfirmarEnvio) {
        btnConfirmarEnvio.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Procesando envío...';
        btnConfirmarEnvio.disabled = true;
        btnConfirmarEnvio.classList.remove('btn-primary-dark');
        btnConfirmarEnvio.classList.add('btn-secondary');
    }
    
    // Mostrar mensaje
    mostrarMensaje('✓ Procesando envío del pedido <?= $pedido['Codigo_Acceso'] ?>...', 'success');
    
    // Preparar datos del formulario
    const transportadoraPersonalizada = document.getElementById('TransportadoraPersonalizada');
    
    // Crear campo oculto para transportadora
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'Transportadora';
    hiddenInput.value = transportadoraSeleccionada;
    form.appendChild(hiddenInput);
    
    // Combinar notas esenciales y adicionales
    const notasAdicionales = document.getElementById('Notas_Adicionales')?.value.trim() || '';
    const notasEsenciales = [
        'Se entregó a transportadora, tiempo estimado 3 días.',
        'Cliente notificado por correo electrónico.',
        'Seguro incluido en el envío.'
    ];
    
    let todasLasNotas = notasEsenciales;
    if (notasAdicionales) {
        todasLasNotas = [...notasEsenciales, notasAdicionales];
    }
    
    // Crear campo para todas las notas
    const notasInput = document.createElement('input');
    notasInput.type = 'hidden';
    notasInput.name = 'Notas_Envio';
    notasInput.value = todasLasNotas.join('\n');
    form.appendChild(notasInput);
    
    // Enviar formulario después de breve espera
    setTimeout(() => {
        form.submit();
    }, 1500);
}

// FUNCIÓN PARA MOSTRAR MENSAJES
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

// INICIALIZAR CUANDO EL DOCUMENTO ESTÉ LISTO
document.addEventListener('DOMContentLoaded', initEnvioRapido);

// TAMBIÉN INICIALIZAR CUANDO LA PÁGINA SE VUELVA A CARGAR (por si hay problemas)
window.addEventListener('load', initEnvioRapido);

// Y ADEMÁS, RE-CONFIGURAR SI SE HACE CLIC EN EL BOTÓN (por seguridad)
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'btnConfirmarEnvio') {
        // Solo si no está ya configurado
        if (!e.target.hasAttribute('data-inicializado')) {
            initEnvioRapido();
            e.target.setAttribute('data-inicializado', 'true');
        }
    }
});
</script>

<style>
/* Estilos para las tarjetas de transportadora */
.card-hover {
    transition: all 0.3s ease;
    cursor: pointer;
    border-radius: var(--border-radius);
    border: 2px solid var(--gray-medium);
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: var(--hover-shadow);
    border-color: var(--primary-light);
}

.transportadora-card.selected {
    background-color: rgba(27, 32, 45, 0.1) !important;
    border-color: var(--primary-dark) !important;
    border-width: 3px;
}

.transportadora-card.selected strong {
    color: var(--primary-dark) !important;
    font-weight: 700;
}

/* Imágenes de transportadora */
.transportadora-card img {
    width: 40px !important;
    height: 40px !important;
    object-fit: contain;
    border-radius: 4px;
    background-color: white;
    padding: 4px;
    border: 1px solid #dee2e6;
}

/* Notas esenciales */
.notas-esenciales .nota-item {
    padding: 8px 12px;
    background-color: rgba(27, 32, 45, 0.05);
    border-radius: 8px;
    border-left: 3px solid var(--success);
}

/* Checkbox de confirmación */
.confirmacion-final {
    border-color: var(--primary-light) !important;
}

.confirmacion-final .form-check-input {
    width: 20px;
    height: 20px;
    cursor: pointer;
}

.confirmacion-final .form-check-input:checked {
    background-color: var(--primary-dark);
    border-color: var(--primary-dark);
}

.confirmacion-final .form-check-input:focus {
    box-shadow: 0 0 0 0.25rem rgba(27, 32, 45, 0.25);
}

.confirmacion-final .form-check-label {
    cursor: pointer;
}

/* Estado del botón confirmado */
.btn-success {
    background-color: #198754 !important;
    border-color: #198754 !important;
    animation: pulse 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Estilos para el código del número de guía */
code {
    background-color: rgba(27, 32, 45, 0.1);
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    color: var(--primary-dark);
    border: 1px dashed var(--primary-light);
}

/* Botones deshabilitados */
.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Botones de fecha sugerida */
.fecha-sugerencia {
    font-size: 0.85rem;
    padding: 4px 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .row.g-2 .col-md-3 {
        flex: 0 0 50%;
        max-width: 50%;
    }
    
    .detalle-envio .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .detalle-envio .d-flex span:last-child {
        margin-top: 4px;
    }
    
    .transportadora-card img {
        width: 30px !important;
        height: 30px !important;
        margin-right: 4px !important;
    }
}

@media (max-width: 576px) {
    .row.g-2 .col-md-3 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .d-flex.justify-content-end.gap-2 {
        flex-direction: column;
    }
    
    .d-flex.justify-content-end.gap-2 .btn {
        width: 100%;
        margin-bottom: 8px;
    }
    
    .transportadora-card .d-flex {
        justify-content: flex-start !important;
    }
}
</style>

<!-- CSS -->
<link rel="stylesheet" href="assets/css/usuario.css">
<link rel="stylesheet" href="assets/css/pedido.css">

<!-- JS -->
<script src="assets/js/pedido.js"></script>