<?php
declare(strict_types=1);

require __DIR__ . '/includes/app.php';

// El identificador determina el curso y el archivo JSON opcional aporta el temario extendido.
$course = find_course(param('id'));
$allContent = $course ? load_course_content() : [];
$detail = $course ? ($allContent[$course['id']] ?? null) : null;
$u = user();
$mine = $course ? (my_enrollments()[$course['id']] ?? null) : null;

// Las mutaciones de inscripción y finalización solo se procesan con POST y token CSRF válido.
if ($course && $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    if (!$u) {
        go('acceso.php?next=' . rawurlencode('curso.php?id=' . $course['id']));
    }

    if (post('action') === 'enroll') {
        // INSERT OR IGNORE evita duplicar una inscripción en SQLite.
        db()->prepare('INSERT OR IGNORE INTO enrollments (user_id, course_id) VALUES (?, ?)')
            ->execute([$u['id'], $course['id']]);
    } elseif (post('action') === 'complete') {
        // COALESCE conserva la fecha original si el registro ya estaba completado.
        db()->prepare('UPDATE enrollments SET completed_at = COALESCE(completed_at, CURRENT_TIMESTAMP) WHERE user_id = ? AND course_id = ?')
            ->execute([$u['id'], $course['id']]);
    }

    go('curso.php?id=' . rawurlencode($course['id']));
}

if (!$course) {
    http_response_code(404);
}
page_start($course ? $course['title'][LI] : t('notfound'), 'cursos');

// La imagen se obtiene de los datos del curso; los cursos sin portada usan la ilustración educativa general.
$courseImage = $course ? (string) ($course['image'] ?? 'assets/img/aula.svg') : 'assets/img/aula.svg';
$courseAlt = $detail ? $detail['imageAlt'][LI] : t('course_visual');
?>
<?php if (!$course): ?>
  <section class="course-not-found">
    <p class="empty"><?= h(t('notfound')) ?></p>
    <a class="ghost" href="cursos.php"><?= h(t('back')) ?></a>
  </section>
<?php else: ?>
<article class="course-page">
  <a class="note course-back" href="cursos.php">← <?= h(t('back')) ?></a>

  <!-- Encabezado editorial común: descripción, públicos, acciones de inscripción e ilustración representativa. -->
  <section class="course-detail-hero">
    <div class="course-detail-copy">
      <p class="eyebrow"><?= h(t('course_program')) ?></p>
      <h1><?= h($course['title'][LI]) ?></h1>
      <p class="lead"><?= h($course['desc'][LI]) ?></p>
      <p class="aud-row"><?php foreach ($course['audience'] as $a) echo aud_badge($a); ?></p>

      <form method="post" class="enroll course-enroll">
        <?= csrf_field() ?>
<?php if (!$mine): ?>
        <input type="hidden" name="action" value="enroll">
        <button class="solid big"><?= h(t('enroll')) ?></button>
<?php elseif (empty($mine['completed_at'])): ?>
        <span class="badge ok">✓ <?= h(t('enrolled')) ?></span>
        <input type="hidden" name="action" value="complete">
        <button class="ghost"><?= h(t('complete')) ?></button>
<?php else: ?>
        <span class="badge ok">✓ <?= h(t('done')) ?></span>
        <a class="solid" href="reconocimiento.php?id=<?= h($course['id']) ?>"><?= h(t('get_cert')) ?></a>
<?php endif; ?>
      </form>
    </div>

    <figure class="course-cover">
      <img src="<?= h($courseImage) ?>" alt="<?= h($courseAlt) ?>" width="720" height="430" fetchpriority="high">
      <figcaption><?= h(t('course_visual')) ?></figcaption>
    </figure>
  </section>

  <!-- Datos de referencia rápida: nivel, duración, modalidad y público se agrupan en componentes consistentes. -->
  <section class="course-facts" aria-label="<?= h(t('overview')) ?>">
    <div class="fact-card"><span><?= h(t('level')) ?></span><strong><?= h(t('lvl_' . $course['level'])) ?></strong></div>
    <div class="fact-card"><span><?= h(t('duration')) ?></span><strong><?= (int) $course['hours'] ?> <?= h(t('hours')) ?></strong></div>
    <div class="fact-card"><span><?= h(t('format')) ?></span><strong><?= h($detail ? $detail['modality'][LI] : t('format_v')) ?></strong></div>
    <div class="fact-card"><span><?= h($detail ? t('modules_label') : t('topics')) ?></span><strong><?= $detail ? count($detail['modules']) . ' ' . h(t('module_count')) : count($course['topics'][LI]) . ' ' . h(t('topic_count')) ?></strong></div>
  </section>

