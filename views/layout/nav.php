<?php
// views/layout/nav.php - VERSIÓN CON BARRA DE BÚSQUEDA MEJORADA

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
$textoPanel = $esAdministrador ? 'Admin' : ($esEditor ? 'Editor' : 'Panel');
?>

<!-- Styles para navbar con azul elegante -->
<style>
/* Variables CSS con la paleta proporcionada */
:root {
    --primary-dark: #1B202D;
    --primary-blue: #2A3448;
    --accent-blue: #3A4A6B;
    --secondary-blue: #4A5B7D;
    --light-blue: #5B6E8F;
    --light-bg: #f5f7fa;
    --text-dark: #2c3e50;
    --text-light: #6c757d;
    --border-radius: 12px;
    --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    --card-shadow-hover: 0 8px 20px rgba(0, 0, 0, 0.12);
    --transition: all 0.3s ease;
}

/* Navbar principal con la paleta de colores */
.navbar-custom {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-blue) 100%);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.25);
    padding: 0.5rem 0;
    border-bottom: 3px solid var(--accent-blue);
    min-height: 60px;
    backdrop-filter: blur(10px);
}

.navbar-brand {
    font-size: 1.5rem;
    font-weight: 800;
    color: #fff !important;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: var(--transition);
    letter-spacing: -0.5px;
    font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
}

.navbar-brand:hover {
    color: var(--light-blue) !important;
    transform: translateY(-1px);
}

.navbar-brand i {
    color: var(--light-blue);
    font-size: 1.7rem;
    text-shadow: 0 2px 4px rgba(58, 74, 107, 0.3);
}

.brand-badge {
    font-size: 0.5rem;
    padding: 2px 6px;
    margin-left: 5px;
    border-radius: 4px;
    background: linear-gradient(135deg, var(--accent-blue) 0%, var(--primary-blue) 100%) !important;
    border: none;
}

/* Items del menú */
.nav-item .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    font-weight: 600;
    padding: 0.5rem 1rem !important;
    margin: 0 3px;
    border-radius: var(--border-radius);
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
    position: relative;
    overflow: hidden;
}

.nav-item .nav-link i {
    font-size: 1rem;
    width: 20px;
    text-align: center;
    transition: var(--transition);
}

.nav-item .nav-link:hover,
.nav-item .nav-link:focus {
    background: linear-gradient(135deg, rgba(58, 74, 107, 0.15) 0%, rgba(42, 52, 72, 0.1) 100%);
    color: #fff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(58, 74, 107, 0.2);
}

.nav-item .nav-link:hover i {
    transform: scale(1.1);
}

.nav-item .nav-link.text-primary {
    color: var(--light-blue) !important;
    font-weight: 700;
}

.nav-item .nav-link.text-primary:hover {
    background: linear-gradient(135deg, rgba(58, 74, 107, 0.2) 0%, rgba(42, 52, 72, 0.15) 100%);
}

/* BARRA DE BÚSQUEDA CORREGIDA Y ELEGANTE */
.search-container {
    position: relative;
    min-width: 220px;
    max-width: 320px;
    margin: 0 15px;
    flex: 1;
}

.search-input-group {
    background: rgba(255, 255, 255, 0.1);
    border-radius: var(--border-radius);
    padding: 2px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    transition: var(--transition);
    backdrop-filter: blur(10px);
    box-shadow: var(--card-shadow);
    display: flex;
    align-items: center;
}

.search-input-group:focus-within {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(58, 74, 107, 0.6);
    box-shadow: 0 0 0 3px rgba(58, 74, 107, 0.1), var(--card-shadow-hover);
    transform: translateY(-1px);
}

/* Botón de búsqueda a la derecha */
.search-btn {
    background: transparent !important;
    border: none !important;
    color: rgba(255, 255, 255, 0.8) !important;
    width: 44px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    transition: var(--transition);
    font-size: 1rem;
    border-radius: 0 var(--border-radius) var(--border-radius) 0;
    order: 3;
}

