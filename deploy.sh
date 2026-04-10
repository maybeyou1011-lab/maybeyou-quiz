#!/bin/bash
# ═══════════════════════════════════════════════════
# MaybeYou Quiz - One-Click Deploy Script
# Run on the DO server: bash /tmp/deploy.sh
# ═══════════════════════════════════════════════════
set -e

echo "=============================="
echo " MaybeYou Quiz - Deploying..."
echo "=============================="

# 1. Read DB password from DO config
DB_PASS=""
if [ -f /root/.digitalocean_password ]; then
    DB_PASS=$(grep -i 'wordpress_mysql_pass' /root/.digitalocean_password | grep -o '"[^"]*"' | tr -d '"' | head -1)
fi

if [ -z "$DB_PASS" ]; then
    # Try wp-config.php
    DB_PASS=$(grep "DB_PASSWORD" /var/www/html/wp-config.php | grep -o "'[^']*'" | tail -1 | tr -d "'")
fi

if [ -z "$DB_PASS" ]; then
    echo "[ERROR] Cannot find DB password!"
    echo "Please set it manually in /var/www/quiz/config.php"
    DB_PASS="CHANGE_ME"
fi

echo "[1/5] DB password found"

# 2. Create quiz directory
QUIZ_DIR="/var/www/quiz"
mkdir -p "$QUIZ_DIR"
echo "[2/5] Directory created: $QUIZ_DIR"

# 3. Copy files (already uploaded via scp)
if [ -f /tmp/quiz-files/index.php ]; then
    cp /tmp/quiz-files/*.php "$QUIZ_DIR/"
    echo "[3/5] Files copied"
else
    echo "[3/5] Files already in place (skipping copy)"
fi

# Set permissions
chown -R www-data:www-data "$QUIZ_DIR"
chmod -R 755 "$QUIZ_DIR"

# 4. Create database
echo "[4/5] Setting up database..."
mysql -u root <<EOSQL
CREATE DATABASE IF NOT EXISTS maybeyou_quiz DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON maybeyou_quiz.* TO 'wordpress'@'localhost';
FLUSH PRIVILEGES;
EOSQL

# Create tables
mysql -u wordpress -p"$DB_PASS" maybeyou_quiz <<EOSQL
CREATE TABLE IF NOT EXISTS quiz_submissions (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id  VARCHAR(64)  NOT NULL,
    gender      VARCHAR(20)  NOT NULL,
    age         VARCHAR(20)  NOT NULL,
    goal        VARCHAR(20)  NOT NULL,
    diet        VARCHAR(20)  NOT NULL,
    gut         VARCHAR(20)  NOT NULL,
    sleep_state VARCHAR(20)  NOT NULL,
    skin        VARCHAR(20)  NOT NULL,
    exercise    VARCHAR(20)  NOT NULL,
    result_code VARCHAR(10)  NOT NULL,
    result_name VARCHAR(100) NOT NULL,
    ip_address  VARCHAR(45)  DEFAULT NULL,
    user_agent  TEXT         DEFAULT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_result (result_code),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_daily_stats (
    date        DATE         PRIMARY KEY,
    total_count INT UNSIGNED NOT NULL DEFAULT 0,
    top_result  VARCHAR(10)  DEFAULT NULL,
    updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
EOSQL

echo "    Database & tables created!"

# 5. Add quiz site to Caddy
CADDY_FILE="/etc/caddy/Caddyfile"
if ! grep -q "quiz.maybeyou.cc" "$CADDY_FILE" 2>/dev/null; then
    cat >> "$CADDY_FILE" <<'CADDY'

quiz.maybeyou.cc {
    root * /var/www/quiz
    php_fastcgi unix//run/php/php-fpm.sock
    file_server
    encode gzip

    # Security headers
    header {
        X-Content-Type-Options nosniff
        X-Frame-Options SAMEORIGIN
        Referrer-Policy strict-origin-when-cross-origin
    }

    # Block install.php
    @blocked path /install.php
    respond @blocked 403
}
CADDY
    echo "[5/5] Caddy config added for quiz.maybeyou.cc"

    # Check if php-fpm sock exists, try TCP fallback
    if [ ! -S /run/php/php-fpm.sock ]; then
        # Replace unix sock with TCP
        sed -i 's|php_fastcgi unix//run/php/php-fpm.sock|php_fastcgi 127.0.0.1:9000|' "$CADDY_FILE"
        echo "    (Using TCP php-fpm instead of socket)"
    fi

    # Reload Caddy
    systemctl reload caddy 2>/dev/null || caddy reload --config "$CADDY_FILE" 2>/dev/null || true
    echo "    Caddy reloaded!"
else
    echo "[5/5] Caddy config already exists (skipping)"
fi

# Remove deploy script and install.php
rm -f "$QUIZ_DIR/install.php"
rm -f /tmp/deploy.sh

echo ""
echo "=============================="
echo " DEPLOY COMPLETE!"
echo "=============================="
echo ""
echo " Quiz:  https://quiz.maybeyou.cc"
echo " Admin: https://quiz.maybeyou.cc/admin.php?token=maybeyou2026"
echo ""
echo " NEXT STEP: Add DNS record"
echo "   quiz.maybeyou.cc → A → 157.245.202.133"
echo ""
