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
            <p><strong>🎨 Color base:</strong> <?= htmlspecialchars($articulo['ColorBaseNombre'] ?? '—'); ?></p>
            <p><strong>📂 Categoría:</strong> <?= htmlspecialchars($categoriaAuto); ?></p>
            <p><strong>👥 Género:</strong> <?= htmlspecialchars($generoAuto); ?></p>
            <p><strong>📁 Subcategoría:</strong> <?= htmlspecialchars($subcategoriaAuto); ?></p>
            <p><strong>⚙️ Estado:</strong> 
                <span class="badge <?= $articulo['Activo'] ? 'bg-success' : 'bg-secondary' ?>">
                    <?= $articulo['Activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
            </p>
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
                    <th>Color</th>
                    <th>Talla</th>
                    <th>% Precio</th>
                    <th>Precio Final</th>
                    <th>Cantidad</th>
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

                        $nombreVariante = !empty($v['Nombre_Producto']) 
                            ? $v['Nombre_Producto'] 
                            : $articulo['N_Articulo'] . ' ' . $v['N_Color'] . ' ' . $v['N_Talla'];
                        ?>
                        <tr>
                            <td><img src="<?= htmlspecialchars($rutaVar); ?>" width="70" class="rounded border shadow-sm"></td>
                            <td><?= htmlspecialchars($nombreVariante); ?></td>
                            <td><?= htmlspecialchars($v['N_Color']); ?></td>
                            <td><?= htmlspecialchars($v['N_Talla']); ?></td>
                            <td><?= htmlspecialchars($v['Porcentaje']); ?>%</td>
                            <td><?= formatoPrecio($precioFinal); ?></td>
                            <td><?= htmlspecialchars($v['Cantidad']); ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-warning" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditarVariante"
                                            data-id="<?= $v['ID_Producto']; ?>"
                                            data-nombre="<?= htmlspecialchars($v['Nombre_Producto'] ?? ''); ?>"
                                            data-color="<?= $v['ID_Color']; ?>"
                                            data-talla="<?= $v['ID_Talla']; ?>"
                                            data-porcentaje="<?= $v['Porcentaje']; ?>"
                                            data-cantidad="<?= $v['Cantidad']; ?>"
                                            data-foto="<?= htmlspecialchars($v['Foto'] ?? ''); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <a href="<?= BASE_URL ?>?c=Admin&a=eliminarVariante&id=<?= $v['ID_Producto'] ?>&articulo=<?= $articulo['ID_Articulo'] ?>" 
                                       class="btn btn-danger"
                                       onclick="return confirm('⚠️ ¿Seguro que deseas eliminar esta variante? Esta acción no se puede deshacer.')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-muted">No hay variantes registradas aún.</td></tr>
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
            <input type="text" name="Nombre_Producto" class="form-control" placeholder="Ej: Boxer Azul" required>
        </div>

        <div class="col-md-3">
            <label class="form-label fw-bold">Color</label>
            <select name="ID_Color" class="form-select" required>
                <option value="" disabled selected>Seleccionar...</option>
                <?php foreach ($colors as $c): ?>
                    <option value="<?= $c['ID_Color']; ?>"><?= htmlspecialchars($c['N_Color']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold">Talla</label>
            <select name="ID_Talla" class="form-select" required>
                <option value="" disabled selected>Seleccionar...</option>
                <?php foreach ($tallas as $t): ?>
                    <option value="<?= $t['ID_Talla']; ?>"><?= htmlspecialchars($t['N_Talla']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold">% Precio</label>
            <input type="number" step="0.1" name="Porcentaje" class="form-control" placeholder="Ej: 10" min="0" max="100" required>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold">Cantidad</label>
            <input type="number" name="Cantidad" class="form-control" placeholder="Ej: 50" required>
        </div>

        <!-- NUEVO: Sistema automático de subida de imagen -->
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

<!-- MODAL PARA EDITAR VARIANTE -->
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
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Color</label>
                                <select name="ID_Color" class="form-select" required id="edit_ID_Color">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($colors as $c): ?>
                                        <option value="<?= $c['ID_Color']; ?>"><?= htmlspecialchars($c['N_Color']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Talla</label>
                                <select name="ID_Talla" class="form-select" required id="edit_ID_Talla">
                                    <option value="">Seleccionar...</option>
                                    <?php foreach ($tallas as $t): ?>
                                        <option value="<?= $t['ID_Talla']; ?>"><?= htmlspecialchars($t['N_Talla']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">% Precio</label>
                                <input type="number" step="0.1" name="Porcentaje" class="form-control" id="edit_Porcentaje" min="0" max="100" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Cantidad</label>
                                <input type="number" name="Cantidad" class="form-control" id="edit_Cantidad" min="0" max="100" required>
                            </div>
                        </div>
                    </div>

                    <!-- NUEVO: Sistema automático de subida para edición -->
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
document.addEventListener('DOMContentLoaded', function() {
    const modalEditar = document.getElementById('modalEditarVariante');

    modalEditar.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        document.getElementById('edit_ID_Producto').value = btn.getAttribute('data-id');
        document.getElementById('edit_Nombre_Producto').value = btn.getAttribute('data-nombre') || '';
        document.getElementById('edit_ID_Color').value = btn.getAttribute('data-color');
        document.getElementById('edit_ID_Talla').value = btn.getAttribute('data-talla');
        document.getElementById('edit_Porcentaje').value = btn.getAttribute('data-porcentaje');
        document.getElementById('edit_Cantidad').value = btn.getAttribute('data-cantidad');
        
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

    document.addEventListener('DOMContentLoaded', function() {
        const formVariante = document.querySelector('form[action*="agregarVariante"], form[action*="editarVariante"]');
        
        if (formVariante) {
            formVariante.onsubmit = function(e) {
                const idColor = this.querySelector('select[name="ID_Color"]').value;
                const idTalla = this.querySelector('select[name="ID_Talla"]').value;
                
                if (!idColor || !idTalla) {
                    alert('Por favor, selecciona color y talla.');
                    return false;
                }
                
                return true;
            };
        }
    });

    function mostrarMensaje(contenedor, tipo, texto) {
        contenedor.innerHTML = `
            <div class="alert alert-${tipo} alert-dismissible fade show mt-2 shadow-sm">
                <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i>
                ${texto}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
    }

    function validarFormulario(form, contenedor) {
        const porcentaje = form.querySelector('[name="Porcentaje"]');
        const cantidad = form.querySelector('[name="Cantidad"]');
        const imagenInput = form.querySelector('input[type="file"]');
        
        contenedor.innerHTML = '';

        if (porcentaje.value < 0 || porcentaje.value > 100) {
            mostrarMensaje(contenedor, 'danger', '⚠️ El porcentaje debe estar entre <strong>0</strong> y <strong>100</strong>.');
            porcentaje.focus();
            return false;
        }
        if (cantidad.value < 0 || cantidad.value > 100) {
            mostrarMensaje(contenedor, 'danger', '⚠️ La cantidad debe estar entre <strong>0</strong> y <strong>100</strong> unidades.');
            cantidad.focus();
            return false;
        }
        
        // Validar archivo solo para formulario principal (no para edición)
        if (form.id === 'formVariante' && (!imagenInput.files || imagenInput.files.length === 0)) {
            mostrarMensaje(contenedor, 'danger', '⚠️ Debes seleccionar una imagen para la variante.');
            imagenInput.focus();
            return false;
        }
        
        mostrarMensaje(contenedor, 'success', '✅ Datos validados correctamente.');
        return true;
    }

    document.getElementById('formVariante').onsubmit = function(e) {
        return validarFormulario(this, document.getElementById('msgContainer'));
    };

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