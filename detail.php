<?php
require_once __DIR__ . '/api/db.php';

$bookingId = $_GET['id'] ?? '';

if (!$bookingId) {
    header('Location: ./submit.php');
    exit;
}

// Fetch booking details
$stmtB = $pdo->prepare("SELECT * FROM bookings WHERE booking_id = :booking_id AND deleted_at IS NULL");
$stmtB->execute(['booking_id' => $bookingId]);
$booking = $stmtB->fetch();

if (!$booking) {
    echo "<h1>Data Booking Tidak Ditemukan atau Telah Dihapus</h1><p><a href='./submit.php'>Kembali ke Daftar</a></p>";
    exit;
}

// Fetch Ketua details
$stmtK = $pdo->prepare("SELECT * FROM ketua WHERE booking_id = :booking_id");
$stmtK->execute(['booking_id' => $bookingId]);
$ketua = $stmtK->fetch();

// Fetch Members details
$stmtM = $pdo->prepare("SELECT * FROM members WHERE booking_id = :booking_id ORDER BY member_index ASC");
$stmtM->execute(['booking_id' => $bookingId]);
$members = $stmtM->fetchAll();

// Dictionary nama provinsi dasar
$provNames = [
    "35" => "JAWA TIMUR",
    "31" => "DKI JAKARTA",
    "32" => "JAWA BARAT",
    "33" => "JAWA TENGAH",
    "34" => "DI YOGYAKARTA",
    "36" => "BANTEN",
    "51" => "BALI"
];

