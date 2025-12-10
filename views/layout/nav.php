<?php
// views/layout/nav.php - VERSIÓN CORREGIDA

// Función para obtener BASE_URL de forma confiable
function getBaseUrlForNav() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Obtener el directorio base del script actual
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    
    // Si estamos en el directorio raíz
    if ($script_dir === '/' || $script_dir === '\\') {
        $base_url = $protocol . '://' . $host . '/';
    } else {
        $base_url = $protocol . '://' . $host . $script_dir . '/';
    }
    
    return rtrim($base_url, '/') . '/';
}

// Usar esta función si BASE_URL no está definida
if (!defined('BASE_URL')) {
    define('BASE_URL', getBaseUrlForNav());
}

// Si hay sesión, obtener datos de usuario
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rolName = $_SESSION['RolName'] ?? null;
$usuario = $_SESSION['usuario'] ?? null;
$rolId = $_SESSION['rol'] ?? null;
$esAdministrador = ($rolId == 1);
$esEditor = ($rolId == 2);
$tieneAccesoAdmin = ($esAdministrador || $esEditor);
$textoPanel = $esAdministrador ? 'Panel Admin' : ($esEditor ? 'Panel Editor' : 'Panel');
?>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color:#3a3a3a;">
  <div class="container">
    <!-- Logo -->
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php">
      <i class="fas fa-tshirt"></i> TuLook
      <?php if ($tieneAccesoAdmin): ?>
        <small class="badge bg-warning ms-1" style="font-size: 0.6rem;">
          <?php echo $esAdministrador ? 'ADMIN' : 'EDITOR'; ?>
        </small>
      <?php endif; ?>
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Menú izquierda -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">
            <i class="fas fa-home"></i> Inicio
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-boxes"></i> Productos
          </a>
          <ul class="dropdown-menu p-2" style="min-width:280px;">
            <li>
              <a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?c=Producto">
                <i class="fas fa-th-list"></i> Todos los productos
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            
            <?php if (isset($categorias) && is_array($categorias)): ?>
              <?php foreach ($categorias as $cat): 
                  $catId = $cat['ID_Categoria'] ?? $cat['id'] ?? null;
                  $catName = $cat['N_Categoria'] ?? $cat['name'] ?? ($cat['N_Categoria'] ?? 'Categoría');
                  if (!$catId) continue;
              ?>
                <li>
                  <a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?c=Producto&a=filtrar&id_categoria=<?php echo $catId; ?>">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($catName); ?>
                  </a>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </li>

        <?php if ($tieneAccesoAdmin): ?>
          <li class="nav-item">
            <a class="nav-link text-warning fw-bold" href="<?php echo BASE_URL; ?>index.php?c=Admin&a=index">
              <i class="fas fa-cog"></i> <?php echo $textoPanel; ?>
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Barra de búsqueda -->
      <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="d-flex me-3 position-relative" id="navSearchForm">
        <input type="hidden" name="c" value="Producto">
        <input type="hidden" name="a" value="filtrar">
        
        <div class="input-group" style="min-width: 280px;">
          <input type="text" 
                name="busqueda" 
                class="form-control" 
                id="navSearchInput"
                placeholder="Buscar productos..." 
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                autocomplete="off">
          
          <!-- Botón de búsqueda -->
          <button type="submit" class="btn btn-outline-light">
            <i class="fas fa-search"></i>
          </button>
          
          <!-- Botón para limpiar - SIEMPRE VISIBLE -->
          <a href="<?php echo BASE_URL; ?>index.php?c=Producto" 
            class="btn btn-outline-light" 
            title="Limpiar búsqueda"
            style="min-width: 42px;">
            <i class="fas fa-times"></i>
          </a>
        </div>
        
        <!-- Contenedor para sugerencias -->
        <div id="navAutocompleteResults" class="position-absolute w-100 mt-1" style="top: 100%; z-index: 1000; display: none;">
          <div class="list-group" id="navSuggestionsList"></div>
        </div>
      </form>

      <!-- Iconos de usuario -->
      <div class="d-flex">
        <!-- Favoritos -->
        <a class="btn btn-outline-light me-2" href="<?php echo BASE_URL; ?>index.php?c=Favorito&a=index" title="Mis favoritos">
          <i class="fas fa-heart"></i>
          <?php if (isset($_SESSION['ID_Usuario'])): ?>
            <span class="badge bg-danger" style="font-size: 0.6rem; position: relative; top: -8px; left: -8px;">❤️</span>
          <?php endif; ?>
        </a>

        <!-- Carrito -->
        <a class="btn btn-outline-light me-2" href="<?php echo BASE_URL; ?>index.php?c=Carrito&a=carrito" title="Carrito">
          <i class="fas fa-shopping-cart"></i>
          <?php if (!empty($_SESSION['carrito'])): ?>
            <span class="badge bg-primary" style="font-size: 0.6rem; position: relative; top: -8px; left: -8px;">
              <?php echo count($_SESSION['carrito']); ?>
            </span>
          <?php endif; ?>
        </a>

        <?php if ($usuario): ?>
          <!-- Usuario logueado -->
          <div class="dropdown">
            <a class="btn btn-light dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="fas fa-user"></i> 
              <?php echo htmlspecialchars($usuario); ?>
              <?php if ($tieneAccesoAdmin): ?>
                <span class="badge <?php echo $esAdministrador ? 'bg-danger' : 'bg-warning text-dark'; ?> ms-1" style="font-size: 0.5rem;">
                  <?php echo $esAdministrador ? 'ADMIN' : 'EDITOR'; ?>
                </span>
              <?php endif; ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?c=Usuario&a=perfil">
                  <i class="fas fa-id-card"></i> Perfil
                </a>
              </li>
              
              <?php if ($tieneAccesoAdmin): ?>
                <li>
                  <a class="dropdown-item text-warning fw-bold" href="<?php echo BASE_URL; ?>index.php?c=Admin&a=index">
                    <i class="fas fa-cog"></i> <?php echo $textoPanel; ?>
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>

              <li>
                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>index.php?c=Usuario&a=logout">
                  <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <!-- Usuario no logueado -->
          <a href="<?php echo BASE_URL; ?>index.php?c=Usuario&a=login" class="btn btn-light">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Espacio para evitar que el contenido quede detrás del navbar fixed -->
