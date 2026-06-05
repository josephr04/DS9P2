<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Postulaciones</title>
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

        /* ── HEADER compacto ── */
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

        /* ── SECCIÓN ESTADÍSTICAS ── */
        .stats-section { margin-bottom: 1.2rem; }

        .stats-section h2 {
            font-size: 0.95rem;
            color: var(--secondary-color);
            margin-bottom: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.8rem;
            margin-bottom: 0.8rem;
        }

        /* ── KPI CARD compacta ── */
        .kpi-card {
            background: var(--white);
            border-radius: 10px;
            padding: 1rem 1.1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }

        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--info-color));
        }

        .kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }

        .kpi-icon {
            font-size: 1.3rem;
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            flex-shrink: 0;
        }

        .kpi-icon.primary { background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); }
        .kpi-icon.success { background: linear-gradient(135deg, var(--success-color), #218838); }
        .kpi-icon.info    { background: linear-gradient(135deg, var(--info-color), #117a8b); }
        .kpi-icon.warning { background: linear-gradient(135deg, var(--warning-color), #e0a800); }

        .kpi-body { flex: 1; min-width: 0; }

        .kpi-label {
            font-size: 0.72rem;
            color: var(--secondary-color);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .kpi-value {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--dark-text);
            line-height: 1.1;
        }

        .kpi-trend {
            font-size: 0.72rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
            margin-top: 0.1rem;
        }

        .kpi-trend.positive { color: var(--success-color); }
        .kpi-trend.negative { color: var(--danger-color); }

        /* ── ACCIONES RÁPIDAS compactas ── */
        .quick-actions {
            background: var(--white);
            border-radius: 10px;
            padding: 1rem 1.2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 1rem;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 0.85rem;
            color: var(--dark-text);
            margin-bottom: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title i { color: var(--primary-color); font-size: 0.9rem; }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 0.6rem;
        }

        .action-btn {
            padding: 0.6rem 0.8rem;
            border: 1.5px solid var(--primary-color);
            background: var(--white);
            color: var(--primary-color);
            border-radius: 7px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .action-btn:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(12,78,212,0.25);
        }

        .action-btn i { font-size: 0.95rem; }

        /* ── TABLA POSTULACIONES compacta ── */
        .recent-applications {
            background: var(--white);
            border-radius: 10px;
            padding: 1rem 1.2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            margin-bottom: 1rem;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .table { margin-bottom: 0; font-size: 0.82rem; }

        .table thead { background-color: var(--light-bg); }

        .table thead th {
            border: none;
            color: var(--secondary-color);
            font-weight: 700;
            padding: 0.55rem 0.75rem;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 0.55rem 0.75rem;
            border-color: #f0f2f7;
            vertical-align: middle;
            color: var(--dark-text);
        }

        .table tbody tr:hover { background-color: rgba(12,78,212,0.02); }

        .badge-status {
            padding: 0.25rem 0.65rem;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
        }

        .badge-status.pending  { background-color: rgba(255,193,7,0.15); color: #b38600; }
        .badge-status.accepted { background-color: rgba(40,167,69,0.15); color: #155724; }
        .badge-status.rejected { background-color: rgba(220,53,69,0.15);  color: #721c24; }

        /* ── ANIMACIONES ── */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stats-grid > * { animation: slideInUp 0.45s ease-out forwards; }
        .stats-grid > :nth-child(1) { animation-delay: 0.05s; }
        .stats-grid > :nth-child(2) { animation-delay: 0.1s; }
        .stats-grid > :nth-child(3) { animation-delay: 0.15s; }
        .stats-grid > :nth-child(4) { animation-delay: 0.2s; }

        .quick-actions       { animation: slideInUp 0.45s ease-out 0.25s forwards; opacity: 0; }
        .recent-applications { animation: slideInUp 0.45s ease-out 0.3s forwards;  opacity: 0; }

        /* ── RESPONSIVE ── */
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .action-buttons { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            body { flex-direction: column !important; }
            .stats-grid { grid-template-columns: 1fr; }
            .action-buttons { grid-template-columns: 1fr; }
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
                <h1><i class="fas fa-tachometer-alt"></i> Inicio</h1>
                <p>Bienvenido a tu Panel de Control</p>
            </div>
            <nav aria-label="breadcrumb" style="margin-left:auto; position:relative; z-index:1;">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active"><i class="fas fa-home"></i> Inicio</li>
                </ol>
            </nav>
        </div>

        <!-- CONTENIDO -->
        <div class="dashboard-content">

            <!-- ESTADÍSTICAS -->
            <section class="stats-section">
                <h2><i class="fas fa-chart-bar"></i> Tus Estadísticas</h2>
                <div class="stats-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon primary"><i class="fas fa-file-alt"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Postulaciones</div>
                            <div class="kpi-value">12</div>
                            <div class="kpi-trend positive"><i class="fas fa-arrow-up"></i> +3 este mes</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon success"><i class="fas fa-file-upload"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Documentos</div>
                            <div class="kpi-value">8</div>
                            <div class="kpi-trend positive"><i class="fas fa-check-circle"></i> Todos verificados</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon info"><i class="fas fa-check"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Aceptadas</div>
                            <div class="kpi-value">4</div>
                            <div class="kpi-trend positive"><i class="fas fa-arrow-up"></i> 33.3% aprobación</div>
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon warning"><i class="fas fa-clock"></i></div>
                        <div class="kpi-body">
                            <div class="kpi-label">Pendientes</div>
                            <div class="kpi-value">5</div>
                            <div class="kpi-trend negative"><i class="fas fa-hourglass-half"></i> Resp. ~7 días</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ACCIONES RÁPIDAS -->
            <section class="quick-actions">
                <div class="section-title"><i class="fas fa-bolt"></i> Acciones Rápidas</div>
                <div class="action-buttons">
                    <a href="#" class="action-btn"><i class="fas fa-plus-circle"></i> Nueva Postulación</a>
                    <a href="documentos.php" class="action-btn"><i class="fas fa-file-upload"></i> Cargar Documento</a>
                    <a href="datosPersonales.php" class="action-btn"><i class="fas fa-user-edit"></i> Editar Perfil</a>
                    <a href="#" class="action-btn"><i class="fas fa-download"></i> Descargar CV</a>
                </div>
            </section>

            <!-- POSTULACIONES RECIENTES -->
            <section class="recent-applications">
                <div class="section-title"><i class="fas fa-list"></i> Postulaciones Recientes</div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Posición</th>
                                <th>Empresa</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Desarrollador Full Stack</strong></td>
                                <td>Tech Solutions Inc.</td>
                                <td>15 Nov, 2024</td>
                                <td><span class="badge-status accepted">Aceptada</span></td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">Ver</a></td>
                            </tr>
                            <tr>
                                <td><strong>Diseñador UX/UI</strong></td>
                                <td>Creative Agency Ltd.</td>
                                <td>12 Nov, 2024</td>
                                <td><span class="badge-status pending">Pendiente</span></td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">Ver</a></td>
                            </tr>
                            <tr>
                                <td><strong>Analista de Datos</strong></td>
                                <td>Data Insights Corp.</td>
                                <td>10 Nov, 2024</td>
                                <td><span class="badge-status accepted">Aceptada</span></td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">Ver</a></td>
                            </tr>
                            <tr>
                                <td><strong>QA Engineer</strong></td>
                                <td>Software Quality Hub</td>
                                <td>8 Nov, 2024</td>
                                <td><span class="badge-status rejected">Rechazada</span></td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">Ver</a></td>
                            </tr>
                            <tr>
                                <td><strong>DevOps Engineer</strong></td>
                                <td>Cloud Infrastructure Co.</td>
                                <td>5 Nov, 2024</td>
                                <td><span class="badge-status pending">Pendiente</span></td>
                                <td><a href="#" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem;">Ver</a></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(el) { return new bootstrap.Tooltip(el); });
        });
    </script>
</body>
</html>