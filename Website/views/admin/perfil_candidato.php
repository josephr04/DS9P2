<?php
require_once '../../config/auth_admin.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

define('API_BASE', 'http://127.0.0.1:8000/api');

$idPostulante = (int)($_GET['idPostulante'] ?? 0);

if (!$idPostulante) {
    header('Location: dashboard.php');
    exit;
}

// ── Helpers de consumo de API ────────────────────────────────────
function apiGet(string $ep): ?array {
    $ch = curl_init(API_BASE . $ep);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

// Para endpoints que devuelven UN solo objeto (no lista)
function apiGetItem(string $ep): ?array {
    $ch = curl_init(API_BASE . $ep);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        CURLOPT_TIMEOUT        => 10,
    ]);
    $raw  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code !== 200 || !$raw) return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function normalizeList(?array $raw): array {
    if (empty($raw)) return [];
    foreach (['data', 'result', 'items', 'records'] as $key) {
        if (isset($raw[$key]) && is_array($raw[$key])) { $raw = $raw[$key]; break; }
    }
    $first = reset($raw);
    if (!is_array($first)) return [];
    return array_values($raw);
}

function formatFecha(?string $fecha): string {
    if (empty($fecha) || $fecha === 'null') return 'No registrada';
    $partes = explode('-', $fecha);
    return count($partes) === 3 ? "{$partes[2]}/{$partes[1]}/{$partes[0]}" : $fecha;
}

// ── Catálogos ─────────────────────────────────────────────────────
$provincias = normalizeList(apiGet('/provincias'));
$provinciaMap = [];
foreach ($provincias as $p) { if (is_array($p)) $provinciaMap[$p['codigo_provincia']] = $p['nombre_provincia']; }

$distritos = normalizeList(apiGet('/distritos'));
$distritoMap = [];
foreach ($distritos as $d) {
    if (is_array($d)) {
        $codigo = str_pad((string)$d['codigo_distrito'], 4, '0', STR_PAD_LEFT);
        $distritoMap[$codigo] = $d['nombre_distrito'];
    }
}

$estadosCiviles = normalizeList(apiGet('/estados-civiles'));
$estadoCivilMap = [];
foreach ($estadosCiviles as $ec) { if (is_array($ec)) $estadoCivilMap[(int)$ec['idEstadoCivil']] = $ec['nombreEstadoCiv']; }

$tiposSangre = normalizeList(apiGet('/tipos-sangre'));
$tipoSangreMap = [];
foreach ($tiposSangre as $ts) { if (is_array($ts)) $tipoSangreMap[(int)$ts['idTipoSangre']] = $ts['nombreTipoSangre']; }

$rangosAcademicos = normalizeList(apiGet('/rangos-academicos'));
$rangoAcademicoMap = [];
foreach ($rangosAcademicos as $ra) { if (is_array($ra)) $rangoAcademicoMap[(int)$ra['idRangoEdu']] = $ra['nombreRangoEdu']; }

$grados = normalizeList(apiGet('/grados-academicos'));
$instituciones = normalizeList(apiGet('/instituciones'));

// ── Datos del postulante ──────────────────────────────────────────
$postulante = apiGetItem('/postulantes/' . $idPostulante);
$apiError = !$postulante;

$nombreCompleto = trim(($postulante['nombre'] ?? '') . ' ' . ($postulante['apellido'] ?? '')) ?: 'Candidato';

$codCorr = $postulante['codigo_corregimiento'] ?? '';
$corregimientoNombre = $codCorr;
if ($codCorr) {
    $corrResp = apiGetItem('/corregimientos/codigo/' . $codCorr);
    if ($corrResp && isset($corrResp['nombre_corregimiento'])) {
        $corregimientoNombre = $corrResp['nombre_corregimiento'];
    }
}

$generoTexto = match ((int)($postulante['genero'] ?? 0)) {
    1 => 'Masculino',
    2 => 'Femenino',
    3 => 'Otro',
    default => 'No especificado',
};

// ── Documentos del postulante ───────────────────────────────────
$documentos = normalizeList(apiGet('/documentos-postulante/por-postulante/' . $idPostulante));
$todasRutas = normalizeList(apiGet('/rutas-documento'));
$rutasPorDoc = [];
foreach ($todasRutas as $r) {
    if (is_array($r) && isset($r['idDocumentoPostulante'])) {
        $rutasPorDoc[(int)$r['idDocumentoPostulante']] = $r['ruta'];
    }
}

