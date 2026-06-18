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

function perfilPorRango(?int $rango): string {
    return match($rango) {
        1 => 'TECNICO',
        2, 3 => 'LICENCIATURA',
        4 => 'POSTGRADO',
        5 => 'MAESTRIA',
        default => ''
    };
}

function labelPorRango(?int $rango): string {
    return match($rango) {
        1 => 'Técnico',
        2, 3 => 'Licenciatura',
        4 => 'Postgrado',
        5 => 'Maestría',
        default => 'Sin perfil'
    };
}

function vacantePorRango(?int $rango): string {
    return match($rango) {
        1 => 'Soporte Técnico',
        2, 3 => 'Analista de Sistemas',
        4 => 'Analista Senior',
        5 => 'Project Manager',
        default => 'Vacante disponible'
    };
}

// ── Obtener postulantes ──────────────────────────────────────────
$postulantes = [];
$apiError    = false;

$resp = apiGet('/postulantes');

if (is_array($resp)) {
    // Caso 1: array directo [ {...}, {...} ]
    if (isset($resp[0])) {
        $postulantes = $resp;
    }
    // Caso 2: { success: true, data: [...] }
    elseif (($resp['success'] ?? false) && isset($resp['data'])) {
        $postulantes = $resp['data'];
    }
    // Caso 3: { data: [...] } sin success
    elseif (isset($resp['data']) && is_array($resp['data'])) {
        $postulantes = $resp['data'];
    }
    else {
        $apiError = true;
    }
} else {
    $apiError = true;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Postulantes - Panel Administrador</title>
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

.page-header{background:linear-gradient(135deg,var(--primary-color),var(--primary-dark));color:#fff;padding:1rem 1.8rem;box-shadow:0 4px 15px rgba(0,0,0,.12);flex-shrink:0;display:flex;align-items:center;gap:1rem}
.page-header h1{font-size:1.4rem;font-weight:700;margin:0}
.page-header p{font-size:.8rem;opacity:.85;margin:0}
.breadcrumb{background:transparent;padding:0;margin:0;font-size:.75rem;margin-left:auto}
.breadcrumb-item.active{color:rgba(255,255,255,.75)}
.breadcrumb-item a{color:#fff;text-decoration:none;font-weight:500}

.page-content{flex:1;overflow-y:auto;padding:1.5rem}
.content-wrapper{max-width:980px;margin:0 auto}

.search-filter-bar{background:var(--white);border-radius:12px;padding:1rem 1.2rem;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06);margin-bottom:1.2rem}
.search-row{display:flex;gap:.7rem;align-items:center;flex-wrap:wrap}
.search-input-wrap{flex:1;min-width:220px;position:relative}
.search-input-wrap i{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:#9aa1b3;font-size:.88rem}
.search-input{width:100%;border:1.5px solid var(--border-color);border-radius:8px;padding:.52rem .9rem .52rem 2.2rem;font-size:.87rem;color:var(--dark-text);background:var(--light-bg);transition:border-color .2s}
.search-input:focus{outline:none;border-color:var(--primary-color);background:#fff}
.filter-select{border:1.5px solid var(--border-color);border-radius:8px;padding:.52rem .9rem;font-size:.85rem;color:var(--dark-text);background:var(--light-bg);cursor:pointer;transition:border-color .2s;min-width:150px}
.filter-select:focus{outline:none;border-color:var(--primary-color);background:#fff}
.btn-reset{background:none;border:1.5px solid var(--border-color);border-radius:8px;padding:.52rem .9rem;font-size:.83rem;color:var(--secondary-color);cursor:pointer;transition:all .2s;white-space:nowrap}
.btn-reset:hover{border-color:#aaa;color:var(--dark-text)}

.results-bar{display:flex;align-items:center;justify-content:space-between;margin-bottom:.8rem}
.results-count{font-size:.85rem;color:var(--secondary-color)}
.results-count strong{color:var(--primary-color)}
.sort-select{border:1.5px solid var(--border-color);border-radius:8px;padding:.4rem .75rem;font-size:.82rem;color:var(--dark-text);background:var(--white);cursor:pointer}
.sort-select:focus{outline:none;border-color:var(--primary-color)}

.candidate-card{background:var(--white);border-radius:12px;padding:1rem 1.1rem;margin-bottom:.75rem;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06);transition:box-shadow .2s,border-color .2s}
.candidate-card:hover{box-shadow:0 4px 18px rgba(12,78,212,.1);border-color:rgba(12,78,212,.2)}
.candidate-top{display:flex;align-items:center;gap:.85rem}
.candidate-avatar{width:46px;height:46px;border-radius:50%;background:var(--primary-light);display:flex;align-items:center;justify-content:center;color:var(--primary-color);font-size:1.2rem;flex-shrink:0;font-weight:700}
.candidate-info{flex:1;min-width:0}
.candidate-name{font-size:.93rem;font-weight:700;color:var(--dark-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.candidate-vacante{font-size:.79rem;color:var(--primary-color);font-weight:500}
.candidate-perfil-badge{display:inline-block;font-size:.7rem;font-weight:600;padding:.17rem .55rem;border-radius:20px;margin-top:.2rem}
.badge-licenciatura{background:#e8effe;color:#0c4ed4}
.badge-maestria{background:#efe7fb;color:#7c3aed}
.badge-tecnico{background:#e3f6e8;color:#1f9d4d}
.badge-postgrado{background:#feeee0;color:#e0701d}
.badge-other{background:#f0f2f7;color:#6c757d}
.candidate-meta{display:flex;align-items:center;gap:1rem;flex-wrap:wrap}
.candidate-divider{border-top:1px solid var(--border-color);margin:.7rem 0 .55rem}
.candidate-footer{display:flex;align-items:center;justify-content:space-between}
.candidate-detail-link{font-size:.82rem;font-weight:600;color:var(--primary-color);text-decoration:none;display:inline-flex;align-items:center;gap:.4rem}
.candidate-detail-link:hover{text-decoration:underline}
.candidate-menu-btn{background:none;border:none;color:var(--secondary-color);font-size:1rem;padding:.3rem .5rem;cursor:pointer}
.candidate-menu-btn:hover{color:var(--dark-text)}
.candidate-edad{font-size:.78rem;color:var(--secondary-color);display:flex;align-items:center;gap:.3rem}

.empty-state{background:var(--white);border-radius:12px;padding:3rem 1rem;text-align:center;border:1px solid var(--border-color);box-shadow:0 2px 10px rgba(0,0,0,.06)}
.empty-state i{font-size:2.8rem;color:#c5cde0;margin-bottom:.8rem;display:block}
.empty-state h4{font-size:.95rem;font-weight:700;color:var(--dark-text);margin-bottom:.3rem}
.empty-state p{font-size:.82rem;color:var(--secondary-color);margin:0}
.btn-clear-search{margin-top:1rem;background:none;border:1.5px solid var(--border-color);border-radius:8px;padding:.45rem 1rem;font-size:.83rem;color:var(--primary-color);font-weight:600;cursor:pointer}
.btn-clear-search:hover{background:var(--primary-light)}

.alert-custom{border-radius:8px;font-size:.83rem;padding:.7rem 1rem;margin-bottom:1.2rem;display:flex;align-items:center;gap:.6rem;background:rgba(255,193,7,.1);border:1px solid rgba(255,193,7,.3);color:#856404}

mark{background:#fff3b0;border-radius:2px;padding:0 1px}
</style>
</head>
<body>

<?php include '../../header/sidebar_admin.php'; ?>

<div class="page-container">
    <div class="page-header">
        <div>
            <h1><i class="fas fa-users"></i> Postulantes</h1>
            <p>Gestión y búsqueda de candidatos registrados</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="dashboard.php"><i class="fas fa-home"></i> Inicio</a></li>
                <li class="breadcrumb-item active">Postulantes</li>
            </ol>
        </nav>
    </div>

    <div class="page-content">
        <div class="content-wrapper">

            <?php if ($apiError): ?>
            <div class="alert-custom">
                <i class="fas fa-exclamation-triangle"></i>
                No se pudo conectar a la API. Verifica que Laravel esté corriendo en <strong><?= API_BASE ?></strong>.
            </div>
            <?php endif; ?>

            <div class="search-filter-bar">
                <div class="search-row">
                    <div class="search-input-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" class="search-input"
                               placeholder="Buscar por nombre, vacante o perfil...">
                    </div>
                    <select id="filterPerfil" class="filter-select">
                        <option value="">Todos los perfiles</option>
                        <option value="LICENCIATURA">Licenciatura</option>
                        <option value="MAESTRIA">Maestría</option>
                        <option value="TECNICO">Técnico</option>
                        <option value="POSTGRADO">Postgrado</option>
                    </select>
                    <button class="btn-reset" id="btnReset">
                        <i class="fas fa-times me-1"></i>Limpiar
                    </button>
                </div>
            </div>

            <div class="results-bar">
                <div class="results-count">
                    Mostrando <strong id="visibleCount">0</strong> de
                    <strong><?= count($postulantes) ?></strong> postulantes
                </div>
                <select id="sortSelect" class="sort-select">
                    <option value="nombre-asc">Nombre A–Z</option>
                    <option value="nombre-desc">Nombre Z–A</option>
                    <option value="perfil">Por perfil</option>
                </select>
            </div>

            <div id="candidateList">
                <?php if (empty($postulantes)): ?>
                <div class="empty-state">
                    <i class="fas fa-user-slash"></i>
                    <h4>Aún no hay postulantes</h4>
                    <p>Los candidatos registrados aparecerán aquí.</p>
                </div>
                <?php else: ?>
                <?php foreach ($postulantes as $cand):
                    if (!is_array($cand)) continue;

                    $partes = array_filter([
                        $cand['nombre']    ?? '',
                        $cand['nombre2']   ?? '',
                        $cand['apellido']  ?? '',
                        $cand['apellido2'] ?? '',
                    ]);
                    $nombre = trim(implode(' ', $partes)) ?: 'Sin nombre';

                    $idPostulante = (int)($cand['idPostulante'] ?? 0);
                    $idUsuario    = (int)($cand['idUsuario']    ?? 0);
                    $rango        = isset($cand['rangoAcademico']) ? (int)$cand['rangoAcademico'] : null;
                    $perfil       = perfilPorRango($rango);
                    $perfilLabel  = labelPorRango($rango);
                    $vacante      = vacantePorRango($rango);

                    $edad = 0;
                    if (!empty($cand['fechaNacimiento'])) {
                        try {
                            $edad = (int)(new DateTime($cand['fechaNacimiento']))->diff(new DateTime())->y;
                        } catch (Exception $e) {}
                    }

                    $inicial    = strtoupper(mb_substr($nombre, 0, 1));
                    $badgeClass = match($perfil) {
                        'LICENCIATURA' => 'badge-licenciatura',
                        'MAESTRIA'     => 'badge-maestria',
                        'TECNICO'      => 'badge-tecnico',
                        'POSTGRADO'    => 'badge-postgrado',
                        default        => 'badge-other'
                    };
                ?>
                <div class="candidate-card"
                     data-nombre="<?= htmlspecialchars(strtolower($nombre)) ?>"
                     data-perfil="<?= htmlspecialchars($perfil) ?>"
                     data-vacante="<?= htmlspecialchars(strtolower($vacante)) ?>">
                    <div class="candidate-top">
                        <div class="candidate-avatar"><?= htmlspecialchars($inicial) ?></div>
                        <div class="candidate-info">
                            <div class="candidate-name candidate-highlight-name"><?= htmlspecialchars($nombre) ?></div>
                            <div class="candidate-vacante"><?= htmlspecialchars($vacante) ?></div>
                            <span class="candidate-perfil-badge <?= $badgeClass ?>"><?= $perfilLabel ?></span>
                        </div>
                        <div class="candidate-meta">
                            <?php if ($edad > 0): ?>
                            <span class="candidate-edad">
                                <i class="fas fa-birthday-cake"></i> <?= $edad ?> años
                            </span>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown">
                            <button class="candidate-menu-btn" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="perfil_candidato.php?idPostulante=<?= $idPostulante ?>&idUsuario=<?= $idUsuario ?>">
                                        <i class="fas fa-eye me-2"></i>Ver Perfil
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="documentos.php?idPostulante=<?= $idPostulante ?>">
                                        <i class="fas fa-file-alt me-2"></i>Ver Documentos
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="candidate-divider"></div>
                    <div class="candidate-footer">
                        <a class="candidate-detail-link"
                           href="perfil_candidato.php?idPostulante=<?= $idPostulante ?>&idUsuario=<?= $idUsuario ?>">
                            Ver Detalle <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div id="no-results" class="empty-state" style="display:none">
                <i class="fas fa-search"></i>
                <h4>Sin resultados</h4>
                <p>No se encontraron postulantes con ese criterio.</p>
                <button class="btn-clear-search" id="btnClearSearch">Limpiar búsqueda</button>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const searchInput    = document.getElementById('searchInput');
    const filterPerfil   = document.getElementById('filterPerfil');
    const sortSelect     = document.getElementById('sortSelect');
    const btnReset       = document.getElementById('btnReset');
    const btnClearSearch = document.getElementById('btnClearSearch');
    const visibleCount   = document.getElementById('visibleCount');
    const noResults      = document.getElementById('no-results');
    const list           = document.getElementById('candidateList');
    const allCards       = Array.from(list.querySelectorAll('.candidate-card'));

    function highlight(text, query) {
        if (!query) return text;
        const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        return text.replace(new RegExp(`(${escaped})`, 'gi'), '<mark>$1</mark>');
    }

    function applyFilters() {
        const query  = searchInput.value.trim().toLowerCase();
        const perfil = filterPerfil.value.toUpperCase();
        const sort   = sortSelect.value;

        let sorted = [...allCards];
        if (sort === 'nombre-asc')  sorted.sort((a,b) => a.dataset.nombre.localeCompare(b.dataset.nombre,'es'));
        if (sort === 'nombre-desc') sorted.sort((a,b) => b.dataset.nombre.localeCompare(a.dataset.nombre,'es'));
        if (sort === 'perfil')      sorted.sort((a,b) => a.dataset.perfil.localeCompare(b.dataset.perfil,'es'));

        let count = 0;
        sorted.forEach(card => {
            const matchSearch = !query ||
                card.dataset.nombre.includes(query)  ||
                card.dataset.vacante.includes(query) ||
                card.dataset.perfil.toLowerCase().includes(query);
            const matchPerfil = !perfil || card.dataset.perfil === perfil;

            if (matchSearch && matchPerfil) {
                card.style.display = '';
                count++;
                const nameEl = card.querySelector('.candidate-highlight-name');
                if (nameEl) nameEl.innerHTML = highlight(nameEl.textContent, query);
                list.appendChild(card);
            } else {
                card.style.display = 'none';
            }
        });

        visibleCount.textContent = count;
        noResults.style.display = (count === 0 && allCards.length > 0) ? 'block' : 'none';
    }

    function reset() {
        searchInput.value  = '';
        filterPerfil.value = '';
        sortSelect.value   = 'nombre-asc';
        allCards.forEach(card => {
            const nameEl = card.querySelector('.candidate-highlight-name');
            if (nameEl) nameEl.innerHTML = nameEl.textContent;
        });
        applyFilters();
    }

    searchInput.addEventListener('input', applyFilters);
    filterPerfil.addEventListener('change', applyFilters);
    sortSelect.addEventListener('change', applyFilters);
    btnReset.addEventListener('click', reset);
    if (btnClearSearch) btnClearSearch.addEventListener('click', reset);

    applyFilters();
})();
</script>
</body>
</html>