<?php
/**
 * Database installer - run once then delete this file
 * Usage: php install.php  OR  visit http://yoursite/quiz/install.php
 */
require_once __DIR__ . '/config.php';

try {
    // Connect without database first
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET,
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    // Quiz submissions table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `quiz_submissions` (
        `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `session_id`  VARCHAR(64)  NOT NULL,
        `gender`      VARCHAR(20)  NOT NULL,
        `age`         VARCHAR(20)  NOT NULL,
        `goal`        VARCHAR(20)  NOT NULL,
        `diet`        VARCHAR(20)  NOT NULL,
        `gut`         VARCHAR(20)  NOT NULL,
        `sleep_state` VARCHAR(20)  NOT NULL,
        `skin`        VARCHAR(20)  NOT NULL,
        `exercise`    VARCHAR(20)  NOT NULL,
        `result_code` VARCHAR(10)  NOT NULL,
        `result_name` VARCHAR(100) NOT NULL,
        `ip_address`  VARCHAR(45)  DEFAULT NULL,
        `user_agent`  TEXT         DEFAULT NULL,
        `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_result (`result_code`),
        INDEX idx_created (`created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Daily stats view (materialized as table, updated by cron or on-demand)
    $pdo->exec("CREATE TABLE IF NOT EXISTS `quiz_daily_stats` (
        `date`          DATE         PRIMARY KEY,
        `total_count`   INT UNSIGNED NOT NULL DEFAULT 0,
        `top_result`    VARCHAR(10)  DEFAULT NULL,
        `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    echo "<h1 style='color:green'>Database installed successfully!</h1>";
    echo "<p>Tables created in <code>" . DB_NAME . "</code>:</p>";
    echo "<ul><li>quiz_submissions</li><li>quiz_daily_stats</li></ul>";
    echo "<p style='color:red;font-weight:bold'>Please delete install.php after installation!</p>";
    echo "<p><a href='index.php'>Go to Quiz</a> | <a href='admin.php'>Go to Admin</a></p>";

} catch (PDOException $e) {
    echo "<h1 style='color:red'>Installation failed</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
