/**
 * Sistema de Favoritos - Versión Limpia
 */
class FavoritoManager {
    constructor() {
        this.baseUrl = window.BASE_URL || 'http://localhost/Tulook_MVC/';
        this.init();
    }

    init() {
        this.initFavoritoButton();
        if (document.getElementById('nav-favoritos')) {
            this.updateFavoritoCount();
        }
    }

    initFavoritoButton() {
        const btn = document.getElementById('btn-favorito');
        if (!btn) return;

        const articuloId = btn.getAttribute('data-articulo-id');
        if (!this.validarArticuloId(articuloId)) {
            console.error('ID de artículo inválido:', articuloId);
            return;
        }

        // Verificar estado inicial
        this.checkFavoritoStatus(articuloId).then(estado => {
            this.updateFavoritoButton(btn, estado);
        });

        // Evento click
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleFavorito(articuloId, btn);
        });
    }

    validarArticuloId(articuloId) {
        if (!articuloId || articuloId.trim() === '') return false;
        const idNum = parseInt(articuloId);
        return !isNaN(idNum) && idNum > 0;
    }

    async checkFavoritoStatus(articuloId) {
        if (!this.validarArticuloId(articuloId)) return false;

        try {
            const response = await fetch(`${this.baseUrl}?c=Favorito&a=verificarEstado&id_articulo=${articuloId}`, {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.updateFavoritoCount(data.totalFavoritos);
                return data.esFavorito;
            }
            return false;
            
        } catch (error) {
            console.error('Error al verificar estado:', error);
            return false;
        }
    }

    async toggleFavorito(articuloId, button) {
        if (!this.validarArticuloId(articuloId)) {
            this.showError('ID de artículo inválido');
            return;
        }

        const originalHtml = button.innerHTML;
        const originalClass = button.className;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.className = 'btn btn-secondary btn-sm';
        button.disabled = true;

        try {
            const url = `${this.baseUrl}?c=Favorito&a=toggleFavorito&id_articulo=${articuloId}`;
            const response = await fetch(url, { method: 'POST' });
            const data = await response.json();

            if (data.success) {
                this.updateFavoritoButton(button, data.esFavorito);
                this.updateFavoritoCount(data.totalFavoritos);
                this.showSuccessMessage(data);
            } else {
                this.showError(data.message);
                if (data.redirect) window.location.href = data.redirect;
            }
            
        } catch (error) {
            console.error('Error:', error);
            this.showError('Error de conexión con el servidor');
        } finally {
            if (button.disabled) {
                button.disabled = false;
                button.innerHTML = originalHtml;
                button.className = originalClass;
            }
        }
    }

    updateFavoritoButton(button, esFavorito) {
        if (esFavorito) {
            button.innerHTML = '<i class="fas fa-heart"></i> Eliminar de favoritos';
            button.classList.remove('btn-outline-danger');
            button.classList.add('btn-danger');
        } else {
            button.innerHTML = '<i class="far fa-heart"></i> Agregar a favoritos';
            button.classList.remove('btn-danger');
            button.classList.add('btn-outline-danger');
        }
        button.disabled = false;
    }

    updateFavoritoCount(count) {
        const counter = document.getElementById('favoritos-count');
        if (!counter) return;

        if (count > 0) {
            counter.textContent = count;
            counter.style.display = 'block';
        } else {
            counter.style.display = 'none';
        }
    }

    showSuccessMessage(data) {
        Swal.fire({
            icon: 'success',
            title: data.accion === 'added' ? '¡Agregado!' : 'Eliminado',
            text: data.message,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message,
            confirmButtonText: 'Entendido'
        });
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.favoritoManager = new FavoritoManager();
});