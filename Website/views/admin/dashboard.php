<?php 
// Incluye el validador de sesión y tiempo de inactividad centralizado
require_once '../../config/auth_admin.php';

// Cargar la conexión 
require_once '../../config/conexion.php'; 

// Aquí podrías realizar consultas para registrar métricas dinámicas si lo deseas
try {
    // Ejemplo de consultas para el admin (puedes descomentar y adaptar según tu BD)
    /*
    $totalPostulantes = $conexion->query("SELECT COUNT(*) FROM postulantes")->fetchColumn();
    $totalVacantes = $conexion->query("SELECT COUNT(*) FROM vacantes")->fetchColumn();
    */
} catch (PDOException $e) {
    error_log("Error en el dashboard de administración: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - CareerPort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo '../../header/sidebar.css'; ?>">
    <style>
        :root {
            --primary-color: #0c4ed4;
            --primary-dark: #0a3fb0;
            --primary-light: #e8effe;
            --secondary-color: #6c757d;
            --light-bg: #f0f2f7;
            --white: #ffffff;
            --dark-text: #2c3e50;
            --border-color: #e0e4ef;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }

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
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: #fff;
            padding: 1rem 1.8rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
            overflow: hidden;
        }

        .header-text { position: relative; z-index: 1; }
        .dashboard-header h1 { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .dashboard-header p  { font-size: 0.8rem; opacity: 0.85; margin: 0; }

        .breadcrumb {
            background: transparent; padding: 0; margin: 0;
            font-size: 0.75rem; position: relative; z-index: 1; margin-left: auto;
        }
        .breadcrumb-item.active { color: rgba(255,255,255,0.75); }
        .breadcrumb-item a { color: #fff; text-decoration: none; font-weight: 500; }

        .dashboard-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .admin-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }

        /* Estilos de Tarjetas de Métricas (Misma línea gráfica) */
        .metric-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1.2rem;
        }

        .metric-icon-wrapper {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        /* Colores específicos basados en la captura */
        .icon-applicants { background-color: #edf2ff; color: #3b5bdb; }
        .icon-documents  { background-color: #ebfbee; color: #2b8a3e; }
        .icon-today      { background-color: #f1f3f5; color: #495057; }
        .icon-vacancies  { background-color: #fff5f5; color: #c92a2a; }

        .metric-info p {
            font-size: 0.8rem;
            color: var(--secondary-color);
            margin: 0;
            font-weight: 600;
        }

        .metric-info h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0;
        }

        /* Sección de Candidatos Recientes */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1.5rem 0 1rem 0;
        }

        .section-header h2 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0;
        }

        .section-header .btn-view-all {
            font-size: 0.85rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        /* Tarjetas de Candidatos (Estilo de la imagen) */
        .candidate-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.2rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid var(--border-color);
        }

        .candidate-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .candidate-avatar {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color);
        }

        .candidate-info h4 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0 0 0.1rem 0;
        }

        .candidate-info p {
            font-size: 0.82rem;
            color: var(--primary-color);
            font-weight: 600;
            margin: 0;
        }

        .candidate-meta {
            font-size: 0.8rem;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding-bottom: 0.8rem;
            border-bottom: 1px solid var(--light-bg);
            margin-bottom: 0.8rem;
        }

        .candidate-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-detail {
            color: var(--primary-color);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.2s;
        }

        .btn-detail:hover {
            color: var(--primary-dark);
        }

        .btn-more {
            color: var(--secondary-color);
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0.2rem 0.5rem;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <?php include '../../header/sidebar.php'; ?>

    <div class="page-container">

        <div class="dashboard-header">
            <div class="header-text">
                <h1><i class="fas fa-chart-pie"></i> Panel de Administración</h1>
                <p>Monitoreo global de postulaciones, vacantes y documentos</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Dashboard Admin</li>
                </ol>
            </nav>
        </div>

        <div class="dashboard-content">
            <div class="admin-wrapper">

                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="metric-card">
                            <div class="metric-icon-wrapper icon-applicants">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="metric-info">
                                <p>Total Applicants</p>
                                <h3>1,284</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="metric-card">
                            <div class="metric-icon-wrapper icon-documents">
                                <i class="far fa-file-alt"></i>
                            </div>
                            <div class="metric-info">
                                <p>Total Documents</p>
                                <h3>4,592</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="metric-card">
                            <div class="metric-icon-wrapper icon-today">
                                <i class="far fa-calendar-check"></i>
                            </div>
                            <div class="metric-info">
                                <p>Postulantes Hoy</p>
                                <h3>86</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="metric-card">
                            <div class="metric-icon-wrapper icon-vacancies">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="metric-info">
                                <p>Total Vacantes</p>
                                <h3>12</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-header">
                    <h2>Candidatos Recientes</h2>
                    <a href="#" class="btn-view-all">Ver todos</a>
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="candidate-card">
                            <div class="candidate-profile">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=150" alt="Ana Garcia" class="candidate-avatar">
                                <div class="candidate-info">
                                    <h4>Ana Garca Mndez</h4>
                                    <p>Senior Software Engineer</p>
                                </div>
                            </div>
                            <div class="candidate-meta">
                                <i class="far fa-calendar"></i> Applied on Oct 12, 2023
                            </div>
                            <div class="candidate-actions">
                                <a href="#" class="btn-detail">Ver Detalle <i class="fas fa-arrow-right"></i></a>
                                <button class="btn-more"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="candidate-card">
                            <div class="candidate-profile">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150" alt="Carlos Ruiz" class="candidate-avatar">
                                <div class="candidate-info">
                                    <h4>Carlos Ruiz Zepeda</h4>
                                    <p>Project Manager</p>
                                </div>
                            </div>
                            <div class="candidate-meta">
                                <i class="far fa-calendar"></i> Applied on Oct 10, 2023
                            </div>
                            <div class="candidate-actions">
                                <a href="#" class="btn-detail">Ver Detalle <i class="fas fa-arrow-right"></i></a>
                                <button class="btn-more"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="candidate-card">
                            <div class="candidate-profile">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150" alt="Roberto Valdez" class="candidate-avatar">
                                <div class="candidate-info">
                                    <h4>Roberto Valdez</h4>
                                    <p>UX Designer</p>
                                </div>
                            </div>
                            <div class="candidate-meta">
                                <i class="far fa-calendar"></i> Applied on Oct 09, 2023
                            </div>
                            <div class="candidate-actions">
                                <a href="#" class="btn-detail">Ver Detalle <i class="fas fa-arrow-right"></i></a>
                                <button class="btn-more"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>