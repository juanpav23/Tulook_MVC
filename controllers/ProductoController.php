<?php
// ==========================================
// CONTROLADOR PRODUCTO - TuLook MVC
// Versión corregida con sistema de descuentos completo
// ==========================================

require_once "models/Producto.php";
require_once "models/Favorito.php";
require_once "models/Database.php";
require_once "services/DescuentoService.php"; // ✅ NUEVO: Servicio de descuentos

class ProductoController {
    private $db;
    private $producto;
    private $descuentoService; // ✅ NUEVO: Servicio de descuentos

    public function __construct($db = null) {
        $this->db = $db ?: (new Database())->getConnection();
        $this->producto = new Producto($this->db);
        $this->descuentoService = new DescuentoService($this->db); // ✅ NUEVO: Inicializar servicio
        if (session_status() === PHP_SESSION_NONE) session_start();
    }
// =======================================================
// 🏠 INDEX - Catálogo principal (VERSIÓN CORREGIDA)
// =======================================================
public function index() {
    $sql = "SELECT 
                a.ID_Articulo,
                a.N_Articulo,
                COALESCE(MIN(aci.Foto), a.Foto) AS Foto,
                c.N_Categoria,
                g.N_Genero,
                pr.Valor AS Precio,
                a.ID_Categoria,
                (COALESCE(a.Cantidad, 0) + COALESCE(SUM(p.Cantidad), 0)) AS Stock
            FROM articulo a
            LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
            LEFT JOIN articulo_color_imagen aci ON aci.ID_Articulo = a.ID_Articulo
            LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
            LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
            LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
            WHERE a.Activo = 1
            GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor, a.Cantidad, a.ID_Categoria
            ORDER BY a.N_Articulo ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ CORREGIDO: Aplicar descuentos con validación
    $productosConDescuento = [];
    foreach ($productos as $producto) {
        $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
            (float)$producto['Precio'],
            $producto['ID_Articulo'],
            null,
            $producto['ID_Categoria']
        );
        
        // ✅ Validar que infoDescuento no sea null
        if ($infoDescuento && is_array($infoDescuento)) {
            $producto['Info_Descuento'] = $infoDescuento;
            $producto['Precio_Con_Descuento'] = $infoDescuento['precio_final'] ?? $producto['Precio'];
        } else {
            $producto['Info_Descuento'] = null;
            $producto['Precio_Con_Descuento'] = $producto['Precio'];
        }
        
        $productosConDescuento[] = $producto;
    }
    $productos = $productosConDescuento;

    $categorias = $this->getMenuCategorias();
    include "views/productos/index.php";
}

    // =======================================================
    // 🔍 FILTRAR PRODUCTOS POR CATEGORÍA / GÉNERO / BÚSQUEDA
    // =======================================================
    public function filtrar() {
        $idCategoria = $_GET['id_categoria'] ?? null;
        $idSub = $_GET['id_subcategoria'] ?? null;
        $idGenero = $_GET['id_genero'] ?? null;
        $busqueda = $_GET['busqueda'] ?? null;

        $sql = "SELECT 
                    a.ID_Articulo, 
                    a.N_Articulo, 
                    COALESCE(MIN(aci.Foto), a.Foto) AS Foto,
                    c.N_Categoria, 
                    g.N_Genero, 
                    pr.Valor AS Precio, 
                    a.ID_Categoria,
                    COALESCE(SUM(p.Cantidad), 0) AS Stock
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN articulo_color_imagen aci ON aci.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                WHERE a.Activo = 1";

        $params = [];
        if ($idCategoria) { $sql .= " AND a.ID_Categoria = ?"; $params[] = $idCategoria; }
        if ($idGenero)    { $sql .= " AND a.ID_Genero = ?";    $params[] = $idGenero; }
        if ($idSub)       { $sql .= " AND a.ID_SubCategoria = ?"; $params[] = $idSub; }
        if ($busqueda) {
            $sql .= " AND (a.N_Articulo LIKE ? OR c.N_Categoria LIKE ?)";
            $params[] = "%{$busqueda}%";
            $params[] = "%{$busqueda}%";
        }

        $sql .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor, a.ID_Categoria
                  ORDER BY a.N_Articulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ✅ NUEVO: Aplicar descuentos a productos filtrados
        $productosConDescuento = [];
        foreach ($productos as $producto) {
            $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
                (float)$producto['Precio'],
                $producto['ID_Articulo'],
                null,
                $producto['ID_Categoria']
            );
            
            $producto['Info_Descuento'] = $infoDescuento;
            $producto['Precio_Con_Descuento'] = $infoDescuento['precio_final'];
            $productosConDescuento[] = $producto;
        }
        $productos = $productosConDescuento;

        $categorias = $this->getMenuCategorias();
        include "views/productos/index.php";
    }

    // =======================================================
