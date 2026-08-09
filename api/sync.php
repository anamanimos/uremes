<?php
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode request tidak diizinkan. Gunakan POST.']);
    exit;
}

$onlineUrl = trim($_POST['online_url'] ?? $_GET['online_url'] ?? '');
$secretKey = trim($_POST['secret'] ?? $_GET['secret'] ?? 'wartik2026');

if (!$onlineUrl) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'URL Website Online wajib diisi.']);
    exit;
}

// Format URL agar menunjuk ke api/export.php
$onlineUrl = rtrim($onlineUrl, '/');
if (!preg_match('/api\/export\.php$/i', $onlineUrl)) {
    $exportUrl = $onlineUrl . '/api/export.php';
} else {
    $exportUrl = $onlineUrl;
}

if (!empty($secretKey)) {
    $exportUrl .= (strpos($exportUrl, '?') !== false ? '&' : '?') . 'secret=' . urlencode($secretKey);
}

// Fetch data dari server online
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $exportUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'WARTIK-LocalSync/1.0');

$responseRaw = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($responseRaw === false || $httpCode !== 200) {
    http_response_code(502);
    echo json_encode([
        'success' => false,
        'message' => 'Gagal terhubung ke website online (' . $exportUrl . '). ' . ($curlErr ?: "HTTP Status Code: $httpCode")
    ]);
    exit;
}

$responseData = json_decode($responseRaw, true);

if (!$responseData || !isset($responseData['success']) || $responseData['success'] !== true) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Respon dari website online tidak valid: ' . ($responseData['message'] ?? 'Format JSON salah')
    ]);
    exit;
}

$onlineItems = $responseData['data'] ?? [];

if (empty($onlineItems)) {
    echo json_encode([
        'success' => true,
        'pulled_count' => 0,
        'message' => 'Server online terhubung, namun tidak ada data pendaftaran baru.'
    ]);
    exit;
}

// Prosess Sync Data ke Database Lokal
try {
    $pdo->beginTransaction();

    $syncedCount = 0;

    $stmtUpsertB = $pdo->prepare("
        INSERT INTO bookings (booking_id, target_date, arrival_date, status, created_at)
        VALUES (:b_id, :t_date, :a_date, :status, :created_at)
        ON DUPLICATE KEY UPDATE
            target_date = VALUES(target_date),
            arrival_date = VALUES(arrival_date),
            status = VALUES(status)
    ");

    $stmtUpsertK = $pdo->prepare("
        INSERT INTO ketua (booking_id, nama, hp, identity_no, birthdate, address, province_id, district_id, gender_id, identity_type_id, country_id, payment_method, tracking_hp, tracking_pin)
        VALUES (:b_id, :nama, :hp, :identity_no, :birthdate, :address, :province_id, :district_id, :gender_id, :identity_type_id, :country_id, :payment_method, :tracking_hp, :tracking_pin)
        ON DUPLICATE KEY UPDATE
            nama = VALUES(nama),
            hp = VALUES(hp),
            identity_no = VALUES(identity_no),
            birthdate = VALUES(birthdate),
            address = VALUES(address),
            province_id = VALUES(province_id),
            district_id = VALUES(district_id),
            gender_id = VALUES(gender_id),
            identity_type_id = VALUES(identity_type_id),
            country_id = VALUES(country_id),
            payment_method = VALUES(payment_method),
            tracking_hp = VALUES(tracking_hp),
            tracking_pin = VALUES(tracking_pin)
    ");

    $stmtDeleteM = $pdo->prepare("DELETE FROM members WHERE booking_id = :b_id");

    $stmtInsertM = $pdo->prepare("
        INSERT INTO members (booking_id, member_index, nama, birthdate, identity_type_id, identity_no, gender_id, hp, address, job_id, family_hp, country_id, doctor_letter)
        VALUES (:b_id, :member_index, :nama, :birthdate, :identity_type_id, :identity_no, :gender_id, :hp, :address, :job_id, :family_hp, :country_id, 0)
    ");

    foreach ($onlineItems as $item) {
        $b = $item['booking'];
        $k = $item['ketua'];
        $members = $item['members'] ?? [];

        if (!$b || !$b['booking_id']) continue;

        // 1. Upsert Bookings
        $stmtUpsertB->execute([
            'b_id' => $b['booking_id'],
            't_date' => $b['target_date'],
            'a_date' => $b['arrival_date'],
            'status' => $b['status'] ?? 'pending',
            'created_at' => $b['created_at'] ?? date('Y-m-d H:i:s')
        ]);

        // 2. Upsert Ketua
        if ($k) {
            $stmtUpsertK->execute([
                'b_id' => $b['booking_id'],
                'nama' => $k['nama'] ?? '',
                'hp' => $k['hp'] ?? '',
                'identity_no' => $k['identity_no'] ?? '',
                'birthdate' => $k['birthdate'] ?? '1990-01-01',
                'address' => $k['address'] ?? '',
                'province_id' => $k['province_id'] ?? '',
                'district_id' => $k['district_id'] ?? '',
                'gender_id' => intval($k['gender_id'] ?? 1),
                'identity_type_id' => intval($k['identity_type_id'] ?? 1),
                'country_id' => $k['country_id'] ?? '99',
                'payment_method' => $k['payment_method'] ?? 'qris',
                'tracking_hp' => $k['tracking_hp'] ?? '',
                'tracking_pin' => $k['tracking_pin'] ?? ''
            ]);
        }

        // 3. Delete & Insert Members
        $stmtDeleteM->execute(['b_id' => $b['booking_id']]);

        foreach ($members as $idx => $m) {
            $stmtInsertM->execute([
                'b_id' => $b['booking_id'],
                'member_index' => intval($m['member_index'] ?? ($idx + 1)),
                'nama' => $m['nama'] ?? '',
                'birthdate' => $m['birthdate'] ?? '1995-01-01',
                'identity_type_id' => intval($m['identity_type_id'] ?? 1),
                'identity_no' => $m['identity_no'] ?? '',
                'gender_id' => intval($m['gender_id'] ?? 1),
                'hp' => $m['hp'] ?? '',
                'address' => $m['address'] ?? '',
                'job_id' => intval($m['job_id'] ?? 2),
                'family_hp' => $m['family_hp'] ?? '',
                'country_id' => $m['country_id'] ?? '99'
            ]);
        }

        $syncedCount++;
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'pulled_count' => $syncedCount,
        'message' => "Berhasil menarik & mensinkronkan {$syncedCount} data pendaftaran dari server online ke database lokal!"
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan saat menyimpang data ke database lokal: ' . $e->getMessage()
    ]);
}
