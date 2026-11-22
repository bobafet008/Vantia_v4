<?php require __DIR__ . '/partials/header.php'; ?>

<section class="pagina-cabecera">
  <div class="contenedor">
    <span class="etiqueta">PANEL GENERAL DE CONTROL</span>
    <h1>Administración de Vantia</h1>
    <p>Supervisión de empresas verificadas, tutores legales, menores de edad y mayores de edad en la plataforma.</p>
    
    <div style="margin-top:1rem; display:flex; gap:0.6rem; flex-wrap:wrap; align-items:center;">
      <a href="ban.php?pagina=trabajos" class="boton boton-sm" style="background:#e2f5e8; color:#1f7a46;">Bolsa pública de empleos</a>
      <a href="ban.php?pagina=panel_reportes" class="boton boton-sm" style="background:#1e40af; color:#ffffff; font-weight:700; box-shadow:0 2px 4px rgba(30,64,175,0.25);">
        Ir a Panel Reportes (Registros, Baneos, Auditoría)
      </a>
    </div>
  </div>
</section>

<section class="contenedor seccion">
  <?php if (!empty($exito)): ?><div class="mensaje-exito" style="margin-bottom:1.5rem;"><?= e($exito) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="mensaje-error" style="margin-bottom:1.5rem;"><?= e($error) ?></div><?php endif; ?>

  <!-- Barra de Navegación Rápida entre Secciones -->
  <div style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:2rem; padding:0.85rem 1.2rem; background:#ffffff; border-radius:10px; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.04); align-items:center;">
    <span style="font-size:12px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.04em; margin-right:4px;">Navegación Rápida:</span>
    <a href="#seccionEmpresas" class="boton-sm" style="background:#f8fafc; color:#334155; border:1px solid #cbd5e1;">Empresas</a>
    <a href="#seccionEmprendimientos" class="boton-sm" style="background:#f8fafc; color:#334155; border:1px solid #cbd5e1;">Emprendimientos</a>
    <a href="#seccionMenores" class="boton-sm" style="background:#f8fafc; color:#334155; border:1px solid #cbd5e1;">Menores</a>
    <a href="#seccionTutores" class="boton-sm" style="background:#f8fafc; color:#334155; border:1px solid #cbd5e1;">Tutores</a>
    <a href="#seccionMayores" class="boton-sm" style="background:#f8fafc; color:#334155; border:1px solid #cbd5e1;">Mayores</a>
    <a href="#seccionAdmins" class="boton-sm" style="background:#f8fafc; color:#334155; border:1px solid #cbd5e1;">Admins</a>
    <a href="#seccionBloqueos" class="boton-sm" style="background:#f8fafc; color:#334155; border:1px solid #cbd5e1;">Correos</a>
    <a href="ban.php?pagina=panel_reportes" class="boton-sm" style="background:#1d4ed8; color:#ffffff; font-weight:700; border:1px solid #1e40af;">PANEL REPORTES</a>
  </div>

  <!-- Tarjetas de Estadísticas Globales -->
  <div class="tarjetas" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:2.5rem;">
    <div class="tarjeta">
      <span class="numero">01 · EMPRESAS</span>
      <h2><?= (int) $totalEmpresas ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">02 · MENORES (&lt;18)</span>
      <h2><?= (int) ($totalMenores ?? 0) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">03 · MAYORES (&ge;18)</span>
      <h2><?= (int) ($totalMayores ?? 0) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">04 · VACANTES</span>
      <h2><?= (int) $totalEmpleos ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">05 · POSTULACIONES</span>
      <h2><?= (int) $totalPostulaciones ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">06 · TUTORES</span>
      <h2><?= (int) ($totalTutores ?? 0) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">07 · ADMINS</span>
      <h2><?= (int) ($totalAdmins ?? 1) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">08 · EMPRENDIMIENTOS</span>
      <h2><?= (int) ($totalEmprendimientos ?? 0) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">09 · CORREOS BLOQUEADOS</span>
      <h2><?= (int) ($totalCorreosBloqueados ?? 0) ?></h2>
    </div>
  </div>

  <!-- TABLA 1: Empresas Registradas -->
  <div id="seccionEmpresas" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">EMPRESAS REGISTRADAS</span>
        <h2>Organizaciones y Validación Fiscal (NIT / ROE)</h2>
        <p class="texto-suave">Empresas registradas en la base de datos con verificación laboral.</p>
      </div>
    </div>



    <div class="tabla-contenedor">
      <table class="tabla-panel">
        <thead>
          <tr>
            <th>ID</th>
            <th>Empresa / Razón Social</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>NIT / ROE</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($empresas)): ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:1.5rem; color:var(--gris);">No hay empresas registradas actualmente.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($empresas as $emp): ?>
              <?php if (($emp['tipo_cuenta'] ?? 'empresa') === 'emprendimiento') continue; ?>
              <tr>
                <td>#<?= (int) $emp['id'] ?></td>
                <td>
                  <strong><?= e($emp['nombre']) ?></strong>
                  <?php if (!empty($emp['representante'])): ?>
                    <div style="font-size:0.82rem; color:var(--texto-suave, #64748b); margin-top:2px;">
                      Rep: <?= e($emp['representante']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="mailto:<?= e($emp['correo']) ?>" class="enlace-contacto"><?= e($emp['correo']) ?></a>
                  <?php 
                    $cNormEmp = strtolower(trim($emp['correo']));
                    $bloqEmp = $mapaBloqueados[$cNormEmp] ?? null;
                    $estaBloqEmp = !empty($bloqEmp) && ((int)($bloqEmp['bloqueado'] ?? 0) === 1 || (int)($bloqEmp['intentos'] ?? 0) >= 3);
                  ?>
                  <?php if ($estaBloqEmp): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:10px; font-weight:bold; display:inline-block;">Bloqueado (<?= (int)$bloqEmp['intentos'] ?> fallos)</span>
                    </div>
                  <?php elseif (!empty($bloqEmp['intentos']) && (int)$bloqEmp['intentos'] > 0): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; display:inline-block;"><?= (int)$bloqEmp['intentos'] ?> / 3 fallos</span>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= !empty($emp['telefono']) ? e($emp['telefono']) : '<span class="texto-suave">-</span>' ?></td>
                <td>
                  <div>NIT: <code><?= e($emp['nit'] ?? 'S/N') ?></code></div>
                  <div style="margin-top:6px;">
                    <?php if (!empty($emp['roe']) && preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', $emp['roe'])): ?>
                      <div style="font-size:0.75rem; font-weight:600; color:var(--texto-suave); margin-bottom:3px;">ROE (QR):</div>
                      <a href="<?= e(rutaArchivo($emp['roe'])) ?>" target="_blank" title="Clic para ver imagen de ROE / QR">
                        <img src="<?= e(rutaArchivo($emp['roe'])) ?>" alt="ROE QR" style="width:50px; height:50px; object-fit:contain; border:1px solid #cbd5e1; border-radius:4px; background:#fff; padding:2px; box-shadow:0 1px 3px rgba(0,0,0,0.08); transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.8)'" onmouseout="this.style.transform='scale(1)'">
                      </a>
                    <?php else: ?>
                      <div>ROE: <code><?= e($emp['roe'] ?? 'S/N') ?></code></div>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div style="margin-bottom:4px;">
                    <?php if (!empty($emp['verificada'])): ?>
                      <span class="etiqueta-verificada">Verificada</span>
                    <?php else: ?>
                      <span class="badge-menor" style="background:#fee2e2; color:#991b1b;">Pendiente</span>
                    <?php endif; ?>
                  </div>
                  <?php if (estaBaneado($emp)): ?>
                    <div style="margin-top:4px;">
                      <?php if (empty($emp['baneado_hasta'])): ?>
                        <span class="badge-estado badge-rechazado" style="font-size:10px;">Suspensión permanente</span>
                      <?php else: ?>
                        <span class="badge-estado" style="background:#fed7aa; color:#9a3412; font-size:10px;" title="Hasta: <?= e($emp['baneado_hasta']) ?>">
                          Baneo hasta <?= date('d/m/Y', strtotime($emp['baneado_hasta'])) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($emp['motivo_baneo'])): ?>
                        <div style="font-size:10px; color:#991b1b; margin-top:2px; max-width:140px; word-break:break-word;">
                          <?= e($emp['motivo_baneo']) ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="badge-estado badge-contratado" style="font-size:10px;">Activa</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <form action="ban.php?pagina=toggle_verificar_empresa" method="post" style="display:inline;">
                      <input type="hidden" name="empresa_id" value="<?= (int) $emp['id'] ?>">
                      <button type="submit" class="boton-sm <?= !empty($emp['verificada']) ? 'boton-outline-oscuro' : 'boton-exito' ?>">
                        <?= !empty($emp['verificada']) ? 'Quitar verificación' : 'Verificar NIT' ?>
                      </button>
                    </form>

                    <?php if ($estaBloqEmp): ?>
                      <form action="ban.php?pagina=desbloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Desbloquear el correo <?= e(addslashes($emp['correo'])) ?> y restablecer intentos a 0?');">
                        <input type="hidden" name="correo" value="<?= e($emp['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#059669; color:white;">Desbloquear Correo</button>
                      </form>
                    <?php else: ?>
                      <form action="ban.php?pagina=bloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Bloquear el acceso para <?= e(addslashes($emp['correo'])) ?>?');">
                        <input type="hidden" name="correo" value="<?= e($emp['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#475569; color:white;">Bloquear Correo</button>
                      </form>
                    <?php endif; ?>

                    <?php if (estaBaneado($emp)): ?>
                      <form action="ban.php?pagina=desbanear_cuenta" method="post" style="display:inline;" onsubmit="return confirm('¿Deseas reactivar y revocar la suspensión de esta empresa?');">
                        <input type="hidden" name="id" value="<?= (int) $emp['id'] ?>">
                        <input type="hidden" name="tabla" value="empresas">
                        <button type="submit" class="boton-sm" style="background:#10b981; color:white;">Reactivar</button>
                      </form>
                    <?php else: ?>
                      <button type="button" class="boton-sm" style="background:#f97316; color:white;" onclick="abrirModalBaneo(<?= (int)$emp['id'] ?>, 'empresas', '<?= e(addslashes($emp['nombre'])) ?>')">
                        Suspender
                      </button>
                    <?php endif; ?>

                    <form action="ban.php?pagina=eliminar_empresa" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar esta empresa y todas sus vacantes?');">
                      <input type="hidden" name="empresa_id" value="<?= (int) $emp['id'] ?>">
                      <button type="submit" class="boton-sm boton-peligro">Eliminar</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLA EMPRENDIMIENTOS: Emprendimientos Registrados -->
  <div id="seccionEmprendimientos" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">EMPRENDIMIENTOS REGISTRADOS</span>
        <h2>Proyectos, Talleres y Emprendimientos Independientes</h2>
        <p class="texto-suave">Emprendimientos registrados en la plataforma para impulsar talento joven.</p>
      </div>
    </div>



    <div class="tabla-contenedor">
      <table class="tabla-panel">
        <thead>
          <tr>
            <th>ID</th>
            <th>Emprendimiento</th>
            <th>Rubro</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>CI / Foto del puesto</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($emprendimientos)): ?>
            <tr>
              <td colspan="8" style="text-align:center; padding:1.5rem; color:var(--gris);">No hay emprendimientos registrados actualmente.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($emprendimientos as $emp): ?>
              <tr>
                <td>#<?= (int) $emp['id'] ?></td>
                <td>
                  <strong><?= e($emp['nombre']) ?></strong>
                  <?php if (!empty($emp['representante'])): ?>
                    <div style="font-size:0.82rem; color:var(--texto-suave, #64748b); margin-top:2px;">
                      Resp: <?= e($emp['representante']) ?>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge-menor" style="background:#e0e7ff; color:#3730a3; font-size:11px;">
                    <?= e($emp['rubro']) ?>
                  </span>
                </td>
                <td>
                  <a href="mailto:<?= e($emp['correo']) ?>" class="enlace-contacto"><?= e($emp['correo']) ?></a>
                  <?php 
                    $cNormEmp = strtolower(trim($emp['correo']));
                    $bloqEmp = $mapaBloqueados[$cNormEmp] ?? null;
                    $estaBloqEmp = !empty($bloqEmp) && ((int)($bloqEmp['bloqueado'] ?? 0) === 1 || (int)($bloqEmp['intentos'] ?? 0) >= 3);
                  ?>
                  <?php if ($estaBloqEmp): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:10px; font-weight:bold; display:inline-block;">Bloqueado (<?= (int)$bloqEmp['intentos'] ?> fallos)</span>
                    </div>
                  <?php elseif (!empty($bloqEmp['intentos']) && (int)$bloqEmp['intentos'] > 0): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; display:inline-block;"><?= (int)$bloqEmp['intentos'] ?> / 3 fallos</span>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= !empty($emp['telefono']) ? e($emp['telefono']) : '<span class="texto-suave">-</span>' ?></td>
                <td>
                  <div>CI: <code><?= e($emp['ci'] ?? $emp['nit_ci'] ?? 'S/N') ?></code></div>
                  <div style="margin-top:6px;">
                    <?php if (!empty($emp['foto_puesto']) && preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', $emp['foto_puesto'])): ?>
                      <div style="font-size:0.75rem; font-weight:600; color:var(--texto-suave); margin-bottom:3px;">Foto del puesto:</div>
                      <a href="<?= e(rutaArchivo($emp['foto_puesto'])) ?>" target="_blank" title="Ver foto del puesto">
                        <img src="<?= e(rutaArchivo($emp['foto_puesto'])) ?>" alt="Foto del puesto" style="width:50px; height:50px; object-fit:contain; border:1px solid #cbd5e1; border-radius:4px; background:#fff; padding:2px; box-shadow:0 1px 3px rgba(0,0,0,0.08); transition:transform 0.15s ease;" onmouseover="this.style.transform='scale(1.8)'" onmouseout="this.style.transform='scale(1)'">
                      </a>
                    <?php else: ?>
                      <div>Foto: <code><?= e($emp['foto_puesto'] ?? 'S/N') ?></code></div>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div style="margin-bottom:4px;">
                    <?php if (!empty($emp['verificado'])): ?>
                      <span class="etiqueta-verificada">Activo</span>
                    <?php else: ?>
                      <span class="badge-menor" style="background:#fee2e2; color:#991b1b;">Pendiente</span>
                    <?php endif; ?>
                  </div>
                  <?php if (estaBaneado($emp)): ?>
                    <div style="margin-top:4px;">
                      <?php if (empty($emp['baneado_hasta'])): ?>
                        <span class="badge-estado badge-rechazado" style="font-size:10px;">Suspensión permanente</span>
                      <?php else: ?>
                        <span class="badge-estado" style="background:#fed7aa; color:#9a3412; font-size:10px;" title="Hasta: <?= e($emp['baneado_hasta']) ?>">
                          Baneo hasta <?= date('d/m/Y', strtotime($emp['baneado_hasta'])) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($emp['motivo_baneo'])): ?>
                        <div style="font-size:10px; color:#991b1b; margin-top:2px; max-width:140px; word-break:break-word;">
                          <?= e($emp['motivo_baneo']) ?>
                        </div>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="badge-estado badge-contratado" style="font-size:10px;">Habilitado</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <form action="ban.php?pagina=toggle_verificar_emprendimiento" method="post" style="display:inline;">
                      <input type="hidden" name="emprendimiento_id" value="<?= (int) $emp['id'] ?>">
                      <button type="submit" class="boton-sm <?= !empty($emp['verificado']) ? 'boton-outline-oscuro' : 'boton-exito' ?>">
                        <?= !empty($emp['verificado']) ? 'Desactivar' : 'Verificar' ?>
                      </button>
                    </form>

                    <?php if ($estaBloqEmp): ?>
                      <form action="ban.php?pagina=desbloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Desbloquear el correo <?= e(addslashes($emp['correo'])) ?> y restablecer intentos a 0?');">
                        <input type="hidden" name="correo" value="<?= e($emp['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#059669; color:white;">Desbloquear Correo</button>
                      </form>
                    <?php else: ?>
                      <form action="ban.php?pagina=bloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Bloquear el acceso para <?= e(addslashes($emp['correo'])) ?>?');">
                        <input type="hidden" name="correo" value="<?= e($emp['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#475569; color:white;">Bloquear Correo</button>
                      </form>
                    <?php endif; ?>

                    <?php if (estaBaneado($emp)): ?>
                      <form action="ban.php?pagina=desbanear_cuenta" method="post" style="display:inline;" onsubmit="return confirm('¿Deseas reactivar y revocar la suspensión de este emprendimiento?');">
                        <input type="hidden" name="id" value="<?= (int) $emp['id'] ?>">
                        <input type="hidden" name="tabla" value="emprendimientos">
                        <button type="submit" class="boton-sm" style="background:#10b981; color:white;">Reactivar</button>
                      </form>
                    <?php else: ?>
                      <button type="button" class="boton-sm" style="background:#f97316; color:white;" onclick="abrirModalBaneo(<?= (int)$emp['id'] ?>, 'emprendimientos', '<?= e(addslashes($emp['nombre'])) ?>')">
                        Suspender
                      </button>
                    <?php endif; ?>

                    <form action="ban.php?pagina=eliminar_emprendimiento" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este emprendimiento y todas sus vacantes?');">
                      <input type="hidden" name="emprendimiento_id" value="<?= (int) $emp['id'] ?>">
                      <button type="submit" class="boton-sm boton-peligro">Eliminar</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLA 2: Jóvenes Menores de 18 años -->
  <div id="seccionMenores" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">JÓVENES MENORES DE EDAD</span>
        <h2>Jóvenes Menores de 18 años (16 y 17 años con Tutor Legal)</h2>
        <p class="texto-suave">Postulantes menores de edad que requieren consentimiento y datos de su tutor legal.</p>
      </div>
    </div>



    <div class="tabla-contenedor">
      <table class="tabla-panel">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre del Joven</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Nacimiento / Edad</th>
            <th>Tutor Legal (Padre/Madre)</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($usuariosMen)): ?>
            <tr>
              <td colspan="8" style="text-align:center; padding:1.5rem; color:var(--gris);">No hay jóvenes menores de 18 años registrados actualmente.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($usuariosMen as $umen): ?>
              <tr>
                <td>#<?= (int) $umen['id'] ?></td>
                <td>
                  <strong><?= e($umen['nombre']) ?></strong>
                  <span class="badge-menor" style="margin-left:6px; font-size:11px;">Menor de edad</span>
                  <?php if (!empty($umen['cv_path'])): ?>
                    <div style="margin-top:4px;">
                      <a href="<?= e(rutaArchivo($umen['cv_path'])) ?>" target="_blank" class="enlace-contacto" style="font-size:11px; color:#2563eb; display:inline-flex; align-items:center; gap:3px;">
                        Ver CV (PDF)
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <a href="mailto:<?= e($umen['correo']) ?>" class="enlace-contacto"><?= e($umen['correo']) ?></a>
                  <?php 
                    $cNormMen = strtolower(trim($umen['correo']));
                    $bloqMen = $mapaBloqueados[$cNormMen] ?? null;
                    $estaBloqMen = !empty($bloqMen) && ((int)($bloqMen['bloqueado'] ?? 0) === 1 || (int)($bloqMen['intentos'] ?? 0) >= 3);
                  ?>
                  <?php if ($estaBloqMen): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:10px; font-weight:bold; display:inline-block;">Bloqueado (<?= (int)$bloqMen['intentos'] ?> fallos)</span>
                    </div>
                  <?php elseif (!empty($bloqMen['intentos']) && (int)$bloqMen['intentos'] > 0): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; display:inline-block;"><?= (int)$bloqMen['intentos'] ?> / 3 fallos</span>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= !empty($umen['telefono']) ? e($umen['telefono']) : '<span class="texto-suave">-</span>' ?></td>
                <td>
                  <?= e($umen['fecha_nacimiento']) ?>
                  <?php if (!empty($umen['fecha_nacimiento'])): ?>
                    <div style="font-size:11px; color:var(--gris);"><?= calcularEdad($umen['fecha_nacimiento']) ?> años</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div><strong><?= e($umen['tutor_nombre'] ?? 'N/D') ?></strong> (<?= e($umen['tutor_relacion'] ?? 'Tutor') ?>)</div>
                  <?php if (!empty($umen['tutor_ci'])): ?>
                    <div style="font-size:11px; color:#1e293b;"><strong>C.I.:</strong> <?= e($umen['tutor_ci']) ?></div>
                  <?php endif; ?>
                  <?php if (!empty($umen['tutor_correo'])): ?>
                    <div style="font-size:11px;"><a href="mailto:<?= e($umen['tutor_correo']) ?>" class="enlace-contacto"><?= e($umen['tutor_correo']) ?></a></div>
                  <?php endif; ?>
                  <div style="font-size:12px; color:var(--gris);">Tel: <?= e($umen['tutor_telefono'] ?? '-') ?></div>
                </td>
                <td>
                  <?php if (estaBaneado($umen)): ?>
                    <div>
                      <?php if (empty($umen['baneado_hasta'])): ?>
                        <span class="badge-estado badge-rechazado" style="font-size:10px;">Permanente</span>
                      <?php else: ?>
                        <span class="badge-estado" style="background:#fed7aa; color:#9a3412; font-size:10px;">
                          Hasta <?= date('d/m/Y', strtotime($umen['baneado_hasta'])) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($umen['motivo_baneo'])): ?>
                        <div style="font-size:10px; color:#991b1b; margin-top:2px; max-width:130px; word-break:break-word;"><?= e($umen['motivo_baneo']) ?></div>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="badge-estado badge-contratado" style="font-size:10px;">Activo</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <?php if ($estaBloqMen): ?>
                      <form action="ban.php?pagina=desbloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Desbloquear el correo <?= e(addslashes($umen['correo'])) ?> y restablecer intentos a 0?');">
                        <input type="hidden" name="correo" value="<?= e($umen['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#059669; color:white;">Desbloquear Correo</button>
                      </form>
                    <?php else: ?>
                      <form action="ban.php?pagina=bloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Bloquear el acceso para <?= e(addslashes($umen['correo'])) ?>?');">
                        <input type="hidden" name="correo" value="<?= e($umen['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#475569; color:white;">Bloquear Correo</button>
                      </form>
                    <?php endif; ?>

                    <?php if (estaBaneado($umen)): ?>
                      <form action="ban.php?pagina=desbanear_cuenta" method="post" style="display:inline;" onsubmit="return confirm('¿Deseas reactivar este postulante?');">
                        <input type="hidden" name="id" value="<?= (int) $umen['id'] ?>">
                        <input type="hidden" name="tabla" value="usuarios_men">
                        <button type="submit" class="boton-sm" style="background:#10b981; color:white;">Reactivar</button>
                      </form>
                    <?php else: ?>
                      <button type="button" class="boton-sm" style="background:#f97316; color:white;" onclick="abrirModalBaneo(<?= (int)$umen['id'] ?>, 'usuarios_men', '<?= e(addslashes($umen['nombre'])) ?>')">
                        Suspender
                      </button>
                    <?php endif; ?>

                    <form action="ban.php?pagina=eliminar_usuario" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este postulante menor de edad?');">
                      <input type="hidden" name="usuario_id" value="<?= (int) $umen['id'] ?>">
                      <input type="hidden" name="tabla" value="usuarios_men">
                      <button type="submit" class="boton-sm boton-peligro">Eliminar</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLA 3: Tutores Legales y Jóvenes a su Cargo -->
  <div id="seccionTutores" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">TUTORES LEGALES</span>
        <h2>Tutores Legales y Jóvenes Menores a su Cargo</h2>
        <p class="texto-suave">Padres, madres o tutores legales responsables de los postulantes menores de edad (16 y 17 años).</p>
      </div>
    </div>



    <div class="tabla-contenedor">
      <table class="tabla-panel">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tutor Legal</th>
            <th>C.I. Tutor</th>
            <th>Parentesco</th>
            <th>Contacto Tutor (Tel / Correo)</th>
            <th>Joven a su Cargo</th>
            <th>Contacto del Joven</th>
            <th>Edad del Joven</th>
            <th>Fecha Registro</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tutores)): ?>
            <tr>
              <td colspan="9" style="text-align:center; padding:1.5rem; color:var(--gris);">No hay tutores registrados actualmente.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($tutores as $tut): ?>
              <tr>
                <td>#<?= (int) $tut['id'] ?></td>
                <td>
                  <strong><?= e($tut['nombre']) ?></strong>
                  <div style="font-size:11px; color:#1d4ed8; margin-top:2px;">Tutor legal activo</div>
                </td>
                <td>
                  <?= !empty($tut['ci']) ? '<strong>' . e($tut['ci']) . '</strong>' : '<span class="texto-suave">-</span>' ?>
                </td>
                <td>
                  <span class="badge-menor" style="background:#f1f5f9; color:#334155; font-size:11px;">
                    <?= e($tut['relacion'] ?? 'Tutor legal') ?>
                  </span>
                </td>
                <td>
                  <div>
                    <a href="tel:<?= e($tut['telefono']) ?>" class="enlace-contacto">
                      <?= e($tut['telefono']) ?>
                    </a>
                  </div>
                  <?php if (!empty($tut['correo'])): ?>
                    <div style="font-size:11px; margin-top:3px;">
                      <a href="mailto:<?= e($tut['correo']) ?>" class="enlace-contacto">
                        <?= e($tut['correo']) ?>
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="font-weight:600; color:#0f172a;">
                    <?= e($tut['menor_nombre']) ?>
                  </div>
                  <div style="font-size:11px; color:var(--gris);">
                    ID Joven: <code>#<?= (int) $tut['usuario_men_id'] ?></code>
                  </div>
                </td>
                <td>
                  <div><a href="mailto:<?= e($tut['menor_correo']) ?>" class="enlace-contacto"><?= e($tut['menor_correo']) ?></a></div>
                  <?php if (!empty($tut['menor_telefono'])): ?>
                    <div style="font-size:12px; color:var(--gris);">Tel: <?= e($tut['menor_telefono']) ?></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($tut['fecha_nacimiento'])): ?>
                    <strong><?= calcularEdad($tut['fecha_nacimiento']) ?> años</strong>
                    <div style="font-size:11px; color:var(--gris);"><?= e($tut['fecha_nacimiento']) ?></div>
                  <?php else: ?>
                    <span class="texto-suave">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span style="font-size:12px; color:var(--gris);">
                    <?= !empty($tut['creado_en']) ? date('d/m/Y H:i', strtotime($tut['creado_en'])) : '-' ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLA 4: Jóvenes Mayores de 18 años y Administradores -->
  <div id="seccionMayores" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">JÓVENES MAYORES Y ADMINISTRADORES</span>
        <h2>Jóvenes Mayores de 18 años y Administradores</h2>
        <p class="texto-suave">Cuentas individuales de postulantes mayores de edad y personal administrativo.</p>
      </div>
    </div>



    <div class="tabla-contenedor">
      <table class="tabla-panel">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre Completo</th>
            <th>Tipo</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Nacimiento / Edad</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($usuariosMay)): ?>
            <tr>
              <td colspan="8" style="text-align:center; padding:1.5rem; color:var(--gris);">No hay usuarios mayores registrados actualmente.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($usuariosMay as $umay): ?>
              <tr>
                <td>#<?= (int) $umay['id'] ?></td>
                <td>
                  <strong><?= e($umay['nombre']) ?></strong>
                  <?php if (!empty($umay['cv_path'])): ?>
                    <div style="margin-top:4px;">
                      <a href="<?= e(rutaArchivo($umay['cv_path'])) ?>" target="_blank" class="enlace-contacto" style="font-size:11px; color:#2563eb; display:inline-flex; align-items:center; gap:3px;">
                        Ver CV (PDF)
                      </a>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="tipo-badge <?= e($umay['tipo']) ?>">
                    <?= ucfirst(e($umay['tipo'])) ?>
                  </span>
                </td>
                <td>
                  <a href="mailto:<?= e($umay['correo']) ?>" class="enlace-contacto"><?= e($umay['correo']) ?></a>
                  <?php 
                    $cNormMay = strtolower(trim($umay['correo']));
                    $bloqMay = $mapaBloqueados[$cNormMay] ?? null;
                    $estaBloqMay = !empty($bloqMay) && ((int)($bloqMay['bloqueado'] ?? 0) === 1 || (int)($bloqMay['intentos'] ?? 0) >= 3);
                  ?>
                  <?php if ($estaBloqMay): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fee2e2; color:#991b1b; font-size:10px; font-weight:bold; display:inline-block;">Bloqueado (<?= (int)$bloqMay['intentos'] ?> fallos)</span>
                    </div>
                  <?php elseif (!empty($bloqMay['intentos']) && (int)$bloqMay['intentos'] > 0): ?>
                    <div style="margin-top:4px;">
                      <span class="badge" style="background:#fef3c7; color:#92400e; font-size:10px; display:inline-block;"><?= (int)$bloqMay['intentos'] ?> / 3 fallos</span>
                    </div>
                  <?php endif; ?>
                </td>
                <td><?= !empty($umay['telefono']) ? e($umay['telefono']) : '<span class="texto-suave">-</span>' ?></td>
                <td>
                  <?php if (!empty($umay['fecha_nacimiento'])): ?>
                    <?= e($umay['fecha_nacimiento']) ?>
                    <div style="font-size:11px; color:var(--gris);"><?= calcularEdad($umay['fecha_nacimiento']) ?> años</div>
                  <?php else: ?>
                    <span class="texto-suave">-</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ((int)$umay['id'] === (int)($_SESSION['usuario_id'] ?? 0)): ?>
                    <span class="badge-estado badge-contratado" style="font-size:10px;">Activo (Admin)</span>
                  <?php elseif (estaBaneado($umay)): ?>
                    <div>
                      <?php if (empty($umay['baneado_hasta'])): ?>
                        <span class="badge-estado badge-rechazado" style="font-size:10px;">Permanente</span>
                      <?php else: ?>
                        <span class="badge-estado" style="background:#fed7aa; color:#9a3412; font-size:10px;">
                          Hasta <?= date('d/m/Y', strtotime($umay['baneado_hasta'])) ?>
                        </span>
                      <?php endif; ?>
                      <?php if (!empty($umay['motivo_baneo'])): ?>
                        <div style="font-size:10px; color:#991b1b; margin-top:2px; max-width:130px; word-break:break-word;"><?= e($umay['motivo_baneo']) ?></div>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="badge-estado badge-contratado" style="font-size:10px;">Activo</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ((int)$umay['id'] !== (int)($_SESSION['usuario_id'] ?? 0)): ?>
                    <div style="display:flex; gap:6px; flex-wrap:wrap;">
                      <?php if ($estaBloqMay): ?>
                        <form action="ban.php?pagina=desbloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Desbloquear el correo <?= e(addslashes($umay['correo'])) ?> y restablecer intentos a 0?');">
                          <input type="hidden" name="correo" value="<?= e($umay['correo']) ?>">
                          <button type="submit" class="boton-sm" style="background:#059669; color:white;">Desbloquear Correo</button>
                        </form>
                      <?php else: ?>
                        <form action="ban.php?pagina=bloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Bloquear el acceso para <?= e(addslashes($umay['correo'])) ?>?');">
                          <input type="hidden" name="correo" value="<?= e($umay['correo']) ?>">
                          <button type="submit" class="boton-sm" style="background:#475569; color:white;">Bloquear Correo</button>
                        </form>
                      <?php endif; ?>

                      <?php if (estaBaneado($umay)): ?>
                        <form action="ban.php?pagina=desbanear_cuenta" method="post" style="display:inline;" onsubmit="return confirm('¿Deseas reactivar este usuario?');">
                          <input type="hidden" name="id" value="<?= (int) $umay['id'] ?>">
                          <input type="hidden" name="tabla" value="usuarios_may">
                          <button type="submit" class="boton-sm" style="background:#10b981; color:white;">Reactivar</button>
                        </form>
                      <?php else: ?>
                        <button type="button" class="boton-sm" style="background:#f97316; color:white;" onclick="abrirModalBaneo(<?= (int)$umay['id'] ?>, 'usuarios_may', '<?= e(addslashes($umay['nombre'])) ?>')">
                          Suspender
                        </button>
                      <?php endif; ?>

                      <form action="ban.php?pagina=eliminar_usuario" method="post" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?');">
                        <input type="hidden" name="usuario_id" value="<?= (int) $umay['id'] ?>">
                        <input type="hidden" name="tabla" value="usuarios_may">
                        <button type="submit" class="boton-sm boton-peligro">Eliminar</button>
                      </form>
                    </div>
                  <?php else: ?>
                    <span class="texto-suave" style="font-size:12px;">Tu sesión actual</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLA 5: Personal Administrativo (usuarios_admin) -->
  <div id="seccionAdmins" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">EQUIPO Y ACCESO PRIVILEGIADO</span>
        <h2>Administradores del Sistema</h2>
      </div>
      <span class="total-badge"><?= count($administradores ?? []) ?> registrados</span>
    </div>

    <div class="tabla-contenedor">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Correo Electrónico</th>
            <th>Teléfono</th>
            <th>Cargo</th>
            <th>Estado</th>
            <th>Fecha Registro</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($administradores)): ?>
            <tr><td colspan="7" class="tabla-vacia">No hay administradores adicionales registrados.</td></tr>
          <?php else: ?>
            <?php foreach ($administradores as $adm): ?>
              <tr>
                <td>#<?= (int) $adm['id'] ?></td>
                <td><strong><?= e($adm['nombre']) ?></strong></td>
                <td><?= e($adm['correo']) ?></td>
                <td><?= e($adm['telefono'] ?: 'No registrado') ?></td>
                <td><span class="tipo-badge admin"><?= e($adm['cargo'] ?? 'Administrador') ?></span></td>
                <td>
                  <?php if (!empty($adm['activo'])): ?>
                    <span class="badge badge-verificada">Activo</span>
                  <?php else: ?>
                    <span class="badge badge-suspendida">Inactivo</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:12px; color:#64748b;"><?= date('d/m/Y H:i', strtotime($adm['fecha_registro'])) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TABLA 6: Control de Seguridad y Bloqueo de Correos / Gmail -->
  <div id="seccionBloqueos" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">SEGURIDAD Y PROTECCION DE ACCESO</span>
        <h2>Control de Intentos y Bloqueo de Correos / Gmail</h2>
        <p class="texto-suave">Protección contra accesos no autorizados. Al acumular 3 intentos fallidos consecutivos, el correo queda bloqueado de forma automática. El administrador puede bloquear o desbloquear cualquier correo directamente.</p>
      </div>
      <span class="total-badge"><?= (int) ($totalCorreosBloqueados ?? 0) ?> bloqueados</span>
    </div>

    <!-- Formulario de Acción Rápida por Correo -->
    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:18px 20px; margin-bottom:24px;">
      <h3 style="font-size:15px; margin-top:0; margin-bottom:6px; color:#0f172a;">Acción Rápida por Dirección de Correo</h3>
      <p style="font-size:13px; color:#64748b; margin-bottom:14px;">Ingresa cualquier dirección de correo (Gmail u otro) para desbloquear su acceso o bloquearlo inmediatamente de forma preventiva.</p>
      <form action="ban.php?pagina=desbloquear_correo" method="post" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
        <input type="email" name="correo" placeholder="ejemplo@gmail.com" required style="flex:1; min-width:240px; height:40px; padding:6px 14px; border:1px solid #cbd5e1; border-radius:6px; font-size:14px;">
        <button type="submit" class="boton-sm" style="height:40px; padding:0 18px; background:#059669; color:white; font-weight:600; border-radius:6px;">
          Desbloquear Correo
        </button>
        <button type="submit" formaction="ban.php?pagina=bloquear_correo" class="boton-sm" style="height:40px; padding:0 18px; background:#dc2626; color:white; font-weight:600; border-radius:6px;" onclick="return confirm('¿Confirmas que deseas bloquear el acceso a este correo?');">
          Bloquear Preventivamente
        </button>
      </form>
    </div>

    <!-- Tabla de Correos con Intentos o Bloqueados -->
    <div class="tabla-contenedor">
      <table class="tabla-panel">
        <thead>
          <tr>
            <th>ID</th>
            <th>Correo Electrónico / Gmail</th>
            <th>Intentos Fallidos</th>
            <th>Estado de Acceso</th>
            <th>Fecha de Bloqueo</th>
            <th>Último Intento</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($bloqueosCorreo)): ?>
            <tr>
              <td colspan="7" style="text-align:center; padding:1.5rem; color:var(--gris);">
                No hay registros de intentos fallidos ni correos bloqueados en este momento.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($bloqueosCorreo as $b): ?>
              <?php $estaBloq = ((int)($b['bloqueado'] ?? 0) === 1 || (int)($b['intentos'] ?? 0) >= 3); ?>
              <tr>
                <td>#<?= (int) $b['id'] ?></td>
                <td>
                  <strong><?= e($b['correo']) ?></strong>
                </td>
                <td>
                  <?php if ($estaBloq): ?>
                    <span style="color:#991b1b; font-weight:700;">
                      <?= (int) $b['intentos'] ?> / 3 (Limite superado)
                    </span>
                  <?php else: ?>
                    <span style="color:#d97706; font-weight:600;">
                      <?= (int) $b['intentos'] ?> / 3 intentos
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($estaBloq): ?>
                    <span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:700; font-size:11px; padding:3px 8px; border-radius:4px;">
                      BLOQUEADO
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background:#fef3c7; color:#92400e; font-size:11px; padding:3px 8px; border-radius:4px;">
                      En seguimiento
                    </span>
                  <?php endif; ?>
                </td>
                <td style="font-size:12px; color:#64748b;">
                  <?= !empty($b['bloqueado_en']) ? date('d/m/Y H:i:s', strtotime($b['bloqueado_en'])) : '<span class="texto-suave">-</span>' ?>
                </td>
                <td style="font-size:12px; color:#64748b;">
                  <?= !empty($b['ultimo_intento']) ? date('d/m/Y H:i:s', strtotime($b['ultimo_intento'])) : '<span class="texto-suave">-</span>' ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <?php if ($estaBloq || (int)($b['intentos'] ?? 0) > 0): ?>
                      <form action="ban.php?pagina=desbloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Desbloquear el correo <?= e(addslashes($b['correo'])) ?> y restablecer sus intentos a 0?');">
                        <input type="hidden" name="correo" value="<?= e($b['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#059669; color:white;">
                          Desbloquear
                        </button>
                      </form>
                    <?php endif; ?>
                    <?php if (!$estaBloq): ?>
                      <form action="ban.php?pagina=bloquear_correo" method="post" style="display:inline;" onsubmit="return confirm('¿Bloquear el correo <?= e(addslashes($b['correo'])) ?> preventivamente?');">
                        <input type="hidden" name="correo" value="<?= e($b['correo']) ?>">
                        <button type="submit" class="boton-sm" style="background:#dc2626; color:white;">
                          Bloquear
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- SECCIÓN INTEGRAL: REGISTROS Y AUDITORÍA DEL SISTEMA -->
  <div id="seccionRegistros" class="bloque-panel" style="margin-top:3.5rem; margin-bottom:3rem; border:2px solid #2563eb; border-radius:14px; padding:28px; background:#ffffff; box-shadow:0 12px 24px -4px rgba(37,99,235,0.08);">
    
    <!-- Encabezado de la Sección de Registros -->
    <div class="titulo-seccion" style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:1.2rem; border-bottom:2px solid #f1f5f9; padding-bottom:1.2rem; margin-bottom:1.8rem;">
      <div>
        <span class="etiqueta" style="background:#dbeafe; color:#1e40af; font-weight:800; letter-spacing:0.05em;">AUDITORÍA Y SUPERVISIÓN CENTRAL</span>
        <h2 style="font-size:1.85rem; color:#0f172a; margin-top:6px; margin-bottom:6px; display:flex; align-items:center; gap:10px;">
          Centro de Registros del Sistema
        </h2>
        <p class="texto-suave" style="font-size:14px; max-width:850px; margin:0; line-height:1.5;">
          Módulo administrativo para el seguimiento exhaustivo de <strong>reportes ejecutivos</strong>, cuentas con <strong>baneos activos</strong>, historial de <strong>desbaneos</strong>, bitácora de <strong>nuevos usuarios</strong>, control de <strong>bloqueos de correo tras 3 contraseñas fallidas</strong> y la auditoría completa de <strong>acciones del administrador</strong>.
        </p>
      </div>

      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <a href="ban.php?pagina=panel_reportes" class="boton boton-sm boton-primario" style="display:inline-flex; align-items:center; gap:6px;">
          <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
          Abrir en Panel Dedicado
        </a>
        <span style="display:inline-flex; align-items:center; gap:6px; font-size:13px; background:#eff6ff; color:#1e40af; font-weight:700; padding:8px 14px; border-radius:9999px; border:1px solid #bfdbfe;">
          <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
          <?= (int) ($totalRegistrosEventos ?? 0) ?> Eventos Auditados
        </span>
      </div>
    </div>



    <!-- Generador Oficial de Reportes en PDF por Día -->
    <div style="margin-bottom:1.8rem; background:#f8fafc; border:1px solid #cbd5e1; border-left:4px solid #1e40af; border-radius:8px; padding:16px 20px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
      <div>
        <strong style="font-size:14px; color:#0f172a; display:block;">Generar Reporte Diario de Registros en PDF</strong>
        <span class="texto-suave" style="font-size:12.5px;">Exporta un documento formal de auditoría con todos los cambios y registros ocurridos en la fecha seleccionada.</span>
      </div>
      <form action="ban.php" method="get" target="_blank" style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <input type="hidden" name="pagina" value="pdf_reporte_diario">
        <label style="font-size:11.5px; font-weight:700; color:#475569;">Fecha:</label>
        <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required style="height:34px; padding:4px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:12.5px;">
        <button type="submit" class="boton-sm boton-primario" style="height:34px; padding:0 14px; font-weight:700;">
          Ver PDF del Día
        </button>
        <button type="submit" name="tipo" value="admin" class="boton-sm" style="height:34px; padding:0 12px; background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-weight:700;">
          PDF Acciones Admin
        </button>
        <button type="submit" name="descargar" value="1" class="boton-sm" style="height:34px; padding:0 12px; background:#f1f5f9; color:#1e293b; border:1px solid #cbd5e1; font-weight:600;">
          Descargar
        </button>
      </form>
    </div>

    <!-- Pestañas de Navegación del Centro de Registros -->
    <style>
      .btn-tab-reg {
        padding: 10px 18px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13.5px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        white-space: nowrap;
      }
      .btn-tab-reg:hover {
        background: #f1f5f9;
        color: #1e293b;
        border-color: #cbd5e1;
      }
      .btn-tab-reg.activo {
        background: #1e40af;
        color: #ffffff;
        border-color: #1e40af;
        box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.2);
      }
      .badge-tab-contador {
        background: rgba(0,0,0,0.08);
        padding: 2px 7px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
      }
      .btn-tab-reg.activo .badge-tab-contador {
        background: rgba(255,255,255,0.25);
        color: #ffffff;
      }
      .tab-panel-reg {
        display: none;
        animation: fadeTab 0.25s ease-in-out;
      }
      .tab-panel-reg.activo {
        display: block;
      }
      @keyframes fadeTab {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
      }
      .tarjeta-kpi-reg {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
      }
      .tarjeta-kpi-reg:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px -2px rgba(0,0,0,0.06);
      }
      .badge-evento {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
      }
      .badge-evento-baneo { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
      .badge-evento-desbaneo { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
      .badge-evento-bloqueo { background: #ffedd5; color: #9a3412; border: 1px solid #fed7aa; }
      .badge-evento-desbloqueo { background: #e0f2fe; color: #075985; border: 1px solid #bae6fd; }
      .badge-evento-nuevo { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
      .badge-evento-verificacion { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
      .badge-evento-accion { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
    </style>

    <div style="display:flex; gap:8px; border-bottom:2px solid #e2e8f0; padding-bottom:12px; margin-bottom:1.8rem; overflow-x:auto;">
      <button type="button" class="btn-tab-reg activo" onclick="cambiarTabRegistros('reg-reportes', this)">
        1. Reportes
      </button>
      <button type="button" class="btn-tab-reg" onclick="cambiarTabRegistros('reg-baneos', this)">
        2. Baneos <span class="badge-tab-contador"><?= count($cuentasBaneadas ?? []) ?></span>
      </button>
      <button type="button" class="btn-tab-reg" onclick="cambiarTabRegistros('reg-desbaneos', this)">
        3. Desbaneos <span class="badge-tab-contador"><?= count($registrosDesbaneos ?? []) ?></span>
      </button>
      <button type="button" class="btn-tab-reg" onclick="cambiarTabRegistros('reg-nuevos', this)">
        4. Registro de Nuevos Usuarios <span class="badge-tab-contador"><?= count($nuevosUsuarios ?? []) ?></span>
      </button>
      <button type="button" class="btn-tab-reg" onclick="cambiarTabRegistros('reg-bloqueos3', this)">
        5. Bloqueo 3 Contraseñas <span class="badge-tab-contador"><?= count($correosBloqueadosFallos ?? []) ?></span>
      </button>
      <button type="button" class="btn-tab-reg" onclick="cambiarTabRegistros('reg-adminlogs', this)">
        6. Registros del Administrador <span class="badge-tab-contador"><?= count($registrosAdmin ?? []) ?></span>
      </button>
      <button type="button" class="btn-tab-reg" onclick="cambiarTabRegistros('reg-soporte', this)">
        7. Atención al Cliente <span class="badge-tab-contador"><?= count($mensajesSoporte ?? []) ?></span>
      </button>
    </div>

    <!-- ========================================== -->
    <!-- SUB-APARTADO 1: REPORTES                   -->
    <!-- ========================================== -->
    <div id="reg-reportes" class="tab-panel-reg activo">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.2rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
          <h3 style="margin:0; font-size:1.25rem; color:#0f172a;">Reportes Ejecutivos y Métricas Globales</h3>
          <p class="texto-suave" style="font-size:13px; margin:2px 0 0 0;">Resumen del rendimiento, estados de seguridad y tasas de crecimiento de Vantia.</p>
        </div>
        <span style="font-size:12px; color:#64748b;">Generado automáticamente en tiempo real</span>
      </div>

      <!-- Tarjetas de Métricas Ejecutivas -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:1rem; margin-bottom:2rem;">
        <div class="tarjeta-kpi-reg" style="border-left:4px solid #2563eb;">
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Usuarios Totales</span>
          <div style="font-size:1.85rem; font-weight:800; color:#0f172a; margin:4px 0;"><?= (int) ($totalUsuariosGlobal ?? 0) ?></div>
          <div style="font-size:12px; color:#2563eb; font-weight:600;">Candidatos, Empresas y Emprendimientos</div>
        </div>

        <div class="tarjeta-kpi-reg" style="border-left:4px solid #10b981;">
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Nuevos (Últimos 7 Días)</span>
          <div style="font-size:1.85rem; font-weight:800; color:#065f46; margin:4px 0;">+<?= (int) ($nuevosUltimos7Dias ?? 0) ?></div>
          <div style="font-size:12px; color:#059669; font-weight:600;">Altas registradas en la semana</div>
        </div>

        <div class="tarjeta-kpi-reg" style="border-left:4px solid #ef4444;">
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Cuentas Suspendidas (Baneadas)</span>
          <div style="font-size:1.85rem; font-weight:800; color:#991b1b; margin:4px 0;"><?= count($cuentasBaneadas ?? []) ?></div>
          <div style="font-size:12px; color:#dc2626; font-weight:600;">Sanciones activas en la plataforma</div>
        </div>

        <div class="tarjeta-kpi-reg" style="border-left:4px solid #f97316;">
          <span style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Correos Bloqueados (3 Fallos)</span>
          <div style="font-size:1.85rem; font-weight:800; color:#c2410c; margin:4px 0;"><?= count($correosBloqueadosFallos ?? []) ?></div>
          <div style="font-size:12px; color:#ea580c; font-weight:600;">Límite de contraseña alcanzado</div>
        </div>
      </div>

      <!-- Cuadrícula de Desglose Operativo -->
      <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.25rem;">
        
        <!-- Desglose 1: Verificación de Entidades -->
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
          <h4 style="margin:0 0 12px 0; font-size:14px; color:#1e293b; display:flex; align-items:center; gap:6px;">
            Validación y Verificación Laboral
          </h4>
          <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e2e8f0; font-size:13px;">
            <span>Empresas Verificadas (con NIT / ROE):</span>
            <strong><?= (int) ($empresasVerificadas ?? 0) ?> de <?= (int) ($totalEmpresas ?? 0) ?></strong>
          </div>
          <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e2e8f0; font-size:13px;">
            <span>Emprendimientos Verificados:</span>
            <strong><?= (int) ($emprendimientosVerificados ?? 0) ?> de <?= (int) ($totalEmprendimientos ?? 0) ?></strong>
          </div>
          <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13px;">
            <span>Tutores Legales Vinculados a Menores:</span>
            <strong><?= (int) ($totalTutores ?? 0) ?> tutores</strong>
          </div>
        </div>

        <!-- Desglose 2: Oferta y Demanda de Empleo -->
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:18px;">
          <h4 style="margin:0 0 12px 0; font-size:14px; color:#1e293b; display:flex; align-items:center; gap:6px;">
            Movimiento Laboral y Postulaciones
          </h4>
          <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e2e8f0; font-size:13px;">
            <span>Vacantes Laborales Publicadas:</span>
            <strong><?= (int) ($totalEmpleos ?? 0) ?> ofertas</strong>
          </div>
          <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #e2e8f0; font-size:13px;">
            <span>Postulaciones Totales Realizadas:</span>
            <strong><?= (int) ($totalPostulaciones ?? 0) ?> aplicaciones</strong>
          </div>
          <div style="display:flex; justify-content:space-between; padding:8px 0; font-size:13px;">
            <span>Jóvenes Menores (16-17 años):</span>
            <strong><?= (int) ($totalMenores ?? 0) ?> candidatos</strong>
          </div>
        </div>

      </div>

      <!-- Alertas de Seguridad Activas -->
      <div style="margin-top:1.5rem; background:#fffbeb; border:1px solid #fef3c7; border-left:4px solid #f59e0b; border-radius:8px; padding:16px 20px;">
        <h4 style="margin:0 0 6px 0; color:#92400e; font-size:14px; display:flex; align-items:center; gap:6px;">
          Diagnóstico Activo de Seguridad
        </h4>
        <p style="margin:0; font-size:13px; color:#78350f; line-height:1.5;">
          <?php if (!empty($correosBloqueadosFallos)): ?>
            Se detectaron <strong><?= count($correosBloqueadosFallos) ?> cuenta(s) con bloqueo automático</strong> debido a que superaron los 3 intentos fallidos de contraseña. Puedes revisarlas y desbloquearlas en la pestaña <em>"5. Bloqueo 3 Contraseñas"</em>.
          <?php else: ?>
            El sistema se encuentra seguro. No existen bloqueos automáticos pendientes por contraseñas fallidas en este momento.
          <?php endif; ?>
        </p>
      </div>
    </div>



    <!-- ========================================== -->
    <!-- SUB-APARTADO 2: BANEOS                     -->
    <!-- ========================================== -->
    <div id="reg-baneos" class="tab-panel-reg">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
          <h3 style="margin:0; font-size:1.35rem; color:#991b1b; display:flex; align-items:center; gap:8px;">
            Cuentas con Suspensión Activa (Baneos)
          </h3>
          <p class="texto-suave" style="font-size:13.5px; margin:3px 0 0 0;">Listado separado por fechas de empresas, emprendimientos, postulantes mayores o menores con acceso suspendido.</p>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
          <input type="date" onchange="filtrarPorFecha(this.value, 'tablaBaneosActivos')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
          <button type="button" onclick="limpiarFiltroFecha('tablaBaneosActivos', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
          <span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:700; padding:6px 14px; font-size:12.5px;">
            <?= count($cuentasBaneadas ?? []) ?> Sanción(es) Activa(s)
          </span>
        </div>
      </div>

      <div class="tabla-contenedor" style="margin-bottom:2rem;">
        <table class="tabla-panel" id="tablaBaneosActivos">
          <thead>
            <tr>
              <th>Entidad / Rol</th>
              <th>Nombre / Razón Social</th>
              <th>Contacto</th>
              <th>Tipo de Suspensión</th>
              <th>Motivo de la Sanción</th>
              <th>Hora / Fecha</th>
              <th>Acción Inmediata</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($cuentasBaneadas)): ?>
              <tr>
                <td colspan="7" style="text-align:center; padding:2.5rem; color:#059669; background:#f0fdf4;">
                  <strong style="font-size:15px;">No hay cuentas suspendidas (baneadas) en la plataforma actualmente.</strong>
                  <div style="font-size:12.5px; color:#166534; margin-top:6px;">Todas las empresas, emprendimientos y candidatos operan en estado activo normal.</div>
                </td>
              </tr>
            <?php else: ?>
              <?php 
                $fechaGrupoBaneo = null;
                foreach ($cuentasBaneadas as $cb): 
                  $esTemporal = !empty($cb['baneado_hasta']);
                  $hastaFormat = $esTemporal ? date('d/m/Y H:i', strtotime($cb['baneado_hasta'])) : null;
                  $dia = !empty($cb['fecha']) ? date('Y-m-d', strtotime($cb['fecha'])) : 'sin_fecha';
                  if ($dia !== $fechaGrupoBaneo):
                    $fechaGrupoBaneo = $dia;
              ?>
                <tr class="fila-separador-fecha" data-fecha="<?= e($dia) ?>">
                  <td colspan="7" style="background:#f1f5f9; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1; padding:9px 16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span style="font-weight:800; font-size:13px; color:#1e40af;">
                        <?= formatoFechaGrupo($dia) ?>
                      </span>
                      <div style="display:flex; align-items:center; gap:8px;">
                        <?php if ($dia !== 'sin_fecha'): ?>
                          <a href="ban.php?pagina=pdf_reporte_diario&tipo=admin&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:3px 9px; font-weight:700; text-decoration:none; border-radius:4px;">
                            PDF Acciones del Día
                          </a>
                          <a href="ban.php?pagina=pdf_reporte_diario&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#ffffff; color:#334155; border:1px solid #cbd5e1; font-size:11px; padding:3px 9px; font-weight:600; text-decoration:none; border-radius:4px;">
                            PDF Completo
                          </a>
                        <?php endif; ?>
                        <span style="font-size:11px; font-weight:700; color:#475569; background:#ffffff; padding:2px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                          <?= $dia !== 'sin_fecha' ? date('d/m/Y', strtotime($dia)) : 'Sin fecha' ?>
                        </span>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
                <tr class="fila-dato-fecha" data-fecha="<?= e($dia) ?>">
                  <td>
                    <?php if ($cb['tipo_entidad'] === 'menor'): ?>
                      <span class="badge-menor" style="background:#e0e7ff; color:#3730a3; font-weight:700;">Menor (16-17)</span>
                    <?php elseif ($cb['tipo_entidad'] === 'mayor'): ?>
                      <span class="badge-menor" style="background:#f1f5f9; color:#1e293b; font-weight:700;">Mayor (18+)</span>
                    <?php elseif ($cb['tipo_entidad'] === 'empresa'): ?>
                      <span class="badge-menor" style="background:#dbeafe; color:#1e40af; font-weight:700;">Empresa</span>
                    <?php elseif ($cb['tipo_entidad'] === 'emprendimiento'): ?>
                      <span class="badge-menor" style="background:#fef3c7; color:#92400e; font-weight:700;">Emprendimiento</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?= e($cb['nombre']) ?></strong>
                    <div style="font-size:11px; color:#64748b;">ID: #<?= (int) $cb['id'] ?> · Tabla: <code><?= e($cb['tabla']) ?></code></div>
                  </td>
                  <td>
                    <div><a href="mailto:<?= e($cb['correo']) ?>" class="enlace-contacto"><?= e($cb['correo']) ?></a></div>
                    <?php if (!empty($cb['telefono'])): ?>
                      <div style="font-size:11.5px; color:#64748b;">Tel: <?= e($cb['telefono']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($esTemporal): ?>
                      <span class="badge" style="background:#fef3c7; color:#92400e; font-weight:700;">
                        Temporal hasta: <?= e($hastaFormat) ?>
                      </span>
                    <?php else: ?>
                      <span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:700;">
                        Permanente
                      </span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span style="font-size:13px; color:#475569;">
                      <?= !empty($cb['motivo_baneo']) ? e($cb['motivo_baneo']) : 'Incumplimiento de normas' ?>
                    </span>
                  </td>
                  <td style="font-size:12px; color:#64748b; white-space:nowrap;">
                    <?= !empty($cb['fecha']) ? date('H:i:s · d/m/Y', strtotime($cb['fecha'])) : '-' ?>
                  </td>
                  <td>
                    <form action="ban.php?pagina=desbanear_cuenta" method="post" onsubmit="return confirm('¿Deseas levantar inmediatamente la suspensión para <?= e(addslashes($cb['nombre'])) ?>?');">
                      <input type="hidden" name="id" value="<?= (int) $cb['id'] ?>">
                      <input type="hidden" name="tabla" value="<?= e($cb['tabla']) ?>">
                      <input type="hidden" name="origen" value="panel_admin">
                      <button type="submit" class="boton-sm" style="background:#10b981; color:white; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                        <svg style="width:13px; height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Levantar Baneo
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Historial de Auditoría de Baneos -->
      <div style="display:flex; justify-content:space-between; align-items:center; margin:2rem 0 1rem 0; flex-wrap:wrap; gap:0.5rem;">
        <h4 style="margin:0; font-size:15px; color:#0f172a; display:flex; align-items:center; gap:6px;">
          Bitácora de Suspensiones Aplicadas (Separadas por Fecha)
        </h4>
        <div style="display:flex; gap:8px; align-items:center;">
          <input type="date" onchange="filtrarPorFecha(this.value, 'tablaBaneosHistorial')" style="padding:5px 8px; border:1px solid #cbd5e1; border-radius:5px; font-size:12px;">
          <button type="button" onclick="limpiarFiltroFecha('tablaBaneosHistorial', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11px; padding:4px 8px;">Todas</button>
        </div>
      </div>

      <div class="tabla-contenedor">
        <table class="tabla-panel" id="tablaBaneosHistorial">
          <thead>
            <tr>
              <th>ID</th>
              <th>Administrador</th>
              <th>Título</th>
              <th>Descripción</th>
              <th>Usuario Afectado</th>
              <th>Hora y Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($registrosBaneos)): ?>
              <tr>
                <td colspan="6" style="text-align:center; padding:1.5rem; color:#64748b;">No hay registros históricos de suspensiones en la bitácora.</td>
              </tr>
            <?php else: ?>
              <?php 
                $fechaGrupoHistBaneo = null;
                foreach ($registrosBaneos as $rb): 
                  $dia = !empty($rb['fecha_registro']) ? date('Y-m-d', strtotime($rb['fecha_registro'])) : 'sin_fecha';
                  if ($dia !== $fechaGrupoHistBaneo):
                    $fechaGrupoHistBaneo = $dia;
              ?>
                <tr class="fila-separador-fecha" data-fecha="<?= e($dia) ?>">
                  <td colspan="6" style="background:#f1f5f9; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1; padding:8px 14px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span style="font-weight:800; font-size:12.5px; color:#1e40af;">
                        <?= formatoFechaGrupo($dia) ?>
                      </span>
                      <?php if ($dia !== 'sin_fecha'): ?>
                        <a href="ban.php?pagina=pdf_reporte_diario&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#ffffff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:2px 8px; font-weight:700; text-decoration:none; border-radius:4px;">
                          PDF del Día
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
                <tr class="fila-dato-fecha" data-fecha="<?= e($dia) ?>">
                  <td>#<?= (int) $rb['id'] ?></td>
                  <td><strong><?= e($rb['admin_nombre'] ?? 'Administrador') ?></strong></td>
                  <td><span class="badge-evento badge-evento-baneo"><?= e($rb['titulo']) ?></span></td>
                  <td style="font-size:13px;"><?= e($rb['descripcion']) ?></td>
                  <td><code><?= e($rb['usuario_afectado'] ?? '-') ?></code></td>
                  <td style="font-size:12px; color:#64748b; white-space:nowrap;"><?= date('H:i:s · d/m/Y', strtotime($rb['fecha_registro'])) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>



    <!-- ========================================== -->
    <!-- SUB-APARTADO 3: DESBANEOS                  -->
    <!-- ========================================== -->
    <div id="reg-desbaneos" class="tab-panel-reg">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
          <h3 style="margin:0; font-size:1.35rem; color:#065f46; display:flex; align-items:center; gap:8px;">
            Historial de Desbaneos y Cuentas Reactivadas
          </h3>
          <p class="texto-suave" style="font-size:13.5px; margin:3px 0 0 0;">Registro separado por fechas de todas las sanciones levantadas y cuentas restauradas.</p>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
          <input type="date" onchange="filtrarPorFecha(this.value, 'tablaDesbaneos')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
          <button type="button" onclick="limpiarFiltroFecha('tablaDesbaneos', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
          <span class="badge" style="background:#d1fae5; color:#065f46; font-weight:700; padding:6px 14px; font-size:12.5px;">
            <?= count($registrosDesbaneos ?? []) ?> Reactivaciones Registradas
          </span>
        </div>
      </div>

      <div class="tabla-contenedor">
        <table class="tabla-panel" id="tablaDesbaneos">
          <thead>
            <tr>
              <th>ID</th>
              <th>Administrador Responsable</th>
              <th>Evento</th>
              <th>Detalle del Levantamiento</th>
              <th>Cuenta / Beneficiario</th>
              <th>IP Origen</th>
              <th>Hora y Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($registrosDesbaneos)): ?>
              <tr>
                <td colspan="7" style="text-align:center; padding:2.5rem; color:#64748b;">
                  No se registran desbaneos en la bitácora del sistema aún.
                </td>
              </tr>
            <?php else: ?>
              <?php 
                $fechaGrupoDesbaneo = null;
                foreach ($registrosDesbaneos as $rd): 
                  $dia = !empty($rd['fecha_registro']) ? date('Y-m-d', strtotime($rd['fecha_registro'])) : 'sin_fecha';
                  if ($dia !== $fechaGrupoDesbaneo):
                    $fechaGrupoDesbaneo = $dia;
              ?>
                <tr class="fila-separador-fecha" data-fecha="<?= e($dia) ?>">
                  <td colspan="7" style="background:#f1f5f9; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1; padding:9px 16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span style="font-weight:800; font-size:13px; color:#1e40af;">
                        <?= formatoFechaGrupo($dia) ?>
                      </span>
                      <div style="display:flex; align-items:center; gap:8px;">
                        <?php if ($dia !== 'sin_fecha'): ?>
                          <a href="ban.php?pagina=pdf_reporte_diario&tipo=admin&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:3px 9px; font-weight:700; text-decoration:none; border-radius:4px;">
                            PDF Acciones del Día
                          </a>
                          <a href="ban.php?pagina=pdf_reporte_diario&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#ffffff; color:#334155; border:1px solid #cbd5e1; font-size:11px; padding:3px 9px; font-weight:600; text-decoration:none; border-radius:4px;">
                            PDF Completo
                          </a>
                        <?php endif; ?>
                        <span style="font-size:11px; font-weight:700; color:#475569; background:#ffffff; padding:2px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                          <?= $dia !== 'sin_fecha' ? date('d/m/Y', strtotime($dia)) : 'Sin fecha' ?>
                        </span>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
                <tr class="fila-dato-fecha" data-fecha="<?= e($dia) ?>">
                  <td>#<?= (int) $rd['id'] ?></td>
                  <td>
                    <strong><?= e($rd['admin_nombre'] ?? 'Administrador') ?></strong>
                    <?php if (!empty($rd['admin_id'])): ?>
                      <div style="font-size:11px; color:#64748b;">ID Admin: #<?= (int) $rd['admin_id'] ?></div>
                    <?php endif; ?>
                  </td>
                  <td>
                    <span class="badge-evento badge-evento-desbaneo"><?= e($rd['titulo']) ?></span>
                  </td>
                  <td style="font-size:13px; color:#1e293b;">
                    <?= e($rd['descripcion']) ?>
                  </td>
                  <td>
                    <code><?= e($rd['usuario_afectado'] ?? '-') ?></code>
                  </td>
                  <td style="font-size:12px; color:#64748b;">
                    <?= e($rd['ip_origen'] ?? '127.0.0.1') ?>
                  </td>
                  <td style="font-size:12px; color:#64748b; white-space:nowrap;">
                    <?= date('H:i:s · d/m/Y', strtotime($rd['fecha_registro'])) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>



    <!-- ========================================== -->
    <!-- SUB-APARTADO 4: REGISTRO DE NUEVOS USUARIOS -->
    <!-- ========================================== -->
    <div id="reg-nuevos" class="tab-panel-reg">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
          <h3 style="margin:0; font-size:1.35rem; color:#1e40af; display:flex; align-items:center; gap:8px;">
            Registro de Nuevos Usuarios en la Plataforma
          </h3>
          <p class="texto-suave" style="font-size:13.5px; margin:3px 0 0 0;">Bitácora cronológica unificada de altas separadas por fechas: jóvenes menores (16-17), mayores (18+), empresas y emprendimientos.</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
          <a href="ban.php?pagina=pdf_reporte_diario&tipo=admin&fecha=<?= date('Y-m-d') ?>" target="_blank" class="boton-sm" style="background:#1e40af; color:#ffffff; font-weight:700; border:1px solid #1e3a8a; padding:6px 12px; text-decoration:none; border-radius:6px; display:inline-flex; align-items:center; gap:5px;">
            <svg style="width:13px; height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            PDF Acciones Admin
          </a>
          <div style="display:flex; gap:6px; align-items:center;">
            <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
            <input type="date" onchange="filtrarPorFecha(this.value, 'tablaNuevosUsuarios')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
            <button type="button" onclick="limpiarFiltroFecha('tablaNuevosUsuarios', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
          </div>
          <input type="text" id="filtroNuevosUsuarios" placeholder="Buscar por nombre o correo..." onkeyup="filtrarTablaGenerica('filtroNuevosUsuarios', 'tablaNuevosUsuarios')" style="padding:7px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; width:220px;">
        </div>
      </div>

      <div class="tabla-contenedor">
        <table class="tabla-panel" id="tablaNuevosUsuarios">
          <thead>
            <tr>
              <th>ID</th>
              <th>Rol / Tipo de Cuenta</th>
              <th>Nombre Completo / Razón Social</th>
              <th>Correo Electrónico</th>
              <th>Teléfono</th>
              <th>Hora y Fecha</th>
              <th>Estado de Cuenta</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($nuevosUsuarios)): ?>
              <tr>
                <td colspan="7" style="text-align:center; padding:2.5rem; color:#64748b;">No hay nuevos usuarios registrados recientemente.</td>
              </tr>
            <?php else: ?>
              <?php 
                $fechaGrupoNuevo = null;
                foreach ($nuevosUsuarios as $nu): 
                  $dia = !empty($nu['fecha']) ? date('Y-m-d', strtotime($nu['fecha'])) : 'sin_fecha';
                  if ($dia !== $fechaGrupoNuevo):
                    $fechaGrupoNuevo = $dia;
              ?>
                <tr class="fila-separador-fecha" data-fecha="<?= e($dia) ?>">
                  <td colspan="7" style="background:#f1f5f9; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1; padding:9px 16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span style="font-weight:800; font-size:13px; color:#1e40af;">
                        <?= formatoFechaGrupo($dia) ?>
                      </span>
                      <div style="display:flex; align-items:center; gap:8px;">
                        <?php if ($dia !== 'sin_fecha'): ?>
                          <a href="ban.php?pagina=pdf_reporte_diario&tipo=admin&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:3px 9px; font-weight:700; text-decoration:none; border-radius:4px;">
                            PDF Acciones del Día
                          </a>
                          <a href="ban.php?pagina=pdf_reporte_diario&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#ffffff; color:#334155; border:1px solid #cbd5e1; font-size:11px; padding:3px 9px; font-weight:600; text-decoration:none; border-radius:4px;">
                            PDF Completo
                          </a>
                        <?php endif; ?>
                        <span style="font-size:11px; font-weight:700; color:#475569; background:#ffffff; padding:2px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                          <?= $dia !== 'sin_fecha' ? date('d/m/Y', strtotime($dia)) : 'Sin fecha' ?>
                        </span>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
                <tr class="fila-dato-fecha" data-fecha="<?= e($dia) ?>">
                  <td>#<?= (int) $nu['id'] ?></td>
                  <td>
                    <?php if ($nu['rol'] === 'menor'): ?>
                      <span class="badge-menor" style="background:#e0e7ff; color:#3730a3; font-weight:700;">Menor (16-17)</span>
                    <?php elseif ($nu['rol'] === 'mayor'): ?>
                      <span class="badge-menor" style="background:#f1f5f9; color:#0f172a; font-weight:700;">Mayor (18+)</span>
                    <?php elseif ($nu['rol'] === 'empresa'): ?>
                      <span class="badge-menor" style="background:#dbeafe; color:#1e40af; font-weight:700;">Empresa</span>
                    <?php elseif ($nu['rol'] === 'emprendimiento'): ?>
                      <span class="badge-menor" style="background:#fef3c7; color:#92400e; font-weight:700;">Emprendimiento</span>
                    <?php else: ?>
                      <span class="badge-menor" style="background:#f1f5f9; color:#475569;"><?= e(ucfirst($nu['rol'])) ?></span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <strong><?= e($nu['nombre']) ?></strong>
                  </td>
                  <td>
                    <a href="mailto:<?= e($nu['correo']) ?>" class="enlace-contacto"><?= e($nu['correo']) ?></a>
                  </td>
                  <td>
                    <?= !empty($nu['telefono']) ? e($nu['telefono']) : '<span class="texto-suave">Sin registrar</span>' ?>
                  </td>
                  <td style="font-size:12px; color:#475569; white-space:nowrap;">
                    <?= !empty($nu['fecha']) ? date('H:i:s · d/m/Y', strtotime($nu['fecha'])) : '-' ?>
                  </td>
                  <td>
                    <?php if (!empty($nu['baneado'])): ?>
                      <span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:700;">Suspendido</span>
                    <?php else: ?>
                      <span class="badge" style="background:#d1fae5; color:#065f46; font-weight:700;">Activo</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>



    <!-- ========================================== -->
    <!-- SUB-APARTADO 5: BLOQUEO TRAS 3 CONTRASEÑAS -->
    <!-- ========================================== -->
    <div id="reg-bloqueos3" class="tab-panel-reg">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
          <h3 style="margin:0; font-size:1.35rem; color:#c2410c; display:flex; align-items:center; gap:8px;">
            Bloqueo de Correos tras fallar 3 veces la Contraseña
          </h3>
          <p class="texto-suave" style="font-size:13.5px; margin:3px 0 0 0;">
            Protección automática anti-fuerza bruta separada por fechas: Cuentas bloqueadas al ingresar 3 contraseñas erróneas.
          </p>
        </div>
        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
          <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
          <input type="date" onchange="filtrarPorFecha(this.value, 'tablaBloqueos3')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
          <button type="button" onclick="limpiarFiltroFecha('tablaBloqueos3', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
          <span class="badge" style="background:#ffedd5; color:#9a3412; font-weight:700; padding:6px 14px; font-size:12.5px;">
            <?= count($correosBloqueadosFallos ?? []) ?> Bloqueos por Contraseña
          </span>
        </div>
      </div>

      <!-- Tarjeta explicativa de la Política -->
      <div style="background:#fff7ed; border:1px solid #ffedd5; border-radius:8px; padding:16px 20px; margin-bottom:1.8rem; font-size:13.5px; color:#9a3412;">
        <strong>Regla de Seguridad Activa:</strong> Al alcanzar 3 intentos fallidos de contraseña en el inicio de sesión, el acceso queda clausurado con registro de fecha y hora exacta. El administrador puede revisar el origen y desbloquear la cuenta con un solo clic para restablecer los intentos a cero.
      </div>

      <div class="tabla-contenedor">
        <table class="tabla-panel" id="tablaBloqueos3">
          <thead>
            <tr>
              <th>ID</th>
              <th>Correo Electrónico Bloqueado</th>
              <th>Intentos Fallidos</th>
              <th>Fecha y Hora de Bloqueo</th>
              <th>Último Intento Registrado</th>
              <th>Estado de Seguridad</th>
              <th>Acción de Desbloqueo</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($correosBloqueadosFallos)): ?>
              <tr>
                <td colspan="7" style="text-align:center; padding:2.5rem; color:#059669; background:#f0fdf4;">
                  <strong style="font-size:15px;">No hay correos bloqueados por intentos fallidos de contraseña en este momento.</strong>
                  <div style="font-size:12.5px; color:#166534; margin-top:6px;">No se registran ataques de fuerza bruta o bloqueos automáticos pendientes de atención.</div>
                </td>
              </tr>
            <?php else: ?>
              <?php 
                $fechaGrupoBloq = null;
                foreach ($correosBloqueadosFallos as $bf): 
                  $fechaRef = !empty($bf['bloqueado_en']) ? $bf['bloqueado_en'] : $bf['ultimo_intento'];
                  $dia = !empty($fechaRef) ? date('Y-m-d', strtotime($fechaRef)) : 'sin_fecha';
                  if ($dia !== $fechaGrupoBloq):
                    $fechaGrupoBloq = $dia;
              ?>
                <tr class="fila-separador-fecha" data-fecha="<?= e($dia) ?>">
                  <td colspan="7" style="background:#f1f5f9; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1; padding:9px 16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span style="font-weight:800; font-size:13px; color:#1e40af;">
                        <?= formatoFechaGrupo($dia) ?>
                      </span>
                      <div style="display:flex; align-items:center; gap:8px;">
                        <?php if ($dia !== 'sin_fecha'): ?>
                          <a href="ban.php?pagina=pdf_reporte_diario&tipo=admin&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:3px 9px; font-weight:700; text-decoration:none; border-radius:4px;">
                            PDF Acciones del Día
                          </a>
                          <a href="ban.php?pagina=pdf_reporte_diario&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#ffffff; color:#334155; border:1px solid #cbd5e1; font-size:11px; padding:3px 9px; font-weight:600; text-decoration:none; border-radius:4px;">
                            PDF Completo
                          </a>
                        <?php endif; ?>
                        <span style="font-size:11px; font-weight:700; color:#475569; background:#ffffff; padding:2px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                          <?= $dia !== 'sin_fecha' ? date('d/m/Y', strtotime($dia)) : 'Sin fecha' ?>
                        </span>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
                <tr class="fila-dato-fecha" data-fecha="<?= e($dia) ?>">
                  <td>#<?= (int) $bf['id'] ?></td>
                  <td>
                    <strong><?= e($bf['correo']) ?></strong>
                  </td>
                  <td>
                    <span class="badge" style="background:#fee2e2; color:#991b1b; font-weight:800; font-size:12.5px;">
                      <?= (int) $bf['intentos'] ?> / 3 Intentos Fallidos
                    </span>
                  </td>
                  <td style="font-size:12px; color:#64748b; white-space:nowrap;">
                    <?= !empty($bf['bloqueado_en']) ? date('H:i:s · d/m/Y', strtotime($bf['bloqueado_en'])) : '<span class="texto-suave">Automático</span>' ?>
                  </td>
                  <td style="font-size:12px; color:#64748b; white-space:nowrap;">
                    <?= !empty($bf['ultimo_intento']) ? date('H:i:s · d/m/Y', strtotime($bf['ultimo_intento'])) : '-' ?>
                  </td>
                  <td>
                    <span class="badge badge-suspendida" style="background:#fee2e2; color:#991b1b; font-weight:700;">
                      Bloqueado por Seguridad
                    </span>
                  </td>
                  <td>
                    <form action="ban.php?pagina=desbloquear_correo" method="post" onsubmit="return confirm('¿Desbloquear el correo <?= e(addslashes($bf['correo'])) ?> y reiniciar sus intentos fallidos a 0?');">
                      <input type="hidden" name="correo" value="<?= e($bf['correo']) ?>">
                      <input type="hidden" name="origen" value="panel_admin">
                      <button type="submit" class="boton-sm" style="background:#059669; color:white; font-weight:700; display:inline-flex; align-items:center; gap:4px;">
                        <svg style="width:13px; height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                        Desbloquear y Reiniciar
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>



    <!-- ========================================== -->
    <!-- SUB-APARTADO 6: REGISTROS DEL ADMINISTRADOR -->
    <!-- ========================================== -->
    <div id="reg-adminlogs" class="tab-panel-reg">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
          <h3 style="margin:0; font-size:1.35rem; color:#0f172a; display:flex; align-items:center; gap:8px;">
            Registros y Bitácora del Administrador
          </h3>
          <p class="texto-suave" style="font-size:13.5px; margin:3px 0 0 0;">Historial cronológico de auditoría con todas las intervenciones separadas por día.</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
          <a href="ban.php?pagina=pdf_reporte_diario&tipo=admin&fecha=<?= date('Y-m-d') ?>" target="_blank" class="boton-sm" style="background:#1e40af; color:#ffffff; font-weight:700; border:1px solid #1e3a8a; padding:6px 12px; text-decoration:none; border-radius:6px; display:inline-flex; align-items:center; gap:5px;">
            <svg style="width:13px; height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            PDF Acciones Admin
          </a>
          <div style="display:flex; gap:6px; align-items:center;">
            <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
            <input type="date" onchange="filtrarPorFecha(this.value, 'tablaLogsAdmin')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
            <button type="button" onclick="limpiarFiltroFecha('tablaLogsAdmin', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
          </div>
          <input type="text" id="filtroLogsAdmin" placeholder="Filtrar por texto..." onkeyup="filtrarTablaGenerica('filtroLogsAdmin', 'tablaLogsAdmin')" style="padding:7px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; width:220px;">
        </div>
      </div>

      <div class="tabla-contenedor">
        <table class="tabla-panel" id="tablaLogsAdmin">
          <thead>
            <tr>
              <th>ID</th>
              <th>Administrador</th>
              <th>Tipo de Evento</th>
              <th>Título de la Acción</th>
              <th>Descripción Detallada</th>
              <th>Usuario / Correo Afectado</th>
              <th>IP Origen</th>
              <th>Hora y Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($registrosAdmin)): ?>
              <tr>
                <td colspan="8" style="text-align:center; padding:2.5rem; color:#64748b;">No hay eventos registrados en la bitácora todavía.</td>
              </tr>
            <?php else: ?>
              <?php 
                $fechaGrupoLogs = null;
                foreach ($registrosAdmin as $ra): 
                  $dia = !empty($ra['fecha_registro']) ? date('Y-m-d', strtotime($ra['fecha_registro'])) : 'sin_fecha';
                  if ($dia !== $fechaGrupoLogs):
                    $fechaGrupoLogs = $dia;
              ?>
                <tr class="fila-separador-fecha" data-fecha="<?= e($dia) ?>">
                  <td colspan="8" style="background:#f1f5f9; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1; padding:9px 16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span style="font-weight:800; font-size:13px; color:#1e40af;">
                        <?= formatoFechaGrupo($dia) ?>
                      </span>
                      <div style="display:flex; align-items:center; gap:8px;">
                        <?php if ($dia !== 'sin_fecha'): ?>
                          <a href="ban.php?pagina=pdf_reporte_diario&tipo=admin&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:3px 9px; font-weight:700; text-decoration:none; border-radius:4px;">
                            PDF Acciones del Día
                          </a>
                          <a href="ban.php?pagina=pdf_reporte_diario&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#ffffff; color:#334155; border:1px solid #cbd5e1; font-size:11px; padding:3px 9px; font-weight:600; text-decoration:none; border-radius:4px;">
                            PDF Completo
                          </a>
                        <?php endif; ?>
                        <span style="font-size:11px; font-weight:700; color:#475569; background:#ffffff; padding:2px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                          <?= $dia !== 'sin_fecha' ? date('d/m/Y', strtotime($dia)) : 'Sin fecha' ?>
                        </span>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
                <?php
                  $tipoEv = $ra['tipo_evento'] ?? 'accion_admin';
                  $badgeClase = 'badge-evento-accion';
                  if ($tipoEv === 'baneo') $badgeClase = 'badge-evento-baneo';
                  elseif ($tipoEv === 'desbaneo') $badgeClase = 'badge-evento-desbaneo';
                  elseif ($tipoEv === 'bloqueo_correo') $badgeClase = 'badge-evento-bloqueo';
                  elseif ($tipoEv === 'desbloqueo_correo') $badgeClase = 'badge-evento-desbloqueo';
                  elseif ($tipoEv === 'nuevo_usuario') $badgeClase = 'badge-evento-nuevo';
                  elseif ($tipoEv === 'verificacion') $badgeClase = 'badge-evento-verificacion';
                ?>
                <tr class="fila-dato-fecha" data-fecha="<?= e($dia) ?>">
                  <td>#<?= (int) $ra['id'] ?></td>
                  <td>
                    <strong><?= e($ra['admin_nombre'] ?? (!empty($ra['admin_id']) ? "Admin #{$ra['admin_id']}" : 'Sistema Vantia')) ?></strong>
                  </td>
                  <td>
                    <span class="badge-evento <?= $badgeClase ?>">
                      <?= e(str_replace('_', ' ', $tipoEv)) ?>
                    </span>
                  </td>
                  <td>
                    <strong><?= e($ra['titulo']) ?></strong>
                  </td>
                  <td style="font-size:13px; color:#334155;">
                    <?= e($ra['descripcion']) ?>
                  </td>
                  <td>
                    <?= !empty($ra['usuario_afectado']) ? '<code>' . e($ra['usuario_afectado']) . '</code>' : '<span class="texto-suave">-</span>' ?>
                  </td>
                  <td style="font-size:12px; color:#64748b;">
                    <?= e($ra['ip_origen'] ?? '127.0.0.1') ?>
                  </td>
                  <td style="font-size:12px; color:#64748b; white-space:nowrap;">
                    <?= date('H:i:s · d/m/Y', strtotime($ra['fecha_registro'])) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ========================================== -->
    <!-- SUB-APARTADO 7: ATENCIÓN AL CLIENTE        -->
    <!-- ========================================== -->
    <div id="reg-soporte" class="tab-panel-reg">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.4rem; flex-wrap:wrap; gap:0.5rem;">
        <div>
          <h3 style="margin:0; font-size:1.35rem; color:#0f172a; display:flex; align-items:center; gap:8px;">
            Registros de Atención al Cliente (Gmails Recibidos)
          </h3>
          <p class="texto-suave" style="font-size:13.5px; margin:3px 0 0 0;">Bandeja de auditoría y seguimiento de los correos Gmail enviados por usuarios a través de Atención al Cliente.</p>
        </div>
        <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
          <div style="display:flex; gap:6px; align-items:center;">
            <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
            <input type="date" onchange="filtrarPorFecha(this.value, 'tablaAtencionClienteAdmin')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
            <button type="button" onclick="limpiarFiltroFecha('tablaAtencionClienteAdmin', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
          </div>
          <input type="text" id="filtroAtencionClienteAdmin" placeholder="Buscar por nombre, correo, asunto..." onkeyup="filtrarTablaGenerica('filtroAtencionClienteAdmin', 'tablaAtencionClienteAdmin')" style="padding:7px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; width:240px;">
          <span class="total-badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;"><?= count($mensajesSoporte ?? []) ?> consultas</span>
          <?php if (!empty($totalSoportePendientes) && $totalSoportePendientes > 0): ?>
            <span class="total-badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; font-weight:800;"><?= (int) $totalSoportePendientes ?> pendiente(s)</span>
          <?php endif; ?>
        </div>
      </div>

      <div class="tabla-contenedor">
        <table class="tabla-panel" id="tablaAtencionClienteAdmin">
          <thead>
            <tr>
              <th>ID</th>
              <th>Remitente</th>
              <th>Correo Gmail</th>
              <th>Tipo de Usuario</th>
              <th>Asunto</th>
              <th>Detalle del Mensaje</th>
              <th>Estado</th>
              <th>Hora y Fecha</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($mensajesSoporte)): ?>
              <tr>
                <td colspan="9" style="text-align:center; padding:2.5rem; color:#64748b;">No hay mensajes o correos de Atención al Cliente registrados todavía.</td>
              </tr>
            <?php else: ?>
              <?php 
                $fechaGrupoSoporteAdmin = null;
                foreach ($mensajesSoporte as $ms): 
                  $dia = !empty($ms['creado_en']) ? date('Y-m-d', strtotime($ms['creado_en'])) : 'sin_fecha';
                  if ($dia !== $fechaGrupoSoporteAdmin):
                    $fechaGrupoSoporteAdmin = $dia;
              ?>
                <tr class="fila-separador-fecha" data-fecha="<?= e($dia) ?>">
                  <td colspan="9" style="background:#f1f5f9; border-top:2px solid #cbd5e1; border-bottom:1px solid #cbd5e1; padding:9px 16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                      <span style="font-weight:800; font-size:13px; color:#1e40af;">
                        <?= formatoFechaGrupo($dia) ?>
                      </span>
                      <div style="display:flex; align-items:center; gap:8px;">
                        <?php if ($dia !== 'sin_fecha'): ?>
                          <a href="ban.php?pagina=pdf_reporte_diario&fecha=<?= urlencode($dia) ?>" target="_blank" class="boton-sm" style="background:#ffffff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:3px 9px; font-weight:700; text-decoration:none; border-radius:4px;">
                            PDF del Día
                          </a>
                        <?php endif; ?>
                        <span style="font-size:11px; font-weight:700; color:#475569; background:#ffffff; padding:2px 8px; border-radius:4px; border:1px solid #cbd5e1;">
                          <?= $dia !== 'sin_fecha' ? date('d/m/Y', strtotime($dia)) : 'Sin fecha' ?>
                        </span>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endif; ?>
                <tr class="fila-dato-fecha" data-fecha="<?= e($dia) ?>">
                  <td>#<?= (int) $ms['id'] ?></td>
                  <td>
                    <strong><?= e($ms['nombre']) ?></strong>
                  </td>
                  <td>
                    <a href="mailto:<?= e($ms['email']) ?>?subject=<?= urlencode('[Vantia Atención al Cliente] RE: ' . $ms['asunto']) ?>" style="color:#1d4ed8; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Clic para responder">
                      <svg style="width:13px; height:13px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                      <?= e($ms['email']) ?>
                    </a>
                  </td>
                  <td>
                    <span class="etiqueta" style="font-size:11px; background:#f1f5f9; color:#475569;"><?= e($ms['rol']) ?></span>
                  </td>
                  <td>
                    <strong><?= e($ms['asunto']) ?></strong>
                  </td>
                  <td>
                    <div style="font-size:12.5px; color:#334155; line-height:1.45; max-width:320px; word-break:break-word; background:#f8fafc; padding:6px 10px; border-radius:4px; border:1px solid #e2e8f0;">
                      <?= nl2br(e($ms['mensaje'])) ?>
                    </div>
                  </td>
                  <td>
                    <?php if (($ms['estado'] ?? 'pendiente') === 'respondido'): ?>
                      <span style="display:inline-flex; align-items:center; gap:4px; background:#dcfce7; color:#166534; font-weight:700; font-size:11px; padding:3px 8px; border-radius:4px; border:1px solid #bbf7d0;">
                        Respondido
                      </span>
                    <?php else: ?>
                      <span style="display:inline-flex; align-items:center; gap:4px; background:#fef3c7; color:#92400e; font-weight:700; font-size:11px; padding:3px 8px; border-radius:4px; border:1px solid #fde68a;">
                        Pendiente
                      </span>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:12px; color:#64748b; white-space:nowrap;">
                    <?= date('H:i:s · d/m/Y', strtotime($ms['creado_en'])) ?>
                  </td>
                  <td>
                    <div style="display:flex; gap:5px; align-items:center; flex-wrap:wrap;">
                      <a href="mailto:<?= e($ms['email']) ?>?subject=<?= urlencode('[Vantia Atención al Cliente] RE: ' . $ms['asunto']) ?>" class="boton-sm" style="background:#1e40af; color:#ffffff; font-weight:700; text-decoration:none; padding:4px 9px; border-radius:4px; font-size:11px; display:inline-flex; align-items:center; gap:4px;" title="Redactar correo de respuesta">
                        Responder Gmail
                      </a>
                      <?php if (($ms['estado'] ?? 'pendiente') === 'pendiente'): ?>
                        <form action="ban.php?pagina=cambiar_estado_soporte" method="post" style="display:inline;">
                          <input type="hidden" name="id" value="<?= (int) $ms['id'] ?>">
                          <input type="hidden" name="estado" value="respondido">
                          <input type="hidden" name="origen" value="panel_admin">
                          <button type="submit" class="boton-sm" style="background:#059669; color:#ffffff; border:none; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;" title="Marcar como respondido">
                            Listo
                          </button>
                        </form>
                      <?php else: ?>
                        <form action="ban.php?pagina=cambiar_estado_soporte" method="post" style="display:inline;">
                          <input type="hidden" name="id" value="<?= (int) $ms['id'] ?>">
                          <input type="hidden" name="estado" value="pendiente">
                          <input type="hidden" name="origen" value="panel_admin">
                          <button type="submit" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;" title="Reabrir a pendiente">
                            Reabrir
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>

<!-- Modal Reutilizable de Baneo Temporal o Permanente -->
<div id="modalBaneo" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(2px);">
  <div style="background:white; border-radius:8px; width:90%; max-width:440px; padding:24px; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2); border:1px solid #e2e8f0;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; border-bottom:1px solid #e2e8f0; padding-bottom:10px;">
      <h3 style="margin:0; font-size:17px; color:#991b1b; display:flex; align-items:center; gap:6px;">
        Suspender Cuenta (Baneo)
      </h3>
      <button type="button" onclick="cerrarModalBaneo()" style="background:none; border:none; font-size:24px; cursor:pointer; color:#64748b; line-height:1;">&times;</button>
    </div>

    <form action="ban.php?pagina=banear_cuenta" method="post" id="formBaneo">
      <input type="hidden" name="id" id="baneoId" value="">
      <input type="hidden" name="tabla" id="baneoTabla" value="">

      <div style="margin-bottom:14px;">
        <label style="font-size:12px; font-weight:bold; color:#475569; display:block; margin-bottom:4px;">Cuenta seleccionada:</label>
        <div id="baneoNombre" style="font-weight:700; color:#0f172a; font-size:14px; background:#f8fafc; padding:8px 12px; border-radius:4px; border:1px solid #e2e8f0;"></div>
      </div>

      <div style="margin-bottom:14px;">
        <label for="tipo_baneo" style="font-size:12px; font-weight:bold; color:#475569; display:block; margin-bottom:4px;">Tipo de Suspensión:</label>
        <select name="tipo_baneo" id="tipo_baneo" class="select-sm" style="width:100%; height:38px; border-color:#cbd5e1; border-radius:4px;" onchange="alternarDuracionBaneo(this.value)">
          <option value="temporal">Suspensión Temporal (días)</option>
          <option value="permanente">Suspensión Permanente</option>
        </select>
      </div>

      <div id="grupoDuracion" style="margin-bottom:14px;">
        <label for="duracion_dias" style="font-size:12px; font-weight:bold; color:#475569; display:block; margin-bottom:4px;">Tiempo de suspensión:</label>
        <select name="duracion_dias" id="duracion_dias" class="select-sm" style="width:100%; height:38px; border-color:#cbd5e1; border-radius:4px;">
          <option value="1">1 Día</option>
          <option value="3">3 Días</option>
          <option value="7" selected>7 Días (1 semana)</option>
          <option value="15">15 Días (2 semanas)</option>
          <option value="30">30 Días (1 mes)</option>
          <option value="90">90 Días (3 meses)</option>
        </select>
      </div>

      <div style="margin-bottom:20px;">
        <label for="motivo" style="font-size:12px; font-weight:bold; color:#475569; display:block; margin-bottom:4px;">Motivo de la sanción:</label>
        <input type="text" name="motivo" id="motivo" placeholder="Ej. Publicación de ofertas falsas o conducta inapropiada" required style="width:100%; height:38px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:13px;">
      </div>

      <div style="display:flex; justify-content:flex-end; gap:8px;">
        <button type="button" onclick="cerrarModalBaneo()" class="boton-sm boton-outline-oscuro" style="border-color:#cbd5e1; color:#475569;">Cancelar</button>
        <button type="submit" class="boton-sm boton-peligro" style="background:#dc2626;">Aplicar Suspensión</button>
      </div>
    </form>
  </div>
</div>

<script>
function abrirModalBaneo(id, tabla, nombre) {
  document.getElementById('baneoId').value = id;
  document.getElementById('baneoTabla').value = tabla;
  document.getElementById('baneoNombre').textContent = nombre;
  document.getElementById('tipo_baneo').value = 'temporal';
  document.getElementById('grupoDuracion').style.display = 'block';
  document.getElementById('motivo').value = '';
  document.getElementById('modalBaneo').style.display = 'flex';
}

function cerrarModalBaneo() {
  document.getElementById('modalBaneo').style.display = 'none';
}

function alternarDuracionBaneo(tipo) {
  document.getElementById('grupoDuracion').style.display = (tipo === 'temporal') ? 'block' : 'none';
}

// Cerrar modal al hacer clic en el fondo oscuro
window.addEventListener('click', function(e) {
  const modal = document.getElementById('modalBaneo');
  if (e.target === modal) {
    cerrarModalBaneo();
  }
});

// Control de Pestañas de la Sección de Registros
function cambiarTabRegistros(tabId, btnElement) {
  // Ocultar todos los paneles de registros
  const paneles = document.querySelectorAll('.tab-panel-reg');
  paneles.forEach(p => p.classList.remove('activo'));

  // Desactivar todos los botones de pestañas
  const botones = document.querySelectorAll('.btn-tab-reg');
  botones.forEach(b => b.classList.remove('activo'));

  // Activar el panel y el botón correspondiente
  const panelObjetivo = document.getElementById(tabId);
  if (panelObjetivo) {
    panelObjetivo.classList.add('activo');
  }
  if (btnElement) {
    btnElement.classList.add('activo');
  }
}

// Filtro rápido de tablas por texto
function filtrarTablaGenerica(inputId, tableId) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const filtro = input.value.toLowerCase().trim();
  const tabla = document.getElementById(tableId);
  if (!tabla) return;
  const filas = tabla.getElementsByTagName('tr');

  for (let i = 1; i < filas.length; i++) {
    const fila = filas[i];
    if (fila.classList.contains('fila-separador-fecha')) {
      fila.style.display = (filtro === '') ? '' : 'none';
      continue;
    }
    const textoFila = fila.textContent || fila.innerText;
    if (textoFila.toLowerCase().indexOf(filtro) > -1) {
      fila.style.display = '';
    } else {
      fila.style.display = 'none';
    }
  }
}

