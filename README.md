# Defense Technology Company Website

Headless WordPress + Vue 3 enterprise website: product catalog (no e-commerce),
solutions, brands, news, careers, inquiry/quote system, support tickets, repair
tracking, and a permission-based customer download portal.

## Repository layout

```
frontend/                  Vue 3 + TypeScript + Vite + Tailwind SPA
  src/api/                 REST client, endpoint wrappers, shared types
  src/stores/              Pinia stores (auth, inquiry cart, site settings)
  src/router/              Routes + auth guard
  src/layouts/             Default (public) and Portal layouts
  src/components/          Header, footer, product card, Three.js hero
  src/pages/               All public pages + pages/portal/* (customer portal)
wordpress/
  wp-content/plugins/dtc-headless/   The entire backend as one plugin:
    class-dtc-post-types.php         CPTs + taxonomies (products, brands, …)
    class-dtc-roles.php              Customer/Dealer/Partner/Employee roles
    class-dtc-jwt.php                JWT auth (login, /auth/me, Bearer tokens)
    class-dtc-settings.php           Global site settings (/dtc/v1/settings)
    class-dtc-rest-content.php       Public catalog/content endpoints
    class-dtc-rest-forms.php         Inquiries, contact, consultations, jobs
    class-dtc-rest-portal.php        Downloads, tickets, repairs (auth required)
    class-dtc-security.php           CORS, rate limiting, GeoIP block, audit log
    class-dtc-admin.php              Website Settings admin screen
docs/                      API, schema, deployment, security, backup guides
```

## Quick start (development)

1. **WordPress**: run any local WP (e.g. `wp server`, LocalWP, or Docker) on
   `http://localhost:8080`. Symlink or copy `wordpress/wp-content/plugins/dtc-headless`
   into its plugins directory and activate **DTC Headless**.
   Add to `wp-config.php`: `define('DTC_JWT_SECRET', '<long random string>');`
2. **Frontend**:
   ```bash
   cd frontend
   npm install
   npm run dev        # http://localhost:5173, proxies /wp-json to :8080
   ```
3. Set permalinks to "Post name" in WP (Settings → Permalinks) so the REST
   routes work.

## Production build

```bash
cd frontend && npm run build   # outputs frontend/dist (static files)
```

Serve `dist/` with Nginx (SPA fallback to `index.html`) and proxy `/wp-json`
to PHP-FPM/WordPress. See [docs/deployment-lxc.md](docs/deployment-lxc.md) and
[docs/deployment-cpanel.md](docs/deployment-cpanel.md).

## Documentation

- [API reference](docs/api.md)
- [Database schema](docs/database-schema.md)
- [Proxmox LXC deployment](docs/deployment-lxc.md)
- [cPanel deployment](docs/deployment-cpanel.md)
- [Security hardening](docs/security.md)
- [Backup & restore](docs/backup-restore.md)
- [Maintenance guide](docs/maintenance.md)

## Development phases

The codebase already contains the skeleton for all phases; suggested order:
1. Infrastructure, auth, API, base layout ✅ (this scaffold)
2. Homepage / About / Contact / Blog content in WP
3. Products, brands, inquiry system content + ACF field groups
4. Solutions
5. Customer portal data (assign downloads/products/repairs to users)
6. Admin refinements (replace the JSON settings editor with ACF options pages)
7. Three.js polish + performance (image sizes, Redis object cache, CDN)
