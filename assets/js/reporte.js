// assets/js/reporte.js - Funciones JavaScript para reportes (SIN CONFIRMACIÓN)
var chartVentas = null;
var chartCategoria = null;
var chartComparativa = null;
var vistaGraficoActual = 'diaria';
var tipoGraficoActual = 'categoria';
var tipoComparativaActual = 'mes_vs_mes';

// Verificar si Chart ya está cargado
function waitForChartJS(callback) {
    if (typeof Chart !== 'undefined') {
        callback();
    } else {
        setTimeout(function() {
            waitForChartJS(callback);
        }, 100);
    }
}

// Función para limpiar todos los filtros
function limpiarFiltros() {
    // Redirigir a la página sin parámetros de filtro
    const url = new URL(window.location.href);
    url.searchParams.delete('periodo');
    url.searchParams.delete('fecha_inicio');
    url.searchParams.delete('fecha_fin');
    url.searchParams.set('periodo', 'hoy');
    
    window.location.href = url.toString();
}

// Función para resetear al período "Hoy"
function resetearAPeriodoHoy() {
    const url = new URL(window.location.href);
    url.searchParams.set('periodo', 'hoy');
    url.searchParams.delete('fecha_inicio');
    url.searchParams.delete('fecha_fin');
    
    window.location.href = url.toString();
}

// Mostrar mensaje temporal
function mostrarMensaje(mensaje, tipo = 'info') {
    // Crear elemento de mensaje
    const mensajeDiv = document.createElement('div');
    mensajeDiv.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
    mensajeDiv.style.cssText = 'top: 20px; right: 20px; z-index: 1050; max-width: 400px;';
    mensajeDiv.innerHTML = `
        ${mensaje}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Agregar al body
    document.body.appendChild(mensajeDiv);
    
    // Auto-eliminar después de 3 segundos
    setTimeout(() => {
        if (mensajeDiv.parentNode) {
            mensajeDiv.parentNode.removeChild(mensajeDiv);
        }
    }, 3000);
}

// Inicializar todo cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando reporte.js');
    
    // Inicializar tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            html: true
        });
    });
    
    // Configurar fechas por defecto
    const fechaInicio = document.getElementById('fecha_inicio');
    const fechaFin = document.getElementById('fecha_fin');
    
    if (fechaInicio && !fechaInicio.value) {
        fechaInicio.value = new Date().toISOString().split('T')[0];
    }
    
    if (fechaFin && !fechaFin.value) {
        fechaFin.value = new Date().toISOString().split('T')[0];
    }
    
    // Validar fechas
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
    
    // Configurar filtros de período
    const periodoSelect = document.getElementById('periodo');
    if (periodoSelect) {
        periodoSelect.addEventListener('change', function() {
            toggleCustomDates();
        });
    }
    
    // Agregar evento de limpieza rápida con tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            limpiarFiltros();
        }
    });
    
    // Esperar a que Chart.js esté disponible
    waitForChartJS(function() {
        console.log('Chart.js cargado, inicializando gráficos...');
        // Inicializar gráficos con retardo para asegurar que el DOM esté listo
        setTimeout(function() {
            inicializarGraficos();
        }, 200);
    });
    
    // Inicializar navegación rápida
    inicializarNavegacionRapida();
});

// Inicializar navegación rápida con scroll suave
function inicializarNavegacionRapida() {
    // Agregar scroll suave a los enlaces del menú de navegación
    document.querySelectorAll('nav.nav-pills a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                // Remover clase active de todos los enlaces
                document.querySelectorAll('nav.nav-pills a').forEach(link => {
                    link.classList.remove('active');
                });
                
                // Agregar clase active al enlace clickeado
                this.classList.add('active');
                
                // Scroll suave al elemento
                window.scrollTo({
                    top: targetElement.offsetTop - 100,
                    behavior: 'smooth'
                });
                
                // Actualizar URL sin recargar la página
                history.pushState(null, null, targetId);
            }
        });
    });
    
    // Observador para cambiar el menú activo al hacer scroll
    const sections = document.querySelectorAll('[id^="top-"], [id^="perdidas-"], [id^="metodos-"], [id^="analisis-"]');
    
    if (sections.length > 0) {
        const observerOptions = {
            root: null,
            rootMargin: '-100px 0px -100px 0px',
            threshold: 0.1
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const id = entry.target.id;
                    document.querySelectorAll('nav.nav-pills a').forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${id}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }, observerOptions);
        
        sections.forEach(section => {
            observer.observe(section);
        });
    }
    
    // Marcar el primer enlace como activo al cargar
    if (window.location.hash) {
        const activeLink = document.querySelector(`nav.nav-pills a[href="${window.location.hash}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    } else {
        const firstLink = document.querySelector('nav.nav-pills a');
        if (firstLink) {
            firstLink.classList.add('active');
        }
    }
}

