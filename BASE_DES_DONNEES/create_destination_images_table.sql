-- Création de la table destination_images pour la galerie d'images
USE travelia2_safari_rdc;

CREATE TABLE IF NOT EXISTS destination_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destination_id INT NOT NULL,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    is_primary TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_destination_images_destination FOREIGN KEY (destination_id) REFERENCES destinations(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les requêtes
CREATE INDEX idx_destination_id ON destination_images(destination_id);
CREATE INDEX idx_is_primary ON destination_images(is_primary);
CREATE INDEX idx_sort_order ON destination_images(sort_order);

-- Exemple d'insertion de données (à adapter selon vos images)
-- INSERT INTO destination_images (destination_id, image_url, alt_text, is_primary, sort_order) VALUES
-- (1, 'uploads/destinations/destination1_1.jpg', 'Vue principale du Parc National des Virunga', 1, 1),
-- (1, 'uploads/destinations/destination1_2.jpg', 'Gorilles des montagnes', 0, 2),
-- (1, 'uploads/destinations/destination1_3.jpg', 'Paysage volcanique', 0, 3);
