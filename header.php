<?php
// header.php - En-tête et menu de navigation
// Vérifier si la session n'est pas déjà démarrée
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion FiveM</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
    <header>
        <div class="logo">
            <h1>Gestion FiveM</h1>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="citoyens.php">Citoyens</a></li>
                <li><a href="gangs.php">Gangs</a></li>
                <li><a href="lspd.php">LSPD</a></li>
                <li><a href="bcso.php">BCSO</a></li>
                <li><a href="entreprises.php">Entreprises</a></li>
                <li><a href="inventaire.php">Inventaire</a></li>
                <li><a href="coffre.php">Coffre</a></li>
                <li><a href="notes.php">Notes</a></li>
                <?php if(isset($_SESSION["role"]) && $_SESSION["role"] === "admin"): ?>
                <li><a href="users.php">Utilisateurs</a></li>
                <?php endif; ?>
                <li class="logout"><a href="logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <div class="user-info">
        Connecté en tant que: <b><?php echo htmlspecialchars($_SESSION["username"]); ?></b>
    </div>