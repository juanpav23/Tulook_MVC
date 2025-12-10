// Sistema de atributos dinámicos para formularios
class AtributosDinamicos {
    constructor() {
        this.baseUrl = window.BASE_URL || '';
    }
    
    // Cargar atributos por subcategoría
    cargarAtributosSubcategoria(idSubcategoria, contenedorId) {
        if (!idSubcategoria) {
            document.getElementById(contenedorId).innerHTML = '';
            return;
        }
        
        fetch(`${this.baseUrl}?c=Admin&a=getAtributosBySubcategoria&id_subcategoria=${idSubcategoria}`)
            .then(response => response.json())
            .then(atributos => {
                this.mostrarAtributosEnContenedor(atributos, contenedorId);
            })
            .catch(error => {
                console.error('Error cargando atributos:', error);
                document.getElementById(contenedorId).innerHTML = 
                    '<div class="alert alert-danger">Error al cargar atributos</div>';
            });
    }
    
    // Mostrar atributos en el contenedor especificado
    mostrarAtributosEnContenedor(atributos, contenedorId) {
        let html = '';
        
        if (atributos.length === 0) {
            html = '<div class="alert alert-info">Esta subcategoría no requiere atributos específicos.</div>';
        } else {
            atributos.forEach((atributo, index) => {
                const numero = index + 1;
                html += `
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">${this.escapeHtml(atributo.tipo.Nombre)}</label>
                        <input type="hidden" name="atributo${numero}" value="${atributo.tipo.ID_TipoAtributo}">
                        <select class="form-select" name="valor_atributo${numero}" required>
                            <option value="">Seleccionar ${this.escapeHtml(atributo.tipo.Nombre)}</option>`;
                
                atributo.valores.forEach(valor => {
                    html += `<option value="${this.escapeHtml(valor.Valor)}">${this.escapeHtml(valor.Valor)}</option>`;
                });
                
                html += `
                        </select>
                        ${atributo.tipo.Descripcion ? `<small class="form-text text-muted">${this.escapeHtml(atributo.tipo.Descripcion)}</small>` : ''}
                    </div>
                </div>`;
            });
        }
        
        document.getElementById(contenedorId).innerHTML = html;
    }
    
    // Escapar HTML para prevenir XSS
    escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    // Inicializar en formularios
    inicializar() {
        // Para formulario de producto base
        const selectSubcategoria = document.getElementById('ID_SubCategoria');
        if (selectSubcategoria) {
            selectSubcategoria.addEventListener('change', (e) => {
                this.cargarAtributosSubcategoria(e.target.value, 'atributos-container');
            });
            
            // Cargar inicialmente si hay valor
            if (selectSubcategoria.value) {
                this.cargarAtributosSubcategoria(selectSubcategoria.value, 'atributos-container');
            }
        }
    }
}

// Instanciar y inicializar
const atributosManager = new AtributosDinamicos();
document.addEventListener('DOMContentLoaded', function() {
    atributosManager.inicializar();
});