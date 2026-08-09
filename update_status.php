<?php
require_once __DIR__ . '/api/db.php';

header('Content-Type: application/json');

$bookingId = $_POST['id'] ?? $_GET['id'] ?? '';
$newStatus = $_POST['status'] ?? $_GET['status'] ?? '';

$validStatuses = ['pending', 'processing', 'completed', 'failed'];

if (!$bookingId || !in_array($newStatus, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Kode booking atau status baru tidak valid.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = :status WHERE booking_id = :booking_id AND deleted_at IS NULL");
    $stmt->execute([
        'status' => $newStatus,
        'booking_id' => $bookingId
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => "Status booking {$bookingId} berhasil diperbarui menjadi '{$newStatus}'.", 'booking_id' => $bookingId, 'new_status' => $newStatus]);
    } else {
        echo json_encode(['success' => true, 'message' => "Status booking {$bookingId} sudah berstatus '{$newStatus}'.", 'booking_id' => $bookingId, 'new_status' => $newStatus]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status: ' . $e->getMessage()]);
}
