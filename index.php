<?php
// index.php - Page d'accueil
// Inclure le fichier de configuration
require_once "config.php";

// Inclure l'en-tête
include 'header.php';

// Récupérer quelques statistiques pour l'affichage
$stats = array();

// Nombre de citoyens
$sql = "SELECT COUNT(*) as total FROM citoyens";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['citoyens'] = $row['total'];
} else {
    $stats['citoyens'] = 0;
}

// Nombre de gangs
$sql = "SELECT COUNT(*) as total FROM gangs";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['gangs'] = $row['total'];
} else {
    $stats['gangs'] = 0;
}

// Nombre d'agents LSPD
$sql = "SELECT COUNT(*) as total FROM agents WHERE type = 'LSPD'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['lspd'] = $row['total'];
} else {
    $stats['lspd'] = 0;
}

// Nombre d'agents BCSO
$sql = "SELECT COUNT(*) as total FROM agents WHERE type = 'BCSO'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['bcso'] = $row['total'];
} else {
    $stats['bcso'] = 0;
}

// Nombre d'entreprises
$sql = "SELECT COUNT(*) as total FROM entreprises";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['entreprises'] = $row['total'];
} else {
    $stats['entreprises'] = 0;
}

// Argent total dans le coffre
$sql = "SELECT * FROM vue_coffre";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $stats['argent_propre'] = $row['total_propre'] ?? 0;
    $stats['argent_sale'] = $row['total_sale'] ?? 0;
} else {
    $stats['argent_propre'] = 0;
    $stats['argent_sale'] = 0;
}

// Fermer la connexion
mysqli_close($conn);
?>

<div class="container">
    <div class="welcome-section">
        <h2>Bienvenue sur votre système de gestion FiveM</h2>
        <p>Ce système vous permet de gérer vos informations FiveM avec une interface locale personnalisée.</p>
    </div>

    <div class="stats-section">
        <h3>Statistiques</h3>
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Citoyens</h4>
                <p class="stat-number"><?php echo $stats['citoyens']; ?></p>
                <a href="citoyens.php" class="btn">Gérer les citoyens</a>
            </div>
            <div class="stat-card">
                <h4>Gangs</h4>
                <p class="stat-number"><?php echo $stats['gangs']; ?></p>
                <a href="gangs.php" class="btn">Gérer les gangs</a>
            </div>
            <div class="stat-card">
                <h4>Agents LSPD</h4>
                <p class="stat-number"><?php echo $stats['lspd']; ?></p>
                <a href="lspd.php" class="btn">Gérer le LSPD</a>
            </div>
            <div class="stat-card">
                <h4>Agents BCSO</h4>
                <p class="stat-number"><?php echo $stats['bcso']; ?></p>
                <a href="bcso.php" class="btn">Gérer le BCSO</a>
            </div>
            <div class="stat-card">
                <h4>Entreprises</h4>
                <p class="stat-number"><?php echo $stats['entreprises']; ?></p>
                <a href="entreprises.php" class="btn">Gérer les entreprises</a>
            </div>
            <div class="stat-card">
                <h4>Coffre</h4>
                <p class="stat-number">Propre: $<?php echo number_format($stats['argent_propre'], 2); ?></p>
                <p class="stat-number">Sale: $<?php echo number_format($stats['argent_sale'], 2); ?></p>
                <a href="coffre.php" class="btn">Gérer le coffre</a>
            </div>
        </div>
    </div>

    <div class="quick-links-section">
        <h3>Accès rapides</h3>
        <div class="quick-links">
            <a href="citoyens.php?action=add" class="btn">Ajouter un citoyen</a>
            <a href="gangs.php?action=add" class="btn">Ajouter un gang</a>
            <a href="lspd.php?action=add" class="btn">Ajouter un agent LSPD</a>
            <a href="bcso.php?action=add" class="btn">Ajouter un agent BCSO</a>
            <a href="entreprises.php?action=add" class="btn">Ajouter une entreprise</a>
            <a href="inventaire.php?action=add" class="btn">Ajouter à l'inventaire</a>
            <a href="notes.php?action=add" class="btn">Ajouter une note</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>