<?php
require __DIR__ . '/includes/app.php';
page_start(t('nav_home'), 'index');
?>
<!-- Aquí presento la academia con un mensaje principal y una zona visual para el video de bienvenida. -->
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
    <div class="arch" id="arch">
      <div class="ph"><?= h(t('video_ph')) ?></div>
      <video id="welcome" autoplay muted loop playsinline>
        <source src="assets/video/bienvenida.mov" type='video/quicktime; codecs="hvc1"'>
        <source src="assets/video/bienvenida.webm" type="video/webm">
      </video>
      <svg class="swirl" viewBox="0 0 400 160" aria-hidden="true"><path d="M10 90C60 20 340 10 390 70S300 150 140 135 20 120 120 60"/></svg>
      <button id="sound" type="button" hidden aria-label="Activar o silenciar video">🔇</button>
    </div>
  </div>
</section>

<!-- Aquí explico el objetivo del proyecto y a quién le sirve. -->
<section class="about">
  <h2><?= h(t('about_title')) ?></h2>
  <p class="lead"><?= h(t('about_text')) ?></p>
</section>

<!-- Aquí muestro los tres públicos y enlazo cada tarjeta con el filtro correspondiente del catálogo. -->
<section>
  <h2><?= h(t('aud_title')) ?></h2>
  <div class="grid">
<?php foreach (['maestros' => '👩‍🏫', 'alumnos' => '🎓', 'general' => '🌐'] as $aud => $icon): ?>
    <a class="card aud" data-aud="<?= $aud ?>" href="cursos.php?aud=<?= $aud ?>">
      <span class="icon" aria-hidden="true"><?= $icon ?></span>
      <h3><?= h(t('aud_' . $aud)) ?></h3>
      <p><?= h(t('apply_' . $aud)) ?></p>
    </a>
<?php endforeach; ?>
  </div>
</section>
<?php page_end();
