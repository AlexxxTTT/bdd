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
            $notes = $_POST['notes'];
            
            // Gestion des 4 photos
            $photos = ['', '', '', ''];
            
            for ($i = 1; $i <= 4; $i++) {
                if (isset($_FILES["photo$i"]) && $_FILES["photo$i"]['error'] === 0) {
                    $upload_dir = 'uploads/gangs/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    $photos[$i-1] = $upload_dir . time() . "_photo{$i}_" . basename($_FILES["photo$i"]['name']);
                    move_uploaded_file($_FILES["photo$i"]['tmp_name'], $photos[$i-1]);
                }
            }
            
            if ($_POST['action'] === 'add') {
                $sql = "INSERT INTO gangs (nom, frequence_radio, notes, photo1, photo2, photo3, photo4) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $frequence_radio, $notes, $photos[0], $photos[1], $photos[2], $photos[3]]);
                $success = "Gang ajouté avec succès !";
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE gangs SET nom = ?, frequence_radio = ?, notes = ?";
                $params = [$nom, $frequence_radio, $notes];
                
                for ($i = 1; $i <= 4; $i++) {
                    if ($photos[$i-1]) {
                        $sql .= ", photo$i = ?";
                        $params[] = $photos[$i-1];
                    }
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = "Gang modifié avec succès !";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            
            // Récupérer les photos pour les supprimer
            $stmt = $pdo->prepare("SELECT photo1, photo2, photo3, photo4 FROM gangs WHERE id = ?");
            $stmt->execute([$id]);
            $photos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Supprimer les photos du serveur
            foreach ($photos as $photo) {
                if ($photo && file_exists($photo)) {
                    unlink($photo);
                }
            }
            
            // Supprimer de la base de données
            $stmt = $pdo->prepare("DELETE FROM gangs WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Gang supprimé avec succès !";
        }
    }
}

// Récupérer tous les gangs
$stmt = $pdo->query("SELECT * FROM gangs ORDER BY nom");
$gangs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer un gang spécifique pour modification
$gang_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM gangs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $gang_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Gangs - FiveM Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Gestion des Gangs</h1>
            <p>Gérez les gangs et leurs informations</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout/modification -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $gang_edit ? 'Modifier le gang' : 'Ajouter un gang'; ?></h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $gang_edit ? 'edit' : 'add'; ?>">
                    <?php if ($gang_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $gang_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom du gang *</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?php echo $gang_edit ? htmlspecialchars($gang_edit['nom']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="frequence_radio">Fréquence radio</label>
                            <input type="text" id="frequence_radio" name="frequence_radio" 
                                   value="<?php echo $gang_edit ? htmlspecialchars($gang_edit['frequence_radio']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="4"><?php echo $gang_edit ? htmlspecialchars($gang_edit['notes']) : ''; ?></textarea>
                    </div>
                    
                    <div class="photos-section">
                        <h3>Photos du gang</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="photo1">Photo 1</label>
                                <input type="file" id="photo1" name="photo1" accept="image/*">
                                <?php if ($gang_edit && $gang_edit['photo1']): ?>
                                    <p class="file-info">Photo actuelle: <?php echo basename($gang_edit['photo1']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="photo2">Photo 2</label>
                                <input type="file" id="photo2" name="photo2" accept="image/*">
                                <?php if ($gang_edit && $gang_edit['photo2']): ?>
                                    <p class="file-info">Photo actuelle: <?php echo basename($gang_edit['photo2']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="photo3">Photo 3</label>
                                <input type="file" id="photo3" name="photo3" accept="image/*">
                                <?php if ($gang_edit && $gang_edit['photo3']): ?>
                                    <p class="file-info">Photo actuelle: <?php echo basename($gang_edit['photo3']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-group">
                                <label for="photo4">Photo 4</label>
                                <input type="file" id="photo4" name="photo4" accept="image/*">
                                <?php if ($gang_edit && $gang_edit['photo4']): ?>
                                    <p class="file-info">Photo actuelle: <?php echo basename($gang_edit['photo4']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $gang_edit ? 'Modifier' : 'Ajouter'; ?>
                        </button>
                        <?php if ($gang_edit): ?>
                            <a href="gangs.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Liste des gangs -->
        <div class="card">
            <div class="card-header">
                <h2>Liste des gangs</h2>
            </div>
            <div class="card-body">
                <?php if (empty($gangs)): ?>
                    <p class="no-data">Aucun gang enregistré.</p>
                <?php else: ?>
                    <div class="gangs-grid">
                        <?php foreach ($gangs as $gang): ?>
                            <div class="gang-card">
                                <div class="gang-header">
                                    <h3><?php echo htmlspecialchars($gang['nom']); ?></h3>
                                    <div class="gang-actions">
                                        <a href="gangs.php?edit=<?php echo $gang['id']; ?>" 
                                           class="btn btn-sm btn-primary">Modifier</a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce gang ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?php echo $gang['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="gang-info">
                                    <p><strong>Fréquence radio:</strong> <?php echo htmlspecialchars($gang['frequence_radio'] ?: 'Non renseignée'); ?></p>
                                    <?php if ($gang['notes']): ?>
                                        <p><strong>Notes:</strong> <?php echo htmlspecialchars($gang['notes']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="gang-photos">
                                    <?php 
                                    $photos = [$gang['photo1'], $gang['photo2'], $gang['photo3'], $gang['photo4']];
                                    foreach ($photos as $index => $photo): 
                                        if ($photo): ?>
                                            <div class="photo-item">
                                                <img src="<?php echo htmlspecialchars($photo); ?>" 
                                                     alt="Photo <?php echo $index + 1; ?>" 
                                                     class="gang-photo"
                                                     onclick="openPhotoModal('<?php echo htmlspecialchars($photo); ?>')">
                                            </div>
                                        <?php endif; 
                                    endforeach; ?>
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
            <img id="modalPhoto" src="" alt="Photo du gang">
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