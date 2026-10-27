<?php
require __DIR__ . '/includes/app.php';
page_start(t('nav_home'), 'index');
?>
<!-- Hero de portada: mensaje principal, llamadas a la acción e ilustración estática con carga rápida. -->
<section class="hero">
  <div class="hero-copy">
    <p class="eyebrow"><?= h(t('home_eyebrow')) ?></p>
    <h1>GTools <span>Academy</span></h1>
    <p><?= h(t('tagline')) ?></p>
    <div class="hero-actions">
      <a class="solid" href="cursos.php"><?= h(t('cta_courses')) ?></a>
      <a class="ghost" href="acceso.php"><?= h(t('cta_join')) ?></a>
    </div>
  </div>
  <div class="hero-visual">
    <figure class="arch workspace-illustration">
      <img src="assets/img/learning-workspace.svg" alt="<?= h(t('home_image_alt')) ?>" width="720" height="430" fetchpriority="high">
      <figcaption><?= h(t('course_visual')) ?></figcaption>
      <svg class="swirl" viewBox="0 0 400 160" aria-hidden="true"><path d="M10 90C60 20 340 10 390 70S300 150 140 135 20 120 120 60"/></svg>
    </figure>
  </div>
</section>

<!-- Esta sección presenta la finalidad educativa y la propuesta de valor de la plataforma. -->
<section class="about">
  <h2><?= h(t('about_title')) ?></h2>
  <p class="lead"><?= h(t('about_text')) ?></p>
</section>

<!-- Las tarjetas segmentan la experiencia por perfil y dirigen al catálogo con el filtro correspondiente. -->
<section>
  <h2><?= h(t('aud_title')) ?></h2>
  <div class="grid">
<?php foreach (['maestros' => '👩‍🏫', 'alumnos' => '🎓', 'general' => '🌐'] as $aud => $icon): ?>
    <a class="card aud" data-aud="<?= h($aud) ?>" href="cursos.php?aud=<?= h($aud) ?>">
      <span class="icon" aria-hidden="true"><?= h($icon) ?></span>
      <h3><?= h(t('aud_' . $aud)) ?></h3>
      <p><?= h(t('apply_' . $aud)) ?></p>
    </a>
<?php endforeach; ?>
  </div>
</section>
<?php page_end();
