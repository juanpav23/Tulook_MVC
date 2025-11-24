<?php
require_once "models/Compra.php";
require_once "models/MetodoPago.php";
require_once "models/Direccion.php";

class CheckoutController {

    private $db;
    private $compra;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db;
        $this->compra = new Compra($db);
    }

    // =============== MOSTRAR CHECKOUT ================
    public function index() {

        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];

        $mp = new MetodoPago($this->db);
        $metodos = $mp->obtenerMetodosPago();

        $dir = new Direccion($this->db);
        $direcciones = $dir->obtenerDireccionesUsuario($id_usuario);

        $carrito = $_SESSION['carrito'] ?? [];

        include "views/checkout/checkout.php";
    }

    // =============== PROCESAR COMPRA ================
    public function procesar() {

        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        $carrito = $_SESSION['carrito'] ?? [];

        if (!$carrito) {
            $_SESSION['mensaje_error'] = "❌ Tu carrito está vacío.";
            header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
            exit;
        }

        // ---------------- DIRECCIÓN ----------------
        if (!empty($_POST['crear_direccion'])) {

            $direccion = trim($_POST['nueva_direccion']);
            $ciudad = trim($_POST['nueva_ciudad']);
            $departamento = trim($_POST['nueva_departamento']);
            $postal = trim($_POST['nueva_postal']);

            if ($direccion === "" || $ciudad === "" || $departamento === "") {
                $_SESSION['mensaje_error'] = "❌ Completa todos los campos de la nueva dirección.";
                header("Location: " . BASE_URL . "?c=Checkout&a=index");
                exit;
            }

            $sql = "INSERT INTO direccion (ID_Usuario, Direccion, Ciudad, Departamento, CodigoPostal, Predeterminada)
                    VALUES (?, ?, ?, ?, ?, 1)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_usuario, $direccion, $ciudad, $departamento, $postal]);

            $direccion_id = $this->db->lastInsertId();

        } else {

            $direccion_id = $_POST['direccion'] ?? null;

            if (!$direccion_id) {
                $_SESSION['mensaje_error'] = "❌ Selecciona una dirección.";
                header("Location: " . BASE_URL . "?c=Checkout&a=index");
                exit;
            }
        }

        // ---------------- MÉTODO DE PAGO ----------------
        $metodo_pago_nombre = $_POST['metodo_pago'] ?? null;

        if (!$metodo_pago_nombre) {
            $_SESSION['mensaje_error'] = "❌ Debes seleccionar un método de pago.";
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // CONVERTIR NOMBRE DE MÉTODO A ID
        $mp = new MetodoPago($this->db);
        $metodos = $mp->obtenerMetodosPago();
        $metodo_pago_id = null;

        // Mapear nombres a IDs
        $metodos_map = [
            'Tarjeta' => 1,
            'Efectivo' => 2, 
            'PSE' => 3
        ];

        $metodo_pago_id = $metodos_map[$metodo_pago_nombre] ?? null;

        if (!$metodo_pago_id) {
            $_SESSION['mensaje_error'] = "❌ Método de pago no válido: " . $metodo_pago_nombre;
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // ---------------- VALIDAR STOCK ----------------
        foreach ($carrito as $item) {

            $tipo = ($item['Tipo'] ?? 'variante') === 'base' ? 'base' : 'variante';
            $id = $tipo === 'base' ? $item['ID_Articulo'] : $item['ID_Producto'];

            if (!$this->compra->stockDisponible($id, $item['Cantidad'], $tipo)) {
                $_SESSION['mensaje_error'] = "❌ Stock insuficiente de: {$item['N_Articulo']}";
                header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                exit;
            }
        }

        // ---------------- CALCULAR TOTAL ----------------
        $total = 0;
        $itemsToSave = [];

        foreach ($carrito as $item) {

            $precioUnit = floatval($item['Precio']);
            $cant = intval($item['Cantidad']);
            $subtotal = $precioUnit * $cant;

            $total += $subtotal;

            $itemsToSave[] = [
                "ID_Producto" => $item["ID_Producto"] ?? null,
                "ID_Articulo" => $item["ID_Articulo"] ?? null,
                "Cantidad" => $cant,
                "Precio_Unitario" => $precioUnit,
                "Subtotal" => $subtotal,
                "Descuento_Aplicado" => floatval($item["Descuento"]["Valor"] ?? 0)
            ];
        }

        // ---------------- CREAR FACTURA ----------------
        $codigo_acceso = strtoupper(bin2hex(random_bytes(5)));

        $id_factura = $this->compra->crearFactura(
            $id_usuario,
            $direccion_id,
            $metodo_pago_id,  // ← USAR EL ID, NO EL NOMBRE
            $total,
            $codigo_acceso
        );

        if (!$id_factura) {
            $_SESSION['mensaje_error'] = "❌ Error creando factura.";
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // ---------------- GUARDAR ITEMS ----------------
        foreach ($itemsToSave as $i) {

            $this->compra->agregarItem(
                $id_factura,
                $i['ID_Producto'],
                $i['ID_Articulo'],
                $i['Cantidad'],
                $i['Precio_Unitario'],
                $i['Subtotal'],
                $i['Descuento_Aplicado']
            );

            if (!empty($i['ID_Producto'])) {
                $this->compra->descontarStock($i['ID_Producto'], $i['Cantidad'], "variante");
            } else {
                $this->compra->descontarStock($i['ID_Articulo'], $i['Cantidad'], "base");
            }
        }

        unset($_SESSION['carrito']);

        // ---------------- PDF + CORREO ----------------
        require_once "services/Mailer.php";
        $mailer = new Mailer();

        $factura = $this->compra->obtenerFacturaDetalle($id_factura);
        $items = $this->compra->obtenerFacturaItems($id_factura);

        // PREPARAR DATOS CORRECTAMENTE PARA EL PDF
        $itemsParaPdf = [];
        foreach ($items as $item) {
            $itemsParaPdf[] = [
                'Nombre_Producto' => $item['Nombre_Producto'] ?? $item['N_Articulo'] ?? 'Producto',
                'Producto' => $item['Nombre_Producto'] ?? $item['N_Articulo'] ?? 'Producto',
                'Color' => $item['N_Color'] ?? 'No especificado',
                'Talla' => $item['N_Talla'] ?? 'Única',
                'Cantidad' => (int)($item['Cantidad'] ?? 1),
                'Precio_Unitario' => floatval($item['Precio_Unitario'] ?? $item['Subtotal'] / max(1, $item['Cantidad'])),
                'Precio' => floatval($item['Precio_Unitario'] ?? $item['Subtotal'] / max(1, $item['Cantidad'])),
                'Subtotal' => floatval($item['Subtotal'] ?? 0)
            ];
        }

        // CONVERTIR ID DE MÉTODO PAGO A NOMBRE PARA PDF
        $metodos_map_inverso = [
            1 => 'Tarjeta',
            2 => 'Efectivo',
            3 => 'PSE'
        ];
        
        $metodo_pago_nombre_pdf = $metodos_map_inverso[$factura['ID_Metodo_Pago']] ?? 'No especificado';

        // En la parte de preparar datos para PDF, actualiza:
$facturaParaPdf = [
    'ID_Factura' => $factura['ID_Factura'] ?? $id_factura,
    'Nombre_Cliente' => trim(($factura['Nombre'] ?? '') . ' ' . ($factura['Apellido'] ?? '')),
    'Email_Cliente' => $factura['Correo'] ?? 'No especificado',
    'Telefono_Cliente' => 'No especificado',
    'Fecha_Factura' => $factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'),
    'Metodo_Pago' => $metodo_pago_nombre_pdf,
    'Total' => floatval($factura['Monto_Total'] ?? 0),
    'Direccion_Completa' => ($factura['Direccion'] ?? '') . ', ' . 
                           ($factura['Ciudad'] ?? '') . ', ' . 
                           ($factura['Departamento'] ?? ''),
    // ✅ AGREGAR ESTOS CAMPOS
    'CodigoPostal' => $factura['CodigoPostal'] ?? '155201',
    'Nombre' => $factura['Nombre'] ?? '',
    'Apellido' => $factura['Apellido'] ?? '',
    'Correo' => $factura['Correo'] ?? '',
    'Monto_Total' => $factura['Monto_Total'] ?? 0,
    'T_Pago' => $metodo_pago_nombre_pdf
];

        $pdfTemp = $mailer->generarPdfFactura([
            "factura" => $facturaParaPdf,
            "items" => $itemsParaPdf
        ]);

        $correo = $factura['Correo'];
        $nombre = trim($factura['Nombre'] . " " . $factura['Apellido']);

        if ($correo) {

            $send = $mailer->enviarFacturaConAdjunto(
                $correo,
                $nombre,
                $pdfTemp,
                $id_factura
            );

            if (!$send["success"]) {
                $_SESSION["mensaje_info"] = "⚠️ Compra realizada, pero el correo no pudo enviarse.";
            } else {
                $_SESSION["mensaje_ok"] = "✅ Compra realizada y correo enviado.";
            }

        } else {
            $_SESSION["mensaje_info"] = "⚠️ Compra realizada, pero el usuario no tiene correo registrado.";
        }

        if (file_exists($pdfTemp)) unlink($pdfTemp);

        header("Location: " . BASE_URL . "?c=Checkout&a=exito&id=" . $id_factura);
        exit;
    }

    // =============== ÉXITO ================
    public function exito() {
        $id = intval($_GET["id"] ?? 0);

        $pedido = $this->compra->obtenerFacturaDetalle($id);
        $items = $this->compra->obtenerFacturaItems($id);

        include "views/checkout/exito.php";
    }
}