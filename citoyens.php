<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Récupérer les gangs pour le dropdown
$gangs_query = "SELECT id, nom FROM gangs ORDER BY nom";
$gangs_result = $pdo->query($gangs_query);
$gangs = $gangs_result->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les entreprises pour le dropdown
$entreprises_query = "SELECT id, nom FROM entreprises ORDER BY nom";
$entreprises_result = $pdo->query($entreprises_query);
$entreprises = $entreprises_result->fetchAll(PDO::FETCH_ASSOC);

// Grades prédéfinis
$grades_citoyen = [
    'Citoyen',
    'Résident',
    'Habitant',
    'Visiteur',
    'Suspect',
    'Criminel',
    'Informateur'
];

// Traitement du formulaire d'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add' || $_POST['action'] === 'edit') {
            $nom = $_POST['nom'];
            $prenom = $_POST['prenom'];
            $telephone = $_POST['telephone'];
            $gang_id = $_POST['gang_id'] ? $_POST['gang_id'] : null;
            $grade = $_POST['grade'];
            $entreprise_id = $_POST['entreprise_id'] ? $_POST['entreprise_id'] : null;
            $grade_entreprise = $_POST['grade_entreprise'];
            $notes = $_POST['notes'];
            
            // Gestion des photos
            $photo_portrait = '';
            $photo_identite = '';
            
            // Upload photo portrait
            if (isset($_FILES['photo_portrait']) && $_FILES['photo_portrait']['error'] === 0) {
                $upload_dir = 'uploads/citoyens/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $photo_portrait = $upload_dir . time() . '_portrait_' . basename($_FILES['photo_portrait']['name']);
                move_uploaded_file($_FILES['photo_portrait']['tmp_name'], $photo_portrait);
            }
            
            // Upload photo identité
            if (isset($_FILES['photo_identite']) && $_FILES['photo_identite']['error'] === 0) {
                $upload_dir = 'uploads/citoyens/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                $photo_identite = $upload_dir . time() . '_identite_' . basename($_FILES['photo_identite']['name']);
                move_uploaded_file($_FILES['photo_identite']['tmp_name'], $photo_identite);
            }
            
            if ($_POST['action'] === 'add') {
                $sql = "INSERT INTO citoyens (nom, prenom, telephone, gang_id, grade, entreprise_id, grade_entreprise, notes, photo_portrait, photo_identite) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nom, $prenom, $telephone, $gang_id, $grade, $entreprise_id, $grade_entreprise, $notes, $photo_portrait, $photo_identite]);
                $success = "Citoyen ajouté avec succès !";
            } else {
                $id = $_POST['id'];
                $sql = "UPDATE citoyens SET nom = ?, prenom = ?, telephone = ?, gang_id = ?, grade = ?, entreprise_id = ?, grade_entreprise = ?, notes = ?";
                $params = [$nom, $prenom, $telephone, $gang_id, $grade, $entreprise_id, $grade_entreprise, $notes];
                
                if ($photo_portrait) {
                    $sql .= ", photo_portrait = ?";
                    $params[] = $photo_portrait;
                }
                if ($photo_identite) {
                    $sql .= ", photo_identite = ?";
                    $params[] = $photo_identite;
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $success = "Citoyen modifié avec succès !";
            }
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id'];
            
            // Récupérer les photos pour les supprimer
            $stmt = $pdo->prepare("SELECT photo_portrait, photo_identite FROM citoyens WHERE id = ?");
            $stmt->execute([$id]);
            $photos = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Supprimer les photos du serveur
            if ($photos['photo_portrait'] && file_exists($photos['photo_portrait'])) {
                unlink($photos['photo_portrait']);
            }
            if ($photos['photo_identite'] && file_exists($photos['photo_identite'])) {
                unlink($photos['photo_identite']);
            }
            
            // Supprimer de la base de données
            $stmt = $pdo->prepare("DELETE FROM citoyens WHERE id = ?");
            $stmt->execute([$id]);
            $success = "Citoyen supprimé avec succès !";
        }
    }
}

// Récupérer tous les citoyens avec leurs informations liées
$sql = "SELECT c.*, g.nom as gang_nom, e.nom as entreprise_nom 
        FROM citoyens c 
        LEFT JOIN gangs g ON c.gang_id = g.id 
        LEFT JOIN entreprises e ON c.entreprise_id = e.id 
        ORDER BY c.nom, c.prenom";
