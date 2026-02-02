<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/colores.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark"><i class="fas fa-edit me-2"></i>Editar Color</h2>
        <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card stats-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Editar Información del Color</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>?c=Color&a=actualizar" method="post" id="form-color" novalidate>
                        <input type="hidden" name="ID_Color" value="<?= $color['ID_Color'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="N_Color" class="form-label">
                                        <strong class="text-primary-dark">Nombre del Color *</strong>
                                    </label>
                                    <input type="text" 
                                           class="form-control border-primary" 
                                           id="N_Color" 
                                           name="N_Color" 
                                           required
                                           maxlength="45"
                                           pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+"
                                           title="Solo letras, espacios y guiones"
                                           value="<?= htmlspecialchars($color['N_Color']) ?>"
                                           placeholder="Ej: Rojo, Azul Oscuro, Verde Claro...">
                                    <div class="form-text text-primary-light">
                                        Solo letras, espacios y guiones. Máx. 45 caracteres.
                                    </div>
                                    <div class="invalid-feedback" id="error-nombre">
                                        El nombre solo puede contener letras, espacios y guiones.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="CodigoHex" class="form-label">
                                        <strong class="text-primary-dark">Código Hexadecimal *</strong>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary-light border-primary text-primary-dark">#</span>
                                        <input type="text" 
                                               class="form-control border-primary" 
                                               id="CodigoHex" 
                                               name="CodigoHex" 
                                               required
                                               maxlength="6"
                                               pattern="[0-9A-Fa-f]{6}"
                                               title="6 caracteres hexadecimales (0-9, A-F)"
                                               value="<?= htmlspecialchars(str_replace('#', '', $color['CodigoHex'])) ?>"
                                               placeholder="RRGGBB">
                                        <button type="button" class="btn btn-outline-primary" id="color-picker-btn">
                                            <i class="fas fa-eye-dropper"></i> Seleccionar
                                        </button>
                                    </div>
                                    <div class="form-text text-primary-light">
                                        6 caracteres hexadecimales (0-9, A-F). Ej: FF0000 para rojo.
                                    </div>
                                    <div class="invalid-feedback" id="error-hex">
                                        Código hexadecimal inválido. Use formato RRGGBB.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="Activo" 
                                               name="Activo" 
                                               value="1" 
                                               <?= ($color['Activo'] ?? 1) == 1 ? 'checked' : '' ?>>
                                        <label class="form-check-label text-primary-dark" for="Activo">
                                            <strong>Color Activo</strong>
                                        </label>
                                        <div class="form-text text-primary-light">
                                            Los colores inactivos no estarán disponibles en los formularios de productos.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <!-- Muestra de color -->
                                <div class="mb-4">
                                    <label class="form-label text-primary-dark"><strong>Vista Previa</strong></label>
                                    <div class="color-preview" id="color-preview" style="background-color: <?= $color['CodigoHex'] ?>"></div>
                                    <div class="text-center mt-2">
                                        <small id="preview-text" class="text-primary-light"><?= $color['CodigoHex'] ?></small>
                                    </div>
                                    
                                    <!-- Información del color -->
                                    <div class="mt-4">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="text-primary-dark"><i class="fas fa-info-circle me-2"></i>Información del Color</h6>
                                                <small class="text-primary-light">
                                                    • Productos asociados: <?= $color['productos_asociados'] ?? 0 ?><br>
                                                    • Última modificación: <?= date('d/m/Y H:i') ?>
                                                </small>
                                                <?php if (($color['productos_asociados'] ?? 0) > 0): ?>
                                                    <div class="alert alert-warning mt-2 mb-0 py-2">
                                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                                        <small class="text-primary-dark">
                                                            <strong>Advertencia:</strong> Este color está siendo usado por <?= $color['productos_asociados'] ?> productos. 
                                                            No puede ser desactivado ni eliminado mientras esté en uso.
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-outline-primary-light me-md-2 text-primary-dark">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Actualizar Color
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Incluir solo el archivo JS -->
<script src="<?= BASE_URL ?>assets/js/colores.js"></script>