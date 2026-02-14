<?php
// views/admin/reporte/productos.php - VERSIÓN CORREGIDA
// ARREGLADO: Formato de números y tamaño igual de gráficos
?>
<div class="container-fluid">
    <!-- Encabezado con indicador de HISTORIAL COMPLETO -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <h2 class="mb-0"><i class="fas fa-box-open me-2" style="color: var(--primary-dark);"></i>Reporte de Productos</h2>
                <span class="badge" style="background-color: var(--primary); color: white; padding: 8px 16px; font-size: 0.9rem;">
                    <i class="fas fa-history me-1"></i> HISTORIAL COMPLETO
                </span>
            </div>
            <p class="text-muted mb-0">
                <i class="fas fa-calendar-alt me-1" style="color: var(--primary);"></i> 
                <strong>Todos los productos desde el inicio del sistema</strong>
            </p>
            <p class="text-muted mb-0 mt-1">
                <i class="fas fa-info-circle me-1" style="color: var(--primary);"></i>
                Mostrando <strong><?= number_format(count($productos), 0, ',', '.') ?></strong> productos con ventas registradas
            </p>
        </div>
        <div class="d-flex gap-2 mt-2 mt-sm-0">
            <a href="<?= BASE_URL ?>?c=ReporteVenta&a=index" class="btn" style="background-color: var(--primary-dark); color: white; border: none;">
                <i class="fas fa-arrow-left me-1"></i> Volver a Reportes
            </a>
        </div>
    </div>

    <!-- Tarjetas de métricas GLOBALES -->
    <div class="row mb-4 g-3">
        <div class="col-md-4">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--primary-dark);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">PRODUCTOS VENDIDOS</small>
                        <h2 class="card-text mt-1" style="font-size: 2.2rem; font-weight: 700; color: var(--primary-dark);">
                            <?= number_format($totales['productos_vendidos'] ?? 0, 0, ',', '.') ?>
                        </h2>
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-box me-1" style="color: var(--primary);"></i> Unidades totales
                        </small>
                    </div>
                    <div style="background: rgba(27, 32, 45, 0.08); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-cubes" style="font-size: 1.8rem; color: var(--primary-dark);"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--primary);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">VENTAS TOTALES</small>
                        <h3 class="card-text mt-1" style="font-size: 1.8rem; font-weight: 700; color: var(--primary); word-break: break-word;">
                            $<?= number_format($totales['ventas_totales'] ?? 0, 2, ',', '.') ?>
                        </h3>
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-chart-line me-1" style="color: var(--primary);"></i> Historial completo
                        </small>
                    </div>
                    <div style="background: rgba(42, 52, 72, 0.08); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-dollar-sign" style="font-size: 1.8rem; color: var(--primary);"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card stats-card-pedido h-100" style="border-left: 4px solid var(--secondary);">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <small style="color: var(--gray-dark); text-transform: uppercase; font-size: 0.7rem; font-weight: 600;">PRODUCTOS ÚNICOS</small>
                        <h2 class="card-text mt-1" style="font-size: 2.2rem; font-weight: 700; color: var(--secondary);">
                            <?= number_format(count($productos), 0, ',', '.') ?>
                        </h2>
                        <small style="color: var(--gray-dark);">
                            <i class="fas fa-tag me-1" style="color: var(--secondary);"></i> Diferentes artículos
                        </small>
                    </div>
                    <div style="background: rgba(74, 91, 125, 0.08); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-box" style="font-size: 1.8rem; color: var(--secondary);"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de Categoría y Ordenamiento -->
    <div class="card mb-4">
        <div class="card-header" style="background-color: var(--primary-dark); color: white;">
            <h5 class="mb-0 text-white"><i class="fas fa-filter me-2"></i>Filtrar y Ordenar Productos</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="c" value="ReporteVenta">
                <input type="hidden" name="a" value="productos_todos">
                
                <div class="col-md-5">
                    <label for="categoria" class="form-label" style="color: var(--primary-dark); font-weight: 600;">Categoría:</label>
                    <select class="form-select" id="categoria" name="categoria" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['ID_Categoria'] ?>" <?= ($categoria ?? '') == $cat['ID_Categoria'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['N_Categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-5">
                    <label for="orden" class="form-label" style="color: var(--primary-dark); font-weight: 600;">Ordenar por:</label>
                    <select class="form-select" id="orden" name="orden" onchange="this.form.submit()">
                        <option value="ventas_desc" <?= ($orden ?? 'ventas_desc') === 'ventas_desc' ? 'selected' : '' ?>>Ventas (Mayor a menor)</option>
                        <option value="ventas_asc" <?= ($orden ?? '') === 'ventas_asc' ? 'selected' : '' ?>>Ventas (Menor a mayor)</option>
                        <option value="cantidad_desc" <?= ($orden ?? '') === 'cantidad_desc' ? 'selected' : '' ?>>Cantidad (Mayor a menor)</option>
                        <option value="cantidad_asc" <?= ($orden ?? '') === 'cantidad_asc' ? 'selected' : '' ?>>Cantidad (Menor a mayor)</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <div class="d-flex justify-content-end">
                        <span class="badge p-3" style="background-color: var(--primary-dark); color: white; font-size: 0.9rem; width: 100%;">
                            <i class="fas fa-box me-1"></i> <?= number_format($totales['productos_vendidos'] ?? 0, 0, ',', '.') ?> und
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de TODOS los productos -->
    <div class="card">
        <div class="card-header" style="background-color: var(--primary-dark); color: white;">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="mb-0 text-white"><i class="fas fa-list me-2"></i>Historial Completo de Productos Vendidos</h5>
                <span class="badge p-2 mt-2 mt-sm-0" style="background-color: var(--primary); color: white; font-size: 0.9rem;">
                    <i class="fas fa-database me-1"></i> Total: <?= number_format(count($productos), 0, ',', '.') ?> productos
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($productos)): ?>
                <div class="table-container">
                    <table class="table table-striped table-hover mb-0">
                        <thead style="background-color: var(--primary-dark); color: white;">
                            <tr>
                                <th class="ps-3">Producto</th>
                                <th>Categoría</th>
                                <th>Género</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Promedio</th>
                                <th class="text-end">Ventas Brutas</th>
                                <th class="text-end">Descuentos</th>
                                <th class="text-end">Ventas Netas</th>
                                <th class="text-end pe-3">Pedidos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalVentas = 0;
                            $totalDescuentos = 0;
                            $totalNeto = 0;
                            $totalCantidad = 0;
                            $totalPedidos = 0;
                            
                            foreach ($productos as $producto): 
                                $totalVentas += $producto['Total_Ventas'] ?? 0;
                                $totalDescuentos += $producto['Descuentos_Otorgados'] ?? 0;
                                $totalNeto += $producto['Ventas_Netas'] ?? 0;
                                $totalCantidad += $producto['Cantidad_Vendida'] ?? 0;
                                $totalPedidos += $producto['Pedidos'] ?? 0;
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold" style="color: var(--primary-dark);"><?= htmlspecialchars($producto['Producto'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: var(--secondary); color: white;">
                                        <?= htmlspecialchars($producto['Categoria'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge" style="background-color: var(--light); color: white;">
                                        <?= htmlspecialchars($producto['Genero'] ?? 'N/A') ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold">
                                    <?= number_format($producto['Cantidad_Vendida'] ?? 0, 0, ',', '.') ?>
                                </td>
                                <td class="text-end">
                                    $<?= number_format($producto['Precio_Promedio'] ?? 0, 2, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold" style="color: var(--primary-dark);">
                                    $<?= number_format($producto['Total_Ventas'] ?? 0, 2, ',', '.') ?>
                                </td>
                                <td class="text-end" style="color: var(--danger);">
                                    <?php if (($producto['Descuentos_Otorgados'] ?? 0) > 0): ?>
                                        -$<?= number_format($producto['Descuentos_Otorgados'], 2, ',', '.') ?>
                                    <?php else: ?>
                                        <span style="color: var(--gray-dark);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-bold" style="color: var(--primary);">
                                    $<?= number_format($producto['Ventas_Netas'] ?? 0, 2, ',', '.') ?>
                                </td>
                                <td class="text-end pe-3">
                                    <span class="badge" style="background-color: var(--primary-dark); color: white;">
                                        <?= number_format($producto['Pedidos'] ?? 0, 0, ',', '.') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot style="background-color: rgba(42, 52, 72, 0.05);">
                            <tr>
                                <th colspan="3" class="ps-3 text-end" style="color: var(--primary-dark);">TOTALES HISTÓRICOS:</th>
                                <th class="text-end" style="color: var(--primary-dark);"><?= number_format($totalCantidad, 0, ',', '.') ?></th>
                                <th class="text-end" style="color: var(--gray-dark);">-</th>
                                <th class="text-end" style="color: var(--primary-dark);">$<?= number_format($totalVentas, 2, ',', '.') ?></th>
                                <th class="text-end" style="color: var(--danger);">-$<?= number_format($totalDescuentos, 2, ',', '.') ?></th>
                                <th class="text-end" style="color: var(--primary);">$<?= number_format($totalNeto, 2, ',', '.') ?></th>
                                <th class="text-end pe-3" style="color: var(--primary-dark);"><?= number_format($totalPedidos, 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- GRÁFICOS - CORREGIDOS: MISMO TAMAÑO Y FORMATO DE NÚMEROS -->
                <div class="row mt-4 mx-2">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header" style="background-color: var(--primary-dark); color: white;">
                                <h6 class="mb-0 text-white"><i class="fas fa-chart-bar me-2"></i>Top 5 Productos por Ventas Netas</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 250px;">
                                <canvas id="topProductosChart" style="width:100%; height: 220px; max-height: 220px;"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100">
                            <div class="card-header" style="background-color: var(--primary-dark); color: white;">
                                <h6 class="mb-0 text-white"><i class="fas fa-chart-pie me-2"></i>Distribución por Categoría</h6>
                            </div>
                            <div class="card-body d-flex align-items-center justify-content-center" style="min-height: 250px;">
                                <canvas id="categoriaProductosChart" style="width:100%; height: 220px; max-height: 220px;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x mb-3" style="color: var(--primary);"></i>
                    <h5 style="color: var(--primary-dark);">No hay productos vendidos</h5>
                    <p style="color: var(--gray-dark);">No se encontraron productos con ventas en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/reporte.css">

<!-- JS y Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // DATOS DIRECTOS DE PHP
    // ============================================
    const productos = <?= json_encode($productos) ?>;
    
    if (!productos || productos.length === 0) {
        console.log('No hay productos para mostrar gráficos');
        return;
    }
    
    // ============================================
    // TOP 5 PRODUCTOS - CON FORMATO DE NÚMEROS
    // ============================================
    const top5 = productos.slice(0, 5);
    const labelsTop = top5.map(p => {
        let nombre = p.Producto || 'Producto';
        return nombre.length > 20 ? nombre.substring(0, 20) + '...' : nombre;
    });
    const dataTop = top5.map(p => p.Ventas_Netas || 0);
    
    const ctx1 = document.getElementById('topProductosChart');
    if (ctx1) {
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: labelsTop,
                datasets: [{
                    label: 'Ventas Netas',
                    data: dataTop,
                    backgroundColor: '#2A3448',
                    borderColor: '#1B202D',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let value = context.raw || 0;
                                // FORMATO CORREGIDO: 400.000,00
                                return 'Ventas Netas: $' + value.toLocaleString('es-CO', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            // FORMATO CORREGIDO: 400.000
                            callback: function(value) {
                                return '$' + value.toLocaleString('es-CO', {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 0
                                });
                            }
                        }
                    }
                }
            }
        });
    }
    
    // ============================================
    // DISTRIBUCIÓN POR CATEGORÍA - CON FORMATO DE NÚMEROS
    // ============================================
    const categorias = {};
    productos.forEach(p => {
        const cat = p.Categoria || 'Sin categoría';
        const ventas = p.Ventas_Netas || 0;
        categorias[cat] = (categorias[cat] || 0) + ventas;
    });
    
    const labelsCat = Object.keys(categorias);
    const dataCat = Object.values(categorias);
    
    // SOLO COLORES DEL :root
    const coloresRoot = [
        '#1B202D', // primary-dark
        '#2A3448', // primary
        '#3A4A6B', // primary-light
        '#4A5B7D', // secondary
        '#5B6E8F', // light
        '#4A5B7D', // info
        '#4A5B7D', // warning
        '#1B202D', // danger
        '#4A5B7D', // pink
        '#5B6E8F'  // light
    ];
    
    const ctx2 = document.getElementById('categoriaProductosChart');
    if (ctx2 && labelsCat.length > 0) {
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: labelsCat,
                datasets: [{
                    data: dataCat,
                    backgroundColor: coloresRoot.slice(0, labelsCat.length),
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            color: '#1B202D',
                            font: { size: 11 },
                            boxWidth: 12,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                // FORMATO CORREGIDO: 400.000,00
                                return `${label}: $${value.toLocaleString('es-CO', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })} (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }
});
</script>