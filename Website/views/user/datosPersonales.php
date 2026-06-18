<?php
require_once '../../config/auth_user.php';

// ── Sesión y auth ──────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (isset($_SESSION['ultimo_acceso']) && (time() - $_SESSION['ultimo_acceso']) > 300) {
    session_destroy();
    header('Location: login.php'); exit;
}
$_SESSION['ultimo_acceso'] = time();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit;
}

$idUsuario = (int)$_SESSION['user_id'];

// ── Verificar si ya tiene perfil (modo edición vs creación) ────
$modoEdicion = false;
$postulante  = null;
$idPostulante = null;

$ch = curl_init("http://127.0.0.1:8000/api/postulantes/usuario/{$idUsuario}");
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_HTTPHEADER => ['Accept: application/json']]);
$res      = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    $postulante   = json_decode($res, true);
    $modoEdicion  = true;
    $idPostulante = $postulante['idPostulante'];
}

// ── Catálogos ──────────────────────────────────────────────────
require_once '../../config/conexion.php';
try {
    $provincias      = $conexion->query("SELECT codigo_provincia, nombre_provincia FROM provincia ORDER BY nombre_provincia")->fetchAll(PDO::FETCH_ASSOC);
    $estadosCiviles  = $conexion->query("SELECT idEstadoCivil, nombreEstadoCiv FROM estadocivil ORDER BY nombreEstadoCiv")->fetchAll(PDO::FETCH_ASSOC);
    $tiposSangre     = $conexion->query("SELECT idTipoSangre, nombreTipoSangre FROM tiposangre ORDER BY nombreTipoSangre")->fetchAll(PDO::FETCH_ASSOC);
    $rangosAcademicos= $conexion->query("SELECT idRangoEdu, nombreRangoEdu FROM rangoacademico ORDER BY idRangoEdu")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $provincias = $estadosCiviles = $tiposSangre = $rangosAcademicos = [];
}

