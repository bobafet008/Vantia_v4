<?php
class UsuarioController
{
    public function registro()
    {
        $tipo = $_GET['tipo'] ?? 'empleado';
        if (!in_array($tipo, ['empleado', 'empresa', 'emprendimiento'], true)) {
            $tipo = 'empleado';
        }
        vista('registro', ['tipo' => $tipo]);
    }

    public function registroEmpleado()
    {
        vista('registro', ['tipo' => 'empleado']);
    }

    public function registroEmpresa()
    {
        vista('registro', ['tipo' => 'empresa']);
    }

    public function registroEmprendimiento()
    {
        vista('registro', ['tipo' => 'emprendimiento']);
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ban.php?pagina=registro_empleado');
            exit;
        }

        $tipo = $_POST['tipo'] ?? '';
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmarContrasena = $_POST['confirmar_contrasena'] ?? '';
        $telefono = trim($_POST['telefono'] ?? '');

        // Validación inicial común
        if ($tipo !== 'empleado' && $tipo !== 'empresa' && $tipo !== 'emprendimiento') {
            $error = 'Selecciona un tipo de cuenta válido.';
        } elseif ($nombre === '') {
            $error = 'Por favor completa el nombre de usuario u organización.';
        } elseif ($correo === '' || strpos($correo, '@') === false || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = 'El correo electrónico debe contener un símbolo @ y tener un formato válido (ejemplo: usuario@correo.com).';
        } elseif ($telefono === '') {
            $error = 'El número de teléfono / WhatsApp es obligatorio.';
        } elseif (!ctype_digit($telefono) || strlen($telefono) !== 8) {
            $error = 'El número de teléfono solo debe contener números y tener exactamente 8 dígitos.';
        } elseif (strlen($contrasena) < 6) {
            $error = 'La contraseña debe tener al menos 6 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $contrasena)) {
            $error = 'La contraseña debe incluir al menos una letra mayúscula.';
        } elseif (!preg_match('/[a-z]/', $contrasena)) {
            $error = 'La contraseña debe incluir al menos una letra minúscula.';
        } elseif (!preg_match('/[0-9]/', $contrasena)) {
            $error = 'La contraseña debe incluir al menos un número.';
        } elseif (!preg_match('/[^a-zA-Z0-9]/', $contrasena)) {
            $error = 'La contraseña debe incluir al menos un carácter especial (ej. !@#$%&*).';
        } elseif ($confirmarContrasena !== '' && $contrasena !== $confirmarContrasena) {
            $error = 'Las contraseñas no coinciden. Por favor verifícalas.';
        }

        require __DIR__ . '/../../config/database.php';
        require_once __DIR__ . '/../Models/Usuario.php';
        require_once __DIR__ . '/../Models/Empresa.php';
        require_once __DIR__ . '/../Models/Emprendimiento.php';
        require_once __DIR__ . '/../Models/UsuarioAdmin.php';

        $modeloUsuario        = new Usuario($conexion);
        $modeloEmpresa        = new Empresa($conexion);
        $modeloEmprendimiento = new Emprendimiento($conexion);
        $modeloAdmin          = new UsuarioAdmin($conexion);

        // Verificar si el correo ya existe en cualquier tipo de cuenta (mayor, menor, empresa, emprendimiento o admin)
        if (!isset($error)) {
            if (!empty($modeloUsuario->buscarPorCorreo($correo)) ||
                !empty($modeloEmpresa->buscarPorCorreo($correo)) ||
                !empty($modeloEmprendimiento->buscarPorCorreo($correo)) ||
                !empty($modeloAdmin->buscarPorCorreo($correo))) {
                $error = 'Ese correo electrónico ya se encuentra registrado en la plataforma.';
            }
        }

        $hashContrasena = password_hash($contrasena, PASSWORD_DEFAULT);
        $esMenor = false;

