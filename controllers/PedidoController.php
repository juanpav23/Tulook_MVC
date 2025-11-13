<?php
// controllers/PedidoController.php
class PedidoController {
    private $db;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db;
    }

    public function checkout() {
        $carrito = $_SESSION['carrito'] ?? [];

        if (empty($carrito)) {
            echo "<div style='padding:20px; text-align:center;'>
                    <h2>Tu carrito está vacío 🛒</h2>
                    <a href='".BASE_URL."?c=Producto' class='btn btn-primary'>Ver productos</a>
                  </div>";
            return;
        }

        // 🔹 Aquí podrías guardar en la BD (tabla pedidos + detalle_pedido)
        // Por ahora solo limpiamos el carrito
        unset($_SESSION['carrito']);

        echo "<div style='padding:20px; text-align:center;'>
                <h2>✅ Compra finalizada con éxito</h2>
                <p>Gracias por tu pedido. Te contactaremos pronto.</p>
                <a href='".BASE_URL."?c=Producto' class='btn btn-success'>Seguir comprando</a>
              </div>";
    }
}
