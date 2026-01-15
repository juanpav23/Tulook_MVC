<h2 class="fw-bold text-primary-dark mb-4"><i class="fas fa-plus-circle"></i> Añadir Variante al Producto</h2>

<form action="<?php echo BASE_URL; ?>?c=Admin&a=guardarVariante" method="POST" enctype="multipart/form-data" class="form-detalle-variante">
    <input type="hidden" name="ID_Articulo" value="<?php echo $idArticulo; ?>">

    <div class="row">
        <div class="col-md-4">
            <label class="form-label fw-bold text-primary-dark">Nombre de la Variante:</label>
            <input type="text" name="Nombre_Producto" class="form-control" placeholder="Ej: Boxer Azul, Camiseta Roja..." required>
        </div>

        <!-- ATRIBUTOS DINÁMICOS BASADOS EN LA SUBCATEGORÍA -->
        <?php if (isset($atributosData) && !empty($atributosData)): ?>
            <?php foreach ($atributosData as $index => $atributo): ?>
                <?php $numero = $index + 1; ?>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-primary-dark"><?= htmlspecialchars($atributo['tipo']['Nombre']) ?></label>
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
        <?php else: ?>
            <div class="col-md-12">
                <div class="alert alert-primary-dark">
                    <i class="fas fa-info-circle"></i> No hay atributos configurados para esta subcategoría.
                </div>
            </div>
        <?php endif; ?>

        <!-- Permitir porcentajes negativos -->
        <div class="col-md-2">
            <label class="form-label fw-bold text-primary-dark">Variación Precio (%):</label>
            <input type="number" name="Porcentaje" class="form-control" 
                  min="-90" max="300" step="0.1" 
                  placeholder="Ej: 10 o -10" required>
            <small class="form-text text-muted">
                <span class="text-primary-dark">Positivo: Aumento</span> | 
                <span class="text-primary-light-detalle">Negativo: Descuento</span><br>
                Rango: -90% a +300%
            </small>
        </div>

        <div class="col-md-2">
            <label class="form-label fw-bold text-primary-dark">Cantidad:</label>
            <input type="number" name="Cantidad" class="form-control" min="0" max="99999" value="0" required>
            <small class="form-text text-muted">Máx: 99,999</small>
        </div>

        <div class="col-md-3 mt-3">
            <label class="form-label fw-bold text-primary-dark">Foto (15MB máximo):</label>
            <input type="file" name="imagen_variante" class="form-control" accept="image/*" required>
            <small class="form-text text-muted">Formatos: JPG, PNG, GIF, WebP</small>
        </div>

        <div class="col-md-2 mt-3">
            <label class="form-label fw-bold text-primary-dark">Estado:</label>
            <select name="Activo" class="form-select" required>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
    </div>

    <div class="mt-4">
        <button type="submit" class="btn btn-detalle-primary">Guardar Variante</button>
        <a href="<?= BASE_URL ?>?c=Admin&a=detalleProducto&id=<?= $idArticulo ?>" class="btn btn-detalle-outline-primary-light">Cancelar</a>
    </div>
</form>

<!-- Incluir CSS específico para detalle de variantes -->
<link rel="stylesheet" href="<?= BASE_URL; ?>assets/css/detalleVarianteAdmin.css">