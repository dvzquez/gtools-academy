<?php
// Reconocimiento imprimible (NO es una certificación oficial).
// Uso: reconocimiento.php?id=forms&name=Ana&date=2026-10-20&lang=es
declare(strict_types=1);
require __DIR__ . '/includes/data.php';

$lang = current_lang();
$i = $lang === 'en' ? 1 : 0;
$course = find_course(param('id'));
if ($course === null) {
    http_response_code(404);
    exit('Curso no encontrado / Course not found');
}

// Nombre de hasta 60 caracteres (con /u no depende de mbstring)
preg_match('/^.{0,60}/su', trim(param('name')), $m);
$name = $m[0] ?? '';
if ($name === '') {
    $name = $i ? 'Student' : 'Estudiante';
}
$date = DateTime::createFromFormat('Y-m-d', param('date')) ?: new DateTime();
$dateText = $i ? $date->format('F j, Y') : $date->format('d/m/Y');
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $i ? 'Recognition' : 'Reconocimiento' ?> | GTools Academy</title>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
  <style>
    * { box-sizing: border-box; }
    @page { size: A4 landscape; margin: 0; }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; gap: 12px; padding: 16px; background: #e9ecf2; color: #14213d; font-family: "Space Grotesk", system-ui, sans-serif; }
    .cert { width: min(980px, 94vw); aspect-ratio: 1.414; display: flex; flex-direction: column; justify-content: center; gap: 0.5em; padding: 5% 6%; text-align: center; background: #fff; border: 10px double #b8924a; }
    .cert p { margin: 0; }
    .cert h1 { margin: 0; font: 700 clamp(1.8rem, 5vw, 3.2rem) "Fraunces", Georgia, serif; letter-spacing: 0.04em; }
    .who { align-self: center; padding-bottom: 0.15em; border-bottom: 2px solid #b8924a; font: 600 clamp(1.6rem, 4.5vw, 2.8rem) "Fraunces", Georgia, serif; }
    .course { font-size: clamp(1.1rem, 3vw, 1.8rem); font-weight: 700; }
    .note { margin-top: 1em; font-size: 0.8rem; color: #5a6478; }
    button { padding: 10px 22px; border: 0; border-radius: 999px; background: #14213d; color: #f1dfae; font: inherit; cursor: pointer; }
    @media print { body { padding: 0; background: #fff; } .cert { width: 100vw; height: 100vh; aspect-ratio: auto; border-width: 12px; } .no-print { display: none; } }
  </style>
</head>
<body>
<main class="cert">
  <p>GTools Academy</p>
  <h1><?= $i ? 'Recognition' : 'Reconocimiento' ?></h1>
  <p><?= $i ? 'GTools Academy recognizes' : 'GTools Academy reconoce a' ?></p>
  <p class="who"><?= h($name) ?></p>
  <p><?= $i ? 'for completing the course' : 'por completar el curso' ?></p>
  <p class="course"><?= h($course['icon'] . ' ' . $course['title'][$i]) ?></p>
  <p><?= h($dateText) ?></p>
  <p class="note"><?= $i
      ? 'This recognition belongs to the GTools Academy project. It is not an official certification from Google or any institution.'
      : 'Este reconocimiento es del proyecto GTools Academy. No es una certificación oficial de Google ni de ninguna institución.' ?></p>
</main>
<button class="no-print" onclick="window.print()"><?= $i ? 'Print or save as PDF' : 'Imprimir o guardar como PDF' ?></button>
</body>
</html>
