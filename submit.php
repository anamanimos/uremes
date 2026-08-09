<?php
require_once __DIR__ . '/api/db.php';

// Fetch active bookings (soft delete filtered)
$query = "
    SELECT 
        b.id,
        b.booking_id,
        b.target_date,
        b.arrival_date,
        b.status,
        b.created_at,
        k.nama AS nama_ketua,
        k.hp AS hp_ketua,
        k.payment_method,
        (SELECT COUNT(*) FROM members m WHERE m.booking_id = b.booking_id) AS total_anggota
    FROM bookings b
    LEFT JOIN ketua k ON k.booking_id = b.booking_id
    WHERE b.deleted_at IS NULL
    ORDER BY b.created_at DESC
";

$stmt = $pdo->query($query);
$bookings = $stmt->fetchAll();

// Statistics
$totalCount = count($bookings);
$pendingCount = 0;
$processingCount = 0;
$completedCount = 0;
$failedCount = 0;

foreach ($bookings as $row) {
    if ($row['status'] === 'pending') $pendingCount++;
    elseif ($row['status'] === 'processing') $processingCount++;
    elseif ($row['status'] === 'completed') $completedCount++;
    elseif ($row['status'] === 'failed') $failedCount++;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pendaftaran Booking | WARTIK</title>

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
    <style>
        .status-select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
        }
    </style>
</head>

