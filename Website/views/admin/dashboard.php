<?php
require_once '../../config/auth_admin.php';

if (session_status() === PHP_SESSION_NONE) session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

define('API_BASE', 'http://127.0.0.1:8000/api');

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

// ── Obtener estadísticas del dashboard ──────────────────────────
$stats = [
    'totalPostulantes'     => 0,
    'totalDocumentos'      => 0,
    'postulantesListos'    => 0,
    'edadPromedio'         => 0,
    'postulantesRecientes' => [],
];
$apiError = false;

$resp = apiGet('/dashboard/stats');

if ($resp && ($resp['success'] ?? false) && isset($resp['data']) && is_array($resp['data'])) {
    $d = $resp['data'];
    $stats['totalPostulantes']     = (int)($d['totalPostulantes'] ?? 0);
    $stats['totalDocumentos']      = (int)($d['totalDocumentos'] ?? 0);
    $stats['postulantesListos']    = (int)($d['postulantesListos'] ?? 0);
    $stats['edadPromedio']         = (int)($d['edadPromedio'] ?? 0);
    $stats['postulantesRecientes'] = is_array($d['postulantesRecientes'] ?? null) ? $d['postulantesRecientes'] : [];
} else {
    $apiError = true;
}

// ── Mapeo perfil académico → vacante (igual al de la app Android) ──
function vacantePorPerfil(?string $perfil): string {
    switch (strtoupper((string)$perfil)) {
        case 'LICENCIATURA': return 'Analista de Sistemas';
        case 'MAESTRIA':     return 'Project Manager';
        case 'TECNICO':      return 'Soporte Técnico';
        default:             return 'Vacante disponible';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Panel Administrador</title>
<link rel="icon" type="image/png" href="../../assets/newwayslogo.png">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../../header/sidebar.css">
<style>
:root{
    --primary-color:#0c4ed4; --primary-dark:#0a3fb0; --primary-light:#e8effe;
    --secondary-color:#6c757d; --light-bg:#f0f2f7; --white:#fff;
    --dark-text:#2c3e50; --border-color:#e0e4ef;
}
*{margin:0;padding:0;box-sizing:border-box}
html,body{height:100%}
body{display:flex!important;flex-direction:row!important;background:var(--light-bg);font-family:'Segoe UI',sans-serif;height:100vh}
.page-container{flex:1;display:flex;flex-direction:column;height:100vh;overflow:hidden}

.dashboard-header{background:linear-gradient(135deg,var(--primary-color),var(--primary-dark));color:#fff;padding:1rem 1.8rem;box-shadow:0 4px 15px rgba(0,0,0,.12);flex-shrink:0;display:flex;align-items:center;gap:1rem}
.dashboard-header h1{font-size:1.4rem;font-weight:700;margin:0}
.dashboard-header p{font-size:.8rem;opacity:.85;margin:0}
.breadcrumb{background:transparent;padding:0;margin:0;font-size:.75rem;margin-left:auto}
.breadcrumb-item.active{color:rgba(255,255,255,.75)}
.breadcrumb-item a{color:#fff;text-decoration:none;font-weight:500}

.dashboard-content{flex:1;overflow-y:auto;padding:1.5rem}
.content-wrapper{max-width:980px;margin:0 auto}

/* ── Tarjetas de estadísticas ── */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.5rem}
@media(max-width:992px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:576px){.stats-grid{grid-template-columns:1fr}}

.stat-card{background:var(--white);border-radius:12px;padding:1.1rem 1.2rem;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06);display:flex;align-items:flex-start;gap:.9rem}
.stat-icon{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.stat-icon.blue{background:#e8effe;color:#0c4ed4}
.stat-icon.green{background:#e3f6e8;color:#1f9d4d}
.stat-icon.purple{background:#efe7fb;color:#7c3aed}
.stat-icon.orange{background:#feeee0;color:#e0701d}
.stat-label{font-size:.78rem;color:var(--secondary-color);margin-bottom:.2rem}
.stat-value{font-size:1.4rem;font-weight:700;color:var(--dark-text)}

/* ── Candidatos recientes ── */
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:.9rem}
.section-header h3{font-size:1.05rem;font-weight:700;color:var(--dark-text);margin:0}
.section-header a{font-size:.82rem;font-weight:600;color:var(--primary-color);text-decoration:none}
.section-header a:hover{text-decoration:underline}

.candidate-card{background:var(--white);border-radius:12px;padding:1rem 1.1rem;margin-bottom:.8rem;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06)}
.candidate-top{display:flex;align-items:center;gap:.8rem}
.candidate-avatar{width:46px;height:46px;border-radius:50%;background:#e7e9ef;display:flex;align-items:center;justify-content:center;color:#9aa1b3;font-size:1.3rem;flex-shrink:0}
.candidate-info{flex:1;min-width:0}
.candidate-name{font-size:.92rem;font-weight:700;color:var(--dark-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.candidate-vacante{font-size:.8rem;color:var(--primary-color);font-weight:500}
.candidate-menu-btn{background:none;border:none;color:var(--secondary-color);font-size:1rem;padding:.3rem .5rem;cursor:pointer}
.candidate-menu-btn:hover{color:var(--dark-text)}
.candidate-divider{border-top:1px solid var(--border-color);margin:.7rem 0 .55rem}
.candidate-detail-link{font-size:.82rem;font-weight:600;color:var(--primary-color);text-decoration:none;display:inline-flex;align-items:center;gap:.4rem}
.candidate-detail-link:hover{text-decoration:underline}

.empty-state{background:var(--white);border-radius:10px;padding:2.2rem 1rem;text-align:center;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06)}
.empty-state i{font-size:2.6rem;color:#c5cde0;margin-bottom:.7rem}
.empty-state h4{font-size:.95rem;font-weight:700;color:var(--dark-text);margin-bottom:.2rem}
.empty-state p{font-size:.8rem;color:var(--secondary-color);margin:0}

.alert-custom{border-radius:8px;font-size:.83rem;padding:.7rem 1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem}
.alert-warning{background:rgba(255,193,7,.1);border:1px solid rgba(255,193,7,.3);color:#856404}
</style>
</head>
<body>

<?php include '../../header/sidebar_admin.php'; ?>

<div class="page-container">
    <div class="dashboard-header">
        <div>
            <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
            <p>Resumen general del proceso de selección</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <div class="dashboard-content">
        <div class="content-wrapper">

            <?php if ($apiError): ?>
            <div class="alert-custom alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se pudo conectar a la API. Verifica que Laravel esté corriendo en <strong><?= API_BASE ?></strong>.
            </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
                    <div>
                        <div class="stat-label">Total Postulantes</div>
                        <div class="stat-value"><?= $stats['totalPostulantes'] ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green"><i class="fas fa-file-alt"></i></div>
                    <div>
                        <div class="stat-label">Total Documentos</div>
                        <div class="stat-value"><?= $stats['totalDocumentos'] ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fas fa-check-circle"></i></div>
                    <div>
                        <div class="stat-label">Postulantes Listos</div>
                        <div class="stat-value"><?= $stats['postulantesListos'] ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon orange"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="stat-label">Edad Promedio</div>
                        <div class="stat-value"><?= $stats['edadPromedio'] ?> años</div>
                    </div>
                </div>
            </div>

            <div class="section-header">
                <h3>Candidatos Recientes</h3>
                <a href="postulantes.php">Ver todos</a>
            </div>

            <?php if (empty($stats['postulantesRecientes'])): ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <h4>Aún no hay candidatos</h4>
                <p>Los postulantes recientes aparecerán aquí.</p>
            </div>
            <?php else: ?>

            <?php foreach ($stats['postulantesRecientes'] as $cand):
                if (!is_array($cand)) continue;
                $nombre       = $cand['nombreCompleto'] ?? 'Sin nombre';
                $idPostulante = (int)($cand['idPostulante'] ?? 0);
                $idUsuario    = (int)($cand['idUsuario'] ?? 0);
                $vacante      = vacantePorPerfil($cand['perfil'] ?? null);
            ?>
            <div class="candidate-card">
                <div class="candidate-top">
                    <div class="candidate-avatar"><i class="fas fa-user"></i></div>
                    <div class="candidate-info">
                        <div class="candidate-name"><?= htmlspecialchars($nombre) ?></div>
                        <div class="candidate-vacante"><?= htmlspecialchars($vacante) ?></div>
                    </div>
                    <div class="dropdown">
                        <button class="candidate-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="perfil_candidato.php?idPostulante=<?= $idPostulante ?>&idUsuario=<?= $idUsuario ?>"><i class="fas fa-eye me-2"></i>Ver Perfil</a></li>
                        </ul>
                    </div>
                </div>
                <div class="candidate-divider"></div>
                <a class="candidate-detail-link" href="perfil_candidato.php?idPostulante=<?= $idPostulante ?>&idUsuario=<?= $idUsuario ?>">
                    Ver Detalle <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>