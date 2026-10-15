const DATA_URL = 'data/courses.json'; // Fase 2: 'api/courses.php'
// Etapas de la ruta de aprendizaje (ids de cursos, en orden)
const ROAD = [['drive', 'docs-slides', 'gmail'], ['sheets', 'looker-studio'], ['apps-script', 'colab', 'notebooklm']];
// En courses.json los textos son pares [español, inglés]
const I18N = {
  es: {
    nav_courses: 'Cursos', nav_roadmap: 'Ruta de aprendizaje', nav_award: 'Reconocimiento', search: 'Buscar un curso',
    login: 'Iniciar sesión', register: 'Registrarse', register_title: 'Crear cuenta', logout: 'Salir', hi: 'Hola, {name}',
    to_elegant: 'Tema elegante', to_dark: 'Tema oscuro', hero_sub: 'Aprende las herramientas de Google, incluso las que nadie te enseñó.',
    cta: 'Ver cursos', video_ph: 'Aquí va tu video de bienvenida', sound: 'Sonido', cat_all: 'Todos', cat_pop: 'Populares', cat_rare: 'Poco conocidas',
    lvl_all: 'Todos los niveles', lvl_basico: 'Básico', lvl_intermedio: 'Intermedio', lvl_avanzado: 'Avanzado', count: 'Resultados: {n}',
    empty: 'No hay cursos con esos filtros.', load_error: 'No se pudieron cargar los cursos. Abre el proyecto con un servidor local (mira el README).',
    road_sub: 'Sigue este orden para ir de lo básico a la automatización.', stage1: 'Fundamentos', stage2: 'Datos', stage3: 'Automatización e IA',
    details: 'Ver temario', goal: 'Objetivo', topics: 'Temas', done_btn: 'Marcar como completado',
    award_line: 'Reconocimiento de GTools Academy para {name} por completar “{course}” el {date}.', award_note: 'No es una certificación oficial.',
    award_text: 'Al terminar un curso recibes un reconocimiento con tu nombre y la fecha. Es un reconocimiento del proyecto, no una certificación oficial de Google ni de ninguna institución.',
    name_label: 'Tu nombre', google: 'Continuar con Google', auth_note: 'Modo demostración: el inicio de sesión real con Google llega en la Fase 5.',
    close: 'Cerrar', footer: 'Proyecto educativo, sin afiliación con Google.',
  },
  en: {
    nav_courses: 'Courses', nav_roadmap: 'Learning path', nav_award: 'Recognition', search: 'Search a course',
    login: 'Log in', register: 'Sign up', register_title: 'Create account', logout: 'Log out', hi: 'Hi, {name}',
    to_elegant: 'Elegant theme', to_dark: 'Dark theme', hero_sub: 'Learn Google tools, even the ones nobody taught you.',
    cta: 'Browse courses', video_ph: 'Your welcome video goes here', sound: 'Sound', cat_all: 'All', cat_pop: 'Popular', cat_rare: 'Lesser-known',
    lvl_all: 'All levels', lvl_basico: 'Beginner', lvl_intermedio: 'Intermediate', lvl_avanzado: 'Advanced', count: 'Results: {n}',
    empty: 'No courses match those filters.', load_error: 'Courses could not be loaded. Open the project with a local server (see README).',
    road_sub: 'Follow this order to go from the basics to automation.', stage1: 'Foundations', stage2: 'Data', stage3: 'Automation and AI',
    details: 'See syllabus', goal: 'Goal', topics: 'Topics', done_btn: 'Mark as completed',
    award_line: 'GTools Academy recognition for {name} for completing “{course}” on {date}.', award_note: 'Not an official certification.',
    award_text: 'When you finish a course you get a recognition with your name and the date. It is a project recognition, not an official certification from Google or any institution.',
    name_label: 'Your name', google: 'Continue with Google', auth_note: 'Demo mode: real Google sign-in arrives in Phase 5.',
    close: 'Close', footer: 'Educational project, not affiliated with Google.',
  },
};

const $ = (s) => document.querySelector(s);
const load = (k, d) => { try { return JSON.parse(localStorage.getItem(k)) ?? d; } catch { return d; } };
const save = (k, v) => { try { localStorage.setItem(k, JSON.stringify(v)); } catch { /* sin almacenamiento */ } };
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const norm = (s) => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

let courses = [], failed = false, fCat = 'all', fLevel = 'all', fQuery = '';
let lang = load('lang', navigator.language?.startsWith('en') ? 'en' : 'es');
let theme = load('theme', 'dark');
let user = load('user', null);
const done = load('done', {}); // { idCurso: fechaISO }

const t = (k, v = {}) => (I18N[lang][k] ?? k).replace(/\{(\w+)\}/g, (_, n) => v[n] ?? '');
const pick = (pair) => pair[lang === 'es' ? 0 : 1];
const fmt = (iso) => new Date(iso).toLocaleDateString(lang);

function applyTheme() {
  document.documentElement.dataset.theme = theme;
  $('#theme').textContent = t(theme === 'dark' ? 'to_elegant' : 'to_dark');
}

function applyI18n() {
  document.documentElement.lang = lang;
  document.querySelectorAll('[data-i18n]').forEach((el) => { el.textContent = t(el.dataset.i18n); });
  document.querySelectorAll('[data-i18n-ph]').forEach((el) => {
    el.placeholder = t(el.dataset.i18nPh);
    el.setAttribute('aria-label', el.placeholder);
  });
  $('#lang').textContent = lang === 'es' ? 'EN' : 'ES';
  $('#sound').setAttribute('aria-label', t('sound'));
  applyTheme(); renderAuth(); render();
}

