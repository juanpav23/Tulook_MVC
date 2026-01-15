<?php
// test_sql.php - Colócalo en la raíz de tu proyecto
require_once "models/Database.php";

$database = new Database();
$db = $database->getConnection();

$id_factura = 1; // Cambia por un ID real que exista

// Prueba 1: Consulta ORIGINAL
echo "<h3>Prueba 1: Consulta ORIGINAL</h3>";
$sql1 = "SELECT f.ID_Factura, f.Fecha_Factura, f.Monto_Total, 
        mp.T_Pago AS Metodo_Pago,
        d.Direccion, d.Ciudad, d.Departamento
        FROM factura f
        LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
        LEFT JOIN direccion d ON f.ID_Direccion = d.ID_Direccion
        WHERE f.ID_Factura = ?";

$stmt1 = $db->prepare($sql1);
$stmt1->execute([$id_factura]);
$result1 = $stmt1->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($result1);
echo "</pre>";

// Prueba 2: Consulta MODIFICADA
echo "<h3>Prueba 2: Consulta MODIFICADA (con usuario)</h3>";
$sql2 = "SELECT f.ID_Factura, f.Fecha_Factura, f.Monto_Total, 
        mp.T_Pago AS Metodo_Pago,
        d.Direccion, d.Ciudad, d.Departamento,
        u.Nombre, u.Apellido, u.Correo
        FROM factura f
        LEFT JOIN metodo_pago mp ON f.ID_Metodo_Pago = mp.ID_Metodo_Pago
        LEFT JOIN direccion d ON f.ID_Direccion = d.ID_Direccion
        LEFT JOIN usuario u ON f.ID_Usuario = u.ID_Usuario
        WHERE f.ID_Factura = ?";

$stmt2 = $db->prepare($sql2);
$stmt2->execute([$id_factura]);
$result2 = $stmt2->fetch(PDO::FETCH_ASSOC);

echo "<pre>";
print_r($result2);
echo "</pre>";

// Verificar errores
if (!$result2) {
    echo "<h3 style='color:red'>ERROR en consulta 2:</h3>";
    echo "<pre>";
    print_r($stmt2->errorInfo());
    echo "</pre>";
}
?>