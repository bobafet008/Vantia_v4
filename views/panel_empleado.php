<?php require __DIR__ . '/partials/header.php'; ?>

<section class="pagina-cabecera">
  <div class="contenedor">
    <span class="etiqueta">PORTAL DEL EMPLEADO / JOVEN</span>
    <h1>Mi Perfil y Configuración</h1>
    <p>Administra tu Curriculum Vitae, credenciales de acceso y datos de tu cuenta en Vantia.</p>

    <div style="display:flex; gap:10px; margin-top:1.25rem; flex-wrap:wrap;">
      <a href="index.php?pagina=mis_postulaciones" class="boton boton-sm">Mis Postulaciones</a>
      <a href="index.php?pagina=trabajos" class="boton boton-sm boton-oscuro">Explorar Oportunidades Laborales</a>
    </div>
  </div>
</section>

<section class="contenedor seccion">
  <?php if (!empty($mensajeExito)): ?>
    <div class="mensaje-exito" style="margin-bottom:1.5rem; font-weight:600;">
      <?= e($mensajeExito) ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($mensajeError)): ?>
    <div class="mensaje-error" style="margin-bottom:1.5rem; font-weight:600;">
      <?= e($mensajeError) ?>
    </div>
  <?php endif; ?>

  <div style="display:grid; grid-template-columns:320px 1fr; gap:28px; align-items:start;">
    
    
    <div style="background:white; border:1px solid var(--linea); border-radius:8px; padding:24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
      <div style="display:flex; align-items:center; gap:12px; margin-bottom:1rem;">
        <div style="width:48px; height:48px; border-radius:50%; background:#14233d; color:#d5a347; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:1.2rem;">
          <?= strtoupper(substr($usuario['nombre'] ?? 'U', 0, 1)) ?>
        </div>
        <div>
          <h2 style="font-size:1.15rem; margin:0; color:var(--azul); font-family:Georgia, serif;"><?= e($usuario['nombre']) ?></h2>
          <span style="font-size:0.8rem; color:var(--gris);">Candidato Postulante</span>
        </div>
      </div>

      <div style="margin-bottom:1rem; padding-bottom:1rem; border-bottom:1px solid #f1f5f9;">
        <span class="tipo-badge <?= $candidatoTipo === 'menor' ? 'empleado' : 'admin' ?>" style="font-size:11px; display:inline-block; margin-bottom:8px;">
          <?= $candidatoTipo === 'menor' ? 'Joven Menor de 18 años (16 y 17 años)' : 'Joven Mayor de 18 años' ?>
        </span>
        <div style="font-size:0.85rem; color:#475569; margin-bottom:4px;">
          <strong>Correo:</strong> <?= e($usuario['correo']) ?>
        </div>
        <div style="font-size:0.85rem; color:#475569; margin-bottom:4px;">
          <strong>Teléfono:</strong> <?= !empty($usuario['telefono']) ? e($usuario['telefono']) : 'No registrado' ?>
        </div>
        <?php if (!empty($usuario['fecha_nacimiento'])): ?>
          <div style="font-size:0.85rem; color:#475569;">
            <strong>Edad:</strong> <?= calcularEdad($usuario['fecha_nacimiento']) ?> años (<?= e($usuario['fecha_nacimiento']) ?>)
          </div>
        <?php endif; ?>
      </div>

      <?php if ($candidatoTipo === 'menor' && !empty($usuario['tutor_nombre'])): ?>
        
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:12px; margin-bottom:1rem;">
          <div style="font-size:0.75rem; font-weight:bold; color:#1d4ed8; text-transform:uppercase; margin-bottom:6px;">
            Tutor Legal Autorizado
          </div>
          <div style="font-size:0.85rem; font-weight:600; color:#1e293b;"><?= e($usuario['tutor_nombre']) ?></div>
          <div style="font-size:0.78rem; color:var(--gris);">Relación: <?= e($usuario['tutor_relacion'] ?? 'Tutor') ?></div>
          <?php if (!empty($usuario['tutor_ci'])): ?>
            <div style="font-size:0.78rem; color:#334155; margin-top:2px;"><strong>C.I.:</strong> <?= e($usuario['tutor_ci']) ?></div>
          <?php endif; ?>
          <?php if (!empty($usuario['tutor_correo'])): ?>
            <div style="font-size:0.78rem; color:#334155; margin-top:2px;"><strong>Correo:</strong> <?= e($usuario['tutor_correo']) ?></div>
          <?php endif; ?>
          <?php if (!empty($usuario['tutor_telefono'])): ?>
            <div style="font-size:0.78rem; color:#334155; margin-top:2px;"><strong>Teléfono:</strong> <?= e($usuario['tutor_telefono']) ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <div style="display:flex; flex-direction:column; gap:8px; margin-top:1rem;">
        <a href="#seccionCv" style="font-size:0.85rem; color:var(--azul); text-decoration:none; padding:6px 0; border-bottom:1px dashed #e2e8f0; font-weight:600;">
          Curriculum Vitae (CV)
        </a>
        <a href="#seccionCorreo" style="font-size:0.85rem; color:var(--azul); text-decoration:none; padding:6px 0; border-bottom:1px dashed #e2e8f0; font-weight:600;">
          Cambiar Correo Electronico
        </a>
        <a href="#seccionContrasena" style="font-size:0.85rem; color:var(--azul); text-decoration:none; padding:6px 0; font-weight:600;">
          Cambiar Contrasena
        </a>
      </div>
    </div>

    
    <div style="display:flex; flex-direction:column; gap:28px;">

      
      <div id="seccionCv" class="bloque-panel" style="background:white; border:1px solid var(--linea); border-radius:8px; padding:28px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div class="titulo-seccion" style="margin-bottom:1rem;">
          <div>
            <span class="etiqueta">DOCUMENTO LABORAL</span>
            <h2 style="font-size:1.4rem;">Curriculum Vitae (CV)</h2>
            <p class="texto-suave">Mantén actualizado tu CV en formato PDF. Al postularte, las empresas consultarán este archivo.</p>
          </div>
        </div>

        
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin-bottom:1.5rem; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
          <div>
            <div style="font-size:0.8rem; font-weight:bold; color:var(--gris); margin-bottom:4px;">ESTADO DEL CURRICULUM:</div>
            <?php if (!empty($usuario['cv_path'])): ?>
              <div style="display:flex; align-items:center; gap:8px;">
                <span class="badge-estado badge-contratado" style="font-size:11px;">CV Cargado y Activo</span>
                <span style="font-size:0.85rem; color:#475569; word-break:break-all;"><?= e(basename($usuario['cv_path'])) ?></span>
              </div>
            <?php else: ?>
              <span class="badge-estado badge-rechazado" style="font-size:11px;">No tienes un CV registrado</span>
            <?php endif; ?>
          </div>

          <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <?php if (!empty($usuario['cv_path'])): ?>
              <a href="<?= e(rutaArchivo($usuario['cv_path'])) ?>" target="_blank" class="boton boton-sm" style="background:#e0e7ff; color:#3730a3; padding:8px 14px; text-decoration:none; border:1px solid #c7d2fe;">
                Ver CV actual (PDF)
              </a>
              <form action="index.php?pagina=eliminar_cv_empleado" method="post" onsubmit="return confirm('¿Seguro que deseas eliminar tu Curriculum Vitae del servidor?');" style="display:inline; margin:0;">
                <button type="submit" class="boton-sm boton-peligro" style="padding:8px 12px; cursor:pointer;">
                  Eliminar CV
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        
        <form action="index.php?pagina=actualizar_cv_empleado" method="post" enctype="multipart/form-data" style="gap:10px;">
          <label for="cv_archivo" style="font-weight:bold; font-size:0.9rem;">
            <?= !empty($usuario['cv_path']) ? 'Subir nuevo CV (reemplaza y elimina el anterior) *' : 'Subir tu Curriculum Vitae (PDF) *' ?>
          </label>
          <div style="border:2px dashed #cbd5e1; border-radius:8px; padding:18px; text-align:center; background:#fafafa;">
            <input type="file" id="cv_archivo" name="cv_archivo" accept="application/pdf,.pdf" required style="font-size:0.88rem; max-width:350px; margin:auto;" onchange="validarCvPdfPanel(this)">
            <p style="font-size:0.78rem; color:#64748b; margin:8px 0 0;">
              Solo formato PDF (.pdf), tamaño máximo permitido: 5MB. Si ya posees un archivo cargado, será eliminado y sustituido inmediatamente.
            </p>
          </div>
          <div style="margin-top:6px;">
            <button type="submit" class="boton" style="padding:10px 18px;">
              Subir y actualizar Curriculum Vitae
            </button>
          </div>
        </form>
      </div>

      
      <div id="seccionCorreo" class="bloque-panel" style="background:white; border:1px solid var(--linea); border-radius:8px; padding:28px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div class="titulo-seccion" style="margin-bottom:1rem;">
          <div>
            <span class="etiqueta">DATOS DE ACCESO</span>
            <h2 style="font-size:1.4rem;">Cambio de Correo Electrónico</h2>
            <p class="texto-suave">Actualiza la dirección de correo electrónico con la que inicias sesión y recibes notificaciones.</p>
          </div>
        </div>

        <div style="background:#f1f5f9; border-radius:6px; padding:10px 14px; margin-bottom:1.25rem; font-size:0.88rem; color:#334155;">
          <strong>Correo actual:</strong> <code><?= e($usuario['correo']) ?></code>
        </div>

        <form action="index.php?pagina=cambiar_correo_empleado" method="post" style="gap:12px; max-width:540px;">
          <div>
            <label for="nuevo_correo" style="font-weight:bold; font-size:0.85rem;">Nuevo correo electrónico *</label>
            <input type="email" id="nuevo_correo" name="nuevo_correo" required maxlength="150" placeholder="nuevo.correo@ejemplo.com" style="margin-top:4px;">
          </div>

          <div>
            <label for="contrasena_confirm_correo" style="font-weight:bold; font-size:0.85rem;">Contraseña actual (por seguridad) *</label>
            <input type="password" id="contrasena_confirm_correo" name="contrasena_actual" required placeholder="Ingresa tu contraseña para autorizar el cambio" style="margin-top:4px;">
            <small style="display:block; color:var(--gris); margin-top:2px;">Requerimos tu contraseña actual para confirmar tu identidad.</small>
          </div>

          <div style="margin-top:8px;">
            <button type="submit" class="boton boton-oscuro" style="padding:10px 18px;">
              Guardar nuevo correo electrónico
            </button>
          </div>
        </form>
      </div>

      
      <div id="seccionContrasena" class="bloque-panel" style="background:white; border:1px solid var(--linea); border-radius:8px; padding:28px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
        <div class="titulo-seccion" style="margin-bottom:1rem;">
          <div>
            <span class="etiqueta">SEGURIDAD DE LA CUENTA</span>
            <h2 style="font-size:1.4rem;">Cambiar Contraseña</h2>
            <p class="texto-suave">Asegura tu cuenta actualizando periódicamente tu contraseña de ingreso.</p>
          </div>
        </div>

        <form action="index.php?pagina=cambiar_contrasena_empleado" method="post" id="formCambioPassword" style="gap:12px; max-width:540px;">
          <div>
            <label for="pass_actual" style="font-weight:bold; font-size:0.85rem;">Contraseña actual *</label>
            <input type="password" id="pass_actual" name="contrasena_actual" required placeholder="Tu contraseña actual" style="margin-top:4px;">
          </div>

          <div>
            <label for="pass_nueva" style="font-weight:bold; font-size:0.85rem;">Nueva contraseña (mínimo 6 caracteres) *</label>
            <input type="password" id="pass_nueva" name="nueva_contrasena" required minlength="6" placeholder="Mínimo 6 caracteres con mayúscula, minúscula, número y símbolo" style="margin-top:4px;">
            <small style="display:block; color:var(--gris); margin-top:2px;">Debe incluir al menos 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial.</small>
            <div id="errorPolicyPanel" class="mensaje-error" style="display:none; margin-top:6px; font-size:12px;"></div>
          </div>

          <div>
            <label for="pass_confirmar" style="font-weight:bold; font-size:0.85rem;">Confirmar nueva contraseña *</label>
            <input type="password" id="pass_confirmar" name="confirmar_nueva_contrasena" required minlength="6" placeholder="Vuelve a escribir la nueva contraseña" style="margin-top:4px;">
            <div id="errorMismatchPanel" class="mensaje-error" style="display:none; margin-top:6px; font-size:12px;">Las contraseñas no coinciden.</div>
          </div>

          <div style="margin-top:8px;">
            <button type="submit" class="boton boton-oscuro" style="padding:10px 18px;">
              Actualizar contraseña
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</section>

