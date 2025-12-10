<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-<?= isset($atributo) ? 'edit' : 'plus' ?> me-2"></i>
            <?= isset($atributo) ? 'Editar Atributo' : 'Nuevo Atributo' ?>
        </h2>
        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    <!-- Mensajes -->
    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['mensaje_tipo'] ?? 'info' ?> alert-dismissible fade show" role="alert">
            <?= $_SESSION['mensaje'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['mensaje_tipo']); ?>
    <?php endif; ?>

    <!-- Formulario -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="fas fa-cog me-2"></i>
                Información del Atributo
            </h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Atributo&a=<?= isset($atributo) ? 'actualizar' : 'guardar' ?>" method="post" id="formAtributo">
                <?php if (isset($atributo)): ?>
                    <input type="hidden" name="ID_AtributoValor" value="<?= $atributo['ID_AtributoValor'] ?>">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="ID_TipoAtributo" class="form-label">
                                <strong>Tipo de Atributo *</strong>
                            </label>
                            <select class="form-select" id="ID_TipoAtributo" name="ID_TipoAtributo" required>
                                <option value="">Seleccionar tipo...</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?= $tipo['ID_TipoAtributo'] ?>"
                                        <?= (isset($atributo) && $atributo['ID_TipoAtributo'] == $tipo['ID_TipoAtributo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['Nombre']) ?>
                                        <?php if (!empty($tipo['Descripcion'])): ?>
                                            <small class="text-muted"> - <?= htmlspecialchars($tipo['Descripcion']) ?></small>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Selecciona la categoría del atributo
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Valor" class="form-label">
                                <strong>Valor del Atributo *</strong>
                            </label>
                            
                            <!-- Campo de entrada simple -->
                            <input type="text" 
                                   class="form-control" 
                                   id="Valor" 
                                   name="Valor" 
                                   required
                                   maxlength="50"
                                   value="<?= isset($atributo) ? htmlspecialchars($atributo['Valor']) : '' ?>"
                                   placeholder="Ingresa el valor del atributo">
                            
                            <!-- Texto de ejemplo según el tipo seleccionado -->
                            <div class="form-text" id="ejemploTexto">
                                <span id="ejemplo">Ej: M, 32, Mediano, 100 ml, etc.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                <strong>Estado del Atributo</strong>
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="Activo" 
                                       name="Activo" 
                                       value="1"
                                       <?= (isset($atributo) && $atributo['Activo']) || !isset($atributo) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="Activo">
                                    Atributo Activo
                                </label>
                            </div>
                            <div class="form-text">
                                Los atributos inactivos no estarán disponibles para nuevos productos
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <small>
                                <strong><i class="fas fa-info-circle me-1"></i>Información:</strong><br>
                                <?php if (isset($atributo)): ?>
                                    • ID: #<?= $atributo['ID_AtributoValor'] ?><br>
                                    • Tipo: <?= htmlspecialchars($atributo['TipoNombre']) ?><br>
                                    • Orden: <span class="badge bg-info">Automático (<?= $atributo['Orden'] ?>)</span><br>
                                    • Estado: 
                                    <span class="badge bg-<?= $atributo['Activo'] ? 'success' : 'secondary' ?>">
                                        <?= $atributo['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                <?php else: ?>
                                    • El <strong>orden es automático</strong><br>
                                    • Se excluyen los colores (gestión separada)<br>
                                    • Valores únicos por tipo
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-success me-2">
                            <i class="fas fa-save me-1"></i>
                            <?= isset($atributo) ? 'Actualizar Atributo' : 'Crear Atributo' ?>
                        </button>
                        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Ejemplos de valor por tipo
const ejemplosPorTipo = {
    'talla': 'Ej: M, 32, XL, S, 40, Única, etc.',
    'medida': 'Ej: 18, 30, Ajuste Estándar, Correa Larga, etc.',
    'volumen': 'Ej: 100 ml, 50 ml, 30 ml, 150 ml, etc.',
    'tamaño': 'Ej: Mediano, Grande, Pequeño, Extra Grande'
};

// Actualizar el texto de ejemplo según el tipo seleccionado
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelect = document.getElementById('ID_TipoAtributo');
    const ejemploSpan = document.getElementById('ejemplo');
    
    function actualizarEjemplo() {
        const selectedOption = tipoSelect.options[tipoSelect.selectedIndex];
        const tipoNombre = selectedOption.textContent.toLowerCase();
        
        // Buscar qué tipo se seleccionó
        for (const [tipo, ejemplo] of Object.entries(ejemplosPorTipo)) {
            if (tipoNombre.includes(tipo)) {
                ejemploSpan.textContent = ejemplo;
                return;
            }
        }
        
        // Si no se encuentra, mostrar ejemplo general
        ejemploSpan.textContent = 'Ej: M, 32, Mediano, 100 ml, etc.';
    }
    
    // Actualizar al cambiar el tipo
    tipoSelect.addEventListener('change', actualizarEjemplo);
    
    // Actualizar al cargar si ya hay un tipo seleccionado
    if (tipoSelect.value) {
        actualizarEjemplo();
    }
});
</script>