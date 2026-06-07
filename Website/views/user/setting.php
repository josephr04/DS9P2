<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - CareerPort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo '../../header/sidebar.css'; ?>">
    <style>
        :root {
            --primary-color: #0c4ed4;
            --primary-dark: #0a3fb0;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-bg: #f0f2f7;
            --white: #ffffff;
            --dark-text: #2c3e50;
            --sidebar-width: 280px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body { height: 100%; width: 100%; }

        body {
            display: flex !important;
            flex-direction: row !important;
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0 !important;
            padding: 0 !important;
            height: 100vh;
        }

        .page-container {
            flex: 1 !important;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* ── HEADER ── */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            padding: 1rem 1.8rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            border-bottom: 2px solid rgba(255,255,255,0.12);
            position: relative;
            overflow: hidden;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -60%;
            right: -5%;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .header-text { position: relative; z-index: 1; }

        .dashboard-header h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .dashboard-header p {
            font-size: 0.8rem;
            opacity: 0.85;
            margin: 0;
        }

        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
            font-size: 0.75rem;
        }

        .breadcrumb-item.active { color: rgba(255,255,255,0.75); }
        .breadcrumb-item a { color: var(--white); text-decoration: none; font-weight: 500; }
        .breadcrumb-item a:hover { text-decoration: underline; }
        .breadcrumb-item + .breadcrumb-item::before { color: rgba(255,255,255,0.5); }

        /* ── CONTENIDO ── */
        .dashboard-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1.2rem 1.5rem;
        }

        .dashboard-content::-webkit-scrollbar { width: 6px; }
        .dashboard-content::-webkit-scrollbar-track { background: transparent; }
        .dashboard-content::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.18); border-radius: 4px; }

        /* ── TÍTULO DE SECCIÓN ── */
        .section-label {
            font-size: 0.72rem;
            color: var(--secondary-color);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            margin-bottom: 0.5rem;
            margin-top: 1.2rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        /* ── CARD CONTENEDOR ── */
        .settings-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 1px solid rgba(0,0,0,0.05);
            overflow: hidden;
            margin-bottom: 1.2rem;
        }

        /* ── FILA DE AJUSTE ── */
        .settings-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.2rem;
            cursor: pointer;
            transition: background 0.18s ease;
            border-bottom: 1px solid #f0f2f7;
            text-decoration: none;
            color: inherit;
        }

        .settings-row:last-child { border-bottom: none; }

        .settings-row:hover { background-color: rgba(12,78,212,0.03); }

        .settings-row-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(12,78,212,0.09);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--primary-color);
            flex-shrink: 0;
        }

        .settings-row-icon.danger {
            background: rgba(220,53,69,0.09);
            color: var(--danger-color);
        }

        .settings-row-icon.success {
            background: rgba(40,167,69,0.09);
            color: var(--success-color);
        }

        .settings-row-icon.warning {
            background: rgba(255,193,7,0.1);
            color: #b38600;
        }

        .settings-row-body { flex: 1; min-width: 0; }

        .settings-row-title {
            font-size: 0.92rem;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.1rem;
        }

        .settings-row-desc {
            font-size: 0.78rem;
            color: var(--secondary-color);
        }

        .settings-row-arrow {
            color: #c0c8d5;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* ── BOTÓN CERRAR SESIÓN ── */
        .logout-card {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 1.5px solid rgba(220,53,69,0.25);
            overflow: hidden;
            margin-bottom: 1.2rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            padding: 1rem 1.2rem;
            color: var(--danger-color);
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.18s ease;
        }

        .logout-btn:hover {
            background-color: rgba(220,53,69,0.04);
            color: var(--danger-color);
        }

        /* ── INFO DE CUENTA ── */
        .account-info-card {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            padding: 1.2rem 1.4rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.2rem;
            box-shadow: 0 4px 15px rgba(12,78,212,0.2);
            position: relative;
            overflow: hidden;
        }

        .account-info-card::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -5%;
            width: 130px;
            height: 130px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
        }

        .account-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: var(--white);
            flex-shrink: 0;
            border: 2px solid rgba(255,255,255,0.35);
            position: relative;
            z-index: 1;
        }

        .account-info-body { flex: 1; position: relative; z-index: 1; }

        .account-name {
            font-size: 1rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.1rem;
        }

        .account-email {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.78);
        }

        .account-badge {
            background: rgba(255,255,255,0.2);
            color: var(--white);
            font-size: 0.68rem;
            font-weight: 600;
            padding: 0.2rem 0.55rem;
            border-radius: 20px;
            position: relative;
            z-index: 1;
        }

        /* ── MODAL ── */
        .modal-content { border-radius: 14px; border: none; }
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            border-radius: 14px 14px 0 0;
            border-bottom: none;
            padding: 1rem 1.4rem;
        }
        .modal-header .btn-close { filter: invert(1); }
        .modal-title { font-size: 0.95rem; font-weight: 700; }
        .modal-body { padding: 1.4rem; }
        .modal-footer { border-top: 1px solid #f0f2f7; padding: 0.9rem 1.4rem; }

        .form-label { font-size: 0.82rem; font-weight: 600; color: var(--dark-text); margin-bottom: 0.3rem; }

        .form-control {
            font-size: 0.88rem;
            border-radius: 8px;
            border: 1px solid #dde2ec;
            padding: 0.55rem 0.85rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(12,78,212,0.12);
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            color: var(--white);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.55rem 1.2rem;
            border-radius: 8px;
            transition: opacity 0.2s, transform 0.15s;
        }

        .btn-primary-custom:hover { opacity: 0.9; transform: translateY(-1px); color: var(--white); }

        .btn-secondary-custom {
            background: #f0f2f7;
            border: 1px solid #dde2ec;
            color: var(--secondary-color);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.55rem 1.2rem;
            border-radius: 8px;
        }

        .password-toggle {
            position: relative;
        }
        .password-toggle .toggle-eye {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--secondary-color);
            font-size: 0.85rem;
        }
        .password-toggle .form-control { padding-right: 2.2rem; }

        /* ── ALERTAS ── */
        .alert-custom {
            border-radius: 8px;
            font-size: 0.82rem;
            padding: 0.65rem 1rem;
            border: none;
        }
        .alert-success-custom { background: rgba(40,167,69,0.1); color: #155724; }
        .alert-danger-custom  { background: rgba(220,53,69,0.1); color: #721c24; }

        /* ── ANIMACIONES ── */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .account-info-card  { animation: slideInUp 0.4s ease-out 0.05s forwards; opacity: 0; }
        .settings-card      { animation: slideInUp 0.4s ease-out 0.15s forwards; opacity: 0; }
        .logout-card        { animation: slideInUp 0.4s ease-out 0.25s forwards; opacity: 0; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            body { flex-direction: column !important; }
            .dashboard-content { padding: 0.8rem; }
        }
    </style>
</head>
<body>
    <?php include '../../header/sidebar.php'; ?>

    <div class="page-container">
        <!-- HEADER -->
        <div class="dashboard-header">
            <div class="header-text">
                <h1><i class="fas fa-cog"></i> Configuración</h1>
                <p>Administra tu cuenta y preferencias de seguridad</p>
            </div>
            <nav aria-label="breadcrumb" style="margin-left:auto; position:relative; z-index:1;">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Configuración</li>
                </ol>
            </nav>
        </div>

        <!-- CONTENIDO -->
        <div class="dashboard-content">

            <!-- TARJETA DE CUENTA -->
            <div class="account-info-card">
                <div class="account-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="account-info-body">
                    <div class="account-name"><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Usuario'); ?></div>
                    <div class="account-email"><?php echo htmlspecialchars($_SESSION['correo'] ?? 'correo@ejemplo.com'); ?></div>
                </div>
                <div class="account-badge"><i class="fas fa-shield-alt me-1"></i>Activo</div>
            </div>

            <!-- SECCIÓN SEGURIDAD -->
            <div class="section-label">
                <i class="fas fa-lock"></i> Seguridad
            </div>

            <div class="settings-card">
                <!-- Cambiar Nombre de Usuario -->
                <a class="settings-row" href="#" data-bs-toggle="modal" data-bs-target="#modalUsername">
                    <div class="settings-row-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="settings-row-body">
                        <div class="settings-row-title">Cambiar Nombre de Usuario</div>
                        <div class="settings-row-desc">Actualiza tu identificador de acceso</div>
                    </div>
                    <div class="settings-row-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>

                <!-- Cambiar Correo -->
                <a class="settings-row" href="#" data-bs-toggle="modal" data-bs-target="#modalEmail">
                    <div class="settings-row-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="settings-row-body">
                        <div class="settings-row-title">Cambiar Correo Electrónico</div>
                        <div class="settings-row-desc">Actualiza tu dirección de correo principal</div>
                    </div>
                    <div class="settings-row-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>

                <!-- Cambiar Contraseña -->
                <a class="settings-row" href="#" data-bs-toggle="modal" data-bs-target="#modalPassword">
                    <div class="settings-row-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="settings-row-body">
                        <div class="settings-row-title">Cambiar Contraseña</div>
                        <div class="settings-row-desc">Protege tu cuenta actualizando tu clave de acceso</div>
                    </div>
                    <div class="settings-row-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
            </div>

            <!-- SECCIÓN CUENTA -->
            <div class="section-label">
                <i class="fas fa-user-cog"></i> Cuenta
            </div>

            <div class="settings-card">
                <!-- Perfil -->
                <a class="settings-row" href="datosPersonales.php">
                    <div class="settings-row-icon success">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="settings-row-body">
                        <div class="settings-row-title">Datos Personales</div>
                        <div class="settings-row-desc">Edita tu información de perfil y foto</div>
                    </div>
                    <div class="settings-row-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>

                <!-- Documentos -->
                <a class="settings-row" href="documentos.php">
                    <div class="settings-row-icon warning">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="settings-row-body">
                        <div class="settings-row-title">Mis Documentos</div>
                        <div class="settings-row-desc">Gestiona tus documentos y certificaciones</div>
                    </div>
                    <div class="settings-row-arrow"><i class="fas fa-chevron-right"></i></div>
                </a>
            </div>

            <!-- CERRAR SESIÓN -->
            <div class="logout-card">
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>

        </div><!-- /dashboard-content -->
    </div><!-- /page-container -->


    <!-- ── MODAL: CAMBIAR NOMBRE DE USUARIO ── -->
    <div class="modal fade" id="modalUsername" tabindex="-1" aria-labelledby="modalUsernameLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUsernameLabel">
                        <i class="fas fa-user-circle me-2"></i>Cambiar Nombre de Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertUsername"></div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de usuario actual</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nuevo nombre de usuario</label>
                        <input type="text" class="form-control" id="newUsername" placeholder="Ingresa el nuevo nombre">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="passUsername" placeholder="Tu contraseña actual">
                            <span class="toggle-eye" onclick="togglePwd('passUsername', this)"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom" onclick="cambiarUsername()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MODAL: CAMBIAR CORREO ── -->
    <div class="modal fade" id="modalEmail" tabindex="-1" aria-labelledby="modalEmailLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmailLabel">
                        <i class="fas fa-envelope me-2"></i>Cambiar Correo Electrónico
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertEmail"></div>
                    <div class="mb-3">
                        <label class="form-label">Correo actual</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['correo'] ?? ''); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nuevo correo electrónico</label>
                        <input type="email" class="form-control" id="newEmail" placeholder="nuevo@correo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nuevo correo</label>
                        <input type="email" class="form-control" id="confirmEmail" placeholder="nuevo@correo.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar contraseña</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="passEmail" placeholder="Tu contraseña actual">
                            <span class="toggle-eye" onclick="togglePwd('passEmail', this)"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom" onclick="cambiarEmail()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ── MODAL: CAMBIAR CONTRASEÑA ── -->
    <div class="modal fade" id="modalPassword" tabindex="-1" aria-labelledby="modalPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPasswordLabel">
                        <i class="fas fa-lock me-2"></i>Cambiar Contraseña
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="alertPassword"></div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña actual</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="currentPass" placeholder="Tu contraseña actual">
                            <span class="toggle-eye" onclick="togglePwd('currentPass', this)"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva contraseña</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="newPass" placeholder="Mínimo 8 caracteres">
                            <span class="toggle-eye" onclick="togglePwd('newPass', this)"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirmar nueva contraseña</label>
                        <div class="password-toggle">
                            <input type="password" class="form-control" id="confirmPass" placeholder="Repite la nueva contraseña">
                            <span class="toggle-eye" onclick="togglePwd('confirmPass', this)"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                    <!-- Indicador de seguridad -->
                    <div id="strengthBar" style="display:none; margin-top:0.3rem;">
                        <div style="font-size:0.75rem; color:var(--secondary-color); margin-bottom:0.25rem;">Seguridad de la contraseña</div>
                        <div style="height:5px; border-radius:3px; background:#e9ecef; overflow:hidden;">
                            <div id="strengthFill" style="height:100%; width:0; transition:width 0.3s, background 0.3s; border-radius:3px;"></div>
                        </div>
                        <div id="strengthText" style="font-size:0.72rem; margin-top:0.2rem;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary-custom" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary-custom" onclick="cambiarPassword()">
                        <i class="fas fa-save me-1"></i>Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /* ── Mostrar / ocultar contraseña ── */
        function togglePwd(id, el) {
            const input = document.getElementById(id);
            const isText = input.type === 'text';
            input.type = isText ? 'password' : 'text';
            el.innerHTML = isText ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
        }

        /* ── Mostrar alerta ── */
        function showAlert(containerId, type, msg) {
            const cls = type === 'success' ? 'alert-success-custom' : 'alert-danger-custom';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
            document.getElementById(containerId).innerHTML =
                `<div class="alert-custom ${cls} mb-3"><i class="fas ${icon} me-1"></i>${msg}</div>`;
        }

        /* ── Indicador de fortaleza ── */
        document.getElementById('newPass')?.addEventListener('input', function() {
            const val = this.value;
            const bar = document.getElementById('strengthBar');
            const fill = document.getElementById('strengthFill');
            const text = document.getElementById('strengthText');
            if (!val) { bar.style.display = 'none'; return; }
            bar.style.display = 'block';
            let score = 0;
            if (val.length >= 8)  score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const levels = [
                { w: '25%', bg: '#dc3545', label: 'Muy débil' },
                { w: '50%', bg: '#ffc107', label: 'Débil' },
                { w: '75%', bg: '#17a2b8', label: 'Moderada' },
                { w: '100%', bg: '#28a745', label: 'Fuerte' },
            ];
            const lvl = levels[score - 1] || levels[0];
            fill.style.width = lvl.w;
            fill.style.background = lvl.bg;
            text.style.color = lvl.bg;
            text.textContent = lvl.label;
        });

        /* ── AJAX: Cambiar username ── */
        function cambiarUsername() {
            const username = document.getElementById('newUsername').value.trim();
            const pass     = document.getElementById('passUsername').value;
            if (!username || !pass) { showAlert('alertUsername', 'error', 'Completa todos los campos.'); return; }

            fetch('actions/change_username.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password: pass })
            })
            .then(r => r.json())
            .then(data => {
                showAlert('alertUsername', data.success ? 'success' : 'error', data.message);
            })
            .catch(() => showAlert('alertUsername', 'error', 'Error de conexión. Intenta de nuevo.'));
        }

        /* ── AJAX: Cambiar email ── */
        function cambiarEmail() {
            const email   = document.getElementById('newEmail').value.trim();
            const confirm = document.getElementById('confirmEmail').value.trim();
            const pass    = document.getElementById('passEmail').value;
            if (!email || !confirm || !pass) { showAlert('alertEmail', 'error', 'Completa todos los campos.'); return; }
            if (email !== confirm) { showAlert('alertEmail', 'error', 'Los correos no coinciden.'); return; }

            fetch('actions/change_email.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password: pass })
            })
            .then(r => r.json())
            .then(data => {
                showAlert('alertEmail', data.success ? 'success' : 'error', data.message);
            })
            .catch(() => showAlert('alertEmail', 'error', 'Error de conexión. Intenta de nuevo.'));
        }

        /* ── AJAX: Cambiar contraseña ── */
        function cambiarPassword() {
            const current = document.getElementById('currentPass').value;
            const newP    = document.getElementById('newPass').value;
            const confirm = document.getElementById('confirmPass').value;
            if (!current || !newP || !confirm) { showAlert('alertPassword', 'error', 'Completa todos los campos.'); return; }
            if (newP !== confirm) { showAlert('alertPassword', 'error', 'Las contraseñas nuevas no coinciden.'); return; }
            if (newP.length < 8)  { showAlert('alertPassword', 'error', 'La contraseña debe tener al menos 8 caracteres.'); return; }

            fetch('actions/change_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ current_password: current, new_password: newP })
            })
            .then(r => r.json())
            .then(data => {
                showAlert('alertPassword', data.success ? 'success' : 'error', data.message);
                if (data.success) {
                    document.getElementById('currentPass').value = '';
                    document.getElementById('newPass').value = '';
                    document.getElementById('confirmPass').value = '';
                    document.getElementById('strengthBar').style.display = 'none';
                }
            })
            .catch(() => showAlert('alertPassword', 'error', 'Error de conexión. Intenta de nuevo.'));
        }
    </script>
</body>
</html>