<?php if ($detail): ?>
  <!-- Objetivos y descripción usan columnas independientes para que el temario sea legible y escaneable. -->
  <section class="course-info-grid">
    <div class="detail-panel">
      <p class="eyebrow">01</p>
      <h2><?= h(t('course_description')) ?></h2>
      <p><?= h($detail['fullDescription'][LI]) ?></p>
      <h3><?= h(t('goal')) ?></h3>
      <p><?= h($course['goal'][LI]) ?></p>
    </div>
    <div class="detail-panel">
      <p class="eyebrow">02</p>
      <h2><?= h(t('learning_objectives')) ?></h2>
      <ol class="objective-list">
<?php foreach ($detail['objectives'][LI] as $objective): ?>
        <li><?= h($objective) ?></li>
<?php endforeach; ?>
      </ol>
    </div>
  </section>

  <!-- Los prerrequisitos se muestran antes de los módulos para dejar claras las competencias de entrada. -->
  <section class="course-requirements">
    <div class="section-heading">
      <div><p class="eyebrow">03</p><h2><?= h(t('requirements')) ?></h2></div>
      <span class="badge"><?= h(t('practical_course')) ?></span>
    </div>
    <ul class="check-list">
<?php foreach ($detail['prerequisites'][LI] as $requirement): ?>
      <li><?= h($requirement) ?></li>
<?php endforeach; ?>
    </ul>
  </section>

  <!-- Cada módulo mantiene la misma estructura: número, duración, ilustración contextual y lista de contenidos. -->
  <section class="course-syllabus" id="temario">
    <div class="section-heading">
      <div><p class="eyebrow">04</p><h2><?= h(t('detailed_syllabus')) ?></h2></div>
      <span class="badge"><?= (int) $course['hours'] ?> <?= h(t('hours')) ?></span>
    </div>
<!-- La ficha puede distinguir contenido validado desde un temario PDF de una propuesta editorial basada en el catálogo. -->
<?php if (!empty($detail['contentNote'])): ?>
    <aside class="syllabus-source-note" role="note">
      <span class="source-note-mark" aria-hidden="true">i</span>
      <p><?= h($detail['contentNote'][LI]) ?></p>
    </aside>
<?php endif; ?>
<?php if (!empty($detail['contentNote'])): ?>
    <aside class="syllabus-source-note" role="note">
      <span class="source-note-mark" aria-hidden="true">i</span>
      <p><?= h($detail['contentNote'][LI]) ?></p>
    </aside>
<?php endif; ?>
    <nav class="module-index" aria-label="<?= h(t('module_index')) ?>">
<?php foreach ($detail['modules'] as $index => $module): ?>
      <a href="#modulo-<?= $index + 1 ?>"><span><?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?></span><strong><?= h($module['title'][LI]) ?></strong><small><?= h($module['duration'][LI]) ?></small></a>
<?php endforeach; ?>
    </nav>
    <div class="module-list">
<?php foreach ($detail['modules'] as $index => $module): ?>
      <article class="module-card" id="modulo-<?= $index + 1 ?>">
        <figure class="module-visual">
          <img src="<?= h($module['visual']) ?>" alt="<?= h($module['visualAlt'][LI]) ?>" width="720" height="430" loading="lazy">
          <figcaption><?= h(t('module_label')) ?> <?= $index + 1 ?></figcaption>
        </figure>
        <div class="module-content">
          <div class="module-meta"><span><?= h(t('module_label')) ?> <?= $index + 1 ?></span><span class="badge"><?= h($module['duration'][LI]) ?></span></div>
          <h3><?= h($module['title'][LI]) ?></h3>
          <ul class="module-topics">
<?php foreach ($module['topics'][LI] as $topic): ?>
            <li><?= h($topic) ?></li>
