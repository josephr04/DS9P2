<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no hay sesión activa, redirigir al login
if (!isset($_SESSION['idUsuario'])) {
    header('Location: /ds9p2/Website/views/user/login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — NewWays</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">

    <style>
        :root {
            --nw-blue:        #0c4ed4;
            --nw-blue-dark:   #0a3fb0;
            --nw-blue-light:  #eff6ff;
            --nw-blue-mid:    #dbeafe;
            --nw-text:        #0f172a;
            --nw-muted:       #64748b;
            --nw-border:      #e2e8f0;
            --nw-surface:     #ffffff;
            --nw-bg:          #f4f7fb;
            --nw-sidebar-bg:  #0f172a;
            --nw-sidebar-w:   240px;
            --nw-green:       #16a34a;
            --nw-green-bg:    #f0fdf4;
            --nw-amber:       #d97706;
            --nw-amber-bg:    #fffbeb;
            --nw-red:         #dc2626;
            --nw-red-bg:      #fef2f2;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--nw-bg);
            color: var(--nw-text);
        }

        /* ════════════════════════════════
           LAYOUT: sidebar + main
        ════════════════════════════════ */
        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        /* ─── SIDEBAR ─── */
        .sidebar {
            width: var(--nw-sidebar-w);
            background: var(--nw-sidebar-bg);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-logo {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.07);
        }
        .sidebar-logo a {
            font-family: 'DM Serif Display', serif;
            font-size: 1.35rem;
            color: #fff;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 9px;
        }
        .sidebar-logo a i { color: #6ea8fe; font-size: 1rem; }
        .sidebar-logo .sidebar-sub {
            font-family: 'DM Sans', sans-serif;
            font-size: .75rem;
            color: #94a3b8;
            margin-top: 2px;
            padding-left: 26px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 16px 12px;
        }
        .nav-section-label {
            font-size: .68rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #475569;
            padding: 12px 8px 6px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            color: #94a3b8;
            text-decoration: none;
            font-size: .88rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            margin-bottom: 2px;
        }
        .nav-item i { font-size: 1rem; width: 18px; text-align: center; }
        .nav-item:hover {
            background: rgba(255,255,255,.06);
            color: #e2e8f0;
        }
        .nav-item.active {
            background: var(--nw-blue);
            color: #fff;
        }
        .nav-item .badge-count {
            margin-left: auto;
            background: rgba(255,255,255,.15);
            color: #e2e8f0;
            font-size: .7rem;
            padding: 2px 7px;
            border-radius: 20px;
        }
        .nav-item.active .badge-count {
            background: rgba(255,255,255,.25);
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s;
        }
        .sidebar-user:hover { background: rgba(255,255,255,.06); }
        .sidebar-user img {
            width: 34px; height: 34px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255,255,255,.15);
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-name {
            font-size: .82rem;
            font-weight: 600;
            color: #e2e8f0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-role {
            font-size: .72rem;
            color: #64748b;
        }
        .sidebar-user i { color: #475569; font-size: .85rem; }

        /* ─── MAIN AREA ─── */
        .main-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top bar */
        .topbar {
            background: var(--nw-surface);
            border-bottom: 1px solid var(--nw-border);
            padding: 0 28px;
            height: 58px;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }
        .topbar-search {
            flex: 1;
            max-width: 380px;
            position: relative;
        }
        .topbar-search i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--nw-muted);
            font-size: .9rem;
        }
        .topbar-search input {
            width: 100%;
            padding: 8px 14px 8px 36px;
            border: 1px solid var(--nw-border);
            border-radius: 8px;
            font-size: .88rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--nw-text);
            background: var(--nw-bg);
            outline: none;
            transition: border-color .15s;
        }
        .topbar-search input:focus {
            border-color: var(--nw-blue);
            background: var(--nw-surface);
            box-shadow: 0 0 0 3px rgba(12,78,212,.1);
        }
        .topbar-search input::placeholder { color: #94a3b8; }

        .topbar-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            border: 1px solid var(--nw-border);
            background: var(--nw-surface);
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--nw-muted);
            position: relative;
            transition: background .15s, border-color .15s;
        }
        .btn-icon:hover { background: var(--nw-bg); border-color: #cbd5e1; }
        .notif-dot {
            position: absolute;
            top: 7px; right: 7px;
            width: 7px; height: 7px;
            background: var(--nw-red);
            border-radius: 50%;
            border: 1.5px solid var(--nw-surface);
        }

        /* ─── PAGE BODY ─── */
        .page-body {
            flex: 1;
            overflow-y: auto;
            padding: 32px 32px 40px;
        }

        .page-title {
            font-family: 'DM Serif Display', serif;
            font-size: 1.9rem;
            color: var(--nw-text);
            margin-bottom: 24px;
        }

        /* ── Stat cards ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: var(--nw-surface);
            border: 1px solid var(--nw-border);
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: box-shadow .15s, transform .15s;
            cursor: default;
        }
        .stat-card:hover {
            box-shadow: 0 6px 20px rgba(12,78,212,.09);
            transform: translateY(-1px);
        }
        .stat-icon {
            width: 46px; height: 46px;
            border-radius: 11px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .si-blue   { background: var(--nw-blue-light); color: var(--nw-blue); }
        .si-green  { background: var(--nw-green-bg);   color: var(--nw-green); }
        .si-amber  { background: var(--nw-amber-bg);   color: var(--nw-amber); }
        .si-red    { background: var(--nw-red-bg);     color: var(--nw-red); }
        .stat-label {
            font-size: .75rem;
            font-weight: 500;
            color: var(--nw-muted);
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-bottom: 3px;
        }
        .stat-value {
            font-size: 1.65rem;
            font-weight: 600;
            color: var(--nw-text);
            line-height: 1.1;
        }

        /* ── Section header ── */
        .section-hdr {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .section-hdr h2 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--nw-text);
        }
        .link-ver {
            font-size: .85rem;
            font-weight: 600;
            color: var(--nw-blue);
            text-decoration: none;
        }
        .link-ver:hover { text-decoration: underline; }

        /* ── Candidate cards grid ── */
        .candidate-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }
        .candidate-card {
            background: var(--nw-surface);
            border: 1px solid var(--nw-border);
            border-radius: 14px;
            padding: 22px 20px 16px;
            display: flex;
            flex-direction: column;
            transition: box-shadow .15s, transform .15s;
        }
        .candidate-card:hover {
            box-shadow: 0 6px 20px rgba(12,78,212,.09);
            transform: translateY(-2px);
        }
        .candidate-avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
            border: 2px solid var(--nw-blue-mid);
        }
        .candidate-name {
            font-size: .97rem;
            font-weight: 600;
            color: var(--nw-text);
            margin-bottom: 2px;
        }
        .candidate-role {
            font-size: .83rem;
            font-weight: 500;
            color: var(--nw-blue);
            margin-bottom: 5px;
        }
        .candidate-date {
            font-size: .77rem;
            color: var(--nw-muted);
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 16px;
        }
        .divider { border: none; border-top: 1px solid var(--nw-border); margin-bottom: 12px; }
        .candidate-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: auto;
        }
        .link-detalle {
            font-size: .83rem;
            font-weight: 600;
            color: var(--nw-blue);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .link-detalle:hover { text-decoration: underline; }
        .btn-more {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--nw-muted);
            padding: 4px 6px;
            border-radius: 6px;
            font-size: 1rem;
            transition: background .15s;
        }
        .btn-more:hover { background: var(--nw-bg); color: var(--nw-text); }

        .badge-estado {
            font-size: .72rem;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
        }
        .badge-nuevo { background: var(--nw-blue-light); color: var(--nw-blue); }
        .badge-rev   { background: var(--nw-amber-bg);   color: var(--nw-amber); }
        .badge-aprob { background: var(--nw-green-bg);   color: var(--nw-green); }

        /* ── Quick actions bar ── */
        .quick-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 28px;
        }
        .btn-quick {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: .85rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid var(--nw-border);
            background: var(--nw-surface);
            color: var(--nw-text);
            font-family: 'DM Sans', sans-serif;
            transition: background .15s, border-color .15s;
            text-decoration: none;
        }
        .btn-quick:hover { background: var(--nw-bg); border-color: #cbd5e1; }
        .btn-quick.primary {
            background: var(--nw-blue);
            border-color: var(--nw-blue);
            color: #fff;
        }
        .btn-quick.primary:hover { background: var(--nw-blue-dark); }

        /* ── Responsive ── */
        @media (max-width: 1100px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .candidate-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .sidebar { display: none; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
            .candidate-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="app-shell">

    <!-- ══════════════════════════════════
         SIDEBAR
    ══════════════════════════════════ -->
    <aside class="sidebar">

        <div class="sidebar-logo">
            <a href="dashboard.php">
                <i class="fa-solid fa-route"></i> NewWays
            </a>
            <div class="sidebar-sub">Recruitment Manager</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Principal</div>

            <a href="dashboard.php" class="nav-item active">
                <i class="fa-solid fa-grip"></i>
                Dashboard
            </a>
            <a href="applicants.php" class="nav-item">
                <i class="fa-solid fa-users"></i>
                Applicants
                <span class="badge-count">1,284</span>
            </a>
            <a href="reports.php" class="nav-item">
                <i class="fa-solid fa-chart-bar"></i>
                Reports
            </a>

            <div class="nav-section-label" style="margin-top:8px;">Sistema</div>

            <a href="settings.php" class="nav-item">
                <i class="fa-solid fa-gear"></i>
                Settings
            </a>
            <a href="login.php" class="nav-item">
                <i class="fa-solid fa-right-from-bracket"></i>
                Cerrar sesión
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="settings.php" class="sidebar-user" style="text-decoration:none;">
                <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=80"
                     alt="Admin">
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">Admin Administrator</div>
                    <div class="sidebar-user-role">v1.0.4</div>
                </div>
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </a>
        </div>

    </aside>

    <!-- ══════════════════════════════════
         MAIN AREA
    ══════════════════════════════════ -->
    <div class="main-area">

        <!-- Topbar -->
        <header class="topbar">
            <div class="topbar-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Filtrar candidatos..." id="search-input">
            </div>
            <div class="topbar-actions">
                <button class="btn-icon" aria-label="Notificaciones">
                    <i class="fa-regular fa-bell"></i>
                    <span class="notif-dot"></span>
                </button>
                <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=80"
                     alt="Admin"
                     style="width:32px;height:32px;border-radius:50%;object-fit:cover;cursor:pointer;border:2px solid var(--nw-border);">
            </div>
        </header>

        <!-- Page body -->
        <div class="page-body">

            <h1 class="page-title">Dashboard</h1>

            <!-- Quick actions -->
            <div class="quick-bar">
                <a href="datosPersonales.php" class="btn-quick primary">
                    <i class="fa-solid fa-user-plus"></i> Nuevo postulante
                </a>
                <a href="reports.php" class="btn-quick">
                    <i class="fa-solid fa-file-export"></i> Exportar reporte
                </a>
                <a href="applicants.php" class="btn-quick">
                    <i class="fa-solid fa-filter"></i> Filtrar
                </a>
            </div>

            <!-- Stat cards -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-icon si-blue">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Applicants</div>
                        <div class="stat-value" data-target="1284">1,284</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon si-green">
                        <i class="fa-regular fa-file-lines"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Documents</div>
                        <div class="stat-value" data-target="4592">4,592</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon si-amber">
                        <i class="fa-solid fa-briefcase-clock"></i>
                    </div>
                    <div>
                        <div class="stat-label">Postulantes Hoy</div>
                        <div class="stat-value" data-target="86">86</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon si-red">
                        <i class="fa-regular fa-circle-check"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Vacantes</div>
                        <div class="stat-value" data-target="12">12</div>
                    </div>
                </div>
            </div>

            <!-- Candidatos recientes -->
            <div class="section-hdr">
                <h2>Candidatos Recientes</h2>
                <a href="applicants.php" class="link-ver">Ver todos</a>
            </div>

            <div class="candidate-grid" id="candidate-grid">

                <div class="candidate-card" data-name="Ana García Méndez" data-role="Senior Software Engineer">
                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=80"
                         alt="Ana García Méndez" class="candidate-avatar">
                    <div class="candidate-name">Ana García Méndez</div>
                    <div class="candidate-role">Senior Software Engineer</div>
                    <div class="candidate-date">
                        <i class="fa-regular fa-calendar"></i> Applied on Oct 12, 2023
                    </div>
                    <hr class="divider">
                    <div class="candidate-footer">
                        <a href="perfilAspirante.php" class="link-detalle">
                            Ver Detalle <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="badge-estado badge-nuevo">Nuevo</span>
                            <button class="btn-more" aria-label="Opciones">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="candidate-card" data-name="Carlos Ruiz Zepeda" data-role="Project Manager">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?q=80&w=80"
                         alt="Carlos Ruiz Zepeda" class="candidate-avatar">
                    <div class="candidate-name">Carlos Ruiz Zepeda</div>
                    <div class="candidate-role">Project Manager</div>
                    <div class="candidate-date">
                        <i class="fa-regular fa-calendar"></i> Applied on Oct 10, 2023
                    </div>
                    <hr class="divider">
                    <div class="candidate-footer">
                        <a href="perfilAspirante.php" class="link-detalle">
                            Ver Detalle <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="badge-estado badge-rev">En revisión</span>
                            <button class="btn-more" aria-label="Opciones">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="candidate-card" data-name="Roberto Valdez" data-role="UX Designer">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=80"
                         alt="Roberto Valdez" class="candidate-avatar">
                    <div class="candidate-name">Roberto Valdez</div>
                    <div class="candidate-role">UX Designer</div>
                    <div class="candidate-date">
                        <i class="fa-regular fa-calendar"></i> Applied on Oct 09, 2023
                    </div>
                    <hr class="divider">
                    <div class="candidate-footer">
                        <a href="perfilAspirante.php" class="link-detalle">
                            Ver Detalle <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span class="badge-estado badge-aprob">Aprobado</span>
                            <button class="btn-more" aria-label="Opciones">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
    /* ── Animación numérica ── */
    document.querySelectorAll('.stat-value[data-target]').forEach(el => {
        const end  = parseInt(el.dataset.target, 10);
        const dur  = 900;
        const step = Math.ceil(end / (dur / 16));
        let cur    = 0;
        const tick = () => {
            cur = Math.min(cur + step, end);
            el.textContent = cur.toLocaleString('en-US');
            if (cur < end) requestAnimationFrame(tick);
        };
        requestAnimationFrame(tick);
    });

    /* ── Búsqueda en tiempo real ── */
    document.getElementById('search-input').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#candidate-grid .candidate-card').forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const role = card.dataset.role.toLowerCase();
            card.style.display = (name.includes(q) || role.includes(q)) ? '' : 'none';
        });
    });
})();
</script>
</body>
</html>