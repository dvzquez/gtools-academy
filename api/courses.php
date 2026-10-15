<?php
// Fase 2: API mínima en PHP. Devuelve los cursos en JSON, con filtro opcional ?category=populares
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

$courses = json_decode(file_get_contents(__DIR__ . '/../data/courses.json'), true);
$category = $_GET['category'] ?? null;

if ($category) {
    $courses = array_values(array_filter($courses, fn($c) => $c['category'] === $category));
}
echo json_encode($courses, JSON_UNESCAPED_UNICODE);
