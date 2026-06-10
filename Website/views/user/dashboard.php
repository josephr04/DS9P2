<?php 
// Incluye el validador de sesión y tiempo de inactividad centralizado
require_once '../../config/auth_user.php'; 

// Cargar la conexión 
require_once '../../config/conexion.php'; 

// AISLAMIENTO DE DATOS: Capturamos quién es el usuario con la sesión abierta en el Login
$idUsuarioActual = $_SESSION['idUsuario'] ?? 0; 
$yaTieneDatos = false;
$datosPostulante = [];

try {
    // 1. Verificar si este usuario específico ya tiene un registro guardado en la base de datos
    $stmtVerificar = $conexion->prepare("SELECT * FROM postulante WHERE idUsuario = :idUsuario LIMIT 1");
    $stmtVerificar->execute([':idUsuario' => $idUsuarioActual]);
    $datosPostulante = $stmtVerificar->fetch(PDO::FETCH_ASSOC);
    
    if ($datosPostulante) {
        $yaTieneDatos = true; // Cambia a verdadero si ya tiene historial guardado
    }

    // 2. Cargar Catálogos Generales para los Listados Desplegables
    $queryProvincias = $conexion->query("SELECT codigo_provincia, nombre_provincia FROM provincia ORDER BY nombre_provincia ASC");
    $provincias = $queryProvincias->fetchAll(PDO::FETCH_ASSOC);

    $queryEstadoCivil = $conexion->query("SELECT idEstadoCivil, nombreEstadoCiv FROM estadocivil ORDER BY nombreEstadoCiv ASC");
    $estadosCiviles = $queryEstadoCivil->fetchAll(PDO::FETCH_ASSOC);

    $queryTipoSangre = $conexion->query("SELECT idTipoSangre, nombreTipoSangre FROM tiposangre ORDER BY nombreTipoSangre ASC");
    $tiposSangre = $queryTipoSangre->fetchAll(PDO::FETCH_ASSOC);

    $queryRangoEdu = $conexion->query("SELECT idRangoEdu, nombreRangoEdu FROM rangoacademico ORDER BY idRangoEdu ASC");
    $rangosAcademicos = $queryRangoEdu->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $provincias = []; $estadosCiviles = []; $tiposSangre = []; $rangosAcademicos = [];
    error_log("Error al cargar datos en el dashboard: " . $e->getMessage());
}
?>

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

        /* Contenedor principal para empujar el contenido al lado del sidebar */
        .page-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Encabezado del Dashboard */
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
        }

        .dashboard-header h1 { font-size: 1.4rem; font-weight: 700; margin: 0; }
        .dashboard-header p  { font-size: 0.8rem; opacity: 0.85; margin: 0; }

        /* Migas de pan (Breadcrumb) */
        .breadcrumb {
            background: transparent; padding: 0; margin: 0;
            font-size: 0.75rem; margin-left: auto;
        }
        .breadcrumb-item.active { color: rgba(255,255,255,0.75); }
        .breadcrumb-item a { color: #fff; text-decoration: none; font-weight: 500; }

        /* Área de contenido scrollable */
        .dashboard-content {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;

        }


        .form-wrapper { max-width: 860px; margin: 0 auto; }

        .form-intro h2 { font-size: 1.3rem; font-weight: 700; color: var(--dark-text); margin-bottom: 0.2rem; }
        .form-intro p { font-size: 0.85rem; color: var(--secondary-color); }


        /* Tarjetas de Secciones del Formulario */
        .form-section {
            background: var(--white);
            border-radius: 10px;
            padding: 1.2rem 1.5rem;
            margin-bottom: 1rem;
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
            border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(12,78,212,0.1);
            background-color: var(--white); outline: none;
        }

        /* Estilo especial para el formato de cédula separada */
        .cedula-wrapper { display: flex; align-items: center; gap: 0.3rem; }
        .cedula-wrapper span { font-weight: 700; color: var(--secondary-color); }

        /* Botón de Enviar Moderno */
        .btn-submit {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: #fff; border: none; padding: 0.7rem 2.5rem; border-radius: 8px;
            font-size: 0.9rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(12,78,212,0.3); display: flex; align-items: center; gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px); box-shadow: 0 6px 20px rgba(12,78,212,0.4);
        }

        /* Transición suave para ocultar/mostrar elementos */
        .fade-container {
            transition: all 0.3s ease-in-out;
        }
    </style>
