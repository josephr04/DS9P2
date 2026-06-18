<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$rol = $_SESSION['user_role'] ?? '';

if ($rol == 0) {
    require_once '../../config/auth_admin.php';
} elseif ($rol == 1) {
    require_once '../../config/auth_user.php';
} else {
    header('Location: ../../views/user/login.php');
    exit;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

define('API_BASE', 'http://127.0.0.1:8000/api');
$idUsuario = $_SESSION['idUsuario'] ?? 0;

$successMsg = '';
$errorMsg   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevoCorreo    = trim($_POST['nuevo_correo'] ?? '');
    $confirmarCorreo = trim($_POST['confirmar_correo'] ?? '');

    if (empty($nuevoCorreo)) {
        $errorMsg = 'Ingresa un correo electrónico.';
    } elseif (!filter_var($nuevoCorreo, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Ingresa un correo electrónico válido.';
    } elseif (empty($confirmarCorreo)) {
        $errorMsg = 'Confirma tu nuevo correo electrónico.';
    } elseif ($nuevoCorreo !== $confirmarCorreo) {
        $errorMsg = 'Los correos electrónicos no coinciden.';
    } elseif ($idUsuario === 0) {
        $errorMsg = 'Error: sesión no válida.';
    } else {
        $payload = json_encode([
            'nuevo_correo'    => $nuevoCorreo,
            'confirmar_correo' => $confirmarCorreo,
        ]);
        $ch = curl_init(API_BASE . "/usuarios/{$idUsuario}/cambiar-correo");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            $_SESSION['email'] = $nuevoCorreo;
            $successMsg = 'Correo electrónico actualizado correctamente.';
        } elseif ($code === 409) {
            $errorMsg = 'El correo electrónico ya está en uso.';
        } elseif ($code === 404) {
            $errorMsg = 'Usuario no encontrado.';
        } elseif ($code === 401) {
            $errorMsg = 'Error de validación. Verifica los datos ingresados.';
        } else {
            $errorMsg = 'Error al actualizar el correo. Intenta nuevamente.';
        }
    }
}

