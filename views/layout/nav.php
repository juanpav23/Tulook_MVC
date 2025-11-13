<?php
// views/layout/nav.php - SOLO EL NAVBAR
if (!isset($categorias) || !is_array($categorias)) {
    $categorias = [];
}

if (session_status() === PHP_SESSION_NONE) session_start();
$rolName = $_SESSION['RolName'] ?? null;
$usuario = $_SESSION['usuario'] ?? null;
?>
<!-- SOLO EL NAVBAR - SIN HTML, HEAD, BODY -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background-color:#3a3a3a;">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>"><i class="fas fa-tshirt"></i> TuLook</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>"><i class="fas fa-home"></i> Inicio</a></li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"><i class="fas fa-boxes"></i> Productos</a>
          <ul class="dropdown-menu p-2" style="min-width:280px;">
            <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Producto'; ?>"><i class="fas fa-th-list"></i> Todos</a></li>
            <li><hr class="dropdown-divider"></li>

            <?php
            foreach ($categorias as $cat) {
                $catId = $cat['ID_Categoria'] ?? $cat['id'] ?? null;
                $catName = $cat['N_Categoria'] ?? $cat['name'] ?? ($cat['N_Categoria'] ?? 'Categoría');
                if (!$catId) continue;
                echo '<li><a class="dropdown-item" href="' . BASE_URL . '?c=Producto&a=filtrar&id_categoria=' . $catId . '"><i class="fas fa-tag"></i> ' . htmlspecialchars($catName) . '</a></li>';
            }
            ?>
          </ul>
        </li>

        <?php if ($rolName && strtolower($rolName) === 'administrador'): ?>
          <li class="nav-item">
            <a class="nav-link text-warning" href="<?php echo BASE_URL . '?c=Admin&a=index'; ?>"><i class="fas fa-cog"></i> Panel Admin</a>
          </li>
        <?php endif; ?>
      </ul>

      <form method="GET" action="<?php echo BASE_URL; ?>?c=Producto&a=filtrar" class="d-flex me-3">
        <input type="hidden" name="c" value="Producto">
        <input type="hidden" name="a" value="filtrar">
        <input type="text" name="busqueda" class="form-control me-2" placeholder="Buscar productos..." value="<?php echo htmlspecialchars($_GET['busqueda'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn btn-outline-light"><i class="fas fa-search"></i> Buscar</button>
      </form>

      <div class="d-flex">
        <!-- Favoritos -->
        <a class="btn btn-outline-light me-2" href="<?php echo BASE_URL . '?c=Favorito&a=index'; ?>" title="Mis favoritos">
          <i class="fas fa-heart"></i>
        </a>

        <!-- Carrito -->
        <a class="btn btn-outline-light me-2" href="<?php echo BASE_URL . '?c=Carrito&a=carrito'; ?>" title="Carrito">
          <i class="fas fa-shopping-cart"></i>
        </a>

        <?php if ($usuario): ?>
          <div class="dropdown">
            <a class="btn btn-light dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="fas fa-user"></i> <?php echo htmlspecialchars($usuario); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Usuario&a=perfil'; ?>"><i class="fas fa-id-card"></i> Perfil</a></li>
              <?php if ($rolName && strtolower($rolName) === 'administrador'): ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Admin&a=index'; ?>"><i class="fas fa-cog"></i> Panel Admin</a></li>
              <?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Usuario&a=logout'; ?>"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?php echo BASE_URL . '?c=Usuario&a=login'; ?>" class="btn btn-light">
            <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<!-- FIN DEL NAVBAR -->




