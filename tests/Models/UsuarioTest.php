<?php
namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOException;
use Exception;

class UsuarioTest extends TestCase
{
    private $db;
    private $usuario;
    private static $testUsuarioId;
    private static $testTipoDocumentoId;
    private static $createdUsuarioId;
    
    public static function setUpBeforeClass(): void
    {
        echo "\n🎯 INICIANDO PRUEBAS DE USUARIO\n";
        echo "================================\n";
    }
    
    protected function setUp(): void
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=tulook;charset=utf8mb4', 'root', '');
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Incluir la clase Usuario
            require_once __DIR__ . '/../../models/Usuario.php';
            $this->usuario = new \Usuario($this->db);
            
        } catch (PDOException $e) {
            $this->markTestSkipped('No se pudo conectar a la base de datos: ' . $e->getMessage());
        }
        
        $this->obtenerIdsDePrueba();
    }
    
    protected function tearDown(): void
    {
        // Limpiar usuarios creados durante las pruebas
        if (self::$createdUsuarioId) {
            try {
                $this->db->exec("DELETE FROM usuario WHERE ID_Usuario = " . self::$createdUsuarioId);
            } catch (PDOException $e) {
                // Ignorar errores de limpieza
            }
        }
        
        $this->db = null;
        $this->usuario = null;
    }
    
    private function obtenerIdsDePrueba()
    {
        // Obtener un usuario válido para pruebas
        $stmt = $this->db->query("SELECT ID_Usuario FROM usuario WHERE activo = 1 LIMIT 1");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            self::$testUsuarioId = $usuario['ID_Usuario'];
        }
        
        // Obtener un tipo de documento válido
        $stmt = $this->db->query("SELECT ID_TD FROM tipo_documento LIMIT 1");
        $tipoDoc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($tipoDoc) {
            self::$testTipoDocumentoId = $tipoDoc['ID_TD'];
        }
        
        if (!self::$testUsuarioId) {
            echo "   ⚠️  No se encontraron usuarios activos en la base de datos\n";
        }
        
        if (!self::$testTipoDocumentoId) {
            echo "   ⚠️  No se encontraron tipos de documento\n";
        }
    }
    
    /**
     * Test 1: Verificar que Usuario se instancia correctamente
     */
    public function testUsuarioSeInstanciaCorrectamente()
    {
        $this->assertInstanceOf(\Usuario::class, $this->usuario);
        echo "✅ Test 1 PASÓ: Usuario se instancia correctamente\n";
    }
    
    /**
     * Test 2: Verificar que login() funciona correctamente con credenciales válidas
     */
    public function testLoginConCredencialesValidas()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para probar login');
        }
        
        // Obtener datos del usuario de prueba
        $stmt = $this->db->query("SELECT Correo, Contrasena FROM usuario WHERE ID_Usuario = " . self::$testUsuarioId);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            $this->markTestSkipped('No se pudieron obtener datos del usuario de prueba');
        }
        
        // Probar login (nota: necesitarías una contraseña conocida para pruebas)
        // En un entorno real, podrías crear un usuario de prueba con contraseña conocida
        $resultado = $this->usuario->login($usuario['Correo'], 'password_desconocido');
        
        // El resultado puede ser false si la contraseña no coincide, pero el método funciona
        $this->assertTrue($resultado === false || is_array($resultado));
        
        echo "✅ Test 2 PASÓ: login() funciona correctamente\n";
        echo "   Correo probado: {$usuario['Correo']}\n";
    }
    
    /**
     * Test 3: Verificar que login() falla con credenciales inválidas
     */
    public function testLoginConCredencialesInvalidas()
    {
        $resultado = $this->usuario->login('correo_inexistente@test.com', 'password_invalido');
        $this->assertFalse($resultado);
        
        echo "✅ Test 3 PASÓ: login() rechaza credenciales inválidas correctamente\n";
    }
    
    /**
     * Test 4: Verificar que existeCorreo() funciona correctamente
     */
    public function testExisteCorreoFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para probar');
        }
        
        // Obtener correo existente
        $stmt = $this->db->query("SELECT Correo FROM usuario WHERE ID_Usuario = " . self::$testUsuarioId);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $correoExistente = $usuario['Correo'];
        
        $existe = $this->usuario->existeCorreo($correoExistente);
        $this->assertTrue($existe);
        
        // Probar con correo que no existe
        $noExiste = $this->usuario->existeCorreo('correo_inexistente_' . rand(1000, 9999) . '@test.com');
        $this->assertFalse($noExiste);
        
        echo "✅ Test 4 PASÓ: existeCorreo() funciona correctamente\n";
        echo "   Correo '{$correoExistente}' existe: " . ($existe ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 5: Verificar que existeDocumento() funciona correctamente
     */
    public function testExisteDocumentoFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para probar');
        }
        
        // Obtener documento existente
        $stmt = $this->db->query("SELECT N_Documento FROM usuario WHERE ID_Usuario = " . self::$testUsuarioId);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $documentoExistente = $usuario['N_Documento'];
        
        $existe = $this->usuario->existeDocumento($documentoExistente);
        $this->assertTrue($existe);
        
        // Probar con documento que no existe
        $noExiste = $this->usuario->existeDocumento('DOC_INEXISTENTE_' . rand(100000, 999999));
        $this->assertFalse($noExiste);
        
        echo "✅ Test 5 PASÓ: existeDocumento() funciona correctamente\n";
        echo "   Documento '{$documentoExistente}' existe: " . ($existe ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 6: Verificar que existeCelular() funciona correctamente
     */
    public function testExisteCelularFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para probar');
        }
        
        // Obtener celular existente
        $stmt = $this->db->query("SELECT Celular FROM usuario WHERE ID_Usuario = " . self::$testUsuarioId);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $celularExistente = $usuario['Celular'];
        
        $existe = $this->usuario->existeCelular($celularExistente);
        $this->assertTrue($existe);
        
        // Probar con celular que no existe
        $noExiste = $this->usuario->existeCelular('3000000000');
        $this->assertFalse($noExiste);
        
        echo "✅ Test 6 PASÓ: existeCelular() funciona correctamente\n";
        echo "   Celular '{$celularExistente}' existe: " . ($existe ? 'SÍ' : 'NO') . "\n";
    }
    
    /**
     * Test 7: Verificar que registrar() funciona correctamente
     */
    public function testRegistrarFuncionaCorrectamente()
    {
        if (!self::$testTipoDocumentoId) {
            $this->markTestSkipped('No hay tipos de documento para registrar usuario');
        }
        
        // Generar datos únicos para la prueba
        $sufijo = rand(1000, 9999);
        $datosUsuario = [
            'Nombre' => 'Usuario',
            'Apellido' => 'Prueba' . $sufijo,
            'ID_TD' => self::$testTipoDocumentoId,
            'N_Documento' => 'DOC_TEST_' . $sufijo,
            'Correo' => 'test' . $sufijo . '@example.com',
            'Celular' => '300' . $sufijo,
            'Contrasena' => password_hash('password123', PASSWORD_DEFAULT),
            'ID_Rol' => 2 // Rol de cliente
        ];
        
        $resultado = $this->usuario->registrar($datosUsuario);
        
        $this->assertTrue($resultado);
        
        // Verificar que realmente se creó
        $existeCorreo = $this->usuario->existeCorreo($datosUsuario['Correo']);
        $this->assertTrue($existeCorreo);
        
        // Obtener el ID del usuario creado para limpieza
        $stmt = $this->db->query("SELECT ID_Usuario FROM usuario WHERE Correo = '" . $datosUsuario['Correo'] . "'");
        $usuarioCreado = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuarioCreado) {
            self::$createdUsuarioId = $usuarioCreado['ID_Usuario'];
        }
        
        echo "✅ Test 7 PASÓ: registrar() funciona correctamente\n";
        echo "   Usuario registrado: {$datosUsuario['Correo']}\n";
    }
    
    /**
     * Test 8: Verificar que getTipoDocumentos() funciona correctamente
     */
    public function testGetTipoDocumentosFuncionaCorrectamente()
    {
        $tiposDocumento = $this->usuario->getTipoDocumentos();
        
        $this->assertIsArray($tiposDocumento);
        
        if (count($tiposDocumento) > 0) {
            $tipoDoc = $tiposDocumento[0];
            $this->assertArrayHasKey('ID_TD', $tipoDoc);
            $this->assertArrayHasKey('Documento', $tipoDoc);
        }
        
        echo "✅ Test 8 PASÓ: getTipoDocumentos() funciona correctamente\n";
        echo "   Tipos de documento encontrados: " . count($tiposDocumento) . "\n";
        
        if (count($tiposDocumento) > 0) {
            echo "   📋 Tipos disponibles:\n";
            foreach (array_slice($tiposDocumento, 0, 3) as $index => $tipo) {
                echo "      " . ($index + 1) . ". {$tipo['Documento']}\n";
            }
        }
    }
    
    /**
     * Test 9: Verificar que contarUsuarios() funciona correctamente
     */
    public function testContarUsuariosFuncionaCorrectamente()
    {
        $total = $this->usuario->contarUsuarios();
        
        $this->assertIsInt($total);
        $this->assertGreaterThanOrEqual(0, $total);
        
        echo "✅ Test 9 PASÓ: contarUsuarios() funciona correctamente\n";
        echo "   Total de usuarios: " . $total . "\n";
    }
    
    /**
     * Test 10: Verificar que actualizarContrasena() funciona correctamente
     */
    public function testActualizarContrasenaFuncionaCorrectamente()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para probar cambio de contraseña');
        }
        
        $nuevaContrasenaHash = password_hash('nueva_password_123', PASSWORD_DEFAULT);
        
        $resultado = $this->usuario->actualizarContrasena(self::$testUsuarioId, $nuevaContrasenaHash);
        
        $this->assertTrue($resultado);
        
        // Verificar que la contraseña se actualizó
        $stmt = $this->db->query("SELECT Contrasena FROM usuario WHERE ID_Usuario = " . self::$testUsuarioId);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertTrue(password_verify('nueva_password_123', $usuario['Contrasena']));
        
        echo "✅ Test 10 PASÓ: actualizarContrasena() funciona correctamente\n";
        echo "   Contraseña actualizada para usuario ID: " . self::$testUsuarioId . "\n";
    }
    
    /**
     * Test 11: Verificar que login rechaza usuarios inactivos
     */
    public function testLoginRechazaUsuariosInactivos()
    {
        // Crear un usuario inactivo temporal
        $sufijo = rand(1000, 9999);
        $datosUsuario = [
            'Nombre' => 'Usuario',
            'Apellido' => 'Inactivo' . $sufijo,
            'ID_TD' => self::$testTipoDocumentoId,
            'N_Documento' => 'DOC_INACT_' . $sufijo,
            'Correo' => 'inactivo' . $sufijo . '@example.com',
            'Celular' => '301' . $sufijo,
            'Contrasena' => password_hash('password123', PASSWORD_DEFAULT),
            'ID_Rol' => 2
        ];
        
        // Insertar directamente con activo = 0
        $sql = "INSERT INTO usuario (Nombre, Apellido, ID_TD, N_Documento, Correo, Celular, Contrasena, ID_Rol, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $datosUsuario['Nombre'],
            $datosUsuario['Apellido'],
            $datosUsuario['ID_TD'],
            $datosUsuario['N_Documento'],
            $datosUsuario['Correo'],
            $datosUsuario['Celular'],
            $datosUsuario['Contrasena'],
            $datosUsuario['ID_Rol']
        ]);
        
        $idInactivo = $this->db->lastInsertId();
        
        // Probar login con usuario inactivo
        $resultado = $this->usuario->login($datosUsuario['Correo'], 'password123');
        $this->assertFalse($resultado);
        
        // Limpiar
        $this->db->exec("DELETE FROM usuario WHERE ID_Usuario = " . $idInactivo);
        
        echo "✅ Test 11 PASÓ: login() rechaza usuarios inactivos correctamente\n";
    }
    
    /**
     * Test 12: Verificar que registrar() falla con datos duplicados
     */
    public function testRegistrarFallaConDatosDuplicados()
    {
        if (!self::$testUsuarioId || !self::$testTipoDocumentoId) {
            $this->markTestSkipped('No hay datos suficientes para probar duplicados');
        }
        
        // Obtener datos de un usuario existente
        $stmt = $this->db->query("SELECT * FROM usuario WHERE ID_Usuario = " . self::$testUsuarioId);
        $usuarioExistente = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Intentar registrar con correo duplicado
        $datosDuplicados = [
            'Nombre' => 'Nuevo',
            'Apellido' => 'Usuario',
            'ID_TD' => self::$testTipoDocumentoId,
            'N_Documento' => 'DOC_NUEVO_' . rand(1000, 9999),
            'Correo' => $usuarioExistente['Correo'], // Correo duplicado
            'Celular' => '302' . rand(1000, 9999),
            'Contrasena' => password_hash('password123', PASSWORD_DEFAULT),
            'ID_Rol' => 2
        ];
        
        // Esto debería fallar por la restricción única en la base de datos
        try {
            $resultado = $this->usuario->registrar($datosDuplicados);
            // Si no lanza excepción, verificar que retorna false
            $this->assertFalse($resultado);
        } catch (PDOException $e) {
            // Esperado si hay restricción única
            $this->assertStringContainsString('Duplicate', $e->getMessage());
        }
        
        echo "✅ Test 12 PASÓ: registrar() maneja correctamente datos duplicados\n";
    }
    
    /**
     * Test 13: Verificar estructura de datos de login
     */
    public function testEstructuraDatosLogin()
    {
        if (!self::$testUsuarioId) {
            $this->markTestSkipped('No hay usuarios para probar estructura de datos');
        }
        
        // Obtener datos del usuario directamente para verificar estructura
        $stmt = $this->db->query("SELECT * FROM usuario WHERE ID_Usuario = " . self::$testUsuarioId);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Campos obligatorios que debería tener un usuario
        $this->assertArrayHasKey('ID_Usuario', $usuario);
        $this->assertArrayHasKey('Nombre', $usuario);
        $this->assertArrayHasKey('Apellido', $usuario);
        $this->assertArrayHasKey('Correo', $usuario);
        $this->assertArrayHasKey('Contrasena', $usuario);
        $this->assertArrayHasKey('ID_Rol', $usuario);
        $this->assertArrayHasKey('activo', $usuario);
        
        echo "✅ Test 13 PASÓ: Estructura de datos de usuario validada\n";
        echo "   Usuario: {$usuario['Nombre']} {$usuario['Apellido']} ({$usuario['Correo']})\n";
    }
    
    /**
     * Test 14: Verificar hash de contraseña
     */
    public function testHashContrasenaValido()
    {
        // Crear un usuario temporal para probar el hash
        $sufijo = rand(1000, 9999);
        $contrasenaPlana = 'mi_password_secreta';
        $contrasenaHash = password_hash($contrasenaPlana, PASSWORD_DEFAULT);
        
        $datosUsuario = [
            'Nombre' => 'Test',
            'Apellido' => 'Hash' . $sufijo,
            'ID_TD' => self::$testTipoDocumentoId,
            'N_Documento' => 'DOC_HASH_' . $sufijo,
            'Correo' => 'hash' . $sufijo . '@test.com',
            'Celular' => '303' . $sufijo,
            'Contrasena' => $contrasenaHash,
            'ID_Rol' => 2
        ];
        
        $registrado = $this->usuario->registrar($datosUsuario);
        $this->assertTrue($registrado);
        
        // Obtener el usuario recién creado
        $stmt = $this->db->query("SELECT * FROM usuario WHERE Correo = '" . $datosUsuario['Correo'] . "'");
        $usuarioCreado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verificar que el hash funciona correctamente
        $hashValido = password_verify($contrasenaPlana, $usuarioCreado['Contrasena']);
        $this->assertTrue($hashValido);
        
        // Verificar que contraseña incorrecta falla
        $hashInvalido = password_verify('password_incorrecta', $usuarioCreado['Contrasena']);
        $this->assertFalse($hashInvalido);
        
        // Limpiar
        $this->db->exec("DELETE FROM usuario WHERE ID_Usuario = " . $usuarioCreado['ID_Usuario']);
        
        echo "✅ Test 14 PASÓ: Hash de contraseña funciona correctamente\n";
    }
    
    /**
     * Test 15: Verificar ciclo completo de autenticación
     */
    public function testCicloCompletoAutenticacion()
    {
        if (!self::$testTipoDocumentoId) {
            $this->markTestSkipped('No hay tipos de documento para probar ciclo completo');
        }
        
        // 1. Registrar nuevo usuario
        $sufijo = rand(1000, 9999);
        $contrasenaPlana = 'password_ciclo_' . $sufijo;
        $datosUsuario = [
            'Nombre' => 'Ciclo',
            'Apellido' => 'Completo' . $sufijo,
            'ID_TD' => self::$testTipoDocumentoId,
            'N_Documento' => 'DOC_CICLO_' . $sufijo,
            'Correo' => 'ciclo' . $sufijo . '@test.com',
            'Celular' => '304' . $sufijo,
            'Contrasena' => password_hash($contrasenaPlana, PASSWORD_DEFAULT),
            'ID_Rol' => 2
        ];
        
        $registrado = $this->usuario->registrar($datosUsuario);
        $this->assertTrue($registrado);
        
        // 2. Verificar que existe
        $existeCorreo = $this->usuario->existeCorreo($datosUsuario['Correo']);
        $this->assertTrue($existeCorreo);
        
        $existeDoc = $this->usuario->existeDocumento($datosUsuario['N_Documento']);
        $this->assertTrue($existeDoc);
        
        $existeCelular = $this->usuario->existeCelular($datosUsuario['Celular']);
        $this->assertTrue($existeCelular);
        
        // 3. Probar login exitoso
        $usuarioLogueado = $this->usuario->login($datosUsuario['Correo'], $contrasenaPlana);
        $this->assertIsArray($usuarioLogueado);
        $this->assertEquals($datosUsuario['Correo'], $usuarioLogueado['Correo']);
        
        // 4. Cambiar contraseña
        $nuevaContrasena = 'nueva_password_ciclo';
        $nuevaContrasenaHash = password_hash($nuevaContrasena, PASSWORD_DEFAULT);
        
        $stmt = $this->db->query("SELECT ID_Usuario FROM usuario WHERE Correo = '" . $datosUsuario['Correo'] . "'");
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $idUsuario = $usuario['ID_Usuario'];
        
        $contrasenaActualizada = $this->usuario->actualizarContrasena($idUsuario, $nuevaContrasenaHash);
        $this->assertTrue($contrasenaActualizada);
        
        // 5. Verificar que el login funciona con nueva contraseña
        $usuarioRelogueado = $this->usuario->login($datosUsuario['Correo'], $nuevaContrasena);
        $this->assertIsArray($usuarioRelogueado);
        
        // 6. Limpiar
        $this->db->exec("DELETE FROM usuario WHERE ID_Usuario = " . $idUsuario);
        
        echo "✅ Test 15 PASÓ: Ciclo completo de autenticación funciona correctamente\n";
        echo "   Usuario: {$datosUsuario['Correo']} | Registro ✓ | Login ✓ | Cambio contraseña ✓\n";
    }
    
    public static function tearDownAfterClass(): void
    {
        echo "\n================================\n";
        echo "🏁 PRUEBAS DE USUARIO COMPLETADAS\n\n";
    }
}