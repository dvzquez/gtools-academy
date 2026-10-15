<?php
// Página de un curso renderizada en el servidor: curso.php?id=forms&lang=es
declare(strict_types=1);
require __DIR__ . '/includes/data.php';

$lang = current_lang();
$i = $lang === 'en' ? 1 : 0;
$other = $i ? 'es' : 'en';
$course = find_course(param('id'));
if ($course === null) {
    http_response_code(404);
}

$ui = [
    'back' => ['Volver a los cursos', 'Back to courses'],
    'goal' => ['Objetivo', 'Goal'],
    'topics' => ['Temas', 'Topics'],
    'for' => ['Ideal para', 'Great for'],
    'hours' => ['horas', 'hours'],
    'none' => ['Curso no encontrado.', 'Course not found.'],
];
$levels = ['basico' => ['Básico', 'Beginner'], 'intermedio' => ['Intermedio', 'Intermediate'], 'avanzado' => ['Avanzado', 'Advanced']];
$audiences = ['maestros' => ['Maestros', 'Teachers'], 'alumnos' => ['Alumnos', 'Students'], 'general' => ['Público general', 'General public']];
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $course ? h($course['title'][$i]) . ' | ' : '' ?>GTools Academy</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <script>try { document.documentElement.dataset.theme = JSON.parse(localStorage.getItem('theme')) || 'dark'; } catch (e) {}</script>
</head>
<body>
<header class="top">
  <a class="brand" href="index.html">GTools Academy</a>
  <a class="ghost" href="index.html#cursos"><?= h($ui['back'][$i]) ?></a>
  <?php if ($course): ?><a class="ghost" href="?id=<?= h($course['id']) ?>&amp;lang=<?= $other ?>"><?= strtoupper($other) ?></a><?php endif; ?>
</header>
<main>
<?php if ($course === null): ?>
  <p class="empty"><?= h($ui['none'][$i]) ?></p>
<?php else: ?>
  <article class="course-page">
    <span class="icon" aria-hidden="true"><?= h($course['icon']) ?></span>
    <h1><?= h($course['title'][$i]) ?></h1>
    <p class="lead"><?= h($course['desc'][$i]) ?></p>
    <p class="meta">
      <span class="badge"><?= h($levels[$course['level']][$i]) ?></span>
      <span class="badge"><?= (int) $course['hours'] ?> <?= h($ui['hours'][$i]) ?></span>
    </p>
    <h2><?= h($ui['goal'][$i]) ?></h2>
    <p><?= h($course['goal'][$i]) ?></p>
    <h2><?= h($ui['topics'][$i]) ?></h2>
    <ul>
      <?php foreach ($course['topics'][$i] as $topic): ?><li><?= h($topic) ?></li><?php endforeach; ?>
    </ul>
    <h2><?= h($ui['for'][$i]) ?></h2>
    <p class="meta"><?php foreach ($course['audience'] as $a): ?><span class="badge"><?= h($audiences[$a][$i]) ?></span><?php endforeach; ?></p>
  </article>
<?php endif; ?>
</main>
</body>
</html>
