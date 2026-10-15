const DATA_URL = 'data/courses.json'; // Fase 2: 'api/courses.php'
const LEVELS = { basico: 'Básico', intermedio: 'Intermedio', avanzado: 'Avanzado' };
const state = { courses: [], q: '', cat: 'all', level: 'all' };

const grid = document.querySelector('#courses');
const count = document.querySelector('#count');
const empty = document.querySelector('#empty');

const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const norm = (s) => s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

function matches(c) {
  const text = norm([c.title, c.description, ...c.tags].join(' '));
  return (state.cat === 'all' || c.category === state.cat)
    && (state.level === 'all' || c.level === state.level)
    && text.includes(norm(state.q));
}

function card(c) {
  return `<article class="card" data-color="${esc(c.color)}">
    <span class="icon" aria-hidden="true">${esc(c.icon)}</span>
    <h3>${esc(c.title)}</h3>
    <p>${esc(c.description)}</p>
    <div class="meta">
      <span class="badge">${LEVELS[c.level]}</span>
      <span class="badge">${esc(c.hours)} h</span>
      <span class="soon">Próximamente</span>
    </div>
  </article>`;
}

function render() {
  const list = state.courses.filter(matches);
  grid.innerHTML = list.map(card).join('');
  count.textContent = `${list.length} ${list.length === 1 ? 'curso' : 'cursos'}`;
  empty.hidden = list.length > 0;
}

document.querySelector('#q').addEventListener('input', (e) => { state.q = e.target.value; render(); });
document.querySelector('#level').addEventListener('change', (e) => { state.level = e.target.value; render(); });
document.querySelectorAll('.chip').forEach((btn) => btn.addEventListener('click', () => {
  state.cat = btn.dataset.cat;
  document.querySelectorAll('.chip').forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
  render();
}));

async function init() {
  try {
    const res = await fetch(DATA_URL);
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    state.courses = await res.json();
    render();
  } catch (err) {
    count.textContent = '';
    grid.innerHTML = '<p class="empty">No se pudieron cargar los cursos. Abre el proyecto con un servidor local (mira el README).</p>';
    console.error(err);
  }
}
init();
