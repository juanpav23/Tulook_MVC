/**
 * layout_admin.js
 * Funcionalidades del layout de administración
 */

class LayoutAdmin {
    constructor() {
        this.sidebarToggle = null;
        this.sidebar = null;
        this.contentArea = null;
        this.sidebarOverlay = null;
        this.isDesktop = null;
        this.sidebarCollapsed = false;
        this.resizeTimer = null;
        this.timeUpdateInterval = null;
        this.dateTimeUpdateInterval = null;
        
        this.init();
    }

    init() {
        console.log('Admin layout JS cargado');
        
        // Elementos del DOM
        this.sidebarToggle = document.getElementById('sidebarToggle');
        this.sidebar = document.getElementById('mainSidebar');
        this.contentArea = document.getElementById('mainContentArea');
        this.sidebarOverlay = document.getElementById('sidebarOverlay');
        this.isDesktop = window.innerWidth >= 992;
        
        // Inicializar eventos
        this.initEventListeners();
        
        // Cargar estado guardado
        this.loadSavedState();
        
        // Inicializar estado
        this.updateSidebarState();
        
        // Inicializar actualizadores de tiempo (INMEDIATAMENTE)
        this.initTimeUpdates();
        
        // Ajustar topbar
        this.adjustTopbar();
    }

