<h2>Gestión de Artículos</h2>
<div class="mt-3">
  <a href="<?= BASE_URL; ?>?c=Admin&a=productoForm" class="btn btn-success mb-3">
    <i class="fas fa-plus-circle"></i> Nuevo producto base
  </a>

  <table class="table table-striped align-middle">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Categoría</th>
        <th>SubCategoría</th>
        <th>Género</th>
        <th>Foto</th>
        <th>Activo</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articulos as $a): ?>
        <tr>
          <td><?= (int)$a['ID_Articulo']; ?></td>
          <td><?= htmlspecialchars($a['N_Articulo']); ?></td>
          <td><?= htmlspecialchars($a['N_Categoria']); ?></td>
          <td><?= htmlspecialchars($a['SubCategoria']); ?></td>
          <td><?= htmlspecialchars($a['N_Genero']); ?></td>
          <td style="width:120px;">
            <?php
              $foto = trim($a['Foto'] ?? '');
              if ($foto !== '') {
                // Si no tiene prefijo ImgProducto, se lo agregamos
                if (!preg_match('/^https?:/i', $foto) && !str_starts_with($foto, 'ImgProducto/')) {
                  $foto = 'ImgProducto/' . ltrim($foto, '/');
                }
                $rutaFoto = BASE_URL . ltrim($foto, '/');
              } else {
                $rutaFoto = BASE_URL . 'assets/img/sin_imagen.png';
              }
            ?>
            <img src="<?= htmlspecialchars($rutaFoto); ?>"
                 style="width:100px; height:70px; object-fit:cover; border-radius:8px;">
          </td>
          <td><?= !empty($a['Activo']) ? 'Sí' : 'No'; ?></td>
          <td>
            <div class="btn-group" role="group">
              <a class="btn btn-sm btn-primary"
                 href="<?= BASE_URL; ?>?c=Admin&a=productoForm&id=<?= (int)$a['ID_Articulo']; ?>">
                 <i class="fas fa-edit"></i> Editar
              </a>
              <a class="btn btn-sm btn-info"
                 href="<?= BASE_URL; ?>?c=Admin&a=detalleProducto&id=<?= (int)$a['ID_Articulo']; ?>">
                 <i class="fas fa-eye"></i> Ver detalle
              </a>
              <a class="btn btn-sm btn-danger"
                 href="<?= BASE_URL; ?>?c=Admin&a=deleteProducto&id=<?= (int)$a['ID_Articulo']; ?>"
                 onclick="return confirm('¿Eliminar artículo?');">
                 <i class="fas fa-trash-alt"></i> Eliminar
              </a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>



