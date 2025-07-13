<?php
// footer.php
?>
    <footer>
        <p>&copy; 2024 Système de gestion FiveM. Tous droits réservés.</p>
    </footer>
</body>
</html>

<?php
// logout.php - Déconnexion
session_start();
 
// Détruire la session
$_SESSION = array();
 
// Détruire le cookie de session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
 
// Détruire la session
session_destroy();
 
// Rediriger vers la page de connexion
header("location: login.php");
exit;
?>