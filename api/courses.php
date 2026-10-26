<?php
// Se exponen los cursos como JSON y permite filtrar por categoría, nivel o público sin cambiar el catálogo original.
declare(strict_types=1);
require __DIR__ . '/../includes/data.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Lee el catálogo desde el mismo archivo que utiliza la página de cursos.
    $courses = load_courses();
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Aplica los filtros opcionales de categoría y nivel cuando vienen en la URL.
foreach (['category', 'level'] as $field) {
    $value = param($field);
    if ($value !== '') {
        $courses = array_filter($courses, fn(array $c): bool => $c[$field] === $value);
    }
}
// Aparecen solo los cursos que se dirigen al público solicitado, si se indicó uno.
$audience = param('audience');
if ($audience !== '') {
    $courses = array_filter($courses, fn(array $c): bool => in_array($audience, $c['audience'], true));
}

echo json_encode(array_values($courses), JSON_UNESCAPED_UNICODE);