// Inicializar todos los gráficos
function inicializarGraficos() {
    console.log('Inicializando gráficos...');
    
    // Destruir gráficos existentes si los hay
    if (chartVentas) {
        try {
            chartVentas.destroy();
        } catch (e) {
            console.log('Error al destruir chartVentas:', e);
        }
        chartVentas = null;
    }
    
    if (chartCategoria) {
        try {
            chartCategoria.destroy();
        } catch (e) {
            console.log('Error al destruir chartCategoria:', e);
        }
        chartCategoria = null;
    }
    
    if (chartComparativa) {
        try {
            chartComparativa.destroy();
        } catch (e) {
            console.log('Error al destruir chartComparativa:', e);
        }
        chartComparativa = null;
    }
    
    // Limpiar canvas
    const ventasCanvas = document.getElementById('ventasChart');
    const categoriaCanvas = document.getElementById('categoriaChart');
    const comparativaCanvas = document.getElementById('comparativaChart');
    
    if (ventasCanvas) {
        const ctx = ventasCanvas.getContext('2d');
        ctx.clearRect(0, 0, ventasCanvas.width, ventasCanvas.height);
    }
    
    if (categoriaCanvas) {
        const ctx = categoriaCanvas.getContext('2d');
        ctx.clearRect(0, 0, categoriaCanvas.width, categoriaCanvas.height);
    }
    
    if (comparativaCanvas) {
        const ctx = comparativaCanvas.getContext('2d');
        ctx.clearRect(0, 0, comparativaCanvas.width, comparativaCanvas.height);
    }
    
    // Crear nuevos gráficos
    try {
        chartVentas = crearGraficoVentas(vistaGraficoActual);
        chartCategoria = crearGraficoCategoria(tipoGraficoActual);
        chartComparativa = crearGraficoComparativa(tipoComparativaActual);
        console.log('Gráficos inicializados correctamente');
    } catch (error) {
        console.error('Error al inicializar gráficos:', error);
    }
}

// Cambiar vista del gráfico de ventas
function cambiarVistaGrafico(tipo) {
    vistaGraficoActual = tipo;
    
    // Actualizar botones activos
    const buttons = document.querySelectorAll('.btn-group .btn-outline-light');
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Actualizar gráfico
    if (chartVentas) {
        try {
            chartVentas.destroy();
        } catch (e) {
            console.log('Error al destruir chartVentas:', e);
        }
    }
    chartVentas = crearGraficoVentas(tipo);
}

// Cambiar tipo de gráfico de análisis
function cambiarTipoGrafico(tipo) {
    tipoGraficoActual = tipo;
    
    // Actualizar botones activos
    const buttons = document.querySelectorAll('.btn-group .btn-outline-light');
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Actualizar gráfico
    if (chartCategoria) {
        try {
            chartCategoria.destroy();
        } catch (e) {
            console.log('Error al destruir chartCategoria:', e);
        }
    }
    chartCategoria = crearGraficoCategoria(tipo);
}

// Cargar comparativa
function cargarComparativa(tipo) {
    tipoComparativaActual = tipo;
    
    // Actualizar botones activos
    const buttons = document.querySelectorAll('.btn-group .btn-outline-light');
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Actualizar gráfico
    if (chartComparativa) {
        try {
            chartComparativa.destroy();
        } catch (e) {
            console.log('Error al destruir chartComparativa:', e);
        }
    }
    chartComparativa = crearGraficoComparativa(tipo);
}

