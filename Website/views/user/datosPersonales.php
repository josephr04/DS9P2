

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos Personales - CareerPort</title>
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
            --input-bg: #f8f9fc;
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

        /* ── HEADER ── */
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

        .dashboard-header::before {
            content: '';
            position: absolute;
            top: -60%; right: -5%;
            width: 180px; height: 180px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
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

        /* ── SCROLL AREA ── */
        .dashboard-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .dashboard-content::-webkit-scrollbar { width: 6px; }
        .dashboard-content::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.18); border-radius: 4px; }

        /* ── FORM WRAPPER ── */
        .form-wrapper {
            max-width: 860px;
            margin: 0 auto;
        }

        .form-intro {
            margin-bottom: 1.2rem;
        }

        .form-intro h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 0.2rem;
        }

        .form-intro p {
            font-size: 0.85rem;
            color: var(--secondary-color);
        }

        /* ── SECCIÓN CARD ── */
        .form-section {
            background: var(--white);
            border-radius: 10px;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            border: 1px solid var(--border-color);
            animation: slideInUp 0.4s ease-out forwards;
            opacity: 0;
        }

        .form-section:nth-child(1) { animation-delay: 0.05s; }
        .form-section:nth-child(2) { animation-delay: 0.1s; }
        .form-section:nth-child(3) { animation-delay: 0.15s; }
        .form-section:nth-child(4) { animation-delay: 0.2s; }

        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 0.6rem;
            border-bottom: 2px solid var(--primary-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-title i { font-size: 0.95rem; }

        /* ── INPUTS ── */
        .form-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.3rem;
        }

        .form-control, .form-select {
            font-size: 0.82rem;
            padding: 0.45rem 0.75rem;
            border: 1.5px solid var(--border-color);
            border-radius: 7px;
            background-color: var(--input-bg);
            color: var(--dark-text);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(12,78,212,0.1);
            background-color: var(--white);
            outline: none;
        }

        .form-control::placeholder { color: #aab0bf; font-size: 0.8rem; }

        /* ── CÉDULA ── */
        .cedula-group {
            display: grid;
            grid-template-columns: 60px 80px 1fr;
            gap: 0.5rem;
            align-items: center;
        }

        .cedula-sep {
            text-align: center;
            font-weight: 700;
            color: var(--secondary-color);
            font-size: 1.1rem;
            grid-column: unset;
        }

        .cedula-wrapper {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .cedula-wrapper span {
            font-weight: 700;
            color: var(--secondary-color);
        }

        /* ── BOTÓN ENVIAR ── */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff;
            border: none;
            padding: 0.7rem 2.5rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(12,78,212,0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(12,78,212,0.4);
        }

        /* ── AVISO PRIVACIDAD ── */
        .privacy-notice {
            background: #eef2ff;
            border: 1px solid #c7d4f8;
            border-radius: 8px;
            padding: 0.65rem 1rem;
            font-size: 0.78rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .required-note {
            font-size: 0.75rem;
            color: var(--secondary-color);
            margin-bottom: 0.8rem;
        }

        .required-note span { color: #dc3545; }

        /* ── ANIMACIÓN ── */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            body { flex-direction: column !important; }
            .dashboard-content { padding: 0.8rem; }
            .cedula-group { grid-template-columns: 55px 70px 1fr; }
        }
    </style>
</head>
<body>

    <?php include '../../header/sidebar.php'; ?>

    <div class="page-container">

        <!-- HEADER -->
        <div class="dashboard-header">
            <div class="header-text">
                <h1><i class="fas fa-user-circle"></i> Mi Perfil</h1>
                <p>Completa tu información para el proceso de aplicación</p>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="#"><i class="fas fa-home"></i> Inicio</a></li>
                    <li class="breadcrumb-item active">Mi Perfil</li>
                </ol>
            </nav>
        </div>

        <!-- CONTENIDO -->
        <div class="dashboard-content">
            <div class="form-wrapper">

                <div class="form-intro">
                    <h2>Completa tu Perfil</h2>
                    <p>Por favor, proporciona tus datos generales para comenzar tu proceso de aplicación.</p>
                </div>

                <p class="required-note"><span>*</span> Campos obligatorios</p>

                <form method="POST" action="">

                    <!-- ══ INFORMACIÓN PERSONAL ══ -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-user"></i> Información Personal
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Primer Nombre <span style="color:#dc3545">*</span></label>
                                <input type="text" class="form-control" name="primer_nombre" placeholder="Ej: Juan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segundo Nombre <span style="color:#6c757d">(Opcional)</span></label>
                                <input type="text" class="form-control" name="segundo_nombre" placeholder="Ej: Carlos">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Primer Apellido <span style="color:#dc3545">*</span></label>
                                <input type="text" class="form-control" name="primer_apellido" placeholder="Ej: García" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segundo Apellido <span style="color:#6c757d">(Opcional)</span></label>
                                <input type="text" class="form-control" name="segundo_apellido" placeholder="Ej: López">
                            </div>

                            <!-- CÉDULA -->
                            <div class="col-12">
                                <label class="form-label">Cédula Panameña <span style="color:#dc3545">*</span></label>
                                <div class="cedula-wrapper">
                                    <input type="text" class="form-control" name="cedula_tipo" placeholder="00" maxlength="2" style="width:60px; text-align:center;">
                                    <span>-</span>
                                    <input type="text" class="form-control" name="cedula_tomo" placeholder="0000" maxlength="4" style="width:75px; text-align:center;">
                                    <span>-</span>
                                    <input type="text" class="form-control" name="cedula_asiento" placeholder="00000" maxlength="5" style="width:85px; text-align:center;">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Género <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="genero" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option>Masculino</option>
                                    <option>Femenino</option>
                                    <option>Prefiero no decir</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de Nacimiento <span style="color:#dc3545">*</span></label>
                                <input type="date" class="form-control" name="fecha_nacimiento" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado Civil <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="estado_civil" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option>Soltero/a</option>
                                    <option>Casado/a</option>
                                    <option>Divorciado/a</option>
                                    <option>Viudo/a</option>
                                    <option>Unión libre</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Sangre <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="tipo_sangre" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option>A+</option><option>A-</option>
                                    <option>B+</option><option>B-</option>
                                    <option>AB+</option><option>AB-</option>
                                    <option>O+</option><option>O-</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Nivel Académico <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="nivel_academico" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                    <option>Primaria</option>
                                    <option>Secundaria</option>
                                    <option>Técnico</option>
                                    <option>Universidad</option>
                                    <option>Postgrado</option>
                                    <option>Maestría</option>
                                    <option>Doctorado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- ══ INFORMACIÓN DE CONTACTO ══ -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-address-book"></i> Información de Contacto
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Teléfono Primario <span style="color:#6c757d">(Opcional)</span></label>
                                <input type="tel" class="form-control" name="telefono_primario" placeholder="000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono Secundario <span style="color:#6c757d">(Opcional)</span></label>
                                <input type="tel" class="form-control" name="telefono_secundario" placeholder="000-0000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Celular Primario <span style="color:#dc3545">*</span></label>
                                <input type="tel" class="form-control" name="celular_primario" placeholder="6000-0000" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Celular Secundario <span style="color:#6c757d">(Opcional)</span></label>
                                <input type="tel" class="form-control" name="celular_secundario" placeholder="6000-0000">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Correo Electrónico <span style="color:#dc3545">*</span></label>
                                <input type="email" class="form-control" name="correo" placeholder="usuario@ejemplo.com" required>
                            </div>
                        </div>
                    </div>

                    <!-- ══ DIRECCIÓN RESIDENCIAL ══ -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-map-marker-alt"></i> Dirección Residencial
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Provincia <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="provincia" id="provincia" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Distrito <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="distrito" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Corregimiento <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="corregimiento" required>
                                    <option value="" disabled selected>Seleccione...</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Urbanización/Barriada <span style="color:#dc3545">*</span></label>
                                <input type="text" class="form-control" name="urbanizacion" placeholder="Ej: Altos de Panamá" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Calle <span style="color:#dc3545">*</span></label>
                                <input type="text" class="form-control" name="calle" placeholder="Ej: Calle 5ta" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Casa/Edificio # <span style="color:#dc3545">*</span></label>
                                <input type="text" class="form-control" name="casa_edificio" placeholder="Ej: Casa 12 / Apto 3B" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Detalles Adicionales <span style="color:#6c757d">(Opcional)</span></label>
                                <textarea class="form-control" name="detalles_adicionales" rows="2" placeholder="Ej: Detrás de la tienda X, portón azul..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ══ APLICACIÓN LABORAL ══ -->
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-briefcase"></i> Aplicación Laboral
                        </div>

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Vacante a la que aplica <span style="color:#dc3545">*</span></label>
                                <select class="form-select" name="vacante" required>
                                    <option value="" disabled selected>Seleccione una posición...</option>
                                    <option>Desarrollador Full Stack</option>
                                    <option>Diseñador UX/UI</option>
                                    <option>Analista de Datos</option>
                                    <option>QA Engineer</option>
                                    <option>DevOps Engineer</option>
                                    <option>Project Manager</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- BOTÓN Y AVISO -->
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Enviar Solicitud
                        </button>
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

     
</body>
</html>