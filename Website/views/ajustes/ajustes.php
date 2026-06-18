<?php
if (session_status() === PHP_SESSION_NONE) session_start();
// Detecta rol y valida sesión según quien entra
$rol = $_SESSION['user_role'] ?? '';

if ($rol === 0) {
    require_once '../../config/auth_admin.php';
} elseif ($rol === 1) {
    require_once '../../config/auth_user.php';
} else {
    header('Location: ../../views/user/login.php');
    exit;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajustes - Panel Administrador</title>
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
.page-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

/* Header */
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

/* Content */
.page-content { flex: 1; overflow-y: auto; padding: 1.5rem; }
.content-wrapper { max-width: 680px; margin: 0 auto; }

/* Section label */
.section-label {
    font-size: .72rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--secondary-color);
    margin-bottom: .6rem;
    padding-left: .2rem;
}

/* Settings card */
.settings-card {
    background: var(--white);
    border-radius: 14px;
    border: 1px solid var(--border-color);
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
    overflow: hidden;
    margin-bottom: 1.2rem;
}
.settings-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.2rem;
    text-decoration: none;
    color: inherit;
    transition: background .15s;
    cursor: pointer;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
}
.settings-item:hover { background: var(--light-bg); }
.settings-item + .settings-item { border-top: 1px solid var(--border-color); }

.settings-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: var(--primary-light);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.settings-icon.red { background: #fde8e8; color: var(--danger-color); }

.settings-text { flex: 1; }
.settings-title { font-size: .92rem; font-weight: 600; color: var(--dark-text); }
.settings-desc  { font-size: .78rem; color: var(--secondary-color); margin-top: .1rem; }

.settings-arrow { color: #c0c6d4; font-size: .85rem; }

/* User info card */
.user-info-card {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    border-radius: 14px;
    padding: 1.4rem 1.5rem;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 1.1rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 15px rgba(12,78,212,.25);
}
.user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
    border: 2px solid rgba(255,255,255,.4);
}
.user-name  { font-size: 1.05rem; font-weight: 700; }
.user-email { font-size: .8rem; opacity: .8; margin-top: .15rem; }
.user-role  {
    display: inline-block;
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    padding: .15rem .65rem;
    font-size: .72rem;
    font-weight: 600;
    margin-top: .4rem;
}

/* Logout button */
.btn-logout {
    width: 100%;
    padding: .85rem;
    border-radius: 10px;
    border: 2px solid var(--danger-color);
    background: transparent;
    color: var(--danger-color);
    font-size: .92rem;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .6rem;
    transition: background .2s, color .2s;
    text-decoration: none;
}
.btn-logout:hover { background: var(--danger-color); color: #fff; }
</style>
</head>
<body>

<?php
// ✅ Solo aquí, una vez, después del <body>
if ($rol === '0') {
    include '../../header/sidebar_admin.php';
} else {
    include '../../header/sidebar.php';
}
?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-cog"></i> Ajustes</h1>
            <p>Administra tu cuenta y preferencias de seguridad</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active">Ajustes</li>
            </ol>
        </nav>
    </div>

    <div class="page-content">
        <div class="content-wrapper">

            <!-- Info del usuario -->
            <div class="user-info-card">
                <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
                <div>
                    <div class="user-name"><?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Administrador') ?></div>
                    <div class="user-email"><?= htmlspecialchars($_SESSION['correo_usuario'] ?? '') ?></div>
                    <span class="user-role">Panel Administrador</span>
                </div>
            </div>

            <!-- Sección Seguridad -->
            <div class="section-label">Seguridad</div>
            <div class="settings-card">
                <a href="cambiar_usuario.php" class="settings-item">
                    <div class="settings-icon"><i class="fas fa-user-edit"></i></div>
                    <div class="settings-text">
                        <div class="settings-title">Cambiar Nombre de Usuario</div>
                        <div class="settings-desc">Actualiza tu identificador de acceso</div>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </a>
                <a href="cambiar_correo.php" class="settings-item">
                    <div class="settings-icon"><i class="fas fa-envelope"></i></div>
                    <div class="settings-text">
                        <div class="settings-title">Cambiar Correo Electrónico</div>
                        <div class="settings-desc">Actualiza tu dirección de correo principal</div>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </a>
                <a href="cambiar_contrasena.php" class="settings-item">
                    <div class="settings-icon"><i class="fas fa-lock"></i></div>
                    <div class="settings-text">
                        <div class="settings-title">Cambiar Contraseña</div>
                        <div class="settings-desc">Protege tu cuenta actualizando tu clave de acceso</div>
                    </div>
                    <i class="fas fa-chevron-right settings-arrow"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>