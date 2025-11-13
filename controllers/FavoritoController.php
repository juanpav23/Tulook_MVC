<?php
class FavoritoController {
    private $db;
    private $favorito;

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->favorito = new Favorito($this->db);
    }

    // Agregar / quitar favorito
    public function toggle() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['usuario_id'];
        $id_producto = $_POST['id_producto'] ?? null;

        if (!$id_producto) {
            header("Location: " . BASE_URL);
            exit;
        }

        $this->favorito->ID_Usuario = $id_usuario;
        $this->favorito->ID_Producto = $id_producto;

        if ($this->favorito->exists()) {
            $this->favorito->remove();
        } else {
            $this->favorito->add();
        }

        header("Location: " . BASE_URL . "?c=Producto&a=ver&id=" . $id_producto);
    }

    // Ver lista de favoritos
    public function megusta() {
        if (!isset($_SESSION['usuario_id'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $this->favorito->ID_Usuario = $_SESSION['usuario_id'];
        $productos = $this->favorito->getByUser();

        // cargar categorías para el navbar
        $productoCtrl = new ProductoController();
        $menuCategorias = $productoCtrl->getMenuCategorias();

        include "views/productos/megusta.php";
    }
}


