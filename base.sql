-- Création de la base de données
CREATE DATABASE agence_voyage;
USE agence_voyage;

-- Table des destinations
CREATE TABLE destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    pays VARCHAR(100) NOT NULL,
    description TEXT,
    prix DECIMAL(10,2) NOT NULL,
    duree_jours INT NOT NULL,
    image_url VARCHAR(500),
    categorie ENUM('aventure', 'plage', 'culture', 'romantique', 'famille'),
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des réservations
CREATE TABLE reservations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT,
    nom_client VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(50),
    date_depart DATE NOT NULL,
    nombre_personnes INT NOT NULL,
    prix_total DECIMAL(10,2) NOT NULL,
    statut ENUM('en_attente', 'confirmee', 'annulee') DEFAULT 'en_attente',
    message_special TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id)
);

-- Table des avis clients
CREATE TABLE avis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    destination_id INT,
    nom_client VARCHAR(255) NOT NULL,
    note INT CHECK (note >= 1 AND note <= 5),
    commentaire TEXT,
    approuve BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(id)
);

-- Insertion de données d'exemple
INSERT INTO destinations (nom, pays, description, prix, duree_jours, image_url, categorie, featured) VALUES
('Bali Paradise', 'Indonésie', 'Découvrez les plages de sable blanc et la culture riche de Bali avec ses temples majestueux et ses rizières en terrasses. Une expérience unique alliant détente et découverte culturelle.', 1200.00, 7, 'https://images.unsplash.com/photo-1537953773345-d172ccf13cf1?w=800&h=600&fit=crop', 'plage', TRUE),
('Safari Kenya', 'Kenya', 'Vivez l aventure ultime avec un safari dans les réserves naturelles du Kenya. Observez les Big Five dans leur habitat naturel et découvrez la culture masaï authentique.', 2500.00, 10, 'https://images.unsplash.com/photo-1516426122078-c23e76319801?w=800&h=600&fit=crop', 'aventure', TRUE),
('Rome Antique', 'Italie', 'Plongez dans l histoire de la Rome antique avec la visite du Colisée, du Forum Romain et du Vatican. Un voyage culturel au cœur de la civilisation romaine.', 800.00, 5, 'https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=800&h=600&fit=crop', 'culture', TRUE),
('Maldives Luxe', 'Maldives', 'Séjour romantique dans des bungalows sur pilotis avec eau turquoise et plages de sable fin. Parfait pour une lune de miel ou une escapade romantique.', 3000.00, 8, 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', 'romantique', FALSE),
('Tokyo Modern', 'Japon', 'Découverte de la modernité et des traditions japonaises. De Shibuya aux temples anciens, explorez le contraste unique de Tokyo.', 1500.00, 6, 'https://images.unsplash.com/photo-1540959733332-57c87c984f4b?w=800&h=600&fit=crop', 'culture', FALSE),
('New York City', 'USA', 'Expérience urbaine dans la ville qui ne dort jamais. Découvrez Times Square, Central Park, les musées et la vie nocturne vibrante de NYC.', 1800.00, 7, 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=800&h=600&fit=crop', 'aventure', FALSE),
('Santorini Grece', 'Grèce', 'Villages blancs perchés sur les falaises avec vue sur la mer Égée. Couchers de soleil inoubliables et cuisine méditerranéenne authentique.', 1600.00, 7, 'https://images.unsplash.com/photo-1570077188670-e3a8d69ac5ff?w=800&h=600&fit=crop', 'romantique', TRUE),
('Thaïlande Aventure', 'Thaïlande', 'Combinaison parfaite de plages tropicales, temples bouddhistes et jungle luxuriante. Découverte de Bangkok, Chiang Mai et les îles du sud.', 1400.00, 12, 'https://images.unsplash.com/photo-1528181304800-259b08848526?w=800&h=600&fit=crop', 'aventure', FALSE);

INSERT INTO avis (destination_id, nom_client, note, commentaire, approuve) VALUES
(1, 'Marie Lambert', 5, 'Un voyage exceptionnel ! Les paysages de Bali étaient à couper le souffle. L organisation était parfaite.', TRUE),
(2, 'Pierre Dubois', 4, 'Safari incroyable, guides très professionnels. Nous avons vu tous les animaux que nous espérions.', TRUE),
(3, 'Sophie Martin', 5, 'Rome est magnifique, riche en histoire et culture. Le guide était très compétent.', TRUE),
(4, 'Thomas Bernard', 5, 'Lune de miel parfaite aux Maldives. Le resort était luxueux et le service impeccable.', TRUE),
(7, 'Camille Petit', 4, 'Santorini est un rêve. Les couchers de soleil sont encore plus beaux en réalité.', TRUE);

-- Creation d'un utilisateur pour la base de données
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'agent', 'client') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);