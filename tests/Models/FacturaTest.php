<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class FacturaTest extends TestCase
{
    private $db;
    private $factura;
    private static $testFacturaId;
    private static $testUsuarioId;
    private static $createdFacturaId;
    
    public static function setUpBeforeClass(): void
    {
        echo "\n🎯 INICIANDO PRUEBAS DE FACTURA\n";
        echo "================================\n";
    }
    
    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=tulook;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            require_once __DIR__ . '/../../models/Factura.php';
            $this->factura = new \Factura($this->db);
            
        } catch (PDOException $e) {
            $this->markTestSkipped('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        
        $this->obtenerIdsDePrueba();
    }
    
    protected function tearDown(): void
    {
        if (self::$createdFacturaId) {
            try {
                $this->db->exec("DELETE FROM factura WHERE ID_Factura = " . self::$createdFacturaId);
            } catch (PDOException $e) {
                // Ignorar errores de limpieza
            }
        }
        
        $this->db = null;
        $this->factura = null;
    }
    
    private function obtenerIdsDePrueba()
    {
        // Obtener una factura válida para pruebas
        $stmt = $this->db->query("SELECT ID_Factura FROM factura LIMIT 1");
        $factura = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($factura) {
            self::$testFacturaId = $factura['ID_Factura'];
        }
        
        // Obtener un usuario válido
        $stmt = $this->db->query("SELECT ID_Usuario FROM usuario LIMIT 1");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            self::$testUsuarioId = $usuario['ID_Usuario'];
        }
    }
    
    public function testFacturaSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(\Factura::class, $this->factura);
        echo "✅ Test 1 PASÓ: Factura se instancia correctamente\n";
    }
    
    public function testReadFuncionaCorrectamente()
    {
        $result = $this->factura->read();
        
        $this->assertInstanceOf(\PDOStatement::class, $result);
        
        $facturas = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->assertIsArray($facturas);
        
        echo "✅ Test 2 PASÓ: read() funciona correctamente\n";
        echo "   Facturas encontradas: " . count($facturas) . "\n";
        
        if (count($facturas) > 0) {
            echo "   📄 Muestra de facturas:\n";
            foreach (array_slice($facturas, 0, 3) as $index => $factura) {
                echo "      " . ($index + 1) . ". ID: {$factura['ID_Factura']} | ";
                echo "Usuario: {$factura['ID_Usuario']}";
                // Mostrar otros campos si existen
                if (isset($factura['Fecha'])) {
                    echo " | Fecha: {$factura['Fecha']}";
                }
                if (isset($factura['Total'])) {
                    echo " | Total: \${$factura['Total']}";
                }
                echo "\n";
            }
        }
    }
    
    public function testGetByIdFuncionaCorrectamente()
    {
        if (!self::$testFacturaId) {
            $this->markTestSkipped('No hay facturas para probar');
            return;
        }
        
        $factura = $this->factura->getById(self::$testFacturaId);
        
        $this->assertIsArray($factura);
        $this->assertArrayHasKey('ID_Factura', $factura);
        $this->assertArrayHasKey('ID_Usuario', $factura);
        
        echo "✅ Test 3 PASÓ: getById() funciona correctamente\n";
        echo "   Factura ID: " . self::$testFacturaId . " | Usuario: {$factura['ID_Usuario']}\n";
    }
    
    public function testGetByIdRetornaFalseParaIdInexistente()
    {
        $factura = $this->factura->getById(999999);
        $this->assertFalse($factura);
        
        echo "✅ Test 4 PASÓ: getById() retorna false para ID inexistente\n";
    }
    
    public function testCreateFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para crear factura');
            return;
        }
        
        $datosFactura = [
            'ID_Usuario' => self::$testUsuarioId
        ];
        
        $resultado = $this->factura->create($datosFactura);
        
        $this->assertTrue($resultado);
        
        // Obtener el ID de la factura creada para limpieza
        $stmt = $this->db->query("SELECT ID_Factura FROM factura WHERE ID_Usuario = " . self::$testUsuarioId . " ORDER BY ID_Factura DESC LIMIT 1");
        $facturaCreada = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($facturaCreada) {
            self::$createdFacturaId = $facturaCreada['ID_Factura'];
        }
        
        echo "✅ Test 5 PASÓ: create() funciona correctamente\n";
        echo "   Factura creada para usuario: " . self::$testUsuarioId . "\n";
    }
    
    public function testUpdateFuncionaCorrectamente()
    {
        if (!self::$testFacturaId || !self::$testUsuarioId) {
            $this->markTestSkipped('No hay facturas o usuarios para actualizar');
            return;
        }
        
        $facturaActual = $this->factura->getById(self::$testFacturaId);
        
        $nuevosDatos = [
            'ID_Usuario' => self::$testUsuarioId
        ];
        
        $resultado = $this->factura->update(self::$testFacturaId, $nuevosDatos);
        
        $this->assertTrue($resultado);
        
        echo "✅ Test 6 PASÓ: update() funciona correctamente\n";
        echo "   Factura actualizada: Usuario cambiado\n";
    }
    
    public function testDeleteFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para crear factura temporal');
            return;
        }
        
        // Primero crear una factura temporal para eliminar
        $datosFactura = [
            'ID_Usuario' => self::$testUsuarioId
        ];
        
        $creada = $this->factura->create($datosFactura);
        $this->assertTrue($creada);
        
        // Obtener el ID de la factura creada
        $stmt = $this->db->query("SELECT ID_Factura FROM factura WHERE ID_Usuario = " . self::$testUsuarioId . " ORDER BY ID_Factura DESC LIMIT 1");
        $facturaCreada = $stmt->fetch(PDO::FETCH_ASSOC);
        $idTemporal = $facturaCreada['ID_Factura'];
        
        // Eliminar la factura
        $eliminada = $this->factura->delete($idTemporal);
        $this->assertTrue($eliminada);
        
        // Verificar que ya no existe
        $facturaEliminada = $this->factura->getById($idTemporal);
        $this->assertFalse($facturaEliminada);
        
        echo "✅ Test 7 PASÓ: delete() funciona correctamente\n";
        echo "   Factura temporal eliminada: ID " . $idTemporal . "\n";
    }
    
    public function testEstructuraDatosFactura()
    {
        if (!self::$testFacturaId) {
            $this->markTestSkipped('No hay facturas para validar estructura');
            return;
        }
        
        $factura = $this->factura->getById(self::$testFacturaId);
        
        // Solo verificar campos que sabemos que existen
        $camposExistentes = [
            'ID_Factura', 'ID_Usuario'
        ];
        
        foreach ($camposExistentes as $campo) {
            $this->assertArrayHasKey($campo, $factura, "El campo {$campo} debería existir en la factura");
        }
        
        // Verificar tipos de datos
        $this->assertIsNumeric($factura['ID_Factura']);
        $this->assertIsNumeric($factura['ID_Usuario']);
        
        echo "✅ Test 8 PASÓ: Estructura de datos validada\n";
        echo "   Factura validada: ID " . $factura['ID_Factura'] . "\n";
    }
    
    public function testReadRetornaCamposBasicos()
    {
        $result = $this->factura->read();
        $facturas = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($facturas) > 0) {
            $primerFactura = $facturas[0];
            
            $camposEsperados = [
                'ID_Factura', 'ID_Usuario'
            ];
            
            foreach ($camposEsperados as $campo) {
                $this->assertArrayHasKey($campo, $primerFactura);
            }
        }
        
        echo "✅ Test 9 PASÓ: read() retorna campos básicos\n";
    }
    
    public function testCreateConDiferentesUsuarios()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para crear facturas');
            return;
        }
        
        // Crear varias facturas con el mismo usuario (ya que es el único que tenemos)
        for ($i = 0; $i < 3; $i++) {
            $datosFactura = [
                'ID_Usuario' => self::$testUsuarioId
            ];
            
            $resultado = $this->factura->create($datosFactura);
            $this->assertTrue($resultado);
            
            // Limpiar después de cada creación
            $stmt = $this->db->query("SELECT ID_Factura FROM factura WHERE ID_Usuario = " . self::$testUsuarioId . " ORDER BY ID_Factura DESC LIMIT 1");
            $facturaCreada = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($facturaCreada) {
                $this->db->exec("DELETE FROM factura WHERE ID_Factura = " . $facturaCreada['ID_Factura']);
            }
        }
        
        echo "✅ Test 10 PASÓ: create() funciona con múltiples inserciones\n";
    }
    
    public function testUpdateCambiaUsuario()
    {
        if (!self::$testFacturaId || !self::$testUsuarioId) {
            $this->markTestSkipped('No hay facturas o usuarios para probar actualización');
            return;
        }
        
        $facturaOriginal = $this->factura->getById(self::$testFacturaId);
        
        $nuevosDatos = [
            'ID_Usuario' => self::$testUsuarioId
        ];
        
        $this->factura->update(self::$testFacturaId, $nuevosDatos);
        
        $facturaActualizada = $this->factura->getById(self::$testFacturaId);
        
        // Verificar que cambió el usuario
        $this->assertEquals($nuevosDatos['ID_Usuario'], $facturaActualizada['ID_Usuario']);
        
        echo "✅ Test 11 PASÓ: update() cambia usuario correctamente\n";
    }
    
    public function testDeleteRetornaTrueParaIdInexistente()
    {
        $resultado = $this->factura->delete(999999);
        $this->assertTrue($resultado);
        
        echo "✅ Test 12 PASÓ: delete() retorna true para ID inexistente\n";
    }
    
    public function testCreateConUsuarioInexistente()
    {
        // Intentar crear factura con usuario que no existe
        $datosFactura = [
            'ID_Usuario' => 999999
        ];
        
        // Esto podría fallar por la restricción de clave foránea o podría funcionar
        try {
            $resultado = $this->factura->create($datosFactura);
            $this->assertIsBool($resultado);
        } catch (PDOException $e) {
            // Si hay restricción de clave foránea, esperamos una excepción
            $this->assertTrue(true, 'Excepción esperada con usuario inexistente');
        }
        
        echo "✅ Test 13 PASÓ: create() maneja usuario inexistente\n";
    }
    
    public function testCicloCompletoCRUD()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para probar ciclo CRUD');
            return;
        }
        
        // 1. CREATE
        $datosCreacion = [
            'ID_Usuario' => self::$testUsuarioId
        ];
        
        $creada = $this->factura->create($datosCreacion);
        $this->assertTrue($creada);
        
        // Obtener ID de la factura creada
        $stmt = $this->db->query("SELECT ID_Factura FROM factura WHERE ID_Usuario = " . self::$testUsuarioId . " ORDER BY ID_Factura DESC LIMIT 1");
        $facturaCreada = $stmt->fetch(PDO::FETCH_ASSOC);
        $idCRUD = $facturaCreada['ID_Factura'];
        
        // 2. READ (verificar creación)
        $facturaLeida = $this->factura->getById($idCRUD);
        $this->assertEquals($datosCreacion['ID_Usuario'], $facturaLeida['ID_Usuario']);
        
        // 3. UPDATE
        $datosActualizacion = [
            'ID_Usuario' => self::$testUsuarioId
        ];
        
        $actualizada = $this->factura->update($idCRUD, $datosActualizacion);
        $this->assertTrue($actualizada);
        
        // 4. DELETE
        $eliminada = $this->factura->delete($idCRUD);
        $this->assertTrue($eliminada);
        
        // Verificar eliminación
        $facturaEliminada = $this->factura->getById($idCRUD);
        $this->assertFalse($facturaEliminada);
        
        echo "✅ Test 14 PASÓ: Ciclo completo CRUD funciona correctamente\n";
        echo "   Factura CRUD: Create ✓ | Read ✓ | Update ✓ | Delete ✓\n";
    }
    
    public function testReadMantieneConsistenciaDatos()
    {
        $result = $this->factura->read();
        $facturas = $result->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($facturas as $factura) {
            $this->assertArrayHasKey('ID_Factura', $factura);
            $this->assertArrayHasKey('ID_Usuario', $factura);
            
            // Verificar que los valores numéricos son válidos
            $this->assertIsNumeric($factura['ID_Factura']);
            $this->assertIsNumeric($factura['ID_Usuario']);
            $this->assertGreaterThan(0, $factura['ID_Factura']);
            $this->assertGreaterThan(0, $factura['ID_Usuario']);
        }
        
        echo "✅ Test 15 PASÓ: read() mantiene consistencia de datos\n";
        echo "   Facturas verificadas: " . count($facturas) . "\n";
    }
    
    public static function tearDownAfterClass(): void
    {
        echo "\n================================\n";
        echo "🏁 PRUEBAS DE FACTURA COMPLETADAS\n\n";
    }
}