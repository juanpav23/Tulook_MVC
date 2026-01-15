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

    // Obtener información de categoría, género y subcategoría para generar rutas automáticas
    $stmtInfo = $this->db->prepare("
        SELECT 
            c.N_Categoria as categoria,
            g.N_Genero as genero,
            s.SubCategoria as subcategoria
        FROM articulo a
        LEFT JOIN categoria c ON a.ID_Categoria = c.ID_Categoria
        LEFT JOIN genero g ON a.ID_Genero = g.ID_Genero
        LEFT JOIN subcategoria s ON a.ID_SubCategoria = s.ID_SubCategoria
        WHERE a.ID_Articulo = ?
    ");
    $stmtInfo->execute([$articulo['ID_Articulo']]);
    $infoProducto = $stmtInfo->fetch(PDO::FETCH_ASSOC);

    $categoriaAuto = $infoProducto['categoria'] ?? '';
    $generoAuto = $infoProducto['genero'] ?? '';
    $subcategoriaAuto = $infoProducto['subcategoria'] ?? '';

    // Calcular el número total de columnas para el colspan
    $totalColumnas = 7 + count($atributosData); // 7 columnas fijas + atributos dinámicos
    ?>

    <div class="container mt-4">

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
            <h3 class="fw-bold text-primary"><i class="fas fa-box-open"></i> Detalle del Producto Base</h3>
            <a href="<?= BASE_URL ?>?c=Admin&a=productos" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Productos
            </a>
        </div>

        <div class="row">
            <div class="col-md-4 text-center">
                <img src="<?= htmlspecialchars($rutaImagen); ?>" class="img-fluid rounded border shadow-sm" alt="Imagen del producto" style="max-height: 300px;">
            </div>

            <div class="col-md-8">
                <h4 class="fw-bold text-dark"><?= htmlspecialchars($articulo['N_Articulo']); ?></h4>
                <p><strong>💰 Precio base:</strong> <?= formatoPrecio($articulo['PrecioBase']); ?></p>
                <p><strong>📂 Categoría:</strong> <?= htmlspecialchars($categoriaAuto); ?></p>
                <p><strong>👥 Género:</strong> <?= htmlspecialchars($generoAuto); ?></p>
                <p><strong>📁 Subcategoría:</strong> <?= htmlspecialchars($subcategoriaAuto); ?></p>
                <p><strong>🎯 Atributos requeridos:</strong> 
                    <?php if (!empty($atributosData)): ?>
                        <?php 
                        $soloColor = false;
                        if (count($atributosData) === 1 && strtolower($atributosData[0]['tipo']['Nombre']) === 'color') {
                            $soloColor = true;
                            echo '<span class="badge bg-warning text-dark"><i class="fas fa-palette"></i> Solo Color</span>';
                        } else {
                            foreach ($atributosData as $atributo): ?>
                                <span class="badge bg-info"><?= htmlspecialchars($atributo['tipo']['Nombre']) ?></span>
                            <?php endforeach;
                        }
                        ?>
                    <?php else: ?>
                        <span class="text-muted">Ninguno</span>
                    <?php endif; ?>
                </p>
                <?php if (isset($soloColor) && $soloColor): ?>
                    <div class="alert alert-info mt-2">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Producto especial:</strong> Este producto solo requiere seleccionar el color como atributo.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <hr>
        <h4 class="mt-4 text-primary fw-bold"><i class="fas fa-layer-group"></i> Variantes del Producto</h4>

        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center shadow-sm">
                <thead class="table-dark">
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
                            <tr>
                                <td>
                                    <?php if ($rutaVar && $rutaVar != BASE_URL . 'assets/img/sin_imagen.png'): ?>
                                        <img src="<?= htmlspecialchars($rutaVar); ?>" 
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
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($valorAtributo) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                
                                <!-- COLUMNA % VARIACIÓN (con colores) -->
                                <td>
                                    <?php if ($v['Porcentaje'] >= 0): ?>
                                        <span class="badge bg-success text-white">
                                            +<?= htmlspecialchars($v['Porcentaje']); ?>%
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger text-white">
                                            <?= htmlspecialchars($v['Porcentaje']); ?>%
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- COLUMNA PRECIO FINAL -->
                                <td>
                                    <strong><?= formatoPrecio($precioFinal); ?></strong>
                                </td>
                                
                                <!-- COLUMNA CANTIDAD -->
                                <td>
                                    <?php if ($v['Cantidad'] > 0): ?>
                                        <span class="badge bg-success">
                                            <?= htmlspecialchars($v['Cantidad']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <?= htmlspecialchars($v['Cantidad']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- COLUMNA ESTADO -->
                                <td>
                                    <span class="badge <?= $v['Activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $v['Activo'] ? '✅ Activa' : '❌ Inactiva' ?>
                                    </span>
                                </td>
                                
                                <!-- COLUMNA ACCIONES -->
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <!-- BOTÓN ACTIVAR/DESACTIVAR -->
                                        <?php if ($v['Activo']): ?>
                                            <a href="<?= BASE_URL ?>?c=Admin&a=toggleVariante&id=<?= $v['ID_Producto'] ?>&articulo=<?= $articulo['ID_Articulo'] ?>" 
                                            class="btn btn-outline-warning"
                                            onclick="return confirm('¿Desactivar esta variante?')"
                                            title="Desactivar variante">
                                                <i class="fas fa-pause"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>?c=Admin&a=toggleVariante&id=<?= $v['ID_Producto'] ?>&articulo=<?= $articulo['ID_Articulo'] ?>" 
                                            class="btn btn-outline-success"
                                            onclick="return confirm('¿Activar esta variante?')"
                                            title="Activar variante">
                                                <i class="fas fa-play"></i>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Botón editar -->
                                        <button type="button" class="btn btn-outline-primary" 
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

                                        <!-- Botón eliminar -->
                                        <a href="<?= BASE_URL ?>?c=Admin&a=eliminarVariante&id=<?= $v['ID_Producto'] ?>&articulo=<?= $articulo['ID_Articulo'] ?>" 
                                        class="btn btn-outline-danger"
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
        <h4 class="mt-4 text-success fw-bold"><i class="fas fa-plus-circle"></i> Agregar Nueva Variante</h4>

        <!-- Contenedor de mensajes bonitos -->
        <div id="msgContainer"></div>

        <form id="formVariante" action="<?= BASE_URL; ?>?c=Admin&a=agregarVariante" method="POST" enctype="multipart/form-data" class="row g-3 mt-2 shadow-sm p-3 rounded border">
            <input type="hidden" name="ID_Articulo" value="<?= htmlspecialchars($articulo['ID_Articulo']); ?>">

            <div class="col-md-4">
                <label class="form-label fw-bold">Nombre de Variante</label>
                <input type="text" name="Nombre_Producto" class="form-control" placeholder="Ej: Boxer Azul Mediano" required>
            </div>

            <!-- CONTENEDOR DINÁMICO PARA ATRIBUTOS -->
            <div id="atributos-container" class="row">
                <?php foreach ($atributosData as $index => $atributo): ?>
                    <?php $numero = $index + 1; ?>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold">
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
                <label class="form-label fw-bold">% Variación Precio</label>
                <input type="number" step="0.1" name="Porcentaje" class="form-control" 
                    placeholder="Ej: 10 (aumento) o -10 (descuento)" 
                    min="-90" max="300" required>
                <small class="form-text text-muted">
                    <span class="text-success">Positivo: aumento</span> | 
                    <span class="text-danger">Negativo: descuento</span><br>
                    Rango: -90% a +300%
                </small>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-bold">Cantidad</label>
                <input type="number" name="Cantidad" min="0" max="99999" value="0" class="form-control">
                <small class="form-text text-muted">Máximo: 99,999 unidades</small>
            </div>

            <!-- ESTADO -->
            <div class="col-md-2">
                <label class="form-label fw-bold">Estado</label>
                <select name="Activo" class="form-select" required>
                    <option value="0">❌ Inactiva</option>
                    <option value="1" selected>✅ Activa</option>
                </select>
                <small class="form-text text-muted">Las variantes inactivas no se mostrarán en la tienda</small>
            </div>

            <!-- Sistema automático de subida de imagen -->
            <div class="col-md-12 mb-3">
                <label class="form-label fw-bold">Subir Imagen de la Variante</label>
                
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-upload"></i> Subida Automática de Imagen
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <p><strong>Categoría:</strong><br>
                                    <span class="badge bg-primary"><?= htmlspecialchars($categoriaAuto) ?></span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Género:</strong><br>
                                    <span class="badge bg-info"><?= htmlspecialchars($generoAuto) ?></span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>Subcategoría:</strong><br>
                                    <span class="badge bg-warning text-dark"><?= htmlspecialchars($subcategoriaAuto) ?></span>
                                </p>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Seleccionar imagen *</label>
                                <input type="file" name="imagen_variante" class="form-control" id="imagenVariante" 
                                    accept=".jpg,.jpeg,.png,.gif" required
                                    onchange="procesarSubidaImagen()">
                                <small class="form-text text-muted">
                                    Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB
                                </small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Nombre personalizado (opcional)</label>
                                <input type="text" class="form-control" id="nombrePersonalizado" 
                                    placeholder="Ej: boxer_azul_mediano"
                                    oninput="actualizarRutaDesdeNombre()">
                                <small class="form-text text-muted">
                                    Si no especificas un nombre, se usará el nombre original del archivo
                                </small>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Ruta donde se guardará:</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">ImgProducto/<?= htmlspecialchars($categoriaAuto) ?>/<?= htmlspecialchars($generoAuto) ?>/<?= htmlspecialchars($subcategoriaAuto) ?>/</span>
                                    <input type="text" class="form-control bg-light" id="nombreArchivoFinal" readonly>
                                </div>
                                <input type="hidden" name="Foto" id="fotoFinal">
                                
                                <!-- Vista previa de la imagen seleccionada -->
                                <div class="row mt-3" id="vistaPreviaContainer" style="display:none;">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Vista previa de la imagen:</label>
                                        <div class="border rounded p-3 text-center bg-light">
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
                <button type="submit" class="btn btn-success btn-lg shadow">
                    <i class="fas fa-save"></i> Guardar Variante
                </button>
            </div>
        </form>
    </div>

<!-- MODAL PARA EDITAR VARIANTE - SIMPLIFICADO -->
<div class="modal fade" id="modalEditarVariante" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow">
            <form id="formEditarVariante" action="<?= BASE_URL; ?>?c=Admin&a=editarVariante" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Variante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ID_Producto" id="edit_ID_Producto">
                    <input type="hidden" name="ID_Articulo" value="<?= htmlspecialchars($articulo['ID_Articulo']); ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Nombre de Variante</label>
                                <input type="text" name="Nombre_Producto" id="edit_Nombre_Producto" class="form-control" required>
                            </div>
                        </div>
                        
                        <!-- CONTENEDOR DINÁMICO PARA ATRIBUTOS EN EDICIÓN -->
                        <div id="edit-atributos-container" class="row">
                            <?php foreach ($atributosData as $index => $atributo): ?>
                                <?php $numero = $index + 1; ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">
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
                                <label class="form-label fw-bold">% Variación Precio</label>
                                <input type="number" step="0.1" name="Porcentaje" class="form-control" id="edit_Porcentaje" 
                                    min="-90" max="300" required>
                                <small class="form-text text-muted">
                                    <span class="text-success">Positivo: aumento</span> | 
                                    <span class="text-danger">Negativo: descuento</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cantidad</label>
                                <input type="number" name="Cantidad" class="form-control" id="edit_Cantidad" min="0" max="99999" required>
                                <small class="form-text text-muted">Máximo: 99,999 unidades</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Estado</label>
                                <select name="Activo" class="form-select" id="edit_Activo" required>
                                    <option value="1">✅ Activa</option>
                                    <option value="0">❌ Inactiva</option>
                                </select>
                                <small class="form-text text-muted">Las variantes inactivas no se mostrarán en el catálogo</small>
                            </div>
                        </div>
                    </div>

                    <!-- Gestión de imágenes -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Imagen de la Variante</label>
                        
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-upload"></i> Gestión de Imagen
                            </div>
                            <div class="card-body">
                                <!-- Imagen actual -->
                                <div id="imagenActualContainer" style="display:none;">
                                    <label class="form-label fw-bold">Imagen actual:</label>
                                    <div class="border rounded p-2 mb-3 text-center bg-light">
                                        <img id="imagenActual" src="" alt="Imagen actual" style="max-height: 100px;">
                                        <div class="mt-2">
                                            <small class="text-muted" id="rutaActual"></small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Cambiar imagen (opcional)</label>
                                        <input type="file" name="imagen_variante_edit" class="form-control" id="imagenVarianteEdit" 
                                               accept=".jpg,.jpeg,.png,.gif"
                                               onchange="procesarSubidaImagenEdit()">
                                        <small class="form-text text-muted">
                                            Deja vacío para mantener la imagen actual
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Nuevo nombre (opcional)</label>
                                        <input type="text" class="form-control" id="edit_nombrePersonalizado" 
                                               placeholder="Ej: boxer_azul_mediano"
                                               oninput="actualizarRutaDesdeNombreEdit()">
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Ruta donde se guardará:</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light">ImgProducto/<?= htmlspecialchars($categoriaAuto) ?>/<?= htmlspecialchars($generoAuto) ?>/<?= htmlspecialchars($subcategoriaAuto) ?>/</span>
                                            <input type="text" class="form-control bg-light" id="edit_nombreArchivoFinal" readonly>
                                        </div>
                                        <input type="hidden" name="Foto" id="edit_fotoFinal">
                                        
                                        <!-- Vista previa de nueva imagen -->
                                        <div class="row mt-3" id="edit_vistaPreviaContainer" style="display:none;">
                                            <div class="col-md-12">
                                                <label class="form-label fw-bold">Vista previa de nueva imagen:</label>
                                                <div class="border rounded p-2 text-center bg-light">
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
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary shadow">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT DE VALIDACIÓN Y SISTEMA DE SUBIDA DE IMÁGENES -->
<script>
// ===== INICIALIZACIÓN Y FUNCIONES EXISTENTES =====
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarVariante');

    modalEditar.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('edit_ID_Producto').value = btn.getAttribute('data-id');
        document.getElementById('edit_Nombre_Producto').value = btn.getAttribute('data-nombre') || '';
        document.getElementById('edit_Porcentaje').value = btn.getAttribute('data-porcentaje');
        document.getElementById('edit_Cantidad').value = btn.getAttribute('data-cantidad');
        document.getElementById('edit_Activo').value = btn.getAttribute('data-activo'); 
        
        // Cargar valores de atributos dinámicos
        <?php for ($i = 1; $i <= 3; $i++): ?>
            const atributo<?= $i ?> = btn.getAttribute('data-atributo<?= $i ?>');
            const valorAtributo<?= $i ?> = btn.getAttribute('data-valor-atributo<?= $i ?>');
            if (atributo<?= $i ?> && valorAtributo<?= $i ?>) {
                const select = document.getElementById('edit_valor_atributo<?= $i ?>');
                if (select) {
                    select.value = valorAtributo<?= $i ?>;
                }
            }
        <?php endfor; ?>
        
        // Manejar la imagen existente en el modal
        const fotoExistente = btn.getAttribute('data-foto') || '';
        if (fotoExistente) {
            mostrarImagenActualEdit(fotoExistente);
        } else {
            document.getElementById('imagenActualContainer').style.display = 'none';
            document.getElementById('edit_nombreArchivoFinal').value = '';
            document.getElementById('edit_fotoFinal').value = '';
        }
    });

    // Validación del formulario
    const formVariante = document.getElementById('formVariante');
    if (formVariante) {
        formVariante.onsubmit = function(e) {
            // Validar que todos los atributos requeridos estén seleccionados
            const selects = this.querySelectorAll('select[name^="valor_atributo"]');
            let todosSeleccionados = true;
            
            selects.forEach(select => {
                if (!select.value) {
                    todosSeleccionados = false;
                    select.classList.add('is-invalid');
                } else {
                    select.classList.remove('is-invalid');
                }
            });
            
            if (!todosSeleccionados) {
                e.preventDefault();
                mostrarMensaje(document.getElementById('msgContainer'), 'danger', 'Por favor, completa todos los atributos requeridos.');
                return false;
            }
            
            return validarFormulario(this, document.getElementById('msgContainer'));
        };
    }

    function mostrarMensaje(contenedor, tipo, texto) {
        contenedor.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show mt-2 shadow-sm">
                <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
                ${texto}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
    }

    function validarProductoEspecial() {
        // Verificar si el producto es Gorras, Relojes o Gafas
        const categoria = "<?= htmlspecialchars($categoriaAuto) ?>";
        const subcategoria = "<?= htmlspecialchars($subcategoriaAuto) ?>";
        
        const productosEspeciales = ['gorras', 'relojes', 'gafas', 'gorra', 'reloj', 'gafa'];
        const esProductoEspecial = productosEspeciales.includes(subcategoria.toLowerCase());
        
        if (esProductoEspecial) {
            // Deshabilitar todos los selects excepto el de color
            const selects = document.querySelectorAll('select[name^="valor_atributo"]');
            selects.forEach((select, index) => {
                const hiddenInput = document.querySelector(`input[name="atributo${index + 1}"]`);
                if (hiddenInput && hiddenInput.value !== '2') { // Si no es color (ID 2)
                    select.disabled = true;
                    select.value = '';
                    select.parentElement.style.display = 'none';
                }
            });
            
            // Mostrar mensaje informativo
            const msgContainer = document.getElementById('msgContainer');
            if (msgContainer) {
                msgContainer.innerHTML = `
                    <div class="alert alert-warning alert-dismissible fade show">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Producto especial:</strong> ${subcategoria} solo requiere seleccionar el color como atributo.
                    </div>
                `;
            }
        }
    }

    // Ejecutar al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        validarProductoEspecial();
    });

    function validarFormulario(form, contenedor) {
        const porcentaje = form.querySelector('[name="Porcentaje"]');
        const cantidad = form.querySelector('[name="Cantidad"]');
        const imagenInput = form.querySelector('input[type="file"]');
        
        contenedor.innerHTML = '';

        // VALIDACIÓN ACTUALIZADA: Límites realistas para e-commerce
        if (porcentaje.value < -90 || porcentaje.value > 300) {
            mostrarMensaje(contenedor, 'danger', 
                'El porcentaje debe estar entre <strong>-90%</strong> (descuento máximo) y <strong>+300%</strong> (aumento máximo).');
            porcentaje.focus();
            return false;
        }

        // Validación adicional para porcentajes extremos
        if (porcentaje.value < -50) {
            if (!confirm('⚠️ ¿Estás seguro de aplicar un descuento mayor al 50%?\n\n' + 
                        'Esto podría afectar significativamente la rentabilidad.')) {
                porcentaje.focus();
                return false;
            }
        }

        if (porcentaje.value > 100) {
            if (!confirm('⚠️ ¿Estás seguro de aplicar un aumento mayor al 100%?\n\n' + 
                        'El precio se duplicará o más.')) {
                porcentaje.focus();
                return false;
            }
        }
        
        if (cantidad.value < 0 || cantidad.value > 99999) {
            mostrarMensaje(contenedor, 'danger', 'La cantidad debe estar entre <strong>0</strong> y <strong>99,999</strong> unidades.');
            cantidad.focus();
            return false;
        }
        
        // Validar archivo solo para formulario principal (no para edición)
        if (form.id === 'formVariante' && (!imagenInput.files || imagenInput.files.length === 0)) {
            mostrarMensaje(contenedor, 'danger', 'Debes seleccionar una imagen para la variante.');
            imagenInput.focus();
            return false;
        }
        
        mostrarMensaje(contenedor, 'success', 'Datos validados correctamente.');
        return true;
    }

    document.getElementById('formEditarVariante').onsubmit = function(e) {
        return validarFormulario(this, document.getElementById('msgModal'));
    };
});

