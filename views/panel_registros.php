<?php require __DIR__ . '/partials/header.php'; ?>

<section class="pagina-cabecera">
  <div class="contenedor">
    <span class="etiqueta">PANEL DE REGISTROS</span>
    <h1>Panel de Registros</h1>
    <p>Supervisión ejecutiva, bitácora de eventos, reportes globales, cuentas suspendidas, historial de desbaneos, registro de nuevos usuarios, bloqueos por 3 contraseñas fallidas y auditoría de administradores.</p>
    
    <div style="margin-top:1rem; display:flex; gap:0.6rem; flex-wrap:wrap; align-items:center;">
      <a href="ban.php?pagina=panel_admin" class="boton boton-sm boton-oscuro" style="display:inline-flex; align-items:center; gap:6px;">
        <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Volver a Panel Admin General
      </a>
      <a href="ban.php?pagina=trabajos" class="boton boton-sm" style="background:#e2f5e8; color:#1f7a46;">Bolsa pública de empleos</a>
    </div>
  </div>
</section>

<section class="contenedor seccion">
  <?php if (!empty($exito)): ?><div class="mensaje-exito" style="margin-bottom:1.5rem;"><?= e($exito) ?></div><?php endif; ?>
  <?php if (!empty($error)): ?><div class="mensaje-error" style="margin-bottom:1.5rem;"><?= e($error) ?></div><?php endif; ?>

  <!-- Generador Oficial de Reportes en PDF por Día -->
  <div class="bloque-panel" style="margin-bottom:2.2rem; background:#ffffff; border:1px solid #cbd5e1; border-left:4px solid #1e40af; border-radius:10px; padding:20px 24px; box-shadow:0 1px 3px rgba(0,0,0,0.04);">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      <div>
        <span class="etiqueta" style="background:#eff6ff; color:#1e40af; font-weight:800;">DOCUMENTO OFICIAL EN PDF</span>
        <h3 style="margin:6px 0 4px 0; font-size:1.25rem; color:#0f172a;">Generar Reporte Diario de Registros en PDF</h3>
        <p class="texto-suave" style="margin:0; font-size:13.5px; max-width:700px;">
          Descarga o visualiza el documento formal de auditoría para cualquier día seleccionado, detallando altas de usuarios, cuentas suspendidas, desbaneos, bloqueos de seguridad y bitácora administrativa.
        </p>
      </div>

      <form action="ban.php" method="get" target="_blank" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
        <input type="hidden" name="pagina" value="pdf_reporte_diario">
        <div style="display:flex; flex-direction:column; gap:3px;">
          <label style="font-size:11.5px; font-weight:700; color:#475569; text-transform:uppercase;">Fecha del reporte:</label>
          <input type="date" name="fecha" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required style="height:38px; padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px;">
        </div>
        <div style="display:flex; gap:6px; align-items:flex-end; margin-top:17px;">
          <button type="submit" class="boton-sm boton-primario" style="height:38px; padding:0 18px; font-weight:700; display:inline-flex; align-items:center; gap:6px;">
            <svg style="width:14px; height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Ver PDF del Día
          </button>
          <button type="submit" name="tipo" value="admin" class="boton-sm" style="height:38px; padding:0 14px; background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-weight:700; display:inline-flex; align-items:center; gap:5px;">
            PDF Acciones Admin
          </button>
          <button type="submit" name="descargar" value="1" class="boton-sm" style="height:38px; padding:0 14px; background:#f1f5f9; color:#1e293b; border:1px solid #cbd5e1; font-weight:600;">
            Descargar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Tarjetas de Estadísticas Globales (Mismo formato que Panel Admin) -->
  <div class="tarjetas" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem; margin-bottom:2.5rem;">
    <div class="tarjeta">
      <span class="numero">01 · TOTAL USUARIOS</span>
      <h2><?= (int) ($totalUsuariosGlobal ?? 0) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">02 · ALTAS RECIENTES</span>
      <h2>+<?= (int) ($nuevosUltimos7Dias ?? 0) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">03 · BANEOS ACTIVOS</span>
      <h2><?= count($cuentasBaneadas ?? []) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">04 · DESBANEOS</span>
      <h2><?= count($registrosDesbaneos ?? []) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">05 · ACCIONES ADMIN</span>
      <h2><?= count($registrosAdmin ?? []) ?></h2>
    </div>
    <div class="tarjeta">
      <span class="numero">06 · ATENCIÓN CLIENTE</span>
      <h2><?= count($mensajesSoporte ?? []) ?></h2>
      <span style="font-size:11.5px; font-weight:700; color:<?= (!empty($totalSoportePendientes) && $totalSoportePendientes > 0) ? '#b45309' : '#059669' ?>;">
        <?= (int) ($totalSoportePendientes ?? 0) ?> pendiente(s)
      </span>
    </div>
  </div>

  <style>
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
    .badge-evento-nuevo { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
    .badge-evento-verificacion { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .badge-evento-accion { background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; }
  </style>

  <!-- ========================================== -->
  <!-- SECCION 1: REPORTES Y METRICAS             -->
  <!-- ========================================== -->
  <div id="seccionReportes" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">RESUMEN Y METRICAS</span>
        <h2>Reportes Ejecutivos y Métricas Globales</h2>
        <p class="texto-suave">Resumen general del rendimiento, estados de seguridad y tasas de crecimiento de la plataforma Vantia.</p>
      </div>
      <span class="total-badge"><?= (int) ($totalUsuariosGlobal ?? 0) ?> usuarios globales</span>
    </div>

    <!-- Desglose Operativo en Columnas -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:1.5rem; margin-bottom:1.5rem;">
      <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:20px;">
        <h4 style="margin:0 0 14px 0; font-size:15px; color:#1e293b;">Validación y Verificación Laboral</h4>
        <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #e2e8f0; font-size:13.5px;">
          <span>Empresas Verificadas (con NIT / ROE):</span>
          <strong><?= (int) ($empresasVerificadas ?? 0) ?> de <?= (int) ($totalEmpresas ?? 0) ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #e2e8f0; font-size:13.5px;">
          <span>Emprendimientos Verificados:</span>
          <strong><?= (int) ($emprendimientosVerificados ?? 0) ?> de <?= (int) ($totalEmprendimientos ?? 0) ?></strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:9px 0; font-size:13.5px;">
          <span>Tutores Legales Vinculados a Menores:</span>
          <strong><?= (int) ($totalTutores ?? 0) ?> tutores</strong>
        </div>
      </div>

      <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:20px;">
        <h4 style="margin:0 0 14px 0; font-size:15px; color:#1e293b;">Movimiento Laboral y Postulaciones</h4>
        <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #e2e8f0; font-size:13.5px;">
          <span>Vacantes Laborales Publicadas:</span>
          <strong><?= (int) ($totalEmpleos ?? 0) ?> ofertas</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:9px 0; border-bottom:1px solid #e2e8f0; font-size:13.5px;">
          <span>Postulaciones Totales Realizadas:</span>
          <strong><?= (int) ($totalPostulaciones ?? 0) ?> aplicaciones</strong>
        </div>
        <div style="display:flex; justify-content:space-between; padding:9px 0; font-size:13.5px;">
          <span>Jóvenes Menores (16-17 años):</span>
          <strong><?= (int) ($totalMenores ?? 0) ?> candidatos</strong>
        </div>
      </div>
    </div>

    <!-- Alerta de Diagnóstico del Sistema -->
    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-left:4px solid #10b981; border-radius:8px; padding:16px 20px;">
      <h4 style="margin:0 0 6px 0; color:#166534; font-size:14px;">Diagnóstico Activo del Sistema</h4>
      <p style="margin:0; font-size:13px; color:#14532d; line-height:1.5;">
        La plataforma se encuentra operando de manera segura y estable. Todas las verificaciones, auditorías y acciones administrativas están sincronizadas en tiempo real.
      </p>
    </div>
  </div>

  <!-- ========================================== -->
  <!-- SECCION 2: BANEOS                          -->
  <!-- ========================================== -->
  <div id="seccionBaneos" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">SUSPENSIONES ACTIVAS</span>
        <h2>Cuentas con Suspensión Activa (Baneos)</h2>
        <p class="texto-suave">Listado separado por fechas de empresas, emprendimientos, postulantes mayores o menores con acceso suspendido temporal o permanentemente.</p>
      </div>
      <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
        <input type="date" onchange="filtrarPorFecha(this.value, 'tablaBaneosActivos')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
        <button type="button" onclick="limpiarFiltroFecha('tablaBaneosActivos', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
        <span class="total-badge"><?= count($cuentasBaneadas ?? []) ?> baneados</span>
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
              <td colspan="7" style="text-align:center; padding:2rem; color:#059669; background:#f0fdf4;">
                <strong style="font-size:14px;">No hay cuentas suspendidas (baneadas) en la plataforma actualmente.</strong>
                <div style="font-size:12px; color:#166534; margin-top:4px;">Todas las empresas, emprendimientos y candidatos operan en estado activo normal.</div>
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
                    <input type="hidden" name="origen" value="panel_registros">
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

    <!-- Subtabla: Bitácora de suspensiones aplicadas -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin:2rem 0 1rem 0; flex-wrap:wrap; gap:0.5rem;">
      <h3 style="margin:0; font-size:1.15rem; color:#0f172a;">Bitácora de Suspensiones Aplicadas (Separadas por Fecha)</h3>
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
  <!-- SECCION 3: DESBANEOS                       -->
  <!-- ========================================== -->
  <div id="seccionDesbaneos" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">HISTORIAL DE REACTIVACION</span>
        <h2>Historial de Desbaneos y Cuentas Reactivadas</h2>
        <p class="texto-suave">Registro separado por fechas de todas las sanciones levantadas y cuentas restauradas por los administradores.</p>
      </div>
      <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
        <input type="date" onchange="filtrarPorFecha(this.value, 'tablaDesbaneos')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
        <button type="button" onclick="limpiarFiltroFecha('tablaDesbaneos', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
        <span class="total-badge"><?= count($registrosDesbaneos ?? []) ?> reactivaciones</span>
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
  <!-- SECCION 4: REGISTRO DE NUEVOS USUARIOS     -->
  <!-- ========================================== -->
  <div id="seccionNuevos" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">ALTAS Y NUEVOS USUARIOS</span>
        <h2>Registro de Nuevos Usuarios en la Plataforma</h2>
        <p class="texto-suave">Bitácora cronológica unificada de altas separadas por fechas: jóvenes menores (16-17), mayores (18+), empresas y emprendimientos.</p>
      </div>
      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <div style="display:flex; gap:6px; align-items:center;">
          <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
          <input type="date" onchange="filtrarPorFecha(this.value, 'tablaNuevosUsuarios')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
          <button type="button" onclick="limpiarFiltroFecha('tablaNuevosUsuarios', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
        </div>
        <input type="text" id="filtroNuevosUsuarios" placeholder="Buscar por nombre o correo..." onkeyup="filtrarTablaGenerica('filtroNuevosUsuarios', 'tablaNuevosUsuarios')" style="padding:7px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; width:220px;">
        <span class="total-badge"><?= count($nuevosUsuarios ?? []) ?> registrados</span>
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
  <!-- SECCION 5: REGISTROS DEL ADMINISTRADOR     -->
  <!-- ========================================== -->
  <div id="seccionAdminLogs" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta">AUDITORIA Y REGISTROS</span>
        <h2>Registros y Bitácora del Administrador</h2>
        <p class="texto-suave">Historial cronológico de auditoría con todas las intervenciones separadas por día.</p>
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
        <span class="total-badge"><?= count($registrosAdmin ?? []) ?> eventos</span>
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
  <!-- SECCION 6: GMAIL ATENCION AL CLIENTE       -->
  <!-- ========================================== -->
  <div id="seccionAtencionCliente" class="bloque-panel" style="margin-bottom:3rem;">
    <div class="titulo-seccion">
      <div>
        <span class="etiqueta" style="background:#f0fdf4; color:#166534; font-weight:800;">SOPORTE Y CONTACTO GMAIL</span>
        <h2>Registros de Atención al Cliente (Gmails Recibidos)</h2>
        <p class="texto-suave">Bandeja de auditoría y seguimiento de los correos Gmail enviados por usuarios y postulantes a través del formulario de Atención al Cliente.</p>
      </div>
      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <div style="display:flex; gap:6px; align-items:center;">
          <label style="font-size:12px; font-weight:700; color:#475569;">Fecha:</label>
          <input type="date" onchange="filtrarPorFecha(this.value, 'tablaAtencionCliente')" style="padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px; font-size:12.5px;">
          <button type="button" onclick="limpiarFiltroFecha('tablaAtencionCliente', this)" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11.5px; padding:5px 9px;">Todas</button>
        </div>
        <input type="text" id="filtroAtencionCliente" placeholder="Buscar por nombre, correo, asunto..." onkeyup="filtrarTablaGenerica('filtroAtencionCliente', 'tablaAtencionCliente')" style="padding:7px 12px; border:1px solid #cbd5e1; border-radius:6px; font-size:13px; width:240px;">
        <span class="total-badge" style="background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0;"><?= count($mensajesSoporte ?? []) ?> consultas</span>
        <?php if (!empty($totalSoportePendientes) && $totalSoportePendientes > 0): ?>
          <span class="total-badge" style="background:#fef3c7; color:#92400e; border:1px solid #fde68a; font-weight:800;"><?= (int) $totalSoportePendientes ?> pendiente(s)</span>
        <?php endif; ?>
      </div>
    </div>

    <div class="tabla-contenedor">
      <table class="tabla-panel" id="tablaAtencionCliente">
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
              $fechaGrupoSoporte = null;
              foreach ($mensajesSoporte as $ms): 
                $dia = !empty($ms['creado_en']) ? date('Y-m-d', strtotime($ms['creado_en'])) : 'sin_fecha';
                if ($dia !== $fechaGrupoSoporte):
                  $fechaGrupoSoporte = $dia;
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
                  <a href="mailto:<?= e($ms['email']) ?>?subject=<?= urlencode('[Vantia Atención al Cliente] RE: ' . $ms['asunto']) ?>" style="color:#1d4ed8; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:4px;" title="Clic para redactar respuesta por correo">
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
                        <input type="hidden" name="origen" value="panel_registros">
                        <button type="submit" class="boton-sm" style="background:#059669; color:#ffffff; border:none; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;" title="Marcar como atendido y respondido">
                          Listo
                        </button>
                      </form>
                    <?php else: ?>
                      <form action="ban.php?pagina=cambiar_estado_soporte" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?= (int) $ms['id'] ?>">
                        <input type="hidden" name="estado" value="pendiente">
                        <input type="hidden" name="origen" value="panel_registros">
                        <button type="submit" class="boton-sm" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;" title="Reabrir a estado pendiente">
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
      <input type="hidden" name="origen" value="panel_registros">

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
        <input type="text" name="motivo" id="motivo" maxlength="60" placeholder="Ej. Publicación de ofertas falsas o conducta inapropiada" required style="width:100%; height:38px; padding:6px 10px; border:1px solid #cbd5e1; border-radius:4px; font-size:13px;">
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
</script>

<?php require __DIR__ . '/partials/footer.php'; ?>
