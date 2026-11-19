#!/bin/bash
# Run ON THE VM:  cd ~/ISProject1/deploy && sudo bash setup-ip.sh
set -e
cd "$(dirname "$0")"
APP=/var/www/isproject1
DBPASS=$(openssl rand -hex 12)

apt update
DEBIAN_FRONTEND=noninteractive apt install -y apache2 mysql-server php libapache2-mod-php php-mysql fail2ban unzip
a2enmod headers rewrite

mkdir -p $APP
cp -r ../. $APP/
# put a random DB password into the schema and the app config
sed -i "s/ChangeMe_Str0ng!/$DBPASS/g" $APP/db/schema.sql $APP/inc/bootstrap.php
mysql < $APP/db/schema.sql
chown -R www-data:www-data $APP

cp ip-site.conf /etc/apache2/sites-available/isproject1.conf
a2dissite 000-default.conf || true
a2ensite isproject1.conf
apache2ctl configtest
systemctl reload apache2
systemctl enable --now fail2ban

echo "-------------------------------------------"
echo "DONE. DB password (saved in app config): $DBPASS"
echo "Open: http://$(curl -s ifconfig.me)/"
echo "Next: php $APP/deploy/create_admin.php admin@example.com 'Admin12345'"
