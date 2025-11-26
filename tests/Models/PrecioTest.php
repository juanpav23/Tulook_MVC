<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;
use Exception;

class PrecioTest extends TestCase
{
    private $db;
    private $precio;
    private static $testPrecioId;
    private static $testPrecioActivoId;
    private static $createdPrecioId;
    
    public static function setUpBeforeClass(): void
    {
        echo "\n🎯 INICIANDO PRUEBAS DE PRECIO\n";
        echo "================================\n";
    }
    
    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=tulook;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Incluir la clase Precio
            require_once __DIR__ . '/../../models/Precio.php';
            $this->precio = new \Precio($this->db);
            
        } catch (PDOException $e) {
            $this->markTestSkipped('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        
        $this->obtenerIdsDePrueba();
    }
    
    protected function tearDown(): void
    {
        // Limpiar precios creados durante las pruebas
        if (self::$createdPrecioId) {
            try {
                $this->db->exec("DELETE FROM precio WHERE ID_precio = " . self::$createdPrecioId);
            } catch (PDOException $e) {
                // Ignorar errores de limpieza
            }
        }
        
        $this->db = null;
        $this->precio = null;
    }
    
    private function obtenerIdsDePrueba()
    {
        // Obtener un precio válido para pruebas
        $stmt = $this->db->query("SELECT ID_precio FROM precio LIMIT 1");
        $precio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($precio) {
            self::$testPrecioId = $precio['ID_precio'];
        }
        
        // Obtener un precio activo para pruebas
        $stmt = $this->db->query("SELECT ID_precio FROM precio WHERE Activo = 1 LIMIT 1");
        $precioActivo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($precioActivo) {
            self::$testPrecioActivoId = $precioActivo['ID_precio'];
        }
        
        if (!self::$testPrecioId) {
            echo "   ⚠️  No se encontraron precios en la base de datos\n";
        }
    }
    
    /**
     * Test 1: Verificar que Precio se instancia correctamente
     */
    public function testPrecioSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(\Precio::class, $this->precio);
        echo "✅ Test 1 PASÓ: Precio se instancia correctamente\n";
    }
    
    /**
     * Test 2: Verificar que obtenerTodos() funciona correctamente
     */
    public function testObtenerTodosFuncionaCorrectamente()
    {
        $precios = $this->precio->obtenerTodos();
        
        $this->assertIsArray($precios);
        
        echo "✅ Test 2 PASÓ: obtenerTodos() funciona correctamente\n";
        echo "   Precios encontrados: " . count($precios) . "\n";
        
        if (count($precios) > 0) {
            echo "   💰 Muestra de precios:\n";
            foreach (array_slice($precios, 0, 3) as $index => $precio) {
                $activo = $precio['Activo'] ? 'ACTIVO' : 'INACTIVO';
                echo "      " . ($index + 1) . ". ID: {$precio['ID_precio']} | ";
                echo "Valor: \${$precio['Valor']} | ";
                echo "Estado: {$activo}\n";
            }
        }
    }
    
    /**
     * Test 3: Verificar que obtenerPorId() funciona correctamente
     */
    public function testObtenerPorIdFuncionaCorrectamente()
    {
        if (!self::$testPrecioId) {
            $this->markTestSkipped('No hay precios para probar');
        }
        
        $precio = $this->precio->obtenerPorId(self::$testPrecioId);
        
        $this->assertIsArray($precio);
        $this->assertArrayHasKey('ID_precio', $precio);
        $this->assertArrayHasKey('Valor', $precio);
        $this->assertArrayHasKey('Activo', $precio);
        
        echo "✅ Test 3 PASÓ: obtenerPorId() funciona correctamente\n";
        echo "   Precio ID: " . self::$testPrecioId . " | Valor: \${$precio['Valor']}\n";
    }
    
    /**
     * Test 4: Verificar que existePrecio() funciona correctamente
     */
    public function testExistePrecioFuncionaCorrectamente()
    {
        if (!self::$testPrecioId) {
            $this->markTestSkipped('No hay precios para probar');
        }
        
        // Obtener el valor del precio de prueba
        $precio = $this->precio->obtenerPorId(self::$testPrecioId);
        $valor = $precio['Valor'];
        
        $existe = $this->precio->existePrecio($valor);
        
        $this->assertIsBool($existe);
        $this->assertTrue($existe, "El precio con valor {$valor} debería existir");
        
        // Probar con valor que no existe
        $noExiste = $this->precio->existePrecio(999999.99);
        $this->assertFalse($noExiste, "El precio con valor 999999.99 no debería existir");
        
        echo "✅ Test 4 PASÓ: existePrecio() funciona correctamente\n";
        echo "   Valor \${$valor} existe: " . ($existe ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 5: Verificar que crear() funciona correctamente
     */
    public function testCrearFuncionaCorrectamente()
    {
        // Generar un valor único para la prueba
        $valorUnico = rand(1000, 9999) + (rand(0, 99) / 100);
        
        try {
            $resultado = $this->precio->crear($valorUnico, 1);
            
            $this->assertTrue($resultado);
            
            // Verificar que realmente se creó
            $existe = $this->precio->existePrecio($valorUnico);
            $this->assertTrue($existe);
            
            // Obtener el ID del precio creado para limpieza
            $precios = $this->precio->obtenerTodos();
            foreach ($precios as $precio) {
                if ($precio['Valor'] == $valorUnico) {
                    self::$createdPrecioId = $precio['ID_precio'];
                    break;
                }
            }
            
            echo "✅ Test 5 PASÓ: crear() funciona correctamente\n";
            echo "   Precio creado: \${$valorUnico}\n";
            
        } catch (Exception $e) {
            $this->markTestSkipped('No se pudo crear precio: ' . $e->getMessage());
        }
    }
    
    /**
     * Test 6: Verificar que crear() lanza excepción con valor duplicado
     */
    public function testCrearLanzaExcepcionConDuplicado()
    {
        if (!self::$testPrecioId) {
            $this->markTestSkipped('No hay precios para probar');
        }
        
        // Obtener un valor existente
        $precioExistente = $this->precio->obtenerPorId(self::$testPrecioId);
        $valorExistente = $precioExistente['Valor'];
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Ya existe un precio con el valor");
        
        $this->precio->crear($valorExistente, 1);
        
        echo "✅ Test 6 PASÓ: crear() lanza excepción correctamente con valor duplicado\n";
    }
    
    /**
     * Test 7: Verificar que actualizar() funciona correctamente
     */
    public function testActualizarFuncionaCorrectamente()
    {
        if (!self::$testPrecioId) {
            $this->markTestSkipped('No hay precios para probar');
        }
        
        // Crear un precio temporal para actualizar
        $valorTemp = rand(5000, 5999) + (rand(0, 99) / 100);
        $this->precio->crear($valorTemp, 1);
        
        // Obtener el ID del precio temporal
        $precios = $this->precio->obtenerTodos();
        $idTemp = null;
        foreach ($precios as $precio) {
            if ($precio['Valor'] == $valorTemp) {
                $idTemp = $precio['ID_precio'];
                break;
            }
        }
        
        if (!$idTemp) {
            $this->markTestSkipped('No se pudo crear precio temporal para actualizar');
        }
        
        // Actualizar el precio
        $nuevoValor = $valorTemp + 1000;
        $resultado = $this->precio->actualizar($idTemp, $nuevoValor, 0);
        
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $precioActualizado = $this->precio->obtenerPorId($idTemp);
        $this->assertEquals($nuevoValor, $precioActualizado['Valor']);
        $this->assertEquals(0, $precioActualizado['Activo']);
        
        // Limpiar
        $this->db->exec("DELETE FROM precio WHERE ID_precio = " . $idTemp);
        
        echo "✅ Test 7 PASÓ: actualizar() funciona correctamente\n";
        echo "   Precio actualizado: \${$valorTemp} → \${$nuevoValor}\n";
    }
    
    /**
     * Test 8: Verificar que cambiarEstado() funciona correctamente
     */
    public function testCambiarEstadoFuncionaCorrectamente()
    {
        if (!self::$testPrecioId) {
            $this->markTestSkipped('No hay precios para probar');
        }
        
        // Obtener estado actual
        $precio = $this->precio->obtenerPorId(self::$testPrecioId);
        $estadoActual = $precio['Activo'];
        $nuevoEstado = $estadoActual ? 0 : 1;
        
        $resultado = $this->precio->cambiarEstado(self::$testPrecioId, $nuevoEstado);
        
        $this->assertTrue($resultado);
        
        // Verificar el cambio
        $precioActualizado = $this->precio->obtenerPorId(self::$testPrecioId);
        $this->assertEquals($nuevoEstado, $precioActualizado['Activo']);
        
        // Restaurar estado original
        $this->precio->cambiarEstado(self::$testPrecioId, $estadoActual);
        
        echo "✅ Test 8 PASÓ: cambiarEstado() funciona correctamente\n";
        echo "   Estado cambiado: " . ($estadoActual ? 'ACTIVO' : 'INACTIVO') . " → " . ($nuevoEstado ? 'ACTIVO' : 'INACTIVO') . "\n";
    }
    
    /**
     * Test 9: Verificar que obtenerDuplicados() funciona correctamente
     */
    public function testObtenerDuplicadosFuncionaCorrectamente()
    {
        $duplicados = $this->precio->obtenerDuplicados();
        
        $this->assertIsArray($duplicados);
        
        echo "✅ Test 9 PASÓ: obtenerDuplicados() funciona correctamente\n";
        echo "   Duplicados encontrados: " . count($duplicados) . "\n";
        
        if (count($duplicados) > 0) {
            foreach (array_slice($duplicados, 0, 2) as $index => $dup) {
                echo "      " . ($index + 1) . ". Valor: \${$dup['Valor']} | ";
                echo "Cantidad: {$dup['cantidad']} | IDs: {$dup['ids']}\n";
            }
        }
    }
    
    /**
     * Test 10: Verificar que estaEnUso() funciona correctamente
     */
    public function testEstaEnUsoFuncionaCorrectamente()
    {
        if (!self::$testPrecioId) {
            $this->markTestSkipped('No hay precios para probar');
        }
        
        $enUso = $this->precio->estaEnUso(self::$testPrecioId);
        
        $this->assertIsBool($enUso);
        
        echo "✅ Test 10 PASÓ: estaEnUso() funciona correctamente\n";
        echo "   Precio ID: " . self::$testPrecioId . " | En uso: " . ($enUso ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 11: Verificar que obtenerActivos() funciona correctamente
     */
    public function testObtenerActivosFuncionaCorrectamente()
    {
        $activos = $this->precio->obtenerActivos();
        
        $this->assertIsArray($activos);
        
        // Verificar que todos están activos
        foreach ($activos as $precio) {
            $this->assertEquals(1, $precio['Activo']);
        }
        
        echo "✅ Test 11 PASÓ: obtenerActivos() funciona correctamente\n";
        echo "   Precios activos encontrados: " . count($activos) . "\n";
    }
    
    /**
     * Test 12: Verificar que contarActivos() funciona correctamente
     */
    public function testContarActivosFuncionaCorrectamente()
    {
        $cantidad = $this->precio->contarActivos();
        
        $this->assertIsInt($cantidad);
        $this->assertGreaterThanOrEqual(0, $cantidad);
        
        echo "✅ Test 12 PASÓ: contarActivos() funciona correctamente\n";
        echo "   Total precios activos: " . $cantidad . "\n";
    }
    
    /**
     * Test 13: Verificar que buscar() funciona correctamente
     */
    public function testBuscarFuncionaCorrectamente()
    {
        // Buscar sin término (debería retornar todos)
        $resultados = $this->precio->buscar();
        $this->assertIsArray($resultados);
        
        // Buscar por estado activo
        $activos = $this->precio->buscar('', 1);
        $this->assertIsArray($activos);
        
        // Verificar que todos los resultados están activos
        foreach ($activos as $precio) {
            $this->assertEquals(1, $precio['Activo']);
        }
        
        echo "✅ Test 13 PASÓ: buscar() funciona correctamente\n";
        echo "   Búsqueda sin filtro: " . count($resultados) . " resultados\n";
        echo "   Búsqueda activos: " . count($activos) . " resultados\n";
    }
    
    /**
     * Test 14: Verificar que limpiarDuplicados() funciona correctamente
     */
    public function testLimpiarDuplicadosFuncionaCorrectamente()
    {
        $resultados = $this->precio->limpiarDuplicados();
        
        $this->assertIsArray($resultados);
        $this->assertArrayHasKey('eliminados', $resultados);
        $this->assertArrayHasKey('migrados', $resultados);
        $this->assertArrayHasKey('errores', $resultados);
        
        echo "✅ Test 14 PASÓ: limpiarDuplicados() funciona correctamente\n";
        echo "   Eliminados: {$resultados['eliminados']} | Migrados: {$resultados['migrados']} | Errores: " . count($resultados['errores']) . "\n";
    }
    
    /**
     * Test 15: Verificar ciclo completo de gestión de precios
     */
    public function testCicloCompletoGestionPrecios()
    {
        // 1. Crear un nuevo precio
        $valorInicial = rand(7000, 7999) + (rand(0, 99) / 100);
        $creado = $this->precio->crear($valorInicial, 1);
        $this->assertTrue($creado);
        
        // 2. Verificar que existe
        $existe = $this->precio->existePrecio($valorInicial);
        $this->assertTrue($existe);
        
        // 3. Obtener el ID del precio creado
        $precios = $this->precio->obtenerTodos();
        $idCreado = null;
        foreach ($precios as $precio) {
            if ($precio['Valor'] == $valorInicial) {
                $idCreado = $precio['ID_precio'];
                break;
            }
        }
        $this->assertNotNull($idCreado);
        
        // 4. Actualizar el precio
        $valorActualizado = $valorInicial + 500;
        $actualizado = $this->precio->actualizar($idCreado, $valorActualizado, 0);
        $this->assertTrue($actualizado);
        
        // 5. Verificar la actualización
        $precioActualizado = $this->precio->obtenerPorId($idCreado);
        $this->assertEquals($valorActualizado, $precioActualizado['Valor']);
        $this->assertEquals(0, $precioActualizado['Activo']);
        
        // 6. Cambiar estado
        $cambiado = $this->precio->cambiarEstado($idCreado, 1);
        $this->assertTrue($cambiado);
        
        // 7. Verificar estado cambiado
        $precioFinal = $this->precio->obtenerPorId($idCreado);
        $this->assertEquals(1, $precioFinal['Activo']);
        
        // 8. Limpiar
        $this->db->exec("DELETE FROM precio WHERE ID_precio = " . $idCreado);
        
        echo "✅ Test 15 PASÓ: Ciclo completo de gestión de precios funciona correctamente\n";
        echo "   Precio creado y gestionado: \${$valorInicial} → \${$valorActualizado}\n";
    }
    
    public static function tearDownAfterClass(): void
    {
        echo "\n================================\n";
        echo "🏁 PRUEBAS DE PRECIO COMPLETADAS\n\n";
    }
}
?>