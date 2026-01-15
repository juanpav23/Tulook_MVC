<?php
// services/DescuentoService.php
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
        return $this->descuentoModel->obtenerMejorDescuento($idArticulo, $idProducto, $idCategoria);
    }

    /**
     * Aplica el descuento al precio
     */
    private function aplicarDescuento($precio, $descuento) {
        if ($descuento['Tipo'] === 'Porcentaje') {
            $precioConDescuento = $precio * (1 - ($descuento['Valor'] / 100));
            return round($precioConDescuento, 2);
        } else { // ValorFijo
            $precioFinal = $precio - $descuento['Valor'];
            return $precioFinal > 0 ? round($precioFinal, 2) : 0;
        }
    }

    /**
     * Obtiene información formateada del descuento para mostrar
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
            'tiene_descuento' => true
        ];
    }
}
?>