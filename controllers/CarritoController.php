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
    // ➕ Agregar producto al carrito (solo sesión) - VERSIÓN CORREGIDA
    // =======================================================
    public function agregar() {
        $id_producto = $_POST['id_producto'] ?? ($_GET['id'] ?? null);
        $id_articulo = $_POST['id_articulo'] ?? null;
        $cantidad    = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
        $id_color    = $_POST['id_color'] ?? null;
        $n_color     = $_POST['n_color'] ?? null; // NUEVO: nombre del color desde el formulario
        $codigo_hex  = $_POST['codigo_hex'] ?? null; // NUEVO: código hexadecimal desde el formulario
        $id_talla    = $_POST['id_talla'] ?? null;
        $tipo        = $_POST['tipo'] ?? 'base'; // 'base' o 'variante'

        if ($cantidad < 1) $cantidad = 1;

        if (!$id_producto && !$id_articulo) {
            header("Location: " . BASE_URL);
            exit;
        }

        $producto = new Producto($this->db);
        $data = null;

        // 🔹 Caso 1: Variante (producto)
        if ($tipo === 'variante' && $id_producto) {
            $data = $producto->readOne($id_producto);
            if ($data) {
                $data['Tipo'] = 'variante';
                $data['ID_Color'] = $id_color;
                $data['ID_Talla'] = $id_talla;
            }
        }

        // 🔹 Caso 2: Base (artículo)
        if ($tipo === 'base' && $id_articulo) {
            $data = $producto->readBase($id_articulo);
            if ($data) {
                $data['Tipo'] = 'base';
                $data['ID_Producto'] = null;
                $data['Nombre_Producto'] = $data['N_Articulo'];
                $data['Nombre_Talla'] = $data['N_Talla'] ?? 'Única';
                $data['ID_Color'] = 'base';
                $data['ID_Talla'] = $data['ID_Talla'] ?? $id_talla;
            }
        }

        if (!$data) {
            $_SESSION['mensaje_error'] = "❌ Producto no encontrado.";
            header("Location: " . BASE_URL);
            exit;
        }

        // ✅ Solo se valida stock, NO se descuenta todavía
        $stock_disponible = (int)($data['Cantidad'] ?? 0);
        if ($stock_disponible <= 0) {
            $_SESSION['mensaje_error'] = "❌ Este producto está agotado.";
            header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
            exit;
        }

        if ($cantidad > $stock_disponible) {
            $_SESSION['mensaje_error'] = "⚠️ Solo hay {$stock_disponible} unidades disponibles.";
            header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
            exit;
        }

        // 🧺 Inicializar carrito
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // 🔁 Verificar si ya está en el carrito
        $encontrado = false;
        foreach ($_SESSION['carrito'] as &$item) {
            $mismoProducto = false;
            
            if ($tipo === 'variante') {
                // Para variantes: mismo producto, color y talla
                $mismoProducto = (
                    $item['ID_Producto'] == $data['ID_Producto'] &&
                    $item['ID_Color'] == $data['ID_Color'] &&
                    $item['ID_Talla'] == $data['ID_Talla'] &&
                    $item['Tipo'] === 'variante'
                );
            } else {
                // Para artículo base: mismo artículo base
                $mismoProducto = (
                    $item['ID_Articulo'] == $data['ID_Articulo'] &&
                    $item['Tipo'] === 'base' &&
                    $item['ID_Producto'] === null
                );
            }

            if ($mismoProducto) {
                $nueva_cantidad = $item['Cantidad'] + $cantidad;
                if ($nueva_cantidad > $stock_disponible) {
                    $_SESSION['mensaje_error'] = "⚠️ Solo hay {$stock_disponible} unidades disponibles.";
                    header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                    exit;
                }
                $item['Cantidad'] = $nueva_cantidad;
                $encontrado = true;
                break;
            }
        }
        unset($item);

        // 🆕 Si no existe en el carrito, agregarlo
        if (!$encontrado) {
            $item_carrito = [
                'ID_Producto' => $data['ID_Producto'] ?? null,
                'ID_Articulo' => $data['ID_Articulo'],
                'N_Articulo'  => $data['N_Articulo'] ?? $data['Nombre_Producto'],
                'Foto'        => $data['Foto'] ?? 'assets/img/placeholder.png',
                'Precio'      => $data['Precio'] ?? $data['Precio_Final'] ?? $data['Precio_Base'] ?? 0,
                'N_Talla'     => $data['Nombre_Talla'] ?? 'Única',
                'N_Color'     => $n_color ?? $data['Nombre_Color'] ?? 'Sin color', // USAR n_color DEL FORMULARIO
                'ID_Color'    => $data['ID_Color'] ?? 'base',
                'ID_Talla'    => $data['ID_Talla'] ?? $id_talla,
                'Tipo'        => $tipo,
                'Cantidad'    => $cantidad,
                'CodigoHex'   => $codigo_hex ?? $data['CodigoHex'] ?? null // USAR codigo_hex DEL FORMULARIO
            ];

            // Para artículo base, usar "Sin color" si no hay color específico
            if ($tipo === 'base' && (!$n_color || $n_color === 'Base')) {
                $item_carrito['N_Color'] = 'Sin color';
            }

            // Para variantes, usar la información del formulario o consultar la BD si es necesario
            if ($tipo === 'variante' && $id_color && $id_color !== 'base') {
                // Si no tenemos información del color del formulario, consultar la BD
                if (empty($n_color) || empty($codigo_hex)) {
                    $color_info = $producto->getColorInfo($id_color);
                    if ($color_info) {
                        $item_carrito['N_Color'] = $color_info['N_Color'];
                        $item_carrito['CodigoHex'] = $color_info['CodigoHex'];
                    }
                }
            }

            // Para variantes, obtener nombre real de la talla si no viene
            if ($tipo === 'variante' && $id_talla && empty($item_carrito['N_Talla'])) {
                $talla_info = $producto->getTallaInfo($id_talla);
                if ($talla_info) {
                    $item_carrito['N_Talla'] = $talla_info['N_Talla'];
                }
            }

            $_SESSION['carrito'][] = $item_carrito;
        }

        $_SESSION['mensaje_ok'] = "✅ Producto agregado al carrito correctamente.";
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // ✅ Confirmar compra (descuenta stock real)
    // =======================================================
    public function confirmarCompra() {
        if (empty($_SESSION['carrito'])) {
            $_SESSION['mensaje_error'] = "❌ Tu carrito está vacío.";
            header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
            exit;
        }

        $producto = new Producto($this->db);
        $errores = [];

        foreach ($_SESSION['carrito'] as $index => $item) {
            $cantidad = (int)$item['Cantidad'];
            
            // Verificar stock nuevamente antes de comprar
            if ($item['Tipo'] === 'variante' && $item['ID_Producto']) {
                $stock_actual = $producto->verificarStock($item['ID_Producto'], $cantidad, 'variante');
                if (!$stock_actual) {
                    $errores[] = "No hay suficiente stock para: {$item['N_Articulo']} - {$item['N_Color']} {$item['N_Talla']}";
                    continue;
                }
            } else {
                $stock_actual = $producto->verificarStock($item['ID_Articulo'], $cantidad, 'base');
                if (!$stock_actual) {
                    $errores[] = "No hay suficiente stock para: {$item['N_Articulo']}";
                    continue;
                }
            }

            // Descontar stock
            if ($item['Tipo'] === 'variante' && $item['ID_Producto']) {
                $producto->actualizarStock($item['ID_Producto'], $cantidad, 'variante');
            } else {
                $producto->actualizarStock($item['ID_Articulo'], $cantidad, 'base');
            }
        }

        if (!empty($errores)) {
            $_SESSION['mensaje_error'] = implode('<br>', $errores);
            header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
            exit;
        }

        unset($_SESSION['carrito']);
        $_SESSION['mensaje_ok'] = "✅ ¡Compra realizada con éxito!";
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // ❌ Eliminar producto del carrito
    // =======================================================
    public function eliminar() {
        if (isset($_GET['id']) && isset($_SESSION['carrito'][$_GET['id']])) {
            $producto_eliminado = $_SESSION['carrito'][$_GET['id']]['N_Articulo'];
            unset($_SESSION['carrito'][$_GET['id']]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']); // reindexar
            $_SESSION['mensaje_ok'] = "🗑️ {$producto_eliminado} eliminado del carrito.";
        }
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // 🧹 Vaciar todo el carrito (sin tocar BD)
    // =======================================================
    public function vaciar() {
        unset($_SESSION['carrito']);
        $_SESSION['mensaje_ok'] = "🧺 Carrito vaciado correctamente.";
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // 🔄 Actualizar cantidad en carrito
    // =======================================================
    public function actualizarCantidad() {
        if (isset($_POST['index']) && isset($_POST['cantidad']) && isset($_SESSION['carrito'][$_POST['index']])) {
            $index = (int)$_POST['index'];
            $nueva_cantidad = (int)$_POST['cantidad'];
            
            if ($nueva_cantidad < 1) {
                $nueva_cantidad = 1;
            }
            
            // Verificar stock disponible
            $item = $_SESSION['carrito'][$index];
            $producto = new Producto($this->db);
            
            if ($item['Tipo'] === 'variante' && $item['ID_Producto']) {
                $stock_disponible = $producto->verificarStock($item['ID_Producto'], $nueva_cantidad, 'variante');
            } else {
                $stock_disponible = $producto->verificarStock($item['ID_Articulo'], $nueva_cantidad, 'base');
            }
            
            if (!$stock_disponible) {
                $_SESSION['mensaje_error'] = "⚠️ No hay suficiente stock disponible.";
            } else {
                $_SESSION['carrito'][$index]['Cantidad'] = $nueva_cantidad;
                $_SESSION['mensaje_ok'] = "✅ Cantidad actualizada correctamente.";
            }
        }
        
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }
}
?>









