<?php
require_once "models/Favorito.php";
require_once "models/Database.php";

class FavoritoController {
    private $db;
    private $model;

    public function __construct($db = null) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db ?: (new Database())->getConnection();
        $this->model = new Favorito($this->db);
    }

    // Lista de favoritos
    public function index() {
        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $idUsuario = (int)$_SESSION['ID_Usuario'];
        $favoritos = $this->model->getByUser($idUsuario);
        
        // Obtener categorías de forma directa
        $categorias = $this->obtenerCategorias();

        require_once "views/productos/megusta.php";
    }

    // Método para obtener categorías
    private function obtenerCategorias() {
        try {
            $sql = "SELECT c.ID_Categoria, c.N_Categoria 
                    FROM categoria c 
                    WHERE c.Activo = 1 
                    ORDER BY c.N_Categoria";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error obteniendo categorías: " . $e->getMessage());
            return [];
        }
    }

    // ✅ NUEVO: Método para verificar estado del favorito (AJAX)
    public function verificarEstado() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Método no permitido']);
            exit;
        }

        if (!isset($_SESSION['ID_Usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Usuario no autenticado']);
            exit;
        }

        try {
            $idUsuario = (int)$_SESSION['ID_Usuario'];
            $idProducto = $_POST['id_producto'] ?? null;
            $idArticulo = $_POST['id_articulo'] ?? null;

            if (!$idProducto && !$idArticulo) {
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
                exit;
            }

            $esFavorito = $this->model->existsFor($idUsuario, $idProducto, $idArticulo);

            echo json_encode([
                'success' => true,
                'esFavorito' => $esFavorito
            ]);

        } catch (Exception $e) {
            error_log("Error en verificarEstado: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'error' => 'Error al verificar favorito'
            ]);
        }
        exit;
    }

    // toggle con AJAX - VERSIÓN CORREGIDA
    public function toggleAjax() {
        if (!isset($_SESSION['ID_Usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }

        $idUsuario = (int)$_SESSION['ID_Usuario'];
        
        // Obtener IDs del POST
        $idProducto = !empty($_POST['id_producto']) ? (int)$_POST['id_producto'] : null;
        $idArticulo = !empty($_POST['id_articulo']) ? (int)$_POST['id_articulo'] : null;

        // Validar que al menos uno tenga valor
        if (!$idProducto && !$idArticulo) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No se especificó producto o artículo']);
            exit;
        }

        try {
            // Verificar si existe
            $exists = $this->model->existsFor($idUsuario, $idProducto, $idArticulo);
            
            if ($exists) {
                // Eliminar de favoritos
                $result = $this->model->remove($idUsuario, $idProducto, $idArticulo);
                echo json_encode([
                    'success' => $result, 
                    'action' => 'removed',
                    'message' => 'Eliminado de favoritos',
                    'esFavorito' => false
                ]);
            } else {
                // Agregar a favoritos
                $result = $this->model->add($idUsuario, $idProducto, $idArticulo);
                echo json_encode([
                    'success' => $result, 
                    'action' => 'added',
                    'message' => 'Agregado a favoritos',
                    'esFavorito' => true
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en toggleAjax: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error del servidor']);
        }
        exit;
    }

    // toggle normal (para compatibilidad)
    public function toggle() {
        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $idUsuario = (int)$_SESSION['ID_Usuario'];
        
        $idProducto = !empty($_POST['id_producto']) ? (int)$_POST['id_producto'] : null;
        $idArticulo = !empty($_POST['id_articulo']) ? (int)$_POST['id_articulo'] : null;

        if (!$idProducto && !$idArticulo) {
            $_SESSION['mensaje_error'] = "No se especificó producto o artículo";
            header("Location: " . BASE_URL);
            exit;
        }

        try {
            $exists = $this->model->existsFor($idUsuario, $idProducto, $idArticulo);
            
            if ($exists) {
                $this->model->remove($idUsuario, $idProducto, $idArticulo);
                $_SESSION['mensaje'] = "❌ Eliminado de favoritos";
            } else {
                $this->model->add($idUsuario, $idProducto, $idArticulo);
                $_SESSION['mensaje'] = "❤️ Agregado a favoritos";
            }
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = "Error al procesar favorito";
        }

        header("Location: " . ($_SERVER['HTTP_REFERER'] ?? BASE_URL));
        exit;
    }

    public function megusta() { 
        $this->index(); 
    }
}
?>