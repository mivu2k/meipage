# Database Schema

The backend uses standard WordPress tables (MariaDB/MySQL) — no custom tables.
All domain objects are custom post types with post meta, so every WP backup,
migration and hosting tool works unchanged.

## Custom post types (`wp_posts.post_type`)

| Post type | Visibility | Purpose |
|---|---|---|
| `dtc_product` | public | Catalog product |
| `dtc_brand` | public | Manufacturer landing page |
| `dtc_solution` | public | Solution page |
| `dtc_download` | admin-only listing | Downloadable file with access rules |
| `dtc_branch` | admin-only | Office/branch (unlimited) |
| `dtc_career` | public | Job position |
| `dtc_testimonial`, `dtc_partner` | admin-only | Homepage content |
| `dtc_inquiry` | private | Submitted quote request |
| `dtc_ticket` | private | Support ticket |
| `dtc_repair` | private | RMA / repair case |
| `dtc_application` | private | Job application |

## Taxonomies (`wp_terms` / `wp_term_taxonomy`)

- `dtc_product_cat` — hierarchical categories → subcategories for products
- `dtc_brand_tax` — flat brand relation on products/downloads/solutions (fast filtering; the `dtc_brand` CPT holds page content, matched by slug)
- `dtc_download_type` — driver, firmware, manual, software, brochure, config, bulletin, training, datasheet

## Key post meta (`wp_postmeta`)

| Post type | Meta keys |
|---|---|
| product | `gallery` (media IDs), `videos`, `features`, `specifications` ([{label,value}]), `applications`, `accessories`/`compatible`/`related` (product IDs), `downloads` (download IDs), `certifications` |
| brand | `website`, `country` |
| solution | `architecture` (HTML), `components`, `benefits`, `related_products`, `case_studies` ([{title,summary}]), `downloads` |
| download | `file` (attachment ID), `version`, `size`, `access` (public/customer/assigned), `assigned_users` (user IDs), `allowed_roles` |
| branch | `address`, `lat`, `lng`, `phone`, `email`, `working_hours`, `departments` ([{name,phone,email}]) |
| career | `location`, `type`, `department` |
| inquiry | `name`, `email`, `organization`, `country`, `message`, `products` ([{id,title,quantity}]), `status`, `user_id` |
| ticket | `user_id`, `type`, `status`, `messages` ([{author,body,date}]) |
| repair | `user_id`, `rma`, `product`, `serial`, `stage`, `history` ([{stage,date,note}]) |

Array values are stored serialized (standard WP meta). ACF field groups using
these exact field names will populate them directly.

## Users

- Roles: `dtc_customer`, `dtc_dealer`, `dtc_partner`, `dtc_employee` (+ WP `administrator`)
- Capabilities: `dtc_portal_access`, `dtc_download_files`, `dtc_create_tickets`, `dtc_manage` (admin)
- User meta: `dtc_products` — array of product IDs shown as "My Products"

## Options (`wp_options`)

- `dtc_settings` — the entire global website settings object (see API `/settings`)
- `dtc_allowed_origins` — CORS allowlist for cross-origin SPA deployments
- `dtc_blocked_countries` — GeoIP block list (ISO codes)
- `dtc_audit_log` — ring buffer of the last 2000 security events
