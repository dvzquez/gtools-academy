// Se conecta el selector de tema con las preferencias guardadas para que se conserve al navegar.
const root = document.documentElement;
const themeBtn = document.querySelector('#theme');
const paintTheme = () => {
  if (!themeBtn) return;
  themeBtn.textContent = root.dataset.theme === 'dark' ? themeBtn.dataset.light : themeBtn.dataset.dark;
};
if (themeBtn) {
  themeBtn.addEventListener('click', () => {
    root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
    try { localStorage.setItem('gt-theme', root.dataset.theme); } catch (e) { /* Aquí continúo aunque el navegador bloquee el almacenamiento. */ }
    paintTheme();
  });
  paintTheme();
}

// Cambio de idioma usando el único selector del menú; cada opción conserva la página y sus filtros actuales.
const languageSelect = document.querySelector('#language');
languageSelect?.addEventListener('change', () => {
  if (languageSelect.value) window.location.assign(languageSelect.value);
});

// Aquí permito copiar la dirección actual para compartir el curso o la página en la que estamos.
const copyBtn = document.querySelector('#copy');
copyBtn?.addEventListener('click', async () => {
  try { await navigator.clipboard.writeText(location.href); copyBtn.textContent = copyBtn.dataset.done; } catch (e) { /* sin permiso */ }
});

// Combinación de la búsqueda, el nivel y el público elegido; además se actualiza el contador de cursos visibles.
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

// Se oculta el texto provisional cuando el video carga y se deja alternar el sonido desde su botón.
const video = document.querySelector('#welcome');
if (video) {
  const sound = document.querySelector('#sound');
  video.addEventListener('loadeddata', () => { document.querySelector('#arch').classList.add('has-video'); sound.hidden = false; });
  sound.addEventListener('click', () => { video.muted = !video.muted; sound.textContent = video.muted ? '🔇' : '🔊'; });
}
