<?php
// controllers/FacturaPDFController.php
require_once "models/Compra.php";
require_once "services/Mailer.php";

class FacturaPDFController {
    private $db;
    private $compra;

    public function __construct($db) {
        $this->db = $db;
        $this->compra = new Compra($db);
    }

    public function generar() {
        // Limpiar buffer
        if (ob_get_length()) ob_clean();
        
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $this->redirigirInicio();
            return;
        }

        try {
            // Obtener datos
            $factura = $this->compra->obtenerFacturaDetalle($id);
            
            if (!$factura) {
                throw new Exception("Factura no encontrada");
            }

            $items = $this->compra->obtenerFacturaItems($id);
            
            if (empty($items)) {
                throw new Exception("No hay items en la factura");
            }

            // Preparar items para PDF
            $itemsParaPdf = [];
            $descuento_total = 0;
            
            foreach ($items as $item) {
                // Obtener especificaciones limpias
                $especificaciones = $this->obtenerEspecificacionesLimpias($item);
                
                // Calcular precios
                $cantidad = intval($item['Cantidad'] ?? 1);
                $precio_unitario = floatval($item['Precio_Unitario'] ?? 0);
                $descuento_item = floatval($item['Descuento_Aplicado'] ?? 0);
                
                // Calcular precio original antes de descuento
                $precio_original = $precio_unitario;
                if ($descuento_item > 0 && $cantidad > 0) {
                    $precio_original = $precio_unitario + ($descuento_item / $cantidad);
                }
                
                // Acumular descuento total
                $descuento_total += $descuento_item;
                
                $itemsParaPdf[] = [
                    'Nombre_Producto' => $this->obtenerNombreProducto($item),
                    'Producto' => $this->obtenerNombreProducto($item),
                    'Especificaciones' => $especificaciones,
                    'Cantidad' => $cantidad,
                    'Precio_Unitario' => $precio_unitario,
                    'Precio_Original' => $precio_original,
                    'Precio' => $precio_unitario,
                    'Subtotal' => floatval($item['Subtotal'] ?? 0),
                    'Descuento_Aplicado' => $descuento_item
                ];
            }

            // Preparar datos de factura para PDF
            $facturaParaPdf = $this->prepararDatosFactura($factura, $descuento_total);

            // Generar PDF
            $mailer = new Mailer();
            $pdfPath = $mailer->generarPdfFactura([
                'factura' => $facturaParaPdf,
                'items' => $itemsParaPdf
            ]);

            if (!file_exists($pdfPath)) {
                throw new Exception("No se pudo generar el archivo PDF");
            }

            // Enviar PDF al navegador
            $this->enviarPDF($pdfPath, $facturaParaPdf['ID_Factura']);

        } catch (Exception $e) {
            error_log("Error generando PDF: " . $e->getMessage());
            $this->redirigirConError($id, $e->getMessage());
        }
    }

    // =======================================================
    // MÉTODOS AUXILIARES
    // =======================================================
    
    private function obtenerNombreProducto($item) {
    // ✅ CORRECCIÓN: USAR EL MISMO LÓGICA QUE EN exito.php
    
    $nombre_base = $item['N_Articulo'] ?? 'Producto';
    $nombre_variante = $item['Nombre_Producto'] ?? '';
    
    // Decidir qué nombre mostrar
    if (!empty($nombre_variante) && trim($nombre_variante) !== '') {
        // ✅ SIEMPRE mostrar el nombre de la variante si existe
        $nombre = $nombre_variante;
    } else {
        // Si no hay nombre de variante, mostrar el base con atributos
        $especificaciones = [];
        
        for ($i = 1; $i <= 3; $i++) {
            $valor = $item["ValorAtributo{$i}"] ?? '';
            if (!empty($valor) && $valor !== '—' && $valor !== 'NO' && $valor !== 'NULL') {
                $especificaciones[] = $valor;
            }
        }
        
        $nombre = $nombre_base;
        if (!empty($especificaciones)) {
            $nombre .= " (" . implode(", ", $especificaciones) . ")";
        }
    }
    
    // ✅ Formatear igual que en exito.php
    return ucwords(strtolower(trim($nombre)));
}
    
    private function obtenerEspecificacionesLimpias($item) {
    // ✅ Si ya usamos el Nombre_Producto completo, no necesitamos duplicar atributos
    // Solo mostrar información adicional que no esté en el nombre
    
    $especificaciones = [];
    
    // Podrías mostrar aquí información técnica o específica
    // Por ejemplo: código del producto, sku, etc.
    $codigo = $item['Codigo'] ?? $item['Codigo_Producto'] ?? '';
    if (!empty($codigo)) {
        $especificaciones[] = "Código: $codigo";
    }
    
    // O mostrar solo los atributos 2 y 3 si el 1 ya está en el nombre
    for ($i = 2; $i <= 3; $i++) {
        $valor = $item["ValorAtributo{$i}"] ?? '';
        if (!empty($valor) && $valor != 'NO' && $valor != '—') {
            $especificaciones[] = $valor;
        }
    }
    
    return empty($especificaciones) ? '—' : implode(' | ', $especificaciones);
}
    
    private function prepararDatosFactura($factura, $descuento_total) {
        // Mapear métodos de pago según tu BD
        $metodos_map = [
            1 => 'Tarjeta de Crédito',
            2 => 'Tarjeta de Débito', 
            3 => 'Efectivo',
            4 => 'PSE',
            5 => 'Transferencia'
        ];
        
        $metodo_pago_id = $factura['ID_Metodo_Pago'] ?? 0;
        $metodo_pago_nombre = $metodos_map[$metodo_pago_id] ?? 'No especificado';
        
        // Construir dirección completa
        $direccion_parts = [];
        if (!empty($factura['Direccion'])) $direccion_parts[] = $factura['Direccion'];
        if (!empty($factura['Ciudad'])) $direccion_parts[] = $factura['Ciudad'];
        if (!empty($factura['Departamento'])) $direccion_parts[] = $factura['Departamento'];
        $direccion_completa = implode(', ', $direccion_parts);
        
        return [
            'ID_Factura' => $factura['ID_Factura'] ?? 0,
            'Nombre_Cliente' => trim(($factura['Nombre'] ?? '') . ' ' . ($factura['Apellido'] ?? '')),
            'Email_Cliente' => $factura['Correo'] ?? 'No especificado',
            'Telefono_Cliente' => $factura['Celular'] ?? 'No especificado',
            'Celular' => $factura['Celular'] ?? 'No especificado',
            'Fecha_Factura' => $factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'),
            'Metodo_Pago' => $metodo_pago_nombre,
            'Monto_Total' => floatval($factura['Monto_Total'] ?? 0),
            'Subtotal' => floatval($factura['Subtotal'] ?? 0),
            'IVA' => floatval($factura['IVA'] ?? 0),
            'Direccion_Completa' => $direccion_completa ?: 'No especificada',
            'CodigoPostal' => $factura['CodigoPostal'] ?? '',
            'Nombre' => $factura['Nombre'] ?? '',
            'Apellido' => $factura['Apellido'] ?? '',
            'Correo' => $factura['Correo'] ?? '',
            'N_Documento' => $factura['N_Documento'] ?? '',
            'Tipo_Documento' => $factura['Tipo_Documento'] ?? '',
            'T_Pago' => $metodo_pago_nombre,
            'Estado' => $factura['Estado'] ?? '',
            'Descuento_Total' => $descuento_total,
            'IVA_Porcentaje' => 19
        ];
    }
    
    private function enviarPDF($pdfPath, $facturaId) {
        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=Factura_TuLook_" . 
               str_pad($facturaId, 6, '0', STR_PAD_LEFT) . ".pdf");
        header("Content-Length: " . filesize($pdfPath));
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: 0");
        header("Pragma: no-cache");
        
        readfile($pdfPath);
        
        // Limpiar archivo temporal
        if (file_exists($pdfPath)) {
            unlink($pdfPath);
        }
        
        exit;
    }
    
    private function redirigirInicio() {
        header("Location: " . BASE_URL);
        exit;
    }
    
    private function redirigirConError($id_factura, $mensaje = '') {
        $url = BASE_URL . "?c=Checkout&a=exito&id=$id_factura";
        if ($mensaje) {
            $url .= "&error=" . urlencode($mensaje);
        }
        header("Location: $url");
        exit;
    }
}