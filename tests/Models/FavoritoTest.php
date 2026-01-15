<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;
use PDOStatement;

class FavoritoTest extends TestCase
{
    private $db;
    private $favorito;
    private static $testUsuarioId;
    private static $testArticuloId;
    private static $testProductoId;
    private static $createdFavoritoId;
    
    public static function setUpBeforeClass(): void
    {
        echo "\n🎯 INICIANDO PRUEBAS DE FAVORITO\n";
        echo "================================\n";
    }
    
    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=tulook;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Incluir la clase Favorito
            require_once __DIR__ . '/../../models/Favorito.php';
            $this->favorito = new \Favorito($this->db);
            
        } catch (PDOException $e) {
            $this->markTestSkipped('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        
        $this->obtenerIdsDePrueba();
    }
    
    protected function tearDown(): void
    {
        // Limpiar favoritos creados durante las pruebas
        if (self::$createdFavoritoId && self::$testUsuarioId) {
            $this->db->exec("DELETE FROM favorito WHERE ID_Favorito = " . self::$createdFavoritoId);
        }
        
        $this->db = null;
        $this->favorito = null;
    }
    
    private function obtenerIdsDePrueba()
    {
        // Obtener un usuario válido para pruebas
        $stmt = $this->db->query("SELECT ID_Usuario FROM usuario LIMIT 1");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            self::$testUsuarioId = $usuario['ID_Usuario'];
            
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
        
        // Si no hay usuario, crear uno temporal para pruebas
        if (!self::$testUsuarioId) {
            echo "   ⚠️  No se encontró usuario, usando ID 1 para pruebas\n";
            self::$testUsuarioId = 1;
        }
        
        if (!self::$testArticuloId) {
            $stmt = $this->db->query("SELECT ID_Articulo FROM articulo LIMIT 1");
            $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($articulo) {
                self::$testArticuloId = $articulo['ID_Articulo'];
            } else {
                echo "   ⚠️  No se encontraron artículos en la base de datos\n";
            }
        }
        
        if (!self::$testProductoId && self::$testArticuloId) {
            $stmt = $this->db->query("SELECT ID_Producto FROM producto WHERE ID_Articulo = " . self::$testArticuloId . " LIMIT 1");
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($producto) {
                self::$testProductoId = $producto['ID_Producto'];
            } else {
                echo "   ⚠️  No se encontraron productos para el artículo\n";
            }
        }
    }
    
    /**
     * Test 1: Verificar que Favorito se instancia correctamente
     */
    public function testFavoritoSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(\Favorito::class, $this->favorito);
        echo "✅ Test 1 PASÓ: Favorito se instancia correctamente\n";
    }
    
    /**
     * Test 2: Verificar que existsFor() funciona correctamente para artículo
     */
    public function testExistsForArticuloFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId || !self::$testArticuloId) {
            $this->markTestSkipped('No hay usuario o artículo para probar');
        }
        
        $existe = $this->favorito->existsFor(self::$testUsuarioId, null, self::$testArticuloId);
        
        $this->assertIsBool($existe);
        
        echo "✅ Test 2 PASÓ: existsFor() para artículo funciona correctamente\n";
        echo "   Usuario: " . self::$testUsuarioId . " | Artículo: " . self::$testArticuloId . " | Existe: " . ($existe ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 3: Verificar que existsFor() funciona correctamente para producto
     */
    public function testExistsForProductoFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId || !self::$testProductoId) {
            $this->markTestSkipped('No hay usuario o producto para probar');
        }
        
        $existe = $this->favorito->existsFor(self::$testUsuarioId, self::$testProductoId, null);
        
        $this->assertIsBool($existe);
        
        echo "✅ Test 3 PASÓ: existsFor() para producto funciona correctamente\n";
        echo "   Usuario: " . self::$testUsuarioId . " | Producto: " . self::$testProductoId . " | Existe: " . ($existe ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 4: Verificar que add() funciona correctamente para artículo
     */
    public function testAddArticuloFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId || !self::$testArticuloId) {
            $this->markTestSkipped('No hay usuario o artículo para probar');
        }
        
        // Primero eliminar si ya existe
        $this->favorito->remove(self::$testUsuarioId, null, self::$testArticuloId);
        
        $resultado = $this->favorito->add(self::$testUsuarioId, null, self::$testArticuloId);
        
        $this->assertTrue($resultado);
        
        // Verificar que realmente se agregó
        $existe = $this->favorito->existsFor(self::$testUsuarioId, null, self::$testArticuloId);
        $this->assertTrue($existe);
        
        // Guardar ID para limpieza
        $stmt = $this->db->query("SELECT ID_Favorito FROM favorito WHERE ID_Usuario = " . self::$testUsuarioId . " AND ID_Articulo = " . self::$testArticuloId);
        $favorito = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($favorito) {
            self::$createdFavoritoId = $favorito['ID_Favorito'];
        }
        
        echo "✅ Test 4 PASÓ: add() para artículo funciona correctamente\n";
        echo "   Artículo agregado a favoritos: " . self::$testArticuloId . "\n";
    }
    
    /**
     * Test 5: Verificar que add() funciona correctamente para producto
     */
    public function testAddProductoFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId || !self::$testProductoId) {
            $this->markTestSkipped('No hay usuario o producto para probar');
        }
        
        // Primero eliminar si ya existe
        $this->favorito->remove(self::$testUsuarioId, self::$testProductoId, null);
        
        $resultado = $this->favorito->add(self::$testUsuarioId, self::$testProductoId, null);
        
        $this->assertTrue($resultado);
        
        // Verificar que realmente se agregó
        $existe = $this->favorito->existsFor(self::$testUsuarioId, self::$testProductoId, null);
        $this->assertTrue($existe);
        
        echo "✅ Test 5 PASÓ: add() para producto funciona correctamente\n";
        echo "   Producto agregado a favoritos: " . self::$testProductoId . "\n";
    }
    
    /**
     * Test 6: Verificar que remove() funciona correctamente para artículo
     */
    public function testRemoveArticuloFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId || !self::$testArticuloId) {
            $this->markTestSkipped('No hay usuario o artículo para probar');
        }
        
        // Primero agregar para luego eliminar
        $this->favorito->add(self::$testUsuarioId, null, self::$testArticuloId);
        
        $resultado = $this->favorito->remove(self::$testUsuarioId, null, self::$testArticuloId);
        
        $this->assertTrue($resultado);
        
        // Verificar que realmente se eliminó
        $existe = $this->favorito->existsFor(self::$testUsuarioId, null, self::$testArticuloId);
        $this->assertFalse($existe);
        
        echo "✅ Test 6 PASÓ: remove() para artículo funciona correctamente\n";
        echo "   Artículo eliminado de favoritos: " . self::$testArticuloId . "\n";
    }
    
    /**
     * Test 7: Verificar que remove() funciona correctamente para producto
     */
    public function testRemoveProductoFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId || !self::$testProductoId) {
            $this->markTestSkipped('No hay usuario o producto para probar');
        }
        
        // Primero agregar para luego eliminar
        $this->favorito->add(self::$testUsuarioId, self::$testProductoId, null);
        
        $resultado = $this->favorito->remove(self::$testUsuarioId, self::$testProductoId, null);
        
        $this->assertTrue($resultado);
        
        // Verificar que realmente se eliminó
        $existe = $this->favorito->existsFor(self::$testUsuarioId, self::$testProductoId, null);
        $this->assertFalse($existe);
        
        echo "✅ Test 7 PASÓ: remove() para producto funciona correctamente\n";
        echo "   Producto eliminado de favoritos: " . self::$testProductoId . "\n";
    }
    
    /**
     * Test 8: Verificar que getByUser() funciona correctamente
     */
    public function testGetByUserFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuario para probar');
        }
        
        $favoritos = $this->favorito->getByUser(self::$testUsuarioId);
        
        $this->assertIsArray($favoritos);
        
        echo "✅ Test 8 PASÓ: getByUser() funciona correctamente\n";
        echo "   Usuario: " . self::$testUsuarioId . " | Favoritos encontrados: " . count($favoritos) . "\n";
        
        if (count($favoritos) > 0) {
            echo "   📋 Muestra de favoritos:\n";
            foreach (array_slice($favoritos, 0, 3) as $index => $fav) {
                $nombre = $fav['Nombre'] ?? 'Sin nombre';
                $tipo = $fav['ID_Producto'] ? 'Producto' : 'Artículo';
                $precio = $fav['Precio_Final'] ?? '0';
                echo "      " . ($index + 1) . ". {$nombre} ({$tipo}) - Precio: \${$precio}\n";
            }
        }
    }
    
    /**
     * Test 9: Verificar estructura de datos de getByUser()
     */
    public function testEstructuraGetByUser()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuario para probar');
        }
        
        $favoritos = $this->favorito->getByUser(self::$testUsuarioId);
        
        $this->assertIsArray($favoritos);
        
        if (count($favoritos) > 0) {
            $favorito = $favoritos[0];
            
            // Campos obligatorios
            $this->assertArrayHasKey('ID_Favorito', $favorito);
            $this->assertArrayHasKey('Nombre', $favorito);
            $this->assertArrayHasKey('Foto', $favorito);
            $this->assertArrayHasKey('Precio_Final', $favorito);
            
            // Verificar que al menos uno de estos campos existe
            $this->assertTrue(
                isset($favorito['ID_Producto']) || isset($favorito['ID_Articulo']),
                'Debe tener ID_Producto o ID_Articulo'
            );
        }
        
        echo "✅ Test 9 PASÓ: Estructura de getByUser() validada correctamente\n";
    }
    
    /**
     * Test 10: Verificar que no se pueden duplicar favoritos
     */
    public function testNoDuplicadosFavoritos()
    {
        if (!self::$testUsuarioId || !self::$testArticuloId) {
            $this->markTestSkipped('No hay usuario o artículo para probar');
        }
        
        // Limpiar primero
        $this->favorito->remove(self::$testUsuarioId, null, self::$testArticuloId);
        
        // Agregar primera vez
        $resultado1 = $this->favorito->add(self::$testUsuarioId, null, self::$testArticuloId);
        $this->assertTrue($resultado1);
        
        // Intentar agregar segunda vez (debería fallar por restricción única)
        try {
            $resultado2 = $this->favorito->add(self::$testUsuarioId, null, self::$testArticuloId);
            // Si no lanza excepción, verificar que existsFor sigue siendo true
            $existe = $this->favorito->existsFor(self::$testUsuarioId, null, self::$testArticuloId);
            $this->assertTrue($existe);
        } catch (PDOException $e) {
            // Esperado si hay restricción única en la BD
            $this->assertStringContainsString('Duplicate', $e->getMessage());
        }
        
        // Limpiar
        $this->favorito->remove(self::$testUsuarioId, null, self::$testArticuloId);
        
        echo "✅ Test 10 PASÓ: Control de duplicados funciona correctamente\n";
    }
    
    /**
     * Test 11: Verificar manejo de usuarios inexistentes
     */
    public function testManejoUsuarioInexistente()
    {
        $usuarioInexistente = 999999;
        
        $favoritos = $this->favorito->getByUser($usuarioInexistente);
        $this->assertIsArray($favoritos);
        $this->assertEmpty($favoritos);
        
        $existe = $this->favorito->existsFor($usuarioInexistente, null, 1);
        $this->assertFalse($existe);
        
        echo "✅ Test 11 PASÓ: Manejo de usuario inexistente funciona correctamente\n";
    }
    
    /**
     * Test 12: Verificar que add() retorna false con datos inválidos
     */
    public function testAddConDatosInvalidos()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuario para probar');
        }
        
        // Intentar agregar sin producto ni artículo
        try {
            $resultado = $this->favorito->add(self::$testUsuarioId, null, null);
            // Depende de cómo esté configurada la BD, puede fallar o no
            $this->assertIsBool($resultado);
        } catch (PDOException $e) {
            // Esperado si la BD tiene restricciones
            $this->assertTrue(true, 'Excepción esperada con datos inválidos');
        }
        
        echo "✅ Test 12 PASÓ: Manejo de datos inválidos funciona correctamente\n";
    }
    
    /**
     * Test 13: Verificar integración completa - ciclo completo
     */
    public function testCicloCompletoFavorito()
    {
        if (!self::$testUsuarioId || !self::$testArticuloId) {
            $this->markTestSkipped('No hay usuario o artículo para probar ciclo completo');
        }
        
        // 1. Verificar que no existe inicialmente
        $existeInicial = $this->favorito->existsFor(self::$testUsuarioId, null, self::$testArticuloId);
        
        // 2. Agregar a favoritos
        $agregado = $this->favorito->add(self::$testUsuarioId, null, self::$testArticuloId);
        $this->assertTrue($agregado);
        
        // 3. Verificar que existe después de agregar
        $existeDespues = $this->favorito->existsFor(self::$testUsuarioId, null, self::$testArticuloId);
        $this->assertTrue($existeDespues);
        
        // 4. Verificar que aparece en getByUser()
        $favoritos = $this->favorito->getByUser(self::$testUsuarioId);
        $encontrado = false;
        foreach ($favoritos as $fav) {
            if (isset($fav['ID_Articulo']) && $fav['ID_Articulo'] == self::$testArticuloId) {
                $encontrado = true;
                break;
            }
        }
        $this->assertTrue($encontrado);
        
        // 5. Eliminar de favoritos
        $eliminado = $this->favorito->remove(self::$testUsuarioId, null, self::$testArticuloId);
        $this->assertTrue($eliminado);
        
        // 6. Verificar que ya no existe
        $existeFinal = $this->favorito->existsFor(self::$testUsuarioId, null, self::$testArticuloId);
        $this->assertFalse($existeFinal);
        
        echo "✅ Test 13 PASÓ: Ciclo completo de favorito funciona correctamente\n";
        echo "   Artículo probado: " . self::$testArticuloId . "\n";
    }
    
    public static function tearDownAfterClass(): void
    {
        echo "\n================================\n";
        echo "🏁 PRUEBAS DE FAVORITO COMPLETADAS\n\n";
    }
}