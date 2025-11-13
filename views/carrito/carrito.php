<?php
$total = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>🛒 Mi Carrito</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body {
      background-color: #f8f9fa;
    }
    .card {
      border-radius: 1rem;
      box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    table img {
      border-radius: 0.5rem;
      transition: transform .2s;
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
  </style>
</head>
<body>
<div class="container mt-5 mb-5">
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0"><i class="fas fa-shopping-cart text-primary"></i> Mi Carrito</h2>
      <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Seguir Comprando
      </a>
    </div>

    <?php if (!empty($carrito)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead class="table-dark">
            <tr>
              <th>Foto</th>
              <th>Producto</th>
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
          ?>
            <tr>
              <td><img src="<?php echo htmlspecialchars($item['Foto']); ?>" width="80" alt="Foto"></td>
              <td><?php echo htmlspecialchars($item['N_Articulo']); ?></td>
              <td><?php echo htmlspecialchars($item['N_Talla']); ?></td>
              <td><?php echo htmlspecialchars($item['N_Color']); ?></td>
              <td><strong>$<?php echo number_format($item['Precio'], 0, ',', '.'); ?></strong></td>
              <td><?php echo (int)$item['Cantidad']; ?></td>
              <td><strong>$<?php echo number_format($subtotal, 0, ',', '.'); ?></strong></td>
              <td>
                <div class="d-flex justify-content-center gap-2">
                  <a href="<?php echo BASE_URL . '?c=Producto&a=ver&id=' . $item['ID_Producto']; ?>" 
                     class="btn btn-outline-info btn-sm" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                  </a>
                  <button class="btn btn-danger btn-sm eliminar-btn" 
                          data-url="<?php echo BASE_URL . '?c=Carrito&a=eliminar&id=' . $index; ?>">
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
          <a href="<?php echo BASE_URL . '?c=Pedido&a=checkout'; ?>" class="btn btn-success">
            <i class="fas fa-credit-card"></i> Finalizar Compra
          </a>
        </div>
      </div>

    <?php else: ?>
      <div class="alert alert-info text-center py-5">
        <i class="fas fa-info-circle fa-2x mb-3"></i><br>
        Tu carrito está vacío. <br>
        <a href="<?php echo BASE_URL; ?>" class="btn btn-primary mt-3">
          <i class="fas fa-store"></i> Ir a la tienda
        </a>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
// Confirmación al eliminar un producto
document.querySelectorAll('.eliminar-btn').forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    const url = btn.dataset.url;
    Swal.fire({
      title: '¿Eliminar producto?',
      text: "Este producto se quitará del carrito.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#6c757d',
      confirmButtonText: 'Sí, eliminar'
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
      confirmButtonText: 'Sí, vaciar'
    }).then(result => {
      if (result.isConfirmed) {
        window.location.href = '<?php echo BASE_URL . "?c=Carrito&a=vaciar"; ?>';
      }
    });
  });
}
</script>

</body>
</html>





