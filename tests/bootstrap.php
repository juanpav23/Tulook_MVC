<?php
// tests/bootstrap.php

// Verificar si las constantes ya están definidas antes de definirlas
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/Tulook_MVC/');
}

// Configurar el autoloader de Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Incluir archivos necesarios
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../models/Producto.php';
require_once __DIR__ . '/../models/Favorito.php';
require_once __DIR__ . '/../controllers/AdminController.php';

// Configurar entorno de pruebas
error_reporting(E_ALL);
ini_set('display_errors', '1');

// NO iniciar sesión aquí - lo haremos en los tests individuales cuando sea necesario