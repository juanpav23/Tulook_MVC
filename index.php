<?php
session_start();

// 🔹 URL base del proyecto
define("BASE_URL", "http://localhost/Tulook_MVC/");

// 🔹 Conexión a la BD
require_once "models/Database.php";
$database = new Database();
$db = $database->getConnection();

// 🔹 Controladores
require_once "controllers/ProductoController.php";
require_once "controllers/CarritoController.php";
require_once "controllers/UsuarioController.php";
require_once "controllers/PedidoController.php";
require_once "controllers/AdminController.php";

// 🔹 Determinar controlador y acción por URL (GET)
$controlador = $_GET['c'] ?? 'Producto';
$accion      = $_GET['a'] ?? 'index';

// 🔹 Crear instancia del controlador correspondiente
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
        $controller = new AdminController(); // Admin no usa conexión
        break;
    default:
        $controller = new ProductoController($db);
        break;
}

// 🔹 Verificar que la acción exista, si no, redirigir al index
if (!method_exists($controller, $accion)) {
    $accion = 'index';
}

// 🔹 Manejo de errores globales
try {
    $controller->$accion();
} catch (Throwable $e) {
    http_response_code(500);
    echo "<h2 style='color:red;text-align:center;margin-top:50px;'>⚠️ Error interno del servidor</h2>";
    echo "<p style='text-align:center;'>".$e->getMessage()."</p>";
}




