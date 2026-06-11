<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$userId = $_SESSION['user_id'];
$idUsuario = $_SESSION['idUsuario'];

define('API_BASE',   'http://localhost:8000/api');
define('UPLOAD_DIR', __DIR__ . '/uploads/documentos/');
define('UPLOAD_URL', 'uploads/documentos/');
define('MAX_SIZE',   10 * 1024 * 1024);

if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

function apiGet(string $ep): ?array {
    $ch = curl_init(API_BASE . $ep);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_HTTPHEADER=>['Accept: application/json'], CURLOPT_TIMEOUT=>10]);
    $raw = curl_exec($ch); curl_close($ch);
    if (!$raw) return null;
    $d = json_decode($raw, true);
    if (!is_array($d)) return null;
    // Laravel a veces envuelve en {"data":[...]} o {"result":[...]}
    foreach (['data','result','items','records'] as $key) {
        if (isset($d[$key]) && is_array($d[$key])) return $d[$key];
    }
    return $d;
}

function apiPost(string $ep, array $data): array {
    $ch = curl_init(API_BASE . $ep);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode($data),
        CURLOPT_HTTPHEADER=>['Accept: application/json','Content-Type: application/json'], CURLOPT_TIMEOUT=>10]);
    $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return ['status'=>$status, 'body'=>json_decode($raw, true)];
}

function apiDelete(string $ep): int {
    $ch = curl_init(API_BASE . $ep);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true, CURLOPT_CUSTOMREQUEST=>'DELETE',
        CURLOPT_HTTPHEADER=>['Accept: application/json'], CURLOPT_TIMEOUT=>10]);
    curl_exec($ch); $s = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    return $s;
}

if (isset($_GET['delete'])) {
    $idDoc = (int)$_GET['delete'];
    $rutas = normalizeList(apiGet('/rutas-documento'));
    foreach ($rutas as $r) {
        if (!is_array($r)) continue;
        if ((int)$r['idDocumentoPostulante'] === $idDoc) {
            $p = __DIR__ . '/' . $r['ruta'];
            if (file_exists($p)) @unlink($p);
            apiDelete('/rutas-documento/' . $r['idRutadoc']);
            break;
        }
    }
    apiDelete('/documentos-postulante/' . $idDoc);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?')); exit;
}

if (isset($_GET['download'])) {
    $idDoc = (int)$_GET['download'];
    $rutas = normalizeList(apiGet('/rutas-documento'));
    foreach ($rutas as $r) {
        if (!is_array($r)) continue;
        if ((int)$r['idDocumentoPostulante'] === $idDoc) {
            $p = __DIR__ . '/' . $r['ruta'];
            if (file_exists($p)) {
                $docs = normalizeList(apiGet('/documentos-postulante/por-postulante/' . $userId));
                $titulo = 'documento';
                foreach ($docs as $d) {
                    if (!is_array($d)) continue;
                    if ((int)$d['idDocumentoPostulante'] === $idDoc) { $titulo = $d['titulo']; break; }
                }
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $titulo) . '.pdf"');
                header('Content-Length: ' . filesize($p));
                readfile($p); exit;
            }
        }
    }
    exit;
}

