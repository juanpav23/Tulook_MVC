<?php
class DescuentoService {
    private $db;
    private $descuentoModel;

    public function __construct($db) {
        $this->db = $db;
        require_once "models/Descuento.php";
        $this->descuentoModel = new Descuento($db);
    }

    /**
     * Obtiene el precio final con descuento aplicado
     */
    public function calcularPrecioFinal($precioOriginal, $idArticulo = null, $idProducto = null, $idCategoria = null) {
        $descuento = $this->obtenerDescuentoAplicable($idArticulo, $idProducto, $idCategoria);
        
        if (!$descuento) {
            return [
                'precio_original' => $precioOriginal,
                'precio_final' => $precioOriginal,
                'descuento' => null,
                'ahorro' => 0,
                'tiene_descuento' => false
            ];
        }

        // Verificar si el descuento requiere monto mínimo (es automático)
        if ($descuento['Monto_Minimo'] > 0) {
            return [
                'precio_original' => $precioOriginal,
                'precio_final' => $precioOriginal,
                'descuento' => null,
                'ahorro' => 0,
                'tiene_descuento' => false
            ];
        }

        $precioFinal = $this->aplicarDescuento($precioOriginal, $descuento);
        $ahorro = $precioOriginal - $precioFinal;

        return [
            'precio_original' => $precioOriginal,
            'precio_final' => $precioFinal,
            'descuento' => $descuento,
            'ahorro' => $ahorro,
            'tiene_descuento' => true
        ];
    }

    /**
     * Obtiene el descuento aplicable
     */
    public function obtenerDescuentoAplicable($idArticulo = null, $idProducto = null, $idCategoria = null) {
        $now = date('Y-m-d H:i:s');
        $descuento = null;
        
        // Primero buscar para producto específico
        if ($idProducto) {
            $query = "SELECT * FROM descuento 
                      WHERE ID_Producto = ? 
                      AND Activo = 1 
                      AND Monto_Minimo = 0
                      AND FechaInicio <= ? 
                      AND FechaFin >= ?
                      AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                      ORDER BY 
                        CASE 
                            WHEN Tipo = 'Porcentaje' THEN Valor
                            ELSE 0
                        END DESC
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$idProducto, $now, $now]);
            $descuento = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Si no hay para producto, buscar para artículo
        if (!$descuento && $idArticulo) {
            $query = "SELECT * FROM descuento 
                      WHERE ID_Articulo = ? 
                      AND Activo = 1 
                      AND Monto_Minimo = 0
                      AND FechaInicio <= ? 
                      AND FechaFin >= ?
                      AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                      ORDER BY 
                        CASE 
                            WHEN Tipo = 'Porcentaje' THEN Valor
                            ELSE 0
                        END DESC
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$idArticulo, $now, $now]);
            $descuento = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Si no hay para artículo, buscar para categoría
        if (!$descuento && $idCategoria) {
            $query = "SELECT * FROM descuento 
                      WHERE ID_Categoria = ? 
                      AND Activo = 1 
                      AND Monto_Minimo = 0
                      AND FechaInicio <= ? 
                      AND FechaFin >= ?
                      AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                      ORDER BY 
                        CASE 
                            WHEN Tipo = 'Porcentaje' THEN Valor
                            ELSE 0
                        END DESC
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$idCategoria, $now, $now]);
            $descuento = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return $descuento;
    }

    /**
     * Aplica el descuento al precio
     */
    private function aplicarDescuento($precio, $descuento) {
        if ($descuento['Tipo'] === 'Porcentaje') {
            $precioConDescuento = $precio * (1 - ($descuento['Valor'] / 100));
            return round($precioConDescuento, 2);
        } else {
            $precioFinal = $precio - $descuento['Valor'];
            return $precioFinal > 0 ? round($precioFinal, 2) : 0;
        }
    }

    /**
     * Obtiene información formateada del descuento
     */
    public function obtenerInfoDescuento($precioOriginal, $idArticulo = null, $idProducto = null, $idCategoria = null) {
        $resultado = $this->calcularPrecioFinal($precioOriginal, $idArticulo, $idProducto, $idCategoria);
        
        if (!$resultado['tiene_descuento']) {
            return null;
        }

        $descuento = $resultado['descuento'];
        $porcentajeAhorro = $precioOriginal > 0 ? round(($resultado['ahorro'] / $precioOriginal) * 100, 1) : 0;
        
        return [
            'tipo' => $descuento['Tipo'],
            'valor' => $descuento['Valor'],
            'codigo' => $descuento['Codigo'],
            'precio_original' => $precioOriginal,
            'precio_final' => $resultado['precio_final'],
            'ahorro' => $resultado['ahorro'],
            'porcentaje_ahorro' => $porcentajeAhorro,
            'tiene_descuento' => true,
            'es_automatico' => false
        ];
    }

    /**
     * Verifica si un usuario ganó descuentos automáticos (NO GENERA NUEVOS CÓDIGOS)
     */
    public function verificarDescuentosGanados($id_usuario, $monto_compra) {
        // Obtener descuentos automáticos que cumplan con el monto mínimo
        $descuentos_ganados = $this->descuentoModel->obtenerDescuentosGanados($id_usuario, $monto_compra);
        
        $descuentos_registrados = [];
        foreach ($descuentos_ganados as $descuento) {
            // Verificar si el usuario YA ganó este descuento antes
            $ya_ganado = $this->verificarDescuentoYaGanado($id_usuario, $descuento['ID_Descuento']);
            if (!$ya_ganado) {
                // Registrar que el usuario ganó acceso a este descuento existente
                $this->registrarDescuentoGanado($id_usuario, $descuento);
                $descuentos_registrados[] = $descuento;
            }
        }
        
        return $descuentos_registrados;
    }

    /**
     * Registra que un usuario ganó acceso a un descuento existente
     */
    /**
 * Registra que un usuario ganó acceso a un descuento existente
 */
