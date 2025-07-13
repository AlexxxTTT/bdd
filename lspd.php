<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_agent':
                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $telephone = $_POST['telephone'];
                $rang = $_POST['rang'];
                $matricule = $_POST['matricule'];
                $unite = $_POST['unite'];
                $grade_unite = $_POST['grade_unite'];
                $notes = $_POST['notes'];
                
                // Gestion des photos
                $photo_portrait = '';
                $photo_identite = '';
                
                if (isset($_FILES['photo_portrait']) && $_FILES['photo_portrait']['error'] == 0) {
                    $photo_portrait = 'uploads/lspd_portraits/' . time() . '_' . $_FILES['photo_portrait']['name'];
                    move_uploaded_file($_FILES['photo_portrait']['tmp_name'], $photo_portrait);
                }
                
                if (isset($_FILES['photo_identite']) && $_FILES['photo_identite']['error'] == 0) {
                    $photo_identite = 'uploads/lspd_identites/' . time() . '_' . $_FILES['photo_identite']['name'];
                    move_uploaded_file($_FILES['photo_identite']['tmp_name'], $photo_identite);
                }
                
                $stmt = $pdo->prepare("INSERT INTO agents_lspd (nom, prenom, telephone, rang, matricule, unite, grade_unite, notes, photo_portrait, photo_identite) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $telephone, $rang, $matricule, $unite, $grade_unite, $notes, $photo_portrait, $photo_identite]);
                
                $message = "Agent LSPD ajouté avec succès !";
                break;
                
            case 'edit_agent':
                $id = $_POST['id'];
                $nom = $_POST['nom'];
                $prenom = $_POST['prenom'];
                $telephone = $_POST['telephone'];
                $rang = $_POST['rang'];
                $matricule = $_POST['matricule'];
                $unite = $_POST['unite'];
                $grade_unite = $_POST['grade_unite'];
                $notes = $_POST['notes'];
                
                // Récupérer les anciennes photos
                $stmt = $pdo->prepare("SELECT photo_portrait, photo_identite FROM agents_lspd WHERE id = ?");
                $stmt->execute([$id]);
                $old_photos = $stmt->fetch();
                
                $photo_portrait = $old_photos['photo_portrait'];
                $photo_identite = $old_photos['photo_identite'];
                
                if (isset($_FILES['photo_portrait']) && $_FILES['photo_portrait']['error'] == 0) {
                    if ($photo_portrait && file_exists($photo_portrait)) {
                        unlink($photo_portrait);
                    }
                    $photo_portrait = 'uploads/lspd_portraits/' . time() . '_' . $_FILES['photo_portrait']['name'];
                    move_uploaded_file($_FILES['photo_portrait']['tmp_name'], $photo_portrait);
                }
                
                if (isset($_FILES['photo_identite']) && $_FILES['photo_identite']['error'] == 0) {
                    if ($photo_identite && file_exists($photo_identite)) {
                        unlink($photo_identite);
                    }
                    $photo_identite = 'uploads/lspd_identites/' . time() . '_' . $_FILES['photo_identite']['name'];
                    move_uploaded_file($_FILES['photo_identite']['tmp_name'], $photo_identite);
                }
                
                $stmt = $pdo->prepare("UPDATE agents_lspd SET nom = ?, prenom = ?, telephone = ?, rang = ?, matricule = ?, unite = ?, grade_unite = ?, notes = ?, photo_portrait = ?, photo_identite = ? WHERE id = ?");
                $stmt->execute([$nom, $prenom, $telephone, $rang, $matricule, $unite, $grade_unite, $notes, $photo_portrait, $photo_identite, $id]);
                
                $message = "Agent LSPD modifié avec succès !";
                break;
                
            case 'delete_agent':
                $id = $_POST['id'];
                
                // Supprimer les photos
                $stmt = $pdo->prepare("SELECT photo_portrait, photo_identite FROM agents_lspd WHERE id = ?");
                $stmt->execute([$id]);
                $photos = $stmt->fetch();
                
                if ($photos['photo_portrait'] && file_exists($photos['photo_portrait'])) {
                    unlink($photos['photo_portrait']);
                }
                if ($photos['photo_identite'] && file_exists($photos['photo_identite'])) {
                    unlink($photos['photo_identite']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM agents_lspd WHERE id = ?");
                $stmt->execute([$id]);
                
                $message = "Agent LSPD supprimé avec succès !";
                break;
        }
    }
}

// Récupérer tous les agents LSPD
$stmt = $pdo->query("SELECT * FROM agents_lspd ORDER BY nom, prenom");
$agents = $stmt->fetchAll();

