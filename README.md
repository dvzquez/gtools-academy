# GTools Academy

Sitio de cursos sobre herramientas de Google (populares y poco conocidas), pensado para maestros, alumnos y, después, cualquier persona que quiera aprender. Proyecto universitario y de portafolio.

**Stack de la presentación:** HTML, CSS, JavaScript, PHP y SQL (SQLite). TypeScript, React, Node.js y Python se agregan cuando se indique.

## Páginas
- `index.php`: inicio (bienvenida, objetivo y los tres públicos)
- `cursos.php`: cursos con filtros, 3 rutas de aprendizaje y explicación del reconocimiento
- `curso.php?id=forms`: programa de un curso (Program Overview) y botón Enroll now
- `contacto.php`: dudas y propuestas de cursos (se guardan en SQL)
- `acceso.php`: registro e inicio de sesión
- `reconocimiento.php`: reconocimiento imprimible (no es una certificación oficial)

## Estructura
```
includes/   app.php (sesión, idioma, BD, plantilla), i18n.php (es/en/fr), config.php (datos de contacto), data.php
database/   schema.sql (tablas), queries.sql (consultas de ejemplo); gtools.sqlite se crea sola
data/       courses.json (12 cursos en es/en/fr)
css/  js/  assets/
```

## Ejecutar
Necesitas PHP con la extensión `pdo_sqlite`.
- Mac/Linux: `php -S localhost:8000` en la carpeta del proyecto y abre http://localhost:8000
- Windows: XAMPP, copia el proyecto a `C:\xampp\htdocs\gtools-academy` y abre http://localhost/gtools-academy/
- Si aparece "could not find driver", activa `extension=pdo_sqlite` en `php.ini`.
La base de datos `database/gtools.sqlite` se crea sola al primer uso (no se sube a Git).

## Base de datos (SQL)
Tablas `users`, `enrollments` y `messages` en `database/schema.sql`, con claves únicas y CHECK. Todas las consultas usan sentencias preparadas. Para MySQL: cambia `AUTOINCREMENT` por `AUTO_INCREMENT` y `INSERT OR IGNORE` por `INSERT IGNORE`.

## Roadmap
- [x] Fase 1: HTML, CSS y JavaScript
- [x] Fase 2: PHP + SQL, sitio de varias páginas, tres idiomas y dos temas
- [ ] Fase 3: TypeScript, React, Node.js y Python (cuando se indique)