<?php endforeach; ?>
          </ul>
        </div>
      </article>
<?php endforeach; ?>
    </div>
  </section>

<?php if (!empty($detail['referenceRows'])): ?>
  <!-- La tabla de consulta sintetiza los permisos, atajos o funciones clave definidos en cada temario. -->
  <section class="reference-section">
    <div class="section-heading">
      <div><p class="eyebrow">05</p><h2><?= h($detail['referenceTitle'][LI]) ?></h2></div>
    </div>
    <div class="table-scroll">
      <table class="reference-table">
        <thead><tr>
<?php foreach ($detail['referenceColumns'][LI] as $column): ?>
          <th scope="col"><?= h($column) ?></th>
<?php endforeach; ?>
        </tr></thead>
        <tbody>
<?php foreach ($detail['referenceRows'] as $row): ?>
          <tr>
<?php foreach ($row['cells'] as $cell): ?>
            <td><?= h($cell[LI]) ?></td>
<?php endforeach; ?>
          </tr>
<?php endforeach; ?>
        </tbody>
      </table>
    </div>
<?php if (!empty($detail['referenceNote'])): ?>
    <p class="note reference-note"><?= h($detail['referenceNote'][LI]) ?></p>
<?php endif; ?>
  </section>
<?php endif; ?>

  <!-- La metodología, los resultados y los recursos cierran el temario con expectativas explícitas de aprendizaje. -->
  <section class="course-outcomes-grid">
    <div class="detail-panel">
      <p class="eyebrow">06</p>
      <h2><?= h(t('methodology')) ?></h2>
      <p><?= h($detail['methodology'][LI]) ?></p>
      <p class="visual-note"><span aria-hidden="true">✦</span> <?= h(t('no_video_note')) ?></p>
    </div>
    <div class="detail-panel">
      <p class="eyebrow">07</p>
      <h2><?= h(t('expected_results')) ?></h2>
      <ul class="check-list">
<?php foreach ($detail['outcomes'][LI] as $outcome): ?>
        <li><?= h($outcome) ?></li>
<?php endforeach; ?>
      </ul>
    </div>
    <div class="detail-panel materials-panel">
      <p class="eyebrow">08</p>
      <h2><?= h(t('included_materials')) ?></h2>
      <ul class="check-list">
<?php foreach ($detail['materials'][LI] as $material): ?>
        <li><?= h($material) ?></li>
<?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php else: ?>
  <!-- Para los cursos sin PDF, solo se presentan datos ya documentados; el contenido pendiente se señala claramente. -->
  <section class="course-info-grid">
    <div class="detail-panel">
      <p class="eyebrow">01</p>
      <h2><?= h(t('goal')) ?></h2>
      <p><?= h($course['goal'][LI]) ?></p>
      <p class="visual-note"><?= h(t('program_soon')) ?></p>
    </div>
    <div class="detail-panel">
      <p class="eyebrow">02</p>
      <h2><?= h(t('topics')) ?></h2>
      <ul class="module-topics">
<?php foreach ($course['topics'][LI] as $topic): ?>
        <li><?= h($topic) ?></li>
<?php endforeach; ?>
      </ul>
    </div>
  </section>
<?php endif; ?>

  <!-- Las tarjetas de aplicación relacionan los públicos definidos por el catálogo con sus usos más frecuentes. -->
  <section class="course-application">
    <div class="section-heading">
      <div><p class="eyebrow"><?= $detail ? '09' : '03' ?></p><h2><?= h(t('apply_title')) ?></h2></div>
    </div>
    <div class="grid">
<?php foreach (['maestros', 'alumnos', 'general'] as $a): $on = in_array($a, $course['audience'], true); ?>
      <div class="card aud<?= $on ? ' on' : '' ?>" data-aud="<?= h($a) ?>">
        <h3><?= aud_badge($a) ?></h3>
        <p><?= h(t('apply_' . $a)) ?></p>
<?php if ($on): ?><span class="note">★ <?= h(t('recommended')) ?></span><?php endif; ?>
      </div>
<?php endforeach; ?>
    </div>
  </section>
</article>
<?php endif; ?>
<?php page_end(); ?>
