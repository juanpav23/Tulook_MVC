<?php
// =======================
// views/productos/ver.php (DISEÑO COMPACTO Y FUNCIONAL)
// =======================

if (!isset($producto)) {
  header("Location: " . BASE_URL);
  exit;
}

if (!isset($categorias)) $categorias = [];

$usuario_logueado = isset($_SESSION['ID_Usuario']);
$variantes = $variantes ?? [];
$baseTallas = $baseTallas ?? [];
$infoDescuento = $infoDescuento ?? null;

// AGREGAR: Verificar si ya es favorito
$esFavorito = false;
if ($usuario_logueado) {
    global $db;
    if ($db) {
        require_once "models/Favorito.php";
        $favoritoModel = new Favorito($db);
        $esFavorito = $favoritoModel->existsFor(
            $_SESSION['ID_Usuario'], 
            isset($producto->ID_Producto) ? $producto->ID_Producto : null, 
            $producto->ID_Articulo
        );
    }
}

// OBTENER TODOS LOS DESCUENTOS DISPONIBLES PARA ESTE ARTÍCULO
$todosDescuentos = [];
if ($usuario_logueado) {
    try {
        require_once "models/Descuento.php";
        $descuentoModel = new Descuento($db);
        
        // Obtener descuentos vigentes y filtrar los que aplican a este artículo
        $descuentosVigentes = $descuentoModel->obtenerDescuentosVigentes();
        
        foreach ($descuentosVigentes as $descuento) {
            // Verificar si el descuento aplica a este artículo, producto o categoría
            $aplica = false;
            
            // Descuento específico para este artículo
            if ($descuento['ID_Articulo'] == $producto->ID_Articulo) {
                $aplica = true;
            }
            // Descuento para productos específicos (verificar variantes)
            elseif ($descuento['ID_Producto'] !== null) {
                foreach ($variantes as $variante) {
                    if ($variante['ID_Producto'] == $descuento['ID_Producto']) {
                        $aplica = true;
                        break;
                    }
                }
            }
            // Descuento para categoría del artículo
            elseif ($descuento['ID_Categoria'] == $producto->ID_Categoria) {
                $aplica = true;
            }
            
            if ($aplica) {
                $todosDescuentos[] = $descuento;
            }
        }
        
    } catch (Exception $e) {
        error_log("Error obteniendo descuentos: " . $e->getMessage());
        $todosDescuentos = [];
    }
}

// Agrupar variantes por color
$variantesPorColor = [];
foreach ($variantes as $v) {
  $cid = $v['ID_Color'] ?? 0;
  if (!isset($variantesPorColor[$cid])) {
    $variantesPorColor[$cid] = [
      'ID_Color' => $cid,
      'N_Color' => $v['N_Color'] ?? 'Sin color',
      'CodigoHex' => $v['CodigoHex'] ?? '#cccccc',
      'Foto' => $v['Foto'] ?? $producto->Foto,
      'opciones' => []
    ];
  }
  
  $tieneDescuento = isset($v['Info_Descuento']) && is_array($v['Info_Descuento']) && ($v['Info_Descuento']['tiene_descuento'] ?? false);
  $precioFinal = $tieneDescuento ? ($v['Info_Descuento']['precio_final'] ?? $v['Precio_Final']) : $v['Precio_Final'];
  
  $variantesPorColor[$cid]['opciones'][] = [
    'ID_Producto' => $v['ID_Producto'] ?? null,
    'ID_Talla' => $v['ID_Talla'] ?? null,
    'N_Talla' => $v['N_Talla'] ?? 'Única',
    'Porcentaje' => $v['Porcentaje'] ?? 0,
    'Precio_Final' => $v['Precio_Final'] ?? $producto->Precio,
    'Precio_Con_Descuento' => $precioFinal,
    'Cantidad' => $v['Cantidad'] ?? 0,
    'N_Producto' => $v['Nombre_Producto'] ?? ($producto->N_Articulo . ' - ' . ($v['N_Color'] ?? '') . ' ' . ($v['N_Talla'] ?? '')),
    'Foto' => $v['Foto'] ?? $producto->Foto,
    'Info_Descuento' => $v['Info_Descuento'] ?? null
  ];
}

