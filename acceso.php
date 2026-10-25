<?php
// Registro e inicio de sesión (usuarios guardados en SQL, contraseñas con password_hash)
require __DIR__ . '/includes/app.php';

// Aquí limito la URL de retorno a páginas internas antes de procesar el registro o el acceso.
$next = param('next'); // a dónde volver después de entrar (solo páginas internas)
if (!preg_match('/^[a-z-]+\.php(\?[\w=&%.-]*)?$/i', $next)) {
    $next = 'acceso.php';
}
$err = '';
$f = array_fill_keys(['first_name', 'last_name', 'phone', 'role', 'email', 'level'], '');

// Aquí separo cerrar sesión, crear una cuenta e iniciar sesión según la acción enviada por el formulario.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_ok()) {
    $action = post('action');
    if ($action === 'logout') {
        unset($_SESSION['uid']);
        session_regenerate_id(true);
        go('index.php');
    }
    if ($action === 'register') {
        foreach ($f as $k => $_) {
            $f[$k] = post($k);
        }
        $pass = post('password');
        if (len($f['first_name']) < 2 || len($f['first_name']) > 60 || len($f['last_name']) < 2 || len($f['last_name']) > 60
            || !preg_match('/^\+?[0-9 ()-]{7,20}$/', $f['phone'])
            || !in_array($f['role'], ['maestro', 'alumno', 'general'], true)
            || !filter_var($f['email'], FILTER_VALIDATE_EMAIL) || strlen($f['email']) > 120
            || !in_array($f['level'], ['basico', 'intermedio', 'avanzado'], true) || len($pass) < 8) {
            $err = 'err_form';
        } else {
            try {
                db()->prepare('INSERT INTO users (first_name, last_name, phone, role, email, level, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?)')
                    ->execute(array_merge(array_values($f), [password_hash($pass, PASSWORD_DEFAULT)]));
                $_SESSION['uid'] = (int) db()->lastInsertId();
                session_regenerate_id(true);
                flash_set('ok_registered');
                go($next);
            } catch (PDOException $e) {
                $err = $e->getCode() === '23000' ? 'err_dup' : 'err_form'; // 23000 = correo repetido (UNIQUE)
            }
        }
    }
    if ($action === 'login') {
        $st = db()->prepare('SELECT id, password_hash FROM users WHERE email = ?');
        $st->execute([post('email')]);
        $row = $st->fetch();
        if ($row && password_verify(post('password'), $row['password_hash'])) {
            $_SESSION['uid'] = (int) $row['id'];
            session_regenerate_id(true);
            go($next);
        }
        $err = 'err_login';
    }
}

page_start(t('access_title'), 'acceso');
$u = user();
?>
<!-- Aquí introduzco el acceso con una composición propia y conservo la lógica de registro e inicio de sesión que guarda la base de datos. -->
<section class="access-page">
<?php if (!$u): ?>
  <div class="access-intro">
    <div>
      <p class="eyebrow"><?= h(t('access_eyebrow')) ?></p>
      <h1><?= h(t('access_title')) ?></h1>
      <p><?= h(t('access_intro')) ?></p>
    </div>
    <div class="access-art" aria-hidden="true"></div>
  </div>
<?php else: ?>
  <h1><?= h(t('access_title')) ?></h1>
<?php endif; ?>
  <?php flash_show(); ?>
<?php if ($err): ?>
  <p class="flash err" role="alert"><?= h(t($err)) ?></p>
<?php endif; ?>
<?php if ($u): $mine = my_enrollments(); $byId = array_column(load_courses(), null, 'id'); ?>
  <div class="card profile-card">
    <h2><?= h($u['first_name'] . ' ' . $u['last_name']) ?></h2>
    <p class="note"><?= h($u['email']) ?>, <?= h(t('role_' . $u['role'])) ?>, <?= h(t('lvl_' . $u['level'])) ?></p>
    <h3><?= h(t('my_courses')) ?></h3>
    <ul>
<?php foreach ($mine as $id => $row): if (!isset($byId[$id])) continue; ?>
      <li><a href="curso.php?id=<?= h($id) ?>"><?= h($byId[$id]['title'][LI]) ?></a> <?= $row['completed_at'] ? '✓' : '' ?></li>
<?php endforeach; ?>
    </ul>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button class="ghost"><?= h(t('logout')) ?></button></form>
  </div>
<?php else: ?>
  <div class="access-grid">
    <div class="access-form-card">
      <p class="eyebrow"><?= h(t('access_register_label')) ?></p>
      <h2><?= h(t('cta_join')) ?></h2>
      <p class="note"><?= h(t('access_register_note')) ?></p>
      <form method="post" action="?next=<?= h(rawurlencode($next)) ?>" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="register">
      <label><span><?= h(t('f_first')) ?></span><input name="first_name" required minlength="2" maxlength="60" autocomplete="given-name" value="<?= h($f['first_name']) ?>"></label>
      <label><span><?= h(t('f_last')) ?></span><input name="last_name" required minlength="2" maxlength="60" autocomplete="family-name" value="<?= h($f['last_name']) ?>"></label>
      <label><span><?= h(t('f_phone')) ?></span><input name="phone" type="tel" required maxlength="20" autocomplete="tel" value="<?= h($f['phone']) ?>"></label>
      <label><span><?= h(t('f_role')) ?></span>
        <select name="role" required>
<?php foreach (['maestro', 'alumno', 'general'] as $r): ?>
          <option value="<?= $r ?>"<?= $f['role'] === $r ? ' selected' : '' ?>><?= h(t('role_' . $r)) ?></option>
<?php endforeach; ?>
        </select>
      </label>
      <label><span><?= h(t('lbl_email')) ?></span><input name="email" type="email" required maxlength="120" autocomplete="email" value="<?= h($f['email']) ?>"></label>
      <label><span><?= h(t('level')) ?></span>
        <select name="level" required>
<?php foreach (['basico', 'intermedio', 'avanzado'] as $l): ?>
          <option value="<?= $l ?>"<?= $f['level'] === $l ? ' selected' : '' ?>><?= h(t('lvl_' . $l)) ?></option>
<?php endforeach; ?>
        </select>
      </label>
      <label><span><?= h(t('f_pass')) ?></span><input name="password" type="password" required minlength="8" autocomplete="new-password"></label>
        <button class="solid"><?= h(t('cta_join')) ?></button>
      </form>
    </div>
    <div class="access-form-card">
      <p class="eyebrow"><?= h(t('access_login_label')) ?></p>
      <h2><?= h(t('login')) ?></h2>
      <p class="note"><?= h(t('access_login_note')) ?></p>
      <form method="post" action="?next=<?= h(rawurlencode($next)) ?>" class="form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="login">
      <label><span><?= h(t('lbl_email')) ?></span><input name="email" type="email" required autocomplete="email"></label>
      <label><span><?= h(t('f_pass')) ?></span><input name="password" type="password" required autocomplete="current-password"></label>
        <button class="solid"><?= h(t('login')) ?></button>
      </form>
    </div>
  </div>
<?php endif; ?>
</section>
<?php page_end();
