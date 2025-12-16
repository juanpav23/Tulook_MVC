<?php
// prueba_criticos.php
echo "=== VERIFICACIÓN PROBLEMAS CRÍTICOS ===\n\n";

// 1. Problemas de seguridad - PASSWORD_DEFAULT
echo "1. 🔴 PROBLEMAS SEGURIDAD CRÍTICOS:\n";
$archivos_problema = [
    'controllers/AdminController.php',
    'models/UsuarioAdmin.php'
];

foreach($archivos_problema as $archivo) {
    if(file_exists($archivo)) {
        $content = file_get_contents($archivo);
        if(strpos($content, 'PASSWORD_DEFAULT') !== false) {
            echo "   ❌ $archivo: USA PASSWORD_DEFAULT (INSEGURO)\n";
            
            // Contar ocurrencias
            $ocurrencias = substr_count($content, 'PASSWORD_DEFAULT');
            echo "      📍 $ocurrencias ocurrencias encontradas\n";
        } else {
            echo "   ✅ $archivo: NO USA PASSWORD_DEFAULT\n";
        }
    }
}

// 2. Verificar si Usuario.php tiene encriptación
echo "\n2. 🔒 Usuario.php - Encriptación:\n";
if(file_exists('models/Usuario.php')) {
    $content = file_get_contents('models/Usuario.php');
    
    if(strpos($content, 'password_hash') !== false) {
        echo "   ✅ password_hash: IMPLEMENTADO\n";
    } else {
        echo "   ❌ password_hash: NO IMPLEMENTADO\n";
    }
    
    if(strpos($content, 'password_verify') !== false) {
        echo "   ✅ password_verify: IMPLEMENTADO\n";
    } else {
        echo "   ❌ password_verify: NO IMPLEMENTADO\n";
    }
    
    if(strpos($content, 'PASSWORD_BCRYPT') !== false) {
        echo "   ✅ ALGORITMO: PASSWORD_BCRYPT (SEGURO)\n";
    } else {
        echo "   ❌ ALGORITMO: NO USA PASSWORD_BCRYPT\n";
    }
}

// 3. Verificar persistencia carrito
echo "\n3. 💾 Carrito - Persistencia:\n";
if(file_exists('controllers/CarritoController.php')) {
    $content = file_get_contents('controllers/CarritoController.php');
    
    // Buscar uso de sesión vs BD
    $uso_sesion = substr_count($content, '$_SESSION');
    $uso_bd = substr_count($content, 'INSERT') + substr_count($content, 'UPDATE') + substr_count($content, 'SELECT');
    
    echo "   📊 Sesiones usadas: $uso_sesion veces\n";
    echo "   📊 Consultas BD: $uso_bd veces\n";
    
    if($uso_sesion > 2 && $uso_bd < 2) {
        echo "   ❌ PERSISTENCIA: SOLO SESIÓN (SE PIERDEN DATOS)\n";
    } elseif($uso_bd > 2) {
        echo "   ✅ PERSISTENCIA: USA BASE DE DATOS\n";
    } else {
        echo "   ⚠️ PERSISTENCIA: MIXTA O INDETERMINADA\n";
    }
}

// 4. Verificar CRUD ProductoController
echo "\n4. 🛠️ ProductoController - CRUD Completo:\n";
if(file_exists('controllers/ProductoController.php')) {
    $content = file_get_contents('controllers/ProductoController.php');
    
    $metodos_crud = [
        'index' => 'Listar',
        'crear' => 'Crear', 
        'guardar' => 'Guardar',
        'editar' => 'Editar',
        'actualizar' => 'Actualizar',
        'eliminar' => 'Eliminar'
    ];
    
    $encontrados = 0;
    foreach($metodos_crud as $metodo => $desc) {
        if(strpos($content, "function $metodo") !== false) {
            echo "   ✅ $desc ($metodo): EXISTE\n";
            $encontrados++;
        } else {
            echo "   ❌ $desc ($metodo): NO EXISTE\n";
        }
    }
    echo "   📊 CRUD Completo: $encontrados/6 métodos\n";
}

echo "\n=== RESUMEN PROBLEMAS CRÍTICOS ===\n";
echo "Se necesitan correcciones inmediatas en los items marcados con ❌\n";
?>