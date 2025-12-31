<?php
// ========================
// views/productos/ver.php (ACTUALIZADO CON CAMPO DE CÓDIGO DE DESCUENTO)
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
        // Si $db no está definido, intentar obtener la conexión de forma global
        if (!isset($db)) {
            if (isset($GLOBALS['db'])) {
                $db = $GLOBALS['db'];
            } else {
                // Intentar crear una nueva conexión
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
    body { padding-top: 80px; background: #f8f9fa; }
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
        font-weight: 500;
        color: #495057;
    }

    .chip:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-color: #6c757d;
    }

    .chip.active {
        background: #212529;
        color: #fff;
        border-color: #212529;
        box-shadow: 0 4px 12px rgba(33, 37, 41, 0.2);
    }

    .chip.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background: #f8f9fa;
    }

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

    .precio-final { 
      color: #000 !important; 
      font-weight: 700 !important; 
      font-size: 1.4rem !important;
    }
    
    /* Estilos para el campo de descuento */
    #input-codigo-descuento {
        border: 2px solid #dee2e6;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }

    #input-codigo-descuento:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    #btn-aplicar-descuento {
        height: 45px;
        font-weight: 600;
    }

    #mensaje-descuento .alert {
        padding: 8px 12px;
        font-size: 0.9rem;
        border-radius: 6px;
        margin-bottom: 0;
    }

    #descuento-actual .alert {
        padding: 8px 12px;
        font-size: 0.9rem;
        border-radius: 6px;
        margin-bottom: 0;
    }

    #descuento-actual .btn-close {
        padding: 0.5rem;
        font-size: 0.7rem;
    }

    .btn-codigo-descuento {
        min-width: 90px;
        font-weight: 500;
        font-size: 0.85rem;
        padding: 4px 8px;
    }

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

    .precio-final { 
      color: #000 !important; 
      font-weight: 700 !important; 
      font-size: 1.4rem !important;
    }

    .step-badge {
        background: #007bff;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        margin-right: 8px;
    }

    .atributo-group {
        transition: all 0.3s ease;
    }

    .atributo-group .chip.disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .atributo-group .chip:not(.disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    #estado-seleccion {
        transition: all 0.3s ease;
    }

    .precio-gratis {
        color: #28a745 !important;
        font-weight: 900 !important;
        font-size: 1.5rem !important;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-descuento {
        border: 1px solid #dee2e6; 
        border-radius: 8px; 
        padding: 15px; 
        background: #ffffff;
        margin-bottom: 20px;
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

    @media (max-width: 768px) {
      .precio-contenedor-dinamico {
        gap: 5px;
      }
      
      .precio-final {
        font-size: 1.3rem !important;
      }
      
      .card-descuento {
        padding: 12px;
      }
    }

    /* ESTILOS PARA IMÁGENES CONSISTENTES */
    .imagen-variante {
        width: 70px !important;
        height: 70px !important;
        object-fit: cover !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }

    .contenedor-sin-imagen {
        width: 70px !important;
        height: 70px !important;
        border: 1px solid #dee2e6 !important;
        border-radius: 0.375rem !important;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background-color: #f8f9fa !important;
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
        <!-- Botón de Favoritos -->
        <?php if (isset($_SESSION['ID_Usuario'])): ?>
            <div class="mb-3">
                <button id="btn-favorito" class="btn btn-outline-danger btn-sm" 
                        data-articulo-id="<?= htmlspecialchars((string)(int)$producto->ID_Articulo); ?>">
                    <i class="far fa-heart"></i> Agregar a favoritos
                </button>
                <span id="favorito-mensaje" class="small text-muted ms-2" style="display: none;"></span>
                
                <!-- Debug info (solo para testing) -->
                <small class="text-muted d-block mt-1">
                    Artículo ID: <?= (int)$producto->ID_Articulo; ?>
                </small>
            </div>
        <?php else: ?>
            <div class="mb-3">
                <a href="<?= BASE_URL; ?>?c=Usuario&a=login" class="btn btn-outline-secondary btn-sm">
                    <i class="far fa-heart"></i> Inicia sesión para guardar en favoritos
                </a>
            </div>
        <?php endif; ?>

      <!-- $$$ SECCIÓN DE PRECIOS $$$ -->
      <div class="price-container mb-3">
        <div id="precio-text">
          <div class="precio-contenedor-dinamico">
            <span id="precio-original" class="precio-original me-2" style="display: none;"></span>
            <span id="precio-final" class="precio-final">
              <?php 
              //Solo mostrar "GRATIS" si el precio base realmente es 0
              $precioBase = $producto->Precio ?? 0;
              $precioConDescuento = $infoDescuento['tiene_descuento'] ? 
                                    ($infoDescuento['precio_final'] ?? $precioBase) : 
                                    $precioBase;
              
              // Solo mostrar GRATIS si el precio base es 0
              if ($precioBase == 0 || $precioBase == 0.00): 
                echo '<span class="text-success fw-bold">GRATIS</span>';
              else:
                echo '$' . number_format($precioBase, 0, ',', '.');
              endif; 
              ?>
            </span>
            <span id="descuento-badge" class="badge-descuento" style="display: none;">
              <i class="fas fa-tag me-1"></i>
            </span>
          </div>
          <div id="ahorro-info" class="ahorro-info" style="display: none;"></div>
        </div>
      </div>
      <!-- Campo oculto para el precio base REAL -->
      <input type="hidden" id="precio-base-real" value="<?php echo $precioBase; ?>">

      <!-- SECCIÓN: INGRESO DE CÓDIGO DE DESCUENTO -->
      <?php if ($usuario_logueado): ?>
      <div class="mb-4" id="seccion-descuento">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-light">
            <h6 class="mb-0">
              <i class="fas fa-tag me-2"></i>¿Tienes un código de descuento?
            </h6>
          </div>
          <div class="card-body">
            <div class="row align-items-center">
              <div class="col-md-8 mb-2">
                <input type="text" 
                       id="input-codigo-descuento" 
                       class="form-control" 
                       placeholder="Ingresa tu código de descuento (ej: VERANO2024)"
                       maxlength="20">
                <div class="form-text small">
                  <i class="fas fa-info-circle me-1"></i>
                  Ingresa el código y presiona "Aplicar" para validarlo
                </div>
              </div>
              <div class="col-md-4 mb-2">
                <button type="button" id="btn-aplicar-descuento" class="btn btn-outline-success w-100">
                  <i class="fas fa-check me-1"></i>Aplicar
                </button>
              </div>
            </div>
            
            <!-- Mensaje de resultado -->
            <div id="mensaje-descuento" class="mt-2" style="display: none;"></div>
            
            <!-- Descuento aplicado actualmente -->
            <div id="descuento-actual" class="mt-2" style="display: none;">
              <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <i class="fas fa-check-circle me-2"></i>
                    <span id="texto-descuento-activo"></span>
                  </div>
                  <button type="button" class="btn-close btn-sm" id="btn-remover-descuento"></button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Campos ocultos para el descuento seleccionado -->
      <input type="hidden" name="codigo_descuento" id="form-codigo-descuento" value="">
      <input type="hidden" name="tipo_descuento" id="form-tipo-descuento" value="">
      <input type="hidden" name="valor_descuento" id="form-valor-descuento" value="0">
      <input type="hidden" name="id_descuento" id="form-id-descuento" value="">

      <?php else: ?>
        <div class="alert alert-warning border-0 py-2 mb-3">
          <small>
            <i class="fas fa-exclamation-circle me-1"></i>
            <a href="<?= BASE_URL; ?>?c=Usuario&a=login" class="alert-link">Inicia sesión</a> para aplicar descuentos.
          </small>
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
                          data-step="<?= $index + 1 ?>"
                          title="<?= htmlspecialchars($opcion['Valor']) ?>">
                        <?= htmlspecialchars($opcion['Valor']) ?>
                        <?php if (!empty($opcion['CodigoHex'])): ?>
                          <span class="color-preview" style="
                            display: inline-block;
                            width: 16px;
                            height: 16px;
                            background: <?= $opcion['CodigoHex'] ?>;
                            border: 1px solid #ccc;
                            border-radius: 50%;
                            margin-left: 5px;
                          "></span>
                        <?php endif; ?>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  
                  <?php if (!$esPrimerAtributo): ?>
                    <div class="atributo-message mt-2">
                      <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Selecciona primero <?= htmlspecialchars($opcionesAtributos[$atributosRequeridos[0]][0]['Nombre'] ?? 'las opciones anteriores') ?>
                      </small>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          
          <!-- Estado de selección -->
          <div id="estado-seleccion" class="alert alert-info mt-3" style="display: none;">
            <i class="fas fa-spinner fa-spin"></i>
            <span id="texto-estado">Calculando disponibilidad...</span>
          </div>
        </div>
      <?php else: ?>
        <div class="alert alert-info mb-4">
          <i class="fas fa-info-circle me-2"></i>
          Este producto no requiere selección de opciones.
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
        
        <!-- Campos para descuento -->
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
            <img src="<?= $fotoVarUrl; ?>" 
                 style="height:140px;object-fit:cover;width:100%;border-radius:8px" 
                 alt="Variante"
                 onerror="this.src='<?= BASE_URL ?>assets/img/placeholder.png'">
            
            <div class="mt-2 fw-bold">
              <?php foreach ($grupo['atributos'] as $atributo): ?>
                <span class="badge bg-light text-dark me-1">
                  <?= htmlspecialchars($atributo['valor']) ?>
                </span>
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
                      <span class="text-decoration-line-through text-muted me-1">
                        $<?= number_format($variante['Precio_Final'] ?? 0, 0, ',', '.') ?>
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

<?php if (isset($_GET['test'])): ?>
    <script src="<?= BASE_URL ?>assets/js/favoritos-final.js"></script>
<?php endif; ?>

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
<!-- Incluir JavaScript de favoritos -->
<script src="<?= BASE_URL ?>assets/js/favoritos.js"></script>

<!-- Script para verificar estado inicial del favorito -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Si hay un botón de favoritos y el usuario está logueado
    const btnFavorito = document.getElementById('btn-favorito');
    if (btnFavorito && <?= isset($_SESSION['ID_Usuario']) ? 'true' : 'false'; ?>) {
        const articuloId = btnFavorito.getAttribute('data-articulo-id');
        
        // Verificar estado inicial
        fetch('<?= BASE_URL; ?>?c=Favorito&a=verificarEstado', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                id_articulo: articuloId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar contador en navbar
                const countElement = document.getElementById('favoritos-count');
                if (countElement && data.totalFavoritos > 0) {
                    countElement.textContent = data.totalFavoritos;
                    countElement.style.display = 'block';
                }
                
                // Actualizar botón según estado
                if (data.esFavorito) {
                    btnFavorito.innerHTML = '<i class="fas fa-heart"></i> En favoritos';
                    btnFavorito.classList.remove('btn-outline-danger');
                    btnFavorito.classList.add('btn-danger');
                }
            }
        })
        .catch(error => {
            console.error('Error al verificar estado de favorito:', error);
        });
    }
});
// Asegurar que BASE_URL esté definida
if (typeof window.BASE_URL === 'undefined') {
    window.BASE_URL = '<?= BASE_URL ?>';
}
console.log('BASE_URL definida:', window.BASE_URL);
</script>

</body>
</html>