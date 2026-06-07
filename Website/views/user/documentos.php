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
    <title>Documentos - CareerPort</title>
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
            --danger: #dc3545;
            --success: #28a745;
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

        /* ── SCROLL ── */
        .dashboard-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .dashboard-content::-webkit-scrollbar { width: 6px; }
        .dashboard-content::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.18); border-radius: 4px; }

        .form-wrapper { max-width: 860px; margin: 0 auto; }

        .form-intro { margin-bottom: 1.2rem; }
        .form-intro h2 { font-size: 1.3rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.2rem; }
        .form-intro p  { font-size: 0.85rem; color: var(--secondary-color); }

        /* ── CARD SECCIÓN ── */
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

        /* ── CHECKBOX institución ── */
        .form-check-label { font-size: 0.78rem; color: var(--secondary-color); }
        .form-check-input:checked { background-color: var(--primary-color); border-color: var(--primary-color); }

        /* ── DROP ZONE PDF ── */
        .drop-zone {
            border: 2px dashed #b0bcdf;
            border-radius: 10px;
            background: var(--primary-light);
            padding: 1.8rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }

        .drop-zone:hover, .drop-zone.dragover {
            border-color: var(--primary-color);
            background: #dce6fc;
        }

        .drop-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }

        .drop-zone i { font-size: 2rem; color: var(--primary-color); margin-bottom: 0.5rem; display: block; }
        .drop-zone p { font-size: 0.82rem; color: var(--dark-text); margin: 0; font-weight: 600; }
        .drop-zone small { font-size: 0.73rem; color: var(--secondary-color); }

        #file-name {
            font-size: 0.78rem;
            color: var(--success);
            margin-top: 0.4rem;
            display: none;
            font-weight: 600;
        }

        /* ── BOTÓN SUBIR ── */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff;
            border: none;
            padding: 0.7rem 2rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(12,78,212,0.3);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(12,78,212,0.4);
        }

        /* ── LISTA DE DOCUMENTOS ── */
        .docs-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.8rem;
        }

        .docs-toolbar h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0;
        }

        .docs-toolbar-actions { display: flex; gap: 0.5rem; }

        .toolbar-btn {
            background: var(--white);
            border: 1.5px solid var(--border-color);
            border-radius: 7px;
            padding: 0.35rem 0.65rem;
            font-size: 0.82rem;
            color: var(--dark-text);
            cursor: pointer;
            transition: all 0.2s;
        }

        .toolbar-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        /* ── SEARCH BAR ── */
        .search-wrapper {
            position: relative;
            margin-bottom: 0.8rem;
            display: none;
        }

        .search-wrapper.show { display: block; }

        .search-wrapper input {
            padding-left: 2rem;
            font-size: 0.82rem;
        }

        .search-wrapper i {
            position: absolute;
            left: 0.65rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            font-size: 0.8rem;
        }

        /* ── ESTADO VACÍO ── */
        .empty-state {
            background: var(--white);
            border-radius: 10px;
            padding: 2.5rem 1rem;
            text-align: center;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }

        .empty-state i { font-size: 3rem; color: #c5cde0; margin-bottom: 0.8rem; }
        .empty-state h4 { font-size: 1rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.3rem; }
        .empty-state p  { font-size: 0.82rem; color: var(--secondary-color); margin: 0; }

        /* ── DOCUMENT CARD ── */
        .doc-card {
            background: var(--white);
            border-radius: 10px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 0.7rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 0.9rem;
            transition: box-shadow 0.2s;
        }

        .doc-card:hover { box-shadow: 0 6px 18px rgba(0,0,0,0.09); }

        .doc-icon {
            width: 42px; height: 42px;
            background: var(--primary-light);
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem;
            color: var(--primary-color);
            flex-shrink: 0;
        }

        .doc-info { flex: 1; min-width: 0; }

        .doc-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--dark-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .doc-meta { font-size: 0.73rem; color: var(--secondary-color); margin-top: 0.1rem; }

        .doc-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            flex-shrink: 0;
        }

        .doc-badge.pending  { background: rgba(255,193,7,0.15); color: #b38600; }
        .doc-badge.verified { background: rgba(40,167,69,0.15);  color: #155724; }
        .doc-badge.rejected { background: rgba(220,53,69,0.15);  color: #721c24; }

        .doc-actions { display: flex; gap: 0.4rem; flex-shrink: 0; }

        .doc-action-btn {
            background: none;
            border: 1.5px solid var(--border-color);
            border-radius: 6px;
            padding: 0.3rem 0.5rem;
            font-size: 0.75rem;
            color: var(--secondary-color);
            cursor: pointer;
            transition: all 0.2s;
        }

        .doc-action-btn:hover { border-color: var(--primary-color); color: var(--primary-color); }
        .doc-action-btn.danger:hover { border-color: var(--danger); color: var(--danger); }

        /* ── ANIMACIÓN ── */
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

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

    <!-- CONTENIDO -->
    <div class="dashboard-content">
        <div class="form-wrapper">

            <div class="form-intro">
                <h2>Document Management</h2>
                <p>Gestiona y organiza tus certificaciones profesionales de manera eficiente.</p>
            </div>

            <form method="POST" action="" enctype="multipart/form-data">

                <!-- ══ SUBIR DOCUMENTO ══ -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-cloud-upload-alt"></i> Subir Nuevo Documento
                    </div>

                    <div class="row g-3">

                        <!-- Título -->
                        <div class="col-12">
                            <label class="form-label">Document Title <span style="color:#dc3545">*</span></label>
                            <input type="text" class="form-control" name="titulo"
                                   placeholder="Ej. Certificación AWS Cloud Architect" required>
                        </div>

                        <!-- Tipo -->
                        <div class="col-md-6">
                            <label class="form-label">Type <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="tipo" required>
                                <option value="" disabled selected>Seleccione un tipo...</option>
                                <option>Diploma</option>
                                <option>Certificado</option>
                                <option>Título Universitario</option>
                                <option>Constancia</option>
                                <option>Licencia</option>
                                <option>Otro</option>
                            </select>
                        </div>

                        <!-- Institución -->
                        <div class="col-md-6">
                            <label class="form-label">Institución <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="institucion" id="selectInstitucion" required>
                                <option value="" disabled selected>Selecciona una institución</option>
                                <option>Universidad Tecnológica de Panamá</option>
                                <option>Universidad de Panamá</option>
                                <option>Universidad Santa María La Antigua</option>
                                <option>Universidad Latina de Panamá</option>
                                <option>Universidad Interamericana de Panamá</option>
                                <option>Instituto Especializado de Estudios Superiores</option>
                                <option>INADEH</option>
                                <option>INFOTEP</option>
                                <option>Coursera</option>
                                <option>Udemy</option>
                                <option>Google</option>
                                <option>Amazon Web Services</option>
                                <option>Microsoft</option>
                                <option>Cisco</option>
                                <option>Otra institución</option>
                            </select>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="chkOtraInstitucion">
                                <label class="form-check-label" for="chkOtraInstitucion">
                                    ¿La institución no está en la lista?
                                </label>
                            </div>
                            <input type="text" class="form-control mt-2" id="inputOtraInstitucion"
                                   name="otra_institucion" placeholder="Escribe el nombre de la institución"
                                   style="display:none;">
                        </div>

                        <!-- Provincia -->
                        <div class="col-md-6">
                            <label class="form-label">Provincia <span style="color:#dc3545">*</span></label>
                            <select class="form-select" name="provincia" required>
                                <option value="" disabled selected>Seleccione...</option>
                                <option>Bocas del Toro</option>
                                <option>Chiriquí</option>
                                <option>Coclé</option>
                                <option>Colón</option>
                                <option>Darién</option>
                                <option>Herrera</option>
                                <option>Los Santos</option>
                                <option>Panamá</option>
                                <option>Panamá Oeste</option>
                                <option>Veraguas</option>
                                <option>Guna Yala</option>
                                <option>Emberá</option>
                                <option>Ngäbe-Buglé</option>
                            </select>
                        </div>

                        <!-- Fecha Inicio -->
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Inicio <span style="color:#dc3545">*</span></label>
                            <input type="date" class="form-control" name="fecha_inicio" required>
                        </div>

                        <!-- Fecha Finalización -->
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Finalización <span style="color:#dc3545">*</span></label>
                            <input type="date" class="form-control" name="fecha_fin" required>
                        </div>

                        <!-- Fecha Emisión -->
                        <div class="col-md-4">
                            <label class="form-label">Fecha de Emisión <span style="color:#dc3545">*</span></label>
                            <input type="date" class="form-control" name="fecha_emision" required>
                        </div>

                        <!-- Total de Horas -->
                        <div class="col-md-4">
                            <label class="form-label">Total de Horas <span style="color:#dc3545">*</span></label>
                            <input type="number" class="form-control" name="total_horas"
                                   placeholder="Ej. 40" min="1" required>
                        </div>

                        <!-- Archivo PDF -->
                        <div class="col-12">
                            <label class="form-label">Archivo PDF</label>
                            <div class="drop-zone" id="dropZone">
                                <input type="file" name="archivo_pdf" id="filePdf" accept=".pdf">
                                <i class="fas fa-file-upload"></i>
                                <p>Arrastra o haz clic para subir</p>
                                <small>Solo archivos PDF (Máx. 10MB)</small>
                                <div id="file-name"></div>
                            </div>
                        </div>

                        <!-- Botón -->
                        <div class="col-12">
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-plus-circle"></i> Subir Documento
                            </button>
                        </div>

                    </div>
                </div>

            </form>

            <!-- ══ LISTA DE DOCUMENTOS ══ -->
            <div class="docs-toolbar">
                <h3><i class="fas fa-folder-open" style="color:var(--primary-color); margin-right:0.4rem;"></i> Tus Documentos</h3>
                <div class="docs-toolbar-actions">
                    <button class="toolbar-btn" id="btnFilter" title="Filtrar">
                        <i class="fas fa-filter"></i>
                    </button>
                    <button class="toolbar-btn" id="btnSearch" title="Buscar">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <!-- Buscador -->
            <div class="search-wrapper" id="searchWrapper">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" id="searchInput" placeholder="Buscar documento...">
            </div>

            <!-- Documentos (PHP llenará aquí) -->
            <?php
            // Simulación — reemplaza con tu consulta real a la BD
            $documentos = []; // $documentos = $tu_query_aqui;

            if (empty($documentos)): ?>
            <div class="empty-state">
                <i class="fas fa-image"></i>
                <h4>No tienes documentos subidos</h4>
                <p>Sube tu primer certificado para comenzar tu proceso</p>
            </div>
            <?php else: ?>
            <div id="docList">
                <?php foreach ($documentos as $doc): ?>
                <div class="doc-card">
                    <div class="doc-icon"><i class="fas fa-file-pdf"></i></div>
                    <div class="doc-info">
                        <div class="doc-name"><?php echo htmlspecialchars($doc['titulo']); ?></div>
                        <div class="doc-meta">
                            <?php echo htmlspecialchars($doc['institucion']); ?> ·
                            <?php echo htmlspecialchars($doc['tipo']); ?> ·
                            <?php echo $doc['total_horas']; ?> hrs
                        </div>
                    </div>
                    <span class="doc-badge <?php echo $doc['estado']; ?>">
                        <?php echo ucfirst($doc['estado']); ?>
                    </span>
                    <div class="doc-actions">
                        <button class="doc-action-btn" title="Ver PDF">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="doc-action-btn danger" title="Eliminar">
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
    // ── Checkbox "otra institución"
    document.getElementById('chkOtraInstitucion').addEventListener('change', function() {
        const input = document.getElementById('inputOtraInstitucion');
        const select = document.getElementById('selectInstitucion');
        if (this.checked) {
            input.style.display = 'block';
            input.required = true;
            select.required = false;
            select.disabled = true;
        } else {
            input.style.display = 'none';
            input.required = false;
            select.required = true;
            select.disabled = false;
        }
    });

    // ── Drop zone
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('filePdf');
    const fileName  = document.getElementById('file-name');

    fileInput.addEventListener('change', function() {
        if (this.files[0]) {
            fileName.textContent = '✔ ' + this.files[0].name;
            fileName.style.display = 'block';
        }
    });

    ['dragover','dragenter'].forEach(e => {
        dropZone.addEventListener(e, ev => {
            ev.preventDefault();
            dropZone.classList.add('dragover');
        });
    });

    ['dragleave','drop'].forEach(e => {
        dropZone.addEventListener(e, ev => {
            ev.preventDefault();
            dropZone.classList.remove('dragover');
            if (e === 'drop' && ev.dataTransfer.files[0]) {
                fileInput.files = ev.dataTransfer.files;
                fileName.textContent = '✔ ' + ev.dataTransfer.files[0].name;
                fileName.style.display = 'block';
            }
        });
    });

    // ── Buscador toggle
    document.getElementById('btnSearch').addEventListener('click', function() {
        document.getElementById('searchWrapper').classList.toggle('show');
        document.getElementById('searchInput').focus();
    });

    // ── Filtro simple por nombre
    document.getElementById('searchInput').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('.doc-card').forEach(card => {
            const name = card.querySelector('.doc-name').textContent.toLowerCase();
            card.style.display = name.includes(q) ? 'flex' : 'none';
        });
    });
</script>
</body>
</html>