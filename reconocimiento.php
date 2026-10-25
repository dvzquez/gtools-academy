<?php
// Reconocimiento imprimible (NO es una certificación oficial). Solo para quien completó el curso.
require __DIR__ . '/includes/app.php';

$course = find_course(param('id'));
$u = user();
$row = false;
if ($course && $u) {
    $st = db()->prepare('SELECT completed_at FROM enrollments WHERE user_id = ? AND course_id = ? AND completed_at IS NOT NULL');
    $st->execute([$u['id'], $course['id']]);
    $row = $st->fetch();
}
if (!$row) {
    go($course ? 'curso.php?id=' . rawurlencode($course['id']) : 'cursos.php');
}
$date = new DateTime($row['completed_at']);
$dateText = LANG === 'en' ? $date->format('F j, Y') : $date->format('d/m/Y');
?>
<!DOCTYPE html>
<html lang="<?= LANG ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h(t('cert_title')) ?> | GTools Academy</title>
  <!-- Aquí reutilizo la fuente principal de la academia también en el reconocimiento imprimible. -->
  <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
  <style>
    /* Aquí mantengo el diseño del reconocimiento dentro de la identidad visual de la versión 4.0. */
    * { box-sizing: border-box; }
    @page { size: A4 landscape; margin: 0; }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; gap: 12px; padding: 16px; background: #0a1b35; color: #f3f6f8; font-family: "Faculty Glyphic", system-ui, sans-serif; }
    .cert { width: min(980px, 94vw); aspect-ratio: 1.414; display: flex; flex-direction: column; justify-content: center; gap: .6em; padding: 5% 6%; text-align: center; background: #102747; border: 8px double #215EBB; outline: 1px solid #AFCEE3; outline-offset: -18px; }
    .cert p { margin: 0; }
    .cert h1 { margin: 0; font: 700 clamp(1.8rem, 5vw, 3.2rem) "Faculty Glyphic", system-ui, sans-serif; letter-spacing: .025em; color: #AFCEE3; }
    .who { align-self: center; padding-bottom: .15em; border-bottom: 3px solid #CE6C37; font: 700 clamp(1.6rem, 4.5vw, 2.8rem) "Faculty Glyphic", system-ui, sans-serif; }
    .course { font-size: clamp(1.1rem, 3vw, 1.8rem); font-weight: 700; }
    .note { margin-top: 1em; font-size: .8rem; color: #AFCEE3; }
    button { padding: 10px 22px; border: 1px solid #215EBB; border-radius: 999px; background: #215EBB; color: #fff; font: inherit; cursor: pointer; }
    @media print {
      body { padding: 0; background: #fff; color: #0E2859; }
      .cert { width: 100vw; height: 100vh; aspect-ratio: auto; background: #fff; border-color: #215EBB; outline-color: #AFCEE3; }
      .cert h1 { color: #0E2859; }
      .who { border-color: #CE6C37; }
      .note { color: #53677c; }
      .no-print { display: none; }
    }
  </style>
</head>
<body>
<!-- Aquí muestro el nombre de quien completó el curso, el curso y la fecha; el texto aclara que no es una certificación oficial. -->
<main class="cert">
  <p>GTools Academy</p>
  <h1><?= h(t('cert_title')) ?></h1>
  <p><?= h(t('cert_to')) ?></p>
  <p class="who"><?= h($u['first_name'] . ' ' . $u['last_name']) ?></p>
  <p><?= h(t('cert_for')) ?></p>
  <p class="course"><?= h($course['icon'] . ' ' . $course['title'][LI]) ?></p>
  <p><?= h($dateText) ?></p>
  <p class="note"><?= h(t('not_official')) ?></p>
</main>
<button class="no-print" onclick="window.print()"><?= h(t('cert_print')) ?></button>
</body>
</html>
