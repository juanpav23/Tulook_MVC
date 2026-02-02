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
  <style>
    :root {
        --primary-dark: <?php echo $colorPrimario; ?>;
        --primary-blue: <?php echo $colorSecundario; ?>;
        --accent-blue: <?php echo $colorAcento; ?>;
        --secondary-blue: #4A5B7D;
        --light-blue: #5B6E8F;
        --light-bg: #f5f7fa;
        --text-dark: #2c3e50;
        --text-light: #6c757d;
        --border-radius: 12px;
        --card-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        --transition: all 0.3s ease;
        --admin-gradient: linear-gradient(135deg, #1B202D 0%, #2A3448 100%);
        --editor-gradient: linear-gradient(135deg, #2A3448 0%, #3A4A6B 100%);
    }

    * {
        box-sizing: border-box;
    }

    body { 
      min-height: 100vh; 
      display: flex; 
      flex-direction: column;
      background-color: #f8f9fa;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
      min-width: 280px;
    }
    
    .main-container {
      display: flex;
      flex: 1;
      min-height: 100vh;
      width: 100%;
      position: relative;
    }
    
    .sidebar { 
      width: 270px;
      background: var(--primary-dark); 
      color: #fff; 
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      overflow-y: auto;
      z-index: 1000;
      background: <?php echo $esAdministrador ? 'linear-gradient(135deg, #1B202D 0%, #2A3448 100%)' : 'linear-gradient(135deg, #2A3448 0%, #3A4A6B 100%)'; ?>;
      box-shadow: 3px 0 15px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, width 0.3s ease;
    }
    
    .sidebar-content {
      padding: 25px 20px;
      height: 100%;
      display: flex;
      flex-direction: column;
      width: 270px;
    }
    
    .sidebar-nav {
      flex: 1;
      overflow-y: auto;
      padding-right: 10px;
      margin-top: 20px;
    }
    
    .sidebar a { 
      color: #d1d5db; 
      text-decoration: none; 
      transition: var(--transition);
      font-size: 0.95rem;
      padding: 10px 15px;
      border-radius: 8px;
      margin-bottom: 5px;
      display: block;
      white-space: nowrap;
    }
    
    .sidebar a:hover { 
      color: #fff; 
      background: rgba(255, 255, 255, 0.1);
      transform: translateX(5px);
    }
    
    .sidebar .nav-link.active { 
      background: rgba(255, 255, 255, 0.15); 
      color: #fff;
      border-left: 4px solid <?php echo $esAdministrador ? '#ffffff' : '#f8f9fa'; ?>;
      font-weight: 500;
    }
    
    .content-area {
      flex: 1;
      margin-left: 270px;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: #f8f9fa;
      transition: margin-left 0.3s ease, width 0.3s ease;
      width: calc(100% - 270px);
    }
    
    .topbar { 
      background: <?php echo $esAdministrador ? '#1B202D' : '#2A3448'; ?>;
      color: white;
      padding: 12px 20px;
      position: sticky;
      top: 0;
      z-index: 999;
      box-shadow: 0 2px 15px rgba(0,0,0,0.1);
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 3px solid rgba(255,255,255,0.1);
      min-height: 70px;
      transition: all 0.3s ease;
      flex-wrap: nowrap;
      gap: 15px;
    }
    
    .topbar-left {
      display: flex;
      align-items: center;
      gap: 15px;
      flex: 1;
      min-width: 0;
      overflow: hidden;
    }
    
    .topbar-right {
      display: flex;
      align-items: center;
      gap: 15px;
      flex-shrink: 0;
      min-width: 0;
    }
    
    .user-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      min-width: 0;
      flex: 1;
    }
    
    .user-role-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 12px;
      border-radius: 16px;
      font-size: 0.8rem;
      font-weight: 600;
      width: fit-content;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }
    
    .user-role-badge.admin {
      background: linear-gradient(135deg, #2A3448, #3A4A6B);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .user-role-badge.editor {
      background: linear-gradient(135deg, #3A4A6B, #4A5B7D);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .session-info {
      display: flex;
      flex-direction: column;
      gap: 4px;
      text-align: right;
      min-width: 0;
    }
    
    .session-time {
      background: <?php echo $esAdministrador ? 'linear-gradient(135deg, #3A4A6B, #4A5B7D)' : 'linear-gradient(135deg, #4A5B7D, #5B6E8F)'; ?>;
      color: white;
      padding: 5px 12px;
      border-radius: 16px;
      font-size: 0.8rem;
      font-weight: 500;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      width: fit-content;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .session-date {
      font-size: 0.75rem;
      color: rgba(255,255,255,0.8);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .main-content {
      flex: 1;
      padding: 0;
      overflow-y: auto;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      min-height: calc(100vh - 70px);
    }

    /* Botón de toggle sidebar - MEJORADO Y ADAPTABLE */
    #sidebarToggle {
      background: <?php echo $esAdministrador ? '#2A3448' : '#3A4A6B'; ?>;
      border: 2px solid white;
      border-radius: 8px;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      color: white;
      font-size: 1.1rem;
      position: fixed;
      left: 245px;
      top: 75px;
      z-index: 1100;
      transition: all 0.3s ease;
      flex-shrink: 0;
    }

    #sidebarToggle:hover {
      background: <?php echo $esAdministrador ? '#3A4A6B' : '#4A5B7D'; ?>;
      transform: scale(1.05);
    }

    /* Sidebar collapsed state */
    .sidebar.collapsed {
      transform: translateX(-100%);
      width: 0;
      overflow: hidden;
    }

    .content-area.expanded {
      margin-left: 0 !important;
      width: 100% !important;
    }

    #sidebarToggle.collapsed {
      left: 20px;
    }

    #sidebarToggle .fa-chevron-left {
      display: block;
    }

    #sidebarToggle .fa-bars {
      display: none;
    }

    #sidebarToggle.collapsed .fa-chevron-left {
      display: none;
    }

    #sidebarToggle.collapsed .fa-bars {
      display: block;
    }

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

    /* Iconos específicos por rol */
    .role-icon-admin {
        color: #ffffff;
        text-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
    }
    
    .role-icon-editor {
        color: #a3beff;
    }

    /* Scrollbar personalizado para sidebar */
    .sidebar-nav::-webkit-scrollbar {
      width: 6px;
    }
    
    .sidebar-nav::-webkit-scrollbar-track {
      background: rgba(255, 255, 255, 0.05);
    }
    
    .sidebar-nav::-webkit-scrollbar-thumb {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 3px;
    }
    
    .sidebar-nav::-webkit-scrollbar-thumb:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    /* Estilos para el header del sidebar */
    .sidebar-header {
      text-align: center;
      padding-bottom: 25px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      margin-bottom: 15px;
    }
    
    .sidebar-title {
      font-family: 'Montserrat', sans-serif;
      font-weight: 800;
      font-size: 1.5rem;
      margin-bottom: 15px;
      letter-spacing: 0.5px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .role-display {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 6px 16px;
      border-radius: 16px;
      font-size: 0.8rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      white-space: nowrap;
    }
    
    .role-display.admin {
      background: rgba(255, 255, 255, 0.15);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .role-display.editor {
      background: rgba(163, 190, 255, 0.2);
      color: #a3beff;
      border: 1px solid rgba(163, 190, 255, 0.3);
    }
    
    /* Animación para el tiempo conectado */
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.8; }
      100% { opacity: 1; }
    }
    
    .time-animation {
      animation: pulse 2s infinite;
    }

    /* Nombre del usuario en topbar */
    .user-name {
      font-size: 1rem;
      font-weight: 600;
      color: white;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 200px;
    }

    .user-details {
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.7);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    /* Overlay para móviles */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 999;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .sidebar-overlay.active {
      display: block;
      opacity: 1;
    }

    /* Responsive */
    @media (max-width: 1200px) {
      .user-name {
        max-width: 180px;
      }
      
      .user-role-badge, .session-time {
        font-size: 0.75rem;
      }
    }
    
    @media (max-width: 992px) {
      .sidebar {
        width: 280px;
        transform: translateX(-100%);
        z-index: 1000;
        box-shadow: 5px 0 25px rgba(0,0,0,0.2);
      }
      
      .sidebar.active {
        transform: translateX(0);
      }
      
      .sidebar.collapsed {
        transform: translateX(-100%);
      }
      
      .sidebar-content {
        width: 280px;
      }
      
      .content-area {
        margin-left: 0;
        width: 100%;
      }
      
      .topbar {
        padding: 10px 15px;
        min-height: 65px;
        flex-wrap: wrap;
        gap: 10px;
      }
      
      .topbar-left {
        order: 1;
        width: 100%;
        justify-content: flex-start;
      }
      
      .topbar-right {
        order: 2;
        width: 100%;
        justify-content: space-between;
      }
      
      #sidebarToggle {
        position: static;
        order: -1;
        margin-right: 10px;
      }
      
      .user-name {
        font-size: 0.95rem;
        max-width: 160px;
      }
      
      .user-role-badge, .session-time {
        font-size: 0.75rem;
        padding: 4px 10px;
      }
      
      .session-date {
        font-size: 0.7rem;
      }
      
      .main-content {
        min-height: calc(100vh - 120px);
      }
    }
    
    @media (max-width: 768px) {
      .sidebar {
        width: 260px;
      }
      
      .sidebar-content {
        width: 260px;
        padding: 20px 15px;
      }
      
      .topbar {
        padding: 8px 12px;
        min-height: 60px;
      }
      
      .user-name {
        font-size: 0.9rem;
        max-width: 140px;
      }
      
      .user-role-badge, .session-time {
        font-size: 0.7rem;
        padding: 3px 8px;
      }
      
      .session-date {
        font-size: 0.65rem;
      }
      
      #sidebarToggle {
        width: 36px;
        height: 36px;
        font-size: 1rem;
      }
      
      .main-content {
        min-height: calc(100vh - 115px);
      }
      
      .sidebar-title {
        font-size: 1.3rem;
      }
      
      .role-display {
        font-size: 0.75rem;
        padding: 5px 14px;
      }
    }
    
    @media (max-width: 576px) {
      .topbar-left, .topbar-right {
        flex-wrap: wrap;
      }
      
      .user-info {
        flex: 1;
        min-width: 0;
      }
      
      .session-info {
        flex-direction: row;
        align-items: center;
        gap: 8px;
      }
      
      .user-name {
        font-size: 0.85rem;
        max-width: 120px;
      }
      
      .user-role-badge, .session-time {
        font-size: 0.65rem;
        padding: 2px 6px;
      }
      
      .session-date {
        font-size: 0.6rem;
      }
      
      #sidebarToggle {
        width: 34px;
        height: 34px;
        font-size: 0.9rem;
      }
      
      .sidebar {
        width: 250px;
      }
      
      .sidebar-content {
        width: 250px;
        padding: 15px;
      }
      
      .sidebar-title {
        font-size: 1.2rem;
      }
      
      .role-display {
        font-size: 0.7rem;
        padding: 4px 12px;
      }
    }
    
    @media (max-width: 400px) {
      .topbar {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
      }
      
      .topbar-left {
        order: 1;
      }
      
      .topbar-right {
        order: 2;
      }
      
      .user-name {
        max-width: 100%;
      }
      
      .session-info {
        flex-wrap: wrap;
        justify-content: space-between;
      }
      
      .user-role-badge, .session-time {
        font-size: 0.6rem;
        padding: 2px 5px;
      }
    }
    
    /* Ajustes para zoom alto */
    @media (min-resolution: 144dpi) {
      .user-name {
        font-size: 0.9rem;
      }
      
      .user-role-badge, .session-time {
        font-size: 0.75rem;
      }
    }
  </style>
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
              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo (($_GET['c'] ?? 'Admin') === 'Admin' && ($_GET['a'] ?? 'index') === 'index') ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Admin&a=index">
                      <i class="fa fa-chart-pie me-2"></i> Panel de Administración
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
                      <i class="fa fa-palette me-2"></i> Variantes
                  </a>
              </li>

              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Atributo' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Atributo&a=index">
                      <i class="fas fa-list-alt me-2"></i> Gestión de Atributos
                  </a>
              </li>

              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Color' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Color&a=index">
                      <i class="fas fa-palette me-2"></i> Gestión de Colores
                  </a>
              </li>

              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Precio' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Precio&a=index">
                      <i class="fa fa-tags me-2"></i> Gestión de Precios
                  </a>
              </li>

              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'FavoritoStats' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=FavoritoStats&a=index">
                      <i class="fa fa-heart me-2"></i> Estadísticas Favoritos
                  </a>
              </li>

              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Pedido' && ($_GET['a'] ?? 'index') === 'index' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Pedido&a=index">
                      <i class="fas fa-shopping-cart me-2"></i> Gestión de Pedidos
                  </a>
              </li>

              <li class="nav-item mb-1">
                  <a class="nav-link <?php echo ($_GET['c'] ?? '') === 'Pedido' && ($_GET['a'] ?? '') === 'enviados' ? 'active' : ''; ?>" 
                    href="<?php echo BASE_URL; ?>?c=Pedido&a=enviados">
                      <i class="fas fa-truck me-2"></i> Pedidos Enviados
                  </a>
              </li>

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
              <?php endif; ?>

              <li class="nav-item my-3">
                  <hr style="border-color: rgba(255,255,255,0.1); margin: 8px 0;">
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
                  'solicitarMotivo' => 'usuario/motivo_desactivacion.php' 
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
  
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      console.log('Admin layout cargado - Bootstrap listo');
      
      // Elementos del DOM
      const sidebarToggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('mainSidebar');
      const contentArea = document.getElementById('mainContentArea');
      const sidebarOverlay = document.getElementById('sidebarOverlay');
      const isDesktop = window.innerWidth >= 992;
      
      // Estado inicial
      let sidebarCollapsed = false;
      
      // Función para alternar sidebar
      function toggleSidebar() {
          sidebarCollapsed = !sidebarCollapsed;
          updateSidebarState();
      }
      
      // Función para actualizar el estado visual
      function updateSidebarState() {
          if (isDesktop) {
              // Modo desktop - colapsar/expandir
              if (sidebarCollapsed) {
                  sidebar.classList.add('collapsed');
                  contentArea.classList.add('expanded');
                  sidebarToggle.classList.add('collapsed');
                  sidebarToggle.title = "Mostrar menú";
                  sidebarOverlay.classList.remove('active');
              } else {
                  sidebar.classList.remove('collapsed');
                  contentArea.classList.remove('expanded');
                  sidebarToggle.classList.remove('collapsed');
                  sidebarToggle.title = "Ocultar menú";
                  sidebarOverlay.classList.remove('active');
              }
          } else {
              // Modo móvil - mostrar/ocultar con overlay
              if (sidebarCollapsed) {
                  sidebar.classList.remove('collapsed');
                  sidebar.classList.add('active');
                  sidebarOverlay.classList.add('active');
                  sidebarToggle.title = "Cerrar menú";
              } else {
                  sidebar.classList.remove('active');
                  sidebar.classList.add('collapsed');
                  sidebarOverlay.classList.remove('active');
                  sidebarToggle.title = "Abrir menú";
              }
          }
          
          // Guardar estado en localStorage
          localStorage.setItem('sidebarCollapsed', sidebarCollapsed);
      }
      
      // Función para cerrar sidebar en móviles
      function closeMobileSidebar() {
          if (!isDesktop) {
              sidebarCollapsed = false;
              updateSidebarState();
          }
      }
      
      // Event listeners
      if (sidebarToggle) {
          sidebarToggle.addEventListener('click', function(e) {
              e.stopPropagation();
              toggleSidebar();
          });
      }
      
      if (sidebarOverlay) {
          sidebarOverlay.addEventListener('click', function(e) {
              e.stopPropagation();
              closeMobileSidebar();
          });
      }
      
      // Cerrar sidebar al hacer clic en un enlace (solo móviles)
      if (!isDesktop) {
          document.querySelectorAll('.sidebar a').forEach(link => {
              link.addEventListener('click', closeMobileSidebar);
          });
      }
      
      // Cargar estado guardado
      const savedState = localStorage.getItem('sidebarCollapsed');
      if (savedState !== null) {
          sidebarCollapsed = savedState === 'true';
      } else {
          // Por defecto, en móviles el sidebar está cerrado
          sidebarCollapsed = !isDesktop;
      }
      
      // Inicializar estado
      updateSidebarState();
      
      // Manejar redimensionamiento
      let resizeTimer;
      window.addEventListener('resize', function() {
          clearTimeout(resizeTimer);
          resizeTimer = setTimeout(function() {
              const newIsDesktop = window.innerWidth >= 992;
              
              if (newIsDesktop !== isDesktop) {
                  // Recargar la página para evitar problemas
                  location.reload();
              }
          }, 150);
      });
      
      // Función para actualizar el tiempo conectado
      function actualizarTiempoConectado() {
        const loginTime = <?php echo $_SESSION['login_time'] ?? 'null'; ?>;
        
        if (!loginTime) return;
        
        const ahora = Math.floor(Date.now() / 1000);
        const diffSec = ahora - loginTime;
        
        const horas = Math.floor(diffSec / 3600);
        const minutos = Math.floor((diffSec % 3600) / 60);
        const segundos = diffSec % 60;
        
        const tiempoFormateado = `${horas}h ${minutos}m ${segundos}s`;
        
        const sessionTimeDisplay = document.getElementById('sessionTimeDisplay');
        const sidebarSessionTime = document.getElementById('sidebarSessionTime');
        
        if (sessionTimeDisplay) sessionTimeDisplay.textContent = tiempoFormateado;
        if (sidebarSessionTime) sidebarSessionTime.textContent = tiempoFormateado;
      }
      
      // Función para actualizar fecha y hora actual
      function actualizarFechaHora() {
          const ahora = new Date();
          const opciones = { 
              day: '2-digit', 
              month: '2-digit', 
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
              hour12: false
          };
          
          const fechaHoraFormateada = ahora.toLocaleDateString('es-ES', opciones);
          const currentDateTime = document.getElementById('currentDateTime');
          
          if (currentDateTime) {
              currentDateTime.textContent = fechaHoraFormateada;
          }
      }
      
      // Actualizar cada segundo
      setInterval(actualizarTiempoConectado, 1000);
      setInterval(actualizarFechaHora, 1000);
      
      // Inicializar
      actualizarTiempoConectado();
      actualizarFechaHora();
      
      // Ajustar dinámicamente el topbar
      function adjustTopbar() {
          const topbar = document.querySelector('.topbar');
          const topbarLeft = document.querySelector('.topbar-left');
          const topbarRight = document.querySelector('.topbar-right');
          
          if (!topbar || !topbarLeft || !topbarRight) return;
          
          const availableWidth = topbar.offsetWidth - 40; // Restar padding
          const leftWidth = topbarLeft.offsetWidth;
          const rightWidth = topbarRight.offsetWidth;
          
          // Si el contenido es demasiado ancho, reducir tamaños
          if (leftWidth + rightWidth > availableWidth) {
              document.querySelector('.user-name').style.fontSize = '0.85rem';
              document.querySelectorAll('.user-role-badge, .session-time').forEach(el => {
                  el.style.fontSize = '0.7rem';
                  el.style.padding = '3px 8px';
              });
              document.querySelector('.session-date').style.fontSize = '0.65rem';
          }
      }
      
      // Ajustar al cargar y al redimensionar
      adjustTopbar();
      window.addEventListener('resize', adjustTopbar);
      
      // Efecto de pulso para el timer
      setInterval(function() {
          const timerElement = document.getElementById('sessionTimer');
          if (timerElement) {
              timerElement.classList.remove('time-animation');
              void timerElement.offsetWidth;
              timerElement.classList.add('time-animation');
          }
      }, 60000);
  });
  </script>
</body>
</html>