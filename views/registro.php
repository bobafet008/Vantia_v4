<?php 
require __DIR__ . '/partials/header.php'; 
$tipo = (!empty($tipo) && in_array($tipo, ['empresa', 'emprendimiento'], true)) ? $tipo : 'empleado'; 
$roeActual = $form['roe_actual'] ?? $form['roe'] ?? 'uploads/roe/roe_default.png';
?>

<section class="auth-layout">
  <div class="auth-info">
    <span class="etiqueta">VANTIA · <?= $tipo === 'empresa' ? 'EMPRESAS VERIFICADAS' : ($tipo === 'emprendimiento' ? 'EMPRENDIMIENTOS' : 'JOVENES Y TALENTO') ?></span>
    <h1><?= $tipo === 'empresa' ? 'Conecta con nuevo talento.' : ($tipo === 'emprendimiento' ? 'Impulsa tu emprendimiento con talento joven.' : 'Da el primer paso hacia tu empleo.') ?></h1>
    <p>
      <?= $tipo === 'empresa' 
          ? 'Registra los datos de tu empresa u organizacion, incluyendo NIT y ROE para publicar vacantes seguras.' 
          : ($tipo === 'emprendimiento' 
              ? 'Registra tu negocio o proyecto independiente para publicar vacantes y sumar talento joven a tu equipo sin tramites burocraticos.' 
              : 'Crea tu perfil laboral para postularte a ofertas de trabajo verificadas. Si eres menor de edad, te acompanamos con el registro de tu tutor legal.') ?>
    </p>

    <div class="nota-seguridad" style="margin-top:2rem;">
      <strong>Proteccion y Verificacion</strong>
      <span>Vantia valida organizaciones y resguarda a los jovenes postulantes en conformidad con la normativa laboral.</span>
    </div>
  </div>

  <div class="form-card">
    
    <div style="display:flex; background:#eef2f6; padding:4px; border-radius:6px; margin-bottom:1.25rem; gap:4px;">
      <a href="index.php?pagina=registro_empleado" style="flex:1; text-align:center; padding:9px 8px; font-size:12px; font-weight:bold; border-radius:4px; text-decoration:none; transition:all 0.2s; <?= $tipo === 'empleado' ? 'background:var(--azul); color:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);' : 'color:var(--gris); background:transparent;' ?>">
        Joven / Postulante
      </a>
      <a href="index.php?pagina=registro_empresa" style="flex:1; text-align:center; padding:9px 8px; font-size:12px; font-weight:bold; border-radius:4px; text-decoration:none; transition:all 0.2s; <?= $tipo === 'empresa' ? 'background:var(--azul); color:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);' : 'color:var(--gris); background:transparent;' ?>">
        Empresa / Empleador
      </a>
      <a href="index.php?pagina=registro_emprendimiento" style="flex:1; text-align:center; padding:9px 8px; font-size:12px; font-weight:bold; border-radius:4px; text-decoration:none; transition:all 0.2s; <?= $tipo === 'emprendimiento' ? 'background:var(--azul); color:white; box-shadow:0 1px 3px rgba(0,0,0,0.1);' : 'color:var(--gris); background:transparent;' ?>">
        Emprendimientos
      </a>
    </div>

    <h2><?= $tipo === 'empresa' ? 'Registro de empresa' : ($tipo === 'emprendimiento' ? 'Registro de emprendimiento' : 'Registro de empleado / joven') ?></h2>
    <p class="texto-suave">Completa los datos para crear tu cuenta en la plataforma.</p>

    <?php if (!empty($error)): ?>
      <div class="mensaje-error"><?= e($error) ?></div>
    <?php endif; ?>

    <form action="index.php?pagina=guardar_usuario" method="post" id="formRegistro" enctype="multipart/form-data">
      <input type="hidden" name="tipo" value="<?= e($tipo) ?>">

      <label for="nombre">Nombre <?= $tipo === 'empresa' ? 'de la empresa' : ($tipo === 'emprendimiento' ? 'del emprendimiento' : 'completo') ?> *</label>
      <input id="nombre" name="nombre" type="text" required maxlength="120" value="<?= e($form['nombre'] ?? '') ?>" placeholder="<?= $tipo === 'empresa' ? 'Ej. Soluciones Digitales SRL' : ($tipo === 'emprendimiento' ? 'Ej. Dulce Cafe o Taller Creativo' : 'Ej. Juan Perez') ?>">

      <label for="correo">Correo electronico *</label>
      <input id="correo" name="correo" type="email" required maxlength="150" value="<?= e($form['correo'] ?? '') ?>" placeholder="ejemplo@correo.com">
      <div id="errorCorreo" class="mensaje-error" style="display:none; margin-top:4px; padding:6px 10px; font-size:12px;"></div>

      <label for="contrasena">Contrasena *</label>
      <input id="contrasena" name="contrasena" type="password" required minlength="6" placeholder="Minimo 6 caracteres" title="Debe contener al menos 1 mayuscula, 1 minuscula, 1 numero y 1 caracter especial">
      <small style="display:block; margin-top:-4px; margin-bottom:8px; color:var(--gris);">Debe incluir al menos 1 mayuscula, 1 minuscula, 1 numero y 1 caracter especial.</small>
      <div id="errorPasswordPolicy" class="mensaje-error" style="display:none; margin-top:4px; padding:6px 10px; font-size:12px;"></div>

      <label for="confirmar_contrasena">Confirmar contrasena *</label>
      <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" required minlength="6" placeholder="Repite tu contrasena">
      <div id="errorPasswordMismatch" class="mensaje-error" style="display:none; margin-top:4px; padding:6px 10px; font-size:12px;">Las contrasenas no coinciden.</div>

      <label for="telefono">Telefono / WhatsApp (8 digitos) *</label>
      <input id="telefono" name="telefono" type="text" required maxlength="8" minlength="8" pattern="[0-9]{8}" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 8)" value="<?= e($form['telefono'] ?? '') ?>" placeholder="Ej. 70012345" title="Debe contener solo numeros y exactamente 8 digitos">
      <small style="display:block; margin-top:-4px; margin-bottom:8px; color:var(--gris);">Solo numeros (exactamente 8 digitos).</small>
      <div id="errorTelefono" class="mensaje-error" style="display:none; margin-top:4px; padding:6px 10px; font-size:12px;"></div>

      <?php if ($tipo === 'empresa'): ?>
        <label for="representante">Nombre del representante / persona de contacto *</label>
        <input id="representante" name="representante" type="text" required maxlength="150" value="<?= e($form['representante'] ?? '') ?>" placeholder="Ej. Juan Perez">

        <label for="nit">Numero de NIT *</label>
        <input id="nit" name="nit" type="text" required minlength="7" maxlength="50" pattern="[0-9]{7,}" inputmode="numeric" oninput="this.value = this.value.replace(/\D/g, '')" value="<?= e($form['nit'] ?? '') ?>" placeholder="Ej. 1029384756" title="El NIT debe ser numerico y tener al menos 7 digitos">
        <small style="display:block; margin-top:-4px; margin-bottom:8px; color:var(--gris);">NIT oficial de la empresa (solo digitos numericos).</small>

        <label for="roe_archivo">Certificado / Codigo QR de ROE (Formato de imagen) *</label>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:1rem; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
          <img id="previewRoe" src="<?= e(rutaArchivo($roeActual)) ?>" alt="Codigo QR ROE" style="width:85px; height:85px; object-fit:contain; border:1px solid #cbd5e1; border-radius:6px; background:#fff; padding:4px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
          <div style="flex:1; min-width:200px;">
            <div style="font-weight:600; font-size:0.88rem; color:#1e293b; margin-bottom:2px;">Imagen asignada para validacion laboral</div>
            <div style="font-size:0.78rem; color:#64748b; margin-bottom:8px;">Formato de imagen (PNG, JPG). Codigo QR predeterminado activo. Puedes subir otra imagen si lo requieres:</div>
            <input type="file" id="roe_archivo" name="roe_archivo" accept="image/png, image/jpeg, image/jpg" style="font-size:0.82rem;" onchange="actualizarPreviewRoe(this)">
            <input type="hidden" name="roe_actual" value="<?= e($roeActual) ?>">
          </div>
        </div>

        <label for="descripcion_empresa">Descripcion de la empresa</label>
        <textarea id="descripcion_empresa" name="descripcion_empresa" rows="3" placeholder="A que se dedica tu empresa y que rubro maneja"><?= e($form['descripcion_empresa'] ?? '') ?></textarea>
      
      <?php elseif ($tipo === 'emprendimiento'): ?>
        <label for="representante">Nombre del emprendedor(a) o responsable *</label>
        <input id="representante" name="representante" type="text" required maxlength="150" value="<?= e($form['representante'] ?? '') ?>" placeholder="Ej. Carlos Mendoza">

        <label for="rubro_emprendimiento">Rubro / Sector del emprendimiento *</label>
        <?php $rubroActual = $form['rubro_emprendimiento'] ?? ''; ?>
        <select id="rubro_emprendimiento" name="rubro_emprendimiento" required>
          <option value="" disabled <?= $rubroActual === '' ? 'selected' : '' ?>>-- Selecciona un rubro --</option>
          <option value="Gastronomia y Alimentos" <?= $rubroActual === 'Gastronomia y Alimentos' ? 'selected' : '' ?>>Gastronomia y Alimentos</option>
          <option value="Tecnologia y Diseno" <?= $rubroActual === 'Tecnologia y Diseno' ? 'selected' : '' ?>>Tecnologia y Diseno</option>
          <option value="Comercio y Ventas" <?= $rubroActual === 'Comercio y Ventas' ? 'selected' : '' ?>>Comercio y Ventas</option>
          <option value="Servicios y Atencion al Cliente" <?= $rubroActual === 'Servicios y Atencion al Cliente' ? 'selected' : '' ?>>Servicios y Atencion al Cliente</option>
          <option value="Artesania y Manufactura" <?= $rubroActual === 'Artesania y Manufactura' ? 'selected' : '' ?>>Artesania y Manufactura</option>
          <option value="Educacion y Capacitacion" <?= $rubroActual === 'Educacion y Capacitacion' ? 'selected' : '' ?>>Educacion y Capacitacion</option>
          <option value="Belleza y Cuidado Personal" <?= $rubroActual === 'Belleza y Cuidado Personal' ? 'selected' : '' ?>>Belleza y Cuidado Personal</option>
          <option value="Otro" <?= $rubroActual === 'Otro' ? 'selected' : '' ?>>Otro rubro</option>
        </select>

        <label for="ci">Cédula de Identidad (C.I.) (Opcional)</label>
        <input id="ci" name="ci" type="text" maxlength="50" value="<?= e($form['ci'] ?? $form['nit'] ?? '') ?>" placeholder="Ej. 1234567 LP o número de carnet">
        <small style="display:block; margin-top:-4px; margin-bottom:8px; color:var(--gris);">Número de carnet de identidad del emprendedor(a) o responsable.</small>

        <label for="roe_archivo">Foto del puesto (Opcional)</label>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:12px; margin-bottom:1rem; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
          <img id="previewRoe" src="<?= e(rutaArchivo($roeActual)) ?>" alt="Foto del puesto" style="width:85px; height:85px; object-fit:contain; border:1px solid #cbd5e1; border-radius:6px; background:#fff; padding:4px; box-shadow:0 1px 3px rgba(0,0,0,0.08);">
          <div style="flex:1; min-width:200px;">
            <div style="font-weight:600; font-size:0.88rem; color:#1e293b; margin-bottom:2px;">Foto del puesto o imagen de tu negocio</div>
            <div style="font-size:0.78rem; color:#64748b; margin-bottom:8px;">Formato de imagen (PNG, JPG). Si no cuentas con una foto del puesto, se asignara una imagen predeterminada. Puedes subirla cuando gustes:</div>
            <input type="file" id="roe_archivo" name="roe_archivo" accept="image/png, image/jpeg, image/jpg" style="font-size:0.82rem;" onchange="actualizarPreviewRoe(this)">
            <input type="hidden" name="roe_actual" value="<?= e($roeActual) ?>">
          </div>
        </div>

        <label for="descripcion_empresa">Descripcion de tus actividades *</label>
        <textarea id="descripcion_empresa" name="descripcion_empresa" rows="3" required placeholder="Describe brevemente que productos, servicios o proyectos realizas"><?= e($form['descripcion_empresa'] ?? '') ?></textarea>

      <?php else: ?>
        <label for="fecha_nacimiento">Fecha de nacimiento *</label>
        <input id="fecha_nacimiento" name="fecha_nacimiento" type="date" required max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d', strtotime('-100 years')) ?>" value="<?= e($form['fecha_nacimiento'] ?? '') ?>">

        
        <div id="alertaMenor16" class="mensaje-error" style="display: none; margin-top: 10px;">
          <strong>Edad no permitida:</strong> Debes tener al menos 16 años cumplidos para crear una cuenta en Vantia.
        </div>

        
        <div id="seccionTutor" class="caja-tutor" style="display: none;">
          <div class="aviso-tutor">
            <strong>Detección de joven de 16 a 17 años</strong>
            <span>Al ser menor de 18 años, la ley exige que cuentes con la autorización y datos de tu padre, madre o tutor legal.</span>
          </div>

          <label for="tutor_nombre">Nombre del padre, madre o tutor *</label>
          <input id="tutor_nombre" name="tutor_nombre" type="text" maxlength="100" value="<?= e($form['tutor_nombre'] ?? '') ?>" placeholder="Nombre completo del tutor">

          <label for="tutor_ci">Número de Cédula de Identidad (C.I.) del tutor *</label>
          <input id="tutor_ci" name="tutor_ci" type="text" maxlength="30" value="<?= e($form['tutor_ci'] ?? '') ?>" placeholder="Ej. 12345678 LP o documento de identidad">

          <label for="tutor_correo">Correo electrónico del padre, madre o tutor *</label>
          <input id="tutor_correo" name="tutor_correo" type="email" maxlength="150" value="<?= e($form['tutor_correo'] ?? '') ?>" placeholder="correo.tutor@ejemplo.com">

          <label for="tutor_telefono">Teléfono de contacto del tutor *</label>
          <input id="tutor_telefono" name="tutor_telefono" type="text" maxlength="30" value="<?= e($form['tutor_telefono'] ?? '') ?>" placeholder="Teléfono del tutor">

          <label for="tutor_relacion">Parentesco / Relación</label>
          <?php $relacionActual = $form['tutor_relacion'] ?? 'Madre'; ?>
          <select id="tutor_relacion" name="tutor_relacion">
            <option value="Madre" <?= $relacionActual === 'Madre' ? 'selected' : '' ?>>Madre</option>
            <option value="Padre" <?= $relacionActual === 'Padre' ? 'selected' : '' ?>>Padre</option>
            <option value="Tutor legal" <?= $relacionActual === 'Tutor legal' ? 'selected' : '' ?>>Tutor(a) legal</option>
          </select>
        </div>

        <label for="cv_archivo" style="margin-top:1.15rem;">Curriculum Vitae (CV en formato PDF) *</label>
        <div style="background:#f8fafc; border:1px solid #cbd5e1; border-radius:8px; padding:12px; margin-bottom:1rem;">
          <input type="file" id="cv_archivo" name="cv_archivo" accept="application/pdf,.pdf" required style="font-size:0.88rem; width:100%;" onchange="validarCvPdf(this)">
          <div style="font-size:0.78rem; color:#64748b; margin-top:6px;">
            Documento obligatorio en formato PDF (.pdf, máx. 5MB). Este archivo será tu presentación laboral ante las empresas empleadoras.
          </div>
        </div>
      <?php endif; ?>

      <button id="btnCrearCuenta" class="boton ancho" type="submit" style="margin-top:1.5rem;">Crear cuenta e ingresar</button>
    </form>

    <div class="auth-footer-links">
      <p>¿Ya tienes una cuenta?</p>
      <a href="index.php?pagina=login">Iniciar sesión</a>
    </div>
  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('formRegistro');
  const pass = document.getElementById('contrasena');
  const passConfirm = document.getElementById('confirmar_contrasena');
  const inputCorreo = document.getElementById('correo');
  const inputTelefono = document.getElementById('telefono');
  const errorPass = document.getElementById('errorPasswordMismatch');
  const errorPassPolicy = document.getElementById('errorPasswordPolicy');
  const errorTelefono = document.getElementById('errorTelefono');
  const errorCorreo = document.getElementById('errorCorreo');

  function validarPoliticaPassword() {
    if (!pass) return true;
    const val = pass.value;
    if (!val) {
      if (errorPassPolicy) errorPassPolicy.style.display = 'none';
      pass.setCustomValidity('');
      return true;
    }
    const tieneMayus = /[A-Z]/.test(val);
    const tieneMinus = /[a-z]/.test(val);
    const tieneNum = /[0-9]/.test(val);
    const tieneEspecial = /[^a-zA-Z0-9]/.test(val);

    let faltantes = [];
    if (!tieneMayus) faltantes.push('1 mayúscula');
    if (!tieneMinus) faltantes.push('1 minúscula');
    if (!tieneNum) faltantes.push('1 número');
    if (!tieneEspecial) faltantes.push('1 carácter especial');
    if (val.length < 6) faltantes.push('mínimo 6 caracteres');

    if (faltantes.length > 0) {
      const msg = 'La contraseña requiere: ' + faltantes.join(', ') + '.';
      if (errorPassPolicy) {
        errorPassPolicy.textContent = msg;
        errorPassPolicy.style.display = 'block';
      }
      pass.setCustomValidity(msg);
      return false;
    } else {
      if (errorPassPolicy) errorPassPolicy.style.display = 'none';
      pass.setCustomValidity('');
      return true;
    }
  }

  function validarPasswords() {
    if (!passConfirm || !pass) return true;
    if (passConfirm.value && pass.value !== passConfirm.value) {
      if (errorPass) errorPass.style.display = 'block';
      passConfirm.setCustomValidity('Las contraseñas no coinciden');
      return false;
    } else {
      if (errorPass) errorPass.style.display = 'none';
      passConfirm.setCustomValidity('');
      return true;
    }
  }

  function validarTelefono() {
    if (!inputTelefono) return true;
    const val = inputTelefono.value.trim();
    if (val.length > 0 && !/^[0-9]{8}$/.test(val)) {
      const msg = 'El teléfono debe contener solo números y exactamente 8 dígitos.';
      if (errorTelefono) {
        errorTelefono.textContent = msg;
        errorTelefono.style.display = 'block';
      }
      inputTelefono.setCustomValidity(msg);
      return false;
    } else {
      if (errorTelefono) errorTelefono.style.display = 'none';
      inputTelefono.setCustomValidity('');
      return true;
    }
  }

  function validarCorreo() {
    if (!inputCorreo) return true;
    const val = inputCorreo.value.trim();
    if (val.length > 0 && (!val.includes('@') || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val))) {
      const msg = 'El correo debe incluir un signo @ y tener un formato válido (ej. usuario@correo.com).';
      if (errorCorreo) {
        errorCorreo.textContent = msg;
        errorCorreo.style.display = 'block';
      }
      inputCorreo.setCustomValidity(msg);
      return false;
    } else {
      if (errorCorreo) errorCorreo.style.display = 'none';
      inputCorreo.setCustomValidity('');
      return true;
    }
  }

  if (pass) {
    pass.addEventListener('input', function() {
      validarPoliticaPassword();
      if (passConfirm && passConfirm.value) validarPasswords();
    });
  }

  if (passConfirm) {
    passConfirm.addEventListener('input', validarPasswords);
  }

  if (inputTelefono) {
    inputTelefono.addEventListener('input', validarTelefono);
  }

  if (inputCorreo) {
    inputCorreo.addEventListener('input', validarCorreo);
  }

  if (form) {
    form.addEventListener('submit', function(e) {
      const okPassPol = validarPoliticaPassword();
      const okPass = validarPasswords();
      const okTel = validarTelefono();
      const okCor = validarCorreo();

      if (!okPassPol) {
        e.preventDefault();
        pass.focus();
      } else if (!okPass) {
        e.preventDefault();
        passConfirm.focus();
      } else if (!okTel) {
        e.preventDefault();
        inputTelefono.focus();
      } else if (!okCor) {
        e.preventDefault();
        inputCorreo.focus();
      }
    });
  }

  <?php if ($tipo === 'empleado'): ?>
  const inputFecha = document.getElementById('fecha_nacimiento');
  const seccionTutor = document.getElementById('seccionTutor');
  const alertaMenor16 = document.getElementById('alertaMenor16');
  const btnCrearCuenta = document.getElementById('btnCrearCuenta');
  const inputTutorNombre = document.getElementById('tutor_nombre');
  const inputTutorCi = document.getElementById('tutor_ci');
  const inputTutorCorreo = document.getElementById('tutor_correo');
  const inputTutorTel = document.getElementById('tutor_telefono');

  function calcularEdad(fechaString) {
    if (!fechaString) return null;
    const partes = fechaString.split('-');
    if (partes.length !== 3) return null;
    const [y, m, d] = partes.map(Number);
    if (!y || !m || !d) return null;
    const nac = new Date(y, m - 1, d);
    const hoy = new Date();
    let edad = hoy.getFullYear() - nac.getFullYear();
    const mes = hoy.getMonth() - nac.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nac.getDate())) {
      edad--;
    }
    return edad;
  }

  function verificarMenor() {
    if (!inputFecha.value) {
      alertaMenor16.style.display = 'none';
      seccionTutor.style.display = 'none';
      inputTutorNombre.required = false;
      if (inputTutorCi) inputTutorCi.required = false;
      if (inputTutorCorreo) inputTutorCorreo.required = false;
      inputTutorTel.required = false;
      btnCrearCuenta.disabled = false;
      btnCrearCuenta.style.opacity = '1';
      btnCrearCuenta.style.cursor = 'pointer';
      return;
    }

    const edad = calcularEdad(inputFecha.value);

    if (edad !== null && edad < 0) {
      alertaMenor16.innerHTML = '<strong>Fecha no válida:</strong> La fecha de nacimiento no puede ser futura.';
      alertaMenor16.style.display = 'block';
      seccionTutor.style.display = 'none';
      inputTutorNombre.required = false;
      if (inputTutorCi) inputTutorCi.required = false;
      if (inputTutorCorreo) inputTutorCorreo.required = false;
      inputTutorTel.required = false;
      btnCrearCuenta.disabled = true;
      btnCrearCuenta.style.opacity = '0.5';
      btnCrearCuenta.style.cursor = 'not-allowed';
    } else if (edad !== null && edad < 16) {
      alertaMenor16.innerHTML = '<strong>Edad no permitida:</strong> Debes tener al menos 16 años cumplidos para crear una cuenta en Vantia.';
      alertaMenor16.style.display = 'block';
      seccionTutor.style.display = 'none';
      inputTutorNombre.required = false;
      if (inputTutorCi) inputTutorCi.required = false;
      if (inputTutorCorreo) inputTutorCorreo.required = false;
      inputTutorTel.required = false;
      btnCrearCuenta.disabled = true;
      btnCrearCuenta.style.opacity = '0.5';
      btnCrearCuenta.style.cursor = 'not-allowed';
    } else if (edad !== null && edad < 18) {
      alertaMenor16.style.display = 'none';
      seccionTutor.style.display = 'block';
      inputTutorNombre.required = true;
      if (inputTutorCi) inputTutorCi.required = true;
      if (inputTutorCorreo) inputTutorCorreo.required = true;
      inputTutorTel.required = true;
      btnCrearCuenta.disabled = false;
      btnCrearCuenta.style.opacity = '1';
      btnCrearCuenta.style.cursor = 'pointer';
    } else {
      alertaMenor16.style.display = 'none';
      seccionTutor.style.display = 'none';
      inputTutorNombre.required = false;
      if (inputTutorCi) inputTutorCi.required = false;
      if (inputTutorCorreo) inputTutorCorreo.required = false;
      inputTutorTel.required = false;
      btnCrearCuenta.disabled = false;
      btnCrearCuenta.style.opacity = '1';
      btnCrearCuenta.style.cursor = 'pointer';
    }
  }

  inputFecha.addEventListener('change', verificarMenor);
  inputFecha.addEventListener('input', verificarMenor);
  verificarMenor();
  <?php endif; ?>
});

function actualizarPreviewRoe(input) {
  if (input.files && input.files[0]) {
    const lector = new FileReader();
    lector.onload = function(e) {
      const img = document.getElementById('previewRoe');
      if (img) {
        img.src = e.target.result;
      }
    };
    lector.readAsDataURL(input.files[0]);
  }
}

function validarCvPdf(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const extension = file.name.split('.').pop().toLowerCase();
    if (extension !== 'pdf') {
      alert('Solo se admiten documentos en formato PDF (.pdf) para tu Curriculum Vitae.');
      input.value = '';
      return false;
    }
    if (file.size > 5 * 1024 * 1024) {
      alert('El archivo PDF del Curriculum Vitae no debe exceder los 5MB de tamaño.');
      input.value = '';
      return false;
    }
  }
}
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
