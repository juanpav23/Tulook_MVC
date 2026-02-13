/**
 * dashboard.js
 * Funcionalidades del panel de administración
 */

class DashboardManager {
    constructor() {
        this.backToTopBtn = null;
        this.init();
    }

    init() {
        console.log('Dashboard JS cargado');
        this.initEventListeners();
        this.initScrollBehavior();
    }

    initEventListeners() {
        // Enlaces internos con scroll suave
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                const href = anchor.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    this.scrollToElement(href.substring(1));
                }
            });
        });

        // Referencia al botón de volver arriba
        this.backToTopBtn = document.querySelector('.back-to-top-btn');
        
        // Mostrar/ocultar botón de volver arriba al hacer scroll
        if (this.backToTopBtn) {
            window.addEventListener('scroll', () => {
                if (window.scrollY > 300) {
                    this.backToTopBtn.style.opacity = '1';
                    this.backToTopBtn.style.visibility = 'visible';
                } else {
                    this.backToTopBtn.style.opacity = '0';
                    this.backToTopBtn.style.visibility = 'hidden';
                }
            });
        }
    }

    initScrollBehavior() {
        // Inicializar el estado del botón de volver arriba
        if (this.backToTopBtn && window.scrollY > 300) {
            this.backToTopBtn.style.opacity = '1';
            this.backToTopBtn.style.visibility = 'visible';
        }
    }

    scrollToElement(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }

    scrollToTop() {
        window.scrollTo({ 
            top: 0, 
            behavior: 'smooth' 
        });
    }

    // Métodos utilitarios para acceso desde HTML (onclick)
    static scrollToElementStatic(elementId) {
        const manager = window.dashboardManager;
        if (manager) {
            manager.scrollToElement(elementId);
        } else {
            const element = document.getElementById(elementId);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    static scrollToTopStatic() {
        const manager = window.dashboardManager;
        if (manager) {
            manager.scrollToTop();
        } else {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.dashboardManager = new DashboardManager();
    
    // Hacer los métodos estáticos disponibles globalmente para onclick
    window.scrollToElement = DashboardManager.scrollToElementStatic;
    window.scrollToTop = DashboardManager.scrollToTopStatic;
});