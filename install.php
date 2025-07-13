<?php
// Script d'installation pour la base de données FiveM
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration de la base de données
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'fivem_database';

echo "<h2>Installation de la base de données FiveM</h2>";

try {
    // Connexion MySQL sans spécifier de base de données
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connexion au serveur MySQL réussie</p>";
    
    // Créer la base de données
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p>✅ Base de données '$dbname' créée</p>";
    
    // Utiliser la base de données
    $pdo->exec("USE `$dbname`");
    
    // Créer les tables
    $tables = [
        'users' => "CREATE TABLE IF NOT EXISTS `users` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(50) NOT NULL UNIQUE,
            `password` varchar(255) NOT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'gangs' => "CREATE TABLE IF NOT EXISTS `gangs` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'entreprises' => "CREATE TABLE IF NOT EXISTS `entreprises` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nom` varchar(100) NOT NULL,
            `frequence_radio` varchar(50) DEFAULT NULL,
            `photo_entreprise` varchar(255) DEFAULT NULL,
            `photo_gps` varchar(255) DEFAULT NULL,
            `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'citoyens' => "CREATE TABLE IF NOT EXISTS `citoyens` (
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
            KEY `entreprise_id` (`entreprise_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'agents_lspd' => "CREATE TABLE IF NOT EXISTS `agents_lspd` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'agents_bcso' => "CREATE TABLE IF NOT EXISTS `agents_bcso` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'notes' => "CREATE TABLE IF NOT EXISTS `notes` (
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
            KEY `date_creation` (`date_creation`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($tables as $table => $sql) {
        $pdo->exec($sql);
        echo "<p>✅ Table '$table' créée</p>";
    }
    
    // Ajouter les contraintes de clés étrangères
    $constraints = [
        "ALTER TABLE `citoyens` ADD CONSTRAINT `citoyens_ibfk_1` FOREIGN KEY (`gang_id`) REFERENCES `gangs` (`id`) ON DELETE SET NULL",
        "ALTER TABLE `citoyens` ADD CONSTRAINT `citoyens_ibfk_2` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE SET NULL",
        "ALTER TABLE `notes` ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE"
    ];
    
    foreach ($constraints as $constraint) {
        try {
            $pdo->exec($constraint);
        } catch (PDOException $e) {
            // Ignorer si la contrainte existe déjà
        }
    }
    
    echo "<p>✅ Contraintes de clés étrangères ajoutées</p>";
    
    // Insérer des données de test
    $test_data = [
        "INSERT IGNORE INTO `gangs` (`nom`, `frequence_radio`, `notes`) VALUES
        ('Ballas', '150.0', 'Gang de Los Santos Est'),
        ('Grove Street', '151.0', 'Gang historique de Grove Street'),
        ('Vagos', '152.0', 'Gang latino de Los Santos')",
        
        "INSERT IGNORE INTO `entreprises` (`nom`, `frequence_radio`) VALUES
        ('Burger Shot', '100.0'),
        ('Cluckin Bell', '101.0'),
        ('Vanilla Unicorn', '102.0'),
        ('Diamond Casino', '103.0')",
        
        "INSERT IGNORE INTO `users` (`username`, `password`) VALUES
        ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "')"
    ];
    
    foreach ($test_data as $sql) {
        $pdo->exec($sql);
    }
    
    echo "<p>✅ Données de test insérées</p>";
    
    echo "<div style='background: #d4edda; padding: 15px; margin: 20px 0; border-radius: 5px; color: #155724;'>
        <h3>🎉 Installation terminée avec succès !</h3>
        <p><strong>Compte administrateur créé :</strong></p>
        <p>Nom d'utilisateur : <strong>admin</strong></p>
        <p>Mot de passe : <strong>admin123</strong></p>
        <p><a href='login.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Se connecter maintenant</a></p>
    </div>";
    
    echo "<p><em>⚠️ Vous pouvez maintenant supprimer ce fichier install.php pour des raisons de sécurité.</em></p>";
    
} catch (PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 20px 0; border-radius: 5px; color: #721c24;'>";
    echo "<h3>❌ Erreur lors de l'installation</h3>";
    echo "<p>Erreur : " . $e->getMessage() . "</p>";
    echo "<p>Vérifiez que :</p>";
    echo "<ul>";
    echo "<li>MySQL/MariaDB est en cours d'exécution</li>";
    echo "<li>Les paramètres de connexion sont corrects dans config.php</li>";
    echo "<li>L'utilisateur a les permissions nécessaires</li>";
    echo "</ul>";
    echo "</div>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f5f5f5;
}

h2 {
    color: #333;
    text-align: center;
}

p {
    padding: 5px 0;
}
</style>