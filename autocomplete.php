<?php
// autocomplete.php (en la raíz del proyecto)

// Determinar BASE_URL dinámicamente
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
$path = dirname($script); // Esto te da la carpeta donde está autocomplete.php
$base_url = $protocol . '://' . $host . $path . '/';
$base_url = rtrim($base_url, '/') . '/';

// Conexión a la base de datos
try {
    $pdo = new PDO("mysql:host=localhost;dbname=tulook;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$term = $_GET['term'] ?? '';
$term = trim($term);

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

// Buscar productos
$sql = "SELECT 
            a.ID_Articulo as id,
            a.N_Articulo as label,
            a.N_Articulo as value,
            'producto' as type,
            a.Foto as imagen
        FROM articulo a
        WHERE a.Activo = 1 
        AND a.N_Articulo LIKE ?
        ORDER BY a.N_Articulo ASC
        LIMIT 10";

$stmt = $pdo->prepare($sql);
$stmt->execute(["%$term%"]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Formatear URLs
foreach ($results as &$item) {
    // Construir URL de imagen
    if (!empty($item['imagen'])) {
        // Si ya empieza con ImgProducto/, usar directamente
        if (strpos($item['imagen'], 'ImgProducto/') === 0) {
            $item['imagen'] = $base_url . $item['imagen'];
        }
        // Si no tiene el prefijo, agregarlo
        elseif (!preg_match('/^https?:\\/\\//i', $item['imagen'])) {
            $item['imagen'] = $base_url . 'ImgProducto/' . ltrim($item['imagen'], '/');
        }
        // Si ya es URL completa, dejarla como está
    } else {
        $item['imagen'] = $base_url . 'assets/img/placeholder.png';
    }
    
    // Construir URL del producto
    $item['url'] = $base_url . "index.php?c=Producto&a=ver&id=" . $item['id'];
}

echo json_encode($results);