// ===== SISTEMA DE SUBIDA DE IMÁGENES =====

// Datos del producto base
const categoriaProducto = "<?= htmlspecialchars($categoriaAuto) ?>";
const generoProducto = "<?= htmlspecialchars($generoAuto) ?>";
const subcategoriaProducto = "<?= htmlspecialchars($subcategoriaAuto) ?>";

// ===== FUNCIONES PARA FORMULARIO PRINCIPAL =====
function procesarSubidaImagen() {
    const fileInput = document.getElementById('imagenVariante');
    const nombrePersonalizado = document.getElementById('nombrePersonalizado').value.trim();
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Error: Solo se permiten archivos JPG, JPEG, PNG o GIF.');
            fileInput.value = '';
            return;
        }
        
        // Validar tamaño (2MB máximo)
        if (file.size > 2 * 1024 * 1024) {
            alert('❌ Error: La imagen no puede ser mayor a 2MB.');
            fileInput.value = '';
            return;
        }
        
        // Generar nombre del archivo
        let nombreArchivo = nombrePersonalizado;
        if (!nombreArchivo) {
            // Usar el nombre original sin extensión
            nombreArchivo = file.name.replace(/\.[^/.]+$/, "");
        }
        
        // Mantener la extensión original
        const extension = file.name.split('.').pop().toLowerCase();
        nombreArchivo = nombreArchivo + '.' + extension;
        
        // Actualizar interfaz
        document.getElementById('nombreArchivoFinal').value = nombreArchivo;
        const rutaCompleta = `ImgProducto/${categoriaProducto}/${generoProducto}/${subcategoriaProducto}/${nombreArchivo}`;
        document.getElementById('fotoFinal').value = rutaCompleta;
        
        // Mostrar vista previa
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('vistaPreviaImg').src = e.target.result;
            document.getElementById('vistaPreviaContainer').style.display = 'block';
            document.getElementById('infoArchivo').textContent = `Archivo: ${file.name} (${(file.size/1024).toFixed(1)} KB)`;
        };
        reader.readAsDataURL(file);
    }
}

