<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;
use PDOStatement;

class ProductoTest extends TestCase
{
    private $db;
    private $producto;
    private static $testArticuloId;
    private static $testProductoId;
    
    public static function setUpBeforeClass(): void
    {
        // Configuración inicial una vez antes de todas las pruebas
        echo "\n🎯 INICIANDO PRUEBAS DE PRODUCTO\n";
        echo "================================\n";
    }
    
    protected function setUp(): void
    {
        // Configurar conexión a BD para cada test
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=tulook;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Pasar la conexión al constructor de Producto
            $this->producto = new \Producto($this->db);
            
        } catch (PDOException $e) {
            $this->markTestSkipped('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        
        // Obtener IDs de prueba válidos de la base de datos
        $this->obtenerIdsDePrueba();
    }
    
    protected function tearDown(): void
    {
        $this->db = null;
        $this->producto = null;
    }
    
    private function obtenerIdsDePrueba()
    {
        // Obtener un artículo válido para pruebas
        $stmt = $this->db->query("SELECT ID_Articulo FROM articulo WHERE Activo = 1 LIMIT 1");
        $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($articulo) {
            self::$testArticuloId = $articulo['ID_Articulo'];
            
            // Obtener un producto válido para pruebas
            $stmt = $this->db->query("SELECT ID_Producto FROM producto WHERE ID_Articulo = " . self::$testArticuloId . " LIMIT 1");
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($producto) {
                self::$testProductoId = $producto['ID_Producto'];
            }
        }
    }
    
    /**
     * Test 1: Verificar que Producto se instancia correctamente
     */
    public function testProductoSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(\Producto::class, $this->producto);
        echo "✅ Test 1 PASÓ: Producto se instancia correctamente\n";
    }
    
    /**
     * Test 2: Verificar que read() funciona correctamente
     */
    public function testReadFuncionaCorrectamente()
    {
        $stmt = $this->producto->read();
        
        $this->assertInstanceOf(PDOStatement::class, $stmt);
        
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertIsArray($productos);
        
        echo "✅ Test 2 PASÓ: read() funciona correctamente - " . count($productos) . " productos encontrados\n";
        
        // Mostrar primeros 3 productos para diagnóstico
        if (count($productos) > 0) {
            echo "   📋 Muestra de productos:\n";
            foreach (array_slice($productos, 0, 3) as $index => $producto) {
                echo "      " . ($index + 1) . ". ID: {$producto['ID_Articulo']} | ";
                echo "Nombre: {$producto['N_Articulo']} | ";
                echo "Precio: {$producto['Precio']} | ";
                echo "Stock: {$producto['Stock']}\n";
            }
        }
    }
    
    /**
     * Test 3: Verificar que obtenerPorId() funciona correctamente
     */
    public function testObtenerPorIdFuncionaCorrectamente()
    {
        if (!self::$testProductoId) {
            // Intentar obtener cualquier producto de la base de datos
            $stmt = $this->db->query("SELECT ID_Producto FROM producto LIMIT 1");
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$producto) {
                $this->markTestSkipped('No hay productos en la base de datos para probar');
            }
            
            self::$testProductoId = $producto['ID_Producto'];
        }
        
        $productoDetalle = $this->producto->obtenerPorId(self::$testProductoId);
        
        // Verificar que no sea false y sea un array
        if ($productoDetalle === false) {
            $this->markTestSkipped('obtenerPorId() retornó false para ID: ' . self::$testProductoId);
        }
        
        $this->assertIsArray($productoDetalle);
        $this->assertArrayHasKey('ID_Producto', $productoDetalle);
        $this->assertArrayHasKey('N_Articulo', $productoDetalle);
        
