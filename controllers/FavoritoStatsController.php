<?php
// controllers/FavoritoStatsController.php
require_once "models/Database.php";
require_once "models/FavoritoStats.php";

class FavoritoStatsController {
    private $db;
    private $statsModel;

    public function __construct($db) {
        $this->db = $db;
        $this->statsModel = new FavoritoStats($this->db);

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

    // 📊 VISTA PRINCIPAL DE ESTADÍSTICAS DE FAVORITOS
    public function index() {
        $termino = $_GET['buscar'] ?? '';
        $filtroCategoria = $_GET['categoria'] ?? '';
        $filtroGenero = $_GET['genero'] ?? '';
        $filtroSubcategoria = $_GET['subcategoria'] ?? '';
        $filtroEstado = $_GET['estado'] ?? '';
        
        // Determinar si mostrar solo activos
        $soloActivos = false;
        if (isset($_GET['incluir_inactivos'])) {
            $soloActivos = !((bool)$_GET['incluir_inactivos']);
        } else {
            // Por defecto, mostrar solo activos
            $soloActivos = true;
        }
        
        // Si hay filtro específico de estado, tiene prioridad
        if ($filtroEstado === 'activo') {
            $soloActivos = true;
        } elseif ($filtroEstado === 'inactivo') {
            $soloActivos = false;
        }
        
        // Obtener opciones para filtros
        $categorias = $this->statsModel->obtenerCategorias($soloActivos);
        $generos = $this->statsModel->obtenerGeneros($soloActivos);
        $subcategorias = $this->statsModel->obtenerSubcategorias($soloActivos);
        
        // Obtener datos principales con filtro aplicado
        $masFavoritos = $this->statsModel->obtenerMasFavoritos(10, $soloActivos);
        $menosFavoritos = $this->statsModel->obtenerMenosFavoritos(10, $soloActivos);
        $productoMasFavorito = $this->statsModel->obtenerProductoMasFavorito($soloActivos);
        $productoMenosFavorito = $this->statsModel->obtenerProductoMenosFavorito($soloActivos);
        $estadisticas = $this->statsModel->obtenerEstadisticasGenerales($soloActivos);
        $distribucionCategoria = $this->statsModel->obtenerDistribucionPorCategoria($soloActivos);
        $distribucionGenero = $this->statsModel->obtenerDistribucionPorGenero($soloActivos);
        $distribucionSubcategoria = $this->statsModel->obtenerDistribucionPorSubcategoria($soloActivos); // 🔥 NUEVO
        
        // Búsqueda si hay términos (CONDICIÓN CORREGIDA)
        if (!empty($termino) || !empty($filtroCategoria) || !empty($filtroGenero) || !empty($filtroSubcategoria) || !empty($filtroEstado)) {
            $resultadosBusqueda = $this->statsModel->buscar($termino, $filtroCategoria, $filtroGenero, $filtroSubcategoria, $soloActivos, $filtroEstado);
            $modoBusqueda = true;
        } else {
            $resultadosBusqueda = [];
            $modoBusqueda = false;
        }

        include "views/admin/layout_admin.php";
    }

    // 📈 VISTA SOLO DE ESTADÍSTICAS (GRAFICOS)
    public function estadisticas() {
        $soloActivos = isset($_GET['incluir_inactivos']) ? !((bool)$_GET['incluir_inactivos']) : true;
        
        $estadisticas = $this->statsModel->obtenerEstadisticasGenerales($soloActivos);
        $distribucionCategoria = $this->statsModel->obtenerDistribucionPorCategoria($soloActivos);
        $distribucionGenero = $this->statsModel->obtenerDistribucionPorGenero($soloActivos);
        
        include "views/admin/layout_admin.php";
    }
}
?>