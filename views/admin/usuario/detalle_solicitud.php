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

<!-- Botones de acción ARRIBA -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="text-primary-dark d-inline-block">
            <i class="fas fa-file-alt me-2"></i>
            <?= htmlspecialchars($solicitud['Nombre'] . ' ' . $solicitud['Apellido']) ?>
        </h2>
        <span class="badge ms-3 p-2 <?php 
            if ($solicitud['Estado'] == 'pendiente') {
                echo $expirada ? 'estado-inactivo' : 'bg-secondary text-dark';
            } elseif ($solicitud['Estado'] == 'procesada') {
                echo 'estado-activo';
            } else {
                echo 'estado-inactivo';
            }
        ?>" style="font-size: 0.9rem;">
            <i class="fas <?php 
                if ($solicitud['Estado'] == 'pendiente') {
                    echo $expirada ? 'fa-hourglass-end' : 'fa-clock';
                } elseif ($solicitud['Estado'] == 'procesada') {
                    echo 'fa-check-circle';
                } else {
                    echo 'fa-times-circle';
                }
            ?> me-1"></i>
            <?php 
                if ($solicitud['Estado'] == 'pendiente') {
                    echo $expirada ? 'EXPIRADA' : 'PENDIENTE';
                } elseif ($solicitud['Estado'] == 'procesada') {
                    echo 'APROBADA';
                } else {
                    echo 'RECHAZADA';
                }
            ?>
        </span>
    </div>
    <div>
        <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=solicitudesReactivacion&estado=pendiente" class="btn btn-outline-primary me-2">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
        <?php if ($solicitud['Estado'] == 'procesada' && $solicitud['Estado_Usuario'] == 1): ?>
            <a href="<?= BASE_URL ?>?c=UsuarioAdmin&a=index&buscar=<?= urlencode($solicitud['Correo']) ?>" class="btn btn-primary-dark">
                <i class="fas fa-user me-1"></i> Ver Usuario
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <!-- COLUMNA IZQUIERDA - Información del Usuario y Desactivación -->
    <div class="col-lg-6 mb-4">
        <!-- Tarjeta de Usuario (SIN AVATAR) -->
        <div class="card mb-4">
            <div class="card-header bg-primary-dark">
                <h5 class="mb-0 text-white"><i class="fas fa-id-card me-2"></i> Información del Usuario</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h4 class="text-primary-dark mb-1"><?= htmlspecialchars($solicitud['Nombre'] . ' ' . $solicitud['Apellido']) ?></h4>
                        <span class="badge <?= $solicitud['Estado_Usuario'] ? 'estado-activo' : 'estado-inactivo' ?>">
                            <i class="fas <?= $solicitud['Estado_Usuario'] ? 'fa-check-circle' : 'fa-pause-circle' ?> me-1"></i>
                            <?= $solicitud['Estado_Usuario'] ? 'Activo' : 'Desactivado' ?>
                        </span>
                    </div>
                </div>
                
                <div class="row mt-2">
                    <div class="col-md-6 mb-3">
                        <small class="text-primary-dark fw-bold d-block">Documento</small>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-id-card text-primary me-2"></i>
                            <?= htmlspecialchars($solicitud['Tipo_Documento'] ?? 'C.C') ?>: <?= htmlspecialchars($solicitud['N_Documento']) ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-primary-dark fw-bold d-block">Correo</small>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <?= htmlspecialchars($solicitud['Correo']) ?>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <small class="text-primary-dark fw-bold d-block">Celular</small>
                        <div class="p-2 bg-light rounded">
                            <i class="fas fa-phone-alt text-primary me-2"></i>
                            <?= htmlspecialchars($solicitud['Celular'] ?? 'No registrado') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Desactivación -->
        <div class="card mb-4">
            <div class="card-header bg-primary">
                <h5 class="mb-0 text-white"><i class="fas fa-ban me-2"></i> Motivo de Desactivación</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($solicitud['Motivo_Desactivacion'])): ?>
                    <div class="alert-primary-light p-3 mb-3">
                        <p class="mb-0" style="white-space: pre-line;"><?= nl2br(htmlspecialchars($solicitud['Motivo_Desactivacion'])) ?></p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-primary-dark fw-bold">Fecha de desactivación</small>
                            <p class="mb-0"><i class="fas fa-calendar-alt text-primary me-2"></i><?= $solicitud['Fecha_Desactivacion'] ? date('d/m/Y H:i', strtotime($solicitud['Fecha_Desactivacion'])) : 'No registrada' ?></p>
                        </div>
                        <div class="col-md-6">
                            <small class="text-primary-dark fw-bold">Desactivado por</small>
                            <p class="mb-0"><i class="fas fa-user-shield text-primary me-2"></i>
                                <?php if ($solicitud['Admin_Desactiva_Nombre']): ?>
                                    <?= htmlspecialchars($solicitud['Admin_Desactiva_Nombre'] . ' ' . $solicitud['Admin_Desactiva_Apellido']) ?>
                                <?php else: ?>
                                    Sistema
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center mb-0">No se registró un motivo de desactivación.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- COLUMNA DERECHA - Detalle de Solicitud -->
    <div class="col-lg-6 mb-4">
        <!-- Tarjeta de Solicitud -->
        <div class="card mb-4">
            <div class="card-header bg-primary-dark">
                <h5 class="mb-0 text-white"><i class="fas fa-file-signature me-2"></i> Detalles de la Solicitud</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="border-start border-4 border-secondary ps-3">
                            <small class="text-primary-dark fw-bold">SOLICITADA</small>
                            <p class="h5 mb-0 text-primary-dark"><?= date('d/m/Y', strtotime($solicitud['Fecha_Solicitud'])) ?></p>
                            <small class="text-muted"><?= date('H:i:s', strtotime($solicitud['Fecha_Solicitud'])) ?></small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border-start border-4 border-secondary ps-3">
                            <small class="text-primary-dark fw-bold">VENCE</small>
                            <p class="h5 mb-0 text-primary-dark"><?= date('d/m/Y', strtotime($solicitud['Fecha_Expiracion'] ?? $solicitud['Fecha_Solicitud'])) ?></p>
                            <small class="text-muted"><?= date('H:i', strtotime($solicitud['Fecha_Expiracion'] ?? $solicitud['Fecha_Solicitud'])) ?></small>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-bold text-primary-dark d-block mb-2">
                        <i class="fas fa-comment-dots me-1"></i> MOTIVO DE REACTIVACIÓN
                    </label>
                    <div class="bg-light p-3 rounded" style="border-left: 4px solid var(--primary);">
                        <?php if (!empty($solicitud['Motivo_Reactivacion'])): ?>
                            <p class="mb-0" style="white-space: pre-line;"><?= nl2br(htmlspecialchars($solicitud['Motivo_Reactivacion'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted fst-italic mb-0">El usuario no especificó un motivo</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Solicitudes (simplificado) -->
        <?php if (!empty($historial)): ?>
        <div class="card mb-4">
            <div class="card-header bg-primary-light">
                <h5 class="mb-0 text-white"><i class="fas fa-history me-2"></i> Historial de Solicitudes</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($historial, 0, 3) as $index => $hist): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center hover-shadow-detalle">
                            <div>
                                <span class="badge <?= match($hist['Estado']) {
                                    'pendiente' => 'bg-secondary text-dark',
                                    'procesada' => 'estado-activo',
                                    'rechazada' => 'estado-inactivo',
                                    default => 'bg-secondary'
                                } ?>">
                                    <?= ucfirst($hist['Estado']) ?>
                                </span>
                            </div>
                            <small class="text-muted">
                                <?= date('d/m/Y', strtotime($hist['Fecha_Solicitud'])) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Resolución si ya fue procesada (CON COLORES DEL SISTEMA) -->
        <?php if ($solicitud['Estado'] != 'pendiente'): ?>
        <div class="card mb-4">
            <div class="card-header <?= $solicitud['Estado'] == 'procesada' ? 'bg-primary' : 'bg-primary-dark' ?>">
                <h5 class="mb-0 text-white">
                    <i class="fas <?= $solicitud['Estado'] == 'procesada' ? 'fa-check-circle' : 'fa-times-circle' ?> me-2"></i>
                    Resolución de la Solicitud
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <div class="bg-primary-dark text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                    </div>
                    <div>
                        <p class="mb-0 fw-bold text-primary-dark"><?= htmlspecialchars($solicitud['Admin_Procesa_Nombre'] . ' ' . $solicitud['Admin_Procesa_Apellido']) ?></p>
                        <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?= date('d/m/Y H:i', strtotime($solicitud['Fecha_Procesamiento'])) ?></small>
                    </div>
                </div>
                <?php if (!empty($solicitud['Comentario_Admin'])): ?>
                    <div class="mt-3 p-3 bg-light rounded">
                        <strong class="text-primary-dark">Comentario:</strong>
                        <p class="mb-0 mt-1" style="white-space: pre-line;"><?= nl2br(htmlspecialchars($solicitud['Comentario_Admin'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ZONA DE ACCIONES - Solo para pendientes no expiradas -->
<?php if ($solicitud['Estado'] == 'pendiente' && !$expirada): ?>
    <div class="card mt-3">
        <div class="card-header bg-primary-dark">
            <h5 class="mb-0 text-white"><i class="fas fa-gavel me-2"></i> Decidir Solicitud</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <div class="decision-card border rounded h-100">
                        <!-- Header con color distintivo -->
                        <div class="decision-card-header bg-primary text-white p-4 text-center rounded-top">
                            <div class="decision-icon mx-auto mb-3">
                                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; margin: 0 auto;">
                                    <i class="fas fa-check-circle fa-3x"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold mb-0 text-white">APROBAR</h3>
                            <p class="mb-0 opacity-75">Reactivar cuenta inmediatamente</p>
                        </div>
                        
                        <!-- Cuerpo de la tarjeta -->
                        <div class="decision-card-body p-4">
                            <!-- Información de la acción -->
                            <div class="alert alert-success bg-light border-0 mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-lg"></i>
                                    </div>
                                    <div class="text-start">
                                        <strong class="d-block text">¿Qué sucederá?</strong>
                                        <span class="small">El usuario será reactivado inmediatamente y podrá iniciar sesión nuevamente.</span>
                                    </div>
                                </div>
                            </div>
                            
                            <form action="<?= BASE_URL ?>?c=UsuarioAdmin&a=procesarSolicitudReactivacion" method="POST" class="decision-form" id="formAprobar">
                                <input type="hidden" name="id_solicitud" value="<?= $solicitud['ID_Solicitud'] ?>">
                                <input type="hidden" name="accion" value="aprobar">
                                <input type="hidden" name="redirigir_detalle" value="1">
                                
                                <!-- Campo de comentario (opcional) -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary">
                                        <i class="fas fa-comment me-1"></i> Comentario (opcional)
                                    </label>
                                    <textarea name="comentario_admin" class="form-control border-success" rows="3" placeholder="Agrega un comentario sobre esta aprobación..."></textarea>
                                    <small class="text-muted">Este comentario será visible para el usuario.</small>
                                </div>
                                
                                <!-- CASILLAS DE VERIFICACIÓN PARA APROBAR (2 CONFIRMACIONES) -->
                                <div class="verification-section bg-light p-3 rounded mb-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-shield-alt me-2"></i>CONFIRMACIÓN DE APROBACIÓN (2 PASOS)
                                    </h6>
                                    
                                    <div class="form-check mb-3 p-2 border rounded bg-white">
                                        <input class="form-check-input confirmacion-aprobar" type="checkbox" id="confirmarAprobar1" style="width: 20px; height: 20px;">
                                        <label class="form-check-label ms-2" for="confirmarAprobar1">
                                            <strong>PRIMERA CONFIRMACIÓN:</strong> Confirmo que deseo <span class="text-primary fw-bold">APROBAR</span> esta solicitud y <span class="fw-bold">REACTIVAR</span> al usuario.
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-3 p-2 border rounded bg-white">
                                        <input class="form-check-input confirmacion-aprobar" type="checkbox" id="confirmarAprobar2" style="width: 20px; height: 20px;">
                                        <label class="form-check-label ms-2" for="confirmarAprobar2">
                                            <strong>SEGUNDA CONFIRMACIÓN:</strong> Verifico que el motivo de reactivación es válido y el usuario cumple con las políticas.
                                        </label>
                                    </div>
                                    
                                    <!-- Barra de progreso de verificación -->
                                    <div class="progress mt-3" style="height: 8px;">
                                        <div class="progress-bar bg-success" id="progresoAprobar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="textoProgresoAprobar">
                                        <i class="fas fa-info-circle me-1"></i> 0 de 2 confirmaciones completadas
                                    </small>
                                </div>
                                
                                <!-- Botón de confirmación -->
                                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" id="btnConfirmarAprobar" disabled>
                                    <i class="fas fa-check-circle me-2 fa-lg"></i>
                                    CONFIRMAR APROBACIÓN Y REACTIVAR
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Botón Rechazar -->
                <div class="col-md-6">
                    <div class="decision-card border rounded h-100">
                        <!-- Header con color distintivo -->
                        <div class="decision-card-header bg-primary text-white p-4 text-center rounded-top">
                            <div class="decision-icon mx-auto mb-3">
                                <div class="bg-white text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; margin: 0 auto;">
                                    <i class="fas fa-times-circle fa-3x"></i>
                                </div>
                            </div>
                            <h3 class="fw-bold mb-0 text-white">RECHAZAR</h3>
                            <p class="mb-0 opacity-75">Cuenta permanecerá desactivada</p>
                        </div>
                        
                        <!-- Cuerpo de la tarjeta -->
                        <div class="decision-card-body p-4">
                            <!-- Información de la acción -->
                            <div class="alert alert-primary bg-light border-0 mb-4">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                                    </div>
                                    <div class="text-start">
                                        <strong class="d-block">Importante:</strong>
                                        <span class="small">El motivo de rechazo es OBLIGATORIO y será visible para el usuario. Mínimo 20 caracteres.</span>
                                    </div>
                                </div>
                            </div>
                            
                            <form action="<?= BASE_URL ?>?c=UsuarioAdmin&a=procesarSolicitudReactivacion" method="POST" class="decision-form" id="formRechazar">
                                <input type="hidden" name="id_solicitud" value="<?= $solicitud['ID_Solicitud'] ?>">
                                <input type="hidden" name="accion" value="rechazar">
                                <input type="hidden" name="redirigir_detalle" value="1">
                                
                                <!-- Campo de motivo (OBLIGATORIO) con contador de caracteres -->
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-primary">
                                        <i class="fas fa-comment-dots me-1"></i> * MOTIVO DE RECHAZO
                                        <span class="float-end">
                                            <span id="contadorRechazo">0</span> / 500
                                        </span>
                                    </label>
                                    <textarea name="comentario_admin" class="form-control border-danger" id="motivoRechazo" rows="4" placeholder="Describe detalladamente el motivo por el cual rechazas esta solicitud..." required></textarea>
                                    <div class="progress mt-2" style="height: 5px;">
                                        <div id="progresoRechazo" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <small class="text-muted d-block mt-1">
                                        <i class="fas fa-info-circle me-1"></i> Mínimo 20 caracteres • Máximo 500 caracteres
                                    </small>
                                    <small class="d-block mt-1" id="errorMotivoRechazo" style="display: none;">
                                        El motivo debe tener al menos 20 caracteres
                                    </small>
                                </div>
                                
                                <!-- CASILLAS DE VERIFICACIÓN PARA RECHAZAR (2 CONFIRMACIONES) -->
                                <div class="verification-section bg-light p-3 rounded mb-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-shield-alt me-2"></i>CONFIRMACIÓN DE RECHAZO (2 PASOS)
                                    </h6>
                                    
                                    <div class="form-check mb-3 p-2 border rounded bg-white">
                                        <input class="form-check-input confirmacion-rechazar" type="checkbox" id="confirmarRechazar1" style="width: 20px; height: 20px;" disabled>
                                        <label class="form-check-label ms-2" for="confirmarRechazar1">
                                            <strong>PRIMERA CONFIRMACIÓN:</strong> Confirmo que deseo <span class="text-primary fw-bold">RECHAZAR</span> esta solicitud de reactivación.
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-3 p-2 border rounded bg-white">
                                        <input class="form-check-input confirmacion-rechazar" type="checkbox" id="confirmarRechazar2" style="width: 20px; height: 20px;" disabled>
                                        <label class="form-check-label ms-2" for="confirmarRechazar2">
                                            <strong>SEGUNDA CONFIRMACIÓN:</strong> El motivo proporcionado es claro, específico y cumple con la longitud mínima requerida.
                                        </label>
                                    </div>
                                    
                                    <!-- Barra de progreso de verificación -->
                                    <div class="progress mt-3" style="height: 8px;">
                                        <div class="progress-bar bg-danger" id="progresoRechazar" role="progressbar" style="width: 0%;"></div>
                                    </div>
                                    <small class="text-muted d-block mt-2" id="textoProgresoRechazar">
                                        <i class="fas fa-info-circle me-1"></i> Complete el motivo y las confirmaciones
                                    </small>
                                </div>
                                
                                <!-- Botón de confirmación -->
                                <button type="submit" class="btn btn-danger w-100 py-3 fw-bold" id="btnConfirmarRechazar" disabled>
                                    <i class="fas fa-times-circle me-2 fa-lg"></i>
                                    CONFIRMAR RECHAZO DE SOLICITUD
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT MEJORADO PARA VALIDACIÓN EN TIEMPO REAL -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        'use strict';
        
        // ============================================
        // CONFIGURACIÓN PARA APROBAR SOLICITUD
        // ============================================
        const formAprobar = document.getElementById('formAprobar');
        if (formAprobar) {
            const checkboxesAprobar = document.querySelectorAll('.confirmacion-aprobar');
            const btnConfirmarAprobar = document.getElementById('btnConfirmarAprobar');
            const progresoBarAprobar = document.getElementById('progresoAprobar');
            const textoProgresoAprobar = document.getElementById('textoProgresoAprobar');
            
            function verificarConfirmacionesAprobar() {
                const totalCasillas = checkboxesAprobar.length;
                const casillasMarcadas = Array.from(checkboxesAprobar).filter(c => c.checked).length;
                const todasMarcadas = casillasMarcadas === totalCasillas;
                
                // Actualizar barra de progreso
                const porcentaje = (casillasMarcadas / totalCasillas) * 100;
                progresoBarAprobar.style.width = `${porcentaje}%`;
                
                // Cambiar color según progreso
                if (casillasMarcadas === 0) {
                    progresoBarAprobar.classList.remove('bg-warning', 'bg-success');
                    progresoBarAprobar.classList.add('bg-success');
                } else if (casillasMarcadas === 1) {
                    progresoBarAprobar.classList.remove('bg-success', 'bg-warning');
                    progresoBarAprobar.classList.add('bg-warning');
                } else {
                    progresoBarAprobar.classList.remove('bg-success', 'bg-warning');
                    progresoBarAprobar.classList.add('bg-success');
                }
                
                // Actualizar texto de progreso
                textoProgresoAprobar.innerHTML = `<i class="fas fa-info-circle me-1"></i> ${casillasMarcadas} de ${totalCasillas} confirmaciones completadas`;
                
                // Habilitar/deshabilitar botón
                btnConfirmarAprobar.disabled = !todasMarcadas;
                
                if (todasMarcadas) {
                    btnConfirmarAprobar.classList.remove('btn-secondary');
                    btnConfirmarAprobar.classList.add('btn-success');
                    btnConfirmarAprobar.innerHTML = '<i class="fas fa-check-circle me-2 fa-lg"></i> CONFIRMAR APROBACIÓN Y REACTIVAR';
                } else {
                    btnConfirmarAprobar.classList.remove('btn-success');
                    btnConfirmarAprobar.classList.add('btn-secondary');
                }
            }
            
            // Agregar eventos a los checkboxes
            checkboxesAprobar.forEach(checkbox => {
                checkbox.addEventListener('change', verificarConfirmacionesAprobar);
            });
            
            // Inicializar
            verificarConfirmacionesAprobar();
        }
        
        // ============================================
        // CONFIGURACIÓN PARA RECHAZAR SOLICITUD
        // ============================================
        const motivoRechazo = document.getElementById('motivoRechazo');
        const checkboxesRechazar = document.querySelectorAll('.confirmacion-rechazar');
        const btnConfirmarRechazar = document.getElementById('btnConfirmarRechazar');
        const contadorRechazo = document.getElementById('contadorRechazo');
        const progresoRechazo = document.getElementById('progresoRechazo');
        const errorMotivoRechazo = document.getElementById('errorMotivoRechazo');
        const progresoBarRechazar = document.getElementById('progresoRechazar');
        const textoProgresoRechazar = document.getElementById('textoProgresoRechazar');
        
        function validarMotivoRechazo() {
            if (!motivoRechazo) return false;
            
            const texto = motivoRechazo.value.trim();
            const longitud = texto.length;
            
            // Actualizar contador
            contadorRechazo.textContent = longitud;
            
            // Actualizar barra de progreso
            const porcentajeTexto = Math.min((longitud / 500) * 100, 100);
            progresoRechazo.style.width = `${porcentajeTexto}%`;
            
            // Cambiar color según longitud
            if (longitud < 20) {
                progresoRechazo.classList.remove('bg-warning', 'bg-success');
                progresoRechazo.classList.add('bg-danger');
                errorMotivoRechazo.style.display = 'block';
                return false;
            } else if (longitud >= 20 && longitud <= 450) {
                progresoRechazo.classList.remove('bg-danger', 'bg-success');
                progresoRechazo.classList.add('bg-warning');
                errorMotivoRechazo.style.display = 'none';
            } else {
                progresoRechazo.classList.remove('bg-danger', 'bg-warning');
                progresoRechazo.classList.add('bg-success');
                errorMotivoRechazo.style.display = 'none';
            }
            
            return longitud >= 20;
        }
        
        function habilitarCheckboxesRechazo(habilitar) {
            checkboxesRechazar.forEach(checkbox => {
                checkbox.disabled = !habilitar;
                if (!habilitar) {
                    checkbox.checked = false;
                }
            });
        }
        
        function verificarConfirmacionesRechazar() {
            const motivoValido = validarMotivoRechazo();
            
            // Habilitar/deshabilitar checkboxes según validez del motivo
            habilitarCheckboxesRechazo(motivoValido);
            
            const totalCasillas = checkboxesRechazar.length;
            const casillasMarcadas = Array.from(checkboxesRechazar).filter(c => c.checked).length;
            const todasMarcadas = casillasMarcadas === totalCasillas && motivoValido;
            
            // Actualizar barra de progreso principal
            let porcentajeTotal = 0;
            if (motivoValido) {
                porcentajeTotal = (casillasMarcadas / totalCasillas) * 100;
            }
            progresoBarRechazar.style.width = `${porcentajeTotal}%`;
            
            // Cambiar color según progreso
            if (porcentajeTotal === 0) {
                progresoBarRechazar.classList.remove('bg-warning', 'bg-success');
                progresoBarRechazar.classList.add('bg-danger');
            } else if (porcentajeTotal < 100) {
                progresoBarRechazar.classList.remove('bg-danger', 'bg-success');
                progresoBarRechazar.classList.add('bg-warning');
            } else {
                progresoBarRechazar.classList.remove('bg-danger', 'bg-warning');
                progresoBarRechazar.classList.add('bg-success');
            }
            
            // Actualizar texto de progreso
            if (!motivoValido) {
                textoProgresoRechazar.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> Complete el motivo (mínimo 20 caracteres)`;
            } else {
                textoProgresoRechazar.innerHTML = `<i class="fas fa-info-circle me-1"></i> ${casillasMarcadas} de ${totalCasillas} confirmaciones completadas`;
            }
            
            // Habilitar/deshabilitar botón
            btnConfirmarRechazar.disabled = !todasMarcadas;
            
            if (todasMarcadas) {
                btnConfirmarRechazar.classList.remove('btn-secondary');
                btnConfirmarRechazar.classList.add('btn-danger');
                btnConfirmarRechazar.innerHTML = '<i class="fas fa-times-circle me-2 fa-lg"></i> CONFIRMAR RECHAZO DE SOLICITUD';
            } else {
                btnConfirmarRechazar.classList.remove('btn-danger');
                btnConfirmarRechazar.classList.add('btn-secondary');
            }
        }
        
        // Agregar eventos para rechazo
        if (motivoRechazo) {
            motivoRechazo.addEventListener('input', verificarConfirmacionesRechazar);
            motivoRechazo.addEventListener('blur', verificarConfirmacionesRechazar);
        }
        
        // Agregar eventos a los checkboxes de rechazo
        checkboxesRechazar.forEach(checkbox => {
            checkbox.addEventListener('change', verificarConfirmacionesRechazar);
        });
        
        // Inicializar validación de rechazo
        verificarConfirmacionesRechazar();
        
        // Función para auto-expandir textarea
        window.autoExpandTextarea = function(textarea) {
            if (textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
            }
        };
        
        // Auto-expandir textarea de motivo de rechazo
        if (motivoRechazo) {
            motivoRechazo.addEventListener('input', function() {
                autoExpandTextarea(this);
            });
            autoExpandTextarea(motivoRechazo);
        }
    });
    </script>

<?php elseif ($solicitud['Estado'] == 'pendiente' && $expirada): ?>
    <div class="alert alert-primary-light mt-4">
        <div class="d-flex">
            <div class="me-3">
                <i class="fas fa-exclamation-triangle fa-2x text-primary"></i>
            </div>
            <div>
                <h5 class="alert-heading fw-bold text-primary-dark">Solicitud Expirada</h5>
                <p class="mb-0 text-primary">Esta solicitud ha superado las 24 horas de vigencia y ya no puede ser procesada.</p>
                <p class="mb-0 mt-2 text-primary">El usuario debe generar una nueva solicitud de reactivación.</p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- CSS Adicional para las tarjetas de decisión -->
<style>
.decision-card {
    transition: all 0.3s ease;
    border: none !important;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    overflow: hidden;
}

.decision-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.decision-card-header {
    position: relative;
    overflow: hidden;
}

.decision-card-header::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
    pointer-events: none;
}

.decision-icon {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

.decision-card-body {
    background: white;
}

.verification-section {
    border-left: 4px solid;
    transition: all 0.3s ease;
}

.verification-section:hover {
    background: #f8f9fa !important;
}

.form-check {
    transition: all 0.2s ease;
}

.form-check:hover {
    background: #f8f9fa !important;
    border-color: #dee2e6 !important;
}

.progress {
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar {
    transition: width 0.3s ease;
}

.btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn:active {
    transform: translateY(0);
}

/* Estilos para los badges de estado */
.estado-activo {
    background-color: #28a745 !important;
    color: white !important;
}

.estado-inactivo {
    background-color: #dc3545 !important;
    color: white !important;
}

/* Animación para mensajes */
.alert-message {
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .decision-card-header {
        padding: 1.5rem !important;
    }
    
    .decision-icon div {
        width: 60px !important;
        height: 60px !important;
    }
    
    .decision-icon i {
        font-size: 1.8rem !important;
    }
}
</style>

<!-- CSS y JS con BASE_URL -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/usuario.css">
<script src="<?= BASE_URL ?>assets/js/usuario.js"></script>

<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true
        });
    });
});
</script>