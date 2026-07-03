# Security Hardening Guide

## Built into the plugin

- **JWT auth** (HS256, 8 h expiry) — set a dedicated `DTC_JWT_SECRET` in
  `wp-config.php`; rotate it to invalidate all sessions.
- **Login rate limiting** — 5 failed attempts / 15 min / IP on `/auth/login`.
- **Form rate limiting** — 10 submissions / 10 min / IP on all public forms.
- **Role-based access** — portal endpoints require the `dtc_portal_access`
  capability; downloads support public / customer / per-user assignment plus
  role restrictions, re-checked on every file request.
- **Download streaming** — files are served through the API (never direct
  upload URLs), and every download is written to the audit log.
- **Audit log** — logins, failed logins, geo blocks and file downloads
  (`dtc_audit_log` option; export regularly for retention).
- **GeoIP blocking** — set `dtc_blocked_countries` (ISO codes); works with
  Cloudflare's `CF-IPCountry` or Nginx geoip2.
- **CORS allowlist** — `dtc_allowed_origins`; empty by default (same-origin).

## Server checklist

- [ ] HTTPS everywhere (certbot / AutoSSL), redirect HTTP → HTTPS, HSTS.
- [ ] `define('DISALLOW_FILE_EDIT', true);` in wp-config.
- [ ] Block `xmlrpc.php` and `/wp-json/wp/v2/users` publicly in Nginx:
  ```nginx
  location = /xmlrpc.php { deny all; }
  ```
- [ ] Restrict `/wp-admin` and `/wp-login.php` by IP or basic-auth if the team
      is small.
- [ ] Keep uploads non-executable: `location ~* /wp-content/uploads/.*\.php$ { deny all; }`
- [ ] Automatic updates: `apt unattended-upgrades`; WP core/plugin auto-updates on.
- [ ] Strong DB password, DB user limited to the WP database, MariaDB bound to
      localhost only.
- [ ] fail2ban with the nginx and sshd jails.
- [ ] Restricted download files: store outside `public_html`/webroot or protect
      the uploads path — the API streams them with permission checks.
- [ ] Off-site encrypted backups (see backup-restore.md); test restores quarterly.
- [ ] Review the audit log (`Website Settings` DB option or WP-CLI:
      `wp option get dtc_audit_log --format=json`).
