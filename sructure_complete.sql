-- Table des utilisateurs (déjà créée)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des gangs
CREATE TABLE IF NOT EXISTS gangs (
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
CREATE TABLE IF NOT EXISTS entreprises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    frequence_radio VARCHAR(50),
    photo_entreprise VARCHAR(255),
    photo_gps VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des citoyens (mise à jour)
CREATE TABLE IF NOT EXISTS citoyens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    gang_id INT,
    grade VARCHAR(50),
    entreprise_id INT,
    grade_entreprise VARCHAR(50),
    notes TEXT,
    photo_portrait VARCHAR(255),
    photo_identite VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gang_id) REFERENCES gangs(id) ON DELETE SET NULL,
    FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE SET NULL
);

-- Table des agents LSPD
CREATE TABLE IF NOT EXISTS lspd_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    rang VARCHAR(50),
    matricule VARCHAR(20) UNIQUE,
    unite VARCHAR(50),
    grade_unite VARCHAR(50),
    notes TEXT,
    photo_portrait VARCHAR(255),
    photo_identite VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des agents BCSO
CREATE TABLE IF NOT EXISTS bcso_agents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20),
    rang VARCHAR(50),
    matricule VARCHAR(20) UNIQUE,
    unite VARCHAR(50),
    grade_unite VARCHAR(50),
    notes TEXT,
    photo_portrait VARCHAR(255),
    photo_identite VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des notes
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT NOT NULL,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertion de données d'exemple pour les gangs
INSERT INTO gangs (nom, frequence_radio, notes) VALUES
('Los Santos Vagos', '101.5', 'Gang présent dans East Los Santos'),
('Ballas', '102.3', 'Gang rival des Grove Street'),
('Grove Street Families', '103.1', 'Gang historique de Grove Street'),
('Marabunta Grande', '104.7', 'Gang d\'origine mexicaine');

-- Insertion de données d'exemple pour les entreprises
INSERT INTO entreprises (nom, frequence_radio) VALUES
('Burger Shot', '201.1'),
('Cluckin\' Bell', '201.2'),
('Ammunation', '201.3'),
('Los Santos Customs', '201.4'),
('Weazel News', '201.5'),
('Maze Bank', '201.6'),
('Premium Deluxe Motorsport', '201.7'),
('Bahama Mamas', '201.8');