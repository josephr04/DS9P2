<?php
session_start();

ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

try {
    $configPath = __DIR__ . '/../config/conexion.php';

    if (!file_exists($configPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'Error de configuración: No se encontró conexion.php en ' . $configPath
        ]);
        exit;
    }

    require_once $configPath;

    $action = $_POST['action'] ?? '';

    // ══════════════════════════════════════════
    //  LOGIN
    // ══════════════════════════════════════════
    if ($action === 'login') {
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'El correo y la contraseña son obligatorios.']);
            exit;
        }

        $stmt = $conexion->prepare(
            "SELECT idUsuario, nombreUsuario, correo, contrasen, rolUsuario 
             FROM usuarios 
             WHERE correo = ? 
             LIMIT 1"
        );
        $stmt->execute([$email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
            exit;
        }

        // Soporte para contraseñas con password_hash Y texto plano (migración gradual)
        $passwordValida = false;
        if (password_get_info($usuario['contrasen'])['algo'] !== null) {
            // La contraseña está hasheada con password_hash
            $passwordValida = password_verify($password, $usuario['contrasen']);
        } else {
            // Contraseña en texto plano (legacy) — comparación directa
            $passwordValida = ($password === $usuario['contrasen']);

            // Opcional: re-hashear automáticamente al hacer login exitoso
            if ($passwordValida) {
                $nuevoHash = password_hash($password, PASSWORD_BCRYPT);
                $update = $conexion->prepare("UPDATE usuarios SET contrasen = ? WHERE idUsuario = ?");
                $update->execute([$nuevoHash, $usuario['idUsuario']]);
            }
        }

        if (!$passwordValida) {
            echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
            exit;
        }

        // Sesión
        $_SESSION['idUsuario']     = $usuario['idUsuario'];
        $_SESSION['nombreUsuario'] = $usuario['nombreUsuario'];
        $_SESSION['correo']        = $usuario['correo'];
        $_SESSION['rolUsuario']    = $usuario['rolUsuario'];
        $_SESSION['loggedin']      = true;

        // Cookies "recordarme"
        if (!empty($_POST['remember'])) {
            setcookie('nombreUsuario', $usuario['nombreUsuario'], time() + 86400 * 30, "/", "", false, true);
            setcookie('correo',        $usuario['correo'],        time() + 86400 * 30, "/", "", false, true);
        }

        // Redirección según rol: 0 = admin, 1 = usuario normal
        $redirect = ($usuario['rolUsuario'] == 0)
            ? '../views/admin/dashboard.php'
            : '../views/datosPersonales.php';

        echo json_encode([
            'success'  => true,
            'message'  => 'Inicio de sesión exitoso.',
            'redirect' => $redirect
        ]);
        exit;
    }

    // ══════════════════════════════════════════
    //  REGISTRO
    // ══════════════════════════════════════════
    if ($action === 'register') {
        $username         = trim($_POST['username']         ?? '');
        $email            = trim($_POST['email']            ?? '');
        $password         = trim($_POST['password']         ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico no es válido.']);
            exit;
        }

        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden.']);
            exit;
        }

        if (strlen($password) < 6) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.']);
            exit;
        }

        // Verificar correo duplicado
        $stmt = $conexion->prepare("SELECT idUsuario FROM usuarios WHERE correo = ? LIMIT 1");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']);
            exit;
        }

        // Verificar username duplicado
        $stmt = $conexion->prepare("SELECT idUsuario FROM usuarios WHERE nombreUsuario = ? LIMIT 1");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe.']);
            exit;
        }

        // Guardar con hash seguro
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conexion->prepare(
            "INSERT INTO usuarios (nombreUsuario, correo, contrasen, rolUsuario) VALUES (?, ?, ?, 1)"
        );
        $stmt->execute([$username, $email, $hashedPassword]);
        
        // Obtener el ID del usuario recién creado
        $nuevoIdUsuario = $conexion->lastInsertId();

        // Iniciar sesión automáticamente
        $_SESSION['idUsuario']     = $nuevoIdUsuario;
        $_SESSION['nombreUsuario'] = $username;
        $_SESSION['correo']        = $email;
        $_SESSION['rolUsuario']    = 1;
        $_SESSION['loggedin']      = true;

        // Redirigir a datos personales para completar el perfil
        echo json_encode([
            'success'  => true,
            'message'  => 'Registro exitoso. Completa tu perfil.',
            'redirect' => '../views/user/datosPersonales.php'
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no válida.']);
    exit;

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ]);
    exit;
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error inesperado: ' . $e->getMessage()
    ]);
    exit;
}