// assets/js/pedido.js - Funciones JavaScript específicas para pedidos

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true
        });
    });
    
    // Configurar fechas por defecto en filtros
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    if (fechaInicio && !fechaInicio.value) {
        // Establecer fecha de inicio como hace 30 días
        const fecha = new Date();
        fecha.setDate(fecha.getDate() - 30);
        fechaInicio.value = fecha.toISOString().split('T')[0];
    }
    
    if (fechaFin && !fechaFin.value) {
        // Establecer fecha fin como hoy
        fechaFin.value = new Date().toISOString().split('T')[0];
    }
    
    // Validar que fecha inicio no sea mayor que fecha fin
    if (fechaInicio && fechaFin) {
        fechaInicio.addEventListener('change', function() {
            if (this.value && fechaFin.value && this.value > fechaFin.value) {
                fechaFin.value = this.value;
            }
        });
        
        fechaFin.addEventListener('change', function() {
            if (this.value && fechaInicio.value && this.value < fechaInicio.value) {
                fechaInicio.value = this.value;
            }
        });
    }
    
    // Resaltar pedidos atrasados
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const estadoBadge = row.querySelector('.badge-estado-retrasado');
        if (estadoBadge) {
            row.classList.add('table-danger');
            row.style.animation = 'pulse-error 2s infinite';
        }
    });
    
    // Auto-ocultar mensajes globales después de 5 segundos
    setTimeout(function() {
        const mensajeGlobal = document.getElementById('mensajeGlobal');
        if (mensajeGlobal) {
            mensajeGlobal.style.display = 'none';
        }
    }, 5000);
});

// Animación para resaltar pedidos atrasados
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse-error {
        0% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.2); }
        70% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
        100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0); }
    }
    
    .table-danger {
        border-left: 3px solid #dc3545 !important;
    }
`;
document.head.appendChild(style);