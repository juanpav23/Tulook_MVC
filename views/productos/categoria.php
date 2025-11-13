<?php if (!isset($productos)) { header('Location: ' . BASE_URL); exit; } ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?> - TuLook</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <?php include "views/layout/navbar.php"; ?>

  <div class="container mt-5">
    <h2 class="mb-4"><?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?></h2>
    <div class="row">
      <?php if (count($productos) > 0): ?>
        <?php foreach ($productos as $producto): ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="<?php echo BASE_URL . $producto['Foto']; ?>" class="card-img-top product-image" alt="<?php echo $producto['N_Articulo']; ?>">
              <div class="card-body text-center">
                <h5><?php echo $producto['N_Articulo']; ?></h5>
                <p class="price">$<?php echo number_format($producto['Precio_Final'], 0, ',', '.'); ?></p>
                <?php if ($producto['Stock'] > 0): ?>
                  <p class="text-success">Disponible</p>
                <?php else: ?>
                  <p class="text-danger">Agotado</p>
                <?php endif; ?>
              </div>
              <div class="card-footer bg-white text-center">
                <a href="<?php echo BASE_URL . '?c=Producto&a=ver&id=' . $producto['ID_Producto']; ?>" class="btn btn-primary w-100">Ver Detalles</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <h3>No se encontraron productos en esta categoría</h3>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>

