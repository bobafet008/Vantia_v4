<?php require __DIR__ . '/partials/header.php'; ?>

<section class="pagina-cabecera">
  <div class="contenedor">
    <span class="etiqueta">ESPACIO PARA ORGANIZACIONES</span>
    <h1>Conecta con talento joven.</h1>
    <p>Publica oportunidades laborales para jóvenes y administra tus procesos de selección.</p>
  </div>
</section>

<section class="contenedor seccion">
  <div class="tarjetas dos">
    <article class="tarjeta">
      <span class="numero">01 · ACCESO</span>
      <h2>Iniciar sesión</h2>
      <p>Ingresa a tu cuenta de empresa para publicar vacantes y revisar los candidatos postulados.</p>
      <?php if (!empty($_SESSION['usuario_id']) && ($_SESSION['tipo'] ?? '') === 'empresa'): ?>
        <a class="boton" href="index.php?pagina=panel_empresa">Ir al panel de gestion</a>
      <?php else: ?>
        <a class="boton" href="index.php?pagina=login&tipo=empresa">Iniciar sesion</a>
      <?php endif; ?>
    </article>

    <article class="tarjeta">
      <span class="numero">02 · REGISTRO</span>
      <h2>Registrar empresa o emprendimiento</h2>
      <p>Si aun no tienes cuenta, registra los datos de tu empresa formal o tu emprendimiento independiente.</p>
      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <a class="boton boton-oscuro" href="index.php?pagina=registro_empresa">Registrar empresa</a>
        <a class="boton boton-outline" href="index.php?pagina=registro_emprendimiento">Registrar emprendimiento</a>
      </div>
    </article>
  </div>
</section>

<?php require __DIR__ . '/partials/footer.php'; ?>
