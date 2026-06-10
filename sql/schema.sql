-- Kontactanos - Database Schema (PostgreSQL)
-- Plataforma de conexión entre proveedores y clientes locales

CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "unaccent";
CREATE EXTENSION IF NOT EXISTS "pg_trgm";

-- Categorías de servicios
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) NOT NULL DEFAULT 'briefcase',
    color VARCHAR(20) NOT NULL DEFAULT '#15803d',
    description TEXT,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Provincias / Regiones
CREATE TABLE locations (
    id SERIAL PRIMARY KEY,
    country VARCHAR(100) NOT NULL DEFAULT 'Venezuela',
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE
);

-- Usuarios (proveedores y clientes)
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    uuid UUID DEFAULT uuid_generate_v4() UNIQUE NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255),
    google_id VARCHAR(100) UNIQUE,
    role VARCHAR(20) NOT NULL DEFAULT 'client' CHECK (role IN ('provider', 'client', 'admin')),
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    email_token VARCHAR(100),
    reset_token VARCHAR(100),
    reset_expires TIMESTAMP,
    last_login TIMESTAMP,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Perfiles de proveedores
CREATE TABLE provider_profiles (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    slug VARCHAR(120) UNIQUE NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    tagline VARCHAR(200),
    bio TEXT,
    avatar_url VARCHAR(500),
    cover_url VARCHAR(500),
    phone VARCHAR(30),
    whatsapp VARCHAR(30),
    website VARCHAR(300),
    instagram VARCHAR(100),
    facebook VARCHAR(100),
    linkedin VARCHAR(100),
    twitter VARCHAR(100),
    category_id INT REFERENCES categories(id),
    location_id INT REFERENCES locations(id),
    address TEXT,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    years_experience INT DEFAULT 0,
    is_featured BOOLEAN DEFAULT FALSE,
    is_verified BOOLEAN DEFAULT FALSE,
    response_time VARCHAR(50) DEFAULT '< 1 hora',
    profile_views INT DEFAULT 0,
    rating_avg DECIMAL(3,2) DEFAULT 0,
    rating_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Servicios / Productos ofrecidos por proveedor
CREATE TABLE services (
    id SERIAL PRIMARY KEY,
    provider_id INT NOT NULL REFERENCES provider_profiles(id) ON DELETE CASCADE,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price_from DECIMAL(12,2),
    price_to DECIMAL(12,2),
    price_type VARCHAR(30) DEFAULT 'fixed' CHECK (price_type IN ('fixed', 'hourly', 'negotiable', 'free_quote')),
    currency VARCHAR(10) DEFAULT 'USD',
    images JSONB DEFAULT '[]',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Reseñas / Reviews
CREATE TABLE reviews (
    id SERIAL PRIMARY KEY,
    provider_id INT NOT NULL REFERENCES provider_profiles(id) ON DELETE CASCADE,
    reviewer_user_id INT REFERENCES users(id) ON DELETE SET NULL,
    reviewer_name VARCHAR(100),
    reviewer_avatar VARCHAR(300),
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    reply TEXT,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Contactos / Leads generados
CREATE TABLE contact_requests (
    id SERIAL PRIMARY KEY,
    provider_id INT NOT NULL REFERENCES provider_profiles(id) ON DELETE CASCADE,
    requester_user_id INT REFERENCES users(id) ON DELETE SET NULL,
    requester_name VARCHAR(150),
    requester_email VARCHAR(255),
    requester_phone VARCHAR(30),
    message TEXT,
    service_id INT REFERENCES services(id) ON DELETE SET NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'read', 'replied', 'closed')),
    created_at TIMESTAMP DEFAULT NOW()
);

-- Tags de habilidades
CREATE TABLE tags (
    id SERIAL PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    slug VARCHAR(80) NOT NULL UNIQUE
);

CREATE TABLE provider_tags (
    provider_id INT REFERENCES provider_profiles(id) ON DELETE CASCADE,
    tag_id INT REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (provider_id, tag_id)
);

-- Portfolio de trabajos
CREATE TABLE portfolio_items (
    id SERIAL PRIMARY KEY,
    provider_id INT NOT NULL REFERENCES provider_profiles(id) ON DELETE CASCADE,
    title VARCHAR(200),
    description TEXT,
    image_url VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW()
);

-- Favoritos
CREATE TABLE favorites (
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    provider_id INT REFERENCES provider_profiles(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT NOW(),
    PRIMARY KEY (user_id, provider_id)
);

-- Índices de búsqueda
CREATE INDEX idx_provider_category ON provider_profiles(category_id);
CREATE INDEX idx_provider_location ON provider_profiles(location_id);
CREATE INDEX idx_provider_featured ON provider_profiles(is_featured);
CREATE INDEX idx_provider_rating ON provider_profiles(rating_avg DESC);
CREATE INDEX idx_provider_slug ON provider_profiles(slug);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_reviews_provider ON reviews(provider_id);

-- Full text search index
CREATE INDEX idx_provider_search ON provider_profiles USING gin(
    to_tsvector('spanish', coalesce(full_name,'') || ' ' || coalesce(tagline,'') || ' ' || coalesce(bio,''))
);

-- Función para actualizar rating promedio
CREATE OR REPLACE FUNCTION update_provider_rating()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE provider_profiles SET
        rating_avg = (SELECT AVG(rating) FROM reviews WHERE provider_id = NEW.provider_id AND is_approved = TRUE),
        rating_count = (SELECT COUNT(*) FROM reviews WHERE provider_id = NEW.provider_id AND is_approved = TRUE)
    WHERE id = NEW.provider_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trg_update_rating
AFTER INSERT OR UPDATE OR DELETE ON reviews
FOR EACH ROW EXECUTE FUNCTION update_provider_rating();

-- Categorías iniciales
INSERT INTO categories (name, slug, icon, color, description, sort_order) VALUES
('Construcción y Hogar', 'construccion-hogar', 'home', '#f59e0b', 'Albañiles, plomeros, electricistas y más', 1),
('Tecnología y IT', 'tecnologia-it', 'computer-desktop', '#3b82f6', 'Programadores, diseñadores, soporte técnico', 2),
('Salud y Bienestar', 'salud-bienestar', 'heart', '#ef4444', 'Médicos, nutricionistas, psicólogos', 3),
('Educación y Clases', 'educacion-clases', 'academic-cap', '#8b5cf6', 'Tutores, profesores particulares, cursos', 4),
('Legal y Finanzas', 'legal-finanzas', 'scale', '#0ea5e9', 'Abogados, contadores, asesores', 5),
('Eventos y Entretenimiento', 'eventos-entretenimiento', 'musical-note', '#ec4899', 'Fotógrafos, DJ, decoradores, catering', 6),
('Belleza y Cuidado Personal', 'belleza-cuidado', 'sparkles', '#14b8a6', 'Peluqueros, maquilladores, esteticistas', 7),
('Transporte y Logística', 'transporte-logistica', 'truck', '#f97316', 'Mudanzas, mensajería, transporte', 8),
('Reparaciones y Mantenimiento', 'reparaciones-mantenimiento', 'wrench', '#64748b', 'Electrodomésticos, celulares, computadoras', 9),
('Diseño y Creatividad', 'diseno-creatividad', 'paint-brush', '#a855f7', 'Diseño gráfico, arquitectura, decoración', 10),
('Gastronomía', 'gastronomia', 'cake', '#f43f5e', 'Chefs, repostería, catering, comida', 11),
('Mascotas', 'mascotas', 'paw', '#84cc16', 'Veterinaria, peluquería canina, paseo', 12);

-- Ubicaciones iniciales (Venezuela como ejemplo base)
INSERT INTO locations (country, state, city, slug) VALUES
('Venezuela', 'Distrito Capital', 'Caracas', 'venezuela-caracas'),
('Venezuela', 'Miranda', 'Los Teques', 'venezuela-los-teques'),
('Venezuela', 'Carabobo', 'Valencia', 'venezuela-valencia'),
('Venezuela', 'Zulia', 'Maracaibo', 'venezuela-maracaibo'),
('Venezuela', 'Aragua', 'Maracay', 'venezuela-maracay'),
('Venezuela', 'Lara', 'Barquisimeto', 'venezuela-barquisimeto'),
('Venezuela', 'Táchira', 'San Cristóbal', 'venezuela-san-cristobal'),
('Venezuela', 'Mérida', 'Mérida', 'venezuela-merida'),
('Venezuela', 'Bolívar', 'Ciudad Bolívar', 'venezuela-ciudad-bolivar'),
('Venezuela', 'Anzoátegui', 'Barcelona', 'venezuela-barcelona'),
('Colombia', 'Cundinamarca', 'Bogotá', 'colombia-bogota'),
('Colombia', 'Antioquia', 'Medellín', 'colombia-medellin'),
('Colombia', 'Valle del Cauca', 'Cali', 'colombia-cali'),
('Argentina', 'Buenos Aires', 'Buenos Aires', 'argentina-buenos-aires'),
('México', 'Ciudad de México', 'Ciudad de México', 'mexico-cdmx'),
('España', 'Madrid', 'Madrid', 'espana-madrid'),
('Estados Unidos', 'Florida', 'Miami', 'eeuu-miami');

-- Perfil demo (admin/proveedor)
INSERT INTO users (email, password_hash, role, is_verified) VALUES
('demo@kontactanos.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE);
