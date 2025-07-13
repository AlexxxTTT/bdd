-- Base de données pour le projet FiveM

-- Table des utilisateurs pour la connexion
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des gangs
CREATE TABLE gangs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    frequence_radio VARCHAR(50),
    notes TEXT,
    photo1 VARCHAR(255),
    photo2 VARCHAR(255),
    photo3 VARCHAR(255),
    photo4 VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des entreprises
CREATE TABLE entreprises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    frequence_radio VARCHAR(50),
    photo VARCHAR(255),
    photo_gps VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des grades (pour les entreprises)
CREATE TABLE grades_entreprise (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entreprise_id INT,
    nom VARCHAR(50) NOT NULL,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
);

-- Table des citoyens
CREATE TABLE citoyens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(20),
    gang_id INT NULL,
    grade_gang VARCHAR(50),
    entreprise_id INT NULL,
    grade_entreprise_id INT NULL,
    notes TEXT,
    photo_portrait VARCHAR(255),
    photo_identite VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gang_id) REFERENCES gangs(id) ON DELETE SET NULL,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE SET NULL,
    FOREIGN KEY (grade_entreprise_id) REFERENCES grades_entreprise(id) ON DELETE SET NULL
);

-- Table des unités (pour LSPD/BCSO)
CREATE TABLE unites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    type ENUM('LSPD', 'BCSO') NOT NULL
);

-- Table des rangs (pour LSPD/BCSO)
CREATE TABLE rangs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    type ENUM('LSPD', 'BCSO') NOT NULL
);

-- Table des agents (pour LSPD et BCSO)
CREATE TABLE agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    telephone VARCHAR(20),
    rang_id INT,
    matricule VARCHAR(20),
    unite_id INT,
    grade_unite VARCHAR(50),
    notes TEXT,
    photo_portrait VARCHAR(255),
    photo_identite VARCHAR(255),
    type ENUM('LSPD', 'BCSO') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rang_id) REFERENCES rangs(id) ON DELETE SET NULL,
    FOREIGN KEY (unite_id) REFERENCES unites(id) ON DELETE SET NULL
);

-- Table des types d'items
CREATE TABLE types_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL
);

-- Insertion des types d'items de base
INSERT INTO types_item (nom) VALUES ('Arme'), ('Item');

-- Table d'inventaire
CREATE TABLE inventaire (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    quantite INT NOT NULL DEFAULT 0,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES types_item(id)
);

-- Table des transactions financières
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('propre', 'sale') NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des notes
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(100) NOT NULL,
    contenu TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Vue pour le total d'argent
CREATE VIEW vue_coffre AS
SELECT 
    SUM(CASE WHEN type = 'propre' THEN montant ELSE 0 END) AS total_propre,
    SUM(CASE WHEN type = 'sale' THEN montant ELSE 0 END) AS total_sale
FROM transactions;