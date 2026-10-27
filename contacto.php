<?php
require __DIR__ . '/includes/app.php';

// Los valores recibidos se conservan temporalmente para volver a mostrarlos si una validación falla.
$err = '';
$f = ['name' => '', 'email' => '', 'topic' => 'duda', 'message' => ''];
// La validación comprueba el token CSRF, el campo honeypot y los datos antes de ejecutar una sentencia preparada.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($f as $k => $_) {
        $f[$k] = post($k);
    }
    if (!csrf_ok() || post('website') !== '') {
        go('contacto.php'); // Un token inválido o el honeypot completado bloquea el envío.
    }
    if (len($f['name']) < 2 || len($f['name']) > 80 || !filter_var($f['email'], FILTER_VALIDATE_EMAIL)
        || !in_array($f['topic'], ['duda', 'propuesta'], true) || len($f['message']) < 10 || len($f['message']) > 1000) {
        $err = 'err_form';
    } else {
        db()->prepare('INSERT INTO messages (name, email, topic, message) VALUES (?, ?, ?, ?)')
            ->execute([$f['name'], $f['email'], $f['topic'], $f['message']]);
        flash_set('sent_ok');
        go('contacto.php');
    }
}
page_start(t('nav_contact'), 'contacto');
?>
<!-- El formulario y los datos de soporte comparten una composición de centro de ayuda. -->
<section class="contact-page">
  <p class="eyebrow"><?= h(t('contact_eyebrow')) ?></p>
  <h1><?= h(t('nav_contact')) ?></h1>
  <p class="lead"><?= h(t('contact_sub')) ?></p>
  <?php flash_show(); ?>
  <div class="contact-layout">
    <div class="contact-form-card">
      <span class="contact-mark" aria-hidden="true">✉</span>
      <h2><?= h(t('contact_form_title')) ?></h2>
      <p class="note"><?= h(t('contact_form_note')) ?></p>
      <form method="post" class="form">
        <?= csrf_field() ?>
<?php if ($err): ?>
        <p class="flash err" role="alert"><?= h(t($err)) ?></p>
<?php endif; ?>
        <label><span><?= h(t('f_first')) ?></span><input name="name" required minlength="2" maxlength="80" autocomplete="name" value="<?= h($f['name']) ?>"></label>
        <label><span><?= h(t('lbl_email')) ?></span><input name="email" type="email" required maxlength="120" autocomplete="email" value="<?= h($f['email']) ?>"></label>
        <label><span><?= h(t('topic')) ?></span>
          <select name="topic">
<?php foreach (['duda' => 'topic_q', 'propuesta' => 'topic_p'] as $value => $key): ?>
            <option value="<?= $value ?>"<?= $f['topic'] === $value ? ' selected' : '' ?>><?= h(t($key)) ?></option>
<?php endforeach; ?>
          </select>
        </label>
        <label><span><?= h(t('message')) ?></span><textarea name="message" required minlength="10" maxlength="1000" rows="5"><?= h($f['message']) ?></textarea></label>
        <input class="hp" name="website" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button class="solid"><?= h(t('send')) ?> <span aria-hidden="true">↗</span></button>
      </form>
    </div>
    <aside class="contact-aside">
      <h2><?= h(t('contact_details_title')) ?></h2>
      <p class="note"><?= h(t('contact_details_note')) ?></p>
      <div class="info">
        <dl>
          <dt><?= h(t('lbl_email')) ?></dt><dd><a href="mailto:<?= h($CONFIG['email']) ?>"><?= h($CONFIG['email']) ?></a></dd>
          <dt><?= h(t('lbl_phone')) ?></dt><dd><?= h($CONFIG['phone']) ?></dd>
          <dt><?= h(t('lbl_address')) ?></dt><dd><?= h($CONFIG['address']) ?></dd>
          <dt><?= h(t('lbl_hours')) ?></dt><dd><?= h($CONFIG['hours'][LI]) ?></dd>
        </dl>
      </div>
      <div>
        <p class="eyebrow"><?= h(t('contact_social_title')) ?></p>
        <div class="contact-socials">
<?php foreach ($CONFIG['social'] as $name => $href): ?>
          <a class="ghost" href="<?= h($href) ?>" target="_blank" rel="noopener"><?= h($name) ?></a>
<?php endforeach; ?>
        </div>
      </div>
    </aside>
  </div>
</section>
<?php page_end();
