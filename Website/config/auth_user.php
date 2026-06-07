<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar inactividad (5 minutos)
if (isset($_SESSION['ultimo_acceso'])) {
    $inactivo = time() - $_SESSION['ultimo_acceso'];
    if ($inactivo > 300) {
        session_destroy();
        header('Location: ../user/login.php');
        exit;
    }
}
$_SESSION['ultimo_acceso'] = time();

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/login.php');
    exit;
}

// Verificar que sea usuario normal (rol 1)
if ($_SESSION['user_role'] != 1) {
    header('Location: ../admin/dashboard.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];
$userRole = $_SESSION['user_role'];