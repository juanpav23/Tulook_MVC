<?php
// views/admin/layout_admin.php
if (!isset($_SESSION)) session_start();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Admin - TuLook</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { min-height:100vh; display:flex; }
    .sidebar { width:250px; background:#1f2937; color:#fff; min-height:100vh; }
    .sidebar a { color:#d1d5db; text-decoration:none; }
    .sidebar .nav-link.active { background: #111827; color:#fff; }
    .content { flex:1; padding:20px; }
    .topbar { background:#fff; padding:10px 20px; border-bottom:1px solid #e5e7eb; }
  </style>
</head>
<body>
  <div class="sidebar p-3">
    <h4 class="text-white">TuLook Admin</h4>
    <hr style="border-color: rgba(255,255,255,0.08);">
    <ul class="nav flex-column">
        <li class="nav-item mb-1">
            <a class="nav-link <?php echo (($_GET['c'] ?? 'Admin') === 'Admin' && ($_GET['a'] ?? 'index') === 'index') ? 'active' : ''; ?>" 
              href="<?php echo BASE_URL; ?>?c=Admin&a=index">
                <i class="fa fa-chart-pie me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link <?php echo (($_GET['c'] ?? '') === 'Admin' && ($_GET['a'] ?? '') === 'productos') ? 'active' : ''; ?>" 
              href="<?php echo BASE_URL; ?>?c=Admin&a=productos">
                <i class="fa fa-box-open me-2"></i> Productos Base
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link <?php echo (($_GET['c'] ?? '') === 'Admin' && ($_GET['a'] ?? '') === 'variantes') ? 'active' : ''; ?>" 
              href="<?php echo BASE_URL; ?>?c=Admin&a=variantes">
                <i class="fa fa-palette me-2"></i> Variantes / Imágenes
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Tallas' ? 'active' : ''; ?>" 
              href="<?php echo BASE_URL; ?>?c=Tallas&a=index">
                <i class="fa fa-ruler me-2"></i> Gestión de Tallas
            </a>
        </li>
 <li class="nav-item mb-1">
    <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Descuento' ? 'active' : ''; ?>" 
      href="<?php echo BASE_URL; ?>?c=Descuento&a=index">
        <i class="fas fa-tag me-2"></i> Descuentos
    </a>
</li>
        <li class="nav-item mb-1">
            <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Precio' ? 'active' : ''; ?>" 
              href="<?php echo BASE_URL; ?>?c=Precio&a=index">
                <i class="fa fa-tags me-2"></i> Gestión de Precios
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'UsuarioAdmin' ? 'active' : ''; ?>" 
              href="<?php echo BASE_URL; ?>?c=UsuarioAdmin&a=index">
                <i class="fa fa-users me-2"></i> Gestión de Usuarios
            </a>
        </li>
        <li class="nav-item mb-1">
            <a class="nav-link" href="<?php echo BASE_URL; ?>">
                <i class="fa fa-home me-2"></i> Volver a la tienda
            </a>
        <li class="nav-item mt-3">
    <form method="POST" action="<?php echo BASE_URL; ?>?c=Usuario&a=logout">
        <button type="submit" class="btn btn-sm btn-danger w-100">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </button>
    </form>
</li>
    </ul>
  </div>

  <div class="content">
    <div class="topbar d-flex justify-content-between align-items-center">
      <div>
        <strong>Administrador</strong> - <?php echo htmlspecialchars($_SESSION['Nombre_Completo'] ?? ($_SESSION['usuario'] ?? '')); ?>
      </div>
      <div>
        <small>Usuario ID: <?php echo htmlspecialchars($_SESSION['ID_Usuario'] ?? '-'); ?></small>
      </div>
    </div>
    <div class="p-3">
        <?php 
        $controller = $_GET['c'] ?? 'Admin';
        $action = $_GET['a'] ?? 'index';

        // Mapeo de vistas por controlador y acción
$viewMap = [
    'Admin' => [
        'index' => 'dashboard.php',
        'productos' => 'productos.php', 
        'productoForm' => 'producto_form.php',
        'detalleProducto' => 'detalle_producto.php',
        'saveProducto' => 'productos.php',
        'agregarVariante' => 'detalle_producto.php',
        'editarVariante' => 'detalle_producto.php',
        'eliminarVariante' => 'detalle_producto.php',
        'variantes' => 'variantes.php'
    ],
    'Tallas' => [
        'index' => 'talla/index.php',
        'crear' => 'talla/form.php',
        'editar' => 'talla/form.php',
        'guardar' => 'talla/index.php',
        'actualizar' => 'talla/index.php',
        'cambiarEstado' => 'talla/index.php'
    ],
    'Precio' => [
        'index' => 'precio/index.php',
        'crear' => 'precio/form.php',
        'editar' => 'precio/form.php',
        'guardar' => 'precio/index.php',
        'actualizar' => 'precio/index.php',
        'cambiarEstado' => 'precio/index.php',
        'limpiarDuplicados' => 'precio/index.php',
        'duplicados' => 'precio/duplicados.php'
    ],
    'Descuento' => [
        'index' => 'descuentos/index.php',
        'crear' => 'descuentos/crear.php',
        'editar' => 'descuentos/editar.php',
        'ver' => 'descuentos/ver.php',
        'guardar' => 'descuentos/index.php',
        'actualizar' => 'descuentos/index.php',
        'eliminar' => 'descuentos/index.php'
    ],
    'UsuarioAdmin' => [
        'index' => 'usuario/index.php',
        'crear' => 'usuario/form.php',
        'guardar' => 'usuario/index.php',
        'cambiarEstado' => 'usuario/index.php',
        'cambiarRol' => 'usuario/index.php'
    ]
];
        // Determinar el archivo de vista
        $viewFile = "views/admin/";
        
        if (isset($viewMap[$controller][$action])) {
            $viewFile .= $viewMap[$controller][$action];
        } else {
            // Intento alternativo: buscar por acción solamente
            $viewFile .= $action . '.php';
        }
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            echo '<div class="alert alert-warning">';
            echo '<h5>Vista no encontrada</h5>';
            echo '<p>El sistema intentó cargar: <code>' . htmlspecialchars($viewFile) . '</code></p>';
            echo '<p>Controlador: <code>' . htmlspecialchars($controller) . '</code></p>';
            echo '<p>Acción: <code>' . htmlspecialchars($action) . '</code></p>';
            echo '<a href="' . BASE_URL . '?c=Admin&a=index" class="btn btn-primary me-2">Ir al Dashboard</a>';
            echo '<a href="' . BASE_URL . '?c=Precio&a=index" class="btn btn-secondary">Ir a Precios</a>';
            echo '</div>';
            
            // Debug información
            echo '<div class="mt-3 p-3 bg-light rounded">';
            echo '<h6>Información de Debug:</h6>';
            echo '<pre>GET: ' . print_r($_GET, true) . '</pre>';
            echo '<pre>View Map: ' . print_r($viewMap, true) . '</pre>';
            echo '</div>';
        }
        ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      console.log('Admin layout cargado - Bootstrap listo');
      
      function abrirModalEditar(button) {
          console.log('Abriendo modal de edición...');
          
          const idProducto = button.getAttribute('data-id');
          const idColor = button.getAttribute('data-color');
          const idTalla = button.getAttribute('data-talla');
          const porcentaje = button.getAttribute('data-porcentaje');
          const cantidad = button.getAttribute('data-cantidad');
          const foto = button.getAttribute('data-foto');
          
          if (document.getElementById('edit_ID_Producto')) {
              document.getElementById('edit_ID_Producto').value = idProducto;
          }
          if (document.getElementById('edit_ID_Color')) {
              document.getElementById('edit_ID_Color').value = idColor;
          }
          if (document.getElementById('edit_ID_Talla')) {
              document.getElementById('edit_ID_Talla').value = idTalla;
          }
          if (document.getElementById('edit_Porcentaje')) {
              document.getElementById('edit_Porcentaje').value = porcentaje;
          }
          if (document.getElementById('edit_Cantidad')) {
              document.getElementById('edit_Cantidad').value = cantidad;
          }
          if (document.getElementById('edit_Foto')) {
              document.getElementById('edit_Foto').value = foto || '';
          }
          
          const modalElement = document.getElementById('modalEditarVariante');
          if (modalElement) {
              const modal = new bootstrap.Modal(modalElement);
              modal.show();
          }
      }
      
      window.abrirModalEditar = abrirModalEditar;
  });
  </script>
</body>
</html>