$stmt = $pdo->query($sql);
$citoyens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer un citoyen spécifique pour modification
$citoyen_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM citoyens WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $citoyen_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Citoyens - FiveM Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Gestion des Citoyens</h1>
            <p>Gérez la base de données des citoyens de votre serveur FiveM</p>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <!-- Formulaire d'ajout/modification -->
        <div class="card">
            <div class="card-header">
                <h2><?php echo $citoyen_edit ? 'Modifier le citoyen' : 'Ajouter un citoyen'; ?></h2>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $citoyen_edit ? 'edit' : 'add'; ?>">
                    <?php if ($citoyen_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $citoyen_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom *</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?php echo $citoyen_edit ? htmlspecialchars($citoyen_edit['nom']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom">Prénom *</label>
                            <input type="text" id="prenom" name="prenom" required 
                                   value="<?php echo $citoyen_edit ? htmlspecialchars($citoyen_edit['prenom']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Numéro de téléphone</label>
                            <input type="text" id="telephone" name="telephone" 
                                   value="<?php echo $citoyen_edit ? htmlspecialchars($citoyen_edit['telephone']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="gang_id">Gang</label>
                            <select id="gang_id" name="gang_id">
                                <option value="">Aucun gang</option>
                                <?php foreach ($gangs as $gang): ?>
                                    <option value="<?php echo $gang['id']; ?>" 
                                            <?php echo ($citoyen_edit && $citoyen_edit['gang_id'] == $gang['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($gang['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="grade">Grade</label>
                            <select id="grade" name="grade">
                                <?php foreach ($grades_citoyen as $grade): ?>
                                    <option value="<?php echo $grade; ?>" 
                                            <?php echo ($citoyen_edit && $citoyen_edit['grade'] == $grade) ? 'selected' : ''; ?>>
                                        <?php echo $grade; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="entreprise_id">Entreprise</label>
                            <select id="entreprise_id" name="entreprise_id">
                                <option value="">Aucune entreprise</option>
                                <?php foreach ($entreprises as $entreprise): ?>
                                    <option value="<?php echo $entreprise['id']; ?>" 
                                            <?php echo ($citoyen_edit && $citoyen_edit['entreprise_id'] == $entreprise['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($entreprise['nom']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="grade_entreprise">Grade dans l'entreprise</label>
                        <input type="text" id="grade_entreprise" name="grade_entreprise" 
                               value="<?php echo $citoyen_edit ? htmlspecialchars($citoyen_edit['grade_entreprise']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="4"><?php echo $citoyen_edit ? htmlspecialchars($citoyen_edit['notes']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="photo_portrait">Photo de portrait</label>
                            <input type="file" id="photo_portrait" name="photo_portrait" accept="image/*">
                            <?php if ($citoyen_edit && $citoyen_edit['photo_portrait']): ?>
                                <p class="file-info">Photo actuelle: <?php echo basename($citoyen_edit['photo_portrait']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="photo_identite">Photo d'identité</label>
                            <input type="file" id="photo_identite" name="photo_identite" accept="image/*">
                            <?php if ($citoyen_edit && $citoyen_edit['photo_identite']): ?>
                                <p class="file-info">Photo actuelle: <?php echo basename($citoyen_edit['photo_identite']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $citoyen_edit ? 'Modifier' : 'Ajouter'; ?>
                        </button>
                        <?php if ($citoyen_edit): ?>
                            <a href="citoyens.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Liste des citoyens -->
        <div class="card">
            <div class="card-header">
                <h2>Liste des citoyens</h2>
            </div>
            <div class="card-body">
                <?php if (empty($citoyens)): ?>
                    <p class="no-data">Aucun citoyen enregistré.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Téléphone</th>
                                    <th>Gang</th>
                                    <th>Grade</th>
                                    <th>Entreprise</th>
                                    <th>Grade Entreprise</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($citoyens as $citoyen): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($citoyen['nom']); ?></td>
                                        <td><?php echo htmlspecialchars($citoyen['prenom']); ?></td>
                                        <td><?php echo htmlspecialchars($citoyen['telephone']); ?></td>
                                        <td><?php echo htmlspecialchars($citoyen['gang_nom'] ?: 'Aucun'); ?></td>
                                        <td><?php echo htmlspecialchars($citoyen['grade']); ?></td>
                                        <td><?php echo htmlspecialchars($citoyen['entreprise_nom'] ?: 'Aucune'); ?></td>
                                        <td><?php echo htmlspecialchars($citoyen['grade_entreprise']); ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="citoyens.php?edit=<?php echo $citoyen['id']; ?>" 
                                                   class="btn btn-sm btn-primary">Modifier</a>
                                                <button onclick="openModal(<?php echo $citoyen['id']; ?>)" 
                                                        class="btn btn-sm btn-info">Détails</button>
                                                <form method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce citoyen ?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $citoyen['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Modal pour les détails -->
    <div id="detailModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Détails du citoyen</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>
    
    <script>
        function openModal(citizenId) {
            // Trouver les données du citoyen
            const citoyens = <?php echo json_encode($citoyens); ?>;
            const citoyen = citoyens.find(c => c.id == citizenId);
            
            if (citoyen) {
                let modalContent = `
                    <div class="citizen-details">
                        <h3>${citoyen.nom} ${citoyen.prenom}</h3>
                        <div class="details-grid">
                            <div class="detail-item">
                                <strong>Téléphone:</strong> ${citoyen.telephone || 'Non renseigné'}
                            </div>
                            <div class="detail-item">
                                <strong>Gang:</strong> ${citoyen.gang_nom || 'Aucun'}
                            </div>
                            <div class="detail-item">
                                <strong>Grade:</strong> ${citoyen.grade || 'Non renseigné'}
                            </div>
                            <div class="detail-item">
                                <strong>Entreprise:</strong> ${citoyen.entreprise_nom || 'Aucune'}
                            </div>
                            <div class="detail-item">
                                <strong>Grade Entreprise:</strong> ${citoyen.grade_entreprise || 'Non renseigné'}
                            </div>
                        </div>
                        
                        ${citoyen.notes ? `<div class="notes-section">
                            <strong>Notes:</strong>
                            <p>${citoyen.notes}</p>
                        </div>` : ''}
                        
                        <div class="photos-section">
                            ${citoyen.photo_portrait ? `
                                <div class="photo-item">
                                    <strong>Photo de portrait:</strong>
                                    <img src="${citoyen.photo_portrait}" alt="Portrait" class="citizen-photo">
                                </div>
                            ` : ''}
                            ${citoyen.photo_identite ? `
                                <div class="photo-item">
                                    <strong>Photo d'identité:</strong>
                                    <img src="${citoyen.photo_identite}" alt="Identité" class="citizen-photo">
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
                
                document.getElementById('modalBody').innerHTML = modalContent;
                document.getElementById('detailModal').style.display = 'block';
            }
        }
        
        function closeModal() {
            document.getElementById('detailModal').style.display = 'none';
        }
        
        // Fermer le modal en cliquant en dehors
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>