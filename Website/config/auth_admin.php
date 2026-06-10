<?php
// config/auth_admin.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificar primero si está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php');
    exit;
}

// 2. Verificar inactividad (5 minutos)
if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > 300) {
        $_SESSION = array();
        session_destroy();
        header('Location: ../user/login.php?error=timeout');
        exit;
    }
}
$_SESSION['ultimo_acceso'] = time();

// 3. Filtro de Rol Estricto para el Administrador
// SI NO ES ROL 0 (Administrador), lo expulsamos de la zona de administración
if ($_SESSION['user_role'] != 0) {
    // Si es un usuario común (Rol 1), lo mandamos a su respectivo dashboard
    if ($_SESSION['user_role'] == 1) {
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: ../user/login.php');
    }
    exit;
}

// Si llegó aquí, es un Admin autenticado. Creamos sus variables seguras:
$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$userRole = $_SESSION['user_role'];
?>