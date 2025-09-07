<?php
require_once __DIR__ . '/config.php';
ensureDirectories();
// Nettoyage opportuniste
cleanupExpired();
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transfert de fichiers</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="bg"></div>
    <main class="container">
        <div class="card">
            <h1>Envoyer un fichier</h1>
            <p class="subtitle">Glissez-déposez votre fichier ou cliquez pour choisir. Optionnellement, indiquez l'email du destinataire.</p>

            <form id="uploadForm">
                <label class="dropzone" id="dropzone">
                    <input type="file" id="fileInput" name="file" hidden>
                    <div class="dropzone-inner">
                        <div class="icon">⬆️</div>
                        <div class="dz-text">
                            <strong>Glissez votre fichier ici</strong>
                            <span>ou cliquez pour sélectionner</span>
                        </div>
                    </div>
                </label>

                <div class="field">
                    <label for="recipient">Email du destinataire (optionnel)</label>
                    <input type="email" id="recipient" name="recipient" placeholder="destinataire@exemple.com">
                </div>

                <button type="submit" id="sendBtn" class="primary">Envoyer</button>

                <div class="progress" id="progress" hidden>
                    <div class="bar" id="progressBar" style="width:0%"></div>
                    <span class="label" id="progressLabel">0%</span>
                </div>

                <div class="result" id="result" hidden>
                    <p><strong>Lien de téléchargement :</strong></p>
                    <div class="link-row">
                        <input type="text" id="downloadLink" readonly>
                        <button type="button" id="copyBtn" class="secondary">Copier</button>
                    </div>
                    <p class="muted">Le fichier expirera automatiquement dans <?php echo (int)EXPIRY_DAYS; ?> jours.</p>
                </div>
            </form>
        </div>
        <footer>
            <span>Propulsé par un mini‑service privé</span>
        </footer>
    </main>

    <script>
        window.__APP__ = {
            uploadUrl: 'upload.php'
        };
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>