// Crear gráfico de ventas según tipo
function crearGraficoVentas(tipo) {
    const canvas = document.getElementById('ventasChart');
    if (!canvas) {
        console.error('No se encontró el canvas ventasChart');
        return null;
    }
    
    let datos = ventasDiariasData;
    let titulo = 'Tendencia de Ventas - ' + (ventasDiariasData.periodo || 'Últimos 30 días');
    
    switch(tipo) {
        case 'semanal':
            datos = ventasSemanalesData;
            titulo = 'Tendencia de Ventas - ' + (ventasSemanalesData.periodo || 'Últimas 12 semanas');
            break;
        case 'mensual':
            datos = ventasMensualesData;
            titulo = 'Tendencia de Ventas - ' + (ventasMensualesData.periodo || 'Últimos 12 meses');
            break;
        case 'trimestral':
            datos = ventasTrimestralesData;
            titulo = 'Tendencia de Ventas - ' + (ventasTrimestralesData.periodo || 'Últimos 4 años');
            break;
        case 'anual':
            datos = ventasAnualesData;
            titulo = 'Tendencia de Ventas - ' + (ventasAnualesData.periodo || 'Últimos 5 años');
            break;
    }
    
    // Verificar que haya datos
    if (!datos || !datos.labels || datos.labels.length === 0) {
        console.warn('No hay datos para el gráfico de ventas');
        datos = {
            labels: ['Sin datos'],
            datasets: [{
                label: 'Ventas',
                data: [0],
                borderColor: '#3A4A6B',
                backgroundColor: 'rgba(58, 74, 107, 0.1)'
            }]
        };
    }
    
    try {
        return new Chart(canvas, {
            type: 'line',
            data: datos,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: titulo,
                        color: '#1B202D',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#1B202D',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(27, 32, 45, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#3A4A6B',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Ventas Diarias' || 
                                    context.dataset.label === 'Ventas Semanales' ||
                                    context.dataset.label === 'Ventas Mensuales' ||
                                    context.dataset.label === 'Ventas Trimestrales' ||
                                    context.dataset.label === 'Ventas Anuales') {
                                    return `${context.dataset.label}: $${context.raw.toLocaleString('es-CO')}`;
                                } else if (context.dataset.label === 'Pedidos') {
                                    return `${context.dataset.label}: ${context.raw}`;
                                }
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#6c757d',
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-CO');
                            }
                        },
                        title: {
                            display: true,
                            text: 'Ventas ($)',
                            color: '#1B202D',
                            font: {
                                weight: '600'
                            }
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        },
                        ticks: {
                            color: '#000000',
                            callback: function(value) {
                                return value;
                            }
                        },
                        title: {
                            display: true,
                            text: 'Pedidos',
                            color: '#040707',
                            font: {
                                weight: '600'
                            }
                        },
                        position: 'right'
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#6c757d',
                            maxRotation: 45,
                            minRotation: 45
                        },
                        title: {
                            display: true,
                            text: 'Período',
                            color: '#1B202D',
                            font: {
                                weight: '600'
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                animations: {
                    tension: {
                        duration: 1000,
                        easing: 'linear'
                    }
                }
            }
        });
    } catch (error) {
        console.error('Error al crear gráfico de ventas:', error);
        return null;
    }
}

// Crear gráfico de categoría/género/color
function crearGraficoCategoria(tipo) {
    const canvas = document.getElementById('categoriaChart');
    if (!canvas) {
        console.error('No se encontró el canvas categoriaChart');
        return null;
    }
    
    let datos = categoriaData;
    let titulo = 'Análisis por Categoría - Período seleccionado';
    
    switch(tipo) {
        case 'genero':
            datos = generoData;
            titulo = 'Análisis por Género - Período seleccionado';
            break;
        case 'color':
            datos = colorData;
            titulo = 'Análisis por Color - Período seleccionado';
            break;
    }
    
    // Verificar que haya datos
    if (!datos || !datos.labels || datos.labels.length === 0) {
        console.warn('No hay datos para el gráfico de categoría');
        datos = {
            labels: ['Sin datos'],
            datasets: [{
                data: [100],
                backgroundColor: ['#6c757d']
            }]
        };
    }
    
    try {
        return new Chart(canvas, {
            type: 'doughnut',
            data: datos,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: titulo,
                        color: '#1B202D',
                        font: {
                            size: 16,
                            weight: 'bold'
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    },
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            color: '#1B202D',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 11
                            },
                            padding: 15,
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(27, 32, 45, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#3A4A6B',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                return `${label}: $${value.toLocaleString('es-CO')} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%',
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        });
    } catch (error) {
        console.error('Error al crear gráfico de categoría:', error);
        return null;
    }
}

