<?php
declare(strict_types=1);

// Configuration de base
const OWNER_EMAIL = 'axcavpro@gmail.com';
const EXPIRY_DAYS = 4; // jours

define('STORAGE_DIR', __DIR__ . '/storage/uploads');
define('META_DIR', __DIR__ . '/storage/meta');

function ensureDirectories(): void
{
    if (!is_dir(STORAGE_DIR)) {
        @mkdir(STORAGE_DIR, 0775, true);
    }
    if (!is_dir(META_DIR)) {
        @mkdir(META_DIR, 0775, true);
    }
}

function baseUrl(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return $scheme . '://' . $host . ($scriptDir === '' ? '' : $scriptDir);
}

function generateToken(int $hexLength = 32): string
{
    // Génère une chaîne hex aléatoire de longueur désirée
    $bytes = random_bytes((int)ceil($hexLength / 2));
    return substr(bin2hex($bytes), 0, $hexLength);
}

function saveMeta(string $token, array $data): void
{
    $path = META_DIR . '/' . $token . '.json';
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    @chmod($path, 0664);
}

function loadMeta(string $token): ?array
{
    $path = META_DIR . '/' . $token . '.json';
    if (!is_file($path)) {
        return null;
    }
    $raw = file_get_contents($path);
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function deleteByToken(string $token): void
{
    $meta = loadMeta($token);
    $filePath = $meta['file_path'] ?? (STORAGE_DIR . '/' . $token);
    if (is_file($filePath)) {
        @unlink($filePath);
    }
    $metaPath = META_DIR . '/' . $token . '.json';
    if (is_file($metaPath)) {
        @unlink($metaPath);
    }
}

function cleanupExpired(): int
{
    $count = 0;
    if (!is_dir(META_DIR)) {
        return 0;
    }
    $files = glob(META_DIR . '/*.json') ?: [];
    $now = time();
    foreach ($files as $metaPath) {
        $raw = @file_get_contents($metaPath);
        $data = $raw ? json_decode($raw, true) : null;
        if (!$data) {
            continue;
        }
        $expiresAt = (int)($data['expires_at'] ?? 0);
        if ($expiresAt <= $now) {
            $token = $data['token'] ?? basename($metaPath, '.json');
            $filePath = $data['file_path'] ?? (STORAGE_DIR . '/' . $token);
            if (is_file($filePath)) {
                @unlink($filePath);
            }
            @unlink($metaPath);
            $count++;
        }
    }
    return $count;
}