// 👁️ VER PRODUCTO DETALLADO (VERSIÓN CORREGIDA)
// =======================================================
public function ver() {
    if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: " . BASE_URL . "?c=Producto&a=index");
        exit;
    }

    $idArticulo = (int)$_GET['id'];

    // 🔸 Datos base del artículo
    $stmt = $this->db->prepare("SELECT 
                                    a.*, 
                                    pr.Valor AS Precio, 
                                    t.N_Talla,
                                    col.N_Color,
                                    col.CodigoHex
                                FROM articulo a
                                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                                LEFT JOIN talla t ON t.ID_Talla = a.ID_Talla
                                LEFT JOIN color col ON col.ID_Color = a.ID_Color
                                WHERE a.ID_Articulo = ? LIMIT 1");
    $stmt->execute([$idArticulo]);
    $art = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$art) {
        include "views/error/404.php";
        return;
    }

    // 🔸 Construcción del objeto principal
    $producto = (object)[
        'ID_Articulo'  => (int)$art['ID_Articulo'],
        'N_Articulo'   => $art['N_Articulo'],
        'Foto'         => $this->resolveImageUrl($art['Foto']),
        'Precio'       => (float)($art['Precio'] ?? 0),
        'ID_Categoria' => $art['ID_Categoria'] ?? null,
        'ID_Genero'    => $art['ID_Genero'] ?? null,
        'ID_Talla'     => $art['ID_Talla'] ?? null,
        'N_Talla'      => $art['N_Talla'] ?? 'Única',
        'ID_Color'     => $art['ID_Color'] ?? null,
        'N_Color'      => $art['N_Color'] ?? 'Color base',
        'CodigoHex'    => $art['CodigoHex'] ?? '#cccccc',
        'Cantidad'     => isset($art['Cantidad']) ? (int)$art['Cantidad'] : 0
    ];

    // ✅ CORREGIDO: Obtener información de descuento con validación
    $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
        $producto->Precio,
        $producto->ID_Articulo,
        null,
        $producto->ID_Categoria
    );

    // 🔸 Variantes del producto (colores y tallas)
    $variantes = $this->producto->getVariantesByArticulo($idArticulo);

    // ✅ CORREGIDO: Aplicar descuentos a cada variante con validación
    $variantesConDescuento = [];
    foreach ($variantes as $variante) {
        $precioVariante = $variante['Precio_Final'] ?? $producto->Precio;
        
        // Obtener descuento para esta variante
        $infoDescuentoVariante = $this->descuentoService->obtenerInfoDescuento(
            $precioVariante,
            $producto->ID_Articulo,
            $variante['ID_Producto'] ?? null,
            $producto->ID_Categoria
        );
        
        // ✅ CORREGIDO: Validar que infoDescuentoVariante no sea null
        if ($infoDescuentoVariante && is_array($infoDescuentoVariante)) {
            $variante['Info_Descuento'] = $infoDescuentoVariante;
            $variante['Precio_Con_Descuento'] = $infoDescuentoVariante['precio_final'] ?? $precioVariante;
        } else {
            $variante['Info_Descuento'] = null;
            $variante['Precio_Con_Descuento'] = $precioVariante;
        }
        
        $variantesConDescuento[] = $variante;
    }
    $variantes = $variantesConDescuento;

    // 🔸 Tallas disponibles (artículo base + variantes)
    $baseTallas = $this->getTallasDisponiblesByArticulo($idArticulo);

    // 🔸 Si no hay variantes, crear una por defecto del artículo base CON COLOR REAL
    if (empty($variantes)) {
        // Obtener el color real si existe
        $color_real = 'Sin color';
        $codigo_hex_real = null;
        if ($producto->ID_Color && $producto->ID_Color !== 'base') {
            $stmt_color = $this->db->prepare("SELECT N_Color, CodigoHex FROM color WHERE ID_Color = ?");
            $stmt_color->execute([$producto->ID_Color]);
            $color_data = $stmt_color->fetch(PDO::FETCH_ASSOC);
            if ($color_data) {
                $color_real = $color_data['N_Color'];
                $codigo_hex_real = $color_data['CodigoHex'];
            }
        }
        
        // ✅ CORREGIDO: Validar que infoDescuento no sea null
        $precioConDescuento = $infoDescuento && is_array($infoDescuento) 
            ? ($infoDescuento['precio_final'] ?? $producto->Precio) 
            : $producto->Precio;
        
        $variantes[] = [
            'ID_Producto'     => null,
            'ID_Articulo'     => $producto->ID_Articulo,
            'Nombre_Producto' => $producto->N_Articulo,
            'ID_Color'        => $producto->ID_Color,
            'N_Color'         => $color_real,
            'CodigoHex'       => $codigo_hex_real ?: $producto->CodigoHex,
            'ID_Talla'        => $producto->ID_Talla,
            'N_Talla'         => $producto->N_Talla,
            'Foto'            => $producto->Foto,
            'Porcentaje'      => 0,
            'Cantidad'        => $producto->Cantidad,
            'Precio_Base'     => $producto->Precio,
            'Precio_Final'    => $producto->Precio,
            'Info_Descuento'  => $infoDescuento, // ✅ Puede ser null
            'Precio_Con_Descuento' => $precioConDescuento
        ];
    }

    // 🔸 Si no hay tallas en la base de datos pero el artículo base tiene talla, agregarla
    if (empty($baseTallas) && $producto->ID_Talla) {
        $baseTallas[] = [
            'ID_Talla' => $producto->ID_Talla,
            'N_Talla' => $producto->N_Talla,
            'Cantidad' => $producto->Cantidad,
            'Tipo' => 'base',
            'ID_Producto' => $producto->ID_Articulo
        ];
    }

    foreach ($variantes as &$v) {
        $v['Foto'] = $this->resolveImageUrl($v['Foto'] ?? null);
        if (empty($v['Nombre_Producto'])) {
            $nombreExtra = trim(($v['N_Color'] ?? '') . ' ' . ($v['N_Talla'] ?? ''));
            $v['Nombre_Producto'] = trim($producto->N_Articulo . ' ' . $nombreExtra);
        }
    }
    unset($v);

    // 🔸 Comprobar si es favorito
    $esFavorito = false;
    if (isset($_SESSION['ID_Usuario'])) {
        $favModel = new Favorito($this->db);
        $favModel->ID_Usuario = $_SESSION['ID_Usuario'];
        if (!empty($variantes[0]['ID_Producto'])) {
            $favModel->ID_Producto = (int)$variantes[0]['ID_Producto'];
            if (method_exists($favModel, 'exists')) $esFavorito = $favModel->exists();
        }
    }

    $categorias = $this->getMenuCategorias();
    
    // ✅ CORREGIDO: Validar que infoDescuento no sea null antes de pasar a la vista
    $datosVista = [
        'producto' => $producto,
        'variantes' => $variantes,
        'baseTallas' => $baseTallas,
        'esFavorito' => $esFavorito,
        'categorias' => $categorias,
        'infoDescuento' => $infoDescuento // ✅ Puede ser null
    ];
    
    include "views/productos/ver.php";
}

   // =======================================================
