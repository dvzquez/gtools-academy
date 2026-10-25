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

## Versión 4.0 — actualización visual
Esta versión mantiene el proyecto en **v4.0**. El rediseño usa la paleta `#215EBB`, `#AFCEE3`, `#0E2859`, `#CE6C37` y `#C3B692`, la fuente Faculty Glyphic, tarjetas con estados de interacción, contador de cursos filtrados, rutas con títulos más claros y mejoras en contacto, acceso, pie de página y selector único de idioma. También dejé comentarios en primera persona en los bloques principales para explicar cómo funciona cada parte.

Antes de publicar, revisa los cambios ejecutando el proyecto en un entorno PHP con `pdo_sqlite` habilitado. Desde la carpeta del repositorio, una vez comprobado el resultado visual, los comandos para publicar esta entrega como v4.0 son:

```bash
git add .
git commit -m "v4: rediseño visual y mejoras de navegación"
git push
git tag v4.0
git push origin v4.0
```

No ejecuto esos comandos desde esta entrega para dejarte revisar primero los cambios y decidir cuándo publicarlos.
