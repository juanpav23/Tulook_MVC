<?php
// ========================
// views/productos/ver.php 
// ========================

if (!isset($producto)) {
  header("Location: " . BASE_URL);
  exit;
}

if (!isset($categorias)) $categorias = [];

$usuario_logueado = isset($_SESSION['ID_Usuario']);
$variantes = $variantes ?? [];
$infoDescuento = $infoDescuento ?? null;

// OBTENER TODOS LOS DESCUENTOS DISPONIBLES
$todosDescuentos = [];
if ($usuario_logueado) {
    try {
        if (!isset($db)) {
            if (isset($GLOBALS['db'])) {
                $db = $GLOBALS['db'];
            } else {
                require_once "models/Database.php";
                $database = new Database();
                $db = $database->getConnection();
            }
        }
        
        if ($db) {
            require_once "models/Descuento.php";
            $descuentoModel = new Descuento($db);
            $descuentosVigentes = $descuentoModel->obtenerDescuentosVigentes();
            
            foreach ($descuentosVigentes as $descuento) {
                $aplica = false;
                
                if ($descuento['ID_Articulo'] == $producto->ID_Articulo) {
                    $aplica = true;
                }
                elseif ($descuento['ID_Producto'] !== null) {
                    foreach ($variantes as $variante) {
                        if ($variante['ID_Producto'] == $descuento['ID_Producto']) {
                            $aplica = true;
                            break;
                        }
                    }
                }
                elseif ($descuento['ID_Categoria'] == $producto->ID_Categoria) {
                    $aplica = true;
                }
                
                if ($aplica) {
                    $todosDescuentos[] = $descuento;
                }
            }
        }
        
    } catch (Exception $e) {
        error_log("Error obteniendo descuentos: " . $e->getMessage());
        $todosDescuentos = [];
    }
}

// Agrupar variantes por atributos dinámicos
$variantesAgrupadas = [];
foreach ($variantes as $v) {
    $clave = '';
    $atributos = [];
    
    for ($i = 1; $i <= 3; $i++) {
        $idAtributo = $v["ID_Atributo{$i}"] ?? null;
        $valorAtributo = $v["ValorAtributo{$i}"] ?? null;
        
        if ($idAtributo && $valorAtributo) {
            $clave .= "{$idAtributo}:{$valorAtributo}_";
            $atributos[] = [
                'id' => $idAtributo,
                'valor' => $valorAtributo,
                'nombre' => $this->getNombreAtributo($idAtributo)
            ];
        }
    }
    
    if (!isset($variantesAgrupadas[$clave])) {
        $variantesAgrupadas[$clave] = [
            'atributos' => $atributos,
            'variantes' => []
        ];
    }
    
    $tieneDescuento = isset($v['Info_Descuento']) && is_array($v['Info_Descuento']) && ($v['Info_Descuento']['tiene_descuento'] ?? false);
    $precioFinal = $tieneDescuento ? ($v['Info_Descuento']['precio_final'] ?? $v['Precio_Final']) : $v['Precio_Final'];
    
    $variantesAgrupadas[$clave]['variantes'][] = [
        'ID_Producto' => $v['ID_Producto'] ?? null,
        'Precio_Final' => $v['Precio_Final'] ?? $producto->Precio,
        'Precio_Con_Descuento' => $precioFinal,
        'Cantidad' => $v['Cantidad'] ?? 0,
        'Foto' => $v['Foto'] ?? $producto->Foto,
        'Info_Descuento' => $v['Info_Descuento'] ?? null
    ];
}

