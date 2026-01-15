<?php
if (!isset($articulo) || empty($articulo)) {
    echo '<div class="alert alert-danger text-center mt-4"><i class="fas fa-exclamation-circle"></i> ❌ Producto no encontrado.</div>';
    return;
}

function formatoPrecio($valor) {
    return '$' . number_format($valor, 0, ',', '.');
}

// Normalizar ruta de imagen principal
$rutaImagen = trim($articulo['Foto'] ?? '');
if ($rutaImagen !== '') {
    if (!preg_match('/^https?:/i', $rutaImagen) && !str_starts_with($rutaImagen, 'ImgProducto/')) {
        $rutaImagen = 'ImgProducto/' . ltrim($rutaImagen, '/');
    }
    $rutaImagen = BASE_URL . ltrim($rutaImagen, '/');
} else {
    $rutaImagen = BASE_URL . 'assets/img/sin_imagen.png';
}

// Obtener información de categoría, género y subcategoría
$categoriaAuto = $articulo['N_Categoria'] ?? '';
$generoAuto = $articulo['N_Genero'] ?? '';
$subcategoriaAuto = $articulo['SubCategoria'] ?? '';

// Calcular el número total de columnas para el colspan
$totalColumnas = 7 + count($atributosData);
?>

<div class="container mt-4 detalle-producto-container">
    <!-- Mensajes de sesión -->
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert alert-<?= $_SESSION['msg_type'] ?? 'info' ?> alert-dismissible fade show shadow-sm">
            <i class="fas <?= $_SESSION['msg_type'] === 'success' ? 'fa-check-circle' : 'fa-info-circle' ?>"></i>
            <?= $_SESSION['msg'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['msg'], $_SESSION['msg_type']); ?>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-primary-dark"><i class="fas fa-box-open"></i> Detalle del Producto Base</h3>
        <a href="<?= BASE_URL ?>?c=Admin&a=productos" class="btn btn-detalle-outline-primary-light">
            <i class="fas fa-arrow-left"></i> Volver a Productos
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 text-center">
            <img src="<?= htmlspecialchars($rutaImagen); ?>" class="img-fluid rounded border shadow-sm" alt="Imagen del producto" style="max-height: 300px;">
        </div>

        <div class="col-md-8">
            <h4 class="fw-bold text-primary-dark"><?= htmlspecialchars($articulo['N_Articulo']); ?></h4>
            <p><strong class="text-primary-dark">💰 Precio base:</strong> <?= formatoPrecio($articulo['PrecioBase']); ?></p>
            <p><strong class="text-primary-dark">📂 Categoría:</strong> <?= htmlspecialchars($categoriaAuto); ?></p>
            <p><strong class="text-primary-dark">👥 Género:</strong> <?= htmlspecialchars($generoAuto); ?></p>
            <p><strong class="text-primary-dark">📁 Subcategoría:</strong> <?= htmlspecialchars($subcategoriaAuto); ?></p>
            <p><strong class="text-primary-dark">🎯 Atributos requeridos:</strong> 
                <?php if (!empty($atributosData)): ?>
                    <?php 
                    $soloColor = false;
                    if (count($atributosData) === 1 && strtolower($atributosData[0]['tipo']['Nombre']) === 'color') {
                        $soloColor = true;
                        echo '<span class="badge bg-primary text-light"><i class="fas fa-palette"></i> Solo Color</span>';
                    } else {
                        foreach ($atributosData as $atributo): ?>
                            <span class="badge bg-primary text-light"><?= htmlspecialchars($atributo['tipo']['Nombre']) ?></span>
                        <?php endforeach;
                    }
                    ?>
                <?php else: ?>
                    <span class="text-muted">Ninguno</span>
                <?php endif; ?>
            </p>
            <?php if (isset($soloColor) && $soloColor): ?>
                <div class="alert alert-primary-dark mt-2 border-primary-detalle">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Producto especial:</strong> Este producto solo requiere seleccionar el color como atributo.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <hr>
    <h4 class="mt-4 text-primary-dark fw-bold"><i class="fas fa-layer-group"></i> Variantes del Producto</h4>

    <div class="table-responsive">
        <table class="table table-bordered align-middle text-center shadow-sm">
            <thead class="table-primary-dark">
                <tr>
                    <th>Foto</th>
                    <th>Nombre Variante</th>
                    <?php foreach ($atributosData as $index => $atributo): ?>
                        <th><?= htmlspecialchars($atributo['tipo']['Nombre']) ?></th>
                    <?php endforeach; ?>
                    <th>% Variación</th>
                    <th>Precio Final</th>
                    <th>Cantidad</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($variantes)): ?>
                    <?php foreach ($variantes as $v): ?>
                        <?php
                        $rutaVar = trim($v['Foto'] ?? '');
                        if ($rutaVar !== '') {
                            if (!preg_match('/^https?:/i', $rutaVar) && !str_starts_with($rutaVar, 'ImgProducto/')) {
                                $rutaVar = 'ImgProducto/' . ltrim($rutaVar, '/');
                            }
                            $rutaVar = BASE_URL . ltrim($rutaVar, '/');
                        } else {
                            $rutaVar = BASE_URL . 'assets/img/sin_imagen.png';
                        }

                        $precioBase = floatval($articulo['PrecioBase']);
                        $porcentaje = floatval($v['Porcentaje']);
                        $precioFinal = $precioBase + ($precioBase * ($porcentaje / 100));

                        // Construir nombre basado en atributos
                        $nombrePartes = [$articulo['N_Articulo']];
                        if (!empty($v['ValorAtributo1'])) $nombrePartes[] = $v['ValorAtributo1'];
                        if (!empty($v['ValorAtributo2'])) $nombrePartes[] = $v['ValorAtributo2'];
                        if (!empty($v['ValorAtributo3'])) $nombrePartes[] = $v['ValorAtributo3'];
                        
                        $nombreVariante = !empty($v['Nombre_Producto']) 
                            ? $v['Nombre_Producto'] 
                            : implode(' ', $nombrePartes);
                        ?>
                        <tr class="hover-shadow-detalle">
                            <td>
                                <?php if ($rutaVar && $rutaVar != BASE_URL . 'assets/img/sin_imagen.png'): ?>
                                    <img src="<?= htmlspecialchars($rutaVar); ?>" 
                                        class="product-image"
                                        style="width: 70px; height: 70px; object-fit: cover; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);" 
                                        alt="Imagen variante">
                                <?php else: ?>
                                    <div style="width: 70px; height: 70px; border: 1px solid #dee2e6; border-radius: 0.375rem; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); display: flex; align-items: center; justify-content: center; background-color: #f8f9fa;">
                                        <span class="text-muted small">Sin imagen</span>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-start"><?= htmlspecialchars($nombreVariante); ?></td>
                            
                            <!-- Mostrar SOLO los atributos que existen -->
                            <?php foreach ($atributosData as $index => $atributo): ?>
                                <?php $numero = $index + 1; ?>
                                <td>
                                    <?php 
                                    $valorAtributo = $v["ValorAtributo{$numero}"] ?? '';
                                    if (!empty($valorAtributo)): ?>
                                        <span class="badge bg-light text-primary-dark border">
                                            <?= htmlspecialchars($valorAtributo) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            
                            <!-- COLUMNA % VARIACIÓN -->
                            <td>
                                <?php if ($v['Porcentaje'] >= 0): ?>
                                    <span class="badge bg-primary text-light">
                                        +<?= htmlspecialchars($v['Porcentaje']); ?>%
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary-light text-primary-dark">
                                        <?= htmlspecialchars($v['Porcentaje']); ?>%
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- COLUMNA PRECIO FINAL -->
                            <td>
                                <strong class="text-primary-dark"><?= formatoPrecio($precioFinal); ?></strong>
                            </td>
                            
                            <!-- COLUMNA CANTIDAD -->
                            <td>
                                <?php if ($v['Cantidad'] > 0): ?>
                                    <span class="badge bg-primary text-light">
                                        <?= htmlspecialchars($v['Cantidad']); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary-light text-primary-dark">
                                        <?= htmlspecialchars($v['Cantidad']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- COLUMNA ESTADO -->
                            <td>
                                <span class="badge <?= $v['Activo'] ? 'bg-primary text-light' : 'bg-primary-light text-primary-dark' ?>">
                                    <?= $v['Activo'] ? 'Activa' : 'Inactiva' ?>
                                </span>
                            </td>
                            
                            <!-- COLUMNA ACCIONES -->
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <!-- BOTÓN ACTIVAR/DESACTIVAR -->
                                    <?php if ($v['Activo']): ?>
                                        <a href="<?= BASE_URL ?>?c=Admin&a=toggleVariante&id=<?= $v['ID_Producto'] ?>&articulo=<?= $articulo['ID_Articulo'] ?>" 
                                        class="btn btn-detalle-outline-primary-light"
                                        onclick="return confirm('¿Desactivar esta variante?')"
                                        title="Desactivar variante">
                                            <i class="fas fa-pause"></i>
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>?c=Admin&a=toggleVariante&id=<?= $v['ID_Producto'] ?>&articulo=<?= $articulo['ID_Articulo'] ?>" 
                                        class="btn btn-detalle-outline-primary"
                                        onclick="return confirm('¿Activar esta variante?')"
                                        title="Activar variante">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    <?php endif; ?>

                                    <!-- Botón editar -->
                                    <button type="button" class="btn btn-detalle-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditarVariante"
                                            data-id="<?= $v['ID_Producto']; ?>"
                                            data-nombre="<?= htmlspecialchars($v['Nombre_Producto'] ?? ''); ?>"
                                            data-porcentaje="<?= $v['Porcentaje']; ?>"
                                            data-cantidad="<?= $v['Cantidad']; ?>"
                                            data-foto="<?= htmlspecialchars($v['Foto'] ?? ''); ?>"
                                            data-activo="<?= $v['Activo']; ?>"
                                            <?php for ($i = 1; $i <= count($atributosData); $i++): ?>
                                                data-atributo<?= $i ?>="<?= $v["ID_Atributo{$i}"] ?? '' ?>" 
                                                data-valor-atributo<?= $i ?>="<?= htmlspecialchars($v["ValorAtributo{$i}"] ?? '') ?>"
                                            <?php endfor; ?>>
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <!-- Botón eliminar (ROJO para peligro) -->
                                    <a href="<?= BASE_URL ?>?c=Admin&a=eliminarVariante&id=<?= $v['ID_Producto'] ?>&articulo=<?= $articulo['ID_Articulo'] ?>" 
                                    class="btn btn-detalle-outline-danger"
                                    onclick="return confirm('⚠️ ¿Seguro que deseas eliminar esta variante? Esta acción no se puede deshacer.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $totalColumnas ?>" class="text-muted py-4">
                            <div class="text-center">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="mb-0">No hay variantes registradas aún.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <hr>
    <h4 class="mt-4 text-primary-dark fw-bold"><i class="fas fa-plus-circle"></i> Agregar Nueva Variante</h4>

    <!-- Contenedor de mensajes -->
    <div id="msgContainer"></div>

    <form id="formVariante" action="<?= BASE_URL; ?>?c=Admin&a=agregarVariante" method="POST" enctype="multipart/form-data" class="row g-3 mt-2 shadow-sm p-3 rounded border form-detalle-variante">
        <input type="hidden" name="ID_Articulo" value="<?= htmlspecialchars($articulo['ID_Articulo']); ?>">

        <div class="col-md-4">
            <label class="form-label fw-bold text-primary-dark">Nombre de Variante</label>
            <input type="text" name="Nombre_Producto" class="form-control" placeholder="Ej: Boxer Azul Mediano" required>
        </div>

        <!-- CONTENEDOR DINÁMICO PARA ATRIBUTOS -->
        <div id="atributos-container" class="row">
            <?php foreach ($atributosData as $index => $atributo): ?>
                <?php $numero = $index + 1; ?>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary-dark">
                            <?= htmlspecialchars($atributo['tipo']['Nombre']) ?>
                        </label>
                        <input type="hidden" name="atributo<?= $numero ?>" value="<?= $atributo['tipo']['ID_TipoAtributo'] ?>">
                        
                        <select class="form-select" name="valor_atributo<?= $numero ?>" required>
                            <option value="">Seleccionar <?= htmlspecialchars($atributo['tipo']['Nombre']) ?></option>
                            <?php foreach ($atributo['valores'] as $valor): ?>
                                <option value="<?= htmlspecialchars($valor['Valor']) ?>">
                                    <?= htmlspecialchars($valor['Valor']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <?php if (!empty($atributo['tipo']['Descripcion'])): ?>
                            <small class="form-text text-muted"><?= htmlspecialchars($atributo['tipo']['Descripcion']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- CAMBIO: Permitir porcentajes negativos -->
        <div class="col-md-2">
            <label class="form-label fw-bold text-primary-dark">% Variación Precio</label>
            <input type="number" step="0.1" name="Porcentaje" class="form-control" 
                placeholder="Ej: 10 (aumento) o -10 (descuento)" 
                min="-90" max="300" required>
            <small class="form-text text-muted">
                <span class="text-primary-dark">Positivo: aumento</span> | 
                <span class="text-primary-light-detalle">Negativo: descuento</span><br>
                Rango: -90% a +300%
            </small>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold text-primary-dark">Cantidad</label>
            <input type="number" name="Cantidad" min="0" max="99999" value="0" class="form-control">
            <small class="form-text text-muted">Máximo: 99,999 unidades</small>
        </div>

        <!-- ESTADO -->
        <div class="col-md-2">
            <label class="form-label fw-bold text-primary-dark">Estado</label>
            <select name="Activo" class="form-select" required>
                <option value="0">Inactiva</option>
                <option value="1" selected>Activa</option>
            </select>
            <small class="form-text text-muted">Las variantes inactivas no se mostrarán en la tienda</small>
        </div>

        <!-- Sistema automático de subida de imagen -->
        <div class="col-md-12 mb-3">
            <label class="form-label fw-bold text-primary-dark">Subir Imagen de la Variante *</label>
            
            <div class="card card-border-primary">
                <div class="card-header bg-primary text-light">
                    <i class="fas fa-upload"></i> Subida de Imagen (15MB máximo)
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <p><strong class="text-primary-dark">Categoría:</strong><br>
                                <span class="badge bg-primary text-light"><?= htmlspecialchars($categoriaAuto) ?></span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong class="text-primary-dark">Género:</strong><br>
                                <span class="badge bg-primary text-light"><?= htmlspecialchars($generoAuto) ?></span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p><strong class="text-primary-dark">Subcategoría:</strong><br>
                                <span class="badge bg-primary-light text-primary-dark"><?= htmlspecialchars($subcategoriaAuto) ?></span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary-dark">Seleccionar imagen *</label>
                            <input type="file" name="imagen_variante" class="form-control" id="imagenVariante" 
                                accept=".jpg,.jpeg,.png,.gif,.webp" required
                                onchange="procesarSubidaImagen()">
                            <small class="form-text text-muted">
                                Formatos permitidos: JPG, JPEG, PNG, GIF, WebP. Tamaño máximo: 15MB
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-primary-dark">Ruta donde se guardará:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">ImgProducto/<?= htmlspecialchars($categoriaAuto) ?>/<?= htmlspecialchars($generoAuto) ?>/<?= htmlspecialchars($subcategoriaAuto) ?>/</span>
                                <input type="text" class="form-control bg-light" id="nombreArchivoFinal" readonly>
                            </div>
                            <input type="hidden" name="Foto" id="fotoFinal">
                            
                            <!-- Vista previa de la imagen seleccionada -->
                            <div class="row mt-3" id="vistaPreviaContainer" style="display:none;">
                                <div class="col-md-12">
                                    <label class="form-label fw-bold text-primary-dark">Vista previa de la imagen:</label>
                                    <div class="border rounded p-3 text-center bg-light vista-previa-detalle">
                                        <img id="vistaPreviaImg" src="" alt="Vista previa" style="max-height: 150px; max-width: 100%;">
                                        <div class="mt-2">
                                            <small class="text-muted" id="infoArchivo"></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 text-end mt-3">
            <button type="submit" class="btn btn-detalle-primary btn-lg shadow">
                <i class="fas fa-save"></i> Guardar Variante
            </button>
        </div>
    </form>
</div>

<!-- Incluir CSS específico para detalle de variantes -->
<link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/detalleVarianteAdmin.css">

<!-- MODAL PARA EDITAR VARIANTE -->
<div class="modal fade" id="modalEditarVariante" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <form id="formEditarVariante" action="<?= BASE_URL; ?>?c=Admin&a=editarVariante" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-light">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Variante</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ID_Producto" id="edit_ID_Producto">
                    <input type="hidden" name="ID_Articulo" value="<?= htmlspecialchars($articulo['ID_Articulo']); ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary-dark">Nombre de Variante</label>
                                <input type="text" name="Nombre_Producto" id="edit_Nombre_Producto" class="form-control" required>
                            </div>
                        </div>
                        
                        <!-- CONTENEDOR DINÁMICO PARA ATRIBUTOS EN EDICIÓN -->
                        <div id="edit-atributos-container" class="row">
                            <?php foreach ($atributosData as $index => $atributo): ?>
                                <?php $numero = $index + 1; ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold text-primary-dark">
                                            <?= htmlspecialchars($atributo['tipo']['Nombre']) ?>
                                        </label>
                                        <input type="hidden" name="atributo<?= $numero ?>" value="<?= $atributo['tipo']['ID_TipoAtributo'] ?>">
                                        
                                        <select class="form-select" name="valor_atributo<?= $numero ?>" id="edit_valor_atributo<?= $numero ?>" required>
                                            <option value="">Seleccionar <?= htmlspecialchars($atributo['tipo']['Nombre']) ?></option>
                                            <?php foreach ($atributo['valores'] as $valor): ?>
                                                <option value="<?= htmlspecialchars($valor['Valor']) ?>">
                                                    <?= htmlspecialchars($valor['Valor']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row">
                        <!-- CAMBIO: Permitir porcentajes negativos -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary-dark">% Variación Precio</label>
                                <input type="number" step="0.1" name="Porcentaje" class="form-control" id="edit_Porcentaje" 
                                    min="-90" max="300" required>
                                <small class="form-text text-muted">
                                    <span class="text-primary-dark">Positivo: aumento</span> | 
                                    <span class="text-primary-light-detalle">Negativo: descuento</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary-dark">Cantidad</label>
                                <input type="number" name="Cantidad" class="form-control" id="edit_Cantidad" min="0" max="99999" required>
                                <small class="form-text text-muted">Máximo: 99,999 unidades</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-primary-dark">Estado</label>
                                <select name="Activo" class="form-select" id="edit_Activo" required>
                                    <option value="1">Activa</option>
                                    <option value="0">Inactiva</option>
                                </select>
                                <small class="form-text text-muted">Las variantes inactivas no se mostrarán en el catálogo</small>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de imágenes -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary-dark">Imagen de la Variante</label>
                        
                        <div class="card card-border-primary-light">
                            <div class="card-header bg-primary-light text-primary-dark">
                                <i class="fas fa-upload"></i> Gestión de Imagen
                            </div>
                            <div class="card-body">
                                <!-- Imagen actual -->
                                <div id="imagenActualContainer" style="display:none;">
                                    <label class="form-label fw-bold text-primary-dark">Imagen actual:</label>
                                    <div class="border rounded p-2 mb-3 text-center bg-light">
                                        <img id="imagenActual" src="" alt="Imagen actual" style="max-height: 100px;">
                                        <div class="mt-2">
                                            <small class="text-muted" id="rutaActual"></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-primary-dark">Cambiar imagen (opcional)</label>
                                        <input type="file" name="imagen_variante_edit" class="form-control" id="imagenVarianteEdit" 
                                               accept=".jpg,.jpeg,.png,.gif,.webp"
                                               onchange="procesarSubidaImagenEdit()">
                                        <small class="form-text text-muted">
                                            Deja vacío para mantener la imagen actual (Máximo 15MB)
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold text-primary-dark">Ruta donde se guardará:</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">ImgProducto/<?= htmlspecialchars($categoriaAuto) ?>/<?= htmlspecialchars($generoAuto) ?>/<?= htmlspecialchars($subcategoriaAuto) ?>/</span>
                                            <input type="text" class="form-control bg-light" id="edit_nombreArchivoFinal" readonly>
                                        </div>
                                        <input type="hidden" name="Foto" id="edit_fotoFinal">
                                        
                                        <!-- Vista previa de nueva imagen -->
                                        <div class="row mt-3" id="edit_vistaPreviaContainer" style="display:none;">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold text-primary-dark">Vista previa de nueva imagen:</label>
                                                <div class="border rounded p-2 text-center bg-light vista-previa-detalle">
                                                    <img id="edit_vistaPreviaImg" src="" alt="Vista previa" style="max-height: 100px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="msgModal"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-detalle-outline-primary-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-detalle-primary shadow">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Incluir JavaScript para variantes -->
<script src="<?= BASE_URL; ?>assets/js/variantesAdmin.js"></script>