// Filtro interactivo por fecha específica
function filtrarPorFecha(fecha, tableId) {
  const tabla = document.getElementById(tableId);
  if (!tabla) return;
  const filas = tabla.querySelectorAll('tr[data-fecha]');
  filas.forEach(f => {
    if (!fecha || f.getAttribute('data-fecha') === fecha) {
      f.style.display = '';
    } else {
      f.style.display = 'none';
    }
  });
}

// Limpiar filtro de fecha y mostrar todos los grupos
function limpiarFiltroFecha(tableId, btn) {
  const contenedor = btn.parentElement;
  const input = contenedor.querySelector('input[type="date"]');
  if (input) input.value = '';
  filtrarPorFecha('', tableId);
}

// Detección automática de pestaña por hash de URL al cargar la página
window.addEventListener('DOMContentLoaded', function() {
  const hash = window.location.hash;
  if (hash) {
    const mapaHash = {
      '#reg-reportes': 'reg-reportes',
      '#reg-baneos': 'reg-baneos',
      '#reg-desbaneos': 'reg-desbaneos',
      '#reg-nuevos': 'reg-nuevos',
      '#reg-bloqueos3': 'reg-bloqueos3',
      '#reg-adminlogs': 'reg-adminlogs'
    };

    if (mapaHash[hash]) {
      const tabId = mapaHash[hash];
      const boton = document.querySelector(`button[onclick*="'${tabId}'"]`);
      if (boton) {
        cambiarTabRegistros(tabId, boton);
      }
    }
  }
});
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
