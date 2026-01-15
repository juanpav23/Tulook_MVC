<?php
// Verificar si es el atributo "Única"
$esUnica = isset($atributo) && ($atributo['ID_AtributoValor'] == 16 || strtolower($atributo['Valor']) === 'única');
?>

<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/atributos.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark">
            <i class="fas fa-<?= isset($atributo) ? 'edit' : 'plus' ?> me-2"></i>
            <?= isset($atributo) ? 'Editar Atributo' : 'Nuevo Atributo' ?>
        </h2>
        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-outline-primary-light text-primary-dark">
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
    <div class="card stats-card">
        <div class="card-header bg-light">
            <h5 class="mb-0 text-primary-dark">
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
                                <strong class="text-primary-dark">Tipo de Atributo *</strong>
                            </label>
                            <select class="form-select" id="ID_TipoAtributo" name="ID_TipoAtributo" required 
                                    <?= $esUnica ? 'disabled' : '' ?>>
                                <option value="">Seleccionar tipo...</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?= $tipo['ID_TipoAtributo'] ?>"
                                        <?= (isset($atributo) && $atributo['ID_TipoAtributo'] == $tipo['ID_TipoAtributo']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tipo['Nombre']) ?>
                                        <?php if (!empty($tipo['Descripcion'])): ?>
                                            <small class="text-primary-light"> - <?= htmlspecialchars($tipo['Descripcion']) ?></small>
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($esUnica): ?>
                                <input type="hidden" name="ID_TipoAtributo" value="<?= $atributo['ID_TipoAtributo'] ?>">
                            <?php endif; ?>
                            <div class="form-text text-primary-light">
                                Selecciona la categoría del atributo
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="Valor" class="form-label">
                                <strong class="text-primary-dark">Valor del Atributo *</strong>
                            </label>
                            
                            <input type="text" 
                                   class="form-control" 
                                   id="Valor" 
                                   name="Valor" 
                                   required
                                   maxlength="50"
                                   value="<?= isset($atributo) ? htmlspecialchars($atributo['Valor']) : '' ?>"
                                   placeholder="Ingresa el valor del atributo"
                                   <?= $esUnica ? 'readonly' : '' ?>>
                            
                            <div class="form-text" id="ejemploTexto">
                                <span id="ejemplo" class="text-primary-light">Ej: M, 32, Mediano, 100 ml, etc.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">
                                <strong class="text-primary-dark">Estado del Atributo</strong>
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="Activo" 
                                       name="Activo" 
                                       value="1"
                                       <?= (isset($atributo) && $atributo['Activo']) || !isset($atributo) ? 'checked' : '' ?>
                                       <?= $esUnica ? 'disabled' : '' ?>>
                                <label class="form-check-label text-primary-dark" for="Activo">
                                    Atributo Activo
                                </label>
                                <?php if ($esUnica): ?>
                                    <input type="hidden" name="Activo" value="1">
                                <?php endif; ?>
                            </div>
                            <div class="form-text text-primary-light">
                                Los atributos inactivos no estarán disponibles para nuevos productos
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="alert alert-<?= $esUnica ? 'warning' : 'primary-light' ?> border border-<?= $esUnica ? 'warning' : 'primary' ?>">
                            <small>
                                <strong class="text-primary-dark"><i class="fas fa-info-circle me-1"></i>Información:</strong><br>
                                <?php if (isset($atributo)): ?>
                                    • ID: <span class="text-primary-dark">#<?= $atributo['ID_AtributoValor'] ?></span><br>
                                    • Tipo: <?= htmlspecialchars($atributo['TipoNombre']) ?><br>
                                    • Orden: <span class="badge bg-primary-light text-primary-dark border border-primary">Automático (<?= $atributo['Orden'] ?>)</span><br>
                                    • Estado: 
                                    <span class="badge bg-<?= $atributo['Activo'] ? 'primary' : 'secondary' ?>">
                                        <?= $atributo['Activo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                    <?php if ($esUnica): ?>
                                        <br>• <span class="text-warning"><i class="fas fa-shield-alt me-1"></i> Valor universal del sistema</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    • El <strong class="text-primary-dark">orden es automático</strong><br>
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
                        <button type="submit" class="btn btn-primary me-2" <?= $esUnica ? 'disabled' : '' ?>>
                            <i class="fas fa-save me-1"></i>
                            <?= isset($atributo) ? 'Actualizar Atributo' : 'Crear Atributo' ?>
                        </button>
                        <a href="<?= BASE_URL ?>?c=Atributo&a=index" class="btn btn-outline-primary-light text-primary-dark">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        
                        <?php if ($esUnica): ?>
                            <div class="alert alert-warning mt-3 p-2">
                                <small>
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Este valor universal no puede ser editado. Solo puede ser consultado.
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/atributo.js"></script>