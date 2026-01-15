<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/colores.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark"><i class="fas fa-plus me-2"></i>Nuevo Color</h2>
        <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card stats-card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Información del Color</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>?c=Color&a=guardar" method="post" id="form-color" novalidate>
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
                                               checked>
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
                                    <div class="color-preview" id="color-preview"></div>
                                    <div class="text-center mt-2">
                                        <small id="preview-text" class="text-primary-light">#FFFFFF</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <button type="reset" class="btn btn-outline-primary-light me-md-2 text-primary-dark">
                                <i class="fas fa-undo me-1"></i> Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Guardar Color
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información importante -->
            <div class="alert alert-warning mt-3">
                <h6 class="text-primary-dark"><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
                <small class="text-primary-light">
                    • Los nombres y códigos hexadecimales deben ser únicos<br>
                    • No podrás eliminar un color que esté siendo usado por productos<br>
                    • Los colores inactivos no aparecerán en los formularios de productos<br>
                    • Use nombres descriptivos y consistentes (ej: "Azul Claro" en lugar de "Azul claro")
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Incluir solo el archivo JS -->
<script src="<?= BASE_URL ?>assets/js/colores.js"></script>