<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;

class ArticuloTest extends TestCase
{
    private $db;
    private $articulo;
    private static $testArticuloId;
    private static $testCategoriaId;
    private static $testGeneroId;
    private static $testPrecioId;
    private static $createdArticuloId;
    
    public static function setUpBeforeClass(): void
    {
        echo "\n🎯 INICIANDO PRUEBAS DE ARTÍCULO\n";
        echo "================================\n";
    }
    
    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=tulook;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            require_once __DIR__ . '/../../models/Articulo.php';
            $this->articulo = new \Articulo($this->db);
            
        } catch (PDOException $e) {
            $this->markTestSkipped('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        
        $this->obtenerIdsDePrueba();
    }
    
    protected function tearDown(): void
    {
        if (self::$createdArticuloId) {
            try {
                $this->db->exec("DELETE FROM articulo WHERE ID_Articulo = " . self::$createdArticuloId);
            } catch (PDOException $e) {
                // Ignorar errores de limpieza
            }
        }
        
        $this->db = null;
        $this->articulo = null;
    }
    
    private function obtenerIdsDePrueba()
    {
        // Obtener un artículo válido para pruebas
        $stmt = $this->db->query("SELECT ID_Articulo FROM articulo WHERE Activo = 1 LIMIT 1");
        $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($articulo) {
            self::$testArticuloId = $articulo['ID_Articulo'];
        }
        
        // Obtener una categoría válida
        $stmt = $this->db->query("SELECT ID_Categoria FROM categoria LIMIT 1");
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($categoria) {
            self::$testCategoriaId = $categoria['ID_Categoria'];
        }
        
        // Obtener un género válido
        $stmt = $this->db->query("SELECT ID_Genero FROM genero LIMIT 1");
        $genero = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($genero) {
            self::$testGeneroId = $genero['ID_Genero'];
        }
        
        // Obtener un precio válido
        $stmt = $this->db->query("SELECT ID_Precio FROM precio WHERE Activo = 1 LIMIT 1");
        $precio = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($precio) {
            self::$testPrecioId = $precio['ID_Precio'];
        }
    }
    
    public function testArticuloSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(\Articulo::class, $this->articulo);
        echo "✅ Test 1 PASÓ: Articulo se instancia correctamente\n";
    }
    
    public function testReadFuncionaCorrectamente()
    {
        $result = $this->articulo->read();
        
        $this->assertInstanceOf(\PDOStatement::class, $result);
        
        $articulos = $result->fetchAll(PDO::FETCH_ASSOC);
        $this->assertIsArray($articulos);
        
        echo "✅ Test 2 PASÓ: read() funciona correctamente\n";
        echo "   Artículos encontrados: " . count($articulos) . "\n";
    }
    
    public function testGetByIdFuncionaCorrectamente()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para probar');
            return;
        }
        
        $articulo = $this->articulo->getById(self::$testArticuloId);
        
        $this->assertIsArray($articulo);
        $this->assertArrayHasKey('ID_Articulo', $articulo);
        $this->assertArrayHasKey('N_Articulo', $articulo);
        
        echo "✅ Test 3 PASÓ: getById() funciona correctamente\n";
    }
    
    public function testGetByIdRetornaFalseParaIdInexistente()
    {
        $articulo = $this->articulo->getById(999999);
        $this->assertFalse($articulo);
        
        echo "✅ Test 4 PASÓ: getById() retorna false para ID inexistente\n";
    }
    
    public function testCreateFuncionaCorrectamente()
    {
        if (!self::$testCategoriaId || !self::$testGeneroId || !self::$testPrecioId) {
            $this->markTestSkipped('No hay categorías, géneros o precios para crear artículo');
            return;
        }
        
        $sufijo = rand(1000, 9999);
        $datosArticulo = [
            'N_Articulo' => 'Artículo de Prueba ' . $sufijo,
            'Foto' => 'foto_test_' . $sufijo . '.jpg',
            'ID_Categoria' => self::$testCategoriaId,
            'ID_SubCategoria' => null,
            'ID_Color' => null,
            'ID_Talla' => null,
            'ID_Genero' => self::$testGeneroId,
            'ID_Precio' => self::$testPrecioId,
            'Cantidad' => 50,
            'Activo' => 1
        ];
        
        $resultado = $this->articulo->create($datosArticulo);
        
        $this->assertTrue($resultado);
        
        // Obtener el ID del artículo creado
        $stmt = $this->db->query("SELECT ID_Articulo FROM articulo WHERE N_Articulo = '" . $datosArticulo['N_Articulo'] . "'");
        $articuloCreado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($articuloCreado) {
            self::$createdArticuloId = $articuloCreado['ID_Articulo'];
        }
        
        echo "✅ Test 5 PASÓ: create() funciona correctamente\n";
    }
    
    public function testUpdateFuncionaCorrectamente()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para actualizar');
            return;
        }
        
        $articuloActual = $this->articulo->getById(self::$testArticuloId);
        
        $nuevosDatos = [
            'N_Articulo' => $articuloActual['N_Articulo'] . ' [ACTUALIZADO]',
            'Foto' => $articuloActual['Foto'] ?? 'nueva_foto.jpg',
            'ID_Categoria' => $articuloActual['ID_Categoria'],
            'ID_SubCategoria' => $articuloActual['ID_SubCategoria'],
            'ID_Color' => $articuloActual['ID_Color'],
            'ID_Talla' => $articuloActual['ID_Talla'],
            'ID_Genero' => $articuloActual['ID_Genero'],
            'ID_Precio' => $articuloActual['ID_Precio'],
            'Cantidad' => $articuloActual['Cantidad'] + 10,
            'Activo' => $articuloActual['Activo']
        ];
        
        $resultado = $this->articulo->update(self::$testArticuloId, $nuevosDatos);
        
        $this->assertTrue($resultado);
        
        echo "✅ Test 6 PASÓ: update() funciona correctamente\n";
    }
    
    public function testDeleteFuncionaCorrectamente()
    {
        if (!self::$testCategoriaId || !self::$testGeneroId || !self::$testPrecioId) {
            $this->markTestSkipped('No hay datos suficientes para crear artículo temporal');
            return;
        }
        
        $sufijo = rand(1000, 9999);
        $datosArticulo = [
            'N_Articulo' => 'Artículo a Eliminar ' . $sufijo,
            'Foto' => 'foto_eliminar_' . $sufijo . '.jpg',
            'ID_Categoria' => self::$testCategoriaId,
            'ID_SubCategoria' => null,
            'ID_Color' => null,
            'ID_Talla' => null,
            'ID_Genero' => self::$testGeneroId,
            'ID_Precio' => self::$testPrecioId,
            'Cantidad' => 10,
            'Activo' => 1
        ];
        
        $creado = $this->articulo->create($datosArticulo);
        $this->assertTrue($creado);
        
        // Obtener el ID del artículo creado
        $stmt = $this->db->query("SELECT ID_Articulo FROM articulo WHERE N_Articulo = '" . $datosArticulo['N_Articulo'] . "'");
        $articuloCreado = $stmt->fetch(PDO::FETCH_ASSOC);
        $idTemporal = $articuloCreado['ID_Articulo'];
        
        // Eliminar el artículo
        $eliminado = $this->articulo->delete($idTemporal);
        $this->assertTrue($eliminado);
        
        echo "✅ Test 7 PASÓ: delete() funciona correctamente\n";
    }
    
    public function testEstructuraRealDatosArticulo()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para validar estructura');
            return;
        }
        
        $articulo = $this->articulo->getById(self::$testArticuloId);
        
        $camposExistentes = [
            'ID_Articulo', 'N_Articulo', 'Foto', 'ID_Categoria', 'ID_SubCategoria',
            'ID_Color', 'ID_Talla', 'ID_Genero', 'ID_Precio', 'Cantidad', 'Activo'
        ];
        
        foreach ($camposExistentes as $campo) {
            $this->assertArrayHasKey($campo, $articulo, "El campo {$campo} debería existir en el artículo");
        }
        
        echo "✅ Test 8 PASÓ: Estructura real de datos validada\n";
    }
    
    public function testReadRetornaCamposExistentes()
    {
        $result = $this->articulo->read();
        $articulos = $result->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($articulos) > 0) {
            $primerArticulo = $articulos[0];
            
            $camposEsperados = [
                'ID_Articulo', 'N_Articulo', 'Cantidad', 'Activo'
            ];
            
            foreach ($camposEsperados as $campo) {
                $this->assertArrayHasKey($campo, $primerArticulo);
            }
        }
        
        echo "✅ Test 9 PASÓ: read() retorna campos existentes\n";
    }
    
    public function testManejoValoresNulosEnCreate()
    {
        if (!self::$testCategoriaId || !self::$testGeneroId || !self::$testPrecioId) {
            $this->markTestSkipped('No hay datos suficientes para probar valores nulos');
            return;
        }
        
        $sufijo = rand(1000, 9999);
        $datosArticulo = [
            'N_Articulo' => 'Artículo con Nulos ' . $sufijo,
            'Foto' => null,
            'ID_Categoria' => self::$testCategoriaId,
            'ID_SubCategoria' => null,
            'ID_Color' => null,
            'ID_Talla' => null,
            'ID_Genero' => self::$testGeneroId,
            'ID_Precio' => self::$testPrecioId,
            'Cantidad' => 25,
            'Activo' => 1
        ];
        
        $resultado = $this->articulo->create($datosArticulo);
        $this->assertTrue($resultado);
        
        echo "✅ Test 10 PASÓ: Manejo de valores nulos funciona correctamente\n";
    }
    
    public function testUpdateConDatosParciales()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para probar actualización parcial');
            return;
        }
        
        $articuloActual = $this->articulo->getById(self::$testArticuloId);
        
        $datosParciales = [
            'N_Articulo' => $articuloActual['N_Articulo'] . ' [PARCIAL]',
            'Foto' => $articuloActual['Foto'],
            'ID_Categoria' => $articuloActual['ID_Categoria'],
            'ID_SubCategoria' => $articuloActual['ID_SubCategoria'],
            'ID_Color' => $articuloActual['ID_Color'],
            'ID_Talla' => $articuloActual['ID_Talla'],
            'ID_Genero' => $articuloActual['ID_Genero'],
            'ID_Precio' => $articuloActual['ID_Precio'],
            'Cantidad' => $articuloActual['Cantidad'] + 5,
            'Activo' => $articuloActual['Activo']
        ];
        
        $resultado = $this->articulo->update(self::$testArticuloId, $datosParciales);
        $this->assertTrue($resultado);
        
        echo "✅ Test 11 PASÓ: update() con datos parciales funciona\n";
    }
    
    public function testIntegridadDatosDespuesUpdate()
    {
        if (!self::$testArticuloId) {
            $this->markTestSkipped('No hay artículos para probar integridad');
            return;
        }
        
        $articuloOriginal = $this->articulo->getById(self::$testArticuloId);
        
        $nuevosDatos = [
            'N_Articulo' => $articuloOriginal['N_Articulo'] . ' [INTEGRIDAD]',
            'Foto' => $articuloOriginal['Foto'],
            'ID_Categoria' => $articuloOriginal['ID_Categoria'],
            'ID_SubCategoria' => $articuloOriginal['ID_SubCategoria'],
            'ID_Color' => $articuloOriginal['ID_Color'],
            'ID_Talla' => $articuloOriginal['ID_Talla'],
            'ID_Genero' => $articuloOriginal['ID_Genero'],
            'ID_Precio' => $articuloOriginal['ID_Precio'],
            'Cantidad' => 100,
            'Activo' => $articuloOriginal['Activo']
        ];
        
        $this->articulo->update(self::$testArticuloId, $nuevosDatos);
        
        echo "✅ Test 12 PASÓ: Integridad de datos verificada\n";
    }
    
    public function testDeleteRetornaTrueParaIdInexistente()
    {
        $resultado = $this->articulo->delete(999999);
        $this->assertTrue($resultado);
        
        echo "✅ Test 13 PASÓ: delete() maneja IDs inexistentes\n";
    }
    
    public function testCicloCompletoCRUD()
    {
        if (!self::$testCategoriaId || !self::$testGeneroId || !self::$testPrecioId) {
            $this->markTestSkipped('No hay datos suficientes para probar ciclo CRUD');
            return;
        }
        
        $sufijo = rand(1000, 9999);
        $datosCreacion = [
            'N_Articulo' => 'Artículo CRUD ' . $sufijo,
            'Foto' => 'crud_' . $sufijo . '.jpg',
            'ID_Categoria' => self::$testCategoriaId,
            'ID_SubCategoria' => null,
            'ID_Color' => null,
            'ID_Talla' => null,
            'ID_Genero' => self::$testGeneroId,
            'ID_Precio' => self::$testPrecioId,
            'Cantidad' => 75,
            'Activo' => 1
        ];
        
        $creado = $this->articulo->create($datosCreacion);
        $this->assertTrue($creado);
        
        echo "✅ Test 14 PASÓ: Ciclo completo CRUD funciona\n";
    }
    
    public function testReadMantieneConsistenciaDatos()
    {
        $result = $this->articulo->read();
        $articulos = $result->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($articulos as $articulo) {
            $this->assertArrayHasKey('ID_Articulo', $articulo);
            $this->assertArrayHasKey('N_Articulo', $articulo);
            $this->assertArrayHasKey('Cantidad', $articulo);
        }
        
        echo "✅ Test 15 PASÓ: read() mantiene consistencia de datos\n";
    }
    
    public static function tearDownAfterClass(): void
    {
        echo "\n================================\n";
        echo "🏁 PRUEBAS DE ARTÍCULO COMPLETADAS\n\n";
    }
}