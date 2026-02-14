// assets/js/enviados.js - Funciones JavaScript específicas para seguimiento de pedidos enviados

document.addEventListener('DOMContentLoaded', function() {
    // Añadir estilos dinámicos
    agregarEstilosEnviados();
    
    // Configurar verificación de casillas
    configurarVerificacionCasillas();
    
    // Configurar reset de casillas al cerrar modales
    configurarResetCasillas();
    
    // Auto-ocultar mensajes globales después de 5 segundos
    setTimeout(function() {
        const mensajeGlobal = document.getElementById('mensajeGlobal');
        if (mensajeGlobal) {
            mensajeGlobal.style.display = 'none';
        }
    }, 5000);
    
    // Configurar fechas mínimas en los modales y filtros
    const today = new Date().toISOString().split('T')[0];
    
    // Configurar filtros de fecha
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    
    if (fechaInicioInput && !fechaInicioInput.value) {
        // Establecer fecha de inicio como hace 30 días
        const fecha = new Date();
        fecha.setDate(fecha.getDate() - 30);
        fechaInicioInput.value = fecha.toISOString().split('T')[0];
    }
    
    if (fechaFinInput && !fechaFinInput.value) {
        fechaFinInput.value = today;
    }
    
    // Configurar fechas mínimas en los modales
    document.querySelectorAll('input[type="date"]').forEach(input => {
        if (!input.value) {
            input.value = today;
        }
    });
    
    // Resaltar filas según prioridad
    document.querySelectorAll('tr[data-prioridad]').forEach(row => {
        const prioridad = row.getAttribute('data-prioridad');
        const diasRetraso = parseInt(row.getAttribute('data-dias-retraso') || 0);
        
        if (prioridad === 'Alta' || diasRetraso > 7) {
            row.style.animation = 'pulse-high-priority 2s infinite';
        } else if (prioridad === 'Media') {
            row.classList.add('table-warning');
        }
    });
    
    // Tooltips para botones
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Colorear opciones del select de transportadoras
    const selectTransportadora = document.getElementById('transportadora');
    if (selectTransportadora) {
        selectTransportadora.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const color = selectedOption.getAttribute('data-color') || 'primary-light';
            selectedOption.style.backgroundColor = getComputedStyle(document.documentElement).getPropertyValue('--' + color) || '';
        });
        
        // Aplicar color a la opción seleccionada al cargar
        if (selectTransportadora.selectedIndex > -1) {
            const selectedOption = selectTransportadora.options[selectTransportadora.selectedIndex];
            const color = selectedOption.getAttribute('data-color') || 'primary-light';
            selectedOption.style.backgroundColor = getComputedStyle(document.documentElement).getPropertyValue('--' + color) || '';
        }
    }
    
    // Actualizar automáticamente los contadores de días cada hora
    function actualizarContadoresDias() {
        document.querySelectorAll('td:nth-child(5)').forEach(td => {
            const badge = td.querySelector('.badge');
            if (badge) {
                const texto = badge.textContent.trim();
                const match = texto.match(/(\d+)/);
                if (match) {
                    const dias = parseInt(match[1]);
                    if (texto.includes('Hoy')) {
                        // Después de medianoche, actualizar a "1 día"
                        const ahora = new Date();
                        if (ahora.getHours() === 0 && ahora.getMinutes() < 5) {
                            badge.textContent = '1 día';
                            badge.className = 'badge bg-primary';
                        }
                    }
                }
            }
        });
    }
    
    // Verificar fechas vencidas cada minuto
    setInterval(function() {
        const hoy = new Date().toISOString().split('T')[0];
        document.querySelectorAll('td:nth-child(6)').forEach(td => {
            const fechaText = td.querySelector('strong')?.textContent;
            if (fechaText) {
                const [dia, mes, ano] = fechaText.split('/');
                const fechaEstimada = `${ano}-${mes}-${dia}`;
                
                if (fechaEstimada < hoy) {
                    // Marcar como retrasado visualmente
                    const estadoBadge = td.parentElement.querySelector('.badge-estado-enviado');
                    if (estadoBadge && !estadoBadge.classList.contains('badge-estado-retrasado')) {
                        estadoBadge.className = 'badge badge-estado-retrasado d-flex align-items-center justify-content-center gap-1';
                        estadoBadge.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Retrasado';
                        td.parentElement.classList.add('table-danger');
                    }
                }
            }
        });
    }, 60000); // Cada minuto
    
    // Inicializar contadores
    actualizarContadoresDias();
    
    // Validación de fechas en filtros
    if (fechaInicioInput && fechaFinInput) {
        fechaInicioInput.addEventListener('change', function() {
            if (this.value && fechaFinInput.value && this.value > fechaFinInput.value) {
                fechaFinInput.value = this.value;
            }
        });
        
        fechaFinInput.addEventListener('change', function() {
            if (this.value && fechaInicioInput.value && this.value < fechaInicioInput.value) {
                fechaInicioInput.value = this.value;
            }
        });
    }
});

