<?php
require_once __DIR__ . '/db.php';
try {
    $pdo->exec("ALTER TABLE bookings ADD COLUMN organisasi VARCHAR(255) DEFAULT 'Pecinta Alam Semeru'");
    echo "SUCCESS: Column organisasi added.\n";
} catch (PDOException $e) {
    echo "NOTE: " . $e->getMessage() . "\n";
}
