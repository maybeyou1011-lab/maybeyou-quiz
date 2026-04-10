<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'maybeyou_quiz');
define('DB_USER', 'wordpress');
// Auto-read password from DO WordPress config
$_wp_db_pass = '';
if (file_exists('/root/.digitalocean_password')) {
    $_lines = file('/root/.digitalocean_password', FILE_IGNORE_NEW_LINES);
    foreach ($_lines as $_l) {
        if (stripos($_l, 'wordpress_mysql_pass') !== false && strpos($_l, '"') !== false) {
            $_wp_db_pass = trim(explode('"', $_l)[1]);
            break;
        }
    }
}
define('DB_PASS', $_wp_db_pass);
define('DB_CHARSET', 'utf8mb4');

// Admin password (change this!)
define('ADMIN_PASS', 'maybeyou2026');

// Site
define('SITE_NAME', 'MaybeYou 每日營養搭配測驗');

// PDO connection
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}
