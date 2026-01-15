<?php
// Vista del carrito - VERSIÓN CON ATRIBUTOS CON NOMBRES
if (!isset($carrito) || !is_array($carrito)) $carrito = [];
$total = 0;
$total_descuentos = 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>🛒 Mi Carrito - TuLook</title>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    /* Estilos existentes se mantienen */
    body {
      background-color: #f8f9fa;
      padding-top: 80px;
    }
    .card-producto {
      border: 1px solid #e0e0e0;
      border-radius: 12px;
      transition: transform 0.2s, box-shadow 0.2s;
      overflow: hidden;
    }
    .card-producto:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .producto-img {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 10px;
    }
    .atributo-item {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 6px;
      font-size: 0.9rem;
    }
    .atributo-badge {
      background: #f0f0f0;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.85rem;
      color: #555;
    }
    .color-dot {
      width: 16px;
      height: 16px;
      border-radius: 50%;
      border: 1px solid #ccc;
    }
    .precio-original {
      text-decoration: line-through;
      color: #888;
      font-size: 0.9rem;
    }
    .badge-descuento {
      background: linear-gradient(135deg, #ff6b6b, #ee5a52);
      color: white;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 0.75rem;
    }
    .cantidad-control {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .cantidad-btn {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      border: 1px solid #ddd;
      background: white;
      cursor: pointer;
      transition: all 0.2s;
    }
    .cantidad-btn:hover {
      background: #f8f9fa;
      border-color: #aaa;
    }
    .cantidad-numero {
      font-size: 1.1rem;
      font-weight: 600;
      min-width: 30px;
      text-align: center;
    }
    .acciones-producto {
      display: flex;
      gap: 10px;
      margin-top: 10px;
    }
    .resumen-compra {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 15px;
      padding: 25px;
    }
    .total-grande {
      font-size: 2.5rem;
      font-weight: 700;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .empty-cart {
      text-align: center;
      padding: 60px 20px;
    }
    .empty-cart i {
      font-size: 5rem;
      color: #ddd;
      margin-bottom: 20px;
    }
    .btn-checkout {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      padding: 15px 30px;
      font-size: 1.2rem;
      font-weight: 600;
      border-radius: 12px;
      transition: all 0.3s;
    }
    .btn-checkout:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    @media (max-width: 768px) {
      .producto-img {
        width: 100px;
        height: 100px;
      }
      .total-grande {
        font-size: 2rem;
      }
    }
    /* Nuevo estilo para IVA */
    .iva-badge {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 0.85rem;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<?php include "views/layout/nav.php"; ?>

<div class="container mt-4 mb-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">
      <i class="fas fa-shopping-cart text-primary me-2"></i>Mi Carrito
      <?php if (!empty($carrito)): ?>
        <span class="badge bg-primary ms-2"><?php echo count($carrito); ?> productos</span>
      <?php endif; ?>
    </h1>
    <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-primary">
      <i class="fas fa-arrow-left me-2"></i>Seguir Comprando
    </a>
  </div>

  <!-- Mensajes -->
  <?php if (isset($_SESSION['mensaje_ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['mensaje_ok']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensaje_ok']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i><?php echo $_SESSION['mensaje_error']; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensaje_error']); ?>
  <?php endif; ?>

  <div class="row">
    <!-- Lista de productos -->
    <div class="col-lg-8">
      <?php if (!empty($carrito)): ?>
        <?php 
        $subtotal_carrito = 0;
        $descuentos_carrito = 0;
        $total_items = 0;
        
        foreach ($carrito as $index => $item): 
          $precio_original = floatval($item['Precio_Original'] ?? $item['Precio']);
          $precio_final = floatval($item['Precio']);
          $subtotal = $precio_final * intval($item['Cantidad']);
          $subtotal_original = $precio_original * intval($item['Cantidad']);

          $subtotal_carrito += $subtotal;
          $descuentos_carrito += ($subtotal_original - $subtotal);
          $total_items += $item['Cantidad'];
          
          $tiene_descuento = isset($item['Descuento']) && !empty($item['Descuento']['Codigo']);
          $porcentaje_descuento = $tiene_descuento ? (($precio_original - $precio_final) / $precio_original * 100) : 0;
          
          // URL para detalles
          if (isset($item['ID_Producto']) && !empty($item['ID_Producto'])) {
              $url_detalles = BASE_URL . '?c=Producto&a=ver&id=' . $item['ID_Articulo'] . '&variante=' . $item['ID_Producto'];
          } else {
              $url_detalles = BASE_URL . '?c=Producto&a=ver&id=' . $item['ID_Articulo'];
          }

          // Manejar imagen
          $foto = $item['Foto'] ?? 'assets/img/placeholder.png';
          if (!preg_match('/^https?:\\/\\//i', $foto) && !str_starts_with($foto, 'ImgProducto/') && !str_starts_with($foto, 'assets/')) {
              $foto = 'ImgProducto/' . ltrim($foto, '/');
          }
          $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : rtrim(BASE_URL, '/') . '/' . ltrim($foto, '/');
        ?>
        
        <div class="card card-producto mb-4">
          <div class="card-body">
            <div class="row">
              <!-- Imagen -->
              <div class="col-md-3 text-center">
                <img src="<?php echo $fotoUrl; ?>" 
                     class="producto-img" 
                     alt="<?php echo htmlspecialchars($item['N_Articulo']); ?>"
                     onerror="this.src='<?php echo BASE_URL; ?>assets/img/placeholder.png'">
              </div>
              
              <!-- Información -->
              <div class="col-md-9">
                <div class="row">
                  <div class="col-md-8">
                    <h5 class="mb-2"><?php echo htmlspecialchars($item['N_Articulo']); ?></h5>
                    
                    <?php if ($tiene_descuento): ?>
                      <div class="mb-2">
                        <span class="badge-descuento">
                          <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($item['Descuento']['Codigo']); ?>
                        </span>
                      </div>
                    <?php endif; ?>
                    
                    <!-- Mostrar atributos dinámicamente CON NOMBRES -->
                    <?php if (!empty($item['Atributos'])): ?>
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Especificaciones:</p>
                            <?php foreach ($item['Atributos'] as $atributo): ?>
                                <?php if (!empty($atributo['nombre']) && !empty($atributo['valor'])): ?>
                                    <div class="atributo-item">
                                        <span class="fw-medium text-primary">
                                            <?php echo htmlspecialchars($atributo['nombre']); ?>:
                                        </span>
                                        <?php if ($atributo['nombre'] === 'Color' && isset($item['CodigoHex']) && !empty($item['CodigoHex'])): ?>
                                            <span class="color-dot me-2" style="background:<?php echo htmlspecialchars($item['CodigoHex']); ?>"></span>
                                        <?php endif; ?>
                                        <span class="atributo-badge"><?php echo htmlspecialchars($atributo['valor']); ?></span>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (!empty($item['ValorAtributo1']) || !empty($item['ValorAtributo2']) || !empty($item['ValorAtributo3'])): ?>
                        <!-- Mostrar atributos básicos si no están en el array Atributos -->
                        <div class="mb-3">
                            <p class="text-muted small mb-2">Especificaciones:</p>
                            <?php if (!empty($item['ValorAtributo1'])): ?>
                                <div class="atributo-item">
                                    <span class="atributo-badge"><?php echo htmlspecialchars($item['ValorAtributo1']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($item['ValorAtributo2'])): ?>
                                <div class="atributo-item">
                                    <span class="atributo-badge"><?php echo htmlspecialchars($item['ValorAtributo2']); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($item['ValorAtributo3'])): ?>
                                <div class="atributo-item">
                                    <span class="atributo-badge"><?php echo htmlspecialchars($item['ValorAtributo3']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Precio -->
                    <div class="mb-3">
                      <?php if ($tiene_descuento && $precio_original > $precio_final): ?>
                        <div class="d-flex align-items-center gap-2">
                          <span class="precio-original">$<?php echo number_format($precio_original, 0, ',', '.'); ?></span>
                          
                          <!-- ✅ MOSTRAR "GRATIS" CUANDO EL PRECIO ES 0 -->
                          <?php if ($precio_final == 0 || $precio_final == 0.00): ?>
                            <span class="h5 text-success mb-0 fw-bold">GRATIS</span>
                          <?php else: ?>
                            <span class="h5 text-danger mb-0">$<?php echo number_format($precio_final, 0, ',', '.'); ?></span>
                          <?php endif; ?>
                          
                          <span class="badge bg-danger">-<?php echo number_format($porcentaje_descuento, 1); ?>%</span>
                        </div>
                      <?php else: ?>
                        <!-- ✅ MOSTRAR "GRATIS" TAMBIÉN PARA PRODUCTOS SIN DESCUENTO PERO CON PRECIO 0 -->
                        <?php if ($precio_final == 0 || $precio_final == 0.00): ?>
                          <span class="h5 text-success mb-0 fw-bold">GRATIS</span>
                        <?php else: ?>
                          <span class="h5 text-dark mb-0">$<?php echo number_format($precio_final, 0, ',', '.'); ?></span>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                  </div>
                  
                  <!-- Cantidad y subtotal -->
                  <div class="col-md-4 text-center text-md-end">
                    <div class="cantidad-control justify-content-center justify-content-md-end mb-3">
                      <form method="POST" action="<?php echo BASE_URL . '?c=Carrito&a=actualizarCantidad'; ?>" 
                            class="d-flex align-items-center">
                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                        <button type="submit" name="cantidad" value="<?php echo max(1, $item['Cantidad'] - 1); ?>" 
                                class="cantidad-btn" <?php echo $item['Cantidad'] <= 1 ? 'disabled' : ''; ?>>
                          <i class="fas fa-minus"></i>
                        </button>
                        <span class="cantidad-numero mx-2"><?php echo (int)$item['Cantidad']; ?></span>
                        <button type="submit" name="cantidad" value="<?php echo $item['Cantidad'] + 1; ?>" 
                                class="cantidad-btn">
                          <i class="fas fa-plus"></i>
                        </button>
                      </form>
                    </div>
                    
                    <!-- Subtotal -->
                    <div class="mb-3">
                      <span class="h6">Subtotal:</span><br>
                      <?php if ($subtotal_original > $subtotal): ?>
                        <span class="precio-original">$<?php echo number_format($subtotal_original, 0, ',', '.'); ?></span><br>
                        
                        <!-- ✅ MOSTRAR "GRATIS" CUANDO EL SUBTOTAL ES 0 -->
                        <?php if ($subtotal == 0 || $subtotal == 0.00): ?>
                          <span class="h5 text-success fw-bold">GRATIS</span><br>
                        <?php else: ?>
                          <span class="h5 text-success">$<?php echo number_format($subtotal, 0, ',', '.'); ?></span><br>
                        <?php endif; ?>
                        
                        <small class="text-success">
                          <i class="fas fa-savings"></i> Ahorras $<?php echo number_format($subtotal_original - $subtotal, 0, ',', '.'); ?>
                        </small>
                      <?php else: ?>
                        <!-- ✅ MOSTRAR "GRATIS" TAMBIÉN PARA PRODUCTOS SIN DESCUENTO PERO CON PRECIO 0 -->
                        <?php if ($subtotal == 0 || $subtotal == 0.00): ?>
                          <span class="h5 text-success fw-bold">GRATIS</span>
                        <?php else: ?>
                          <span class="h5 text-success">$<?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                        <?php endif; ?>
                      <?php endif; ?>
                    </div>
                    
                    <!-- Acciones -->
                    <div class="acciones-producto justify-content-center justify-content-md-end">
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
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-cart">
          <i class="fas fa-shopping-cart"></i>
          <h3 class="text-muted">Tu carrito está vacío</h3>
          <p class="text-muted mb-4">¡Explora nuestra tienda y encuentra productos increíbles!</p>
          <a href="<?php echo BASE_URL; ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-store me-2"></i>Ir a la tienda
          </a>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Resumen de compra -->
    <?php if (!empty($carrito)): 
      // Calcular IVA
      $iva_porcentaje = 19;
      $iva_monto = $subtotal_carrito * ($iva_porcentaje / 100);
      $total_con_iva = $subtotal_carrito + $iva_monto;
    ?>
    <div class="col-lg-4">
      <div class="resumen-compra mb-4">
        <h3 class="mb-4"><i class="fas fa-receipt me-2"></i>Resumen de Compra</h3>
        
        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal (<?php echo $total_items; ?> productos):</span>
          <span>$<?php echo number_format($subtotal_carrito, 0, ',', '.'); ?></span>
        </div>
        
        <?php if ($descuentos_carrito > 0): ?>
        <div class="d-flex justify-content-between mb-2 text-warning">
          <span>Descuentos:</span>
          <span>-$<?php echo number_format($descuentos_carrito, 0, ',', '.'); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between mb-2">
          <span>Envío:</span>
          <span class="text-success">Gratis</span>
        </div>
        
        <div class="d-flex justify-content-between mb-3">
          <span>IVA (<?php echo $iva_porcentaje; ?>%):</span>
          <span class="text-info">$<?php echo number_format($iva_monto, 0, ',', '.'); ?></span>
        </div>
        
        <hr class="bg-white">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
          <span class="h4 mb-0">Total:</span>
          <!-- ✅ MOSTRAR "GRATIS" CUANDO EL TOTAL ES 0 -->
          <?php if ($total_con_iva == 0 || $total_con_iva == 0.00): ?>
            <span class="total-grande text-success">GRATIS</span>
          <?php else: ?>
            <span class="total-grande">$<?php echo number_format($total_con_iva, 0, ',', '.'); ?></span>
          <?php endif; ?>
        </div>
        
        <?php if ($descuentos_carrito > 0): ?>
        <div class="alert alert-light mb-0">
          <i class="fas fa-piggy-bank me-2 text-primary"></i>
          <strong>¡Estás ahorrando $<?php echo number_format($descuentos_carrito, 0, ',', '.'); ?>!</strong>
        </div>
        <?php endif; ?>
      </div>
      
      <!-- Botones de acción -->
      <div class="d-grid gap-3">
        <form id="formCompra" action="<?php echo BASE_URL . '?c=Checkout&a=index'; ?>" method="POST">
          <button type="submit" class="btn btn-checkout w-100 py-3" id="btn-proceder-pago">
            <?php if ($total_con_iva == 0 || $total_con_iva == 0.00): ?>
              <i class="fas fa-check-circle me-2"></i>Confirmar Pedido Gratuito
            <?php else: ?>
              <i class="fas fa-credit-card me-2"></i>Proceder al Pago
            <?php endif; ?>
          </button>
        </form>
        
        <button id="vaciarCarrito" class="btn btn-outline-danger w-100 py-3">
          <i class="fas fa-trash-alt me-2"></i>Vaciar Carrito
        </button>
        
        <a href="<?php echo BASE_URL; ?>" class="btn btn-outline-primary w-100 py-3">
          <i class="fas fa-store me-2"></i>Seguir Comprando
        </a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Incluir funciones del carrito -->
<script src="<?php echo BASE_URL; ?>assets/js/carrito-functions.js"></script>

<!-- Pasar datos al JavaScript -->
<script>
// Variables globales para el carrito
window.carritoItems = <?php echo json_encode($carrito); ?>;
window.BASE_URL = '<?php echo BASE_URL; ?>';
window.IVA_PORCENTAJE = 19;

// Datos para cálculos
window.carritoData = {
    subtotal: <?php echo $subtotal_carrito; ?>,
    descuentos: <?php echo $descuentos_carrito; ?>,
    ivaMonto: <?php echo $iva_monto; ?>,
    totalConIVA: <?php echo $total_con_iva; ?>,
    totalItems: <?php echo $total_items; ?>
};
</script>

</body>
</html>