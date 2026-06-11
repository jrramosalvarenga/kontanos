-- Kontactanos - Migración: soporte de login con Facebook
-- Ejecutar después de schema.sql

ALTER TABLE users ADD COLUMN facebook_id VARCHAR(100) UNIQUE;
CREATE INDEX idx_users_facebook_id ON users(facebook_id);
