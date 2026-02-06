#!/bin/bash

###############################################################################
# Server Manager - Server Provisioning Script
# Multi-Architecture Support (x86_64 and ARM64)
###############################################################################

set -e # Exit on error

echo "======================================"
echo "Server Manager - Server Provisioning"
echo "======================================"
echo "Server: {{ $server->name }}"
echo "Architecture: {{ $server->architecture }}"
echo "Instance Type: {{ $server->instance_type }}"
echo "======================================"

# Detect architecture
ARCH=$(uname -m)
echo "Detected architecture: $ARCH"

# Update system
echo "[1/15] Updating system packages..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq

# Install basic utilities
echo "[2/15] Installing basic utilities..."
apt-get install -y -qq \
    software-properties-common \
    curl \
    wget \
    git \
    unzip \
    zip \
    ca-certificates \
    apt-transport-https \
    gnupg \
    lsb-release \
    ufw \
    fail2ban

# Add PHP repository
echo "[3/15] Adding PHP repository..."
add-apt-repository ppa:ondrej/php -y
add-apt-repository ppa:ondrej/nginx -y
apt-get update -qq

# Install Webserver
echo "[4/15] Installing {{ $config['webserver'] ?? 'NGINX' }}..."
@if(($config['webserver'] ?? 'nginx') === 'nginx')
apt-get install -y -qq nginx
systemctl enable nginx
@else
apt-get install -y -qq apache2
systemctl enable apache2
@endif

# Install PHP
echo "[5/15] Installing PHP {{ $config['php_version'] ?? '8.3' }}..."
PHP_VERSION="{{ $config['php_version'] ?? '8.3' }}"
apt-get install -y -qq \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-common \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-sqlite3 \
    php${PHP_VERSION}-redis \
    php${PHP_VERSION}-memcached \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-opcache

systemctl enable php${PHP_VERSION}-fpm

# Install Composer
echo "[6/15] Installing Composer..."
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
chmod +x /usr/local/bin/composer

# Install Database
@if(isset($config['database']) && $config['database'] !== 'none')
echo "[7/15] Installing {{ $config['database'] }}..."
@if($config['database'] === 'mysql')
apt-get install -y -qq mysql-server
systemctl enable mysql

# Secure MySQL and create password
MYSQL_ROOT_PASSWORD="{{ Str::random(32) }}"
mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '${MYSQL_ROOT_PASSWORD}';"
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

echo "MySQL root password: ${MYSQL_ROOT_PASSWORD}" > /root/.mysql_password
chmod 600 /root/.mysql_password

# Optimize MySQL for instance size
@if($server->architecture === 'arm64')
# ARM64 optimizations
sed -i 's/^key_buffer_size.*/key_buffer_size = 32M/' /etc/mysql/mysql.conf.d/mysqld.cnf
sed -i 's/^max_connections.*/max_connections = 100/' /etc/mysql/mysql.conf.d/mysqld.cnf
@else
# x86_64 optimizations
sed -i 's/^key_buffer_size.*/key_buffer_size = 64M/' /etc/mysql/mysql.conf.d/mysqld.cnf
sed -i 's/^max_connections.*/max_connections = 150/' /etc/mysql/mysql.conf.d/mysqld.cnf
@endif

@elseif($config['database'] === 'postgresql')
apt-get install -y -qq postgresql postgresql-contrib
systemctl enable postgresql

# Create PostgreSQL password
PG_PASSWORD="{{ Str::random(32) }}"
sudo -u postgres psql -c "ALTER USER postgres PASSWORD '${PG_PASSWORD}';"
echo "PostgreSQL password: ${PG_PASSWORD}" > /root/.pg_password
chmod 600 /root/.pg_password
@endif
@else
echo "[7/15] Skipping database installation..."
@endif

# Install Cache
@if(isset($config['cache']) && $config['cache'] !== 'none')
echo "[8/15] Installing {{ $config['cache'] }}..."
@if($config['cache'] === 'redis')
apt-get install -y -qq redis-server
systemctl enable redis-server
@elseif($config['cache'] === 'memcached')
apt-get install -y -qq memcached
systemctl enable memcached
@endif
@else
echo "[8/15] Skipping cache installation..."
@endif

