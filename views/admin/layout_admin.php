<?php
// views/admin/layout_admin.php
if (!isset($_SESSION)) session_start();

// Determinar el rol y tipo de usuario
$rol = $_SESSION['rol'] ?? null;
$rolName = $_SESSION['RolName'] ?? '';
$esAdministrador = ($rol == 1);
$esEditor = ($rol == 2);
$tipoUsuario = $esAdministrador ? 'Administrador' : ($esEditor ? 'Editor' : 'Usuario');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?php echo $tipoUsuario; ?> - TuLook</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { 
      min-height: 100vh; 
      display: flex; 
      flex-direction: column;
    }
    
    .main-container {
      display: flex;
      flex: 1;
      min-height: 100vh;
    }
    
    .sidebar { 
      width: 250px; 
      background: #1f2937; 
      color: #fff; 
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      overflow-y: auto;
      z-index: 1000;
    }
    
    .sidebar-content {
      padding: 20px 15px;
      height: 100%;
      display: flex;
      flex-direction: column;
    }
    
    .sidebar-nav {
      flex: 1;
      overflow-y: auto;
    }
    
    .sidebar a { 
      color: #d1d5db; 
      text-decoration: none; 
    }
    
    .sidebar .nav-link.active { 
      background: #111827; 
      color: #fff; 
    }
    
    .content-area {
      flex: 1;
      margin-left: 250px;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }
    
    .topbar { 
      background: #fff; 
      padding: 15px 25px; 
      border-bottom: 1px solid #e5e7eb;
      position: sticky;
      top: 0;
      z-index: 999;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .main-content {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }
    
    .badge-admin { background: #dc3545; }
    .badge-editor { background: #fd7e14; }

    .alert-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    .alert-gradient-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border: none;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%) !important;
    }

    .hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .hover-lift:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    /* Scrollbar personalizado para sidebar */
    .sidebar-nav::-webkit-scrollbar {
      width: 6px;
    }
    
    .sidebar-nav::-webkit-scrollbar-track {
      background: #374151;
    }
    
    .sidebar-nav::-webkit-scrollbar-thumb {
      background: #6b7280;
      border-radius: 3px;
    }
    
    .sidebar-nav::-webkit-scrollbar-thumb:hover {
      background: #9ca3af;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
      }
      
      .content-area {
        margin-left: 0;
      }
      
      .sidebar-content {
        padding: 15px;
      }
    }
  </style>