$provName = $provNames[$ketua['province_id'] ?? ''] ?? ($ketua['province_id'] ?: '-');
$distId = $ketua['district_id'] ?: '-';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking - <?= htmlspecialchars($bookingId) ?> | WARTIK</title>

    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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

    <div class="max-w-5xl mx-auto space-y-8">

        <!-- Top Nav & Actions -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <a href="./submit.php" class="bg-white hover:bg-slate-50 text-slate-700 font-semibold px-5 py-2.5 rounded-xl border border-slate-200 shadow-sm transition-all flex items-center gap-2 text-sm">
                <i class="fas fa-arrow-left"></i> Kembali ke List Pendaftaran
            </a>

            <div class="flex items-center gap-3">
                <a href="./edit.php?id=<?= urlencode($bookingId) ?>" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-pen"></i> Edit Data
                </a>
                <button type="button" onclick="confirmSoftDelete('<?= htmlspecialchars($bookingId) ?>')" class="bg-rose-500 hover:bg-rose-600 text-white font-semibold px-5 py-2.5 rounded-xl shadow-sm transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-trash-alt"></i> Soft Delete
                </button>
            </div>
        </div>

        <!-- Main Card Container -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-white overflow-hidden">
            
            <!-- Banner Header -->
            <div class="bg-slate-900 text-white p-8 relative overflow-hidden">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
                    <div>
                        <div class="text-xs uppercase font-bold tracking-widest text-brand-400 mb-1">Rincian Booking</div>
                        <h1 class="text-3xl font-bold font-mono"><?= htmlspecialchars($bookingId) ?></h1>
                        <p class="text-slate-400 text-sm mt-1">Didaftarkan pada: <?= date('d F Y, H:i', strtotime($booking['created_at'])) ?> WIB</p>
                    </div>

                    <div>
                        <?php if ($booking['status'] === 'pending'): ?>
                            <span class="bg-amber-500/20 text-amber-300 border border-amber-400/30 text-sm font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-2">
                                <i class="fas fa-clock"></i> Status Bot: Pending
                            </span>
                        <?php elseif ($booking['status'] === 'processing'): ?>
                            <span class="bg-indigo-500/20 text-indigo-300 border border-indigo-400/30 text-sm font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-2">
                                <i class="fas fa-spinner fa-spin"></i> Status Bot: Processing
                            </span>
                        <?php elseif ($booking['status'] === 'completed'): ?>
                            <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-sm font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-2">
                                <i class="fas fa-check-circle"></i> Status Bot: Completed
                            </span>
                        <?php else: ?>
                            <span class="bg-rose-500/20 text-rose-300 border border-rose-400/30 text-sm font-semibold px-4 py-2 rounded-xl inline-flex items-center gap-2">
                                <i class="fas fa-times-circle"></i> Status Bot: Failed
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="p-8 sm:p-10 space-y-10">

                <!-- SECTION: Jadwal -->
                <section class="bg-slate-50 rounded-2xl p-6 border border-slate-200/80">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-brand-500"></i> Jadwal Kunjungan
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase">Tanggal Keberangkatan</span>
                            <p class="text-lg font-bold text-slate-700 mt-1"><i class="fas fa-plane-departure text-brand-500 mr-2"></i><?= htmlspecialchars($booking['target_date']) ?></p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-slate-400 uppercase">Tanggal Kepulangan</span>
                            <p class="text-lg font-bold text-slate-700 mt-1"><i class="fas fa-plane-arrival text-brand-500 mr-2"></i><?= htmlspecialchars($booking['arrival_date']) ?></p>
                        </div>
                    </div>
                </section>

                <!-- SECTION: Data Ketua Kelompok -->
                <section>
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 pb-3 border-b border-slate-100">
                        <i class="fas fa-user-shield text-brand-500"></i> Data Ketua Kelompok
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-semibold uppercase">Nama Lengkap</span>
                            <p class="font-bold text-slate-800 text-base mt-1"><?= htmlspecialchars($ketua['nama'] ?? '-') ?></p>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-semibold uppercase">Nomor Identitas (NIK)</span>
                            <p class="font-mono font-semibold text-slate-800 text-base mt-1"><?= htmlspecialchars($ketua['identity_no'] ?? '-') ?></p>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-semibold uppercase">No HP / WhatsApp</span>
                            <p class="font-semibold text-slate-800 mt-1"><i class="fab fa-whatsapp text-emerald-500 mr-1"></i><?= htmlspecialchars($ketua['hp'] ?? '-') ?></p>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-semibold uppercase">Jenis Kelamin / Identitas</span>
                            <p class="font-semibold text-slate-800 mt-1">
                                <?= ($ketua['gender_id'] == 1 ? 'Laki-Laki' : 'Perempuan') ?> (<?= ($ketua['identity_type_id'] == 1 ? 'KTP' : 'SIM/Lainnya') ?>)
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-semibold uppercase">Provinsi</span>
                            <p class="font-semibold text-slate-800 mt-1"><?= htmlspecialchars($provName) ?></p>
                        </div>
                        <div class="bg-white p-4 rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-semibold uppercase">Kota / Kabupaten (ID)</span>
                            <p class="font-semibold text-slate-800 mt-1"><?= htmlspecialchars($distId) ?></p>
                        </div>
                        <div class="md:col-span-2 bg-white p-4 rounded-xl border border-slate-200">
                            <span class="text-xs text-slate-400 font-semibold uppercase">Alamat Lengkap</span>
                            <p class="font-medium text-slate-700 mt-1"><?= htmlspecialchars($ketua['address'] ?? '-') ?></p>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                            <span class="text-xs text-indigo-400 font-semibold uppercase">Pembayaran</span>
                            <p class="font-bold text-indigo-900 mt-1 uppercase"><?= htmlspecialchars($ketua['payment_method'] ?? 'QRIS') ?></p>
                        </div>
                        <div class="bg-indigo-50 p-4 rounded-xl border border-indigo-100">
                            <span class="text-xs text-indigo-400 font-semibold uppercase">Tracking HP / PIN</span>
                            <p class="font-mono font-semibold text-indigo-900 mt-1">HP: <?= htmlspecialchars($ketua['tracking_hp'] ?? '-') ?> | PIN: <?= htmlspecialchars($ketua['tracking_pin'] ?? '-') ?></p>
                        </div>
                    </div>
                </section>

                <!-- SECTION: Data Anggota -->
                <section>
                    <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-users text-purple-500"></i> Data Anggota Pendaki
                        </h2>
                        <span class="bg-purple-50 text-purple-700 text-xs font-bold px-3 py-1 rounded-full">
                            Total: <?= count($members) ?> Anggota (Rombongan <?= count($members) + 1 ?> Orang)
                        </span>
                    </div>

                    <?php if (count($members) === 0): ?>
                        <p class="text-slate-400 text-sm italic">Tidak ada data anggota tambahan.</p>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($members as $idx => $m): ?>
                                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-7 h-7 rounded-full bg-purple-600 text-white font-bold text-xs flex items-center justify-center">
                                            <?= $idx + 1 ?>
                                        </div>
                                        <h3 class="font-bold text-slate-800 text-base"><?= htmlspecialchars($m['nama']) ?></h3>
                                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full <?= $m['gender_id'] == 1 ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' ?>">
                                            <?= $m['gender_id'] == 1 ? 'Laki-Laki' : 'Perempuan' ?>
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
                                        <div>
                                            <span class="text-slate-400">NIK / Identitas:</span>
                                            <p class="font-mono font-semibold text-slate-700"><?= htmlspecialchars($m['identity_no']) ?></p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Tanggal Lahir:</span>
                                            <p class="font-medium text-slate-700"><?= htmlspecialchars($m['birthdate']) ?></p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">No HP:</span>
                                            <p class="font-medium text-slate-700"><?= htmlspecialchars($m['hp'] ?: '-') ?></p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Pekerjaan:</span>
                                            <p class="font-medium text-slate-700">
                                                <?php
                                                $jobs = [1 => 'PNS', 2 => 'Swasta', 5 => 'Pelajar', 6 => 'Mahasiswa', 3 => 'Lainnya'];
                                                echo $jobs[$m['job_id']] ?? 'Swasta';
                                                ?>
                                            </p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">No HP Keluarga:</span>
                                            <p class="font-medium text-slate-700"><?= htmlspecialchars($m['family_hp'] ?: '-') ?></p>
                                        </div>
                                        <div>
                                            <span class="text-slate-400">Alamat:</span>
                                            <p class="font-medium text-slate-700 truncate"><?= htmlspecialchars($m['address'] ?: '-') ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmSoftDelete(bookingId) {
            Swal.fire({
                title: 'Hapus Data Booking?',
                text: `Data ${bookingId} akan di-soft delete.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`./delete.php?id=${encodeURIComponent(bookingId)}`)
                        .then(res => res.json())
                        .then(res => {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Dihapus',
                                    text: 'Data booking berhasil di-soft delete.',
                                    confirmButtonColor: '#10b981'
                                }).then(() => {
                                    window.location.href = './submit.php';
                                });
                            } else {
                                throw new Error(res.message);
                            }
                        })
                        .catch(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Hapus',
                                text: err.message || 'Terjadi kesalahan.',
                                confirmButtonColor: '#10b981'
                            });
                        });
                }
            });
        }
    </script>
</body>

</html>
