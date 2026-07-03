# Deployment — Ubuntu 24.04 Proxmox LXC (2 vCPU / 4 GB / 100 GB)

Request flow: Visitor → Nginx → Vue static files → `/wp-json/*` proxied to PHP-FPM/WordPress → MariaDB (+ Redis object cache).

## 1. Container

Create an unprivileged Ubuntu 24.04 LXC, enable nesting, then:

```bash
apt update && apt upgrade -y
apt install -y nginx php8.3-fpm php8.3-mysql php8.3-curl php8.3-gd php8.3-mbstring \
  php8.3-xml php8.3-zip php8.3-intl php8.3-redis mariadb-server redis-server \
  certbot python3-certbot-nginx unzip curl git nodejs npm
```

Node.js/npm are used to build the frontend on the server; if you prefer to
build on your workstation or CI instead, they can be omitted.

## 1b. Clone the repository

The code lives at **https://github.com/mivu2k/meipage** (private). Give the
LXC read access with a fine-grained Personal Access Token (GitHub → Settings →
Developer settings → tokens, repo `meipage`, permission *Contents: read-only*),
or a deploy key (`ssh-keygen` on the LXC, add the public key under the repo's
Settings → Deploy keys).

```bash
git clone https://<TOKEN>@github.com/mivu2k/meipage.git /opt/meipage
# or with a deploy key:
git clone git@github.com:mivu2k/meipage.git /opt/meipage
```

## 2. MariaDB

```bash
mysql_secure_installation
mysql -e "CREATE DATABASE wp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'wp'@'localhost' IDENTIFIED BY '<strong-password>';
GRANT ALL ON wp.* TO 'wp'@'localhost'; FLUSH PRIVILEGES;"
```

Tune for 4 GB RAM in `/etc/mysql/mariadb.conf.d/60-tuning.cnf`:
```ini
[mysqld]
innodb_buffer_pool_size = 512M
max_connections = 60
```

## 3. WordPress

```bash
mkdir -p /var/www/wp && cd /var/www/wp
curl -O https://wordpress.org/latest.zip && unzip latest.zip && mv wordpress/* . && rm -rf wordpress latest.zip
cp -r /opt/meipage/wordpress/wp-content/plugins/dtc-headless wp-content/plugins/
chown -R www-data:www-data /var/www/wp
```

In `wp-config.php` add:
```php
define('DTC_JWT_SECRET', '<64 random chars>');
define('WP_REDIS_HOST', '127.0.0.1');
define('DISALLOW_FILE_EDIT', true);
```

Complete the install at `http://<ip>/wp-admin`, set **Permalinks → Post name**,
activate **DTC Headless**, and install the *Redis Object Cache* plugin.

## 4. Frontend

Build from the clone on the LXC and publish to the web root:

```bash
cd /opt/meipage/frontend
npm ci && npm run build
mkdir -p /var/www/site
rsync -a --delete dist/ /var/www/site/
```

(Alternative: build on your workstation and `rsync -a dist/ root@<lxc>:/var/www/site/`.)

## 5. Nginx

`/etc/nginx/sites-available/site`:
```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/site;
    index index.html;

    # Vue SPA
    location / {
        try_files $uri $uri/ /index.html;
    }
    location /assets/ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # WordPress API + admin
    location ~ ^/(wp-json|wp-admin|wp-login\.php|wp-content|wp-includes) {
        root /var/www/wp;
        try_files $uri $uri/ /index.php?$args;
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        }
    }

    client_max_body_size 64m;
    add_header X-Frame-Options SAMEORIGIN always;
    add_header X-Content-Type-Options nosniff always;
    add_header Referrer-Policy strict-origin-when-cross-origin always;
}
```

```bash
ln -s /etc/nginx/sites-available/site /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx
certbot --nginx -d example.com     # HTTPS + auto-renew
```

Because the SPA and API share one origin, no CORS configuration is needed.

## 6. PHP-FPM tuning (4 GB)

`/etc/php/8.3/fpm/pool.d/www.conf`: `pm = dynamic`, `pm.max_children = 12`,
`pm.start_servers = 3`. Set `memory_limit = 256M` in `php.ini`.

## 7. Updating from GitHub

Save as `/usr/local/bin/site-deploy.sh` (`chmod +x`) and run after each push:

```bash
#!/usr/bin/env bash
set -euo pipefail
cd /opt/meipage
git pull --ff-only origin main

# Frontend
cd frontend && npm ci && npm run build
rsync -a --delete dist/ /var/www/site/

# WordPress plugin
rsync -a --delete /opt/meipage/wordpress/wp-content/plugins/dtc-headless/ \
  /var/www/wp/wp-content/plugins/dtc-headless/
chown -R www-data:www-data /var/www/wp/wp-content/plugins/dtc-headless
echo "Deployed $(git -C /opt/meipage rev-parse --short HEAD)"
```

Hashed asset filenames make frontend deploys atomic; plugin updates take effect
immediately (flush the object cache with `wp redis flush` if responses look stale).

## 8. GeoIP blocking (optional)

Easiest: put the site behind Cloudflare — the plugin reads `CF-IPCountry`.
Self-hosted: `apt install libnginx-mod-http-geoip2`, download the MaxMind
GeoLite2 country DB, and set `fastcgi_param GEOIP_COUNTRY_CODE $geoip2_country_code;`.
Configure blocked ISO codes in the `dtc_blocked_countries` option.
