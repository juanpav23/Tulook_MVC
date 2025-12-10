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

    // 🔥 MÉTODO PARA MANEJAR ACCIONES NO DEFINIDAS
    public function __call($name, $arguments) {
        error_log("⚠️ Intento de acceder a acción no definida: $name");
        
        // Redirigir al dashboard principal
        $_SESSION['msg'] = "La página solicitada no existe. Redirigiendo al dashboard.";
        $_SESSION['msg_type'] = "warning";
        
        header("Location: " . BASE_URL . "?c=Admin&a=index");
        exit;
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
    private function verificarVarianteDuplicada($idArticulo, $atributosData, $idExcluir = null) {
        $sql = "SELECT ID_Producto FROM producto WHERE ID_Articulo = ?";
        $params = [$idArticulo];
        
        // Construir consulta para verificar duplicados basada en atributos
        for ($i = 0; $i < count($atributosData); $i++) {
            $columna = "ID_Atributo" . ($i + 1);
            $valorColumna = "ValorAtributo" . ($i + 1);
            
            $sql .= " AND $columna = ? AND $valorColumna = ?";
            $params[] = $atributosData[$i]['id_tipo'];
            $params[] = $atributosData[$i]['valor'];
        }
        
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
                        p.Valor AS PrecioBase
                FROM articulo a
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                LEFT JOIN precio p ON p.ID_Precio = a.ID_Precio
                ORDER BY a.N_Articulo ASC";
        $articulos = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Obtener datos para filtros
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcategorias = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // 🧾 FORMULARIO PRODUCTO BASE
    public function productoForm() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        $articulo = null;
        $tieneVariantes = false;

        if ($id) {
            // CONSULTA CORREGIDA - SIN ID_COLOR EN ARTICULO
            $sql = "SELECT a.*, c.N_Categoria, g.N_Genero, s.SubCategoria, 
                        p.Valor AS PrecioBase
                    FROM articulo a
                    LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                    LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                    LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                    LEFT JOIN precio p ON p.ID_Precio = a.ID_Precio
                    WHERE a.ID_Articulo = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$articulo) {
                error_log("❌ No se encontró el artículo con ID: " . $id);
                $_SESSION['msg'] = "❌ No se encontró el producto con ID: " . $id;
                $_SESSION['msg_type'] = "danger";
                header("Location: " . BASE_URL . "?c=Admin&a=productos");
                exit;
            }

            // Verificar si tiene variantes
            $tieneVariantes = $this->tieneVariantes($id);
        }

        // Obtener datos para los selects
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos    = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcats    = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);
        $precios    = $this->db->query("SELECT * FROM precio ORDER BY Valor ASC")->fetchAll(PDO::FETCH_ASSOC);

        // Pasar variable a la vista
        $datosVista = [
            'articulo' => $articulo,
            'categorias' => $categorias,
            'generos' => $generos,
            'subcats' => $subcats,
            'precios' => $precios,
            'tieneVariantes' => $tieneVariantes
        ];

        extract($datosVista);
        include "views/admin/layout_admin.php";
    }

    // =======================================================
    // 🎯 OBTENER ATRIBUTOS POR SUBCATEGORÍA (AJAX)
    // =======================================================
    public function getAtributosBySubcategoria() {
        $idSubcategoria = $_GET['id_subcategoria'] ?? null;
        
        if (!$idSubcategoria) {
            echo json_encode([]);
            return;
        }
        
        // Obtener información de la subcategoría
        $sql = "SELECT s.SubCategoria, s.AtributosRequeridos FROM subcategoria s WHERE s.ID_SubCategoria = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idSubcategoria]);
        $subcategoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$subcategoria || empty($subcategoria['AtributosRequeridos'])) {
            echo json_encode([]);
            return;
        }
        
        // VERIFICAR SI ES GORRAS, RELOJES O GAFAS
        $esProductoEspecial = false;
        if (isset($subcategoria['SubCategoria'])) {
            $esProductoEspecial = in_array(
                strtolower($subcategoria['SubCategoria']), 
                ['gorras', 'relojes', 'gafas', 'gorra', 'reloj', 'gafa']
            );
        }
        
        // Si es producto especial, forzar solo color
        if ($esProductoEspecial) {
            $atributosIds = ['2']; // Solo color
        } else {
            $atributosIds = explode(',', $subcategoria['AtributosRequeridos']);
        }
        
        $atributosData = [];
        
        foreach ($atributosIds as $idTipoAtributo) {
            $idTipoAtributo = trim($idTipoAtributo);
            if (empty($idTipoAtributo)) continue;
            
            $sql = "SELECT ta.ID_TipoAtributo, ta.Nombre, ta.Descripcion 
                    FROM tipo_atributo ta 
                    WHERE ta.ID_TipoAtributo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idTipoAtributo]);
            $tipoAtributo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($tipoAtributo) {
                // Si es el atributo de Color (ID_TipoAtributo = 2)
                if ($tipoAtributo['ID_TipoAtributo'] == 2) {
                    // Cargar colores desde la tabla color
                    $sql = "SELECT ID_Color as ID_AtributoValor, N_Color as Valor, CodigoHex 
                            FROM color 
                            ORDER BY N_Color ASC";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute();
                    $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    // Agregar información del código hexadecimal
                    foreach ($valores as &$valor) {
                        $valor['CodigoHex'] = $valor['CodigoHex'] ?? '#FFFFFF';
                    }
                } else {
                    // Para otros atributos
                    $sql = "SELECT av.ID_AtributoValor, av.Valor, av.Orden 
                            FROM atributo_valor av 
                            WHERE av.ID_TipoAtributo = ? AND av.Activo = 1 
                            ORDER BY av.Orden ASC";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$idTipoAtributo]);
                    $valores = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                
                $atributosData[] = [
                    'tipo' => $tipoAtributo,
                    'valores' => $valores,
                    'esProductoEspecial' => $esProductoEspecial // Flag para frontend
                ];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($atributosData);
    }

    // 💾 GUARDAR O ACTUALIZAR PRODUCTO BASE
    public function saveProducto() {
        try {
            $id = isset($_POST['ID_Articulo']) ? (int)$_POST['ID_Articulo'] : null;
            $nombre = trim($_POST['N_Articulo'] ?? '');
            $cat = (int)($_POST['ID_Categoria'] ?? 0);
            $subcat = (int)($_POST['ID_SubCategoria'] ?? 0);
            $gen = (int)($_POST['ID_Genero'] ?? 0);
            $idPrecio = (int)($_POST['ID_Precio'] ?? 0);
            $activo = isset($_POST['Activo']) ? 1 : 0;

            // Si es edición y tiene variantes, verificar que no se modifiquen categorías
            if ($id) {
                $tieneVariantes = $this->tieneVariantes($id);
                
                if ($tieneVariantes) {
                    // Obtener datos actuales del artículo
                    $stmt = $this->db->prepare("SELECT ID_Categoria, ID_SubCategoria, ID_Genero FROM articulo WHERE ID_Articulo = ?");
                    $stmt->execute([$id]);
                    $articuloActual = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Verificar si se intentan modificar categorías
                    if ($articuloActual && 
                        ($articuloActual['ID_Categoria'] != $cat || 
                        $articuloActual['ID_SubCategoria'] != $subcat || 
                        $articuloActual['ID_Genero'] != $gen)) {
                        
                        $_SESSION['msg'] = "❌ No se pueden modificar la categoría, subcategoría o género porque este producto ya tiene variantes creadas.";
                        $_SESSION['msg_type'] = "warning";
                        header("Location: " . BASE_URL . "?c=Admin&a=productoForm&id=$id");
                        exit;
                    }
                }
            }
            
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
                        ID_Precio = ?, 
                        Activo = ?
                    WHERE ID_Articulo = ?
                ");
                $ok = $update->execute([
                    $nombre, $fotoFinal, $cat, $subcat, $gen,
                    $idPrecio, $activo, $id
                ]);

                $_SESSION['msg'] = $ok
                    ? "✅ Producto actualizado correctamente."
                    : "❌ Error al actualizar el producto.";
                $_SESSION['msg_type'] = $ok ? "success" : "danger";
            } else {
                $insert = $this->db->prepare("
                    INSERT INTO articulo 
                        (N_Articulo, Foto, ID_Categoria, ID_SubCategoria, ID_Genero, ID_Precio, Activo)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $ok = $insert->execute([
                    $nombre, $fotoFinal, $cat, $subcat, $gen,
                    $idPrecio, $activo
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

    // ➕ GUARDAR NUEVA VARIANTE
    public function agregarVariante() {
        $idArticulo = (int)($_POST['ID_Articulo'] ?? 0);
        $porcentaje = (float)($_POST['Porcentaje'] ?? 0);
        $activo = isset($_POST['Activo']) ? 1 : 0;
        
        // VALIDACIÓN DE PORCENTAJE (NEGATIVO O POSITIVO)
        if ($porcentaje < -90 || $porcentaje > 300) {
            $_SESSION['msg'] = "❌ Error: El porcentaje debe estar entre -90% (descuento) y +300% (aumento).";
            $_SESSION['msg_type'] = "danger";
            header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
            exit;
        }
        
        // CAMBIO: Establecer límite de 99,999
        $cantidad = isset($_POST['Cantidad']) ? (int)$_POST['Cantidad'] : 0;
        if ($cantidad > 99999) {
            $cantidad = 99999;
        }
        if ($cantidad < 0) {
            $cantidad = 0;
        }
        
        $foto = trim($_POST['Foto'] ?? '');
        $nombreProducto = trim($_POST['Nombre_Producto'] ?? '');

        if ($idArticulo <= 0) {
            header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
            exit;
        }

        // Obtener información del artículo para saber la subcategoría
        $sql = "SELECT a.ID_SubCategoria, sc.AtributosRequeridos 
                FROM articulo a 
                LEFT JOIN subcategoria sc ON sc.ID_SubCategoria = a.ID_SubCategoria 
                WHERE a.ID_Articulo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idArticulo]);
        $articuloInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$articuloInfo) {
            $_SESSION['msg'] = "❌ Error: Artículo no encontrado";
            $_SESSION['msg_type'] = "danger";
            header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
            exit;
        }

        // Procesar atributos dinámicos
        $atributosIds = explode(',', $articuloInfo['AtributosRequeridos'] ?? '');
        $atributosData = [];
        
        for ($i = 0; $i < 3; $i++) {
            $atributoKey = "atributo" . ($i + 1);
            $valorKey = "valor_atributo" . ($i + 1);
            
            if (isset($atributosIds[$i])) {
                $idTipoAtributo = trim($atributosIds[$i]);
                $valorAtributo = $_POST[$valorKey] ?? '';
                
                $atributosData[] = [
                    'id_tipo' => $idTipoAtributo,
                    'valor' => $valorAtributo
                ];
            }
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

        // Verificar variante duplicada
        $varianteDuplicada = $this->verificarVarianteDuplicada($idArticulo, $atributosData);
        
        if ($varianteDuplicada) {
            $_SESSION['msg'] = "❌ Ya existe una variante con esta combinación de atributos.";
            $_SESSION['msg_type'] = "warning";
            header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
            exit;
        }

        // ✅ INSERT ACTUALIZADO CON ATRIBUTOS DINÁMICOS
        $sql = "INSERT INTO producto (ID_Articulo, Foto, Porcentaje, Cantidad, Nombre_Producto, Activo";
        $values = "VALUES (?, ?, ?, ?, ?, ?";
        $insertParams = [$idArticulo, $foto, $porcentaje, $cantidad, $nombreProducto, $activo];
        
        // Agregar atributos dinámicos
        for ($i = 0; $i < count($atributosData); $i++) {
            $columnaId = "ID_Atributo" . ($i + 1);
            $columnaValor = "ValorAtributo" . ($i + 1);
            
            $sql .= ", $columnaId, $columnaValor";
            $values .= ", ?, ?";
            $insertParams[] = $atributosData[$i]['id_tipo'];
            $insertParams[] = $atributosData[$i]['valor'];
        }
        
        $sql .= ") " . $values . ")";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($insertParams);

        $_SESSION['msg'] = "✅ Variante agregada correctamente.";
        $_SESSION['msg_type'] = "success";

        header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=$idArticulo");
        exit;
    }

    // 🔄 ACTIVAR/DESACTIVAR VARIANTE
    public function toggleVariante() {
        $idProducto = (int)($_GET['id'] ?? 0);
        $idArticulo = (int)($_GET['articulo'] ?? 0);
        
        if ($idProducto > 0) {
            // Obtener estado actual
            $stmt = $this->db->prepare("SELECT Activo FROM producto WHERE ID_Producto = ?");
            $stmt->execute([$idProducto]);
            $currentState = $stmt->fetchColumn();
            
            // Cambiar estado
            $newState = $currentState ? 0 : 1;
            $update = $this->db->prepare("UPDATE producto SET Activo = ? WHERE ID_Producto = ?");
            $update->execute([$newState, $idProducto]);
            
            $_SESSION['msg'] = $newState 
                ? "✅ Variante activada correctamente" 
                : "✅ Variante desactivada correctamente";
            $_SESSION['msg_type'] = "success";
        } else {
            $_SESSION['msg'] = "❌ Error: ID de variante no válido";
            $_SESSION['msg_type'] = "danger";
        }
        
        header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=" . $idArticulo);
        exit;
    }

    // 🔄 ACTIVAR/DESACTIVAR VARIANTE DESDE GESTIÓN DE VARIANTES
    public function toggleVarianteDesdeGestion() {
        $idProducto = (int)($_GET['id'] ?? 0);
        
        if ($idProducto > 0) {
            // Obtener estado actual y ID del artículo
            $stmt = $this->db->prepare("SELECT Activo, ID_Articulo FROM producto WHERE ID_Producto = ?");
            $stmt->execute([$idProducto]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                // Cambiar estado
                $newState = $result['Activo'] ? 0 : 1;
                $update = $this->db->prepare("UPDATE producto SET Activo = ? WHERE ID_Producto = ?");
                $update->execute([$newState, $idProducto]);
                
                $_SESSION['msg'] = $newState 
                    ? "✅ Variante activada correctamente" 
                    : "✅ Variante desactivada correctamente";
                $_SESSION['msg_type'] = "success";
            }
        } else {
            $_SESSION['msg'] = "❌ Error: ID de variante no válido";
            $_SESSION['msg_type'] = "danger";
        }
        
        header("Location: " . BASE_URL . "?c=Admin&a=variantes");
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
                        p.Valor AS PrecioBase
                FROM articulo a
                LEFT JOIN categoria c ON c.ID_Categoria = a.ID_Categoria
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                LEFT JOIN genero g ON g.ID_Genero = a.ID_Genero
                LEFT JOIN precio p ON p.ID_Precio = a.ID_Precio
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

    // ✏️ EDITAR VARIANTE
    public function editarVariante() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "?c=Admin&a=productos");
            exit;
        }

        try {
            $idProducto = $_POST['ID_Producto'] ?? null;
            $idArticulo = $_POST['ID_Articulo'] ?? null;
            $porcentaje = (float)($_POST['Porcentaje'] ?? 0);
            $activo = isset($_POST['Activo']) ? (int)$_POST['Activo'] : 0;
            
            // VALIDACIÓN DE PORCENTAJE (NEGATIVO O POSITIVO)
            if ($porcentaje < -90 || $porcentaje > 300) {
                $_SESSION['msg'] = "❌ Error: El porcentaje debe estar entre -90% y +300%.";
                $_SESSION['msg_type'] = "danger";
                header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=" . ($idArticulo ?? ''));
                exit;
            }
            
            // CAMBIO: Establecer límite de 99,999
            $cantidad = isset($_POST['Cantidad']) ? (int)$_POST['Cantidad'] : 0;
            if ($cantidad > 99999) {
                $cantidad = 99999;
            }
            if ($cantidad < 0) {
                $cantidad = 0;
            }
            
            $foto = $_POST['Foto'] ?? '';
            $nombreProducto = trim($_POST['Nombre_Producto'] ?? '');

            if (!$idProducto || !$idArticulo) {
                throw new Exception("Datos incompletos");
            }

            // Obtener información del artículo para saber la subcategoría
            $sql = "SELECT a.ID_SubCategoria, sc.AtributosRequeridos 
                    FROM articulo a 
                    LEFT JOIN subcategoria sc ON sc.ID_SubCategoria = a.ID_SubCategoria 
                    WHERE a.ID_Articulo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idArticulo]);
            $articuloInfo = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$articuloInfo) {
                throw new Exception("Artículo no encontrado");
            }

            // Procesar atributos dinámicos
            $atributosIds = explode(',', $articuloInfo['AtributosRequeridos'] ?? '');
            $atributosData = [];
            
            for ($i = 0; $i < 3; $i++) {
                $atributoKey = "atributo" . ($i + 1);
                $valorKey = "valor_atributo" . ($i + 1);
                
                if (isset($atributosIds[$i])) {
                    $idTipoAtributo = trim($atributosIds[$i]);
                    $valorAtributo = $_POST[$valorKey] ?? '';
                    
                    $atributosData[] = [
                        'id_tipo' => $idTipoAtributo,
                        'valor' => $valorAtributo
                    ];
                }
            }

            // Verificar variante duplicada (excluyendo la actual)
            $varianteDuplicada = $this->verificarVarianteDuplicada($idArticulo, $atributosData, $idProducto);
            
            if ($varianteDuplicada) {
                $_SESSION['msg'] = "❌ Ya existe otra variante con esta combinación de atributos.";
                $_SESSION['msg_type'] = "warning";
                header("Location: " . BASE_URL . "?c=Admin&a=detalleProducto&id=" . $idArticulo);
                exit;
            }

            // ✅ UPDATE ACTUALIZADO CON ATRIBUTOS DINÁMICOS
            $sql = "UPDATE producto SET 
                        Porcentaje = ?, 
                        Cantidad = ?, 
                        Foto = ?,
                        Nombre_Producto = ?,
                        Activo = ?";
            
            $updateParams = [$porcentaje, $cantidad, $foto, $nombreProducto, $activo];
            
            // Agregar atributos dinámicos
            for ($i = 0; $i < count($atributosData); $i++) {
                $columnaId = "ID_Atributo" . ($i + 1);
                $columnaValor = "ValorAtributo" . ($i + 1);
                
                $sql .= ", $columnaId = ?, $columnaValor = ?";
                $updateParams[] = $atributosData[$i]['id_tipo'];
                $updateParams[] = $atributosData[$i]['valor'];
            }
            
            $sql .= " WHERE ID_Producto = ? AND ID_Articulo = ?";
            $updateParams[] = $idProducto;
            $updateParams[] = $idArticulo;
            
            $stmt = $this->db->prepare($sql);
            $success = $stmt->execute($updateParams);

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
            SELECT a.*, p.Valor AS PrecioBase
            FROM articulo a
            LEFT JOIN precio p ON a.ID_Precio = p.ID_Precio
            WHERE a.ID_Articulo = ?
        ");
        $stmt->execute([$id]);
        $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

        // Obtener información de atributos requeridos - CON VALIDACIÓN ESPECÍFICA
        $atributosData = [];
        if ($articulo) {
            // Obtener el nombre de la subcategoría para verificar si es Gorras, Relojes o Gafas
            $sqlSubcat = "SELECT s.SubCategoria, s.AtributosRequeridos 
                        FROM subcategoria s 
                        WHERE s.ID_SubCategoria = ?";
            $stmtSubcat = $this->db->prepare($sqlSubcat);
            $stmtSubcat->execute([$articulo['ID_SubCategoria']]);
            $subcategoriaInfo = $stmtSubcat->fetch(PDO::FETCH_ASSOC);
            
            if ($subcategoriaInfo && !empty($subcategoriaInfo['AtributosRequeridos'])) {
                // VERIFICAR SI ES GORRAS, RELOJES O GAFAS
                $esProductoEspecial = in_array(
                    strtolower($subcategoriaInfo['SubCategoria']), 
                    ['gorras', 'relojes', 'gafas', 'gorra', 'reloj', 'gafa']
                );
                
                // Si es producto especial y ya tiene solo color, mantenerlo así
                if ($esProductoEspecial && $subcategoriaInfo['AtributosRequeridos'] === '2') {
                    // Solo cargar atributo de Color (ID 2)
                    $sqlTipo = "SELECT ta.ID_TipoAtributo, ta.Nombre, ta.Descripcion 
                            FROM tipo_atributo ta 
                            WHERE ta.ID_TipoAtributo = 2";
                    $stmtTipo = $this->db->prepare($sqlTipo);
                    $stmtTipo->execute();
                    $tipoAtributo = $stmtTipo->fetch(PDO::FETCH_ASSOC);
                    
                    if ($tipoAtributo) {
                        // Cargar colores desde la tabla color
                        $sqlValores = "SELECT ID_Color as ID_AtributoValor, N_Color as Valor, CodigoHex 
                                    FROM color 
                                    ORDER BY N_Color ASC";
                        $stmtValores = $this->db->prepare($sqlValores);
                        $stmtValores->execute();
                        $valores = $stmtValores->fetchAll(PDO::FETCH_ASSOC);
                        
                        // Agregar información del código hexadecimal
                        foreach ($valores as &$valor) {
                            $valor['CodigoHex'] = $valor['CodigoHex'] ?? '#FFFFFF';
                        }
                        
                        $atributosData[] = [
                            'tipo' => $tipoAtributo,
                            'valores' => $valores
                        ];
                    }
                } else {
                    // Procesamiento normal para otros productos
                    $atributosIds = explode(',', $subcategoriaInfo['AtributosRequeridos']);
                    
                    foreach ($atributosIds as $idTipoAtributo) {
                        $idTipoAtributo = trim($idTipoAtributo);
                        if (empty($idTipoAtributo)) continue;
                        
                        $sqlTipo = "SELECT ta.ID_TipoAtributo, ta.Nombre, ta.Descripcion 
                                FROM tipo_atributo ta 
                                WHERE ta.ID_TipoAtributo = ?";
                        $stmtTipo = $this->db->prepare($sqlTipo);
                        $stmtTipo->execute([$idTipoAtributo]);
                        $tipoAtributo = $stmtTipo->fetch(PDO::FETCH_ASSOC);
                        
                        if ($tipoAtributo) {
                            // 🎨 Si es el atributo de Color (ID_TipoAtributo = 2)
                            if ($tipoAtributo['ID_TipoAtributo'] == 2) {
                                $sqlValores = "SELECT ID_Color as ID_AtributoValor, N_Color as Valor, CodigoHex 
                                            FROM color 
                                            ORDER BY N_Color ASC";
                                $stmtValores = $this->db->prepare($sqlValores);
                                $stmtValores->execute();
                                $valores = $stmtValores->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($valores as &$valor) {
                                    $valor['CodigoHex'] = $valor['CodigoHex'] ?? '#FFFFFF';
                                }
                            } else {
                                // Para otros atributos
                                $sqlValores = "SELECT av.ID_AtributoValor, av.Valor, av.Orden 
                                            FROM atributo_valor av 
                                            WHERE av.ID_TipoAtributo = ? AND av.Activo = 1 
                                            ORDER BY av.Orden ASC";
                                $stmtValores = $this->db->prepare($sqlValores);
                                $stmtValores->execute([$idTipoAtributo]);
                                $valores = $stmtValores->fetchAll(PDO::FETCH_ASSOC);
                            }
                            
                            $atributosData[] = [
                                'tipo' => $tipoAtributo,
                                'valores' => $valores
                            ];
                        }
                    }
                }
            }
        }

        // Resto del código permanece igual...
        $stmtV = $this->db->prepare("
            SELECT p.*
            FROM producto p
            WHERE p.ID_Articulo = ?
            ORDER BY p.ID_Producto DESC
        ");
        $stmtV->execute([$id]);
        $variantes = $stmtV->fetchAll(PDO::FETCH_ASSOC);

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
        // Consulta principal para productos base
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
                    COALESCE(SUM(p.Cantidad), 0) AS StockTotal
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN categoria cat ON cat.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero gen ON gen.ID_Genero = a.ID_Genero
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.ID_Categoria, a.ID_SubCategoria, a.ID_Genero, cat.N_Categoria, gen.N_Genero, s.SubCategoria
                ORDER BY a.N_Articulo ASC";

        $productos = $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // Para cada producto, obtener información de atributos disponibles - CORREGIDO
        foreach ($productos as &$producto) {
            // Obtener todos los atributos únicos para este producto
            $sqlAtributos = "SELECT 
                                ta1.Nombre as nombre_atributo1,
                                p.ValorAtributo1 as valor_atributo1,
                                ta2.Nombre as nombre_atributo2,
                                p.ValorAtributo2 as valor_atributo2,
                                ta3.Nombre as nombre_atributo3,
                                p.ValorAtributo3 as valor_atributo3
                            FROM producto p
                            LEFT JOIN tipo_atributo ta1 ON ta1.ID_TipoAtributo = p.ID_Atributo1
                            LEFT JOIN tipo_atributo ta2 ON ta2.ID_TipoAtributo = p.ID_Atributo2
                            LEFT JOIN tipo_atributo ta3 ON ta3.ID_TipoAtributo = p.ID_Atributo3
                            WHERE p.ID_Articulo = ?
                            AND p.Activo = 1";
            
            $stmtAtributos = $this->db->prepare($sqlAtributos);
            $stmtAtributos->execute([$producto['ID_Articulo']]);
            $atributosData = $stmtAtributos->fetchAll(PDO::FETCH_ASSOC);
            
            // Procesar atributos disponibles - FORMA CORREGIDA
            $atributosDisponibles = [];
            
            foreach ($atributosData as $fila) {
                // Atributo 1
                if (!empty($fila['nombre_atributo1']) && !empty($fila['valor_atributo1'])) {
                    $atributosDisponibles[] = [
                        'nombre' => $fila['nombre_atributo1'],
                        'valor' => $fila['valor_atributo1']
                    ];
                }
                
                // Atributo 2
                if (!empty($fila['nombre_atributo2']) && !empty($fila['valor_atributo2'])) {
                    $atributosDisponibles[] = [
                        'nombre' => $fila['nombre_atributo2'],
                        'valor' => $fila['valor_atributo2']
                    ];
                }
                
                // Atributo 3
                if (!empty($fila['nombre_atributo3']) && !empty($fila['valor_atributo3'])) {
                    $atributosDisponibles[] = [
                        'nombre' => $fila['nombre_atributo3'],
                        'valor' => $fila['valor_atributo3']
                    ];
                }
            }
            
            // Eliminar duplicados manteniendo la estructura
            $atributosUnicos = [];
            $seen = [];
            
            foreach ($atributosDisponibles as $atributo) {
                $key = $atributo['nombre'] . '|' . $atributo['valor'];
                if (!isset($seen[$key])) {
                    $seen[$key] = true;
                    $atributosUnicos[] = $atributo;
                }
            }
            
            $producto['AtributosDisponibles'] = $atributosUnicos;
            
            // DEBUG: Para verificar los datos (puedes eliminar esto después)
            if (empty($atributosUnicos)) {
                error_log("⚠️ Producto ID {$producto['ID_Articulo']} - No se encontraron atributos");
            } else {
                error_log("✅ Producto ID {$producto['ID_Articulo']} - Atributos encontrados: " . count($atributosUnicos));
            }
        }
        unset($producto); // Liberar referencia

        // Obtener datos para filtros
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcategorias = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);

        include "views/admin/layout_admin.php";
    }

    // 🔍 BUSCAR VARIANTES CON FILTROS - SIMPLIFICADO
    public function buscarVariantes() {
        $termino = trim($_GET['q'] ?? '');
        $categoria = $_GET['categoria'] ?? '';
        $genero = $_GET['genero'] ?? '';
        $subcategoria = $_GET['subcategoria'] ?? '';
        $estado = $_GET['estado'] ?? '';
        
        // Consulta base para búsqueda
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
                    COALESCE(SUM(p.Cantidad), 0) AS StockTotal
                FROM articulo a
                LEFT JOIN producto p ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN categoria cat ON cat.ID_Categoria = a.ID_Categoria
                LEFT JOIN genero gen ON gen.ID_Genero = a.ID_Genero
                LEFT JOIN subcategoria s ON s.ID_SubCategoria = a.ID_SubCategoria
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($termino)) {
            $sql .= " AND (a.N_Articulo LIKE ? OR cat.N_Categoria LIKE ? OR s.SubCategoria LIKE ? OR gen.N_Genero LIKE ?)";
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
        
        $sql .= " GROUP BY a.ID_Articulo, a.N_Articulo, a.Foto, a.ID_Categoria, a.ID_SubCategoria, a.ID_Genero, cat.N_Categoria, gen.N_Genero, s.SubCategoria
                ORDER BY a.N_Articulo ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Para cada producto encontrado, obtener información de atributos disponibles
        foreach ($productos as &$producto) {
    // Obtener todos los atributos únicos para este producto
    $sqlAtributos = "SELECT 
                        ta1.Nombre as nombre_atributo1,
                        p.ValorAtributo1 as valor_atributo1,
                        ta2.Nombre as nombre_atributo2,
                        p.ValorAtributo2 as valor_atributo2,
                        ta3.Nombre as nombre_atributo3,
                        p.ValorAtributo3 as valor_atributo3
                    FROM producto p
                    LEFT JOIN tipo_atributo ta1 ON ta1.ID_TipoAtributo = p.ID_Atributo1
                    LEFT JOIN tipo_atributo ta2 ON ta2.ID_TipoAtributo = p.ID_Atributo2
                    LEFT JOIN tipo_atributo ta3 ON ta3.ID_TipoAtributo = p.ID_Atributo3
                    WHERE p.ID_Articulo = ?
                    AND p.Activo = 1";
    
    $stmtAtributos = $this->db->prepare($sqlAtributos);
    $stmtAtributos->execute([$producto['ID_Articulo']]);
    $atributosData = $stmtAtributos->fetchAll(PDO::FETCH_ASSOC);
    
    // Procesar atributos disponibles - FORMA CORREGIDA
    $atributosDisponibles = [];
    
        foreach ($atributosData as $fila) {
            // Atributo 1
            if (!empty($fila['nombre_atributo1']) && !empty($fila['valor_atributo1'])) {
                $atributosDisponibles[] = [
                    'nombre' => $fila['nombre_atributo1'],
                    'valor' => $fila['valor_atributo1']
                ];
            }
            
            // Atributo 2
            if (!empty($fila['nombre_atributo2']) && !empty($fila['valor_atributo2'])) {
                $atributosDisponibles[] = [
                    'nombre' => $fila['nombre_atributo2'],
                    'valor' => $fila['valor_atributo2']
                ];
            }
            
            // Atributo 3
            if (!empty($fila['nombre_atributo3']) && !empty($fila['valor_atributo3'])) {
                $atributosDisponibles[] = [
                    'nombre' => $fila['nombre_atributo3'],
                    'valor' => $fila['valor_atributo3']
                ];
            }
        }
        
        // Eliminar duplicados manteniendo la estructura
        $atributosUnicos = [];
        $seen = [];
        
        foreach ($atributosDisponibles as $atributo) {
            $key = $atributo['nombre'] . '|' . $atributo['valor'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $atributosUnicos[] = $atributo;
            }
        }
        
        $producto['AtributosDisponibles'] = $atributosUnicos;
    }
    unset($producto); // Liberar referencia

        // Obtener datos para filtros
        $categorias = $this->db->query("SELECT * FROM categoria ORDER BY N_Categoria")->fetchAll(PDO::FETCH_ASSOC);
        $generos = $this->db->query("SELECT * FROM genero ORDER BY N_Genero")->fetchAll(PDO::FETCH_ASSOC);
        $subcategorias = $this->db->query("SELECT * FROM subcategoria ORDER BY SubCategoria")->fetchAll(PDO::FETCH_ASSOC);
        
        // Pasar datos a la vista
        $terminoBusqueda = $termino;
        $filtrosAplicados = $_GET;
        
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

    // 🔍 VERIFICAR SI UN ARTÍCULO TIENE VARIANTES
    private function tieneVariantes($idArticulo) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM producto WHERE ID_Articulo = ?");
        $stmt->execute([$idArticulo]);
        return $stmt->fetchColumn() > 0;
    }

    private function verificarYDesactivarStockCero() {
        // Desactivar productos con stock 0
        $sql = "UPDATE producto SET Activo = 0 WHERE Cantidad <= 0 AND Activo = 1";
        $this->db->exec($sql);
        
        // Verificar artículos base: si todas sus variantes están inactivas, desactivar el artículo
        $sql = "UPDATE articulo a 
                SET a.Activo = 0 
                WHERE a.Activo = 1 
                AND NOT EXISTS (
                    SELECT 1 FROM producto p 
                    WHERE p.ID_Articulo = a.ID_Articulo 
                    AND p.Activo = 1
                )";
        $this->db->exec($sql);
    }


}