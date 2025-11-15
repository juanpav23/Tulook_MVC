<?php
if (!isset($articulos)) $articulos = [];
if (!isset($productos)) $productos = [];
if (!isset($categorias)) $categorias = [];
$formErrors = $_SESSION['form_errors'] ?? [];
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Descuento</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-color: #f8f9fc;
            --dark-color: #5a5c69;
        }
        
        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', sans-serif;
            color: #333;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.15);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 0.5rem 0.5rem 0 0 !important;
            padding: 1.25rem 1.5rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
        }
        
        .input-group-text {
            background-color: #f8f9fc;
            border: 1px solid #d1d3e2;
            color: #6e707e;
        }
        
        .form-control, .form-select {
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: var(--danger-color);
        }
        
        .invalid-feedback {
            font-weight: 500;
        }
        
        .form-text {
            color: #858796;
            font-size: 0.875rem;
        }
        
        .btn {
            border-radius: 0.35rem;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
        }
        
        .alert {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 0.5rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .page-header {
            border-bottom: 1px solid #e3e6f0;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        
        .form-check-input:checked {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .form-check-input:focus {
            border-color: var(--success-color);
            box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
        }
        
        .application-card {
            border-width: 2px !important;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .application-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        
        .text-primary { color: var(--primary-color) !important; }
        .text-success { color: var(--success-color) !important; }
        .text-info { color: var(--info-color) !important; }
        .text-warning { color: var(--warning-color) !important; }
        .text-danger { color: var(--danger-color) !important; }
        .text-secondary { color: var(--secondary-color) !important; }
        
        .bg-primary { background-color: var(--primary-color) !important; }
        .bg-success { background-color: var(--success-color) !important; }
        .bg-info { background-color: var(--info-color) !important; }
        .bg-warning { background-color: var(--warning-color) !important; }
        .bg-danger { background-color: var(--danger-color) !important; }
        
        .border-top {
            border-top: 1px solid #e3e6f0 !important;
        }
        
        .required-field::after {
            content: " *";
            color: var(--danger-color);
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .form-section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e3e6f0;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1">
                        <i class="fas fa-plus-circle text-primary me-2"></i>
                        Crear Nuevo Descuento
                    </h1>
                    <p class="text-muted mb-0">Completa el formulario para crear un nuevo descuento</p>
                </div>
                <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-secondary">
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
            <div class="col-xl-10">
                <div class="card shadow-sm border-0">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Información del Descuento
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" id="descuentoForm">
                            <!-- Sección: Información Básica -->
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
                                            <span class="input-group-text">
                                                <i class="fas fa-tag"></i>
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
                                                   oninput="this.value = this.value.toUpperCase()">
                                            <?php if (isset($formErrors['codigo'])): ?>
                                                <div class="invalid-feedback">
                                                    <?= $formErrors['codigo'] ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="fas fa-lightbulb text-warning me-1"></i>
                                            Usa un código único y descriptivo. Solo mayúsculas, números y _
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required-field">
                                            Tipo de Descuento
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-percentage"></i>
                                            </span>
                                            <select class="form-control <?= isset($formErrors['tipo']) ? 'is-invalid' : '' ?>" name="tipo" required id="tipoDescuento">
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
                                            <span class="input-group-text" id="simboloValor">
                                                <?= (($formData['tipo'] ?? '') == 'Porcentaje' || empty($formData['tipo'])) ? '%' : '$' ?>
                                            </span>
                                            <input type="number" 
                                                   class="form-control <?= isset($formErrors['valor']) ? 'is-invalid' : '' ?>" 
                                                   name="valor" 
                                                   step="0.01" 
                                                   min="0.01" 
                                                   max="<?= (($formData['tipo'] ?? '') == 'Porcentaje' || empty($formData['tipo'])) ? '100' : '999999' ?>" 
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
                                                <?= (($formData['tipo'] ?? '') == 'Porcentaje' || empty($formData['tipo'])) ? 
                                                    'Ingresa el porcentaje de descuento. Máximo 100%.' : 
                                                    'Ingresa el valor fijo del descuento.' ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required-field">
                                            Aplicar a
                                        </label>
                                        <select class="form-control <?= isset($formErrors['aplicacion']) ? 'is-invalid' : '' ?>" name="tipo_aplicacion" id="tipo_aplicacion" required>
                                            <option value="">¿Dónde aplicar el descuento?</option>
                                            <option value="articulo" <?= ($formData['tipo_aplicacion'] ?? '') == 'articulo' ? 'selected' : '' ?>>Artículo Específico</option>
                                            <option value="producto" <?= ($formData['tipo_aplicacion'] ?? '') == 'producto' ? 'selected' : '' ?>>Producto/Variante Específica</option>
                                            <option value="categoria" <?= ($formData['tipo_aplicacion'] ?? '') == 'categoria' ? 'selected' : '' ?>>Categoría Completa</option>
                                        </select>
                                        <?php if (isset($formErrors['aplicacion'])): ?>
                                            <div class="invalid-feedback">
                                                <?= $formErrors['aplicacion'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección: Aplicación del Descuento -->
                            <div class="form-section">
                                <h6 class="form-section-title">
                                    <i class="fas fa-bullseye me-2"></i>Aplicación del Descuento
                                </h6>
                                
                                <!-- Campos Dinámicos -->
                                <div class="row mb-4" id="articulo-field" style="display:none;">
                                    <div class="col-12">
                                        <div class="card application-card border-info">
                                            <div class="card-header bg-info bg-opacity-10 text-info border-0 py-3">
                                                <i class="fas fa-cube me-2"></i>Seleccionar Artículo
                                            </div>
                                            <div class="card-body">
                                                <select class="form-control" name="id_articulo" id="selectArticulo">
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
                                                <select class="form-control" name="id_producto" id="selectProducto">
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
                                                <select class="form-control" name="id_categoria" id="selectCategoria">
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

                            <!-- Sección: Vigencia -->
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
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar-plus"></i>
                                            </span>
                                            <input type="datetime-local" 
                                                   class="form-control <?= isset($formErrors['fechas']) ? 'is-invalid' : '' ?>" 
                                                   name="fecha_inicio" 
                                                   value="<?= $formData['fecha_inicio'] ?? date('Y-m-d\T00:00') ?>" 
                                                   required
                                                   id="fechaInicio"
                                                   min="<?= date('Y-m-d\T00:00') ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label required-field">
                                            Fecha de Fin
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-calendar-minus"></i>
                                            </span>
                                            <input type="datetime-local" 
                                                   class="form-control <?= isset($formErrors['fechas']) ? 'is-invalid' : '' ?>" 
                                                   name="fecha_fin" 
                                                   value="<?= $formData['fecha_fin'] ?? date('Y-m-d\T23:59', strtotime('+30 days')) ?>" 
                                                   required
                                                   id="fechaFin"
                                                   min="<?= date('Y-m-d\T00:00', strtotime('+1 hour')) ?>">
                                        </div>
                                        <?php if (isset($formErrors['fechas'])): ?>
                                            <div class="invalid-feedback d-block">
                                                <?= $formErrors['fechas'] ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Sección: Estado -->
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
                                                           <?= ($formData['activo'] ?? 1) ? 'checked' : '' ?>>
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
                                            <a href="<?= BASE_URL ?>?c=Descuento&a=index" class="btn btn-outline-secondary me-2">
                                                <i class="fas fa-times me-1"></i>Cancelar
                                            </a>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="fas fa-plus-circle me-1"></i>
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
            tipoAplicacion.value = '<?= $formData['tipo_aplicacion'] ?>';
            mostrarCampoAplicacion();
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

        // Validación en tiempo real para el código
        const codigoInput = document.querySelector('input[name="codigo"]');
        codigoInput.addEventListener('input', function(e) {
            const valor = e.target.value;
            const regex = /^[A-Z0-9_]*$/;
            
            if (!regex.test(valor)) {
                e.target.value = valor.slice(0, -1);
            }
            
            // Validar longitud
            if (valor.length > 20) {
                e.target.value = valor.slice(0, 20);
            }
        });

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
        });

        // Inicializar
        actualizarTipoDescuento();
    });
    </script>
</body>
</html>