// Separar tallas base de tallas variantes
$tallasBase = array_filter($baseTallas, function($t) {
    return ($t['Tipo'] ?? '') === 'base';
});
$tallasVariantes = array_filter($baseTallas, function($t) {
    return ($t['Tipo'] ?? '') === 'variante';
});

// Precio inicial para el formulario
$precioInicial = ($infoDescuento && is_array($infoDescuento) && ($infoDescuento['tiene_descuento'] ?? false)) 
    ? ($infoDescuento['precio_final'] ?? $producto->Precio) 
    : $producto->Precio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($producto->N_Articulo); ?> - TuLook</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body { padding-top: 80px; background: #f8f9fa; }
    .product-img { width: 100%; object-fit: cover; border-radius: 12px; max-height: 500px; }
    .chip { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 999px; border: 1px solid #ddd; margin: 4px; cursor: pointer; transition: all 0.2s ease; background: #fff; }
    .chip:hover { transform: scale(1.05); box-shadow: 0 0 5px rgba(0,0,0,0.1); }
    .chip.active { background: #212529; color: #fff; border-color: #212529; }
    .chip.disabled { opacity: 0.5; cursor: not-allowed; background: #f8f9fa; }
    .color-dot { display: inline-block; width: 18px; height: 18px; border-radius: 50%; border: 1px solid #aaa; }
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .stock-info { font-size: 0.85rem; margin-top: 4px; }
    .btn-cart-disabled { opacity: 0.6; cursor: not-allowed; }
    .qty-wrapper { display: flex; align-items: center; gap: 8px; }
    .qty-wrapper button { width: 35px; height: 35px; font-weight: bold; }
    .card-variant { transition: transform 0.2s ease; }
    .card-variant:hover { transform: translateY(-5px); }
    .precio-original { text-decoration: line-through; color: #6c757d; font-size: 1rem; }
    .badge-descuento { background: #dc3545; color: white; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 600; }
    .ahorro-info { color: #28a745; font-size: 0.85rem; font-weight: 500; }
    .precio-variante { font-size: 0.9rem; }
    
    .precio-contenedor-dinamico {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 5px;
    }

    /* ESTILOS COMPACTOS PARA SELECCIÓN DE DESCUENTOS */
    .precio-final { 
      color: #000 !important; 
      font-weight: 700 !important; 
      font-size: 1.4rem !important;
    }
    
    .selector-descuentos { 
      border: 1px solid #dee2e6; 
      border-radius: 8px; 
      padding: 15px; 
      background: #ffffff;
      margin-bottom: 20px;
    }
    
    .opcion-descuento { 
      cursor: pointer; 
      padding: 12px 15px; 
      border-radius: 8px; 
      margin-bottom: 8px; 
      transition: all 0.2s ease; 
      border: 2px solid transparent;
      background: #f8f9fa;
    }
    
    .opcion-descuento:hover { 
      background: #e9ecef; 
      border-color: #adb5bd;
    }
    
    .opcion-descuento.activa { 
      background: #d4edda; 
      border: 2px solid #28a745;
    }
    
    .codigo-descuento { 
      font-weight: 600; 
      color: #dc3545; 
      font-size: 1rem;
    }
    
    .tipo-descuento { 
      font-size: 0.85rem; 
      color: #6c757d;
      line-height: 1.3;
    }
    
    .aplica-a { 
      font-size: 0.75rem; 
      color: #495057; 
      background: #e9ecef; 
      padding: 2px 6px; 
      border-radius: 3px;
    }
    
    .valor-descuento { 
      font-weight: 600; 
      color: #28a745; 
      font-size: 0.9rem;
    }
    
    .header-descuentos {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
    }
    
    .header-descuentos h6 {
      margin: 0;
      color: #333;
      font-weight: 600;
      font-size: 1rem;
    }
    
    .badge-cantidad {
      background: #dc3545;
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
      margin-left: 6px;
    }

    .btn-descuento {
      min-width: 90px;
      font-weight: 500;
      font-size: 0.85rem;
      padding: 4px 8px;
    }

    @media (max-width: 768px) {
      .precio-contenedor-dinamico {
        gap: 5px;
      }
      
      .precio-final {
        font-size: 1.3rem !important;
      }
      
      .selector-descuentos {
        padding: 12px;
      }
      
      .opcion-descuento {
        padding: 10px 12px;
      }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<?php include "views/layout/nav.php"; ?>

<div class="container my-4">
  <div class="row g-4">
    <!-- Imagen -->
    <div class="col-md-6 text-center">
      <?php
      $foto = $producto->Foto ?? 'assets/img/placeholder.png';
      if (!preg_match('/^https?:\\/\\//i', $foto) && !str_starts_with($foto, 'ImgProducto/') && !str_starts_with($foto, 'assets/')) {
          $foto = 'ImgProducto/' . ltrim($foto, '/');
      }
      $fotoUrl = (strpos($foto, 'http') === 0) ? $foto : rtrim(BASE_URL, '/') . '/' . ltrim($foto, '/');
      ?>
      <img id="main-img" src="<?= $fotoUrl; ?>" class="product-img shadow-sm" alt="<?= htmlspecialchars($producto->N_Articulo); ?>"
           onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.png'">
    </div>

    <!-- Información del producto -->
    <div class="col-md-6">
      <h2 id="nombre-producto" class="mb-2"><?= htmlspecialchars($producto->N_Articulo); ?></h2>

      <?php if (!empty($producto->N_Talla)): ?>
        <p class="text-muted mb-2">Talla base: <?= htmlspecialchars($producto->N_Talla); ?></p>
      <?php endif; ?>

      <!-- ✅ SECCIÓN DE PRECIOS COMPACTA -->
      <div class="price-container mb-3">
        <div id="precio-text">
          <div class="precio-contenedor-dinamico">
            <span id="precio-original" class="precio-original me-2" style="display: none;"></span>
            <span id="precio-final" class="precio-final">
              $<?= number_format($producto->Precio, 0, ',', '.'); ?>
            </span>
            <span id="descuento-badge" class="badge-descuento" style="display: none;">
              <i class="fas fa-tag me-1"></i>
            </span>
          </div>
          <div id="ahorro-info" class="ahorro-info" style="display: none;"></div>
        </div>
      </div>

      <!-- SECCIÓN: SELECTOR DE DESCUENTOS COMPACTO -->
      <?php if ($usuario_logueado && !empty($todosDescuentos)): ?>
      <div class="selector-descuentos">
        <div class="header-descuentos">
          <h6><i class="fas fa-tags me-2"></i>Descuentos disponibles</h6>
          <span class="badge-cantidad"><?= count($todosDescuentos) ?></span>
        </div>
        <p class="text-muted small mb-2">Selecciona el descuento que deseas aplicar:</p>
        
        <!-- Opción: Sin descuento -->
        <div class="opcion-descuento activa" data-codigo="" data-tipo="ninguno" data-valor="0" data-id-descuento="">
          <div class="d-flex justify-content-between align-items-center">
            <div class="flex-grow-1">
              <div class="d-flex align-items-center mb-1">
                <span class="fw-bold text-dark">Sin descuento</span>
              </div>
              <div class="tipo-descuento">Precio regular</div>
            </div>
            <div class="text-end ms-2">
              <span class="badge bg-secondary px-2">Seleccionado</span>
            </div>
          </div>
        </div>

        <!-- Lista de descuentos disponibles -->
        <?php foreach ($todosDescuentos as $descuento): ?>
          <?php
          $valorMostrar = ($descuento['Tipo'] === 'Porcentaje') 
              ? $descuento['Valor'] . '%' 
              : '$' . number_format($descuento['Valor'], 0, ',', '.');
          
          $fechaFin = !empty($descuento['FechaFin']) ? date('d/m/Y', strtotime($descuento['FechaFin'])) : 'Sin fecha límite';
          
          // Determinar a qué aplica el descuento
          $aplicaA = '';
          if ($descuento['ID_Articulo']) {
              $aplicaA = 'Este artículo';
          } elseif ($descuento['ID_Producto']) {
              $aplicaA = 'Producto específico';
          } elseif ($descuento['ID_Categoria']) {
              $aplicaA = 'Categoría: ' . ($descuento['CategoriaNombre'] ?? 'General');
          }
          ?>
          <div class="opcion-descuento" 
               data-codigo="<?= htmlspecialchars($descuento['Codigo']) ?>" 
               data-tipo="<?= htmlspecialchars($descuento['Tipo']) ?>" 
               data-valor="<?= $descuento['Valor'] ?>"
               data-id-descuento="<?= $descuento['ID_Descuento'] ?>">
            <div class="d-flex justify-content-between align-items-center">
              <div class="flex-grow-1">
                <div class="d-flex align-items-center mb-1">
                  <span class="codigo-descuento me-2"><?= htmlspecialchars($descuento['Codigo']) ?></span>
                  <span class="valor-descuento badge bg-success">-<?= $valorMostrar ?></span>
                </div>
                <div class="tipo-descuento">
                  <i class="fas fa-calendar-alt me-1"></i>Hasta <?= $fechaFin ?>
                  <?php if ($aplicaA): ?>
                    <span class="aplica-a ms-2"><i class="fas fa-bullseye me-1"></i><?= $aplicaA ?></span>
                  <?php endif; ?>
                </div>
              </div>
              <div class="text-end ms-2">
                <button class="btn btn-outline-success btn-sm btn-descuento">Seleccionar</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Campos ocultos para el descuento seleccionado -->
      <input type="hidden" name="codigo_descuento" id="form-codigo-descuento" value="">
      <input type="hidden" name="tipo_descuento" id="form-tipo-descuento" value="">
      <input type="hidden" name="valor_descuento" id="form-valor-descuento" value="0">
      <input type="hidden" name="id_descuento" id="form-id-descuento" value="">

      <?php elseif ($usuario_logueado): ?>
        <div class="alert alert-info border-0 py-2 mb-3">
          <small>
            <i class="fas fa-info-circle me-1"></i>
            No hay descuentos disponibles para este producto.
          </small>
        </div>
      <?php else: ?>
        <div class="alert alert-warning border-0 py-2 mb-3">
          <small>
            <i class="fas fa-exclamation-circle me-1"></i>
            <a href="<?= BASE_URL; ?>?c=Usuario&a=login" class="alert-link">Inicia sesión</a> para ver descuentos.
          </small>
        </div>
      <?php endif; ?>

      <!-- Favoritos -->
      <div class="mb-3">
        <?php if ($usuario_logueado): ?>
          <form id="fav-form" method="post" action="<?= BASE_URL; ?>?c=Favorito&a=toggle" style="display:inline-block">
            <input type="hidden" name="id_articulo" value="<?= (int)$producto->ID_Articulo; ?>">
            <input type="hidden" name="id_producto" id="fav-id-prod" value="<?= isset($producto->ID_Producto) ? (int)$producto->ID_Producto : $producto->ID_Articulo; ?>">
            <input type="hidden" name="return_url" value="<?= urlencode($_SERVER['REQUEST_URI']); ?>">
            <button type="submit" class="btn <?= ($esFavorito ? 'btn-danger' : 'btn-outline-danger'); ?>">
              <i class="fas fa-heart me-1"></i> <?= ($esFavorito ? 'Quitar de Me Gusta' : 'Añadir a Me Gusta'); ?>
            </button>
          </form>
        <?php else: ?>
          <a href="<?= BASE_URL; ?>?c=Usuario&a=login" class="btn btn-outline-secondary">
            <i class="fas fa-heart me-1"></i> Inicia sesión para favoritos
          </a>
        <?php endif; ?>
      </div>

      <hr class="my-3">

      <!-- Colores -->
      <h6 class="mb-2">Color</h6>
      <div id="color-container" class="d-flex flex-wrap mb-3">
        <?php
        $coloresMostrados = [];
        // Artículo base
        $tieneDescuentoBase = $infoDescuento && is_array($infoDescuento) && ($infoDescuento['tiene_descuento'] ?? false);
        echo '<div class="chip active" data-id="' . htmlspecialchars($producto->ID_Color ?? 'base') . '" 
                      data-foto="' . htmlspecialchars($fotoUrl) . '" 
                      data-nombre="' . htmlspecialchars($producto->N_Articulo) . '" 
                      data-base="1"
                      data-precio-original="' . $producto->Precio . '"
                      data-precio-final="' . ($tieneDescuentoBase ? ($infoDescuento['precio_final'] ?? $producto->Precio) : $producto->Precio) . '"
                      data-tiene-descuento="' . ($tieneDescuentoBase ? 'true' : 'false') . '"
                      data-id-producto="' . $producto->ID_Articulo . '">
                <span class="color-dot" style="background:' . htmlspecialchars($producto->CodigoHex ?? '#999') . ';"></span>
                ' . htmlspecialchars($producto->N_Color ?? 'Base') . '
                ' . ($tieneDescuentoBase ? ' <span class="badge bg-danger ms-1" style="font-size: 0.6rem; padding: 2px 4px;">OFF</span>' : '') . '
              </div>';
        $coloresMostrados[$producto->ID_Color ?? 'base'] = true;

        // Variantes por color
        foreach ($variantesPorColor as $c) {
          if (isset($coloresMostrados[$c['ID_Color']])) continue;
          
          $fotoVar = $c['Foto'] ?? $producto->Foto;
          if (!preg_match('/^https?:\\/\\//i', $fotoVar) && !str_starts_with($fotoVar, 'ImgProducto/') && !str_starts_with($fotoVar, 'assets/')) {
              $fotoVar = 'ImgProducto/' . ltrim($fotoVar, '/');
          }
          $fotoVarUrl = (strpos($fotoVar, 'http') === 0) ? $fotoVar : rtrim(BASE_URL, '/') . '/' . ltrim($fotoVar, '/');
          
          // Obtener primera opción para precio referencial
          $primeraOpcion = $c['opciones'][0] ?? null;
          $precioReferencial = $primeraOpcion ? ($primeraOpcion['Precio_Final'] ?? $producto->Precio) : $producto->Precio;
          $tieneDescuentoVariante = $primeraOpcion && $primeraOpcion['Info_Descuento'] && is_array($primeraOpcion['Info_Descuento']) && ($primeraOpcion['Info_Descuento']['tiene_descuento'] ?? false);
          $precioFinalVariante = $tieneDescuentoVariante ? ($primeraOpcion['Info_Descuento']['precio_final'] ?? $precioReferencial) : $precioReferencial;
          
          echo '<div class="chip" data-id="' . $c['ID_Color'] . '" 
                        data-foto="' . htmlspecialchars($fotoVarUrl) . '" 
                        data-nombre="' . htmlspecialchars($producto->N_Articulo . ' - ' . $c['N_Color']) . '"
                        data-base="0"
                        data-precio-original="' . $precioReferencial . '"
                        data-precio-final="' . $precioFinalVariante . '"
                        data-tiene-descuento="' . ($tieneDescuentoVariante ? 'true' : 'false') . '"
                        data-id-producto="' . ($primeraOpcion ? ($primeraOpcion['ID_Producto'] ?? $producto->ID_Articulo) : $producto->ID_Articulo) . '">
                  <span class="color-dot" style="background:' . htmlspecialchars($c['CodigoHex'] ?? '#ccc') . ';"></span>
                  ' . htmlspecialchars($c['N_Color']) . '
                  ' . ($tieneDescuentoVariante ? ' <span class="badge bg-danger ms-1" style="font-size: 0.6rem; padding: 2px 4px;">OFF</span>' : '') . '
                </div>';
          $coloresMostrados[$c['ID_Color']] = true;
        }
        ?>
      </div>

      <!-- Tallas -->
      <h6 class="mb-2">Talla</h6>
      <div id="talla-container" class="mb-2">
        <div class="text-muted">Selecciona un color para ver las tallas disponibles</div>
      </div>
      <div id="stock-info" class="stock-info text-muted mb-2"></div>

      <!-- Cantidad -->
      <div class="mb-3">
        <label for="cantidad" class="form-label">Cantidad</label>
        <div class="qty-wrapper">
          <button type="button" id="qty-minus" class="btn btn-outline-secondary btn-sm">−</button>
          <input id="cantidad" type="number" min="1" value="1" class="form-control text-center" style="width:80px;">
          <button type="button" id="qty-plus" class="btn btn-outline-secondary btn-sm">+</button>
        </div>
      </div>

      <!-- Botón agregar al carrito -->
      <form id="add-cart-form" method="post" action="<?= BASE_URL; ?>?c=Carrito&a=agregar">
        <input type="hidden" name="tipo" id="form-tipo" value="base">
        <input type="hidden" name="id_articulo" value="<?= (int)$producto->ID_Articulo; ?>">
        <input type="hidden" name="id_producto" id="form-id-producto" value="<?= (int)$producto->ID_Articulo; ?>">
        <input type="hidden" name="id_color" id="form-id-color" value="<?= $producto->ID_Color ?? 'base'; ?>">
        <input type="hidden" name="n_color" id="form-n-color" value="<?= htmlspecialchars($producto->N_Color ?? 'Base'); ?>">
        <input type="hidden" name="codigo_hex" id="form-codigo-hex" value="<?= htmlspecialchars($producto->CodigoHex ?? '#cccccc'); ?>">
        <input type="hidden" name="id_talla" id="form-id-talla" value="">
        <input type="hidden" name="cantidad" id="form-cantidad" value="1">
        <input type="hidden" name="precio_unitario" id="form-precio-unitario" value="<?= $precioInicial; ?>">
        <input type="hidden" name="precio_final" id="form-precio-final" value="<?= $precioInicial; ?>">
        
        <!-- NUEVOS CAMPOS PARA DESCUENTO SELECCIONADO -->
        <input type="hidden" name="codigo_descuento" id="form-codigo-descuento-final" value="">
        <input type="hidden" name="tipo_descuento" id="form-tipo-descuento-final" value="">
        <input type="hidden" name="valor_descuento" id="form-valor-descuento-final" value="0">
        <input type="hidden" name="id_descuento" id="form-id-descuento-final" value="">
        
        <button id="btn-add-cart" type="button" class="btn btn-dark w-100 btn-cart-disabled py-2" disabled>
          <i class="fas fa-shopping-cart me-2"></i> Selecciona una talla
        </button>
      </form>
    </div>
  </div>

  <hr class="my-4">

  <!-- Variantes -->
  <h4 class="mb-3"><i class="fas fa-palette me-2"></i> Variantes disponibles</h4>
  <div class="row mt-3">
    <?php if (empty($variantesPorColor)): ?>
      <div class="col-12 text-center text-muted py-4">
        <i class="fas fa-box-open fa-2x mb-2"></i>
        <p>No hay variantes disponibles para este producto.</p>
      </div>
    <?php else: ?>
      <?php foreach ($variantesPorColor as $c): ?>
        <div class="col-md-3 mb-4">
          <div class="card card-variant h-100 p-2 text-center shadow-sm">
            <?php
            $fotoVar = $c['Foto'] ?? $producto->Foto;
            if (!preg_match('/^https?:\\/\\//i', $fotoVar) && !str_starts_with($fotoVar, 'ImgProducto/') && !str_starts_with($fotoVar, 'assets/')) {
                $fotoVar = 'ImgProducto/' . ltrim($fotoVar, '/');
            }
            $fotoVarUrl = (strpos($fotoVar, 'http') === 0) ? $fotoVar : rtrim(BASE_URL, '/') . '/' . ltrim($fotoVar, '/');
            ?>
            <img src="<?= $fotoVarUrl; ?>" style="height:140px;object-fit:cover;width:100%;border-radius:8px" 
                 alt="<?= htmlspecialchars($c['N_Color']); ?>"
                 onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.png'">
            <div class="mt-2 fw-bold d-flex align-items-center justify-content-center gap-2">
              <span class="color-dot" style="background:<?= htmlspecialchars($c['CodigoHex'] ?? '#ccc'); ?>;"></span>
              <?= htmlspecialchars($c['N_Color']); ?>
            </div>
            <?php foreach ($c['opciones'] as $o): ?>
              <?php
              $tieneDescuento = $o['Info_Descuento'] && is_array($o['Info_Descuento']) && ($o['Info_Descuento']['tiene_descuento'] ?? false);
              $precioMostrar = $tieneDescuento ? ($o['Precio_Con_Descuento'] ?? $o['Precio_Final']) : $o['Precio_Final'];
              ?>
              <div class="d-flex justify-content-between align-items-center mt-2 p-2 border rounded">
                <div>
                  <strong><?= htmlspecialchars($o['N_Talla'] ?? 'Única'); ?></strong><br>
                  <div class="precio-variante">
                    <?php if ($tieneDescuento): ?>
                      <span class="text-decoration-line-through text-muted me-1">
                        $<?= number_format($o['Precio_Final'] ?? 0, 0, ',', '.') ?>
                      </span>
                      <span class="text-danger fw-bold">
                        $<?= number_format($precioMostrar, 0, ',', '.') ?>
                      </span>
                      <small class="badge bg-danger ms-1">OFF</small>
                    <?php else: ?>
                      <span class="text-dark">
                        $<?= number_format($precioMostrar, 0, ',', '.') ?>
                      </span>
                    <?php endif; ?>
                  </div>
                  <?php if (($o['Porcentaje'] ?? 0) != 0): ?>
                    <div><small class="text-success">(<?= number_format($o['Porcentaje'] ?? 0, 1); ?>%)</small></div>
                  <?php endif; ?>
                </div>
                <div class="text-end">
                  <span class="badge <?= (($o['Cantidad'] ?? 0) > 0) ? 'bg-success' : 'bg-danger'; ?>">
                    <?= (($o['Cantidad'] ?? 0) > 0) ? ($o['Cantidad'] . ' und') : 'Agotado'; ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const PRODUCTO_DATA = {
    usuarioLogueado: <?= json_encode($usuario_logueado); ?>,
    variantes: <?= json_encode($variantes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    baseTallas: <?= json_encode($baseTallas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    productoBase: {
      id: <?= (int)$producto->ID_Articulo; ?>,
      precio: <?= json_encode($producto->Precio); ?>,
      nombre: <?= json_encode($producto->N_Articulo); ?>,
      idColor: <?= json_encode($producto->ID_Color ?? 'base'); ?>
    },
    baseUrl: <?= json_encode(BASE_URL); ?>,
    esFavorito: <?= json_encode($esFavorito); ?>,
    infoDescuento: <?= json_encode($infoDescuento, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    todosDescuentos: <?= json_encode($todosDescuentos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
  };
</script>
           
<script src="<?= BASE_URL ?>assets/js/ver.js"></script>

</body>
</html>