</head>
<body>
    


    <?php include '../../header/sidebar.php'; ?>

    <div class="page-container">

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

        <div class="dashboard-content">
            <div class="form-wrapper">

                <div class="form-intro mb-3">
                    <h2>Completa tu Perfil de Aspirante</h2>
                    <p>Proporciona tus datos generales de forma precisa. Los campos con un <span class="text-danger">*</span> son de carácter obligatorio.</p>
                </div>

                <form method="POST" action="procesar_postulante.php">

                    <div class="form-section">
                        <div class="section-title"><i class="fas fa-user"></i> Información Personal</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Primer Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="primer_nombre" required 
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['nombre']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segundo Nombre</label>
                                <input type="text" class="form-control" name="segundo_nombre" 
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['nombre2'] ?? '') : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Primer Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="primer_apellido" required
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['apellido']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Segundo Apellido</label>
                                <input type="text" class="form-control" name="segundo_apellido" 
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['apellido2'] ?? '') : ''; ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Cédula de Identidad Personal <span class="text-danger">*</span></label>
                                <div class="cedula-wrapper">
                                    <input type="text" class="form-control" name="cedula_tipo" placeholder="Prov" maxlength="2" style="width: 60px; text-align: center;" required
                                           value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['prefijo']) : ''; ?>">
                                    <span>-</span>
                                    <input type="text" class="form-control" name="cedula_tomo" placeholder="Tomo" maxlength="4" style="width: 75px; text-align: center;" required
                                           value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['tomo']) : ''; ?>">
                                    <span>-</span>
                                    <input type="text" class="form-control" name="cedula_asiento" placeholder="Asiento" maxlength="5" style="width: 85px; text-align: center;" required
                                           value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['asiento']) : ''; ?>">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Género <span class="text-danger">*</span></label>
                                <select class="form-select" name="genero" required>
                                    <option value="" disabled <?php echo !$yaTieneDatos ? 'selected' : ''; ?>>Seleccione...</option>
                                    <option value="M" <?php echo ($yaTieneDatos && $datosPostulante['genero'] == 1) ? 'selected' : ''; ?>>Masculino</option>
                                    <option value="F" <?php echo ($yaTieneDatos && $datosPostulante['genero'] == 0) ? 'selected' : ''; ?>>Femenino</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="fecha_nacimiento" required
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['fechaNacimiento']) : ''; ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Estado Civil <span class="text-danger">*</span></label>
                                <select class="form-select" name="estado_civil" id="estado_civil" required>
                                    <option value="" disabled <?php echo !$yaTieneDatos ? 'selected' : ''; ?>>Seleccione...</option>
                                    <?php foreach ($estadosCiviles as $ec): ?>
                                        <option value="<?php echo $ec['idEstadoCivil']; ?>" <?php echo ($yaTieneDatos && $datosPostulante['estadoCivil'] == $ec['idEstadoCivil']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ec['nombreEstadoCiv']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php 
                                $esCasada = ($yaTieneDatos && $datosPostulante['estadoCivil'] == 2); 
                            ?>
                            <div class="col-12 fade-container" id="contenedor_apellido_casada" style="<?php echo $esCasada ? '' : 'display: none;'; ?>">
                                <div class="p-3 bg-light rounded border">
                                    <label class="form-label text-primary fw-bold">Apellido de Casada <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control bg-white" name="apelCasada" id="apelCasada" placeholder="Ingrese su apellido de casada" <?php echo $esCasada ? 'required' : ''; ?>
                                           value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['apelCasada'] ?? '') : ''; ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Tipo de Sangre <span class="text-danger">*</span></label>
                                <select class="form-select" name="tipo_sangre" required>
                                    <option value="" disabled <?php echo !$yaTieneDatos ? 'selected' : ''; ?>>Seleccione...</option>
                                    <?php foreach ($tiposSangre as $ts): ?>
                                        <option value="<?php echo $ts['idTipoSangre']; ?>" <?php echo ($yaTieneDatos && $datosPostulante['tipoSangre'] == $ts['idTipoSangre']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ts['nombreTipoSangre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Nivel Académico Máximo <span class="text-danger">*</span></label>
                                <select class="form-select" name="nivel_academico" required>
                                    <option value="" disabled <?php echo !$yaTieneDatos ? 'selected' : ''; ?>>Seleccione...</option>
                                    <?php foreach ($rangosAcademicos as $ra): ?>
                                        <option value="<?php echo $ra['idRangoEdu']; ?>" <?php echo ($yaTieneDatos && $datosPostulante['rangoAcademico'] == $ra['idRangoEdu']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ra['nombreRangoEdu']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>


                    <div class="form-section">
                        <div class="section-title"><i class="fas fa-address-book"></i> Información de Contacto</div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Teléfono Residencial</label>
                                <input type="tel" class="form-control" name="telefono_primario" placeholder="e.g. 254-0000"
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['telefono'] ?? '') : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono de Oficina / Secundario</label>
                                <input type="tel" class="form-control" name="telefono_secundario" placeholder="e.g. 254-0000"
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['telefono2'] ?? '') : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Número Celular Primario <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="celular_primario" placeholder="e.g. 6666-6666" required
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['celular']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Número Celular Secundario</label>
                                <input type="tel" class="form-control" name="celular_secundario" placeholder="e.g. 6666-6666"
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['celular2'] ?? '') : ''; ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Correo Electrónico de Contacto <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="correo" placeholder="ejemplo@correo.com" required
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['correoPostulante']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title"><i class="fas fa-map-marker-alt"></i> Dirección Residencial Actual</div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Provincia <span class="text-danger">*</span></label>
                                <select class="form-select" name="provincia" id="provincia" required>
                                    <option value="" disabled <?php echo !$yaTieneDatos ? 'selected' : ''; ?>>Seleccione...</option>
                                    <?php foreach ($provincias as $provincia): ?>
                                        <option value="<?php echo htmlspecialchars($provincia['codigo_provincia']); ?>" <?php echo ($yaTieneDatos && $datosPostulante['codigo_provincia'] == $provincia['codigo_provincia']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($provincia['nombre_provincia']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Distrito <span class="text-danger">*</span></label>
                                <select class="form-select" name="distrito" id="distrito" required <?php echo !$yaTieneDatos ? 'disabled' : ''; ?>>
                                    <?php if($yaTieneDatos): ?>
                                        <option value="<?php echo $datosPostulante['codigo_distrito']; ?>" selected>Cargado correctamente</option>
                                    <?php else: ?>
                                        <option value="" disabled selected>Seleccione...</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Corregimiento <span class="text-danger">*</span></label>
                                <select class="form-select" name="corregimiento" id="corregimiento" required <?php echo !$yaTieneDatos ? 'disabled' : ''; ?>>
                                    <?php if($yaTieneDatos): ?>
                                        <option value="<?php echo $datosPostulante['codigo_corregimiento']; ?>" selected>Cargado correctamente</option>
                                    <?php else: ?>
                                        <option value="" disabled selected>Seleccione...</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Urbanización / Barriada / Comunidad <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="urbanizacion" required
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['comunidad']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Calle / Avenida <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="calle" required
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['calle']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Casa o Edificio N° <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="casa_edificio" required
                                       value="<?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['casa']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Detalles Adicionales de Referencia</label>
                                <textarea class="form-control" name="detalles_adicionales" rows="2" placeholder="Cerca de..., frente a..."><?php echo $yaTieneDatos ? htmlspecialchars($datosPostulante['detalleDireccion'] ?? '') : ''; ?></textarea>
                            </div>
                        </div>
                   </div>

                    <div class="d-flex justify-content-end mt-4">
                        <?php if($yaTieneDatos): ?>
                            <button type="submit" class="btn btn-warning text-dark fw-bold px-4 py-2" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(241, 196, 15, 0.25);">
                                <i class="fas fa-edit me-1"></i> Actualizar mi Información
                            </button>
                        <?php else: ?>
                            <button type="submit" class="btn-submit">
                                <i class="fas fa-paper-plane"></i> Guardar mi Perfil
                            </button>
                        <?php endif; ?>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const selectProvincia = document.getElementById('provincia');
        const selectDistrito = document.getElementById('distrito');
        const selectCorregimiento = document.getElementById('corregimiento');

        // ELEMENTOS PARA EL CONTROL DEL APELLIDO DE CASADA
        const selectEstadoCivil = document.getElementById('estado_civil');
        const contenedorCasada = document.getElementById('contenedor_apellido_casada');
        const inputApelCasada = document.getElementById('apelCasada');

        // Escuchar el cambio en el selector de Estado Civil en tiempo real
        selectEstadoCivil.addEventListener('change', function() {
            // RECUERDA: Cambia el '2' por el ID numérico que tenga "Casada" en tu base de datos si es diferente.
            if (this.value === "2") {
                contenedorCasada.style.display = "block"; // Lo hace visible
                inputApelCasada.required = true;         // Lo vuelve campo obligatorio
            } else {
                contenedorCasada.style.display = "none";  // Lo oculta
                inputApelCasada.required = false;        // Quita la obligación
                inputApelCasada.value = "";              // Limpia el texto por seguridad
            }

        });

        // 1. Cambio de Provincia -> Carga Distritos
        selectProvincia.addEventListener('change', async function() {
            const codigoProvincia = this.value;

            selectDistrito.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
            selectCorregimiento.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
            selectDistrito.disabled = true;
            selectCorregimiento.disabled = true;

            if (!codigoProvincia) return;

            try {
                const response = await fetch(`get_distritos.php?codigo_provincia=${encodeURIComponent(codigoProvincia)}`);
                const distritos = await response.json();

                distritos.forEach(distrito => {
                    const option = document.createElement('option');
                    option.value = distrito.codigo_distrito; 
                    option.textContent = distrito.nombre_distrito;
                    selectDistrito.appendChild(option);
                });

                selectDistrito.disabled = false;
            } catch (error) {
                console.error("Error al obtener los distritos:", error);
            }
        });


        // 2. Cambio de Distrito -> Carga Corregimientos
        selectDistrito.addEventListener('change', async function() {
            const codigoDistrito = this.value;

            selectCorregimiento.innerHTML = '<option value="" disabled selected>Seleccione...</option>';
            selectCorregimiento.disabled = true;

            if (!codigoDistrito) return;

            try {
                const response = await fetch(`get_corregimientos.php?codigo_distrito=${encodeURIComponent(codigoDistrito)}`);
                const corregimientos = await response.json();

                corregimientos.forEach(corregimiento => {
                    const option = document.createElement('option');
                    option.value = corregimiento.codigo_corregimiento;
                    option.textContent = corregimiento.nombre_corregimiento;
                    selectCorregimiento.appendChild(option);
                });

                selectCorregimiento.disabled = false;
            } catch (error) {
                console.error("Error al obtener los corregimientos:", error);
            }
        });
    });
    </script>
</body>
</html>