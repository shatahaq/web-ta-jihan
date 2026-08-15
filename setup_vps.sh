#!/bin/bash
set -e

# Domain and paths
DOMAIN="dwiva.my.id"
PROJECT_DIR="/var/www/web-ta"
DB_NAME="tirtanadi"
DB_USER="tirtanadi_user"
DB_PASS="SecurePass123!"

echo "Updating system and installing dependencies..."
export DEBIAN_FRONTEND=noninteractive
sudo apt-get update
sudo apt-get install -y nginx mysql-server php-fpm php-mysql php-mbstring php-xml php-curl certbot python3-certbot-nginx

# Determine PHP version for FPM socket (e.g. 8.1 or 8.3)
PHP_V=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
PHP_FPM_SOCK="unix:/var/run/php/php${PHP_V}-fpm.sock"

echo "Setting up Database..."
sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

echo "Importing database schemas..."
if [ -f /home/ubuntu/web-ta/database/schema.sql ]; then
    sudo mysql -u root ${DB_NAME} < /home/ubuntu/web-ta/database/schema.sql
fi
if [ -f /home/ubuntu/web-ta/database/seed.sql ]; then
    sudo mysql -u root ${DB_NAME} < /home/ubuntu/web-ta/database/seed.sql
fi

echo "Moving files to ${PROJECT_DIR}..."
sudo mkdir -p ${PROJECT_DIR}
sudo rsync -av /home/ubuntu/web-ta/ ${PROJECT_DIR}/
sudo chown -R www-data:www-data ${PROJECT_DIR}

echo "Setting permissions..."
sudo chmod -R 775 ${PROJECT_DIR}/storage/uploads
sudo chmod -R 775 ${PROJECT_DIR}/storage/logs

echo "Configuring .env..."
sudo cp ${PROJECT_DIR}/.env.example ${PROJECT_DIR}/.env
sudo sed -i "s/APP_URL=.*/APP_URL=https:\/\/${DOMAIN}/" ${PROJECT_DIR}/.env
sudo sed -i "s/DB_DATABASE=.*/DB_DATABASE=${DB_NAME}/" ${PROJECT_DIR}/.env
sudo sed -i "s/DB_USERNAME=.*/DB_USERNAME=${DB_USER}/" ${PROJECT_DIR}/.env
sudo sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=${DB_PASS}/" ${PROJECT_DIR}/.env

echo "Configuring Nginx..."
NGINX_CONF="/etc/nginx/sites-available/${DOMAIN}"
sudo tee $NGINX_CONF > /dev/null <<EOF
server {
    listen 80;
    server_name ${DOMAIN} www.${DOMAIN};
    root ${PROJECT_DIR}/public;

    index index.php index.html index.htm;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass ${PHP_FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

sudo ln -sf $NGINX_CONF /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo systemctl restart nginx

echo "Obtaining SSL certificate via Certbot..."
sudo certbot --nginx -d ${DOMAIN} -d www.${DOMAIN} --non-interactive --agree-tos -m admin@${DOMAIN} --redirect || echo "Certbot failed, but moving on (perhaps domain DNS not propagated or Cloudflare proxy issue)"

echo "Deployment script completed!"
