<?php
// helpers.php
function getImagePath($dbPath) {
    $baseDir = __DIR__;
    
    // Verificar si la imagen existe en la ruta de la base de datos
    if (file_exists($baseDir . '/' . $dbPath)) {
        return BASE_URL . $dbPath;
    }
    
    // Buscar el archivo en assets/img/
    $fileName = basename($dbPath);
    $assetsPath = 'assets/img/' . $fileName;
    
    if (file_exists($baseDir . '/' . $assetsPath)) {
        return BASE_URL . $assetsPath;
    }
    
    // Si no se encuentra, usar imagen por defecto
    return BASE_URL . 'ImgProducto/Default.png';
}
?>