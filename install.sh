#!/bin/bash

set -e
set -x

if [ "$EUID" -ne 0 ]
  then echo "Please run as root (sudo)"
  exit 1
fi

echo "Install required packages..."
sleep 3
apt update
apt -y install \
   git \
   wget \
   mysql-server \
   nginx \
   php7.4-fpm \
   php7.4-cli \
   php7.4-curl \
   php7.4-gd \
   php7.4-igbinary \
   php7.4-json \
   php7.4-mbstring \
   php7.4-memcache \
   php7.4-memcached \
   php7.4-msgpack \
   php7.4-mysql \
   php7.4-opcache \
   php7.4-readline \
   php7.4-snmp \
   php7.4-soap \
   php7.4-xml \
   php7.4-yaml \
   php7.4-zip \
   composer \
   apt-transport-https \
   ca-certificates \
   curl \
   gnupg-agent \
   software-properties-common

curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
add-apt-repository \
   "deb [arch=amd64] https://download.docker.com/linux/ubuntu \
   $(lsb_release -cs) \
   stable"
apt update && apt -y install \
    docker-ce \
    docker-ce-cli \
    containerd.io

curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

echo "Configure files ..." && sleep 1
mkdir -p /var/log/all-ok-billing
mkdir -p /www
cp -rT ./ /www
cd /www
chown -R www-data:www-data /www
chown -R www-data:www-data /var/log/all-ok-billing

echo "Configure nginx" && sleep 1
cp ./install/nginx/sites-enabled/* /etc/nginx/sites-enabled/

echo "Install /etc/hosts ..." && sleep 1
cat <<EOT >> /etc/hosts
127.0.0.1 apiv2.local
127.0.0.1 api.local
127.0.0.1 service.local
127.0.0.1 gw.local
127.0.0.1 sw.local
EOT

echo "Install crontabs ..." && sleep 1
cd /www
cp ./install/cron /etc/cron.d/cron
crontab /etc/cron.d/cron

echo "Configure PHP ..." && sleep 1
cd /www
cp ./install/php.ini /etc/php/7.4/fpm/conf.d/40-all-ok-billing.ini
cp ./install/php.ini /etc/php/7.4/cli/conf.d/40-all-ok-billing.ini
cp /etc/php/7.4/fpm/pool.d/www.conf /etc/php/7.4/fpm/pool.d/www.conf.bak
cp ./install/www.conf /etc/php/7.4/fpm/pool.d/www.conf


echo "Configure mysql server..." && sleep 1
cd /www
cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.bak
cp ./install/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf

echo "Restart services..." && sleep 1
service nginx restart
service php7.4-fpm restart
service mysql restart

echo "Prepare mysql databases..." && sleep 1
cd /www
mysql -e "CREATE DATABASE IF NOT exists service; CREATE DATABASE IF NOT EXISTS gwPayments;"
mysql -e "CREATE USER 'service'@'localhost' IDENTIFIED WITH mysql_native_password BY 'service';"
mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'service'@'localhost';"
mysql service < ./install/databases/service.sql
mysql gwPayments < ./install/databases/gwPayments.sql

echo "Configure all-ok-billing configuration files..." && sleep 1
cd /www
cp ./.env-example ./.env
mkdir -p /www/configs
cp -R ./configs-example/* ./configs

echo "Install all-ok-billing dependencies..." && sleep 1
cd /www
COMPOSER_NO_INTERACTION=1 composer install




echo "Start docker containers..." && sleep 1
cd /www/docker && docker-compose up -d --build
cd /www/docker/radius-server && docker-compose up -d --build

echo "Install wildcore-agent..." && sleep 1
cd ~
wget https://releases.wildcore.tools/install.sh && \
chmod +x ./install.sh && \
./install.sh
sleep 3
wca component:control all_ok_billing install

set +e
set +x
cat << EOF
All-Ok-Billing success installed!

You can connect to server by addresses(Add same hosts to /etc/hosts before use):
http://service.local - all-ok-billing service
http://sw.local - wildcore agent(devices diagnostic tool)
http://my.local - user personal area
http://api.local - all-ok-billing old API(used in schedule, payments)
http://gw.local - all-ok-billing payment gateway

Default login/password for service|wildcore - admin/admin

For working with wildcore console write for see supported commands
wca --help

If you want change *.local domain - change URL parameters in /www/.env

EOF