function actualizarRutaDesdeNombre() {
    const fileInput = document.getElementById('imagenVariante');
    const nombrePersonalizado = document.getElementById('nombrePersonalizado').value.trim();
    
    if (fileInput.files && fileInput.files[0] && nombrePersonalizado) {
        const file = fileInput.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        const nombreArchivo = nombrePersonalizado + '.' + extension;
        
        document.getElementById('nombreArchivoFinal').value = nombreArchivo;
        const rutaCompleta = `ImgProducto/${categoriaProducto}/${generoProducto}/${subcategoriaProducto}/${nombreArchivo}`;
        document.getElementById('fotoFinal').value = rutaCompleta;
    }
}

// ===== FUNCIONES PARA MODAL DE EDICIÓN =====
function mostrarImagenActualEdit(rutaExistente) {
    const partes = rutaExistente.split('/');
    const nombreArchivo = partes.length > 0 ? partes[partes.length - 1] : rutaExistente;
    
    document.getElementById('edit_nombreArchivoFinal').value = nombreArchivo;
    document.getElementById('edit_fotoFinal').value = rutaExistente;
    
    // Mostrar imagen actual
    const urlCompleta = '<?= BASE_URL ?>' + rutaExistente;
    document.getElementById('imagenActual').src = urlCompleta;
    document.getElementById('rutaActual').textContent = `Ruta actual: ${rutaExistente}`;
    document.getElementById('imagenActualContainer').style.display = 'block';
    
    // Verificar si la imagen existe
    document.getElementById('imagenActual').onerror = function() {
        document.getElementById('imagenActualContainer').style.display = 'none';
    };
}

