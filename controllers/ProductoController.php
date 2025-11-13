<?php
// ==========================================
// CONTROLADOR PRODUCTO - TuLook MVC (Versión Final Corregida)
// ==========================================

require_once "models/Producto.php";
require_once "models/Favorito.php";
require_once "models/Database.php";

class ProductoController {
    private $db;
    private $producto;

    public function __construct($db = null) {
        $this->db = $db ?: (new Database())->getConnection();
        $this->producto = new Producto($this->db);
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // =======================================================
    // 🏠 INDEX
    // =======================================================
    public function index() {
        $stmt = $this->producto->read();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categorias = $this->getMenuCategorias();
        include "views/productos/index.php";
    }

    // =======================================================
    // 🔍 FILTRAR PRODUCTOS
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
                    SUM(p.Cantidad) AS Stock
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

        $sql .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, c.N_Categoria, g.N_Genero, pr.Valor
                  ORDER BY a.N_Articulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $categorias = $this->getMenuCategorias();
        include "views/productos/index.php";
    }

    // =======================================================
    // 👁️ VER PRODUCTO DETALLADO
    // =======================================================
    public function ver() {
        if (empty($_GET['id']) || !is_numeric($_GET['id'])) {
            header("Location: " . BASE_URL . "?c=Producto&a=index");
            exit;
        }

        $idArticulo = (int)$_GET['id'];

        // 🔸 Obtener datos base
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

        // 🔸 Construir objeto base
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

        // 🔸 Variantes
        $variantes = $this->producto->getVariantesByArticulo($idArticulo);

        // 🔸 Talla base (aunque no haya variantes)
        $baseTallas = [];
        if ($producto->ID_Talla) {
            $baseTallas[] = [
                'ID_Talla'  => $producto->ID_Talla,
                'N_Talla'   => $producto->N_Talla,
                'Cantidad'  => $producto->Cantidad,
                'ID_Producto' => null
            ];
        }

        // 🔸 Si no hay variantes, crear una virtual
        if (empty($variantes)) {
            $variantes[] = [
                'ID_Producto'     => null,
                'ID_Articulo'     => $producto->ID_Articulo,
                'Nombre_Producto' => $producto->N_Articulo,
                'ID_Color'        => $producto->ID_Color,
                'N_Color'         => $producto->N_Color,
                'CodigoHex'       => $producto->CodigoHex,
                'ID_Talla'        => $producto->ID_Talla,
                'N_Talla'         => $producto->N_Talla,
                'Foto'            => $producto->Foto,
                'Porcentaje'      => 0,
                'Cantidad'        => $producto->Cantidad,
                'Precio_Base'     => $producto->Precio,
                'Precio_Final'    => $producto->Precio
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

        // 🔸 Favorito
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

        // ✅ Pasar también $baseTallas
        include "views/productos/ver.php";
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
}
?>











