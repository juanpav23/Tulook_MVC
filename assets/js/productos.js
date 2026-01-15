// productos.js - Funcionalidades para la gestión de productos

class ProductosManager {
    constructor() {
        this.init();
    }

    init() {
        this.initTooltips();
        this.initEventListeners();
        this.initTableSorting();
        this.applyThemeColors();
    }

    // Inicializar tooltips de Bootstrap
    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Aplicar colores del tema dinámicamente
    applyThemeColors() {
        // Aplicar colores a badges específicos
        document.querySelectorAll('.badge.bg-info').forEach(badge => {
            badge.style.backgroundColor = 'var(--accent-blue)';
            badge.style.color = 'white';
        });

        document.querySelectorAll('.badge.bg-warning').forEach(badge => {
            badge.style.backgroundColor = 'var(--light-blue)';
            badge.style.color = 'white';
        });

        document.querySelectorAll('.badge.bg-primary').forEach(badge => {
            badge.style.backgroundColor = 'var(--primary-dark)';
            badge.style.color = 'white';
        });
    }

    // Inicializar event listeners
    initEventListeners() {
        const searchForm = document.getElementById('searchForm');
        const clearButton = document.getElementById('clearButton');
        const filterCategoria = document.getElementById('filterCategoria');
        const filterGenero = document.getElementById('filterGenero');
        const filterSubcategoria = document.getElementById('filterSubcategoria');
        const filterEstado = document.getElementById('filterEstado');
        const searchButton = document.getElementById('searchButton');

        // Detectar cambios en filtros para búsqueda en tiempo real
        if (filterCategoria) {
            filterCategoria.addEventListener('change', () => {
                this.highlightActiveFilters();
                // Auto-buscar cuando se selecciona un filtro
                if (filterCategoria.value) {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        if (filterGenero) {
            filterGenero.addEventListener('change', () => {
                this.highlightActiveFilters();
                // Auto-buscar cuando se selecciona un filtro
                if (filterGenero.value) {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        if (filterSubcategoria) {
            filterSubcategoria.addEventListener('change', () => {
                this.highlightActiveFilters();
                // Auto-buscar cuando se selecciona un filtro
                if (filterSubcategoria.value) {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        if (filterEstado) {
            filterEstado.addEventListener('change', () => {
                this.highlightActiveFilters();
                // Auto-buscar cuando se selecciona un filtro
                if (filterEstado.value !== '') {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        // Validación de búsqueda - CORREGIDA
        if (searchForm) {
            searchForm.addEventListener('submit', (e) => this.validateSearch(e));
        }

        // Confirmación para eliminar productos
        document.querySelectorAll('a[href*="deleteProducto"]').forEach(link => {
            link.addEventListener('click', (e) => {
                if (!confirm('¿Estás seguro de eliminar este producto y todas sus variantes?')) {
                    e.preventDefault();
                }
            });
        });

        // Permitir búsqueda con Enter en el campo de texto
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchForm.submit();
                }
            });
        }

        // Auto-submit cuando hay filtros activos y se carga la página
        setTimeout(() => {
            this.checkForActiveFiltersOnLoad();
        }, 100);
    }

    // Verificar filtros activos al cargar la página
    checkForActiveFiltersOnLoad() {
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.has('categoria') || urlParams.has('genero') || 
                          urlParams.has('subcategoria') || urlParams.has('estado') || 
                          urlParams.has('q');
        
        if (hasFilters) {
            this.highlightActiveFilters();
        }
    }

    // Auto-submit para búsqueda con filtros
    autoSubmitSearch() {
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.submit();
        }
    }

    // Resaltar filtros activos
    highlightActiveFilters() {
        const filters = ['categoria', 'genero', 'subcategoria', 'estado'];
        let activeFilters = 0;

        filters.forEach(filter => {
            const element = document.getElementById(`filter${filter.charAt(0).toUpperCase() + filter.slice(1)}`);
            if (element && element.value) {
                element.classList.add('active-filter');
                activeFilters++;
            } else {
                element.classList.remove('active-filter');
            }
        });

        // Actualizar contador de filtros activos
        const badge = document.querySelector('.badge.bg-white.text-primary');
        if (badge) {
            badge.textContent = `${activeFilters} Filtro${activeFilters !== 1 ? 's' : ''} Activo${activeFilters !== 1 ? 's' : ''}`;
        }
    }

    // Validar búsqueda - CORREGIDA (PERMITIR FILTROS SIN TEXTO)
    validateSearch(e) {
        const searchInput = document.getElementById('searchInput');
        const categoria = document.getElementById('filterCategoria');
        const genero = document.getElementById('filterGenero');
        const subcategoria = document.getElementById('filterSubcategoria');
        const estado = document.getElementById('filterEstado');
        
        // PERMITIR BÚSQUEDA SOLO CON FILTROS (sin texto)
        const hasText = searchInput && searchInput.value.trim() !== '';
        const hasCategoria = categoria && categoria.value && categoria.value !== '';
        const hasGenero = genero && genero.value && genero.value !== '';
        const hasSubcategoria = subcategoria && subcategoria.value && subcategoria.value !== '';
        const hasEstado = estado && estado.value !== '';
        
        // Si no hay ningún criterio de búsqueda
        if (!hasText && !hasCategoria && !hasGenero && !hasSubcategoria && !hasEstado) {
            // Si no hay criterios, mostrar todos los productos (no prevenir el submit)
            return true;
        }
        
        // Si hay al menos un criterio, permitir la búsqueda
        return true;
    }

    // Ordenamiento de tabla
    initTableSorting() {
        const table = document.getElementById('productosTable');
        if (!table) return;

        const headers = table.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            if (index !== headers.length - 1) { // Excluir columna de acciones
                header.style.cursor = 'pointer';
                header.addEventListener('click', () => this.sortTable(index));
            }
        });
    }

    // Ordenar tabla
    sortTable(column) {
        const table = document.getElementById('productosTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Determinar dirección de ordenamiento
        const isAscending = !table.classList.contains('asc');
        table.classList.toggle('asc', isAscending);
        table.classList.toggle('desc', !isAscending);
        
        rows.sort((a, b) => {
            const aText = a.cells[column].textContent.trim();
            const bText = b.cells[column].textContent.trim();
            
            // Para columnas numéricas (precio)
            if (column === 5) {
                const aNum = parseFloat(aText.replace(/[^0-9.-]+/g, ""));
                const bNum = parseFloat(bText.replace(/[^0-9.-]+/g, ""));
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            // Para texto
            return isAscending ? 
                aText.localeCompare(bText) : 
                bText.localeCompare(aText);
        });
        
        // Reordenar filas
        rows.forEach(row => tbody.appendChild(row));
        
        // Actualizar indicador visual
        this.updateSortIndicator(column, isAscending);
    }

    // Actualizar indicador de ordenamiento
    updateSortIndicator(column, isAscending) {
        const headers = document.querySelectorAll('#productosTable thead th');
        headers.forEach((header, index) => {
            header.classList.remove('sorting-asc', 'sorting-desc');
            if (index === column) {
                header.classList.add(isAscending ? 'sorting-asc' : 'sorting-desc');
            }
        });
    }

    // Mostrar toast de notificación
    showToast(message, type = 'info') {
        const toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1050';
        
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        const toastBody = document.createElement('div');
        toastBody.className = 'd-flex';
        
        toastBody.innerHTML = `
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        `;
        
        toast.appendChild(toastBody);
        toastContainer.appendChild(toast);
        document.body.appendChild(toastContainer);
        
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        
        toast.addEventListener('hidden.bs.toast', () => {
            toastContainer.remove();
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.productosManager = new ProductosManager();
});