<div style="height: 80px;"></div>

<!-- Mostrar mensajes de sesión -->
<?php if (isset($_SESSION['mensaje'])): ?>
<div class="container mt-3">
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="fas fa-info-circle"></i> <?php echo $_SESSION['mensaje']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php unset($_SESSION['mensaje']); endif; ?>

<?php if (isset($_SESSION['mensaje_error'])): ?>
<div class="container mt-3">
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['mensaje_error']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php unset($_SESSION['mensaje_error']); endif; ?>

<?php if (isset($_SESSION['mensaje_ok'])): ?>
<div class="container mt-3">
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle"></i> <?php echo $_SESSION['mensaje_ok']; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
</div>
<?php unset($_SESSION['mensaje_ok']); endif; ?>

<!-- JavaScript para autocompletado en navbar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navSearchInput = document.getElementById('navSearchInput');
    const navAutocompleteResults = document.getElementById('navAutocompleteResults');
    const navSuggestionsList = document.getElementById('navSuggestionsList');
    let navDebounceTimer;
    
    if (!navSearchInput) return;
    
    // Evento cuando el usuario escribe
    navSearchInput.addEventListener('input', function(e) {
        clearTimeout(navDebounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            hideNavAutocomplete();
            return;
        }
        
        // Usar debounce
        navDebounceTimer = setTimeout(() => {
            fetchNavSuggestions(query);
        }, 300);
    });
    
    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#navAutocompleteResults') && e.target !== navSearchInput) {
            hideNavAutocomplete();
        }
    });
    
    // Tecla ESC para cerrar
    navSearchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideNavAutocomplete();
        }
    });
    
    // Función para obtener sugerencias
    function fetchNavSuggestions(query) {
        // La ruta a autocomplete.php (en la raíz)
        const url = '<?php echo rtrim(BASE_URL, "/"); ?>/autocomplete.php?term=' + encodeURIComponent(query);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    showNavSuggestions(data);
                } else {
                    hideNavAutocomplete();
                }
            })
            .catch(error => {
                console.error('Error en autocompletado:', error);
                hideNavAutocomplete();
            });
    }
    
    // Mostrar sugerencias en navbar
    function showNavSuggestions(items) {
        navSuggestionsList.innerHTML = '';
        
        items.forEach(item => {
            const suggestionItem = document.createElement('a');
            suggestionItem.href = item.url;
            suggestionItem.className = 'list-group-item list-group-item-action';
            suggestionItem.style.borderRadius = '0';
            suggestionItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <div style="width:40px;height:40px;margin-right:10px;flex-shrink:0;">
                        ${item.imagen ? 
                            `<img src="${item.imagen}" style="width:100%;height:100%;object-fit:cover;border-radius:4px;">` : 
                            `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#f8f9fa;border-radius:4px;">
                                <i class="fas fa-box text-muted"></i>
                            </div>`
                        }
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${item.label}</div>
                        <small class="text-muted">Producto</small>
                    </div>
                </div>
            `;
            
            navSuggestionsList.appendChild(suggestionItem);
        });
        
        navAutocompleteResults.style.display = 'block';
    }
    
    // Ocultar autocompletado navbar
    function hideNavAutocomplete() {
        navAutocompleteResults.style.display = 'none';
        navSuggestionsList.innerHTML = '';
    }
});
</script>

<style>
/* Estilos para autocompletado en navbar */
#navAutocompleteResults {
    max-height: 300px;
    overflow-y: auto;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

#navAutocompleteResults .list-group-item {
    border: none;
    border-bottom: 1px solid #f0f0f0;
    padding: 10px 15px;
}

#navAutocompleteResults .list-group-item:last-child {
    border-bottom: none;
}

#navAutocompleteResults .list-group-item:hover {
    background-color: #f8f9fa;
}

/* Asegurar que el z-index sea correcto */
.navbar {
    z-index: 1030;
}

#navAutocompleteResults {
    z-index: 1040;
}
</style>