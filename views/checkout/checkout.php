<?php
if (!isset($_SESSION['ID_Usuario'])) {
    header("Location: " . BASE_URL . "?c=Usuario&a=login");
    exit;
}
$carrito = $_SESSION['carrito'] ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Checkout - TuLook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card-summary { border-left: 4px solid #212529; }
        .metodo-form { 
            display: none; 
            animation: fade .3s ease-in-out; 
            background: #f8f9fa;
            border-radius: 8px;
        }
        @keyframes fade {
            from { opacity: 0; transform: translateY(5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<!-- INCLUIR NAVBAR -->
<?php 
// Pasar variables necesarias al nav
$categorias = []; // O cargar desde tu modelo si es necesario
include "views/layout/nav.php"; 
?>

<div class="container my-5" style="margin-top: 100px !important;">
    <h2 class="mb-4">🛒 Finalizar compra</h2>

    <?php if (!empty($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?></div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>?c=Checkout&a=procesar" method="POST">

        <div class="row">

            <!-- ======================
                 SECCIÓN IZQUIERDA
            ======================= -->
            <div class="col-md-7">
                
                <!-- DIRECCIÓN -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Dirección de envío</strong></div>
                    <div class="card-body">

                        <?php if (empty($direcciones)): ?>
                            <div class="alert alert-warning">
                                No tienes direcciones guardadas. Agrega una para continuar.
                            </div>

                            <!-- FORMULARIO PARA AGREGAR DIRECCIÓN -->
                            <div class="p-3 border rounded bg-light">
                                <h6>Nueva dirección</h6>

                                <div class="mb-2">
                                    <label>Dirección</label>
                                    <input type="text" class="form-control" name="nueva_direccion" required>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <label>Ciudad</label>
                                        <input type="text" class="form-control" name="nueva_ciudad" required>
                                    </div>
                                    <div class="col">
                                        <label>Departamento</label>
                                        <input type="text" class="form-control" name="nueva_departamento" required>
                                    </div>
                                </div>

                                <div class="mt-2">
                                    <label>Código Postal</label>
                                    <input type="text" class="form-control" name="nueva_postal">
                                </div>

                                <input type="hidden" name="crear_direccion" value="1">
                            </div>

                        <?php else: ?>
                            <?php foreach ($direcciones as $i => $dir): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input"
                                        type="radio"
                                        name="direccion"
                                        value="<?= $dir['ID_Direccion'] ?>"
                                        <?= $i === 0 ? 'checked' : '' ?>>

                                    <label class="form-check-label">
                                        <?= $dir['Direccion'] ?> - <?= $dir['Ciudad'] ?> (<?= $dir['Departamento'] ?>)
                                    </label>
                                </div>
                            <?php endforeach; ?>

                            <a href="<?= BASE_URL ?>?c=Usuario&a=perfil" 
                            class="btn btn-outline-dark btn-sm mt-2">
                                Administrar direcciones
                            </a>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- MÉTODO DE PAGO -->
                <div class="card mb-4">
                    <div class="card-header"><strong>Método de pago</strong></div>
                    <div class="card-body">

                        <?php foreach ($metodos as $m): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input metodo-radio" type="radio" name="metodo_pago"
                                    value="<?= $m['T_Pago'] ?>" required>
                                <label class="form-check-label"><?= $m['T_Pago'] ?></label>
                            </div>
                        <?php endforeach; ?>

                        <!-- FORMULARIO TARJETA -->
                        <div id="form_tarjeta" class="metodo-form p-3 mt-3 border">
                            <h5>Pago con tarjeta</h5>

                            <div class="mb-2">
                                <label>Titular</label>
                                <input type="text" class="form-control" name="tarjeta_titular">
                            </div>

                            <div class="mb-2">
                                <label>Número de tarjeta</label>
                                <input type="text" maxlength="16" class="form-control" name="tarjeta_numero">
                            </div>

                            <div class="row">
                                <div class="col">
                                    <label>Expiración</label>
                                    <input type="month" class="form-control" name="tarjeta_expiracion">
                                </div>
                                <div class="col">
                                    <label>CVV</label>
                                    <input type="password" maxlength="3" class="form-control" name="tarjeta_cvv">
                                </div>
                            </div>
                        </div>

                        <!-- EFECTIVO -->
                        <div id="form_efectivo" class="metodo-form p-3 mt-3 border">
                            <h5>Pago en efectivo</h5>
                            <p class="text-muted">Pagarás en efectivo cuando recibas el producto.</p>
                        </div>

                        <!-- PSE -->
                        <div id="form_pse" class="metodo-form p-3 mt-3 border">
                            <h5>Pago por PSE</h5>

                            <div class="mb-2">
                                <label>Banco</label>
                                <select class="form-control" name="pse_banco">
                                    <option>Bancolombia</option>
                                    <option>Banco de Bogotá</option>
                                    <option>Davivienda</option>
                                    <option>BBVA</option>
                                    <option>Nequi</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label>Nombre del titular</label>
                                <input type="text" class="form-control" name="pse_titular">
                            </div>

                            <div class="row">
                                <div class="col">
                                    <label>Tipo documento</label>
                                    <select class="form-control" name="pse_tipo_doc">
                                        <option>CC</option>
                                        <option>TI</option>
                                        <option>CE</option>
                                    </select>
                                </div>
                                <div class="col">
                                    <label>N° documento</label>
                                    <input type="text" class="form-control" name="pse_documento">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>


            <!-- ======================
                 RESUMEN
            ======================= -->
            <div class="col-md-5">

                <div class="card card-summary mb-4">
                    <div class="card-header bg-dark text-white"><strong>Resumen de compra</strong></div>
                    <div class="card-body">

                        <?php
                        $total = 0;
                        foreach ($carrito as $item):
                            $subtotal = $item['Precio'] * $item['Cantidad'];
                            $total += $subtotal;
                        ?>
                        <div class="d-flex justify-content-between border-bottom py-2">
                            <div>
                                <strong><?= $item['N_Articulo'] ?></strong><br>
                                <small><?= $item['N_Color'] ?> | <?= $item['N_Talla'] ?></small><br>
                                <small>Cant: <?= $item['Cantidad'] ?></small>
                            </div>
                            <div class="text-end">
                                $<?= number_format($subtotal,0,',','.') ?>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-between mt-3">
                            <h5>Total:</h5>
                            <h5 class="text-success">$<?= number_format($total,0,',','.') ?></h5>
                        </div>

                        <button class="btn btn-dark w-100 mt-4">Pagar ahora</button>

                    </div>
                </div>

            </div>
        </div>

    </form>
</div>

<!-- INCLUIR BOOTSTRAP JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// MOSTRAR FORMULARIOS SEGÚN MÉTODO SELECCIONADO
document.querySelectorAll(".metodo-radio").forEach(radio => {
    radio.addEventListener("change", () => {
        document.getElementById("form_tarjeta").style.display = "none";
        document.getElementById("form_efectivo").style.display = "none";
        document.getElementById("form_pse").style.display = "none";

        switch (radio.value.toLowerCase()) {
            case "tarjeta":
                document.getElementById("form_tarjeta").style.display = "block";
                break;
            case "efectivo":
                document.getElementById("form_efectivo").style.display = "block";
                break;
            case "pse":
                document.getElementById("form_pse").style.display = "block";
                break;
        }
    });
});
</script>

</body>
</html>