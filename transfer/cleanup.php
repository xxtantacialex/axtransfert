<?php
require_once __DIR__ . '/config.php';
ensureDirectories();

$deleted = cleanupExpired();
header('Content-Type: text/plain; charset=utf-8');
echo "Nettoyage terminé. Fichiers supprimés: $deleted\n";


