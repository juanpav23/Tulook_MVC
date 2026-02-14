<?php
class Pedido
{
    private $conn;
    private $table_name = "factura";
    private $table_seguimiento = "pedido_seguimiento";
    private $table_factura_producto = "factura_producto";
    private $table_producto = "producto";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // OBTENER PEDIDO POR ID CON DETALLE
    public function obtenerPorId($id) {
        // Obtener información del pedido
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo, 
                    u.Celular,
                    d.Direccion,
                    d.Ciudad,
                    d.Departamento,
                    d.CodigoPostal,
                    mp.T_Pago as MetodoPago,
                    ue.Nombre as NombreEnvio,
                    uent.Nombre as NombreEntrega
                FROM " . $this->table_name . " f
                LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                LEFT JOIN direccion d ON f.ID_Direccion = d.ID_Direccion
                LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                LEFT JOIN usuario ue ON f.Usuario_Envio = ue.ID_Usuario
                LEFT JOIN usuario uent ON f.Usuario_Entrega = uent.ID_Usuario
                WHERE f.ID_Factura = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pedido) {
            // Obtener productos del pedido
            $queryProductos = "SELECT 
                                fp.*,
                                a.N_Articulo as NombreProducto,
                                a.Foto,
                                p.Porcentaje,
                                p.Cantidad as StockActual,
                                av1.Valor as Atributo1,
                                av2.Valor as Atributo2,
                                av3.Valor as Atributo3
                            FROM factura_producto fp
                            LEFT JOIN producto p ON fp.ID_Producto = p.ID_Producto
                            LEFT JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                            LEFT JOIN atributo_valor av1 ON p.ID_Atributo1 = av1.ID_AtributoValor
                            LEFT JOIN atributo_valor av2 ON p.ID_Atributo2 = av2.ID_AtributoValor
                            LEFT JOIN atributo_valor av3 ON p.ID_Atributo3 = av3.ID_AtributoValor
                            WHERE fp.ID_Factura = ?";

            $stmtProductos = $this->conn->prepare($queryProductos);
            $stmtProductos->execute([$id]);
            $pedido['productos'] = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

            // Obtener historial de seguimiento
            $pedido['seguimiento'] = $this->obtenerSeguimiento($id);
        }

