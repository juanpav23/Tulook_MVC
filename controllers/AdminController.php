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

        if (!$rol || strtolower($rol) !== 'administrador') {
            header("Location: " . BASE_URL);
            exit;
        }
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

        include "views/admin/layout_admin.php";
    }

    // 🧾 FORMULARIO PRODUCTO BASE
    public function productoForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $articulo = null;

        if ($id) {
            $stmt = $this->db->prepare("SELECT * FROM articulo WHERE ID_Articulo = ?");
            $stmt->execute([$id]);
            $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos    = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcats    = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);
        $colors     = $this->db->query("SELECT * FROM color ORDER BY N_Color")->fetchAll(PDO::FETCH_ASSOC);
        $precios    = $this->db->query("SELECT * FROM precio ORDER BY Valor ASC")->fetchAll(PDO::FETCH_ASSOC);
        $tallas     = $this->db->query("SELECT * FROM talla ORDER BY N_Talla")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // 💾 GUARDAR O ACTUALIZAR PRODUCTO BASE
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

            // ⚙️ Manejo de la imagen (ruta manual o subida directa)
            $fotoFinal = $_POST['foto_actual'] ?? '';

            if (!empty($_FILES['foto']['name'])) {
                // Subida de archivo - usar la ruta generada si está disponible
                $nombreArchivo = basename($_FILES['foto']['name']);
                
                // Usar la ruta generada por el sistema de selección o crear una por defecto
                if (!empty($_POST['Ruta_Imagen'])) {
                    $rutaDestino = $_POST['Ruta_Imagen'];
                } else {
                    $rutaDestino = 'ImgProducto/' . $nombreArchivo;
                }

                // Crear directorio si no existe
                $directorio = dirname($rutaDestino);
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0777, true);
                }

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaDestino)) {
                    $fotoFinal = $rutaDestino;
                }
            } elseif (!empty($_POST['Ruta_Imagen'])) {
                // Usar la ruta generada por el sistema de selección
                $fotoFinal = trim($_POST['Ruta_Imagen']);
}

            // 🔍 Validar precio (para evitar errores de FK)
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

                $_SESSION['mensaje'] = $ok
                    ? "✅ Producto actualizado correctamente."
                    : "❌ Error al actualizar el producto.";
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

                $_SESSION['mensaje'] = $ok
                    ? "✅ Producto guardado correctamente."
                    : "❌ Error al guardar el producto.";
            }

            header("Location: " . BASE_URL . "?c=Admin&a=productos");
            exit;

        } catch (PDOException $e) {
            $_SESSION['mensaje'] = "⚠️ Error SQL: " . $e->getMessage();
            header("Location: " . BASE_URL . "?c=Admin&a=productos");
            exit;
        }
    }

    // ➕ GUARDAR NUEVA VARIANTE CON SUBIDA DE IMAGEN
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
                // Imagen subida correctamente, $foto ya contiene la ruta correcta
            }
        }

        $stmt = $this->db->prepare("
            INSERT INTO producto (ID_Articulo, ID_Talla, ID_Color, Foto, Porcentaje, Cantidad, Nombre_Producto)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$idArticulo, $idTalla, $idColor, $foto, $porcentaje, $cantidad, $nombreProducto]);

        header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
        exit;
    }

    // ✏️ EDITAR VARIANTE
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
                ? "Variante actualizada correctamente"
                : "Error al actualizar la variante";
            $_SESSION['msg_type'] = $success ? "success" : "danger";

        } catch (Exception $e) {
            $_SESSION['msg'] = "Error: " . $e->getMessage();
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
            $_SESSION['msg'] = "Variante eliminada correctamente";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "Error: ID de variante no válido";
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
                    COUNT(p.ID_Producto) AS TotalVariantes,
                    GROUP_CONCAT(DISTINCT c.N_Color SEPARATOR ', ') AS Colores,
                    GROUP_CONCAT(DISTINCT t.N_Talla SEPARATOR ', ') AS Tallas
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN color c ON c.ID_Color = p.ID_Color
                LEFT JOIN talla t ON t.ID_Talla = p.ID_Talla
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto
                ORDER BY a.N_Articulo ASC";

        $productos = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }
}








