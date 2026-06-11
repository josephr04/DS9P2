<?php
// 1. Inicializamos el sistema de sesiones en el servidor
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
define('BASE_URL', '/ds9p2/Website/views/user/');

// 2. Forzamos a que la respuesta del script sea estrictamente JSON en formato UTF-8
header('Content-Type: application/json; charset=utf-8');

// 3. Importamos tu archivo de conexión
require_once '../config/conexion.php';

// 4. Evaluamos que la petición sea estrictamente por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if (!isset($conexion)) {
        echo json_encode(['success' => false, 'message' => 'Error de configuración: Variable de conexión no disponible.']);
        exit;
    }

    // =========================================================================
    // PROCESO DE INICIO DE SESIÓN (LOGIN)
    // =========================================================================
    if ($action === 'login') {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            exit;
        }

        try {
            $query = $conexion->prepare("SELECT idUsuario, rolUsuario, nombreUsuario, contrasen, correo FROM usuarios WHERE correo = :correo LIMIT 1");
            $query->execute([':correo' => $email]);
            $user = $query->fetch();

            // Comparamos contraseña en texto plano (entorno de práctica)
            if ($user && hash('sha256', $password) === $user['contrasen']) {
                $_SESSION['user_id']   = $user['idUsuario'];
                $_SESSION['idUsuario']    = $user['idUsuario'];
                $_SESSION['username']  = $user['nombreUsuario'];
                $_SESSION['user_role'] = $user['rolUsuario'];
                $_SESSION['email']     = $user['correo'];

                // 0 = admin → panel de administración
                // 1 = usuario → panel de aspirante
                $redirect = $user['rolUsuario'] == 0
                    ? '/ds9p2/Website/views/admin/dashboard.php'
                    : BASE_URL . 'datosPersonales.php';

                echo json_encode([
                    'success'  => true,
                    'message'  => '¡Acceso concedido! Redirigiendo...',
                    'redirect' => $redirect
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'El correo o la contraseña son incorrectos.']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno en el servidor al procesar el inicio de sesión.']);
            exit;
        }
    }

    // =========================================================================
    // PROCESO DE REGISTRO DE NUEVOS USUARIOS
    // =========================================================================
    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            exit;
        }

        if ($password !== $confirm) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener mínimo 6 caracteres.']);
            exit;
        }

        try {
            // Verificamos que el correo no esté ya registrado
            $checkEmail = $conexion->prepare("SELECT idUsuario FROM usuarios WHERE correo = :correo LIMIT 1");
            $checkEmail->execute([':correo' => $email]);
            if ($checkEmail->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está registrado.']);
                exit;
            }

            // Todo usuario nuevo entra como rol 1 (usuario normal)
            // Para hacer admin a alguien, cambia manualmente a 0 en phpMyAdmin
            $insert = $conexion->prepare("INSERT INTO usuarios (rolUsuario, nombreUsuario, contrasen, correo) VALUES (:rol, :username, :password, :correo)");
            $result = $insert->execute([
                ':rol'      => 1,
                ':username' => $username,
                ':password' => hash('sha256', $password),
                ':correo'   => $email
            ]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Registro exitoso. Ya puedes iniciar sesión.'
                ]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo completar el registro en la base de datos.']);
                exit;
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al registrar los datos: ' . $e->getMessage()]);
            exit;
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de acceso no autorizado.']);
    exit;
}
