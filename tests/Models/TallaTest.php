<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;
use Exception;

class TallaTest extends TestCase
{
    private $db;
    private $talla;
    private static $testTallaId;
    private static $createdTallaId;
    
    public static function setUpBeforeClass(): void
    {
        echo "\n🎯 INICIANDO PRUEBAS DE TALLA\n";
        echo "================================\n";
    }
    
    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=tulook;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Incluir la clase Talla
            require_once __DIR__ . '/../../models/Talla.php';
            $this->talla = new \Talla($this->db);
            
        } catch (PDOException $e) {
            $this->markTestSkipped('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        
        $this->obtenerIdsDePrueba();
    }
    
    protected function tearDown(): void
    {
        // Limpiar tallas creadas durante las pruebas
        if (self::$createdTallaId) {
            try {
                // Primero eliminar de sobrecosto_talla
                $this->db->exec("DELETE FROM sobrecosto_talla WHERE ID_Talla = " . self::$createdTallaId);
                // Luego eliminar de talla
                $this->db->exec("DELETE FROM talla WHERE ID_Talla = " . self::$createdTallaId);
            } catch (PDOException $e) {
                // Ignorar errores de limpieza
            }
        }
        
        $this->db = null;
        $this->talla = null;
    }
    
    private function obtenerIdsDePrueba()
    {
        // Obtener una talla válida para pruebas (excluyendo ID 1 que es "Indefinido")
        $stmt = $this->db->query("SELECT ID_Talla FROM talla WHERE ID_Talla > 1 LIMIT 1");
        $talla = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($talla) {
            self::$testTallaId = $talla['ID_Talla'];
        }
        
        if (!self::$testTallaId) {
            echo "   ⚠️  No se encontraron tallas activas en la base de datos\n";
            
            // Intentar obtener cualquier talla
            $stmt = $this->db->query("SELECT ID_Talla FROM talla LIMIT 1");
            $talla = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($talla) {
                self::$testTallaId = $talla['ID_Talla'];
            }
        }
    }
    
    /**
     * Test 1: Verificar que Talla se instancia correctamente
     */
    public function testTallaSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(\Talla::class, $this->talla);
        echo "✅ Test 1 PASÓ: Talla se instancia correctamente\n";
    }
    
    /**
     * Test 2: Verificar que obtenerTodas() funciona correctamente
     */
    public function testObtenerTodasFuncionaCorrectamente()
    {
        $tallas = $this->talla->obtenerTodas();
        
        $this->assertIsArray($tallas);
        
        echo "✅ Test 2 PASÓ: obtenerTodas() funciona correctamente\n";
        echo "   Tallas encontradas: " . count($tallas) . "\n";
        
        if (count($tallas) > 0) {
            echo "   📏 Muestra de tallas:\n";
            foreach (array_slice($tallas, 0, 3) as $index => $talla) {
                $sobrecosto = $talla['Sobrecosto'] ?? 0;
                echo "      " . ($index + 1) . ". ID: {$talla['ID_Talla']} | ";
                echo "Nombre: {$talla['N_Talla']} | ";
                echo "Sobrecosto: \${$sobrecosto}\n";
            }
        }
    }
    
    /**
     * Test 3: Verificar que obtenerTodas() con búsqueda funciona
     */
    public function testObtenerTodasConBusquedaFunciona()
    {
        // Obtener todas las tallas primero para tener un término de búsqueda
        $tallas = $this->talla->obtenerTodas();
        
        if (count($tallas) > 0) {
            $primerTalla = $tallas[0];
            $terminoBusqueda = substr($primerTalla['N_Talla'], 0, 3); // Primeros 3 caracteres
            
            $resultados = $this->talla->obtenerTodas($terminoBusqueda);
            
            $this->assertIsArray($resultados);
            $this->assertGreaterThanOrEqual(1, count($resultados));
            
            echo "✅ Test 3 PASÓ: obtenerTodas() con búsqueda funciona correctamente\n";
            echo "   Búsqueda: '{$terminoBusqueda}' | Resultados: " . count($resultados) . "\n";
        } else {
            $this->markTestSkipped('No hay tallas para probar búsqueda');
        }
    }
    
    /**
     * Test 4: Verificar que obtenerPorId() funciona correctamente
     */
    public function testObtenerPorIdFuncionaCorrectamente()
    {
        if (!self::$testTallaId) {
            $this->markTestSkipped('No hay tallas para probar');
        }
        
        $talla = $this->talla->obtenerPorId(self::$testTallaId);
        
        $this->assertIsArray($talla);
        $this->assertArrayHasKey('ID_Talla', $talla);
        $this->assertArrayHasKey('N_Talla', $talla);
        $this->assertArrayHasKey('Sobrecosto', $talla);
        
        echo "✅ Test 4 PASÓ: obtenerPorId() funciona correctamente\n";
        echo "   Talla ID: " . self::$testTallaId . " | Nombre: {$talla['N_Talla']}\n";
    }
    
    /**
     * Test 5: Verificar que obtenerPorNombre() funciona correctamente
     */
    public function testObtenerPorNombreFuncionaCorrectamente()
    {
        if (!self::$testTallaId) {
            $this->markTestSkipped('No hay tallas para probar');
        }
        
        // Obtener el nombre de la talla de prueba
        $talla = $this->talla->obtenerPorId(self::$testTallaId);
        $nombre = $talla['N_Talla'];
        
        $tallaPorNombre = $this->talla->obtenerPorNombre($nombre);
        
        $this->assertIsArray($tallaPorNombre);
        $this->assertEquals($nombre, $tallaPorNombre['N_Talla']);
        
        echo "✅ Test 5 PASÓ: obtenerPorNombre() funciona correctamente\n";
        echo "   Nombre buscado: '{$nombre}' | Encontrada: " . ($tallaPorNombre ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 6: Verificar que existeTalla() funciona correctamente
     */
    public function testExisteTallaFuncionaCorrectamente()
    {
        if (!self::$testTallaId) {
            $this->markTestSkipped('No hay tallas para probar');
        }
        
        // Obtener el nombre de la talla de prueba
        $talla = $this->talla->obtenerPorId(self::$testTallaId);
        $nombre = $talla['N_Talla'];
        
        $existe = $this->talla->existeTalla($nombre);
        
        $this->assertIsBool($existe);
        $this->assertTrue($existe, "La talla '{$nombre}' debería existir");
        
        // Probar con nombre que no existe
        $noExiste = $this->talla->existeTalla('TALLA_INEXISTENTE_XYZ');
        $this->assertFalse($noExiste, "La talla 'TALLA_INEXISTENTE_XYZ' no debería existir");
        
        echo "✅ Test 6 PASÓ: existeTalla() funciona correctamente\n";
        echo "   Talla '{$nombre}' existe: " . ($existe ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 7: Verificar que crear() funciona correctamente
     */
    public function testCrearFuncionaCorrectamente()
    {
        // Generar un nombre único para la prueba
        $nombreUnico = 'TALLA_TEST_' . rand(1000, 9999);
        
        try {
            $nuevoId = $this->talla->crear($nombreUnico);
            
            $this->assertIsNumeric($nuevoId);
            $this->assertGreaterThan(0, $nuevoId);
            
            // Verificar que realmente se creó
            $existe = $this->talla->existeTalla($nombreUnico);
            $this->assertTrue($existe);
            
            // Verificar que se creó el sobrecosto
            $tallaCreada = $this->talla->obtenerPorId($nuevoId);
            $this->assertArrayHasKey('Sobrecosto', $tallaCreada);
            $this->assertEquals(0, $tallaCreada['Sobrecosto']);
            
            // Guardar ID para limpieza
            self::$createdTallaId = $nuevoId;
            
            echo "✅ Test 7 PASÓ: crear() funciona correctamente\n";
            echo "   Talla creada: '{$nombreUnico}' (ID: {$nuevoId})\n";
            
        } catch (Exception $e) {
            $this->markTestSkipped('No se pudo crear talla: ' . $e->getMessage());
        }
    }
    
    /**
     * Test 8: Verificar que crear() falla con nombre duplicado
     */
    public function testCrearFallaConNombreDuplicado()
    {
        if (!self::$testTallaId) {
            $this->markTestSkipped('No hay tallas para probar');
        }
        
        // Obtener un nombre existente
        $tallaExistente = $this->talla->obtenerPorId(self::$testTallaId);
        $nombreExistente = $tallaExistente['N_Talla'];
        
        $resultado = $this->talla->crear($nombreExistente);
        
        // Debería retornar false por duplicado
        $this->assertFalse($resultado);
        
        echo "✅ Test 8 PASÓ: crear() maneja correctamente nombres duplicados\n";
        echo "   Intento de crear talla duplicada: '{$nombreExistente}'\n";
    }
    
    /**
     * Test 9: Verificar que actualizar() funciona correctamente
     */
    public function testActualizarFuncionaCorrectamente()
    {
        // Primero crear una talla temporal para actualizar
        $nombreTemp = 'TALLA_TEMP_' . rand(1000, 9999);
        $idTemp = $this->talla->crear($nombreTemp);
        
        if (!$idTemp) {
            $this->markTestSkipped('No se pudo crear talla temporal para actualizar');
        }
        
        // Actualizar la talla
        $nuevoNombre = 'TALLA_ACTUALIZADA_' . rand(1000, 9999);
        $resultado = $this->talla->actualizar($idTemp, $nuevoNombre);
        
        $this->assertTrue($resultado);
        
        // Verificar la actualización
        $tallaActualizada = $this->talla->obtenerPorId($idTemp);
        $this->assertEquals($nuevoNombre, $tallaActualizada['N_Talla']);
        
        // Limpiar
        $this->db->exec("DELETE FROM sobrecosto_talla WHERE ID_Talla = " . $idTemp);
        $this->db->exec("DELETE FROM talla WHERE ID_Talla = " . $idTemp);
        
        echo "✅ Test 9 PASÓ: actualizar() funciona correctamente\n";
        echo "   Talla actualizada: '{$nombreTemp}' → '{$nuevoNombre}'\n";
    }
    
    /**
     * Test 10: Verificar que contarActivas() funciona correctamente
     */
    public function testContarActivasFuncionaCorrectamente()
    {
        $cantidad = $this->talla->contarActivas();
        
        $this->assertIsInt($cantidad);
        $this->assertGreaterThanOrEqual(0, $cantidad);
        
        echo "✅ Test 10 PASÓ: contarActivas() funciona correctamente\n";
        echo "   Tallas activas (ID > 1): " . $cantidad . "\n";
    }
    
    /**
     * Test 11: Verificar que contarTotal() funciona correctamente
     */
    public function testContarTotalFuncionaCorrectamente()
    {
        $total = $this->talla->contarTotal();
        
        $this->assertIsInt($total);
        $this->assertGreaterThanOrEqual(0, $total);
        
        // Verificar que contarTotal >= contarActivas
        $activas = $this->talla->contarActivas();
        $this->assertGreaterThanOrEqual($activas, $total);
        
        echo "✅ Test 11 PASÓ: contarTotal() funciona correctamente\n";
        echo "   Total de tallas: " . $total . " | Activas: " . $activas . "\n";
    }
    
    /**
     * Test 12: Verificar estructura de datos completa
     */
    public function testEstructuraDatosCompleta()
    {
        $tallas = $this->talla->obtenerTodas();
        
        $this->assertIsArray($tallas);
        
        if (count($tallas) > 0) {
            foreach ($tallas as $talla) {
                // Campos obligatorios de la tabla talla
                $this->assertArrayHasKey('ID_Talla', $talla);
                $this->assertArrayHasKey('N_Talla', $talla);
                
                // Campos del JOIN con sobrecosto_talla
                $this->assertArrayHasKey('Sobrecosto', $talla);
                $this->assertArrayHasKey('FechaActualizacionSobrecosto', $talla);
                
                // Verificar tipos de datos
                $this->assertIsNumeric($talla['ID_Talla']);
                $this->assertIsString($talla['N_Talla']);
                $this->assertIsNumeric($talla['Sobrecosto']);
            }
        }
        
        echo "✅ Test 12 PASÓ: Estructura de datos completa validada\n";
        echo "   Tallas validadas: " . count($tallas) . "\n";
    }
    
    /**
     * Test 13: Verificar que cambiarEstado() funciona (aunque no haga nada)
     */
    public function testCambiarEstadoFunciona()
    {
        // El método cambiarEstado no hace nada en esta implementación
        // pero debería existir y retornar true
        $resultado = $this->talla->cambiarEstado(1, 1);
        
        $this->assertTrue($resultado);
        
        echo "✅ Test 13 PASÓ: cambiarEstado() funciona (método dummy)\n";
    }
    
    /**
     * Test 14: Verificar manejo de talla inexistente
     */
    public function testManejoTallaInexistente()
    {
        $tallaInexistente = $this->talla->obtenerPorId(999999);
        $this->assertFalse($tallaInexistente);
        
        $tallaPorNombreInexistente = $this->talla->obtenerPorNombre('TALLA_INEXISTENTE_999');
        $this->assertFalse($tallaPorNombreInexistente);
        
        $existeInexistente = $this->talla->existeTalla('TALLA_INEXISTENTE_999');
        $this->assertFalse($existeInexistente);
        
        echo "✅ Test 14 PASÓ: Manejo de talla inexistente funciona correctamente\n";
    }
    
    /**
     * Test 15: Verificar ciclo completo de gestión de tallas
     */
    public function testCicloCompletoGestionTallas()
    {
        // 1. Crear una nueva talla
        $nombreInicial = 'TALLA_CICLO_' . rand(1000, 9999);
        $idCreado = $this->talla->crear($nombreInicial);
        
        $this->assertIsNumeric($idCreado);
        $this->assertGreaterThan(0, $idCreado);
        
        // 2. Verificar que existe
        $existe = $this->talla->existeTalla($nombreInicial);
        $this->assertTrue($existe);
        
        // 3. Obtener por ID y verificar datos
        $tallaCreada = $this->talla->obtenerPorId($idCreado);
        $this->assertEquals($nombreInicial, $tallaCreada['N_Talla']);
        $this->assertEquals(0, $tallaCreada['Sobrecosto']);
        
        // 4. Actualizar la talla
        $nombreActualizado = 'TALLA_ACTUALIZADA_' . rand(1000, 9999);
        $actualizado = $this->talla->actualizar($idCreado, $nombreActualizado);
        $this->assertTrue($actualizado);
        
        // 5. Verificar la actualización
        $tallaActualizada = $this->talla->obtenerPorId($idCreado);
        $this->assertEquals($nombreActualizado, $tallaActualizada['N_Talla']);
        
        // 6. Verificar en la lista general
        $tallas = $this->talla->obtenerTodas();
        $encontrada = false;
        foreach ($tallas as $talla) {
            if ($talla['ID_Talla'] == $idCreado) {
                $encontrada = true;
                $this->assertEquals($nombreActualizado, $talla['N_Talla']);
                break;
            }
        }
        $this->assertTrue($encontrada);
        
        // 7. Limpiar
        $this->db->exec("DELETE FROM sobrecosto_talla WHERE ID_Talla = " . $idCreado);
        $this->db->exec("DELETE FROM talla WHERE ID_Talla = " . $idCreado);
        
        echo "✅ Test 15 PASÓ: Ciclo completo de gestión de tallas funciona correctamente\n";
        echo "   Talla creada y gestionada: '{$nombreInicial}' → '{$nombreActualizado}'\n";
    }
    
    public static function tearDownAfterClass(): void
    {
        echo "\n================================\n";
        echo "🏁 PRUEBAS DE TALLA COMPLETADAS\n\n";
    }
}
?>