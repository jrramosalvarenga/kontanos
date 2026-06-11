-- Kontactanos - Migración: sistema de referidos piramidal, rangos, publicidad y prioridad admin
-- Ejecutar después de schema.sql

-- Rangos de referidos (deben existir antes de referenciarse desde users)
CREATE TABLE ranks (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL UNIQUE,
    min_points INT NOT NULL DEFAULT 0,
    badge_icon VARCHAR(10) NOT NULL DEFAULT '⭐',
    badge_color VARCHAR(20) NOT NULL DEFAULT '#15803d',
    search_boost INT NOT NULL DEFAULT 0,
    sort_order INT NOT NULL DEFAULT 0
);

INSERT INTO ranks (name, slug, min_points, badge_icon, badge_color, search_boost, sort_order) VALUES
('Nuevo',     'nuevo',     0,    '🌱', '#9ca3af', 0,  1),
('Promotor',  'promotor',  50,   '🔹', '#3b82f6', 5,  2),
('Embajador', 'embajador', 200,  '🥈', '#0ea5e9', 15, 3),
('Líder',     'lider',     500,  '🥇', '#f59e0b', 30, 4),
('Leyenda',   'leyenda',   1500, '👑', '#a855f7', 50, 5);

-- Campos de referidos / puntos / rango en users
ALTER TABLE users ADD COLUMN referral_code VARCHAR(12) UNIQUE;
ALTER TABLE users ADD COLUMN referred_by INT REFERENCES users(id) ON DELETE SET NULL;
ALTER TABLE users ADD COLUMN points INT NOT NULL DEFAULT 0;
ALTER TABLE users ADD COLUMN rank_id INT NOT NULL DEFAULT 1 REFERENCES ranks(id);

-- Historial de puntos otorgados
CREATE TABLE point_transactions (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    points INT NOT NULL,
    level INT NOT NULL,
    source_user_id INT REFERENCES users(id) ON DELETE SET NULL,
    reason VARCHAR(150),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Prioridad manual de búsqueda asignada por el admin
ALTER TABLE provider_profiles ADD COLUMN admin_priority INT NOT NULL DEFAULT 0;

-- Publicidad / banners
CREATE TABLE ads (
    id SERIAL PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    link_url VARCHAR(500),
    position VARCHAR(30) NOT NULL DEFAULT 'home_banner' CHECK (position IN ('home_banner','search_top','sidebar')),
    is_active BOOLEAN DEFAULT TRUE,
    starts_at TIMESTAMP,
    ends_at TIMESTAMP,
    sort_order INT DEFAULT 0,
    clicks INT DEFAULT 0,
    impressions INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Índices
CREATE INDEX idx_users_referred_by ON users(referred_by);
CREATE INDEX idx_users_referral_code ON users(referral_code);
CREATE INDEX idx_point_tx_user ON point_transactions(user_id);
CREATE INDEX idx_provider_admin_priority ON provider_profiles(admin_priority DESC);

-- Generar código de referido para usuarios existentes (ej. demo)
UPDATE users SET referral_code = upper(substr(md5(random()::text || id::text), 1, 6))
WHERE referral_code IS NULL;
