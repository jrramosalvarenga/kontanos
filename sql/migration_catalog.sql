-- Catálogo de productos y servicios tipo app (Fase 1)
-- Migra la tabla "services" existente al nuevo modelo simplificado y agrega "products".
-- Las columnas legacy (price_from, price_to, price_type, currency, images) se conservan
-- sin usar para no perder datos históricos; el código nuevo usa nombre/descripcion/precio.

ALTER TABLE services RENAME COLUMN title TO nombre;
ALTER TABLE services RENAME COLUMN description TO descripcion;
ALTER TABLE services RENAME COLUMN is_active TO activo;

ALTER TABLE services ADD COLUMN IF NOT EXISTS precio DECIMAL(12,2);
ALTER TABLE services ADD COLUMN IF NOT EXISTS imagen VARCHAR(500);
ALTER TABLE services ADD COLUMN IF NOT EXISTS duracion_min INT;
ALTER TABLE services ADD COLUMN IF NOT EXISTS orden INT DEFAULT 0;

UPDATE services SET precio = COALESCE(price_from, price_to) WHERE precio IS NULL;

CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    provider_id INT NOT NULL REFERENCES provider_profiles(id) ON DELETE CASCADE,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(12,2),
    imagen VARCHAR(500),
    activo BOOLEAN DEFAULT TRUE,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_products_provider ON products(provider_id);
CREATE INDEX IF NOT EXISTS idx_services_provider_orden ON services(provider_id, orden);
