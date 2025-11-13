<?php
// =============================
// VIEWS/LAYOUT/NAV.PHP
// =============================
if (!isset($categorias) || !is_array($categorias)) {
    $categorias = [];
}

if (session_status() === PHP_SESSION_NONE) session_start();
$rolName = $_SESSION['RolName'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        nav.navbar {
            background-color: #3a3a3a;
        }
        .navbar .nav-link,
        .navbar .navbar-brand {
            color: #fff !important;
        }
        .navbar .nav-link:hover {
            color: #ff7675 !important;
        }
        .btn-outline-light:hover {
            background-color: #ff7675;
            border-color: #ff7675;
        }
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>">
      <i class="fas fa-tshirt"></i> TuLook
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <!-- ====== Menú principal ====== -->
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="<?php echo BASE_URL; ?>">Inicio</a>
        </li>

        <!-- Menú dinámico de productos -->
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="categoriasDropdown" role="button" data-bs-toggle="dropdown">
            Productos
          </a>
          <ul class="dropdown-menu p-2" style="min-width:280px;">
            <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Producto'; ?>">Todos</a></li>
            <li><hr class="dropdown-divider"></li>

            <?php foreach ($categorias as $catId => $cat): ?>
              <li class="dropdown-header"><?php echo htmlspecialchars($cat['name']); ?></li>

              <?php foreach ($cat['genders'] as $g): ?>
                <li class="dropend">
                  <a class="dropdown-item dropdown-toggle" href="#" data-bs-toggle="dropdown">
                    <?php echo htmlspecialchars($g['genero_name']); ?>
                  </a>
                  <ul class="dropdown-menu">
                    <li>
                      <a class="dropdown-item" href="<?php echo BASE_URL . '?c=Producto&a=filtrar&id_categoria=' . $catId . '&id_genero=' . $g['genero_id']; ?>">
                        Ver todo <?php echo htmlspecialchars($g['genero_name']); ?>
                      </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <?php foreach ($g['subcategories'] as $sub): ?>
                      <li>
                        <a class="dropdown-item" href="<?php echo BASE_URL . '?c=Producto&a=filtrar&id_categoria=' . $catId . '&id_genero=' . $g['genero_id'] . '&id_subcategoria=' . $sub['id']; ?>">
                          <?php echo htmlspecialchars($sub['name']); ?>
                        </a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                </li>
              <?php endforeach; ?>

              <li><hr class="dropdown-divider"></li>
            <?php endforeach; ?>
          </ul>
        </li>

        <!-- Panel admin -->
        <?php if ($rolName && strtolower($rolName) === 'administrador'): ?>
          <li class="nav-item">
            <a class="nav-link text-warning" href="<?php echo BASE_URL . '?c=Admin&a=index'; ?>">
              <i class="fas fa-cog"></i> Panel Admin
            </a>
          </li>
        <?php endif; ?>
      </ul>

      <!-- ====== Buscador ====== -->
      <form method="GET" action="<?php echo BASE_URL; ?>?c=Producto&a=filtrar" class="d-flex me-3">
        <input type="hidden" name="c" value="Producto">
        <input type="hidden" name="a" value="filtrar">
        <input type="text" name="busqueda" class="form-control me-2" placeholder="Buscar..." 
               value="<?php echo htmlspecialchars($_GET['busqueda'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="btn btn-outline-light">Buscar</button>
      </form>

      <!-- ====== Iconos y usuario ====== -->
      <div class="d-flex">
        <!-- ❤️ Favoritos -->
        <button class="btn btn-outline-light me-2" onclick="accesoProtegido('Producto','megusta')">
          <i class="fas fa-heart"></i>
        </button>

        <!-- 🛒 Carrito -->
        <button class="btn btn-outline-light me-2" onclick="accesoProtegido('Carrito','carrito')">
          <i class="fas fa-shopping-cart"></i>
        </button>

        <!-- Usuario -->
        <?php if (isset($_SESSION['usuario'])): ?>
          <div class="dropdown">
            <a class="btn btn-light dropdown-toggle" href="#" data-bs-toggle="dropdown">
              <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['usuario']); ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Usuario&a=perfil'; ?>">Perfil</a></li>
              <?php if ($rolName && strtolower($rolName) === 'administrador'): ?>
                <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Admin&a=index'; ?>">Panel de Administración</a></li>
                <li><hr class="dropdown-divider"></li>
              <?php endif; ?>
              <li><a class="dropdown-item" href="<?php echo BASE_URL . '?c=Usuario&a=logout'; ?>">Cerrar sesión</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a href="<?php echo BASE_URL . '?c=Usuario&a=login'; ?>" class="btn btn-light">
            <i class="fas fa-user"></i> Iniciar Sesión
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function accesoProtegido(controlador, accion) {
  const logueado = <?= isset($_SESSION['usuario']) ? 'true' : 'false' ?>;
  if (!logueado) {
    Swal.fire({
      icon: 'warning',
      title: 'Acceso restringido',
      text: 'Debes iniciar sesión para acceder a esta función.',
      confirmButtonText: 'Iniciar sesión',
      confirmButtonColor: '#ff7675'
    }).then((r) => {
      if (r.isConfirmed) {
        window.location.href = '<?= BASE_URL ?>?c=Usuario&a=login';
      }
    });
  } else {
    window.location.href = `<?= BASE_URL ?>?c=${controlador}&a=${accion}`;
  }
}
</script>
</body>
</html>