        // Validaciones específicas según el tipo de usuario
        if (!isset($error)) {
            if ($tipo === 'empresa') {
                $representante = trim($_POST['representante'] ?? '');
                $nit = trim($_POST['nit'] ?? '');
                $descripcion = trim($_POST['descripcion_empresa'] ?? '');

                // Procesamiento del certificado / código QR ROE en formato de imagen
                $roePath = 'uploads/roe/roe_default.png';
                if (isset($_FILES['roe_archivo']) && $_FILES['roe_archivo']['error'] === UPLOAD_ERR_OK) {
                    $subido = subirArchivo('roe_archivo', 'roe', 'roe');
                    if ($subido === false) {
                        $error = 'El certificado ROE debe ser una imagen válida (JPG, PNG) y no superar los 5MB.';
                    } elseif ($subido !== null) {
                        $roePath = 'uploads/' . $subido;
                    }
                } elseif (!empty($_POST['roe_actual'])) {
                    $roePath = trim($_POST['roe_actual']);
                }

                if ($representante === '') {
                    $error = 'El nombre del representante de la empresa es obligatorio.';
                } elseif ($nit === '') {
                    $error = 'El número de NIT es obligatorio.';
                } elseif (!ctype_digit($nit) || strlen($nit) < 7 || strlen($nit) > 20) {
                    $error = 'El NIT debe ser numérico y contener al menos 7 dígitos.';
                } elseif (empty($roePath)) {
                    $error = 'La imagen o código QR de ROE es obligatorio para la empresa.';
                } else {
                    $datosEmpresa = [
                        'nombre' => $nombre,
                        'tipo_cuenta' => 'empresa',
                        'representante' => $representante,
                        'nit' => $nit,
                        'roe' => $roePath,
                        'correo' => $correo,
                        'contrasena' => $hashContrasena,
                        'telefono' => $telefono,
                        'descripcion' => $descripcion ?: null,
                        'documento_legal_path' => null,
                        'verificada' => 1,
                    ];
                }
            } elseif ($tipo === 'emprendimiento') {
                $representante = trim($_POST['representante'] ?? '');
                $ci = trim($_POST['ci'] ?? $_POST['nit'] ?? '');
                $rubro = trim($_POST['rubro_emprendimiento'] ?? '');
                $descripcion = trim($_POST['descripcion_empresa'] ?? '');

                // Procesamiento de foto del puesto o imagen (opcional con imagen por defecto)
                $roePath = 'uploads/roe/roe_default.png';
                if (isset($_FILES['roe_archivo']) && $_FILES['roe_archivo']['error'] === UPLOAD_ERR_OK) {
                    $subido = subirArchivo('roe_archivo', 'roe', 'roe');
                    if ($subido === false) {
                        $error = 'La foto del puesto o imagen del emprendimiento debe ser una imagen válida (JPG, PNG) y no superar los 5MB.';
                    } elseif ($subido !== null) {
                        $roePath = 'uploads/' . $subido;
                    }
                } elseif (!empty($_POST['roe_actual'])) {
                    $roePath = trim($_POST['roe_actual']);
                }

                if ($representante === '') {
                    $error = 'El nombre del emprendedor(a) o responsable es obligatorio.';
                } elseif ($descripcion === '') {
                    $error = 'Por favor, ingresa una descripción de las actividades de tu emprendimiento.';
                } else {
                    $descFinal = '';
                    if ($rubro !== '') {
                        $descFinal .= "Rubro: $rubro. ";
                    }
                    $descFinal .= $descripcion;

                    $datosEmprendimiento = [
                        'nombre'        => $nombre,
                        'representante' => $representante,
                        'rubro'         => $rubro ?: 'General',
                        'ci'            => $ci ?: null,
                        'correo'        => $correo,
                        'contrasena'    => $hashContrasena,
                        'telefono'      => $telefono,
                        'foto_puesto'   => $roePath,
                        'descripcion'   => $descripcion,
                        'verificado'    => 1,
                    ];

                    $datosEmpresa = [
                        'nombre'        => $nombre,
                        'tipo_cuenta'   => 'emprendimiento',
                        'representante' => $representante,
                        'nit'           => $ci ?: null,
                        'roe'           => $roePath,
                        'correo'        => $correo,
                        'contrasena'    => $hashContrasena,
                        'telefono'      => $telefono,
                        'descripcion'   => trim($descFinal) ?: null,
                        'documento_legal_path' => null,
                        'verificada'    => 1,
                    ];
                }
            } else {
                // Empleado / Candidato
                $fechaNacimiento = $_POST['fecha_nacimiento'] ?? '';
                if ($fechaNacimiento === '') {
                    $error = 'Ingresa tu fecha de nacimiento.';
                } else {
                    $nac = new DateTime($fechaNacimiento);
                    $hoy = new DateTime();
                    if ($nac > $hoy) {
                        $error = 'La fecha de nacimiento no puede ser una fecha futura.';
                    } else {
                        $edad = $hoy->diff($nac)->y;

                        if ($edad < 16) {
                            $error = 'Debes tener al menos 16 años para crear una cuenta en la plataforma.';
                        } elseif ($edad < 18) {
                            // Menor de 18 años -> tabla usuarios_men
                            $esMenor = true;
                            $tutorNombre = trim($_POST['tutor_nombre'] ?? '');
                            $tutorCi = trim($_POST['tutor_ci'] ?? '');
                            $tutorCorreo = trim($_POST['tutor_correo'] ?? '');
                            $tutorTel = trim($_POST['tutor_telefono'] ?? '');
                            $tutorRel = trim($_POST['tutor_relacion'] ?? '');

                            if ($tutorNombre === '' || $tutorCi === '' || $tutorCorreo === '' || $tutorTel === '') {
                                $error = 'Al tener entre 16 y 17 años, es obligatorio ingresar el nombre, cédula de identidad, correo electrónico y teléfono de tu tutor(a) legal.';
                            } elseif (!filter_var($tutorCorreo, FILTER_VALIDATE_EMAIL)) {
                                $error = 'Por favor, ingresa una dirección de correo electrónico válida para tu tutor(a) legal.';
                            } else {
                                $datosMenor = [
                                    'nombre' => $nombre,
                                    'correo' => $correo,
                                    'contrasena' => $hashContrasena,
                                    'telefono' => $telefono ?: null,
                                    'fecha_nacimiento' => $fechaNacimiento,
                                    'cv_path' => null,
                                    'permiso_tutor_path' => null,
                                ];
                                $datosTutor = [
                                    'nombre'   => $tutorNombre,
                                    'ci'       => $tutorCi,
                                    'correo'   => $tutorCorreo,
                                    'telefono' => $tutorTel,
                                    'relacion' => $tutorRel ?: 'Tutor legal',
                                ];
                            }
                        } else {
                            // Mayor de 18 años -> tabla usuarios_may
                            $datosMayor = [
                                'nombre' => $nombre,
                                'correo' => $correo,
                                'contrasena' => $hashContrasena,
                                'tipo' => 'empleado',
                                'telefono' => $telefono ?: null,
                                'fecha_nacimiento' => $fechaNacimiento,
                                'cv_path' => null,
                            ];
                        }
                    }
                }

                // Subida y validación obligatoria del Curriculum Vitae en PDF
                if (!isset($error)) {
                    if (!isset($_FILES['cv_archivo']) || $_FILES['cv_archivo']['error'] !== UPLOAD_ERR_OK) {
                        $error = 'Es obligatorio adjuntar tu Curriculum Vitae en formato PDF para completar tu registro.';
                    } else {
                        $extCv = strtolower(pathinfo($_FILES['cv_archivo']['name'] ?? '', PATHINFO_EXTENSION));
                        if ($extCv !== 'pdf') {
                            $error = 'El archivo de Curriculum Vitae debe ser obligatoriamente un documento en formato PDF (.pdf).';
                        } else {
                            $cvPath = subirArchivo('cv_archivo', 'cv', 'cv');
                            if (!$cvPath) {
                                $error = 'No se pudo subir el archivo PDF del Curriculum Vitae. Verifica que no exceda el límite de 5MB.';
                            } else {
                                if ($esMenor) {
                                    $datosMenor['cv_path'] = $cvPath;
                                } else {
                                    $datosMayor['cv_path'] = $cvPath;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (isset($error)) {
            vista('registro', [
                'tipo' => $tipo ?: 'empleado',
                'error' => $error,
                'form' => $_POST,
            ]);
            return;
        }

        if ($tipo === 'emprendimiento') {
            try {
                // Guardar en la tabla emprendimientos
                $nuevoId = $modeloEmprendimiento->crear($datosEmprendimiento);

                // Sincronizar en empresas para compatibilidad relacional
                $datosEmpresa['id'] = $nuevoId;
                $modeloEmpresa->crearConId($datosEmpresa);

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $nuevoId;
                $_SESSION['usuario'] = $nombre;
                $_SESSION['correo'] = $correo;
                $_SESSION['tipo'] = 'empresa';
                $_SESSION['subtipo'] = 'emprendimiento';
                $_SESSION['es_menor'] = 0;
                $_SESSION['representante'] = $representante ?: null;
                $_SESSION['tabla_usuario'] = 'emprendimientos';

                header('Location: ban.php?pagina=panel_empresa');
                exit;
            } catch (PDOException $errorBase) {
                error_log('Error registro emprendimiento: ' . $errorBase->getMessage());
                vista('registro', [
                    'tipo' => 'emprendimiento',
                    'error' => 'No se pudo completar el registro del emprendimiento. Verifica los datos o intenta nuevamente.',
                    'form' => $_POST,
                ]);
                return;
            }
        } elseif ($tipo === 'empresa') {
            try {
                $modeloEmpresa->crear($datosEmpresa);
                $nuevoId = (int) $conexion->lastInsertId();

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $nuevoId;
                $_SESSION['usuario'] = $nombre;
                $_SESSION['correo'] = $correo;
                $_SESSION['tipo'] = 'empresa';
                $_SESSION['subtipo'] = 'empresa';
                $_SESSION['es_menor'] = 0;
                $_SESSION['representante'] = $representante ?: null;
                $_SESSION['tabla_usuario'] = 'empresas';

                header('Location: ban.php?pagina=panel_empresa');
                exit;
            } catch (PDOException $errorBase) {
                error_log('Error registro empresa: ' . $errorBase->getMessage());
                vista('registro', [
                    'tipo' => 'empresa',
                    'error' => 'No se pudo completar el registro. Verifica los datos o intenta nuevamente.',
                    'form' => $_POST,
                ]);
                return;
            }
        } else {
            // Empleado: menor en usuarios_men o mayor en usuarios_may
            if ($esMenor) {
                require_once __DIR__ . '/../Models/UsuarioMen.php';
                $modeloMen = new UsuarioMen($conexion);

                try {
                    $nuevoId = $modeloMen->crearConTutor($datosMenor, $datosTutor);

                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $nuevoId;
                    $_SESSION['usuario'] = $nombre;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['tipo'] = 'empleado';
                    $_SESSION['es_menor'] = 1;
                    $_SESSION['tabla_usuario'] = 'usuarios_men';

                    header('Location: ban.php?pagina=trabajos');
                    exit;
                } catch (PDOException $errorBase) {
                    error_log('Error registro menor: ' . $errorBase->getMessage());
                    vista('registro', [
                        'tipo' => 'empleado',
                        'error' => 'No se pudo completar el registro del menor con su tutor legal. Intenta nuevamente.',
                        'form' => $_POST,
                    ]);
                    return;
                }
            } else {
                require_once __DIR__ . '/../Models/UsuarioMay.php';
                $modeloMay = new UsuarioMay($conexion);

                try {
                    $modeloMay->crear($datosMayor);
                    $nuevoId = (int) $conexion->lastInsertId();

                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $nuevoId;
                    $_SESSION['usuario'] = $nombre;
                    $_SESSION['correo'] = $correo;
                    $_SESSION['tipo'] = 'empleado';
                    $_SESSION['es_menor'] = 0;
                    $_SESSION['tabla_usuario'] = 'usuarios_may';
                enviarCorreoRegistro([
                        'rol'      => $tipo,
                        'nombre'   => $nombre,
                        'correo'   => $correo,
                        'telefono' => $telefono,
                        'roe'      => $roe ?? '',
                    ]);

                    header('Location: ban.php?pagina=trabajos');
                    exit;
                } catch (PDOException $errorBase) {
                    error_log('Error registro mayor: ' . $errorBase->getMessage());
                    vista('registro', [
                        'tipo' => 'empleado',
                        'error' => 'No se pudo completar el registro. Verifica los datos e intenta nuevamente.',
                        'form' => $_POST,
                    ]);
                    return;
                }
            }
        }
    }
}
