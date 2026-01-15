<?php
require_once "models/Favorito.php";

class FavoritoController {
    private $db;
    private $favoritoModel;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->db = $db;
        $this->favoritoModel = new Favorito($db);
    }

    private function verificarAutenticacion() {
        return isset($_SESSION['ID_Usuario']) && !empty($_SESSION['ID_Usuario']);
    }

    public function toggleFavorito() {
        if (!$this->verificarAutenticacion()) {
            echo json_encode([
                'success' => false, 
                'message' => 'Debes iniciar sesión para usar favoritos',
                'redirect' => BASE_URL . '?c=Usuario&a=login'
            ]);
            exit;
        }

        try {
            // Obtener ID del artículo
            $idArticulo = $_POST['id_articulo'] ?? $_GET['id_articulo'] ?? null;
            $idUsuario = $_SESSION['ID_Usuario'];
            
            if (!$idArticulo || !is_numeric($idArticulo)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'ID de artículo inválido'
                ]);
                exit;
            }

            $idArticulo = (int)$idArticulo;
            
            // Verificar si el artículo existe
            $sql = "SELECT ID_Articulo FROM articulo WHERE ID_Articulo = ? AND Activo = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idArticulo]);
            
            if ($stmt->rowCount() === 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'El artículo no existe o no está disponible'
                ]);
                exit;
            }

            // Verificar si ya es favorito
            $esFavorito = $this->favoritoModel->esFavorito($idUsuario, $idArticulo);
            
            if ($esFavorito) {
                $result = $this->favoritoModel->eliminarFavorito($idUsuario, $idArticulo);
                $accion = 'removed';
            } else {
                $result = $this->favoritoModel->agregarFavorito($idUsuario, $idArticulo);
                $accion = 'added';
            }

            $totalFavoritos = $this->favoritoModel->contarFavoritos($idUsuario);
            
            echo json_encode([
                'success' => $result['success'],
                'message' => $result['message'],
                'accion' => $accion,
                'esFavorito' => !$esFavorito,
                'totalFavoritos' => $totalFavoritos
            ]);
            
        } catch (Exception $e) {
            error_log("ERROR en toggleFavorito: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Error interno del servidor'
            ]);
        }
        exit;
    }

    public function verificarEstado() {
        if (!$this->verificarAutenticacion()) {
            echo json_encode([
                'success' => false, 
                'message' => 'No autenticado',
                'esFavorito' => false
            ]);
            exit;
        }

        try {
            $idArticulo = $_POST['id_articulo'] ?? $_GET['id_articulo'] ?? null;
            $idUsuario = $_SESSION['ID_Usuario'];
            
            if (!$idArticulo) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'ID de artículo requerido'
                ]);
                exit;
            }

            $esFavorito = $this->favoritoModel->esFavorito($idUsuario, $idArticulo);
            $totalFavoritos = $this->favoritoModel->contarFavoritos($idUsuario);
            
            echo json_encode([
                'success' => true,
                'esFavorito' => $esFavorito,
                'totalFavoritos' => $totalFavoritos
            ]);
            
        } catch (Exception $e) {
            error_log("Error en verificarEstado: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'esFavorito' => false
            ]);
        }
        exit;
    }

    public function index() {
        if (!$this->verificarAutenticacion()) {
            $_SESSION['redirect_url'] = BASE_URL . '?c=Favorito&a=index';
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $idUsuario = $_SESSION['ID_Usuario'];
        $favoritos = $this->favoritoModel->obtenerFavoritos($idUsuario);
        $totalFavoritos = $this->favoritoModel->contarFavoritos($idUsuario);

        $categorias = $this->getMenuCategorias();
        
        include "views/favoritos/index.php";
    }

    private function getMenuCategorias() {
        try {
            $sql = "SELECT ID_Categoria, N_Categoria FROM categoria ORDER BY N_Categoria";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>