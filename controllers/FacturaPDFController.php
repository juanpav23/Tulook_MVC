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
        // ✅ LIMPIAR CUALQUIER SALIDA ANTES DE ENVIAR HEADERS
        if (ob_get_length()) ob_clean();
        
        // Obtener ID de la factura
        $id = (int)($_GET['id'] ?? 0);
        
        if (!$id) {
            $this->redirigirInicio();
            return;
        }

        try {
            // Obtener datos de la factura
            $factura = $this->compra->obtenerFacturaDetalle($id);
            
            if (!$factura) {
                $this->redirigirInicio();
                return;
            }

            $items = $this->compra->obtenerFacturaItems($id);

            // ✅ PREPARAR ITEMS PARA EL PDF CON ATRIBUTOS DINÁMICOS
            $itemsParaPdf = [];
            foreach ($items as $item) {
                // ✅ USAR EL MÉTODO AUXILIAR PARA OBTENER ESPECIFICACIONES
                $especificaciones = $this->formatearEspecificacionesItem($item);
                
                // ✅ CREAR ESPECIFICACIONES CLARAS
                $especificacionesStr = '';
                
                // Construir especificaciones limpias
                foreach ($especificaciones as $esp) {
                    if (!empty($esp)) {
                        if (!empty($especificacionesStr)) {
                            $especificacionesStr .= ' | ';
                        }
                        $especificacionesStr .= $esp;
                    }
                }
                
                // Si no hay especificaciones, usar "—"
                if (empty($especificacionesStr)) {
                    $especificacionesStr = '—';
                }
                
                $itemsParaPdf[] = [
                    'Nombre_Producto' => $item['Nombre_Final'] ?? $item['N_Articulo'] ?? 'Producto',
                    'Producto' => $item['Nombre_Final'] ?? $item['N_Articulo'] ?? 'Producto',
                    'Especificaciones' => $especificacionesStr,  // ✅ SOLO ESTE CAMPO
                    'Cantidad' => (int)($item['Cantidad'] ?? 1),
                    'Precio_Unitario' => floatval($item['Precio_Unitario'] ?? $item['Subtotal'] / max(1, $item['Cantidad'])),
                    'Precio' => floatval($item['Precio_Unitario'] ?? $item['Subtotal'] / max(1, $item['Cantidad'])),
                    'Subtotal' => floatval($item['Subtotal'] ?? 0)
                ];
            }

            // CONVERTIR MÉTODO DE PAGO
            $metodos_map = [1 => 'Tarjeta', 2 => 'Efectivo', 3 => 'PSE'];
            $metodo_pago_nombre = $metodos_map[$factura['ID_Metodo_Pago']] ?? 'No especificado';

            // Preparar factura para el PDF
            $facturaParaPdf = [
                'ID_Factura' => $factura['ID_Factura'] ?? $id,
                'Nombre_Cliente' => trim(($factura['Nombre'] ?? '') . ' ' . ($factura['Apellido'] ?? '')),
                'Email_Cliente' => $factura['Correo'] ?? 'No especificado',
                'Telefono_Cliente' => 'No especificado',
                'Fecha_Factura' => $factura['Fecha_Factura'] ?? date('Y-m-d H:i:s'),
                'Metodo_Pago' => $metodo_pago_nombre,
                'Total' => floatval($factura['Monto_Total'] ?? 0),
                'Direccion_Completa' => ($factura['Direccion'] ?? '') . ', ' . 
                                       ($factura['Ciudad'] ?? '') . ', ' . 
                                       ($factura['Departamento'] ?? ''),
                // CAMPOS COMPATIBILIDAD
                'CodigoPostal' => $factura['CodigoPostal'] ?? '155201',
                'Nombre' => $factura['Nombre'] ?? '',
                'Apellido' => $factura['Apellido'] ?? '',
                'Correo' => $factura['Correo'] ?? '',
                'Monto_Total' => $factura['Monto_Total'] ?? 0,
                'T_Pago' => $metodo_pago_nombre
            ];

            $mailer = new Mailer();
            $pdfPath = $mailer->generarPdfFactura([
                'factura' => $facturaParaPdf,
                'items' => $itemsParaPdf
            ]);

            if (!file_exists($pdfPath)) {
                throw new Exception("No se pudo generar el archivo PDF");
            }

            // ✅ ENVIAR HEADERS ANTES DE CUALQUIER SALIDA
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=factura_$id.pdf");
            header("Content-Length: " . filesize($pdfPath));
            header("Cache-Control: no-cache, must-revalidate");
            header("Expires: 0");
            header("Pragma: no-cache");

            // ✅ LEER Y ENVIAR EL ARCHIVO
            readfile($pdfPath);
            
            // ✅ LIMPIAR ARCHIVO TEMPORAL
            unlink($pdfPath);
            exit;

        } catch (Exception $e) {
            // ✅ MANEJAR ERRORES SIN ENVIAR SALIDA
            error_log("Error generando PDF: " . $e->getMessage());
            $this->redirigirConError($id, 'Error al generar el PDF');
        }
    }

    // ✅ MÉTODO AUXILIAR PARA FORMATEAR ATRIBUTOS (COPIADO DE Compra.php)
    private function formatearEspecificacionesItem($item) {
        $especificaciones = [];
        
        // Verificar si hay atributos dinámicos
        if (!empty($item['ID_Atributo1']) && !empty($item['ValorAtributo1'])) {
            $nombre = !empty($item['Nombre_Atributo1']) ? $item['Nombre_Atributo1'] : 'Especificación';
            $especificaciones[] = $nombre . ': ' . $item['ValorAtributo1'];
        }
        
        if (!empty($item['ID_Atributo2']) && !empty($item['ValorAtributo2'])) {
            $nombre = !empty($item['Nombre_Atributo2']) ? $item['Nombre_Atributo2'] : 'Especificación';
            $especificaciones[] = $nombre . ': ' . $item['ValorAtributo2'];
        }
        
        if (!empty($item['ID_Atributo3']) && !empty($item['ValorAtributo3'])) {
            $nombre = !empty($item['Nombre_Atributo3']) ? $item['Nombre_Atributo3'] : 'Especificación';
            $especificaciones[] = $nombre . ': ' . $item['ValorAtributo3'];
        }
        
        return $especificaciones;
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