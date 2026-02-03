// productos.js - Funcionalidades para la gestión de productos
// Mantenido para compatibilidad, pero se recomienda usar productosManager.js

class ProductosManagerLegacy {
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

        // Detectar cambios en filtros
        if (filterCategoria) {
            filterCategoria.addEventListener('change', () => {
                this.highlightActiveFilters();
                if (filterCategoria.value) {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        if (filterGenero) {
            filterGenero.addEventListener('change', () => {
                this.highlightActiveFilters();
                if (filterGenero.value) {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        if (filterSubcategoria) {
            filterSubcategoria.addEventListener('change', () => {
                this.highlightActiveFilters();
                if (filterSubcategoria.value) {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        if (filterEstado) {
            filterEstado.addEventListener('change', () => {
                this.highlightActiveFilters();
                if (filterEstado.value !== '') {
                    setTimeout(() => this.autoSubmitSearch(), 500);
                }
            });
        }

        // Permitir búsqueda con Enter
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchForm.submit();
                }
            });
        }

        setTimeout(() => {
            this.checkForActiveFiltersOnLoad();
        }, 100);
    }

    // Verificar filtros activos al cargar
    checkForActiveFiltersOnLoad() {
        const urlParams = new URLSearchParams(window.location.search);
        const hasFilters = urlParams.has('categoria') || urlParams.has('genero') || 
                          urlParams.has('subcategoria') || urlParams.has('estado') || 
                          urlParams.has('q');
        
        if (hasFilters) {
            this.highlightActiveFilters();
        }
    }

    // Auto-submit para búsqueda
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

        const badge = document.getElementById('filtrosBadge') || document.querySelector('.badge.bg-white.text-primary');
        if (badge) {
            badge.textContent = `${activeFilters} Filtro${activeFilters !== 1 ? 's' : ''} Activo${activeFilters !== 1 ? 's' : ''}`;
        }
    }

    // Ordenamiento de tabla
    initTableSorting() {
        const table = document.getElementById('productosTable');
        if (!table) return;

        const headers = table.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            if (index !== headers.length - 1) {
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
        
        const isAscending = !table.classList.contains('asc');
        table.classList.toggle('asc', isAscending);
        table.classList.toggle('desc', !isAscending);
        
        rows.sort((a, b) => {
            const aText = a.cells[column].textContent.trim();
            const bText = b.cells[column].textContent.trim();
            
            if (column === 5) {
                const aNum = parseFloat(aText.replace(/[^0-9.-]+/g, ""));
                const bNum = parseFloat(bText.replace(/[^0-9.-]+/g, ""));
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            return isAscending ? 
                aText.localeCompare(bText) : 
                bText.localeCompare(aText);
        });
        
        rows.forEach(row => tbody.appendChild(row));
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
}

// Inicializar versión legacy si no existe el nuevo manager
document.addEventListener('DOMContentLoaded', () => {
    if (!window.productosManager) {
        window.productosManagerLegacy = new ProductosManagerLegacy();
    }
});