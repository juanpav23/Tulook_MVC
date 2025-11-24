<?php
// controllers/PedidoController.php

require_once "models/Compra.php";

class PedidoController {

    private $db;
    private $compra;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db;
        $this->compra = new Compra($db);
    }

    // ===============================================================
    // HISTORIAL DE COMPRAS
    // ===============================================================
    public function historial() {

        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        $compras = $this->compra->obtenerCompras($id_usuario);

        include "views/pedidos/historial.php";
    }

    // ===============================================================
    // VER FACTURA ONLINE
    // ===============================================================
    public function factura() {

        $id = $_GET['id'] ?? 0;

        $factura = $this->compra->obtenerFacturaDetalle($id);
        $items = $this->compra->obtenerFacturaItems($id);

        include "views/pedidos/factura.php";
    }

    // ===============================================================
    // PERMITIR RESEÑAS SOLO SI COMPRÓ
    // ===============================================================
    public function permiteResena() {
        $id_producto = $_GET['id_producto'] ?? 0;

        if (!isset($_SESSION['ID_Usuario'])) {
            echo json_encode(['compra' => false]);
            return;
        }

        $id_usuario = $_SESSION['ID_Usuario'];

        $comprado = $this->compra->usuarioComproProducto($id_usuario, $id_producto);

        echo json_encode(['compra' => $comprado]);
    }
}