.search-btn:hover {
    color: var(--light-blue) !important;
    background: rgba(255, 255, 255, 0.1) !important;
}

/* Botón para limpiar a la izquierda */
.clear-btn {
    background: transparent !important;
    border: none !important;
    color: rgba(255, 255, 255, 0.6) !important;
    width: 44px;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    transition: var(--transition);
    font-size: 1rem;
    border-radius: var(--border-radius) 0 0 var(--border-radius);
    order: 1;
}

.clear-btn:hover {
    color: var(--light-blue) !important;
    background: rgba(255, 255, 255, 0.1) !important;
}

/* Input en el centro */
.search-input {
    background: transparent !important;
    border: none !important;
    color: #fff !important;
    padding: 10px 12px !important;
    height: 42px;
    font-size: 0.95rem;
    font-weight: 500;
    flex: 1;
    order: 2;
    min-width: 0;
}

.search-input::placeholder {
    color: rgba(255, 255, 255, 0.7);
    font-weight: 400;
}

.search-input:focus {
    box-shadow: none !important;
    background: transparent !important;
    outline: none;
}

/* Dropdown ELEGANTE */
.dropdown-menu {
    background: linear-gradient(135deg, #ffffff 0%, var(--light-bg) 100%);
    border: none;
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow-hover);
    margin-top: 8px !important;
    min-width: 220px;
    padding: 8px 0;
    border: 1px solid rgba(226, 232, 240, 0.8);
    overflow: hidden;
}

.dropdown-item {
    padding: 12px 16px;
    color: var(--text-dark);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: var(--transition);
    font-size: 0.95rem;
    margin: 0 4px;
    border-radius: var(--border-radius);
    position: relative;
    overflow: hidden;
}

.dropdown-item i {
    width: 20px;
    color: var(--text-light);
    font-size: 1rem;
    transition: var(--transition);
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: var(--primary-dark);
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dropdown-item:hover i {
    color: var(--accent-blue);
    transform: scale(1.1);
}

.dropdown-divider {
    margin: 8px 16px;
    border-color: #e2e8f0;
    opacity: 0.6;
}

/* Botones de usuario elegantes */
.user-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-nav-icon {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: rgba(255, 255, 255, 0.9) !important;
    width: 42px;
    height: 42px;
    border-radius: var(--border-radius) !important;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    position: relative;
    transition: var(--transition);
    font-size: 1rem;
    backdrop-filter: blur(10px);
}

.btn-nav-icon:hover {
    background: linear-gradient(135deg, rgba(58, 74, 107, 0.2) 0%, rgba(42, 52, 72, 0.15) 100%) !important;
    border-color: rgba(58, 74, 107, 0.5) !important;
    color: var(--light-blue) !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(58, 74, 107, 0.25);
}

.btn-nav-icon .badge {
    position: absolute;
    top: -6px;
    right: -6px;
    font-size: 0.6rem;
    padding: 3px 7px;
    min-width: 18px;
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
    border: 2px solid var(--primary-dark);
    font-weight: 700;
}

/* Dropdown de usuario ELEGANTE */
.user-dropdown .btn-user {
    background: linear-gradient(135deg, var(--accent-blue) 0%, var(--primary-blue) 100%);
    border: none;
    color: white !important;
    font-weight: 700;
    padding: 10px 18px !important;
    border-radius: var(--border-radius);
    display: flex;
    align-items: center;
    gap: 10px;
    transition: var(--transition);
    font-size: 0.95rem;
    height: 44px;
    box-shadow: 0 4px 15px rgba(58, 74, 107, 0.3);
    position: relative;
    overflow: hidden;
}

.user-dropdown .btn-user:before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: 0.5s;
}

.user-dropdown .btn-user:hover:before {
    left: 100%;
}

.user-dropdown .btn-user:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(58, 74, 107, 0.4);
}

