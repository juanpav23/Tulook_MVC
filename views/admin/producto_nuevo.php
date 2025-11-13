<h2>Nuevo Producto Base</h2>

<form action="<?php echo BASE_URL; ?>?c=Admin&a=guardarProductoBase" method="POST" enctype="multipart/form-data">
  <div class="mb-3">
    <label>Nombre del Artículo:</label>
    <input type="text" name="N_Articulo" class="form-control" required>
  </div>

  <div class="row">
    <div class="col-md-4">
      <label>Categoría:</label>
      <select name="ID_Categoria" class="form-select">
        <?php foreach ($categorias as $cat): ?>
          <option value="<?php echo $cat['ID_Categoria']; ?>"><?php echo $cat['N_Categoria']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label>Subcategoría:</label>
      <select name="ID_SubCategoria" class="form-select">
        <?php foreach ($subcategorias as $sub): ?>
          <option value="<?php echo $sub['ID_SubCategoria']; ?>"><?php echo $sub['SubCategoria']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label>Género:</label>
      <select name="ID_Genero" class="form-select">
        <?php foreach ($generos as $g): ?>
          <option value="<?php echo $g['ID_Genero']; ?>"><?php echo $g['N_Genero']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="mt-3">
    <label>Foto principal:</label>
    <input type="file" name="Foto" class="form-control" accept="image/*">
  </div>

  <div class="form-check mt-3">
    <input type="checkbox" name="Activo" value="1" checked class="form-check-input">
    <label class="form-check-label">Activo</label>
  </div>

  <button type="submit" class="btn btn-success mt-3">Guardar producto</button>
</form>