private function registrarDescuentoGanado($id_usuario, $descuento) {
    error_log("=== registrarDescuentoGanado ===");
    error_log("Usuario: {$id_usuario}, Descuento: {$descuento['ID_Descuento']}");
    
    // ✅ CORRECTO: Insertar con Usos = 0 y Fecha_Ultimo_Uso = NULL
    // Cuando sea usado, se actualizará a Usos = 1 y Fecha_Ultimo_Uso = ahora
    $query = "INSERT INTO descuento_usuario (ID_Descuento, ID_Usuario, Usos, Fecha_Ultimo_Uso) 
              VALUES (?, ?, 0, NULL) 
              ON DUPLICATE KEY UPDATE 
                Usos = VALUES(Usos), 
                Fecha_Ultimo_Uso = VALUES(Fecha_Ultimo_Uso)";
    
    $stmt = $this->db->prepare($query);
    $resultado = $stmt->execute([$descuento['ID_Descuento'], $id_usuario]);
    
    if ($resultado) {
        error_log("✅ Descuento ganado registrado (Usos = 0, Fecha = NULL)");
    } else {
        error_log("❌ Error al registrar descuento ganado");
    }
    
    return $resultado;
}
    /**
     * Verifica si un usuario ya ganó un descuento
     */
    private function verificarDescuentoYaGanado($id_usuario, $id_descuento) {
        $query = "SELECT COUNT(*) as total FROM descuento_usuario 
                  WHERE ID_Descuento = ? AND ID_Usuario = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_descuento, $id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($result['total'] > 0);
    }

    /**
     * Obtiene códigos de descuentos ganados (USA LOS CÓDIGOS EXISTENTES, NO GENERA NUEVOS)
     */
    public function obtenerCodigosDescuentosGanados($descuentos_ganados) {
        $codigos_info = [];
        
        foreach ($descuentos_ganados as $descuento) {
            // Usar el código existente creado por el administrador
            $codigos_info[] = [
                'codigo' => $descuento['Codigo'],
                'tipo' => $descuento['Tipo'],
                'valor' => $descuento['Valor'],
                'descripcion' => $this->getDescripcionDescuento($descuento),
                'fecha_fin' => $descuento['FechaFin'],
                'monto_minimo' => $descuento['Monto_Minimo'],
                'usos_restantes_usuario' => $descuento['Max_Usos_Usuario'] ?? 0,
                'usos_restantes_global' => $descuento['Max_Usos_Global'] > 0 ? 
                                          $descuento['Max_Usos_Global'] - $descuento['Usos_Globales'] : 
                                          'Ilimitado'
            ];
        }
        
        return $codigos_info;
    }

    private function getDescripcionDescuento($descuento) {
        $tipo = $descuento['Tipo'] === 'Porcentaje' ? '%' : 'COP';
        $valor = $descuento['Valor'] . $tipo;
        
        if ($descuento['Monto_Minimo'] > 0) {
            return "Descuento automático ganado - {$valor} (Mínimo: $" . number_format($descuento['Monto_Minimo'], 0) . ")";
        }
        
        return "Descuento manual - {$valor}";
    }

    /**
     * Valida un código de descuento
     */
    public function validarCodigoDescuento($codigo, $id_usuario) {
        return $this->descuentoModel->validarCodigo($codigo, $id_usuario);
    }

    /**
     * Registra el uso de un descuento - ¡VERSIÓN CORREGIDA!
     * Ahora usa registrarUsoCompleto que incrementa ambos contadores
     */
    public function registrarUsoDescuento($id_descuento, $id_usuario) {
        // Usar el método registrarUsoCompleto que ya está implementado
        // Este método incrementa tanto Usos_Globales como el registro en descuento_usuario
        return $this->descuentoModel->registrarUsoCompleto($id_descuento, $id_usuario);
    }

    /**
     * Obtiene los descuentos disponibles para un usuario
     */
    public function obtenerDescuentosUsuario($id_usuario) {
        return $this->descuentoModel->obtenerDescuentosVigentesUsuario($id_usuario);
    }

    /**
     * Obtiene descuentos ganados por un usuario
     */
    public function obtenerDescuentosGanadosUsuario($id_usuario) {
        $now = date('Y-m-d H:i:s');
        
        $query = "SELECT d.*, du.Usos
                  FROM descuento d
                  INNER JOIN descuento_usuario du ON d.ID_Descuento = du.ID_Descuento
                  WHERE du.ID_Usuario = ?
                    AND d.Activo = 1
                    AND d.FechaInicio <= ?
                    AND d.FechaFin >= ?
                    AND d.Monto_Minimo > 0
                  ORDER BY d.FechaFin DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id_usuario, $now, $now]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcula el descuento total aplicable al carrito
     */
    public function calcularDescuentoCarrito($carrito, $descuento_aplicado) {
        if (!$descuento_aplicado) {
            return [
                'subtotal' => 0,
                'descuento' => 0,
                'total' => 0
            ];
        }

        $subtotal = 0;
        foreach ($carrito as $item) {
            $subtotal += ($item['Precio'] * $item['Cantidad']);
        }

        $monto_descuento = 0;
        
        if ($descuento_aplicado['Tipo'] === 'Porcentaje') {
            $monto_descuento = $subtotal * ($descuento_aplicado['Valor'] / 100);
        } else {
            $monto_descuento = min($descuento_aplicado['Valor'], $subtotal);
        }

        $total_con_descuento = $subtotal - $monto_descuento;

        return [
            'subtotal' => $subtotal,
            'descuento' => $monto_descuento,
            'total' => $total_con_descuento,
            'descuento_info' => $descuento_aplicado
        ];
    }

    /**
     * Aplica descuento a un producto específico
     */
    public function aplicarDescuentoProducto($precio, $descuento) {
        if (!$descuento) {
            return $precio;
        }

        if ($descuento['Tipo'] === 'Porcentaje') {
            return $precio * (1 - ($descuento['Valor'] / 100));
        } else {
            return max(0, $precio - $descuento['Valor']);
        }
    }

    /**
     * Método adicional: Verifica y actualiza contadores de un descuento
     */
    public function verificarYActualizarDescuento($id_descuento, $id_usuario) {
        // Verificar si el descuento es válido
        $valido = $this->descuentoModel->puedeUsarDescuento($id_descuento, $id_usuario);
        
        if ($valido) {
            // Registrar el uso
            return $this->registrarUsoDescuento($id_descuento, $id_usuario);
        }
        
        return false;
    }
}
?>