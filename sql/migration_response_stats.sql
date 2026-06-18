-- Migración: tiempo de respuesta real de proveedores (para badge/boost "Responde rápido")
ALTER TABLE contact_requests ADD COLUMN IF NOT EXISTS replied_at TIMESTAMP;
