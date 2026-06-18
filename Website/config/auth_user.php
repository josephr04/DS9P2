<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Primero comprobar que esté logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php');
    exit;
}

// 2. Comprobar inactividad (5 minutos) solo si ya está logueado
if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > 300) {
        $_SESSION = array(); // Limpia las variables de sesión
        session_destroy();   // Destruye la sesión en el servidor
        header('Location: ../user/login.php?error=timeout');
        exit;
    }
}
$_SESSION['ultimo_acceso'] = time();

// 3. Filtro de Rol: Esta página es EXCLUSIVA para Rol 1 (Usuarios)
$rol = isset($_SESSION['user_role']) ? (int)$_SESSION['user_role'] : null;

// Si el que intenta entrar es el Administrador (Rol 0), lo mandamos a su panel.
if ($rol === 0) {
    header('Location: ../admin/dashboard.php');
    exit;
}
// Si es cualquier otro rol no autorizado, ausente o dañado, lo mandamos al login
elseif ($rol !== 1) {
    header('Location: ../user/login.php');
    exit;
}

// Variables listas para usar de forma segura en las vistas del usuario
$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$userRole = $rol;
?>