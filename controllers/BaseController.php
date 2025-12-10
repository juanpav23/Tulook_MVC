<?php
// controllers/BaseController.php

class BaseController {
    protected $db;

    public function __construct($db = null) {
        $this->db = $db;
    }

    // Método index por defecto para todos los controladores
    public function index() {
        // Redirigir a página principal por defecto
        header("Location: " . BASE_URL);
        exit;
    }

    // Método para mostrar errores
    protected function mostrarError($mensaje) {
        $_SESSION['error_message'] = $mensaje;
        header("Location: " . BASE_URL);
        exit;
    }

    // Verificar si usuario está logueado
    protected function verificarAutenticacion() {
        if (!isset($_SESSION['usuario'])) {
            header("Location: " . BASE_URL . "?c=Usuario&a=login");
            exit;
        }
    }

    // Verificar si usuario es administrador
    protected function verificarAdmin() {
        $this->verificarAutenticacion();
        if ($_SESSION['rol'] != 1) { // Asumiendo que 1 es admin
            $this->mostrarError("No tienes permisos para acceder a esta página");
        }
    }
}