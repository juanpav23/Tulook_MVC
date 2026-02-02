<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/precio.css">
<div class="container-fluid">
    <!-- Encabezado -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary-dark">
            <i class="fas fa-<?= isset($precio) ? 'edit' : 'plus' ?> me-2"></i>
            <?= isset($precio) ? 'Editar Precio' : 'Nuevo Precio' ?>
        </h2>
        <div>
            <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <?php if (isset($precio)): ?>
                <button type="button" class="btn btn-outline-primary ms-2" onclick="mostrarAyuda()">
                    <i class="fas fa-question-circle me-1"></i> Ayuda
                </button>
            <?php endif; ?>
        </div>
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
                <i class="fas fa-info-circle me-2"></i>
                Información del Precio
            </h5>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>?c=Precio&a=<?= isset($precio) ? 'actualizar' : 'guardar' ?>" 
                  method="post" 
                  id="form-precio">
                
                <?php if (isset($precio)): ?>
                    <input type="hidden" name="ID_precio" value="<?= $precio['ID_precio'] ?>">
                <?php endif; ?>

                <!-- Campo oculto para el valor real -->
                <input type="hidden" id="ValorReal" name="Valor" value="<?= isset($precio) ? number_format($precio['Valor'], 2, '.', '') : '' ?>">

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="ValorVisual" class="form-label text-primary-dark">
                                <strong>Valor del Precio *</strong>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-primary-light border-primary text-white">$</span>
                                <input type="text" 
                                       class="form-control border-primary" 
                                       id="ValorVisual" 
                                       required
                                       value="<?= isset($precio) ? number_format($precio['Valor'], 0, ',', '.') : '' ?>"
                                       placeholder="Ej: 1.000.000">
                                <span class="input-group-text bg-light border-primary">
                                    COP
                                </span>
                            </div>
                            <div class="form-text text-primary-light" id="sugerencia-valor">
                                Ingresa el valor numérico del precio.
                                <span id="valor-preview" class="ms-2 fw-bold"></span>
                            </div>
                            
                            <!-- Sugerencias rápidas -->
                            <div class="mt-2">
                                <small class="text-primary-dark d-block mb-1">Valores sugeridos:</small>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" data-valor="10000">10.000</button>
                                    <button type="button" class="btn btn-outline-primary" data-valor="50000">50.000</button>
                                    <button type="button" class="btn btn-outline-primary" data-valor="100000">100.000</button>
                                    <button type="button" class="btn btn-outline-primary" data-valor="500000">500.000</button>
                                    <button type="button" class="btn btn-outline-primary" data-valor="1000000">1.000.000</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label text-primary-dark">
                                <strong>Estado del Precio</strong>
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="Activo" 
                                       name="Activo" 
                                       value="1"
                                       <?= (isset($precio) && $precio['Activo']) || !isset($precio) ? 'checked' : '' ?>
                                       <?= isset($precio) && $precio['en_uso'] ? 'disabled' : '' ?>>
                                <label class="form-check-label text-primary-dark" for="Activo">
                                    Precio Activo
                                    <?php if (isset($precio) && $precio['en_uso']): ?>
                                        <br><small class="text-primary-light">(No se puede desactivar mientras esté en uso)</small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Información adicional -->
                <?php if (isset($precio)): ?>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert <?= $precio['en_uso'] ? 'alert-primary-light' : 'alert-primary-light' ?>">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small class="text-primary-dark">
                                            <strong><i class="fas fa-calendar-alt me-1"></i>Última Actualización:</strong><br>
                                            <?= date('d/m/Y H:i', strtotime($precio['FechaAct'])) ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <small class="text-primary-dark">
                                            <strong><i class="fas fa-tag me-1"></i>Valor Actual:</strong><br>
                                            $<?= number_format($precio['Valor'], 0, ',', '.') ?>
                                        </small>
                                    </div>
                                    <div class="col-md-4">
                                        <?php if ($precio['en_uso']): ?>
                                            <small class="text-primary-dark">
                                                <strong><i class="fas fa-box me-1"></i>En uso por:</strong><br>
                                                <?= $precio['uso_articulos'] ?? 0 ?> artículo(s) base
                                            </small>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-primary mt-1 btn-ver-productos"
                                                    data-id="<?= $precio['ID_precio'] ?>"
                                                    data-valor="<?= number_format($precio['Valor'], 0, ',', '.') ?>">
                                                <i class="fas fa-eye me-1"></i> Ver artículos
                                            </button>
                                        <?php else: ?>
                                            <small class="text-primary-dark">
                                                <strong><i class="fas fa-box-open me-1"></i>Estado:</strong><br>
                                                Este precio no está siendo usado por ningún artículo
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Advertencia para precios en uso -->
                    <?php if ($precio['en_uso']): ?>
                        <div class="alert alert-primary-light mt-2 border-start border-3 border-primary">
                            <h6 class="text-primary-dark"><i class="fas fa-exclamation-triangle me-2"></i>Importante</h6>
                            <small class="text-primary-dark">
                                • Este precio está siendo usado por artículos base<br>
                                • No se puede eliminar mientras esté en uso<br>
                                • No se puede desactivar mientras esté en uso<br>
                                • Para eliminar, primero migra los artículos a otro precio
                            </small>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-primary mt-2 btn-migrar-precio"
                                    data-id="<?= $precio['ID_precio'] ?>"
                                    data-valor="<?= number_format($precio['Valor'], 0, ',', '.') ?>">
                                <i class="fas fa-exchange-alt me-1"></i> Migrar artículos
                            </button>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Botones -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between">
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save me-1"></i>
                                    <?= isset($precio) ? 'Actualizar Precio' : 'Crear Precio' ?>
                                </button>
                                <a href="<?= BASE_URL ?>?c=Precio&a=index" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </a>
                            </div>
                            
                            <?php if (isset($precio) && !$precio['en_uso']): ?>
                                <div>
                                    <a href="<?= BASE_URL ?>?c=Precio&a=eliminar&id=<?= $precio['ID_precio'] ?>" 
                                       class="btn btn-outline-danger btn-eliminar-precio"
                                       data-id="<?= $precio['ID_precio'] ?>"
                                       data-valor="<?= number_format($precio['Valor'], 0, ',', '.') ?>">
                                        <i class="fas fa-trash me-1"></i> Eliminar
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sugerencias de valores (solo en creación) -->
    <?php if (!isset($precio) && isset($sugerencias)): ?>
        <div class="card mt-3 stats-card">
            <div class="card-header bg-light">
                <h6 class="mb-0 text-primary-dark"><i class="fas fa-lightbulb me-2"></i>Sugerencias de Valores</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-primary-dark mb-2">Rango Bajo</h6>
                        <div class="btn-group-vertical w-100" role="group">
                            <?php foreach ($sugerencias['bajos'] as $valor): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary-light text-start mb-1 text-primary-dark btn-sugerencia"
                                        data-valor="<?= $valor ?>">
                                    <i class="fas fa-tag me-1"></i> $<?= number_format($valor, 0, ',', '.') ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary-dark mb-2">Rango Medio</h6>
                        <div class="btn-group-vertical w-100" role="group">
                            <?php foreach ($sugerencias['medios'] as $valor): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary-light text-start mb-1 text-primary-dark btn-sugerencia"
                                        data-valor="<?= $valor ?>">
                                    <i class="fas fa-tag me-1"></i> $<?= number_format($valor, 0, ',', '.') ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-primary-dark mb-2">Rango Alto</h6>
                        <div class="btn-group-vertical w-100" role="group">
                            <?php foreach ($sugerencias['altos'] as $valor): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary-light text-start mb-1 text-primary-dark btn-sugerencia"
                                        data-valor="<?= $valor ?>">
                                    <i class="fas fa-tag me-1"></i> $<?= number_format($valor, 0, ',', '.') ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Información importante -->
    <div class="alert alert-primary-light mt-3">
        <h6 class="text-primary-dark"><i class="fas fa-info-circle me-2"></i>Información Importante</h6>
        <small class="text-primary-dark">
            • Los precios se guardan como números enteros (sin decimales)<br>
            • Ingresa el valor completo (ej: 1000000 para un millón)<br>
            • El sistema formatea visualmente pero guarda el valor numérico completo<br>
            • Los precios en uso no se pueden eliminar ni desactivar
        </small>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/precio.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>