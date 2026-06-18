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
    $actual    = trim($_POST['contrasena_actual'] ?? '');
    $nueva     = trim($_POST['nueva_contrasena'] ?? '');
    $confirmar = trim($_POST['confirmar_contrasena'] ?? '');

    if (empty($actual)) {
        $errorMsg = 'Ingresa tu contraseña actual.';
    } elseif (empty($nueva)) {
        $errorMsg = 'Ingresa una nueva contraseña.';
    } elseif (strlen($nueva) < 6) {
        $errorMsg = 'La contraseña debe tener mínimo 6 caracteres.';
    } elseif ($nueva !== $confirmar) {
        $errorMsg = 'Las contraseñas no coinciden.';
    } elseif ($idUsuario === 0) {
        $errorMsg = 'Error: sesión no válida.';
    } else {
        $payload = json_encode([
            'contrasena_actual' => $actual,
            'nueva_contrasena'  => $nueva,
        ]);
        $ch = curl_init(API_BASE . "/usuarios/{$idUsuario}/cambiar-contrasena");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Accept: application/json'],
            CURLOPT_TIMEOUT        => 10,
        ]);
        $raw  = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200) {
            $successMsg = 'Contraseña actualizada correctamente.';
        } elseif ($code === 401) {
            $errorMsg = 'La contraseña actual es incorrecta.';
        } elseif ($code === 404) {
            $errorMsg = 'Usuario no encontrado.';
        } else {
            $errorMsg = 'Error al actualizar la contraseña. Intenta nuevamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cambiar Contraseña - Panel Administrador</title>
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

.form-group { margin-bottom: 1.1rem; }

.input-group-custom { position: relative; }
.input-group-custom .input-icon {
    position: absolute;
    left: .9rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9aa1b3;
    font-size: .9rem;
    pointer-events: none;
}
.input-group-custom input {
    width: 100%;
    padding: .75rem 2.8rem .75rem 2.4rem;
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
.toggle-pass {
    position: absolute;
    right: .85rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #9aa1b3;
    cursor: pointer;
    font-size: .9rem;
    padding: 0;
    line-height: 1;
}
.toggle-pass:hover { color: var(--primary-color); }

/* Requirements */
.requirements-box {
    background: var(--light-bg);
    border-radius: 8px;
    padding: .75rem 1rem;
    margin-top: .8rem;
}
.req-title {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--secondary-color);
    margin-bottom: .4rem;
}
.req-item {
    font-size: .82rem;
    color: var(--secondary-color);
    display: flex;
    align-items: center;
    gap: .4rem;
    padding: .15rem 0;
}
.req-item i { color: var(--success-color); font-size: .75rem; }

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
            <h1><i class="fas fa-lock"></i> Cambiar Contraseña</h1>
            <p>Protege tu cuenta actualizando tu clave de acceso</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item"><a href="ajustes.php">Ajustes</a></li>
                <li class="breadcrumb-item active">Cambiar Contraseña</li>
            </ol>
        </nav>
    </div>

    <div class="page-content">
        <div class="content-wrapper">

            <div class="info-banner">
                <i class="fas fa-shield-halved me-1"></i>
                Ingresa tu contraseña actual y elige una nueva contraseña segura.
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
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="field-label" for="contrasena_actual">Contraseña Actual</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="contrasena_actual"
                                name="contrasena_actual"
                                placeholder="Ingresa tu contraseña actual"
                                autocomplete="current-password"
                            >
                            <button type="button" class="toggle-pass" onclick="togglePass('contrasena_actual', 'ico_actual')">
                                <i id="ico_actual" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="field-label" for="nueva_contrasena">Nueva Contraseña</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="nueva_contrasena"
                                name="nueva_contrasena"
                                placeholder="Ingresa tu nueva contraseña"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-pass" onclick="togglePass('nueva_contrasena', 'ico_nueva')">
                                <i id="ico_nueva" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="field-label" for="confirmar_contrasena">Confirmar Nueva Contraseña</label>
                        <div class="input-group-custom">
                            <i class="fas fa-lock input-icon"></i>
                            <input
                                type="password"
                                id="confirmar_contrasena"
                                name="confirmar_contrasena"
                                placeholder="Confirma tu nueva contraseña"
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-pass" onclick="togglePass('confirmar_contrasena', 'ico_confirmar')">
                                <i id="ico_confirmar" class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="requirements-box">
                        <div class="req-title">Requisitos de contraseña</div>
                        <div class="req-item"><i class="fas fa-check"></i> Mínimo 6 caracteres</div>
                    </div>

                    <div style="margin-top: 1.4rem">
                        <button type="submit" class="btn-primary-custom">
                            <i class="fas fa-lock"></i> Actualizar Contraseña
                        </button>
                        <a href="ajustes.php" class="btn-secondary-custom">Cancelar</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePass(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    }
}
</script>
</body>
</html>