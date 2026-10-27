<?php
// Endpoint de solo lectura que serializa el catálogo y admite filtros opcionales por categoría, nivel y público.
declare(strict_types=1);
require __DIR__ . '/../includes/data.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // La API utiliza la misma fuente JSON del catálogo para mantener una única definición de cursos.
    $courses = load_courses();
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

// Los parámetros de categoría y nivel se aplican solo cuando están presentes y son válidos.
foreach (['category', 'level'] as $field) {
    $value = param($field);
    if ($value !== '') {
        $courses = array_filter($courses, fn(array $c): bool => $c[$field] === $value);
    }
}
// El filtro de público compara el identificador solicitado con la lista de audiencias de cada curso.
$audience = param('audience');
if ($audience !== '') {
    $courses = array_filter($courses, fn(array $c): bool => in_array($audience, $c['audience'], true));
}

echo json_encode(array_values($courses), JSON_UNESCAPED_UNICODE);
