-- Consultas de ejemplo para revisar los datos (abre database/gtools.sqlite con DB Browser for SQLite).
-- Inscripciones por curso
SELECT course_id, COUNT(*) AS inscritos FROM enrollments GROUP BY course_id ORDER BY inscritos DESC;
-- Usuarios por rol y nivel
SELECT role, level, COUNT(*) AS total FROM users GROUP BY role, level;
-- Cursos completados por usuario
SELECT u.first_name, u.last_name, COUNT(e.completed_at) AS completados
FROM users u LEFT JOIN enrollments e ON e.user_id = u.id GROUP BY u.id;
-- Propuestas de cursos recibidas
SELECT name, email, message, created_at FROM messages WHERE topic = 'propuesta' ORDER BY created_at DESC;
