<?php
require_once __DIR__ . '/config.php';
ensureDirectories();
// Nettoyage opportuniste à chaque upload
cleanupExpired();

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'error' => 'Méthode non autorisée']);
        exit;
    }

    if (!isset($_FILES['file']) || !is_array($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Aucun fichier reçu']);
        exit;
    }

    $recipient = trim((string)($_POST['recipient'] ?? ''));
    if ($recipient !== '' && !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Email destinataire invalide']);
        exit;
    }

    $file = $_FILES['file'];
    $originalName = $file['name'];
    $size = (int)$file['size'];
    if ($size <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Fichier vide']);
        exit;
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $token = generateToken(32);
    $storedName = $token . ($ext ? ('.' . preg_replace('/[^a-z0-9]+/i', '', $ext)) : '');
    $targetPath = STORAGE_DIR . '/' . $storedName;

    if (!is_uploaded_file($file['tmp_name'])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Upload invalide']);
        exit;
    }

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'error' => "Impossible d'enregistrer le fichier"]);
        exit;
    }

    @chmod($targetPath, 0664);

    // Mime
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $targetPath) : null;
    if ($finfo) { finfo_close($finfo); }
    if (!$mime) { $mime = $file['type'] ?: 'application/octet-stream'; }

    $createdAt = time();
    $expiresAt = $createdAt + (EXPIRY_DAYS * 86400);
    $downloadUrl = rtrim(baseUrl(), '/') . '/download.php?token=' . $token;
    $prettyUrl = rtrim(baseUrl(), '/') . '/d/' . $token;

    $meta = [
        'token' => $token,
        'original_name' => $originalName,
        'stored_name' => $storedName,
        'file_path' => $targetPath,
        'mime' => $mime,
        'size' => $size,
        'created_at' => $createdAt,
        'expires_at' => $expiresAt,
        'sender_email' => OWNER_EMAIL,
        'recipient_email' => $recipient,
        'download_url' => $downloadUrl,
        'pretty_url' => $prettyUrl,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ];

    saveMeta($token, $meta);

    $emailSent = false;
    if ($recipient !== '') {
        $subject = 'Vous avez reçu un fichier';
        $expireDate = date('d/m/Y H:i', $expiresAt);
        $body = "Bonjour,\n\nVous avez reçu un fichier à télécharger :\n$downloadUrl\n(ou lien court) $prettyUrl\n\nCe lien expirera le $expireDate.\n\nCordialement,\nService de transfert";
        $headers = 'From: Transfert <' . OWNER_EMAIL . ">\r\n" .
                   'Reply-To: ' . OWNER_EMAIL . ">\r\n" .
                   "Content-Type: text/plain; charset=UTF-8\r\n";
        $emailSent = @mail($recipient, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers) === true;
    }

    echo json_encode([
        'ok' => true,
        'token' => $token,
        'download_url' => $downloadUrl,
        'pretty_url' => $prettyUrl,
        'expires_at' => $expiresAt,
        'email_sent' => $emailSent,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Erreur serveur', 'detail' => $e->getMessage()]);
}


