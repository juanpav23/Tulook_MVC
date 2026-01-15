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
            $_SESSION['redirect_url'] = BASE_URL . '?c=Checkout&a=index';
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        $carrito = $_SESSION['carrito'] ?? [];

        if (empty($carrito)) {
            $_SESSION['mensaje_error'] = "❌ Tu carrito está vacío. Agrega productos antes de proceder al pago.";
            header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
            exit;
        }

        // Obtener métodos de pago directamente desde la base de datos
        try {
            $sql = "SELECT ID_Metodo_Pago, T_Pago FROM metodo_pago ORDER BY ID_Metodo_Pago";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            // Si hay error, usar valores por defecto
            $metodos = [
                ['ID_Metodo_Pago' => 1, 'T_Pago' => 'Tarjeta'],
                ['ID_Metodo_Pago' => 2, 'T_Pago' => 'Efectivo'],
                ['ID_Metodo_Pago' => 3, 'T_Pago' => 'PSE']
            ];
            error_log("Error obteniendo métodos de pago, usando valores por defecto: " . $e->getMessage());
        }

        // Obtener direcciones del usuario
        $dir = new Direccion($this->db);
        $direcciones = $dir->obtenerDireccionesUsuario($id_usuario);

        // Calcular totales para la vista
        $subtotal = 0;
        $total_items = 0;
        $iva_porcentaje = 19;

        foreach ($carrito as $item) {
            $precio = floatval($item['Precio'] ?? 0);
            $cantidad = intval($item['Cantidad'] ?? 0);
            $subtotal += ($precio * $cantidad);
            $total_items += $cantidad;
        }

        $iva_monto = $subtotal * ($iva_porcentaje / 100);
        $total_con_iva = $subtotal + $iva_monto;

        // Incluir vista
        include "views/checkout/checkout.php";
    }

    // =============== PROCESAR COMPRA ================
    public function procesar() {
        if (!isset($_SESSION['ID_Usuario'])) {
            $_SESSION['mensaje_error'] = "❌ Debes iniciar sesión para realizar una compra.";
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        $carrito = $_SESSION['carrito'] ?? [];

        if (empty($carrito)) {
            $_SESSION['mensaje_error'] = "❌ Tu carrito está vacío.";
            header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
            exit;
        }

        // =============== VALIDACIÓN DE DIRECCIÓN ================
        $direccion_id = null;
        
        if (!empty($_POST['crear_direccion'])) {
            // Crear nueva dirección
            $direccion = trim($_POST['nueva_direccion'] ?? '');
            $ciudad = trim($_POST['nueva_ciudad'] ?? '');
            $departamento = trim($_POST['nueva_departamento'] ?? '');
            $postal = trim($_POST['nueva_postal'] ?? '');

            if (empty($direccion) || empty($ciudad) || empty($departamento)) {
                $_SESSION['mensaje_error'] = "❌ Completa todos los campos obligatorios de la dirección.";
                header("Location: " . BASE_URL . "?c=Checkout&a=index");
                exit;
            }

            try {
                $sql = "INSERT INTO direccion (ID_Usuario, Direccion, Ciudad, Departamento, CodigoPostal, Predeterminada) 
                        VALUES (?, ?, ?, ?, ?, 1)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$id_usuario, $direccion, $ciudad, $departamento, $postal]);
                $direccion_id = $this->db->lastInsertId();
            } catch (Exception $e) {
                $_SESSION['mensaje_error'] = "❌ Error al guardar la dirección: " . $e->getMessage();
                header("Location: " . BASE_URL . "?c=Checkout&a=index");
                exit;
            }
        } else {
            // Usar dirección existente
            $direccion_id = $_POST['direccion'] ?? null;
            
            if (!$direccion_id) {
                $_SESSION['mensaje_error'] = "❌ Debes seleccionar una dirección de envío.";
                header("Location: " . BASE_URL . "?c=Checkout&a=index");
                exit;
            }
            
            // Verificar que la dirección pertenezca al usuario
            $sql = "SELECT ID_Direccion FROM direccion WHERE ID_Direccion = ? AND ID_Usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$direccion_id, $id_usuario]);
            
            if (!$stmt->fetch()) {
                $_SESSION['mensaje_error'] = "❌ La dirección seleccionada no es válida.";
                header("Location: " . BASE_URL . "?c=Checkout&a=index");
                exit;
            }
        }

        // =============== VALIDACIÓN DE MÉTODO DE PAGO ================
        $metodo_pago_id = intval($_POST['metodo_pago'] ?? 0);

        if (!$metodo_pago_id) {
            $_SESSION['mensaje_error'] = "❌ Debes seleccionar un método de pago.";
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // Verificar que el método de pago existe
        try {
            // CORREGIDO: Usar ID_Metodo_Pago
            $sql = "SELECT ID_Metodo_Pago FROM metodo_pago WHERE ID_Metodo_Pago = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$metodo_pago_id]);
            
            if (!$stmt->fetch()) {
                $_SESSION['mensaje_error'] = "❌ Método de pago no válido.";
                header("Location: " . BASE_URL . "?c=Checkout&a=index");
                exit;
            }
        } catch (Exception $e) {
            $_SESSION['mensaje_error'] = "❌ Error al validar el método de pago: " . $e->getMessage();
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // =============== VALIDACIÓN DE STOCK ================
        foreach ($carrito as $item) {
            if (empty($item['ID_Producto'])) {
                $_SESSION['mensaje_error'] = "❌ Error: Producto no válido en el carrito.";
                header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                exit;
            }

            if (!$this->compra->stockDisponible($item['ID_Producto'], $item['Cantidad'])) {
                $nombre_producto = htmlspecialchars($item['N_Articulo']);
                $_SESSION['mensaje_error'] = "❌ Stock insuficiente para: {$nombre_producto}";
                header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                exit;
            }
        }

        // =============== CÁLCULO DE TOTALES ================
        $subtotal = 0;
        $itemsToSave = [];

        foreach ($carrito as $item) {
            $precioUnit = floatval($item['Precio']);
            $cant = intval($item['Cantidad']);
            $subtotalItem = $precioUnit * $cant;

            $subtotal += $subtotalItem;

            // Asegurar que ID_Producto tenga un valor válido
            $id_producto = intval($item["ID_Producto"] ?? 0);
            if (!$id_producto) {
                // Si no hay ID_Producto, intentar obtenerlo del artículo base
                if (!empty($item["ID_Articulo"])) {
                    // Buscar el primer producto activo para este artículo
                    try {
                        $sql = "SELECT ID_Producto FROM producto WHERE ID_Articulo = ? AND Activo = 1 LIMIT 1";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([$item["ID_Articulo"]]);
                        $producto_data = $stmt->fetch(PDO::FETCH_ASSOC);
                        $id_producto = $producto_data ? intval($producto_data['ID_Producto']) : 0;
                    } catch (Exception $e) {
                        error_log("Error obteniendo ID_Producto: " . $e->getMessage());
                        $id_producto = 0;
                    }
                }
                
                if (!$id_producto) {
                    $_SESSION['mensaje_error'] = "❌ Error: Producto no válido en el carrito.";
                    header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                    exit;
                }
            }

            $itemsToSave[] = [
                "ID_Producto" => $id_producto, // Usar el ID corregido
                "ID_Articulo" => $item["ID_Articulo"] ?? null,
                "Cantidad" => $cant,
                "Precio_Unitario" => $precioUnit,
                "Subtotal" => $subtotalItem,
                "Descuento_Aplicado" => floatval($item["Descuento"]["Valor"] ?? 0),
                "Codigo_Descuento" => $item["Descuento"]["Codigo"] ?? '',
                "Nombre_Producto" => $item["N_Articulo"] ?? 'Producto',
                "Atributos" => $item["Atributos"] ?? []
            ];
        }

        // =============== CÁLCULO DE IVA ================
        $iva_porcentaje = 19; // 19% de IVA
        $iva_monto = $subtotal * ($iva_porcentaje / 100);
        $total_con_iva = $subtotal + $iva_monto;

        // =============== CREAR FACTURA ================
        $codigo_acceso = strtoupper(substr(md5(uniqid() . time()), 0, 8));

        try {
            // Iniciar transacción
            $this->db->beginTransaction();
            
            // Crear factura
            $id_factura = $this->compra->crearFacturaConIVA(
                $id_usuario,
                $direccion_id,
                $metodo_pago_id, // Enviar ID numérico
                $subtotal,
                $iva_monto,
                $total_con_iva,
                $codigo_acceso
            );

            if (!$id_factura) {
                throw new Exception("No se pudo crear la factura");
            }

            // =============== GUARDAR ITEMS DE LA FACTURA ================
            foreach ($itemsToSave as $item) {
                $success = $this->compra->agregarItem(
                    $id_factura,
                    $item['ID_Producto'], 
                    $item['Cantidad'],
                    $item['Precio_Unitario'],
                    $item['Subtotal'],
                    $item['Descuento_Aplicado']
                );

                if (!$success) {
                    $error_msg = "Error al guardar item en factura para producto ID: " . $item['ID_Producto'];
                    error_log($error_msg);
                    throw new Exception($error_msg);
                }

                // Descontar stock del producto
                $successStock = $this->compra->descontarStock($item['ID_Producto'], $item['Cantidad']);
                if (!$successStock) {
                    $error_msg = "Error al descontar stock para producto ID: " . $item['ID_Producto'];
                    error_log($error_msg);
                    throw new Exception($error_msg);
                }
            }
            
            // Confirmar transacción
            $this->db->commit();
            
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            
            error_log("Error en proceso de compra: " . $e->getMessage());
            $_SESSION['mensaje_error'] = "❌ Error al procesar la compra: " . $e->getMessage();
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // =============== LIMPIAR CARRITO ================
        unset($_SESSION['carrito']);

        // =============== OBTENER DATOS DE FACTURA ================
        $factura = $this->compra->obtenerFacturaDetalle($id_factura);
        $items = $this->compra->obtenerFacturaItems($id_factura);

        if (!$factura) {
            $_SESSION['mensaje_info'] = "⚠️ Compra realizada, pero no se pudieron obtener todos los detalles.";
        }

        // =============== GUARDAR EN SESIÓN PARA PÁGINA DE ÉXITO ================
        $_SESSION['ultima_factura'] = $id_factura;

        // =============== REDIRIGIR A PÁGINA DE ÉXITO ================
        header("Location: " . BASE_URL . "?c=Checkout&a=exito&id=" . $id_factura);
        exit;
    }

    // =============== ENVIAR FACTURA POR CORREO ================
    private function enviarFacturaPorCorreo($id_factura, $factura, $items) {
        try {
            // Verificar si Mailer existe
            $mailerPath = "services/Mailer.php";
            if (!file_exists($mailerPath)) {
                error_log("Archivo Mailer.php no encontrado en: " . $mailerPath);
                return false;
            }
            
            require_once $mailerPath;
            
            if (!class_exists('Mailer')) {
                error_log("Clase Mailer no encontrada");
                return false;
            }

            $mailer = new Mailer();

            // Preparar datos para el PDF
            $itemsParaPdf = [];
            foreach ($items as $item) {
                // Construir descripción del producto con atributos
                $descripcion = $item['N_Articulo'] ?? 'Producto';
                if (!empty($item['N_Color'])) {
                    $descripcion .= " - " . $item['N_Color'];
                }
                if (!empty($item['N_Talla'])) {
                    $descripcion .= " - Talla " . $item['N_Talla'];
                }

                $itemsParaPdf[] = [
                    'Producto' => $descripcion,
                    'Cantidad' => (int)($item['Cantidad'] ?? 1),
                    'Precio_Unitario' => floatval($item['Precio_Unitario'] ?? 0),
                    'Subtotal' => floatval($item['Subtotal'] ?? 0)
                ];
            }

            // Mapear ID de método de pago a nombre
            $metodos_map_inverso = [
                1 => 'Tarjeta de Crédito/Débito',
                2 => 'Efectivo',
                3 => 'PSE',
                4 => 'Transferencia Bancaria'
            ];
            
            $metodo_pago_nombre = $metodos_map_inverso[$factura['ID_Metodo_Pago']] ?? 'No especificado';

            $facturaParaPdf = [
                'ID_Factura' => $id_factura,
                'Nombre_Cliente' => trim(($factura['Nombre'] ?? '') . ' ' . ($factura['Apellido'] ?? '')),
                'Email_Cliente' => $factura['Correo'] ?? '',
                'Telefono_Cliente' => 'No especificado',
                'Fecha_Factura' => $factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'),
                'Metodo_Pago' => $metodo_pago_nombre,
                'Subtotal' => floatval($factura['Subtotal'] ?? 0),
                'IVA' => floatval($factura['IVA'] ?? 0),
                'Total' => floatval($factura['Monto_Total'] ?? 0),
                'Direccion_Completa' => ($factura['Direccion'] ?? '') . ', ' . 
                                    ($factura['Ciudad'] ?? '') . ', ' . 
                                    ($factura['Departamento'] ?? ''),
                'CodigoPostal' => $factura['CodigoPostal'] ?? '',
                'Nombre' => $factura['Nombre'] ?? '',
                'Apellido' => $factura['Apellido'] ?? '',
                'Correo' => $factura['Correo'] ?? '',
                'Monto_Total' => $factura['Monto_Total'] ?? 0,
                'Estado' => $factura['Estado'] ?? 'Confirmado'
            ];

            // Generar PDF
            $pdfTemp = $mailer->generarPdfFactura([
                "factura" => $facturaParaPdf,
                "items" => $itemsParaPdf
            ]);

            if (!$pdfTemp || !file_exists($pdfTemp)) {
                error_log("Error generando PDF de factura");
                return false;
            }

            // Enviar correo
            $correo = $factura['Correo'];
            $nombre = trim($factura['Nombre'] . " " . $factura['Apellido']);

            if (empty($correo)) {
                error_log("No hay correo para enviar factura");
                if (file_exists($pdfTemp)) {
                    unlink($pdfTemp);
                }
                return false;
            }

            $send = $mailer->enviarFacturaConAdjunto(
                $correo,
                $nombre,
                $pdfTemp,
                $id_factura
            );

            // Eliminar archivo temporal
            if (file_exists($pdfTemp)) {
                unlink($pdfTemp);
            }

            return $send["success"] ?? false;

        } catch (Exception $e) {
            error_log("Error enviando factura por correo: " . $e->getMessage());
            return false;
        }
    }

    // =============== PÁGINA DE ÉXITO ================
    public function exito() {
        $id_factura = intval($_GET["id"] ?? 0);

        if (!$id_factura) {
            if (isset($_SESSION['ultima_factura'])) {
                $id_factura = $_SESSION['ultima_factura'];
            } else {
                $_SESSION['mensaje_error'] = "❌ No se especificó una factura.";
                header("Location: " . BASE_URL);
                exit;
            }
        }

        // Obtener datos de la factura
        $factura = $this->compra->obtenerFacturaDetalle($id_factura);
        $items = $this->compra->obtenerFacturaItems($id_factura);

        if (!$factura) {
            // Si no se encuentra la factura, mostrar mensaje pero permitir continuar
            $factura = [
                'ID_Factura' => $id_factura,
                'Fecha_Factura' => date('Y-m-d H:i:s'),
                'Estado' => 'Confirmado',
                'Monto_Total' => 0,
                'Subtotal' => 0,
                'IVA' => 0
            ];
            $items = [];
            $_SESSION['mensaje_info'] = "⚠️ No se pudieron cargar todos los detalles de la factura, pero tu compra fue procesada exitosamente.";
        }

        // Verificar que la factura pertenezca al usuario actual
        if (isset($factura['ID_Usuario']) && $factura['ID_Usuario'] != $_SESSION['ID_Usuario'] && !isset($_SESSION['es_admin'])) {
            $_SESSION['mensaje_error'] = "❌ No tienes permiso para ver esta factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Pasar variables a la vista
        $pedido = $factura;
        
        // Incluir vista de éxito
        include "views/checkout/exito.php";
        
        // Limpiar factura de sesión después de mostrar
        unset($_SESSION['ultima_factura']);
    }

    // =============== DESCARGAR FACTURA ================
    public function descargarFactura() {
        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_factura = intval($_GET["id"] ?? 0);
        
        if (!$id_factura) {
            $_SESSION['mensaje_error'] = "❌ No se especificó una factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Obtener datos de la factura
        $factura = $this->compra->obtenerFacturaDetalle($id_factura);
        
        if (!$factura) {
            $_SESSION['mensaje_error'] = "❌ Factura no encontrada.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Verificar permisos
        if ($factura['ID_Usuario'] != $_SESSION['ID_Usuario'] && !isset($_SESSION['es_admin'])) {
            $_SESSION['mensaje_error'] = "❌ No tienes permiso para descargar esta factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Redirigir al generador de PDF
        header("Location: " . BASE_URL . "?c=FacturaPDF&a=generar&id=" . $id_factura);
        exit;
    }

    // =============== VER HISTORIAL DE COMPRAS ================
    public function historial() {
        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        
        // Obtener historial de compras
        $compras = $this->compra->obtenerHistorialCompras($id_usuario);
        $estadisticas = $this->compra->obtenerEstadisticasCompras($id_usuario);

        // Incluir vista de historial
        include "views/checkout/historial.php";
    }

    // =============== VER DETALLE DE COMPRA ================
    public function detalleCompra() {
        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_factura = intval($_GET["id"] ?? 0);
        
        if (!$id_factura) {
            $_SESSION['mensaje_error'] = "❌ No se especificó una factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Obtener datos de la factura
        $factura = $this->compra->obtenerFacturaDetalle($id_factura);
        $items = $this->compra->obtenerFacturaItems($id_factura);

        if (!$factura) {
            $_SESSION['mensaje_error'] = "❌ Factura no encontrada.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Verificar permisos
        if ($factura['ID_Usuario'] != $_SESSION['ID_Usuario'] && !isset($_SESSION['es_admin'])) {
            $_SESSION['mensaje_error'] = "❌ No tienes permiso para ver esta factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Incluir vista de detalle
        include "views/checkout/detalle.php";
    }
}
?>