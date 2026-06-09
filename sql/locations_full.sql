-- ============================================================
-- Kontactanos - Ubicaciones completas
-- 299 municipios de Honduras + ciudades principales de LatAm
-- ============================================================

-- Limpiar ubicaciones previas y reemplazar con la lista completa
TRUNCATE TABLE locations RESTART IDENTITY CASCADE;

-- ============================================================
-- HONDURAS - 18 departamentos, 298 municipios
-- ============================================================

-- ATLÁNTIDA (8 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Atlántida','La Ceiba','honduras-atlantida-la-ceiba'),
('Honduras','Atlántida','Tela','honduras-atlantida-tela'),
('Honduras','Atlántida','El Porvenir','honduras-atlantida-el-porvenir'),
('Honduras','Atlántida','Esparta','honduras-atlantida-esparta'),
('Honduras','Atlántida','Jutiapa','honduras-atlantida-jutiapa'),
('Honduras','Atlántida','La Masica','honduras-atlantida-la-masica'),
('Honduras','Atlántida','San Francisco','honduras-atlantida-san-francisco'),
('Honduras','Atlántida','Arizona','honduras-atlantida-arizona');

-- CHOLUTECA (16 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Choluteca','Choluteca','honduras-choluteca-choluteca'),
('Honduras','Choluteca','Apacilagua','honduras-choluteca-apacilagua'),
('Honduras','Choluteca','Concepción de María','honduras-choluteca-concepcion-de-maria'),
('Honduras','Choluteca','Duyure','honduras-choluteca-duyure'),
('Honduras','Choluteca','El Corpus','honduras-choluteca-el-corpus'),
('Honduras','Choluteca','El Triunfo','honduras-choluteca-el-triunfo'),
('Honduras','Choluteca','Marcovia','honduras-choluteca-marcovia'),
('Honduras','Choluteca','Morolica','honduras-choluteca-morolica'),
('Honduras','Choluteca','Namasigüe','honduras-choluteca-namasigue'),
('Honduras','Choluteca','Orocuina','honduras-choluteca-orocuina'),
('Honduras','Choluteca','Pespire','honduras-choluteca-pespire'),
('Honduras','Choluteca','San Antonio de Flores','honduras-choluteca-san-antonio-de-flores'),
('Honduras','Choluteca','San Isidro','honduras-choluteca-san-isidro'),
('Honduras','Choluteca','San José','honduras-choluteca-san-jose'),
('Honduras','Choluteca','San Marcos de Colón','honduras-choluteca-san-marcos-de-colon'),
('Honduras','Choluteca','Santa Ana de Yusguare','honduras-choluteca-santa-ana-de-yusguare');

-- COLÓN (10 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Colón','Trujillo','honduras-colon-trujillo'),
('Honduras','Colón','Balfate','honduras-colon-balfate'),
('Honduras','Colón','Iriona','honduras-colon-iriona'),
('Honduras','Colón','Limón','honduras-colon-limon'),
('Honduras','Colón','Sabá','honduras-colon-saba'),
('Honduras','Colón','Santa Fe','honduras-colon-santa-fe'),
('Honduras','Colón','Santa Rosa de Aguán','honduras-colon-santa-rosa-de-aguan'),
('Honduras','Colón','Sonaguera','honduras-colon-sonaguera'),
('Honduras','Colón','Tocoa','honduras-colon-tocoa'),
('Honduras','Colón','Bonito Oriental','honduras-colon-bonito-oriental');

-- COMAYAGUA (20 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Comayagua','Comayagua','honduras-comayagua-comayagua'),
('Honduras','Comayagua','Ajuterique','honduras-comayagua-ajuterique'),
('Honduras','Comayagua','El Rosario','honduras-comayagua-el-rosario'),
('Honduras','Comayagua','Esquías','honduras-comayagua-esquias'),
('Honduras','Comayagua','Humuya','honduras-comayagua-humuya'),
('Honduras','Comayagua','La Libertad','honduras-comayagua-la-libertad'),
('Honduras','Comayagua','Lamaní','honduras-comayagua-lamani'),
('Honduras','Comayagua','La Trinidad','honduras-comayagua-la-trinidad'),
('Honduras','Comayagua','Lejamani','honduras-comayagua-lejamani'),
('Honduras','Comayagua','Meámbar','honduras-comayagua-meambar'),
('Honduras','Comayagua','Minas de Oro','honduras-comayagua-minas-de-oro'),
('Honduras','Comayagua','Ojos de Agua','honduras-comayagua-ojos-de-agua'),
('Honduras','Comayagua','San Jerónimo','honduras-comayagua-san-jeronimo'),
('Honduras','Comayagua','San José de Comayagua','honduras-comayagua-san-jose-de-comayagua'),
('Honduras','Comayagua','San José del Potrero','honduras-comayagua-san-jose-del-potrero'),
('Honduras','Comayagua','San Luis','honduras-comayagua-san-luis'),
('Honduras','Comayagua','San Sebastián','honduras-comayagua-san-sebastian'),
('Honduras','Comayagua','Siguatepeque','honduras-comayagua-siguatepeque'),
('Honduras','Comayagua','Taulabé','honduras-comayagua-taulabe'),
('Honduras','Comayagua','Villa de San Antonio','honduras-comayagua-villa-de-san-antonio');

-- COPÁN (23 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Copán','Santa Rosa de Copán','honduras-copan-santa-rosa-de-copan'),
('Honduras','Copán','Cabañas','honduras-copan-cabanas'),
('Honduras','Copán','Concepción','honduras-copan-concepcion'),
('Honduras','Copán','Copán Ruinas','honduras-copan-copan-ruinas'),
('Honduras','Copán','Corquín','honduras-copan-corquin'),
('Honduras','Copán','Cucuyagua','honduras-copan-cucuyagua'),
('Honduras','Copán','Dolores','honduras-copan-dolores'),
('Honduras','Copán','Dulce Nombre','honduras-copan-dulce-nombre'),
('Honduras','Copán','El Paraíso','honduras-copan-el-paraiso'),
('Honduras','Copán','Florida','honduras-copan-florida'),
('Honduras','Copán','La Jigua','honduras-copan-la-jigua'),
('Honduras','Copán','La Unión','honduras-copan-la-union'),
('Honduras','Copán','Nueva Arcadia','honduras-copan-nueva-arcadia'),
('Honduras','Copán','San Agustín','honduras-copan-san-agustin'),
('Honduras','Copán','San Antonio','honduras-copan-san-antonio'),
('Honduras','Copán','San Jerónimo','honduras-copan-san-jeronimo'),
('Honduras','Copán','San José','honduras-copan-san-jose'),
('Honduras','Copán','San Juan de Opoa','honduras-copan-san-juan-de-opoa'),
('Honduras','Copán','San Nicolás','honduras-copan-san-nicolas'),
('Honduras','Copán','San Pedro','honduras-copan-san-pedro'),
('Honduras','Copán','Santa Rita','honduras-copan-santa-rita'),
('Honduras','Copán','Trinidad de Copán','honduras-copan-trinidad-de-copan'),
('Honduras','Copán','Veracruz','honduras-copan-veracruz');

-- CORTÉS (12 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Cortés','San Pedro Sula','honduras-cortes-san-pedro-sula'),
('Honduras','Cortés','Choloma','honduras-cortes-choloma'),
('Honduras','Cortés','La Lima','honduras-cortes-la-lima'),
('Honduras','Cortés','Omoa','honduras-cortes-omoa'),
('Honduras','Cortés','Pimienta','honduras-cortes-pimienta'),
('Honduras','Cortés','Potrerillos','honduras-cortes-potrerillos'),
('Honduras','Cortés','Puerto Cortés','honduras-cortes-puerto-cortes'),
('Honduras','Cortés','San Antonio de Cortés','honduras-cortes-san-antonio-de-cortes'),
('Honduras','Cortés','San Francisco de Yojoa','honduras-cortes-san-francisco-de-yojoa'),
('Honduras','Cortés','San Manuel','honduras-cortes-san-manuel'),
('Honduras','Cortés','Santa Cruz de Yojoa','honduras-cortes-santa-cruz-de-yojoa'),
('Honduras','Cortés','Villanueva','honduras-cortes-villanueva');

-- EL PARAÍSO (19 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','El Paraíso','Yuscarán','honduras-el-paraiso-yuscaran'),
('Honduras','El Paraíso','Alauca','honduras-el-paraiso-alauca'),
('Honduras','El Paraíso','Danlí','honduras-el-paraiso-danli'),
('Honduras','El Paraíso','El Paraíso','honduras-el-paraiso-el-paraiso'),
('Honduras','El Paraíso','Güinope','honduras-el-paraiso-guinope'),
('Honduras','El Paraíso','Jacaleapa','honduras-el-paraiso-jacaleapa'),
('Honduras','El Paraíso','Liure','honduras-el-paraiso-liure'),
('Honduras','El Paraíso','Morocelí','honduras-el-paraiso-moroceli'),
('Honduras','El Paraíso','Oropolí','honduras-el-paraiso-oropoli'),
('Honduras','El Paraíso','Potrerillos','honduras-el-paraiso-potrerillos'),
('Honduras','El Paraíso','San Antonio de Flores','honduras-el-paraiso-san-antonio-de-flores'),
('Honduras','El Paraíso','San Lucas','honduras-el-paraiso-san-lucas'),
('Honduras','El Paraíso','San Matías','honduras-el-paraiso-san-matias'),
('Honduras','El Paraíso','Soledad','honduras-el-paraiso-soledad'),
('Honduras','El Paraíso','Teupasenti','honduras-el-paraiso-teupasenti'),
('Honduras','El Paraíso','Texiguat','honduras-el-paraiso-texiguat'),
('Honduras','El Paraíso','Trojes','honduras-el-paraiso-trojes'),
('Honduras','El Paraíso','Vado Ancho','honduras-el-paraiso-vado-ancho'),
('Honduras','El Paraíso','Yauyupe','honduras-el-paraiso-yauyupe');

-- FRANCISCO MORAZÁN (28 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Francisco Morazán','Tegucigalpa','honduras-fm-tegucigalpa'),
('Honduras','Francisco Morazán','Distrito Central','honduras-fm-distrito-central'),
('Honduras','Francisco Morazán','Alubaren','honduras-fm-alubaren'),
('Honduras','Francisco Morazán','Cedros','honduras-fm-cedros'),
('Honduras','Francisco Morazán','Curarén','honduras-fm-curaren'),
('Honduras','Francisco Morazán','El Porvenir','honduras-fm-el-porvenir'),
('Honduras','Francisco Morazán','Guaimaca','honduras-fm-guaimaca'),
('Honduras','Francisco Morazán','La Libertad','honduras-fm-la-libertad'),
('Honduras','Francisco Morazán','La Venta','honduras-fm-la-venta'),
('Honduras','Francisco Morazán','Lepaterique','honduras-fm-lepaterique'),
('Honduras','Francisco Morazán','Maraita','honduras-fm-maraita'),
('Honduras','Francisco Morazán','Marale','honduras-fm-marale'),
('Honduras','Francisco Morazán','Nueva Armenia','honduras-fm-nueva-armenia'),
('Honduras','Francisco Morazán','Ojojona','honduras-fm-ojojona'),
('Honduras','Francisco Morazán','Orica','honduras-fm-orica'),
('Honduras','Francisco Morazán','Reitoca','honduras-fm-reitoca'),
('Honduras','Francisco Morazán','Sabanagrande','honduras-fm-sabanagrande'),
('Honduras','Francisco Morazán','San Ana','honduras-fm-san-ana'),
('Honduras','Francisco Morazán','San Buenaventura','honduras-fm-san-buenaventura'),
('Honduras','Francisco Morazán','San Ignacio','honduras-fm-san-ignacio'),
('Honduras','Francisco Morazán','San Juan de Flores','honduras-fm-san-juan-de-flores'),
('Honduras','Francisco Morazán','San Miguelito','honduras-fm-san-miguelito'),
('Honduras','Francisco Morazán','Santa Ana','honduras-fm-santa-ana'),
('Honduras','Francisco Morazán','Santa Lucía','honduras-fm-santa-lucia'),
('Honduras','Francisco Morazán','Talanga','honduras-fm-talanga'),
('Honduras','Francisco Morazán','Tatumbla','honduras-fm-tatumbla'),
('Honduras','Francisco Morazán','Valle de Ángeles','honduras-fm-valle-de-angeles'),
('Honduras','Francisco Morazán','Villa de San Francisco','honduras-fm-villa-de-san-francisco');

-- GRACIAS A DIOS (6 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Gracias a Dios','Puerto Lempira','honduras-gad-puerto-lempira'),
('Honduras','Gracias a Dios','Brus Laguna','honduras-gad-brus-laguna'),
('Honduras','Gracias a Dios','Ahuas','honduras-gad-ahuas'),
('Honduras','Gracias a Dios','Juan Francisco Bulnes','honduras-gad-juan-francisco-bulnes'),
('Honduras','Gracias a Dios','Villeda Morales','honduras-gad-villeda-morales'),
('Honduras','Gracias a Dios','Wampusirpe','honduras-gad-wampusirpe');

-- INTIBUCÁ (17 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Intibucá','La Esperanza','honduras-intibuca-la-esperanza'),
('Honduras','Intibucá','Camasca','honduras-intibuca-camasca'),
('Honduras','Intibucá','Colomoncagua','honduras-intibuca-colomoncagua'),
('Honduras','Intibucá','Concepción','honduras-intibuca-concepcion'),
('Honduras','Intibucá','Dolores','honduras-intibuca-dolores'),
('Honduras','Intibucá','Intibucá','honduras-intibuca-intibuca'),
('Honduras','Intibucá','Jesús de Otoro','honduras-intibuca-jesus-de-otoro'),
('Honduras','Intibucá','Magdalena','honduras-intibuca-magdalena'),
('Honduras','Intibucá','Masaguara','honduras-intibuca-masaguara'),
('Honduras','Intibucá','San Antonio','honduras-intibuca-san-antonio'),
('Honduras','Intibucá','San Isidro','honduras-intibuca-san-isidro'),
('Honduras','Intibucá','San Juan','honduras-intibuca-san-juan'),
('Honduras','Intibucá','San Marcos de Sierra','honduras-intibuca-san-marcos-de-sierra'),
('Honduras','Intibucá','San Miguel Guancapla','honduras-intibuca-san-miguel-guancapla'),
('Honduras','Intibucá','Santa Lucía','honduras-intibuca-santa-lucia'),
('Honduras','Intibucá','Yamaranguila','honduras-intibuca-yamaranguila'),
('Honduras','Intibucá','San Francisco de Opalaca','honduras-intibuca-san-francisco-de-opalaca');

-- ISLAS DE LA BAHÍA (4 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Islas de la Bahía','Roatán','honduras-islas-roatan'),
('Honduras','Islas de la Bahía','Guanaja','honduras-islas-guanaja'),
('Honduras','Islas de la Bahía','José Santos Guardiola','honduras-islas-jose-santos-guardiola'),
('Honduras','Islas de la Bahía','Utila','honduras-islas-utila');

-- LA PAZ (19 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','La Paz','La Paz','honduras-la-paz-la-paz'),
('Honduras','La Paz','Aguanqueterique','honduras-la-paz-aguanqueterique'),
('Honduras','La Paz','Cabañas','honduras-la-paz-cabanas'),
('Honduras','La Paz','Cane','honduras-la-paz-cane'),
('Honduras','La Paz','Chinacla','honduras-la-paz-chinacla'),
('Honduras','La Paz','Guajiquiro','honduras-la-paz-guajiquiro'),
('Honduras','La Paz','Lauterique','honduras-la-paz-lauterique'),
('Honduras','La Paz','Marcala','honduras-la-paz-marcala'),
('Honduras','La Paz','Mercedes de Oriente','honduras-la-paz-mercedes-de-oriente'),
('Honduras','La Paz','Opatoro','honduras-la-paz-opatoro'),
('Honduras','La Paz','San Antonio del Norte','honduras-la-paz-san-antonio-del-norte'),
('Honduras','La Paz','San Juan','honduras-la-paz-san-juan'),
('Honduras','La Paz','San Pedro de Tutule','honduras-la-paz-san-pedro-de-tutule'),
('Honduras','La Paz','Santa Ana','honduras-la-paz-santa-ana'),
('Honduras','La Paz','Santa Elena','honduras-la-paz-santa-elena'),
('Honduras','La Paz','Santa María','honduras-la-paz-santa-maria'),
('Honduras','La Paz','Santiago de Puringla','honduras-la-paz-santiago-de-puringla'),
('Honduras','La Paz','Yarula','honduras-la-paz-yarula'),
('Honduras','La Paz','San José','honduras-la-paz-san-jose');

-- LEMPIRA (28 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Lempira','Gracias','honduras-lempira-gracias'),
('Honduras','Lempira','Belén','honduras-lempira-belen'),
('Honduras','Lempira','Candelaria','honduras-lempira-candelaria'),
('Honduras','Lempira','Cololaca','honduras-lempira-cololaca'),
('Honduras','Lempira','Erandique','honduras-lempira-erandique'),
('Honduras','Lempira','Gualcince','honduras-lempira-gualcince'),
('Honduras','Lempira','Guarita','honduras-lempira-guarita'),
('Honduras','Lempira','La Campa','honduras-lempira-la-campa'),
('Honduras','Lempira','La Iguala','honduras-lempira-la-iguala'),
('Honduras','Lempira','La Unión','honduras-lempira-la-union'),
('Honduras','Lempira','La Virtud','honduras-lempira-la-virtud'),
('Honduras','Lempira','Lepaera','honduras-lempira-lepaera'),
('Honduras','Lempira','Mapulaca','honduras-lempira-mapulaca'),
('Honduras','Lempira','Piraera','honduras-lempira-piraera'),
('Honduras','Lempira','San Andrés','honduras-lempira-san-andres'),
('Honduras','Lempira','San Francisco','honduras-lempira-san-francisco'),
('Honduras','Lempira','San Juan de Guarita','honduras-lempira-san-juan-de-guarita'),
('Honduras','Lempira','San Manuel Colohete','honduras-lempira-san-manuel-colohete'),
('Honduras','Lempira','San Rafael','honduras-lempira-san-rafael'),
('Honduras','Lempira','San Sebastián','honduras-lempira-san-sebastian'),
('Honduras','Lempira','Santa Cruz','honduras-lempira-santa-cruz'),
('Honduras','Lempira','Talgua','honduras-lempira-talgua'),
('Honduras','Lempira','Tambla','honduras-lempira-tambla'),
('Honduras','Lempira','Tomalá','honduras-lempira-tomala'),
('Honduras','Lempira','Valladolid','honduras-lempira-valladolid'),
('Honduras','Lempira','Virginia','honduras-lempira-virginia'),
('Honduras','Lempira','San Marcos de Caiquín','honduras-lempira-san-marcos-de-caiquin'),
('Honduras','Lempira','Las Flores','honduras-lempira-las-flores');

-- OCOTEPEQUE (16 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Ocotepeque','Ocotepeque','honduras-ocotepeque-ocotepeque'),
('Honduras','Ocotepeque','Belén Gualcho','honduras-ocotepeque-belen-gualcho'),
('Honduras','Ocotepeque','Concepción','honduras-ocotepeque-concepcion'),
('Honduras','Ocotepeque','Dolores Merendón','honduras-ocotepeque-dolores-merendon'),
('Honduras','Ocotepeque','Fraternidad','honduras-ocotepeque-fraternidad'),
('Honduras','Ocotepeque','La Encarnación','honduras-ocotepeque-la-encarnacion'),
('Honduras','Ocotepeque','La Labor','honduras-ocotepeque-la-labor'),
('Honduras','Ocotepeque','Lucerna','honduras-ocotepeque-lucerna'),
('Honduras','Ocotepeque','Mercedes','honduras-ocotepeque-mercedes'),
('Honduras','Ocotepeque','San Fernando','honduras-ocotepeque-san-fernando'),
('Honduras','Ocotepeque','San Francisco del Valle','honduras-ocotepeque-san-francisco-del-valle'),
('Honduras','Ocotepeque','San Jorge','honduras-ocotepeque-san-jorge'),
('Honduras','Ocotepeque','San Marcos','honduras-ocotepeque-san-marcos'),
('Honduras','Ocotepeque','Santa Fe','honduras-ocotepeque-santa-fe'),
('Honduras','Ocotepeque','Sensenti','honduras-ocotepeque-sensenti'),
('Honduras','Ocotepeque','Sinuapa','honduras-ocotepeque-sinuapa');

-- OLANCHO (23 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Olancho','Juticalpa','honduras-olancho-juticalpa'),
('Honduras','Olancho','Campamento','honduras-olancho-campamento'),
('Honduras','Olancho','Catacamas','honduras-olancho-catacamas'),
('Honduras','Olancho','Concordia','honduras-olancho-concordia'),
('Honduras','Olancho','Dulce Nombre de Culmí','honduras-olancho-dulce-nombre-de-culmi'),
('Honduras','Olancho','El Rosario','honduras-olancho-el-rosario'),
('Honduras','Olancho','Esquipulas del Norte','honduras-olancho-esquipulas-del-norte'),
('Honduras','Olancho','Gualaco','honduras-olancho-gualaco'),
('Honduras','Olancho','Guarizama','honduras-olancho-guarizama'),
('Honduras','Olancho','Guata','honduras-olancho-guata'),
('Honduras','Olancho','Jano','honduras-olancho-jano'),
('Honduras','Olancho','La Unión','honduras-olancho-la-union'),
('Honduras','Olancho','Mangulile','honduras-olancho-mangulile'),
('Honduras','Olancho','Manto','honduras-olancho-manto'),
('Honduras','Olancho','Salamá','honduras-olancho-salama'),
('Honduras','Olancho','San Esteban','honduras-olancho-san-esteban'),
('Honduras','Olancho','San Francisco de Becerra','honduras-olancho-san-francisco-de-becerra'),
('Honduras','Olancho','San Francisco de la Paz','honduras-olancho-san-francisco-de-la-paz'),
('Honduras','Olancho','Santa María del Real','honduras-olancho-santa-maria-del-real'),
('Honduras','Olancho','Silca','honduras-olancho-silca'),
('Honduras','Olancho','Yocón','honduras-olancho-yocon'),
('Honduras','Olancho','Patuca','honduras-olancho-patuca'),
('Honduras','Olancho','Coalición','honduras-olancho-coalicion');

-- SANTA BÁRBARA (28 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Santa Bárbara','Santa Bárbara','honduras-santa-barbara-santa-barbara'),
('Honduras','Santa Bárbara','Arada','honduras-santa-barbara-arada'),
('Honduras','Santa Bárbara','Atima','honduras-santa-barbara-atima'),
('Honduras','Santa Bárbara','Azacualpa','honduras-santa-barbara-azacualpa'),
('Honduras','Santa Bárbara','Ceguaca','honduras-santa-barbara-ceguaca'),
('Honduras','Santa Bárbara','Chinda','honduras-santa-barbara-chinda'),
('Honduras','Santa Bárbara','Concepción del Norte','honduras-santa-barbara-concepcion-del-norte'),
('Honduras','Santa Bárbara','Concepción del Sur','honduras-santa-barbara-concepcion-del-sur'),
('Honduras','Santa Bárbara','El Níspero','honduras-santa-barbara-el-nispero'),
('Honduras','Santa Bárbara','Gualala','honduras-santa-barbara-gualala'),
('Honduras','Santa Bárbara','Ilama','honduras-santa-barbara-ilama'),
('Honduras','Santa Bárbara','Las Vegas','honduras-santa-barbara-las-vegas'),
('Honduras','Santa Bárbara','Macuelizo','honduras-santa-barbara-macuelizo'),
('Honduras','Santa Bárbara','Naranjito','honduras-santa-barbara-naranjito'),
('Honduras','Santa Bárbara','Nuevo Celilac','honduras-santa-barbara-nuevo-celilac'),
('Honduras','Santa Bárbara','Petoa','honduras-santa-barbara-petoa'),
('Honduras','Santa Bárbara','Protección','honduras-santa-barbara-proteccion'),
('Honduras','Santa Bárbara','Quimistán','honduras-santa-barbara-quimistan'),
('Honduras','Santa Bárbara','San Francisco de Ojuera','honduras-santa-barbara-san-francisco-de-ojuera'),
('Honduras','Santa Bárbara','San José de Colinas','honduras-santa-barbara-san-jose-de-colinas'),
('Honduras','Santa Bárbara','San Luis','honduras-santa-barbara-san-luis'),
('Honduras','Santa Bárbara','San Marcos','honduras-santa-barbara-san-marcos'),
('Honduras','Santa Bárbara','San Nicolás','honduras-santa-barbara-san-nicolas'),
('Honduras','Santa Bárbara','San Pedro Zacapa','honduras-santa-barbara-san-pedro-zacapa'),
('Honduras','Santa Bárbara','San Vicente Centenario','honduras-santa-barbara-san-vicente-centenario'),
('Honduras','Santa Bárbara','Santa Rita','honduras-santa-barbara-santa-rita'),
('Honduras','Santa Bárbara','Talgua','honduras-santa-barbara-talgua'),
('Honduras','Santa Bárbara','Trinidad','honduras-santa-barbara-trinidad');

-- VALLE (9 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Valle','Nacaome','honduras-valle-nacaome'),
('Honduras','Valle','Alianza','honduras-valle-alianza'),
('Honduras','Valle','Amapala','honduras-valle-amapala'),
('Honduras','Valle','Aramecina','honduras-valle-aramecina'),
('Honduras','Valle','Caridad','honduras-valle-caridad'),
('Honduras','Valle','Goascorán','honduras-valle-goascoran'),
('Honduras','Valle','Langue','honduras-valle-langue'),
('Honduras','Valle','San Francisco de Coray','honduras-valle-san-francisco-de-coray'),
('Honduras','Valle','San Lorenzo','honduras-valle-san-lorenzo');

-- YORO (11 municipios)
INSERT INTO locations (country, state, city, slug) VALUES
('Honduras','Yoro','Yoro','honduras-yoro-yoro'),
('Honduras','Yoro','Arenal','honduras-yoro-arenal'),
('Honduras','Yoro','El Negrito','honduras-yoro-el-negrito'),
('Honduras','Yoro','El Progreso','honduras-yoro-el-progreso'),
('Honduras','Yoro','Jocon','honduras-yoro-jocon'),
('Honduras','Yoro','Morazán','honduras-yoro-morazan'),
('Honduras','Yoro','Olanchito','honduras-yoro-olanchito'),
('Honduras','Yoro','Santa Rita','honduras-yoro-santa-rita'),
('Honduras','Yoro','Sulaco','honduras-yoro-sulaco'),
('Honduras','Yoro','Victoria','honduras-yoro-victoria'),
('Honduras','Yoro','Yorito','honduras-yoro-yorito');

-- ============================================================
-- LATINOAMÉRICA - Ciudades principales
-- ============================================================

-- MÉXICO
INSERT INTO locations (country, state, city, slug) VALUES
('México','Ciudad de México','Ciudad de México','mexico-cdmx'),
('México','Jalisco','Guadalajara','mexico-guadalajara'),
('México','Nuevo León','Monterrey','mexico-monterrey'),
('México','Puebla','Puebla','mexico-puebla'),
('México','Baja California','Tijuana','mexico-tijuana'),
('México','Guanajuato','León','mexico-leon'),
('México','Chihuahua','Ciudad Juárez','mexico-ciudad-juarez'),
('México','Jalisco','Zapopan','mexico-zapopan'),
('México','Yucatán','Mérida','mexico-merida'),
('México','Quintana Roo','Cancún','mexico-cancun'),
('México','San Luis Potosí','San Luis Potosí','mexico-san-luis-potosi'),
('México','Aguascalientes','Aguascalientes','mexico-aguascalientes'),
('México','Querétaro','Querétaro','mexico-queretaro'),
('México','Sonora','Hermosillo','mexico-hermosillo'),
('México','Chihuahua','Chihuahua','mexico-chihuahua'),
('México','Estado de México','Ecatepec','mexico-ecatepec'),
('México','Jalisco','Guadalajara Zona Metro','mexico-guadalajara-zm'),
('México','Veracruz','Veracruz','mexico-veracruz'),
('México','Tabasco','Villahermosa','mexico-villahermosa'),
('México','Oaxaca','Oaxaca','mexico-oaxaca'),
('México','Sinaloa','Culiacán','mexico-culiacan'),
('México','Tamaulipas','Tampico','mexico-tampico'),
('México','Morelos','Cuernavaca','mexico-cuernavaca'),
('México','Coahuila','Saltillo','mexico-saltillo'),
('México','Michoacán','Morelia','mexico-morelia');

-- GUATEMALA
INSERT INTO locations (country, state, city, slug) VALUES
('Guatemala','Guatemala','Ciudad de Guatemala','guatemala-ciudad-de-guatemala'),
('Guatemala','Guatemala','Mixco','guatemala-mixco'),
('Guatemala','Guatemala','Villa Nueva','guatemala-villa-nueva'),
('Guatemala','Sacatepéquez','Antigua Guatemala','guatemala-antigua'),
('Guatemala','Quetzaltenango','Quetzaltenango','guatemala-quetzaltenango'),
('Guatemala','Escuintla','Escuintla','guatemala-escuintla'),
('Guatemala','Alta Verapaz','Cobán','guatemala-coban'),
('Guatemala','Petén','Flores','guatemala-flores'),
('Guatemala','Izabal','Puerto Barrios','guatemala-puerto-barrios'),
('Guatemala','Jutiapa','Jutiapa','guatemala-jutiapa'),
('Guatemala','Chiquimula','Chiquimula','guatemala-chiquimula'),
('Guatemala','Retalhuleu','Retalhuleu','guatemala-retalhuleu');

-- EL SALVADOR
INSERT INTO locations (country, state, city, slug) VALUES
('El Salvador','San Salvador','San Salvador','elsalvador-san-salvador'),
('El Salvador','San Miguel','San Miguel','elsalvador-san-miguel'),
('El Salvador','Santa Ana','Santa Ana','elsalvador-santa-ana'),
('El Salvador','San Salvador','Soyapango','elsalvador-soyapango'),
('El Salvador','San Salvador','Mejicanos','elsalvador-mejicanos'),
('El Salvador','La Libertad','Santa Tecla','elsalvador-santa-tecla'),
('El Salvador','San Salvador','Apopa','elsalvador-apopa'),
('El Salvador','Usulután','Usulután','elsalvador-usulutan'),
('El Salvador','La Unión','La Unión','elsalvador-la-union'),
('El Salvador','Sonsonate','Sonsonate','elsalvador-sonsonate');

-- NICARAGUA
INSERT INTO locations (country, state, city, slug) VALUES
('Nicaragua','Managua','Managua','nicaragua-managua'),
('Nicaragua','León','León','nicaragua-leon'),
('Nicaragua','Masaya','Masaya','nicaragua-masaya'),
('Nicaragua','Chinandega','Chinandega','nicaragua-chinandega'),
('Nicaragua','Matagalpa','Matagalpa','nicaragua-matagalpa'),
('Nicaragua','Granada','Granada','nicaragua-granada'),
('Nicaragua','Estelí','Estelí','nicaragua-esteli'),
('Nicaragua','Carazo','Jinotepe','nicaragua-jinotepe'),
('Nicaragua','Jinotega','Jinotega','nicaragua-jinotega'),
('Nicaragua','RAAN','Bilwi','nicaragua-bilwi');

-- COSTA RICA
INSERT INTO locations (country, state, city, slug) VALUES
('Costa Rica','San José','San José','costarica-san-jose'),
('Costa Rica','Alajuela','Alajuela','costarica-alajuela'),
('Costa Rica','Cartago','Cartago','costarica-cartago'),
('Costa Rica','Heredia','Heredia','costarica-heredia'),
('Costa Rica','Guanacaste','Liberia','costarica-liberia'),
('Costa Rica','Limón','Limón','costarica-limon'),
('Costa Rica','Puntarenas','Puntarenas','costarica-puntarenas'),
('Costa Rica','San José','Desamparados','costarica-desamparados'),
('Costa Rica','San José','Pérez Zeledón','costarica-perez-zeledon'),
('Costa Rica','Alajuela','San Ramón','costarica-san-ramon');

-- PANAMÁ
INSERT INTO locations (country, state, city, slug) VALUES
('Panamá','Panamá','Ciudad de Panamá','panama-ciudad-de-panama'),
('Panamá','Colón','Colón','panama-colon'),
('Panamá','Chiriquí','David','panama-david'),
('Panamá','Panamá Oeste','La Chorrera','panama-la-chorrera'),
('Panamá','Veraguas','Santiago','panama-santiago'),
('Panamá','Herrera','Chitré','panama-chitre'),
('Panamá','Los Santos','Las Tablas','panama-las-tablas'),
('Panamá','Coclé','Penonomé','panama-penonome'),
('Panamá','Bocas del Toro','Bocas del Toro','panama-bocas-del-toro'),
('Panamá','Darién','La Palma','panama-la-palma');

-- COLOMBIA
INSERT INTO locations (country, state, city, slug) VALUES
('Colombia','Bogotá D.C.','Bogotá','colombia-bogota'),
('Colombia','Antioquia','Medellín','colombia-medellin'),
('Colombia','Valle del Cauca','Cali','colombia-cali'),
('Colombia','Atlántico','Barranquilla','colombia-barranquilla'),
('Colombia','Bolívar','Cartagena','colombia-cartagena'),
('Colombia','Norte de Santander','Cúcuta','colombia-cucuta'),
('Colombia','Santander','Bucaramanga','colombia-bucaramanga'),
('Colombia','Risaralda','Pereira','colombia-pereira'),
('Colombia','Magdalena','Santa Marta','colombia-santa-marta'),
('Colombia','Caldas','Manizales','colombia-manizales'),
('Colombia','Tolima','Ibagué','colombia-ibague'),
('Colombia','Nariño','Pasto','colombia-pasto'),
('Colombia','Huila','Neiva','colombia-neiva'),
('Colombia','Córdoba','Montería','colombia-monteria'),
('Colombia','Meta','Villavicencio','colombia-villavicencio'),
('Colombia','Quindío','Armenia','colombia-armenia'),
('Colombia','Cauca','Popayán','colombia-popayan'),
('Colombia','Cesar','Valledupar','colombia-valledupar'),
('Colombia','Sucre','Sincelejo','colombia-sincelejo');

-- VENEZUELA
INSERT INTO locations (country, state, city, slug) VALUES
('Venezuela','Distrito Capital','Caracas','venezuela-caracas'),
('Venezuela','Zulia','Maracaibo','venezuela-maracaibo'),
('Venezuela','Carabobo','Valencia','venezuela-valencia'),
('Venezuela','Lara','Barquisimeto','venezuela-barquisimeto'),
('Venezuela','Aragua','Maracay','venezuela-maracay'),
('Venezuela','Mérida','Mérida','venezuela-merida'),
('Venezuela','Bolívar','Ciudad Bolívar','venezuela-ciudad-bolivar'),
('Venezuela','Anzoátegui','Barcelona','venezuela-barcelona'),
('Venezuela','Táchira','San Cristóbal','venezuela-san-cristobal'),
('Venezuela','Monagas','Maturín','venezuela-maturin'),
('Venezuela','Miranda','Los Teques','venezuela-los-teques'),
('Venezuela','Miranda','Guarenas','venezuela-guarenas'),
('Venezuela','Zulia','Cabimas','venezuela-cabimas'),
('Venezuela','Guárico','San Juan de los Morros','venezuela-san-juan-de-los-morros'),
('Venezuela','Nueva Esparta','Porlamar','venezuela-porlamar'),
('Venezuela','Falcón','Coro','venezuela-coro');

-- ECUADOR
INSERT INTO locations (country, state, city, slug) VALUES
('Ecuador','Pichincha','Quito','ecuador-quito'),
('Ecuador','Guayas','Guayaquil','ecuador-guayaquil'),
('Ecuador','Azuay','Cuenca','ecuador-cuenca'),
('Ecuador','Tungurahua','Ambato','ecuador-ambato'),
('Ecuador','Manabí','Manta','ecuador-manta'),
('Ecuador','Manabí','Portoviejo','ecuador-portoviejo'),
('Ecuador','Loja','Loja','ecuador-loja'),
('Ecuador','El Oro','Machala','ecuador-machala'),
('Ecuador','Esmeraldas','Esmeraldas','ecuador-esmeraldas'),
('Ecuador','Imbabura','Ibarra','ecuador-ibarra'),
('Ecuador','Los Ríos','Quevedo','ecuador-quevedo'),
('Ecuador','Santo Domingo','Santo Domingo','ecuador-santo-domingo');

-- PERÚ
INSERT INTO locations (country, state, city, slug) VALUES
('Perú','Lima','Lima','peru-lima'),
('Perú','Arequipa','Arequipa','peru-arequipa'),
('Perú','La Libertad','Trujillo','peru-trujillo'),
('Perú','Lambayeque','Chiclayo','peru-chiclayo'),
('Perú','Piura','Piura','peru-piura'),
('Perú','Loreto','Iquitos','peru-iquitos'),
('Perú','Junín','Huancayo','peru-huancayo'),
('Perú','Cusco','Cusco','peru-cusco'),
('Perú','Callao','Callao','peru-callao'),
('Perú','Ancash','Chimbote','peru-chimbote'),
('Perú','Tacna','Tacna','peru-tacna'),
('Perú','Ica','Ica','peru-ica');

-- BOLIVIA
INSERT INTO locations (country, state, city, slug) VALUES
('Bolivia','La Paz','La Paz','bolivia-la-paz'),
('Bolivia','Santa Cruz','Santa Cruz de la Sierra','bolivia-santa-cruz'),
('Bolivia','Cochabamba','Cochabamba','bolivia-cochabamba'),
('Bolivia','Oruro','Oruro','bolivia-oruro'),
('Bolivia','Potosí','Potosí','bolivia-potosi'),
('Bolivia','Chuquisaca','Sucre','bolivia-sucre'),
('Bolivia','Tarija','Tarija','bolivia-tarija'),
('Bolivia','Beni','Trinidad','bolivia-trinidad'),
('Bolivia','Pando','Cobija','bolivia-cobija'),
('Bolivia','Santa Cruz','Montero','bolivia-montero');

-- CHILE
INSERT INTO locations (country, state, city, slug) VALUES
('Chile','Región Metropolitana','Santiago','chile-santiago'),
('Chile','Biobío','Concepción','chile-concepcion'),
('Chile','Valparaíso','Valparaíso','chile-valparaiso'),
('Chile','Coquimbo','La Serena','chile-la-serena'),
('Chile','Antofagasta','Antofagasta','chile-antofagasta'),
('Chile','La Araucanía','Temuco','chile-temuco'),
('Chile','O''Higgins','Rancagua','chile-rancagua'),
('Chile','Maule','Talca','chile-talca'),
('Chile','Arica y Parinacota','Arica','chile-arica'),
('Chile','Tarapacá','Iquique','chile-iquique'),
('Chile','Los Lagos','Puerto Montt','chile-puerto-montt'),
('Chile','Los Ríos','Valdivia','chile-valdivia');

-- ARGENTINA
INSERT INTO locations (country, state, city, slug) VALUES
('Argentina','Buenos Aires','Buenos Aires','argentina-buenos-aires'),
('Argentina','Córdoba','Córdoba','argentina-cordoba'),
('Argentina','Santa Fe','Rosario','argentina-rosario'),
('Argentina','Mendoza','Mendoza','argentina-mendoza'),
('Argentina','Tucumán','San Miguel de Tucumán','argentina-tucuman'),
('Argentina','Buenos Aires','La Plata','argentina-la-plata'),
('Argentina','Buenos Aires','Mar del Plata','argentina-mar-del-plata'),
('Argentina','Salta','Salta','argentina-salta'),
('Argentina','San Juan','San Juan','argentina-san-juan'),
('Argentina','Chaco','Resistencia','argentina-resistencia'),
('Argentina','Entre Ríos','Paraná','argentina-parana'),
('Argentina','Misiones','Posadas','argentina-posadas'),
('Argentina','Neuquén','Neuquén','argentina-neuquen'),
('Argentina','Corrientes','Corrientes','argentina-corrientes');

-- URUGUAY
INSERT INTO locations (country, state, city, slug) VALUES
('Uruguay','Montevideo','Montevideo','uruguay-montevideo'),
('Uruguay','Salto','Salto','uruguay-salto'),
('Uruguay','Paysandú','Paysandú','uruguay-paysandu'),
('Uruguay','Canelones','Las Piedras','uruguay-las-piedras'),
('Uruguay','Rivera','Rivera','uruguay-rivera'),
('Uruguay','Maldonado','Maldonado','uruguay-maldonado'),
('Uruguay','Colonia','Colonia del Sacramento','uruguay-colonia-del-sacramento');

-- PARAGUAY
INSERT INTO locations (country, state, city, slug) VALUES
('Paraguay','Asunción','Asunción','paraguay-asuncion'),
('Paraguay','Alto Paraná','Ciudad del Este','paraguay-ciudad-del-este'),
('Paraguay','Central','San Lorenzo','paraguay-san-lorenzo'),
('Paraguay','Central','Luque','paraguay-luque'),
('Paraguay','Central','Capiatá','paraguay-capiata'),
('Paraguay','Caaguazú','Coronel Oviedo','paraguay-coronel-oviedo'),
('Paraguay','Itapúa','Encarnación','paraguay-encarnacion');

-- BRASIL
INSERT INTO locations (country, state, city, slug) VALUES
('Brasil','São Paulo','São Paulo','brasil-sao-paulo'),
('Brasil','Rio de Janeiro','Rio de Janeiro','brasil-rio-de-janeiro'),
('Brasil','Distrito Federal','Brasília','brasil-brasilia'),
('Brasil','Bahia','Salvador','brasil-salvador'),
('Brasil','Ceará','Fortaleza','brasil-fortaleza'),
('Brasil','Minas Gerais','Belo Horizonte','brasil-belo-horizonte'),
('Brasil','Amazonas','Manaus','brasil-manaus'),
('Brasil','Paraná','Curitiba','brasil-curitiba'),
('Brasil','Pernambuco','Recife','brasil-recife'),
('Brasil','Rio Grande do Sul','Porto Alegre','brasil-porto-alegre'),
('Brasil','Goiás','Goiânia','brasil-goiania'),
('Brasil','Pará','Belém','brasil-belem');

-- REPÚBLICA DOMINICANA
INSERT INTO locations (country, state, city, slug) VALUES
('República Dominicana','Distrito Nacional','Santo Domingo','rd-santo-domingo'),
('República Dominicana','Santiago','Santiago de los Caballeros','rd-santiago'),
('República Dominicana','La Romana','La Romana','rd-la-romana'),
('República Dominicana','San Pedro de Macorís','San Pedro de Macorís','rd-san-pedro-de-macoris'),
('República Dominicana','San Cristóbal','San Cristóbal','rd-san-cristobal'),
('República Dominicana','Puerto Plata','Puerto Plata','rd-puerto-plata'),
('República Dominicana','La Vega','La Vega','rd-la-vega');

-- CUBA
INSERT INTO locations (country, state, city, slug) VALUES
('Cuba','La Habana','La Habana','cuba-la-habana'),
('Cuba','Santiago de Cuba','Santiago de Cuba','cuba-santiago-de-cuba'),
('Cuba','Camagüey','Camagüey','cuba-camaguey'),
('Cuba','Holguín','Holguín','cuba-holguin'),
('Cuba','Guantánamo','Guantánamo','cuba-guantanamo'),
('Cuba','Santa Clara','Santa Clara','cuba-santa-clara');

-- PUERTO RICO
INSERT INTO locations (country, state, city, slug) VALUES
('Puerto Rico','San Juan','San Juan','pr-san-juan'),
('Puerto Rico','Bayamón','Bayamón','pr-bayamon'),
('Puerto Rico','Carolina','Carolina','pr-carolina'),
('Puerto Rico','Ponce','Ponce','pr-ponce'),
('Puerto Rico','Caguas','Caguas','pr-caguas');

-- ESPAÑA
INSERT INTO locations (country, state, city, slug) VALUES
('España','Madrid','Madrid','espana-madrid'),
('España','Cataluña','Barcelona','espana-barcelona'),
('España','Valencia','Valencia','espana-valencia'),
('España','Andalucía','Sevilla','espana-sevilla'),
('España','Aragón','Zaragoza','espana-zaragoza'),
('España','Andalucía','Málaga','espana-malaga'),
('España','País Vasco','Bilbao','espana-bilbao'),
('España','Galicia','A Coruña','espana-a-coruna'),
('España','Canarias','Las Palmas','espana-las-palmas');

-- ESTADOS UNIDOS (comunidades hispanas)
INSERT INTO locations (country, state, city, slug) VALUES
('Estados Unidos','Florida','Miami','eeuu-miami'),
('Estados Unidos','California','Los Ángeles','eeuu-los-angeles'),
('Estados Unidos','New York','Nueva York','eeuu-nueva-york'),
('Estados Unidos','Texas','Houston','eeuu-houston'),
('Estados Unidos','Illinois','Chicago','eeuu-chicago'),
('Estados Unidos','Arizona','Phoenix','eeuu-phoenix'),
('Estados Unidos','Texas','San Antonio','eeuu-san-antonio'),
('Estados Unidos','Texas','Dallas','eeuu-dallas'),
('Estados Unidos','California','San Diego','eeuu-san-diego'),
('Estados Unidos','Florida','Orlando','eeuu-orlando');
