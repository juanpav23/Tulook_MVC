// usuario.js - Gestión completa de usuarios - NUEVO SISTEMA DE CAMBIO DE ROL Y MOTIVOS DE DESACTIVACIÓN
class UsuarioManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupFormValidation();
        this.setupConfirmations();
        this.autoHideMessages();
        this.setupPasswordToggle();
        this.setupCleanForm();
        this.setupValidacionDuplicados();
        this.setupCambioRol();
        this.setupDesactivacionConMotivo();
    }

    setupDesactivacionConMotivo() {
        // Configurar modal para solicitar motivo de desactivación
        document.querySelectorAll('.cambiar-estado-btn[data-accion="desactivar"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleDesactivarConMotivo(e, btn));
        });
    }

    handleDesactivarConMotivo(event, btn) {
        event.preventDefault();
        
        const id = btn.getAttribute('data-id');
        const nombre = btn.getAttribute('data-nombre');
        const url = btn.getAttribute('href');
        
        this.showMotivoModal(id, nombre, url);
    }

    showMotivoModal(id, nombre, url) {
        event.preventDefault();
        
        const modalId = 'motivoDesactivacionModal';
        
        // Eliminar modal existente
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();
        
        const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-primary-dark text-white">
                        <div class="d-flex align-items-center w-100">
                            <div class="modal-icon" style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; margin-right: 15px;">
                                <i class="fas fa-user-slash fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0">Desactivar Usuario</h5>
                                <small class="text-light">Máximo 500 caracteres</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-primary-dark text-white mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-triangle me-3"></i>
                                <div>
                                    <h6 class="mb-1">Estás a punto de desactivar al usuario:</h6>
                                    <h5 class="text-primary-dark mb-0">${nombre}</h5>
                                    <small class="text-muted">ID: ${id}</small>
                                </div>
                            </div>
                        </div>
                        
                        <form id="formMotivoDesactivacion">
                            <div class="mb-3">
                                <label for="motivoDesactivacion" class="form-label">
                                    <strong>Motivo de desactivación *</strong>
                                    <span class="text-muted float-end">
                                        <span id="contadorCaracteres">0</span> / 500 caracteres
                                    </span>
                                </label>
                                <textarea class="form-control" id="motivoDesactivacion" name="motivo" 
                                        rows="6" required 
                                        maxlength="500"
                                        placeholder="Describe detalladamente el motivo por el cual desactivas este usuario. Ejemplos:
    • El usuario ha infringido las políticas de la plataforma
    • Comportamiento inapropiado en los comentarios
    • Uso inadecuado del sistema
    • Solicitud del mismo usuario
    • Cuenta inactiva por más de 1 año"
                                        oninput="autoExpandTextarea(this)"></textarea>
                                
                                <div class="progress mt-2" style="height: 5px;">
                                    <div id="progresoMotivo" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                                </div>
                                
                                <div class="row mt-2">
                                    <div class="col-6">
                                        <small class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Mínimo: 20 caracteres • Máximo: 500 caracteres
                                        </small>
                                    </div>
                                    <div class="col-6 text-end">
                                        <small class="form-text" id="estadoCaracteres">
                                            <span class="text-success">✓ Caracteres suficientes</span>
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="alert alert-primary-light mt-3 mb-0">
                                    <div class="row">
                                        <div class="col-1 text-center">
                                            <i class="fas fa-lightbulb text-primary"></i>
                                        </div>
                                        <div class="col-11">
                                            <small class="text-dark">
                                                <strong>Recomendaciones:</strong><br>
                                                • Sé claro y específico<br>
                                                • Usa puntos si es necesario<br>
                                                • Incluye fechas o referencias si aplica<br>
                                                • Este motivo será visible para el usuario al intentar iniciar sesión
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary-dark" id="btnConfirmarDesactivacion" disabled>
                            <i class="fas fa-user-slash me-1"></i> Desactivar Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        modal.show();
        
        // Función para auto-expandir textarea
        window.autoExpandTextarea = function(textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = (textarea.scrollHeight) + 'px';
        };
        
        const form = modalElement.querySelector('#formMotivoDesactivacion');
        const btnConfirmar = modalElement.querySelector('#btnConfirmarDesactivacion');
        const textarea = modalElement.querySelector('#motivoDesactivacion');
        const contador = modalElement.querySelector('#contadorCaracteres');
        const progresoBar = modalElement.querySelector('#progresoMotivo');
        const estadoCaracteres = modalElement.querySelector('#estadoCaracteres');
        
        // Inicializar textarea
        autoExpandTextarea(textarea);
        
        // Validar formulario en tiempo real
        const validarFormulario = () => {
            const texto = textarea.value.trim();
            const longitud = texto.length;
            
            // Actualizar contador
            contador.textContent = longitud;
            
            // Actualizar barra de progreso
            const porcentaje = Math.min((longitud / 500) * 100, 100);
            progresoBar.style.width = `${porcentaje}%`;
            
            // Cambiar color de la barra según la longitud
            if (longitud < 20) {
                progresoBar.classList.remove('bg-warning', 'bg-success', 'bg-danger');
                progresoBar.classList.add('bg-danger');
            } else if (longitud >= 20 && longitud <= 450) {
                progresoBar.classList.remove('bg-danger', 'bg-success');
                progresoBar.classList.add('bg-warning');
            } else {
                progresoBar.classList.remove('bg-danger', 'bg-warning');
                progresoBar.classList.add('bg-success');
            }
            
            // Actualizar estado de caracteres
            if (longitud < 20) {
                estadoCaracteres.innerHTML = `<span class="text-danger">✗ Mínimo 20 caracteres</span>`;
                btnConfirmar.disabled = true;
                btnConfirmar.classList.remove('btn-primary-dark');
                btnConfirmar.classList.add('btn-secondary');
            } else if (longitud > 500) {
                estadoCaracteres.innerHTML = `<span class="text-danger">✗ Máximo 500 caracteres</span>`;
                btnConfirmar.disabled = true;
                btnConfirmar.classList.remove('btn-primary-dark');
                btnConfirmar.classList.add('btn-secondary');
            } else {
                estadoCaracteres.innerHTML = `<span class="text-success">✓ ${500 - longitud} caracteres disponibles</span>`;
                btnConfirmar.disabled = false;
                btnConfirmar.classList.remove('btn-secondary');
                btnConfirmar.classList.add('btn-primary-dark');
            }
        };
        
        // Event listeners
        textarea.addEventListener('input', validarFormulario);
        textarea.addEventListener('keydown', (e) => {
            // Permitir Enter pero evitar múltiples líneas excesivas
            if (e.key === 'Enter') {
                const lines = textarea.value.split('\n').length;
                if (lines > 10) {
                    e.preventDefault();
                    this.showToast('Máximo 10 líneas permitidas', 'warning');
                }
            }
        });
        
        // Validar inicialmente
        validarFormulario();
        
        // Configurar confirmación
        btnConfirmar.addEventListener('click', () => {
            const motivo = textarea.value.trim();
            
            // Validaciones finales
            if (motivo.length < 20) {
                this.showToast('El motivo debe tener al menos 20 caracteres', 'warning');
                textarea.focus();
                return;
            }
            
            if (motivo.length > 500) {
                this.showToast('El motivo no puede exceder los 500 caracteres', 'warning');
                textarea.focus();
                return;
            }
            
            // Verificar que no sea solo espacios o caracteres repetidos
            const motivoSinEspacios = motivo.replace(/\s+/g, '');
            if (motivoSinEspacios.length < 10) {
                this.showToast('El motivo es demasiado corto. Por favor, proporciona más detalles.', 'warning');
                textarea.focus();
                return;
            }
            
            // Verificar que tenga contenido real (no solo caracteres especiales)
            const contenidoReal = motivo.replace(/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]/g, '');
            if (contenidoReal.length < 15) {
                this.showToast('El motivo debe contener texto significativo', 'warning');
                textarea.focus();
                return;
            }
            
            // Crear formulario dinámico y enviarlo
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.style.display = 'none';
            
            // Limpiar y formatear el motivo antes de enviar
            const motivoFormateado = this.formatearMotivo(motivo);
            
            // Agregar campo motivo
            const motivoInput = document.createElement('input');
            motivoInput.type = 'hidden';
            motivoInput.name = 'motivo';
            motivoInput.value = motivoFormateado;
            form.appendChild(motivoInput);
            
            // Agregar campo de longitud para validación en backend
            const longitudInput = document.createElement('input');
            longitudInput.type = 'hidden';
            longitudInput.name = 'longitud_motivo';
            longitudInput.value = motivoFormateado.length;
            form.appendChild(longitudInput);
            
            document.body.appendChild(form);
            
            modal.hide();
            setTimeout(() => {
                form.submit();
            }, 300);
        });
        
        // Limpiar al cerrar
        modalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                modalElement.remove();
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }, 300);
        });
    }

    // Nueva función para formatear el motivo
    formatearMotivo(texto) {
        // 1. Limpiar espacios extras
        let formateado = texto.trim();
        
        // 2. Reemplazar múltiples espacios por uno solo
        formateado = formateado.replace(/\s+/g, ' ');
        
        // 3. Reemplazar múltiples saltos de línea por máximo 2
        formateado = formateado.replace(/\n{3,}/g, '\n\n');
        
        // 4. Capitalizar primera letra de cada oración
        formateado = formateado.replace(/(^\w|\.\s+\w)/g, match => match.toUpperCase());
        
        // 5. Limitar a máximo 500 caracteres
        if (formateado.length > 500) {
            formateado = formateado.substring(0, 497) + '...';
        }
        
        return formateado;
    }

    setupEventListeners() {
        // Botones de cambiar estado (index.php) - solo activaciones
        document.querySelectorAll('.cambiar-estado-btn[data-accion="activar"]').forEach(btn => {
            btn.addEventListener('click', (e) => this.handleCambiarEstado(e, btn));
        });
    }

    setupCambioRol() {
        // Configurar botones para abrir modal de cambio de rol
        document.querySelectorAll('.cambiar-rol-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => this.abrirModalCambioRol(e, btn));
        });

        // Configurar botones de selección de rol en el modal
        document.querySelectorAll('.rol-option-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.seleccionarRol(e, btn));
        });

        // Configurar botón de confirmación
        const confirmBtn = document.getElementById('confirmarCambioRolBtn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', () => this.confirmarCambioRol());
        }
    }

    abrirModalCambioRol(event, boton) {
        event.preventDefault();
        
        // Obtener datos del usuario
        const usuarioId = boton.getAttribute('data-usuario-id');
        const usuarioNombre = boton.getAttribute('data-usuario-nombre');
        const rolActual = parseInt(boton.getAttribute('data-rol-actual'));
        const rolActualTexto = boton.getAttribute('data-rol-actual-texto');
        
        // Actualizar datos en el modal
        document.getElementById('modalUsuarioNombre').textContent = usuarioNombre;
        document.getElementById('modalRolActualTexto').textContent = rolActualTexto;
        
        // Guardar datos en el modal para uso posterior
        const modal = document.getElementById('modalCambioRol');
        modal.setAttribute('data-usuario-id', usuarioId);
        modal.setAttribute('data-rol-actual', rolActual);
        modal.setAttribute('data-usuario-nombre', usuarioNombre);
        
        // Generar opciones de rol disponibles
        this.generarOpcionesRoles(rolActual);
        
        // Resetear selección de rol nuevo
        this.resetearSeleccionRol();
        
        // Mostrar modal
        const modalInstance = new bootstrap.Modal(modal);
        modalInstance.show();
    }

    generarOpcionesRoles(rolActual) {
        const contenedor = document.getElementById('rolesDisponibles');
        contenedor.innerHTML = '';
        
        // Definir roles disponibles
        const rolesDisponibles = [];
        
        // ============================================================
        // CONFIGURACIÓN DE ROLES DISPONIBLES
        // Actualmente solo permitimos roles 2 (Editor) y 3 (Cliente)
        // ============================================================

        /*
        PARA PODER HABILITAR EL ROL ADMINISTRADOR DEBES SEGUIER LOS PASOS DE CDRA
        DEBES SEGUIR LOS PASOS EN ORDEN NUMERICO Y ASI PODER HABILITAR Y DESABILITAR LA OPCION DE PODER CAMBIAR EL ROL A ADMIN
        SI UN PASO NO SE COMPLETA CORECTAMENTE EL SISTEMA LE GENERA UN ERROR Y NO SE PODRA HABILITAR NINGUN ROL
        LAS PARTES SE DIVIDEN POR SECCIONES DE INICIO A FIN Y DE DEBE COMENTAR O QUITAR COMENTARIO DEPENDIENDO DE LO QUE DIGA EL PASO  
        */
        
        // CDRA1 QUITAR COMENTARIO DE ESTA SECCIÓN SI SE DESEA HABILITAR ROR ADMIN O PONER COMENTARIO EN LA SECCIÓN SI DESEA DESHABILITAR ROL ADMIN
        
        // INICIO SECCIÓN
        
        // En la nueva función generarOpcionesRoles():
        if (rolActual === 2) {
            // Editor puede cambiar a: Cliente O Administrador (si habilitado)
            rolesDisponibles.push({
                id: 3,
                texto: 'Cliente',
                icono: 'fa-user',
                clase: 'btn-outline-primary'
            });
            // Agregar Administrador si está habilitado
            rolesDisponibles.push({
                id: 1,
                texto: 'Administrador',
                icono: 'fa-crown',
                clase: 'btn-outline-danger'
            });
        }
        else if (rolActual === 3) {
            // Cliente puede cambiar SOLO a Editor (NO puede volverse Administrador directamente)
            rolesDisponibles.push({
                id: 2,
                texto: 'Editor',
                icono: 'fa-edit',
                clase: 'btn-outline-primary'
            });
            // NO SE AGREGA ADMINISTRADOR PARA CLIENTES
            // Un cliente debe primero ser Editor para luego poder ser Administrador
        }
        else if (rolActual === 1) {
            // Administrador NO puede cambiar su rol (es irreversible)
            // No se agregan opciones de cambio
            // Si necesitas permitir que Administradores bajen de rol, descomenta:
            /*
            rolesDisponibles.push(
                {
                    id: 2,
                    texto: 'Editor',
                    icono: 'fa-edit',
                    clase: 'btn-outline-primary'
                },
                {
                    id: 3,
                    texto: 'Cliente',
                    icono: 'fa-user',
                    clase: 'btn-outline-primary'
                }
            );
            */
        }
        
        // FIN DE SECCIÓN
        
        // CDRA 2 COMENTAR ESTA SECCIÓN PARA PERMITIR ROL ADMIN O QUITAR COMENTARIO DE ESTA SECCIÓN PARA DESABILITAR EL ROL ADMIN
        
        // INICIO SECCIÓN
        /*
        // Si el rol actual es 2 (Editor), solo mostrar 3 (Cliente)
        if (rolActual === 2) {
            rolesDisponibles.push({
                id: 3,
                texto: 'Cliente',
                icono: 'fa-user',
                clase: 'btn-outline-primary'
            });
        }
        // Si el rol actual es 3 (Cliente), solo mostrar 2 (Editor)
        else if (rolActual === 3) {
            rolesDisponibles.push({
                id: 2,
                texto: 'Editor',
                icono: 'fa-edit',
                clase: 'btn-outline-primary'
            });
        }
        // Si el rol actual es 1 (Administrador), mostrar ambos roles (Editor y Cliente)
        else if (rolActual === 1) {
            rolesDisponibles.push(
                {
                    id: 2,
                    texto: 'Editor',
                    icono: 'fa-edit',
                    clase: 'btn-outline-primary'
                },
                {
                    id: 3,
                    texto: 'Cliente',
                    icono: 'fa-user',
                    clase: 'btn-outline-primary'
                }
            );
        }
        */
        // FIN DE SECCIÓN

        // Generar botones
        rolesDisponibles.forEach(rol => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `btn ${rol.clase} rol-option-btn`;
            button.setAttribute('data-rol-id', rol.id);
            button.setAttribute('data-rol-texto', rol.texto);
            button.innerHTML = `<i class="fas ${rol.icono} me-1"></i>${rol.texto}`;
            
            button.addEventListener('click', (e) => this.seleccionarRol(e, button));
            contenedor.appendChild(button);
        });
        
        // Si no hay roles disponibles (caso extremo)
        if (rolesDisponibles.length === 0) {
            contenedor.innerHTML = `
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay roles disponibles para cambiar
                </div>
            `;
        }
    }

    seleccionarRol(event, boton) {
        event.preventDefault();
        
        // Remover clase active de todos los botones
        document.querySelectorAll('.rol-option-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('btn-primary-dark');
            btn.classList.add('btn-outline-primary');
        });
        
        // Agregar clase active al botón seleccionado
        boton.classList.add('active');
        boton.classList.remove('btn-outline-primary');
        boton.classList.add('btn-primary-dark');
        
        // Obtener datos del rol seleccionado
        const rolId = boton.getAttribute('data-rol-id');
        const rolTexto = boton.getAttribute('data-rol-texto');
        
        // Actualizar display del rol nuevo
        document.getElementById('modalRolNuevoTexto').textContent = rolTexto;
        
        // Actualizar datos en el modal
        const modal = document.getElementById('modalCambioRol');
        modal.setAttribute('data-rol-nuevo', rolId);
        modal.setAttribute('data-rol-nuevo-texto', rolTexto);
        
        // Habilitar botón de confirmación
        document.getElementById('confirmarCambioRolBtn').disabled = false;
    }

    resetearSeleccionRol() {
        // Resetear botones de selección
        document.querySelectorAll('.rol-option-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('btn-primary-dark');
            btn.classList.add('btn-outline-primary');
        });
        
        // Resetear texto del rol nuevo
        document.getElementById('modalRolNuevoTexto').textContent = 'Seleccionar';
        
        // Deshabilitar botón de confirmación
        document.getElementById('confirmarCambioRolBtn').disabled = true;
        
        // Remover datos del modal
        const modal = document.getElementById('modalCambioRol');
        modal.removeAttribute('data-rol-nuevo');
        modal.removeAttribute('data-rol-nuevo-texto');
    }

    confirmarCambioRol() {
        const modal = document.getElementById('modalCambioRol');
        const usuarioId = modal.getAttribute('data-usuario-id');
        const usuarioNombre = modal.getAttribute('data-usuario-nombre');
        const rolActual = parseInt(modal.getAttribute('data-rol-actual'));
        const rolNuevo = parseInt(modal.getAttribute('data-rol-nuevo'));
        const rolActualTexto = document.getElementById('modalRolActualTexto').textContent;
        const rolNuevoTexto = document.getElementById('modalRolNuevoTexto').textContent;
        
        if (!rolNuevo) {
            this.showToast('Por favor, selecciona un rol nuevo.', 'warning');
            return;
        }
        
        // ============================================================
        // VALIDACIONES ESPECIALES PARA ROL ADMINISTRADOR
        // ============================================================
        
        // Si está cambiando a Administrador (rol 1)
        if (rolNuevo === 1) {
            // Solo permitir si el rol actual es Editor (2)
            if (rolActual !== 2) {
                this.showToast('❌ Solo los Editores pueden ser promovidos a Administradores.', 'warning');
                return;
            }
            
            // Mostrar modal de confirmación CRÍTICA con casillas de verificación
            this.showConfirmacionCriticaAdmin(usuarioId, usuarioNombre, rolActualTexto, rolNuevoTexto, modal);
            return;
        }
        
        // Si está cambiando de Administrador a otro rol (deshabilitado por defecto)
        if (rolActual === 1) {
            this.showToast('❌ No se puede cambiar el rol de un Administrador.', 'warning');
            return;
        }
        
        // Para cambios normales (Editor ↔ Cliente)
        this.showConfirmacionNormal(usuarioId, usuarioNombre, rolActual, rolNuevo, rolActualTexto, rolNuevoTexto, modal);
    }

    // CONFIRMACIÓN CRÍTICA PARA ASCENSO A ADMINISTRADOR (2 casillas)
    showConfirmacionCriticaAdmin(usuarioId, usuarioNombre, rolActualTexto, rolNuevoTexto, modalOriginal) {
        const modalId = 'confirmacionCriticaAdmin';
        
        // Eliminar modal existente
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();
        
        const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <div class="d-flex align-items-center w-100">
                            <div class="modal-icon" style="background: rgba(255,255,255,0.3); padding: 12px; border-radius: 50%; margin-right: 15px;">
                                <i class="fas fa-crown fa-lg"></i>
                            </div>
                            <div>
                                <h4 class="modal-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>PROMOCIÓN A ADMINISTRADOR</h4>
                                <small class="text-primary">ADVERTENCIA CRÍTICA - ACCIÓN IRREVERSIBLE</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <!-- Información del usuario -->
                            <div class="alert alert-primary-light mb-4">
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="avatar-circle-sm bg-primary-dark me-3">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div class="text-start">
                                        <h5 class="mb-0 text-primary-dark">${usuarioNombre}</h5>
                                        <small class="text-primary">ID: ${usuarioId}</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Visualización del cambio -->
                            <div class="d-flex justify-content-center align-items-center gap-4 mb-4">
                                <div class="text-center">
                                    <div class="rol-badge rol-actual bg-primary">
                                        <i class="fas fa-edit me-1"></i>${rolActualTexto}
                                    </div>
                                    <small class="text-muted d-block mt-1">Rol Actual</small>
                                </div>
                                
                                <div class="arrow-container">
                                    <i class="fas fa-long-arrow-alt-right fa-2x text-primary"></i>
                                </div>
                                
                                <div class="text-center">
                                    <div class="rol-badge rol-nuevo bg-primary-dark">
                                        <i class="fas fa-crown me-1"></i>${rolNuevoTexto}
                                    </div>
                                    <small class="text-muted d-block mt-1">Rol Nuevo</small>
                                </div>
                            </div>
                            
                            <!-- Consecuencias importantes -->
                            <div class="alert alert-primary mb-4">
                                <h5 class="alert-heading text-primary-dark"><i class="fas fa-ban me-2"></i>CONSECUENCIAS IRREVERSIBLES</h5>
                                <ul class="mb-0 text-dark">
                                    <li><strong>ACCESO COMPLETO</strong> a todas las funciones del sistema</li>
                                    <li><strong>NO SE PUEDE DESACTIVAR</strong> su usuario después</li>
                                    <li><strong>NO SE PUEDE CAMBIAR</strong> su rol en el futuro</li>
                                    <li><strong>RESPONSABILIDAD TOTAL</strong> por sus acciones</li>
                                </ul>
                            </div>
                            
                            <!-- CASILLAS DE VERIFICACIÓN DOBLE -->
                            <div class="casillas-verificacion mb-4">
                                <h6 class="text-primary-dark mb-3"><i class="fas fa-shield-alt me-2"></i>CONFIRMACIÓN DE SEGURIDAD (2 PASOS)</h6>
                                
                                <div class="form-check mb-3 p-3 border rounded bg-light">
                                    <input class="form-check-input casilla-confirmacion" type="checkbox" id="confirmacion1" style="width: 20px; height: 20px;">
                                    <label class="form-check-label ms-2" for="confirmacion1" style="font-size: 1rem;">
                                        <strong>PRIMERA CONFIRMACIÓN:</strong> Entiendo que esta acción es <span class="text-danger">IRREVERSIBLE</span> y no se puede deshacer.
                                    </label>
                                </div>
                                
                                <div class="form-check mb-3 p-3 border rounded bg-light">
                                    <input class="form-check-input casilla-confirmacion" type="checkbox" id="confirmacion2" style="width: 20px; height: 20px;">
                                    <label class="form-check-label ms-2" for="confirmacion2" style="font-size: 1rem;">
                                        <strong>SEGUNDA CONFIRMACIÓN:</strong> Confirmo que <span class="text-primary-dark">${usuarioNombre}</span> debe tener acceso completo a todas las funciones del sistema.
                                    </label>
                                </div>
                                
                                <div class="alert alert-primary-light mt-3">
                                    <i class="fas fa-info-circle me-2 text-primary"></i>
                                    <small class="text-primary-dark">Ambas casillas deben estar marcadas para habilitar la confirmación.</small>
                                </div>
                            </div>
                            
                            <!-- Contador de verificación -->
                            <div class="verificacion-contador mb-3">
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-danger" id="progresoConfirmacion" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <small class="text-muted" id="textoProgreso">0 de 2 confirmaciones completadas</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-danger" id="btnConfirmarCritico" disabled>
                            <i class="fas fa-crown me-1"></i> CONFIRMAR PROMOCIÓN A ADMIN
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        modal.show();
        
        // Configurar eventos para las casillas de verificación
        const casillas = modalElement.querySelectorAll('.casilla-confirmacion');
        const btnConfirmar = modalElement.querySelector('#btnConfirmarCritico');
        const progresoBar = modalElement.querySelector('#progresoConfirmacion');
        const textoProgreso = modalElement.querySelector('#textoProgreso');
        
        // Función para verificar estado de casillas
        const verificarCasillas = () => {
            const casillasMarcadas = Array.from(casillas).filter(c => c.checked).length;
            const todasMarcadas = casillasMarcadas === casillas.length;
            
            // Actualizar progreso
            const porcentaje = (casillasMarcadas / casillas.length) * 100;
            progresoBar.style.width = `${porcentaje}%`;
            textoProgreso.textContent = `${casillasMarcadas} de ${casillas.length} confirmaciones completadas`;
            
            // Habilitar/deshabilitar botón
            btnConfirmar.disabled = !todasMarcadas;
            if (todasMarcadas) {
                btnConfirmar.classList.remove('btn-secondary');
                btnConfirmar.classList.add('btn-danger');
            } else {
                btnConfirmar.classList.remove('btn-danger');
                btnConfirmar.classList.add('btn-secondary');
            }
        };
        
        // Agregar eventos a cada casilla
        casillas.forEach(casilla => {
            casilla.addEventListener('change', verificarCasillas);
        });
        
        // Configurar botón de confirmación
        btnConfirmar.addEventListener('click', () => {
            this.enviarCambioRol(usuarioId, 1); // Siempre es rol 1 para Admin
            modal.hide();
            
            // Cerrar también el modal original de cambio de rol
            const modalOriginalInstancia = bootstrap.Modal.getInstance(modalOriginal);
            if (modalOriginalInstancia) {
                modalOriginalInstancia.hide();
            }
        });
        
        // Limpiar al cerrar
        modalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                modalElement.remove();
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }, 300);
        });
    }

    // CONFIRMACIÓN NORMAL PARA CAMBIOS EDITOR ↔ CLIENTE (1 casilla)
    showConfirmacionNormal(usuarioId, usuarioNombre, rolActual, rolNuevo, rolActualTexto, rolNuevoTexto, modalOriginal) {
        const modalId = 'confirmacionNormalRol';
        
        // Determinar colores según el cambio
        const esEditorACliente = (rolActual === 2 && rolNuevo === 3);
        const esClienteAEditor = (rolActual === 3 && rolNuevo === 2);
        
        // USAR MISMO COLOR PARA AMBOS: bg-primary-dark (como Cliente → Editor)
        const colorClase = 'bg-primary-dark';
        const colorIcono = esEditorACliente ? 'fa-user' : 'fa-edit';
        const textoDireccion = esEditorACliente ? 'Editor → Cliente' : 'Cliente → Editor';
        
        // Eliminar modal existente
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();
        
        const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header ${colorClase} text-white">
                        <div class="d-flex align-items-center w-100">
                            <div class="modal-icon" style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; margin-right: 15px;">
                                <i class="fas ${colorIcono} fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0">Confirmar Cambio de Rol</h5>
                                <small class="text-light">${textoDireccion}</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="text-center">
                            <!-- Información del usuario -->
                            <div class="alert alert-primary-light mb-3">
                                <i class="fas fa-user me-2 text-primary-dark"></i>
                                <strong class="text-primary-dark">${usuarioNombre}</strong>
                                <br>
                                <small class="text-primary">ID: ${usuarioId}</small>
                            </div>
                            
                            <!-- Visualización del cambio -->
                            <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                                <span class="badge ${rolActual === 2 ? 'bg-primary' : 'bg-primary-light'} p-2">
                                    <i class="fas ${rolActual === 2 ? 'fa-edit' : 'fa-user'} me-1"></i>${rolActualTexto}
                                </span>
                                <i class="fas fa-arrow-right fa-lg text-primary"></i>
                                <span class="badge ${rolNuevo === 2 ? 'bg-primary' : 'bg-primary-light'} p-2">
                                    <i class="fas ${rolNuevo === 2 ? 'fa-edit' : 'fa-user'} me-1"></i>${rolNuevoTexto}
                                </span>
                            </div>
                            
                            <!-- Consecuencias -->
                            <div class="alert alert-primary-light mb-4">
                                <h6 class="text-primary-dark"><i class="fas fa-info-circle me-2 text-primary"></i>Consecuencias del cambio:</h6>
                                <ul class="mb-0 ps-3 text-dark">
                                    ${esEditorACliente 
                                        ? '<li><strong>PERDERÁ</strong> permisos de edición</li><li><strong>SÓLO PODRÁ</strong> realizar compras</li><li><strong>NO PODRÁ</strong> gestionar productos</li>'
                                        : '<li><strong>OBTENDRÁ</strong> permisos de edición</li><li><strong>PODRÁ</strong> gestionar productos</li><li><strong>PUEDE SER</strong> promovido a Admin en el futuro</li>'
                                    }
                                </ul>
                            </div>
                            
                            <!-- CASILLA DE VERIFICACIÓN SIMPLE -->
                            <div class="casilla-verificacion mb-4">
                                <div class="form-check p-3 border rounded bg-light">
                                    <input class="form-check-input casilla-confirmacion-normal" type="checkbox" id="confirmacionNormal" style="width: 18px; height: 18px;">
                                    <label class="form-check-label ms-2 text-dark" for="confirmacionNormal">
                                        Confirmo que deseo cambiar el rol de <strong class="text-primary-dark">${usuarioNombre}</strong> de <strong class="text-primary-dark">${rolActualTexto}</strong> a <strong class="text-primary-dark">${rolNuevoTexto}</strong>.
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary-dark" id="btnConfirmarNormal" disabled>
                            <i class="fas fa-check me-1"></i> Confirmar Cambio
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        modal.show();
        
        // Configurar eventos para la casilla de verificación
        const casilla = modalElement.querySelector('.casilla-confirmacion-normal');
        const btnConfirmar = modalElement.querySelector('#btnConfirmarNormal');
        
        // Función para verificar estado de la casilla
        const verificarCasilla = () => {
            btnConfirmar.disabled = !casilla.checked;
            if (casilla.checked) {
                btnConfirmar.classList.remove('btn-secondary');
                btnConfirmar.classList.add('btn-primary-dark');
            } else {
                btnConfirmar.classList.remove('btn-primary-dark');
                btnConfirmar.classList.add('btn-secondary');
            }
        };
        
        // Agregar evento a la casilla
        casilla.addEventListener('change', verificarCasilla);
        
        // Configurar botón de confirmación
        btnConfirmar.addEventListener('click', () => {
            this.enviarCambioRol(usuarioId, rolNuevo);
            modal.hide();
            
            // Cerrar también el modal original de cambio de rol
            const modalOriginalInstancia = bootstrap.Modal.getInstance(modalOriginal);
            if (modalOriginalInstancia) {
                modalOriginalInstancia.hide();
            }
        });
        
        // Limpiar al cerrar
        modalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                modalElement.remove();
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }, 300);
        });
    }

    enviarCambioRol(idUsuario, nuevoRol) {
        const form = document.getElementById('formCambiarRol');
        if (!form) {
            console.error('No se encontró el formulario para cambiar rol');
            return;
        }
        
        // Establecer valores
        document.getElementById('cambiarRolIdUsuario').value = idUsuario;
        document.getElementById('cambiarRolIdRol').value = nuevoRol;
        
        // Enviar formulario
        form.submit();
    }

    setupCleanForm() {
        // Botón para limpiar formulario (form.php)
        const cleanButtons = document.querySelectorAll('#btnLimpiar, #btnLimpiar2');
        if (cleanButtons.length > 0) {
            cleanButtons.forEach(btn => {
                btn.addEventListener('click', () => this.cleanForm());
            });
        }
    }

    setupValidacionDuplicados() {
        // Para campos únicos, validar duplicados al perder foco
        const camposUnicos = ['N_Documento', 'Correo', 'Celular'];
        camposUnicos.forEach(campoId => {
            const input = document.getElementById(campoId);
            if (input) {
                input.addEventListener('blur', () => this.verificarCampoUnico(input));
            }
        });
    }

    cleanForm() {
        const form = document.getElementById('formUsuario');
        if (!form) return;
        
        this.showConfirmModal({
            title: 'Limpiar Formulario',
            message: '¿Estás seguro de limpiar todos los campos del formulario?<br><small>Esta acción no se puede deshacer.</small>',
            icon: 'fa-eraser',
            type: 'primary',
            confirmText: 'Limpiar Todo',
            onConfirm: () => {
                // Resetear formulario
                form.reset();
                
                // Resetear selects a opción por defecto
                const selects = form.querySelectorAll('select');
                selects.forEach(select => {
                    if (select.id === 'ID_Rol') {
                        select.value = '';
                        const defaultOption = select.querySelector('option[value=""]');
                        if (defaultOption) {
                            defaultOption.selected = true;
                        }
                    } else if (select.id === 'ID_TD') {
                        select.value = '';
                        const defaultOption = select.querySelector('option[value=""]');
                        if (defaultOption) {
                            defaultOption.selected = true;
                        }
                    }
                });
                
                // Limpiar errores de validación
                this.clearAllErrors();
                
                // Enfocar primer campo
                const firstInput = form.querySelector('input, select');
                if (firstInput) firstInput.focus();
                
                this.showToast('Formulario limpiado correctamente', 'success');
            }
        });
    }

    clearAllErrors() {
        // Limpiar todos los errores de validación
        const form = document.getElementById('formUsuario');
        if (!form) return;
        
        const errorInputs = form.querySelectorAll('.is-invalid');
        errorInputs.forEach(input => {
            input.classList.remove('is-invalid');
        });
        
        const errorMessages = form.querySelectorAll('.invalid-feedback');
        errorMessages.forEach(error => {
            error.remove();
        });
    }

    setupFormValidation() {
        const form = document.getElementById('formUsuario');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            if (!this.validateForm()) {
                return false;
            }
            
            // Validación adicional: verificar duplicados antes de enviar
            const validacionDuplicados = await this.verificarDuplicados();
            if (!validacionDuplicados) {
                return false;
            }
            
            // Si todo está bien, enviar el formulario
            form.submit();
            return true;
        });

        // Validación en tiempo real para campos del formulario
        const campos = ['N_Documento', 'Nombre', 'Apellido', 'Correo', 'Celular', 'Password', 'ID_TD', 'ID_Rol'];
        campos.forEach(campoId => {
            const input = document.getElementById(campoId);
            if (input) {
                input.addEventListener('input', () => this.validateField(input));
                input.addEventListener('change', () => this.validateField(input));
            }
        });
    }

    async verificarCampoUnico(input) {
        const value = input.value.trim();
        const fieldId = input.id;
        
        if (!value) {
            this.clearFieldError(input);
            return true;
        }
        
        // Validar formato básico primero
        if (!this.validateField(input)) {
            return false;
        }
        
        try {
            let url = '';
            let campoNombre = '';
            
            switch(fieldId) {
                case 'N_Documento':
                    url = `${window.BASE_URL || ''}?c=UsuarioAdmin&a=verificarDocumento&documento=${value}`;
                    campoNombre = 'documento';
                    break;
                case 'Correo':
                    url = `${window.BASE_URL || ''}?c=UsuarioAdmin&a=verificarEmail&email=${encodeURIComponent(value)}`;
                    campoNombre = 'correo electrónico';
                    break;
                case 'Celular':
                    url = `${window.BASE_URL || ''}?c=UsuarioAdmin&a=verificarCelular&celular=${value}`;
                    campoNombre = 'celular';
                    break;
            }
            
            if (!url) return true;
            
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.existe) {
                this.showFieldError(input, `Este ${campoNombre} ya está registrado por: ${data.usuario}`);
                // Resaltar el campo con error
                input.classList.add('field-error-highlight');
                setTimeout(() => input.classList.remove('field-error-highlight'), 2000);
                return false;
            } else {
                this.clearFieldError(input);
                return true;
            }
            
        } catch (error) {
            console.error('Error al verificar duplicado:', error);
            return true;
        }
    }

    async verificarDuplicados() {
        let todosValidos = true;
        
        // Verificar documento
        const documentoInput = document.getElementById('N_Documento');
        if (documentoInput && documentoInput.value.trim()) {
            const valido = await this.verificarCampoUnico(documentoInput);
            if (!valido) todosValidos = false;
        }
        
        // Verificar correo
        const correoInput = document.getElementById('Correo');
        if (correoInput && correoInput.value.trim()) {
            const valido = await this.verificarCampoUnico(correoInput);
            if (!valido) todosValidos = false;
        }
        
        // Verificar celular
        const celularInput = document.getElementById('Celular');
        if (celularInput && celularInput.value.trim()) {
            const valido = await this.verificarCampoUnico(celularInput);
            if (!valido) todosValidos = false;
        }
        
        if (!todosValidos) {
            this.showToast('Por favor, corrige los campos duplicados.', 'warning');
        }
        
        return todosValidos;
    }

    setupPasswordToggle() {
        const passwordInput = document.getElementById('Password');
        const toggleBtn = document.getElementById('togglePassword');
        
        if (passwordInput && toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                toggleBtn.innerHTML = type === 'password' 
                    ? '<i class="fas fa-eye"></i>' 
                    : '<i class="fas fa-eye-slash"></i>';
            });
        }
    }

    handleCambiarEstado(event, btn) {
        event.preventDefault();
        
        const id = btn.getAttribute('data-id');
        const nombre = btn.getAttribute('data-nombre');
        const accion = btn.getAttribute('data-accion');
        const url = btn.getAttribute('href');
        
        // Para activaciones, mostrar modal con confirmación
        if (accion === 'activar') {
            this.showConfirmacionActivacion(id, nombre, url);
        }
        // Las desactivaciones ya tienen su propio modal con motivo
    }

    // Nueva función para confirmación de activación
    showConfirmacionActivacion(id, nombre, url) {
        const modalId = 'confirmacionActivacionModal';
        
        // Eliminar modal existente
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();
        
        const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <div class="d-flex align-items-center w-100">
                            <div class="modal-icon" style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; margin-right: 15px;">
                                <i class="fas fa-user-check fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0">Activar Usuario</h5>
                                <small class="text-light">Confirmación requerida</small>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-primary text-white mb-3">
                            <div class="d-flex align-items-center">
                                <div class="user-icon-container me-3">
                                    <div class="avatar-circle-small bg-white text-primary d-flex align-items-center justify-content-center">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-light">Estás a punto de activar al usuario:</h6>
                                    <h4 class="mb-0 text-white">${nombre}</h4>
                                    <small class="text-light opacity-75">ID: ${id}</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-primary-light mb-4">
                            <h6 class="text-primary-dark mb-2"><i class="fas fa-info-circle me-2 text-primary"></i>Consecuencias de la activación:</h6>
                            <ul class="mb-0 ps-3 text-dark">
                                <li>El usuario podrá iniciar sesión nuevamente</li>
                                <li>Se eliminará el motivo de desactivación anterior</li>
                            </ul>
                        </div>
                        
                        <!-- Casillas de verificación -->
                        <div class="casillas-verificacion-activacion mb-4">
                            <h6 class="text-primary-dark mb-3"><i class="fas fa-shield-alt me-2 text-primary"></i>Confirmación de activación</h6>
                            
                            <div class="form-check mb-3 p-3 border rounded bg-light">
                                <input class="form-check-input casilla-confirmacion-activacion" type="checkbox" id="confirmacionActivacion1" style="width: 20px; height: 20px;">
                                <label class="form-check-label ms-2 text-dark" for="confirmacionActivacion1">
                                    <strong>PRIMERA CONFIRMACIÓN:</strong> Confirmo que deseo activar al usuario <strong class="text-primary-dark">${nombre}</strong>.
                                </label>
                            </div>
                            
                            <div class="form-check mb-3 p-3 border rounded bg-light">
                                <input class="form-check-input casilla-confirmacion-activacion" type="checkbox" id="confirmacionActivacion2" style="width: 20px; height: 20px;">
                                <label class="form-check-label ms-2 text-dark" for="confirmacionActivacion2">
                                    <strong>SEGUNDA CONFIRMACIÓN:</strong> Estoy seguro de que este usuario cumple con las políticas de la plataforma.
                                </label>
                            </div>
                            
                            <div class="alert alert-primary-light mt-3">
                                <i class="fas fa-info-circle me-2 text-primary"></i>
                                <small class="text-primary-dark">Ambas casillas deben estar marcadas para habilitar la activación.</small>
                            </div>
                        </div>
                        
                        <!-- Contador de verificación -->
                        <div class="verificacion-contador-activacion mb-3">
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" id="progresoConfirmacionActivacion" role="progressbar" style="width: 0%;"></div>
                            </div>
                            <small class="text-muted" id="textoProgresoActivacion">0 de 2 confirmaciones completadas</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="button" class="btn btn-primary" id="btnConfirmarActivacion" disabled>
                            <i class="fas fa-user-check me-1"></i> Activar Usuario
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        modal.show();
        
        // Configurar eventos para las casillas de verificación
        const casillas = modalElement.querySelectorAll('.casilla-confirmacion-activacion');
        const btnConfirmar = modalElement.querySelector('#btnConfirmarActivacion');
        const progresoBar = modalElement.querySelector('#progresoConfirmacionActivacion');
        const textoProgreso = modalElement.querySelector('#textoProgresoActivacion');
        
        // Función para verificar estado de casillas
        const verificarCasillas = () => {
            const casillasMarcadas = Array.from(casillas).filter(c => c.checked).length;
            const todasMarcadas = casillasMarcadas === casillas.length;
            
            // Actualizar progreso
            const porcentaje = (casillasMarcadas / casillas.length) * 100;
            progresoBar.style.width = `${porcentaje}%`;
            textoProgreso.textContent = `${casillasMarcadas} de ${casillas.length} confirmaciones completadas`;
            
            // Cambiar color de la barra según el progreso
            if (casillasMarcadas === 0) {
                progresoBar.classList.remove('bg-success', 'bg-warning');
                progresoBar.classList.add('bg-primary');
            } else if (casillasMarcadas === 1) {
                progresoBar.classList.remove('bg-primary', 'bg-success');
                progresoBar.classList.add('bg-warning');
            } else {
                progresoBar.classList.remove('bg-primary', 'bg-warning');
                progresoBar.classList.add('bg-success');
            }
            
            // Habilitar/deshabilitar botón
            btnConfirmar.disabled = !todasMarcadas;
            if (todasMarcadas) {
                btnConfirmar.classList.remove('btn-secondary');
                btnConfirmar.classList.add('btn-primary');
            } else {
                btnConfirmar.classList.remove('btn-primary');
                btnConfirmar.classList.add('btn-secondary');
            }
        };
        
        // Agregar eventos a cada casilla
        casillas.forEach(casilla => {
            casilla.addEventListener('change', verificarCasillas);
        });
        
        // Configurar botón de confirmación
        btnConfirmar.addEventListener('click', () => {
            modal.hide();
            setTimeout(() => {
                window.location.href = url;
            }, 300);
        });
        
        // Limpiar al cerrar
        modalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                modalElement.remove();
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }, 300);
        });
    }

    setupConfirmations() {
        // Confirmación para links de cambio de estado que no tienen la clase
        document.querySelectorAll('a[href*="cambiarEstado"][href*="estado=1"]').forEach(link => {
            if (!link.classList.contains('cambiar-estado-btn')) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = new URL(link.href);
                    const action = 'activar';
                    this.confirmarCambioEstado(e, link, action);
                });
            }
        });
    }

    confirmarCambioEstado(event, link, action) {
        event.preventDefault();
        
        let userName = 'este usuario';
        const row = link.closest('tr');
        if (row) {
            const nameCell = row.querySelector('td:nth-child(2)');
            if (nameCell) userName = nameCell.textContent.trim();
        }

        const url = link.getAttribute('href');
        
        this.showConfirmModal({
            title: 'Activar Usuario',
            message: `¿Estás seguro de activar al usuario <strong>"${userName}"</strong>?`,
            icon: 'fa-play',
            type: 'primary',
            confirmText: 'Activar',
            onConfirm: () => {
                window.location.href = url;
            }
        });
    }

    validateForm() {
        let isValid = true;
        
        // Validar tipo de documento
        const idTD = document.getElementById('ID_TD');
        if (idTD && !this.validateField(idTD)) {
            isValid = false;
        }
        
        // Validar documento
        const nDocumento = document.getElementById('N_Documento');
        if (nDocumento && !this.validateField(nDocumento)) {
            isValid = false;
        }

        // Validar nombre
        const nombre = document.getElementById('Nombre');
        if (nombre && !this.validateField(nombre)) {
            isValid = false;
        }

        // Validar apellido
        const apellido = document.getElementById('Apellido');
        if (apellido && !this.validateField(apellido)) {
            isValid = false;
        }

        // Validar email
        const correo = document.getElementById('Correo');
        if (correo && !this.validateField(correo)) {
            isValid = false;
        }

        // Validar celular
        const celular = document.getElementById('Celular');
        if (celular && !this.validateField(celular)) {
            isValid = false;
        }

        // Validar contraseña
        const password = document.getElementById('Password');
        if (password && !this.validateField(password)) {
            isValid = false;
        }

        // Validar rol
        const idRol = document.getElementById('ID_Rol');
        if (idRol && !this.validateField(idRol)) {
            isValid = false;
        }

        if (!isValid) {
            this.showToast('Por favor, corrige los errores en el formulario.', 'warning');
        }

        return isValid;
    }

    validateField(input) {
        const value = input.value.trim();
        const fieldId = input.id;
        
        // Limpiar error anterior
        this.clearFieldError(input);
        
        // Si es select vacío
        if (input.tagName === 'SELECT' && !value) {
            this.showFieldError(input, `Debes seleccionar un ${fieldId === 'ID_TD' ? 'tipo de documento' : 'rol'}`);
            return false;
        }
        
        // Validaciones específicas por campo
        switch(fieldId) {
            case 'N_Documento':
                if (!value) {
                    this.showFieldError(input, 'El número de documento es requerido');
                    return false;
                }
                const docNum = parseInt(value);
                if (isNaN(docNum) || docNum < 100000000 || docNum > 9999999999) {
                    this.showFieldError(input, 'El documento debe tener 9-10 dígitos');
                    return false;
                }
                break;
                
            case 'Nombre':
                if (!value) {
                    this.showFieldError(input, 'El nombre es requerido');
                    return false;
                }
                if (value.length < 2) {
                    this.showFieldError(input, 'El nombre debe tener al menos 2 caracteres');
                    return false;
                }
                break;
                
            case 'Apellido':
                if (!value) {
                    this.showFieldError(input, 'El apellido es requerido');
                    return false;
                }
                if (value.length < 2) {
                    this.showFieldError(input, 'El apellido debe tener al menos 2 caracteres');
                    return false;
                }
                break;
                
            case 'Correo':
                if (!value) {
                    this.showFieldError(input, 'El correo electrónico es requerido');
                    return false;
                }
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    this.showFieldError(input, 'Ingresa un correo electrónico válido');
                    return false;
                }
                break;
                
            case 'Celular':
                if (!value) {
                    this.showFieldError(input, 'El número de celular es requerido');
                    return false;
                }
                if (!/^\d{10}$/.test(value)) {
                    this.showFieldError(input, 'El celular debe tener 10 dígitos');
                    return false;
                }
                break;
                
            case 'Password':
                if (!value) {
                    this.showFieldError(input, 'La contraseña es requerida');
                    return false;
                }
                if (value.length < 6) {
                    this.showFieldError(input, 'La contraseña debe tener al menos 6 caracteres');
                    return false;
                }
                break;
                
            case 'ID_Rol':
                if (!value) {
                    this.showFieldError(input, 'Debes seleccionar un rol');
                    return false;
                }
                const rolNum = parseInt(value);

                // ============================================================
                // CDRA3 IMPORTANTE: ACTUALMENTE SOLO PERMITIR ROLES 2 y 3
                // PARA HABILITAR EL ROL ADMIN SE DEBE CAMBIAR ( if (rolNum != 2 && rolNum != 3) { ) POR ( if (rolNum != 1 && rolNum != 2 && rolNum != 3) { )
                // PARA DESABILITAR EL ROL ADMIN DE CAMBIAR ( if (rolNum != 1 && rolNum != 2 && rolNum != 3) { ) POR ( if (rolNum != 2 && rolNum != 3) { )
                // ============================================================

                if (rolNum != 1 && rolNum != 2 && rolNum != 3) {
                    this.showFieldError(input, 'Solo se pueden seleccionar roles de Editor o Cliente');
                    return false;
                }
                break;
        }
        
        return true;
    }

    showFieldError(input, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        
        input.classList.add('is-invalid');
        input.parentNode.appendChild(errorDiv);
        
        // Scroll al campo con error
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    clearFieldError(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }

    showConfirmModal(config) {
        const modalId = 'usuarioConfirmModal';
        
        // Eliminar modal existente
        const existingModal = document.getElementById(modalId);
        if (existingModal) existingModal.remove();
        
        const buttonClass = config.type === 'danger' ? 'btn-danger' : 'btn-primary-dark';
        
        const modalHTML = `
        <div class="modal fade" id="${modalId}" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary-dark text-white">
                        <div class="d-flex align-items-center w-100">
                            <div class="modal-icon" style="background: rgba(255,255,255,0.2); padding: 10px; border-radius: 50%; margin-right: 15px;">
                                <i class="fas ${config.icon} fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="modal-title mb-0">${config.title}</h5>
                            </div>
                            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
                        </div>
                    </div>
                    <div class="modal-body">
                        ${config.message}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </button>
                        <button type="button" class="btn ${buttonClass} confirm-action-btn">
                            <i class="fas ${config.icon} me-1"></i> ${config.confirmText}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        const modalElement = document.getElementById(modalId);
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });
        
        modal.show();
        
        const confirmBtn = modalElement.querySelector('.confirm-action-btn');
        confirmBtn.addEventListener('click', () => {
            if (typeof config.onConfirm === 'function') {
                config.onConfirm();
            }
            modal.hide();
        });
        
        modalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                modalElement.remove();
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) backdrop.remove();
            }, 300);
        });
    }

    autoHideMessages() {
        const mensaje = document.getElementById('mensajeGlobal');
        if (mensaje) {
            setTimeout(() => {
                mensaje.style.opacity = '0';
                mensaje.style.transition = 'opacity 0.5s ease';
                setTimeout(() => {
                    mensaje.style.display = 'none';
                }, 500);
            }, 5000);
        }
    }

    showToast(message, type = 'info') {
        const toastId = 'usuarioToast-' + Date.now();
        const toastHTML = `
        <div class="toast align-items-center text-white bg-primary-dark border-0" id="${toastId}" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
        `;
        
        let container = document.querySelector('.usuario-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'usuario-toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1060';
            document.body.appendChild(container);
        }
        
        container.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
        toast.show();
        
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    window.usuarioManager = new UsuarioManager();
});