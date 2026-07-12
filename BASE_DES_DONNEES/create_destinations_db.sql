-- Création de la table destinations
USE travelia2_safari_rdc;

CREATE TABLE IF NOT EXISTS destinations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    pays VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    image VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de données de test
INSERT INTO destinations (nom, pays, description, prix, image) VALUES
('Parc National de la Virunga', 'République Démocratique du Congo', 'Le plus ancien parc national d''Afrique, abritant les gorilles de montagne. Une expérience unique de safari et de trekking dans un paysage volcanique spectaculaire.', 250.00, 'IMG_G/IMAGE_RDC/Parc_Virunga/V1.jpg'),
('Falls de Boyoma', 'République Démocratique du Congo', 'Série de sept chutes d''eau spectaculaires sur le fleuve Congo. Un site naturel impressionnant offrant des paysages à couper le souffle.', 180.00, 'IMG_G/IMAGE_RDC/Falls_Boyoma/B1.jpg'),
('Lac Kivu', 'République Démocratique du Congo', 'Un magnifique lac bordé de collines verdoyantes, parfait pour la détente, la baignade et les excursions en bateau. Ambiance tropicale garantie.', 120.00, 'IMG_G/IMAGE_RDC/Lac_Kivu/K1.jpg'),
('Rwenzori Mountains', 'Ouganda', 'Les "Montagnes de la Lune" avec leurs sommets enneigés éternels. Un paradis pour les randonneurs en quête d''aventure et de paysages grandioses.', 300.00, 'IMG_G/IMAGE_UG/Rwenzori_Mountains/M1.jpg'),
('Parc National des Volcans', 'Rwanda', 'Fameux pour ses gorilles de montagne, ce parc offre une expérience de trekking inoubliable au cœur des volcans du Virunga.', 280.00, 'IMG_G/IMAGE_RW/Parc_Volcans/V1.jpg'),
('Lac Tanganyika', 'Tanzanie', 'Le plus profond lac d''Afrique, aux eaux cristallines et aux plages de sable blanc. Idéal pour la plongée, la pêche et la détente.', 200.00, 'IMG_G/IMAGE_TZ/Lac_Tanganyika/T1.jpg'),
('Parc National de Gombe', 'Tanzanie', 'Sanctuaire des chimpanzés étudiés par Jane Goodall. Une expérience unique d''observation des primates dans leur habitat naturel.', 320.00, 'IMG_G/IMAGE_TZ/Parc_Gombe/G1.jpg'),
('Lac Victoria', 'Kenya', 'Le plus grand lac d''Afrique, offrant des couchers de soleil spectaculaires et une riche biodiversité. Destination idéale pour les amoureux de la nature.', 150.00, 'IMG_G/IMAGE_KE/Lac_Victoria/V1.jpg'),
('Parc National de Kahuzi-Biega', 'République Démocratique du Congo', 'Refuge des gorilles de plaine orientaux dans une forêt dense et mystérieuse. Une aventure safari authentique.', 270.00, 'IMG_G/IMAGE_RDC/Parc_Kahuzi_Biega/K1.jpg'),
('Réserve de Selous', 'Tanzanie', 'La plus grande réserve animalière d''Afrique, avec une concentration exceptionnelle d''éléphants, lions et hippopotames.', 350.00, 'IMG_G/IMAGE_TZ/Reserve_Selous/S1.jpg');