# Install Node.js
@if(isset($config['nodejs']) && $config['nodejs'] !== 'none')
echo "[9/15] Installing Node.js {{ $config['nodejs'] }}..."
curl -fsSL https://deb.nodesource.com/setup_{{ $config['nodejs'] }}.x | bash -
apt-get install -y -qq nodejs
npm install -g npm@latest
npm install -g yarn pm2
@else
echo "[9/15] Skipping Node.js installation..."
@endif

# Install Supervisor
@if(isset($config['extras']) && in_array('supervisor', $config['extras'] ?? []))
echo "[10/15] Installing Supervisor..."
apt-get install -y -qq supervisor
systemctl enable supervisor
@else
echo "[10/15] Skipping Supervisor..."
@endif

# Install Docker (experimental on ARM64)
@if(isset($config['extras']) && in_array('docker', $config['extras'] ?? []))
echo "[11/15] Installing Docker..."
@if($server->architecture === 'arm64')
echo "⚠️  Warning: Docker on ARM64 is experimental"
@endif
curl -fsSL https://get.docker.com | sh
usermod -aG docker ubuntu
systemctl enable docker
@else
echo "[11/15] Skipping Docker..."
@endif

# Configure UFW Firewall
echo "[12/15] Configuring firewall..."
ufw --force enable
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp

# Configure Fail2ban
echo "[13/15] Configuring Fail2ban..."
systemctl enable fail2ban
systemctl start fail2ban

# Create deploy user
echo "[14/15] Creating deploy user..."
useradd -m -s /bin/bash -G www-data admin_agile
echo "admin_agile ALL=(ALL) NOPASSWD:ALL" > /etc/sudoers.d/admin_agile
chmod 440 /etc/sudoers.d/admin_agile

# Setup SSH for deploy user
mkdir -p /home/admin_agile/.ssh
chmod 700 /home/admin_agile/.ssh
cat > /home/admin_agile/.ssh/authorized_keys << 'EOF'
{{ $server->private_key ? ssh_keygen_public($server->private_key) : '' }}
EOF
chmod 600 /home/admin_agile/.ssh/authorized_keys
chown -R admin_agile:admin_agile /home/admin_agile/.ssh

# Optimize PHP-FPM
echo "[15/15] Optimizing PHP-FPM..."
PHP_FPM_CONF="/etc/php/${PHP_VERSION}/fpm/pool.d/www.conf"
sed -i 's/^pm = .*/pm = dynamic/' $PHP_FPM_CONF
sed -i 's/^pm.max_children = .*/pm.max_children = 10/' $PHP_FPM_CONF
sed -i 's/^pm.start_servers = .*/pm.start_servers = 2/' $PHP_FPM_CONF
sed -i 's/^pm.min_spare_servers = .*/pm.min_spare_servers = 1/' $PHP_FPM_CONF
sed -i 's/^pm.max_spare_servers = .*/pm.max_spare_servers = 3/' $PHP_FPM_CONF

# Restart services
systemctl restart php${PHP_VERSION}-fpm
@if(($config['webserver'] ?? 'nginx') === 'nginx')
systemctl restart nginx
@else
systemctl restart apache2
@endif

# Verify installation
echo ""
echo "======================================"
echo "Verifying Installation..."
echo "======================================"
php -v
composer --version
@if(($config['webserver'] ?? 'nginx') === 'nginx')
nginx -v
@else
apache2 -v
@endif
@if(isset($config['nodejs']) && $config['nodejs'] !== 'none')
node -v
npm -v
@endif

echo ""
echo "======================================"
echo "✅ Provisioning Complete!"
echo "======================================"
echo "Server is ready to deploy applications"
echo ""
echo "Stack Summary:"
echo "- OS: Ubuntu {{ $server->os_version }}"
echo "- Arch: {{ $server->architecture }}"
echo "- Webserver: {{ $config['webserver'] ?? 'NGINX' }}"
echo "- PHP: {{ $config['php_version'] ?? '8.3' }}"
@if(isset($config['database']) && $config['database'] !== 'none')
echo "- Database: {{ $config['database'] }}"
@endif
@if(isset($config['cache']) && $config['cache'] !== 'none')
echo "- Cache: {{ $config['cache'] }}"
@endif
@if(isset($config['nodejs']) && $config['nodejs'] !== 'none')
echo "- Node.js: {{ $config['nodejs'] }}"
@endif
echo "======================================"
