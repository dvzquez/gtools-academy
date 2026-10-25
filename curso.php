<?php
// Página de un curso: curso.php?id=forms
require __DIR__ . '/includes/app.php';

// Aquí busco el curso solicitado y recupero el progreso de la persona que inició sesión.
$course = find_course(param('id'));
$u = user();
$mine = $course ? (my_enrollments()[$course['id']] ?? null) : null;

// Aquí registro la inscripción o la finalización solo después de comprobar el token del formulario.
if ($course && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    if (!$u) {
        go('acceso.php?next=' . rawurlencode('curso.php?id=' . $course['id']));
    }
    if (post('action') === 'enroll') {
        // INSERT OR IGNORE es de SQLite (en MySQL: INSERT IGNORE)
        db()->prepare('INSERT OR IGNORE INTO enrollments (user_id, course_id) VALUES (?, ?)')->execute([$u['id'], $course['id']]);
    } elseif (post('action') === 'complete') {
        db()->prepare('UPDATE enrollments SET completed_at = COALESCE(completed_at, CURRENT_TIMESTAMP) WHERE user_id = ? AND course_id = ?')->execute([$u['id'], $course['id']]);
    }
    go('curso.php?id=' . rawurlencode($course['id']));
}

if (!$course) {
    http_response_code(404);
}
page_start($course ? $course['title'][LI] : t('notfound'), 'cursos');

if (!$course): ?>
<p class="empty"><?= h(t('notfound')) ?></p>
<a class="ghost" href="cursos.php"><?= h(t('back')) ?></a>
<?php else: ?>
<article class="course-page">
  <a class="note" href="cursos.php"><?= h(t('back')) ?></a>
  <header class="course-head">
    <span class="icon" aria-hidden="true"><?= h($course['icon']) ?></span>
    <h1><?= h($course['title'][LI]) ?></h1>
    <p class="lead"><?= h($course['desc'][LI]) ?></p>
    <p class="aud-row"><?php foreach ($course['audience'] as $a) echo aud_badge($a); ?></p>
    <form method="post" class="enroll">
      <?= csrf_field() ?>
<?php if (!$mine): ?>
      <input type="hidden" name="action" value="enroll">
      <button class="solid big"><?= h(t('enroll')) ?></button>
<?php elseif (!$mine['completed_at']): ?>
      <span class="badge ok">✓ <?= h(t('enrolled')) ?></span>
      <input type="hidden" name="action" value="complete">
      <button class="ghost"><?= h(t('complete')) ?></button>
<?php else: ?>
      <span class="badge ok">✓ <?= h(t('done')) ?></span>
      <a class="solid" href="reconocimiento.php?id=<?= h($course['id']) ?>"><?= h(t('get_cert')) ?></a>
<?php endif; ?>
    </form>
  </header>

  <section class="overview">
    <h2><?= h(t('overview')) ?></h2>
    <dl>
      <dt><?= h(t('duration')) ?></dt><dd><?= (int) $course['hours'] ?> <?= h(t('hours')) ?></dd>
      <dt><?= h(t('level')) ?></dt><dd><?= h(t('lvl_' . $course['level'])) ?></dd>
      <dt><?= h(t('format')) ?></dt><dd><?= h(t('format_v')) ?></dd>
      <dt><?= h(t('audience')) ?></dt><dd class="aud-row"><?php foreach ($course['audience'] as $a) echo aud_badge($a); ?></dd>
      <dt><?= h(t('prereq')) ?></dt><dd><?= h(t($course['level'] === 'basico' ? 'prereq_none' : 'prereq_some')) ?></dd>
      <dt><?= h(t('outcome')) ?></dt><dd><?= h(t('outcome_v')) ?></dd>
    </dl>
    <h3><?= h(t('goal')) ?></h3>
    <p><?= h($course['goal'][LI]) ?></p>
    <h3><?= h(t('topics')) ?></h3>
    <ol>
<?php foreach ($course['topics'][LI] as $topic): ?>
      <li><?= h($topic) ?></li>
<?php endforeach; ?>
    </ol>
  </section>

  <section>
    <h2><?= h(t('apply_title')) ?></h2>
    <div class="grid">
<?php foreach (['maestros', 'alumnos', 'general'] as $a): $on = in_array($a, $course['audience'], true); ?>
      <div class="card aud<?= $on ? ' on' : '' ?>" data-aud="<?= $a ?>">
        <h3><?= aud_badge($a) ?></h3>
        <p><?= h(t('apply_' . $a)) ?></p>
<?php if ($on): ?>
        <span class="note">★ <?= h(t('recommended')) ?></span>
<?php endif; ?>
      </div>
<?php endforeach; ?>
    </div>
  </section>
</article>
<?php endif;
page_end();
