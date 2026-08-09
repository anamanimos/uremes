<?php
require_once __DIR__ . '/api/db.php';

header('Content-Type: application/json');

$bookingId = $_GET['id'] ?? $_POST['id'] ?? '';

if (!$bookingId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kode booking tidak valid.']);
    exit;
}

try {
    // Soft delete: Set deleted_at = CURRENT_TIMESTAMP
    $stmt = $pdo->prepare("UPDATE bookings SET deleted_at = NOW() WHERE booking_id = :booking_id");
    $stmt->execute(['booking_id' => $bookingId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Data booking berhasil di-soft delete.', 'booking_id' => $bookingId]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Data booking tidak ditemukan atau sudah dihapus.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()]);
}
