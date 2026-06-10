<?php
// 1. Incluir el validador de sesión (que arranca session_start()) y la conexión
require_once '../../config/auth_user.php'; 
require_once '../../config/conexion.php'; 

// 2. Verificar que los datos lleguen exclusivamente por el método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // SEGURIDAD MULTIUSUARIO: Capturamos el ID del usuario con sesión activa.
        if (!isset($_SESSION['idUsuario'])) {
            header('Location: login.php'); // Redirige al login que está en el mismo nivel
            exit;
        }
        
        $idUsuario = $_SESSION['idUsuario']; 

        // Convertir el género a formato TINYINT(1) para la base de datos (M = 1, F = 0)
        $genero_numeric = ($_POST['genero'] === 'M') ? 1 : 0;

        // VERIFICACIÓN DE PERSISTENCIA: ¿Este usuario ya registró sus datos anteriormente?
        $stmtCheck = $conexion->prepare("SELECT COUNT(*) FROM postulante WHERE idUsuario = :idUsuario");
        $stmtCheck->execute([':idUsuario' => $idUsuario]);
        $existePerfil = $stmtCheck->fetchColumn() > 0;

        if ($existePerfil) {
            // SI YA EXISTE: Actualizamos sus datos permanentes (UPDATE) para que no se dupliquen
            $sql = "UPDATE postulante SET 
                        rangoAcademico = :rangoAcademico, 
                        nombre = :nombre, 
                        nombre2 = :nombre2, 
                        apellido = :apellido, 
                        apellido2 = :apellido2, 
                        prefijo = :prefijo, 
                        tomo = :tomo, 
                        asiento = :asiento, 
                        genero = :genero, 
                        estadoCivil = :estadoCivil, 
                        tipoSangre = :tipoSangre, 
                        fechaNacimiento = :fechaNacimiento, 
                        codigo_provincia = :codigo_provincia, 
                        codigo_distrito = :codigo_distrito, 
                        codigo_corregimiento = :codigo_corregimiento, 
                        comunidad = :comunidad, 
                        calle = :calle, 
                        casa = :casa, 
                        detalleDireccion = :detalleDireccion, 
                        telefono = :telefono, 
                        telefono2 = :telefono2, 
                        celular = :celular, 
                        celular2 = :celular2, 
                        correoPostulante = :correoPostulante
                    WHERE idUsuario = :idUsuario";
        } else {
            // SI NO EXISTE: Guardamos la información por primera vez (INSERT)
            $sql = "INSERT INTO postulante (
                        idUsuario, rangoAcademico, nombre, nombre2, apellido, apellido2, 
                        prefijo, tomo, asiento, genero, estadoCivil, tipoSangre, 
                        fechaNacimiento, codigo_provincia, codigo_distrito, codigo_corregimiento, 
                        comunidad, calle, casa, detalleDireccion, 
                        telefono, telefono2, celular, celular2, correoPostulante
                    ) VALUES (
                        :idUsuario, :rangoAcademico, :nombre, :nombre2, :apellido, :apellido2, 
                        :prefijo, :tomo, :asiento, :genero, :estadoCivil, :tipoSangre, 
                        :fechaNacimiento, :codigo_provincia, :codigo_distrito, :codigo_corregimiento, 
                        :comunidad, :calle, :casa, :detalleDireccion, 
                        :telefono, :telefono2, :celular, :celular2, :correoPostulante
                    )";
        }

        $stmt = $conexion->prepare($sql);

        // 4. Vincular todos los parámetros de manera segura contra Inyecciones SQL
        $stmt->execute([
            ':idUsuario'            => $idUsuario,
            ':rangoAcademico'       => $_POST['nivel_academico'],
            ':nombre'               => $_POST['primer_nombre'],
            ':nombre2'              => !empty($_POST['segundo_nombre']) ? $_POST['segundo_nombre'] : null,
            ':apellido'             => $_POST['primer_apellido'],
            ':apellido2'            => !empty($_POST['segundo_apellido']) ? $_POST['segundo_apellido'] : null,
            ':prefijo'              => $_POST['cedula_tipo'],
            ':tomo'                 => $_POST['cedula_tomo'],
            ':asiento'              => $_POST['cedula_asiento'],
            ':genero'               => $genero_numeric,
            ':estadoCivil'          => $_POST['estado_civil'],
            ':tipoSangre'           => $_POST['tipo_sangre'],
            ':fechaNacimiento'      => $_POST['fecha_nacimiento'],
            ':codigo_provincia'     => $_POST['provincia'],
            ':codigo_distrito'      => $_POST['distrito'],
            ':codigo_corregimiento' => $_POST['corregimiento'],
            ':comunidad'            => $_POST['urbanizacion'], 
            ':calle'                => $_POST['calle'],
            ':casa'                 => $_POST['casa_edificio'], 
            ':detalleDireccion'     => !empty($_POST['detalles_adicionales']) ? $_POST['detalles_adicionales'] : null,
            ':telefono'             => !empty($_POST['telefono_primario']) ? $_POST['telefono_primario'] : null,
            ':telefono2'            => !empty($_POST['telefono_secundario']) ? $_POST['telefono_secundario'] : null,
            ':celular'              => $_POST['celular_primario'],
            ':celular2'             => !empty($_POST['celular_secundario']) ? $_POST['celular_secundario'] : null,
            ':correoPostulante'     => $_POST['correo']
        ]);

        // Definimos el mensaje según la acción que se ejecutó
        $mensajeExito = $existePerfil ? '¡Tu perfil ha sido actualizado con éxito!' : '¡Tus datos de perfil se han guardado permanentemente!';

        echo "<script>
                alert('$mensajeExito');
                window.location.href = 'dashboard.php';
              </script>";
        exit;

    } catch (PDOException $e) {
        error_log("Error SQL al procesar postulante: " . $e->getMessage());
        echo "<script>
                alert('Error crítico al guardar en la base de datos.');
                window.history.back();
              </script>";
        exit;
    }
} else {
    header('Location: dashboard.php');
    exit;
}
?>