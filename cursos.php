<?php
// Preparación del catálogo, la información de cada curso y el progreso personal para renderizar los filtros y las rutas.
require __DIR__ . '/includes/app.php';

$courses = load_courses();
$byId = array_column($courses, null, 'id');
$mine = my_enrollments();
$u = user();
// Tres rutas de aprendizaje opcionales (una por público)
$paths = [
    'maestros' => ['drive', 'docs-slides', 'classroom', 'calendar-meet', 'forms', 'sheets'],
    'alumnos' => ['gmail', 'drive', 'docs-slides', 'notebooklm', 'scholar-trends', 'colab'],
    'general' => ['gmail', 'drive', 'sheets', 'looker-studio', 'apps-script'],
];
page_start(t('nav_courses'), 'cursos');
?>
<section class="banner">
  <div>
    <h1><?= h(t('nav_courses')) ?></h1>
    <p class="lead"><?= h(t('courses_sub')) ?></p>
  </div>
  <img src="assets/img/aula.svg" alt="" width="360" height="260">
</section>

<!-- Aquí agrupo búsqueda, filtros y contador para que siempre se entienda cuántos cursos coinciden. -->
<section>
  <div class="filters">
    <input id="q" type="search" placeholder="<?= h(t('search')) ?>" aria-label="<?= h(t('search')) ?>">
    <div class="chips" role="group">
      <button class="chip" data-aud="all" aria-pressed="true"><?= h(t('all')) ?></button>
<?php foreach (['maestros', 'alumnos', 'general'] as $a): ?>
      <button class="chip" data-aud="<?= $a ?>" aria-pressed="false"><?= h(t('aud_' . $a)) ?></button>
<?php endforeach; ?>
    </div>
    <select id="lvl" aria-label="<?= h(t('level')) ?>">
      <option value="all"><?= h(t('lvl_all')) ?></option>
<?php foreach (['basico', 'intermedio', 'avanzado'] as $l): ?>
      <option value="<?= $l ?>"><?= h(t('lvl_' . $l)) ?></option>
<?php endforeach; ?>
    </select>
  </div>

  <p class="course-count" aria-live="polite"><strong id="courseCount"><?= count($courses) ?></strong> <span id="courseCountLabel" data-singular="<?= h(t('course_count_one')) ?>" data-plural="<?= h(t('courses_count')) ?>"><?= h(t('courses_count')) ?></span></p>
  <div id="grid" class="grid">
<?php foreach ($courses as $c): ?>
    <article class="card" data-color="<?= h($c['color']) ?>" data-level="<?= h($c['level']) ?>" data-aud="<?= h(implode(' ', $c['audience'])) ?>" data-text="<?= h($c['title'][LI] . ' ' . $c['desc'][LI] . ' ' . implode(' ', $c['topics'][LI])) ?>">
      <span class="icon" aria-hidden="true"><?= h($c['icon']) ?></span>
      <h3><?= h($c['title'][LI]) ?></h3>
      <p><?= h($c['desc'][LI]) ?></p>
      <ul class="topics">
<?php foreach ($c['topics'][LI] as $topic): ?>
        <li><?= h($topic) ?></li>
<?php endforeach; ?>
      </ul>
      <p class="aud-row"><?php foreach ($c['audience'] as $a) echo aud_badge($a); ?></p>
      <div class="meta">
        <span class="badge"><?= h(t('lvl_' . $c['level'])) ?></span>
        <span class="badge"><?= (int) $c['hours'] ?> <?= h(t('hours')) ?></span>
<?php if ($c['category'] === 'poco-conocidas'): ?>
        <span class="badge"><?= h(t('rare')) ?></span>
<?php endif; ?>
        <a class="ghost" href="curso.php?id=<?= h($c['id']) ?>"><?= h(t('view')) ?></a>
      </div>
    </article>
<?php endforeach; ?>
  </div>
  <p id="empty" class="empty" hidden><?= h(t('empty')) ?></p>
</section>

<!-- Presentación de las tres rutas usando sus nombres como títulos y sin repetir las etiquetas de público. -->
<section id="rutas">
  <h2><?= h(t('paths_title')) ?></h2>
  <p class="note"><?= h(t('paths_sub')) ?></p>
  <ol class="road">
<?php foreach ($paths as $aud => $ids): ?>
    <li>
      <h3><?= h(t('path_' . $aud)) ?></h3>
      <p class="note"><?= h(t('apply_' . $aud)) ?></p>
      <ol>
<?php foreach ($ids as $id): ?>
        <li><a class="step<?= !empty($mine[$id]['completed_at']) ? ' done' : '' ?>" href="curso.php?id=<?= h($id) ?>"><?= h($byId[$id]['title'][LI]) ?></a></li>
<?php endforeach; ?>
      </ol>
    </li>
<?php endforeach; ?>
  </ol>
</section>

<!-- Explicación del reconocimiento que se desbloquea al completar un curso. -->
<section class="award">
  <h2><?= h(t('award_title')) ?></h2>
  <p class="lead"><?= h(t('award_text')) ?> <?= h(t('not_official')) ?></p>
  <p class="cert-name"><?= $u ? h($u['first_name'] . ' ' . $u['last_name']) : h(t('award_name')) ?></p>
<?php if (!$u): ?>
  <a class="solid" href="acceso.php"><?= h(t('cta_join')) ?></a>
<?php endif; ?>
</section>
<?php page_end();
