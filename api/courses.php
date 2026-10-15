<?php
// API JSON de cursos
// Filtros opcionales: ?category=populares&level=basico&audience=maestros
declare(strict_types=1);
require __DIR__ . '/../includes/data.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $courses = load_courses();
} catch (RuntimeException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    exit;
}

foreach (['category', 'level'] as $field) {
    $value = param($field);
    if ($value !== '') {
        $courses = array_filter($courses, fn(array $c): bool => $c[$field] === $value);
    }
}
$audience = param('audience');
if ($audience !== '') {
    $courses = array_filter($courses, fn(array $c): bool => in_array($audience, $c['audience'], true));
}

echo json_encode(array_values($courses), JSON_UNESCAPED_UNICODE);