function matches(c) {
  const text = norm([pick(c.title), pick(c.desc), ...pick(c.topics)].join(' '));
  return (fCat === 'all' || c.category === fCat) && (fLevel === 'all' || c.level === fLevel) && text.includes(norm(fQuery));
}

function card(c) {
  return `<article class="card" data-color="${esc(c.color)}">
    <span class="icon" aria-hidden="true">${esc(c.icon)}</span>
    <h3>${esc(pick(c.title))}${done[c.id] ? ' ✓' : ''}</h3>
    <p>${esc(pick(c.desc))}</p>
    <div class="meta">
      <span class="badge">${t('lvl_' + c.level)}</span><span class="badge">${esc(c.hours)} h</span>
      <button class="ghost" data-open="${esc(c.id)}">${t('details')}</button>
    </div>
  </article>`;
}

function renderRoad() {
  if (!courses.length) return;
  const by = Object.fromEntries(courses.map((c) => [c.id, c]));
  $('#roadmap').innerHTML = ROAD.map((ids, i) => `<li><h3>${t('stage' + (i + 1))}</h3><ol>${ids.map((id) =>
    `<li><button class="step${done[id] ? ' done' : ''}" data-open="${id}">${esc(pick(by[id].title))}</button></li>`).join('')}</ol></li>`).join('');
}

function render() {
  const list = courses.filter(matches);
  $('#courses').innerHTML = failed ? `<p class="empty">${t('load_error')}</p>` : list.map(card).join('');
  $('#count').textContent = failed ? '' : t('count', { n: list.length });
  $('#empty').hidden = failed || list.length > 0;
  $('#awards').innerHTML = courses.filter((c) => done[c.id]).map((c) => `<li>🏅 ${esc(pick(c.title))}, ${fmt(done[c.id])}</li>`).join('');
  renderRoad();
}

function renderAuth() {
  $('#login').textContent = user ? t('hi', { name: user.name }) : t('login');
  $('#register').textContent = user ? t('logout') : t('register');
}

function openAuth(mode) {
  $('#auth-title').textContent = t(mode === 'login' ? 'login' : 'register_title');
  $('#dlg-auth').showModal();
}

function openCourse(id) {
  const c = courses.find((x) => x.id === id);
  const d = done[id];
  $('#course-body').innerHTML = `
    <h2>${esc(c.icon)} ${esc(pick(c.title))}</h2>
    <p><strong>${t('goal')}:</strong> ${esc(pick(c.goal))}</p>
    <p><strong>${t('topics')}:</strong></p>
    <ul>${pick(c.topics).map((x) => `<li>${esc(x)}</li>`).join('')}</ul>
    <p class="note">${t('lvl_' + c.level)}, ${esc(c.hours)} h</p>
    ${d ? `<div class="award"><p>${esc(t('award_line', { name: user?.name ?? '', course: pick(c.title), date: fmt(d) }))}</p><small>${t('award_note')}</small></div>`
        : `<button class="solid" id="finish" data-id="${esc(id)}">${t('done_btn')}</button>`}
    <button class="ghost" id="course-close">${t('close')}</button>`;
  const dlg = $('#dlg-course');
  if (!dlg.open) dlg.showModal();
}

document.addEventListener('click', (e) => {
  const open = e.target.closest('[data-open]');
  if (open) return openCourse(open.dataset.open);
  if (e.target.id === 'course-close') $('#dlg-course').close();
  if (e.target.id === 'finish') {
    if (!user) { $('#dlg-course').close(); return openAuth('login'); }
    done[e.target.dataset.id] = new Date().toISOString();
    save('done', done);
    openCourse(e.target.dataset.id);
    render();
  }
});

$('#q').addEventListener('input', (e) => { fQuery = e.target.value; render(); if (fQuery) $('#cursos').scrollIntoView(); });
$('#level').addEventListener('change', (e) => { fLevel = e.target.value; render(); });
document.querySelectorAll('.chip').forEach((b) => b.addEventListener('click', () => {
  fCat = b.dataset.cat;
  document.querySelectorAll('.chip').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
  render();
}));

$('#lang').onclick = () => { lang = lang === 'es' ? 'en' : 'es'; save('lang', lang); applyI18n(); };
$('#theme').onclick = () => { theme = theme === 'dark' ? 'elegant' : 'dark'; save('theme', theme); applyTheme(); };
$('#login').onclick = () => { if (!user) openAuth('login'); };
$('#register').onclick = () => {
  if (user) { user = null; save('user', null); renderAuth(); } else openAuth('register');
};
// Demo: en la Fase 5 se reemplaza por Google Identity Services
$('#auth-form').addEventListener('submit', (e) => {
  if (e.submitter?.value !== 'ok') return;
  user = { name: $('#auth-name').value.trim() };
  save('user', user);
  renderAuth();
});

// Video de bienvenida: muestra el recuadro hasta que el archivo cargue
const video = $('#welcome');
video.addEventListener('loadeddata', () => { $('#arch').classList.add('has-video'); $('#sound').hidden = false; });
$('#sound').onclick = () => { video.muted = !video.muted; $('#sound').textContent = video.muted ? '🔇' : '🔊'; };

(async function init() {
  try {
    const res = await fetch(DATA_URL);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    courses = await res.json();
  } catch (err) {
    failed = true;
    console.error(err);
  }
  applyI18n();
})();
