<?php
if (!isset($_SESSION)) session_start();

// Determinar el rol y tipo de usuario
$rol = $_SESSION['rol'] ?? null;
$rolName = $_SESSION['RolName'] ?? '';
$esAdministrador = ($rol == 1);
$esEditor = ($rol == 2);
$tipoUsuario = $esAdministrador ? 'Administrador' : ($esEditor ? 'Editor' : 'Usuario');

// Configurar colores según rol
$colorPrimario = $esAdministrador ? '#1B202D' : '#2A3448';
$colorSecundario = $esAdministrador ? '#2A3448' : '#3A4A6B'; 
$colorAcento = $esAdministrador ? '#3A4A6B' : '#4A5B7D';

// Obtener hora de inicio de sesión
$tiempoConectado = "0h 0m 0s";

// Si hay sesión activa, calcular tiempo desde login_time
if (isset($_SESSION['login_time']) && is_numeric($_SESSION['login_time'])) {
    $segundosTranscurridos = time() - $_SESSION['login_time'];
    
    $horas = floor($segundosTranscurridos / 3600);
    $minutos = floor(($segundosTranscurridos % 3600) / 60);
    $segundos = $segundosTranscurridos % 60;
    
    $tiempoConectado = sprintf('%dh %dm %ds', $horas, $minutos, $segundos);
}

