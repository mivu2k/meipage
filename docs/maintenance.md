# Maintenance Guide

## Routine

| Cadence | Task |
|---|---|
| Daily (automated) | DB + uploads backup, off-site sync |
| Weekly | Apply WP core/plugin updates on a staging copy, then production; `apt upgrade`; check disk (`df -h`) |
| Monthly | Review audit log for anomalies; prune resolved tickets/old inquiries if desired; test a backup restore |
| Quarterly | Rotate `DTC_JWT_SECRET` (logs everyone out); review user accounts/roles; renew MaxMind GeoIP DB if self-hosted |

## Content operations (WordPress admin)

- **Products / Brands / Solutions / Careers / Branches** — their CPT menus; use
  ACF field groups matching the meta keys in `docs/database-schema.md` for
  friendly editing UIs.
- **Website Settings** — global branding, theme colors, hero, menus, footer
  (`Website Settings` menu; served at `/dtc/v1/settings`).
- **Downloads** — create a Download, attach the file (media), set `access`
  (public / customer / assigned) and, if assigned, the user IDs.
- **Tickets & repairs** — private CPTs; update `status` / `stage` meta and
  append to `messages` / `history` to advance them. (A richer admin UI is a
  good Phase-6 enhancement.)
- **Customers** — create WP users with role *Customer* (or Dealer/Partner);
  assign owned products via the `dtc_products` user meta.

## Frontend updates

```bash
cd frontend
npm outdated          # review
npm update
npm run build         # verify build passes
rsync -a dist/ server:/var/www/site/
```

Cache-busted asset filenames mean deploys are atomic; `index.html` is the only
non-hashed file.

## Monitoring

- Uptime check on `/` and `/wp-json/dtc/v1/settings`.
- `journalctl -u php8.3-fpm -u nginx --since today` for errors.
- MariaDB slow query log if the catalog grows large; add `WP Redis` object
  cache stats via `wp redis status`.
