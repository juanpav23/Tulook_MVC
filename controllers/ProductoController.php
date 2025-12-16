<?php
// ==========================================
// CONTROLADOR PRODUCTO - TuLook MVC
// VERSIÓN ACTUALIZADA CON MEJORAS DE IMÁGENES Y ATRIBUTOS
// ==========================================

require_once "models/Producto.php";
require_once "models/Database.php";
require_once "services/DescuentoService.php";

class ProductoController {
    private $db;
    private $producto;
    private $descuentoService;

    public function __construct($db = null) {
        $this->db = $db ?: (new Database())->getConnection();
        $this->producto = new Producto($this->db);
        $this->descuentoService = new DescuentoService($this->db);
        
        // ✅ FORZAR INICIO DE SESIÓN Y PREVENIR CACHE
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Headers para prevenir cache en todas las respuestas
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");
    }

    // 🏠 INDEX - Catálogo principal
    public function index() {
        // SOLO ARTÍCULOS CON AL MENOS 1 VARIANTE ACTIVA
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    a.Foto,
                    c.N_Categoria,
                    g.N_Genero,
                    pr.Valor AS Precio,
                    a.ID_Categoria,
                    COALESCE(SUM(p.Cantidad), 0) AS Stock,
                    COUNT(p.ID_Producto) AS Total_Variantes
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo AND p.Activo = 1
                LEFT JOIN precio pr ON pr.ID_Precio = a.ID_Precio
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                WHERE a.Activo = 1
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor, a.ID_Categoria
                HAVING COUNT(p.ID_Producto) > 0
                ORDER BY a.N_Articulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Aplicar descuentos
        $productosConDescuento = [];
        foreach ($productos as $producto) {
            $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
                (float)$producto['Precio'],
                $producto['ID_Articulo'],
                null,
                $producto['ID_Categoria']
            );
            
            if ($infoDescuento && is_array($infoDescuento)) {
                $producto['Info_Descuento'] = $infoDescuento;
                $producto['Precio_Con_Descuento'] = $infoDescuento['precio_final'] ?? $producto['Precio'];
            } else {
                $producto['Info_Descuento'] = [
                    'tiene_descuento' => false,
                    'precio_final' => $producto['Precio'],
                    'descuento_porcentaje' => 0,
                    'valor_descuento' => 0,
                    'tipo_descuento' => ''
                ];
                $producto['Precio_Con_Descuento'] = $producto['Precio'];
            }
            
            $productosConDescuento[] = $producto;
        }
        $productos = $productosConDescuento;

