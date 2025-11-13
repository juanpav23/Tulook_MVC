<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión BD
$mysqli = new mysqli("localhost", "root", "", "tulook");
if ($mysqli->connect_error) {
    die("❌ Error de conexión: " . $mysqli->connect_error);
}

$basePath = __DIR__ . "/ImgProducto";

// Función recursiva
function scanDirRecursive($dir) {
    $result = [];
    foreach (scandir($dir) as $item) {
        if ($item == "." || $item == "..") continue;
        $path = $dir . "/" . $item;
        if (is_dir($path)) {
            $result = array_merge($result, scanDirRecursive($path));
        } else {
            $result[] = $path;
        }
    }
    return $result;
}

// Detectar color
function detectarColor($nombreArchivo, $mysqli) {
    $nombreArchivo = strtolower($nombreArchivo);
    $res = $mysqli->query("SELECT ID_Color, N_Color FROM color");
    while ($row = $res->fetch_assoc()) {
        $colorNombre = strtolower($row['N_Color']);
        if (strpos($nombreArchivo, $colorNombre) !== false) {
            return $row['ID_Color'];
        }
    }
    return 1; // Blanco por defecto
}

$files = scanDirRecursive($basePath);
echo "✅ Escaneando " . count($files) . " archivos...\n\n";

foreach ($files as $file) {
    $relativePath = str_replace(__DIR__ . "/", "", $file);
    $parts = explode("/", $relativePath);

    if (count($parts) < 4) continue;

    $genero = $parts[1];
    $subcat = $parts[2];
    $nombreArchivo = $parts[3];
    $nombreArticulo = pathinfo($nombreArchivo, PATHINFO_FILENAME);
    $nombreArticulo = str_replace("_", " ", $nombreArticulo);

    // IDs de género y subcategoría
    $resGenero = $mysqli->query("SELECT ID_Genero FROM genero WHERE N_Genero='$genero'");
    if (!$resGenero || $resGenero->num_rows == 0) continue;
    $idGenero = $resGenero->fetch_assoc()["ID_Genero"];

    $resSub = $mysqli->query("SELECT ID_SubCategoria FROM subcategoria WHERE SubCategoria='$subcat'");
    if (!$resSub || $resSub->num_rows == 0) continue;
    $idSub = $resSub->fetch_assoc()["ID_SubCategoria"];

    // Detectar color
    $idColor = detectarColor($nombreArchivo, $mysqli);

    // Revisar si el artículo ya existe
    $resArticulo = $mysqli->query("SELECT ID_Articulo FROM articulo WHERE N_Articulo='$nombreArticulo' AND ID_SubCategoria=$idSub AND ID_Genero=$idGenero");
    
    if ($resArticulo->num_rows == 0) {
        // Insertar nuevo artículo
        $mysqli->query("INSERT INTO articulo (N_Articulo, Foto, ID_Categoria, ID_SubCategoria, ID_Genero, Activo)
                        VALUES ('$nombreArticulo', '$relativePath', 1, $idSub, $idGenero, 1)");
        $idArticulo = $mysqli->insert_id;
        echo "🆕 ARTICULO insertado: $nombreArticulo ($relativePath)\n";
    } else {
        $idArticulo = $resArticulo->fetch_assoc()["ID_Articulo"];
    }

    // Insertar imagen del artículo
    $resImg = $mysqli->query("SELECT ID_ArticuloColor FROM articulo_color_imagen WHERE ID_Articulo=$idArticulo AND Foto='$relativePath'");
    if ($resImg->num_rows == 0) {
        $mysqli->query("INSERT INTO articulo_color_imagen (ID_Articulo, ID_Color, Foto)
                        VALUES ($idArticulo, $idColor, '$relativePath')");
        echo "   ➕ Imagen añadida al artículo $idArticulo ($relativePath)\n";
    }

    // Insertar producto
    $resProd = $mysqli->query("SELECT ID_Producto FROM producto WHERE ID_Articulo=$idArticulo AND Foto='$relativePath'");
    if ($resProd->num_rows == 0) {
        $mysqli->query("INSERT INTO producto (ID_Articulo, ID_Talla, ID_Color, Foto, Porcentaje, Cantidad)
                        VALUES ($idArticulo, 1, $idColor, '$relativePath', 1, 10)");
        echo "   ➕ Producto creado para artículo $idArticulo ($relativePath)\n";
    }
}

echo "\n✅ Sincronización completada.\n";
?>