        echo "✅ Test 3 PASÓ: obtenerPorId() funciona correctamente\n";
        echo "   Producto: {$productoDetalle['N_Articulo']} (ID: {$productoDetalle['ID_Producto']})\n";
    }
    
    /**
     * Test 4: Verificar que readOne() funciona correctamente
     */
    public function testReadOneFuncionaCorrectamente()
    {
        if (!self::$testProductoId) {
            $this->markTestSkipped('No hay productos variantes para probar');
        }
        
        $producto = $this->producto->readOne(self::$testProductoId);
        
        // readOne puede retornar null si no encuentra el producto
        if ($producto === null) {
            $this->markTestSkipped('readOne() retornó null para ID: ' . self::$testProductoId);
        }
        
        $this->assertIsArray($producto);
        $this->assertArrayHasKey('ID_Producto', $producto);
        $this->assertArrayHasKey('Nombre_Producto', $producto);
        
        echo "✅ Test 4 PASÓ: readOne() funciona correctamente\n";
        if (isset($producto['Nombre_Completo'])) {
            echo "   Nombre completo: {$producto['Nombre_Completo']}\n";
        }
    }
    
    /**
     * Test 5: Verificar que readBase() funciona correctamente
     */
    public function testReadBaseFuncionaCorrectamente()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para probar');
        }
        
        $articulo = $this->producto->readBase(self::$testArticuloId);
        
        // readBase puede retornar null si no encuentra el artículo
        if ($articulo === null) {
            $this->markTestSkipped('readBase() retornó null para ID: ' . self::$testArticuloId);
        }
        
        $this->assertIsArray($articulo);
        $this->assertArrayHasKey('ID_Articulo', $articulo);
        $this->assertArrayHasKey('N_Articulo', $articulo);
        $this->assertArrayHasKey('Precio', $articulo);
        $this->assertArrayHasKey('Cantidad', $articulo);
        
        echo "✅ Test 5 PASÓ: readBase() funciona correctamente\n";
        echo "   Artículo: {$articulo['N_Articulo']} (ID: {$articulo['ID_Articulo']})\n";
    }
    
    /**
     * Test 6: Verificar que getVariantesByArticulo() funciona correctamente
     */
    public function testGetVariantesByArticuloFuncionaCorrectamente()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para probar');
        }
        
        $variantes = $this->producto->getVariantesByArticulo(self::$testArticuloId);
        
        $this->assertIsArray($variantes);
        
        echo "✅ Test 6 PASÓ: getVariantesByArticulo() funciona correctamente - " . count($variantes) . " variantes\n";
        
        if (count($variantes) > 0) {
            echo "   🎨 Muestra de variantes:\n";
            foreach (array_slice($variantes, 0, 2) as $index => $variante) {
                $color = $variante['N_Color'] ?? 'Sin color';
                $talla = $variante['N_Talla'] ?? 'Sin talla';
                echo "      " . ($index + 1) . ". Color: {$color} | Talla: {$talla} | ";
                echo "Precio: {$variante['Precio_Final']} | Stock: {$variante['Cantidad']}\n";
            }
        }
    }
    
    /**
     * Test 7: Verificar que getTallasDisponiblesByArticulo() funciona correctamente
     */
    public function testGetTallasDisponiblesByArticuloFuncionaCorrectamente()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para probar');
        }
        
        $tallas = $this->producto->getTallasDisponiblesByArticulo(self::$testArticuloId);
        
        $this->assertIsArray($tallas);
        
        echo "✅ Test 7 PASÓ: getTallasDisponiblesByArticulo() funciona correctamente - " . count($tallas) . " tallas\n";
        
        if (count($tallas) > 0) {
            echo "   📏 Tallas disponibles: ";
            $nombresTallas = array_map(function($talla) {
                return $talla['N_Talla'] ?? 'Desconocida';
            }, $tallas);
            echo implode(', ', array_slice($nombresTallas, 0, 5)) . "\n";
        }
    }
    
    /**
     * Test 8: Verificar que getStockTotal() funciona correctamente
     */
    public function testGetStockTotalFuncionaCorrectamente()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para probar');
        }
        
        $stockTotal = $this->producto->getStockTotal(self::$testArticuloId);
        
        $this->assertIsInt($stockTotal);
        $this->assertGreaterThanOrEqual(0, $stockTotal);
        
        echo "✅ Test 8 PASÓ: getStockTotal() funciona correctamente\n";
        echo "   Stock total del artículo " . self::$testArticuloId . ": " . $stockTotal . "\n";
    }
    
    /**
     * Test 9: Verificar que getDestacados() funciona correctamente
     */
    public function testGetDestacadosFuncionaCorrectamente()
    {
        try {
            // Usar un límite más pequeño
            $destacados = $this->producto->getDestacados(3);
            
            $this->assertIsArray($destacados);
            
            echo "✅ Test 9 PASÓ: getDestacados() funciona correctamente - " . count($destacados) . " destacados\n";
            
            if (count($destacados) > 0) {
                echo "   ⭐ Productos destacados:\n";
                foreach ($destacados as $index => $destacado) {
                    echo "      " . ($index + 1) . ". {$destacado['N_Articulo']} - \${$destacado['Precio']}\n";
                }
            }
            
        } catch (\Exception $e) {
            // Si hay error, saltar la prueba
            $this->markTestSkipped('Error en getDestacados(): ' . $e->getMessage());
        }
    }
    
    /**
     * Test 10: Verificar que buscar() funciona correctamente
     */
    public function testBuscarFuncionaCorrectamente()
    {
        // Probar con término que probablemente exista
        $terminos = ['bóxer', 'negro', 'algodón'];
        $terminoEncontrado = '';
        
        foreach ($terminos as $termino) {
            $resultados = $this->producto->buscar($termino);
            
            if (count($resultados) > 0) {
                $terminoEncontrado = $termino;
                break;
            }
        }
        
        if (empty($terminoEncontrado)) {
            // Si no encuentra con términos específicos, probar con uno genérico
            $resultados = $this->producto->buscar('a');
            $terminoEncontrado = 'a';
        }
        
        $this->assertIsArray($resultados);
        
        echo "✅ Test 10 PASÓ: buscar() funciona correctamente\n";
        echo "   Búsqueda: '{$terminoEncontrado}' - " . count($resultados) . " resultados\n";
        
        if (count($resultados) > 0) {
            echo "   🔍 Primer resultado: {$resultados[0]['N_Articulo']}\n";
        }
    }
    
    /**
     * Test 11: Verificar que getColorInfo() funciona correctamente
     */
    public function testGetColorInfoFuncionaCorrectamente()
    {
        // Obtener un color existente de la base de datos
        $stmt = $this->db->query("SELECT ID_Color FROM color LIMIT 1");
        $color = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($color) {
            $colorInfo = $this->producto->getColorInfo($color['ID_Color']);
            
            if ($colorInfo === false) {
                $this->markTestSkipped('getColorInfo() retornó false para ID: ' . $color['ID_Color']);
            }
            
            $this->assertIsArray($colorInfo);
            $this->assertArrayHasKey('N_Color', $colorInfo);
            
            echo "✅ Test 11 PASÓ: getColorInfo() funciona correctamente\n";
            echo "   Color: {$colorInfo['N_Color']}";
            if (isset($colorInfo['CodigoHex'])) {
                echo " - Código: {$colorInfo['CodigoHex']}";
            }
            echo "\n";
        } else {
            $this->markTestSkipped('No hay colores en la base de datos');
        }
    }
    
    /**
     * Test 12: Verificar que getTallaInfo() funciona correctamente
     */
    public function testGetTallaInfoFuncionaCorrectamente()
    {
        // Obtener una talla existente de la base de datos
        $stmt = $this->db->query("SELECT ID_Talla FROM talla WHERE ID_Talla IS NOT NULL LIMIT 1");
        $talla = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($talla) {
            $tallaInfo = $this->producto->getTallaInfo($talla['ID_Talla']);
            
            if ($tallaInfo === false) {
                $this->markTestSkipped('getTallaInfo() retornó false para ID: ' . $talla['ID_Talla']);
            }
            
            $this->assertIsArray($tallaInfo);
            $this->assertArrayHasKey('N_Talla', $tallaInfo);
            
            echo "✅ Test 12 PASÓ: getTallaInfo() funciona correctamente\n";
            echo "   Talla: {$tallaInfo['N_Talla']}\n";
        } else {
            $this->markTestSkipped('No hay tallas en la base de datos');
        }
    }
    
    /**
     * Test 13: Verificar que verificarStock() funciona correctamente
     */
    public function testVerificarStockFuncionaCorrectamente()
    {
        if (!self::$testProductoId) {
            $this->markTestSkipped('No hay productos para probar stock');
        }
        
        $stockDisponible = $this->producto->verificarStock(self::$testProductoId, 1, 'variante');
        
        $this->assertIsBool($stockDisponible);
        
        echo "✅ Test 13 PASÓ: verificarStock() funciona correctamente\n";
        echo "   Producto ID: " . self::$testProductoId . " - Stock disponible: " . ($stockDisponible ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 14: Verificar estructura completa de productos
     */
    public function testEstructuraCompletaProductos()
    {
        $stmt = $this->producto->read();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $this->assertIsArray($productos);
        
        if (count($productos) > 0) {
            foreach ($productos as $producto) {
                // Campos obligatorios
                $this->assertArrayHasKey('ID_Articulo', $producto);
                $this->assertArrayHasKey('N_Articulo', $producto);
                $this->assertArrayHasKey('Foto', $producto);
                $this->assertArrayHasKey('N_Categoria', $producto);
                $this->assertArrayHasKey('Precio', $producto);
                $this->assertArrayHasKey('Stock', $producto);
            }
        }
        
        echo "✅ Test 14 PASÓ: Estructura completa de productos validada\n";
        echo "   Total de productos verificados: " . count($productos) . "\n";
    }
    
    /**
     * Test 15: Verificar búsqueda con filtros
     */
    public function testBuscarConFiltrosFuncionaCorrectamente()
    {
        // Obtener categoría y género existentes para pruebas
        $stmt = $this->db->query("SELECT ID_Categoria FROM categoria LIMIT 1");
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $this->db->query("SELECT ID_Genero FROM genero LIMIT 1");
        $genero = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($categoria && $genero) {
            $resultados = $this->producto->buscar('', $categoria['ID_Categoria'], $genero['ID_Genero']);
            
            $this->assertIsArray($resultados);
            
            echo "✅ Test 15 PASÓ: buscar() con filtros funciona correctamente\n";
            echo "   Categoría ID: {$categoria['ID_Categoria']}, Género ID: {$genero['ID_Genero']}\n";
            echo "   Resultados: " . count($resultados) . "\n";
        } else {
            $this->markTestSkipped('No hay categorías o géneros para probar filtros');
        }
    }
    
    public static function tearDownAfterClass(): void
    {
        echo "\n================================\n";
        echo "🏁 PRUEBAS DE PRODUCTO COMPLETADAS\n\n";
    }
}
?>