$error = '';
$success = isset($_GET['success']) ? 'Documento subido correctamente.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo    = trim($_POST['titulo'] ?? '');
    $idGrado   = (int)($_POST['id_grado_est'] ?? 0);
    $provincia = (int)($_POST['provincia_pos'] ?? 0);
    $fInicio   = trim($_POST['fecha_inicio'] ?? '');
    $fFin      = trim($_POST['fecha_fin'] ?? '');
    $fEmision  = trim($_POST['fecha_emision'] ?? '');
    $horas     = (int)($_POST['total_horas'] ?? 0);
    $chkOtra   = isset($_POST['otra_institucion_check']);
    $idInst    = $chkOtra ? 0 : (int)($_POST['id_institucion'] ?? 0);
    $otraInst  = $chkOtra ? trim($_POST['otra_institucion'] ?? '') : null;

    if (!$titulo || !$idGrado || (!$idInst && !$otraInst) || !$provincia || !$fInicio || !$fFin || !$fEmision || $horas < 1) {
        $error = 'Completa todos los campos obligatorios.';
    } elseif (empty($_FILES['archivo_pdf']['name'])) {
        $error = 'Selecciona un archivo PDF.';
    } else {
        $file = $_FILES['archivo_pdf'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error al subir el archivo (codigo ' . $file['error'] . ').';
        } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
            $error = 'Solo se permiten archivos PDF.';
        } elseif ($file['size'] > MAX_SIZE) {
            $error = 'El archivo supera los 10 MB.';
        } else {
            $nombreArchivo = $userId . '_' . time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', basename($file['name']));
            $destino = UPLOAD_DIR . $nombreArchivo;
            $rutaRelativa = UPLOAD_URL . $nombreArchivo;

            if (!move_uploaded_file($file['tmp_name'], $destino)) {
                $error = 'No se pudo guardar el archivo. Verifica permisos de la carpeta uploads/.';
            } else {
                $docData = [
                    'idGradoEst' => $idGrado, 'idPostulante' => $userId,
                    'codigo_provincia' => (string)$provincia, 'titulo' => $titulo,
                    'institucion' => $idInst, 'otraInstitucionn' => $chkOtra ? 1 : 0,
                    'nombreOtraInstitucion' => $otraInst,
                    'fechaInicio' => $fInicio, 'fechaFinaizacion' => $fFin,
                    'fechaEmision' => $fEmision, 'totalHoras' => $horas,
                ];
                $resp = apiPost('/documentos-postulante', $docData);
                if ($resp['status'] >= 200 && $resp['status'] < 300 && isset($resp['body']['idDocumentoPostulante'])) {
                    apiPost('/rutas-documento', ['idDocumentoPostulante' => $resp['body']['idDocumentoPostulante'], 'ruta' => $rutaRelativa]);
                    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?success=1');
                    exit;
                    } else {
                    @unlink($destino);
                    $error = 'Error al registrar en la base de datos.';
                    if (isset($resp['body']['message'])) $error .= ' ' . $resp['body']['message'];
                }
            }
        }
    }
}

// Normaliza: garantiza array indexado de arrays (no strings ni nulls)
function normalizeList(?array $raw): array {
    if (empty($raw)) return [];
    $first = reset($raw);
    if (!is_array($first)) return [];
    return array_values($raw);
}

$grados        = normalizeList(apiGet('/grados-academicos'));
$instituciones = normalizeList(apiGet('/instituciones'));
$provincias    = normalizeList(apiGet('/provincias'));
$documentos    = normalizeList(apiGet('/documentos-postulante/por-postulante/' . $userId));
$todasRutas    = normalizeList(apiGet('/rutas-documento'));
$rutasPorDoc   = [];
foreach ($todasRutas as $r) {
    if (is_array($r) && isset($r['idDocumentoPostulante'])) {
        $rutasPorDoc[(int)$r['idDocumentoPostulante']] = $r['ruta'];
    }
}