// ✅ CORREGIDO: OBTENER PRECIO CON DESCUENTO (AJAX)
// =======================================================
// En controllers/ProductoController.php - agregar este método
public function obtenerPrecioConDescuento() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Método no permitido']);
        exit;
    }

    try {
        $idProducto = $_POST['id_producto'] ?? null;
        $precioBase = $_POST['precio_base'] ?? null;
        
        if (!$idProducto || !$precioBase) {
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            exit;
        }

        // Obtener información del producto para categoría
        $stmt = $this->db->prepare("
            SELECT a.ID_Categoria, a.ID_Articulo 
            FROM producto p 
            INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo 
            WHERE p.ID_Producto = ?
        ");
        $stmt->execute([$idProducto]);
        $productoInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $idCategoria = $productoInfo['ID_Categoria'] ?? null;
        $idArticulo = $productoInfo['ID_Articulo'] ?? null;

        // Obtener descuento aplicable
        $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
            (float)$precioBase,
            $idArticulo,
            $idProducto,
            $idCategoria
        );

        // ✅ CORREGIDO: Validar que infoDescuento no sea null
        if ($infoDescuento && is_array($infoDescuento)) {
            echo json_encode([
                'success' => true,
                'precioFinal' => $infoDescuento['precio_final'] ?? $precioBase,
                'descuentoPorcentaje' => $infoDescuento['descuento_porcentaje'] ?? 0,
                'tieneDescuento' => $infoDescuento['tiene_descuento'] ?? false,
                'precioOriginal' => $precioBase
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'precioFinal' => $precioBase,
                'descuentoPorcentaje' => 0,
                'tieneDescuento' => false,
                'precioOriginal' => $precioBase
            ]);
        }
        
    } catch (Exception $e) {
        error_log("Error en obtenerPrecioConDescuento: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'error' => 'Error al calcular descuento',
            'precioFinal' => $_POST['precio_base'] ?? 0,
            'tieneDescuento' => false
        ]);
    }
    exit;
}

    // =======================================================
    // ❤️ FAVORITOS
    // =======================================================
    public function toggleFavorito() {
        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $idProd = $_POST['id_producto'] ?? null;
        $idArticulo = $_POST['id_articulo'] ?? null;

        if (!$idProd && !$idArticulo) {
            header("Location: " . BASE_URL);
            exit;
        }

        $fav = new Favorito($this->db);
        $fav->ID_Usuario = $_SESSION['ID_Usuario'];
        $fav->ID_Producto = (int)($idProd ?? 0);

        if (method_exists($fav, 'exists') && $fav->exists()) {
            if (method_exists($fav, 'remove')) $fav->remove();
        } else {
            if (method_exists($fav, 'add')) $fav->add();
        }

        header("Location: " . BASE_URL . "?c=Producto&a=ver&id=" . (int)($idArticulo ?? 0));
    }

    // =======================================================
    // 🧩 AUXILIARES
    // =======================================================
    private function resolveImageUrl($path) {
        if (empty($path)) return BASE_URL . 'assets/img/placeholder.png';
        if (preg_match('/^https?:\\/\\//i', $path)) return $path;
        $rel = ltrim($path, '/');
        $fs = __DIR__ . "/../" . $rel;
        if (file_exists($fs)) return rtrim(BASE_URL, '/') . '/' . $rel;
        return BASE_URL . 'assets/img/placeholder.png';
    }

    private function getMenuCategorias() {
        $sql = "SELECT ID_Categoria, N_Categoria FROM categoria ORDER BY N_Categoria";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // =======================================================
    // 👕 TALLAS DISPONIBLES POR ARTÍCULO (BASE + VARIANTES)
    // =======================================================
    private function getTallasDisponiblesByArticulo($idArticulo) {
        $tallas = [];
        
        // 🔸 Tallas del artículo base
        $sqlBase = "SELECT 
                        a.ID_Talla,
                        t.N_Talla,
                        a.Cantidad,
                        'base' AS Tipo,
                        a.ID_Articulo AS ID_Producto
                    FROM articulo a
                    LEFT JOIN talla t ON t.ID_Talla = a.ID_Talla
                    WHERE a.ID_Articulo = ? AND a.ID_Talla IS NOT NULL";
        
        $stmtBase = $this->db->prepare($sqlBase);
        $stmtBase->execute([$idArticulo]);
        $tallasBase = $stmtBase->fetchAll(PDO::FETCH_ASSOC);
        
        // 🔸 Tallas de las variantes (productos)
        $sqlVariantes = "SELECT 
                            p.ID_Talla,
                            t.N_Talla,
                            p.Cantidad,
                            'variante' AS Tipo,
                            p.ID_Producto
                        FROM producto p
                        INNER JOIN talla t ON t.ID_Talla = p.ID_Talla
                        WHERE p.ID_Articulo = ?
                        ORDER BY t.N_Talla ASC";
        
        $stmtVariantes = $this->db->prepare($sqlVariantes);
        $stmtVariantes->execute([$idArticulo]);
        $tallasVariantes = $stmtVariantes->fetchAll(PDO::FETCH_ASSOC);
        
        // Combinar ambas fuentes
        $tallas = array_merge($tallasBase, $tallasVariantes);
        
        return $tallas;
    }
}
?>