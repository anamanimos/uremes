<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/db.php';

$OBF_KEY = 'WARTIK_SEMERU_SECRET_2026';

function deobfuscatePHP($encoded, $key) {
    if (empty($encoded) || empty($key)) return null;
    try {
        $binary = base64_decode($encoded);
        if ($binary === false) return null;
        
        $keyBytes = unpack('C*', $key);
        $keyLen = count($keyBytes);
        $bytes = unpack('C*', $binary);
        $len = count($bytes);
        
        $out = '';
        for ($i = 1; $i <= $len; $i++) {
            $k = $keyBytes[(($i - 1) % $keyLen) + 1];
            $out .= chr($bytes[$i] ^ $k);
        }
        
        return json_decode($out, true);
    } catch (Exception $e) {
        return null;
    }
}

// Handler GET - Mengambil daftar booking untuk bot/dashboard
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $status = $_GET['status'] ?? 'pending';
        $stmt = $pdo->prepare("SELECT b.*, k.nama as nama_ketua, k.hp as hp_ketua, k.identity_no as nik_ketua 
                               FROM bookings b 
                               LEFT JOIN ketua k ON b.booking_id = k.booking_id 
                               WHERE b.status = :status 
                               ORDER BY b.created_at DESC");
        $stmt->execute(['status' => $status]);
        $rows = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit;
    }
}

// Handler POST - Menyimpan booking baru dari form WARTIK
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $body = json_decode($rawInput, true);

    if (empty($body['_p'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Payload _p tidak ditemukan.']);
        exit;
    }

    $payload = deobfuscatePHP($body['_p'], $OBF_KEY);

    if (!$payload || empty($payload['t_dt']) || empty($payload['x_c'])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Data tidak dapat didekripsi atau format tidak valid.']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $bookingId = 'WRT-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $targetDate = $payload['t_dt'];
        $arrivalDate = $payload['a_dt'] ?? date('Y-m-d', strtotime($targetDate . ' +1 day'));

        // 1. Simpan ke tabel bookings
        $stmtB = $pdo->prepare("INSERT INTO bookings (booking_id, target_date, arrival_date, status) VALUES (:booking_id, :target_date, :arrival_date, 'pending')");
        $stmtB->execute([
            'booking_id' => $bookingId,
            'target_date' => $targetDate,
            'arrival_date' => $arrivalDate
        ]);

        // 2. Simpan Data Ketua
        $c = $payload['x_c'];
        $stmtK = $pdo->prepare("INSERT INTO ketua (booking_id, nama, hp, identity_no, birthdate, address, province_id, district_id, gender_id, identity_type_id, country_id, payment_method, tracking_hp, tracking_pin) 
                                VALUES (:booking_id, :nama, :hp, :identity_no, :birthdate, :address, :province_id, :district_id, :gender_id, :identity_type_id, :country_id, :payment_method, :tracking_hp, :tracking_pin)");
        $stmtK->execute([
            'booking_id' => $bookingId,
            'nama' => $c['n_m'] ?? '',
            'hp' => $c['h_p'] ?? '',
            'identity_no' => $c['i_n'] ?? '',
            'birthdate' => $c['b_d'] ?? '1995-01-01',
            'address' => $c['a_d'] ?? '',
            'province_id' => $c['p_r'] ?? '',
            'district_id' => $c['d_t'] ?? '',
            'gender_id' => intval($c['g_d'] ?? 1),
            'identity_type_id' => intval($c['i_t'] ?? 1),
            'country_id' => $c['c_r'] ?? '99',
            'payment_method' => $c['b_k'] ?? 'qris',
            'tracking_hp' => $c['t_hp'] ?? '',
            'tracking_pin' => $c['t_pn'] ?? ''
        ]);

        // 3. Simpan Data Anggota
        if (!empty($payload['x_m']) && is_array($payload['x_m'])) {
            $stmtM = $pdo->prepare("INSERT INTO members (booking_id, member_index, nama, birthdate, identity_type_id, identity_no, gender_id, hp, address, job_id, family_hp, country_id, doctor_letter) 
                                    VALUES (:booking_id, :member_index, :nama, :birthdate, :identity_type_id, :identity_no, :gender_id, :hp, :address, :job_id, :family_hp, :country_id, :doctor_letter)");
            
            foreach ($payload['x_m'] as $idx => $m) {
                $stmtM->execute([
                    'booking_id' => $bookingId,
                    'member_index' => $idx + 1,
                    'nama' => $m['m_nm'] ?? '',
                    'birthdate' => $m['m_bd'] ?? '2000-01-01',
                    'identity_type_id' => intval($m['m_it'] ?? 1),
                    'identity_no' => $m['m_in'] ?? '',
                    'gender_id' => intval($m['m_gd'] ?? 1),
                    'hp' => $m['m_hp'] ?? '',
                    'address' => $m['m_al'] ?? '',
                    'job_id' => intval($m['m_jb'] ?? 1),
                    'family_hp' => $m['m_hk'] ?? '',
                    'country_id' => $m['m_cr'] ?? '99',
                    'doctor_letter' => intval($m['m_as'] ?? 0)
                ]);
            }
        }

        $pdo->commit();

        echo json_encode([
            'success' => true,
            'booking_id' => $bookingId,
            'message' => 'Pendaftaran berhasil disimpan ke database auto.'
        ]);
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan ke database: ' . $e->getMessage()]);
        exit;
    }
}
