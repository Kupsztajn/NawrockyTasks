-- Create database schema for NawrockyTasks

-- Tabela użytkowników (bez redundancji, 3NF)
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela profili użytkowników (relacja jeden-do-jednego z users)
CREATE TABLE IF NOT EXISTS user_profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER UNIQUE NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    phone VARCHAR(20),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela projektów (relacja jeden-do-wielu z users)
CREATE TABLE IF NOT EXISTS projects (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela zadań (relacja jeden-do-wielu z projects)
CREATE TABLE IF NOT EXISTS tasks (
    id SERIAL PRIMARY KEY,
    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    priority VARCHAR(20) DEFAULT 'medium',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela zaproszeń do projektów (relacja wiele-do-wielu między users a projects)
CREATE TABLE IF NOT EXISTS project_invitations (
    id SERIAL PRIMARY KEY,
    project_id INTEGER NOT NULL REFERENCES projects(id) ON DELETE CASCADE,
    inviter_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    invitee_user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(project_id, invitee_user_id)
);

-- Widok podsumowania projektów (łączy projects, users, tasks)
CREATE OR REPLACE VIEW project_summary AS
SELECT
    p.id AS project_id,
    p.name AS project_name,
    p.description AS project_description,
    u.email AS owner_email,
    COUNT(t.id) AS total_tasks,
    COUNT(CASE WHEN t.status = 'completed' THEN 1 END) AS completed_tasks,
    p.created_at AS project_created_at
FROM projects p
JOIN users u ON p.user_id = u.id
LEFT JOIN tasks t ON p.id = t.project_id
GROUP BY p.id, p.name, p.description, u.email, p.created_at;

-- Widok zaproszeń użytkowników (łączy project_invitations, users, projects)
CREATE OR REPLACE VIEW user_invitations AS
SELECT
    pi.id AS invitation_id,
    p.name AS project_name,
    u_inviter.email AS inviter_email,
    u_invitee.email AS invitee_email,
    pi.status AS invitation_status,
    pi.created_at AS invitation_created_at
FROM project_invitations pi
JOIN projects p ON pi.project_id = p.id
JOIN users u_inviter ON pi.inviter_user_id = u_inviter.id
JOIN users u_invitee ON pi.invitee_user_id = u_invitee.id;

-- Wyzwalacz: Aktualizacja statusu projektu po dodaniu zadania
CREATE OR REPLACE FUNCTION update_project_status_on_task_insert()
RETURNS TRIGGER AS $$
BEGIN
    -- Przykład: Jeśli projekt ma więcej niż 10 zadań, ustaw opis na 'Duży projekt'
    IF (SELECT COUNT(*) FROM tasks WHERE project_id = NEW.project_id) > 10 THEN
        UPDATE projects SET description = description || ' (Duży projekt)' WHERE id = NEW.project_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_update_project_status
AFTER INSERT ON tasks
FOR EACH ROW
EXECUTE FUNCTION update_project_status_on_task_insert();

-- Funkcja: Obliczanie postępu projektu (procent ukończonych zadań)
CREATE OR REPLACE FUNCTION calculate_project_progress(project_id_param INTEGER)
RETURNS DECIMAL(5,2) AS $$
DECLARE
    total_tasks INTEGER;
    completed_tasks INTEGER;
BEGIN
    SELECT COUNT(*) INTO total_tasks FROM tasks WHERE project_id = project_id_param;
    SELECT COUNT(*) INTO completed_tasks FROM tasks WHERE project_id = project_id_param AND status = 'completed';

    IF total_tasks = 0 THEN
        RETURN 0.00;
    ELSE
        RETURN (completed_tasks::DECIMAL / total_tasks::DECIMAL) * 100.00;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- Transakcja: Dodanie przykładowych danych z odpowiednim poziomem izolacji
BEGIN;
SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

-- Insert a sample user for testing (password: 'password')
INSERT INTO users (email, password) VALUES ('test@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') ON CONFLICT (email) DO NOTHING;

-- Dodanie profilu dla użytkownika (jeden-do-jednego)
INSERT INTO user_profiles (user_id, first_name, last_name, phone, bio) VALUES
(1, 'Jan', 'Kowalski', '+48 123 456 789', 'Przykładowy użytkownik systemu NawrockyTasks') ON CONFLICT (user_id) DO NOTHING;

-- Dodanie projektu
INSERT INTO projects (user_id, name, description) VALUES
(1, 'Projekt Testowy', 'Opis projektu testowego') ON CONFLICT DO NOTHING;

-- Dodanie zadań
INSERT INTO tasks (project_id, title, description, status, priority) VALUES
(1, 'Zadanie 1', 'Opis zadania 1', 'pending', 'high'),
(1, 'Zadanie 2', 'Opis zadania 2', 'completed', 'medium') ON CONFLICT DO NOTHING;

-- Dodanie zaproszenia
INSERT INTO project_invitations (project_id, inviter_user_id, invitee_user_id, status) VALUES
(1, 1, 1, 'accepted') ON CONFLICT DO NOTHING;

COMMIT;
