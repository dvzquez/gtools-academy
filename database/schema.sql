-- Esquema de GTools Academy (SQLite). Para MySQL: AUTOINCREMENT -> AUTO_INCREMENT y TEXT -> VARCHAR.
CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  first_name TEXT NOT NULL,
  last_name TEXT NOT NULL,
  phone TEXT NOT NULL,
  role TEXT NOT NULL CHECK (role IN ('maestro', 'alumno', 'general')),
  email TEXT NOT NULL UNIQUE,
  level TEXT NOT NULL CHECK (level IN ('basico', 'intermedio', 'avanzado')),
  password_hash TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS enrollments (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  user_id INTEGER NOT NULL REFERENCES users (id) ON DELETE CASCADE,
  course_id TEXT NOT NULL,
  enrolled_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completed_at TEXT,
  UNIQUE (user_id, course_id)
);

CREATE TABLE IF NOT EXISTS messages (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL,
  topic TEXT NOT NULL CHECK (topic IN ('duda', 'propuesta')),
  message TEXT NOT NULL,
  created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
