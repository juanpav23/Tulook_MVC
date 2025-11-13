<div class="container mt-5">
    <h2>Mi Perfil</h2>
    <table class="table">
        <tr><th>Nombre</th><td><?php echo $usuario['Nombre_Completo']; ?></td></tr>
        <tr><th>Correo</th><td><?php echo $usuario['Correo']; ?></td></tr>
        <tr><th>Documento</th><td><?php echo $usuario['Documento']; ?> - <?php echo $usuario['N_Documento']; ?></td></tr>
        <tr><th>Celular</th><td><?php echo $usuario['Celular']; ?></td></tr>
    </table>
</div>
