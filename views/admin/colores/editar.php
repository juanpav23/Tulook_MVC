<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-edit me-2"></i>Editar Color</h2>
        <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Formulario -->
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-palette me-2"></i>Editar Información del Color</h5>
                </div>
                <div class="card-body">
                    <form action="<?= BASE_URL ?>?c=Color&a=actualizar" method="post" id="form-color">
                        <input type="hidden" name="ID_Color" value="<?= $color['ID_Color'] ?>">
                        
                        <div class="mb-3">
                            <label for="N_Color" class="form-label">
                                <strong>Nombre del Color *</strong>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="N_Color" 
                                   name="N_Color" 
                                   required
                                   maxlength="45"
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+"
                                   title="Solo letras, espacios y guiones"
                                   value="<?= htmlspecialchars($color['N_Color']) ?>"
                                   placeholder="Ej: Rojo, Azul Oscuro, Verde Claro...">
                            <div class="form-text">
                                Solo letras, espacios y guiones. Máx. 45 caracteres.
                            </div>
                            <div class="invalid-feedback" id="error-nombre">
                                El nombre solo puede contener letras, espacios y guiones.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="CodigoHex" class="form-label">
                                <strong>Código Hexadecimal *</strong>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">#</span>
                                <input type="text" 
                                       class="form-control" 
                                       id="CodigoHex" 
                                       name="CodigoHex" 
                                       required
                                       maxlength="6"
                                       pattern="[0-9A-Fa-f]{6}"
                                       title="6 caracteres hexadecimales (0-9, A-F)"
                                       value="<?= htmlspecialchars(str_replace('#', '', $color['CodigoHex'])) ?>"
                                       placeholder="RRGGBB">
                                <button type="button" class="btn btn-outline-secondary" id="pick-color">
                                    <i class="fas fa-eye-dropper"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                6 caracteres hexadecimales (0-9, A-F). Ej: FF0000 para rojo.
                            </div>
                            <div class="invalid-feedback" id="error-hex">
                                Código hexadecimal inválido. Use formato RRGGBB.
                            </div>
                        </div>

                        <!-- Muestra de color -->
                        <div class="mb-4">
                            <label class="form-label"><strong>Vista Previa</strong></label>
                            <div id="color-preview" 
                                 style="width: 100%; 
                                        height: 60px; 
                                        border: 1px solid #ddd; 
                                        border-radius: 4px;
                                        background-color: <?= $color['CodigoHex'] ?>;">
                            </div>
                            <small id="preview-text" class="text-muted"><?= $color['CodigoHex'] ?></small>
                        </div>

                        <!-- Botones -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-1"></i> Actualizar Color
                            </button>
                            <a href="<?= BASE_URL ?>?c=Color&a=index" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Información del color -->
            <div class="alert alert-info mt-3">
                <h6><i class="fas fa-info-circle me-2"></i>Información del Color</h6>
                <small>
                    • ID: #<?= $color['ID_Color'] ?><br>
                    • Nombre actual: <?= htmlspecialchars($color['N_Color']) ?><br>
                    • Código HEX actual: <?= htmlspecialchars($color['CodigoHex']) ?>
                </small>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('form-color');
    const nombreInput = document.getElementById('N_Color');
    const hexInput = document.getElementById('CodigoHex');
    const colorPreview = document.getElementById('color-preview');
    const previewText = document.getElementById('preview-text');
    const pickColorBtn = document.getElementById('pick-color');

    // Inicializar vista previa
    const hexValue = hexInput.value.toUpperCase();
    colorPreview.style.backgroundColor = '#' + hexValue;
    previewText.textContent = '#' + hexValue;

    // Validación en tiempo real del nombre
    nombreInput.addEventListener('input', function() {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/;
        if (!regex.test(this.value) && this.value !== '') {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Validación en tiempo real del código HEX
    hexInput.addEventListener('input', function() {
        const hex = this.value.toUpperCase();
        this.value = hex;
        
        const regex = /^[0-9A-F]{6}$/;
        if (regex.test(hex)) {
            this.classList.remove('is-invalid');
            colorPreview.style.backgroundColor = '#' + hex;
            previewText.textContent = '#' + hex;
        } else if (hex.length === 6) {
            this.classList.add('is-invalid');
        } else {
            this.classList.remove('is-invalid');
        }
    });

    // Selector de color (si el navegador lo soporta)
    if (pickColorBtn) {
        pickColorBtn.addEventListener('click', function() {
            if (window.EyeDropper) {
                const eyeDropper = new EyeDropper();
                eyeDropper.open()
                    .then(result => {
                        const hex = result.sRGBHex.substring(1); // Quita el #
                        hexInput.value = hex.toUpperCase();
                        hexInput.dispatchEvent(new Event('input'));
                    })
                    .catch(e => {
                        console.log('Selector de color no disponible');
                    });
            } else {
                alert('Tu navegador no soporta el selector de color. Ingresa el código manualmente.');
            }
        });
    }

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validar nombre
        const nombreRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-]+$/;
        if (!nombreRegex.test(nombreInput.value.trim())) {
            nombreInput.classList.add('is-invalid');
            valid = false;
        }
        
        // Validar HEX - Asegurar que tenga 6 caracteres hexadecimales
        const hexRegex = /^[0-9A-F]{6}$/i;
        if (!hexRegex.test(hexInput.value)) {
            hexInput.classList.add('is-invalid');
            valid = false;
        }
        
        // Opcional: Agregar automáticamente el # antes de enviar
        if (valid) {
            // Si el valor no tiene #, agregarlo automáticamente
            if (hexInput.value.charAt(0) !== '#') {
                hexInput.value = '#' + hexInput.value.toUpperCase();
            }
        }
        
        if (!valid) {
            e.preventDefault();
            alert('Por favor, corrija los errores en el formulario.');
        }
    });
});
</script>