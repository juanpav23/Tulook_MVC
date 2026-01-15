<?php
$getEstadoBadge = $getEstadoBadge ?? 'getEstadoBadge';
?>

<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-bolt me-2"></i>Envío Rápido - Pedido #<?= $pedido['ID_Factura'] ?></h2>
        <div>
            <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
               class="btn btn-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> Volver al detalle
            </a>
        </div>
    </div>

    <!-- Resumen del pedido -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Resumen del Pedido</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>ID:</strong> #<?= $pedido['ID_Factura'] ?><br>
                    <strong>Código:</strong> <?= $pedido['Codigo_Acceso'] ?>
                </div>
                <div class="col-md-3">
                    <strong>Cliente:</strong><br>
                    <?= htmlspecialchars($pedido['Nombre'] . ' ' . $pedido['Apellido']) ?>
                </div>
                <div class="col-md-3">
                    <strong>Total:</strong><br>
                    <span class="text-success fw-bold">$<?= number_format($pedido['Monto_Total'], 2) ?></span>
                </div>
                <div class="col-md-3">
                    <strong>Estado actual:</strong><br>
                    <?= $getEstadoBadge($pedido['Estado']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario de envío rápido -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-rocket me-2"></i>Configuración de Envío</h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Pedido&a=marcarEnviado" method="post" id="formEnvioRapido">
                <input type="hidden" name="ID_Factura" value="<?= $pedido['ID_Factura'] ?>">
                <input type="hidden" name="generar_automatico" value="1">
                
                <!-- Número de guía generado -->
                <div class="alert alert-info mb-4">
                    <h6><i class="fas fa-barcode me-2"></i>Número de Guía Generado</h6>
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-0"><code id="numero-guia-generado"><?= $ejemploNumeroGuia ?></code></h4>
                            <small class="text-muted">Formato: TLL-FECHA-ID-RANDOM</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary" onclick="generarNuevoNumero()">
                            <i class="fas fa-redo me-1"></i> Regenerar
                        </button>
                    </div>
                </div>
                
                <!-- Transportadora -->
                <div class="mb-4">
                    <label for="Transportadora" class="form-label fw-bold">
                        <i class="fas fa-shipping-fast me-1"></i> Selecciona Transportadora
                    </label>
                    <div class="row">
                        <?php 
                        $transportadorasPopulares = [
                            'Servientrega' => 'primary',
                            'Interrapidisimo' => 'warning',
                            'DHL' => 'danger',
                            'Coordinadora' => 'info',
                            'Envía' => 'success',
                            'Deprisa' => 'dark',
                            'FedEx' => 'secondary',
                            'TCC' => 'primary'
                        ];
                        
                        foreach ($transportadorasPopulares as $nombre => $color):
                        ?>
                            <div class="col-md-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" 
                                           name="Transportadora" id="transp-<?= strtolower($nombre) ?>" 
                                           value="<?= $nombre ?>"
                                           <?= $nombre === 'Servientrega' ? 'checked' : '' ?>>
                                    <label class="form-check-label w-100" for="transp-<?= strtolower($nombre) ?>">
                                        <div class="card card-hover border-<?= $color ?>">
                                            <div class="card-body text-center py-2">
                                                <strong><?= $nombre ?></strong>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Transportadora personalizada -->
                    <div class="mt-3">
                        <label for="TransportadoraPersonalizada" class="form-label">
                            Otra transportadora:
                        </label>
                        <input type="text" class="form-control" id="TransportadoraPersonalizada" 
                               placeholder="Ingresa el nombre de otra transportadora">
                    </div>
                </div>
                
                <!-- Notas rápidas -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-sticky-note me-1"></i> Notas del Envío
                    </label>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-outline-secondary w-100" 
                                    onclick="agregarNota('Se entregó a transportadora, tiempo estimado 3 días')">
                                <small>Tiempo estimado 3 días</small>
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-outline-secondary w-100" 
                                    onclick="agregarNota('Cliente notificado por correo')">
                                <small>Cliente notificado</small>
                            </button>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button type="button" class="btn btn-outline-secondary w-100" 
                                    onclick="agregarNota('Seguro incluido')">
                                <small>Seguro incluido</small>
                            </button>
                        </div>
                    </div>
                    
                    <textarea class="form-control mt-2" id="Notas_Envio" name="Notas_Envio" 
                              rows="3" placeholder="Agrega notas adicionales..."></textarea>
                </div>
                
                <!-- Botones de acción -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="<?= BASE_URL ?>?c=Pedido&a=detalle&id=<?= $pedido['ID_Factura'] ?>" 
                       class="btn btn-secondary me-2">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Confirmar Envío
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generar número de guía inicial
    function generarNumeroGuia() {
        const fecha = new Date().toISOString().slice(0,10).replace(/-/g, '');
        const idFactura = <?= $pedido['ID_Factura'] ?>;
        const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
        return `TLL-${fecha}-${idFactura}-${random}`;
    }
    
    // Actualizar número de guía
    window.generarNuevoNumero = function() {
        document.getElementById('numero-guia-generado').textContent = generarNumeroGuia();
    }
    
    // Agregar nota predefinida
    window.agregarNota = function(nota) {
        const textarea = document.getElementById('Notas_Envio');
        const notasActuales = textarea.value.trim();
        textarea.value = notasActuales + (notasActuales ? '\n' : '') + nota;
    }
    
    // Manejar transportadora personalizada
    const transportadoraPersonalizada = document.getElementById('TransportadoraPersonalizada');
    const radiosTransportadora = document.querySelectorAll('input[name="Transportadora"]');
    
    transportadoraPersonalizada.addEventListener('input', function() {
        // Desmarcar todas las opciones cuando se escribe algo
        radiosTransportadora.forEach(radio => radio.checked = false);
    });
    
    // Cuando se selecciona una opción, limpiar el campo personalizado
    radiosTransportadora.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                transportadoraPersonalizada.value = '';
            }
        });
    });
    
    // Validar formulario
    document.getElementById('formEnvioRapido').addEventListener('submit', function(e) {
        // Obtener transportadora seleccionada
        let transportadoraSeleccionada = '';
        radiosTransportadora.forEach(radio => {
            if (radio.checked) {
                transportadoraSeleccionada = radio.value;
            }
        });
        
        // Si no hay opción seleccionada, usar el campo personalizado
        if (!transportadoraSeleccionada) {
            transportadoraSeleccionada = transportadoraPersonalizada.value.trim();
        }
        
        // Validar transportadora
        if (!transportadoraSeleccionada) {
            e.preventDefault();
            alert('Por favor selecciona o ingresa una transportadora');
            transportadoraPersonalizada.focus();
            return;
        }
        
        // Si usamos el campo personalizado, actualizar el campo oculto
        if (transportadoraSeleccionada === transportadoraPersonalizada.value.trim()) {
            // Crear un campo oculto con el valor
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'Transportadora';
            hiddenInput.value = transportadoraSeleccionada;
            this.appendChild(hiddenInput);
        }
        
        // Confirmación
        if (!confirm(`¿Confirmar envío del pedido #<?= $pedido['ID_Factura'] ?> con ${transportadoraSeleccionada}?`)) {
            e.preventDefault();
        }
    });
});
</script>

<style>
.card-hover {
    transition: all 0.3s ease;
    cursor: pointer;
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.form-check-input:checked + .card-hover {
    background-color: var(--bs-primary);
    color: white;
    border-color: var(--bs-primary);
}
</style>