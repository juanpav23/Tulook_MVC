<h2>Añadir Variante al Producto</h2>

<form action="<?php echo BASE_URL; ?>?c=Admin&a=guardarVariante" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="ID_Articulo" value="<?php echo $idArticulo; ?>">

  <div class="row">
    <div class="col-md-4">
      <label>Nombre de la Variante:</label>
      <input type="text" name="Nombre_Producto" class="form-control" placeholder="Ej: Boxer Azul, Camiseta Roja..." required>
    </div>

    <!-- ATRIBUTOS DINÁMICOS BASADOS EN LA SUBCATEGORÍA -->
    <?php
    // Obtener información de la subcategoría para mostrar atributos dinámicos
    if (isset($atributosData) && !empty($atributosData)):
      foreach ($atributosData as $index => $atributo):
        $numero = $index + 1;
    ?>
    <div class="col-md-3">
      <div class="mb-3">
        <label class="form-label fw-bold"><?= htmlspecialchars($atributo['tipo']['Nombre']) ?></label>
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
    <?php
      endforeach;
    else:
    ?>
    <div class="col-md-12">
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No hay atributos configurados para esta subcategoría.
      </div>
    </div>
    <?php endif; ?>

    <!-- CAMBIO: Permitir porcentajes negativos -->
    <div class="col-md-2">
        <label>Variación Precio (%):</label>
        <input type="number" name="Porcentaje" class="form-control" 
              min="-90" max="300" step="0.1" 
              placeholder="Ej: 10 o -10" required>
        <small class="form-text text-muted">
            <span class="text-success">Positivo: Aumento</span> | 
            <span class="text-danger">Negativo: Descuento</span><br>
            Rango: -90% a +300%
        </small>
    </div>

    <div class="col-md-2">
      <label>Cantidad:</label>
      <input type="number" name="Cantidad" class="form-control" min="0" max="99999" value="0" required>
      <small class="form-text text-muted">Máx: 99,999</small>
    </div>

    <div class="col-md-3 mt-3">
      <label>Foto:</label>
      <input type="file" name="imagen_variante" class="form-control" accept="image/*" required>
      <input type="hidden" name="Foto" id="fotoFinal">
    </div>

    <div class="col-md-2 mt-3">
      <label>Estado:</label>
      <select name="Activo" class="form-select" required>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>
  </div>

  <button type="submit" class="btn btn-primary mt-3">Guardar Variante</button>
  <a href="<?= BASE_URL ?>?c=Admin&a=detalleProducto&id=<?= $idArticulo ?>" class="btn btn-secondary mt-3">Cancelar</a>
</form>

<script>
// Sistema automático de rutas para imágenes
document.addEventListener('DOMContentLoaded', function() {
  const fileInput = document.querySelector('input[name="imagen_variante"]');
  const fotoFinal = document.getElementById('fotoFinal');
  
  if (fileInput) {
    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const file = this.files[0];
        const fileName = file.name.toLowerCase();
        
        // Validar tipo de archivo
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
          alert('❌ Error: Solo se permiten archivos JPG, JPEG, PNG o GIF.');
          this.value = '';
          return;
        }
        
        // Validar tamaño (2MB máximo)
        if (file.size > 2 * 1024 * 1024) {
          alert('❌ Error: La imagen no puede ser mayor a 2MB.');
          this.value = '';
          return;
        }
        
        // Generar ruta automática (se completará en el controlador)
        fotoFinal.value = 'ImgProducto/' + fileName;
      }
    });
  }
  
  // Validación de porcentaje negativo
  const porcentajeInput = document.querySelector('input[name="Porcentaje"]');
  if (porcentajeInput) {
    porcentajeInput.addEventListener('change', function() {
      const valor = parseFloat(this.value);
      if (valor < -100 || valor > 1000) {
        alert('⚠️ El porcentaje debe estar entre -100% y +1000%');
        this.focus();
      }
    });
  }
});
</script>