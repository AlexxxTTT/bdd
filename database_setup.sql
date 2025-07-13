-- Créer la base de données
CREATE DATABASE IF NOT EXISTS `fivem_database` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fivem_database`;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des gangs
CREATE TABLE IF NOT EXISTS `gangs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `frequence_radio` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo1` varchar(255) DEFAULT NULL,
  `photo2` varchar(255) DEFAULT NULL,
  `photo3` varchar(255) DEFAULT NULL,
  `photo4` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des entreprises
CREATE TABLE IF NOT EXISTS `entreprises` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `frequence_radio` varchar(50) DEFAULT NULL,
  `photo_entreprise` varchar(255) DEFAULT NULL,
  `photo_gps` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des citoyens
CREATE TABLE IF NOT EXISTS `citoyens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `gang_id` int(11) DEFAULT NULL,
  `grade_gang` varchar(50) DEFAULT NULL,
  `entreprise_id` int(11) DEFAULT NULL,
  `grade_entreprise` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_portrait` varchar(255) DEFAULT NULL,
  `photo_identite` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `gang_id` (`gang_id`),
  KEY `entreprise_id` (`entreprise_id`),
  CONSTRAINT `citoyens_ibfk_1` FOREIGN KEY (`gang_id`) REFERENCES `gangs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `citoyens_ibfk_2` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des agents LSPD
CREATE TABLE IF NOT EXISTS `agents_lspd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `rang` varchar(50) DEFAULT NULL,
  `matricule` varchar(20) DEFAULT NULL,
  `unite` varchar(50) DEFAULT NULL,
  `grade_unite` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_portrait` varchar(255) DEFAULT NULL,
  `photo_identite` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des agents BCSO
CREATE TABLE IF NOT EXISTS `agents_bcso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `rang` varchar(50) DEFAULT NULL,
  `matricule` varchar(20) DEFAULT NULL,
  `unite` varchar(50) DEFAULT NULL,
  `grade_unite` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `photo_portrait` varchar(255) DEFAULT NULL,
  `photo_identite` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricule` (`matricule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des notes
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `priorite` enum('basse','moyenne','haute') NOT NULL DEFAULT 'basse',
  `user_id` int(11) NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `priorite` (`priorite`),
  KEY `date_creation` (`date_creation`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Index pour optimiser les performances
CREATE INDEX idx_notes_user_priorite ON notes(user_id, priorite);
CREATE INDEX idx_notes_date_creation ON notes(date_creation DESC);

-- Insérer quelques données de test pour les gangs
INSERT INTO `gangs` (`nom`, `frequence_radio`, `notes`) VALUES
('Ballas', '150.0', 'Gang de Los Santos Est'),
('Grove Street', '151.0', 'Gang historique de Grove Street'),
('Vagos', '152.0', 'Gang latino de Los Santos');

-- Insérer quelques données de test pour les entreprises
INSERT INTO `entreprises` (`nom`, `frequence_radio`) VALUES
('Burger Shot', '100.0'),
('Cluckin Bell', '101.0'),
('Vanilla Unicorn', '102.0'),
('Diamond Casino', '103.0');

-- Créer un utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO `users` (`username`, `password`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Afficher les informations de connexion
SELECT 'Base de données créée avec succès!' as message;
SELECT 'Utilisateur admin créé - Username: admin, Password: admin123' as info;