$baseUploadUrl = 'http://localhost/DS9P2/uploads/documentos/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil del Candidato - Panel Administrador</title>
<link rel="icon" type="image/png" href="../../assets/newwayslogo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../header/sidebar.css">
<style>
:root{
    --primary-color:#0c4ed4; --primary-dark:#0a3fb0; --primary-light:#e8effe;
    --secondary-color:#6c757d; --light-bg:#f0f2f7; --white:#fff;
    --dark-text:#2c3e50; --border-color:#e0e4ef; --danger:#dc3545;
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{display:flex!important;flex-direction:row!important;background:var(--light-bg);font-family:'Segoe UI',sans-serif;height:100vh}
.page-container{flex:1;display:flex;flex-direction:column;height:100vh;overflow:hidden}

.dashboard-header{background:linear-gradient(135deg,var(--primary-color),var(--primary-dark));color:#fff;padding:1rem 1.8rem;box-shadow:0 4px 15px rgba(0,0,0,.12);flex-shrink:0;display:flex;align-items:center;gap:1rem}
.back-btn{background:rgba(255,255,255,.18);border:none;color:#fff;width:36px;height:36px;border-radius:8px;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;text-decoration:none}
.back-btn:hover{background:rgba(255,255,255,.3);color:#fff}
.dashboard-header h1{font-size:1.3rem;font-weight:700;margin:0}
.breadcrumb{background:transparent;padding:0;margin:0;font-size:.75rem;margin-left:auto}
.breadcrumb-item.active{color:rgba(255,255,255,.75)}
.breadcrumb-item a{color:#fff;text-decoration:none;font-weight:500}

.dashboard-content{flex:1;overflow-y:auto;padding:1.5rem}
.content-wrapper{max-width:900px;margin:0 auto}

/* ── Header del candidato ── */
.candidate-header{background:var(--white);border-radius:12px;padding:2rem 1rem;text-align:center;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:1.2rem}
.candidate-avatar-lg{width:80px;height:80px;border-radius:50%;background:var(--primary-light);border:2px solid var(--primary-color);display:flex;align-items:center;justify-content:center;font-size:2.2rem;color:var(--primary-color);margin:0 auto .8rem}
.candidate-header h2{font-size:1.25rem;font-weight:700;color:var(--dark-text);margin:0}
.candidate-header p{font-size:.85rem;color:var(--primary-color);margin-top:.2rem}

/* ── Tabs estilo Android ── */
.tabs-bar{display:flex;background:var(--white);border-radius:12px 12px 0 0;border:1px solid var(--border-color);border-bottom:none;overflow:hidden}
.tab-btn{flex:1;background:none;border:none;padding:.9rem;font-size:.85rem;font-weight:700;color:var(--dark-text);cursor:pointer;position:relative}
.tab-btn .tab-indicator{height:3px;background:var(--primary-color);width:60px;margin:.5rem auto 0;border-radius:2px;visibility:hidden}
.tab-btn.active .tab-indicator{visibility:visible}
.tab-btn:not(.active){color:var(--secondary-color)}

.tab-panel{background:var(--light-bg);border-radius:0 0 12px 12px;border:1px solid var(--border-color);border-top:none;padding:1.2rem;margin-bottom:1.5rem}
.tab-panel.hidden{display:none}

.info-card{background:var(--white);border-radius:10px;padding:1.2rem 1.4rem;margin-bottom:1rem;border:1px solid var(--border-color);box-shadow:0 2px 8px rgba(0,0,0,.05)}
.info-card h4{font-size:.92rem;font-weight:700;color:var(--primary-color);margin-bottom:1rem}
.info-row{display:grid;grid-template-columns:1fr 1fr;gap:0 1.5rem}
@media(max-width:576px){.info-row{grid-template-columns:1fr}}
.info-label{font-size:.68rem;font-weight:600;color:var(--secondary-color);letter-spacing:.4px;text-transform:uppercase;margin-top:.7rem}
.info-value{font-size:.88rem;font-weight:700;color:var(--dark-text);margin-bottom:.2rem}

/* ── Documentos ── */
.docs-toolbar-mini{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem}
.docs-toolbar-mini h4{font-size:.92rem;font-weight:700;color:var(--primary-color);margin:0}
.docs-toolbar-mini span{font-size:.78rem;color:var(--secondary-color)}

.doc-card{background:var(--white);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:.7rem;border:1px solid var(--border-color);box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;align-items:center;gap:.9rem}
.doc-icon{width:42px;height:42px;background:var(--primary-light);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--primary-color);flex-shrink:0}
.doc-info{flex:1;min-width:0}
.doc-name{font-size:.85rem;font-weight:700;color:var(--dark-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.doc-meta{font-size:.73rem;color:var(--secondary-color);margin-top:.1rem}
.doc-actions{display:flex;gap:.4rem;flex-shrink:0}
.doc-action-btn{background:none;border:1.5px solid var(--border-color);border-radius:6px;padding:.3rem .5rem;font-size:.75rem;color:var(--secondary-color);cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center}
.doc-action-btn:hover{border-color:var(--primary-color);color:var(--primary-color)}

.empty-state{background:var(--white);border-radius:10px;padding:2rem 1rem;text-align:center;border:1px solid var(--border-color)}
.empty-state i{font-size:2.4rem;color:#c5cde0;margin-bottom:.6rem}
.empty-state p{font-size:.82rem;color:var(--secondary-color);margin:0}

.alert-custom{border-radius:8px;font-size:.83rem;padding:.7rem 1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem}
.alert-warning{background:rgba(255,193,7,.1);border:1px solid rgba(255,193,7,.3);color:#856404}

.pdf-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:9999;display:none;align-items:center;justify-content:center}
.pdf-modal-overlay.open{display:flex}
.pdf-modal{background:var(--white);border-radius:12px;width:90vw;max-width:900px;height:88vh;display:flex;flex-direction:column;overflow:hidden}
.pdf-modal-header{background:linear-gradient(135deg,var(--primary-color),var(--primary-dark));color:#fff;padding:.85rem 1.2rem;display:flex;align-items:center;gap:.8rem}
.pdf-modal-header span{font-size:.9rem;font-weight:700;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pdf-modal-btn{background:rgba(255,255,255,.2);border:none;border-radius:6px;color:#fff;width:30px;height:30px;cursor:pointer;font-size:.8rem;display:flex;align-items:center;justify-content:center;text-decoration:none}
.pdf-modal iframe{flex:1;border:none;width:100%}
</style>
</head>
<body>

<?php include '../../header/sidebar_admin.php'; ?>

<div class="pdf-modal-overlay" id="pdfModal">
  <div class="pdf-modal">
    <div class="pdf-modal-header">
      <i class="fas fa-file-pdf"></i>
      <span id="pdfModalTitle">Documento</span>
      <a id="pdfModalNewTab" href="#" target="_blank" class="pdf-modal-btn" style="margin-right:.3rem"><i class="fas fa-external-link-alt"></i></a>
      <button class="pdf-modal-btn" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
    </div>
    <iframe id="pdfIframe" src=""></iframe>
  </div>
</div>

<!-- Modal detalle de documento -->
<div class="modal fade" id="detalleDocModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background:linear-gradient(135deg,#0c4ed4,#0a3fb0);color:#fff;">
        <h5 class="modal-title">Detalle del Documento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="detalleDocBody" style="font-size:.88rem;"></div>
    </div>
  </div>
</div>

<div class="page-container">
    <div class="dashboard-header">
        <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i></a>
        <h1>Perfil del Candidato</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active">Perfil</li>
            </ol>
        </nav>
    </div>

    <div class="dashboard-content">
        <div class="content-wrapper">

            <?php if ($apiError): ?>
            <div class="alert-custom alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se pudo cargar la información del postulante. Verifica que la API esté disponible.
            </div>
            <?php else: ?>

            <div class="candidate-header">
                <div class="candidate-avatar-lg"><i class="fas fa-user"></i></div>
                <h2><?= htmlspecialchars($nombreCompleto) ?></h2>
                <p>Postulante</p>
            </div>

            <div class="tabs-bar">
                <button class="tab-btn active" onclick="cambiarTab('info', this)">
                    Información Personal
                    <div class="tab-indicator"></div>
                </button>
                <button class="tab-btn" onclick="cambiarTab('docs', this)">
                    Documentos
                    <div class="tab-indicator"></div>
                </button>
            </div>

            <div class="tab-panel" id="tab-info">

                <div class="info-card">
                    <h4>Información Personal</h4>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Primer Nombre</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['nombre'] ?? '-') ?></div>
                            <div class="info-label">Primer Apellido</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['apellido'] ?? '-') ?></div>
                            <div class="info-label">Cédula</div>
                            <div class="info-value">
                                <?php
                                $prefijo = $postulante['prefijo'] ?? ''; $tomo = $postulante['tomo'] ?? ''; $asiento = $postulante['asiento'] ?? '';
                                echo ($prefijo || $tomo || $asiento) ? htmlspecialchars("$prefijo-$tomo-$asiento") : 'No registrada';
                                ?>
                            </div>
                            <div class="info-label">Fecha de Nacimiento</div>
                            <div class="info-value"><?= htmlspecialchars(formatFecha($postulante['fechaNacimiento'] ?? '')) ?></div>
                            <div class="info-label">Tipo de Sangre</div>
                            <div class="info-value"><?= htmlspecialchars($tipoSangreMap[(int)($postulante['tipoSangre'] ?? 0)] ?? 'No especificado') ?></div>
                        </div>
                        <div>
                            <div class="info-label">Segundo Nombre</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['nombre2'] ?? '-') ?: '-' ?></div>
                            <div class="info-label">Segundo Apellido</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['apellido2'] ?? '-') ?: '-' ?></div>
                            <div class="info-label">Género</div>
                            <div class="info-value"><?= htmlspecialchars($generoTexto) ?></div>
                            <div class="info-label">Estado Civil</div>
                            <div class="info-value"><?= htmlspecialchars($estadoCivilMap[(int)($postulante['estadoCivil'] ?? 0)] ?? 'No especificado') ?></div>
                            <div class="info-label">Rango Académico</div>
                            <div class="info-value"><?= htmlspecialchars($rangoAcademicoMap[(int)($postulante['rangoAcademico'] ?? 0)] ?? 'No especificado') ?></div>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h4>Información de Contacto</h4>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Teléfono Primario</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['telefono'] ?? '-') ?: '-' ?></div>
                            <div class="info-label">Celular Primario</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['celular'] ?? '-') ?: '-' ?></div>
                        </div>
                        <div>
                            <div class="info-label">Teléfono Secundario</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['telefono2'] ?? '-') ?: '-' ?></div>
                            <div class="info-label">Celular Secundario</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['celular2'] ?? '-') ?: '-' ?></div>
                        </div>
                    </div>
                    <div class="info-label">Correo Electrónico</div>
                    <div class="info-value"><?= htmlspecialchars($postulante['correoPostulante'] ?? '-') ?></div>
                </div>

                <div class="info-card">
                    <h4>Dirección Residencial</h4>
                    <div class="info-row">
                        <div>
                            <div class="info-label">Provincia</div>
                            <div class="info-value"><?= htmlspecialchars($provinciaMap[$postulante['codigo_provincia'] ?? ''] ?? ($postulante['codigo_provincia'] ?? '-')) ?></div>
                            <div class="info-label">Corregimiento</div>
                            <div class="info-value"><?= htmlspecialchars($corregimientoNombre ?: '-') ?></div>
                            <div class="info-label">Calle</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['calle'] ?? '-') ?></div>
                        </div>
                        <div>
                            <div class="info-label">Distrito</div>
                            <div class="info-value"><?= htmlspecialchars($distritoMap[str_pad((string)($postulante['codigo_distrito'] ?? ''), 4, '0', STR_PAD_LEFT)] ?? ($postulante['codigo_distrito'] ?? '-')) ?></div>
                            <div class="info-label">Urbanización / Comunidad</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['comunidad'] ?? '-') ?></div>
                            <div class="info-label">Casa/Edificio #</div>
                            <div class="info-value"><?= htmlspecialchars($postulante['casa'] ?? '-') ?></div>
                        </div>
                    </div>
                    <div class="info-label">Detalles Adicionales</div>
                    <div class="info-value"><?= htmlspecialchars($postulante['detalleDireccion'] ?? '-') ?></div>
                </div>

            </div>

            <div class="tab-panel hidden" id="tab-docs">
                <div class="docs-toolbar-mini">
                    <h4>Documentos Subidos</h4>
                    <span><?= count($documentos) ?> archivo(s)</span>
                </div>

                <?php if (empty($documentos)): ?>
                <div class="empty-state">
                    <i class="fas fa-file-circle-question"></i>
                    <p>Este postulante no ha subido documentos.</p>
                </div>
                <?php else: ?>
                <?php foreach ($documentos as $doc):
                    if (!is_array($doc)) continue;
                    $idDoc     = (int)$doc['idDocumentoPostulante'];
                    $tieneRuta = isset($rutasPorDoc[$idDoc]);
                    $archUrl   = $tieneRuta ? $baseUploadUrl . basename($rutasPorDoc[$idDoc]) : '';

                    $nombreGrado = 'Desconocido';
                    foreach ($grados as $g) { if (is_array($g) && (int)$g['idGradoEst'] === (int)$doc['idGradoEst']) { $nombreGrado = $g['nombreGradoEst']; break; } }

                    if (!empty($doc['otraInstitucionn']) && $doc['otraInstitucionn'] == 1) {
                        $nombreInst = $doc['nombreOtraInstitucion'] ?? 'Otra institución';
                    } else {
                        $nombreInst = 'Desconocida';
                        foreach ($instituciones as $inst) { if (is_array($inst) && (int)$inst['idInstitucion'] === (int)$doc['institucion']) { $nombreInst = $inst['nombreInstitucion']; break; } }
                    }

                    $provPos = (int)($doc['codigo_provincia'] ?? 0);
                    $nombreProvDoc = $provincias[$provPos - 1]['nombre_provincia'] ?? 'Desconocida';
                ?>
                <div class="doc-card">
                    <div class="doc-icon"><i class="fas fa-file-pdf"></i></div>
                    <div class="doc-info">
                        <div class="doc-name"><?= htmlspecialchars($doc['titulo']) ?></div>
                        <div class="doc-meta"><?= htmlspecialchars($nombreInst) ?> &middot; <?= htmlspecialchars($nombreGrado) ?> &middot; <?= (int)$doc['totalHoras'] ?> hrs</div>
                    </div>
                    <div class="doc-actions">
                        <?php if ($tieneRuta): ?>
                        <button class="doc-action-btn" onclick="abrirModal('<?= addslashes(htmlspecialchars($doc['titulo'])) ?>','<?= htmlspecialchars($archUrl) ?>')" title="Ver"><i class="fas fa-eye"></i></button>
                        <a class="doc-action-btn" href="<?= htmlspecialchars($archUrl) ?>" download title="Descargar"><i class="fas fa-download"></i></a>
                        <?php endif; ?>
                        <button class="doc-action-btn" onclick='verDetalleDoc(<?= json_encode([
                            "titulo" => $doc["titulo"],
                            "grado" => $nombreGrado,
                            "institucion" => $nombreInst,
                            "provincia" => $nombreProvDoc,
                            "inicio" => $doc["fechaInicio"] ?? "",
                            "fin" => $doc["fechaFinaizacion"] ?? "",
                            "emision" => $doc["fechaEmision"] ?? "",
                            "horas" => $doc["totalHoras"] ?? 0,
                        ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' title="Detalle"><i class="fas fa-circle-info"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function cambiarTab(tab, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-info').classList.toggle('hidden', tab !== 'info');
    document.getElementById('tab-docs').classList.toggle('hidden', tab !== 'docs');
}

function abrirModal(t, u) {
    document.getElementById('pdfModalTitle').textContent = t;
    document.getElementById('pdfIframe').src = u;
    document.getElementById('pdfModalNewTab').href = u;
    document.getElementById('pdfModal').classList.add('open');
}
function cerrarModal() {
    document.getElementById('pdfModal').classList.remove('open');
    document.getElementById('pdfIframe').src = '';
}
document.getElementById('pdfModal').addEventListener('click', function (e) { if (e.target === this) cerrarModal(); });
document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarModal(); });

function verDetalleDoc(doc) {
    const html = `
        <p><strong>Título:</strong> ${doc.titulo}</p>
        <p><strong>Grado:</strong> ${doc.grado}</p>
        <p><strong>Institución:</strong> ${doc.institucion}</p>
        <p><strong>Provincia:</strong> ${doc.provincia}</p>
        <p><strong>Inicio:</strong> ${doc.inicio}</p>
        <p><strong>Finalización:</strong> ${doc.fin}</p>
        <p><strong>Emisión:</strong> ${doc.emision}</p>
        <p><strong>Total de horas:</strong> ${doc.horas}</p>
    `;
    document.getElementById('detalleDocBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('detalleDocModal')).show();
}
</script>
</body>
</html>