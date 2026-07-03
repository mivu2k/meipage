# API Reference — `dtc/v1`

Base URL: `https://<site>/wp-json/dtc/v1`
Auth: `Authorization: Bearer <JWT>` where noted. Tokens expire after 8 hours.

## Authentication

| Method | Route | Auth | Description |
|---|---|---|---|
| POST | `/auth/login` | – | `{username, password}` → `{token, user}` (rate-limited: 5 fails / 15 min / IP) |
| GET | `/auth/me` | ✅ | Current user `{id, name, email, roles}` |

## Settings

| GET | `/settings` | – | Full site settings: company, contact, social, theme colors, homepage hero/stats/CTA, menus, footer |
| POST | `/settings` | admin | Merge-update settings |

## Catalog & content (public, read-only)

| Route | Params | Returns |
|---|---|---|
| `/products` | `page, per_page(≤48), search, brand, category` | `{items, total, pages}` |
| `/products/{slug}` | | Full product: gallery, videos, features, specifications, applications, accessories, compatible, related, downloads, certifications |
| `/product-categories` | | Hierarchical term list (`parent` field) |
| `/brands`, `/brands/{slug}` | | Brand pages with logo, website, country |
| `/solutions`, `/solutions/{slug}` | | Solutions with architecture, components, benefits, case studies, downloads |
| `/posts`, `/posts/{slug}` | `page, per_page, category` | Blog posts |
| `/pages/{slug}` | | WP pages (e.g. `about`) |
| `/branches` | | Offices with address, lat/lng, phone, email, hours, departments |
| `/careers` | | Open positions |

## Forms (public, POST, rate-limited 10 / 10 min / IP)

| Route | Body |
|---|---|
| `/inquiries` | `{name, email, organization, country, message, products:[{id,title,quantity}]}` — the quote request; emailed to sales and stored as a private `dtc_inquiry` post |
| `/contact` | `{name, email, subject, message, department?}` |
| `/consultations` | `{name, email, organization, solution, message}` |
| `/applications` | `{name, email, phone, position, cover_letter, resume_media_id?}` |

## Customer portal (requires `dtc_portal_access` capability)

| Method | Route | Description |
|---|---|---|
| GET | `/portal/downloads` | Files the user may access (access = public / customer / assigned, optional role restriction) |
| GET | `/portal/download-file/{id}` | Streams the file; permission re-checked and audited per request |
| GET | `/portal/products` | Products assigned to the user (`dtc_products` user meta) |
| GET | `/portal/tickets` | User's tickets |
| POST | `/portal/tickets` | `{subject, type, message}`; type ∈ technical, warranty, repair, documentation, training |
| GET | `/portal/tickets/{id}` | Single ticket with message thread |
| POST | `/portal/tickets/{id}/reply` | `{message}` |
| GET | `/portal/repairs` | RMA cases with stage (received → inspection → repair → testing → quality_check → ready → shipped) and history |
| GET | `/portal/inquiries` | Inquiry history |

## Errors

Standard WP error envelope: `{code, message, data:{status}}`.
`401` invalid credentials/expired token · `403` no permission · `404` not found · `429` rate limited.