.user-dropdown .btn-user:after {
    display: none;
}

.user-dropdown .dropdown-menu {
    min-width: 240px;
}

.user-dropdown .dropdown-item.text-danger {
    color: #ef4444 !important;
}

.user-dropdown .dropdown-item.text-danger:hover {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626 !important;
}

/* Badge de rol elegante */
.role-badge {
    font-size: 0.55rem;
    padding: 3px 8px;
    margin-left: 6px;
    border-radius: 6px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    border: 2px solid rgba(255, 255, 255, 0.3);
    font-weight: 700;
}

.role-badge.bg-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
}

/* Autocompletado elegante */
#navAutocompleteResults {
    background: linear-gradient(135deg, #ffffff 0%, var(--light-bg) 100%);
    border-radius: var(--border-radius);
    box-shadow: var(--card-shadow-hover);
    border: 1px solid rgba(226, 232, 240, 0.8);
    margin-top: 10px !important;
    overflow: hidden;
    max-height: 350px;
    z-index: 1060;
    backdrop-filter: blur(20px);
}

#navAutocompleteResults .list-group-item {
    border: none;
    border-bottom: 1px solid #f1f5f9;
    padding: 12px 16px;
    transition: var(--transition);
    background: transparent;
}

#navAutocompleteResults .list-group-item:hover {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    transform: translateX(5px);
}

#navAutocompleteResults .list-group-item:last-child {
    border-bottom: none;
}

/* Navbar toggler elegante */
.navbar-toggler {
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 6px 10px;
    border-radius: var(--border-radius);
    transition: var(--transition);
}

.navbar-toggler:focus {
    box-shadow: 0 0 0 3px rgba(58, 74, 107, 0.25);
    border-color: var(--light-blue);
}

.navbar-toggler-icon {
    width: 1.3em;
    height: 1.3em;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.9%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
}

/* Alertas elegantes */
.alert-container {
    position: fixed;
    top: 65px;
    left: 0;
    right: 0;
    z-index: 1050;
    padding: 0 15px;
}

.alert {
    padding: 12px 16px;
    font-size: 0.95rem;
    border-radius: var(--border-radius);
    margin-bottom: 8px;
    border: none;
    box-shadow: var(--card-shadow);
    backdrop-filter: blur(10px);
}

.alert i {
    font-size: 1rem;
}

.btn-close {
    padding: 12px;
    opacity: 0.7;
    transition: var(--transition);
}

.btn-close:hover {
    opacity: 1;
}

/* Espacio para navbar fixed */
.navbar-spacer {
    height: 65px;
}

