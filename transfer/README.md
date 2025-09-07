Mini service de transfert (type WeTransfer)

Deploiement
1) Uploadez le dossier "transfer/" sur votre hebergement, dans public_html/transfer/
2) Laissez WordPress dans public_html/. Ce service est independant.
3) Ouvrez https://votre-domaine/transfer/ pour l'interface.

Configuration
- Email expediteur: modifiez OWNER_EMAIL dans transfer/config.php si besoin.
- Expiration: ajustez EXPIRY_DAYS (par defaut 4 jours).
- Taille d'upload: ajustez via hPanel (PHP Options) chez Hostinger.

Nettoyage automatique (CRON)
- Recommandé (toutes les heures) dans hPanel > Cron Jobs:
  php /home/USER/public_html/transfer/cleanup.php > /dev/null 2>&1
- Alternative HTTP si CLI indisponible:
  wget -q -O - https://votre-domaine/transfer/cleanup.php > /dev/null 2>&1

Endpoints
- POST /transfer/upload.php (form-data: file, recipient optionnel)
- GET  /transfer/download.php?token=...
- Court: /transfer/d/{token}

Securite
- Acces direct au dossier storage/ interdit via .htaccess
- Metadonnees en JSON dans transfer/storage/meta/


