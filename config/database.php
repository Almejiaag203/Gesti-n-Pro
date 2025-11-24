<?php
// Iniciar la sesión aquí, como único punto de entrada.
session_start();

// --- CONFIGURACIÓN GLOBAL DE LA APLICACIÓN ---
$config = [
    'moneda' => '$',
    'nombre_tienda' => 'GestiónPRO'
];
function getConfig($key) {
    global $config;
    return $config[$key] ?? null;
}
function getMoneda() {
    if (isset($_SESSION['moneda_usuario']) && !empty($_SESSION['moneda_usuario'])) {
        return $_SESSION['moneda_usuario'];
    }
    return getConfig('moneda');
}
// --- FIN DE LA CONFIGURACIÓN GLOBAL ---

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tienda_sistema');

$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conexion->connect_error) {
    die("Error de Conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8");

function redirigir($url) {
    header("Location: " . $url);
    exit();
}
function tienePermiso($permiso) {
    if (isset($_SESSION['permisos']) && in_array($permiso, $_SESSION['permisos'])) {
        return true;
    }
    return false;
}