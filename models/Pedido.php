<?php
class Pedido
{
    private $conn;
    private $table_name = "factura";
    private $table_seguimiento = "pedido_seguimiento";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // OBTENER PEDIDO POR ID CON DETALLE
    public function obtenerPorId($id)
    {
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
                  LEFT JOIN direccion d ON f.ID_Usuario = d.ID_Usuario AND d.Predeterminada = 1
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
                              LEFT JOIN articulo a ON fp.ID_Articulo = a.ID_Articulo
                              LEFT JOIN producto p ON fp.ID_Producto = p.ID_Producto
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

    // OBTENER TODOS LOS PEDIDOS CON INFORMACIÓN DE USUARIO (ORDEN POR PRIORIDAD)
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
                    uent.Nombre as NombreEntrega
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  LEFT JOIN usuario ue ON f.Usuario_Envio = ue.ID_Usuario
                  LEFT JOIN usuario uent ON f.Usuario_Entrega = uent.ID_Usuario
                  ORDER BY 
                    CASE 
                        WHEN f.Estado = 'Emitido' THEN 1
                        WHEN f.Estado = 'Confirmado' THEN 2
                        WHEN f.Estado = 'Preparando' THEN 3
                        WHEN f.Estado = 'Enviado' THEN 4
                        WHEN f.Estado = 'Retrasado' THEN 5
                        WHEN f.Estado = 'Devuelto' THEN 6
                        WHEN f.Estado = 'Entregado' THEN 7
                        WHEN f.Estado = 'Anulado' THEN 8
                        ELSE 9
                    END,
                    f.Fecha_Factura ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS POR ESTADO CON PRIORIDAD
    public function obtenerPorEstado($estado)
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE f.Estado = ?
                  ORDER BY f.Fecha_Factura ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // OBTENER PEDIDOS ENVIADOS (PARA SEGUIMIENTO)
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
                    ue.Nombre as NombreEnvio
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  LEFT JOIN usuario ue ON f.Usuario_Envio = ue.ID_Usuario
                  WHERE f.Estado IN ('Enviado', 'Retrasado')
                  ORDER BY f.Fecha_Envio ASC, f.Fecha_Factura ASC";

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
    public function buscar($termino)
    {
        $query = "SELECT 
                    f.*, 
                    u.Nombre, 
                    u.Apellido, 
                    u.Correo,
                    mp.T_Pago as MetodoPago
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE f.ID_Factura = ? 
                     OR f.Codigo_Acceso LIKE ?
                     OR u.Nombre LIKE ? 
                     OR u.Apellido LIKE ?
                     OR u.Correo LIKE ?
                  ORDER BY f.Fecha_Factura DESC";

        $likeTerm = '%' . $termino . '%';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$termino, $likeTerm, $likeTerm, $likeTerm, $likeTerm]);
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
                    mp.T_Pago as MetodoPago
                  FROM " . $this->table_name . " f
                  LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  ORDER BY f.Fecha_Factura DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ACTUALIZAR ESTADO DEL PEDIDO CON SEGUIMIENTO
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
                    $query .= ", Fecha_Envio = NOW(), Usuario_Envio = ?, Numero_Guia = ?, Transportadora = ?, Notas_Envio = ?";
                    array_push(
                        $params,
                        $usuarioId,
                        $datosAdicionales['numero_guia'] ?? null,
                        $datosAdicionales['transportadora'] ?? null,
                        $datosAdicionales['notas_envio'] ?? null
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
                    break;

                case 'Retrasado':
                case 'Devuelto':
                    $query .= ", Notas_Envio = ?";
                    $params[] = $descripcion;
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

    // VERIFICAR SI UN PEDIDO PUEDE CAMBIAR DE ESTADO
    public function puedeCambiarEstado($id, $nuevoEstado)
    {
        $query = "SELECT Estado FROM " . $this->table_name . " WHERE ID_Factura = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        $estadoActual = $stmt->fetchColumn();

        // Si está anulado, no se puede cambiar
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

        // Definir transiciones permitidas
        $transicionesPermitidas = [
            'Emitido' => ['Confirmado', 'Anulado'],
            'Confirmado' => ['Preparando', 'Anulado'],
            'Preparando' => ['Enviado', 'Anulado'],
            'Enviado' => ['Entregado', 'Retrasado', 'Devuelto'],
            'Retrasado' => ['Entregado', 'Devuelto'],
            'Devuelto' => ['Preparando', 'Anulado'],
            'Entregado' => ['Devuelto'] // Solo devuelto después de entregado
        ];

        return in_array($nuevoEstado, $transicionesPermitidas[$estadoActual] ?? []);
    }

    // MARCAR COMO ENVIADO CON DETALLES
    public function marcarComoEnviado($id, $usuarioId, $transportadora = null, $notas = null, $numeroGuiaPersonalizado = null)
    {
        // Generar número de guía automático si no se proporciona uno personalizado
        $numeroGuia = $numeroGuiaPersonalizado ?? $this->generarNumeroGuiaUnico($id);

        // Si se proporcionó un número personalizado, verificar que no exista
        if ($numeroGuiaPersonalizado && $this->existeNumeroGuia($numeroGuiaPersonalizado, $id)) {
            throw new Exception("El número de guía '$numeroGuiaPersonalizado' ya está en uso");
        }

        $descripcion = "Envío registrado" . ($notas ? ": $notas" : "");

        return $this->actualizarEstado($id, 'Enviado', $descripcion, $usuarioId, [
            'numero_guia' => $numeroGuia,
            'transportadora' => $transportadora,
            'notas_envio' => $notas
        ]);
    }

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



    // OBTENER ESTADÍSTICAS MEJORADAS
    public function obtenerEstadisticas()
    {
        $query = "SELECT 
                    COUNT(*) as total_pedidos,
                    SUM(CASE WHEN Estado = 'Emitido' THEN 1 ELSE 0 END) as emitidos,
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
}
