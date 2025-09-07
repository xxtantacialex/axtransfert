<?php
require_once __DIR__ . '/config.php';
ensureDirectories();

$token = (string)($_GET['token'] ?? '');
if (!preg_match('/^[a-f0-9]{32}$/i', $token)) {
    http_response_code(400);
    echo 'Lien invalide.';
    exit;
}

$meta = loadMeta($token);
if (!$meta) {
    http_response_code(404);
    echo 'Fichier introuvable ou déjà supprimé.';
    exit;
}

if ((int)($meta['expires_at'] ?? 0) <= time()) {
    // Supprime immédiatement si expiré
    deleteByToken($token);
    http_response_code(410);
    echo 'Lien expiré.';
    exit;
}

$path = $meta['file_path'] ?? (STORAGE_DIR . '/' . $token);
if (!is_file($path)) {
    http_response_code(404);
    echo 'Fichier introuvable.';
    exit;
}

$mime = $meta['mime'] ?? 'application/octet-stream';
$filename = $meta['original_name'] ?? ('fichier-' . $token);
$filesize = (int)filesize($path);

// Désactive la mise en mémoire tampon pour streamer
@set_time_limit(0);
if (function_exists('apache_setenv')) {
    @apache_setenv('no-gzip', '1');
}
@ini_set('zlib.output_compression', 'Off');

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . str_replace('"', '', $filename) . '"');
header('Content-Length: ' . $filesize);
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

$chunkSize = 8 * 1024 * 1024; // 8MB
$fp = fopen($path, 'rb');
if ($fp === false) {
    http_response_code(500);
    echo 'Erreur lors de l\'ouverture du fichier.';
    exit;
}
while (!feof($fp)) {
    echo fread($fp, $chunkSize);
    @ob_flush();
    flush();
}
fclose($fp);
exit;


