# Deployment — cPanel Shared Hosting

The architecture degrades gracefully to shared hosting: WordPress runs as usual;
the Vue app is uploaded as pre-built static files.

## Layout (single domain — recommended)

```
public_html/
  index.html, assets/          ← contents of frontend/dist
  .htaccess                    ← SPA rewrite (below)
  wp/                          ← full WordPress install
```

1. Install WordPress into `public_html/wp` via the cPanel installer (Softaculous),
   upload/activate the `dtc-headless` plugin, set permalinks to "Post name",
   and add `define('DTC_JWT_SECRET', '...')` to `wp-config.php`.
2. Build locally: `cd frontend && npm run build` with `.env`:
   `VITE_API_BASE=/wp` (so requests go to `/wp/wp-json/...`).
3. Upload `dist/*` into `public_html/`.
4. `.htaccess` in `public_html`:

```apache
RewriteEngine On
# Let WordPress handle its own directory
RewriteRule ^wp/ - [L]
# SPA fallback
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.html [L]

# Pass Authorization header to PHP (required for JWT)
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

5. Enable AutoSSL / Let's Encrypt in cPanel for HTTPS.

## Layout (subdomain API)

Alternatively host WP at `api.example.com` and the SPA at `example.com`.
Set `VITE_API_BASE=https://api.example.com` before building, and add
`https://example.com` to the `dtc_allowed_origins` option so the plugin sends
CORS headers.

## Shared-hosting notes

- Redis is usually unavailable — skip the object cache; enable a page-cache
  plugin for `wp-admin` responsiveness if needed.
- Cron: replace WP-Cron with a real cPanel cron job hitting
  `wp/wp-cron.php` every 5 minutes and `define('DISABLE_WP_CRON', true);`.
- File permissions: 644 files / 755 directories.