</head>
<body>
  <div class="main-container">
    <!-- Sidebar fijo -->
    <div class="sidebar">
      <div class="sidebar-content">
        <div class="sidebar-header mb-4">
          <h4 class="text-white">
            TuLook 
            <?php if ($esAdministrador): ?>
              <span class="badge bg-danger">Admin</span>
            <?php elseif ($esEditor): ?>
              <span class="badge bg-warning text-dark">Editor</span>
            <?php endif; ?>
          </h4>
        </div>
        
        <hr style="border-color: rgba(255,255,255,0.08);">
        
        <div class="sidebar-nav">
          <ul class="nav flex-column">
              <!-- Dashboard -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo (($_GET['c'] ?? 'Admin') === 'Admin' && ($_GET['a'] ?? 'index') === 'index') ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Admin&a=index">
                      <i class="fa fa-chart-pie me-2"></i> Dashboard
                  </a>
              </li>

              <!-- Gestión de Productos (Admin y Editor) -->
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

              <!-- Estadísticas (Admin y Editor) -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'FavoritoStats' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=FavoritoStats&a=index">
                      <i class="fa fa-heart me-2"></i> Estadísticas Favoritos
                  </a>
              </li>

              <!-- Gestión de Tallas (Admin y Editor) -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Tallas' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Tallas&a=index">
                      <i class="fa fa-ruler me-2"></i> Gestión de Tallas
                  </a>
              </li>

              <!-- Gestión de Precios (Admin y Editor) -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Precio' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Precio&a=index">
                      <i class="fa fa-tags me-2"></i> Gestión de Precios
                  </a>
              </li>

              <!-- Gestión de Usuarios (SOLO Administrador) -->
              <?php if ($esAdministrador): ?>
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'UsuarioAdmin' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=UsuarioAdmin&a=index">
                      <i class="fa fa-users me-2"></i> Gestión de Usuarios
                  </a>
              </li>
              <li class="nav-item mb-1">
               <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Descuento' ? 'active' : ''; ?>" 
                href="<?php echo BASE_URL; ?>?c=Descuento&a=index">
                <i class="fas fa-tag me-2"></i> Descuentos
                </a>
              </li>
              <!-- Separador sutil SOLO para administradores -->
              <li class="nav-item my-2">
                  <hr style="border-color: rgba(255,255,255,0.08); margin: 8px 0;">
              </li>
              <?php else: ?>
              <!-- Separador sutil para editores (sin Gestión de Usuarios) -->
              <li class="nav-item my-2">
                  <hr style="border-color: rgba(255,255,255,0.08); margin: 8px 0;">
              </li>
              <?php endif; ?>

              <!-- Volver a la tienda -->
              <li class="nav-item mb-2">
                  <a class="nav-link" href="<?php echo BASE_URL; ?>">
                      <i class="fa fa-home me-2"></i> Volver a la tienda
                  </a>
              </li>
          </ul>
        </div>
        
        <!-- Cerrar sesión - BOTÓN ROJO -->
        <div class="sidebar-footer mt-auto pt-3">
          <form method="POST" action="<?php echo BASE_URL; ?>?c=Usuario&a=logout">
              <button type="submit" class="btn btn-sm btn-danger w-100">
                  <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
              </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Área de contenido principal -->
    <div class="content-area">
      <div class="topbar d-flex justify-content-between align-items-center">
        <div>
          <strong>
            <?php 
            if ($esAdministrador) {
                echo '<span class="badge bg-danger me-2">Administrador</span>';
            } elseif ($esEditor) {
                echo '<span class="badge bg-warning text-dark me-2">Editor</span>';
            }
            ?>
            <?php echo htmlspecialchars($_SESSION['Nombre_Completo'] ?? ($_SESSION['usuario'] ?? '')); ?>
          </strong>
        </div>
        <div>
          <small class="text-muted">
            Usuario ID: <?php echo htmlspecialchars($_SESSION['ID_Usuario'] ?? '-'); ?> 
            | Rol: <?php echo htmlspecialchars($rolName); ?>
          </small>
        </div>
      </div>
      
      <div class="main-content">
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
                  'variantes' => 'variantes.php',
                  'buscarProductos' => 'productos.php',
                  'buscarVariantes' => 'variantes.php'
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
              ],
              'FavoritoStats' => [ 
                  'index' => 'favoritos/index.php',
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
          
          // Verificar permisos específicos para Gestión de Usuarios
          if ($controller === 'UsuarioAdmin' && !$esAdministrador) {
              echo '<div class="alert alert-danger">';
              echo '<h4><i class="fas fa-ban"></i> Acceso Denegado</h4>';
              echo '<p>No tienes permisos para acceder a la gestión de usuarios.</p>';
              echo '<p>Esta sección está reservada exclusivamente para administradores.</p>';
              echo '<a href="' . BASE_URL . '?c=Admin&a=index" class="btn btn-primary">Volver al Dashboard</a>';
              echo '</div>';
          } elseif (file_exists($viewFile)) {
              include $viewFile;
          } else {
              echo '<div class="alert alert-warning">';
              echo '<h5><i class="fas fa-exclamation-triangle"></i> Vista no encontrada</h5>';
              echo '<p>El sistema intentó cargar: <code>' . htmlspecialchars($viewFile) . '</code></p>';
              echo '<p>Controlador: <code>' . htmlspecialchars($controller) . '</code></p>';
              echo '<p>Acción: <code>' . htmlspecialchars($action) . '</code></p>';
              echo '<div class="mt-3">';
              echo '<a href="' . BASE_URL . '?c=Admin&a=index" class="btn btn-primary me-2">Ir al Dashboard</a>';
              echo '<a href="' . BASE_URL . '?c=Producto&a=index" class="btn btn-secondary">Ir a Productos</a>';
              echo '</div>';
              echo '</div>';
              
              // Debug información (solo para administradores)
              if ($esAdministrador) {
                  echo '<div class="mt-3 p-3 bg-light rounded">';
                  echo '<h6>Información de Debug (Solo Admin):</h6>';
                  echo '<pre>GET: ' . print_r($_GET, true) . '</pre>';
                  echo '<pre>View Map: ' . print_r($viewMap, true) . '</pre>';
                  echo '</div>';
              }
          }
          ?>
      </div>
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

      // Mostrar mensajes de sesión
      setTimeout(function() {
          const alerts = document.querySelectorAll('.alert');
          alerts.forEach(function(alert) {
              const bsAlert = new bootstrap.Alert(alert);
              setTimeout(() => bsAlert.close(), 5000);
          });
      }, 3000);
  });
  </script>
</body>
</html>