// Crear gráfico de comparativa
function crearGraficoComparativa(tipo) {
    const canvas = document.getElementById('comparativaChart');
    if (!canvas) {
        console.error('No se encontró el canvas comparativaChart');
        return null;
    }
    
    let datos = comparativaData && comparativaData['mes_vs_mes'] ? comparativaData['mes_vs_mes'] : null;
    let titulo = 'Comparativa Mensual';
    
    switch(tipo) {
        case 'dia_vs_dia':
            // Datos simulados para día vs día
            datos = {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Semana Actual',
                    data: [12000, 19000, 30000, 50000, 20000, 30000, 45000],
                    backgroundColor: '#3A4A6B',
                    borderColor: '#2A3448',
                    borderWidth: 2
                }, {
                    label: 'Semana Pasada',
                    data: [10000, 15000, 25000, 45000, 18000, 28000, 40000],
                    backgroundColor: '#4A5B7D',
                    borderColor: '#3A4A6B',
                    borderWidth: 2
                }]
            };
            titulo = 'Comparativa Día vs Día';
            break;
        case 'semana_vs_semana':
            datos = {
                labels: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
                datasets: [{
                    label: 'Mes Actual',
                    data: [120000, 190000, 300000, 500000],
                    backgroundColor: '#3A4A6B',
                    borderColor: '#2A3448',
                    borderWidth: 2
                }, {
                    label: 'Mes Pasado',
                    data: [100000, 150000, 250000, 450000],
                    backgroundColor: '#4A5B7D',
                    borderColor: '#3A4A6B',
                    borderWidth: 2
                }]
            };
            titulo = 'Comparativa Semana vs Semana';
            break;
        case 'mes_vs_mes':
            titulo = 'Comparativa Mensual';
            break;
        case 'ano_vs_ano':
            datos = comparativaData && comparativaData['ano_vs_ano'] ? comparativaData['ano_vs_ano'] : null;
            titulo = 'Comparativa Anual';
            break;
    }
    
    // Verificar que haya datos
    if (!datos || !datos.labels || datos.labels.length === 0) {
        console.warn('No hay datos para el gráfico de comparativa');
        datos = {
            labels: ['Sin datos'],
            datasets: [{
                label: 'Ventas',
                data: [0],
                backgroundColor: '#3A4A6B',
                borderColor: '#2A3448'
            }]
        };
    }
    
    try {
        return new Chart(canvas, {
            type: 'bar',
            data: datos,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: titulo,
                        color: '#1B202D',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            color: '#1B202D',
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(27, 32, 45, 0.9)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#3A4A6B',
                        borderWidth: 1,
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: $${context.raw.toLocaleString('es-CO')}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#6c757d',
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-CO');
                            }
                        },
                        title: {
                            display: true,
                            text: 'Ventas ($)',
                            color: '#1B202D',
                            font: {
                                weight: '600'
                            }
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            color: '#6c757d'
                        },
                        title: {
                            display: true,
                            text: 'Período',
                            color: '#1B202D',
                            font: {
                                weight: '600'
                            }
                        }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    } catch (error) {
        console.error('Error al crear gráfico de comparativa:', error);
        return null;
    }
}

// Función para cambiar período rápidamente
function cambiarPeriodo(periodo) {
    const url = new URL(window.location.href);
    url.searchParams.set('periodo', periodo);
    
    // Si no es personalizado, limpiar fechas personalizadas
    if (periodo !== 'personalizado') {
        url.searchParams.delete('fecha_inicio');
        url.searchParams.delete('fecha_fin');
    }
    
    window.location.href = url.toString();
}

// Función para mostrar/ocultar fechas personalizadas
function toggleCustomDates() {
    const periodo = document.getElementById('periodo');
    const customDates = document.querySelectorAll('.custom-date');
    
    if (!periodo) return;
    
    customDates.forEach(el => {
        el.style.display = periodo.value === 'personalizado' ? 'block' : 'none';
    });
}

// Animación para números que cambian
function animarNumero(elemento, nuevoValor, duracion = 1000) {
    const inicio = parseFloat(elemento.textContent.replace(/[^0-9.-]+/g, ''));
    const diferencia = nuevoValor - inicio;
    const incremento = diferencia / (duracion / 16);
    
    let valorActual = inicio;
    const intervalo = setInterval(() => {
        valorActual += incremento;
        
        if ((incremento > 0 && valorActual >= nuevoValor) || 
            (incremento < 0 && valorActual <= nuevoValor)) {
            valorActual = nuevoValor;
            clearInterval(intervalo);
        }
        
        elemento.textContent = '$' + valorActual.toLocaleString('es-CO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }, 16);
}

// Navegar a sección específica
function navegarASeccion(idSeccion) {
    const elemento = document.getElementById(idSeccion);
    if (elemento) {
        // Remover activo de todos los botones
        document.querySelectorAll('#menu-navegacion-rapida .btn').forEach(btn => {
            btn.classList.remove('active');
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-outline-secondary');
        });
        
        // Agregar activo al botón clickeado
        event.target.classList.remove('btn-outline-secondary');
        event.target.classList.add('btn-secondary', 'active');
        
        // Scroll suave a la sección
        elemento.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
        
        // Agregar efecto visual a la tarjeta
        elemento.classList.add('card-highlight');
        setTimeout(() => {
            elemento.classList.remove('card-highlight');
        }, 2000);
    }
}

// Exportar funciones para uso global
window.ReporteUtils = {
    cambiarPeriodo: cambiarPeriodo,
    animarNumero: animarNumero,
    cambiarVistaGrafico: cambiarVistaGrafico,
    cambiarTipoGrafico: cambiarTipoGrafico,
    cargarComparativa: cargarComparativa,
    navegarASeccion: navegarASeccion,
    limpiarFiltros: limpiarFiltros,
    resetearAPeriodoHoy: resetearAPeriodoHoy,
    inicializarNavegacionRapida: inicializarNavegacionRapida
};