<script>
function validarCvPdfPanel(input) {
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

document.addEventListener('DOMContentLoaded', function() {
  const formPass = document.getElementById('formCambioPassword');
  const passNueva = document.getElementById('pass_nueva');
  const passConfirm = document.getElementById('pass_confirmar');
  const errorPass = document.getElementById('errorMismatchPanel');
  const errorPolicy = document.getElementById('errorPolicyPanel');

  function validarPolitica() {
    if (!passNueva) return true;
    const val = passNueva.value;
    if (!val) {
      if (errorPolicy) errorPolicy.style.display = 'none';
      passNueva.setCustomValidity('');
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
      if (errorPolicy) {
        errorPolicy.textContent = msg;
        errorPolicy.style.display = 'block';
      }
      passNueva.setCustomValidity(msg);
      return false;
    } else {
      if (errorPolicy) errorPolicy.style.display = 'none';
      passNueva.setCustomValidity('');
      return true;
    }
  }

  function validarCoincidencia() {
    if (passConfirm.value && passNueva.value !== passConfirm.value) {
      if (errorPass) errorPass.style.display = 'block';
      passConfirm.setCustomValidity('Las contraseñas no coinciden');
      return false;
    } else {
      if (errorPass) errorPass.style.display = 'none';
      passConfirm.setCustomValidity('');
      return true;
    }
  }

  if (passNueva && passConfirm) {
    passNueva.addEventListener('input', function() {
      validarPolitica();
      if (passConfirm.value) validarCoincidencia();
    });
    passConfirm.addEventListener('input', validarCoincidencia);
  }

  if (formPass) {
    formPass.addEventListener('submit', function(e) {
      const okPol = validarPolitica();
      const okCoin = validarCoincidencia();
      if (!okPol) {
        e.preventDefault();
        passNueva.focus();
      } else if (!okCoin) {
        e.preventDefault();
        passConfirm.focus();
      }
    });
  }
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