<body class="bg-slate-100 min-h-screen py-8 px-4 sm:px-6 lg:px-8 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-fixed">

    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Header Navigation Bar -->
        <div class="bg-white rounded-3xl p-6 shadow-xl shadow-slate-200/50 border border-white flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-2xl font-bold">
                    <i class="fas fa-list-alt"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-slate-800">Daftar Pendaftaran Booking</h1>
                        <?php if (IS_PRODUCTION): ?>
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Server Online</span>
                        <?php else: ?>
                            <span class="bg-emerald-100 text-emerald-800 text-xs font-bold px-2.5 py-0.5 rounded-full">Bot Lokal</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Monitoring & Manajemen Data Booking WARTIK (auto.test)</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <?php if (!IS_PRODUCTION): ?>
                    <!-- Tombol Tarik Data Online (Hanya Tampil di Lingkungan Bot Lokal) -->
                    <button type="button" onclick="openSyncOnlineModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-indigo-500/20 transition-all flex items-center gap-2 text-sm">
                        <i class="fas fa-cloud-download-alt text-base"></i> Tarik Data Online
                    </button>
                <?php endif; ?>

                <!-- Tombol Form Booking Baru -->
                <a href="./index.php" class="bg-brand-500 hover:bg-brand-600 text-white font-semibold px-5 py-2.5 rounded-xl shadow-md shadow-brand-500/20 transition-all flex items-center gap-2 text-sm">
                    <i class="fas fa-plus-circle"></i> Form Booking Baru
                </a>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Booking</p>
                    <h3 class="text-2xl font-bold text-slate-800" id="statTotal"><?= $totalCount ?></h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-clock"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending (Antrean)</p>
                    <h3 class="text-2xl font-bold text-amber-600" id="statPending"><?= $pendingCount ?></h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Processing</p>
                    <h3 class="text-2xl font-bold text-indigo-600" id="statProcessing"><?= $processingCount ?></h3>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Completed</p>
                    <h3 class="text-2xl font-bold text-emerald-600" id="statCompleted"><?= $completedCount ?></h3>
                </div>
            </div>
        </div>

        <!-- Data Table Container -->
        <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-white overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <h2 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                    <i class="fas fa-table text-brand-500"></i> List Pendaftaran Aktif
                </h2>
                
                <div class="relative w-full sm:w-72">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama, kode booking..." class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600" id="bookingTable">
                    <thead class="bg-slate-50 text-xs font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-4 px-6">Kode Booking</th>
                            <th class="py-4 px-6">Ketua Kelompok</th>
                            <th class="py-4 px-6">Tgl Berangkat</th>
                            <th class="py-4 px-6">Rombongan</th>
                            <th class="py-4 px-6">Status Bot (Ubah Status)</th>
                            <th class="py-4 px-6">Tgl Daftar</th>
                            <th class="py-4 px-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (count($bookings) === 0): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                                    <i class="fas fa-inbox text-4xl mb-3 block text-slate-300"></i>
                                    Belum ada data pendaftaran booking.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($bookings as $b): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors" data-search="<?= strtolower($b['booking_id'] . ' ' . $b['nama_ketua'] . ' ' . $b['hp_ketua']) ?>">
                                    <td class="py-4 px-6 font-mono font-semibold text-brand-700">
                                        <?= htmlspecialchars($b['booking_id']) ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-slate-800"><?= htmlspecialchars($b['nama_ketua'] ?: 'Tanpa Nama') ?></div>
                                        <div class="text-xs text-slate-400 font-mono"><i class="fas fa-phone text-slate-300 mr-1"></i><?= htmlspecialchars($b['hp_ketua'] ?: '-') ?></div>
                                    </td>
                                    <td class="py-4 px-6 font-medium text-slate-700">
                                        <i class="fas fa-calendar-day text-brand-500 mr-1"></i> <?= htmlspecialchars($b['target_date']) ?>
                                    </td>
                                    <td class="py-4 px-6 font-medium">
                                        <span class="bg-slate-100 text-slate-700 text-xs px-2.5 py-1 rounded-full font-semibold">
                                            1 Ketua + <?= intval($b['total_anggota']) ?> Anggota
                                        </span>
                                    </td>

                                    <!-- INTERACTIVE STATUS DROPDOWN SELECTION -->
                                    <td class="py-4 px-6">
                                        <?php
                                        $st = $b['status'];
                                        $bgClass = 'bg-amber-50 text-amber-600 border-amber-200';
                                        if ($st === 'processing') $bgClass = 'bg-indigo-50 text-indigo-600 border-indigo-200';
                                        elseif ($st === 'completed') $bgClass = 'bg-emerald-50 text-emerald-600 border-emerald-200';
                                        elseif ($st === 'failed') $bgClass = 'bg-rose-50 text-rose-600 border-rose-200';
                                        ?>
                                        <select 
                                            onchange="changeBookingStatus('<?= htmlspecialchars($b['booking_id']) ?>', this)" 
                                            class="status-select font-semibold text-xs px-3 py-1.5 rounded-full border cursor-pointer font-sans transition-all focus:outline-none <?= $bgClass ?>"
                                            data-current-status="<?= $st ?>"
                                        >
                                            <option value="pending" <?= $st === 'pending' ? 'selected' : '' ?>>⏳ Pending</option>
                                            <option value="processing" <?= $st === 'processing' ? 'selected' : '' ?>>🔄 Processing</option>
                                            <option value="completed" <?= $st === 'completed' ? 'selected' : '' ?>>✅ Completed</option>
                                            <option value="failed" <?= $st === 'failed' ? 'selected' : '' ?>>❌ Failed</option>
                                        </select>
                                    </td>

                                    <td class="py-4 px-6 text-xs text-slate-400">
                                        <?= date('d M Y H:i', strtotime($b['created_at'])) ?>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <!-- Detail Button -->
                                            <a href="./detail.php?id=<?= urlencode($b['booking_id']) ?>" title="Lihat Detail" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <!-- Edit Button -->
                                            <a href="./edit.php?id=<?= urlencode($b['booking_id']) ?>" title="Edit Data" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 flex items-center justify-center transition-colors">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <!-- Soft Delete Button -->
                                            <button type="button" onclick="confirmSoftDelete('<?= htmlspecialchars($b['booking_id']) ?>')" title="Hapus Data (Soft Delete)" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center transition-colors">
                                                <i class="fas fa-trash-alt text-xs"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Default Target Domain Online Hardcoded
        const HARDCODED_ONLINE_URL = "<?= ONLINE_SYNC_URL ?>";

        // Live Filter Search
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#bookingTable tbody tr[data-search]');
            
            rows.forEach(row => {
                const text = row.getAttribute('data-search');
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Handler Tarik Data Online -> Lokal (Presisi 1-Click)
        async function openSyncOnlineModal() {
            const currentUrl = localStorage.getItem('onlineWebUrl') || HARDCODED_ONLINE_URL;

            const { value: formValues } = await Swal.fire({
                title: '☁️ Tarik Data Booking Online',
                html: `
                    <div class="text-left space-y-4 text-sm mt-2">
                        <p class="text-slate-600">Tarik seluruh data pendaftaran dari server online ke database lokal agar dapat diproses oleh bot lokal secara cepat.</p>
                        <div class="bg-indigo-50 p-3 rounded-xl border border-indigo-100 font-mono text-xs text-indigo-900">
                            <strong>Target Domain Online:</strong><br>${currentUrl}
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Ubah Domain Online (Opsional)</label>
                            <input id="swal-online-url" class="swal2-input !w-full !m-0" placeholder="https://domain-online-anda.com" value="${currentUrl}">
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-cloud-download-alt mr-1"></i> Tarik Data Sekarang',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#94a3b8',
                preConfirm: () => {
                    const url = document.getElementById('swal-online-url').value.trim();
                    if (!url) {
                        Swal.showValidationMessage('URL Website Online wajib diisi!');
                        return false;
                    }
                    return { url };
                }
            });

            if (formValues) {
                const { url } = formValues;
                localStorage.setItem('onlineWebUrl', url);

                // Tampilkan Loading Spinner
                Swal.fire({
                    title: 'Sedang Menarik Data...',
                    text: `Terhubung ke ${url} dan mensinkronkan data ke database lokal...`,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const formData = new FormData();
                    formData.append('online_url', url);

                    const response = await fetch('./api/sync.php', {
                        method: 'POST',
                        body: formData
                    });

                    const res = await response.json();

                    if (response.ok && res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sinkronisasi Berhasil!',
                            html: `<p>${res.message}</p><p class="mt-2 text-xs font-semibold text-indigo-600 bg-indigo-50 py-1.5 px-3 rounded-lg inline-block">Data pendaftaran siap diproses oleh Bot Lokal!</p>`,
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(res.message || 'Gagal menarik data dari server online.');
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Sinkronisasi',
                        text: err.message,
                        confirmButtonColor: '#10b981'
                    });
                }
            }
        }

        // Quick Change Status AJAX Handler
        async function changeBookingStatus(bookingId, selectEl) {
            const newStatus = selectEl.value;
            const oldStatus = selectEl.getAttribute('data-current-status');

            if (newStatus === oldStatus) return;

            selectEl.disabled = true;

            try {
                const formData = new FormData();
                formData.append('id', bookingId);
                formData.append('status', newStatus);

                const response = await fetch('./update_status.php', {
                    method: 'POST',
                    body: formData
                });

                const res = await response.json();

                if (response.ok && res.success) {
                    selectEl.setAttribute('data-current-status', newStatus);
                    
                    // Update styling class dynamically
                    selectEl.className = 'status-select font-semibold text-xs px-3 py-1.5 rounded-full border cursor-pointer font-sans transition-all focus:outline-none ';
                    if (newStatus === 'pending') selectEl.className += 'bg-amber-50 text-amber-600 border-amber-200';
                    else if (newStatus === 'processing') selectEl.className += 'bg-indigo-50 text-indigo-600 border-indigo-200';
                    else if (newStatus === 'completed') selectEl.className += 'bg-emerald-50 text-emerald-600 border-emerald-200';
                    else if (newStatus === 'failed') selectEl.className += 'bg-rose-50 text-rose-600 border-rose-200';

                    // Show Toast Notification
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: `Status ${bookingId} diubah ke ${newStatus.toUpperCase()}`
                    });
                } else {
                    throw new Error(res.message || 'Gagal mengubah status.');
                }
            } catch (err) {
                selectEl.value = oldStatus;
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Ubah Status',
                    text: err.message,
                    confirmButtonColor: '#10b981'
                });
            } finally {
                selectEl.disabled = false;
            }
        }

        // Soft Delete Handler
        function confirmSoftDelete(bookingId) {
            Swal.fire({
                title: 'Hapus Data Booking?',
                text: `Data ${bookingId} akan di-soft delete dan tidak akan diproses oleh bot.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus Data',
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
                                    window.location.reload();
                                });
                            } else {
                                throw new Error(res.message);
                            }
                        })
                        .catch(err => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal Hapus',
                                text: err.message || 'Terjadi kesalahan sistem.',
                                confirmButtonColor: '#10b981'
                            });
                        });
                }
            });
        }
    </script>
</body>

</html>
