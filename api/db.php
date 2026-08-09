<?php
// Connection to Laragon MySQL Database 'auto'
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'auto';

// Environment Flag Configuration: 'local' (lokal bot) atau 'production' (web online)
if (!defined('APP_ENV')) {
    define('APP_ENV', getenv('APP_ENV') ?: 'local'); // Ubah ke 'production' pada web online
}

if (!defined('IS_PRODUCTION')) {
    define('IS_PRODUCTION', APP_ENV === 'production');
}

// Hardcoded Domain Target Tarik Data (Website Online)
if (!defined('ONLINE_SYNC_URL')) {
    define('ONLINE_SYNC_URL', getenv('ONLINE_SYNC_URL') ?: 'https://semeru.nams.my.id');
}

if (!defined('ONLINE_SYNC_SECRET')) {
    define('ONLINE_SYNC_SECRET', getenv('ONLINE_SYNC_SECRET') ?: 'wartik2026');
}

try {
    // 1. Connect without dbname to ensure database exists
    $pdoInit = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    $pdoInit->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // 2. Connect to 'auto' database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 3. Auto Create Tables if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `bookings` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_id` VARCHAR(50) NOT NULL UNIQUE,
            `target_date` DATE NOT NULL,
            `arrival_date` DATE NOT NULL,
            `status` ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            `deleted_at` DATETIME DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `ketua` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_id` VARCHAR(50) NOT NULL,
            `nama` VARCHAR(255) NOT NULL,
            `hp` VARCHAR(50) NOT NULL,
            `identity_no` VARCHAR(50) NOT NULL,
            `birthdate` DATE NOT NULL,
            `address` VARCHAR(255) DEFAULT '',
            `province_id` VARCHAR(10) DEFAULT '',
            `district_id` VARCHAR(10) DEFAULT '',
            `gender_id` INT DEFAULT 1,
            `identity_type_id` INT DEFAULT 1,
            `country_id` VARCHAR(10) DEFAULT '99',
            `payment_method` VARCHAR(50) DEFAULT 'qris',
            `tracking_hp` VARCHAR(50) DEFAULT '',
            `tracking_pin` VARCHAR(10) DEFAULT '',
            FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `members` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `booking_id` VARCHAR(50) NOT NULL,
            `member_index` INT DEFAULT 1,
            `nama` VARCHAR(255) NOT NULL,
            `birthdate` DATE NOT NULL,
            `identity_type_id` INT DEFAULT 1,
            `identity_no` VARCHAR(50) NOT NULL,
            `gender_id` INT DEFAULT 1,
            `hp` VARCHAR(50) DEFAULT '',
            `address` VARCHAR(255) DEFAULT '',
            `job_id` INT DEFAULT 1,
            `family_hp` VARCHAR(50) DEFAULT '',
            `country_id` VARCHAR(10) DEFAULT '99',
            `doctor_letter` TINYINT(1) DEFAULT 0,
            FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // 4. Auto Migration: Add deleted_at column if not exists
    $columns = $pdo->query("SHOW COLUMNS FROM `bookings` LIKE 'deleted_at'")->fetchAll();
    if (count($columns) === 0) {
        $pdo->exec("ALTER TABLE `bookings` ADD COLUMN `deleted_at` DATETIME DEFAULT NULL AFTER `status`");
    }

} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Koneksi Database Gagal: ' . $e->getMessage()]);
    exit;
}
