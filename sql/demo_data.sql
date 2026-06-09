-- Demo providers data
INSERT INTO users (email, password_hash, role, is_verified) VALUES
('carlos@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE),
('ana@demo.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE),
('luis@demo.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE),
('maria@demo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE),
('pedro@demo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE),
('sofia@demo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE),
('jorge@demo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE),
('laura@demo.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uRpFITHbi', 'provider', TRUE);

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, response_time)
SELECT u.id, 'carlos-rodriguez-electricista', 'Carlos Rodríguez', 'Electricista certificado 15 años de experiencia',
  'Especialista en instalaciones eléctricas residenciales y comerciales. Certificado por INCE. Atiendo emergencias 24/7.',
  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=300&h=300&fit=crop&crop=face',
  '+58 412 5550001', '+58 412 5550001',
  (SELECT id FROM categories WHERE slug='construccion-hogar'),
  (SELECT id FROM locations WHERE slug='venezuela-caracas'),
  15, TRUE, TRUE, 4.9, 127, 842, '< 1 hora'
FROM users u WHERE u.email='carlos@demo.com';

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, instagram, response_time)
SELECT u.id, 'ana-martinez-diseno', 'Ana Martínez', 'Diseñadora Gráfica UX/UI Proyectos digitales y print',
  'Creo identidades visuales que conectan marcas con su audiencia. Especialista en branding, diseño web y materiales de marketing.',
  'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=300&h=300&fit=crop&crop=face',
  '+58 416 5550002', '+58 416 5550002',
  (SELECT id FROM categories WHERE slug='diseno-creatividad'),
  (SELECT id FROM locations WHERE slug='venezuela-caracas'),
  8, TRUE, TRUE, 4.8, 94, 1203, 'ana.design', '< 3 horas'
FROM users u WHERE u.email='ana@demo.com';

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, response_time)
SELECT u.id, 'luis-perez-developer', 'Luis Pérez', 'Desarrollador Full Stack React NodeJS PostgreSQL',
  'Desarrollador web con 6 años construyendo aplicaciones escalables. Especializado en startups y MVPs rápidos.',
  'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=300&h=300&fit=crop&crop=face',
  '+58 424 5550003', '+58 424 5550003',
  (SELECT id FROM categories WHERE slug='tecnologia-it'),
  (SELECT id FROM locations WHERE slug='venezuela-valencia'),
  6, TRUE, TRUE, 4.7, 58, 967, '< 3 horas'
FROM users u WHERE u.email='luis@demo.com';

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, instagram, response_time)
SELECT u.id, 'maria-gonzalez-nutricion', 'Dra. María González', 'Nutricionista Clínica Pérdida de peso saludable',
  'Nutricionista con maestría en dietética clínica. Planes personalizados para pérdida de peso, deporte y enfermedades metabólicas.',
  'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=300&h=300&fit=crop&crop=face',
  '+58 414 5550004', '+58 414 5550004',
  (SELECT id FROM categories WHERE slug='salud-bienestar'),
  (SELECT id FROM locations WHERE slug='venezuela-maracaibo'),
  10, TRUE, TRUE, 5.0, 203, 1567, 'dra.mariagnutri', '< 24 horas'
FROM users u WHERE u.email='maria@demo.com';

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, response_time)
SELECT u.id, 'pedro-fotografia-eventos', 'Pedro Castillo', 'Fotógrafo profesional Bodas eventos y corporativo',
  'Capturo los momentos más importantes de tu vida. Equipo profesional Canon R5. Entrega en 7 días con edición incluida.',
  'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=300&h=300&fit=crop&crop=face',
  '+58 426 5550005', '+58 426 5550005',
  (SELECT id FROM categories WHERE slug='eventos-entretenimiento'),
  (SELECT id FROM locations WHERE slug='venezuela-caracas'),
  12, TRUE, TRUE, 4.9, 156, 2103, '< 1 hora'
FROM users u WHERE u.email='pedro@demo.com';

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, instagram, response_time)
SELECT u.id, 'sofia-peluqueria-estilista', 'Sofía Vargas', 'Estilista y Colorista Studio Sofía Beauty',
  'Especialista en colorimetría, cortes y tratamientos capilares. Trabajo con productos profesionales.',
  'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=300&h=300&fit=crop&crop=face',
  '+58 412 5550006', '+58 412 5550006',
  (SELECT id FROM categories WHERE slug='belleza-cuidado'),
  (SELECT id FROM locations WHERE slug='venezuela-maracay'),
  7, TRUE, TRUE, 4.8, 89, 743, 'sofia.beauty.vzla', '< 3 horas'
FROM users u WHERE u.email='sofia@demo.com';

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, response_time)
SELECT u.id, 'jorge-contador-finanzas', 'Jorge Blanco', 'Contador Público Asesoría fiscal y contable',
  'CPA con 12 años de experiencia. Declaraciones de renta, contabilidad de empresas y asesoría en ISLR.',
  'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=300&h=300&fit=crop&crop=face',
  '+58 414 5550007', '+58 414 5550007',
  (SELECT id FROM categories WHERE slug='legal-finanzas'),
  (SELECT id FROM locations WHERE slug='venezuela-barquisimeto'),
  12, TRUE, TRUE, 4.6, 47, 521, '< 24 horas'