$correoActual = $_SESSION['email'] ?? 'correo@ejemplo.com';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cambiar Correo - Panel Administrador</title>
<link rel="icon" type="image/png" href="../../assets/newwayslogo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../header/sidebar.css">
<style>
:root {
    --primary-color: #0c4ed4;
    --primary-dark: #0a3fb0;
    --primary-light: #e8effe;
    --secondary-color: #6c757d;
    --light-bg: #f0f2f7;
    --white: #fff;
    --dark-text: #2c3e50;
    --border-color: #e0e4ef;
    --success-color: #1f9d4d;
    --danger-color: #dc3545;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { height: 100%; }
body {
    display: flex !important;
    flex-direction: row !important;
    background: var(--light-bg);
    font-family: 'Segoe UI', sans-serif;
    height: 100vh;
}
.page-container { flex: 1; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

.page-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: #fff;
    padding: 1rem 1.8rem;
    box-shadow: 0 4px 15px rgba(0,0,0,.12);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.page-header h1 { font-size: 1.4rem; font-weight: 700; margin: 0; }
.page-header p  { font-size: .8rem; opacity: .85; margin: 0; }
.breadcrumb { background: transparent; padding: 0; margin: 0 0 0 auto; font-size: .75rem; }
.breadcrumb-item.active { color: rgba(255,255,255,.75); }
.breadcrumb-item a { color: #fff; text-decoration: none; font-weight: 500; }

.page-content { flex: 1; overflow-y: auto; padding: 1.5rem; }
.content-wrapper { max-width: 600px; margin: 0 auto; }

.info-banner {
    border-left: 4px solid var(--primary-color);
    background: var(--primary-light);
    border-radius: 0 10px 10px 0;
    padding: .85rem 1rem;
    margin-bottom: 1.5rem;
    font-size: .85rem;
    color: var(--dark-text);
}

.form-card {
    background: var(--white);
    border-radius: 14px;
    border: 1px solid var(--border-color);
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    padding: 1.6rem;
    margin-bottom: 1.2rem;
}

.field-label {
    font-size: .82rem;
    font-weight: 600;
    color: var(--secondary-color);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .4rem;
    display: block;
}
.field-readonly {
    background: var(--light-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: .7rem 1rem;
    font-size: .92rem;
    color: var(--dark-text);
    font-weight: 500;
    margin-bottom: 1.2rem;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.field-readonly i { color: var(--secondary-color); }

.form-group { margin-bottom: 1.1rem; }
.input-group-custom { position: relative; }
.input-group-custom .input-icon {
    position: absolute;
    left: .9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9aa1b3;
    font-size: .9rem;
}
.input-group-custom input {
    width: 100%;
    padding: .75rem 1rem .75rem 2.4rem;
    border: 1.5px solid var(--border-color);
    border-radius: 8px;
    font-size: .92rem;
    color: var(--dark-text);
    background: var(--white);
    transition: border-color .2s, box-shadow .2s;
    outline: none;
}
.input-group-custom input:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(12,78,212,.12);
}

.btn-primary-custom {
    width: 100%;
    padding: .85rem;
    background: var(--primary-color);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: .95rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
    transition: background .2s, transform .1s;
    margin-bottom: .7rem;
}
.btn-primary-custom:hover { background: var(--primary-dark); transform: translateY(-1px); }

.btn-secondary-custom {
    width: 100%;
    padding: .82rem;
    background: transparent;
    color: var(--secondary-color);
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    font-size: .92rem;
    font-weight: 500;
    cursor: pointer;
    text-align: center;
    text-decoration: none;
    display: block;
    transition: background .2s, color .2s;
}
.btn-secondary-custom:hover { background: var(--light-bg); color: var(--dark-text); }

.alert-success-custom {
    background: #e3f6e8;
    border: 1px solid #a3d9b1;
    border-radius: 8px;
    padding: .75rem 1rem;
    font-size: .85rem;
    color: #155724;
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: 1rem;
}
.alert-error-custom {
    background: #fde8e8;
    border: 1px solid #f5c2c2;
    border-radius: 8px;
    padding: .75rem 1rem;
    font-size: .85rem;
    color: #842029;
    display: flex;
    align-items: center;
    gap: .5rem;
    margin-bottom: 1rem;
}
</style>
</head>
<body>

<!-- reemplaza el include fijo por este -->
<?php
if ($rol == 0) {
    include '../../header/sidebar_admin.php';
} else {
    include '../../header/sidebar.php';
}
?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-envelope"></i> Cambiar Correo</h1>
            <p>Actualiza tu dirección de correo principal</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="ajustes.php">Ajustes</a></li>
                <li class="breadcrumb-item active">Cambiar Correo</li>
            </ol>
        </nav>
    </div>

    <div class="page-content">
        <div class="content-wrapper">

            <div class="info-banner">
                <i class="fas fa-info-circle me-1"></i>
                Actualiza tu dirección de correo electrónico para recibir notificaciones, alertas de seguridad y comunicaciones importantes.
            </div>

            <?php if ($successMsg): ?>
            <div class="alert-success-custom">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars($successMsg) ?>
            </div>
            <?php endif; ?>

            <?php if ($errorMsg): ?>
            <div class="alert-error-custom">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errorMsg) ?>
            </div>
            <?php endif; ?>

            <div class="form-card">
                <label class="field-label">Correo Actual</label>
                <div class="field-readonly">
                    <i class="fas fa-envelope"></i>
                    <?= htmlspecialchars($correoActual) ?>
                </div>

                <form method="POST" action="">
                    <div class="form-group">
                        <label class="field-label" for="nuevo_correo">Nuevo Correo</label>
                        <div class="input-group-custom">
                            <i class="fas fa-envelope input-icon"></i>
                            <input
                                type="email"
                                id="nuevo_correo"
                                name="nuevo_correo"
                                placeholder="Ingresa tu nuevo correo electrónico"
                                value="<?= htmlspecialchars($_POST['nuevo_correo'] ?? '') ?>"
                                autocomplete="off"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="field-label" for="confirmar_correo">Confirmar Nuevo Correo</label>
                        <div class="input-group-custom">
                            <i class="fas fa-envelope input-icon"></i>
                            <input
                                type="email"
                                id="confirmar_correo"
                                name="confirmar_correo"
                                placeholder="Confirma tu nuevo correo electrónico"
                                value="<?= htmlspecialchars($_POST['confirmar_correo'] ?? '') ?>"
                                autocomplete="off"
                            >
                        </div>
                    </div>

                    <div style="margin-top: 1.4rem">
                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-check"></i> Guardar Cambios
                        </button>
                        <a href="ajustes.php" class="btn-secondary-custom">Cancelar</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>