        return $pedido;
    }

    // OBTENER TODOS LOS PEDIDOS CON NUEVA LÓGICA DE PRIORIDAD Y ALERTAS MODIFICADAS
    public function obtenerTodos()
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo, 
                    u.Celular,
                    mp.T_Pago as MetodoPago,
                    ue.Nombre as NombreEnvio,
                    uent.Nombre as NombreEntrega,
                    -- CORRECCIÓN: Calcular horas desde la creación del pedido correctamente
                    CASE 
                        WHEN f.Estado IN ('Entregado', 'Anulado') THEN 0
                        ELSE TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW())
                    END as horas_desde_creacion,
                    -- CORRECCIÓN: Calcular días de retraso para pedidos enviados
                    CASE 
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                        ELSE 0
                    END as dias_retraso,
                    -- CORRECCIÓN: Calcular días desde el envío
                    CASE 
                        WHEN f.Estado IN ('Enviado', 'Retrasado') AND f.Fecha_Envio IS NOT NULL THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Envio)
                        ELSE 0
                    END as dias_desde_envio,
                    -- NUEVA LÓGICA DE ALERTAS MODIFICADA:
                    -- 1. Confirmado: alerta después de 3 horas, mega alerta después de 5 horas
                    -- 2. Preparando: alerta después de 3 días (72 horas)
                    -- 3. Enviado: alerta solo si fecha estimada de entrega pasó
                    -- 4. Otros estados: sin alerta
                    CASE 
                        WHEN f.Estado IN ('Entregado', 'Anulado') THEN 'normal'
                        -- Confirmado: mega alerta después de 5 horas
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 'mega_alerta'
                        -- Confirmado: alerta después de 3 horas
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 'alerta'
                        -- Preparando: alerta después de 3 días (72 horas)
                        WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 'alerta'
                        -- Enviado con fecha estimada pasada
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 'alerta'
                        -- Enviado sin fecha estimada y más de 3 días
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3 THEN 'alerta'
                        ELSE 'normal'
                    END as estado_alerta,
                    -- NUEVO ORDEN DE PRIORIDAD
                    CASE 
                        -- Prioridad 1: Retrasados
                        WHEN f.Estado = 'Retrasado' THEN 1
                        -- Prioridad 2: Confirmados con MEGA ALERTA (> 5 horas) - más horas primero
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 2
                        -- Prioridad 3: Confirmados con ALERTA (3-5 horas) - más horas primero
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 3
                        -- Prioridad 4: Confirmados normales - más horas primero
                        WHEN f.Estado = 'Confirmado' THEN 4
                        -- Prioridad 5: Preparando con alerta (> 72 horas) - más horas primero
                        WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 5
                        -- Prioridad 6: Preparando normales - más horas primero
                        WHEN f.Estado = 'Preparando' THEN 6
                        -- Prioridad 7: Enviados con alerta (fecha pasada o sin fecha > 3 días) - más días primero
                        WHEN f.Estado = 'Enviado' AND (
                            (f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega) OR
                            (f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3)
                        ) THEN 7
                        -- Prioridad 8: Enviados normales - más días primero
                        WHEN f.Estado = 'Enviado' THEN 8
                        -- Prioridad 9: Devueltos
                        WHEN f.Estado = 'Devuelto' THEN 9
                        -- Prioridad 10: Entregados
                        WHEN f.Estado = 'Entregado' THEN 10
                        -- Prioridad 11: Anulados
                        WHEN f.Estado = 'Anulado' THEN 11
                        ELSE 12
                    END as prioridad_orden,
                    -- Campo adicional para ordenar por tiempo dentro de cada prioridad
                    CASE 
                        WHEN f.Estado = 'Confirmado' THEN TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW())
                        WHEN f.Estado = 'Preparando' THEN TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW())
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Envio IS NOT NULL THEN 
                            CASE 
                                WHEN f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 
                                    DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                                ELSE 
                                    DATEDIFF(CURDATE(), f.Fecha_Envio)
                            END
                        ELSE 0
                    END as tiempo_orden_secundario
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  LEFT JOIN usuario ue ON f.Usuario_Envio = ue.ID_Usuario
                  LEFT JOIN usuario uent ON f.Usuario_Entrega = uent.ID_Usuario
                  ORDER BY prioridad_orden ASC, tiempo_orden_secundario DESC, f.Fecha_Factura ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS CON ALERTAS ACTIVAS (para estadísticas) - EXCLUIR ENTREGADOS Y ANULADOS
     public function obtenerPedidosConAlertas()
    {
        $query = "SELECT 
                    COUNT(*) as total_pedidos,
                    -- Confirmado con mega alerta (más de 5 horas)
                    SUM(CASE 
                        WHEN Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, Fecha_Factura, NOW()) >= 5 THEN 1 
                        ELSE 0 
                    END) as confirmado_mega_alerta_count,
                    -- Confirmado con alerta (más de 3 horas, menos de 5)
                    SUM(CASE 
                        WHEN Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, Fecha_Factura, NOW()) >= 3 
                             AND TIMESTAMPDIFF(HOUR, Fecha_Factura, NOW()) < 5 THEN 1 
                        ELSE 0 
                    END) as confirmado_alerta_count,
                    -- Preparando con más de 3 días (72 horas)
                    SUM(CASE 
                        WHEN Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, Fecha_Factura, NOW()) >= 72 THEN 1 
                        ELSE 0 
                    END) as preparando_alerta_count,
                    -- Enviado con fecha estimada pasada
                    SUM(CASE 
                        WHEN Estado = 'Enviado' AND Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > Fecha_Estimada_Entrega THEN 1 
                        ELSE 0 
                    END) as enviado_alerta_count,
                    -- Enviado sin fecha estimada y más de 3 días
                    SUM(CASE 
                        WHEN Estado = 'Enviado' AND Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), Fecha_Envio) > 3 THEN 1 
                        ELSE 0 
                    END) as enviado_sin_fecha_alerta_count
                  FROM " . $this->table_name . "
                  WHERE Estado NOT IN ('Entregado', 'Anulado')";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS POR ESTADO CON PRIORIDAD Y ORDEN CRONOLÓGICO
    public function obtenerPorEstado($estado) {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago,
                    -- Calcular horas desde la creación
                    CASE 
                        WHEN f.Estado IN ('Entregado', 'Anulado') THEN 0
                        ELSE TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW())
                    END as horas_desde_creacion,
                    -- Calcular días de retraso
                    CASE 
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                        ELSE 0
                    END as dias_retraso,
                    -- Calcular días desde el envío
                    CASE 
                        WHEN f.Estado IN ('Enviado', 'Retrasado') AND f.Fecha_Envio IS NOT NULL THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Envio)
                        ELSE 0
                    END as dias_desde_envio
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE f.Estado = ?
                  ORDER BY f.Fecha_Factura ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS ENVIADOS (PARA SEGUIMIENTO) - ACTUALIZADO
    public function obtenerEnviados()
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago,
                    f.Numero_Guia,
                    f.Transportadora,
                    f.Fecha_Envio,
                    f.Fecha_Estimada_Entrega,
                    ue.Nombre as NombreEnvio,
                    CASE 
                        WHEN f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                        WHEN f.Fecha_Envio IS NOT NULL THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Envio)
                        ELSE 0
                    END as dias_transcurridos,
                    CASE 
                        WHEN f.Estado = 'Retrasado' THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                        ELSE 0
                    END as dias_retraso
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  LEFT JOIN usuario ue ON f.Usuario_Envio = ue.ID_Usuario
                  WHERE f.Estado IN ('Enviado', 'Retrasado')
                  ORDER BY f.Fecha_Envio ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER HISTORIAL DE SEGUIMIENTO DE UN PEDIDO
    public function obtenerSeguimiento($idFactura)
    {
        $query = "SELECT 
                    ps.*,
                    u.Nombre as UsuarioNombre,
                    u.Apellido as UsuarioApellido
                  FROM " . $this->table_seguimiento . " ps
                  LEFT JOIN usuario u ON ps.ID_Usuario = u.ID_Usuario
                  WHERE ps.ID_Factura = ?
                  ORDER BY ps.Fecha DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idFactura]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // REGISTRAR SEGUIMIENTO DE PEDIDO
    public function registrarSeguimiento($idFactura, $estado, $descripcion, $usuarioId)
    {
        $query = "INSERT INTO " . $this->table_seguimiento . " 
                  (ID_Factura, Estado, Descripcion, ID_Usuario) 
                  VALUES (?, ?, ?, ?)";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$idFactura, $estado, $descripcion, $usuarioId]);
    }

    // BUSCAR PEDIDOS
    public function buscar($termino) {
        // Primero, intentar buscar por ID de factura exacto
        if (is_numeric($termino)) {
            $query = "SELECT 
                        f.*, 
                        u.Nombre, 
                        u.Apellido, 
                        u.Correo,
                        mp.T_Pago as MetodoPago,
                        -- Calcular horas desde la creación
                        CASE 
                            WHEN f.Estado IN ('Entregado', 'Anulado') THEN 0
                            ELSE TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW())
                        END as horas_desde_creacion,
                        -- Calcular días de retraso
                        CASE 
                            WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 
                                DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                            ELSE 0
                        END as dias_retraso,
                        -- Calcular días desde el envío
                        CASE 
                            WHEN f.Estado IN ('Enviado', 'Retrasado') AND f.Fecha_Envio IS NOT NULL THEN 
                                DATEDIFF(CURDATE(), f.Fecha_Envio)
                            ELSE 0
                        END as dias_desde_envio,
                        -- Lógica de alertas
                        CASE 
                            WHEN f.Estado IN ('Entregado', 'Anulado') THEN 'normal'
                            WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 'mega_alerta'
                            WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 'alerta'
                            WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 'alerta'
                            WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 'alerta'
                            WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3 THEN 'alerta'
                            ELSE 'normal'
                        END as estado_alerta,
                        -- Orden de prioridad
                        CASE 
                            WHEN f.Estado = 'Retrasado' THEN 1
                            WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 2
                            WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 3
                            WHEN f.Estado = 'Confirmado' THEN 4
                            WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 5
                            WHEN f.Estado = 'Preparando' THEN 6
                            WHEN f.Estado = 'Enviado' AND (
                                (f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega) OR
                                (f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3)
                            ) THEN 7
                            WHEN f.Estado = 'Enviado' THEN 8
                            WHEN f.Estado = 'Devuelto' THEN 9
                            WHEN f.Estado = 'Entregado' THEN 10
                            WHEN f.Estado = 'Anulado' THEN 11
                            ELSE 12
                        END as prioridad_orden
                      FROM " . $this->table_name . " f
                      LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                      LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                      WHERE f.ID_Factura = ? 
                      ORDER BY prioridad_orden ASC, f.Fecha_Factura ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([$termino]);
            $resultadosPorId = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($resultadosPorId)) {
                return $resultadosPorId;
            }
        }

        // Si no se encontró por ID exacto, buscar por otros campos
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago,
                    -- Calcular horas desde la creación
                    CASE 
                        WHEN f.Estado IN ('Entregado', 'Anulado') THEN 0
                        ELSE TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW())
                    END as horas_desde_creacion,
                    -- Calcular días de retraso
                    CASE 
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                        ELSE 0
                    END as dias_retraso,
                    -- Calcular días desde el envío
                    CASE 
                        WHEN f.Estado IN ('Enviado', 'Retrasado') AND f.Fecha_Envio IS NOT NULL THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Envio)
                        ELSE 0
                    END as dias_desde_envio,
                    -- Lógica de alertas
                    CASE 
                        WHEN f.Estado IN ('Entregado', 'Anulado') THEN 'normal'
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 'mega_alerta'
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 'alerta'
                        WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 'alerta'
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 'alerta'
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3 THEN 'alerta'
                        ELSE 'normal'
                    END as estado_alerta,
                    -- Orden de prioridad
                    CASE 
                        WHEN f.Estado = 'Retrasado' THEN 1
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 2
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 3
                        WHEN f.Estado = 'Confirmado' THEN 4
                        WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 5
                        WHEN f.Estado = 'Preparando' THEN 6
                        WHEN f.Estado = 'Enviado' AND (
                            (f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega) OR
                            (f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3)
                        ) THEN 7
                        WHEN f.Estado = 'Enviado' THEN 8
                        WHEN f.Estado = 'Devuelto' THEN 9
                        WHEN f.Estado = 'Entregado' THEN 10
                        WHEN f.Estado = 'Anulado' THEN 11
                        ELSE 12
                    END as prioridad_orden
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE f.Codigo_Acceso LIKE ?
                     OR CONCAT(u.Nombre, ' ', u.Apellido) LIKE ?
                     OR u.Nombre LIKE ? 
                     OR u.Apellido LIKE ?
                     OR u.Correo LIKE ?
                  ORDER BY prioridad_orden ASC, f.Fecha_Factura ASC";

        $likeTerm = '%' . $termino . '%';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$likeTerm, $likeTerm, $likeTerm, $likeTerm, $likeTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS POR FECHA
    public function obtenerPorFecha($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago,
                    -- Calcular horas desde la creación del pedido
                    CASE 
                        WHEN f.Estado IN ('Entregado', 'Anulado') THEN 0
                        ELSE TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW())
                    END as horas_desde_creacion,
                    -- Calcular días de retraso
                    CASE 
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega)
                        ELSE 0
                    END as dias_retraso,
                    -- Calcular días desde el envío
                    CASE 
                        WHEN f.Estado IN ('Enviado', 'Retrasado') AND f.Fecha_Envio IS NOT NULL THEN 
                            DATEDIFF(CURDATE(), f.Fecha_Envio)
                        ELSE 0
                    END as dias_desde_envio,
                    -- Lógica de alertas
                    CASE 
                        WHEN f.Estado IN ('Entregado', 'Anulado') THEN 'normal'
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 'mega_alerta'
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 'alerta'
                        WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 'alerta'
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega THEN 'alerta'
                        WHEN f.Estado = 'Enviado' AND f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3 THEN 'alerta'
                        ELSE 'normal'
                    END as estado_alerta,
                    -- Orden de prioridad
                    CASE 
                        WHEN f.Estado = 'Retrasado' THEN 1
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 5 THEN 2
                        WHEN f.Estado = 'Confirmado' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 3 THEN 3
                        WHEN f.Estado = 'Confirmado' THEN 4
                        WHEN f.Estado = 'Preparando' AND TIMESTAMPDIFF(HOUR, f.Fecha_Factura, NOW()) >= 72 THEN 5
                        WHEN f.Estado = 'Preparando' THEN 6
                        WHEN f.Estado = 'Enviado' AND (
                            (f.Fecha_Estimada_Entrega IS NOT NULL AND CURDATE() > f.Fecha_Estimada_Entrega) OR
                            (f.Fecha_Estimada_Entrega IS NULL AND DATEDIFF(CURDATE(), f.Fecha_Envio) > 3)
                        ) THEN 7
                        WHEN f.Estado = 'Enviado' THEN 8
                        WHEN f.Estado = 'Devuelto' THEN 9
                        WHEN f.Estado = 'Entregado' THEN 10
                        WHEN f.Estado = 'Anulado' THEN 11
                        ELSE 12
                    END as prioridad_orden
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  ORDER BY prioridad_orden ASC, f.Fecha_Factura ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstado($id, $estado, $descripcion, $usuarioId, $datosAdicionales = [])
    {
        // Iniciar transacción
        $this->conn->beginTransaction();

        try {
            // Actualizar el pedido
            $query = "UPDATE " . $this->table_name . " 
                     SET Estado = ?";

            $params = [$estado];

            // Agregar campos adicionales según el estado
            switch ($estado) {
                case 'Enviado':
                    $query .= ", Fecha_Envio = NOW(), Usuario_Envio = ?, Numero_Guia = ?, Transportadora = ?, Notas_Envio = ?, Fecha_Estimada_Entrega = ?";
                    array_push(
                        $params,
                        $usuarioId,
                        $datosAdicionales['numero_guia'] ?? null,
                        $datosAdicionales['transportadora'] ?? null,
                        $datosAdicionales['notas_envio'] ?? null,
                        $datosAdicionales['fecha_estimada_entrega'] ?? null
                    );
                    break;

                case 'Entregado':
                    $query .= ", Fecha_Entrega = NOW(), Usuario_Entrega = ?";
                    $params[] = $usuarioId;
                    break;

                case 'Anulado':
                    $query .= ", Motivo_Anulacion = ?, Usuario_Anulacion = ?, Fecha_Anulacion = NOW()";
                    $params[] = $descripcion;
                    $params[] = $usuarioId;
                    // Para anulación desde Enviado, limpiar datos de envío
                    if (isset($datosAdicionales['limpiar_envio']) && $datosAdicionales['limpiar_envio']) {
                        $query .= ", Fecha_Envio = NULL, Usuario_Envio = NULL, Numero_Guia = NULL, Transportadora = NULL, Notas_Envio = NULL, Fecha_Estimada_Entrega = NULL";
                    }
                    break;

                case 'Retrasado':
                    $query .= ", Notas_Envio = ?, Fecha_Estimada_Entrega = ?";
                    $params[] = $descripcion;
                    $params[] = $datosAdicionales['nueva_fecha_estimada'] ?? null;
                    break;

                case 'Devuelto':
                    $query .= ", Notas_Envio = ?";
                    $params[] = $descripcion;
                    break;

                case 'Preparando':
                    // Si viene desde Enviado, limpiar datos de envío
                    if (isset($datosAdicionales['limpiar_envio']) && $datosAdicionales['limpiar_envio']) {
                        $query .= ", Fecha_Envio = NULL, Usuario_Envio = NULL, Numero_Guia = NULL, Transportadora = NULL, Notas_Envio = NULL, Fecha_Estimada_Entrega = NULL";
                    }
                    break;
            }

            $query .= " WHERE ID_Factura = ?";
            $params[] = $id;

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            // Registrar en el historial de seguimiento
            $this->registrarSeguimiento($id, $estado, $descripcion, $usuarioId);

            // Confirmar transacción
            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // DEVOLVER STOCK CUANDO SE ANULA UN PEDIDO
    public function devolverStockPedidoAnulado($idFactura)
    {
        // Obtener los productos del pedido
        $query = "SELECT 
                    fp.ID_Producto,
                    fp.Cantidad,
                    p.Cantidad as StockActual,
                    a.N_Articulo as NombreProducto
                  FROM " . $this->table_factura_producto . " fp
                  INNER JOIN " . $this->table_producto . " p ON fp.ID_Producto = p.ID_Producto
                  INNER JOIN articulo a ON p.ID_Articulo = a.ID_Articulo
                  WHERE fp.ID_Factura = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$idFactura]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($productos)) {
            return false;
        }

        $this->conn->beginTransaction();

        try {
            foreach ($productos as $producto) {
                $nuevoStock = $producto['StockActual'] + $producto['Cantidad'];
                
                $queryUpdate = "UPDATE " . $this->table_producto . " 
                                SET Cantidad = ? 
                                WHERE ID_Producto = ?";
                
                $stmtUpdate = $this->conn->prepare($queryUpdate);
                $stmtUpdate->execute([$nuevoStock, $producto['ID_Producto']]);

                // Registrar en el historial de seguimiento
                $descripcion = "Stock devuelto por anulación de pedido: " . 
                              $producto['Cantidad'] . " unidades de '" . 
                              $producto['NombreProducto'] . "'. Stock anterior: " . 
                              $producto['StockActual'] . ", Stock nuevo: " . $nuevoStock;
                
                $this->registrarSeguimiento($idFactura, 'Anulado', $descripcion, 1);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    // VERIFICAR SI UN PEDIDO PUEDE CAMBIAR DE ESTADO - MODIFICADO PARA PERMITIR MÁS TRANSICIONES
    public function puedeCambiarEstado($id, $nuevoEstado)
    {
        $query = "SELECT Estado FROM " . $this->table_name . " WHERE ID_Factura = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $estadoActual = $stmt->fetchColumn();

        // Si está anulado, no se puede cambiar (excepto casos especiales)
        if ($estadoActual === 'Anulado') {
            return false;
        }

        // Si está entregado, solo puede ser devuelto (en casos especiales)
        if ($estadoActual === 'Entregado' && $nuevoEstado === 'Devuelto') {
            return true;
        }

        // Si está entregado, no se puede cambiar a otros estados
        if ($estadoActual === 'Entregado') {
            return false;
        }

        // Definir transiciones permitidas - MODIFICADO PARA PERMITIR MÁS FLEXIBILIDAD
        $transicionesPermitidas = [
            'Confirmado' => ['Preparando', 'Anulado'],
            'Preparando' => ['Enviado', 'Confirmado', 'Anulado'], // Puede regresar a Confirmado
            'Enviado' => ['Entregado', 'Retrasado', 'Devuelto', 'Preparando', 'Anulado'], // MODIFICADO: Puede regresar a Preparando o Anular
            'Retrasado' => ['Entregado', 'Devuelto', 'Enviado', 'Preparando', 'Anulado'], // MODIFICADO: Puede regresar a Enviado o Preparando
            'Devuelto' => ['Preparando', 'Anulado'],
            'Entregado' => ['Devuelto'] // Solo devuelto después de entregado
        ];

        return in_array($nuevoEstado, $transicionesPermitidas[$estadoActual] ?? []);
    }

    // MARCAR COMO ENVIADO CON DETALLES - ACTUALIZADO CON FECHA ESTIMADA
    public function marcarComoEnviado($id, $usuarioId, $transportadora = null, $notas = null, $numeroGuiaPersonalizado = null, $fechaEstimadaEntrega = null)
    {
        // Generar número de guía automático si no se proporciona uno personalizado
        $numeroGuia = $numeroGuiaPersonalizado ?? $this->generarNumeroGuiaUnico($id);

        // Si se proporcionó un número personalizado, verificar que no exista
        if ($numeroGuiaPersonalizado && $this->existeNumeroGuia($numeroGuiaPersonalizado, $id)) {
            throw new Exception("El número de guía '$numeroGuiaPersonalizado' ya está en uso");
        }

        $descripcion = "Envío registrado" . ($notas ? ": $notas" : "") . ($fechaEstimadaEntrega ? " - Entrega estimada: " . date('d/m/Y', strtotime($fechaEstimadaEntrega)) : "");

        return $this->actualizarEstado($id, 'Enviado', $descripcion, $usuarioId, [
            'numero_guia' => $numeroGuia,
            'transportadora' => $transportadora,
            'notas_envio' => $notas,
            'fecha_estimada_entrega' => $fechaEstimadaEntrega
        ]);
    }

    // ACTUALIZAR FECHA ESTIMADA DE ENTREGA
    public function actualizarFechaEstimada($id, $fechaEstimada)
    {
        $query = "UPDATE " . $this->table_name . " 
                  SET Fecha_Estimada_Entrega = ? 
                  WHERE ID_Factura = ?";

        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$fechaEstimada, $id]);
    }

    // OBTENER PEDIDOS QUE DEBERÍAN SER MARCADOS COMO RETRASADOS
    public function obtenerPedidosParaRetrasar()
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  WHERE f.Estado = 'Enviado'
                    AND f.Fecha_Estimada_Entrega IS NOT NULL
                    AND CURDATE() > f.Fecha_Estimada_Entrega
                    AND (f.Fecha_Estimada_Entrega < CURDATE() - INTERVAL 1 DAY OR f.Estado != 'Retrasado')
                  ORDER BY f.Fecha_Estimada_Entrega ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // MARCAR PEDIDOS COMO RETRASADOS AUTOMÁTICAMENTE
    public function marcarRetrasadosAutomaticamente($usuarioId = null)
    {
        $pedidosParaRetrasar = $this->obtenerPedidosParaRetrasar();
        $contador = 0;

        foreach ($pedidosParaRetrasar as $pedido) {
            $descripcion = "Pedido automáticamente marcado como retrasado. Fecha estimada de entrega superada (" . date('d/m/Y', strtotime($pedido['Fecha_Estimada_Entrega'])) . ").";
            
            $this->actualizarEstado(
                $pedido['ID_Factura'], 
                'Retrasado', 
                $descripcion, 
                $usuarioId ?? 1, // Usuario sistema por defecto
                ['nueva_fecha_estimada' => null] // Quitar fecha estimada ya que está retrasado
            );
            
            $contador++;
        }

        return $contador;
    }

    // OBTENER ESTADÍSTICAS DE RETRASOS
    public function obtenerEstadisticasRetrasos()
    {
        $query = "SELECT 
                    COUNT(*) as total_retrasados,
                    AVG(DATEDIFF(CURDATE(), Fecha_Estimada_Entrega)) as promedio_dias_retraso,
                    MAX(DATEDIff(CURDATE(), Fecha_Estimada_Entrega)) as max_dias_retraso,
                    MIN(DATEDIff(CURDATE(), Fecha_Estimada_Entrega)) as min_dias_retraso
                  FROM " . $this->table_name . "
                  WHERE Estado = 'Retrasado'
                    AND Fecha_Estimada_Entrega IS NOT NULL";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER TRANSPORTADORAS FRECUENTES
    public function obtenerTransportadorasFrecuentes($limite = 5)
    {
        $limite = (int)$limite; // Validar que sea un entero
        $query = "SELECT 
                    Transportadora,
                    COUNT(*) as total_envios
                FROM " . $this->table_name . "
                WHERE Transportadora IS NOT NULL 
                    AND Transportadora != ''
                GROUP BY Transportadora
                ORDER BY total_envios DESC
                LIMIT " . $limite;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // GENERAR NÚMERO DE GUÍA
    public function generarNumeroGuia($idFactura)
    {
        // Formato: TLL-AAAAMMDD-ID-XXXX
        // TLL = TuLook (siglas)
        // AAAAMMDD = Fecha actual
        // ID = ID de factura
        // XXXX = 4 dígitos aleatorios

        $fecha = date('Ymd');
        $random = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $numeroGuia = "TLL-{$fecha}-{$idFactura}-{$random}";

        return $numeroGuia;
    }

    // VERIFICAR SI EXISTE NÚMERO DE GUÍA
    public function existeNumeroGuia($numeroGuia, $excluirId = null)
    {
        $query = "SELECT COUNT(*) FROM " . $this->table_name . " WHERE Numero_Guia = ?";
        $params = [$numeroGuia];

        if ($excluirId) {
            $query .= " AND ID_Factura != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // GENERAR NÚMERO DE GUÍA ÚNICO
    public function generarNumeroGuiaUnico($idFactura)
    {
        $intentos = 0;
        $maxIntentos = 10;

        do {
            $numeroGuia = $this->generarNumeroGuia($idFactura);
            $intentos++;

            // Si después de varios intentos sigue duplicado, agregar más random
            if ($intentos > 5) {
                $numeroGuia = "TLL-" . date('Ymd') . "-{$idFactura}-" .
                    str_pad(rand(0, 99999), 5, '0', STR_PAD_LEFT);
            }
        } while ($this->existeNumeroGuia($numeroGuia, $idFactura) && $intentos < $maxIntentos);

        return $numeroGuia;
    }

    // OBTENER ESTADÍSTICAS MEJORADAS - SIN EMITIDOS
    public function obtenerEstadisticas()
    {
        $query = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(CASE WHEN Estado = 'Confirmado' THEN 1 ELSE 0 END) as confirmados,
                    SUM(CASE WHEN Estado = 'Preparando' THEN 1 ELSE 0 END) as preparando,
                    SUM(CASE WHEN Estado = 'Enviado' THEN 1 ELSE 0 END) as enviados,
                    SUM(CASE WHEN Estado = 'Retrasado' THEN 1 ELSE 0 END) as retrasados,
                    SUM(CASE WHEN Estado = 'Devuelto' THEN 1 ELSE 0 END) as devueltos,
                    SUM(CASE WHEN Estado = 'Entregado' THEN 1 ELSE 0 END) as entregados,
                    SUM(CASE WHEN Estado = 'Anulado' THEN 1 ELSE 0 END) as anulados,
                    SUM(Monto_Total) as total_ventas,
                    SUM(CASE WHEN Estado = 'Entregado' THEN Monto_Total ELSE 0 END) as ventas_entregadas
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER RESUMEN DIARIO MEJORADO
    public function obtenerResumenDiario($fecha = null)
    {
        if (!$fecha) {
            $fecha = date('Y-m-d');
        }

        $query = "SELECT 
                    COUNT(*) as pedidos_hoy,
                    SUM(CASE WHEN Estado = 'Entregado' THEN 1 ELSE 0 END) as entregados_hoy,
                    SUM(Monto_Total) as ventas_hoy,
                    AVG(Monto_Total) as promedio_pedido
                  FROM " . $this->table_name . "
                  WHERE DATE(Fecha_Factura) = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fecha]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS ATRASADOS (más de 3 días en estado 'Enviado' sin entregar)
    public function obtenerAtrasados()
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago,
                    DATEDIFF(NOW(), f.Fecha_Envio) as dias_transcurridos
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE f.Estado = 'Enviado' 
                    AND f.Fecha_Envio IS NOT NULL
                    AND DATEDIFF(NOW(), f.Fecha_Envio) > 3
                  ORDER BY f.Fecha_Envio ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS CON DIAS DE RETRASO
    public function obtenerRetrasadosConDias()
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago,
                    DATEDIFF(CURDATE(), f.Fecha_Estimada_Entrega) as dias_retraso
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE f.Estado = 'Retrasado' 
                    AND f.Fecha_Estimada_Entrega IS NOT NULL
                  ORDER BY dias_retraso DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NUEVO MÉTODO: REGRESAR A PREPARANDO DESDE ENVIADO
    public function regresarAPreparando($id, $descripcion, $usuarioId)
    {
        return $this->actualizarEstado($id, 'Preparando', $descripcion, $usuarioId, [
            'limpiar_envio' => true
        ]);
    }

    // NUEVO MÉTODO: ANULAR PEDIDO ENVIADO
    public function anularPedidoEnviado($id, $descripcion, $usuarioId)
    {
        return $this->actualizarEstado($id, 'Anulado', $descripcion, $usuarioId, [
            'limpiar_envio' => true
        ]);
    }
}
?>