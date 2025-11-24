<?php
// debug_pdf_problem.php
define('DB_DSN', 'mysql:host=localhost;dbname=tulook;charset=utf8');
define('DB_USER', 'root');
define('DB_PASS', '');

try {
    $db = new PDO(DB_DSN, DB_USER, DB_PASS);
    echo "✅ Conexión a BD exitosa\n\n";
} catch (PDOException $e) {
    die("❌ Error conectando a BD: " . $e->getMessage());
}

$id_factura = 133; // Cambia por tu factura actual

echo "🔍 DEBUG DEL PROBLEMA DEL PDF - Factura #$id_factura\n\n";

// 1. Verificar si el controlador existe
echo "=== VERIFICANDO CONTROLADOR ===\n";
if (file_exists('controllers/FacturaPDFController.php')) {
    echo "✅ FacturaPDFController.php existe\n";
    
    // Leer el contenido para verificar
    $content = file_get_contents('controllers/FacturaPDFController.php');
    if (strpos($content, 'class FacturaPDFController') !== false) {
        echo "✅ La clase FacturaPDFController existe en el archivo\n";
    } else {
        echo "❌ No se encuentra la clase en el archivo\n";
    }
} else {
    echo "❌ FacturaPDFController.php NO existe\n";
}

// 2. Verificar configuración de rutas
echo "\n=== VERIFICANDO CONFIGURACIÓN ===\n";
if (defined('BASE_URL')) {
    echo "BASE_URL: " . BASE_URL . "\n";
} else {
    echo "❌ BASE_URL no está definida\n";
}

// 3. Verificar si la factura existe
echo "\n=== VERIFICANDO FACTURA ===\n";
require_once "models/Compra.php";
$compra = new Compra($db);
$factura = $compra->obtenerFacturaDetalle($id_factura);

if ($factura) {
    echo "✅ Factura #$id_factura existe\n";
    echo "   Cliente: " . ($factura['Nombre'] ?? '') . " " . ($factura['Apellido'] ?? '') . "\n";
    echo "   Total: $" . ($factura['Monto_Total'] ?? 0) . "\n";
} else {
    echo "❌ Factura #$id_factura NO existe\n";
}

// 4. Probar el controlador directamente
echo "\n=== PROBANDO CONTROLADOR DIRECTAMENTE ===\n";
require_once "controllers/FacturaPDFController.php";

try {
    $controller = new FacturaPDFController($db);
    echo "✅ Controlador instanciado correctamente\n";
    
    // Probar el método generar
    echo "🔧 Probando método generar()...\n";
    
    // Simular la llamada GET
    $_GET['id'] = $id_factura;
    
    // Capturar la salida
    ob_start();
    $controller->generar();
    $output = ob_get_clean();
    
    if (!empty($output)) {
        echo "✅ El método generar() produjo salida\n";
        echo "   Tamaño de salida: " . strlen($output) . " bytes\n";
        
        // Verificar si es PDF
        if (strpos($output, '%PDF') === 0) {
            echo "✅ ¡SALIDA ES UN PDF VÁLIDO!\n";
            
            // Guardar para verificar
            file_put_contents("debug_pdf_output_$id_factura.pdf", $output);
            echo "📄 PDF guardado en: debug_pdf_output_$id_factura.pdf\n";
        } else {
            echo "❌ La salida NO es un PDF válido\n";
            echo "   Primeros 100 caracteres: " . substr($output, 0, 100) . "\n";
        }
    } else {
        echo "❌ El método generar() no produjo salida\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al ejecutar el controlador: " . $e->getMessage() . "\n";
    echo "📋 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎯 URL DE PRUEBA: http://localhost/Tulook_MVC/?c=FacturaPDF&a=generar&id=$id_factura\n";
?>