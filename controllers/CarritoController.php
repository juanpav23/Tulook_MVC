<?php
require_once "models/Producto.php";

class CarritoController {
    private $db;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db;
    }

    // =======================================================
    // 🛒 Mostrar el carrito
    // =======================================================
    public function carrito() {
        $carrito = $_SESSION['carrito'] ?? [];
        include "views/carrito/carrito.php";
    }

    // =======================================================
    // ➕ Agregar producto al carrito
    // =======================================================
    public function agregar() {
        $id_producto = $_POST['id_producto'] ?? ($_GET['id'] ?? null);
        $id_articulo = $_POST['id_articulo'] ?? null;
        $cantidad    = isset($_POST['cantidad']) ? max(1, (int)$_POST['cantidad']) : 1;
        $color       = $_POST['color'] ?? null;
        $talla       = $_POST['talla'] ?? null;

        if (!$id_producto && !$id_articulo) {
            header("Location: " . BASE_URL);
            exit;
        }

        $producto = new Producto($this->db);
        $data = null;

        // =======================================================
        // 🔹 Caso 1: Producto con variantes (ID_Producto)
        // =======================================================
        if ($id_producto) {
            $data = $producto->readOne($id_producto);
        }

        // =======================================================
        // 🔹 Caso 2: Producto base sin variantes (solo ID_Articulo)
        // =======================================================
        if (!$data && $id_articulo) {
            $data = $producto->readBase($id_articulo);
            if ($data) {
                // Adaptar estructura a la del carrito
                $data['ID_Producto'] = null;
                $data['Nombre_Producto'] = $data['N_Articulo'];
                $data['Nombre_Talla'] = $data['N_Talla'] ?? 'Única';
                $data['Nombre_Color'] = $data['N_Color'] ?? 'Sin color';
            }
        }

        // Si no se encontró producto → redirigir
        if (!$data) {
            header("Location: " . BASE_URL);
            exit;
        }

        // =======================================================
        // 🧺 Inicializar carrito si no existe
        // =======================================================
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // =======================================================
        // 🔁 Verificar si el producto ya está en el carrito
        // =======================================================
        $encontrado = false;
        foreach ($_SESSION['carrito'] as &$item) {
            if (
                $item['ID_Articulo'] == $data['ID_Articulo'] &&
                ($item['ID_Producto'] == ($data['ID_Producto'] ?? null)) &&
                $item['N_Talla'] == ($data['Nombre_Talla'] ?? $talla) &&
                $item['N_Color'] == ($data['Nombre_Color'] ?? $color)
            ) {
                $item['Cantidad'] += $cantidad;
                $encontrado = true;
                break;
            }
        }
        unset($item);

        // =======================================================
        // 🆕 Agregar nuevo producto si no existe
        // =======================================================
        if (!$encontrado) {
            $_SESSION['carrito'][] = [
                'ID_Producto' => $data['ID_Producto'] ?? null,
                'ID_Articulo' => $data['ID_Articulo'],
                'N_Articulo'  => $data['N_Articulo'] ?? $data['Nombre_Producto'],
                'Foto'        => $data['Foto'] ?? 'assets/img/placeholder.png',
                'Precio'      => $data['Precio'] ?? $data['Precio_Final'] ?? $data['Precio_Base'] ?? 0,
                'N_Talla'     => $data['Nombre_Talla'] ?? $talla ?? 'Única',
                'N_Color'     => $data['Nombre_Color'] ?? $color ?? 'Sin color',
                'Cantidad'    => $cantidad
            ];
        }

        // Redirigir al carrito
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // ❌ Eliminar producto del carrito
    // =======================================================
    public function eliminar() {
        if (isset($_GET['id']) && isset($_SESSION['carrito'][$_GET['id']])) {
            unset($_SESSION['carrito'][$_GET['id']]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // reindexar
        }
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // 🧹 Vaciar todo el carrito
    // =======================================================
    public function vaciar() {
        unset($_SESSION['carrito']);
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }
}
?>








