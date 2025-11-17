// script.js - Actualizado para mantener la funcionalidad de deslizamiento
// Agregando los eventos de los botones
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si los elementos existen antes de agregar event listeners
    const btnIniciarSesion = document.getElementById("btn_iniciar_sesion");
    const btnRegistrarse = document.getElementById("btn__registrarse");
    
    if (btnIniciarSesion) {
        btnIniciarSesion.addEventListener("click", iniciarSesion);
    }
    
    if (btnRegistrarse) {
        btnRegistrarse.addEventListener("click", register);
    }
    
    window.addEventListener("resize", anchoPagina);
    
    // Ejecutar anchoPagina al cargar
    anchoPagina();
});

// Declaración de variables
var contenedor__login_register = document.querySelector(".contenedor__login_register");
var formulario_login = document.querySelector(".formulario__login");
var formulario_register = document.querySelector(".formulario__register");
var caja__trasera_login = document.querySelector(".caja__trasera-login");
var caja__trasera_register = document.querySelector(".caja__trasera_register");

function anchoPagina(){
    if(window.innerWidth > 850){
        caja__trasera_login.style.display = "block";
        caja__trasera_register.style.display = "block";
    }else{
        caja__trasera_register.style.display = "block";
        caja__trasera_register.style.opacity = "1";
        caja__trasera_login.style.display = "none";
        formulario_login.style.display = "block";
        formulario_register.style.display = "none";
        contenedor__login_register.style.left = "0px";
    }
}

function iniciarSesion() {
    if (window.innerWidth > 850) {
        formulario_register.style.display = "none";
        contenedor__login_register.style.left = "-25px";
        formulario_login.style.display = "block";
        caja__trasera_register.style.opacity = "1";
        caja__trasera_login.style.opacity = "0";
    } else {
        formulario_register.style.display = "none";
        contenedor__login_register.style.left = "0px";
        formulario_login.style.display = "block";
        caja__trasera_register.style.display = "block";
        caja__trasera_login.style.display = "none";
    }
}

function register() {
    if (window.innerWidth > 850) {
        formulario_register.style.display = "block";
        contenedor__login_register.style.left = "410px";
        formulario_login.style.display = "none";
        caja__trasera_register.style.opacity = "0";
        caja__trasera_login.style.opacity = "1";
    } else {
        formulario_register.style.display = "block";
        contenedor__login_register.style.left = "0px";
        formulario_login.style.display = "none";
        caja__trasera_register.style.display = "none";
        caja__trasera_login.style.display = "block";
        caja__trasera_login.style.opacity = "1";
    }
}

// Función para mostrar/ocultar contraseña con Bootstrap Icons
function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const button = passwordInput.nextElementSibling;
    const icon = button.querySelector('i');
    const isPassword = passwordInput.type === 'password';
    
    // Cambiar el tipo de input
    passwordInput.type = isPassword ? 'text' : 'password';
    
    // Cambiar el ícono
    if (isPassword) {
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Función para limpiar formulario de login (mantenida por compatibilidad)
function limpiarFormularioLogin() {
    const form = document.querySelector('.formulario__login');
    if (form) {
        form.reset();
        
        // Limpiar mensajes de error y éxito
        const errorMensaje = document.getElementById('error-mensaje-login');
        const successMensaje = document.getElementById('success-mensaje-login');
        
        if (errorMensaje) {
            errorMensaje.style.display = 'none';
        }
        if (successMensaje) {
            successMensaje.style.display = 'none';
        }
    }
}

// Función para limpiar formulario de registro (mantenida por compatibilidad)
function limpiarFormularioRegistro() {
    const form = document.querySelector('.formulario__register');
    if (form) {
        form.reset();
        
        // Limpiar mensajes de error
        const errorMensaje = document.getElementById('error-mensaje-registro');
        if (errorMensaje) {
            errorMensaje.style.display = 'none';
        }
    }
}

// Validación de contraseñas
function validarContrasenas() {
    const contrasena = document.getElementById("contrasena").value;
    const confirmar = document.getElementById("confirmar_contrasena").value;

    if (contrasena !== confirmar) {
        alert("Las contraseñas no coinciden. Por favor, vuelve a intentarlo.");
        return false; // Evita el envío del formulario
    }
    return true; // Permite el envío
}