<?php
// =============================================
// 🔹 SOLUCIÓN MÁS SIMPLE: Prevenir cache completamente
// =============================================
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// =============================================
// 🔹 Iniciar sesión
// =============================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// 🔹 Si es una navegación hacia atrás, forzar recarga
// =============================================
if (isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0') {
    // Es una recarga, no hacer nada
} elseif (isset($_SERVER['HTTP_REFERER'])) {
    // Viene de otra página, verificar si necesitamos forzar actualización de UI
    if (isset($_SESSION['ID_Usuario']) && !isset($_GET['fresh'])) {
        // Redirigir a la misma página con parámetro fresh para forzar actualización
        $currentUrl = $_SERVER['REQUEST_URI'];
        if (strpos($currentUrl, '?') === false) {
            $currentUrl .= '?fresh=1';
        } else {
            $currentUrl .= '&fresh=1';
        }
        header("Location: " . $currentUrl);
        exit;
    }
}

// =============================================
// 🔹 Manejo de URLs amigables (si no hay parámetros GET)
// =============================================
if (!isset($_GET['c']) && !isset($_GET['a'])) {
    $requestUri = $_SERVER['REQUEST_URI'];
    $basePath = '/Tulook_MVC/';
    $path = str_replace($basePath, '', $requestUri);
    $path = trim($path, '/');
    
    if (empty($path) || $path == 'index.php') {
        $controlador = 'Producto';
        $accion = 'index';
    } else {
        $parts = explode('/', $path);
        $controlador = ucfirst($parts[0] ?? 'Producto');
        $accion = $parts[1] ?? 'index';
        
        if (count($parts) > 2) {
            for ($i = 2; $i < count($parts); $i += 2) {
                if (isset($parts[$i + 1])) {
                    $_GET[$parts[$i]] = $parts[$i + 1];
                }
            }
        }
    }
    
    $_GET['c'] = $controlador;
    $_GET['a'] = $accion;
}

// =============================================
// 🔹 URL base del proyecto
// =============================================
define("BASE_URL", "http://localhost/Tulook_MVC/");

// =============================================
// 🔹 Conexión a la BD
// =============================================
require_once "models/Database.php";
$database = new Database();
$db = $database->getConnection();

// =============================================
// 🔹 Controladores existentes
// =============================================
require_once "controllers/ProductoController.php";
require_once "controllers/CarritoController.php";
require_once "controllers/UsuarioController.php";
require_once "controllers/PedidoController.php";
require_once "controllers/AdminController.php";
require_once "controllers/FavoritoController.php";
require_once "controllers/TallasController.php";
require_once "controllers/PrecioController.php";
require_once "controllers/UsuarioAdminController.php";
require_once "controllers/DescuentoController.php";
require_once "controllers/FavoritoStatsController.php";
require_once "controllers/ChatbotController.php";
require_once "controllers/CheckoutController.php";
require_once "controllers/FacturaPDFController.php";
require_once "controllers/AtributoController.php";
require_once "controllers/ColorController.php";

// =============================================
// 🔹 Determinar controlador y acción por URL
// =============================================
$controlador = $_GET['c'] ?? 'Producto';
$accion      = $_GET['a'] ?? 'index';

// =============================================
// 🔹 Crear instancia del controlador correspondiente
// =============================================
switch ($controlador) {
    case 'Producto':
        $controller = new ProductoController($db);
        break;
    case 'Carrito':
        $controller = new CarritoController($db);
        break;
    case 'Usuario':
        $controller = new UsuarioController($db);
        break;
    case 'Pedido':
        $controller = new PedidoController($db);
        break;
    case 'Admin':
        $controller = new AdminController();
        break;
    case 'Favorito':
    case 'MeGusta':
        $controller = new FavoritoController($db);
        break;
    case 'Tallas':
        $controller = new TallasController($db);
        break;
    case 'Precio':
        $controller = new PrecioController($db);
        break;
    case 'Descuento':
        $controller = new DescuentoController($db);
        break;
    case 'UsuarioAdmin':
        $controller = new UsuarioAdminController($db);
        break;
    case 'FavoritoStats':
        $controller = new FavoritoStatsController($db);
        break;
    case 'Chatbot':
        $controller = new ChatbotController($db);
        break;
    case 'Checkout':
        $controller = new CheckoutController($db);
        break;
    case 'FacturaPDF':
        $controller = new FacturaPDFController($db);
        break;
    case 'Atributo': 
        $controller = new AtributoController($db);
        break;
    case 'Color':
        $controller = new ColorController($db);
        break;
    default:
        $controller = new ProductoController($db);
        break;
}

// =============================================
// 🔹 Verificar acción válida
// =============================================
if (!method_exists($controller, $accion)) {
    if ($controlador === 'Admin') {
        header("Location: " . BASE_URL . "?c=Admin&a=index");
        exit;
    } else {
        header("Location: " . BASE_URL);
        exit;
    }
}

// =============================================
// 🔹 Manejo global de errores
// =============================================
try {
    $controller->$accion();
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>⚠ Error interno del servidor</h2>";
    echo "<pre style='color:#333;text-align:center;font-size:14px;'>
" . $e->getMessage() . "
</pre>";
}