    initEventListeners() {
        // Evento para toggle sidebar
        if (this.sidebarToggle) {
            this.sidebarToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                this.toggleSidebar();
            });
        }
        
        // Evento para overlay en móviles
        if (this.sidebarOverlay) {
            this.sidebarOverlay.addEventListener('click', (e) => {
                e.stopPropagation();
                this.closeMobileSidebar();
            });
        }
        
        // Cerrar sidebar al hacer clic en un enlace (solo móviles)
        if (!this.isDesktop) {
            document.querySelectorAll('.sidebar a').forEach(link => {
                link.addEventListener('click', () => this.closeMobileSidebar());
            });
        }
        
        // Manejar redimensionamiento
        window.addEventListener('resize', () => {
            clearTimeout(this.resizeTimer);
            this.resizeTimer = setTimeout(() => {
                const newIsDesktop = window.innerWidth >= 992;
                
                if (newIsDesktop !== this.isDesktop) {
                    // Recargar la página para evitar problemas
                    location.reload();
                }
            }, 150);
        });
        
        // Ajustar topbar al redimensionar
        window.addEventListener('resize', () => this.adjustTopbar());
        
        // Efecto de pulso para el timer
        setInterval(() => {
            const timerElement = document.getElementById('sessionTimer');
            if (timerElement) {
                timerElement.classList.remove('time-animation');
                void timerElement.offsetWidth;
                timerElement.classList.add('time-animation');
            }
        }, 60000);
    }

    toggleSidebar() {
        this.sidebarCollapsed = !this.sidebarCollapsed;
        this.updateSidebarState();
    }

    updateSidebarState() {
        if (this.isDesktop) {
            // Modo desktop - colapsar/expandir
            if (this.sidebarCollapsed) {
                this.sidebar.classList.add('collapsed');
                this.contentArea.classList.add('expanded');
                this.sidebarToggle.classList.add('collapsed');
                this.sidebarToggle.title = "Mostrar menú";
                this.sidebarOverlay.classList.remove('active');
            } else {
                this.sidebar.classList.remove('collapsed');
                this.contentArea.classList.remove('expanded');
                this.sidebarToggle.classList.remove('collapsed');
                this.sidebarToggle.title = "Ocultar menú";
                this.sidebarOverlay.classList.remove('active');
            }
        } else {
            // Modo móvil - mostrar/ocultar con overlay
            if (this.sidebarCollapsed) {
                this.sidebar.classList.remove('collapsed');
                this.sidebar.classList.add('active');
                this.sidebarOverlay.classList.add('active');
                this.sidebarToggle.title = "Cerrar menú";
            } else {
                this.sidebar.classList.remove('active');
                this.sidebar.classList.add('collapsed');
                this.sidebarOverlay.classList.remove('active');
                this.sidebarToggle.title = "Abrir menú";
            }
        }
        
        // Guardar estado en localStorage
        localStorage.setItem('sidebarCollapsed', this.sidebarCollapsed);
    }

    closeMobileSidebar() {
        if (!this.isDesktop) {
            this.sidebarCollapsed = false;
            this.updateSidebarState();
        }
    }

    loadSavedState() {
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState !== null) {
            this.sidebarCollapsed = savedState === 'true';
        } else {
            // Por defecto, en móviles el sidebar está cerrado
            this.sidebarCollapsed = !this.isDesktop;
        }
    }

    initTimeUpdates() {
        // Limpiar intervalos anteriores si existen
        if (this.timeUpdateInterval) {
            clearInterval(this.timeUpdateInterval);
        }
        if (this.dateTimeUpdateInterval) {
            clearInterval(this.dateTimeUpdateInterval);
        }
        
        // Actualizar inmediatamente al cargar
        this.updateConnectedTime();
        this.updateCurrentDateTime();
        
        // Actualizar cada segundo (1000ms)
        this.timeUpdateInterval = setInterval(() => this.updateConnectedTime(), 1000);
        this.dateTimeUpdateInterval = setInterval(() => this.updateCurrentDateTime(), 1000);
    }

    updateConnectedTime() {
        const loginTime = window.LOGIN_TIME || null;
        
        if (!loginTime) {
            console.log('LOGIN_TIME no está definido');
            return;
        }
        
        const ahora = Math.floor(Date.now() / 1000);
        const diffSec = ahora - loginTime;
        
        const horas = Math.floor(diffSec / 3600);
        const minutos = Math.floor((diffSec % 3600) / 60);
        const segundos = diffSec % 60;
        
        const tiempoFormateado = `${horas}h ${minutos}m ${segundos}s`;
        
        const sessionTimeDisplay = document.getElementById('sessionTimeDisplay');
        const sidebarSessionTime = document.getElementById('sidebarSessionTime');
        
        if (sessionTimeDisplay) {
            sessionTimeDisplay.textContent = tiempoFormateado;
        }
        if (sidebarSessionTime) {
            sidebarSessionTime.textContent = tiempoFormateado;
        }
    }

    updateCurrentDateTime() {
        const ahora = new Date();
        
        // Formatear fecha: dd/mm/yyyy HH:mm:ss
        const dia = String(ahora.getDate()).padStart(2, '0');
        const mes = String(ahora.getMonth() + 1).padStart(2, '0');
        const anio = ahora.getFullYear();
        const horas = String(ahora.getHours()).padStart(2, '0');
        const minutos = String(ahora.getMinutes()).padStart(2, '0');
        const segundos = String(ahora.getSeconds()).padStart(2, '0');
        
        const fechaHoraFormateada = `${dia}/${mes}/${anio} ${horas}:${minutos}:${segundos}`;
        const currentDateTime = document.getElementById('currentDateTime');
        
        if (currentDateTime) {
            currentDateTime.textContent = fechaHoraFormateada;
        }
    }

    adjustTopbar() {
        const topbar = document.querySelector('.topbar');
        const topbarLeft = document.querySelector('.topbar-left');
        const topbarRight = document.querySelector('.topbar-right');
        
        if (!topbar || !topbarLeft || !topbarRight) return;
        
        const availableWidth = topbar.offsetWidth - 40; // Restar padding
        const leftWidth = topbarLeft.offsetWidth;
        const rightWidth = topbarRight.offsetWidth;
        
        // Si el contenido es demasiado ancho, reducir tamaños
        if (leftWidth + rightWidth > availableWidth) {
            const userName = document.querySelector('.user-name');
            const roleBadges = document.querySelectorAll('.user-role-badge, .session-time');
            const sessionDate = document.querySelector('.session-date');
            
            if (userName) userName.style.fontSize = '0.85rem';
            
            roleBadges.forEach(el => {
                el.style.fontSize = '0.7rem';
                el.style.padding = '3px 8px';
            });
            
            if (sessionDate) sessionDate.style.fontSize = '0.65rem';
        }
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.layoutAdmin = new LayoutAdmin();
});