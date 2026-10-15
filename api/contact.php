<?php
// Formulario de contacto: recibe un POST, valida en el servidor y guarda el mensaje
// en storage/messages.jsonl (una línea JSON por mensaje).
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function respond(int $status, array $body): void
{
    http_response_code($status);
    echo json_encode($body, JSON_UNESCAPED_UNICODE);
    exit;
}

// Valor POST como texto recortado (ignora arrays).
function field(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

//Cuenta caracteres (con /u no depende de mbstring; 0 si el UTF-8 es inválido).
function chars(string $text): int
{
    return (int) preg_match_all('/./su', $text);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['error' => 'method_not_allowed']);
}

// Trampa para bots: el campo "website" está oculto; si trae algo, se ignora el envío.
if (field('website') !== '') {
    respond(201, ['ok' => true]);
}

$name = field('name');
$email = field('email');
$role = field('role');
$message = field('message');
$lang = field('lang') === 'en' ? 'en' : 'es';

$errors = [];
if (chars($name) < 2 || chars($name) > 80) $errors[] = 'name';
if (strlen($email) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'email';
if (!in_array($role, ['maestro', 'alumno', 'otro'], true)) $errors[] = 'role';
if (chars($message) < 10 || chars($message) > 1000) $errors[] = 'message';
if ($errors) {
    respond(422, ['errors' => $errors]);
}

$dir = __DIR__ . '/../storage';
$file = $dir . '/messages.jsonl';
if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
    respond(500, ['error' => 'storage_unavailable']);
}
if (is_file($file) && filesize($file) > 512000) { // límite de ~500 KB contra abuso
    respond(503, ['error' => 'inbox_full']);
}

$entry = ['at' => date('c'), 'name' => $name, 'email' => $email, 'role' => $role, 'lang' => $lang, 'message' => $message];
if (file_put_contents($file, json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX) === false) {
    respond(500, ['error' => 'storage_unavailable']);
}

respond(201, ['ok' => true]);