// Precio inicial
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
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    :root {
      --primary-dark: #1B202D;
      --primary-blue: #2A3448;
      --accent-blue: #3A4A6B;
      --secondary-blue: #4A5B7D;
      --light-blue: #5B6E8F;
      --light-bg: #f5f7fa;
      --text-dark: #2c3e50;
      --text-light: #6c757d;
    }
    body { padding-top: 80px; background: linear-gradient(180deg, #fbfdfe 0%, var(--light-bg) 100%); color: var(--text-dark); }
    .container { max-width: 1150px; }
    .product-img { width: 100%; object-fit: cover; border-radius: 12px; max-height: 500px; }
    .chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 25px;
        border: 2px solid #dee2e6;
        margin: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
    }
    .chip:hover { transform: translateY(-2px); border-color: var(--accent-blue); }
    .chip.active { background: var(--primary-dark); color: #fff; border-color: var(--primary-dark); }
    .chip.disabled { opacity: 0.5; cursor: not-allowed; background: #f8f9fa; }
    .btn-cart-disabled { opacity: 0.6; cursor: not-allowed; }
    .qty-wrapper { display: flex; align-items: center; gap: 8px; }
    .qty-wrapper button { width: 35px; height: 35px; font-weight: bold; }
    .card-variant:hover { transform: translateY(-5px); }
    .precio-original { text-decoration: line-through; color: var(--text-light); }
    .badge-descuento { background: #dc3545; color: white; padding: 4px 8px; border-radius: 6px; }
    .ahorro-info { color: #28a745; font-size: 0.85rem; }
    .step-badge { background: var(--accent-blue); color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; margin-right: 8px; }
    .reviews-section { background: white; border-radius: 20px; padding: 36px; margin-top: 48px; box-shadow: 0 10px 30px -5px rgba(27,32,45,0.08); border: 1px solid rgba(58,74,107,0.08); position: relative; }
    .reviews-section::before { content: ''; position: absolute; top: 0; left: 20px; right: 20px; height: 3px; background: linear-gradient(90deg, var(--primary-dark), var(--accent-blue), var(--secondary-blue)); border-radius: 0 0 3px 3px; }
    .reviews-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; flex-wrap: wrap; gap: 24px; }
    .reviews-title-wrapper { display: flex; align-items: center; gap: 16px; }
    .reviews-icon { width: 56px; height: 56px; background: linear-gradient(145deg, var(--primary-blue), var(--accent-blue)); border-radius: 16px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.6rem; box-shadow: 0 8px 16px rgba(58,74,107,0.2); }
    .reviews-title h3 { font-size: 1.8rem; font-weight: 800; color: var(--primary-dark); margin: 0; }
    .rating-summary { display: flex; align-items: center; gap: 24px; background: var(--light-bg); padding: 12px 24px; border-radius: 60px; }
    .rating-circle .number { font-size: 2.2rem; font-weight: 800; color: var(--primary-dark); }
    .review-card { background: white; border-radius: 20px; padding: 28px; margin-bottom: 24px; border: 1px solid rgba(58,74,107,0.06); transition: all 0.3s ease; }
    .review-card:hover { box-shadow: 0 15px 35px -8px rgba(27,32,45,0.08); border-color: rgba(58,74,107,0.15); transform: translateY(-2px); }
    .reviewer-avatar { width: 56px; height: 56px; border-radius: 18px; background: linear-gradient(145deg, var(--primary-blue), var(--accent-blue)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.3rem; }
    .review-badge { background: linear-gradient(145deg, var(--secondary-blue), var(--light-blue)); color: white; padding: 6px 16px; border-radius: 60px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 6px; }
    .vote-btn { display: flex; align-items: center; gap: 10px; padding: 10px 22px; border-radius: 60px; border: 1.5px solid rgba(58,74,107,0.1); background: white; color: var(--text-dark); font-weight: 600; font-size: 0.9rem; transition: all 0.2s ease; cursor: pointer; }
    .vote-btn:hover { border-color: var(--accent-blue); background: var(--light-bg); transform: translateY(-2px); }
    .vote-btn.active { background: var(--primary-dark); color: white; border-color: var(--primary-dark); }
    .vote-btn.active i { color: white; }
    .action-btn { padding: 8px 18px; border-radius: 60px; font-size: 0.85rem; font-weight: 600; border: 1.5px solid rgba(58,74,107,0.1); background: white; color: var(--text-dark); transition: all 0.2s ease; cursor: pointer; }
    .action-btn:hover { border-color: var(--accent-blue); color: var(--accent-blue); background: var(--light-bg); }
    .action-btn-danger:hover { border-color: #dc3545; color: #dc3545; background: #fff8f8; }
    .mi-resena-card { background: linear-gradient(145deg, #ffffff, #f8fcff); border: 2px solid var(--accent-blue); border-radius: 24px; padding: 28px; margin-top: 36px; position: relative; }
    .mi-resena-badge { position: absolute; top: -14px; left: 28px; background: linear-gradient(145deg, var(--primary-blue), var(--accent-blue)); color: white; padding: 8px 24px; border-radius: 60px; font-weight: 700; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
    .form-review-card { background: white; border-radius: 24px; padding: 32px; margin-top: 36px; border: 2px dashed var(--accent-blue); }
    .btn-submit { background: linear-gradient(145deg, var(--primary-blue), var(--accent-blue)); color: white; border: none; border-radius: 60px; padding: 16px 32px; font-weight: 700; font-size: 1rem; transition: all 0.3s ease; }
    .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 15px 25px -5px rgba(58,74,107,0.35); }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
    .vote-count-update { animation: pulse 0.3s ease; }
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
      <h2 id="nombre-producto" class="mb-2 fw-bold" style="color: var(--primary-dark);"><?= htmlspecialchars($producto->N_Articulo); ?></h2>
      
      <!-- Botón de Favoritos -->
      <?php if (isset($_SESSION['ID_Usuario'])): ?>
        <div class="mb-3">
          <button id="btn-favorito" class="btn btn-outline-danger btn-sm" 
                  data-articulo-id="<?= htmlspecialchars((string)(int)$producto->ID_Articulo); ?>">
            <i class="far fa-heart"></i> Agregar a favoritos
          </button>
          <small class="text-muted d-block mt-1">Artículo ID: <?= (int)$producto->ID_Articulo; ?></small>
        </div>
      <?php else: ?>
        <div class="mb-3">
          <a href="<?= BASE_URL; ?>?c=Usuario&a=login" class="btn btn-outline-secondary btn-sm">
            <i class="far fa-heart"></i> Inicia sesión para guardar en favoritos
          </a>
        </div>
      <?php endif; ?>

      <!-- SECCIÓN DE PRECIOS -->
      <div class="price-container mb-3">
        <div id="precio-text">
          <div class="d-flex align-items-center gap-2">
            <span id="precio-original" class="precio-original me-2" style="display: none;"></span>
            <span id="precio-final" class="fw-bold fs-1" style="color: var(--primary-dark);">
              <?php 
              $precioBase = $producto->Precio ?? 0;
              if ($precioBase == 0): echo '<span class="text-success fw-bold">GRATIS</span>';
              else: echo '$' . number_format($precioBase, 0, ',', '.');
              endif; 
              ?>
            </span>
            <span id="descuento-badge" class="badge-descuento" style="display: none;"><i class="fas fa-tag me-1"></i></span>
          </div>
          <div id="ahorro-info" class="ahorro-info" style="display: none;"></div>
        </div>
      </div>
      
      <input type="hidden" id="precio-base-real" value="<?php echo $precioBase; ?>">

      <!-- SECCIÓN: INGRESO DE CÓDIGO DE DESCUENTO -->
      <?php if ($usuario_logueado): ?>
      <div class="mb-4" id="seccion-descuento">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fas fa-tag me-2"></i>¿Tienes un código de descuento?</h6>
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-8 mb-2">
                <input type="text" id="input-codigo-descuento" class="form-control" 
                       placeholder="Ingresa tu código de descuento" maxlength="20">
              </div>
              <div class="col-md-4 mb-2">
                <button type="button" id="btn-aplicar-descuento" class="btn btn-outline-success w-100">
                  <i class="fas fa-check me-1"></i>Aplicar
                </button>
              </div>
            </div>
            <div id="mensaje-descuento" class="mt-2" style="display: none;"></div>
            <div id="descuento-actual" class="mt-2" style="display: none;">
              <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <div class="d-flex justify-content-between align-items-center">
                  <div><i class="fas fa-check-circle me-2"></i><span id="texto-descuento-activo"></span></div>
                  <button type="button" class="btn-close btn-sm" id="btn-remover-descuento"></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <input type="hidden" name="codigo_descuento" id="form-codigo-descuento" value="">
      <input type="hidden" name="tipo_descuento" id="form-tipo-descuento" value="">
      <input type="hidden" name="valor_descuento" id="form-valor-descuento" value="0">
      <input type="hidden" name="id_descuento" id="form-id-descuento" value="">
      <?php else: ?>
        <div class="alert alert-warning border-0 py-2 mb-3">
          <small><i class="fas fa-exclamation-circle me-1"></i><a href="<?= BASE_URL; ?>?c=Usuario&a=login" class="alert-link">Inicia sesión</a> para aplicar descuentos.</small>
        </div>
      <?php endif; ?>

      <hr class="my-3">

      <!-- SECCIÓN DE ATRIBUTOS DINÁMICOS -->
      <?php if (!empty($atributosRequeridos) && !empty($opcionesAtributos)): ?>
        <div id="seleccion-atributos" class="mb-4">
          <h5 class="fw-bold mb-3">Personaliza tu producto:</h5>
          <div id="atributos-container">
            <?php foreach ($atributosRequeridos as $index => $idTipoAtributo): ?>
              <?php 
              $idTipoAtributo = trim($idTipoAtributo);
              $tieneOpciones = isset($opcionesAtributos[$idTipoAtributo]) && !empty($opcionesAtributos[$idTipoAtributo]);
              $esPrimerAtributo = $index === 0;
              ?>
              <?php if ($tieneOpciones): ?>
                <?php $atributo = $opcionesAtributos[$idTipoAtributo][0]; ?>
                <div class="atributo-group mb-4" data-atributo-id="<?= $idTipoAtributo ?>" data-step="<?= $index + 1 ?>">
                  <h6 class="mb-2 fw-bold text-dark">
                    <span class="step-badge">Paso <?= $index + 1 ?></span>
                    <?= htmlspecialchars($atributo['Nombre'] ?? 'Seleccionar') ?>
                  </h6>
                  <div class="atributo-options d-flex flex-wrap gap-2">
                    <?php foreach ($opcionesAtributos[$idTipoAtributo] as $opcion): ?>
                      <div class="chip <?= $esPrimerAtributo ? '' : 'disabled' ?>" 
                          data-atributo-id="<?= $idTipoAtributo ?>" 
                          data-valor="<?= htmlspecialchars($opcion['Valor']) ?>"
                          data-step="<?= $index + 1 ?>">
                        <?= htmlspecialchars($opcion['Valor']) ?>
                        <?php if (!empty($opcion['CodigoHex'])): ?>
                          <span class="color-preview" style="display: inline-block; width: 16px; height: 16px; background: <?= $opcion['CodigoHex'] ?>; border: 1px solid #ccc; border-radius: 50%; margin-left: 5px;"></span>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div id="estado-seleccion" class="alert alert-info mt-3" style="display: none;">
            <i class="fas fa-spinner fa-spin"></i> <span id="texto-estado">Calculando disponibilidad...</span>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-info mb-4">
          <i class="fas fa-info-circle me-2"></i> Este producto no requiere selección de opciones.
        </div>
      <?php endif; ?>

      <!-- Información de stock -->
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
        <input type="hidden" name="tipo" id="form-tipo" value="variante">
        <input type="hidden" name="id_articulo" value="<?= (int)$producto->ID_Articulo; ?>">
        <input type="hidden" name="id_producto" id="form-id-producto" value="">
        <input type="hidden" name="cantidad" id="form-cantidad" value="1">
        <input type="hidden" name="precio_unitario" id="form-precio-unitario" value="<?= $precioInicial; ?>">
        <input type="hidden" name="precio_final" id="form-precio-final" value="<?= $precioInicial; ?>">
        <input type="hidden" name="codigo_descuento" id="form-codigo-descuento-final" value="">
        <input type="hidden" name="tipo_descuento" id="form-tipo-descuento-final" value="">
        <input type="hidden" name="valor_descuento" id="form-valor-descuento-final" value="0">
        <input type="hidden" name="id_descuento" id="form-id-descuento-final" value="">
        <button id="btn-add-cart" type="button" class="btn btn-dark w-100 btn-cart-disabled py-2" disabled>
          <i class="fas fa-shopping-cart me-2"></i> Selecciona las opciones
        </button>
      </form>
    </div>
  </div>

  <hr class="my-4">

  <!-- Variantes disponibles -->
  <h4 class="mb-3"><i class="fas fa-palette me-2"></i> Variantes disponibles</h4>
  <div class="row mt-3">
    <?php if (empty($variantesAgrupadas)): ?>
      <div class="col-12 text-center text-muted py-4">
        <i class="fas fa-box-open fa-2x mb-2"></i>
        <p>No hay variantes disponibles para este producto.</p>
      </div>
    <?php else: ?>
      <?php foreach ($variantesAgrupadas as $grupo): ?>
        <div class="col-md-4 mb-4">
          <div class="card card-variant h-100 p-2 text-center shadow-sm">
            <?php
            $primeraVariante = $grupo['variantes'][0] ?? [];
            $fotoVar = $primeraVariante['Foto'] ?? $producto->Foto;
            if (!preg_match('/^https?:\\/\\//i', $fotoVar) && !str_starts_with($fotoVar, 'ImgProducto/') && !str_starts_with($fotoVar, 'assets/')) {
                $fotoVar = 'ImgProducto/' . ltrim($fotoVar, '/');
            }
            $fotoVarUrl = (strpos($fotoVar, 'http') === 0) ? $fotoVar : rtrim(BASE_URL, '/') . '/' . ltrim($fotoVar, '/');
            ?>
            <img src="<?= $fotoVarUrl; ?>" style="height:140px;object-fit:cover;width:100%;border-radius:8px" alt="Variante" onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.png'">
            <div class="mt-2 fw-bold">
              <?php foreach ($grupo['atributos'] as $atributo): ?>
                <span class="badge bg-light text-dark me-1"><?= htmlspecialchars($atributo['valor']) ?></span>
              <?php endforeach; ?>
            </div>
            <?php foreach ($grupo['variantes'] as $variante): ?>
              <?php
              $tieneDescuento = $variante['Info_Descuento'] && is_array($variante['Info_Descuento']) && ($variante['Info_Descuento']['tiene_descuento'] ?? false);
              $precioMostrar = $tieneDescuento ? ($variante['Precio_Con_Descuento'] ?? $variante['Precio_Final']) : $variante['Precio_Final'];
              ?>
              <div class="d-flex justify-content-between align-items-center mt-2 p-2 border rounded">
                <div>
                  <strong>Variante</strong><br>
                  <div class="precio-variante">
                    <?php if ($tieneDescuento): ?>
                      <span class="text-decoration-line-through text-muted me-1">$<?= number_format($variante['Precio_Final'] ?? 0, 0, ',', '.') ?></span>
                      <span class="text-danger fw-bold">$<?= number_format($precioMostrar, 0, ',', '.') ?></span>
                      <small class="badge bg-danger ms-1">OFF</small>
                    <?php else: ?>
                      <span class="text-dark">$<?= number_format($precioMostrar, 0, ',', '.') ?></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="text-end">
                  <span class="badge <?= (($variante['Cantidad'] ?? 0) > 0) ? 'bg-success' : 'bg-danger'; ?>">
                    <?= (($variante['Cantidad'] ?? 0) > 0) ? ($variante['Cantidad'] . ' und') : 'Agotado'; ?>
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

<hr class="my-4">

<!-- ============================================ -->
<!-- 🎯 SISTEMA DE RESEÑAS - VOTOS INSTANTÁNEOS  -->
<!-- ============================================ -->

<?php
// Obtener reseñas y estadísticas
if (!isset($db)) {
    if (isset($GLOBALS['db'])) $db = $GLOBALS['db'];
    else { 
        require_once "models/Database.php"; 
        $database = new Database(); 
        $db = $database->getConnection(); 
    }
}
require_once "models/Resena.php";
$resenaModel = new Resena($db);
$stats = $resenaModel->obtenerEstadisticas($producto->ID_Articulo);
$resenas = $resenaModel->obtenerResenas($producto->ID_Articulo);

$usuarioLogueadoId = isset($_SESSION['ID_Usuario']) ? (int)$_SESSION['ID_Usuario'] : null;
$miResena = null;
$resenasOtros = [];

foreach ($resenas as $r) {
    if ($usuarioLogueadoId !== null && (int)$r['ID_Usuario'] === $usuarioLogueadoId) {
        $miResena = $r;
    } else {
        $resenasOtros[] = $r;
    }
}
?>

<!-- CONTENEDOR PRINCIPAL DE RESEÑAS -->
<div class="container mb-5 reviews-section" id="reviews-container" 
     data-articulo-id="<?= (int)$producto->ID_Articulo ?>"
     data-usuario-id="<?= $usuarioLogueadoId ?? 0 ?>">
  
  <div class="reviews-header">
    <div class="reviews-title-wrapper">
      <div class="reviews-icon"><i class="fas fa-star"></i></div>
      <div class="reviews-title">
        <h3>Opiniones de clientes</h3>
        <span><i class="fas fa-users" style="color: var(--accent-blue);"></i> <?= count($resenas) ?> reseñas verificadas</span>
      </div>
    </div>
    <div class="rating-summary">
      <div class="rating-circle">
        <span class="number"><?= number_format(($stats['promedio'] ?? 0), 1) ?></span>
        <span class="total">/5</span>
      </div>
      <div class="rating-stars">
        <?php $promedio = round($stats['promedio'] ?? 0);
        for($i = 1; $i <= 5; $i++): ?>
          <i class="fas fa-star" style="color: <?= $i <= $promedio ? '#fbbf24' : 'rgba(58,74,107,0.15)' ?>;"></i>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- LISTA DE RESEÑAS -->
  <div id="reviews-list">
    <?php if (!empty($resenasOtros)): ?>
      <?php foreach ($resenasOtros as $r): ?>
        <?php $fotos = $resenaModel->obtenerFotos($r['ID_Resena']); ?>
        <?php
          $userVote = null;
          if(isset($_SESSION['ID_Usuario'])){
              $stmtV = $db->prepare("SELECT Es_Positivo FROM resena_voto WHERE ID_Resena = ? AND ID_Usuario = ? LIMIT 1");
              $stmtV->execute([(int)$r['ID_Resena'], (int)$_SESSION['ID_Usuario']]);
              $vv = $stmtV->fetch(PDO::FETCH_ASSOC);
              if($vv !== false) $userVote = (int)$vv['Es_Positivo'];
          }
        ?>
        <div class="review-card" id="review-<?= (int)$r['ID_Resena'] ?>">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex gap-3">
              <div class="reviewer-avatar"><?= strtoupper(substr($r['Nombre'], 0, 1) . substr($r['Apellido'] ?? '', 0, 1)); ?></div>
              <div>
                <div class="fw-bold" style="color: var(--primary-dark);">
                  <?= htmlspecialchars($r['Nombre'] . ' ' . ($r['Apellido'] ?? '')); ?>
                  <?php if($r['Modificado']): ?><span class="text-muted small ms-2">(editado)</span><?php endif; ?>
                </div>
                <div class="small text-muted"><i class="far fa-calendar-alt me-1"></i> <?= date('d/m/Y', strtotime($r['Fecha_Resena'])); ?></div>
              </div>
            </div>
            <div class="review-badge"><i class="fas fa-star"></i> <?= (int)$r['Calificacion']; ?></div>
          </div>
          <?php if(!empty($r['Titulo'])): ?>
            <h5 class="fw-bold mb-2" style="color: var(--primary-dark);"><?= htmlspecialchars($r['Titulo']); ?></h5>
          <?php endif; ?>
          <div class="mb-3" id="comentario-<?= (int)$r['ID_Resena'] ?>" style="line-height: 1.7;">
            <?= nl2br(htmlspecialchars($r['Comentario'])); ?>
          </div>
          <?php if(!empty($fotos)): ?>
            <div class="d-flex gap-2 flex-wrap mb-3">
              <?php foreach($fotos as $foto): ?>
                <?php $fotoUrl = preg_match('/^https?:\/\//i', $foto['Foto']) ? $foto['Foto'] : rtrim(BASE_URL, '/') . '/' . ltrim($foto['Foto'], '/'); ?>
                <div class="rounded overflow-hidden" style="width: 80px; height: 80px; cursor: pointer;" onclick="verFoto('<?= $foto['Foto'] ?>')">
                  <img src="<?= $fotoUrl ?>" style="width:100%; height:100%; object-fit:cover;" alt="Foto de reseña">
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3 pt-3" style="border-top: 1px solid rgba(58,74,107,0.08);">
            <div class="d-flex gap-2">
              <!-- 🔴 IMPORTANTE: AMBOS BOTONES TIENEN data-tipo CORRECTAMENTE DEFINIDO -->
              <button class="vote-btn <?= ($userVote === 1) ? 'active' : '' ?>" 
                      onclick="votarResena(<?= (int)$r['ID_Resena'] ?>, true)" 
                      data-id="<?= (int)$r['ID_Resena'] ?>"
                      data-tipo="positivo">
                <i class="fas fa-thumbs-up"></i>
                <span id="like-count-<?= (int)$r['ID_Resena'] ?>"><?= (int)($r['Util_Positivo'] ?? 0) ?></span>
              </button>
              <button class="vote-btn <?= ($userVote === 0) ? 'active' : '' ?>" 
                      onclick="votarResena(<?= (int)$r['ID_Resena'] ?>, false)" 
                      data-id="<?= (int)$r['ID_Resena'] ?>"
                      data-tipo="negativo">
                <i class="fas fa-thumbs-down"></i>
                <span id="dislike-count-<?= (int)$r['ID_Resena'] ?>"><?= (int)($r['Util_Negativo'] ?? 0) ?></span>
              </button>
            </div>
            <?php if(isset($_SESSION['ID_Usuario'])): ?>
              <?php $esAutor = ((int)$r['ID_Usuario'] === (int)$_SESSION['ID_Usuario']); ?>
              <?php $esAdmin = (($_SESSION['ID_Rol'] ?? 3) != 3); ?>
              <div class="d-flex gap-2">
                <?php if($esAutor || $esAdmin): ?>
                  <button class="action-btn" onclick="editarResena(<?= (int)$r['ID_Resena'] ?>, '<?= htmlspecialchars($r['Titulo'] ?? '', ENT_QUOTES) ?>', <?= (int)$r['Calificacion'] ?>)">
                    <i class="fas fa-edit"></i> Editar
                  </button>
                  <button class="action-btn action-btn-danger" onclick="eliminarResena(<?= (int)$r['ID_Resena'] ?>)">
                    <i class="fas fa-trash"></i> Eliminar
                  </button>
                <?php else: ?>
                  <button class="action-btn" onclick="abrirReporte(<?= (int)$r['ID_Resena'] ?>)">
                    <i class="fas fa-flag"></i> Reportar
                  </button>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center py-5" style="background: var(--light-bg); border-radius: 20px;">
        <i class="fas fa-star mb-3" style="font-size: 3rem; color: rgba(58,74,107,0.2);"></i>
        <h5 style="color: var(--text-dark); font-weight: 700;">Aún no hay reseñas</h5>
        <p style="color: var(--text-light);">Sé el primero en opinar sobre este producto</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- MI RESEÑA -->
  <?php if(isset($_SESSION['ID_Usuario']) && $miResena): ?>
    <?php $fotosResena = $resenaModel->obtenerFotos($miResena['ID_Resena']); ?>
    <div class="mi-resena-card" id="mi-resena">
      <div class="mi-resena-badge"><i class="fas fa-star"></i> Tu opinión - <?= (int)$miResena['Calificacion'] ?> ★</div>
      <div class="d-flex gap-3 mb-3">
        <div style="width: 52px; height: 52px; border-radius: 16px; background: linear-gradient(145deg, var(--primary-blue), var(--accent-blue)); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.3rem;">
          <?= strtoupper(substr($_SESSION['Nombre'] ?? 'U', 0, 1)); ?>
        </div>
        <div>
          <div class="fw-bold" style="color: var(--primary-dark); font-size: 1.1rem;">Tú</div>
          <div class="small text-muted"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($miResena['Fecha_Resena'])); ?></div>
        </div>
      </div>
      <?php if(!empty($miResena['Titulo'])): ?>
        <h5 class="fw-bold mb-2" style="color: var(--primary-dark);"><?= htmlspecialchars($miResena['Titulo']); ?></h5>
      <?php endif; ?>
      <div style="color: var(--text-dark); line-height: 1.7; margin-bottom: 24px;">
        <?= nl2br(htmlspecialchars($miResena['Comentario'])); ?>
      </div>
      <?php if(!empty($fotosResena)): ?>
        <div class="d-flex gap-2 flex-wrap mb-3">
          <?php foreach($fotosResena as $foto): ?>
            <?php $fotoUrl = preg_match('/^https?:\/\//i', $foto['Foto']) ? $foto['Foto'] : rtrim(BASE_URL, '/') . '/' . ltrim($foto['Foto'], '/'); ?>
            <div style="position: relative; width: 100px; height: 100px;">
              <img src="<?= $fotoUrl ?>" style="width:100%; height:100%; object-fit:cover; border-radius: 12px;" alt="Foto">
              <button onclick="eliminarFoto(<?= (int)$foto['ID_Foto'] ?>)" 
                      style="position: absolute; top: 4px; right: 4px; width: 28px; height: 28px; border-radius: 50%; background: rgba(220,53,69,0.9); color: white; border: none; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-trash" style="font-size: 12px;"></i>
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div class="d-flex gap-2 flex-wrap">
        <button class="action-btn" onclick="editarResena(<?= (int)$miResena['ID_Resena'] ?>, '<?= htmlspecialchars($miResena['Titulo'] ?? '', ENT_QUOTES) ?>', <?= (int)$miResena['Calificacion'] ?>)">
          <i class="fas fa-edit"></i> Editar reseña
        </button>
        <button class="action-btn action-btn-danger" onclick="eliminarResena(<?= (int)$miResena['ID_Resena'] ?>)">
          <i class="fas fa-trash"></i> Eliminar reseña
        </button>
        <button class="action-btn" onclick="document.getElementById('foto-input-mi-resena').click();">
          <i class="fas fa-image"></i> Añadir foto
        </button>
        <form id="foto-form-mi-resena" style="display: none;">
          <input type="file" id="foto-input-mi-resena" accept="image/*" onchange="subirFoto(<?= (int)$miResena['ID_Resena'] ?>, this)">
        </form>
      </div>
    </div>
  <?php endif; ?>

  <!-- FORMULARIO NUEVA RESEÑA -->
  <?php if(isset($_SESSION['ID_Usuario']) && !$miResena): ?>
    <div class="form-review-card" id="form-nueva-resena">
      <h4 class="mb-4 fw-bold" style="color: var(--primary-dark);">
        <i class="fas fa-pen-fancy" style="color: var(--accent-blue);"></i> Escribe tu opinión
      </h4>
      <form id="review-form" onsubmit="event.preventDefault(); enviarResena();">
        <input type="hidden" name="id_articulo" value="<?= (int)$producto->ID_Articulo; ?>">
        <input type="hidden" name="id_producto" id="form-id-producto-resena" value="<?= (int)($variantes[0]['ID_Producto'] ?? 0) ?>">
        <div class="row g-4 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-bold" style="color: var(--primary-dark);">Calificación</label>
            <select name="calificacion" id="review-calificacion" class="form-select" required>
              <option value="5">★★★★★ Excelente</option>
              <option value="4">★★★★☆ Muy buena</option>
              <option value="3">★★★☆☆ Buena</option>
              <option value="2">★★☆☆☆ Regular</option>
              <option value="1">★☆☆☆☆ Mala</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-bold" style="color: var(--primary-dark);">Título (opcional)</label>
            <input type="text" name="titulo" id="review-titulo" class="form-control" placeholder="Ej: Excelente calidad">
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-bold" style="color: var(--primary-dark);">Tu experiencia</label>
          <textarea name="comentario" id="review-comentario" class="form-control" rows="5" 
                    placeholder="Cuéntanos qué te pareció el producto..." required></textarea>
          <div class="d-flex justify-content-end mt-2">
            <small class="text-muted" id="comentario-counter">0/500</small>
          </div>
        </div>
        <div class="upload-area mb-4 p-4 text-center rounded" 
             style="background: var(--light-bg); border: 2px dashed var(--accent-blue); cursor: pointer;"
             onclick="document.getElementById('review-fotos').click();">
          <input type="file" name="fotos[]" id="review-fotos" accept="image/*" multiple style="display: none;" onchange="previewFotos(this)">
          <i class="fas fa-cloud-upload-alt" style="color: var(--accent-blue); font-size: 2.5rem;"></i>
          <p class="mt-2 mb-1 fw-semibold" style="color: var(--primary-dark);">Haz clic para subir fotos</p>
          <small class="text-muted">Máximo 5 fotos - PNG, JPG, WebP</small>
          <div id="fotos-preview" class="d-flex gap-2 mt-3 flex-wrap"></div>
        </div>
        <button type="submit" class="btn-submit w-100" id="btn-enviar-resena">
          <i class="fas fa-paper-plane me-2"></i>Publicar reseña
        </button>
      </form>
    </div>
  <?php elseif(!isset($_SESSION['ID_Usuario'])): ?>
    <div class="text-center p-4 mt-4" style="background: var(--light-bg); border-radius: 20px;">
      <i class="fas fa-lock mb-2" style="color: var(--accent-blue); font-size: 1.5rem;"></i>
      <p class="mb-1" style="color: var(--text-dark);">¿Ya compraste este producto?</p>
      <a href="<?= BASE_URL; ?>?c=Usuario&a=login" style="color: var(--accent-blue); font-weight: 700; text-decoration: none;">
        Inicia sesión <i class="fas fa-arrow-right ms-1"></i>
      </a>
    </div>
  <?php endif; ?>
</div>

<!-- MODAL DE EDICIÓN -->
<div class="modal fade" id="modalEditarResena" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 28px; border: none;">
      <div class="modal-header" style="background: linear-gradient(145deg, #ffffff, #f8fafc); border-radius: 28px 28px 0 0; padding: 24px 28px;">
        <h5 class="modal-title fw-bold" style="color: var(--primary-dark);">
          <i class="fas fa-edit me-2" style="color: var(--accent-blue);"></i> Editar reseña
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="edit-id-resena">
        <div class="mb-4">
          <label class="form-label fw-bold" style="color: var(--primary-dark);">Calificación</label>
          <select id="edit-calificacion" class="form-select">
            <option value="5">5 ★★★★★</option>
            <option value="4">4 ★★★★☆</option>
            <option value="3">3 ★★★☆☆</option>
            <option value="2">2 ★★☆☆☆</option>
            <option value="1">1 ★☆☆☆☆</option>
          </select>
        </div>
        <div class="mb-4">
          <label class="form-label fw-bold" style="color: var(--primary-dark);">Título</label>
          <input type="text" id="edit-titulo" class="form-control" placeholder="Título de tu reseña">
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold" style="color: var(--primary-dark);">Comentario</label>
          <textarea id="edit-comentario" class="form-control" rows="5" required></textarea>
        </div>
      </div>
      <div class="modal-footer px-4 pb-4 pt-0" style="border: none;">
        <button type="button" class="btn btn-light px-4 py-2" style="border-radius: 60px;" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn px-5 py-2" style="border-radius: 60px; background: linear-gradient(145deg, var(--primary-blue), var(--accent-blue)); color: white; border: none;" onclick="guardarEdicion()">
          <i class="fas fa-save me-2"></i>Guardar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL DE REPORTE -->
<div class="modal fade" id="modalReportarResena" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 28px; border: none;">
      <div class="modal-header" style="background: linear-gradient(145deg, #fff5f5, #ffffff); border-radius: 28px 28px 0 0; padding: 24px 28px;">
        <h5 class="modal-title fw-bold" style="color: var(--primary-dark);">
          <i class="fas fa-flag me-2" style="color: #dc3545;"></i> Reportar reseña
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <input type="hidden" id="reportar-id-resena">
        <div class="mb-4">
          <label class="form-label fw-bold" style="color: var(--primary-dark);">Motivo del reporte</label>
          <select id="reportar-motivo" class="form-select">
            <option value="Spam">Spam o publicidad</option>
            <option value="Ofensivo">Lenguaje ofensivo</option>
            <option value="Inapropiado">Contenido inapropiado</option>
            <option value="Falso">Información falsa</option>
            <option value="Otro">Otro motivo</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-bold" style="color: var(--primary-dark);">Descripción (opcional)</label>
          <textarea id="reportar-descripcion" class="form-control" rows="3" placeholder="Cuéntanos más detalles..."></textarea>
        </div>
      </div>
      <div class="modal-footer px-4 pb-4 pt-0" style="border: none;">
        <button type="button" class="btn btn-light px-4 py-2" style="border-radius: 60px;" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn px-5 py-2" style="border-radius: 60px; background: #dc3545; color: white; border: none;" onclick="enviarReporte()">
          <i class="fas fa-flag me-2"></i>Reportar
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ============================================ -->
<!-- 🚀 SISTEMA AJAX - VOTOS INSTANTÁNEOS        -->
<!-- ============================================ -->

<script>
const BASE_URL = '<?= BASE_URL ?>';
const USUARIO_ID = <?= $usuarioLogueadoId ?? 0 ?>;

// =============== VOTOS - ACTUALIZACIÓN INSTANTÁNEA ===============
// =============== VOTOS - ACTUALIZACIÓN INSTANTÁNEA CON SCROLL FIJO ===============
async function votarResena(idResena, esPositivo) {
    // Verificar si el usuario está logueado
    if (!USUARIO_ID) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Debes iniciar sesión', 
            text: 'Para votar reseñas necesitas estar logueado', 
            showCancelButton: true, 
            confirmButtonText: 'Ir a login' 
        }).then(result => { 
            if (result.isConfirmed) window.location.href = `${BASE_URL}?c=Usuario&a=login`; 
        });
        return;
    }

    // Guardar la posición actual del scroll
    const scrollPos = window.scrollY;
    
    const btnLike = document.querySelector(`.vote-btn[data-id="${idResena}"][data-tipo="positivo"]`);
    const btnDislike = document.querySelector(`.vote-btn[data-id="${idResena}"][data-tipo="negativo"]`);
    
    // Determinar qué botón se presionó
    const btn = esPositivo ? btnLike : btnDislike;
    
    if (!btn) {
        console.error('❌ Botón no encontrado');
        return;
    }
    
    // Guardar estado original
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    // Deshabilitar ambos botones mientras se procesa
    if (btnLike) btnLike.disabled = true;
    if (btnDislike) btnDislike.disabled = true;

    try {
        const formData = new URLSearchParams();
        formData.append('id_resena', idResena);
        formData.append('tipo', esPositivo ? 'positivo' : 'negativo');
        formData.append('ajax', '1');

        const response = await fetch(`${BASE_URL}?c=Resena&a=votar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            // Actualizar contadores
            const likeSpan = document.getElementById(`like-count-${idResena}`);
            const dislikeSpan = document.getElementById(`dislike-count-${idResena}`);
            
            if (likeSpan) {
                likeSpan.textContent = data.util_positivo;
                likeSpan.classList.add('vote-count-update');
                setTimeout(() => likeSpan.classList.remove('vote-count-update'), 300);
            }
            
            if (dislikeSpan) {
                dislikeSpan.textContent = data.util_negativo;
                dislikeSpan.classList.add('vote-count-update');
                setTimeout(() => dislikeSpan.classList.remove('vote-count-update'), 300);
            }
            
            // Actualizar estados de los botones
            if (btnLike) btnLike.classList.remove('active');
            if (btnDislike) btnDislike.classList.remove('active');
            
            if (data.user_vote === 'positivo' && btnLike) {
                btnLike.classList.add('active');
            } else if (data.user_vote === 'negativo' && btnDislike) {
                btnDislike.classList.add('active');
            }
            
            // Restaurar la posición del scroll
            window.scrollTo({
                top: scrollPos,
                behavior: 'auto' // 'auto' para que sea instantáneo, 'smooth' para suave
            });
            
            // Mostrar notificación flotante pequeña
            Swal.fire({ 
                icon: 'success', 
                title: '¡Voto registrado!', 
                timer: 1000, 
                showConfirmButton: false, 
                toast: true, 
                position: 'top-end',
                background: '#fff',
                iconColor: '#3A4A6B'
            });
        }
    } catch (error) {
        console.error('Error al votar:', error);
        Swal.fire({ 
            icon: 'error', 
            title: 'Error al votar', 
            timer: 1500, 
            showConfirmButton: false, 
            toast: true, 
            position: 'top-end' 
        });
    } finally {
        // Restaurar botones
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        if (btnLike) btnLike.disabled = false;
        if (btnDislike) btnDislike.disabled = false;
    }
}

// =============== CREAR RESEÑA ===============
async function enviarResena() {
    if (!USUARIO_ID) { 
        Swal.fire('Inicia sesión', 'Debes iniciar sesión para publicar una reseña', 'info'); 
        return; 
    }
    
    const comentario = document.getElementById('review-comentario').value.trim();
    if (comentario.length < 20) { 
        Swal.fire('Error', 'El comentario debe tener al menos 20 caracteres', 'error'); 
        return; 
    }
    
    const btn = document.getElementById('btn-enviar-resena');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publicando...';
    btn.disabled = true;

    try {
        const formData = new FormData(document.getElementById('review-form'));
        formData.append('ajax', '1');
        const response = await fetch(`${BASE_URL}?c=Resena&a=crear`, { 
            method: 'POST', 
            body: formData 
        });
        const data = await response.json();
        if (data.success) {
            Swal.fire({ 
                icon: 'success', 
                title: '¡Reseña publicada!', 
                timer: 2000, 
                showConfirmButton: false 
            });
            document.getElementById('review-form').reset();
            document.getElementById('fotos-preview').innerHTML = '';
            document.getElementById('comentario-counter').textContent = '0/500';
            setTimeout(() => location.reload(), 2000);
        } else {
            throw new Error(data.error || 'Error al publicar');
        }
    } catch (error) {
        Swal.fire('Error', error.message || 'No se pudo publicar tu reseña', 'error');
    } finally {
        btn.innerHTML = originalText;
        btn.disabled = false;
    }
}

// =============== EDITAR RESEÑA ===============
function editarResena(idResena, titulo, calificacion) {
    document.getElementById('edit-id-resena').value = idResena;
    document.getElementById('edit-titulo').value = titulo || '';
    document.getElementById('edit-calificacion').value = calificacion;
    const comentarioEl = document.getElementById(`comentario-${idResena}`);
    document.getElementById('edit-comentario').value = comentarioEl ? comentarioEl.innerText : '';
    new bootstrap.Modal(document.getElementById('modalEditarResena')).show();
}

async function guardarEdicion() {
    const idResena = document.getElementById('edit-id-resena').value;
    const titulo = document.getElementById('edit-titulo').value;
    const calificacion = document.getElementById('edit-calificacion').value;
    const comentario = document.getElementById('edit-comentario').value.trim();
    if (!comentario) { 
        Swal.fire('Error', 'El comentario no puede estar vacío', 'error'); 
        return; 
    }
    try {
        const formData = new URLSearchParams();
        formData.append('id_resena', idResena);
        formData.append('titulo', titulo);
        formData.append('calificacion', calificacion);
        formData.append('comentario', comentario);
        formData.append('ajax', '1');
        const response = await fetch(`${BASE_URL}?c=Resena&a=editar`, { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
            body: formData 
        });
        const data = await response.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalEditarResena')).hide();
            Swal.fire('¡Actualizado!', 'Tu reseña ha sido actualizada', 'success');
            const comentarioEl = document.getElementById(`comentario-${idResena}`);
            if (comentarioEl) comentarioEl.innerText = comentario;
            setTimeout(() => location.reload(), 1500);
        } else {
            throw new Error(data.error || 'Error al actualizar');
        }
    } catch (error) { 
        Swal.fire('Error', error.message || 'No se pudo actualizar tu reseña', 'error'); 
    }
}

// =============== ELIMINAR RESEÑA ===============
async function eliminarResena(idResena) {
    const result = await Swal.fire({ 
        title: '¿Eliminar reseña?', 
        text: 'Se eliminará permanentemente', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonColor: '#dc3545', 
        confirmButtonText: 'Sí, eliminar' 
    });
    
    if (result.isConfirmed) {
        try {
            const formData = new URLSearchParams();
            formData.append('id_resena', idResena);
            formData.append('ajax', '1');
            const response = await fetch(`${BASE_URL}?c=Resena&a=eliminar`, { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
                body: formData 
            });
            const data = await response.json();
            if (data.success) {
                const resenaElement = document.querySelector(`#review-${idResena}, #mi-resena`);
                if (resenaElement) {
                    resenaElement.style.transition = 'opacity 0.5s ease';
                    resenaElement.style.opacity = '0';
                    setTimeout(() => resenaElement.remove(), 500);
                }
                Swal.fire({ 
                    icon: 'success', 
                    title: '¡Reseña eliminada!', 
                    timer: 2000, 
                    showConfirmButton: false 
                });
                setTimeout(() => location.reload(), 2000);
            }
        } catch (error) { 
            Swal.fire('Error', 'No se pudo eliminar la reseña', 'error'); 
        }
    }
}

// =============== REPORTAR RESEÑA ===============
function abrirReporte(idResena) {
    document.getElementById('reportar-id-resena').value = idResena;
    new bootstrap.Modal(document.getElementById('modalReportarResena')).show();
}

async function enviarReporte() {
    const idResena = document.getElementById('reportar-id-resena').value;
    const motivo = document.getElementById('reportar-motivo').value;
    const descripcion = document.getElementById('reportar-descripcion').value;
    try {
        const formData = new URLSearchParams();
        formData.append('id_resena', idResena);
        formData.append('motivo', motivo);
        formData.append('descripcion', descripcion);
        formData.append('ajax', '1');
        const response = await fetch(`${BASE_URL}?c=Resena&a=reportar`, { 
            method: 'POST', 
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
            body: formData 
        });
        const data = await response.json();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('modalReportarResena')).hide();
            Swal.fire('Reporte enviado', 'Gracias por ayudarnos a mejorar', 'success');
            document.getElementById('reportar-descripcion').value = '';
        }
    } catch (error) { 
        Swal.fire('Error', 'No se pudo enviar el reporte', 'error'); 
    }
}

// =============== SISTEMA DE FOTOS ===============
function previewFotos(input) {
    const preview = document.getElementById('fotos-preview');
    preview.innerHTML = '';
    if (input.files && input.files.length > 0) {
        Array.from(input.files).slice(0, 5).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.position = 'relative';
                div.style.width = '80px';
                div.style.height = '80px';
                div.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:12px; border: 2px solid var(--accent-blue);">
                                <button type="button" onclick="eliminarFotoPreview(${index})" 
                                        style="position:absolute; top:-6px; right:-6px; width:24px; height:24px; border-radius:50%; background:var(--accent-blue); color:white; border:none; display:flex; align-items:center; justify-content:center; font-size:12px; cursor:pointer;">
                                    <i class="fas fa-times"></i>
                                </button>`;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
}

function eliminarFotoPreview(index) {
    const input = document.getElementById('review-fotos');
    const dt = new DataTransfer();
    Array.from(input.files).forEach((file, i) => { 
        if (i !== index) dt.items.add(file); 
    });
    input.files = dt.files;
    previewFotos(input);
}

async function subirFoto(idResena, input) {
    if (!input.files || input.files.length === 0) return;
    const formData = new FormData();
    formData.append('id_resena', idResena);
    formData.append('foto', input.files[0]);
    formData.append('ajax', '1');
    try {
        const response = await fetch(`${BASE_URL}?c=Resena&a=subirFoto`, { 
            method: 'POST', 
            body: formData 
        });
        const data = await response.json();
        if (data.success) {
            Swal.fire('Foto añadida', 'La foto se ha subido correctamente', 'success');
            setTimeout(() => location.reload(), 1500);
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudo subir la foto', 'error');
    } finally { 
        input.value = ''; 
    }
}

async function eliminarFoto(idFoto) {
    const result = await Swal.fire({ 
        title: '¿Eliminar foto?', 
        icon: 'warning', 
        showCancelButton: true, 
        confirmButtonColor: '#dc3545', 
        confirmButtonText: 'Sí, eliminar' 
    });
    if (result.isConfirmed) {
        try {
            const formData = new URLSearchParams();
            formData.append('id_foto', idFoto);
            formData.append('ajax', '1');
            const response = await fetch(`${BASE_URL}?c=Resena&a=eliminarFoto`, { 
                method: 'POST', 
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, 
                body: formData 
            });
            const data = await response.json();
            if (data.success) {
                Swal.fire('Foto eliminada', 'La foto se ha eliminado correctamente', 'success');
                setTimeout(() => location.reload(), 1500);
            }
        } catch (error) { 
            Swal.fire('Error', 'No se pudo eliminar la foto', 'error'); 
        }
    }
}

function verFoto(foto) {
    const fotoUrl = `${BASE_URL}${foto}`;
    Swal.fire({ 
        imageUrl: fotoUrl, 
        imageAlt: 'Foto de reseña', 
        showConfirmButton: false, 
        showCloseButton: true, 
        width: 'auto', 
        padding: 0, 
        background: 'transparent' 
    });
}

// =============== CONTADOR DE CARACTERES ===============
document.addEventListener('DOMContentLoaded', function() {
    const comentario = document.getElementById('review-comentario');
    if (comentario) {
        comentario.addEventListener('input', function() {
            document.getElementById('comentario-counter').textContent = `${this.value.length}/500`;
        });
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const PRODUCTO_DATA = {
    usuarioLogueado: <?= json_encode($usuario_logueado); ?>,
    variantes: <?= json_encode($variantes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    variantesAgrupadas: <?= json_encode($variantesAgrupadas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    productoBase: { 
        id: <?= (int)$producto->ID_Articulo; ?>, 
        precio: <?= json_encode($producto->Precio); ?>, 
        nombre: <?= json_encode($producto->N_Articulo); ?>, 
        id_categoria: <?= json_encode($producto->ID_Categoria ?? null); ?> 
    },
    baseUrl: <?= json_encode(BASE_URL); ?>,
    infoDescuento: <?= json_encode($infoDescuento, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    todosDescuentos: <?= json_encode($todosDescuentos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
    atributosRequeridos: <?= json_encode($atributosRequeridos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>
  };
</script>
<script src="<?= BASE_URL ?>assets/js/ver.js"></script>
<script src="<?= BASE_URL ?>assets/js/favoritos.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnFavorito = document.getElementById('btn-favorito');
    if (btnFavorito && <?= isset($_SESSION['ID_Usuario']) ? 'true' : 'false'; ?>) {
        const articuloId = btnFavorito.getAttribute('data-articulo-id');
        fetch('<?= BASE_URL; ?>?c=Favorito&a=verificarEstado', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ id_articulo: articuloId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const countElement = document.getElementById('favoritos-count');
                if (countElement && data.totalFavoritos > 0) {
                    countElement.textContent = data.totalFavoritos;
                    countElement.style.display = 'block';
                }
                if (data.esFavorito) {
                    btnFavorito.innerHTML = '<i class="fas fa-heart"></i> En favoritos';
                    btnFavorito.classList.remove('btn-outline-danger');
                    btnFavorito.classList.add('btn-danger');
                }
            }
        })
        .catch(error => console.error('Error al verificar estado de favorito:', error));
    }
});

if (typeof window.BASE_URL === 'undefined') window.BASE_URL = '<?= BASE_URL ?>';
</script>

</body>
</html>