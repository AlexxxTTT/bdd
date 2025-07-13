<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $nom = $_POST['nom'];
            $frequence_radio = $_POST['frequence_radio'];
            
            // Gestion des photos
            $photo_entreprise = '';
            $photo_gps = '';
            
            // Upload photo entreprise
            if (isset($_FILES['photo_entreprise']) && $_FILES['photo_entreprise']['error'] === 0) {
                $upload_dir = 'uploads/entreprises/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $photo_entreprise = $upload_dir . time() . '_entreprise_' . basename($_FILES['photo_entreprise']['name']);
                move_uploaded_file($_FILES['photo_entreprise']['tmp_name'], $photo_entreprise);
            }
            
            // Upload photo GPS
            if (isset($_FILES['photo_gps']) && $_FILES['photo_gps']['error'] === 0) {
                $upload_dir = 'uploads/entreprises/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $photo_gps = $upload_dir . time() . '_gps_' . basename($_FILES['photo_gps']['name']);
                move_uploaded_file($_FILES['photo_gps']['tmp_name'], $photo_gps);
            }
            
            if ($_POST['action'] === 'add') {
                $sql = "INSERT INTO entreprises (nom, frequence_radio, photo_entreprise, photo_gps) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $frequence_radio, $photo_entreprise, $photo_gps]);
                $success = "Entreprise ajoutée avec succès !";
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE entreprises SET nom = ?, frequence_radio = ?";
                $params = [$nom, $frequence_radio];
                
                if ($photo_entreprise) {
                    $sql .= ", photo_entreprise = ?";
                    $params[] = $photo_entreprise;
                }
                if ($photo_gps) {
                    $sql .= ", photo_gps = ?";
                    $params[] = $photo_gps;
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = "Entreprise modifiée avec succès !";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            
            // Récupérer les photos pour les supprimer
            $stmt = $pdo->prepare("SELECT photo_entreprise, photo_gps FROM entreprises WHERE id = ?");
            $stmt->execute([$id]);
            $photos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Supprimer les photos du serveur
            if ($photos['photo_entreprise'] && file_exists($photos['photo_entreprise'])) {
                unlink($photos['photo_entreprise']);
            }
            if ($photos['photo_gps'] && file_exists($photos['photo_gps'])) {
                unlink($photos['photo_gps']);
            }
            
            // Supprimer de la base de données
            $stmt = $pdo->prepare("DELETE FROM entreprises WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Entreprise supprimée avec succès !";
        }
    }
}

// Récupérer toutes les entreprises
$stmt = $pdo->query("SELECT * FROM entreprises ORDER BY nom");
$entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer une entreprise spécifique pour modification
$entreprise_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM entreprises WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $entreprise_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Entreprises - FiveM Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Gestion des Entreprises</h1>
            <p>Gérez les entreprises et leurs informations</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout/modification -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $entreprise_edit ? 'Modifier l\'entreprise' : 'Ajouter une entreprise'; ?></h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $entreprise_edit ? 'edit' : 'add'; ?>">
                    <?php if ($entreprise_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $entreprise_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom de l'entreprise *</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?php echo $entreprise_edit ? htmlspecialchars($entreprise_edit['nom']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="frequence_radio">Fréquence radio</label>
                            <input type="text" id="frequence_radio" name="frequence_radio" 
                                   value="<?php echo $entreprise_edit ? htmlspecialchars($entreprise_edit['frequence_radio']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="photo_entreprise">Photo de l'entreprise</label>
                            <input type="file" id="photo_entreprise" name="photo_entreprise" accept="image/*">
                            <?php if ($entreprise_edit && $entreprise_edit['photo_entreprise']): ?>
                                <p class="file-info">Photo actuelle: <?php echo basename($entreprise_edit['photo_entreprise']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="photo_gps">Photo GPS</label>
                            <input type="file" id="photo_gps" name="photo_gps" accept="image/*">
                            <?php if ($entreprise_edit && $entreprise_edit['photo_gps']): ?>
                                <p class="file-info">Photo actuelle: <?php echo basename($entreprise_edit['photo_gps']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $entreprise_edit ? 'Modifier' : 'Ajouter'; ?>
                        </button>
                        <?php if ($entreprise_edit): ?>
                            <a href="entreprises.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Liste des entreprises -->
        <div class="card">
            <div class="card-header">
                <h2>Liste des entreprises</h2>
            </div>
            <div class="card-body">
                <?php if (empty($entreprises)): ?>
                    <p class="no-data">Aucune entreprise enregistrée.</p>
                <?php else: ?>
                    <div class="entreprises-grid">
                        <?php foreach ($entreprises as $entreprise): ?>
                            <div class="entreprise-card">
                                <div class="entreprise-header">
                                    <h3><?php echo htmlspecialchars($entreprise['nom']); ?></h3>
                                    <div class="entreprise-actions">
                                        <a href="entreprises.php?edit=<?php echo $entreprise['id']; ?>" 
                                           class="btn btn-sm btn-primary">Modifier</a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $entreprise['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="entreprise-info">
                                    <p><strong>Fréquence radio:</strong> <?php echo htmlspecialchars($entreprise['frequence_radio'] ?: 'Non renseignée'); ?></p>
                                </div>
                                
                                <div class="entreprise-photos">
                                    <?php if ($entreprise['photo_entreprise']): ?>
                                        <div class="photo-item">
                                            <label>Photo de l'entreprise:</label>
                                            <img src="<?php echo htmlspecialchars($entreprise['photo_entreprise']); ?>" 
                                                 alt="Photo entreprise" 
                                                 class="entreprise-photo"
                                                 onclick="openPhotoModal('<?php echo htmlspecialchars($entreprise['photo_entreprise']); ?>')">
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($entreprise['photo_gps']): ?>
                                        <div class="photo-item">
                                            <label>Photo GPS:</label>
                                            <img src="<?php echo htmlspecialchars($entreprise['photo_gps']); ?>" 
                                                 alt="Photo GPS" 
                                                 class="entreprise-photo"
                                                 onclick="openPhotoModal('<?php echo htmlspecialchars($entreprise['photo_gps']); ?>')">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Modal pour afficher les photos en grand -->
    <div id="photoModal" class="modal" style="display: none;">
        <div class="modal-content photo-modal">
            <span class="close" onclick="closePhotoModal()">&times;</span>
            <img id="modalPhoto" src="" alt="Photo de l'entreprise">
        </div>
    </div>
    
    <script>
        function openPhotoModal(photoSrc) {
            document.getElementById('modalPhoto').src = photoSrc;
            document.getElementById('photoModal').style.display = 'block';
        }
        
        function closePhotoModal() {
            document.getElementById('photoModal').style.display = 'none';
        }
        
        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('photoModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>