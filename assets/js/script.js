// Agregando los eventos de los botones
document.getElementById("btn_iniciar_sesion").addEventListener("click", iniciarSesion);
document.getElementById("btn__registrarse").addEventListener("click", register);
window.addEventListener("resize", anchoPagina);

// Declaración de variables
var contenedor__login_register = document.querySelector(".contenedor__login_register");
var formulario_login = document.querySelector(".formulario__login");
var formulario_register = document.querySelector(".formulario__register"); // Corrección de nombre
var caja__trasera_login = document.querySelector(".caja__trasera-login");
var caja__trasera_register = document.querySelector(".caja__trasera-register");

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
        document.querySelector('.formulario__register').style.display = "none";
        document.querySelector('.contenedor__login_register').style.left = "-25px";
        document.querySelector('.formulario__login').style.display = "block";
        document.querySelector('.caja__trasera-register').style.opacity = "1";
        document.querySelector('.caja__trasera-login').style.opacity = "0";
    } else {
        document.querySelector('.formulario__register').style.display = "none";
        document.querySelector('.contenedor__login_register').style.left = "0px";
        document.querySelector('.formulario__login').style.display = "block";
        document.querySelector('.caja__trasera-register').style.display = "block";
        document.querySelector('.caja__trasera-login').style.display = "none";
    }
}
function register() {
    if (window.innerWidth > 850) {
        document.querySelector('.formulario__register').style.display = "block";
        document.querySelector('.contenedor__login_register').style.left = "410px";
        document.querySelector('.formulario__login').style.display = "none";
        document.querySelector('.caja__trasera-register').style.opacity = "0";
        document.querySelector('.caja__trasera-login').style.opacity = "1";
    } else {
        document.querySelector('.formulario__register').style.display = "block";
        document.querySelector('.contenedor__login_register').style.left = "0px";
        document.querySelector('.formulario__login').style.display = "none";
        document.querySelector('.caja__trasera-register').style.display = "none";
        document.querySelector('.caja__trasera-login').style.display = "block";
        document.querySelector('.caja__trasera-login').style.opacity = "1";
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

