#!/bin/bash
# Run on the Azure Ubuntu VM from inside the deploy/ folder:  sudo bash setup.sh
set -e
apt update && apt install -y apache2 mysql-server php libapache2-mod-php php-mysql fail2ban unzip
a2enmod ssl rewrite headers
mkdir -p /var/www/isproject1 /var/www/validation /etc/ssl/isproject1
cp -r ../* /var/www/isproject1/
chown -R www-data:www-data /var/www/isproject1
mysql < /var/www/isproject1/db/schema.sql
cp vhosts.conf /etc/apache2/sites-available/isproject1.conf
a2dissite 000-default.conf || true
echo "NEXT: edit /etc/apache2/sites-available/isproject1.conf (your domain), add the ZeroSSL files, then:"
echo "  sudo a2ensite isproject1.conf && sudo apache2ctl configtest && sudo systemctl reload apache2"
systemctl enable --now fail2ban