// Configurar fecha y hora actual en formato legible
$fechaHoraActual = date('d/m/Y H:i:s');
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title><?php echo $tipoUsuario; ?> - TuLook</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <!-- Incluir CSS externo -->
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/layout_admin.css">
</head>
<body>
  <!-- Overlay para móviles -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- Botón para ocultar/mostrar sidebar -->
  <button id="sidebarToggle" class="btn" title="Ocultar/Mostrar menú">
    <i class="fas fa-chevron-left"></i>
    <i class="fas fa-bars"></i>
  </button>

  <div class="main-container">
    <!-- Sidebar fijo -->
    <div class="sidebar" id="mainSidebar">
      <div class="sidebar-content">
        <div class="sidebar-header">
          <div class="sidebar-title">
            TuLook
          </div>
          <div class="role-display <?php echo $esAdministrador ? 'admin' : 'editor'; ?>">
            <?php if ($esAdministrador): ?>
              <i class="fas fa-crown role-icon-admin"></i>
              ADMINISTRADOR
            <?php elseif ($esEditor): ?>
              <i class="fas fa-user-edit role-icon-editor"></i>
              EDITOR
            <?php endif; ?>
          </div>
        </div>
        
        <div class="sidebar-nav">
          <ul class="nav flex-column">
            
              <!--Gestión del Panel de Administración -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo (($_GET['c'] ?? 'Admin') === 'Admin' && ($_GET['a'] ?? 'index') === 'index') ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Admin&a=index">
                      <i class="fa fa-chart-pie me-2"></i> Panel de Administración
                  </a>
              </li>

              <!-- Gestión de Productos Base -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo (($_GET['c'] ?? '') === 'Admin' && ($_GET['a'] ?? '') === 'productos') ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Admin&a=productos">
                      <i class="fa fa-box-open me-2"></i> Productos Base
                  </a>
              </li>

              <!-- Gestión de Variantes -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo (($_GET['c'] ?? '') === 'Admin' && ($_GET['a'] ?? '') === 'variantes') ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Admin&a=variantes">
                      <i class="fa fa-palette me-2"></i> Variantes
                  </a>
              </li>

              <!-- Gestión de Atributos -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Atributo' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Atributo&a=index">
                      <i class="fas fa-list-alt me-2"></i> Gestión de Atributos
                  </a>
              </li>

              <!-- Gestión de Colores -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Color' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Color&a=index">
                      <i class="fas fa-palette me-2"></i> Gestión de Colores
                  </a>
              </li>

              <!-- Gestión de Precios -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Precio' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Precio&a=index">
                      <i class="fa fa-tags me-2"></i> Gestión de Precios
                  </a>
              </li>

              <!-- Estadísticas de Favoritos -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'FavoritoStats' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=FavoritoStats&a=index">
                      <i class="fa fa-heart me-2"></i> Estadísticas Favoritos
                  </a>
              </li>

              <!-- Gestión de Pedidos -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Pedido' && ($_GET['a'] ?? 'index') === 'index' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Pedido&a=index">
                      <i class="fas fa-shopping-cart me-2"></i> Gestión de Pedidos
                  </a>
              </li>

              <!-- Pedidos Enviados -->
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Pedido' && ($_GET['a'] ?? '') === 'enviados' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Pedido&a=enviados">
                      <i class="fas fa-truck me-2"></i> Pedidos Enviados
                  </a>
              </li>

              <!-- Gestión de Usuarios solo para Administradores -->
              <?php if ($esAdministrador): ?>
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'UsuarioAdmin' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=UsuarioAdmin&a=index">
                      <i class="fa fa-users me-2"></i> Gestión de Usuarios
                  </a>
              </li>

              <!-- Gestión de Descuentos solo para Administradores -->
              <li class="nav-item mb-1">
                <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Descuento' ? 'active' : ''; ?>" 
                  href="<?php echo BASE_URL; ?>?c=Descuento&a=index">
                    <i class="fas fa-tag me-2"></i> Descuentos
                </a>
              </li>

             <!-- Reportes de Ventas solo para Administradores --> 
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'ReporteVenta' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=ReporteVenta&a=index">
                      <i class="fas fa-chart-line me-2"></i> Reportes de Ventas
                  </a>
              </li>
              <?php endif; ?>

              <li class="nav-item my-2"> <!-- Reducido de my-3 -->
                  <hr style="border-color: rgba(255,255,255,0.1); margin: 6px 0;"> <!-- Reducido de 8px 0 -->
              </li>

              <li class="nav-item mb-2">
                  <a class="nav-link" href="<?php echo BASE_URL; ?>">
                      <i class="fa fa-home me-2"></i> Volver a la tienda
                  </a>
              </li>
          </ul>
        </div>
        
        <div class="sidebar-session-info mt-auto pt-3 border-top border-white-10">
          <div class="text-center text-white-50 small mb-3">
            <i class="fas fa-clock me-1"></i>
            <span id="sidebarSessionTime"><?php echo $tiempoConectado; ?></span>
          </div>
          
          <form method="POST" action="<?php echo BASE_URL; ?>?c=Usuario&a=logout">
              <button type="submit" class="btn btn-sm btn-danger w-100">
                  <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
              </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Área de contenido principal -->
    <div class="content-area" id="mainContentArea">
      <div class="topbar">
        <div class="topbar-left">
          <div class="user-info">
            <div class="user-role-badge <?php echo $esAdministrador ? 'admin' : 'editor'; ?>">
              <?php if ($esAdministrador): ?>
                <i class="fas fa-crown me-1"></i>
                ADMINISTRADOR
              <?php elseif ($esEditor): ?>
                <i class="fas fa-user-edit me-1"></i>
                EDITOR
              <?php endif; ?>
            </div>
            <div class="user-name">
              <?php echo htmlspecialchars($_SESSION['Nombre_Completo'] ?? ($_SESSION['usuario'] ?? 'Usuario')); ?>
            </div>
          </div>
        </div>
        
        <div class="topbar-right">
          <div class="session-info">
            <div class="session-time time-animation" id="sessionTimer">
              <i class="fas fa-clock"></i>
              <span id="sessionTimeDisplay"><?php echo $tiempoConectado; ?></span>
            </div>
            <div class="session-date">
              <i class="far fa-calendar me-1"></i>
              <span id="currentDateTime"><?php echo $fechaHoraActual; ?></span>
            </div>
          </div>
        </div>
      </div>
      
      <div class="main-content">
          <?php 
          $controller = $_GET['c'] ?? 'Admin';
          $action = $_GET['a'] ?? 'index';
          
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
                  'eliminar' => 'descuentos/index.php',
                  'estadisticas' => 'descuentos/estadisticas.php'
              ],
              'UsuarioAdmin' => [
                  'index' => 'usuario/index.php',
                  'crear' => 'usuario/form.php',
                  'guardar' => 'usuario/index.php',
                  'cambiarEstado' => 'usuario/index.php',
                  'cambiarRol' => 'usuario/index.php',
                  'solicitarMotivo' => 'usuario/motivo_desactivacion.php',
                  'solicitudesReactivacion' => 'usuario/solicitudes_reactivacion.php',
                  'verSolicitudReactivacion' => 'usuario/detalle_solicitud.php',
                  'procesarSolicitudReactivacion' => 'usuario/index.php'
              ],
              'Atributo' => [
                  'index' => 'atributo/index.php',
                  'crear' => 'atributo/form.php',
                  'editar' => 'atributo/form.php',
                  'detalle' => 'atributo/detalle.php',
                  'guardar' => 'atributo/index.php',
                  'actualizar' => 'atributo/index.php',
                  'cambiarEstado' => 'atributo/index.php'
              ],
              'Color' => [
                  'index' => 'colores/index.php',
                  'crear' => 'colores/crear.php',
                  'editar' => 'colores/editar.php',
                  'detalle' => 'colores/detalle.php',
                  'guardar' => 'colores/index.php',
                  'actualizar' => 'colores/index.php',
                  'eliminar' => 'colores/index.php',
                  'cambiarEstado' => 'colores/index.php',
              ],
              'Pedido' => [
                  'index' => 'pedidos/index.php',
                  'enviados' => 'pedidos/enviados.php',
                  'detalle' => 'pedidos/detalle.php',
                  'reporte' => 'pedidos/reporte.php',
                  'envioRapido' => 'pedidos/generar_envio.php'
              ],
              'FavoritoStats' => [ 
                  'index' => 'favoritos/index.php',
              ],
              'ReporteVenta' => [
                  'index' => 'reporte/index.php',
                  'detallado' => 'reporte/detallado.php',
                  'productos' => 'reporte/productos.php',
                  'clientes' => 'reporte/clientes.php',
                  'financiero' => 'reporte/financiero.php',
                  'exportar' => 'reporte/index.php' 
              ]
          ];
          
          $viewFile = "views/admin/";
          
          if (isset($viewMap[$controller][$action])) {
              $viewFile .= $viewMap[$controller][$action];
          } else {
              $viewFile .= $action . '.php';
          }
          
          if ($controller === 'UsuarioAdmin' && !$esAdministrador) {
              echo '<div class="alert alert-danger m-4">';
              echo '<h4><i class="fas fa-ban"></i> Acceso Denegado</h4>';
              echo '<p>No tienes permisos para acceder a la gestión de usuarios.</p>';
              echo '<p>Esta sección está reservada exclusivamente para administradores.</p>';
              echo '<a href="' . BASE_URL . '?c=Admin&a=index" class="btn btn-primary">Volver al Dashboard</a>';
              echo '</div>';
          } elseif ($controller === 'Descuento' && !$esAdministrador) {
              echo '<div class="alert alert-danger m-4">';
              echo '<h4><i class="fas fa-ban"></i> Acceso Denegado</h4>';
              echo '<p>No tienes permisos para acceder a la gestión de descuentos.</p>';
              echo '<p>Esta sección está reservada exclusivamente para administradores.</p>';
              echo '<a href="' . BASE_URL . '?c=Admin&a=index" class="btn btn-primary">Volver al Dashboard</a>';
              echo '</div>';
          } elseif (file_exists($viewFile)) {
              include $viewFile;
          } else {
              echo '<div class="alert alert-warning m-4">';
              echo '<h5><i class="fas fa-exclamation-triangle"></i> Vista no encontrada</h5>';
              echo '<p>El sistema intentó cargar: <code>' . htmlspecialchars($viewFile) . '</code></p>';
              echo '<p>Controlador: <code>' . htmlspecialchars($controller) . '</code></p>';
              echo '<p>Acción: <code>' . htmlspecialchars($action) . '</code></p>';
                  echo '<div class="mt-3">';
              echo '<a href="' . BASE_URL . '?c=Admin&a=index" class="btn btn-primary me-2">Ir al Panel de Administración</a>';
              echo '<a href="' . BASE_URL . '?c=Producto&a=index" class="btn btn-secondary">Ir a Productos</a>';
              echo '</div>';
              echo '</div>';
              
              if ($esAdministrador) {
                  echo '<div class="mt-3 p-3 bg-light rounded m-4">';
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
  <!-- Pasar variables PHP a JavaScript -->
  <script>
    // Variables PHP que necesitan estar disponibles en JavaScript
    window.LOGIN_TIME = <?php echo isset($_SESSION['login_time']) ? $_SESSION['login_time'] : 'null'; ?>;
  </script>
  <!-- Incluir JS externo -->
  <script src="<?php echo BASE_URL; ?>assets/js/layout_admin.js"></script>
</body>
</html>