// Función para añadir estilos dinámicos
function agregarEstilosEnviados() {
    // Verificar si ya existe un estilo con estos selectores para evitar duplicados
    const estilosExistentes = document.querySelectorAll('style[data-enviados-styles]');
    if (estilosExistentes.length > 0) return;
    
    const styleElement = document.createElement('style');
    styleElement.setAttribute('data-enviados-styles', 'true');
    styleElement.textContent = `
        /* Animación para filas con prioridad alta */
        @keyframes pulse-high-priority {
            0% { 
                background-color: rgba(52, 42, 86, 0.05);
                box-shadow: 0 0 0 0 rgba(52, 42, 86, 0.3);
            }
            70% { 
                background-color: rgba(52, 42, 86, 0.15);
                box-shadow: 0 0 0 10px rgba(52, 42, 86, 0);
            }
            100% { 
                background-color: rgba(52, 42, 86, 0.05);
                box-shadow: 0 0 0 0 rgba(52, 42, 86, 0);
            }
        }
        
        /* Ajustes específicos para hover */
        .hover-shadow-pedido:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(27, 32, 45, 0.1);
            transition: all 0.3s ease;
            background-color: rgba(27, 32, 45, 0.02) !important;
        }
        
        /* Icono de check para entregado */
        .btn-outline-primary .fa-check-circle {
            color: var(--primary);
        }
        
        .btn-outline-primary:hover .fa-check-circle {
            color: white;
        }
        
        /* Estilos para select de transportadoras con colores */
        #transportadora option[data-color="primary-dark"] {
            background-color: var(--primary-dark);
            color: white;
        }
        
        #transportadora option[data-color="primary-light"] {
            background-color: var(--primary-light);
            color: white;
        }
    `;
    document.head.appendChild(styleElement);
}

// Función para manejar la verificación de casillas
function configurarVerificacionCasillas() {
    // Configurar para cada modal de entregado
    document.querySelectorAll('.modal[id^="modalEntregado"]').forEach(modal => {
        const idFactura = modal.id.replace('modalEntregado', '');
        const casillas = modal.querySelectorAll(`.casilla-entregado-${idFactura}`);
        const btnConfirmar = document.getElementById(`btnConfirmarEntregado${idFactura}`);
        const contadorSpan = document.getElementById(`contadorCasillasEntregado${idFactura}`);
        const barraProgreso = document.getElementById(`barraProgresoEntregado${idFactura}`);
        
        if (casillas.length && btnConfirmar && contadorSpan && barraProgreso) {
            casillas.forEach(casilla => {
                casilla.addEventListener('change', function() {
                    const casillasMarcadas = modal.querySelectorAll(`.casilla-entregado-${idFactura}:checked`);
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
                    btnConfirmar.disabled = marcadas !== totalCasillas;
                });
            });
        }
    });
    
    // Configurar para cada modal de actualizar fecha
    document.querySelectorAll('.modal[id^="modalActualizarFecha"]').forEach(modal => {
        const idFactura = modal.id.replace('modalActualizarFecha', '');
        const casillas = modal.querySelectorAll(`.casilla-fecha-${idFactura}`);
        const btnConfirmar = document.getElementById(`btnConfirmarFecha${idFactura}`);
        const contadorSpan = document.getElementById(`contadorCasillasFecha${idFactura}`);
        const barraProgreso = document.getElementById(`barraProgresoFecha${idFactura}`);
        
        if (casillas.length && btnConfirmar && contadorSpan && barraProgreso) {
            casillas.forEach(casilla => {
                casilla.addEventListener('change', function() {
                    const casillasMarcadas = modal.querySelectorAll(`.casilla-fecha-${idFactura}:checked`);
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
                    btnConfirmar.disabled = marcadas !== totalCasillas;
                });
            });
        }
    });
    
    // Configurar para cada modal de retrasado
    document.querySelectorAll('.modal[id^="modalRetrasado"]').forEach(modal => {
        const idFactura = modal.id.replace('modalRetrasado', '');
        const casillas = modal.querySelectorAll(`.casilla-retrasado-${idFactura}`);
        const btnConfirmar = document.getElementById(`btnConfirmarRetrasado${idFactura}`);
        const contadorSpan = document.getElementById(`contadorCasillasRetrasado${idFactura}`);
        const barraProgreso = document.getElementById(`barraProgresoRetrasado${idFactura}`);
        
        if (casillas.length && btnConfirmar && contadorSpan && barraProgreso) {
            casillas.forEach(casilla => {
                casilla.addEventListener('change', function() {
                    const casillasMarcadas = modal.querySelectorAll(`.casilla-retrasado-${idFactura}:checked`);
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
                    btnConfirmar.disabled = marcadas !== totalCasillas;
                });
            });
        }
    });
}

// Función para resetear casillas al cerrar modal
function configurarResetCasillas() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('hidden.bs.modal', function() {
            // Resetear todas las casillas dentro de este modal
            const casillas = modal.querySelectorAll('.form-check-input');
            casillas.forEach(casilla => {
                casilla.checked = false;
            });
            
            // Resetear botones de confirmación
            const botones = modal.querySelectorAll('button[type="submit"]');
            botones.forEach(boton => {
                boton.disabled = true;
            });
            
            // Resetear contadores
            const contadores = modal.querySelectorAll('[id*="contadorCasillas"]');
            contadores.forEach(contador => {
                contador.textContent = '0';
            });
            
            // Resetear barras de progreso
            const barras = modal.querySelectorAll('[id*="barraProgreso"]');
            barras.forEach(barra => {
                barra.style.width = '0%';
                barra.setAttribute('aria-valuenow', '0');
                barra.className = 'progress-bar';
            });
        });
    });
}