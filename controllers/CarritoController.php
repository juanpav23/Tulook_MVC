<?php
// controllers/CarritoController.php - VERSIÓN LIMPIA
require_once "models/Producto.php";

class CarritoController {
    private $db;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db;
        
        // ✅ VERIFICAR SI EL USUARIO ESTÁ LOGUEADO PARA TODAS LAS ACCIONES DEL CARRITO
        $this->verificarAutenticacion();
    }

    // =======================================================
    // 🔐 VERIFICAR AUTENTICACIÓN
    // =======================================================
    private function verificarAutenticacion() {
        if (!isset($_SESSION['ID_Usuario']) || empty($_SESSION['ID_Usuario'])) {
            $_SESSION['mensaje_error'] = "🔐 Debes iniciar sesión para acceder al carrito.";
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? BASE_URL . '?c=Carrito&a=carrito';
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }
    }

    // =======================================================
    // 🛒 Mostrar el carrito CON NOMBRES DE ATRIBUTOS
    // =======================================================
    public function carrito() {
        $carrito = $_SESSION['carrito'] ?? [];
        
        // Inicializar variables para la vista
        $total = 0;
        $total_descuentos = 0;
        
        // Procesar cada item del carrito para agregar nombres de atributos
        foreach ($carrito as &$item) {
            // Calcular subtotales
            $precio_original = $item['Precio_Original'] ?? $item['Precio'];
            $precio_final = $item['Precio'];
            $subtotal = $precio_final * $item['Cantidad'];
            $subtotal_original = $precio_original * $item['Cantidad'];
            
            $total += $subtotal;
            $total_descuentos += ($subtotal_original - $subtotal);
            
            // Si ya tiene Atributos con nombres, no hacer nada
            if (!empty($item['Atributos']) && !empty($item['Atributos'][0]['nombre'])) {
                continue;
            }
            
            // Si no tiene Atributos estructurados, crearlos
            $atributosArray = [];
            
            // Atributo 1
            if (!empty($item['ID_Atributo1']) && !empty($item['ValorAtributo1'])) {
                $nombreAtributo = $this->getNombreAtributo($item['ID_Atributo1']);
                if ($nombreAtributo) {
                    $atributosArray[] = [
                        'nombre' => $nombreAtributo,
                        'valor' => $item['ValorAtributo1'],
                        'id' => $item['ID_Atributo1']
                    ];
                }
            }
            
            // Atributo 2
            if (!empty($item['ID_Atributo2']) && !empty($item['ValorAtributo2'])) {
                $nombreAtributo = $this->getNombreAtributo($item['ID_Atributo2']);
                if ($nombreAtributo) {
                    $atributosArray[] = [
                        'nombre' => $nombreAtributo,
                        'valor' => $item['ValorAtributo2'],
                        'id' => $item['ID_Atributo2']
                    ];
                }
            }
            
            // Atributo 3
            if (!empty($item['ID_Atributo3']) && !empty($item['ValorAtributo3'])) {
                $nombreAtributo = $this->getNombreAtributo($item['ID_Atributo3']);
                if ($nombreAtributo) {
                    $atributosArray[] = [
                        'nombre' => $nombreAtributo,
                        'valor' => $item['ValorAtributo3'],
                        'id' => $item['ID_Atributo3']
                    ];
                }
            }
            
            // Si encontramos atributos, actualizar el item
            if (!empty($atributosArray)) {
                $item['Atributos'] = $atributosArray;
            }
        }
        unset($item); // Romper la referencia
        
        // Actualizar la sesión con los atributos procesados
        $_SESSION['carrito'] = $carrito;
        
        // Incluir la vista
        include "views/carrito/carrito.php";
    }

    // =======================================================
    // 🔍 OBTENER NOMBRE DE ATRIBUTO POR ID
    // =======================================================
    private function getNombreAtributo($idAtributo) {
        try {
            if (!$idAtributo) return null;
            
            $sql = "SELECT Nombre FROM tipo_atributo WHERE ID_TipoAtributo = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idAtributo]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? $result['Nombre'] : null;
        } catch (Exception $e) {
            error_log("Error obteniendo nombre atributo: " . $e->getMessage());
            return null;
        }
    }

    // =======================================================
    // ➕ Agregar producto al carrito CON DESCUENTOS CORREGIDO
    // =======================================================
    public function agregar() {
        $id_producto = $_POST['id_producto'] ?? ($_GET['id'] ?? null);
        $id_articulo = $_POST['id_articulo'] ?? null;
        $cantidad    = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
        $tipo        = $_POST['tipo'] ?? 'variante';
        
        // ✅ Campos de descuento
        $precio_final = $_POST['precio_final'] ?? null;
        $codigo_descuento = $_POST['codigo_descuento'] ?? '';
        $tipo_descuento = $_POST['tipo_descuento'] ?? '';
        $valor_descuento = $_POST['valor_descuento'] ?? 0;
        $id_descuento = $_POST['id_descuento'] ?? null;

        if ($cantidad < 1) $cantidad = 1;

        if (!$id_producto && !$id_articulo) {
            $_SESSION['mensaje_error'] = "❌ No se especificó producto o artículo.";
            header("Location: " . BASE_URL);
            exit;
        }

        $producto = new Producto($this->db);
        $data = null;

        // 🔹 Caso 1: Variante (producto)
        if ($tipo === 'variante' && $id_producto) {
            // Obtener información detallada del producto con atributos
            $sql = "SELECT 
                        p.ID_Producto,
                        p.ID_Articulo,
                        p.Nombre_Producto,
                        p.Cantidad,
                        COALESCE(p.Foto, a.Foto) as Foto,
                        p.ID_Atributo1,
                        p.ValorAtributo1,
                        p.ID_Atributo2,
                        p.ValorAtributo2,
                        p.ID_Atributo3,
                        p.ValorAtributo3,
                        a.N_Articulo,
                        pr.Valor as Precio_Base,
                        (pr.Valor + (pr.Valor * (p.Porcentaje / 100))) AS Precio_Final,
                        a.ID_SubCategoria
                    FROM producto p
                    INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                    LEFT JOIN precio pr ON a.ID_Precio = pr.ID_Precio
                    WHERE p.ID_Producto = ? AND p.Activo = 1";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id_producto]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                $data['Tipo'] = 'variante';
                // ✅ CORRECCIÓN: Usar precio_final SIEMPRE que venga del formulario
                if ($precio_final !== null && $precio_final !== '') {
                    $data['Precio'] = floatval($precio_final);
                } else {
                    $data['Precio'] = floatval($data['Precio_Final']);
                }

                // DEBUG: Verificar valores
                error_log("🛒 Carrito (normal) - precio_final recibido: " . $precio_final);
                error_log("🛒 Carrito (normal) - Precio_Final de BD: " . $data['Precio_Final']);
                error_log("🛒 Carrito (normal) - Precio final a usar: " . $data['Precio']);
                
                // 🔍 OBTENER ATRIBUTOS CON NOMBRES
                $atributosArray = [];
                
                // Atributo 1
                if (!empty($data['ID_Atributo1']) && !empty($data['ValorAtributo1'])) {
                    $nombreAtributo1 = $this->getNombreAtributo($data['ID_Atributo1']);
                    if ($nombreAtributo1) {
                        $atributosArray[] = [
                            'nombre' => $nombreAtributo1,
                            'valor' => $data['ValorAtributo1'],
                            'id' => $data['ID_Atributo1']
                        ];
                    }
                }
                
                // Atributo 2
                if (!empty($data['ID_Atributo2']) && !empty($data['ValorAtributo2'])) {
                    $nombreAtributo2 = $this->getNombreAtributo($data['ID_Atributo2']);
                    if ($nombreAtributo2) {
                        $atributosArray[] = [
                            'nombre' => $nombreAtributo2,
                            'valor' => $data['ValorAtributo2'],
                            'id' => $data['ID_Atributo2']
                        ];
                    }
                }
                
                // Atributo 3
                if (!empty($data['ID_Atributo3']) && !empty($data['ValorAtributo3'])) {
                    $nombreAtributo3 = $this->getNombreAtributo($data['ID_Atributo3']);
                    if ($nombreAtributo3) {
                        $atributosArray[] = [
                            'nombre' => $nombreAtributo3,
                            'valor' => $data['ValorAtributo3'],
                            'id' => $data['ID_Atributo3']
                        ];
                    }
                }
                
                $data['Atributos'] = $atributosArray;
                
                // 🔍 OBTENER COLOR HEX SI ES UN COLOR
                $codigoHex = null;
                foreach ($atributosArray as $atributo) {
                    if ($atributo['nombre'] === 'Color' && $atributo['id'] == 2) {
                        $color_info = $producto->getColorInfo($atributo['id']);
                        if ($color_info && isset($color_info['CodigoHex'])) {
                            $codigoHex = $color_info['CodigoHex'];
                        }
                        break;
                    }
                }
                $data['CodigoHex'] = $codigoHex;
                
            } else {
                $_SESSION['mensaje_error'] = "❌ Producto no encontrado.";
                header("Location: " . BASE_URL);
                exit;
            }
        }

        // 🔹 Caso 2: Base (artículo)
        if ($tipo === 'base' && $id_articulo) {
            $data = $producto->readBase($id_articulo);
            if ($data) {
                $data['Tipo'] = 'base';
                $data['ID_Producto'] = null;
                $data['Nombre_Producto'] = $data['N_Articulo'];
                $data['Atributos'] = [];
                $data['Precio'] = $precio_final ? floatval($precio_final) : floatval($data['Precio']);
            } else {
                $_SESSION['mensaje_error'] = "❌ Artículo no encontrado.";
                header("Location: " . BASE_URL);
                exit;
            }
        }

        if (!$data) {
            $_SESSION['mensaje_error'] = "❌ Producto no encontrado.";
            header("Location: " . BASE_URL);
            exit;
        }

        // ✅ Validar stock
        $stock_disponible = (int)($data['Cantidad'] ?? 999);
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
                $mismoProducto = (
                    $item['ID_Producto'] == $data['ID_Producto'] &&
                    $item['Tipo'] === 'variante' &&
                    $item['Precio'] == $data['Precio']
                );
            } else {
                $mismoProducto = (
                    $item['ID_Articulo'] == $data['ID_Articulo'] &&
                    $item['Tipo'] === 'base' &&
                    $item['ID_Producto'] === null &&
                    $item['Precio'] == $data['Precio']
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

        if (!$encontrado) {
            $precio_final_carrito = $precio_final ? floatval($precio_final) : $data['Precio'];
            
            $item_carrito = [
                'ID_Producto' => $data['ID_Producto'] ?? null,
                'ID_Articulo' => $data['ID_Articulo'] ?? $id_articulo,
                'N_Articulo'  => $data['N_Articulo'] ?? $data['Nombre_Producto'] ?? 'Producto ' . ($id_producto ?? $id_articulo),
                'Foto'        => $data['Foto'] ?? 'assets/img/placeholder.png',
                'Precio'      => $precio_final_carrito, // USAR PRECIO FINAL CON DESCUENTO
                'Precio_Original' => $data['Precio_Base'] ?? $data['Precio_Final'] ?? $data['Precio'],
                'Tipo'        => $data['Tipo'],
                'Cantidad'    => $cantidad,
                // ATRIBUTOS DINÁMICOS
                'Atributos' => $data['Atributos'] ?? [],
                'ID_Atributo1' => $data['ID_Atributo1'] ?? null,
                'ValorAtributo1' => $data['ValorAtributo1'] ?? null,
                'ID_Atributo2' => $data['ID_Atributo2'] ?? null,
                'ValorAtributo2' => $data['ValorAtributo2'] ?? null,
                'ID_Atributo3' => $data['ID_Atributo3'] ?? null,
                'ValorAtributo3' => $data['ValorAtributo3'] ?? null,
                'CodigoHex'   => $data['CodigoHex'] ?? null,

                'N_Color' => $data['Nombre_Color'] ?? $data['ValorAtributo2'] ?? null,
                'N_Talla' => $data['Nombre_Talla'] ?? $data['ValorAtributo1'] ?? null,

                // SIEMPRE GUARDAR INFORMACIÓN DE DESCUENTO INCLUSO CON PRECIO 0
                'Descuento' => [
                    'Codigo' => $codigo_descuento,
                    'Tipo' => $tipo_descuento,
                    'Valor' => floatval($valor_descuento),
                    'ID_Descuento' => $id_descuento,
                    'Aplicado' => !empty($codigo_descuento) || floatval($valor_descuento) > 0
                ]
            ];

            $_SESSION['carrito'][] = $item_carrito;
        }

        $_SESSION['mensaje_ok'] = "Producto agregado al carrito correctamente.";
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // 🔍 OBTENER NOMBRES DE ATRIBUTOS PARA LA VISTA
    // =======================================================
    public function getNombresAtributosParaVista($atributosIds = []) {
        $nombres = [];
        
        if (empty($atributosIds)) {
            return $nombres;
        }
        
        try {
            // Crear una cadena de placeholders para la consulta
            $placeholders = str_repeat('?,', count($atributosIds) - 1) . '?';
            
            $sql = "SELECT ID_TipoAtributo, Nombre FROM tipo_atributo 
                    WHERE ID_TipoAtributo IN ($placeholders)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($atributosIds);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Convertir a array asociativo [ID_TipoAtributo => Nombre]
            foreach ($resultados as $row) {
                $nombres[$row['ID_TipoAtributo']] = $row['Nombre'];
            }
            
            return $nombres;
            
        } catch (Exception $e) {
            error_log("Error obteniendo nombres de atributos: " . $e->getMessage());
            return [];
        }
    }

    // =======================================================
    // ➕ Agregar producto al carrito VÍA AJAX
    // =======================================================
    public function agregarAjax() {
        $datos = array_merge($_GET, $_POST);
        
        if (empty($datos)) {
            $input = file_get_contents('php://input');
            if (!empty($input)) {
                parse_str($input, $datos_input);
                $datos = array_merge($datos, $datos_input);
            }
        }
        
        try {
            $id_producto = $datos['id_producto'] ?? null;
            $id_articulo = $datos['id_articulo'] ?? null;
            $cantidad    = isset($datos['cantidad']) ? (int)$datos['cantidad'] : 1;
            $tipo        = $datos['tipo'] ?? 'variante';
            
            $precio_final = $datos['precio_final'] ?? null;
            $codigo_descuento = $datos['codigo_descuento'] ?? '';
            $tipo_descuento = $datos['tipo_descuento'] ?? '';
            $valor_descuento = $datos['valor_descuento'] ?? 0;
            $id_descuento = $datos['id_descuento'] ?? null;

            if ($cantidad < 1) $cantidad = 1;

            if (empty($id_producto) && empty($id_articulo)) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No se especificó producto o artículo.'
                ]);
                exit;
            }

            $producto = new Producto($this->db);
            $data = null;

            if ($tipo === 'variante' && $id_producto) {
                $data = $producto->readOne($id_producto);
                if (!$data) {
                    $data = $this->crearDatosBasicosProducto($id_producto, null, null, $precio_final);
                }
            }

            if ($tipo === 'base' && $id_articulo) {
                $data = $producto->readBase($id_articulo);
                if (!$data) {
                    $data = $this->crearDatosBasicosArticulo($id_articulo, $precio_final);
                }
            }

            if (!$data) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Producto no encontrado.'
                ]);
                exit;
            }

            $precio_original = $data['Precio'] ?? $data['Precio_Final'] ?? $data['Precio_Base'] ?? 0;
            if ($precio_final !== null && $precio_final !== '') {
                $precio_a_usar = floatval($precio_final);
            } else {
                $precio_a_usar = $precio_original;
            }

            // DEBUG: Verificar valores
            error_log("🛒 Carrito - precio_final recibido: " . $precio_final);
            error_log("🛒 Carrito - precio_original: " . $precio_original);
            error_log("🛒 Carrito - precio_a_usar: " . $precio_a_usar);

            $stock_disponible = (int)($data['Cantidad'] ?? 999);
            if ($stock_disponible <= 0) {
                echo json_encode(['success' => false, 'message' => 'Este producto está agotado.']);
                exit;
            }

            if ($cantidad > $stock_disponible) {
                echo json_encode(['success' => false, 'message' => "Solo hay {$stock_disponible} unidades disponibles."]);
                exit;
            }

            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            $encontrado = false;
            foreach ($_SESSION['carrito'] as &$item) {
                $mismoProducto = false;
                
                if ($tipo === 'variante') {
                    $mismoProducto = (
                        $item['ID_Producto'] == $data['ID_Producto'] &&
                        $item['Tipo'] === 'variante' &&
                        $item['Precio'] == $precio_a_usar
                    );
                } else {
                    $mismoProducto = (
                        $item['ID_Articulo'] == $data['ID_Articulo'] &&
                        $item['Tipo'] === 'base' &&
                        $item['ID_Producto'] === null &&
                        $item['Precio'] == $precio_a_usar
                    );
                }

                if ($mismoProducto) {
                    $nueva_cantidad = $item['Cantidad'] + $cantidad;
                    if ($nueva_cantidad > $stock_disponible) {
                        echo json_encode(['success' => false, 'message' => "Solo hay {$stock_disponible} unidades disponibles."]);
                        exit;
                    }
                    $item['Cantidad'] = $nueva_cantidad;
                    $encontrado = true;
                    break;
                }
            }
            unset($item);

            if (!$encontrado) {
                $item_carrito = [
                    'ID_Producto' => $data['ID_Producto'] ?? null,
                    'ID_Articulo' => $data['ID_Articulo'] ?? $id_articulo,
                    'N_Articulo'  => $data['N_Articulo'] ?? $data['Nombre_Producto'] ?? 'Producto ' . ($id_producto ?? $id_articulo),
                    'Foto'        => $data['Foto'] ?? 'assets/img/placeholder.png',
                    'Precio'      => $precio_a_usar,
                    'Precio_Original' => $precio_original,
                    'Tipo'        => $tipo,
                    'Cantidad'    => $cantidad,
                    // ✅ ATRIBUTOS DINÁMICOS
                    'Atributos' => $data['Atributos'] ?? [],
                    'ID_Atributo1' => $data['ID_Atributo1'] ?? null,
                    'ValorAtributo1' => $data['ValorAtributo1'] ?? null,
                    'ID_Atributo2' => $data['ID_Atributo2'] ?? null,
                    'ValorAtributo2' => $data['ValorAtributo2'] ?? null,
                    'ID_Atributo3' => $data['ID_Atributo3'] ?? null,
                    'ValorAtributo3' => $data['ValorAtributo3'] ?? null,
                    'CodigoHex'   => $data['CodigoHex'] ?? null,

                    // ✅ AGREGAR CAMPOS DE COMPATIBILIDAD PARA CHECKOUT (NUEVO)
                    'N_Color' => $data['Nombre_Color'] ?? $data['ValorAtributo2'] ?? null,
                    'N_Talla' => $data['Nombre_Talla'] ?? $data['ValorAtributo1'] ?? null,

                    // ✅ ✅✅ CORRECCIÓN CRÍTICA: SIEMPRE GUARDAR INFORMACIÓN DE DESCUENTO INCLUSO CON PRECIO 0
                    'Descuento' => [
                        'Codigo' => $codigo_descuento,
                        'Tipo' => $tipo_descuento,
                        'Valor' => floatval($valor_descuento),
                        'ID_Descuento' => $id_descuento,
                        'Aplicado' => !empty($codigo_descuento) || floatval($valor_descuento) > 0
                    ]
                ];

                $_SESSION['carrito'][] = $item_carrito;
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Producto agregado al carrito correctamente.',
                'carrito_count' => count($_SESSION['carrito'])
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'Error interno del servidor.'
            ]);
        }
        exit;
    }

    // =======================================================
    // 🆕 MÉTODOS AUXILIARES
    // =======================================================

    private function crearDatosBasicosProducto($id_producto, $id_color, $id_talla, $precio_final) {
        // Consultar producto con información de atributos
        $sql = "SELECT 
                    p.ID_Producto,
                    p.ID_Articulo,
                    p.Nombre_Producto,
                    p.Cantidad,
                    p.Foto,
                    p.ID_Atributo1,
                    p.ValorAtributo1,
                    p.ID_Atributo2,
                    p.ValorAtributo2,
                    p.ID_Atributo3,
                    p.ValorAtributo3,
                    a.N_Articulo,
                    pr.Valor as Precio_Base,
                    (pr.Valor + (pr.Valor * (p.Porcentaje / 100))) AS Precio_Final_Calculado
                FROM producto p
                LEFT JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                LEFT JOIN precio pr ON a.ID_Precio = pr.ID_Precio
                WHERE p.ID_Producto = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_producto]);
        $producto_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($producto_data) {
            // Extraer color y talla de los atributos
            $color = '';
            $talla = '';
            
            // Buscar color (ID_TipoAtributo = 2)
            if ($producto_data['ID_Atributo1'] == 2) $color = $producto_data['ValorAtributo1'];
            elseif ($producto_data['ID_Atributo2'] == 2) $color = $producto_data['ValorAtributo2'];
            elseif ($producto_data['ID_Atributo3'] == 2) $color = $producto_data['ValorAtributo3'];
            
            // Buscar talla (ID_TipoAtributo = 3)
            if ($producto_data['ID_Atributo1'] == 3) $talla = $producto_data['ValorAtributo1'];
            elseif ($producto_data['ID_Atributo2'] == 3) $talla = $producto_data['ValorAtributo2'];
            elseif ($producto_data['ID_Atributo3'] == 3) $talla = $producto_data['ValorAtributo3'];
            
            return [
                'ID_Producto' => $producto_data['ID_Producto'],
                'ID_Articulo' => $producto_data['ID_Articulo'],
                'N_Articulo' => $producto_data['N_Articulo'] ?? 'Producto ' . $id_producto,
                'Nombre_Producto' => $producto_data['Nombre_Producto'] ?? $producto_data['N_Articulo'],
                'Precio' => $precio_final ? floatval($precio_final) : floatval($producto_data['Precio_Final_Calculado'] ?? 0),
                'Precio_Base' => floatval($producto_data['Precio_Base'] ?? 0),
                'Foto' => $producto_data['Foto'] ?? 'assets/img/placeholder.png',
                'Tipo' => 'variante',
                // Usar ID_AtributoX para identificar
                'ID_Color' => ($producto_data['ID_Atributo1'] == 2) ? $producto_data['ID_Atributo1'] : 
                            (($producto_data['ID_Atributo2'] == 2) ? $producto_data['ID_Atributo2'] : 
                            (($producto_data['ID_Atributo3'] == 2) ? $producto_data['ID_Atributo3'] : null)),
                'ID_Talla' => ($producto_data['ID_Atributo1'] == 3) ? $producto_data['ID_Atributo1'] : 
                            (($producto_data['ID_Atributo2'] == 3) ? $producto_data['ID_Atributo2'] : 
                            (($producto_data['ID_Atributo3'] == 3) ? $producto_data['ID_Atributo3'] : null)),
                'Nombre_Talla' => $talla ?: 'Única',
                'Nombre_Color' => $color ?: 'Sin color',
                'Cantidad' => intval($producto_data['Cantidad'] ?? 0)
            ];
        }
        
        // Si no encuentra, devolver datos básicos
        return [
            'ID_Producto' => $id_producto,
            'ID_Articulo' => null,
            'N_Articulo' => 'Producto ' . $id_producto,
            'Nombre_Producto' => 'Producto ' . $id_producto,
            'Precio' => $precio_final ? floatval($precio_final) : 0,
            'Precio_Base' => $precio_final ? floatval($precio_final) : 0,
            'Foto' => 'assets/img/placeholder.png',
            'Tipo' => 'variante',
            'ID_Color' => $id_color,
            'ID_Talla' => $id_talla,
            'Nombre_Talla' => $id_talla ? 'Talla ' . $id_talla : 'Única',
            'Nombre_Color' => $id_color ? 'Color ' . $id_color : 'Sin color',
            'Cantidad' => 999
        ];
    }

    private function crearDatosBasicosArticulo($id_articulo, $precio_final) {
        return [
            'ID_Producto' => null,
            'ID_Articulo' => $id_articulo,
            'N_Articulo' => 'Artículo ' . $id_articulo,
            'Nombre_Producto' => 'Artículo ' . $id_articulo,
            'Precio' => $precio_final ? floatval($precio_final) : 0,
            'Precio_Base' => $precio_final ? floatval($precio_final) : 0,
            'Foto' => 'assets/img/placeholder.png',
            'Tipo' => 'base',
            'ID_Color' => 'base',
            'ID_Talla' => null,
            'Nombre_Talla' => 'Única',
            'Nombre_Color' => 'Base',
            'Cantidad' => 999
        ];
    }

    // =======================================================
    // Eliminar producto del carrito
    // =======================================================
    public function eliminar() {
        if (isset($_GET['id']) && isset($_SESSION['carrito'][$_GET['id']])) {
            $producto_eliminado = $_SESSION['carrito'][$_GET['id']]['N_Articulo'];
            unset($_SESSION['carrito'][$_GET['id']]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);
            // $_SESSION['mensaje_ok'] = "{$producto_eliminado} eliminado del carrito.";
        }
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // 🧹 Vaciar todo el carrito
    // =======================================================
    public function vaciar() {
        unset($_SESSION['carrito']);
        $_SESSION['mensaje_ok'] = "Carrito vaciado correctamente.";
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
            
            $item = $_SESSION['carrito'][$index];
            $producto = new Producto($this->db);
            
            if ($item['Tipo'] === 'variante' && $item['ID_Producto']) {
                $stock_disponible = $producto->verificarStock($item['ID_Producto'], $nueva_cantidad, 'variante');
            } else {
                $stock_disponible = $producto->verificarStock($item['ID_Articulo'], $nueva_cantidad, 'base');
            }
            
            if (!$stock_disponible) {
                // $_SESSION['mensaje_error'] = "No hay suficiente stock disponible.";
            } else {
                $_SESSION['carrito'][$index]['Cantidad'] = $nueva_cantidad;
                // $_SESSION['mensaje_ok'] = "Cantidad actualizada correctamente.";
            }
        }
        
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }
}
?>