FROM users u WHERE u.email='jorge@demo.com';

INSERT INTO provider_profiles (user_id, slug, full_name, tagline, bio, avatar_url, phone, whatsapp, category_id, location_id, years_experience, is_featured, is_verified, rating_avg, rating_count, profile_views, instagram, response_time)
SELECT u.id, 'laura-profesora-ingles', 'Laura Ramos', 'Profesora de Inglés Cambridge Certified CELTA',
  'Clases personalizadas para adultos y niños. Preparación para exámenes IELTS, TOEFL y Cambridge. Online y presencial.',
  'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=300&h=300&fit=crop&crop=face',
  '+58 416 5550008', '+58 416 5550008',
  (SELECT id FROM categories WHERE slug='educacion-clases'),
  (SELECT id FROM locations WHERE slug='venezuela-san-cristobal'),
  5, TRUE, TRUE, 4.9, 112, 876, 'lauraenglish.vzla', '< 1 hora'
FROM users u WHERE u.email='laura@demo.com';

-- Services
INSERT INTO services (provider_id, title, description, price_from, price_to, price_type, currency)
SELECT pp.id, 'Instalación eléctrica completa', 'Tableros, tomacorrientes, iluminación y más.', 50, 300, 'fixed', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'carlos-rodriguez-electricista';

INSERT INTO services (provider_id, title, description, price_from, price_type, currency)
SELECT pp.id, 'Emergencias 24/7', 'Atención de emergencias eléctricas en menos de 1 hora.', 30, 'fixed', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'carlos-rodriguez-electricista';

INSERT INTO services (provider_id, title, description, price_from, price_to, price_type, currency)
SELECT pp.id, 'Diseño de identidad visual', 'Logo, paleta de colores, tipografía y manual de marca.', 150, 500, 'fixed', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'ana-martinez-diseno';

INSERT INTO services (provider_id, title, description, price_from, price_type, currency)
SELECT pp.id, 'Pack de redes sociales', '15 artes profesionales para Instagram y Facebook.', 80, 'fixed', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'ana-martinez-diseno';

INSERT INTO services (provider_id, title, description, price_from, price_type, currency)
SELECT pp.id, 'Desarrollo web a medida', 'Sitio web o app con React y Node.js.', 500, 'free_quote', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'luis-perez-developer';

INSERT INTO services (provider_id, title, description, price_from, price_type, currency)
SELECT pp.id, 'Consulta nutricional inicial', 'Evaluación completa con plan alimentario personalizado.', 40, 'fixed', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'maria-gonzalez-nutricion';

INSERT INTO services (provider_id, title, description, price_from, price_type, currency)
SELECT pp.id, 'Sesión de fotos de boda', 'Cobertura completa, 500+ fotos editadas en alta resolución.', 400, 'fixed', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'pedro-fotografia-eventos';

INSERT INTO services (provider_id, title, description, price_from, price_type, currency)
SELECT pp.id, 'Clases de inglés individuales', 'Clases de 1 hora adaptadas a tu nivel y objetivos.', 15, 'hourly', 'USD'
FROM provider_profiles pp WHERE pp.slug = 'laura-profesora-ingles';

-- Reviews
INSERT INTO reviews (provider_id, reviewer_name, rating, comment)
SELECT pp.id, 'Roberto Sánchez', 5, 'Excelente trabajo! Carlos llegó puntual, trabajó limpio y el precio fue muy justo. 100% recomendado.'
FROM provider_profiles pp WHERE pp.slug = 'carlos-rodriguez-electricista';

INSERT INTO reviews (provider_id, reviewer_name, rating, comment)
SELECT pp.id, 'María López', 5, 'Resolvió un problema que otro electricista no pudo solucionar. Muy profesional.'
FROM provider_profiles pp WHERE pp.slug = 'carlos-rodriguez-electricista';

INSERT INTO reviews (provider_id, reviewer_name, rating, comment)
SELECT pp.id, 'Tech Solutions CA', 5, 'Ana rediseñó toda nuestra identidad corporativa. El resultado superó nuestras expectativas.'
FROM provider_profiles pp WHERE pp.slug = 'ana-martinez-diseno';

INSERT INTO reviews (provider_id, reviewer_name, rating, comment)
SELECT pp.id, 'Luis Fernández', 5, 'Contraté a Luis para desarrollar nuestra plataforma y entregó a tiempo, con calidad impecable.'
FROM provider_profiles pp WHERE pp.slug = 'luis-perez-developer';

INSERT INTO reviews (provider_id, reviewer_name, rating, comment)
SELECT pp.id, 'Carolina Blanco', 5, 'La Dra. González cambió mi vida. Bajé 15 kilos en 6 meses con su plan. Increíble profesional!'
FROM provider_profiles pp WHERE pp.slug = 'maria-gonzalez-nutricion';

INSERT INTO reviews (provider_id, reviewer_name, rating, comment)
SELECT pp.id, 'Alejandro Torres', 5, 'Pedro fotografió nuestra boda y las fotos quedaron de revista. Muy profesional y creativo.'
FROM provider_profiles pp WHERE pp.slug = 'pedro-fotografia-eventos';
