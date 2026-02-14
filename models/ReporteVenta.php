<?php
// models/ReporteVenta.php
class ReporteVenta
{
    private $conn;
    private $table_factura = "factura";
    private $table_factura_producto = "factura_producto";
    private $table_producto = "producto";
    private $table_articulo = "articulo";
    private $table_categoria = "categoria";
    private $table_usuario = "usuario";
    private $table_metodo_pago = "metodo_pago";
    private $table_descuento = "descuento";
    private $table_genero = "genero";
    private $table_color = "color";

    public function __construct($db)
    {
        $this->conn = $db;
    }

    // 📊 OBTENER REPORTE FINANCIERO COMPLETO (para exportar)
    public function obtenerReporteFinancieroCompleto($fechaInicio, $fechaFin)
    {
        $reporte = [];
        
        // Obtener métricas principales
        $metricas = $this->obtenerMetricasPrincipales($fechaInicio, $fechaFin);
        
        // Agregar métricas al reporte
        $reporte[] = [
            'Concepto' => 'Ventas Brutas',
            'Monto' => $metricas['ventas_totales'] ?? 0,
            'Detalle' => 'Total de ventas antes de descuentos'
        ];
        
        $reporte[] = [
            'Concepto' => 'Descuentos Otorgados',
            'Monto' => $metricas['total_descuentos'] ?? 0,
            'Detalle' => 'Total en promociones y descuentos aplicados'
        ];
        
        $reporte[] = [
            'Concepto' => 'Ventas Netas',
            'Monto' => $metricas['ventas_netas'] ?? 0,
            'Detalle' => 'Ventas después de aplicar descuentos'
        ];
        
        $reporte[] = [
            'Concepto' => 'IVA Recaudado',
            'Monto' => $metricas['total_iva'] ?? 0,
            'Detalle' => 'Impuesto al valor agregado'
        ];
        
        $reporte[] = [
            'Concepto' => 'Total Pedidos',
            'Monto' => $metricas['total_pedidos'] ?? 0,
            'Detalle' => 'Número total de pedidos procesados'
        ];
        
        $reporte[] = [
            'Concepto' => 'Ticket Promedio',
            'Monto' => $metricas['ticket_promedio'] ?? 0,
            'Detalle' => 'Valor promedio por pedido'
        ];
        
        $reporte[] = [
            'Concepto' => 'Productos Vendidos',
            'Monto' => $metricas['productos_vendidos'] ?? 0,
            'Detalle' => 'Cantidad total de productos vendidos'
        ];
        
        $reporte[] = [
            'Concepto' => 'Clientes Únicos',
            'Monto' => $metricas['clientes_unicos'] ?? 0,
            'Detalle' => 'Número de clientes diferentes'
        ];
        
        // Obtener pedidos entregados
        $queryEntregados = "SELECT COUNT(*) as entregados FROM " . $this->table_factura . " 
                           WHERE DATE(Fecha_Factura) BETWEEN ? AND ? AND Estado = 'Entregado'";
        
        $stmt = $this->conn->prepare($queryEntregados);
        $stmt->execute([$fechaInicio, $fechaFin]);
        $entregados = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $reporte[] = [
            'Concepto' => 'Pedidos Entregados',
            'Monto' => $entregados['entregados'] ?? 0,
            'Detalle' => 'Pedidos completados exitosamente'
        ];
        
        return $reporte;
    }

