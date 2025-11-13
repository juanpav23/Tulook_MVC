<?php
// views/productos/index.php
if (!isset($productos) || !is_array($productos)) $productos = [];
if (!isset($categorias)) $categorias = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>TuLook - Catálogo</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root{--accent:#e63946}
    body{padding-top:70px;background:#f7f7f7}
    .product-card{transition:all .18s; border: none}
    .product-card:hover{transform:translateY(-6px); box-shadow:0 8px 30px rgba(0,0,0,.07)}
    .product-image{height:220px; object-fit:cover}
    .price{color:var(--accent); font-weight:700}
    .variant-list small{font-size: 0.9rem;}
  </style>
</head>
<body>
<!-- NAVBAR INCLUIDO -->
<?php include "views/layout/nav.php"; ?>

<div class="container mt-4">
  <form class="d-flex mb-4" method="GET" action="<?php echo BASE_URL; ?>?c=Producto&a=filtrar">
    <input type="hidden" name="c" value="Producto">
    <input type="hidden" name="a" value="filtrar">
    <input class="form-control me-2" name="busqueda" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? '', ENT_QUOTES); ?>">
    <button class="btn btn-outline-primary">Buscar</button>
  </form>

  <h2 class="text-center mb-4">Catálogo de Productos</h2>

  <div class="row">
    <?php if (count($productos) === 0): ?>
      <div class="col-12 text-center py-5">
        <h4>No se encontraron productos</h4>
      </div>
    <?php else: ?>
      <?php foreach ($productos as $p): 
        $idArt = (int)($p['ID_Articulo'] ?? 0);
        $nombre = htmlspecialchars($p['N_Articulo'] ?? 'Sin nombre');
        $foto   = !empty($p['Foto']) ? $p['Foto'] : 'assets/img/placeholder.png';
        if (!preg_match('/^https?:\\/\\//i', $foto) && !str_starts_with($foto, 'ImgProducto/') && !str_starts_with($foto, 'assets/')) {
            $foto = 'ImgProducto/' . ltrim($foto, '/');
        }
        $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : rtrim(BASE_URL, '/') . '/' . ltrim($foto, '/');
        $precio = isset($p['Precio']) ? (float)$p['Precio'] : 0;
        $stock  = isset($p['Stock']) ? (int)$p['Stock'] : 0;
        $variantes = $p['Variantes'] ?? [];
      ?>
        <div class="col-md-4 mb-4">
          <div class="card product-card h-100">
            <img src="<?php echo $fotoUrl; ?>" class="card-img-top product-image" alt="<?php echo $nombre; ?>">
            <div class="card-body text-center">
              <h5 class="card-title"><?php echo $nombre; ?></h5>
              <p class="price">$<?php echo number_format($precio, 0, ',', '.'); ?></p>

              <!-- Stock del producto base -->
              <?php if ($stock > 0): ?>
                <p class="text-success mb-2">Disponible: <?php echo $stock; ?></p>
              <?php else: ?>
                <p class="text-danger mb-2">Agotado</p>
              <?php endif; ?>

              <!-- Variantes disponibles -->
              <?php if (!empty($variantes)): ?>
                <div class="variant-list text-muted mt-2">
                  <small><strong>Variantes disponibles:</strong></small><br>
                  <?php foreach ($variantes as $v): ?>
                    <small>
                      - <?php echo htmlspecialchars($v['N_Talla'] ?? ''); ?>
                      <?php if (!empty($v['N_Color'])): ?>
                        (<?php echo htmlspecialchars($v['N_Color']); ?>)
                      <?php endif; ?>
                      : <?php echo (int)($v['Cantidad'] ?? 0); ?> und
                    </small><br>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
            <div class="card-footer bg-white text-center">
              <a class="btn btn-outline-dark w-100" href="<?php echo BASE_URL; ?>?c=Producto&a=ver&id=<?php echo $idArt; ?>">
                <i class="fa fa-eye"></i> Ver Detalles
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer class="mt-5 py-4 text-center bg-light">
  <div class="container">
    &copy; <?php echo date("Y"); ?> TuLook
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>













