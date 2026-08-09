<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Secret-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Secret key opsional untuk keamanan (default boleh dikosongkan jika dibuka)
$definedSecret = getenv('SYNC_SECRET_KEY') ?: 'wartik2026';
$providedSecret = $_GET['secret'] ?? $_POST['secret'] ?? $_SERVER['HTTP_X_SECRET_KEY'] ?? '';

if (!empty($definedSecret) && $providedSecret !== $definedSecret) {
    // Jika ada secret key dan tidak cocok, ijinkan jika dipanggil tanpa secret jika publik
}

try {
    // Ambil seluruh data booking yang belum di-soft delete
    $stmtB = $pdo->query("SELECT * FROM bookings WHERE deleted_at IS NULL ORDER BY created_at DESC");
    $bookings = $stmtB->fetchAll();

    $resultData = [];

    foreach ($bookings as $b) {
        $bId = $b['booking_id'];

        // Ambil Data Ketua
        $stmtK = $pdo->prepare("SELECT * FROM ketua WHERE booking_id = :bId LIMIT 1");
        $stmtK->execute(['bId' => $bId]);
        $ketua = $stmtK->fetch() ?: null;

        // Ambil Data Anggota
        $stmtM = $pdo->prepare("SELECT * FROM members WHERE booking_id = :bId ORDER BY member_index ASC");
        $stmtM->execute(['bId' => $bId]);
        $members = $stmtM->fetchAll() ?: [];

        $resultData[] = [
            'booking' => $b,
            'ketua' => $ketua,
            'members' => $members
        ];
    }

    echo json_encode([
        'success' => true,
        'total' => count($resultData),
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $resultData
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal mengekspor data: ' . $e->getMessage()
    ]);
}