function procesarSubidaImagenEdit() {
    const fileInput = document.getElementById('imagenVarianteEdit');
    const nombrePersonalizado = document.getElementById('edit_nombrePersonalizado').value.trim();
    
    if (fileInput.files && fileInput.files[0]) {
        const file = fileInput.files[0];
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            alert('❌ Error: Solo se permiten archivos JPG, JPEG, PNG o GIF.');
            fileInput.value = '';
            return;
        }
        
        // Validar tamaño (2MB máximo)
        if (file.size > 2 * 1024 * 1024) {
            alert('❌ Error: La imagen no puede ser mayor a 2MB.');
            fileInput.value = '';
            return;
        }
        
        // Generar nombre del archivo
        let nombreArchivo = nombrePersonalizado;
        if (!nombreArchivo) {
            // Usar el nombre original sin extensión
            nombreArchivo = file.name.replace(/\.[^/.]+$/, "");
        }
        
        // Mantener la extensión original
        const extension = file.name.split('.').pop().toLowerCase();
        nombreArchivo = nombreArchivo + '.' + extension;
        
        // Actualizar interfaz
        document.getElementById('edit_nombreArchivoFinal').value = nombreArchivo;
        const rutaCompleta = `ImgProducto/${categoriaProducto}/${generoProducto}/${subcategoriaProducto}/${nombreArchivo}`;
        document.getElementById('edit_fotoFinal').value = rutaCompleta;
        
        // Ocultar imagen actual y mostrar vista previa de nueva imagen
        document.getElementById('imagenActualContainer').style.display = 'none';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('edit_vistaPreviaImg').src = e.target.result;
            document.getElementById('edit_vistaPreviaContainer').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        // Si no hay archivo seleccionado, restaurar imagen actual
        const rutaExistente = document.getElementById('edit_fotoFinal').value;
        if (rutaExistente) {
            document.getElementById('imagenActualContainer').style.display = 'block';
        }
        document.getElementById('edit_vistaPreviaContainer').style.display = 'none';
    }
}

function actualizarRutaDesdeNombreEdit() {
    const fileInput = document.getElementById('imagenVarianteEdit');
    const nombrePersonalizado = document.getElementById('edit_nombrePersonalizado').value.trim();
    
    if (fileInput.files && fileInput.files[0] && nombrePersonalizado) {
        const file = fileInput.files[0];
        const extension = file.name.split('.').pop().toLowerCase();
        const nombreArchivo = nombrePersonalizado + '.' + extension;
        
        document.getElementById('edit_nombreArchivoFinal').value = nombreArchivo;
        const rutaCompleta = `ImgProducto/${categoriaProducto}/${generoProducto}/${subcategoriaProducto}/${nombreArchivo}`;
        document.getElementById('edit_fotoFinal').value = rutaCompleta;
    }
}
</script>