<?php
require_once "models/Compra.php";
require_once "models/MetodoPago.php";
require_once "models/Direccion.php";
require_once "models/Descuento.php";

class CheckoutController {
    private $db;
    private $compra;
    private $descuentoModel;
    private $mailer;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db;
        $this->compra = new Compra($db);
        $this->descuentoModel = new Descuento($db);
        $this->mailer = new Mailer();
    }

    public function index() {
        if (!isset($_SESSION['ID_Usuario'])) {
            $_SESSION['redirect_url'] = BASE_URL . '?c=Checkout&a=index';
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

        // Obtener métodos de pago
        try {
            $sql = "SELECT ID_Metodo_Pago, T_Pago FROM metodo_pago ORDER BY ID_Metodo_Pago";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $metodos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $metodos = [
                ['ID_Metodo_Pago' => 1, 'T_Pago' => 'Tarjeta'],
                ['ID_Metodo_Pago' => 2, 'T_Pago' => 'Efectivo'],
                ['ID_Metodo_Pago' => 3, 'T_Pago' => 'PSE']
            ];
        }

        // Obtener direcciones
        $dir = new Direccion($this->db);
        $direcciones = $dir->obtenerDireccionesUsuario($id_usuario);

        // Calcular totales con descuentos individuales
        $subtotal_sin_descuentos = 0;
        $subtotal_con_descuentos = 0;
        $total_descuentos_individuales = 0;
        $total_items = 0;
        $iva_porcentaje = 19;

        foreach ($carrito as $item) {
            $precio_original = floatval($item['Precio_Original'] ?? $item['Precio']);
            $precio_final = floatval($item['Precio'] ?? $precio_original);
            $cantidad = intval($item['Cantidad'] ?? 0);
            
            $subtotal_sin_descuentos += ($precio_original * $cantidad);
            $subtotal_con_descuentos += ($precio_final * $cantidad);
            $total_descuentos_individuales += (($precio_original - $precio_final) * $cantidad);
            $total_items += $cantidad;
        }

        // Descuento global del carrito (si existe)
        $descuento_carrito = $_SESSION['descuento_carrito'] ?? null;
        $monto_descuento_carrito = 0;
        
        if ($descuento_carrito) {
            if ($descuento_carrito['Tipo'] === 'Porcentaje') {
                $monto_descuento_carrito = $subtotal_con_descuentos * ($descuento_carrito['Valor'] / 100);
            } else {
                $monto_descuento_carrito = min($descuento_carrito['Valor'], $subtotal_con_descuentos);
            }
        }

        $subtotal_con_todos_descuentos = $subtotal_con_descuentos - $monto_descuento_carrito;
        $iva_monto = $subtotal_con_todos_descuentos * ($iva_porcentaje / 100);
        $total_con_iva = $subtotal_con_todos_descuentos + $iva_monto;

        // ✅ CORRECCIÓN: Usar el método CORRECTO de tu modelo
        $descuentos_disponibles = $this->descuentoModel->obtenerDescuentosVigentesUsuario($id_usuario);

        // Preparar datos para la vista
        $datos_vista = [
            'metodos' => $metodos,
            'direcciones' => $direcciones,
            'carrito' => $carrito,
            'total_items' => $total_items,
            'subtotal_sin_descuentos' => $subtotal_sin_descuentos,
            'subtotal_con_descuentos' => $subtotal_con_descuentos,
            'total_descuentos_individuales' => $total_descuentos_individuales,
            'descuento_carrito' => $descuento_carrito,
            'monto_descuento_carrito' => $monto_descuento_carrito,
            'subtotal_con_todos_descuentos' => $subtotal_con_todos_descuentos,
            'iva_porcentaje' => $iva_porcentaje,
            'iva_monto' => $iva_monto,
            'total_con_iva' => $total_con_iva,
            'descuentos_disponibles' => $descuentos_disponibles
        ];

        include "views/checkout/checkout.php";
    }

    public function procesar() {
        if (!isset($_SESSION['ID_Usuario'])) {
            $_SESSION['mensaje_error'] = "❌ Debes iniciar sesión.";
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

        error_log("=== INICIANDO PROCESO DE COMPRA ===");
        error_log("Usuario: " . $id_usuario);
        error_log("Productos en carrito: " . count($carrito));

        // Validación de dirección
        $direccion_id = null;
        $error_direccion = '';
        
        if (isset($_POST['direccion']) && is_numeric($_POST['direccion'])) {
            $direccion_id = intval($_POST['direccion']);
            
            $sql = "SELECT ID_Direccion FROM direccion WHERE ID_Direccion = ? AND ID_Usuario = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$direccion_id, $id_usuario]);
            
            if (!$stmt->fetch()) {
                $error_direccion = "❌ Dirección no válida.";
            }
        } else if ((isset($_POST['direccion']) && $_POST['direccion'] === 'nueva') || 
                   (isset($_POST['nueva_direccion']) && !empty($_POST['nueva_direccion']))) {
            
            $direccion = trim($_POST['nueva_direccion'] ?? '');
            $ciudad = trim($_POST['nueva_ciudad'] ?? '');
            $departamento = trim($_POST['nueva_departamento'] ?? '');
            $postal = trim($_POST['nueva_postal'] ?? '');

            if (empty($direccion) || empty($ciudad) || empty($departamento)) {
                $error_direccion = "❌ Completa todos los campos de la nueva dirección.";
            } else {
                try {
                    $sql = "INSERT INTO direccion (ID_Usuario, Direccion, Ciudad, Departamento, CodigoPostal, Predeterminada) 
                            VALUES (?, ?, ?, ?, ?, 1)";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$id_usuario, $direccion, $ciudad, $departamento, $postal]);
                    $direccion_id = $this->db->lastInsertId();
                } catch (Exception $e) {
                    $error_direccion = "❌ Error al guardar la dirección: " . $e->getMessage();
                }
            }
        } else {
            $error_direccion = "❌ Debes seleccionar o crear una dirección.";
        }

        if (!empty($error_direccion)) {
            $_SESSION['mensaje_error'] = $error_direccion;
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // Validación de método de pago
        $metodo_pago_id = intval($_POST['metodo_pago'] ?? 0);

        if (!$metodo_pago_id) {
            $_SESSION['mensaje_error'] = "❌ Debes seleccionar un método de pago.";
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // Validación de stock
        foreach ($carrito as $item) {
            if (empty($item['ID_Producto'])) {
                $_SESSION['mensaje_error'] = "❌ Producto no válido.";
                header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                exit;
            }

            // Verificar stock
            $sql_stock = "SELECT Cantidad FROM producto WHERE ID_Producto = ?";
            $stmt = $this->db->prepare($sql_stock);
            $stmt->execute([$item['ID_Producto']]);
            $stock = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$stock || $stock['Cantidad'] < $item['Cantidad']) {
                $nombre_producto = htmlspecialchars($item['N_Articulo']);
                $_SESSION['mensaje_error'] = "❌ Stock insuficiente para: {$nombre_producto}";
                header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                exit;
            }
        }

        // Cálculo de totales
        $subtotal_con_descuentos = 0;
        $itemsToSave = [];
        $descuentos_a_registrar = [];
        $descuento_carrito = $_SESSION['descuento_carrito'] ?? null;
        $monto_descuento_carrito = 0;

        foreach ($carrito as $item) {
            $precioUnit = floatval($item['Precio']);
            $cant = intval($item['Cantidad']);
            $precioOriginal = floatval($item['Precio_Original'] ?? $item['Precio']);
            $subtotalItem = $precioUnit * $cant;
            
            $subtotal_con_descuentos += $subtotalItem;

            $id_producto = intval($item["ID_Producto"] ?? 0);
            if (!$id_producto && !empty($item["ID_Articulo"])) {
                try {
                    $sql = "SELECT ID_Producto FROM producto WHERE ID_Articulo = ? AND Activo = 1 LIMIT 1";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([$item["ID_Articulo"]]);
                    $producto_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    $id_producto = $producto_data ? intval($producto_data['ID_Producto']) : 0;
                } catch (Exception $e) {
                    $id_producto = 0;
                }
            }

            if (!$id_producto) {
                $_SESSION['mensaje_error'] = "❌ Producto no válido.";
                header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
                exit;
            }

            $itemsToSave[] = [
                "ID_Producto" => $id_producto,
                "ID_Articulo" => $item["ID_Articulo"] ?? null,
                "Cantidad" => $cant,
                "Precio_Unitario" => $precioUnit,
                "Precio_Original" => $precioOriginal,
                "Subtotal" => $subtotalItem,
                "Descuento_Aplicado" => ($precioOriginal - $precioUnit) * $cant,
                "Codigo_Descuento" => $item["Descuento"]["Codigo"] ?? '',
                "ID_Descuento" => $item["Descuento"]["ID_Descuento"] ?? null,
                "Nombre_Producto" => $item["N_Articulo"] ?? 'Producto',
                "Atributos" => $item["Atributos"] ?? []
            ];

            // Guardar descuentos individuales para registrar después
            if (!empty($item["Descuento"]["ID_Descuento"]) && $item["Descuento"]["Aplicado"]) {
                $descuentos_a_registrar[] = [
                    "ID_Descuento" => $item["Descuento"]["ID_Descuento"],
                    "Codigo" => $item["Descuento"]["Codigo"],
                    "Valor" => $item["Descuento"]["Valor"],
                    "Tipo" => $item["Descuento"]["Tipo"]
                ];
            }
        }

        // Aplicar descuento de carrito si existe
        if ($descuento_carrito) {
            if ($descuento_carrito['Tipo'] === 'Porcentaje') {
                $monto_descuento_carrito = $subtotal_con_descuentos * ($descuento_carrito['Valor'] / 100);
            } else {
                $monto_descuento_carrito = min($descuento_carrito['Valor'], $subtotal_con_descuentos);
            }
            
            $descuentos_a_registrar[] = [
                "ID_Descuento" => $descuento_carrito['ID_Descuento'] ?? null,
                "Codigo" => $descuento_carrito['Codigo'] ?? '',
                "Valor" => $descuento_carrito['Valor'] ?? 0,
                "Tipo" => $descuento_carrito['Tipo'] ?? ''
            ];
        }

        $subtotal_final = $subtotal_con_descuentos - $monto_descuento_carrito;

        // Cálculo de IVA
        $iva_porcentaje = 19;
        $iva_monto = $subtotal_final * ($iva_porcentaje / 100);
        $total_con_iva = $subtotal_final + $iva_monto;

        error_log("Subtotal con descuentos: " . $subtotal_con_descuentos);
        error_log("Descuento carrito: " . $monto_descuento_carrito);
        error_log("Subtotal final: " . $subtotal_final);
        error_log("Total con IVA: " . $total_con_iva);

        // Crear factura
        $codigo_acceso = strtoupper(substr(md5(uniqid() . time()), 0, 8));

        try {
            error_log("=== INICIANDO TRANSACCIÓN DE COMPRA ===");
            $this->db->beginTransaction();
            
            // Crear factura
            $sql_factura = "INSERT INTO factura (ID_Usuario, ID_Direccion, ID_Metodo_Pago, Subtotal, IVA, Monto_Total, Estado, Codigo_Acceso, Fecha_Factura) 
                           VALUES (?, ?, ?, ?, ?, ?, 'Confirmado', ?, NOW())";
            $stmt_factura = $this->db->prepare($sql_factura);
            $stmt_factura->execute([
                $id_usuario,
                $direccion_id,
                $metodo_pago_id,
                $subtotal_final,
                $iva_monto,
                $total_con_iva,
                $codigo_acceso
            ]);
            
            $id_factura = $this->db->lastInsertId();

            if (!$id_factura) {
                throw new Exception("No se pudo crear la factura");
            }
            
            error_log("Factura creada - ID: " . $id_factura);

            // Guardar items de la factura
            foreach ($itemsToSave as $item) {
                $sql_item = "INSERT INTO factura_producto (ID_Factura, ID_Producto, Cantidad, Precio_Unitario, Subtotal, Descuento_Aplicado, ID_Descuento) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt_item = $this->db->prepare($sql_item);
                $success = $stmt_item->execute([
                    $id_factura,
                    $item['ID_Producto'], 
                    $item['Cantidad'],
                    $item['Precio_Unitario'],
                    $item['Subtotal'],
                    $item['Descuento_Aplicado'],
                    $item['ID_Descuento']
                ]);

                if (!$success) {
                    throw new Exception("Error al guardar item en factura");
                }
                
                error_log("Item guardado: " . $item['Nombre_Producto'] . " x" . $item['Cantidad'] . " (ID Descuento: " . ($item['ID_Descuento'] ?? 'Ninguno') . ")");

                // Descontar stock
                $sql_stock = "UPDATE producto SET Cantidad = Cantidad - ? WHERE ID_Producto = ? AND Cantidad >= ?";
                $stmt_stock = $this->db->prepare($sql_stock);
                $successStock = $stmt_stock->execute([$item['Cantidad'], $item['ID_Producto'], $item['Cantidad']]);
                
                if (!$successStock) {
                    throw new Exception("Error al descontar stock");
                }
            }
            
            // ✅ REGISTRAR USO DE DESCUENTOS (individuales y de carrito)
            if (!empty($descuentos_a_registrar)) {
                error_log("=== REGISTRANDO DESCUENTOS ===");
                
                foreach ($descuentos_a_registrar as $descuento) {
                    if (!empty($descuento['ID_Descuento'])) {
                        error_log("Registrando descuento ID: " . $descuento['ID_Descuento'] . " - Código: " . $descuento['Codigo']);
                        
                        // ✅ Usar el método registrarUsoCompleto de tu modelo
                        $resultado = $this->descuentoModel->registrarUsoCompleto($descuento['ID_Descuento'], $id_usuario);
                        
                        if ($resultado) {
                            error_log("✅ Descuento registrado exitosamente: " . $descuento['Codigo']);
                        } else {
                            error_log("❌ Error al registrar descuento: " . $descuento['Codigo']);
                        }
                    }
                }
            } else {
                error_log("=== NO HAY DESCUENTOS PARA REGISTRAR ===");
            }

            // Verificar descuentos ganados
            error_log("Verificando descuentos ganados...");
            $descuentos_ganados = $this->descuentoModel->obtenerDescuentosGanados($id_usuario, $total_con_iva);
            
            if (!empty($descuentos_ganados)) {
                error_log("Descuentos ganados: " . count($descuentos_ganados));
                
                // Registrar descuentos ganados
                foreach ($descuentos_ganados as $descuento) {
                    $this->descuentoModel->registrarDescuentoGanado($descuento['ID_Descuento'], $id_usuario);
                }
                
                $_SESSION['descuentos_ganados_compra'] = $descuentos_ganados;
            } else {
                error_log("No hay descuentos ganados");
            }
            
            // Enviar correo de confirmación
            $this->enviarCorreoConfirmacion($id_factura, $id_usuario, $descuentos_ganados);
            
            error_log("=== CONFIRMANDO TRANSACCIÓN ===");
            $this->db->commit();
            error_log("✅ TRANSACCIÓN COMPLETADA CON ÉXITO");
            
        } catch (Exception $e) {
            error_log("=== ERROR EN TRANSACCIÓN ===");
            error_log("Error: " . $e->getMessage());
            error_log("Archivo: " . $e->getFile());
            error_log("Línea: " . $e->getLine());
            
            if ($this->db->inTransaction()) {
                error_log("Haciendo rollback...");
                $this->db->rollBack();
            }
            
            $_SESSION['mensaje_error'] = "❌ Error al procesar la compra: " . $e->getMessage();
            header("Location: " . BASE_URL . "?c=Checkout&a=index");
            exit;
        }

        // Limpiar carrito y descuento
        unset($_SESSION['carrito']);
        unset($_SESSION['descuento_carrito']);

        // Guardar en sesión para página de éxito
        $_SESSION['ultima_factura'] = $id_factura;

        // Redirigir a página de éxito
        header("Location: " . BASE_URL . "?c=Checkout&a=exito&id=" . $id_factura);
        exit;
    }

    private function enviarCorreoConfirmacion($id_factura, $id_usuario, $descuentos_ganados = []) {
    try {
        // ✅ CONSULTA CORRECTA - con JOIN a tipo_documento
        $sql_cliente = "SELECT 
                        u.Nombre, 
                        u.Apellido, 
                        u.Correo,
                        u.N_Documento,
                        u.Celular,
                        td.Documento AS Tipo_Documento  -- ¡JOIN con tipo_documento!
                       FROM usuario u 
                       LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                       WHERE u.ID_Usuario = ?";
        
        $stmt_cliente = $this->db->prepare($sql_cliente);
        $stmt_cliente->execute([$id_usuario]);
        $cliente_data = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
        
        if (!$cliente_data) {
            error_log("Cliente no encontrado: ID $id_usuario");
            return;
        }
        
        // ✅ CONSULTA DE FACTURA COMPLETA
        $sql_factura = "SELECT 
                        f.ID_Factura, 
                        f.Fecha_Factura, 
                        f.Monto_Total,
                        f.Subtotal,
                        f.IVA,
                        f.Codigo_Acceso,
                        mp.T_Pago AS Metodo_Pago,
                        d.Direccion, 
                        d.Ciudad, 
                        d.Departamento,
                        d.CodigoPostal,
                        CONCAT(d.Direccion, ', ', d.Ciudad, ', ', d.Departamento) AS Direccion_Completa,
                        -- DATOS DEL USUARIO CON JOIN A TIPO_DOCUMENTO
                        u.Nombre,
                        u.Apellido,
                        u.Correo,
                        u.N_Documento,
                        u.Celular,
                        td.Documento AS Tipo_Documento
                    FROM factura f
                    LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                    LEFT JOIN direccion d ON f.ID_Direccion = d.ID_Direccion
                    LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                    LEFT JOIN tipo_documento td ON u.ID_TD = td.ID_TD
                    WHERE f.ID_Factura = ?";
        
        $stmt_factura = $this->db->prepare($sql_factura);
        $stmt_factura->execute([$id_factura]);
        $factura_data = $stmt_factura->fetch(PDO::FETCH_ASSOC);
        
        if (!$factura_data) {
            error_log("Factura no encontrada: ID $id_factura");
            return;
        }
        
        // Obtener items de la factura
        $sql_items = "SELECT fp.*, p.Nombre_Producto, p.ValorAtributo1, p.ValorAtributo2, p.ValorAtributo3
                     FROM factura_producto fp
                     LEFT JOIN producto p ON fp.ID_Producto = p.ID_Producto
                     WHERE fp.ID_Factura = ?";
        $stmt_items = $this->db->prepare($sql_items);
        $stmt_items->execute([$id_factura]);
        $items_data = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        
        // Preparar datos para el correo
        $cliente = [
            'nombre' => $cliente_data['Nombre'] . ' ' . $cliente_data['Apellido'],
            'email' => $cliente_data['Correo']
        ];
        
        // Preparar items para la factura PDF
        $items_for_pdf = [];
        foreach ($items_data as $item) {
            $especificaciones = [];
            if (!empty($item['ValorAtributo1']) && $item['ValorAtributo1'] !== '—') {
                $especificaciones[] = $item['ValorAtributo1'];
            }
            if (!empty($item['ValorAtributo2']) && $item['ValorAtributo2'] !== '—') {
                $especificaciones[] = $item['ValorAtributo2'];
            }
            if (!empty($item['ValorAtributo3']) && $item['ValorAtributo3'] !== '—') {
                $especificaciones[] = $item['ValorAtributo3'];
            }
            
            $items_for_pdf[] = [
                'Nombre_Producto' => $item['Nombre_Producto'],
                'Especificaciones' => implode(' | ', $especificaciones),
                'Cantidad' => $item['Cantidad'],
                'Precio_Unitario' => $item['Precio_Unitario'],
                'Subtotal' => $item['Subtotal'],
                'Descuento_Aplicado' => $item['Descuento_Aplicado']
            ];
        }
        
        // Enviar correo
        $resultado = $this->mailer->enviarConfirmacionCompra(
            $cliente,
            $factura_data,
            $items_for_pdf,
            $descuentos_ganados
        );
        
        if (!$resultado['success']) {
            error_log("Error al enviar correo: " . $resultado['message']);
        }
        
    } catch (Exception $e) {
        error_log("Error en envío de correo: " . $e->getMessage());
    }
}

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
            $factura = [
                'ID_Factura' => $id_factura,
                'Fecha_Factura' => date('Y-m-d H:i:s'),
                'Estado' => 'Confirmado',
                'Monto_Total' => 0,
                'Subtotal' => 0,
                'IVA' => 0
            ];
            $items = [];
            $_SESSION['mensaje_info'] = "⚠️ No se pudieron cargar todos los detalles.";
        }

        // Verificar permisos
        if (isset($factura['ID_Usuario']) && $factura['ID_Usuario'] != $_SESSION['ID_Usuario'] && !isset($_SESSION['es_admin'])) {
            $_SESSION['mensaje_error'] = "❌ No tienes permiso para ver esta factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        // Obtener descuentos ganados desde sesión
        $descuentos_ganados = $_SESSION['descuentos_ganados_compra'] ?? [];

        $pedido = $factura;
        
        include "views/checkout/exito.php";
        
        // Limpiar sesión después de mostrar
        unset($_SESSION['ultima_factura']);
        unset($_SESSION['descuentos_ganados_compra']);
    }

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

        $factura = $this->compra->obtenerFacturaDetalle($id_factura);
        
        if (!$factura) {
            $_SESSION['mensaje_error'] = "❌ Factura no encontrada.";
            header("Location: " . BASE_URL);
            exit;
        }

        if ($factura['ID_Usuario'] != $_SESSION['ID_Usuario'] && !isset($_SESSION['es_admin'])) {
            $_SESSION['mensaje_error'] = "❌ No tienes permiso para descargar esta factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        header("Location: " . BASE_URL . "?c=FacturaPDF&a=generar&id=" . $id_factura);
        exit;
    }

    public function historial() {
        if (!isset($_SESSION['ID_Usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }

        $id_usuario = $_SESSION['ID_Usuario'];
        
        $compras = $this->compra->obtenerHistorialCompras($id_usuario);
        $estadisticas = $this->compra->obtenerEstadisticasCompras($id_usuario);

        include "views/checkout/historial.php";
    }

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

        $factura = $this->compra->obtenerFacturaDetalle($id_factura);
        $items = $this->compra->obtenerFacturaItems($id_factura);

        if (!$factura) {
            $_SESSION['mensaje_error'] = "❌ Factura no encontrada.";
            header("Location: " . BASE_URL);
            exit;
        }

        if ($factura['ID_Usuario'] != $_SESSION['ID_Usuario'] && !isset($_SESSION['es_admin'])) {
            $_SESSION['mensaje_error'] = "❌ No tienes permiso para ver esta factura.";
            header("Location: " . BASE_URL);
            exit;
        }

        include "views/checkout/detalle.php";
    }
}
?>