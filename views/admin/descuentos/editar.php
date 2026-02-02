<?php
if (!isset($articulos)) $articulos = [];
if (!isset($productos)) $productos = [];
if (!isset($categorias)) $categorias = [];
$formErrors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? $descuento;
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);

// Determinar si es descuento automático (monto mínimo > 0)
$esAutomatico = ($formData['Monto_Minimo'] ?? 0) > 0;
?>
<!-- CSS Compartido -->
<link rel="stylesheet" href="assets/css/usuario.css">
<style>
    /* ESTILOS ESPECÍFICOS PARA EDITAR DESCUENTO */
    
    /* Corrección de iconos y colores amarillos */
    .fa-lightbulb.text-warning {
        color: var(--warning) !important;
    }
    
    .fa-bolt.text-warning {
        color: var(--warning) !important;
    }
    
    .fa-trophy.text-success {
        color: var(--success) !important;
    }
    
    .fa-globe.text-primary-light {
        color: var(--primary-light) !important;
    }
    
    .fa-user.text-warning {
        color: var(--warning) !important;
    }
    
    /* Botón warning con variable CSS */
    .btn-warning {
        background-color: var(--warning) !important;
        border-color: var(--warning) !important;
        color: white !important;
    }
    
    .btn-warning:hover {
        background-color: #0e1014 !important;
        border-color: #0e1014 !important;
        color: white !important;
    }
    
    /* Resto de estilos específicos... */
    .application-card {
        border: 2px solid !important;
        border-radius: var(--border-radius);
        transition: var(--transition);
    }
    
    .application-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--hover-shadow);
    }
    
    .border-info {
        border-color: var(--primary-light) !important;
    }
    
    .border-success {
        border-color: var(--success) !important;
    }
    
    .border-secondary {
        border-color: var(--secondary) !important;
    }
    
    .estadisticas-card {
        border-left: 4px solid;
        background: white;
    }
    
    .estadisticas-card.primary { 
        border-left-color: var(--primary-dark) !important; 
    }
    
    .estadisticas-card.success { 
        border-left-color: var(--success) !important; 
    }
    
    .estadisticas-card.info { 
        border-left-color: var(--primary-light) !important; 
    }
    
    .estadisticas-card.warning { 
        border-left-color: var(--warning) !important; 
    }
    
    .icon-circle {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }
    
    .icon-circle.bg-primary-dark { background-color: var(--primary-dark) !important; }
    .icon-circle.bg-success { background-color: var(--success) !important; }
    .icon-circle.bg-primary-light { background-color: var(--primary-light) !important; }
    .icon-circle.bg-warning { background-color: var(--warning) !important; }
    
    .bg-info.bg-opacity-10 {
        background-color: rgba(58, 74, 107, 0.1) !important;
    }
    
    .bg-success.bg-opacity-10 {
        background-color: rgba(30, 41, 68, 0.1) !important;
    }
    
    .bg-secondary.bg-opacity-10 {
        background-color: rgba(74, 91, 125, 0.1) !important;
    }
    
    .form-section {
        margin-bottom: 2rem;
    }
    
    .form-section-title {
        color: var(--primary-dark);
        font-weight: 700;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--gray-medium);
        font-family: 'Montserrat', sans-serif;
    }
    
    .required-field::after {
        content: " *";
        color: var(--danger);
    }
    
    .badge.bg-info {
        background-color: var(--primary-light) !important;
    }
    
    .text-info {
        color: var(--primary-light) !important;
    }
    
    /* Responsive específico */
    @media (max-width: 768px) {
        .form-section-title {
            font-size: 1.1rem;
        }
        
        .estadisticas-card .card-body {
            padding: 1rem;
        }
    }
    
    @media (max-width: 576px) {
        .row.mb-4 > div {
            margin-bottom: 1rem;
        }
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="mb-1">
                    <i class="fas fa-edit text-me-2"></i>
                    Editar Descuento
                </h2>
                <p class="text-muted mb-0">Modifica la información del descuento</p>
            </div>
            <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Volver a la lista
            </a>
        </div>
    </div>

    <!-- Estadísticas del Descuento -->
    <?php if (isset($descuento['estadisticas'])): ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card estadisticas-card primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small">Usos Globales</h6>
                            <h3 class="mb-0"><?= $descuento['Usos_Globales'] ?? 0 ?></h3>
                        </div>
                        <div class="icon-circle bg-primary-dark">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0 small">
                        <?php if ($descuento['Max_Usos_Global'] > 0): ?>
                            Límite: <?= $descuento['Max_Usos_Global'] ?>
                        <?php else: ?>
                            Sin límite
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card estadisticas-card info h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small">Usuarios Únicos</h6>
                            <h3 class="mb-0"><?= $descuento['estadisticas']['TotalUsuarios'] ?? 0 ?></h3>
                        </div>
                        <div class="icon-circle bg-primary-light">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0 small">
                        <?php if ($descuento['Max_Usos_Usuario'] > 0): ?>
                            Límite por usuario: <?= $descuento['Max_Usos_Usuario'] ?>
                        <?php else: ?>
                            Sin límite por usuario
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card estadisticas-card success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small">Promedio por Usuario</h6>
                            <h3 class="mb-0"><?= number_format($descuento['estadisticas']['PromedioUsosPorUsuario'] ?? 0, 1) ?></h3>
                        </div>
                        <div class="icon-circle bg-success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0 small">
                        Usos promedio por cada usuario
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card estadisticas-card warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-muted small">Máximo Usos</h6>
                            <h3 class="mb-0"><?= $descuento['estadisticas']['MaxUsosUsuario'] ?? 0 ?></h3>
                        </div>
                        <div class="icon-circle bg-warning">
                            <i class="fas fa-crown"></i>
                        </div>
                    </div>
                    <p class="text-muted mb-0 small">
                        Máximo usos por un solo usuario
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Alertas de Error -->
    <?php if (!empty($formErrors)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2 fs-5"></i>
                <div>
                    <strong class="me-2">Errores en el formulario:</strong>
                    Por favor corrige los siguientes problemas:
                </div>
            </div>
            <ul class="mb-0 mt-2">
                <?php foreach ($formErrors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Información del Descuento
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="descuentoForm">
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-tag me-2"></i>Información Básica
                            </h6>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        Código del Descuento
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary">
                                            <i class="fas fa-tag text-white"></i>
                                        </span>
                                        <input type="text" 
                                            class="form-control <?= isset($formErrors['codigo']) ? 'is-invalid' : '' ?>" 
                                            name="codigo" 
                                            value="<?= htmlspecialchars($formData['Codigo'] ?? '') ?>" 
                                            required
                                            placeholder="EJ: VERANO2024"
                                            maxlength="20"
                                            pattern="[A-Z0-9_]+"
                                            title="Solo mayúsculas, números y guiones bajos"
                                            oninput="this.value = this.value.toUpperCase()"
                                            id="inputCodigo"
                                            <?= $esAutomatico ? 'readonly' : '' ?>>
                                        <?php if (isset($formErrors['codigo'])): ?>
                                            <div class="invalid-feedback">
                                                <?= $formErrors['codigo'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text mt-2">
                                        <!-- CORREGIDO: Usar variable CSS -->
                                        <i class="fas fa-lightbulb text- me-1"></i>
                                        <?php if ($esAutomatico): ?>
                                            <span class="text-primary-light">Código generado automáticamente. No se puede editar.</span>
                                        <?php else: ?>
                                            Usa un código único y descriptivo. Solo mayúsculas, números y _
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        Tipo de Descuento
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary">
                                            <i class="fas fa-percentage text-white"></i>
                                        </span>
                                        <select class="form-select <?= isset($formErrors['tipo']) ? 'is-invalid' : '' ?>" name="tipo" required id="tipoDescuento">
                                            <option value="">Selecciona un tipo...</option>
                                            <option value="Porcentaje" <?= ($formData['Tipo'] ?? '') == 'Porcentaje' ? 'selected' : '' ?>>Porcentaje (%)</option>
                                            <option value="ValorFijo" <?= ($formData['Tipo'] ?? '') == 'ValorFijo' ? 'selected' : '' ?>>Valor Fijo ($)</option>
                                        </select>
                                        <?php if (isset($formErrors['tipo'])): ?>
                                            <div class="invalid-feedback">
                                                <?= $formErrors['tipo'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        Valor del Descuento
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary" id="simboloValor">
                                            <?= ($formData['Tipo'] ?? '') == 'Porcentaje' ? '%' : '$' ?>
                                        </span>
                                        <input type="number" 
                                               class="form-control <?= isset($formErrors['valor']) ? 'is-invalid' : '' ?>" 
                                               name="valor" 
                                               step="0.01" 
                                               min="0.01" 
                                               max="<?= ($formData['Tipo'] ?? '') == 'Porcentaje' ? '100' : '999999' ?>" 
                                               value="<?= htmlspecialchars($formData['Valor'] ?? '') ?>" 
                                               required
                                               placeholder="0.00"
                                               id="inputValor">
                                        <?php if (isset($formErrors['valor'])): ?>
                                            <div class="invalid-feedback">
                                                <?= $formErrors['valor'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text mt-2" id="textoAyudaValor">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <span id="textoAyuda">
                                            <?= ($formData['Tipo'] ?? '') == 'Porcentaje' ? 
                                                'Ingresa el porcentaje de descuento. Máximo 100%.' : 
                                                'Ingresa el valor fijo del descuento.' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        Aplicar a
                                    </label>
                                    <select class="form-select <?= isset($formErrors['aplicacion']) ? 'is-invalid' : '' ?>" name="tipo_aplicacion" id="tipo_aplicacion" required>
                                        <option value="">¿Dónde aplicar el descuento?</option>
                                        <option value="articulo" <?= isset($formData['ID_Articulo']) ? 'selected' : '' ?>>Artículo Específico</option>
                                        <option value="producto" <?= isset($formData['ID_Producto']) ? 'selected' : '' ?>>Producto/Variante Específica</option>
                                        <option value="categoria" <?= isset($formData['ID_Categoria']) ? 'selected' : '' ?>>Categoría Completa</option>
                                    </select>
                                    <?php if (isset($formErrors['aplicacion'])): ?>
                                        <div class="invalid-feedback">
                                            <?= $formErrors['aplicacion'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Condiciones y Límites -->
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-sliders-h me-2"></i>Condiciones y Límites
                            </h6>
                            
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-dollar-sign text-success me-1"></i>Monto Mínimo para Ganar
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary">
                                            <i class="fas fa-trophy text-white"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control <?= isset($formErrors['monto_minimo']) ? 'is-invalid' : '' ?>" 
                                               name="monto_minimo" 
                                               step="0.01" 
                                               min="0" 
                                               value="<?= $formData['Monto_Minimo'] ?? 0 ?>" 
                                               placeholder="0.00"
                                               id="montoMinimo">
                                        <span class="input-group-text bg-primary text-white">$</span>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?php if ($esAutomatico): ?>
                                            <span class="text-primary-light">Descuento automático (se gana al alcanzar el monto)</span>
                                        <?php else: ?>
                                            0 = aplicable inmediatamente, >0 = se gana al alcanzar monto
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-globe text-primary-light me-1"></i>Máximo Usos Globales
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary">
                                            <i class="fas fa-globe-americas text-white"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control <?= isset($formErrors['max_usos_global']) ? 'is-invalid' : '' ?>" 
                                               name="max_usos_global" 
                                               min="0" 
                                               value="<?= $formData['Max_Usos_Global'] ?? 0 ?>" 
                                               placeholder="0 = ilimitado"
                                               id="maxUsosGlobal">
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Límite total: <?= $formData['Usos_Globales'] ?? 0 ?> usados de <?= $formData['Max_Usos_Global'] ?? 0 ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-user text- me-1"></i>Máximo Usos por Usuario
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary">
                                            <i class="fas fa-user-check text-white"></i>
                                        </span>
                                        <input type="number" 
                                               class="form-control <?= isset($formErrors['max_usos_usuario']) ? 'is-invalid' : '' ?>" 
                                               name="max_usos_usuario" 
                                               min="0" 
                                               value="<?= $formData['Max_Usos_Usuario'] ?? 0 ?>" 
                                               placeholder="0 = ilimitado"
                                               id="maxUsosUsuario">
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Usuarios únicos: <?= $descuento['estadisticas']['TotalUsuarios'] ?? 0 ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($esAutomatico): ?>
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-primary-light border-0">
                                        <div class="d-flex">
                                            <i class="fas fa-bolt text-warning fs-5 me-3 mt-1"></i>
                                            <div>
                                                <h6 class="alert-heading mb-2">Descuento Automático Activado</h6>
                                                <ul class="mb-0">
                                                    <li>Este descuento se gana cuando el usuario alcanza el monto mínimo en una compra</li>
                                                    <li>El código se genera automáticamente al ganar el descuento</li>
                                                    <li>El descuento ganado se aplica en compras futuras</li>
                                                    <li>El código no se puede cambiar manualmente</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Campos Dinámicos -->
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-bullseye me-2"></i>Aplicación del Descuento
                            </h6>
                            
                            <div class="row mb-4" id="articulo-field" style="display:none;">
                                <div class="col-12">
                                    <div class="card application-card border-info">
                                        <div class="card-header bg-info bg-opacity-10 text-primary-light border-0 py-3">
                                            <i class="fas fa-cube me-2"></i>Seleccionar Artículo
                                        </div>
                                        <div class="card-body">
                                            <select class="form-select" name="id_articulo" id="selectArticulo">
                                                <option value="">Elige un artículo...</option>
                                                <?php foreach ($articulos as $a): ?>
                                                <option value="<?= $a['ID_Articulo'] ?>" 
                                                        <?= ($formData['ID_Articulo'] ?? '') == $a['ID_Articulo'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($a['N_Articulo']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4" id="producto-field" style="display:none;">
                                <div class="col-12">
                                    <div class="card application-card border-success">
                                        <div class="card-header bg-success bg-opacity-10 text-success border-0 py-3">
                                            <i class="fas fa-palette me-2"></i>Seleccionar Producto/Variante
                                        </div>
                                        <div class="card-body">
                                            <select class="form-select" name="id_producto" id="selectProducto">
                                                <option value="">Elige un producto/variante...</option>
                                                <?php foreach ($productos as $p): ?>
                                                <option value="<?= $p['ID_Producto'] ?>" 
                                                        <?= ($formData['ID_Producto'] ?? '') == $p['ID_Producto'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($p['Nombre_Completo']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4" id="categoria-field" style="display:none;">
                                <div class="col-12">
                                    <div class="card application-card border-secondary">
                                        <div class="card-header bg-secondary bg-opacity-10 text-secondary border-0 py-3">
                                            <i class="fas fa-layer-group me-2"></i>Seleccionar Categoría
                                        </div>
                                        <div class="card-body">
                                            <select class="form-select" name="id_categoria" id="selectCategoria">
                                                <option value="">Elige una categoría...</option>
                                                <?php foreach ($categorias as $c): ?>
                                                <option value="<?= $c['ID_Categoria'] ?>" 
                                                        <?= ($formData['ID_Categoria'] ?? '') == $c['ID_Categoria'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($c['N_Categoria']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fechas -->
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-calendar-alt me-2"></i>Período de Vigencia
                            </h6>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        Fecha de Inicio
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary">
                                            <i class="fas fa-calendar-plus text-white"></i>
                                        </span>
                                        <input type="datetime-local" 
                                               class="form-control <?= isset($formErrors['fechas']) ? 'is-invalid' : '' ?>" 
                                               name="fecha_inicio" 
                                               value="<?= isset($formData['FechaInicio']) ? date('Y-m-d\TH:i', strtotime($formData['FechaInicio'])) : '' ?>" 
                                               required
                                               id="fechaInicio">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required-field">
                                        Fecha de Fin
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary">
                                            <i class="fas fa-calendar-minus text-white"></i>
                                        </span>
                                        <input type="datetime-local" 
                                               class="form-control <?= isset($formErrors['fechas']) ? 'is-invalid' : '' ?>" 
                                               name="fecha_fin" 
                                               value="<?= isset($formData['FechaFin']) ? date('Y-m-d\TH:i', strtotime($formData['FechaFin'])) : '' ?>" 
                                               required
                                               id="fechaFin">
                                    </div>
                                    <?php if (isset($formErrors['fechas'])): ?>
                                        <div class="invalid-feedback d-block">
                                            <?= $formErrors['fechas'] ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Estado -->
                        <div class="form-section">
                            <h6 class="form-section-title">
                                <i class="fas fa-toggle-on me-2"></i>Estado del Descuento
                            </h6>
                            
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input" type="checkbox" name="activo" id="activo" 
                                                       <?= ($formData['Activo'] ?? 1) ? 'checked' : '' ?>>
                                                <label class="form-check-label fw-semibold" for="activo">
                                                    Descuento Activo
                                                </label>
                                            </div>
                                            <div class="form-text mt-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Cuando está inactivo, el descuento no se aplicará a ningún producto.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                                    <div class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Todos los campos marcados con <span class="text-danger">*</span> son obligatorios
                                    </div>
                                    <div>
                                        <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-primary me-2">
                                            <i class="fas fa-times me-1"></i>Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-warning px-4">
                                            <i class="fas fa-save me-1"></i>
                                            Actualizar Descuento
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Controlar campos dinámicos
    const tipoAplicacion = document.getElementById('tipo_aplicacion');
    const articuloField = document.getElementById('articulo-field');
    const productoField = document.getElementById('producto-field');
    const categoriaField = document.getElementById('categoria-field');

    function mostrarCampoAplicacion() {
        articuloField.style.display = 'none';
        productoField.style.display = 'none';
        categoriaField.style.display = 'none';
        
        if (tipoAplicacion.value === 'articulo') {
            articuloField.style.display = 'block';
        } else if (tipoAplicacion.value === 'producto') {
            productoField.style.display = 'block';
        } else if (tipoAplicacion.value === 'categoria') {
            categoriaField.style.display = 'block';
        }
    }

    tipoAplicacion.addEventListener('change', mostrarCampoAplicacion);

    // Mostrar campos según datos existentes
    <?php if (isset($formData)): ?>
        <?php if ($formData['ID_Articulo']): ?>
            tipoAplicacion.value = 'articulo';
            articuloField.style.display = 'block';
        <?php elseif ($formData['ID_Producto']): ?>
            tipoAplicacion.value = 'producto';
            productoField.style.display = 'block';
        <?php elseif ($formData['ID_Categoria']): ?>
            tipoAplicacion.value = 'categoria';
            categoriaField.style.display = 'block';
        <?php endif; ?>
    <?php endif; ?>

    // Controlar símbolo del valor según tipo
    const tipoDescuento = document.getElementById('tipoDescuento');
    const simboloValor = document.getElementById('simboloValor');
    const inputValor = document.getElementById('inputValor');
    const textoAyuda = document.getElementById('textoAyuda');

    function actualizarTipoDescuento() {
        if (tipoDescuento.value === 'Porcentaje') {
            simboloValor.textContent = '%';
            inputValor.max = 100;
            inputValor.placeholder = '0.00';
            textoAyuda.textContent = 'Ingresa el porcentaje de descuento. Máximo 100%.';
        } else {
            simboloValor.textContent = '$';
            inputValor.max = 999999;
            inputValor.placeholder = '0.00';
            textoAyuda.textContent = 'Ingresa el valor fijo del descuento.';
        }
    }

    tipoDescuento.addEventListener('change', actualizarTipoDescuento);

    // Validar fechas
    const fechaInicio = document.getElementById('fechaInicio');
    const fechaFin = document.getElementById('fechaFin');

    function validarFechas() {
        if (fechaInicio.value && fechaFin.value) {
            if (fechaInicio.value > fechaFin.value) {
                fechaFin.classList.add('is-invalid');
                return false;
            } else {
                fechaFin.classList.remove('is-invalid');
                return true;
            }
        }
        return true;
    }

    fechaInicio.addEventListener('change', function() {
        if (fechaFin.value && this.value > fechaFin.value) {
            fechaFin.value = this.value;
        }
        // Establecer mínimo para fecha fin
        fechaFin.min = this.value;
        validarFechas();
    });

    fechaFin.addEventListener('change', validarFechas);

    // Validación del formulario antes de enviar
    document.getElementById('descuentoForm').addEventListener('submit', function(e) {
        if (!validarFechas()) {
            e.preventDefault();
            alert('Por favor corrige las fechas antes de enviar el formulario.');
            return false;
        }
        
        // Validar que se haya seleccionado una aplicación específica
        const tipoApp = tipoAplicacion.value;
        if (tipoApp === 'articulo' && !document.getElementById('selectArticulo').value) {
            e.preventDefault();
            alert('Por favor selecciona un artículo.');
            return false;
        } else if (tipoApp === 'producto' && !document.getElementById('selectProducto').value) {
            e.preventDefault();
            alert('Por favor selecciona un producto.');
            return false;
        } else if (tipoApp === 'categoria' && !document.getElementById('selectCategoria').value) {
            e.preventDefault();
            alert('Por favor selecciona una categoría.');
            return false;
        }
        
        // Validar límites lógicos
        const montoMinimo = parseFloat(document.getElementById('montoMinimo').value);
        const maxGlobal = parseInt(document.getElementById('maxUsosGlobal').value);
        const maxUsuario = parseInt(document.getElementById('maxUsosUsuario').value);
        
        if (montoMinimo < 0) {
            e.preventDefault();
            alert('El monto mínimo no puede ser negativo.');
            return false;
        }
        
        if (maxGlobal < 0) {
            e.preventDefault();
            alert('El máximo de usos globales no puede ser negativo.');
            return false;
        }
        
        if (maxUsuario < 0) {
            e.preventDefault();
            alert('El máximo de usos por usuario no puede ser negativo.');
            return false;
        }
        
        if (maxUsuario > 0 && maxGlobal > 0 && maxUsuario > maxGlobal) {
            e.preventDefault();
            alert('El límite por usuario no puede ser mayor al límite global.');
            return false;
        }
    });

    // Inicializar
    actualizarTipoDescuento();
});
</script>