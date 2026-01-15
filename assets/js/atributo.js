// atributo.js - Funciones JavaScript para la gestión de atributos

// Ejemplos de valor por tipo
const ejemplosPorTipo = {
    'talla': 'Ej: M, 32, XL, S, 40, Única, etc.',
    'medida': 'Ej: 18, 30, Ajuste Estándar, Correa Larga, etc.',
    'volumen': 'Ej: 100 ml, 50 ml, 30 ml, 150 ml, etc.',
    'tamaño': 'Ej: Mediano, Grande, Pequeño, Extra Grande'
};

// Actualizar el texto de ejemplo según el tipo seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('ID_TipoAtributo');
    const ejemploSpan = document.getElementById('ejemplo');
    
    if (tipoSelect && ejemploSpan) {
        function actualizarEjemplo() {
            const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
            const tipoNombre = selectedOption.textContent.toLowerCase();
            
            // Buscar qué tipo se seleccionó
            for (const [tipo, ejemplo] of Object.entries(ejemplosPorTipo)) {
                if (tipoNombre.includes(tipo)) {
                    ejemploSpan.textContent = ejemplo;
                    return;
                }
            }
            
            // Si no se encuentra, mostrar ejemplo general
            ejemploSpan.textContent = 'Ej: M, 32, Mediano, 100 ml, etc.';
        }
        
        // Actualizar al cambiar el tipo
        tipoSelect.addEventListener('change', actualizarEjemplo);
        
        // Actualizar al cargar si ya hay un tipo seleccionado
        if (tipoSelect.value) {
            actualizarEjemplo();
        }
    }
});

// Confirmación para desactivar atributo
function confirmAtributo(enUso) {
    if (enUso) {
        alert('Este atributo está en uso y NO puede desactivarse. Si necesitas eliminarlo, primero debes cambiar los atributos de los productos que lo usan.');
        return false;
    } else {
        return confirm('¿Estás seguro de desactivar este atributo?');
    }
}

// Confirmación para eliminar atributo
function confirmEliminarAtributo(event, nombreAtributo, idAtributo) {
    // Verificar si es el valor universal "Única" (ID 16)
    if (idAtributo == 16 || nombreAtributo.toLowerCase() === 'única') {
        event.preventDefault();
        alert('⚠️ El valor "Única" es un valor universal del sistema y NO puede eliminarse.');
        return false;
    }
    
    // Verificar si el botón está deshabilitado
    const boton = event.target.closest('a');
    if (boton && (boton.classList.contains('disabled') || boton.hasAttribute('disabled'))) {
        event.preventDefault();
        alert('Este atributo está en uso y NO puede eliminarse.');
        return false;
    }
    
    const confirmacion = confirm('⚠️ ¿Estás seguro de eliminar el atributo "' + nombreAtributo + '"?\n\nEsta acción NO se puede deshacer.');
    
    if (!confirmacion) {
        event.preventDefault();
        return false;
    }
    
    return true;
}

// Validación del formulario
document.addEventListener('DOMContentLoaded', function() {
    const formAtributo = document.getElementById('formAtributo');
    
    if (formAtributo) {
        formAtributo.addEventListener('submit', function(e) {
            const valorInput = document.getElementById('Valor');
            const tipoSelect = document.getElementById('ID_TipoAtributo');
            
            // Validar que el valor no esté vacío
            if (valorInput && valorInput.value.trim() === '') {
                e.preventDefault();
                alert('Por favor, ingresa un valor para el atributo.');
                valorInput.focus();
                return false;
            }
            
            // Validar que se haya seleccionado un tipo
            if (tipoSelect && tipoSelect.value === '') {
                e.preventDefault();
                alert('Por favor, selecciona un tipo de atributo.');
                tipoSelect.focus();
                return false;
            }
            
            return true;
        });
    }
    
    // Agregar eventos a los botones de eliminar en la tabla
    const botonesEliminar = document.querySelectorAll('a.btn-outline-danger[href*="eliminar"]');
    botonesEliminar.forEach(boton => {
        boton.addEventListener('click', function(e) {
            // Obtener el nombre del atributo del texto de la fila
            const fila = this.closest('tr');
            const nombreAtributo = fila ? fila.querySelector('td:nth-child(2) strong').textContent.trim() : '';
            
            // Obtener el ID del atributo del href
            const href = this.getAttribute('href');
            const idMatch = href.match(/id=(\d+)/);
            const idAtributo = idMatch ? parseInt(idMatch[1]) : 0;
            
            // Usar la función de confirmación
            return confirmEliminarAtributo(e, nombreAtributo, idAtributo);
        });
    });
    
    // Prevenir clic en botones deshabilitados
    const botonesDeshabilitados = document.querySelectorAll('button.btn-outline-secondary[disabled], a.btn-outline-secondary[disabled]');
    botonesDeshabilitados.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const title = this.getAttribute('title') || this.getAttribute('data-bs-title');
            if (title) {
                alert(title);
            } else if (this.querySelector('.fa-ban')) {
                alert('Este atributo está en uso y no puede eliminarse.');
            } else if (this.querySelector('.fa-shield-alt')) {
                alert('⚠️ Este valor universal no se puede eliminar.');
            }
            
            return false;
        });
    });
});

// Inicializar tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});