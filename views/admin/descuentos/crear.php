<?php
if (!isset($articulos)) $articulos = [];
if (!isset($productos)) $productos = [];
if (!isset($categorias)) $categorias = [];
$formErrors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
?>
<!-- CSS Compartido -->
<link rel="stylesheet" href="assets/css/usuario.css">
<style>
    /* Estilos específicos para crear descuento (igual que editar) */
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
                    <i class="fas fa-plus-circle text- me-2"></i>
                    Crear Nuevo Descuento
                </h2>
                <p class="text-muted mb-0">Registra un nuevo descuento para productos o categorías</p>
            </div>
            <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Volver a la lista
            </a>
        </div>
    </div>

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
                    <h5 class="mb-0 text-white">
                        <i class="fas fa-info-circle me-2"></i>
                        Información del Nuevo Descuento
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="<?= BASE_URL ?>?c=Descuento&a=guardar" id="descuentoForm">
                        <!-- Información Básica -->
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
                                               value="<?= htmlspecialchars($formData['codigo'] ?? '') ?>" 
                                               required
                                               placeholder="EJ: VERANO2024"
                                               maxlength="20"
                                               pattern="[A-Z0-9_]+"
                                               title="Solo mayúsculas, números y guiones bajos"
                                               oninput="this.value = this.value.toUpperCase()"
                                               id="inputCodigo">
                                        <?php if (isset($formErrors['codigo'])): ?>
                                            <div class="invalid-feedback">
                                                <?= $formErrors['codigo'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-lightbulb text- me-1"></i>
                                        Usa un código único y descriptivo. Solo mayúsculas, números y _
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
                                            <option value="Porcentaje" <?= ($formData['tipo'] ?? '') == 'Porcentaje' ? 'selected' : '' ?>>Porcentaje (%)</option>
                                            <option value="ValorFijo" <?= ($formData['tipo'] ?? '') == 'ValorFijo' ? 'selected' : '' ?>>Valor Fijo ($)</option>
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
                                            <?= ($formData['tipo'] ?? '') == 'Porcentaje' ? '%' : '$' ?>
                                        </span>
                                        <input type="number" 
                                               class="form-control <?= isset($formErrors['valor']) ? 'is-invalid' : '' ?>" 
                                               name="valor" 
                                               step="0.01" 
                                               min="0.01" 
                                               max="<?= ($formData['tipo'] ?? '') == 'Porcentaje' ? '100' : '999999' ?>" 
                                               value="<?= htmlspecialchars($formData['valor'] ?? '') ?>" 
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
                                            <?= ($formData['tipo'] ?? '') == 'Porcentaje' ? 
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
                                        <option value="articulo" <?= isset($formData['tipo_aplicacion']) && $formData['tipo_aplicacion'] == 'articulo' ? 'selected' : '' ?>>Artículo Específico</option>
                                        <option value="producto" <?= isset($formData['tipo_aplicacion']) && $formData['tipo_aplicacion'] == 'producto' ? 'selected' : '' ?>>Producto/Variante Específica</option>
                                        <option value="categoria" <?= isset($formData['tipo_aplicacion']) && $formData['tipo_aplicacion'] == 'categoria' ? 'selected' : '' ?>>Categoría Completa</option>
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
                                               value="<?= $formData['monto_minimo'] ?? 0 ?>" 
                                               placeholder="0.00"
                                               id="montoMinimo">
                                        <span class="input-group-text bg-primary text-white">$</span>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        0 = aplicable inmediatamente, >0 = se gana al alcanzar monto
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
                                               value="<?= $formData['max_usos_global'] ?? 0 ?>" 
                                               placeholder="0 = ilimitado"
                                               id="maxUsosGlobal">
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Límite total de usos para todos los usuarios
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
                                               value="<?= $formData['max_usos_usuario'] ?? 0 ?>" 
                                               placeholder="0 = ilimitado"
                                               id="maxUsosUsuario">
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Límite de usos por cada usuario individual
                                    </div>
                                </div>
                            </div>
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
                                                        <?= ($formData['id_articulo'] ?? '') == $a['ID_Articulo'] ? 'selected' : '' ?>>
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
                                                        <?= ($formData['id_producto'] ?? '') == $p['ID_Producto'] ? 'selected' : '' ?>>
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
                                                        <?= ($formData['id_categoria'] ?? '') == $c['ID_Categoria'] ? 'selected' : '' ?>>
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
                                               value="<?= isset($formData['fecha_inicio']) ? $formData['fecha_inicio'] : date('Y-m-d\T00:00') ?>" 
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
                                               value="<?= isset($formData['fecha_fin']) ? $formData['fecha_fin'] : date('Y-m-d\T23:59', strtotime('+30 days')) ?>" 
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
                                            Crear Descuento
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
    <?php if (isset($formData['tipo_aplicacion'])): ?>
        <?php if ($formData['tipo_aplicacion'] == 'articulo'): ?>
            tipoAplicacion.value = 'articulo';
            articuloField.style.display = 'block';
        <?php elseif ($formData['tipo_aplicacion'] == 'producto'): ?>
            tipoAplicacion.value = 'producto';
            productoField.style.display = 'block';
        <?php elseif ($formData['tipo_aplicacion'] == 'categoria'): ?>
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