    // 📊 OBTENER MÉTRICAS PRINCIPALES
    public function obtenerMetricasPrincipales($fechaInicio, $fechaFin) {
        $query = "SELECT 
                    COUNT(DISTINCT f.ID_Factura) as total_pedidos,
                    SUM(f.Monto_Total) as ventas_totales,
                    SUM(f.Subtotal) as subtotal_ventas,
                    SUM(f.IVA) as total_iva,
                    AVG(f.Monto_Total) as promedio_venta,
                    COUNT(DISTINCT f.ID_Usuario) as clientes_unicos,
                    SUM(fp.Cantidad) as productos_vendidos,
                    SUM(CASE WHEN f.Estado = 'Entregado' THEN f.Monto_Total ELSE 0 END) as ventas_entregadas,
                    SUM(CASE WHEN f.Estado = 'Anulado' THEN f.Monto_Total ELSE 0 END) as ventas_anuladas,
                    SUM(fp.Descuento_Aplicado) as total_descuentos,
                    -- NUEVO: Ganancia neta real (ventas - descuentos - IVA)
                    (SUM(f.Monto_Total) - SUM(fp.Descuento_Aplicado) - SUM(f.IVA)) as ganancia_neta_real
                FROM " . $this->table_factura . " f
                LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                AND f.Estado NOT IN ('Anulado')";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        $metricas = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calcular métricas adicionales
        if ($metricas['total_pedidos'] > 0) {
            $metricas['ticket_promedio'] = $metricas['ventas_totales'] / $metricas['total_pedidos'];
            $metricas['productos_por_pedido'] = $metricas['productos_vendidos'] / $metricas['total_pedidos'];
            $metricas['ganancia_promedio'] = $metricas['ganancia_neta_real'] / $metricas['total_pedidos'];
        } else {
            $metricas['ticket_promedio'] = 0;
            $metricas['productos_por_pedido'] = 0;
            $metricas['ganancia_promedio'] = 0;
        }
        
        $metricas['ventas_netas'] = $metricas['ventas_totales'] - $metricas['total_descuentos'];
        $metricas['tasa_conversion'] = ($metricas['clientes_unicos'] > 0) ? 
            ($metricas['total_pedidos'] / $metricas['clientes_unicos']) : 0;
        $metricas['margen_iva'] = $metricas['ventas_totales'] > 0 ? 
            ($metricas['total_iva'] / $metricas['ventas_totales'] * 100) : 0;
        $metricas['margen_neto'] = $metricas['ventas_totales'] > 0 ? 
            ($metricas['ganancia_neta_real'] / $metricas['ventas_totales'] * 100) : 0;
        
        return $metricas;
    }

