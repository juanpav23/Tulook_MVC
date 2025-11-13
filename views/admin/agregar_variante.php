<h2>Añadir Variante al Producto</h2>

<form action="<?php echo BASE_URL; ?>?c=Admin&a=guardarVariante" method="POST" enctype="multipart/form-data">
  <input type="hidden" name="ID_Articulo" value="<?php echo $idArticulo; ?>">

  <div class="row">
    <div class="col-md-4">
      <label>Nombre de la Variante:</label>
      <input type="text" name="Nombre_Producto" class="form-control" placeholder="Ej: Boxer Azul, Camiseta Roja..." required>
    </div>

    <div class="col-md-2">
      <label>Color:</label>
      <select name="ID_Color" class="form-select">
        <?php foreach ($colores as $c): ?>
          <option value="<?php echo $c['ID_Color']; ?>"><?php echo $c['N_Color']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <label>Talla:</label>
      <select name="ID_Talla" class="form-select">
        <?php foreach ($tallas as $t): ?>
          <option value="<?php echo $t['ID_Talla']; ?>"><?php echo $t['N_Talla']; ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <label>Precio:</label>
      <input type="number" name="Precio" class="form-control" min="0" step="0.01" required>
    </div>

    <div class="col-md-2">
      <label>Cantidad:</label>
      <input type="number" name="Cantidad" class="form-control" min="0" required>
    </div>

    <div class="col-md-3 mt-3">
      <label>Foto:</label>
      <input type="file" name="Foto" class="form-control" accept="image/*">
    </div>
  </div>

  <button type="submit" class="btn btn-primary mt-3">Guardar Variante</button>
</form>

