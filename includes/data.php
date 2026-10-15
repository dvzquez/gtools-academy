<?php
declare(strict_types=1);

//Lee data/courses.json y devuelve la lista de cursos.
function load_courses(): array
{
    $raw = @file_get_contents(__DIR__ . '/../data/courses.json');
    $data = $raw === false ? null : json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('No se pudo leer data/courses.json');
    }
    return $data;
}

function find_course(string $id): ?array
{
    foreach (load_courses() as $course) {
        if ($course['id'] === $id) {
            return $course;
        }
    }
    return null;
}

//Parámetro GET como texto (ignora arrays como ?x[]=1).
function param(string $key): string
{
    $value = $_GET[$key] ?? '';
    return is_string($value) ? $value : '';
}

function current_lang(): string
{
    return param('lang') === 'en' ? 'en' : 'es';
}

//Escapa texto para HTML (evita XSS).
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