    // 📈 OBTENER VENTAS POR DÍA
    public function obtenerVentasPorDia($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    DATE(f.Fecha_Factura) as Fecha,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Monto_Total,
                    SUM(f.Subtotal) as Subtotal,
                    SUM(f.IVA) as IVA,
                    SUM(fp.Descuento_Aplicado) as Descuentos,
                    AVG(f.Monto_Total) as Promedio_Venta
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY DATE(f.Fecha_Factura)
                  ORDER BY Fecha ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📈 OBTENER VENTAS POR SEMANA
    public function obtenerVentasPorSemana($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    YEARWEEK(f.Fecha_Factura, 1) as Semana,
                    CONCAT(YEAR(f.Fecha_Factura), '-W', WEEK(f.Fecha_Factura, 1)) as Etiqueta_Semana,
                    MIN(DATE(f.Fecha_Factura)) as Fecha_Inicio_Semana,
                    MAX(DATE(f.Fecha_Factura)) as Fecha_Fin_Semana,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Monto_Total,
                    SUM(f.Subtotal) as Subtotal,
                    SUM(f.IVA) as IVA,
                    SUM(fp.Descuento_Aplicado) as Descuentos,
                    AVG(f.Monto_Total) as Promedio_Venta,
                    SUM(fp.Cantidad) as Productos_Vendidos
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY YEARWEEK(f.Fecha_Factura, 1)
                  ORDER BY Semana ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📈 OBTENER VENTAS POR MES
    public function obtenerVentasPorMes($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    DATE_FORMAT(f.Fecha_Factura, '%Y-%m') as Mes,
                    DATE_FORMAT(f.Fecha_Factura, '%M %Y') as Etiqueta_Mes,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Monto_Total,
                    SUM(f.Subtotal) as Subtotal,
                    SUM(f.IVA) as IVA,
                    SUM(fp.Descuento_Aplicado) as Descuentos,
                    AVG(f.Monto_Total) as Promedio_Venta,
                    SUM(fp.Cantidad) as Productos_Vendidos,
                    COUNT(DISTINCT f.ID_Usuario) as Clientes_Unicos
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY DATE_FORMAT(f.Fecha_Factura, '%Y-%m')
                  ORDER BY Mes ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📈 OBTENER VENTAS POR TRIMESTRE
    public function obtenerVentasPorTrimestre($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    YEAR(f.Fecha_Factura) as Ano,
                    QUARTER(f.Fecha_Factura) as Trimestre,
                    CONCAT('T', QUARTER(f.Fecha_Factura), ' ', YEAR(f.Fecha_Factura)) as Etiqueta_Trimestre,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Monto_Total,
                    SUM(f.Subtotal) as Subtotal,
                    SUM(f.IVA) as IVA,
                    SUM(fp.Descuento_Aplicado) as Descuentos,
                    AVG(f.Monto_Total) as Promedio_Venta,
                    SUM(fp.Cantidad) as Productos_Vendidos
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY YEAR(f.Fecha_Factura), QUARTER(f.Fecha_Factura)
                  ORDER BY Ano DESC, Trimestre DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📈 OBTENER VENTAS POR AÑO
    public function obtenerVentasPorAno($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    YEAR(f.Fecha_Factura) as Ano,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Monto_Total,
                    SUM(f.Subtotal) as Subtotal,
                    SUM(f.IVA) as IVA,
                    SUM(fp.Descuento_Aplicado) as Descuentos,
                    AVG(f.Monto_Total) as Promedio_Venta,
                    SUM(fp.Cantidad) as Productos_Vendidos,
                    COUNT(DISTINCT f.ID_Usuario) as Clientes_Unicos,
                    COUNT(DISTINCT fp.ID_Producto) as Productos_Distintos
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY YEAR(f.Fecha_Factura)
                  ORDER BY Ano DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📊 OBTENER VENTAS POR CATEGORÍA
    public function obtenerVentasPorCategoria($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    c.ID_Categoria,
                    c.N_Categoria,
                    COUNT(DISTINCT f.ID_Factura) as Pedidos,
                    SUM(fp.Cantidad) as Productos_Vendidos,
                    SUM(fp.Subtotal) as Total_Ventas,
                    AVG(fp.Precio_Unitario) as Precio_Promedio
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  INNER JOIN " . $this->table_producto . " p ON fp.ID_Producto = p.ID_Producto
                  INNER JOIN " . $this->table_articulo . " a ON p.ID_Articulo = a.ID_Articulo
                  INNER JOIN " . $this->table_categoria . " c ON a.ID_Categoria = c.ID_Categoria
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY c.ID_Categoria, c.N_Categoria
                  ORDER BY Total_Ventas DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 👥 OBTENER VENTAS POR GÉNERO
    public function obtenerVentasPorGenero($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    g.ID_Genero,
                    g.N_Genero as Genero,
                    COUNT(DISTINCT f.ID_Factura) as Pedidos,
                    SUM(fp.Cantidad) as Productos_Vendidos,
                    SUM(fp.Subtotal) as Total_Ventas,
                    AVG(fp.Precio_Unitario) as Precio_Promedio,
                    COUNT(DISTINCT f.ID_Usuario) as Clientes_Unicos
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  INNER JOIN " . $this->table_producto . " p ON fp.ID_Producto = p.ID_Producto
                  INNER JOIN " . $this->table_articulo . " a ON p.ID_Articulo = a.ID_Articulo
                  INNER JOIN " . $this->table_genero . " g ON a.ID_Genero = g.ID_Genero
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY g.ID_Genero, g.N_Genero
                  ORDER BY Total_Ventas DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🎨 OBTENER VENTAS POR COLOR
    public function obtenerVentasPorColor($fechaInicio, $fechaFin, $limite = 10)
    {
        $query = "SELECT 
                    c.ID_Color,
                    c.N_Color as Color,
                    c.CodigoHex,
                    COUNT(DISTINCT f.ID_Factura) as Pedidos,
                    SUM(fp.Cantidad) as Productos_Vendidos,
                    SUM(fp.Subtotal) as Total_Ventas,
                    AVG(fp.Precio_Unitario) as Precio_Promedio,
                    COUNT(DISTINCT f.ID_Usuario) as Clientes_Unicos,
                    COUNT(DISTINCT a.ID_Articulo) as Articulos_Distintos
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  INNER JOIN " . $this->table_producto . " p ON fp.ID_Producto = p.ID_Producto
                  LEFT JOIN " . $this->table_color . " c ON p.ValorAtributo2 = c.N_Color
                  INNER JOIN " . $this->table_articulo . " a ON p.ID_Articulo = a.ID_Articulo
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  AND c.ID_Color IS NOT NULL
                  GROUP BY c.ID_Color, c.N_Color, c.CodigoHex
                  ORDER BY Total_Ventas DESC
                  LIMIT " . (int)$limite;

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🏆 OBTENER PRODUCTOS MÁS VENDIDOS CON DETALLES
    public function obtenerProductosMasVendidosConDetalles($fechaInicio, $fechaFin, $limite = 10)
    {
        $query = "SELECT 
                    a.N_Articulo as Producto,
                    c.N_Categoria as Categoria,
                    g.N_Genero as Genero,
                    SUM(fp.Cantidad) as Cantidad_Vendida,
                    SUM(fp.Subtotal) as Ventas_Totales,
                    AVG(fp.Precio_Unitario) as Precio_Promedio,
                    COUNT(DISTINCT f.ID_Factura) as Pedidos,
                    (SELECT GROUP_CONCAT(DISTINCT p.ValorAtributo2) 
                     FROM " . $this->table_factura_producto . " fp2
                     INNER JOIN " . $this->table_producto . " p ON fp2.ID_Producto = p.ID_Producto
                     WHERE fp2.ID_Factura = f.ID_Factura 
                     AND p.ID_Articulo = a.ID_Articulo) as Colores
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  INNER JOIN " . $this->table_producto . " p ON fp.ID_Producto = p.ID_Producto
                  INNER JOIN " . $this->table_articulo . " a ON p.ID_Articulo = a.ID_Articulo
                  INNER JOIN " . $this->table_categoria . " c ON a.ID_Categoria = c.ID_Categoria
                  INNER JOIN " . $this->table_genero . " g ON a.ID_Genero = g.ID_Genero
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY a.ID_Articulo, a.N_Articulo, c.N_Categoria, g.N_Genero
                  ORDER BY Ventas_Totales DESC
                  LIMIT " . (int)$limite;

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📊 OBTENER COMPARATIVA MENSUAL
    public function obtenerComparativaMensual($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    YEAR(f.Fecha_Factura) as Ano,
                    MONTH(f.Fecha_Factura) as Mes,
                    DATE_FORMAT(f.Fecha_Factura, '%M') as Nombre_Mes,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Monto_Total,
                    SUM(fp.Cantidad) as Productos_Vendidos
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY YEAR(f.Fecha_Factura), MONTH(f.Fecha_Factura)
                  ORDER BY Ano DESC, Mes ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📊 OBTENER COMPARATIVA ANUAL
    public function obtenerComparativaAnual($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    YEAR(f.Fecha_Factura) as Ano,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Monto_Total,
                    SUM(fp.Cantidad) as Productos_Vendidos,
                    COUNT(DISTINCT f.ID_Usuario) as Clientes_Unicos,
                    AVG(f.Monto_Total) as Ticket_Promedio
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY YEAR(f.Fecha_Factura)
                  ORDER BY Ano DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 🏆 OBTENER PRODUCTOS MÁS VENDIDOS
    public function obtenerProductosMasVendidos($fechaInicio, $fechaFin, $limite = 10)
    {
        $query = "SELECT 
                    a.N_Articulo as Producto,
                    c.N_Categoria as Categoria,
                    SUM(fp.Cantidad) as Cantidad_Vendida,
                    SUM(fp.Subtotal) as Ventas_Totales,
                    AVG(fp.Precio_Unitario) as Precio_Promedio,
                    COUNT(DISTINCT f.ID_Factura) as Pedidos
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  INNER JOIN " . $this->table_producto . " p ON fp.ID_Producto = p.ID_Producto
                  INNER JOIN " . $this->table_articulo . " a ON p.ID_Articulo = a.ID_Articulo
                  INNER JOIN " . $this->table_categoria . " c ON a.ID_Categoria = c.ID_Categoria
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY a.ID_Articulo, a.N_Articulo, c.N_Categoria
                  ORDER BY Ventas_Totales DESC
                  LIMIT " . (int)$limite;

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 💸 OBTENER PÉRDIDAS POR DESCUENTO
    public function obtenerPerdidasPorDescuento($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    d.Codigo as Codigo_Descuento,
                    COUNT(DISTINCT f.ID_Factura) as Pedidos_Afectados,
                    SUM(fp.Descuento_Aplicado) as Total_Descuento,
                    AVG(fp.Descuento_Aplicado) as Descuento_Promedio,
                    d.Tipo as Tipo_Descuento,
                    d.Valor as Valor_Descuento,
                    CASE 
                        WHEN d.ID_Articulo IS NOT NULL THEN 'Producto'
                        WHEN d.ID_Categoria IS NOT NULL THEN 'Categoria'
                        ELSE 'General'
                    END as Alcance
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  LEFT JOIN " . $this->table_descuento . " d ON fp.ID_Descuento = d.ID_Descuento
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND fp.Descuento_Aplicado > 0
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY d.ID_Descuento, d.Codigo, d.Tipo, d.Valor, Alcance
                  ORDER BY Total_Descuento DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 💳 OBTENER VENTAS POR MÉTODO DE PAGO
    public function obtenerVentasPorMetodoPago($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    mp.T_Pago as Metodo_Pago,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Valor_Total,
                    AVG(f.Monto_Total) as Valor_Promedio,
                    (COUNT(*) * 100.0 / (SELECT COUNT(*) FROM " . $this->table_factura . " 
                      WHERE DATE(Fecha_Factura) BETWEEN ? AND ? AND Estado NOT IN ('Anulado'))) as Porcentaje
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_metodo_pago . " mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY mp.ID_Metodo_Pago, mp.T_Pago
                  ORDER BY Valor_Total DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin, $fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 👥 OBTENER CLIENTES FRECUENTES
    public function obtenerClientesFrecuentes($fechaInicio, $fechaFin, $limite = 10)
    {
        $query = "SELECT 
                    u.ID_Usuario,
                    CONCAT(u.Nombre, ' ', u.Apellido) as Cliente,
                    u.Correo,
                    COUNT(DISTINCT f.ID_Factura) as Total_Pedidos,
                    SUM(f.Monto_Total) as Valor_Total,
                    AVG(f.Monto_Total) as Ticket_Promedio,
                    MAX(f.Fecha_Factura) as Ultima_Compra,
                    MIN(f.Fecha_Factura) as Primera_Compra
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_usuario . " u ON f.ID_Usuario = u.ID_Usuario
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY u.ID_Usuario, u.Nombre, u.Apellido, u.Correo
                  ORDER BY Valor_Total DESC
                  LIMIT " . (int)$limite;

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerGananciaNetaReal($fechaInicio, $fechaFin) {
        $query = "SELECT 
                    COALESCE(SUM(f.Monto_Total), 0) - 
                    COALESCE(SUM(fp.Descuento_Aplicado), 0) - 
                    COALESCE(SUM(f.IVA), 0) as Ganancia_Neta_Real,
                    COALESCE(SUM(f.Monto_Total), 0) as Ventas_Totales,
                    COALESCE(SUM(fp.Descuento_Aplicado), 0) as Total_Descuentos,
                    COALESCE(SUM(f.IVA), 0) as Total_IVA
                FROM " . $this->table_factura . " f
                LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                AND f.Estado NOT IN ('Anulado')";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 📄 OBTENER VENTAS DETALLADAS (CORREGIDA - sin Ciudad, Departamento)
    public function obtenerVentasDetalladas($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    f.Codigo_Acceso,
                    CONCAT(u.Nombre, ' ', u.Apellido) as Cliente,
                    f.Fecha_Factura,
                    f.Monto_Total,
                    f.Subtotal,
                    f.IVA,
                    mp.T_Pago as Metodo_Pago,
                    f.Estado,
                    COUNT(fp.ID_FacturaProducto) as Cantidad_Productos,
                    SUM(fp.Descuento_Aplicado) as Descuento_Aplicado,
                    d.Codigo as Codigo_Descuento,
                    (f.Monto_Total - COALESCE(SUM(fp.Descuento_Aplicado), 0)) as Neto
                FROM " . $this->table_factura . " f
                LEFT JOIN " . $this->table_usuario . " u ON f.ID_Usuario = u.ID_Usuario
                LEFT JOIN " . $this->table_metodo_pago . " mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                LEFT JOIN " . $this->table_descuento . " d ON fp.ID_Descuento = d.ID_Descuento
                WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                AND f.Estado NOT IN ('Anulado')
                GROUP BY f.Codigo_Acceso, u.Nombre, u.Apellido, f.Fecha_Factura, 
                        f.Monto_Total, f.Subtotal, f.IVA, mp.T_Pago, f.Estado, 
                        d.Codigo
                ORDER BY f.Fecha_Factura DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📊 OBTENER VENTAS POR PRODUCTO
    public function obtenerVentasPorProducto($fechaInicio, $fechaFin, $categoria = '', $orden = 'ventas_desc') {
        $whereCategoria = '';
        $params = [$fechaInicio, $fechaFin];
        
        if (!empty($categoria)) {
            $whereCategoria = " AND c.ID_Categoria = ?";
            $params[] = $categoria;
        }
        
        $orderBy = 'Total_Ventas DESC';
        switch ($orden) {
            case 'cantidad_asc':
                $orderBy = 'Cantidad_Vendida ASC';
                break;
            case 'cantidad_desc':
                $orderBy = 'Cantidad_Vendida DESC';
                break;
            case 'ventas_asc':
                $orderBy = 'Total_Ventas ASC';
                break;
            case 'ventas_desc':
                $orderBy = 'Total_Ventas DESC';
                break;
        }
        
        $query = "SELECT 
                    a.ID_Articulo,
                    a.N_Articulo as Producto,
                    c.N_Categoria as Categoria,
                    g.N_Genero as Genero,
                    CAST(COALESCE(SUM(fp.Cantidad), 0) AS DECIMAL(20,2)) as Cantidad_Vendida,
                    CAST(COALESCE(SUM(fp.Subtotal), 0) AS DECIMAL(20,2)) as Total_Ventas,
                    CAST(COALESCE(AVG(fp.Precio_Unitario), 0) AS DECIMAL(20,2)) as Precio_Promedio,
                    CAST(COALESCE(COUNT(DISTINCT f.ID_Factura), 0) AS DECIMAL(20,2)) as Pedidos,
                    CAST(COALESCE(SUM(fp.Descuento_Aplicado), 0) AS DECIMAL(20,2)) as Descuentos_Otorgados,
                    CAST(COALESCE(SUM(fp.Subtotal), 0) - COALESCE(SUM(fp.Descuento_Aplicado), 0) AS DECIMAL(20,2)) as Ventas_Netas
                FROM " . $this->table_factura . " f
                INNER JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                INNER JOIN " . $this->table_producto . " p ON fp.ID_Producto = p.ID_Producto
                INNER JOIN " . $this->table_articulo . " a ON p.ID_Articulo = a.ID_Articulo
                INNER JOIN " . $this->table_categoria . " c ON a.ID_Categoria = c.ID_Categoria
                INNER JOIN genero g ON a.ID_Genero = g.ID_Genero
                WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                AND f.Estado NOT IN ('Anulado')
                " . $whereCategoria . "
                GROUP BY a.ID_Articulo, a.N_Articulo, c.N_Categoria, g.N_Genero
                ORDER BY " . $orderBy;

        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // CONVERTIR TODOS LOS VALORES NÚMERICOS A FLOAT
        foreach ($resultados as &$row) {
            $row['Cantidad_Vendida'] = (float)$row['Cantidad_Vendida'];
            $row['Total_Ventas'] = (float)$row['Total_Ventas'];
            $row['Precio_Promedio'] = (float)$row['Precio_Promedio'];
            $row['Pedidos'] = (float)$row['Pedidos'];
            $row['Descuentos_Otorgados'] = (float)$row['Descuentos_Otorgados'];
            $row['Ventas_Netas'] = (float)$row['Ventas_Netas'];
        }
        
        return $resultados;
    }

    // 👤 OBTENER VENTAS POR CLIENTE
    public function obtenerVentasPorCliente($fechaInicio, $fechaFin, $orden = 'monto_desc')
    {
        $orderBy = 'Valor_Total DESC';
        switch ($orden) {
            case 'pedidos_asc':
                $orderBy = 'Total_Pedidos ASC';
                break;
            case 'pedidos_desc':
                $orderBy = 'Total_Pedidos DESC';
                break;
            case 'monto_asc':
                $orderBy = 'Valor_Total ASC';
                break;
            case 'monto_desc':
                $orderBy = 'Valor_Total DESC';
                break;
            case 'reciente':
                $orderBy = 'Ultima_Compra DESC';
                break;
        }
        
        $query = "SELECT 
                    u.ID_Usuario,
                    CONCAT(u.Nombre, ' ', u.Apellido) as Cliente,
                    u.Correo,
                    u.Celular,
                    u.Activo,
                    COUNT(DISTINCT f.ID_Factura) as Total_Pedidos,
                    SUM(f.Monto_Total) as Valor_Total,
                    AVG(f.Monto_Total) as Ticket_Promedio,
                    MAX(f.Fecha_Factura) as Ultima_Compra,
                    MIN(f.Fecha_Factura) as Primera_Compra,
                    SUM(fp.Cantidad) as Productos_Comprados,
                    SUM(fp.Descuento_Aplicado) as Descuentos_Obtenidos,
                    GROUP_CONCAT(DISTINCT mp.T_Pago ORDER BY mp.T_Pago) as Metodos_Pago_Usados
                  FROM " . $this->table_factura . " f
                  INNER JOIN " . $this->table_usuario . " u ON f.ID_Usuario = u.ID_Usuario
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  LEFT JOIN " . $this->table_metodo_pago . " mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY u.ID_Usuario, u.Nombre, u.Apellido, u.Correo, u.Celular, u.Activo
                  ORDER BY " . $orderBy;

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 💰 OBTENER REPORTE FINANCIERO (simplificado)
    public function obtenerReporteFinanciero($fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    'Ventas Brutas' as Concepto,
                    COALESCE(SUM(f.Monto_Total), 0) as Monto
                  FROM " . $this->table_factura . " f
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  
                  UNION ALL
                  
                  SELECT 
                    'Descuentos Otorgados',
                    COALESCE(SUM(fp.Descuento_Aplicado), 0)
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  
                  UNION ALL
                  
                  SELECT 
                    'IVA Recaudado',
                    COALESCE(SUM(f.IVA), 0)
                  FROM " . $this->table_factura . " f
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  
                  UNION ALL
                  
                  SELECT 
                    'Ventas Netas',
                    COALESCE(SUM(f.Monto_Total), 0) - COALESCE(SUM(fp.Descuento_Aplicado), 0)
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  
                  UNION ALL
                  
                  SELECT 
                    'Total Pedidos',
                    COUNT(*)
                  FROM " . $this->table_factura . " f
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  
                  UNION ALL
                  
                  SELECT 
                    'Pedidos Entregados',
                    COUNT(*)
                  FROM " . $this->table_factura . " f
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado = 'Entregado'
                  
                  UNION ALL
                  
                  SELECT 
                    'Ticket Promedio',
                    COALESCE(AVG(f.Monto_Total), 0)
                  FROM " . $this->table_factura . " f
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  
                  UNION ALL
                  
                  SELECT 
                    'Productos Vendidos',
                    COALESCE(SUM(fp.Cantidad), 0)
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin,
            $fechaInicio, $fechaFin
        ]);
        
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Organizar resultados
        $financiero = [];
        foreach ($resultados as $row) {
            $financiero[$row['Concepto']] = $row['Monto'];
        }
        
        return $financiero;
    }

    // 📈 OBTENER EVOLUCIÓN MENSUAL
    public function obtenerEvolucionMensual($ano, $fechaInicio, $fechaFin)
    {
        $query = "SELECT 
                    DATE_FORMAT(f.Fecha_Factura, '%Y-%m') as Mes,
                    DATE_FORMAT(f.Fecha_Factura, '%M') as Nombre_Mes,
                    COUNT(*) as Pedidos,
                    SUM(f.Monto_Total) as Ventas_Totales,
                    COALESCE(SUM(fp.Cantidad), 0) as Productos_Vendidos,
                    COALESCE(AVG(f.Monto_Total), 0) as Ticket_Promedio,
                    COALESCE(SUM(fp.Descuento_Aplicado), 0) as Descuentos,
                    (COALESCE(SUM(f.Monto_Total), 0) - COALESCE(SUM(fp.Descuento_Aplicado), 0)) as Ventas_Netas
                  FROM " . $this->table_factura . " f
                  LEFT JOIN " . $this->table_factura_producto . " fp ON f.ID_Factura = fp.ID_Factura
                  WHERE YEAR(f.Fecha_Factura) = ?
                  AND DATE(f.Fecha_Factura) BETWEEN ? AND ?
                  AND f.Estado NOT IN ('Anulado')
                  GROUP BY DATE_FORMAT(f.Fecha_Factura, '%Y-%m'), DATE_FORMAT(f.Fecha_Factura, '%M')
                  ORDER BY Mes ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$ano, $fechaInicio, $fechaFin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 📋 OBTENER CATEGORÍAS
    public function obtenerCategorias() {
        $query = "SELECT ID_Categoria, N_Categoria 
                FROM categoria 
                ORDER BY N_Categoria";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>