<?php
session_start();
require_once 'config.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $titre = $_POST['titre'];
                $contenu = $_POST['contenu'];
                $priorite = $_POST['priorite'];
                $user_id = $_SESSION['user_id'];
                
                $stmt = $pdo->prepare("INSERT INTO notes (titre, contenu, priorite, user_id, date_creation) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$titre, $contenu, $priorite, $user_id]);
                
                $success = "Note ajoutée avec succès !";
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $titre = $_POST['titre'];
                $contenu = $_POST['contenu'];
                $priorite = $_POST['priorite'];
                
                $stmt = $pdo->prepare("UPDATE notes SET titre = ?, contenu = ?, priorite = ?, date_modification = NOW() WHERE id = ? AND user_id = ?");
                $stmt->execute([$titre, $contenu, $priorite, $id, $_SESSION['user_id']]);
                
                $success = "Note modifiée avec succès !";
                break;
                
            case 'delete':
                $id = $_POST['id'];
                $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
                $stmt->execute([$id, $_SESSION['user_id']]);
                
                $success = "Note supprimée avec succès !";
                break;
        }
    }
}

// Récupérer les notes de l'utilisateur
$stmt = $pdo->prepare("SELECT * FROM notes WHERE user_id = ? ORDER BY 
    CASE 
        WHEN priorite = 'haute' THEN 1
        WHEN priorite = 'moyenne' THEN 2
        WHEN priorite = 'basse' THEN 3
    END,
    date_creation DESC");
$stmt->execute([$_SESSION['user_id']]);
$notes = $stmt->fetchAll();

// Récupérer une note spécifique pour l'édition
$note_edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM notes WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
    $note_edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Notes - FiveM Database</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .notes-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .note-form {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            height: fit-content;
        }
        
        .note-form h3 {
            color: #4CAF50;
            margin-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #fff;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px;
            background: #3a3a3a;
            border: 1px solid #555;
            border-radius: 4px;
            color: #fff;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .priority-select {
            background: #3a3a3a;
            border: 1px solid #555;
            color: #fff;
        }
        
        .btn {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn:hover {
            background: #45a049;
        }
        
        .btn-danger {
            background: #f44336;
        }
        
        .btn-danger:hover {
            background: #da190b;
        }
        
        .btn-warning {
            background: #ff9800;
        }
        
        .btn-warning:hover {
            background: #f57c00;
        }
        
        .btn-cancel {
            background: #666;
        }
        
        .btn-cancel:hover {
            background: #555;
        }
        
        .notes-list {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 8px;
            max-height: 600px;
            overflow-y: auto;
        }
        
        .note-item {
            background: #3a3a3a;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border-left: 4px solid #4CAF50;
        }
        
        .note-item.priority-haute {
            border-left-color: #f44336;
        }
        
        .note-item.priority-moyenne {
            border-left-color: #ff9800;
        }
        
        .note-item.priority-basse {
            border-left-color: #4CAF50;
        }
        
        .note-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .note-title {
            color: #fff;
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .note-priority {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .priority-haute {
            background: #f44336;
            color: white;
        }
        
        .priority-moyenne {
            background: #ff9800;
            color: white;
        }
        
        .priority-basse {
            background: #4CAF50;
            color: white;
        }
        
        .note-content {
            color: #ddd;
            line-height: 1.6;
            margin-bottom: 10px;
            white-space: pre-wrap;
        }
        
        .note-meta {
            font-size: 12px;
            color: #aaa;
            margin-bottom: 10px;
        }
        
        .note-actions {
            display: flex;
            gap: 10px;
        }
        
        .note-actions button {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .success-message {
            background: #4CAF50;
            color: white;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .search-box {
            margin-bottom: 20px;
        }
        
        .search-box input {
            width: 100%;
            padding: 10px;
            background: #3a3a3a;
            border: 1px solid #555;
            border-radius: 4px;
            color: #fff;
        }
        
        .notes-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: #3a3a3a;
            border-radius: 8px;
        }
        
        .stat-item {
            text-align: center;
            color: #fff;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            display: block;
        }
        
        .stat-label {
            font-size: 12px;
            color: #aaa;
        }
        
        @media (max-width: 768px) {
            .notes-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <h2>Système de Notes</h2>
        
        <?php if (isset($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <!-- Statistiques des notes -->
        <div class="notes-stats">
            <div class="stat-item">
                <span class="stat-number"><?php echo count($notes); ?></span>
                <span class="stat-label">Total Notes</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count(array_filter($notes, function($n) { return $n['priorite'] == 'haute'; })); ?></span>
                <span class="stat-label">Haute Priorité</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count(array_filter($notes, function($n) { return $n['priorite'] == 'moyenne'; })); ?></span>
                <span class="stat-label">Moyenne Priorité</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?php echo count(array_filter($notes, function($n) { return $n['priorite'] == 'basse'; })); ?></span>
                <span class="stat-label">Basse Priorité</span>
            </div>
        </div>
        
        <div class="notes-container">
            <!-- Formulaire d'ajout/édition de note -->
            <div class="note-form">
                <h3><?php echo $note_edit ? 'Modifier la note' : 'Ajouter une nouvelle note'; ?></h3>
                
                <form method="POST">
                    <input type="hidden" name="action" value="<?php echo $note_edit ? 'edit' : 'add'; ?>">
                    <?php if ($note_edit): ?>
                        <input type="hidden" name="id" value="<?php echo $note_edit['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="titre">Titre de la note *</label>
                        <input type="text" id="titre" name="titre" value="<?php echo $note_edit ? htmlspecialchars($note_edit['titre']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contenu">Contenu *</label>
                        <textarea id="contenu" name="contenu" required><?php echo $note_edit ? htmlspecialchars($note_edit['contenu']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="priorite">Priorité</label>
                        <select id="priorite" name="priorite" class="priority-select">
                            <option value="basse" <?php echo ($note_edit && $note_edit['priorite'] == 'basse') ? 'selected' : ''; ?>>Basse</option>
                            <option value="moyenne" <?php echo ($note_edit && $note_edit['priorite'] == 'moyenne') ? 'selected' : ''; ?>>Moyenne</option>
                            <option value="haute" <?php echo ($note_edit && $note_edit['priorite'] == 'haute') ? 'selected' : ''; ?>>Haute</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">
                        <?php echo $note_edit ? 'Modifier la note' : 'Ajouter la note'; ?>
                    </button>
                    
                    <?php if ($note_edit): ?>
                        <a href="notes.php" class="btn btn-cancel">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Liste des notes -->
            <div class="notes-list">
                <h3>Mes Notes</h3>
                
                <!-- Barre de recherche -->
                <div class="search-box">
                    <input type="text" id="search" placeholder="Rechercher dans les notes..." onkeyup="searchNotes()">
                </div>
                
                <?php if (empty($notes)): ?>
                    <p style="color: #aaa; text-align: center; padding: 20px;">Aucune note trouvée. Ajoutez votre première note !</p>
                <?php else: ?>
                    <?php foreach ($notes as $note): ?>
                        <div class="note-item priority-<?php echo $note['priorite']; ?>" data-searchable="<?php echo strtolower($note['titre'] . ' ' . $note['contenu']); ?>">
                            <div class="note-header">
                                <h4 class="note-title"><?php echo htmlspecialchars($note['titre']); ?></h4>
                                <span class="note-priority priority-<?php echo $note['priorite']; ?>">
                                    <?php echo ucfirst($note['priorite']); ?>
                                </span>
                            </div>
                            
                            <div class="note-content">
                                <?php echo htmlspecialchars($note['contenu']); ?>
                            </div>
                            
                            <div class="note-meta">
                                Créée le <?php echo date('d/m/Y à H:i', strtotime($note['date_creation'])); ?>
                                <?php if ($note['date_modification']): ?>
                                    | Modifiée le <?php echo date('d/m/Y à H:i', strtotime($note['date_modification'])); ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="note-actions">
                                <a href="notes.php?edit=<?php echo $note['id']; ?>" class="btn btn-warning">Modifier</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $note['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        function searchNotes() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const notes = document.querySelectorAll('.note-item');
            
            notes.forEach(note => {
                const searchableText = note.getAttribute('data-searchable');
                if (searchableText.includes(searchTerm)) {
                    note.style.display = 'block';
                } else {
                    note.style.display = 'none';
                }
            });
        }
        
        // Auto-save en tant que brouillon (optionnel)
        let autoSaveTimer;
        const titleInput = document.getElementById('titre');
        const contentInput = document.getElementById('contenu');
        
        function autoSave() {
            const title = titleInput.value;
            const content = contentInput.value;
            
            if (title.trim() || content.trim()) {
                // Sauvegarder dans le localStorage
                localStorage.setItem('note_draft', JSON.stringify({
                    title: title,
                    content: content,
                    timestamp: Date.now()
                }));
            }
        }
        
        // Charger le brouillon au chargement de la page
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('note_draft');
            if (draft && !<?php echo $note_edit ? 'true' : 'false'; ?>) {
                const parsed = JSON.parse(draft);
                // Charger seulement si le brouillon est récent (moins de 24h)
                if (Date.now() - parsed.timestamp < 86400000) {
                    if (confirm('Un brouillon a été trouvé. Voulez-vous le restaurer ?')) {
                        titleInput.value = parsed.title;
                        contentInput.value = parsed.content;
                    }
                }
            }
        });
        
        // Auto-save toutes les 30 secondes
        if (titleInput && contentInput) {
            titleInput.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSave, 30000);
            });
            
            contentInput.addEventListener('input', function() {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSave, 30000);
            });
        }
        
        // Supprimer le brouillon après soumission réussie
        document.querySelector('form').addEventListener('submit', function() {
            localStorage.removeItem('note_draft');
        });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>