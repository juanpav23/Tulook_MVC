<?php
// Vista del carrito
if (!isset($carrito) || !is_array($carrito)) $carrito = [];
if (!isset($categorias)) $categorias = [];
$total = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>🛒 Mi Carrito - TuLook</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background-color: #f8f9fa;
      padding-top: 80px;
    }
    .card {
      border-radius: 1rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
      border: none;
    }
    table img {
      border-radius: 0.5rem;
      transition: transform .2s;
      height: 80px;
      object-fit: cover;
    }
    table img:hover {
      transform: scale(1.05);
    }
    .table > :not(caption) > * > * {
      vertical-align: middle;
    }
    .btn {
      border-radius: 0.5rem;
    }
    .badge-tipo {
      font-size: 0.7rem;
      margin-left: 5px;
    }
    .color-dot {
      display: inline-block;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      border: 1px solid #aaa;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<?php include "views/layout/nav.php"; ?>

<div class="container mt-5 mb-5">
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0"><i class="fas fa-shopping-cart text-primary"></i> Mi Carrito</h2>
      <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Seguir Comprando
      </a>
    </div>

    <!-- Mensajes de éxito/error -->
    <?php if (isset($_SESSION['mensaje_ok'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['mensaje_ok']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['mensaje_ok']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['mensaje_error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['mensaje_error']); ?>
    <?php endif; ?>

    <?php if (!empty($carrito)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>Foto</th>
              <th>Producto</th>
              <th>Tipo</th>
              <th>Talla</th>
              <th>Color</th>
              <th>Precio</th>
              <th>Cantidad</th>
              <th>Subtotal</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($carrito as $index => $item): 
              $subtotal = $item['Precio'] * $item['Cantidad'];
              $total += $subtotal;
              
              // Determinar tipo y badge
              $tipo = $item['Tipo'] ?? 'base';
              $badge_class = $tipo === 'variante' ? 'bg-info' : 'bg-secondary';
              $tipo_text = $tipo === 'variante' ? 'Variante' : 'Base';
              
              // Determinar URL para ver detalles
              if ($tipo === 'variante' && !empty($item['ID_Producto'])) {
                  $url_detalles = BASE_URL . '?c=Producto&a=ver&id=' . $item['ID_Articulo'] . '&variante=' . $item['ID_Producto'];
              } else {
                  $url_detalles = BASE_URL . '?c=Producto&a=ver&id=' . $item['ID_Articulo'];
              }

              // Manejar la imagen
              $foto = $item['Foto'] ?? 'assets/img/placeholder.png';
              if (!preg_match('/^https?:\\/\\//i', $foto) && !str_starts_with($foto, 'ImgProducto/') && !str_starts_with($foto, 'assets/')) {
                  $foto = 'ImgProducto/' . ltrim($foto, '/');
              }
              $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : rtrim(BASE_URL, '/') . '/' . ltrim($foto, '/');
          ?>
            <tr>
              <td>
                <img src="<?php echo $fotoUrl; ?>" 
                     width="80" 
                     alt="<?php echo htmlspecialchars($item['N_Articulo']); ?>"
                     onerror="this.src='<?php echo BASE_URL; ?>assets/img/placeholder.png'">
              </td>
              <td class="text-start">
                <?php echo htmlspecialchars($item['N_Articulo']); ?>
              </td>
              <td>
                <span class="badge <?php echo $badge_class; ?> badge-tipo"><?php echo $tipo_text; ?></span>
              </td>
              <td>
                <?php echo htmlspecialchars($item['N_Talla'] ?? 'Única'); ?>
              </td>
              <td>
                <?php if ($tipo === 'variante' && ($item['N_Color'] ?? '') !== 'Sin color'): ?>
                  <div class="d-flex align-items-center justify-content-center gap-2">
                    <span class="color-dot" 
                          style="background:<?php echo !empty($item['CodigoHex']) ? htmlspecialchars($item['CodigoHex']) : '#ccc'; ?>"></span>
                    <?php echo htmlspecialchars($item['N_Color'] ?? 'Sin color'); ?>
                  </div>
                <?php else: ?>
                  <span class="text-muted"><?php echo htmlspecialchars($item['N_Color'] ?? 'Sin color'); ?></span>
                <?php endif; ?>
              </td>
              <td>
                <strong>$<?php echo number_format($item['Precio'], 0, ',', '.'); ?></strong>
              </td>
              <td>
                <div class="d-flex align-items-center justify-content-center gap-2">
                  <form method="POST" action="<?php echo BASE_URL . '?c=Carrito&a=actualizarCantidad'; ?>" class="d-flex align-items-center">
                    <input type="hidden" name="index" value="<?php echo $index; ?>">
                    <button type="submit" name="cantidad" value="<?php echo max(1, $item['Cantidad'] - 1); ?>" 
                            class="btn btn-outline-secondary btn-sm" <?php echo $item['Cantidad'] <= 1 ? 'disabled' : ''; ?>>
                      <i class="fas fa-minus"></i>
                    </button>
                    <span class="mx-2 fw-bold"><?php echo (int)$item['Cantidad']; ?></span>
                    <button type="submit" name="cantidad" value="<?php echo $item['Cantidad'] + 1; ?>" 
                            class="btn btn-outline-secondary btn-sm">
                      <i class="fas fa-plus"></i>
                    </button>
                  </form>
                </div>
              </td>
              <td>
                <strong class="text-success">$<?php echo number_format($subtotal, 0, ',', '.'); ?></strong>
              </td>
              <td>
                <div class="d-flex justify-content-center gap-2">
                  <a href="<?php echo $url_detalles; ?>" 
                     class="btn btn-outline-info btn-sm" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button class="btn btn-danger btn-sm eliminar-btn" 
                          data-url="<?php echo BASE_URL . '?c=Carrito&a=eliminar&id=' . $index; ?>"
                          data-producto="<?php echo htmlspecialchars($item['N_Articulo']); ?>">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div class="mt-4 d-flex flex-column flex-md-row justify-content-between align-items-center">
        <h3 class="text-success mb-3 mb-md-0">
          TOTAL: <strong>$<?php echo number_format($total, 0, ',', '.'); ?></strong>
        </h3>
        <div class="d-flex gap-3">
          <button id="vaciarCarrito" class="btn btn-warning">
            <i class="fas fa-trash-alt"></i> Vaciar Carrito
          </button>

          <!-- ✅ Formulario para confirmar compra -->
          <form id="formCompra" action="<?php echo BASE_URL . '?c=Carrito&a=confirmarCompra'; ?>" method="POST">
            <button type="submit" class="btn btn-success btn-lg">
              <i class="fas fa-credit-card"></i> Finalizar Compra
            </button>
          </form>
        </div>
      </div>

    <?php else: ?>
      <div class="alert alert-info text-center py-5">
        <i class="fas fa-shopping-cart fa-3x mb-3 text-muted"></i>
        <h4 class="text-muted">Tu carrito está vacío</h4>
        <p class="text-muted mb-4">Agrega algunos productos increíbles a tu carrito</p>
        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
          <i class="fas fa-store"></i> Ir a la tienda
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Confirmación al eliminar un producto
document.querySelectorAll('.eliminar-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const url = btn.dataset.url;
    const producto = btn.dataset.producto;
    
    Swal.fire({
      title: '¿Eliminar producto?',
      html: `¿Estás seguro de que quieres eliminar <strong>"${producto}"</strong> del carrito?`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar',
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (result.isConfirmed) {
        window.location.href = url;
      }
    });
  });
});

// Confirmación al vaciar carrito
const vaciar = document.getElementById('vaciarCarrito');
if (vaciar) {
  vaciar.addEventListener('click', e => {
    e.preventDefault();
    Swal.fire({
      title: '¿Vaciar carrito?',
      text: "Se eliminarán todos los productos del carrito.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, vaciar',
      cancelButtonText: 'Cancelar'
    }).then(result => {
      if (result.isConfirmed) {
        window.location.href = '<?php echo BASE_URL . "?c=Carrito&a=vaciar"; ?>';
      }
    });
  });
}

// Confirmación al finalizar compra
const formCompra = document.getElementById('formCompra');
if (formCompra) {
  formCompra.addEventListener('submit', function(e) {
    e.preventDefault();
    
    Swal.fire({
      title: '¿Finalizar compra?',
      html: `Estás a punto de finalizar tu compra por un total de <strong>$<?php echo number_format($total, 0, ',', '.'); ?></strong>`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#28a745',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, comprar',
      cancelButtonText: 'Seguir comprando'
    }).then(result => {
      if (result.isConfirmed) {
        this.submit();
      }
    });
  });
}
</script>

</body>
</html>





