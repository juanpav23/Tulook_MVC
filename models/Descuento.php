<?php
class Descuento {
    private $conn;
    private $table_name = "descuento";

    public function __construct($db) {
        if (!$db) {
            throw new Exception("Conexión a DB no proporcionada al modelo Descuento");
        }
        $this->conn = $db;
    }

    // 🔹 MÉTODO 1: Obtener todos los descuentos con relaciones
    public function obtenerTodos() {
        $query = "SELECT d.*, 
                         a.N_Articulo as ArticuloNombre,
                         p.Nombre_Producto as ProductoNombre,
                         c.N_Categoria as CategoriaNombre,
                         CASE 
                            WHEN d.Activo = 0 THEN 'inactivo'
                            WHEN d.FechaFin < NOW() THEN 'expirado' 
                            WHEN d.FechaInicio > NOW() THEN 'programado'
                            WHEN d.Usos_Globales >= d.Max_Usos_Global AND d.Max_Usos_Global > 0 THEN 'agotado'
                            ELSE 'activo'
                         END as EstadoVigencia,
                         (SELECT COUNT(DISTINCT ID_Usuario) FROM descuento_usuario WHERE ID_Descuento = d.ID_Descuento) as UsuariosUnicos
                  FROM " . $this->table_name . " d
                  LEFT JOIN articulo a ON d.ID_Articulo = a.ID_Articulo
                  LEFT JOIN producto p ON d.ID_Producto = p.ID_Producto
                  LEFT JOIN categoria c ON d.ID_Categoria = c.ID_Categoria
                  ORDER BY d.FechaInicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 2: Obtener descuentos vigentes para usuario (MANUALES Y AUTOMÁTICOS GANADOS)
    public function obtenerDescuentosVigentesUsuario($id_usuario) {
        $now = date('Y-m-d H:i:s');
        
        $query = "SELECT d.*, 
                         COALESCE(du.Usos, 0) as usos_usuario,
                         CASE 
                            WHEN d.Activo = 0 THEN 'inactivo'
                            WHEN d.FechaFin < ? THEN 'expirado'
                            WHEN d.FechaInicio > ? THEN 'programado'
                            WHEN d.Usos_Globales >= d.Max_Usos_Global AND d.Max_Usos_Global > 0 THEN 'agotado'
                            WHEN COALESCE(du.Usos, 0) >= d.Max_Usos_Usuario AND d.Max_Usos_Usuario > 0 THEN 'usado'
                            ELSE 'disponible'
                         END as Estado,
                         CASE
                            WHEN d.Monto_Minimo > 0 AND du.ID_DescuentoUsuario IS NOT NULL THEN 'automatico_ganado'
                            WHEN d.Monto_Minimo > 0 THEN 'automatico_no_ganado'
                            ELSE 'manual'
                         END as TipoDescuento
                  FROM " . $this->table_name . " d
                  LEFT JOIN descuento_usuario du ON d.ID_Descuento = du.ID_Descuento 
                    AND du.ID_Usuario = ?
                  WHERE d.Activo = 1 
                    AND d.FechaInicio <= ? 
                    AND d.FechaFin >= ?
                    AND (
                        -- Descuentos manuales (Monto_Minimo = 0) - siempre disponibles
                        (d.Monto_Minimo = 0)
                        OR 
                        -- Descuentos automáticos que el usuario YA GANÓ
                        (d.Monto_Minimo > 0 AND du.ID_DescuentoUsuario IS NOT NULL)
                    )
                    AND (d.Max_Usos_Global = 0 OR d.Usos_Globales < d.Max_Usos_Global)
                  ORDER BY 
                    CASE 
                        WHEN d.Monto_Minimo > 0 AND du.ID_DescuentoUsuario IS NOT NULL THEN 1
                        WHEN d.Monto_Minimo = 0 THEN 2
                        ELSE 3
                    END,
                    d.FechaFin ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$now, $now, $id_usuario, $now, $now]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 3: Obtener descuentos vigentes (para admin)
    public function obtenerDescuentosVigentes() {
        $now = date('Y-m-d H:i:s');
        
        $query = "SELECT d.*, 
                         a.N_Articulo as ArticuloNombre,
                         p.Nombre_Producto as ProductoNombre,
                         c.N_Categoria as CategoriaNombre,
                         CASE 
                            WHEN d.Monto_Minimo > 0 THEN 'automático'
                            ELSE 'manual'
                         END as TipoDescuento
                  FROM " . $this->table_name . " d
                  LEFT JOIN articulo a ON d.ID_Articulo = a.ID_Articulo
                  LEFT JOIN producto p ON d.ID_Producto = p.ID_Producto
                  LEFT JOIN categoria c ON d.ID_Categoria = c.ID_Categoria
                  WHERE d.Activo = 1 
                    AND d.FechaInicio <= ? 
                    AND d.FechaFin >= ?
                  ORDER BY d.FechaInicio DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$now, $now]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 4: Obtener por ID con relaciones
    public function obtenerPorId($id) {
        $query = "SELECT d.*, 
                         a.N_Articulo as ArticuloNombre,
                         p.Nombre_Producto as ProductoNombre,
                         c.N_Categoria as CategoriaNombre,
                         (SELECT COUNT(*) FROM descuento_usuario du WHERE du.ID_Descuento = d.ID_Descuento) as TotalUsosUsuarios
                  FROM " . $this->table_name . " d
                  LEFT JOIN articulo a ON d.ID_Articulo = a.ID_Articulo
                  LEFT JOIN producto p ON d.ID_Producto = p.ID_Producto
                  LEFT JOIN categoria c ON d.ID_Categoria = c.ID_Categoria
                  WHERE d.ID_Descuento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 5: Obtener mejor descuento disponible - ✅ CORREGIDO
    public function obtenerMejorDescuento($id_articulo = null, $id_producto = null, $id_categoria = null) {
        $now = date('Y-m-d H:i:s');
        
        // Primero buscar descuento para producto específico
        if ($id_producto) {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE ID_Producto = ? 
                      AND Activo = 1 
                      AND Monto_Minimo = 0
                      AND FechaInicio <= ? 
                      AND FechaFin >= ?
                      AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                      ORDER BY 
                        CASE 
                            WHEN Tipo = 'Porcentaje' THEN Valor
                            WHEN Tipo = 'ValorFijo' THEN Valor
                            ELSE 0
                        END DESC
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_producto, $now, $now]);
            $descuento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($descuento) return $descuento;
        }

        // Luego buscar descuento para artículo
        if ($id_articulo) {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE ID_Articulo = ? 
                      AND Activo = 1 
                      AND Monto_Minimo = 0
                      AND FechaInicio <= ? 
                      AND FechaFin >= ?
                      AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                      ORDER BY 
                        CASE 
                            WHEN Tipo = 'Porcentaje' THEN Valor
                            WHEN Tipo = 'ValorFijo' THEN Valor
                            ELSE 0
                        END DESC
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_articulo, $now, $now]);
            $descuento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($descuento) return $descuento;
        }

        // Finalmente buscar descuento para categoría
        if ($id_categoria) {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE ID_Categoria = ? 
                      AND Activo = 1 
                      AND Monto_Minimo = 0
                      AND FechaInicio <= ? 
                      AND FechaFin >= ?
                      AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                      ORDER BY 
                        CASE 
                            WHEN Tipo = 'Porcentaje' THEN Valor
                            WHEN Tipo = 'ValorFijo' THEN Valor
                            ELSE 0
                        END DESC
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_categoria, $now, $now]);
            $descuento = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($descuento) return $descuento;
        }

        return null;
    }

    // 🔹 MÉTODO 6: Generar código automático (SOLO PARA ADMIN)
    public function generarCodigoAutomatico($tipo = 'AUTO') {
        $prefix = $tipo == 'AUTO' ? 'AUTO-' : 'MANUAL-';
        $codigo = $prefix . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        
        // Verificar que no exista
        while ($this->codigoExiste($codigo)) {
            $codigo = $prefix . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
        }
        
        return $codigo;
    }

    // 🔹 MÉTODO 7: Crear descuento (SOLO ADMIN)
    public function crear($datos) {
        // Si no viene código, generar automático
        if (empty($datos['Codigo'])) {
            $datos['Codigo'] = $this->generarCodigoAutomatico('MANUAL');
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (Codigo, ID_Articulo, ID_Producto, ID_Categoria, Tipo, Valor, 
                   Monto_Minimo, Max_Usos_Global, Max_Usos_Usuario, Usos_Globales,
                   FechaInicio, FechaFin, Activo) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $datos['Codigo'],
            $datos['ID_Articulo'] ?? null,
            $datos['ID_Producto'] ?? null,
            $datos['ID_Categoria'] ?? null,
            $datos['Tipo'],
            $datos['Valor'],
            $datos['Monto_Minimo'] ?? 0,
            $datos['Max_Usos_Global'] ?? 0,
            $datos['Max_Usos_Usuario'] ?? 0,
            $datos['Usos_Globales'] ?? 0,
            $datos['FechaInicio'],
            $datos['FechaFin'],
            $datos['Activo'] ?? 1
        ]);
    }

    // 🔹 MÉTODO 8: Actualizar descuento (SOLO ADMIN)
    public function actualizar($id, $datos) {
        $query = "UPDATE " . $this->table_name . " 
                  SET Codigo = ?, ID_Articulo = ?, ID_Producto = ?, ID_Categoria = ?, 
                      Tipo = ?, Valor = ?, Monto_Minimo = ?, Max_Usos_Global = ?, 
                      Max_Usos_Usuario = ?, FechaInicio = ?, FechaFin = ?, Activo = ?
                  WHERE ID_Descuento = ?";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            $datos['Codigo'],
            $datos['ID_Articulo'] ?? null,
            $datos['ID_Producto'] ?? null,
            $datos['ID_Categoria'] ?? null,
            $datos['Tipo'],
            $datos['Valor'],
            $datos['Monto_Minimo'] ?? 0,
            $datos['Max_Usos_Global'] ?? 0,
            $datos['Max_Usos_Usuario'] ?? 0,
            $datos['FechaInicio'],
            $datos['FechaFin'],
            $datos['Activo'] ?? 1,
            $id
        ]);
    }

    // 🔹 MÉTODO 9: Eliminar descuento (SOLO ADMIN)
    public function eliminar($id) {
        // Primero eliminar registros relacionados en descuento_usuario
        $queryDeleteUsuarios = "DELETE FROM descuento_usuario WHERE ID_Descuento = ?";
        $stmt1 = $this->conn->prepare($queryDeleteUsuarios);
        $stmt1->execute([$id]);

        // Luego eliminar el descuento
        $query = "DELETE FROM " . $this->table_name . " WHERE ID_Descuento = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    // 🔹 MÉTODO 10: Verificar si código existe
    public function codigoExiste($codigo, $id_excluir = null) {
        $query = "SELECT ID_Descuento FROM " . $this->table_name . " WHERE Codigo = ?";
        $params = [$codigo];
        
        if ($id_excluir) {
            $query .= " AND ID_Descuento != ?";
            $params[] = $id_excluir;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    // 🔹 MÉTODO 11: Validar código de descuento (PARA USUARIO) - ✅ TOTALMENTE CORREGIDO
   // 🔹 MÉTODO 11: Validar código de descuento (PARA USUARIO) - ✅ CORREGIDO PARA DESCUENTOS GANADOS
public function validarCodigo($codigo, $id_usuario) {
    error_log("=== VALIDAR CÓDIGO INICIADO ===");
    error_log("Código: {$codigo}");
    error_log("Usuario: {$id_usuario}");
    
    $now = date('Y-m-d H:i:s');
    error_log("Fecha actual: {$now}");
    
    // 1. Buscar descuento con este código
    $query = "SELECT d.*, 
                     COALESCE(du.Usos, 0) as usos_usuario,
                     du.ID_DescuentoUsuario as tiene_registro
              FROM " . $this->table_name . " d
              LEFT JOIN descuento_usuario du ON d.ID_Descuento = du.ID_Descuento 
                AND du.ID_Usuario = ?
              WHERE d.Codigo = ? 
                AND d.Activo = 1 
                AND d.FechaInicio <= ? 
                AND d.FechaFin >= ?";
    
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$id_usuario, $codigo, $now, $now]);
    $descuento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$descuento) {
        error_log("❌ Código no encontrado o no vigente");
        return ['valido' => false, 'mensaje' => 'Código no válido o expirado'];
    }
    
    error_log("✅ Descuento encontrado:");
    error_log(" - ID: " . $descuento['ID_Descuento']);
    error_log(" - Tipo: " . $descuento['Tipo']);
    error_log(" - Valor: " . $descuento['Valor']);
    error_log(" - Monto_Minimo: " . $descuento['Monto_Minimo']);
    error_log(" - Usos_Globales: " . $descuento['Usos_Globales']);
    error_log(" - Max_Usos_Global: " . $descuento['Max_Usos_Global']);
    error_log(" - usos_usuario: " . $descuento['usos_usuario']);
    error_log(" - tiene_registro: " . $descuento['tiene_registro']);
    error_log(" - Max_Usos_Usuario: " . $descuento['Max_Usos_Usuario']);
    
    // ✅ CORRECCIÓN: Para descuentos automáticos, verificar si TIENE REGISTRO (no usos)
    if ($descuento['Monto_Minimo'] > 0 && empty($descuento['tiene_registro'])) {
        error_log("❌ Descuento automático pero usuario NO lo ha ganado");
        return ['valido' => false, 'mensaje' => 'Este descuento requiere cumplir un monto mínimo'];
    }
    
    // Verificar límites globales
    if ($descuento['Max_Usos_Global'] > 0 && 
        $descuento['Usos_Globales'] >= $descuento['Max_Usos_Global']) {
        error_log("❌ Límite global alcanzado");
        return ['valido' => false, 'mensaje' => 'Este descuento ya alcanzó su límite de uso'];
    }
    
    // Verificar límites por usuario
    if ($descuento['Max_Usos_Usuario'] > 0 && 
        $descuento['usos_usuario'] >= $descuento['Max_Usos_Usuario']) {
        error_log("❌ Límite por usuario alcanzado");
        return [
            'valido' => false, 
            'mensaje' => 'Ya has usado este descuento el máximo de veces permitido'
        ];
    }
    
    error_log("✅ Código válido");
    return [
        'valido' => true, 
        'descuento' => $descuento, 
        'mensaje' => 'Código válido',
        'tieneLimites' => ($descuento['Max_Usos_Usuario'] > 0 || $descuento['Max_Usos_Global'] > 0)
    ];
}

    // 🔹 MÉTODO 12: Obtener usos por usuario
    public function obtenerUsosUsuario($id_descuento, $id_usuario) {
        try {
            $query = "SELECT Usos FROM descuento_usuario 
                      WHERE ID_Descuento = ? AND ID_Usuario = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_descuento, $id_usuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result ? intval($result['Usos']) : 0;
            
        } catch (Exception $e) {
            error_log("Error en obtenerUsosUsuario: " . $e->getMessage());
            return 0;
        }
    }

    // 🔹 MÉTODO 13: Registrar uso de descuento por usuario - ✅ TOTALMENTE CORREGIDO
    public function registrarUsoUsuario($id_descuento, $id_usuario) {
        try {
            error_log("=== registrarUsoUsuario ===");
            
            // Verificar si ya existe registro
            $query_check = "SELECT Usos FROM descuento_usuario 
                           WHERE ID_Descuento = ? AND ID_Usuario = ?";
            $stmt = $this->conn->prepare($query_check);
            $stmt->execute([$id_descuento, $id_usuario]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $now = date('Y-m-d H:i:s');
            
            if ($existe) {
                // Si existe, incrementar usos
                $nuevos_usos = intval($existe['Usos']) + 1;
                $query = "UPDATE descuento_usuario 
                          SET Usos = ?, Fecha_Ultimo_Uso = ?
                          WHERE ID_Descuento = ? AND ID_Usuario = ?";
                
                $stmt = $this->conn->prepare($query);
                $resultado = $stmt->execute([$nuevos_usos, $now, $id_descuento, $id_usuario]);
                
                error_log("UPDATE - Usos incrementados: " . $nuevos_usos);
                
            } else {
                // Si no existe, crear nuevo registro
                $query = "INSERT INTO descuento_usuario 
                          (ID_Descuento, ID_Usuario, Usos, Fecha_Ultimo_Uso) 
                          VALUES (?, ?, 1, ?)";
                
                $stmt = $this->conn->prepare($query);
                $resultado = $stmt->execute([$id_descuento, $id_usuario, $now]);
                
                error_log("INSERT - Nuevo registro creado");
            }
            
            return $resultado;
            
        } catch (Exception $e) {
            error_log("Error en registrarUsoUsuario: " . $e->getMessage());
            return false;
        }
    }

    // 🔹 MÉTODO 14: Incrementar usos globales
    public function incrementarUsosGlobales($id_descuento) {
        try {
            $query = "UPDATE " . $this->table_name . " 
                      SET Usos_Globales = COALESCE(Usos_Globales, 0) + 1 
                      WHERE ID_Descuento = ?";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$id_descuento]);
            
            if ($result && $stmt->rowCount() > 0) {
                error_log("✅ Usos globales incrementados para descuento ID: " . $id_descuento);
                return true;
            } else {
                error_log("❌ No se pudo incrementar usos globales");
                return false;
            }
        } catch (Exception $e) {
            error_log("Error en incrementarUsosGlobales: " . $e->getMessage());
            return false;
        }
    }

    // 🔹 MÉTODO 15: Verificar si usuario puede usar descuento
    public function puedeUsarDescuento($id_descuento, $id_usuario) {
        // Obtener descuento
        $descuento = $this->obtenerPorId($id_descuento);
        if (!$descuento || !$descuento['Activo']) {
            return false;
        }

        // Verificar fechas
        $now = date('Y-m-d H:i:s');
        if ($descuento['FechaInicio'] > $now || $descuento['FechaFin'] < $now) {
            return false;
        }

        // Verificar límite global
        if ($descuento['Max_Usos_Global'] > 0 && 
            $descuento['Usos_Globales'] >= $descuento['Max_Usos_Global']) {
            return false;
        }

        // Verificar límite por usuario
        if ($descuento['Max_Usos_Usuario'] > 0) {
            $usosUsuario = $this->obtenerUsosUsuario($id_descuento, $id_usuario);
            if ($usosUsuario >= $descuento['Max_Usos_Usuario']) {
                return false;
            }
        }

        // Para descuentos automáticos, verificar que el usuario lo haya ganado
        if ($descuento['Monto_Minimo'] > 0) {
            $query_ganado = "SELECT COUNT(*) as ganado FROM descuento_usuario 
                            WHERE ID_Descuento = ? AND ID_Usuario = ?";
            $stmt = $this->conn->prepare($query_ganado);
            $stmt->execute([$id_descuento, $id_usuario]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['ganado'] == 0) {
                return false;
            }
        }

        return true;
    }

    // 🔹 MÉTODO 16: Obtener descuentos ganados por usuario (DESCUENTOS AUTOMÁTICOS)
    public function obtenerDescuentosGanados($id_usuario, $monto_compra) {
        $query = "SELECT d.* 
                  FROM " . $this->table_name . " d
                  WHERE d.Activo = 1 
                    AND d.FechaInicio <= NOW() 
                    AND d.FechaFin >= NOW()
                    AND d.Monto_Minimo > 0 
                    AND d.Monto_Minimo <= ?
                    AND d.ID_Descuento NOT IN (
                        SELECT ID_Descuento FROM descuento_usuario 
                        WHERE ID_Usuario = ?
                    )
                  ORDER BY d.Monto_Minimo DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$monto_compra, $id_usuario]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 17: REGISTRAR DESCUENTO AUTOMÁTICO GANADO
    public function registrarDescuentoGanado($id_descuento, $id_usuario) {
        $now = date('Y-m-d H:i:s');
        
        // Verificar si ya está registrado
        $query_verificar = "SELECT COUNT(*) as existe FROM descuento_usuario 
                           WHERE ID_Descuento = ? AND ID_Usuario = ?";
        $stmt_verificar = $this->conn->prepare($query_verificar);
        $stmt_verificar->execute([$id_descuento, $id_usuario]);
        $existe = $stmt_verificar->fetch(PDO::FETCH_ASSOC);
        
        if ($existe['existe'] > 0) {
            return true; // Ya está registrado
        }
        
        // Registrar que el usuario ganó acceso a este descuento (Usos = 0 porque aún no lo ha usado)
        $query = "INSERT INTO descuento_usuario (ID_Descuento, ID_Usuario, Usos, Fecha_Ultimo_Uso) 
                  VALUES (?, ?, 0, NULL)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_descuento, $id_usuario]);
    }

    // 🔹 MÉTODO 18: Obtener estadísticas de uso
    public function obtenerEstadisticas($id_descuento) {
        $query = "SELECT 
                    d.Usos_Globales,
                    d.Max_Usos_Global,
                    (SELECT COUNT(*) FROM descuento_usuario WHERE ID_Descuento = ?) as TotalUsuarios,
                    (SELECT AVG(Usos) FROM descuento_usuario WHERE ID_Descuento = ?) as PromedioUsosPorUsuario,
                    (SELECT MAX(Usos) FROM descuento_usuario WHERE ID_Descuento = ?) as MaxUsosUsuario
                  FROM " . $this->table_name . " d
                  WHERE d.ID_Descuento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_descuento, $id_descuento, $id_descuento, $id_descuento]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 19: Obtener descuentos por artículo
    public function obtenerDescuentoArticulo($id_articulo) {
        $now = date('Y-m-d H:i:s');
        
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ID_Articulo = ? 
                  AND Activo = 1 
                  AND Monto_Minimo = 0
                  AND FechaInicio <= ? 
                  AND FechaFin >= ?
                  AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                  ORDER BY FechaInicio DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_articulo, $now, $now]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 20: Obtener descuentos por producto
    public function obtenerDescuentoProducto($id_producto) {
        $now = date('Y-m-d H:i:s');
        
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ID_Producto = ? 
                  AND Activo = 1 
                  AND Monto_Minimo = 0
                  AND FechaInicio <= ? 
                  AND FechaFin >= ?
                  AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                  ORDER BY FechaInicio DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_producto, $now, $now]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 21: Obtener descuentos por categoría
    public function obtenerDescuentoCategoria($id_categoria) {
        $now = date('Y-m-d H:i:s');
        
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE ID_Categoria = ? 
                  AND Activo = 1 
                  AND Monto_Minimo = 0
                  AND FechaInicio <= ? 
                  AND FechaFin >= ?
                  AND (Max_Usos_Global = 0 OR Usos_Globales < Max_Usos_Global)
                  ORDER BY FechaInicio DESC LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_categoria, $now, $now]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 🔹 MÉTODO 22: Registrar uso completo (usuario + global) - ✅ NUEVO MÉTODO MEJORADO
    public function registrarUsoCompleto($id_descuento, $id_usuario) {
        try {
            error_log("=== INICIANDO registrarUsoCompleto ===");
            error_log("Descuento ID: " . $id_descuento);
            error_log("Usuario ID: " . $id_usuario);
            
            // 1. Registrar uso por usuario
            $registro_usuario = $this->registrarUsoUsuario($id_descuento, $id_usuario);
            error_log("Registro usuario: " . ($registro_usuario ? "ÉXITO" : "FALLO"));
            
            // 2. Incrementar usos globales
            $registro_global = $this->incrementarUsosGlobales($id_descuento);
            error_log("Registro global: " . ($registro_global ? "ÉXITO" : "FALLO"));
            
            if ($registro_usuario && $registro_global) {
                error_log("✅ Uso completo registrado exitosamente");
                
                // Verificar que se actualizó correctamente
                $usos_usuario_despues = $this->obtenerUsosUsuario($id_descuento, $id_usuario);
                $descuento_despues = $this->obtenerPorId($id_descuento);
                
                error_log("Usos usuario después: " . $usos_usuario_despues);
                error_log("Usos globales después: " . ($descuento_despues['Usos_Globales'] ?? 0));
                
                return true;
            } else {
                error_log("❌ Error al registrar uso completo");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error en registrarUsoCompleto: " . $e->getMessage());
            return false;
        }
    }
}
?>