// Récupérer un agent spécifique pour modification
$agent_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM agents_lspd WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $agent_edit = $stmt->fetch();
}

// Créer les dossiers s'ils n'existent pas
if (!file_exists('uploads/lspd_portraits')) {
    mkdir('uploads/lspd_portraits', 0777, true);
}
if (!file_exists('uploads/lspd_identites')) {
    mkdir('uploads/lspd_identites', 0777, true);
}

// Listes prédéfinies
$rangs = [
    'Cadet', 'Officer I', 'Officer II', 'Officer III', 'Detective I', 'Detective II', 
    'Detective III', 'Sergeant I', 'Sergeant II', 'Lieutenant I', 'Lieutenant II', 
    'Captain I', 'Captain II', 'Commander', 'Deputy Chief', 'Assistant Chief', 'Chief'
];

$unites = [
    'Patrol', 'Traffic', 'K9', 'SWAT', 'Detective', 'Narcotics', 'Gang Unit', 
    'Air Support', 'Harbor', 'Motorcycle', 'Bicycle', 'Community Relations',
    'Internal Affairs', 'Training', 'Administration'
];

$grades_unite = [
    'Membre', 'Officier Senior', 'Superviseur', 'Chef d\'équipe', 'Commandant'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des agents LSPD - FiveM Database</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <nav>
            <div class="nav-container">
                <h1>FiveM Database</h1>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="citoyens.php">Citoyens</a></li>
                    <li><a href="gangs.php">Gangs</a></li>
                    <li><a href="lspd.php" class="active">LSPD</a></li>
                    <li><a href="bcso.php">BCSO</a></li>
                    <li><a href="entreprises.php">Entreprises</a></li>
                    <li><a href="notes.php">Notes</a></li>
                    <li><a href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main>
        <div class="container">
            <h2>Gestion des agents LSPD</h2>

            <?php if (isset($message)): ?>
                <div class="message success"><?php echo $message; ?></div>
            <?php endif; ?>

            <!-- Formulaire d'ajout/modification -->
            <div class="form-section">
                <h3><?php echo $agent_edit ? 'Modifier l\'agent' : 'Ajouter un nouvel agent'; ?></h3>
                <form method="POST" enctype="multipart/form-data" class="agent-form">
                    <input type="hidden" name="action" value="<?php echo $agent_edit ? 'edit_agent' : 'add_agent'; ?>">
                    <?php if ($agent_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $agent_edit['id']; ?>">
                    <?php endif; ?>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom">Nom :</label>
                            <input type="text" id="nom" name="nom" required 
                                   value="<?php echo $agent_edit ? htmlspecialchars($agent_edit['nom']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="prenom">Prénom :</label>
                            <input type="text" id="prenom" name="prenom" required 
                                   value="<?php echo $agent_edit ? htmlspecialchars($agent_edit['prenom']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="telephone">Téléphone :</label>
                            <input type="text" id="telephone" name="telephone" 
                                   value="<?php echo $agent_edit ? htmlspecialchars($agent_edit['telephone']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="matricule">Matricule :</label>
                            <input type="text" id="matricule" name="matricule" required 
                                   value="<?php echo $agent_edit ? htmlspecialchars($agent_edit['matricule']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="rang">Rang :</label>
                            <select id="rang" name="rang" required>
                                <option value="">Sélectionner un rang</option>
                                <?php foreach ($rangs as $rang): ?>
                                    <option value="<?php echo $rang; ?>" 
                                            <?php echo ($agent_edit && $agent_edit['rang'] == $rang) ? 'selected' : ''; ?>>
                                        <?php echo $rang; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="unite">Unité :</label>
                            <select id="unite" name="unite" required>
                                <option value="">Sélectionner une unité</option>
                                <?php foreach ($unites as $unite): ?>
                                    <option value="<?php echo $unite; ?>" 
                                            <?php echo ($agent_edit && $agent_edit['unite'] == $unite) ? 'selected' : ''; ?>>
                                        <?php echo $unite; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="grade_unite">Grade d'unité :</label>
                            <select id="grade_unite" name="grade_unite" required>
                                <option value="">Sélectionner un grade</option>
                                <?php foreach ($grades_unite as $grade): ?>
                                    <option value="<?php echo $grade; ?>" 
                                            <?php echo ($agent_edit && $agent_edit['grade_unite'] == $grade) ? 'selected' : ''; ?>>
                                        <?php echo $grade; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="photo_portrait">Photo de portrait :</label>
                            <input type="file" id="photo_portrait" name="photo_portrait" accept="image/*">
                            <?php if ($agent_edit && $agent_edit['photo_portrait']): ?>
                                <p class="current-photo">Photo actuelle : 
                                    <img src="<?php echo $agent_edit['photo_portrait']; ?>" alt="Portrait actuel" style="max-width: 50px; max-height: 50px;">
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="photo_identite">Photo d'identité :</label>
                            <input type="file" id="photo_identite" name="photo_identite" accept="image/*">
                            <?php if ($agent_edit && $agent_edit['photo_identite']): ?>
                                <p class="current-photo">Photo actuelle : 
                                    <img src="<?php echo $agent_edit['photo_identite']; ?>" alt="Identité actuelle" style="max-width: 50px; max-height: 50px;">
                                </p>
                            <?php endif; ?>
                        </div>

                        <div class="form-group full-width">
                            <label for="notes">Notes :</label>
                            <textarea id="notes" name="notes" rows="4"><?php echo $agent_edit ? htmlspecialchars($agent_edit['notes']) : ''; ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $agent_edit ? 'Modifier l\'agent' : 'Ajouter l\'agent'; ?>
                        </button>
                        <?php if ($agent_edit): ?>
                            <a href="lspd.php" class="btn btn-secondary">Annuler</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Liste des agents -->
            <div class="agents-section">
                <h3>Liste des agents LSPD (<?php echo count($agents); ?>)</h3>
                
                <?php if (empty($agents)): ?>
                    <p class="no-data">Aucun agent LSPD enregistré.</p>
                <?php else: ?>
                    <div class="agents-grid">
                        <?php foreach ($agents as $agent): ?>
                            <div class="agent-card">
                                <div class="agent-header">
                                    <div class="agent-photos">
                                        <?php if ($agent['photo_portrait']): ?>
                                            <img src="<?php echo $agent['photo_portrait']; ?>" alt="Portrait" class="photo-portrait">
                                        <?php else: ?>
                                            <div class="no-photo">Pas de photo</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="agent-info">
                                        <h4><?php echo htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']); ?></h4>
                                        <p class="matricule">Matricule : <?php echo htmlspecialchars($agent['matricule']); ?></p>
                                        <p class="rang"><?php echo htmlspecialchars($agent['rang']); ?></p>
                                    </div>
                                </div>
                                
                                <div class="agent-details">
                                    <p><strong>Téléphone :</strong> <?php echo htmlspecialchars($agent['telephone']); ?></p>
                                    <p><strong>Unité :</strong> <?php echo htmlspecialchars($agent['unite']); ?></p>
                                    <p><strong>Grade d'unité :</strong> <?php echo htmlspecialchars($agent['grade_unite']); ?></p>
                                    
                                    <?php if ($agent['notes']): ?>
                                        <div class="notes">
                                            <strong>Notes :</strong>
                                            <p><?php echo nl2br(htmlspecialchars($agent['notes'])); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent['photo_identite']): ?>
                                        <div class="photo-identite">
                                            <strong>Photo d'identité :</strong>
                                            <img src="<?php echo $agent['photo_identite']; ?>" alt="Identité" class="photo-id">
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="agent-actions">
                                    <a href="lspd.php?edit=<?php echo $agent['id']; ?>" class="btn btn-edit">Modifier</a>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet agent ?');">
                                        <input type="hidden" name="action" value="delete_agent">
                                        <input type="hidden" name="id" value="<?php echo $agent['id']; ?>">
                                        <button type="submit" class="btn btn-delete">Supprimer</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 FiveM Database. Tous droits réservés.</p>
    </footer>

    <style>
        .agent-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .current-photo {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }

        .current-photo img {
            border-radius: 4px;
            margin-left: 5px;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
        }

        .agents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 20px;
        }

        .agent-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .agent-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .agent-header {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .agent-photos {
            flex-shrink: 0;
        }

        .photo-portrait {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2196F3;
        }

        .no-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #666;
            border: 3px solid #ddd;
        }

        .agent-info h4 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 18px;
        }

        .matricule {
            color: #2196F3;
            font-weight: bold;
            margin: 5px 0;
        }

        .rang {
            color: #666;
            margin: 5px 0;
        }

        .agent-details p {
            margin: 8px 0;
            color: #555;
        }

        .notes {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .notes p {
            margin: 5px 0 0 0;
            color: #666;
        }

        .photo-identite {
            margin-top: 15px;
        }

        .photo-id {
            width: 100px;
            height: 120px;
            object-fit: cover;
            border-radius: 4px;
            margin-top: 5px;
        }

        .agent-actions {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background: #2196F3;
            color: white;
        }

        .btn-primary:hover {
            background: #1976D2;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-edit {
            background: #28a745;
            color: white;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }

        .agents-section h3 {
            margin-bottom: 20px;
            color: #333;
        }
    </style>
</body>
</html>