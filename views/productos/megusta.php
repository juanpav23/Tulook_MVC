<?php if (!isset($productos)) { header("Location: " . BASE_URL); exit; } ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Me Gusta - TuLook</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
        --primary-color: #3a3a3a;
        --accent-color: #e63946;
    }
    body { padding-top: 70px; }
    .navbar { background-color: var(--primary-color); }
    .product-image { height: 250px; object-fit: cover; width: 100%; border-radius: 0.5rem; }
    .price { color: var(--accent-color); font-weight: bold; font-size: 1.1rem; }
    footer { background-color: var(--primary-color); color: white; padding: 30px 0; margin-top: 40px; }
  </style>
</head>
<body>
  <!-- Navbar -->
  <?php include "views/layout/nav.php"; ?>


  <!-- Contenido -->
  <div class="container mt-5">
    <h2 class="mb-4">Mis productos con ❤️</h2>
    <div class="row">
      <?php if (count($productos) > 0): ?>
        <?php foreach ($productos as $producto): ?>
          <div class="col-md-4 mb-4">
            <div class="card h-100">
              <img src="<?php echo BASE_URL . $producto['Foto']; ?>" class="card-img-top product-image" alt="<?php echo $producto['N_Articulo']; ?>">
              <div class="card-body text-center">
                <h5 class="card-title"><?php echo $producto['N_Articulo']; ?></h5>
                <span class="badge bg-secondary"><?php echo $producto['N_Genero']; ?></span>
                <p class="price mt-2">$<?php echo number_format($producto['Precio_Final'], 0, ',', '.'); ?></p>
              </div>
              <div class="card-footer bg-white text-center">
                <a href="<?php echo BASE_URL . '?c=Producto&a=ver&id=' . $producto['ID_Producto']; ?>" class="btn btn-primary w-100">
                  Ver Detalles
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <i class="fas fa-heart-broken fa-3x mb-3 text-muted"></i>
          <h4>No tienes productos en “Me Gusta”</h4>
          <p>Explora el catálogo y agrega tus favoritos</p>
          <a href="<?php echo BASE_URL . '?c=Producto'; ?>" class="btn btn-primary">Ver Catálogo</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Footer -->
  <footer>
    <div class="container text-center">
      <p>&copy; <?php echo date("Y"); ?> TuLook. Todos los derechos reservados.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


