<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destruimos todos los datos de la sesión
$_SESSION = [];

// Destruimos la cookie de sesión si existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruimos la sesión del servidor
session_destroy();

// Redirigimos al login
header('Location: login.php');
exit;
?>