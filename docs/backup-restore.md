# Backup & Restore Guide

Three things constitute the entire site state:
1. **Database** (all content, settings, users, tickets, inquiries)
2. **`wp-content/uploads`** (media + downloadable files)
3. **Code** (this repo: plugin + frontend — already in version control)

## Automated daily backup (LXC)

`/usr/local/bin/site-backup.sh`:
```bash
#!/usr/bin/env bash
set -euo pipefail
STAMP=$(date +%F)
DEST=/var/backups/site
mkdir -p "$DEST"
mysqldump --single-transaction wp | gzip > "$DEST/db-$STAMP.sql.gz"
tar czf "$DEST/uploads-$STAMP.tar.gz" -C /var/www/wp wp-content/uploads
# keep 14 days locally
find "$DEST" -mtime +14 -delete
# off-site (choose one): rclone, borg, restic…
# rclone copy "$DEST" remote:site-backups/
```

```bash
chmod +x /usr/local/bin/site-backup.sh
echo '30 2 * * * root /usr/local/bin/site-backup.sh' > /etc/cron.d/site-backup
```

Also snapshot the whole LXC in Proxmox (vzdump) weekly for bare-metal recovery.

## Restore

```bash
# database
gunzip < db-2026-07-03.sql.gz | mysql wp
# uploads
tar xzf uploads-2026-07-03.tar.gz -C /var/www/wp
chown -R www-data:www-data /var/www/wp/wp-content/uploads
# code
git clone https://github.com/mivu2k/meipage.git /opt/meipage
cp -r /opt/meipage/wordpress/wp-content/plugins/dtc-headless /var/www/wp/wp-content/plugins/
cd /opt/meipage
cd frontend && npm ci && npm run build && rsync -a dist/ /var/www/site/
```

If the domain changed, run `wp search-replace 'old.com' 'new.com' --all-tables`.

## cPanel

Use cPanel's Backup Wizard (full account backup) weekly + JetBackup if
available. The same three components apply; download backups off the host.
