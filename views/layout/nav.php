<?php
// views/layout/nav.php - SOLO EL NAVBAR (VERSIÓN CORREGIDA)
if (!isset($categorias) || !is_array($categorias)) {
    $categorias = [];
}

if (session_status() === PHP_SESSION_NONE) session_start();
$rolName = $_SESSION['RolName'] ?? null;
$usuario = $_SESSION['usuario'] ?? null;
$rolId = $_SESSION['rol'] ?? null;

// Determinar tipo de panel según el rol
$esAdministrador = ($rolId == 1);
$esEditor = ($rolId == 2);
$tieneAccesoAdmin = ($esAdministrador || $esEditor);
$textoPanel = $esAdministrador ? 'Panel Admin' : ($esEditor ? 'Panel Editor' : 'Panel Admin');
?>
<!-- SOLO EL NAVBAR - SIN HTML, HEAD, BODY -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color:#3a3a3a;">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL . '?c=Usuario&a=perfil'; ?>">
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
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>">
            <i class="fas fa-home"></i> Inicio
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-boxes"></i> Productos
          </a>
          <ul class="dropdown-menu p-2" style="min-width:280px;">
            <li>
              <a class="dropdown-item" href="<?php echo BASE_URL . '?c=Producto'; ?>">
                <i class="fas fa-th-list"></i> Todos los productos
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>

            <?php
            foreach ($categorias as $cat) {
                $catId = $cat['ID_Categoria'] ?? $cat['id'] ?? null;
                $catName = $cat['N_Categoria'] ?? $cat['name'] ?? ($cat['N_Categoria'] ?? 'Categoría');
                if (!$catId) continue;
                echo '<li>
                        <a class="dropdown-item" href="' . BASE_URL . '?c=Producto&a=filtrar&id_categoria=' . $catId . '">
                          <i class="fas fa-tag"></i> ' . htmlspecialchars($catName) . '
                        </a>
                      </li>';
            }
            ?>
          </ul>
        </li>

        <?php if ($tieneAccesoAdmin): ?>
          <li class="nav-item">
            <a class="nav-link text-warning fw-bold" href="<?php echo BASE_URL . '?c=Admin&a=index'; ?>">
              <i class="fas fa-cog"></i> 
              <?php echo $textoPanel; ?>
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- Barra de búsqueda -->
      <form method="GET" action="<?php echo BASE_URL; ?>?c=Producto&a=filtrar" class="d-flex me-3">
        <input type="hidden" name="c" value="Producto">
        <input type="hidden" name="a" value="filtrar">
        <input type="text" name="busqueda" class="form-control me-2" placeholder="Buscar productos..." 
               value="<?php echo htmlspecialchars($_GET['busqueda'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn btn-outline-light">
          <i class="fas fa-search"></i> Buscar
        </button>
      </form>

      <!-- Iconos de usuario -->
      <div class="d-flex">
        <!-- Favoritos -->
        <a class="btn btn-outline-light me-2" href="<?php echo BASE_URL . '?c=Favorito&a=index'; ?>" title="Mis favoritos">
          <i class="fas fa-heart"></i>
          <?php if (isset($_SESSION['ID_Usuario'])): ?>
            <span class="badge bg-danger" style="font-size: 0.6rem; position: relative; top: -8px; left: -8px;">❤️</span>
          <?php endif; ?>
        </a>

        <!-- Carrito -->
        <a class="btn btn-outline-light me-2" href="<?php echo BASE_URL . '?c=Carrito&a=carrito'; ?>" title="Carrito">
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
                <a class="dropdown-item" href="<?php echo BASE_URL . '?c=Usuario&a=perfil'; ?>">
                  <i class="fas fa-id-card"></i> Perfil
                </a>
              </li>
              
              <?php if ($tieneAccesoAdmin): ?>
                <li>
                  <a class="dropdown-item text-warning fw-bold" href="<?php echo BASE_URL . '?c=Admin&a=index'; ?>">
                    <i class="fas fa-cog"></i> 
                    <?php echo $textoPanel; ?>
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>

              <li>
                <a class="dropdown-item text-danger" href="<?php echo BASE_URL . '?c=Usuario&a=logout'; ?>">
                  <i class="fas fa-sign-out-alt"></i> Cerrar sesión
                </a>
              </li>
            </ul>
          </div>
        <?php else: ?>
          <!-- Usuario no logueado -->
          <a href="<?php echo BASE_URL . '?c=Usuario&a=login'; ?>" class="btn btn-light">
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
<!-- FIN DEL NAVBAR -->