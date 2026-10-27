// El selector de tema sincroniza la variable de interfaz con localStorage para conservar la preferencia entre visitas.
const root = document.documentElement;
const themeBtn = document.querySelector('#theme');
const paintTheme = () => {
  if (!themeBtn) return;
  themeBtn.textContent = root.dataset.theme === 'dark' ? themeBtn.dataset.light : themeBtn.dataset.dark;
};
if (themeBtn) {
  themeBtn.addEventListener('click', () => {
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem('gt-theme', root.dataset.theme); } catch (e) { /* La interfaz permanece operativa si el navegador bloquea el almacenamiento local. */ }
    paintTheme();
  });
  paintTheme();
}

// El selector de idioma navega a la misma ruta con el parámetro lang, preservando el resto de parámetros de la URL.
const languageSelect = document.querySelector('#language');
languageSelect?.addEventListener('change', () => {
  if (languageSelect.value) window.location.assign(languageSelect.value);
});

// La acción de compartir copia la URL actual mediante la Clipboard API cuando el contexto lo permite.
const copyBtn = document.querySelector('#copy');
copyBtn?.addEventListener('click', async () => {
  try { await navigator.clipboard.writeText(location.href); copyBtn.textContent = copyBtn.dataset.done; } catch (e) { /* La acción se omite si el navegador no concede permiso al portapapeles. */ }
});

// El filtro compone las condiciones de texto, nivel y público, actualizando visibilidad, estados accesibles y contador.
const grid = document.querySelector('#grid');
if (grid) {
  const q = document.querySelector('#q');
  const lvl = document.querySelector('#lvl');
  const chips = [...document.querySelectorAll('.chip')];
  let aud = new URLSearchParams(location.search).get('aud') || 'all';
  const norm = (s) => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const apply = () => {
    let shown = 0;
    grid.querySelectorAll('.card').forEach((c) => {
      const ok = (aud === 'all' || c.dataset.aud.split(' ').includes(aud))
        && (lvl.value === 'all' || c.dataset.level === lvl.value)
        && norm(c.dataset.text).includes(norm(q.value));
      c.hidden = !ok;
      shown += ok ? 1 : 0;
    });
    chips.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.aud === aud)));
    document.querySelector('#empty').hidden = shown > 0;
    const courseCount = document.querySelector('#courseCount');
    const courseCountLabel = document.querySelector('#courseCountLabel');
    if (courseCount) courseCount.textContent = String(shown);
    if (courseCountLabel) courseCountLabel.textContent = shown === 1
      ? courseCountLabel.dataset.singular
      : courseCountLabel.dataset.plural;
  };
  chips.forEach((b) => b.addEventListener('click', () => { aud = b.dataset.aud; apply(); }));
  q.addEventListener('input', apply);
  lvl.addEventListener('change', apply);
  apply();
}

// No se requiere lógica multimedia: las áreas visuales de esta versión usan recursos SVG estáticos.