        $categorias = $this->getMenuCategorias();
        include "views/productos/index.php";
    }

    // 🔍 FILTRAR PRODUCTOS
    public function filtrar() {
        $idCategoria = $_GET['id_categoria'] ?? null;
        $idSub = $_GET['id_subcategoria'] ?? null;
        $idGenero = $_GET['id_genero'] ?? null;
        $busqueda = $_GET['busqueda'] ?? null;

        $sql = "SELECT 
                    a.ID_Articulo, 
                    a.N_Articulo, 
                    a.Foto,
                    c.N_Categoria, 
                    g.N_Genero, 
                    pr.Valor AS Precio, 
                    a.ID_Categoria,
                    COALESCE(SUM(p.Cantidad), 0) AS Stock,
                    COUNT(p.ID_Producto) AS Total_Variantes
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo AND p.Activo = 1
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
                HAVING COUNT(p.ID_Producto) > 0
                ORDER BY a.N_Articulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Aplicar descuentos
        $productosConDescuento = [];
        foreach ($productos as $producto) {
            $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
                (float)$producto['Precio'],
                $producto['ID_Articulo'],
                null,
                $producto['ID_Categoria']
            );
            
            if ($infoDescuento && is_array($infoDescuento)) {
                $producto['Info_Descuento'] = $infoDescuento;
                $producto['Precio_Con_Descuento'] = $infoDescuento['precio_final'] ?? $producto['Precio'];
            } else {
                $producto['Info_Descuento'] = [
                    'tiene_descuento' => false,
                    'precio_final' => $producto['Precio'],
                    'descuento_porcentaje' => 0,
                    'valor_descuento' => 0,
                    'tipo_descuento' => ''
                ];
                $producto['Precio_Con_Descuento'] = $producto['Precio'];
            }
            
            $productosConDescuento[] = $producto;
        }
        $productos = $productosConDescuento;

        $categorias = $this->getMenuCategorias();
        include "views/productos/index.php";
    }

    // 👁️ VER PRODUCTO DETALLADO (CON ATRIBUTOS DINÁMICOS)
    public function ver() {
        if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
            header("Location: " . BASE_URL . "?c=Producto&a=index");
            exit;
        }

        $idArticulo = (int)$_GET['id'];

        // VERIFICAR SI EL ARTÍCULO TIENE VARIANTES ACTIVAS
        $stmtVariantes = $this->db->prepare("SELECT COUNT(*) as total FROM producto WHERE ID_Articulo = ? AND Activo = 1");
        $stmtVariantes->execute([$idArticulo]);
        $totalVariantes = $stmtVariantes->fetch(PDO::FETCH_ASSOC)['total'];

        if ($totalVariantes == 0) {
            $_SESSION['error_message'] = "Este producto no está disponible para compra.";
            header("Location: " . BASE_URL . "?c=Producto&a=index");
            exit;
        }

        // Obtener información base del artículo
        $articulo = $this->producto->readBase($idArticulo);

        if (!$articulo) {
            include "views/error/404.php";
            return;
        }

        // Obtener información de la subcategoría para saber qué atributos mostrar
        $subcategoriaInfo = $this->producto->getSubcategoriaInfo($articulo['ID_SubCategoria']);
        $atributosRequeridos = $subcategoriaInfo['AtributosRequeridosArray'] ?? [];
        // ORGANIZAR ATRIBUTOS: Color primero, luego los demás
        $atributosRequeridos = $this->organizarAtributos($atributosRequeridos);

        // Objeto producto
        $producto = (object)[
            'ID_Articulo'  => (int)$articulo['ID_Articulo'],
            'N_Articulo'   => $articulo['N_Articulo'],
            'Foto'         => $this->resolveImageUrl($articulo['Foto']),
            'Precio'       => (float)($articulo['Precio'] ?? 0),
            'ID_Categoria' => $articulo['ID_Categoria'] ?? null, // ¡IMPORTANTE!
            'ID_Genero'    => $articulo['ID_Genero'] ?? null,
            'ID_SubCategoria' => $articulo['ID_SubCategoria'] ?? null,
            'AtributosRequeridos' => $atributosRequeridos
        ];

        // Obtener descuento
        $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
            $producto->Precio,
            $producto->ID_Articulo,
            null,
            $producto->ID_Categoria
        );

        if (!$infoDescuento || !is_array($infoDescuento)) {
            $infoDescuento = [
                'tiene_descuento' => false,
                'precio_final' => $producto->Precio,
                'descuento_porcentaje' => 0,
                'valor_descuento' => 0,
                'tipo_descuento' => ''
            ];
        }

        // Obtener variantes con atributos dinámicos
        $variantes = $this->producto->getVariantesByArticulo($idArticulo);

        // Procesar variantes para agrupar por combinaciones de atributos
        $variantesAgrupadas = [];
        foreach ($variantes as $variante) {
            $clave = '';
            $atributos = [];
            
            // Construir clave única basada en los atributos dinámicos
            for ($i = 1; $i <= 3; $i++) {
                $idAtributo = $variante["ID_Atributo{$i}"] ?? null;
                $valorAtributo = $variante["ValorAtributo{$i}"] ?? null;
                
                if ($idAtributo && $valorAtributo) {
                    $clave .= "{$idAtributo}:{$valorAtributo}_";
                    $atributos[] = [
                        'id' => $idAtributo,
                        'valor' => $valorAtributo,
                        'nombre' => $this->getNombreAtributo($idAtributo)
                    ];
                }
            }
            
            if (!isset($variantesAgrupadas[$clave])) {
                $variantesAgrupadas[$clave] = [
                    'atributos' => $atributos,
                    'variantes' => []
                ];
            }
            
            $variantesAgrupadas[$clave]['variantes'][] = $variante;
        }

        // Aplicar descuentos a variantes
        $variantesConDescuento = [];
        foreach ($variantes as $variante) {
            $precioVariante = $variante['Precio_Final'] ?? $producto->Precio;
            
            $infoDescuentoVariante = $this->descuentoService->obtenerInfoDescuento(
                $precioVariante,
                $producto->ID_Articulo,
                $variante['ID_Producto'] ?? null,
                $producto->ID_Categoria
            );
            
            if ($infoDescuentoVariante && is_array($infoDescuentoVariante)) {
                $variante['Info_Descuento'] = $infoDescuentoVariante;
                $variante['Precio_Con_Descuento'] = $infoDescuentoVariante['precio_final'] ?? $precioVariante;
            } else {
                $variante['Info_Descuento'] = [
                    'tiene_descuento' => false,
                    'precio_final' => $precioVariante,
                    'descuento_porcentaje' => 0,
                    'valor_descuento' => 0,
                    'tipo_descuento' => ''
                ];
                $variante['Precio_Con_Descuento'] = $precioVariante;
            }
            
            $variantesConDescuento[] = $variante;
        }
        $variantes = $variantesConDescuento;

        // Obtener opciones disponibles para cada atributo
        $opcionesAtributos = [];
        if (!empty($atributosRequeridos)) {
            foreach ($atributosRequeridos as $idTipoAtributo) {
                $idTipoAtributo = trim($idTipoAtributo);
                
                // Obtener TODAS las opciones primero
                $todasOpciones = $this->producto->getAtributosByTipo($idTipoAtributo);
                
                // Filtrar: solo mantener las opciones que existen en alguna variante
                $opcionesFiltradas = [];
                foreach ($todasOpciones as $opcion) {
                    foreach ($variantes as $variante) {
                        for ($i = 1; $i <= 3; $i++) {
                            if ($variante["ID_Atributo{$i}"] == $idTipoAtributo && 
                                $variante["ValorAtributo{$i}"] == $opcion['Valor']) {
                                $opcionesFiltradas[] = $opcion;
                                break 2; // Salir de ambos bucles
                            }
                        }
                    }
                }
                
                if (!empty($opcionesFiltradas)) {
                    $nombreAtributo = $this->producto->getNombreTipoAtributo($idTipoAtributo);
                    $opcionesAtributos[$idTipoAtributo] = $opcionesFiltradas;
                    error_log("✅ Atributo {$idTipoAtributo}: " . count($opcionesFiltradas) . " opciones FILTRADAS");
                }
            }
        }

        $categorias = $this->getMenuCategorias();
        
        // Pasar la conexión a la vista para los descuentos
        $datosVista = [
            'producto' => $producto,
            'variantes' => $variantes,
            'variantesAgrupadas' => $variantesAgrupadas,
            'opcionesAtributos' => $opcionesAtributos,
            'atributosRequeridos' => $atributosRequeridos,
            'categorias' => $categorias,
            'infoDescuento' => $infoDescuento,
            'db' => $this->db // ← ¡IMPORTANTE! Pasar la conexión a la vista
        ];
        
        // Extraer las variables para que estén disponibles en la vista
        extract($datosVista);
        
        include "views/productos/ver.php";
    }

    // Agregar método para organizar atributos
    private function organizarAtributos($atributos) {
        // Poner el color (ID 2) primero si existe
        $colorIndex = array_search('2', $atributos);
        if ($colorIndex !== false) {
            // Remover color de su posición actual
            unset($atributos[$colorIndex]);
            // Poner color al inicio
            array_unshift($atributos, '2');
        }
        return array_values($atributos); // Reindexar
    }

    // =======================================================
    // ✅ OBTENER PRECIO CON DESCUENTO (AJAX)
    // =======================================================
    public function obtenerPrecioConDescuento() {
        // Permitir tanto POST como GET para mayor flexibilidad
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Obtener datos según el método
        if ($method === 'POST') {
            $idProducto = $_POST['id_producto'] ?? null;
            $precioBase = $_POST['precio_base'] ?? null;
        } else if ($method === 'GET') {
            $idProducto = $_GET['id_producto'] ?? null;
            $precioBase = $_GET['precio_base'] ?? null;
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }
        
        error_log("DEBUG obtenerPrecioConDescuento - Método: $method, ID_Producto: $idProducto, PrecioBase: $precioBase");

        try {
            if (!$idProducto || !$precioBase) {
                echo json_encode([
                    'success' => false, 
                    'error' => 'Datos incompletos',
                    'debug' => ['id_producto' => $idProducto, 'precio_base' => $precioBase]
                ]);
                exit;
            }

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

            $infoDescuento = $this->descuentoService->obtenerInfoDescuento(
                (float)$precioBase,
                $idArticulo,
                $idProducto,
                $idCategoria
            );

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
                'error' => 'Error al calcular descuento: ' . $e->getMessage(),
                'precioFinal' => $precioBase ?? 0,
                'tieneDescuento' => false
            ]);
        }
        exit;
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

    private function getNombreAtributo($idAtributo) {
        try {
            if (!$idAtributo) return null;
            
            $stmt = $this->db->prepare("SELECT Nombre FROM tipo_atributo WHERE ID_TipoAtributo = ?");
            $stmt->execute([$idAtributo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['Nombre'] ?? null;
        } catch (Exception $e) {
            error_log("Error obteniendo nombre atributo: " . $e->getMessage());
            return null;
        }
    }
}
?>