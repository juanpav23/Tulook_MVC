<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

// INCLUIR MANUALMENTE EL ARCHIVO DESCUENTO
require_once __DIR__ . '/../../models/Descuento.php';

class DescuentoTest extends TestCase
{
    private $descuento;
    private $mockDb;
    private $mockStmt;

    protected function setUp(): void
    {
        // 1. Crear mock de la base de datos
        $this->mockDb = $this->createMock(PDO::class);
        $this->mockStmt = $this->createMock(PDOStatement::class);
        
        // 2. Instanciar el modelo Descuento
        $this->descuento = new \Descuento($this->mockDb);
    }

    // =======================================================
    // ✅ TEST 1: INSTANCIACIÓN BÁSICA
    // =======================================================
    public function testDescuentoSePuedeInstanciar()
    {
        $this->assertInstanceOf(\Descuento::class, $this->descuento);
        echo "✅ Test 1 PASÓ: Descuento se instancia correctamente\n";
    }

    // =======================================================
    // ✅ TEST 2: OBTENER TODOS LOS DESCUENTOS
    // =======================================================
    public function testObtenerTodosLosDescuentos()
    {
        // Configurar el mock
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('SELECT d.*'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute');
            
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'ID_Descuento' => 1,
                    'Codigo' => 'VERANO25',
                    'Tipo' => 'porcentaje',
                    'Valor' => 25.00,
                    'EstadoVigencia' => 'activo'
                ]
            ]);

        // Ejecutar el método
        $result = $this->descuento->obtenerTodos();

        // Verificaciones
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('VERANO25', $result[0]['Codigo']);
        
        echo "✅ Test 2 PASÓ: obtenerTodos() funciona correctamente\n";
    }

    // =======================================================
    // ✅ TEST 3: OBTENER DESCUENTO POR ID
    // =======================================================
    public function testObtenerDescuentoPorId()
    {
        $mockDescuento = [
            'ID_Descuento' => 1,
            'Codigo' => 'DESC20',
            'Tipo' => 'porcentaje',
            'Valor' => 20.00,
            'ArticuloNombre' => 'Camiseta Test'
        ];

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);
            
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockDescuento);

        $result = $this->descuento->obtenerPorId(1);

        $this->assertEquals($mockDescuento, $result);
        echo "✅ Test 3 PASÓ: obtenerPorId() funciona correctamente\n";
    }

    // =======================================================
    // ✅ TEST 4: CREAR NUEVO DESCUENTO
    // =======================================================
    public function testCrearNuevoDescuento()
    {
        $datosDescuento = [
            'Codigo' => 'INVIERNO30',
            'ID_Articulo' => null,
            'ID_Producto' => null,
            'ID_Categoria' => 1,
            'Tipo' => 'porcentaje',
            'Valor' => 30.00,
            'FechaInicio' => '2024-01-01',
            'FechaFin' => '2024-12-31',
            'Activo' => 1
        ];

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                'INVIERNO30', null, null, 1, 'porcentaje', 30.00, 
                '2024-01-01', '2024-12-31', 1
            ])
            ->willReturn(true);

        $result = $this->descuento->crear($datosDescuento);

        $this->assertTrue($result);
        echo "✅ Test 4 PASÓ: crear() funciona correctamente\n";
    }

    // Los demás tests permanecen igual...
    // =======================================================
    // ✅ TEST 5: ACTUALIZAR DESCUENTO EXISTENTE
    // =======================================================
    public function testActualizarDescuentoExistente()
    {
        $datosActualizar = [
            'Codigo' => 'PRIMAVERA15',
            'ID_Articulo' => 1,
            'ID_Producto' => null,
            'ID_Categoria' => null,
            'Tipo' => 'porcentaje',
            'Valor' => 15.00,
            'FechaInicio' => '2024-03-01',
            'FechaFin' => '2024-05-31',
            'Activo' => 1
        ];

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('UPDATE'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([
                'PRIMAVERA15', 1, null, null, 'porcentaje', 15.00,
                '2024-03-01', '2024-05-31', 1, 1
            ])
            ->willReturn(true);

        $result = $this->descuento->actualizar(1, $datosActualizar);

        $this->assertTrue($result);
        echo "✅ Test 5 PASÓ: actualizar() funciona correctamente\n";
    }

    // =======================================================
    // ✅ TEST 6: ELIMINAR DESCUENTO
    // =======================================================
    public function testEliminarDescuento()
    {
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('DELETE'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1])
            ->willReturn(true);

        $result = $this->descuento->eliminar(1);

        $this->assertTrue($result);
        echo "✅ Test 6 PASÓ: eliminar() funciona correctamente\n";
    }

    // =======================================================
    // ✅ TEST 7: OBTENER DESCUENTO POR ARTÍCULO
    // =======================================================
    public function testObtenerDescuentoArticulo()
    {
        $mockDescuento = [
            'ID_Descuento' => 1,
            'Codigo' => 'ART20',
            'Valor' => 20.00
        ];

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('ID_Articulo = ?'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([1]);
            
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockDescuento);

        $result = $this->descuento->obtenerDescuentoArticulo(1);

        $this->assertEquals($mockDescuento, $result);
        echo "✅ Test 7 PASÓ: obtenerDescuentoArticulo() funciona correctamente\n";
    }

    // =======================================================
    // ✅ TEST 8: OBTENER DESCUENTO POR PRODUCTO
    // =======================================================
    public function testObtenerDescuentoProducto()
    {
        $mockDescuento = [
            'ID_Descuento' => 2,
            'Codigo' => 'PROD15',
            'Valor' => 15.00
        ];

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('ID_Producto = ?'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([5]);
            
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockDescuento);

        $result = $this->descuento->obtenerDescuentoProducto(5);

        $this->assertEquals($mockDescuento, $result);
        echo "✅ Test 8 PASÓ: obtenerDescuentoProducto() funciona correctamente\n";
    }

    // =======================================================
    // ✅ TEST 9: OBTENER DESCUENTO POR CATEGORÍA
    // =======================================================
    public function testObtenerDescuentoCategoria()
    {
        $mockDescuento = [
            'ID_Descuento' => 3,
            'Codigo' => 'CAT10',
            'Valor' => 10.00
        ];

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('ID_Categoria = ?'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with([2]);
            
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($mockDescuento);

        $result = $this->descuento->obtenerDescuentoCategoria(2);

        $this->assertEquals($mockDescuento, $result);
        echo "✅ Test 9 PASÓ: obtenerDescuentoCategoria() funciona correctamente\n";
    }

    // =======================================================
    // ✅ TEST 10: VERIFICAR SI CÓDIGO EXISTE
    // =======================================================
    public function testCodigoExiste()
    {
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('Codigo = ?'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['EXISTENTE']);
            
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(['ID_Descuento' => 1]);

        $result = $this->descuento->codigoExiste('EXISTENTE');

        $this->assertTrue($result);
        echo "✅ Test 10 PASÓ: codigoExiste() detecta código existente\n";
    }

    // =======================================================
    // ✅ TEST 11: VERIFICAR CÓDIGO NO EXISTE
    // =======================================================
    public function testCodigoNoExiste()
    {
        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute')
            ->with(['NO_EXISTE']);
            
        $this->mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false);

        $result = $this->descuento->codigoExiste('NO_EXISTE');

        $this->assertFalse($result);
        echo "✅ Test 11 PASÓ: codigoExiste() detecta código no existente\n";
    }

    // =======================================================
    // ✅ TEST 12: OBTENER DESCUENTOS VIGENTES
    // =======================================================
    public function testObtenerDescuentosVigentes()
    {
        $mockDescuentos = [
            [
                'ID_Descuento' => 1,
                'Codigo' => 'VIGENTE1',
                'ArticuloNombre' => 'Producto 1'
            ],
            [
                'ID_Descuento' => 2, 
                'Codigo' => 'VIGENTE2',
                'ProductoNombre' => 'Producto 2'
            ]
        ];

        $this->mockDb->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('Activo = 1'))
            ->willReturn($this->mockStmt);
            
        $this->mockStmt->expects($this->once())
            ->method('execute');
            
        $this->mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDescuentos);

        $result = $this->descuento->obtenerDescuentosVigentes();

        $this->assertCount(2, $result);
        echo "✅ Test 12 PASÓ: obtenerDescuentosVigentes() funciona correctamente\n";
    }
}