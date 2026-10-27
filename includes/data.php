<?php
declare(strict_types=1);

/**
 * Lee el catálogo general desde JSON y valida que la estructura raíz sea una lista.
 * La separación entre datos y plantilla permite actualizar los cursos sin duplicar HTML.
 */
function load_courses(): array
{
    $raw = @file_get_contents(__DIR__ . '/../data/courses.json');
    $data = $raw === false ? null : json_decode($raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('No se pudo leer data/courses.json');
    }
    return $data;
}

/**
 * Carga los temarios ampliados de los cursos que tienen documentación detallada.
 * Si el archivo no existe o contiene JSON inválido, devuelve un arreglo vacío para
 * que la ficha genérica siga funcionando sin romper el catálogo.
 */
function load_course_content(): array
{
    $path = __DIR__ . '/../data/course-content.json';
    $raw = @file_get_contents($path);
    $data = $raw === false ? null : json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Busca un curso por su identificador estable; null representa una ruta inexistente.
 */
function find_course(string $id): ?array
{
    foreach (load_courses() as $course) {
        if (($course['id'] ?? null) === $id) {
            return $course;
        }
    }
    return null;
}

/** Acepta únicamente parámetros GET escalares para evitar conversiones inesperadas. */
function param(string $key): string
{
    $value = $_GET[$key] ?? '';
    return is_string($value) ? $value : '';
}

/** Escapa texto UTF-8 antes de insertarlo en el HTML generado por PHP. */
function h(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}
