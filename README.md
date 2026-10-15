# GTools Academy

Catálogo de cursos sobre herramientas de Google (populares y poco conocidas). Proyecto universitario y de portafolio, construido por fases para mostrar la evolución del stack.

## Estructura
```
index.html        página principal
css/styles.css    estilos (variables, modo oscuro, responsive)
js/main.js        búsqueda y filtros (JavaScript puro)
data/courses.json datos de los cursos
api/courses.php   API en PHP (se activa en la Fase 2)
```

## Ejecutar en local
`fetch` no funciona abriendo el archivo con doble clic. Usa uno de estos:
- VS Code con la extensión **Live Server** (clic derecho en `index.html` > Open with Live Server)
- `php -S localhost:8000` (sirve HTML y PHP; recomendado)
- `python3 -m http.server 8000`

## Subir a GitHub
1. Crea un repositorio vacío llamado `gtools-academy` en github.com.
2. En la carpeta del proyecto:
```
git init
git add .
git commit -m "feat: catálogo de cursos (fase 1)"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/gtools-academy.git
git push -u origin main
```
3. Publicar la parte estática: Settings > Pages > Deploy from a branch > `main` / root.
GitHub Pages no ejecuta PHP: la Fase 2 solo funciona en local o en un hosting con PHP.

## Roadmap
- [x] **Fase 1:** HTML, CSS y JavaScript. Catálogo con búsqueda y filtros.
- [ ] **Fase 2:** PHP. Cambiar `DATA_URL` a `api/courses.php`, crear `curso.php?id=` (detalle con lecciones) y formulario de contacto con validación.
- [ ] **Fase 3:** TypeScript. Migrar `main.js` a `main.ts` con tipos (`interface Course`) y compilar con Vite.
- [ ] **Fase 4:** React. Reescribir el catálogo con componentes (`CourseCard`, `Filters`) y hooks.
- [ ] **Fase 5:** Integraciones Google: Inicio de sesión con Google, Sheets como fuente de datos vía Apps Script, videos de YouTube en las lecciones, Google Fonts (ya incluido) y Analytics.
