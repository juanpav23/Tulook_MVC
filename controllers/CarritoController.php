<?php
// controllers/CarritoController.php - VERSIÓN CORREGIDA CON MÉTODOS EXISTENTES
require_once "models/Producto.php";
require_once "models/Descuento.php";

class CarritoController {
    private $db;
    private $descuentoModel;

    public function __construct($db) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = $db;
        $this->descuentoModel = new Descuento($db);
        
        $this->verificarAutenticacion();
    }

    private function verificarAutenticacion() {
        if (!isset($_SESSION['ID_Usuario']) || empty($_SESSION['ID_Usuario'])) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'] ?? BASE_URL . '?c=Carrito&a=carrito';
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }
    }

    public function carrito() {
        $carrito = $_SESSION['carrito'] ?? [];
        
        // Inicializar variables para la vista
        $total = 0;
        $total_descuentos = 0;
        $subtotal_sin_descuentos = 0;
        
        // Procesar cada item del carrito para agregar nombres de atributos
        foreach ($carrito as &$item) {
            // Calcular subtotales
            $precio_original = $item['Precio_Original'] ?? $item['Precio'];
            $precio_final = $item['Precio'];
            $subtotal = $precio_final * $item['Cantidad'];
            $subtotal_original = $precio_original * $item['Cantidad'];
            
            $total += $subtotal;
            $subtotal_sin_descuentos += $subtotal_original;
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
        unset($item);
        
        $_SESSION['carrito'] = $carrito;
        
        // ✅ CORRECCIÓN: Usar el método CORRECTO de tu modelo
        $id_usuario = $_SESSION['ID_Usuario'];
        $descuentos_disponibles = [];
        
        if ($this->descuentoModel) {
            try {
                // ✅ Usar el método que realmente existe en tu modelo
                $descuentos_disponibles = $this->descuentoModel->obtenerDescuentosVigentesUsuario($id_usuario);
            } catch (Exception $e) {
                error_log("Error obteniendo descuentos en carrito: " . $e->getMessage());
                $descuentos_disponibles = [];
            }
        }
        
        // Incluir la vista
        include "views/carrito/carrito.php";
    }

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

    public function agregar() {
        $id_usuario = $_SESSION['ID_Usuario'];
        
        $id_producto = $_POST['id_producto'] ?? ($_GET['id'] ?? null);
        $id_articulo = $_POST['id_articulo'] ?? null;
        $cantidad    = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
        $tipo        = $_POST['tipo'] ?? 'variante';
        
        // Campos de descuento
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

        // Caso 1: Variante (producto)
        if ($tipo === 'variante' && $id_producto) {
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
                $precio_original = floatval($data['Precio_Final']);
                
                // ✅ Usar método CORRECTO: puedeUsarDescuento() de tu modelo
                $precio_a_usar = $precio_original;
                $descuento_info = [
                    'Codigo' => '',
                    'Tipo' => '',
                    'Valor' => 0,
                    'ID_Descuento' => null,
                    'Aplicado' => false
                ];
                
                // Si viene un descuento, validarlo
                if (!empty($id_descuento) && is_numeric($id_descuento)) {
                    error_log("Validando descuento ID: " . $id_descuento);
                    
                    // ✅ Usar el método de tu modelo
                    if ($this->descuentoModel->puedeUsarDescuento($id_descuento, $id_usuario)) {
                        // Obtener detalles del descuento
                        $detalle_descuento = $this->descuentoModel->obtenerPorId($id_descuento);
                        
                        if ($detalle_descuento) {
                            $codigo_descuento = $detalle_descuento['Codigo'] ?? $codigo_descuento;
                            $tipo_descuento = $detalle_descuento['Tipo'] ?? $tipo_descuento;
                            $valor_descuento = $detalle_descuento['Valor'] ?? $valor_descuento;
                            
                            // Calcular precio con descuento
                            if ($tipo_descuento === 'Porcentaje') {
                                $descuento_monto = $precio_original * ($valor_descuento / 100);
                            } else {
                                $descuento_monto = $valor_descuento;
                            }
                            
                            $precio_a_usar = $precio_original - $descuento_monto;
                            if ($precio_a_usar < 0) $precio_a_usar = 0;
                            
                            $descuento_info = [
                                'Codigo' => $codigo_descuento,
                                'Tipo' => $tipo_descuento,
                                'Valor' => floatval($valor_descuento),
                                'ID_Descuento' => intval($id_descuento),
                                'Aplicado' => true
                            ];
                            
                            error_log("Descuento aplicado. Precio original: " . $precio_original . ", Precio final: " . $precio_a_usar);
                        }
                    } else {
                        error_log("Descuento NO puede ser usado o límite alcanzado");
                    }
                } elseif ($precio_final !== null && $precio_final !== '' && floatval($precio_final) < $precio_original) {
                    // Si viene precio_final y es menor al original
                    $precio_a_usar = floatval($precio_final);
                    $descuento_valor = $precio_original - $precio_a_usar;
                    
                    $descuento_info = [
                        'Codigo' => $codigo_descuento,
                        'Tipo' => 'ValorFijo',
                        'Valor' => $descuento_valor,
                        'ID_Descuento' => $id_descuento ? intval($id_descuento) : null,
                        'Aplicado' => true
                    ];
                }
                
                $data['Precio'] = $precio_a_usar;

                // Obtener atributos con nombres
                $atributosArray = [];
                
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
                $data['Descuento'] = $descuento_info;
                
            } else {
                $_SESSION['mensaje_error'] = "❌ Producto no encontrado.";
                header("Location: " . BASE_URL);
                exit;
            }
        }

        // Caso 2: Base (artículo)
        if ($tipo === 'base' && $id_articulo) {
            $data = $producto->readBase($id_articulo);
            if ($data) {
                $data['Tipo'] = 'base';
                $data['ID_Producto'] = null;
                $data['Nombre_Producto'] = $data['N_Articulo'];
                $data['Atributos'] = [];
                $data['Precio'] = $precio_final ? floatval($precio_final) : floatval($data['Precio']);
                
                $descuento_info = [
                    'Codigo' => $codigo_descuento,
                    'Tipo' => $tipo_descuento,
                    'Valor' => floatval($valor_descuento),
                    'ID_Descuento' => $id_descuento ? intval($id_descuento) : null,
                    'Aplicado' => !empty($codigo_descuento) || floatval($valor_descuento) > 0
                ];
                $data['Descuento'] = $descuento_info;
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

        // Validar stock
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

        // Inicializar carrito
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        // Verificar si ya está en el carrito
        $encontrado = false;
        $indice_encontrado = -1;
        
        foreach ($_SESSION['carrito'] as $index => &$item) {
            $mismoProducto = false;
            
            if ($tipo === 'variante') {
                $mismoProducto = (
                    $item['ID_Producto'] == $data['ID_Producto'] &&
                    $item['Tipo'] === 'variante' &&
                    $item['Precio'] == $data['Precio'] &&
                    $item['Descuento']['ID_Descuento'] == ($data['Descuento']['ID_Descuento'] ?? null)
                );
            } else {
                $mismoProducto = (
                    $item['ID_Articulo'] == $data['ID_Articulo'] &&
                    $item['Tipo'] === 'base' &&
                    $item['ID_Producto'] === null &&
                    $item['Precio'] == $data['Precio'] &&
                    $item['Descuento']['ID_Descuento'] == ($data['Descuento']['ID_Descuento'] ?? null)
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
                $indice_encontrado = $index;
                break;
            }
        }
        unset($item);

        if (!$encontrado) {
            $precio_final_carrito = $data['Precio'];
            
            $item_carrito = [
                'ID_Producto' => $data['ID_Producto'] ?? null,
                'ID_Articulo' => $data['ID_Articulo'] ?? $id_articulo,
                'N_Articulo'  => $data['N_Articulo'] ?? $data['Nombre_Producto'] ?? 'Producto ' . ($id_producto ?? $id_articulo),
                'Foto'        => $data['Foto'] ?? 'assets/img/placeholder.png',
                'Precio'      => $precio_final_carrito,
                'Precio_Original' => $data['Precio_Base'] ?? $data['Precio_Final'] ?? $data['Precio'],
                'Tipo'        => $data['Tipo'],
                'Cantidad'    => $cantidad,
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

                'Descuento' => $data['Descuento'] ?? [
                    'Codigo' => $codigo_descuento,
                    'Tipo' => $tipo_descuento,
                    'Valor' => floatval($valor_descuento),
                    'ID_Descuento' => $id_descuento ? intval($id_descuento) : null,
                    'Aplicado' => !empty($codigo_descuento) || floatval($valor_descuento) > 0
                ]
            ];

            $_SESSION['carrito'][] = $item_carrito;
        }

        $_SESSION['mensaje_ok'] = "✅ Producto agregado al carrito correctamente.";
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    // =======================================================
    // ➕ Agregar producto al carrito VÍA AJAX (CORREGIDO)
    // =======================================================
    public function agregarAjax() {
        $id_usuario = $_SESSION['ID_Usuario'];
        
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
                $sql = "SELECT 
                            p.ID_Producto,
                            p.ID_Articulo,
                            p.Nombre_Producto,
                            p.Cantidad,
                            COALESCE(p.Foto, a.Foto) as Foto,
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
                    $data['Precio'] = floatval($data['Precio_Final']);
                }
            }

            if ($tipo === 'base' && $id_articulo) {
                $data = $producto->readBase($id_articulo);
                if ($data) {
                    $data['Tipo'] = 'base';
                    $data['ID_Producto'] = null;
                    $data['Nombre_Producto'] = $data['N_Articulo'];
                    $data['Atributos'] = [];
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
            $precio_a_usar = $precio_original;
            
            // Validar descuento si viene
            $descuento_info = [
                'Codigo' => $codigo_descuento,
                'Tipo' => $tipo_descuento,
                'Valor' => floatval($valor_descuento),
                'ID_Descuento' => $id_descuento ? intval($id_descuento) : null,
                'Aplicado' => false
            ];
            
            if (!empty($id_descuento) && is_numeric($id_descuento)) {
                if ($this->descuentoModel->puedeUsarDescuento($id_descuento, $id_usuario)) {
                    $detalle_descuento = $this->descuentoModel->obtenerPorId($id_descuento);
                    
                    if ($detalle_descuento) {
                        $tipo_descuento = $detalle_descuento['Tipo'] ?? $tipo_descuento;
                        $valor_descuento = $detalle_descuento['Valor'] ?? $valor_descuento;
                        
                        if ($tipo_descuento === 'Porcentaje') {
                            $descuento_monto = $precio_original * ($valor_descuento / 100);
                        } else {
                            $descuento_monto = $valor_descuento;
                        }
                        
                        $precio_a_usar = $precio_original - $descuento_monto;
                        if ($precio_a_usar < 0) $precio_a_usar = 0;
                        
                        $descuento_info = [
                            'Codigo' => $detalle_descuento['Codigo'] ?? $codigo_descuento,
                            'Tipo' => $tipo_descuento,
                            'Valor' => floatval($valor_descuento),
                            'ID_Descuento' => intval($id_descuento),
                            'Aplicado' => true
                        ];
                    }
                }
            } elseif ($precio_final !== null && $precio_final !== '' && floatval($precio_final) < $precio_original) {
                $precio_a_usar = floatval($precio_final);
                $descuento_info['Aplicado'] = true;
            }

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
                        $item['Precio'] == $precio_a_usar &&
                        $item['Descuento']['ID_Descuento'] == $descuento_info['ID_Descuento']
                    );
                } else {
                    $mismoProducto = (
                        $item['ID_Articulo'] == $data['ID_Articulo'] &&
                        $item['Tipo'] === 'base' &&
                        $item['ID_Producto'] === null &&
                        $item['Precio'] == $precio_a_usar &&
                        $item['Descuento']['ID_Descuento'] == $descuento_info['ID_Descuento']
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

                    'Descuento' => $descuento_info
                ];

                $_SESSION['carrito'][] = $item_carrito;
            }

            echo json_encode([
                'success' => true, 
                'message' => 'Producto agregado al carrito correctamente.',
                'carrito_count' => count($_SESSION['carrito'])
            ]);
            
        } catch (Exception $e) {
            error_log("Error en agregarAjax: " . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Error interno del servidor'
            ]);
        }
        exit;
    }

    public function eliminar() {
        if (isset($_GET['id']) && isset($_SESSION['carrito'][$_GET['id']])) {
            $producto_eliminado = $_SESSION['carrito'][$_GET['id']]['N_Articulo'];
            unset($_SESSION['carrito'][$_GET['id']]);
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);
        }
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

    public function vaciar() {
        unset($_SESSION['carrito']);
        $_SESSION['mensaje_ok'] = "Carrito vaciado correctamente.";
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }

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
                $sql = "SELECT Cantidad FROM producto WHERE ID_Producto = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([$item['ID_Producto']]);
                $stock = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($stock && $stock['Cantidad'] >= $nueva_cantidad) {
                    $_SESSION['carrito'][$index]['Cantidad'] = $nueva_cantidad;
                }
            }
        }
        
        header("Location: " . BASE_URL . "?c=Carrito&a=carrito");
        exit;
    }
}
?>