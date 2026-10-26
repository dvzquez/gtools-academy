<?php
// Aquí preparo la sesión, el idioma, la base de datos, las protecciones y la plantilla compartida del sitio.
declare(strict_types=1);
session_start();
require_once __DIR__ . '/data.php';

const LANGS = ['es', 'en', 'fr'];
$wanted = param('lang');
if (in_array($wanted, LANGS, true)) {
    setcookie('lang', $wanted, time() + 31536000, '/');
    $_COOKIE['lang'] = $wanted;
}
define('LANG', in_array($_COOKIE['lang'] ?? '', LANGS, true) ? $_COOKIE['lang'] : 'es');
define('LI', (int) array_search(LANG, LANGS, true)); // índice: 0 = es, 1 = en, 2 = fr
$TEXT = require __DIR__ . '/i18n.php';
$CONFIG = require __DIR__ . '/config.php';

/** Aquí recupero el texto traducido que corresponde al idioma activo. */
function t(string $key): string
{
    global $TEXT;
    return $TEXT[$key][LI] ?? $key;
}

/** Sola conexión PDO y creación de las tablas necesarias si todavía no existen. */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . __DIR__ . '/../database/gtools.sqlite', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
    }
    return $pdo;
}

/** Busco a la persona autenticada y guardo el resultado durante esta petición. */
function user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (isset($_SESSION['uid'])) {
            $st = db()->prepare('SELECT * FROM users WHERE id = ?');
            $st->execute([$_SESSION['uid']]);
            $user = $st->fetch() ?: null;
        }
    }
    return $user;
}

/** Recupero las inscripciones de la sesión y las organizo por identificador del curso. */
function my_enrollments(): array
{
    $u = user();
    if (!$u) {
        return [];
    }
    $st = db()->prepare('SELECT course_id, enrolled_at, completed_at FROM enrollments WHERE user_id = ? ORDER BY enrolled_at DESC');
    $st->execute([$u['id']]);
    return array_column($st->fetchAll(), null, 'course_id');
}

function post(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/** Aquí cuento caracteres UTF-8 sin depender de mbstring para validar los formularios. */
function len(string $text): int
{
    return (int) preg_match_all('/./su', $text);
}

function go(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function csrf_field(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function csrf_ok(): bool
{
    return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], post('csrf'));
}

function flash_set(string $key): void
{
    $_SESSION['flash'] = $key;
}

function flash_show(): void
{
    if (isset($_SESSION['flash'])) {
        echo '<p class="flash" role="status">' . h(t($_SESSION['flash'])) . '</p>';
        unset($_SESSION['flash']);
    }
}

function lang_url(string $lang): string
{
    return '?' . http_build_query(array_merge($_GET, ['lang' => $lang]));
}

/** Aquí genero la etiqueta traducida para identificar a qué público se dirige un curso. */
function aud_badge(string $aud): string
{
    return '<span class="badge" data-aud="' . h($aud) . '">' . h(t('aud_' . $aud)) . '</span>';
}

function page_start(string $title, string $page): void
{
    $u = user();
    $nav = ['index' => 'nav_home', 'cursos' => 'nav_courses', 'contacto' => 'nav_contact', 'acceso' => 'nav_access'];
    ?>
<!DOCTYPE html>
<html lang="<?= LANG ?>" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?> | GTools Academy</title>
  <meta name="description" content="<?= h(t('tagline')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <!-- Se carga Faculty Glyphic, una fuente legible que se usó en los títulos y controles de toda la interfaz. -->
  <link href="https://fonts.googleapis.com/css2?family=Faculty+Glyphic&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <script>try { document.documentElement.dataset.theme = localStorage.getItem('gt-theme') || 'dark'; } catch (e) {}</script>
  <script src="js/app.js" defer></script>
</head>
<body>
<!-- Espacio reservado para colocar el icono definitivo de GTools Academy delante del nombre. -->
<header class="top">
  <a class="brand" href="index.php"><span class="brand-icon-slot" aria-hidden="true"></span><span>GTools Academy</span></a>
  <nav aria-label="Menu">
<?php foreach ($nav as $file => $key): ?>
    <a href="<?= $file ?>.php"<?= $file === $page ? ' aria-current="page"' : '' ?>><?= h(t($key)) ?></a>
<?php endforeach; ?>
  </nav>
  <div class="tools">
    <!-- Aquí uso un solo selector para cambiar entre español, inglés y francés sin ocupar tres botones. -->
    <select id="language" class="language-select" aria-label="<?= h(t('language')) ?>">
      <option value="<?= h(lang_url('es')) ?>"<?= LANG === 'es' ? ' selected' : '' ?>>Español</option>
      <option value="<?= h(lang_url('en')) ?>"<?= LANG === 'en' ? ' selected' : '' ?>>English</option>
      <option value="<?= h(lang_url('fr')) ?>"<?= LANG === 'fr' ? ' selected' : '' ?>>Français</option>
    </select>
    <button id="theme" type="button" class="ghost" data-dark="<?= h(t('theme_dark')) ?>" data-light="<?= h(t('theme_light')) ?>"></button>
<?php if ($u): ?>
    <a class="solid" href="acceso.php"><?= h(sprintf(t('hi'), $u['first_name'])) ?></a>
<?php endif; ?>
  </div>
</header>
<main>
<?php
}

function page_end(): void
{
    global $CONFIG;
    $url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ($_SERVER['REQUEST_URI'] ?? '/');
    $link = rawurlencode($url);
    $text = rawurlencode('GTools Academy: ' . t('tagline'));
    ?>
</main>
<!-- Aquí cierro cada página con enlaces útiles, datos de contacto y acciones para compartir el programa. -->
<footer class="foot">
  <div class="foot-grid">
    <div>
      <p class="brand">GTools Academy</p>
      <p><?= h(t('tagline')) ?></p>
    </div>
    <div>
      <h3><?= h(t('foot_explore')) ?></h3>
      <ul>
        <li><a href="index.php"><?= h(t('nav_home')) ?></a></li>
        <li><a href="cursos.php"><?= h(t('nav_courses')) ?></a></li>
        <li><a href="contacto.php"><?= h(t('nav_contact')) ?></a></li>
        <li><a href="acceso.php"><?= h(t('nav_access')) ?></a></li>
      </ul>
    </div>
    <div>
      <h3><?= h(t('nav_contact')) ?></h3>
      <ul>
        <li><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></li>
        <li><?= h($CONFIG['phone']) ?></li>
        <li><?= h($CONFIG['address']) ?></li>
        <li><?= h($CONFIG['hours'][LI]) ?></li>
<?php foreach ($CONFIG['social'] as $name => $href): ?>
        <li><a href="<?= h($href) ?>" target="_blank" rel="noopener"><?= h($name) ?></a></li>
<?php endforeach; ?>
      </ul>
    </div>
    <div class="share">
      <h3><?= h(t('share')) ?></h3>
      <ul>
        <li><button type="button" id="copy" data-done="<?= h(t('copied')) ?>"><?= h(t('copy')) ?></button></li>
        <li><a href="https://wa.me/?text=<?= $text ?>%20<?= $link ?>" target="_blank" rel="noopener">WhatsApp</a></li>
        <li><a href="https://twitter.com/intent/tweet?text=<?= $text ?>&amp;url=<?= $link ?>" target="_blank" rel="noopener">X</a></li>
        <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?= $link ?>" target="_blank" rel="noopener">Facebook</a></li>
      </ul>
    </div>
  </div>
  <p class="legal"><?= h(t('legal')) ?></p>
</footer>
</body>
</html>
<?php
}