// DEBUG: descomenta si ves errores raros para ver que devuelve la API
// echo '<pre>GRADOS: ';     print_r($grados);      echo '</pre>';
// echo '<pre>INST: ';       print_r($instituciones); echo '</pre>';
// echo '<pre>PROVINCIAS: '; print_r($provincias);   echo '</pre>';
// echo '<pre>DOCUMENTOS: '; print_r($documentos);   echo '</pre>';
// die();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<link rel="icon" type="image/png" href="../../assets/newwayslogo.png">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Documentos - CareerPort</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../header/sidebar.css">
<style>
:root{--primary-color:#0c4ed4;--primary-dark:#0a3fb0;--primary-light:#e8effe;--secondary-color:#6c757d;--light-bg:#f0f2f7;--white:#fff;--dark-text:#2c3e50;--border-color:#e0e4ef;--input-bg:#f8f9fc;--danger:#dc3545;--success:#28a745}
*{margin:0;padding:0;box-sizing:border-box}html,body{height:100%}
body{display:flex!important;flex-direction:row!important;background-color:var(--light-bg);font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;margin:0!important;padding:0!important;height:100vh}
.page-container{flex:1;display:flex;flex-direction:column;height:100vh;overflow:hidden}
.dashboard-header{background:linear-gradient(135deg,var(--primary-color) 0%,var(--primary-dark) 100%);color:#fff;padding:1rem 1.8rem;box-shadow:0 4px 15px rgba(0,0,0,.12);flex-shrink:0;display:flex;align-items:center;gap:1rem;position:relative;overflow:hidden}
.dashboard-header::before{content:'';position:absolute;top:-60%;right:-5%;width:180px;height:180px;background:rgba(255,255,255,.05);border-radius:50%}
.header-text{position:relative;z-index:1}.dashboard-header h1{font-size:1.4rem;font-weight:700;margin:0}.dashboard-header p{font-size:.8rem;opacity:.85;margin:0}
.breadcrumb{background:transparent;padding:0;margin:0;font-size:.75rem;position:relative;z-index:1;margin-left:auto}
.breadcrumb-item.active{color:rgba(255,255,255,.75)}.breadcrumb-item a{color:#fff;text-decoration:none;font-weight:500}
.dashboard-content{flex:1;overflow-y:auto;padding:1.5rem}.dashboard-content::-webkit-scrollbar{width:6px}.dashboard-content::-webkit-scrollbar-thumb{background:rgba(0,0,0,.18);border-radius:4px}
.form-wrapper{max-width:860px;margin:0 auto}.form-intro{margin-bottom:1.2rem}.form-intro h2{font-size:1.3rem;font-weight:700;color:var(--dark-text);margin-bottom:.2rem}.form-intro p{font-size:.85rem;color:var(--secondary-color)}
.form-section{background:var(--white);border-radius:10px;padding:1.2rem 1.5rem;margin-bottom:1rem;box-shadow:0 2px 10px rgba(0,0,0,.06);border:1px solid var(--border-color);animation:slideInUp .4s ease-out forwards;opacity:0}
.form-section:nth-child(1){animation-delay:.05s}
.section-title{font-size:.9rem;font-weight:700;color:var(--primary-color);margin-bottom:1rem;display:flex;align-items:center;gap:.5rem;padding-bottom:.6rem;border-bottom:2px solid var(--primary-light);text-transform:uppercase;letter-spacing:.5px}
.form-label{font-size:.78rem;font-weight:600;color:var(--dark-text);margin-bottom:.3rem}
.form-control,.form-select{font-size:.82rem;padding:.45rem .75rem;border:1.5px solid var(--border-color);border-radius:7px;background-color:var(--input-bg);color:var(--dark-text);transition:border-color .2s,box-shadow .2s}
.form-control:focus,.form-select:focus{border-color:var(--primary-color);box-shadow:0 0 0 3px rgba(12,78,212,.1);background-color:var(--white);outline:none}
.form-control::placeholder{color:#aab0bf;font-size:.8rem}.form-check-label{font-size:.78rem;color:var(--secondary-color)}.form-check-input:checked{background-color:var(--primary-color);border-color:var(--primary-color)}
.drop-zone{border:2px dashed #b0bcdf;border-radius:10px;background:var(--primary-light);padding:1.8rem 1rem;text-align:center;cursor:pointer;transition:all .25s ease;position:relative}
.drop-zone:hover,.drop-zone.dragover{border-color:var(--primary-color);background:#dce6fc}
.drop-zone input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.drop-zone i{font-size:2rem;color:var(--primary-color);margin-bottom:.5rem;display:block}.drop-zone p{font-size:.82rem;color:var(--dark-text);margin:0;font-weight:600}.drop-zone small{font-size:.73rem;color:var(--secondary-color)}
#file-name{font-size:.78rem;color:var(--success);margin-top:.4rem;display:none;font-weight:600}
.btn-submit{background:linear-gradient(135deg,var(--primary-color),var(--primary-dark));color:#fff;border:none;padding:.7rem 2rem;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .25s ease;box-shadow:0 4px 15px rgba(12,78,212,.3);display:flex;align-items:center;gap:.5rem;width:100%;justify-content:center}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(12,78,212,.4)}.btn-submit:disabled{opacity:.65;transform:none;cursor:not-allowed}
.alert-custom{border-radius:8px;font-size:.83rem;padding:.7rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.6rem}
.alert-success{background:rgba(40,167,69,.1);border:1px solid rgba(40,167,69,.25);color:#155724}
.alert-danger{background:rgba(220,53,69,.1);border:1px solid rgba(220,53,69,.25);color:#721c24}
.alert-warning{background:rgba(255,193,7,.1);border:1px solid rgba(255,193,7,.3);color:#856404}
.docs-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:.8rem}.docs-toolbar h3{font-size:1rem;font-weight:700;color:var(--dark-text);margin:0}
.toolbar-btn{background:var(--white);border:1.5px solid var(--border-color);border-radius:7px;padding:.35rem .65rem;font-size:.82rem;color:var(--dark-text);cursor:pointer;transition:all .2s}
.toolbar-btn:hover{border-color:var(--primary-color);color:var(--primary-color)}
.search-wrapper{position:relative;margin-bottom:.8rem;display:none}.search-wrapper.show{display:block}.search-wrapper input{padding-left:2rem;font-size:.82rem}.search-wrapper i{position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--secondary-color);font-size:.8rem}
.empty-state{background:var(--white);border-radius:10px;padding:2.5rem 1rem;text-align:center;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06)}
.empty-state i{font-size:3rem;color:#c5cde0;margin-bottom:.8rem}.empty-state h4{font-size:1rem;font-weight:700;color:var(--dark-text);margin-bottom:.3rem}.empty-state p{font-size:.82rem;color:var(--secondary-color);margin:0}
.doc-card{background:var(--white);border-radius:10px;padding:.9rem 1.1rem;margin-bottom:.7rem;border:1px solid var(--border-color);box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;align-items:center;gap:.9rem;transition:box-shadow .2s}
.doc-card:hover{box-shadow:0 6px 18px rgba(0,0,0,.09)}
.doc-icon{width:42px;height:42px;background:var(--primary-light);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:var(--primary-color);flex-shrink:0}
.doc-info{flex:1;min-width:0}.doc-name{font-size:.85rem;font-weight:700;color:var(--dark-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.doc-meta{font-size:.73rem;color:var(--secondary-color);margin-top:.1rem}
.doc-actions{display:flex;gap:.4rem;flex-shrink:0}
.doc-action-btn{background:none;border:1.5px solid var(--border-color);border-radius:6px;padding:.3rem .5rem;font-size:.75rem;color:var(--secondary-color);cursor:pointer;transition:all .2s;text-decoration:none;display:inline-flex;align-items:center}
.doc-action-btn:hover{border-color:var(--primary-color);color:var(--primary-color)}.doc-action-btn.danger:hover{border-color:var(--danger);color:var(--danger)}.doc-action-btn.disabled{opacity:.35;pointer-events:none}
.pdf-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:9999;display:none;align-items:center;justify-content:center}.pdf-modal-overlay.open{display:flex}
.pdf-modal{background:var(--white);border-radius:12px;width:90vw;max-width:900px;height:88vh;display:flex;flex-direction:column;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden}
.pdf-modal-header{background:linear-gradient(135deg,var(--primary-color),var(--primary-dark));color:#fff;padding:.85rem 1.2rem;display:flex;align-items:center;gap:.8rem;flex-shrink:0}
.pdf-modal-header span{font-size:.9rem;font-weight:700;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pdf-modal-btn{background:rgba(255,255,255,.2);border:none;border-radius:6px;color:#fff;width:30px;height:30px;cursor:pointer;font-size:.8rem;display:flex;align-items:center;justify-content:center;transition:background .2s;text-decoration:none}
.pdf-modal-btn:hover{background:rgba(255,255,255,.35);color:#fff}.pdf-modal iframe{flex:1;border:none;width:100%}
@keyframes slideInUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
@media(max-width:768px){body{flex-direction:column!important}.dashboard-content{padding:.8rem}.pdf-modal{width:97vw;height:92vh}.doc-actions{flex-wrap:wrap}}
</style>
</head>
<body>
<?php include '../../header/sidebar.php'; ?>

<!-- MODAL PDF -->
<div class="pdf-modal-overlay" id="pdfModal">
  <div class="pdf-modal">
    <div class="pdf-modal-header">
      <i class="fas fa-file-pdf"></i>
      <span id="pdfModalTitle">Documento</span>
      <a id="pdfModalNewTab" href="#" target="_blank" class="pdf-modal-btn" title="Abrir en nueva pestana" style="margin-right:.3rem">
        <i class="fas fa-external-link-alt"></i>
      </a>
      <button class="pdf-modal-btn" onclick="cerrarModal()" title="Cerrar"><i class="fas fa-times"></i></button>
    </div>
    <iframe id="pdfIframe" src=""></iframe>
  </div>
</div>

<div class="page-container">
  <div class="dashboard-header">
    <div class="header-text">
      <h1><i class="fas fa-file-upload"></i> Documentos</h1>
      <p>Gestiona y organiza tus certificaciones profesionales</p>
    </div>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i> Inicio</a></li>
        <li class="breadcrumb-item active">Documentos</li>
      </ol>
    </nav>
  </div>

  <div class="dashboard-content">
    <div class="form-wrapper">

      <div class="form-intro">
        <h2>Gestion de Documentos</h2>
        <p>Sube y administra tus certificaciones profesionales de manera eficiente.</p>
      </div>

      <?php if ($error): ?>
      <div class="alert-custom alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
      <div class="alert-custom alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
      <?php endif; ?>
      <?php if (empty($grados) || empty($instituciones)): ?>
      <div class="alert-custom alert-warning"><i class="fas fa-exclamation-triangle"></i>
        No se pudo conectar a la API. Verifica que Laravel este corriendo en <strong><?= API_BASE ?></strong>.
      </div>
      <?php endif; ?>

      <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-section">
          <div class="section-title"><i class="fas fa-cloud-upload-alt"></i> Subir Nuevo Documento</div>
          <div class="row g-3">

            <div class="col-12">
              <label class="form-label">Titulo del Documento <span style="color:#dc3545">*</span></label>
              <input type="text" class="form-control" name="titulo" placeholder="Ej. Certificacion AWS Cloud Architect" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
            </div>

            <!-- Grado Academico desde API -->
            <div class="col-md-6">
              <label class="form-label">Tipo / Grado Academico <span style="color:#dc3545">*</span></label>
              <select class="form-select" name="id_grado_est" required <?= empty($grados) ? 'disabled' : '' ?>>
                <option value="">Seleccione un tipo...</option>
                <?php foreach ($grados as $g): ?>
                <option value="<?= (int)$g['idGradoEst'] ?>" <?= (($_POST['id_grado_est'] ?? '') == $g['idGradoEst']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($g['nombreGradoEst']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <?php if (empty($grados)): ?><small class="text-danger">API no disponible</small><?php endif; ?>
            </div>

            <!-- Institucion desde API -->
            <div class="col-md-6">
              <label class="form-label">Institucion <span style="color:#dc3545">*</span></label>
              <select class="form-select" name="id_institucion" id="selectInstitucion"
                <?= (empty($instituciones) || isset($_POST['otra_institucion_check'])) ? 'disabled' : '' ?>>
                <option value="">Selecciona una institucion</option>
                <?php foreach ($instituciones as $inst): ?>
                <option value="<?= (int)$inst['idInstitucion'] ?>" <?= (($_POST['id_institucion'] ?? '') == $inst['idInstitucion']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($inst['nombreInstitucion']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-check mt-2">
                <input class="form-check-input" type="checkbox" id="chkOtraInstitucion" name="otra_institucion_check" <?= isset($_POST['otra_institucion_check']) ? 'checked' : '' ?>>
                <label class="form-check-label" for="chkOtraInstitucion">La institucion no esta en la lista</label>
              </div>
              <input type="text" class="form-control mt-2" id="inputOtraInstitucion" name="otra_institucion"
                placeholder="Escribe el nombre de la institucion"
                style="display:<?= isset($_POST['otra_institucion_check']) ? 'block' : 'none' ?>;"
                value="<?= htmlspecialchars($_POST['otra_institucion'] ?? '') ?>">
            </div>

            <!-- Provincia desde API -->
            <div class="col-md-6">
              <label class="form-label">Provincia <span style="color:#dc3545">*</span></label>
              <select class="form-select" name="provincia_pos" required <?= empty($provincias) ? 'disabled' : '' ?>>
                <option value="">Seleccione...</option>
                <?php foreach ($provincias as $idx => $prov): ?>
                <option value="<?= $idx + 1 ?>" <?= (($_POST['provincia_pos'] ?? '') == ($idx + 1)) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($prov['nombre_provincia'] ?? $prov['nombre'] ?? '') ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="col-md-4">
              <label class="form-label">Fecha de Inicio <span style="color:#dc3545">*</span></label>
              <input type="date" class="form-control" name="fecha_inicio" required value="<?= htmlspecialchars($_POST['fecha_inicio'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Fecha de Finalizacion <span style="color:#dc3545">*</span></label>
              <input type="date" class="form-control" name="fecha_fin" required value="<?= htmlspecialchars($_POST['fecha_fin'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Fecha de Emision <span style="color:#dc3545">*</span></label>
              <input type="date" class="form-control" name="fecha_emision" required value="<?= htmlspecialchars($_POST['fecha_emision'] ?? '') ?>">
            </div>

            <div class="col-md-4">
              <label class="form-label">Total de Horas <span style="color:#dc3545">*</span></label>
              <input type="number" class="form-control" name="total_horas" placeholder="Ej. 40" min="1" required value="<?= htmlspecialchars($_POST['total_horas'] ?? '') ?>">
            </div>

            <div class="col-12">
              <label class="form-label">Archivo PDF <span style="color:#dc3545">*</span></label>
              <div class="drop-zone" id="dropZone">
                <input type="file" name="archivo_pdf" id="filePdf" accept="application/pdf">
                <i class="fas fa-file-upload"></i>
                <p>Arrastra o haz clic para subir</p>
                <small>Solo archivos PDF (Max. 10MB)</small>
                <div id="file-name"></div>
              </div>
            </div>

            <div class="col-12">
              <button type="submit" class="btn-submit" id="btnSubmit">
                <i class="fas fa-plus-circle"></i> Subir Documento
              </button>
            </div>
          </div>
        </div>
      </form>

      <!-- LISTA -->
      <div class="docs-toolbar">
        <h3>
          <i class="fas fa-folder-open" style="color:var(--primary-color);margin-right:.4rem"></i>
          Tus Documentos
          <?php if (count($documentos) > 0): ?>
          <span style="font-size:.75rem;font-weight:600;background:var(--primary-light);color:var(--primary-color);padding:.1rem .5rem;border-radius:20px;margin-left:.4rem"><?= count($documentos) ?></span>
          <?php endif; ?>
        </h3>
        <button class="toolbar-btn" id="btnSearch" title="Buscar"><i class="fas fa-search"></i></button>
      </div>

      <div class="search-wrapper" id="searchWrapper">
        <i class="fas fa-search"></i>
        <input type="text" class="form-control" id="searchInput" placeholder="Buscar documento...">
      </div>

      <?php if (empty($documentos)): ?>
      <div class="empty-state">
        <i class="fas fa-file-circle-plus"></i>
        <h4>No tienes documentos subidos</h4>
        <p>Sube tu primer certificado para comenzar tu proceso</p>
      </div>
      <?php else: ?>
      <div id="docList">
        <?php foreach ($documentos as $doc):
          $idDoc     = (int)$doc['idDocumentoPostulante'];
          $tieneRuta = isset($rutasPorDoc[$idDoc]);
          $archUrl   = $tieneRuta ? $rutasPorDoc[$idDoc] : '';

          $nombreGrado = 'Desconocido';
          foreach ($grados as $g) { if ((int)$g['idGradoEst'] === (int)$doc['idGradoEst']) { $nombreGrado = $g['nombreGradoEst']; break; } }

          if (!empty($doc['otraInstitucionn']) && $doc['otraInstitucionn'] == 1) {
              $nombreInst = $doc['nombreOtraInstitucion'] ?? 'Otra institucion';
          } else {
              $nombreInst = 'Desconocida';
              foreach ($instituciones as $inst) { if ((int)$inst['idInstitucion'] === (int)$doc['institucion']) { $nombreInst = $inst['nombreInstitucion']; break; } }
          }
        ?>
        <div class="doc-card">
          <div class="doc-icon"><i class="fas fa-file-pdf"></i></div>
          <div class="doc-info">
            <div class="doc-name"><?= htmlspecialchars($doc['titulo']) ?></div>
            <div class="doc-meta">
              <?= htmlspecialchars($nombreInst) ?> &middot;
              <?= htmlspecialchars($nombreGrado) ?> &middot;
              <?= (int)$doc['totalHoras'] ?> hrs &middot;
              Emision: <?= htmlspecialchars($doc['fechaEmision'] ?? '') ?>
            </div>
          </div>
          <div class="doc-actions">
            <?php if ($tieneRuta): ?>
            <button class="doc-action-btn" onclick="abrirModal('<?= addslashes(htmlspecialchars($doc['titulo'])) ?>','<?= htmlspecialchars($archUrl) ?>')" title="Vista previa">
              <i class="fas fa-eye"></i>
            </button>
            <a class="doc-action-btn" href="?download=<?= $idDoc ?>" title="Descargar">
              <i class="fas fa-download"></i>
            </a>
            <?php else: ?>
            <span class="doc-action-btn disabled" title="Archivo no disponible"><i class="fas fa-eye-slash"></i></span>
            <span class="doc-action-btn disabled"><i class="fas fa-download"></i></span>
            <?php endif; ?>
            <button class="doc-action-btn danger" onclick="confirmarEliminar(<?= $idDoc ?>,'<?= addslashes(htmlspecialchars($doc['titulo'])) ?>')" title="Eliminar">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('chkOtraInstitucion').addEventListener('change',function(){
  var inp=document.getElementById('inputOtraInstitucion'),sel=document.getElementById('selectInstitucion');
  inp.style.display=this.checked?'block':'none'; inp.required=this.checked;
  sel.required=!this.checked; sel.disabled=this.checked;
  if(this.checked) sel.value='';
});
var dropZone=document.getElementById('dropZone'),fileInput=document.getElementById('filePdf'),fileName=document.getElementById('file-name');
fileInput.addEventListener('change',function(){if(this.files[0]){fileName.textContent='✔ '+this.files[0].name;fileName.style.display='block';}});
['dragover','dragenter'].forEach(function(e){dropZone.addEventListener(e,function(ev){ev.preventDefault();dropZone.classList.add('dragover');});});
['dragleave','drop'].forEach(function(e){dropZone.addEventListener(e,function(ev){ev.preventDefault();dropZone.classList.remove('dragover');if(e==='drop'&&ev.dataTransfer.files[0]){fileInput.files=ev.dataTransfer.files;fileName.textContent='✔ '+ev.dataTransfer.files[0].name;fileName.style.display='block';}});});
document.querySelector('form').addEventListener('submit',function(){var b=document.getElementById('btnSubmit');b.disabled=true;b.innerHTML='<i class="fas fa-spinner fa-spin"></i> Subiendo...';});
document.getElementById('btnSearch').addEventListener('click',function(){document.getElementById('searchWrapper').classList.toggle('show');document.getElementById('searchInput').focus();});
document.getElementById('searchInput').addEventListener('input',function(){var q=this.value.toLowerCase();document.querySelectorAll('.doc-card').forEach(function(c){c.style.display=c.querySelector('.doc-name').textContent.toLowerCase().includes(q)?'flex':'none';});});
function abrirModal(t,u){document.getElementById('pdfModalTitle').textContent=t;document.getElementById('pdfIframe').src=u;document.getElementById('pdfModalNewTab').href=u;document.getElementById('pdfModal').classList.add('open');}
function cerrarModal(){document.getElementById('pdfModal').classList.remove('open');document.getElementById('pdfIframe').src='';}
document.getElementById('pdfModal').addEventListener('click',function(e){if(e.target===this)cerrarModal();});
document.addEventListener('keydown',function(e){if(e.key==='Escape')cerrarModal();});
function confirmarEliminar(id,t){if(confirm('Eliminar "'+t+'"?\nEsta accion no se puede deshacer.')){window.location.href='?delete='+id;}}
setTimeout(function(){document.querySelectorAll('.alert-custom').forEach(function(a){a.style.transition='opacity .5s';a.style.opacity='0';setTimeout(function(){a.remove();},500);});},4000);
</script>
</body>
</html>