/* Responsive mejorado */
@media (max-width: 991px) {
    .search-container {
        min-width: 100%;
        margin: 15px 0;
        order: 3;
        max-width: 100%;
    }
    
    .navbar-collapse {
        padding: 15px 0;
    }
    
    .user-actions {
        justify-content: flex-start;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .nav-item .nav-link {
        padding: 10px 15px !important;
        margin: 2px 0;
    }
    
    .dropdown-menu {
        background: rgba(27, 32, 45, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .dropdown-item {
        color: rgba(255, 255, 255, 0.9);
    }
    
    .dropdown-item:hover {
        background: rgba(255, 255, 255, 0.1);
        color: #fff;
    }
    
    .dropdown-item i {
        color: rgba(255, 255, 255, 0.7);
    }
    
    .dropdown-item:hover i {
        color: var(--light-blue);
    }
}

@media (max-width: 768px) {
    .navbar-brand {
        font-size: 1.3rem;
    }
    
    .navbar-brand i {
        font-size: 1.5rem;
    }
    
    .search-input {
        font-size: 0.9rem;
        padding: 8px 10px !important;
    }
    
    .btn-nav-icon {
        width: 40px;
        height: 40px;
        font-size: 0.95rem;
    }
    
    .btn-user {
        height: 40px;
        font-size: 0.9rem;
        padding: 8px 12px !important;
    }
}

/* Animaciones suaves */
.navbar-custom,
.search-input-group,
.btn-nav-icon,
.user-dropdown .btn-user,
.dropdown-menu,
#navAutocompleteResults {
    animation: slideDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mejoras específicas para la barra de búsqueda */
.search-input-group.focused {
    transform: scale(1.02);
}

.search-btn i, .clear-btn i {
    transition: transform 0.3s ease;
}

.search-btn:hover i, .clear-btn:hover i {
    transform: scale(1.1);
}

/* Contenedor para los botones de búsqueda */
.search-btn-wrapper {
    display: flex;
    align-items: center;
}
</style>

<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
  <div class="container-fluid px-3">
    <!-- Logo elegante -->
    <a class="navbar-brand" href="<?php echo BASE_URL; ?>index.php">
      <i class="fas fa-tshirt"></i> TuLook
      <?php if ($tieneAccesoAdmin): ?>
        <span class="brand-badge badge">
          <?php echo $esAdministrador ? 'ADMIN' : 'EDITOR'; ?>
        </span>
      <?php endif; ?>
    </a>

    <!-- Botón móvil elegante -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- Menú elegante -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>index.php">
            <i class="fas fa-home"></i> <span class="d-none d-md-inline">Inicio</span>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-boxes"></i> <span class="d-none d-md-inline">Productos</span>
          </a>
          <ul class="dropdown-menu">
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

        <!-- Enlace de seguimiento elegante -->
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>views/seguimiento/consultar.php">
            <i class="fas fa-truck"></i> <span class="d-none d-md-inline">Seguimiento</span>
          </a>
        </li>

        <?php if ($tieneAccesoAdmin): ?>
          <li class="nav-item">
            <a class="nav-link text-primary" href="<?php echo BASE_URL; ?>index.php?c=Admin&a=index">
              <i class="fas fa-cog"></i> <span class="d-none d-md-inline"><?php echo $textoPanel; ?></span>
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- BARRA DE BÚSQUEDA CORREGIDA Y ELEGANTE -->
      <form method="GET" action="<?php echo BASE_URL; ?>index.php" class="search-container" id="navSearchForm">
        <input type="hidden" name="c" value="Producto">
        <input type="hidden" name="a" value="filtrar">
        
        <div class="search-input-group">
          <!-- Botón para limpiar a la IZQUIERDA -->
          <a href="<?php echo BASE_URL; ?>index.php?c=Producto" 
            class="clear-btn" 
            title="Limpiar búsqueda">
            <i class="fas fa-times"></i>
          </a>
          
          <!-- Input de búsqueda en el CENTRO -->
          <input type="text" 
                name="busqueda" 
                class="form-control search-input" 
                id="navSearchInput"
                placeholder="Buscar productos..." 
                value="<?php echo htmlspecialchars($_GET['busqueda'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                autocomplete="off">
          
          <!-- Botón de búsqueda a la DERECHA -->
          <button type="submit" class="search-btn" title="Buscar">
            <i class="fas fa-search"></i>
          </button>
        </div>
        
        <!-- Contenedor para sugerencias elegante -->
        <div id="navAutocompleteResults" class="position-absolute w-100" style="display: none;">
          <div class="list-group" id="navSuggestionsList"></div>
        </div>
      </form>

      <!-- Iconos de usuario elegantes -->
      <div class="user-actions">
        <!-- Favoritos -->
        <?php if (isset($usuario)): ?>
            <a class="btn-nav-icon" 
              href="<?php echo BASE_URL; ?>?c=Favorito&a=index" 
              title="Mis Favoritos"
              id="nav-favoritos">
                <i class="fas fa-heart"></i>
                <span id="favoritos-count" class="badge" style="display: none;">
                    0
                </span>
            </a>
        <?php endif; ?>
        
        <!-- Carrito elegante -->
        <a class="btn-nav-icon" href="<?php echo BASE_URL; ?>index.php?c=Carrito&a=carrito" title="Carrito">
          <i class="fas fa-shopping-cart"></i>
          <?php if (!empty($_SESSION['carrito'])): ?>
            <span class="badge">
              <?php echo count($_SESSION['carrito']); ?>
            </span>
          <?php endif; ?>
        </a>

        <?php if ($usuario): ?>
          <!-- Usuario logueado elegante - NO ACORTADO -->
          <div class="dropdown user-dropdown">
            <a class="btn btn-user dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="fas fa-user-circle"></i> 
              <span class="d-none d-lg-inline"><?php echo htmlspecialchars($usuario); ?></span>
              <?php if ($tieneAccesoAdmin): ?>
                <span class="role-badge badge <?php echo $esAdministrador ? 'bg-danger' : ''; ?>">
                  <?php echo $esAdministrador ? 'ADMIN' : 'EDITOR'; ?>
                </span>
              <?php endif; ?>
              <i class="fas fa-chevron-down ms-1" style="font-size: 0.8rem;"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?c=Usuario&a=perfil">
                  <i class="fas fa-user"></i> Mi Perfil
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?c=Usuario&a=misPedidos">
                  <i class="fas fa-box"></i> Mis Pedidos
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="<?php echo BASE_URL; ?>?c=Favorito&a=index">
                  <i class="fas fa-heart"></i> Mis Favoritos
                </a>
              </li>
              
              <?php if ($tieneAccesoAdmin): ?>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="<?php echo BASE_URL; ?>index.php?c=Admin&a=index">
                    <i class="fas fa-cog"></i> Panel de Administración
                  </a>
                </li>
              <?php endif; ?>

              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>index.php?c=Usuario&a=logout">
                  <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <!-- Usuario no logueado elegante -->
          <a href="<?php echo BASE_URL; ?>index.php?c=Usuario&a=login" class="btn btn-user" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
            <i class="fas fa-sign-in-alt me-2"></i> <span>Iniciar Sesión</span>
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Espacio elegante para evitar que el contenido quede detrás del navbar fixed -->
<div class="navbar-spacer"></div>

<!-- Contenedor para alertas elegantes -->
<div class="alert-container">
    <?php if (isset($_SESSION['mensaje'])): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i> <?php echo $_SESSION['mensaje']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensaje']); endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i> <?php echo $_SESSION['mensaje_error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensaje_error']); endif; ?>

    <?php if (isset($_SESSION['mensaje_ok'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['mensaje_ok']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensaje_ok']); endif; ?>
</div>

<!-- JavaScript para autocompletado elegante -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const navSearchInput = document.getElementById('navSearchInput');
    const navAutocompleteResults = document.getElementById('navAutocompleteResults');
    const navSuggestionsList = document.getElementById('navSuggestionsList');
    const searchInputGroup = document.querySelector('.search-input-group');
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
    
    // Focus en el input
    navSearchInput.addEventListener('focus', function() {
        searchInputGroup.classList.add('focused');
    });
    
    navSearchInput.addEventListener('blur', function() {
        searchInputGroup.classList.remove('focused');
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
            navSearchInput.blur();
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
            suggestionItem.innerHTML = `
                <div class="d-flex align-items-center">
                    <div style="width:45px;height:45px;margin-right:12px;flex-shrink:0;border-radius:8px;overflow:hidden;border:2px solid #e2e8f0;">
                        ${item.imagen ? 
                            `<img src="${item.imagen}" style="width:100%;height:100%;object-fit:cover;">` : 
                            `<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);color:#64748b;">
                                <i class="fas fa-box"></i>
                            </div>`
                        }
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark mb-1" style="font-size:0.95rem;">${item.label}</div>
                        <small class="text-muted" style="font-size:0.85rem;">
                            <i class="fas fa-tag me-1"></i>${item.categoria || 'Producto'}
                        </small>
                    </div>
                    <i class="fas fa-chevron-right text-muted"></i>
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