<?php
// views/productos/ver.php
if (!isset($producto)) {
    header("Location: " . BASE_URL);
    exit;
}

$usuario_logueado = isset($_SESSION['ID_Usuario']);
$variantes = $variantes ?? [];
$baseTallas = $baseTallas ?? [];

// Agrupar variantes por color
$variantesPorColor = [];
foreach ($variantes as $v) {
    $cid = $v['ID_Color'] ?? 0;
    if (!isset($variantesPorColor[$cid])) {
        $variantesPorColor[$cid] = [
            'ID_Color' => $cid,
            'N_Color'  => $v['N_Color'] ?? 'Sin color',
            'CodigoHex'=> $v['CodigoHex'] ?? '#cccccc',
            'Foto'     => $v['Foto'] ?? $producto->Foto,
            'opciones' => []
        ];
    }

    $variantesPorColor[$cid]['opciones'][] = [
        'ID_Producto' => $v['ID_Producto'],
        'ID_Talla'    => $v['ID_Talla'],
        'N_Talla'     => $v['N_Talla'],
        'Porcentaje'  => $v['Porcentaje'],
        'Precio_Final'=> $v['Precio_Final'],
        'Cantidad'    => $v['Cantidad'],
        'N_Producto'  => $v['Nombre_Producto'] ?? ($producto->N_Articulo . ' - ' . $v['N_Color'] . ' ' . $v['N_Talla']),
        'Foto'        => $v['Foto'] ?? $producto->Foto
    ];
}
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
    body{padding-top:70px;background:#f8f9fa}
    .product-img{width:100%;object-fit:cover;border-radius:12px}
    .price{color:#e63946;font-size:1.6rem;font-weight:700}
    .chip{display:inline-flex;align-items:center;gap:8px;padding:8px 14px;border-radius:999px;border:1px solid #ddd;margin:4px;cursor:pointer;transition:all 0.2s ease;background:#fff}
    .chip:hover{transform:scale(1.05);box-shadow:0 0 5px rgba(0,0,0,0.1)}
    .chip.active{background:#212529;color:#fff;border-color:#212529}
    .chip.disabled{opacity:0.5;cursor:not-allowed;background:#f8f9fa}
    .color-dot{display:inline-block;width:18px;height:18px;border-radius:50%;border:1px solid #aaa}
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .stock-info{font-size:0.85rem;margin-top:4px;}
    .btn-cart-disabled{opacity:0.6;cursor:not-allowed;}
  </style>
</head>
<body>
<?php include "views/layout/nav.php"; ?>

<div class="container my-5">
  <div class="row g-4">
    <div class="col-md-6 text-center">
      <img id="main-img" src="<?= htmlspecialchars($producto->Foto); ?>" class="product-img shadow-sm" alt="">
    </div>
    <div class="col-md-6">
      <h2 id="nombre-producto"><?= htmlspecialchars($producto->N_Articulo); ?></h2>
      <p class="price" id="precio-text">$<?= number_format($producto->Precio,0,',','.'); ?></p>

      <!-- Favoritos -->
      <div class="mb-3">
        <?php if ($usuario_logueado): ?>
          <form id="fav-form" method="post" action="<?= BASE_URL; ?>?c=Producto&a=toggleFavorito" style="display:inline-block">
            <input type="hidden" name="id_producto" id="fav-id-prod" value="<?= (int)$producto->ID_Articulo; ?>">
            <input type="hidden" name="id_articulo" value="<?= (int)$producto->ID_Articulo; ?>">
            <button type="submit" class="btn <?= ($esFavorito ? 'btn-danger' : 'btn-outline-danger'); ?>">
              <i class="fa fa-heart"></i> <?= ($esFavorito ? 'Quitar de Me Gusta' : 'Añadir a Me Gusta'); ?>
            </button>
          </form>
        <?php else: ?>
          <a href="<?= BASE_URL; ?>?c=Usuario&a=login" class="btn btn-outline-secondary">
            <i class="fa fa-heart"></i> Inicia sesión para favoritos
          </a>
        <?php endif; ?>
      </div>

      <hr>

      <h6>Color</h6>
      <div id="color-container" class="d-flex flex-wrap">
        <?php
        // Mostrar color base + variantes
        $coloresMostrados = [];
        echo '<div class="chip active"
                 data-id="' . htmlspecialchars($producto->ID_Color ?? 'base') . '"
                 data-foto="' . htmlspecialchars($producto->Foto) . '"
                 data-nombre="' . htmlspecialchars($producto->N_Articulo) . '"
                 data-base="1">
                 <span class="color-dot" style="background:' . htmlspecialchars($producto->CodigoHex ?? '#999') . ';"></span> 
                 ' . htmlspecialchars($producto->N_Color ?? 'Base') . '
               </div>';
        $coloresMostrados[$producto->ID_Color ?? 'base'] = true;

        foreach ($variantesPorColor as $c) {
          if (isset($coloresMostrados[$c['ID_Color']])) continue;
          echo '<div class="chip"
                   data-id="' . $c['ID_Color'] . '"
                   data-foto="' . htmlspecialchars($c['Foto']) . '"
                   data-nombre="' . htmlspecialchars($producto->N_Articulo . ' - ' . $c['N_Color']) . '">
                   <span class="color-dot" style="background:' . htmlspecialchars($c['CodigoHex'] ?? '#ccc') . ';"></span>
                   ' . htmlspecialchars($c['N_Color']) . '
                 </div>';
        }
        ?>
      </div>

      <h6 class="mt-3">Talla</h6>
      <div id="talla-container" class="mb-3"></div>
      <div id="stock-info" class="stock-info text-muted"></div>

      <div class="mb-3">
        <label for="cantidad">Cantidad</label>
        <input id="cantidad" type="number" min="1" value="1" class="form-control" style="width:120px;">
      </div>

      <form id="add-cart-form" method="post" action="<?= BASE_URL; ?>?c=Carrito&a=agregar">
        <input type="hidden" name="tipo" id="form-tipo" value="base">
        <input type="hidden" name="id_articulo" value="<?= (int)$producto->ID_Articulo; ?>">
        <input type="hidden" name="id_producto" id="form-id-producto" value="<?= (int)$producto->ID_Articulo; ?>">
        <input type="hidden" name="id_color" id="form-id-color" value="<?= $producto->ID_Color ?? 'base'; ?>">
        <input type="hidden" name="id_talla" id="form-id-talla" value="">
        <input type="hidden" name="cantidad" id="form-cantidad" value="1">
        <button id="btn-add-cart" type="button" class="btn btn-dark w-100 btn-cart-disabled" disabled>
          <i class="fa fa-shopping-cart"></i> Selecciona una talla
        </button>
      </form>
    </div>
  </div>

  <hr class="my-5">
  <h4>Variantes disponibles</h4>
  <div class="row">
    <?php if (empty($variantesPorColor)): ?>
      <div class="col-12 text-center text-muted">No hay variantes disponibles para este producto.</div>
    <?php else: ?>
      <?php foreach ($variantesPorColor as $c): ?>
        <div class="col-md-3 mb-3">
          <div class="card h-100 p-2 text-center">
            <img src="<?= htmlspecialchars($c['Foto']); ?>" style="height:140px;object-fit:cover;width:100%;border-radius:8px">
            <div class="mt-2 fw-bold d-flex align-items-center justify-content-center gap-2">
              <span class="color-dot" style="background:<?= htmlspecialchars($c['CodigoHex'] ?? '#ccc'); ?>;"></span>
              <?= htmlspecialchars($c['N_Color']); ?>
            </div>
            <?php foreach ($c['opciones'] as $o): ?>
              <div class="d-flex justify-content-between align-items-center mt-2 p-2 border rounded">
                <div><?= htmlspecialchars($o['N_Talla']); ?><br>
                  <small class="text-muted">$<?= number_format($o['Precio_Final'],0,',','.'); ?></small>
                  <?php if ($o['Porcentaje'] != 0): ?>
                    <div><small class="text-success">(<?= number_format($o['Porcentaje'],1); ?>%)</small></div>
                  <?php endif; ?>
                </div>
                <div class="text-end">
                  <span class="badge <?= ($o['Cantidad']>0)?'bg-success':'bg-danger'; ?>">
                    <?= ($o['Cantidad']>0)?($o['Cantidad'].' und'):'Agotado'; ?>
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

<script>
// ======= Variables globales =======
const usuarioLogueado = <?= json_encode($usuario_logueado); ?>;
const variantes = <?= json_encode($variantes); ?>;
const baseTallas = <?= json_encode($baseTallas); ?>;
const productoBase = { 
  id: <?= (int)$producto->ID_Articulo; ?>, 
  precio: <?= json_encode($producto->Precio); ?>, 
  nombre: <?= json_encode($producto->N_Articulo); ?> 
};

const colorChips = document.querySelectorAll('#color-container .chip');
const tallaContainer = document.getElementById('talla-container');
const mainImg = document.getElementById('main-img');
const precioText = document.getElementById('precio-text');
const nombreProd = document.getElementById('nombre-producto');
const cantidadInput = document.getElementById('cantidad');
const stockInfo = document.getElementById('stock-info');
const formTipo = document.getElementById('form-tipo');
const formIdProducto = document.getElementById('form-id-producto');
const formIdColor = document.getElementById('form-id-color');
const formIdTalla = document.getElementById('form-id-talla');
const formCantidad = document.getElementById('form-cantidad');
const favIdProd = document.getElementById('fav-id-prod');
const btnAddCart = document.getElementById('btn-add-cart');

// ======= Estado de selección =======
let seleccionActual = {
    tallaSeleccionada: false,
    stockDisponible: 0,
    colorId: '<?= $producto->ID_Color ?? 'base'; ?>',
    productoId: <?= (int)$producto->ID_Articulo; ?>
};

// ======= Funciones =======
function limpiarSeleccionTalla() {
    formIdTalla.value = '';
    formIdProducto.value = seleccionActual.productoId;
    seleccionActual.tallaSeleccionada = false;
    seleccionActual.stockDisponible = 0;
    stockInfo.textContent = '';
    actualizarBotonCarrito();
}

function actualizarBotonCarrito() {
    if (!seleccionActual.tallaSeleccionada) {
        btnAddCart.disabled = true;
        btnAddCart.classList.add('btn-cart-disabled');
        btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Selecciona una talla';
        return;
    }

    if (seleccionActual.stockDisponible === 0) {
        btnAddCart.disabled = true;
        btnAddCart.classList.add('btn-cart-disabled');
        btnAddCart.innerHTML = '<i class="fa fa-times"></i> Sin stock disponible';
        return;
    }

    const cantidad = parseInt(cantidadInput.value) || 1;
    if (cantidad > seleccionActual.stockDisponible) {
        btnAddCart.disabled = true;
        btnAddCart.classList.add('btn-cart-disabled');
        btnAddCart.innerHTML = '<i class="fa fa-times"></i> Stock insuficiente';
        return;
    }

    btnAddCart.disabled = false;
    btnAddCart.classList.remove('btn-cart-disabled');
    btnAddCart.innerHTML = '<i class="fa fa-shopping-cart"></i> Agregar al carrito';
}

function renderBaseTallas() {
    tallaContainer.innerHTML = '';
    limpiarSeleccionTalla();
    
    if (!baseTallas.length) {
        tallaContainer.innerHTML = '<div class="text-muted">No hay tallas disponibles.</div>';
        return;
    }

    baseTallas.forEach(t => {
        const btn = document.createElement('div');
        const tieneStock = t.Cantidad > 0;
        btn.className = `chip ${!tieneStock ? 'disabled' : ''}`;
        btn.textContent = `${t.N_Talla} - $${new Intl.NumberFormat('es-CO').format(productoBase.precio)}`;
        
        if (tieneStock) {
            btn.onclick = () => seleccionarTallaBase(t);
        }
        
        tallaContainer.appendChild(btn);
    });
}

function renderTallasForColor(colorId) {
    tallaContainer.innerHTML = '';
    limpiarSeleccionTalla();
    
    const opciones = variantes.filter(v => String(v.ID_Color) === String(colorId));
    if (!opciones.length) {
        tallaContainer.innerHTML = '<div class="text-muted">No hay tallas para este color.</div>';
        return;
    }

    const ejemplo = opciones[0];
    nombreProd.textContent = ejemplo.Nombre_Producto || `${productoBase.nombre} - ${ejemplo.N_Color}`;
    mainImg.src = ejemplo.Foto || mainImg.src;
    precioText.textContent = `$${new Intl.NumberFormat('es-CO').format(ejemplo.Precio_Final)}`;

    opciones.forEach(opt => {
        const btn = document.createElement('div');
        const tieneStock = opt.Cantidad > 0;
        btn.className = `chip ${!tieneStock ? 'disabled' : ''}`;
        btn.textContent = `${opt.N_Talla} - $${new Intl.NumberFormat('es-CO').format(opt.Precio_Final)}`;
        
        if (tieneStock) {
            btn.onclick = () => seleccionarTallaVariante(opt, colorId);
        }
        
        tallaContainer.appendChild(btn);
    });
}

function seleccionarTallaBase(talla) {
    document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    
    formIdProducto.value = talla.ID_Producto || productoBase.id;
    formIdColor.value = 'base';
    formIdTalla.value = talla.ID_Talla;
    favIdProd.value = talla.ID_Producto || productoBase.id;
    formTipo.value = 'base';
    
    seleccionActual.tallaSeleccionada = true;
    seleccionActual.stockDisponible = talla.Cantidad || 0;
    
    actualizarInfoStock(talla.Cantidad || 0);
    actualizarBotonCarrito();
}

function seleccionarTallaVariante(opcion, colorId) {
    document.querySelectorAll('#talla-container .chip').forEach(c => c.classList.remove('active'));
    event.target.classList.add('active');
    
    formIdProducto.value = opcion.ID_Producto;
    formIdColor.value = colorId;
    formIdTalla.value = opcion.ID_Talla;
    favIdProd.value = opcion.ID_Producto;
    mainImg.src = opcion.Foto || mainImg.src;
    precioText.textContent = `$${new Intl.NumberFormat('es-CO').format(opcion.Precio_Final)}`;
    nombreProd.textContent = opcion.Nombre_Producto || `${productoBase.nombre} - ${opcion.N_Color} ${opcion.N_Talla}`;
    formTipo.value = 'variante';
    
    seleccionActual.tallaSeleccionada = true;
    seleccionActual.stockDisponible = opcion.Cantidad;
    
    actualizarInfoStock(opcion.Cantidad);
    actualizarBotonCarrito();
}

function actualizarInfoStock(stock) {
    if (stock > 0) {
        stockInfo.textContent = `${stock} unidades disponibles`;
        stockInfo.className = 'stock-info text-success';
        cantidadInput.max = stock;
    } else {
        stockInfo.textContent = 'Sin stock disponible';
        stockInfo.className = 'stock-info text-danger';
        cantidadInput.removeAttribute('max');
    }
}

// ======= Eventos =======
colorChips.forEach(chip => {
    chip.addEventListener('click', () => {
        colorChips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        
        const id = chip.dataset.id;
        mainImg.src = chip.dataset.foto;
        nombreProd.textContent = chip.dataset.nombre;
        seleccionActual.colorId = id;

        // Limpiar selección anterior al cambiar de color
        limpiarSeleccionTalla();

        if (chip.dataset.base === "1" || id === "base") {
            precioText.textContent = `$${new Intl.NumberFormat('es-CO').format(productoBase.precio)}`;
            formIdColor.value = 'base';
            formTipo.value = 'base';
            renderBaseTallas();
        } else {
            formIdColor.value = id;
            formTipo.value = 'variante';
            renderTallasForColor(id);
        }
    });
});

cantidadInput.addEventListener('input', () => {
    actualizarBotonCarrito();
});

document.getElementById('btn-add-cart').addEventListener('click', () => {
    if (!usuarioLogueado) {
        Swal.fire({
            icon: 'info',
            title: 'Inicia sesión',
            text: 'Debes iniciar sesión para agregar productos al carrito.',
            confirmButtonText: 'Ir al login'
        }).then(() => window.location.href = "<?= BASE_URL; ?>?c=Usuario&a=login");
        return;
    }

    if (!seleccionActual.tallaSeleccionada) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Selecciona talla', 
            text: 'Debes elegir una talla antes de comprar.' 
        });
        return;
    }

    const qty = parseInt(cantidadInput.value.trim());
    const max = seleccionActual.stockDisponible;

    if (isNaN(qty) || qty <= 0) {
        Swal.fire({ 
            icon: 'warning', 
            title: 'Cantidad inválida', 
            text: 'Solo se permiten números positivos.', 
            timer: 2000 
        });
        cantidadInput.value = 1;
        return;
    }

    if (qty > max) {
        Swal.fire({ 
            icon: 'error', 
            title: 'Stock insuficiente', 
            text: `Solo hay ${max} unidades disponibles.` 
        });
        cantidadInput.value = max;
        return;
    }

    formCantidad.value = qty;

    Swal.fire({
        icon: 'success',
        title: 'Producto agregado 🛒',
        text: 'El producto se agregó correctamente al carrito.',
        showConfirmButton: false,
        timer: 1500
    }).then(() => {
        document.getElementById('add-cart-form').submit();
    });
});

// ======= Inicialización =======
renderBaseTallas();
</script>
</body>
</html>

























