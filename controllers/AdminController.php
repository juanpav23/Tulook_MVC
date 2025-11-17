<?php
require_once "models/Database.php";

class AdminController {
    private $db;

    public function __construct() {
        $dbObj = new Database();
        $this->db = $dbObj->getConnection();

        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->ensureAdmin();
    }

    private function ensureAdmin() {
        if (!isset($_SESSION['rol'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $stmt = $this->db->prepare("SELECT Roles FROM rol WHERE ID_Rol = ?");
        $stmt->execute([(int)$_SESSION['rol']]);
        $rol = $stmt->fetchColumn();

        // Permitir rol 1 (Administrador) y rol 2 (Editor)
        if (!$rol || (strtolower($rol) !== 'administrador' && strtolower($rol) !== 'editor')) {
            header("Location: " . BASE_URL);
            exit;
        }
    }

    // 🔍 VERIFICAR PRODUCTO BASE DUPLICADO (SOLO DATOS, SIN FOTO)
    private function verificarProductoBaseDuplicado($nombre, $categoria, $subcategoria, $genero, $idExcluir = null) {
        $sql = "SELECT ID_Articulo, N_Articulo FROM articulo 
                WHERE N_Articulo = ? 
                AND ID_Categoria = ? 
                AND ID_SubCategoria = ? 
                AND ID_Genero = ?";
        
        $params = [$nombre, $categoria, $subcategoria, $genero];
        
        if ($idExcluir) {
            $sql .= " AND ID_Articulo != ?";
            $params[] = $idExcluir;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔍 VERIFICAR VARIANTE DUPLICADA (SOLO DATOS, SIN FOTO)
    private function verificarVarianteDuplicada($idArticulo, $idColor, $idTalla, $idExcluir = null) {
        $sql = "SELECT ID_Producto FROM producto 
                WHERE ID_Articulo = ? 
                AND ID_Color = ? 
                AND ID_Talla = ?";
        
        $params = [$idArticulo, $idColor, $idTalla];
        
        if ($idExcluir) {
            $sql .= " AND ID_Producto != ?";
            $params[] = $idExcluir;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🏠 DASHBOARD
    public function index() {
        $counts = [
            'articulos' => (int)$this->db->query("SELECT COUNT(*) FROM articulo")->fetchColumn(),
            'productos' => (int)$this->db->query("SELECT COUNT(*) FROM producto")->fetchColumn(),
            'usuarios'  => (int)$this->db->query("SELECT COUNT(*) FROM usuario")->fetchColumn(),
            'ventas'    => (int)$this->db->query("SELECT COUNT(*) FROM factura")->fetchColumn()
        ];

        include "views/admin/layout_admin.php";
    }

    // 📦 LISTADO DE PRODUCTOS BASE
    public function productos() {
        $sql = "SELECT a.*, c.N_Categoria, s.SubCategoria, g.N_Genero, 
                    p.Valor AS PrecioBase, t.N_Talla, col.N_Color
                FROM articulo a
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                LEFT JOIN precio p ON p.ID_Precio = a.ID_Precio
                LEFT JOIN talla t ON t.ID_Talla = a.ID_Talla
                LEFT JOIN color col ON col.ID_Color = a.ID_Color
                ORDER BY a.N_Articulo ASC";
        $articulos = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Obtener datos para filtros
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcategorias = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // 🧾 FORMULARIO PRODUCTO BASE (CORREGIDO)
    public function productoForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $articulo = null;

        if ($id) {
            // Consulta mejorada para obtener toda la información del artículo
            $sql = "SELECT a.*, c.N_Categoria, g.N_Genero, s.SubCategoria, 
                           col.N_Color AS ColorBase, t.N_Talla AS TallaBase,
                           p.Valor AS PrecioBase
                    FROM articulo a
                    LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                    LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                    LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                    LEFT JOIN color col ON col.ID_Color = a.ID_Color
                    LEFT JOIN talla t ON t.ID_Talla = a.ID_Talla
                    LEFT JOIN precio p ON p.ID_Precio = a.ID_Precio
                    WHERE a.ID_Articulo = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

            // Debug: Verificar si se encontró el artículo
            if (!$articulo) {
                error_log("❌ No se encontró el artículo con ID: " . $id);
                $_SESSION['msg'] = "❌ No se encontró el producto con ID: " . $id;
                $_SESSION['msg_type'] = "danger";
                header("Location: " . BASE_URL . "?c=Admin&a=productos");
                exit;
            }
        }

        // Obtener datos para los selects
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos    = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcats    = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);
        $colors     = $this->db->query("SELECT * FROM color ORDER BY N_Color")->fetchAll(PDO::FETCH_ASSOC);
        $precios    = $this->db->query("SELECT * FROM precio ORDER BY Valor ASC")->fetchAll(PDO::FETCH_ASSOC);
        $tallas     = $this->db->query("SELECT * FROM talla ORDER BY N_Talla")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR O ACTUALIZAR PRODUCTO BASE (SOLO VERIFICA DATOS)
    public function saveProducto() {
        try {
            $id = isset($_POST['ID_Articulo']) ? (int)$_POST['ID_Articulo'] : null;
            $nombre = trim($_POST['N_Articulo'] ?? '');
            $cat = (int)($_POST['ID_Categoria'] ?? 0);
            $subcat = (int)($_POST['ID_SubCategoria'] ?? 0);
            $gen = (int)($_POST['ID_Genero'] ?? 0);
            $idColor = (int)($_POST['ID_Color'] ?? 0);
            $idPrecio = (int)($_POST['ID_Precio'] ?? 0);
            $idTalla = (int)($_POST['ID_Talla'] ?? 0);
            $cantidad = (int)($_POST['Cantidad'] ?? 0);
            $activo = isset($_POST['Activo']) ? 1 : 0;

            // ⚙️ Manejo de la imagen
            $fotoFinal = $_POST['foto_actual'] ?? '';

            if (!empty($_FILES['foto']['name'])) {
                $nombreArchivo = basename($_FILES['foto']['name']);
                
                if (!empty($_POST['Foto'])) {
                    $rutaDestino = $_POST['Foto'];
                } else {
                    $rutaDestino = 'ImgProducto/' . $nombreArchivo;
                }

                $directorio = dirname($rutaDestino);
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                    $fotoFinal = $rutaDestino;
                }
            } elseif (!empty($_POST['Foto'])) {
                $fotoFinal = trim($_POST['Foto']);
            }

            // 🔍 VERIFICAR DUPLICADO ANTES DE GUARDAR (SOLO DATOS)
            $productoDuplicado = $this->verificarProductoBaseDuplicado($nombre, $cat, $subcat, $gen, $id);
            
            if ($productoDuplicado) {
                $_SESSION['msg'] = "❌ Este producto ya está creado. No se pueden guardar duplicados.";
                $_SESSION['msg_type'] = "warning";
                header("Location: " . BASE_URL . "?c=Admin&a=productoForm" . ($id ? "&id=$id" : ""));
                exit;
            }

            // 🔍 Validar precio
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM precio WHERE ID_Precio = ?");
            $stmt->execute([$idPrecio]);
            if ($stmt->fetchColumn() == 0) {
                $idPrecio = null;
            }

            // 🧩 Insertar o actualizar
            if ($id) {
                $update = $this->db->prepare("
                    UPDATE articulo SET 
                        N_Articulo = ?, 
                        Foto = ?, 
                        ID_Categoria = ?, 
                        ID_SubCategoria = ?, 
                        ID_Genero = ?, 
                        ID_Color = ?, 
                        ID_Precio = ?, 
                        ID_Talla = ?, 
                        Cantidad = ?, 
                        Activo = ?
                    WHERE ID_Articulo = ?
                ");
                $ok = $update->execute([
                    $nombre, $fotoFinal, $cat, $subcat, $gen,
                    $idColor, $idPrecio, $idTalla, $cantidad, $activo, $id
                ]);

                $_SESSION['msg'] = $ok
                    ? "✅ Producto actualizado correctamente."
                    : "❌ Error al actualizar el producto.";
                $_SESSION['msg_type'] = $ok ? "success" : "danger";
            } else {
                $insert = $this->db->prepare("
                    INSERT INTO articulo 
                        (N_Articulo, Foto, ID_Categoria, ID_SubCategoria, ID_Genero, ID_Color, ID_Precio, ID_Talla, Cantidad, Activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $ok = $insert->execute([
                    $nombre, $fotoFinal, $cat, $subcat, $gen,
                    $idColor, $idPrecio, $idTalla, $cantidad, $activo
                ]);

                $_SESSION['msg'] = $ok
                    ? "✅ Producto guardado correctamente."
                    : "❌ Error al guardar el producto.";
                $_SESSION['msg_type'] = $ok ? "success" : "danger";
            }

            header("Location: " . BASE_URL . "?c=Admin&a=productos");
            exit;

        } catch (PDOException $e) {
            $_SESSION['msg'] = "⚠️ Error SQL: " . $e->getMessage();
            $_SESSION['msg_type'] = "danger";
            header("Location: " . BASE_URL . "?c=Admin&a=productos");
            exit;
        }
    }

    // ➕ GUARDAR NUEVA VARIANTE (SOLO VERIFICA DATOS)
    public function agregarVariante() {
        $idArticulo = (int)($_POST['ID_Articulo'] ?? 0);
        $idColor = (int)($_POST['ID_Color'] ?? 0);
        $idTalla = (int)($_POST['ID_Talla'] ?? 0);
        $porcentaje = (float)($_POST['Porcentaje'] ?? 0);
        $cantidad = (int)($_POST['Cantidad'] ?? 0);
        $foto = trim($_POST['Foto'] ?? '');
        $nombreProducto = trim($_POST['Nombre_Producto'] ?? '');

        if ($idArticulo <= 0 || $idColor <= 0 || $idTalla <= 0) {
            header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
            exit;
        }

        // Manejar subida de imagen
        if (!empty($_FILES['imagen_variante']['name']) && !empty($foto)) {
            $directorio = dirname($foto);
            if (!is_dir($directorio)) {
                mkdir($directorio, 0777, true);
            }

            if (move_uploaded_file($_FILES['imagen_variante']['tmp_name'], $foto)) {
                // Imagen subida correctamente
            }
        }

        // 🔍 VERIFICAR VARIANTE DUPLICADA (SOLO DATOS: producto+color+talla)
        $varianteDuplicada = $this->verificarVarianteDuplicada($idArticulo, $idColor, $idTalla);
        
        if ($varianteDuplicada) {
            $_SESSION['msg'] = "❌ Ya existe una variante con esta combinación de color y talla.";
            $_SESSION['msg_type'] = "warning";
            header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
            exit;
        }

        $stmt = $this->db->prepare("
            INSERT INTO producto (ID_Articulo, ID_Talla, ID_Color, Foto, Porcentaje, Cantidad, Nombre_Producto)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$idArticulo, $idTalla, $idColor, $foto, $porcentaje, $cantidad, $nombreProducto]);

        $_SESSION['msg'] = "✅ Variante agregada correctamente.";
        $_SESSION['msg_type'] = "success";

        header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
        exit;
    }

    // 🔍 BUSCAR PRODUCTOS BASE CON FILTROS
    public function buscarProductos() {
        $termino = trim($_GET['q'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        $genero = $_GET['genero'] ?? '';
        $subcategoria = $_GET['subcategoria'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        $sql = "SELECT a.*, c.N_Categoria, s.SubCategoria, g.N_Genero, 
                    p.Valor AS PrecioBase, t.N_Talla, col.N_Color
                FROM articulo a
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                LEFT JOIN precio p ON p.ID_Precio = a.ID_Precio
                LEFT JOIN talla t ON t.ID_Talla = a.ID_Talla
                LEFT JOIN color col ON col.ID_Color = a.ID_Color
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($termino)) {
            $sql .= " AND (a.N_Articulo LIKE ? OR c.N_Categoria LIKE ? OR s.SubCategoria LIKE ? OR g.N_Genero LIKE ?)";
            $param = "%$termino%";
            $params = array_merge($params, [$param, $param, $param, $param]);
        }
        
        if (!empty($categoria)) {
            $sql .= " AND a.ID_Categoria = ?";
            $params[] = $categoria;
        }
        
        if (!empty($genero)) {
            $sql .= " AND a.ID_Genero = ?";
            $params[] = $genero;
        }
        
        if (!empty($subcategoria)) {
            $sql .= " AND a.ID_SubCategoria = ?";
            $params[] = $subcategoria;
        }
        
        if ($estado !== '') {
            $sql .= " AND a.Activo = ?";
            $params[] = $estado;
        }
        
        $sql .= " ORDER BY a.N_Articulo ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener datos para filtros
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcategorias = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);
        
        // Pasar datos a la vista
        $terminoBusqueda = $termino;
        $filtrosAplicados = $_GET;
        
        include "views/admin/layout_admin.php";
    }

    // 🔍 BUSCAR VARIANTES CON FILTROS
    public function buscarVariantes() {
        $termino = trim($_GET['q'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        $genero = $_GET['genero'] ?? '';
        $subcategoria = $_GET['subcategoria'] ?? '';
        $color = $_GET['color'] ?? '';
        $talla = $_GET['talla'] ?? '';
        
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    a.Foto AS FotoPrincipal,
                    a.ID_Categoria,
                    a.ID_SubCategoria,
                    a.ID_Genero,
                    cat.N_Categoria,
                    gen.N_Genero,
                    s.SubCategoria,
                    COUNT(p.ID_Producto) AS TotalVariantes,
                    GROUP_CONCAT(DISTINCT col.N_Color SEPARATOR ', ') AS Colores,
                    GROUP_CONCAT(DISTINCT t.N_Talla SEPARATOR ', ') AS Tallas
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN color col ON col.ID_Color = p.ID_Color
                LEFT JOIN talla t ON t.ID_Talla = p.ID_Talla
                LEFT JOIN categoria cat ON cat.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero gen ON gen.ID_Genero = a.ID_Genero
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($termino)) {
            $sql .= " AND (a.N_Articulo LIKE ? OR col.N_Color LIKE ? OR t.N_Talla LIKE ? OR s.SubCategoria LIKE ?)";
            $param = "%$termino%";
            $params = array_merge($params, [$param, $param, $param, $param]);
        }
        
        if (!empty($categoria)) {
            $sql .= " AND a.ID_Categoria = ?";
            $params[] = $categoria;
        }
        
        if (!empty($genero)) {
            $sql .= " AND a.ID_Genero = ?";
            $params[] = $genero;
        }
        
        if (!empty($subcategoria)) {
            $sql .= " AND a.ID_SubCategoria = ?";
            $params[] = $subcategoria;
        }
        
        if (!empty($color)) {
            $sql .= " AND p.ID_Color = ?";
            $params[] = $color;
        }
        
        if (!empty($talla)) {
            $sql .= " AND p.ID_Talla = ?";
            $params[] = $talla;
        }
        
        $sql .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.ID_Categoria, a.ID_SubCategoria, a.ID_Genero, cat.N_Categoria, gen.N_Genero, s.SubCategoria
                ORDER BY a.N_Articulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Obtener datos para filtros
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcategorias = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);
        $colores = $this->db->query("SELECT * FROM color ORDER BY N_Color")->fetchAll(PDO::FETCH_ASSOC);
        $tallas = $this->db->query("SELECT * FROM talla ORDER BY N_Talla")->fetchAll(PDO::FETCH_ASSOC);
        
        // Pasar datos a la vista
        $terminoBusqueda = $termino;
        $filtrosAplicados = $_GET;
        
        include "views/admin/layout_admin.php";
    }

    // ✏️ EDITAR VARIANTE (SOLO VERIFICA DATOS)
    public function editarVariante() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "?c=Admin&a=productos");
            exit;
        }

        try {
            $idProducto = $_POST['ID_Producto'] ?? null;
            $idArticulo = $_POST['ID_Articulo'] ?? null;
            $idColor = $_POST['ID_Color'] ?? null;
            $idTalla = $_POST['ID_Talla'] ?? null;
            $porcentaje = $_POST['Porcentaje'] ?? 0;
            $cantidad = $_POST['Cantidad'] ?? 0;
            $foto = $_POST['Foto'] ?? '';
            $nombreProducto = trim($_POST['Nombre_Producto'] ?? '');

            if (!$idProducto || !$idArticulo || !$idColor || !$idTalla) {
                throw new Exception("Datos incompletos");
            }

            // 🔍 VERIFICAR VARIANTE DUPLICADA (SOLO DATOS: producto+color+talla)
            $varianteDuplicada = $this->verificarVarianteDuplicada($idArticulo, $idColor, $idTalla, $idProducto);
            
            if ($varianteDuplicada) {
                $_SESSION['msg'] = "❌ Ya existe otra variante con esta combinación de color y talla.";
                $_SESSION['msg_type'] = "warning";
                header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=" . $idArticulo);
                exit;
            }

            $sql = "UPDATE producto SET 
                        ID_Color = ?, 
                        ID_Talla = ?, 
                        Porcentaje = ?, 
                        Cantidad = ?, 
                        Foto = ?,
                        Nombre_Producto = ?
                    WHERE ID_Producto = ? AND ID_Articulo = ?";
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute([
                $idColor, $idTalla, $porcentaje, $cantidad, $foto,
                $nombreProducto, $idProducto, $idArticulo
            ]);

            $_SESSION['msg'] = $success
                ? "✅ Variante actualizada correctamente"
                : "❌ Error al actualizar la variante";
            $_SESSION['msg_type'] = $success ? "success" : "danger";

        } catch (Exception $e) {
            $_SESSION['msg'] = "❌ Error: " . $e->getMessage();
            $_SESSION['msg_type'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=" . ($idArticulo ?? ''));
        exit;
    }

    // 🗑️ ELIMINAR VARIANTE
    public function eliminarVariante() {
        $idProducto = (int)($_GET['id'] ?? 0);
        $idArticulo = (int)($_GET['articulo'] ?? 0);

        if ($idProducto > 0) {
            $stmt = $this->db->prepare("DELETE FROM producto WHERE ID_Producto = ?");
            $stmt->execute([$idProducto]);
            $_SESSION['msg'] = "✅ Variante eliminada correctamente";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "❌ Error: ID de variante no válido";
            $_SESSION['msg_type'] = "danger";
        }

        header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=" . $idArticulo);
        exit;
    }

    // 🔍 DETALLE PRODUCTO BASE
    public function detalleProducto() {
        $id = (int)($_GET['id'] ?? 0);

        $stmt = $this->db->prepare("
            SELECT a.*, p.Valor AS PrecioBase, c.N_Color AS ColorBaseNombre, t.N_Talla
            FROM articulo a
            LEFT JOIN precio p ON a.ID_Precio = p.ID_Precio
            LEFT JOIN color c ON a.ID_Color = c.ID_Color
            LEFT JOIN talla t ON a.ID_Talla = t.ID_Talla
            WHERE a.ID_Articulo = ?
        ");
        $stmt->execute([$id]);
        $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmtV = $this->db->prepare("
            SELECT pr.*, c.N_Color, t.N_Talla
            FROM producto pr
            LEFT JOIN color c ON pr.ID_Color = c.ID_Color
            LEFT JOIN talla t ON pr.ID_Talla = t.ID_Talla
            WHERE pr.ID_Articulo = ?
        ");
        $stmtV->execute([$id]);
        $variantes = $stmtV->fetchAll(PDO::FETCH_ASSOC);

        $colors = $this->db->query("SELECT * FROM color ORDER BY N_Color")->fetchAll(PDO::FETCH_ASSOC);
        $tallas = $this->db->query("SELECT * FROM talla ORDER BY N_Talla")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // 🗑️ ELIMINAR PRODUCTO BASE + VARIANTES
    public function deleteProducto() {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->db->prepare("DELETE FROM producto WHERE ID_Articulo=?")->execute([$id]);
            $this->db->prepare("DELETE FROM articulo WHERE ID_Articulo=?")->execute([$id]);
        }
        header("Location: " . BASE_URL . "?c=Admin&a=productos");
        exit;
    }

    // 🎨 GESTIÓN DE VARIANTES
    public function variantes() {
        $sql = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo,
                    a.Foto AS FotoPrincipal,
                    a.ID_Categoria,
                    a.ID_SubCategoria,
                    a.ID_Genero,
                    cat.N_Categoria,
                    gen.N_Genero,
                    s.SubCategoria,
                    COUNT(p.ID_Producto) AS TotalVariantes,
                    GROUP_CONCAT(DISTINCT col.N_Color SEPARATOR ', ') AS Colores,
                    GROUP_CONCAT(DISTINCT t.N_Talla SEPARATOR ', ') AS Tallas
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN color col ON col.ID_Color = p.ID_Color
                LEFT JOIN talla t ON t.ID_Talla = p.ID_Talla
                LEFT JOIN categoria cat ON cat.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero gen ON gen.ID_Genero = a.ID_Genero
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.ID_Categoria, a.ID_SubCategoria, a.ID_Genero, cat.N_Categoria, gen.N_Genero, s.SubCategoria
                ORDER BY a.N_Articulo ASC";

        $productos = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Obtener datos para filtros
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcategorias = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);
        $colores = $this->db->query("SELECT * FROM color ORDER BY N_Color")->fetchAll(PDO::FETCH_ASSOC);
        $tallas = $this->db->query("SELECT * FROM talla ORDER BY N_Talla")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // 🔄 OBTENER SUBCATEGORÍAS POR CATEGORÍA Y GÉNERO (AJAX)
    public function getSubcategoriasByCategoria() {
        $id_categoria = $_GET['id_categoria'] ?? null;
        $id_genero = $_GET['id_genero'] ?? null;
        
        if (!$id_categoria) {
            echo json_encode([]);
            return;
        }
        
        $sql = "SELECT s.ID_SubCategoria, s.SubCategoria 
                FROM subcategoria s 
                WHERE s.ID_Categoria = ?";
        
        $params = [$id_categoria];
        
        if ($id_genero) {
            if ($id_genero == 1) {
                $sql .= " AND s.SubCategoria NOT IN ('Lenceria')";
            }
            elseif ($id_genero == 2) {
                $sql .= " AND s.SubCategoria NOT IN ('Boxer')";
            }
            elseif ($id_genero == 3) {
                $sql .= " AND s.SubCategoria NOT IN ('Lenceria', 'Boxer')";
            }
        }
        
        $sql .= " ORDER BY s.SubCategoria";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $subcategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($subcategorias);
    }

    // 👥 GESTIÓN DE USUARIOS
    public function usuarios() {
        $sql = "SELECT u.*, r.Roles, td.Documento 
                FROM usuario u 
                LEFT JOIN rol r ON u.ID_Rol = r.ID_Rol 
                LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD 
                ORDER BY u.Nombre, u.Apellido";
        $usuarios = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $roles = $this->db->query("SELECT * FROM rol ORDER BY Roles")->fetchAll(PDO::FETCH_ASSOC);
        $tiposDocumento = $this->db->query("SELECT * FROM tipo_documento ORDER BY Documento")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // ➕ AGREGAR USUARIO
    public function agregarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nombre = trim($_POST['Nombre'] ?? '');
            $apellido = trim($_POST['Apellido'] ?? '');
            $id_rol = (int)($_POST['ID_Rol'] ?? 3);
            $id_td = (int)($_POST['ID_TD'] ?? 1);
            $n_documento = (int)($_POST['N_Documento'] ?? 0);
            $correo = trim($_POST['Correo'] ?? '');
            $celular = trim($_POST['Celular'] ?? '');
            $contrasena = password_hash($_POST['Contrasena'] ?? '', PASSWORD_DEFAULT);
            $activo = isset($_POST['Activo']) ? 1 : 0;

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO usuario (Nombre, Apellido, ID_Rol, ID_TD, N_Documento, Correo, Celular, Contrasena, Activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nombre, $apellido, $id_rol, $id_td, $n_documento, $correo, $celular, $contrasena, $activo]);

                $_SESSION['msg'] = "✅ Usuario agregado correctamente";
                $_SESSION['msg_type'] = "success";
            } catch (PDOException $e) {
                $_SESSION['msg'] = "❌ Error al agregar usuario: " . $e->getMessage();
                $_SESSION['msg_type'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Admin&a=usuarios");
            exit;
        }
    }

    // ✏️ EDITAR USUARIO
    public function editarUsuario() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario = (int)($_POST['ID_Usuario'] ?? 0);
            $nombre = trim($_POST['Nombre'] ?? '');
            $apellido = trim($_POST['Apellido'] ?? '');
            $id_rol = (int)($_POST['ID_Rol'] ?? 3);
            $id_td = (int)($_POST['ID_TD'] ?? 1);
            $n_documento = (int)($_POST['N_Documento'] ?? 0);
            $correo = trim($_POST['Correo'] ?? '');
            $celular = trim($_POST['Celular'] ?? '');
            $activo = isset($_POST['Activo']) ? 1 : 0;

            try {
                $sql = "UPDATE usuario SET 
                        Nombre = ?, Apellido = ?, ID_Rol = ?, ID_TD = ?, 
                        N_Documento = ?, Correo = ?, Celular = ?, Activo = ?
                        WHERE ID_Usuario = ?";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$nombre, $apellido, $id_rol, $id_td, $n_documento, $correo, $celular, $activo, $id_usuario]);

                $_SESSION['msg'] = "✅ Usuario actualizado correctamente";
                $_SESSION['msg_type'] = "success";
            } catch (PDOException $e) {
                $_SESSION['msg'] = "❌ Error al actualizar usuario: " . $e->getMessage();
                $_SESSION['msg_type'] = "danger";
            }

            header("Location: " . BASE_URL . "?c=Admin&a=usuarios");
            exit;
        }
    }

    // 🗑️ ELIMINAR USUARIO
    public function eliminarUsuario() {
        $id_usuario = (int)($_GET['id'] ?? 0);

        if ($id_usuario > 0) {
            try {
                $stmt = $this->db->prepare("DELETE FROM usuario WHERE ID_Usuario = ?");
                $stmt->execute([$id_usuario]);

                $_SESSION['msg'] = "✅ Usuario eliminado correctamente";
                $_SESSION['msg_type'] = "success";
            } catch (PDOException $e) {
                $_SESSION['msg'] = "❌ Error al eliminar usuario: " . $e->getMessage();
                $_SESSION['msg_type'] = "danger";
            }
        }

        header("Location: " . BASE_URL . "?c=Admin&a=usuarios");
        exit;
    }
}