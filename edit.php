<?php
require_once __DIR__ . '/api/db.php';

$bookingId = $_GET['id'] ?? $_POST['booking_id'] ?? '';

if (!$bookingId) {
    header('Location: ./submit.php');
    exit;
}

// Handle POST Form Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    try {
        $pdo->beginTransaction();

        $targetDate = $_POST['target_date'] ?? '';
        $arrivalDate = $_POST['arrival_date'] ?? '';

        // 1. Update bookings
        $stmtB = $pdo->prepare("UPDATE bookings SET target_date = :target_date, arrival_date = :arrival_date WHERE booking_id = :booking_id AND deleted_at IS NULL");
        $stmtB->execute([
            'target_date' => $targetDate,
            'arrival_date' => $arrivalDate,
            'booking_id' => $bookingId
        ]);

        // 2. Update ketua (termasuk province_id dan district_id)
        $stmtK = $pdo->prepare("
            UPDATE ketua SET 
                nama = :nama,
                identity_no = :identity_no,
                hp = :hp,
                birthdate = :birthdate,
                address = :address,
                province_id = :province_id,
                district_id = :district_id,
                gender_id = :gender_id,
                identity_type_id = :identity_type_id,
                payment_method = :payment_method,
                tracking_hp = :tracking_hp,
                tracking_pin = :tracking_pin
            WHERE booking_id = :booking_id
        ");
        $stmtK->execute([
            'nama' => $_POST['ketua_nama'] ?? '',
            'identity_no' => $_POST['ketua_nik'] ?? '',
            'hp' => $_POST['ketua_hp'] ?? '',
            'birthdate' => $_POST['ketua_birthdate'] ?? '1990-01-01',
            'address' => $_POST['ketua_address'] ?? '',
            'province_id' => $_POST['province_id'] ?? '',
            'district_id' => $_POST['district_id'] ?? '',
            'gender_id' => intval($_POST['ketua_gender'] ?? 1),
            'identity_type_id' => intval($_POST['ketua_identity_type'] ?? 1),
            'payment_method' => $_POST['payment_method'] ?? 'qris',
            'tracking_hp' => $_POST['tracking_hp'] ?? '',
            'tracking_pin' => $_POST['tracking_pin'] ?? '',
            'booking_id' => $bookingId
        ]);

        // 3. Update members
        $pdo->prepare("DELETE FROM members WHERE booking_id = :booking_id")->execute(['booking_id' => $bookingId]);

        $membersData = $_POST['members'] ?? [];
        $stmtM = $pdo->prepare("
            INSERT INTO members (booking_id, member_index, nama, birthdate, identity_type_id, identity_no, gender_id, hp, address, job_id, family_hp, country_id, doctor_letter)
            VALUES (:booking_id, :member_index, :nama, :birthdate, :identity_type_id, :identity_no, :gender_id, :hp, :address, :job_id, :family_hp, :country_id, 0)
        ");

        foreach ($membersData as $idx => $m) {
            $stmtM->execute([
                'booking_id' => $bookingId,
                'member_index' => $idx + 1,
                'nama' => $m['nama'] ?? '',
                'birthdate' => !empty($m['birthdate']) ? $m['birthdate'] : '1995-01-01',
                'identity_type_id' => intval($m['identity_type'] ?? 1),
                'identity_no' => $m['identity_no'] ?? '',
                'gender_id' => intval($m['gender'] ?? 1),
                'hp' => $m['hp'] ?? '',
                'address' => $m['address'] ?? '',
                'job_id' => intval($m['job_id'] ?? 2),
                'family_hp' => $m['family_hp'] ?? '',
                'country_id' => $m['country_id'] ?? '99'
            ]);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Data booking berhasil diperbarui!', 'booking_id' => $bookingId]);
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data: ' . $e->getMessage()]);
        exit;
    }
}

// Fetch booking data for GET request
$stmtB = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = :booking_id AND deleted_at IS NULL");
$stmtB->execute(['booking_id' => $bookingId]);
$booking = $stmtB->fetch();

if (!$booking) {
    header('Location: ./submit.php');
    exit;
}

$stmtK = $pdo->prepare("SELECT * FROM ketua WHERE booking_id = :booking_id");
$stmtK->execute(['booking_id' => $bookingId]);
$ketua = $stmtK->fetch();

$stmtM = $pdo->prepare("SELECT * FROM members WHERE booking_id = :booking_id ORDER BY member_index ASC");
$stmtM->execute(['booking_id' => $bookingId]);
$members = $stmtM->fetchAll();

$savedProv = $ketua['province_id'] ?? '';
$savedDist = $ketua['district_id'] ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - <?= htmlspecialchars($bookingId) ?> | WARTIK</title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-slate-100 min-h-screen py-8 px-4 sm:px-6 lg:px-8 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-fixed">

    <div class="max-w-5xl mx-auto bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-white overflow-hidden backdrop-blur-sm">

        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-600 to-amber-700 px-8 py-8 relative overflow-hidden flex items-center justify-between text-white">
            <div>
                <a href="./submit.php" class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-200 hover:text-white mb-2 transition-colors">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar List
                </a>
                <h1 class="text-2xl font-bold">Edit Pendaftaran Booking</h1>
                <p class="text-amber-100 text-sm font-mono mt-1">Kode: <?= htmlspecialchars($bookingId) ?></p>
            </div>
            <div class="hidden sm:flex w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md items-center justify-center text-2xl">
                <i class="fas fa-pen"></i>
            </div>
        </div>

        <form id="editForm" class="p-8 sm:p-10 space-y-10">
            <input type="hidden" name="booking_id" value="<?= htmlspecialchars($bookingId) ?>">

            <!-- SECTION: Tanggal -->
            <section>
                <h2 class="form-section-title">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i class="fas fa-calendar-alt"></i></div>
                    Jadwal Kunjungan
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label-premium required-star">Tanggal Berangkat</label>
                        <input type="text" name="target_date" id="target_date" value="<?= htmlspecialchars($booking['target_date']) ?>" class="form-input-premium datepicker">
                    </div>
                    <div>
                        <label class="form-label-premium required-star">Tanggal Pulang</label>
                        <input type="text" name="arrival_date" id="arrival_date" value="<?= htmlspecialchars($booking['arrival_date']) ?>" class="form-input-premium datepicker">
                    </div>
                </div>
            </section>

            <!-- SECTION: Data Ketua -->
            <section>
                <h2 class="form-section-title">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center"><i class="fas fa-user-shield"></i></div>
                    Data Ketua Kelompok
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="form-label-premium required-star">Nama Lengkap</label>
                        <input type="text" name="ketua_nama" value="<?= htmlspecialchars($ketua['nama'] ?? '') ?>" class="form-input-premium" required>
                    </div>

                    <div>
                        <label class="form-label-premium required-star">Jenis Identitas</label>
                        <select name="ketua_identity_type" class="form-input-premium">
                            <option value="1" <?= ($ketua['identity_type_id'] ?? 1) == 1 ? 'selected' : '' ?>>KTP</option>
                            <option value="2" <?= ($ketua['identity_type_id'] ?? 1) == 2 ? 'selected' : '' ?>>SIM</option>
                            <option value="3" <?= ($ketua['identity_type_id'] ?? 1) == 3 ? 'selected' : '' ?>>Passport</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-premium required-star">Nomor Identitas (NIK)</label>
                        <input type="text" name="ketua_nik" value="<?= htmlspecialchars($ketua['identity_no'] ?? '') ?>" class="form-input-premium" required>
                    </div>

                    <div>
                        <label class="form-label-premium required-star">Jenis Kelamin</label>
                        <select name="ketua_gender" class="form-input-premium">
                            <option value="1" <?= ($ketua['gender_id'] ?? 1) == 1 ? 'selected' : '' ?>>Laki-Laki</option>
                            <option value="2" <?= ($ketua['gender_id'] ?? 1) == 2 ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-premium required-star">Tanggal Lahir</label>
                        <input type="text" name="ketua_birthdate" value="<?= htmlspecialchars($ketua['birthdate'] ?? '') ?>" class="form-input-premium datepicker">
                    </div>

                    <div>
                        <label class="form-label-premium required-star">No HP / WA</label>
                        <input type="text" name="ketua_hp" value="<?= htmlspecialchars($ketua['hp'] ?? '') ?>" class="form-input-premium" required>
                    </div>

                    <div>
                        <label class="form-label-premium required-star">Metode Pembayaran</label>
                        <select name="payment_method" class="form-input-premium">
                            <option value="qris" <?= ($ketua['payment_method'] ?? '') === 'qris' ? 'selected' : '' ?>>QRIS</option>
                            <option value="VA-Mandiri" <?= ($ketua['payment_method'] ?? '') === 'VA-Mandiri' ? 'selected' : '' ?>>Virtual Account Mandiri</option>
                            <option value="VA-BNI" <?= ($ketua['payment_method'] ?? '') === 'VA-BNI' ? 'selected' : '' ?>>Virtual Account BNI</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label-premium required-star">Alamat Lengkap</label>
                        <input type="text" name="ketua_address" value="<?= htmlspecialchars($ketua['address'] ?? '') ?>" class="form-input-premium">
                    </div>

                    <!-- PROVINSI DAN KOTA/KABUPATEN SELECTION -->
                    <div>
                        <label class="form-label-premium required-star">Provinsi</label>
                        <select name="province_id" id="province_id" class="form-input-premium">
                            <option value="">- Pilih Provinsi -</option>
                            <option value="11" <?= $savedProv == '11' ? 'selected' : '' ?>>ACEH</option>
                            <option value="51" <?= $savedProv == '51' ? 'selected' : '' ?>>BALI</option>
                            <option value="36" <?= $savedProv == '36' ? 'selected' : '' ?>>BANTEN</option>
                            <option value="17" <?= $savedProv == '17' ? 'selected' : '' ?>>BENGKULU</option>
                            <option value="34" <?= $savedProv == '34' ? 'selected' : '' ?>>DI YOGYAKARTA</option>
                            <option value="31" <?= $savedProv == '31' ? 'selected' : '' ?>>DKI JAKARTA</option>
                            <option value="75" <?= $savedProv == '75' ? 'selected' : '' ?>>GORONTALO</option>
                            <option value="15" <?= $savedProv == '15' ? 'selected' : '' ?>>JAMBI</option>
                            <option value="32" <?= $savedProv == '32' ? 'selected' : '' ?>>JAWA BARAT</option>
                            <option value="33" <?= $savedProv == '33' ? 'selected' : '' ?>>JAWA TENGAH</option>
                            <option value="35" <?= $savedProv == '35' ? 'selected' : '' ?>>JAWA TIMUR</option>
                            <option value="61" <?= $savedProv == '61' ? 'selected' : '' ?>>KALIMANTAN BARAT</option>
                            <option value="63" <?= $savedProv == '63' ? 'selected' : '' ?>>KALIMANTAN SELATAN</option>
                            <option value="62" <?= $savedProv == '62' ? 'selected' : '' ?>>KALIMANTAN TENGAH</option>
                            <option value="64" <?= $savedProv == '64' ? 'selected' : '' ?>>KALIMANTAN TIMUR</option>
                            <option value="65" <?= $savedProv == '65' ? 'selected' : '' ?>>KALIMANTAN UTARA</option>
                            <option value="19" <?= $savedProv == '19' ? 'selected' : '' ?>>KEPULAUAN BANGKA BELITUNG</option>
                            <option value="21" <?= $savedProv == '21' ? 'selected' : '' ?>>KEPULAUAN RIAU</option>
                            <option value="18" <?= $savedProv == '18' ? 'selected' : '' ?>>LAMPUNG</option>
                            <option value="81" <?= $savedProv == '81' ? 'selected' : '' ?>>MALUKU</option>
                            <option value="82" <?= $savedProv == '82' ? 'selected' : '' ?>>MALUKU UTARA</option>
                            <option value="52" <?= $savedProv == '52' ? 'selected' : '' ?>>NUSA TENGGARA BARAT</option>
                            <option value="53" <?= $savedProv == '53' ? 'selected' : '' ?>>NUSA TENGGARA TIMUR</option>
                            <option value="94" <?= $savedProv == '94' ? 'selected' : '' ?>>PAPUA</option>
                            <option value="91" <?= $savedProv == '91' ? 'selected' : '' ?>>PAPUA BARAT</option>
                            <option value="14" <?= $savedProv == '14' ? 'selected' : '' ?>>RIAU</option>
                            <option value="76" <?= $savedProv == '76' ? 'selected' : '' ?>>SULAWESI BARAT</option>
                            <option value="73" <?= $savedProv == '73' ? 'selected' : '' ?>>SULAWESI SELATAN</option>
                            <option value="72" <?= $savedProv == '72' ? 'selected' : '' ?>>SULAWESI TENGAH</option>
                            <option value="74" <?= $savedProv == '74' ? 'selected' : '' ?>>SULAWESI TENGGARA</option>
                            <option value="71" <?= $savedProv == '71' ? 'selected' : '' ?>>SULAWESI UTARA</option>
                            <option value="13" <?= $savedProv == '13' ? 'selected' : '' ?>>SUMATERA BARAT</option>
                            <option value="16" <?= $savedProv == '16' ? 'selected' : '' ?>>SUMATERA SELATAN</option>
                            <option value="12" <?= $savedProv == '12' ? 'selected' : '' ?>>SUMATERA UTARA</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label-premium required-star">Kota/Kabupaten</label>
                        <select name="district_id" id="district_id" class="form-input-premium disabled:bg-slate-100 disabled:text-slate-400">
                            <option value="">- Pilih Provinsi Dulu -</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label-premium">No HP Tracking</label>
                        <input type="text" name="tracking_hp" value="<?= htmlspecialchars($ketua['tracking_hp'] ?? '') ?>" class="form-input-premium">
                    </div>
                    <div>
                        <label class="form-label-premium">PIN Tracking</label>
                        <input type="text" name="tracking_pin" value="<?= htmlspecialchars($ketua['tracking_pin'] ?? '') ?>" class="form-input-premium" maxlength="6">
                    </div>
                </div>
            </section>

            <!-- SECTION: Data Anggota -->
            <section>
                <div class="flex items-center justify-between mb-6 pb-3 border-b-2 border-slate-100">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                            <i class="fas fa-users"></i>
                        </div>
                        Data Anggota Pendaki
                    </h2>
                    <span class="bg-slate-100 text-slate-600 text-sm font-semibold px-3 py-1 rounded-full"><span id="memberCounter"><?= count($members) ?></span> / 9 Anggota</span>
                </div>

                <div id="membersContainer" class="space-y-6">
                    <?php foreach ($members as $idx => $m): ?>
                        <div class="member-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative group" data-member-index="<?= $idx ?>">
                            <div class="absolute top-4 right-4 z-10">
                                <button type="button" class="remove-member-btn text-rose-500 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1">
                                    <i class="fas fa-trash-alt"></i> <span class="hidden sm:inline">Hapus</span>
                                </button>
                            </div>

                            <div class="flex items-center gap-3 mb-6 pr-20">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm">
                                    <?= $idx + 1 ?>
                                </div>
                                <h3 class="font-bold text-slate-700">Data Anggota</h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="form-label-premium">Nama Lengkap</label>
                                    <input type="text" name="members[<?= $idx ?>][nama]" value="<?= htmlspecialchars($m['nama']) ?>" class="form-input-premium" required>
                                </div>
                                <div>
                                    <label class="form-label-premium">Tanggal Lahir</label>
                                    <input type="text" name="members[<?= $idx ?>][birthdate]" value="<?= htmlspecialchars($m['birthdate']) ?>" class="form-input-premium datepicker">
                                </div>
                                <div>
                                    <label class="form-label-premium">Jenis Identitas</label>
                                    <select name="members[<?= $idx ?>][identity_type]" class="form-input-premium">
                                        <option value="1" <?= $m['identity_type_id'] == 1 ? 'selected' : '' ?>>KTP</option>
                                        <option value="2" <?= $m['identity_type_id'] == 2 ? 'selected' : '' ?>>SIM</option>
                                        <option value="3" <?= $m['identity_type_id'] == 3 ? 'selected' : '' ?>>Passport</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label-premium">Nomor Identitas (NIK)</label>
                                    <input type="text" name="members[<?= $idx ?>][identity_no]" value="<?= htmlspecialchars($m['identity_no']) ?>" class="form-input-premium" required>
                                </div>
                                <div>
                                    <label class="form-label-premium">Jenis Kelamin</label>
                                    <select name="members[<?= $idx ?>][gender]" class="form-input-premium">
                                        <option value="1" <?= $m['gender_id'] == 1 ? 'selected' : '' ?>>Laki-Laki</option>
                                        <option value="2" <?= $m['gender_id'] == 2 ? 'selected' : '' ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label-premium">No HP</label>
                                    <input type="text" name="members[<?= $idx ?>][hp]" value="<?= htmlspecialchars($m['hp']) ?>" class="form-input-premium">
                                </div>
                                <div class="md:col-span-2">
                                    <label class="form-label-premium">Alamat</label>
                                    <input type="text" name="members[<?= $idx ?>][address]" value="<?= htmlspecialchars($m['address']) ?>" class="form-input-premium">
                                </div>
                                <div>
                                    <label class="form-label-premium">Pekerjaan</label>
                                    <select name="members[<?= $idx ?>][job_id]" class="form-input-premium">
                                        <option value="1" <?= $m['job_id'] == 1 ? 'selected' : '' ?>>PNS</option>
                                        <option value="2" <?= $m['job_id'] == 2 ? 'selected' : '' ?>>Swasta</option>
                                        <option value="5" <?= $m['job_id'] == 5 ? 'selected' : '' ?>>Pelajar</option>
                                        <option value="6" <?= $m['job_id'] == 6 ? 'selected' : '' ?>>Mahasiswa</option>
                                        <option value="3" <?= $m['job_id'] == 3 ? 'selected' : '' ?>>Lainnya</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label-premium">No HP Keluarga</label>
                                    <input type="text" name="members[<?= $idx ?>][family_hp]" value="<?= htmlspecialchars($m['family_hp']) ?>" class="form-input-premium">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="mt-6 flex flex-col sm:flex-row items-center gap-4">
                    <button type="button" id="addMemberBtn" class="w-full sm:w-auto bg-white text-brand-600 font-semibold py-3 px-6 rounded-xl border-2 border-brand-100 hover:bg-brand-50 hover:border-brand-200 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-plus-circle"></i> Tambah Anggota
                    </button>
                </div>
            </section>

            <!-- Submit Section -->
            <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-4">
                <a href="./submit.php" class="bg-white border border-slate-300 text-slate-700 font-bold py-3.5 px-6 rounded-xl hover:bg-slate-50 transition-all">Batal</a>
                <button type="submit" id="saveBtn" class="bg-brand-500 hover:bg-brand-600 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg hover:shadow-brand-500/30 transition-all flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </div>
        </form>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Regency Dictionary Fallback
        const regencyMap = {
            "35": [
                { id: "3507", name: "KAB. MALANG" },
                { id: "3573", name: "KOTA MALANG" },
                { id: "3579", name: "KOTA BATU" },
                { id: "3515", name: "KAB. SIDOARJO" },
                { id: "3578", name: "KOTA SURABAYA" },
                { id: "3514", name: "KAB. PASURUAN" },
                { id: "3575", name: "KOTA PASURUAN" },
                { id: "3513", name: "KAB. PROBOLINGGO" },
                { id: "3574", name: "KOTA PROBOLINGGO" },
                { id: "3508", name: "KAB. LUMAJANG" },
                { id: "3509", name: "KAB. JEMBER" },
                { id: "3510", name: "KAB. BANYUWANGI" },
                { id: "3511", name: "KAB. BONDOWOSO" },
                { id: "3512", name: "KAB. SITUBONDO" },
                { id: "3516", name: "KAB. MOJOKERTO" },
                { id: "3576", name: "KOTA MOJOKERTO" },
                { id: "3525", name: "KAB. GRESIK" },
                { id: "3524", name: "KAB. LAMONGAN" },
                { id: "3522", name: "KAB. BOJONEGORO" },
                { id: "3523", name: "KAB. TUBAN" },
                { id: "3517", name: "KAB. JOMBANG" },
                { id: "3518", name: "KAB. NGANJUK" },
                { id: "3519", name: "KAB. MADIUN" },
                { id: "3577", name: "KOTA MADIUN" },
                { id: "3520", name: "KAB. MAGETAN" },
                { id: "3521", name: "KAB. NGAWI" },
                { id: "3502", name: "KAB. PONOROGO" },
                { id: "3501", name: "KAB. PACITAN" },
                { id: "3503", name: "KAB. TRENGGALEK" },
                { id: "3504", name: "KAB. TULUNGAGUNG" },
                { id: "3505", name: "KAB. BLITAR" },
                { id: "3572", name: "KOTA BLITAR" },
                { id: "3506", name: "KAB. KEDIRI" },
                { id: "3571", name: "KOTA KEDIRI" }
            ],
            "31": [
                { id: "3171", name: "KOTA ADM. JAKARTA SELATAN" },
                { id: "3172", name: "KOTA ADM. JAKARTA TIMUR" },
                { id: "3173", name: "KOTA ADM. JAKARTA PUSAT" },
                { id: "3174", name: "KOTA ADM. JAKARTA BARAT" },
                { id: "3175", name: "KOTA ADM. JAKARTA UTARA" }
            ],
            "32": [
                { id: "3273", name: "KOTA BANDUNG" },
                { id: "3204", name: "KAB. BANDUNG" },
                { id: "3217", name: "KAB. BANDUNG BARAT" },
                { id: "3275", name: "KOTA BEKASI" },
                { id: "3216", name: "KAB. BEKASI" },
                { id: "3271", name: "KOTA BOGOR" },
                { id: "3201", name: "KAB. BOGOR" },
                { id: "3276", name: "KOTA DEPOK" },
                { id: "3277", name: "KOTA CIMAHI" }
            ],
            "33": [
                { id: "3374", name: "KOTA SEMARANG" },
                { id: "3322", name: "KAB. SEMARANG" },
                { id: "3372", name: "KOTA SURAKARTA (SOLO)" },
                { id: "3310", name: "KAB. KLATEN" },
                { id: "3311", name: "KAB. SUKOHARJO" },
                { id: "3313", name: "KAB. KARANGANYAR" },
                { id: "3302", name: "KAB. BANYUMAS (PURWOKERTO)" }
            ],
            "34": [
                { id: "3471", name: "KOTA YOGYAKARTA" },
                { id: "3404", name: "KAB. SLEMAN" },
                { id: "3402", name: "KAB. BANTUL" },
                { id: "3401", name: "KAB. KULON PROGO" },
                { id: "3403", name: "KAB. GUNUNGKIDUL" }
            ]
        };

        const savedProvId = "<?= htmlspecialchars($savedProv) ?>";
        const savedDistId = "<?= htmlspecialchars($savedDist) ?>";

        document.addEventListener('DOMContentLoaded', function () {
            flatpickr('.datepicker', { dateFormat: "Y-m-d", disableMobile: "true" });

            const provSelect = document.getElementById('province_id');
            const distSelect = document.getElementById('district_id');

            async function loadRegencies(provId, targetDistId = '') {
                distSelect.innerHTML = '<option value="">- Memuat Data Kota/Kab... -</option>';
                distSelect.disabled = true;

                if (!provId) {
                    distSelect.innerHTML = '<option value="">- Pilih Provinsi Dulu -</option>';
                    return;
                }

                let regencies = [];

                try {
                    const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/regencies/${provId}.json`);
                    if (res.ok) {
                        const data = await res.json();
                        regencies = data.map(r => ({ id: r.id, name: r.name.toUpperCase() }));
                    }
                } catch (e) {}

                if (regencies.length === 0 && regencyMap[provId]) {
                    regencies = regencyMap[provId];
                }

                if (regencies.length > 0) {
                    distSelect.innerHTML = '<option value="">- Pilih Kota/Kabupaten -</option>';
                    regencies.forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r.id;
                        opt.textContent = r.name;
                        if (r.id === targetDistId) opt.selected = true;
                        distSelect.appendChild(opt);
                    });
                    distSelect.disabled = false;
                } else {
                    distSelect.innerHTML = '<option value="">- Pilih Kota/Kabupaten -</option>';
                    const defaultOpts = [
                        { id: provId + "01", name: `KAB. WILAYAH (${provId})` },
                        { id: provId + "71", name: `KOTA UTAMA (${provId})` }
                    ];
                    defaultOpts.forEach(r => {
                        const opt = document.createElement('option');
                        opt.value = r.id;
                        opt.textContent = r.name;
                        if (r.id === targetDistId) opt.selected = true;
                        distSelect.appendChild(opt);
                    });
                    distSelect.disabled = false;
                }
            }

            provSelect.addEventListener('change', function() {
                loadRegencies(this.value);
            });

            // Initial load for saved province & regency
            if (savedProvId) {
                loadRegencies(savedProvId, savedDistId);
            }

            let memberCount = document.querySelectorAll('.member-card').length;
            const maxMembers = 9;
            const membersContainer = document.getElementById('membersContainer');
            const addMemberBtn = document.getElementById('addMemberBtn');
            const memberCounterEl = document.getElementById('memberCounter');

            function updateMemberCounter() {
                memberCounterEl.textContent = memberCount;
            }

            function getMemberTemplate(idx) {
                return `
                    <div class="member-card bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative group" data-member-index="${idx}">
                        <div class="absolute top-4 right-4 z-10">
                            <button type="button" class="remove-member-btn text-rose-500 bg-rose-50 hover:bg-rose-100 px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1">
                                <i class="fas fa-trash-alt"></i> <span class="hidden sm:inline">Hapus</span>
                            </button>
                        </div>
                        <div class="flex items-center gap-3 mb-6 pr-20">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm">${idx + 1}</div>
                            <h3 class="font-bold text-slate-700">Data Anggota Baru</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="form-label-premium">Nama Lengkap</label>
                                <input type="text" name="members[${idx}][nama]" class="form-input-premium" required>
                            </div>
                            <div>
                                <label class="form-label-premium">Tanggal Lahir</label>
                                <input type="text" name="members[${idx}][birthdate]" class="form-input-premium datepicker-new">
                            </div>
                            <div>
                                <label class="form-label-premium">Jenis Identitas</label>
                                <select name="members[${idx}][identity_type]" class="form-input-premium">
                                    <option value="1" selected>KTP</option>
                                    <option value="2">SIM</option>
                                    <option value="3">Passport</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-premium">Nomor Identitas (NIK)</label>
                                <input type="text" name="members[${idx}][identity_no]" class="form-input-premium" required>
                            </div>
                            <div>
                                <label class="form-label-premium">Jenis Kelamin</label>
                                <select name="members[${idx}][gender]" class="form-input-premium">
                                    <option value="1">Laki-Laki</option>
                                    <option value="2">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-premium">No HP</label>
                                <input type="text" name="members[${idx}][hp]" class="form-input-premium">
                            </div>
                            <div class="md:col-span-2">
                                <label class="form-label-premium">Alamat</label>
                                <input type="text" name="members[${idx}][address]" class="form-input-premium">
                            </div>
                            <div>
                                <label class="form-label-premium">Pekerjaan</label>
                                <select name="members[${idx}][job_id]" class="form-input-premium">
                                    <option value="1">PNS</option>
                                    <option value="2" selected>Swasta</option>
                                    <option value="5">Pelajar</option>
                                    <option value="6">Mahasiswa</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-premium">No HP Keluarga</label>
                                <input type="text" name="members[${idx}][family_hp]" class="form-input-premium">
                            </div>
                        </div>
                    </div>
                `;
            }

            addMemberBtn.addEventListener('click', () => {
                if (memberCount >= maxMembers) {
                    Swal.fire({ icon: 'warning', title: 'Maksimal 9 Anggota', text: 'Batas maksimal 9 anggota tambahan.' });
                    return;
                }
                membersContainer.insertAdjacentHTML('beforeend', getMemberTemplate(memberCount));
                flatpickr(membersContainer.lastElementChild.querySelector('.datepicker-new'), { dateFormat: "Y-m-d", disableMobile: "true" });
                memberCount++;
                updateMemberCounter();
            });

            membersContainer.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-member-btn');
                if (removeBtn) {
                    removeBtn.closest('.member-card').remove();
                    memberCount--;
                    updateMemberCounter();
                }
            });

            // Form Submit AJAX Handler
            document.getElementById('editForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                const saveBtn = document.getElementById('saveBtn');
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';

                try {
                    const formData = new FormData(this);
                    const response = await fetch('./edit.php', {
                        method: 'POST',
                        body: formData
                    });

                    const res = await response.json();
                    if (response.ok && res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil Diperbarui',
                            text: res.message,
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.location.href = `./detail.php?id=${encodeURIComponent(res.booking_id)}`;
                        });
                    } else {
                        throw new Error(res.message || 'Gagal menyimpan perubahan.');
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Kendala Sistem',
                        text: err.message,
                        confirmButtonColor: '#10b981'
                    });
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Simpan Perubahan';
                }
            });
        });
    </script>
</body>

</html>