// Helper para pre-llenar valores
function val($postulante, $key, $default = '') {
    return htmlspecialchars($postulante[$key] ?? $default);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $modoEdicion ? 'Mi Perfil' : 'Completa tu Perfil'; ?> - CareerPort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../../header/sidebar.css">
    <link rel="icon" type="image/png" href="../../assets/newwayslogo.png">
    <style>
        :root {
            --primary-color: #0c4ed4;
            --primary-dark:  #0a3fb0;
            --primary-light: #e8effe;
            --secondary-color: #6c757d;
            --light-bg:  #f0f2f7;
            --white:     #ffffff;
            --dark-text: #2c3e50;
            --border-color: #e0e4ef;
            --input-bg:  #f8f9fc;
        }
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            display: flex !important; flex-direction: row !important;
            background-color: var(--light-bg);
            font-family: 'Segoe UI', sans-serif;
            height: 100vh;
        }
        .page-container { flex: 1; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff; padding: 1rem 1.8rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            flex-shrink: 0; display: flex; align-items: center; gap: 1rem;
        }
        .header-text { position: relative; z-index: 1; }
        .dashboard-header h1 { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .dashboard-header p  { font-size: 0.8rem; opacity: 0.85; margin: 0; }
        .breadcrumb { background: transparent; padding: 0; margin: 0; font-size: 0.75rem; margin-left: auto; }
        .breadcrumb-item.active { color: rgba(255,255,255,0.75); }
        .breadcrumb-item a { color: #fff; text-decoration: none; font-weight: 500; }

        .dashboard-content { flex: 1; overflow-y: auto; padding: 1.5rem; }
        .form-wrapper { max-width: 860px; margin: 0 auto; }
        .form-intro { margin-bottom: 1.2rem; }
        .form-intro h2 { font-size: 1.3rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.2rem; }
        .form-intro p  { font-size: 0.85rem; color: var(--secondary-color); }

        .form-section {
            background: var(--white); border-radius: 10px;
            padding: 1.2rem 1.5rem; margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
        }
        .section-title {
            font-size: 0.9rem; font-weight: 700; color: var(--primary-color);
            margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;
            padding-bottom: 0.6rem; border-bottom: 2px solid var(--primary-light);
            text-transform: uppercase; letter-spacing: 0.5px;
        }
        .form-label { font-size: 0.78rem; font-weight: 600; color: var(--dark-text); margin-bottom: 0.3rem; }
        .form-control, .form-select {
            font-size: 0.82rem; padding: 0.45rem 0.75rem;
            border: 1.5px solid var(--border-color); border-radius: 7px;
            background-color: var(--input-bg); color: var(--dark-text);
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(12,78,212,0.1);
            background-color: var(--white); outline: none;
        }
        /* Campos deshabilitados en modo edición */
        .form-control:disabled, .form-select:disabled {
            background-color: #f1f3f9; color: #555; cursor: default;
            border-color: #dde1ec;
        }
        .cedula-wrapper { display: flex; align-items: center; gap: 0.3rem; }
        .cedula-wrapper span { font-weight: 700; color: var(--secondary-color); }

        /* Banner modo edición */
        .edit-banner {
            background: #fff8e1; border: 1px solid #ffe082;
            border-radius: 8px; padding: 0.65rem 1rem;
            font-size: 0.82rem; color: #7c5e00;
            display: flex; align-items: center; gap: 0.5rem;
            margin-bottom: 1rem;
        }

        /* Botones acción */
        .btn-submit, .btn-editar, .btn-descartar {
            border: none; padding: 0.7rem 2rem; border-radius: 8px;
            font-size: 0.9rem; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; gap: 0.5rem; transition: all 0.2s;
        }
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff; box-shadow: 0 4px 15px rgba(12,78,212,0.3);
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(12,78,212,0.4); }
        .btn-editar {
            background: linear-gradient(135deg, #0284c7, #0369a1);
            color: #fff; box-shadow: 0 4px 12px rgba(2,132,199,0.3);
        }
        .btn-descartar {
            background: #f1f5f9; color: var(--secondary-color);
            border: 1.5px solid var(--border-color);
        }
        .btn-descartar:hover { background: #e2e8f0; }

        .privacy-notice {
            background: #eef2ff; border: 1px solid #c7d4f8; border-radius: 8px;
            padding: 0.65rem 1rem; font-size: 0.78rem; color: var(--primary-dark);
            display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;
        }
        .required-note { font-size: 0.75rem; color: var(--secondary-color); margin-bottom: 0.8rem; }
        .required-note span { color: #dc3545; }
    </style>
</head>
<body>

<?php include '../../header/sidebar.php'; ?>

<div class="page-container">
    <div class="dashboard-header">
        <div class="header-text">
            <h1><i class="fas fa-user-circle"></i> Mi Perfil</h1>
            <p><?php echo $modoEdicion ? 'Actualiza tu información personal' : 'Completa tu información para el proceso de aplicación'; ?></p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active">Mi Perfil</li>
            </ol>
        </nav>
    </div>

    <div class="dashboard-content">
        <div class="form-wrapper">

            <div class="form-intro">
                <h2><?php echo $modoEdicion ? 'Tu Perfil' : 'Completa tu Perfil'; ?></h2>
                <p><?php echo $modoEdicion
                    ? 'Revisa tu información. Haz clic en <strong>Editar</strong> para modificar tus datos.'
                    : 'Por favor, proporciona tus datos generales para comenzar tu proceso de aplicación.'; ?></p>
            </div>

            <?php if ($modoEdicion): ?>
            <div class="edit-banner">
                <i class="fas fa-info-circle"></i>
                Estás viendo tu perfil actual. Haz clic en <strong>&nbsp;Editar perfil&nbsp;</strong> para hacer cambios.
            </div>
            <?php endif; ?>

            <?php if (!$modoEdicion): ?>
            <p class="required-note"><span>*</span> Campos obligatorios</p>
            <?php endif; ?>

            <form id="formPostulante">

                <!-- ══ INFORMACIÓN PERSONAL ══ -->
                <div class="form-section">
                    <div class="section-title"><i class="fas fa-user"></i> Información Personal</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Primer Nombre <span style="color:#dc3545">*</span></label>
                            <input type="text" class="form-control" name="primer_nombre"
                                placeholder="Ej: Juan" required
                                value="<?php echo val($postulante, 'nombre'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Segundo Nombre <span style="color:#6c757d">(Opcional)</span></label>
                            <input type="text" class="form-control" name="segundo_nombre"
                                placeholder="Ej: Carlos"
                                value="<?php echo val($postulante, 'nombre2'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Primer Apellido <span style="color:#dc3545">*</span></label>
                            <input type="text" class="form-control" name="primer_apellido"
                                placeholder="Ej: García" required
                                value="<?php echo val($postulante, 'apellido'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Segundo Apellido <span style="color:#6c757d">(Opcional)</span></label>
                            <input type="text" class="form-control" name="segundo_apellido"
                                placeholder="Ej: López"
                                value="<?php echo val($postulante, 'apellido2'); ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cédula Panameña <span style="color:#dc3545">*</span></label>
                            <div class="cedula-wrapper">
                                <input type="text" class="form-control" name="cedula_tipo"
                                    placeholder="00" maxlength="2" style="width:60px;text-align:center;" required
                                    value="<?php echo val($postulante, 'prefijo'); ?>">
                                <span>-</span>
                                <input type="text" class="form-control" name="cedula_tomo"
                                    placeholder="0000" maxlength="4" style="width:75px;text-align:center;" required
                                    value="<?php echo val($postulante, 'tomo'); ?>">
                                <span>-</span>
                                <input type="text" class="form-control" name="cedula_asiento"
                                    placeholder="00000" maxlength="5" style="width:85px;text-align:center;" required
                                    value="<?php echo val($postulante, 'asiento'); ?>">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Género <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="genero" required>
                                <option value="" disabled <?php echo !$postulante ? 'selected' : ''; ?>>Seleccione...</option>
                                <option value="1" <?php echo ($postulante['genero'] ?? '') == 1 ? 'selected' : ''; ?>>Masculino</option>
                                <option value="2" <?php echo ($postulante['genero'] ?? '') == 2 ? 'selected' : ''; ?>>Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Nacimiento <span style="color:#dc3545">*</span></label>
                            <input type="date" class="form-control" name="fecha_nacimiento" required
                                value="<?php echo val($postulante, 'fechaNacimiento'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado Civil <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="estado_civil" required>
                                <option value="" disabled <?php echo !$postulante ? 'selected' : ''; ?>>Seleccione...</option>
                                <?php foreach ($estadosCiviles as $ec): ?>
                                <option value="<?php echo $ec['idEstadoCivil']; ?>"
                                    <?php echo ($postulante['estadoCivil'] ?? '') == $ec['idEstadoCivil'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ec['nombreEstadoCiv']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de Sangre <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="tipo_sangre" required>
                                <option value="" disabled <?php echo !$postulante ? 'selected' : ''; ?>>Seleccione...</option>
                                <?php foreach ($tiposSangre as $ts): ?>
                                <option value="<?php echo $ts['idTipoSangre']; ?>"
                                    <?php echo ($postulante['tipoSangre'] ?? '') == $ts['idTipoSangre'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ts['nombreTipoSangre']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nivel Académico <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="nivel_academico" required>
                                <option value="" disabled <?php echo !$postulante ? 'selected' : ''; ?>>Seleccione...</option>
                                <?php foreach ($rangosAcademicos as $ra): ?>
                                <option value="<?php echo $ra['idRangoEdu']; ?>"
                                    <?php echo ($postulante['rangoAcademico'] ?? '') == $ra['idRangoEdu'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ra['nombreRangoEdu']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ══ CONTACTO ══ -->
                <div class="form-section">
                    <div class="section-title"><i class="fas fa-address-book"></i> Información de Contacto</div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono Primario <span style="color:#6c757d">(Opcional)</span></label>
                            <input type="tel" class="form-control" name="telefono_primario" placeholder="000-0000"
                                value="<?php echo val($postulante, 'telefono'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono Secundario <span style="color:#6c757d">(Opcional)</span></label>
                            <input type="tel" class="form-control" name="telefono_secundario" placeholder="000-0000"
                                value="<?php echo val($postulante, 'telefono2'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Celular Primario <span style="color:#dc3545">*</span></label>
                            <input type="tel" class="form-control" name="celular_primario" placeholder="6000-0000" required
                                value="<?php echo val($postulante, 'celular'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Celular Secundario <span style="color:#6c757d">(Opcional)</span></label>
                            <input type="tel" class="form-control" name="celular_secundario" placeholder="6000-0000"
                                value="<?php echo val($postulante, 'celular2'); ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Correo Electrónico <span style="color:#dc3545">*</span></label>
                            <input type="email" class="form-control" name="correo" placeholder="usuario@ejemplo.com" required
                                value="<?php echo val($postulante, 'correoPostulante'); ?>">
                        </div>
                    </div>
                </div>

                <!-- ══ DIRECCIÓN ══ -->
                <div class="form-section">
                    <div class="section-title"><i class="fas fa-map-marker-alt"></i> Dirección Residencial</div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Provincia <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="provincia" id="provincia" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <?php foreach ($provincias as $p): ?>
                                <option value="<?php echo htmlspecialchars($p['codigo_provincia']); ?>"
                                    <?php echo ($postulante['codigo_provincia'] ?? '') == $p['codigo_provincia'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['nombre_provincia']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Distrito <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="distrito" id="distrito" required>
                                <option value="" disabled selected>Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Corregimiento <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="corregimiento" id="corregimiento" required>
                                <option value="" disabled selected>Seleccione...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Comunidad <span style="color:#dc3545">*</span></label>
                            <input type="text" class="form-control" name="comunidad" placeholder="Ej: Altos de Panamá" required
                                value="<?php echo val($postulante, 'comunidad'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Calle <span style="color:#dc3545">*</span></label>
                            <input type="text" class="form-control" name="calle" placeholder="Ej: Calle 5ta" required
                                value="<?php echo val($postulante, 'calle'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Casa/Edificio # <span style="color:#dc3545">*</span></label>
                            <input type="text" class="form-control" name="casa_edificio" placeholder="Ej: Casa 12" required
                                value="<?php echo val($postulante, 'casa'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Detalles Adicionales <span style="color:#6c757d">(Opcional)</span></label>
                            <textarea class="form-control" name="detalles_adicionales" rows="2"
                                placeholder="Ej: Detrás de la tienda X..."><?php echo val($postulante, 'detalleDireccion'); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- ══ BOTONES ══ -->
                <div class="d-flex justify-content-end gap-2" id="botones-accion">
                    <?php if ($modoEdicion): ?>
                        <button type="button" class="btn-descartar" id="btn-descartar" style="display:none;">
                            <i class="fas fa-times"></i> Descartar
                        </button>
                        <button type="submit" class="btn-submit" id="btn-submit" style="display:none;">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button type="button" class="btn-editar" id="btn-editar">
                            <i class="fas fa-pen"></i> Editar perfil
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn-submit" id="btn-submit">
                            <i class="fas fa-paper-plane"></i> Enviar Solicitud
                        </button>
                    <?php endif; ?>
                </div>

                <div class="privacy-notice">
                    <i class="fas fa-shield-alt"></i>
                    Tus datos están protegidos bajo nuestras políticas de privacidad y seguridad.
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const modoEdicion  = <?php echo $modoEdicion ? 'true' : 'false'; ?>;
    const idPostulante = <?php echo $idPostulante ?? 'null'; ?>;
    const idUsuario    = <?php echo $idUsuario; ?>;

    const form         = document.getElementById('formPostulante');
    const btnEditar    = document.getElementById('btn-editar');
    const btnDescartar = document.getElementById('btn-descartar');
    const btnSubmit    = document.getElementById('btn-submit');
    const campos       = form.querySelectorAll('.form-control, .form-select');

    // ── Modo edición: deshabilitar campos al inicio ──────────────
    if (modoEdicion) {
        campos.forEach(c => c.disabled = true);
    }

    // ── Botón Editar: habilita campos ────────────────────────────
    btnEditar?.addEventListener('click', function () {
        campos.forEach(c => c.disabled = false);
        btnEditar.style.display   = 'none';
        btnSubmit.style.display   = 'flex';
        btnDescartar.style.display = 'flex';
    });

    // ── Botón Descartar: recarga la página ───────────────────────
    btnDescartar?.addEventListener('click', function () {
        if (confirm('¿Descartar los cambios?')) location.reload();
    });

    // ── Provincia → Distritos ────────────────────────────────────
    const selProvincia     = document.getElementById('provincia');
    const selDistrito      = document.getElementById('distrito');
    const selCorregimiento = document.getElementById('corregimiento');

    const distritoPrevio     = "<?php echo $postulante['codigo_distrito']     ?? ''; ?>";
    const corregimientoPrevio= "<?php echo $postulante['codigo_corregimiento'] ?? ''; ?>";

    async function cargarDistritos(codigoProvincia, seleccionado = '') {
        selDistrito.innerHTML = '<option value="" disabled selected>Cargando...</option>';
        selCorregimiento.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
        const res = await fetch(`get_distritos.php?codigo_provincia=${encodeURIComponent(codigoProvincia)}`);
        const data = await res.json();
        selDistrito.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
        data.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.codigo_distrito;
            opt.textContent = d.nombre_distrito;
            if (d.codigo_distrito == seleccionado) opt.selected = true;
            selDistrito.appendChild(opt);
        });
        selDistrito.disabled = false;
    }

    async function cargarCorregimientos(codigoDistrito, seleccionado = '') {
        selCorregimiento.innerHTML = '<option value="" disabled selected>Cargando...</option>';
        const res = await fetch(`get_corregimientos.php?codigo_distrito=${encodeURIComponent(codigoDistrito)}`);
        const data = await res.json();
        selCorregimiento.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
        data.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c.codigo_corregimiento;
            opt.textContent = c.nombre_corregimiento;
            if (c.codigo_corregimiento == seleccionado) opt.selected = true;
            selCorregimiento.appendChild(opt);
        });
        selCorregimiento.disabled = false;
    }

    // Pre-cargar distritos y corregimientos si hay provincia guardada
    if (selProvincia.value) {
        cargarDistritos(selProvincia.value, distritoPrevio).then(() => {
            if (distritoPrevio) cargarCorregimientos(distritoPrevio, corregimientoPrevio);
        });
    }

    selProvincia.addEventListener('change', function () {
        cargarDistritos(this.value);
    });
    selDistrito.addEventListener('change', function () {
        cargarCorregimientos(this.value);
    });

    // ── Submit ───────────────────────────────────────────────────
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        const data = {
            idUsuario:            idUsuario,
            nombre:               form.primer_nombre.value.trim(),
            nombre2:              form.segundo_nombre.value.trim(),
            apellido:             form.primer_apellido.value.trim(),
            apellido2:            form.segundo_apellido.value.trim(),
            prefijo:              form.cedula_tipo.value.trim(),
            tomo:                 form.cedula_tomo.value.trim(),
            asiento:              form.cedula_asiento.value.trim(),
            genero:               parseInt(form.genero.value),
            fechaNacimiento:      form.fecha_nacimiento.value,
            estadoCivil:          form.estado_civil.value,
            tipoSangre:           form.tipo_sangre.value,
            rangoAcademico:       form.nivel_academico.value,
            telefono:             form.telefono_primario.value.trim(),
            telefono2:            form.telefono_secundario.value.trim(),
            celular:              form.celular_primario.value.trim(),
            celular2:             form.celular_secundario.value.trim(),
            correoPostulante:     form.correo.value.trim(),
            codigo_provincia:     form.provincia.value,
            codigo_distrito:      form.distrito.value,
            codigo_corregimiento: form.corregimiento.value,
            comunidad:            form.comunidad.value.trim(),
            calle:                form.calle.value.trim(),
            casa:                 form.casa_edificio.value.trim(),
            detalleDireccion:     form.detalles_adicionales.value.trim(),
        };

        const method = modoEdicion ? 'PUT' : 'POST';
        const url    = modoEdicion
            ? `http://127.0.0.1:8000/api/postulantes/${idPostulante}`
            : 'http://127.0.0.1:8000/api/postulantes';

        try {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await response.json();

            if (response.ok) {
                alert('✅ ' + (result.mensaje || 'Datos guardados correctamente'));
                location.reload(); // Recarga en modo edición con datos actualizados
            } else {
                const errores = result.errors
                    ? Object.values(result.errors).flat().join('\n')
                    : result.message || 'Error desconocido';
                alert('❌ Error:\n' + errores);
            }
        } catch (err) {
            alert('❌ Error de conexión: ' + err.message);
        } finally {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = modoEdicion
                ? '<i class="fas fa-save"></i> Guardar Cambios'
                : '<i class="fas fa-paper-plane"></i> Enviar Solicitud';
        }
    });
});
</script>
</body>
</html>