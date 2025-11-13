<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuLook - Login</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/Basta.css">
</head>
<body>
    <main>
        <div class="contenedor__todo">
            <div class="caja__trasera">
                <div class="caja__trasera-login">
                    <h3>¿Ya tienes una cuenta?</h3>
                    <p>Inicia sesión para entrar en la página</p>
                    <button id="btn_iniciar_sesion">Iniciar sesión</button>
                </div>
                <div class="caja__trasera_register">
                    <h3>¿Aún no tienes una cuenta?</h3>
                    <p>Regístrate para que puedas iniciar sesión</p>
                    <button id="btn__registrarse">Registrarse</button>
                </div>
            </div>

            <div class="contenedor__login_register">
                <!-- LOGIN -->
                <form action="<?php echo BASE_URL . '?c=Usuario&a=login'; ?>" method="POST" class="formulario__login">
                    <h2>Iniciar sesión</h2>
                    <?php if (isset($_SESSION['error_login'])): ?>
                        <p style="color:red; text-align:center;"><?php echo $_SESSION['error_login']; unset($_SESSION['error_login']); ?></p>
                    <?php endif; ?>
                    <input type="email" placeholder="Correo electrónico" name="Correo" required maxlength="50">
                    <input type="password" placeholder="Contraseña" name="Contrasena" required minlength="12" maxlength="20">
                    <button type="submit">Entrar</button>
                </form>

                <!-- REGISTRO -->
                <form action="<?php echo BASE_URL . '?c=Usuario&a=registrar'; ?>" method="POST" class="formulario__register">
                    <h2>Registrarse</h2>
                    <?php if (isset($_SESSION['error_registro'])): ?>
                        <p style="color:red; text-align:center;"><?php echo $_SESSION['error_registro']; unset($_SESSION['error_registro']); ?></p>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['success_registro'])): ?>
                        <p style="color:green; text-align:center;"><?php echo $_SESSION['success_registro']; unset($_SESSION['success_registro']); ?></p>
                    <?php endif; ?>

                    <input type="text" placeholder="Nombre completo" name="Nombre_Completo" required minlength="10" maxlength="100">

                    <select name="ID_TD" class="form-select" required>
                        <option value="" disabled selected>Seleccione el tipo de documento</option>
                        <?php foreach ($tipo_docs as $doc): ?>
                            <option value="<?php echo $doc['ID_TD']; ?>"><?php echo $doc['Documento']; ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="text" placeholder="Número de Documento" name="N_Documento" required pattern="[0-9]{7,12}" maxlength="12">
                    <input type="text" placeholder="Celular" name="Celular" required pattern="[0-9]{10}" maxlength="10">
                    <input type="email" placeholder="Correo Electrónico" name="Correo" required maxlength="100">
                    <input type="password" placeholder="Contraseña" name="Contrasena" required minlength="12" maxlength="20">
                    <input type="password" placeholder="Confirmar Contraseña" name="Confirmar_Contrasena" required maxlength="20">
                    <button type="submit">Registrarse</button>
                </form>
            </div>
        </div>
    </main>
    <script src="<?php echo BASE_URL; ?>assets/js/script.js"></script>
</body>
</html>

