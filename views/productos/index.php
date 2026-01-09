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
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
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
    .old-price{text-decoration:line-through; color:#777; font-size: .95rem;}
    .variant-list small{font-size: 0.9rem;}
    .price-gratis {
        color: #28a745;
        font-weight: 900;
        font-size: 1.3rem;
        text-transform: uppercase;
    }
  </style>
</head>
<body>
<!-- NAVBAR INCLUIDO -->
<?php include "views/layout/nav.php"; ?>

<div class="container mt-4">
  <!-- FORMULARIO DE BÚSQUEDA -->
  <div class="row mb-4">
    <div class="col-md-8 mx-auto">
      <form class="d-flex" method="GET" action="<?php echo BASE_URL; ?>index.php" id="searchForm">
        <input type="hidden" name="c" value="Producto">
        <input type="hidden" name="a" value="filtrar">
        
        <div class="input-group">
          <input 
            class="form-control" 
            name="busqueda" 
            id="searchInput"
            placeholder="Buscar productos por nombre, categoría..." 
            value="<?php echo htmlspecialchars($_GET['busqueda'] ?? '', ENT_QUOTES); ?>"
            autocomplete="off"
          >
          
          <!-- Botón de búsqueda -->
          <button class="btn btn-primary" type="submit" title="Buscar">
            <i class="fas fa-search"></i>
          </button>
          
          <!-- Botón para limpiar - SIEMPRE VISIBLE -->
          <a href="<?php echo BASE_URL; ?>index.php?c=Producto" 
             class="btn btn-outline-secondary" 
             title="Limpiar búsqueda"
             style="min-width: 42px;">
            <i class="fas fa-times"></i>
          </a>
        </div>
      </form>
      
      <!-- Contenedor para sugerencias -->
      <div id="autocompleteResults" class="mt-1" style="display: none;">
        <div class="list-group" id="suggestionsList"></div>
      </div>
    </div>
  </div>

  <h2 class="text-center mb-4">
    <?php if (!empty($_GET['busqueda'])): ?>
      Resultados de búsqueda
    <?php else: ?>
      Catálogo de Productos
    <?php endif; ?>
  </h2>

  <div class="row">
    <?php if (count($productos) === 0): ?>
      <div class="col-12 text-center py-5">
        <h4>No se encontraron productos</h4>
      </div>
    <?php else: ?>
      <?php foreach ($productos as $p): 
        $idArt = (int)($p['ID_Articulo'] ?? 0);
        $nombre = htmlspecialchars($p['N_Articulo'] ?? 'Sin nombre');
        
        // CORRECCIÓN PARA IMÁGENES
        $foto = $p['Foto'] ?? '';
        
        if (!empty($foto)) {
            // Si ya es una URL completa
            if (strpos($foto, 'http') === 0) {
                $fotoUrl = $foto;
            }
            // Si empieza con ImgProducto/
            elseif (strpos($foto, 'ImgProducto/') === 0) {
                $fotoUrl = BASE_URL . $foto;
            }
            // Si no tiene prefijo, agregarlo
            else {
                $fotoUrl = BASE_URL . 'ImgProducto/' . ltrim($foto, '/');
            }
        } else {
            $fotoUrl = BASE_URL . 'assets/img/placeholder.png';
        }

        $precioBase = isset($p['Precio']) ? (float)$p['Precio'] : 0;
        $precioFinal = isset($p['Precio_Con_Descuento']) ? (float)$p['Precio_Con_Descuento'] : $precioBase;
        $stock  = isset($p['Stock']) ? (int)$p['Stock'] : 0;
        $infoDescuento = $p['Info_Descuento'] ?? null;
        
        // Verificar si hay descuento válido
        $tieneDescuento = false;
        $descuentoPorcentaje = 0;
        $descuentoValor = 0;
        $tipoDescuento = '';
        
        if ($infoDescuento && is_array($infoDescuento)) {
            $tieneDescuento = $infoDescuento['tiene_descuento'] ?? false;
            $descuentoPorcentaje = $infoDescuento['descuento_porcentaje'] ?? 0;
            $descuentoValor = $infoDescuento['valor_descuento'] ?? 0;
            $tipoDescuento = $infoDescuento['tipo_descuento'] ?? '';
        }

      ?>
        <div class="col-md-4 mb-4">
          <div class="card product-card h-100">
            <img src="<?php echo $fotoUrl; ?>" class="card-img-top product-image" alt="<?php echo $nombre; ?>" onerror="this.src='<?php echo BASE_URL; ?>assets/img/placeholder.png'">
            <div class="card-body text-center">
              <h5 class="card-title"><?php echo $nombre; ?></h5>

              <!-- Indicador de variantes disponibles -->
              <?php if (isset($p['Total_Variantes']) && $p['Total_Variantes'] > 0): ?>
                  <small class="text-muted d-block mb-2">
                      <i class="fas fa-palette me-1"></i>
                      <?php echo $p['Total_Variantes']; ?> variante(s) disponible(s)
                  </small>
              <?php endif; ?>

              <!-- $$$ MOSTRAR PRECIOS $$$ -->
              <?php 
              // Verificar si es gratuito
              $esGratis = ($precioFinal == 0 || $precioFinal == 0.00);
              ?>

              <?php if ($esGratis): ?>
                  <p>
                      <?php if ($precioBase > 0): ?>
                          <span class="old-price">$<?php echo number_format($precioBase, 0, ',', '.'); ?></span><br>
                      <?php endif; ?>
                      <span class="price text-success fw-bold">GRATIS</span>
                  </p>
                  <?php if ($precioBase > 0): ?>
                      <span class="badge bg-success">¡Gratuito!</span>
                  <?php endif; ?>
                  
              <?php elseif ($tieneDescuento && $precioFinal < $precioBase): ?>
                  <p>
                    <span class="old-price">$<?php echo number_format($precioBase, 0, ',', '.'); ?></span><br>
                    <span class="price">$<?php echo number_format($precioFinal, 0, ',', '.'); ?></span>
                  </p>
                  <span class="badge bg-danger">
                    <?php if ($tipoDescuento === 'Porcentaje' && $descuentoPorcentaje > 0): ?>
                      -<?php echo $descuentoPorcentaje; ?>%
                    <?php elseif ($tipoDescuento === 'ValorFijo' && $descuentoValor > 0): ?>
                      -$<?php echo number_format($descuentoValor, 0, ',', '.'); ?>
                    <?php else: ?>
                      ¡Oferta!
                    <?php endif; ?>
                  </span>
              <?php else: ?>
                  <p class="price">$<?php echo number_format($precioBase, 0, ',', '.'); ?></p>
              <?php endif; ?>

              <!-- Stock del producto base -->
              <?php if ($stock > 0): ?>
                <p class="text-success mb-2">Disponible: <?php echo $stock; ?></p>
              <?php else: ?>
                <p class="text-danger mb-2">Agotado</p>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const autocompleteResults = document.getElementById('autocompleteResults');
    const suggestionsList = document.getElementById('suggestionsList');
    let debounceTimer;
    
    if (!searchInput) return;
    
    // Evento cuando el usuario escribe
    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideAutocomplete();
            return;
        }
        
        // Usar debounce
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });
    
    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#autocompleteResults') && e.target !== searchInput) {
            hideAutocomplete();
        }
    });
    
    // Tecla ESC para cerrar
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideAutocomplete();
        }
    });
    
    // Función para obtener sugerencias
    function fetchSuggestions(query) {
        // La ruta a autocomplete.php (en la raíz)
        const url = '<?php echo rtrim(BASE_URL, "/"); ?>/autocomplete.php?term=' + encodeURIComponent(query);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    showSuggestions(data);
                } else {
                    hideAutocomplete();
                }
            })
            .catch(error => {
                console.error('Error en autocompletado:', error);
                hideAutocomplete();
            });
    }
    
    // Mostrar sugerencias
    function showSuggestions(items) {
        suggestionsList.innerHTML = '';
        
        items.forEach(item => {
            const suggestionItem = document.createElement('a');
            suggestionItem.href = item.url;
            suggestionItem.className = 'list-group-item list-group-item-action';
            suggestionItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <div style="width:50px;height:50px;margin-right:12px;flex-shrink:0;">
                        ${item.imagen ? 
                            `<img src="${item.imagen}" style="width:100%;height:100%;object-fit:cover;border-radius:4px;">` : 
                            `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f8f9fa;border-radius:4px;">
                                <i class="fas fa-box text-muted"></i>
                            </div>`
                        }
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${item.label}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Producto</small>
                            <small class="text-primary"><i class="fas fa-chevron-right"></i></small>
                        </div>
                    </div>
                </div>
            `;
            
            suggestionsList.appendChild(suggestionItem);
        });
        
        autocompleteResults.style.display = 'block';
    }
    
    // Ocultar autocompletado
    function hideAutocomplete() {
        autocompleteResults.style.display = 'none';
        suggestionsList.innerHTML = '';
    }
    
    // Botón de limpiar
    window.clearSearch = function() {
        searchInput.value = '';
        document.getElementById('searchForm').submit();
    }
});
</script>

<style>
/* Estilos para autocompletado */
#autocompleteResults {
    position: absolute;
    z-index: 1000;
    width: 100%;
    max-width: 600px;
    margin-top: 5px;
}

#autocompleteResults .list-group {
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

#autocompleteResults .list-group-item {
    border: none;
    border-bottom: 1px solid #f0f0f0;
    padding: 12px 15px;
}

#autocompleteResults .list-group-item:last-child {
    border-bottom: none;
}

#autocompleteResults .list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<?php include 'views/chatbot/chat.php'; ?>

</body>
</html>