<?php
// Main entry point for auto.test form booking WARTIK
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semeru Booking Form | WARTIK</title>

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

    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css">
</head>

<body
    class="bg-slate-100 min-h-screen py-8 px-4 sm:px-6 lg:px-8 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-fixed">

    <div
        class="max-w-5xl mx-auto bg-white rounded-3xl shadow-xl shadow-slate-200/50 border border-white overflow-hidden backdrop-blur-sm">

        <!-- Header Image/Banner Area -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-8 py-10 relative overflow-hidden">
            <div
                class="absolute inset-0 opacity-20 bg-[url('https://images.unsplash.com/photo-1589184589252-47525330e791?auto=format&fit=crop&q=80')] bg-cover bg-center">
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900 to-transparent"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Form Booking WARTIK Ranu Kumbolo</h1>
                    <p class="text-slate-300 font-medium">Booking Jasa War Tiket Ranukumbolo (auto.test)</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="./submit.php" class="bg-white/10 hover:bg-white/20 text-white border border-white/20 px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2">
                        <i class="fas fa-list-alt"></i> Lihat List Submit (/submit)
                    </a>
                    <div class="hidden md:flex h-12 w-12 bg-white/10 backdrop-blur-md rounded-2xl items-center justify-center border border-white/20">
                        <i class="fas fa-mountain text-2xl text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <form id="bookingForm" class="p-8 sm:p-10 space-y-10">

            <!-- SECTION: Tanggal Kunjungan -->
            <section>
                <h2 class="form-section-title">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center"><i
                            class="fas fa-calendar-alt"></i></div>
                    Jadwal Kunjungan
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="form-label-premium required-star">Tanggal Berangkat</label>
                        <div class="relative">
                            <i class="fas fa-mountain absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" class="form-input-premium pl-11 datepicker" id="f_d2x"
                                placeholder="Pilih tanggal">
                        </div>
                    </div>
                    <div>
                        <label class="form-label-premium required-star">Tanggal Pulang</label>
                        <div class="relative">
                            <i class="fas fa-home absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" class="form-input-premium pl-11 bg-slate-100 cursor-not-allowed"
                                id="f_p6w" placeholder="Pilih tanggal" disabled>
                        </div>
                        <p class="text-xs text-rose-500 mt-2 font-medium"><i class="fas fa-info-circle mr-1"></i>Tiket
                            terhitung 2 hari 1 malam</p>
                    </div>
                    <div>
                        <label class="form-label-premium">Pendamping</label>
                        <select class="form-input-premium bg-slate-100 cursor-not-allowed" id="f_a3e" disabled>
                            <option value="1" selected>Dengan Pendamping</option>
                        </select>
                    </div>
                </div>
            </section>

            <!-- SECTION: Data Ketua -->
            <section>
                <h2 class="form-section-title">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center"><i
                            class="fas fa-user-shield"></i></div>
                    Data Ketua Kelompok
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="form-label-premium required-star">Nama Lengkap Sesuai Identitas</label>
                        <input type="text" class="form-input-premium" id="f_x7a" placeholder="Masukkan nama lengkap">
                    </div>

                    <div>
                        <label class="form-label-premium required-star">Jenis Identitas</label>
                        <select class="form-input-premium" id="f_h1b">
                            <option value="">- Pilih Jenis Identitas -</option>
                            <option value="1">KTP</option>
                            <option value="2">SIM</option>
                            <option value="3">Passport</option>
                            <option value="4">KTM</option>
                            <option value="5">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-premium required-star">Nomor Identitas</label>
                        <input type="text" class="form-input-premium" id="f_v8n" placeholder="Contoh: 3571234567890001">
                    </div>

                    <div>
                        <label class="form-label-premium required-star">Jenis Kelamin</label>
                        <select class="form-input-premium" id="f_t5g">
                            <option value="">- Pilih -</option>
                            <option value="1">Laki Laki</option>
                            <option value="2">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-premium required-star">Tanggal Lahir</label>
                        <div class="relative">
                            <i class="fas fa-birthday-cake absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" class="form-input-premium pl-11 datepicker" id="f_j4d"
                                placeholder="YYYY-MM-DD">
                        </div>
                    </div>

                    <div>
                        <label class="form-label-premium required-star">Negara</label>
                        <select class="form-input-premium" id="f_c8z">
                            <option value="99" selected>Indonesia</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label-premium required-star">No HP</label>
                        <div class="relative">
                            <i class="fab fa-whatsapp absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" class="form-input-premium pl-11" id="f_m2q" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="form-label-premium required-star">Alamat</label>
                        <input type="text" class="form-input-premium" id="f_w6s"
                            placeholder="Nama Jalan, RT/RW, Kelurahan">
                    </div>

                    <div id="f_pd_wrap" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="form-label-premium required-star">Provinsi</label>
                            <select class="form-input-premium" id="f_k3m">
                                <option value="">- Pilih Provinsi -</option>
                                <option value="11">ACEH</option>
                                <option value="51">BALI</option>
                                <option value="36">BANTEN</option>
                                <option value="17">BENGKULU</option>
                                <option value="34">DI YOGYAKARTA</option>
                                <option value="31">DKI JAKARTA</option>
                                <option value="75">GORONTALO</option>
                                <option value="15">JAMBI</option>
                                <option value="32">JAWA BARAT</option>
                                <option value="33">JAWA TENGAH</option>
                                <option value="35">JAWA TIMUR</option>
                                <option value="61">KALIMANTAN BARAT</option>
                                <option value="63">KALIMANTAN SELATAN</option>
                                <option value="62">KALIMANTAN TENGAH</option>
                                <option value="64">KALIMANTAN TIMUR</option>
                                <option value="65">KALIMANTAN UTARA</option>
                                <option value="19">KEPULAUAN BANGKA BELITUNG</option>
                                <option value="21">KEPULAUAN RIAU</option>
                                <option value="18">LAMPUNG</option>
                                <option value="81">MALUKU</option>
                                <option value="82">MALUKU UTARA</option>
                                <option value="52">NUSA TENGGARA BARAT</option>
                                <option value="53">NUSA TENGGARA TIMUR</option>
                                <option value="94">PAPUA</option>
                                <option value="91">PAPUA BARAT</option>
                                <option value="14">RIAU</option>
                                <option value="76">SULAWESI BARAT</option>
                                <option value="73">SULAWESI SELATAN</option>
                                <option value="72">SULAWESI TENGAH</option>
                                <option value="74">SULAWESI TENGGARA</option>
                                <option value="71">SULAWESI UTARA</option>
                                <option value="13">SUMATERA BARAT</option>
                                <option value="16">SUMATERA SELATAN</option>
                                <option value="12">SUMATERA UTARA</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label-premium required-star">Kota/Kabupaten</label>
                            <select class="form-input-premium disabled:bg-slate-100 disabled:text-slate-400" id="f_r9p">
                                <option value="">- Pilih Provinsi Dulu -</option>
                            </select>
                        </div>
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
                    <span class="bg-slate-100 text-slate-600 text-sm font-semibold px-3 py-1 rounded-full"><span
                            id="memberCounter">0</span> / 9 Anggota</span>
                </div>

                <div id="membersContainer" class="space-y-6">
                    <!-- Member boxes will be injected here -->
                </div>

                <div class="mt-6 flex flex-col sm:flex-row items-center gap-4">
                    <button type="button" id="addMemberBtn"
                        class="w-full sm:w-auto bg-white text-brand-600 font-semibold py-3 px-6 rounded-xl border-2 border-brand-100 hover:bg-brand-50 hover:border-brand-200 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-plus-circle"></i> Tambah Anggota
                    </button>
                    <p class="text-sm text-slate-500 font-medium">
                        <i class="fas fa-info-circle text-slate-400 mr-1"></i> Maksimal 9 anggota tambahan (Total
                        rombongan 10 orang).
                    </p>
                </div>
            </section>

            <!-- SECTION: Pembayaran & Tracking -->
            <section>
                <h2 class="form-section-title">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><i
                            class="fas fa-wallet"></i></div>
                    Metode Pembayaran & Kredensial Tracking
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="form-label-premium required-star">Pilih Metode Pembayaran</label>
                        <select class="form-input-premium" id="f_q7y">
                            <option value="">- Pilih -</option>
                            <option value="qris">QRIS</option>
                            <option value="VA-Mandiri">Virtual Account Mandiri</option>
                            <option value="VA-BNI">Virtual Account BNI</option>
                        </select>
                    </div>
                </div>

                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6">
                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0 mt-0.5">
                            <i class="fas fa-search-location text-sm"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-indigo-900 text-sm mb-1">Informasi Tracking Tiket</h3>
                            <p class="text-xs text-indigo-700 leading-relaxed">
                                Gunakan Nomor HP dan PIN di bawah ini untuk melacak status booking Anda nantinya.<br>
                                <strong class="text-indigo-900 mt-1 block">Tips:</strong> Gunakan Nomor HP dan PIN yang sama saat mendaftarkan rombongan lain.
                            </p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="form-label-premium required-star text-indigo-900">No HP Tracking</label>
                            <div class="relative">
                                <i class="fas fa-mobile-alt absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400"></i>
                                <input type="text" class="form-input-premium pl-11 border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500/20" id="tracking_hp" placeholder="Contoh: 081234567890">
                            </div>
                        </div>
                        <div>
                            <label class="form-label-premium required-star text-indigo-900">PIN (4-6 Angka)</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-indigo-400"></i>
                                <input type="password" class="form-input-premium pl-11 border-indigo-200 focus:border-indigo-500 focus:ring-indigo-500/20" id="tracking_pin" placeholder="Buat PIN 4-6 angka" maxlength="6">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- SECTION: Persetujuan & Submit -->
            <section class="pt-6 border-t border-slate-200">
                <div class="bg-slate-50 rounded-2xl p-6 border border-slate-200 mb-8">
                    <label class="flex items-start gap-4 cursor-pointer group">
                        <div class="relative flex items-center justify-center mt-1">
                            <input type="checkbox" id="termsCheckbox"
                                class="w-5 h-5 appearance-none rounded border-2 border-slate-300 checked:bg-brand-500 checked:border-brand-500 transition-colors peer cursor-pointer">
                            <i
                                class="fas fa-check text-white text-xs absolute pointer-events-none opacity-0 peer-checked:opacity-100"></i>
                        </div>
                        <span
                            class="text-sm text-slate-600 leading-relaxed group-hover:text-slate-800 transition-colors">
                            Dengan mencentang kotak ini, saya sebagai ketua kelompok menyatakan bahwa semua data yang
                            dimasukkan sudah benar. Kesalahan input data sepenuhnya bukan tanggung jawab pihak penyedia
                            jasa war tiket (Wartik Ranukumbolo), dan pengguna menyadari bahwa jasa war ini tidak dapat
                            selalu menjamin diperolehnya slot tiket pada hari yang telah ditentukan.
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <button type="button" id="resetBtn"
                        class="w-full bg-white border-2 border-rose-200 text-rose-600 font-bold py-4 px-6 rounded-xl shadow-sm hover:bg-rose-50 hover:border-rose-300 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-trash-alt"></i> Reset Formulir
                    </button>
                    <button type="button" id="bookingBtn"
                        class="w-full bg-gradient-to-r from-brand-500 to-brand-600 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-brand-500/30 hover:from-brand-600 hover:to-brand-700 transition-all flex items-center justify-center gap-2 group">
                        <i class="fas fa-check-circle group-hover:scale-110 transition-transform"></i> CONFIRM BOOKING
                    </button>
                </div>
            </section>

        </form>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const API_BASE_URL = './api';
        let OBF_KEY = '';

        function obfuscate(data) {
            if (!OBF_KEY) return '';
            const json = JSON.stringify(data);
            const bytes = new TextEncoder().encode(json);
            const keyBytes = new TextEncoder().encode(OBF_KEY);
            const out = new Uint8Array(bytes.length);
            for (let i = 0; i < bytes.length; i++) {
                out[i] = bytes[i] ^ keyBytes[i % keyBytes.length];
            }
            let binary = '';
            for (let i = 0; i < out.length; i++) binary += String.fromCharCode(out[i]);
            return btoa(binary);
        }

        function deobfuscate(encoded) {
            if (!OBF_KEY || !encoded) return null;
            try {
                const binary = atob(encoded);
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                
                const keyBytes = new TextEncoder().encode(OBF_KEY);
                const out = new Uint8Array(bytes.length);
                for (let i = 0; i < bytes.length; i++) {
                    out[i] = bytes[i] ^ keyBytes[i % keyBytes.length];
                }
                
                const json = new TextDecoder().decode(out);
                return JSON.parse(json);
            } catch (e) {
                console.error("Gagal mendekripsi cache:", e);
                return null;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {

            flatpickr('.datepicker', {
                dateFormat: "Y-m-d",
                disableMobile: "true"
            });

            const dateDepartInput = document.getElementById('f_d2x');
            const dateArrivalInput = document.getElementById('f_p6w');

            dateDepartInput.addEventListener('change', function (e) {
                if (e.target.value) {
                    let d = new Date(e.target.value);
                    d.setDate(d.getDate() + 1);
                    const arrivalStr = d.toISOString().split('T')[0];
                    dateArrivalInput.value = arrivalStr;

                    dateArrivalInput.classList.add('ring-2', 'ring-brand-500');
                    setTimeout(() => dateArrivalInput.classList.remove('ring-2', 'ring-brand-500'), 500);
                }
            });

            // ============ DATA KOTA & PROVINSI LENGKAP (TNBTS ACCURATE & OPEN SOURCE API FALLBACK) ============
            const DISTRICT_DATA = {
                "11": "<option value=\"\">-</option><option value=\"1107\">KABUPATEN ACEH BARAT</option><option value=\"1112\">KABUPATEN ACEH BARAT DAYA</option><option value=\"1108\">KABUPATEN ACEH BESAR</option><option value=\"1116\">KABUPATEN ACEH JAYA</option><option value=\"1103\">KABUPATEN ACEH SELATAN</option><option value=\"1102\">KABUPATEN ACEH SINGKIL</option><option value=\"1114\">KABUPATEN ACEH TAMIANG</option><option value=\"1106\">KABUPATEN ACEH TENGAH</option><option value=\"1104\">KABUPATEN ACEH TENGGARA</option><option value=\"1105\">KABUPATEN ACEH TIMUR</option><option value=\"1111\">KABUPATEN ACEH UTARA</option><option value=\"1117\">KABUPATEN BENER MERIAH</option><option value=\"1110\">KABUPATEN BIREUEN</option><option value=\"1113\">KABUPATEN GAYO LUES</option><option value=\"1115\">KABUPATEN NAGAN RAYA</option><option value=\"1109\">KABUPATEN PIDIE</option><option value=\"1118\">KABUPATEN PIDIE JAYA</option><option value=\"1101\">KABUPATEN SIMEULUE</option><option value=\"1171\">KOTA BANDA ACEH</option><option value=\"1173\">KOTA LANGSA</option><option value=\"1174\">KOTA LHOKSEUMAWE</option><option value=\"1172\">KOTA SABANG</option><option value=\"1175\">KOTA SUBULUSSALAM</option>",
                "12": "<option value=\"\">-</option><option value=\"1208\">KABUPATEN ASAHAN</option><option value=\"1219\">KABUPATEN BATU BARA</option><option value=\"1210\">KABUPATEN DAIRI</option><option value=\"1212\">KABUPATEN DELI SERDANG</option><option value=\"1215\">KABUPATEN HUMBANG HASUNDUTAN</option><option value=\"1211\">KABUPATEN KARO</option><option value=\"1207\">KABUPATEN LABUHAN BATU</option><option value=\"1222\">KABUPATEN LABUHAN BATU SELATAN</option><option value=\"1223\">KABUPATEN LABUHAN BATU UTARA</option><option value=\"1213\">KABUPATEN LANGKAT</option><option value=\"1202\">KABUPATEN MANDAILING NATAL</option><option value=\"1201\">KABUPATEN NIAS</option><option value=\"1225\">KABUPATEN NIAS BARAT</option><option value=\"1214\">KABUPATEN NIAS SELATAN</option><option value=\"1224\">KABUPATEN NIAS UTARA</option><option value=\"1221\">KABUPATEN PADANG LAWAS</option><option value=\"1220\">KABUPATEN PADANG LAWAS UTARA</option><option value=\"1216\">KABUPATEN PAKPAK BHARAT</option><option value=\"1217\">KABUPATEN SAMOSIR</option><option value=\"1218\">KABUPATEN SERDANG BEDAGAI</option><option value=\"1209\">KABUPATEN SIMALUNGUN</option><option value=\"1203\">KABUPATEN TAPANULI SELATAN</option><option value=\"1204\">KABUPATEN TAPANULI TENGAH</option><option value=\"1205\">KABUPATEN TAPANULI UTARA</option><option value=\"1206\">KABUPATEN TOBA SAMOSIR</option><option value=\"1276\">KOTA BINJAI</option><option value=\"1278\">KOTA GUNUNGSITOLI</option><option value=\"1275\">KOTA MEDAN</option><option value=\"1277\">KOTA PADANGSIDIMPUAN</option><option value=\"1273\">KOTA PEMATANG SIANTAR</option><option value=\"1271\">KOTA SIBOLGA</option><option value=\"1272\">KOTA TANJUNG BALAI</option><option value=\"1274\">KOTA TEBING TINGGI</option>",
                "13": "<option value=\"\">-</option><option value=\"1307\">KABUPATEN AGAM</option><option value=\"1311\">KABUPATEN DHARMASRAYA</option><option value=\"1301\">KABUPATEN KEPULAUAN MENTAWAI</option><option value=\"1308\">KABUPATEN LIMA PULUH KOTA</option><option value=\"1306\">KABUPATEN PADANG PARIAMAN</option><option value=\"1309\">KABUPATEN PASAMAN</option><option value=\"1312\">KABUPATEN PASAMAN BARAT</option><option value=\"1302\">KABUPATEN PESISIR SELATAN</option><option value=\"1304\">KABUPATEN SIJUNJUNG</option><option value=\"1303\">KABUPATEN SOLOK</option><option value=\"1310\">KABUPATEN SOLOK SELATAN</option><option value=\"1305\">KABUPATEN TANAH DATAR</option><option value=\"1375\">KOTA BUKITTINGGI</option><option value=\"1371\">KOTA PADANG</option><option value=\"1374\">KOTA PADANG PANJANG</option><option value=\"1377\">KOTA PARIAMAN</option><option value=\"1376\">KOTA PAYAKUMBUH</option><option value=\"1373\">KOTA SAWAH LUNTO</option><option value=\"1372\">KOTA SOLOK</option>",
                "14": "<option value=\"\">-</option><option value=\"1408\">KABUPATEN BENGKALIS</option><option value=\"1403\">KABUPATEN INDRAGIRI HILIR</option><option value=\"1402\">KABUPATEN INDRAGIRI HULU</option><option value=\"1406\">KABUPATEN KAMPAR</option><option value=\"1410\">KABUPATEN KEPULAUAN MERANTI</option><option value=\"1401\">KABUPATEN KUANTAN SINGINGI</option><option value=\"1404\">KABUPATEN PELALAWAN</option><option value=\"1409\">KABUPATEN ROKAN HILIR</option><option value=\"1407\">KABUPATEN ROKAN HULU</option><option value=\"1405\">KABUPATEN S I A K</option><option value=\"1473\">KOTA D U M A I</option><option value=\"1471\">KOTA PEKANBARU</option>",
                "15": "<option value=\"\">-</option><option value=\"1504\">KABUPATEN BATANG HARI</option><option value=\"1509\">KABUPATEN BUNGO</option><option value=\"1501\">KABUPATEN KERINCI</option><option value=\"1502\">KABUPATEN MERANGIN</option><option value=\"1505\">KABUPATEN MUARO JAMBI</option><option value=\"1503\">KABUPATEN SAROLANGUN</option><option value=\"1507\">KABUPATEN TANJUNG JABUNG BARAT</option><option value=\"1506\">KABUPATEN TANJUNG JABUNG TIMUR</option><option value=\"1508\">KABUPATEN TEBO</option><option value=\"1571\">KOTA JAMBI</option><option value=\"1572\">KOTA SUNGAI PENUH</option>",
                "16": "<option value=\"\">-</option><option value=\"1607\">KABUPATEN BANYU ASIN</option><option value=\"1611\">KABUPATEN EMPAT LAWANG</option><option value=\"1604\">KABUPATEN LAHAT</option><option value=\"1603\">KABUPATEN MUARA ENIM</option><option value=\"1606\">KABUPATEN MUSI BANYUASIN</option><option value=\"1605\">KABUPATEN MUSI RAWAS</option><option value=\"1613\">KABUPATEN MUSI RAWAS UTARA</option><option value=\"1610\">KABUPATEN OGAN ILIR</option><option value=\"1602\">KABUPATEN OGAN KOMERING ILIR</option><option value=\"1601\">KABUPATEN OGAN KOMERING ULU</option><option value=\"1608\">KABUPATEN OGAN KOMERING ULU SELATAN</option><option value=\"1609\">KABUPATEN OGAN KOMERING ULU TIMUR</option><option value=\"1612\">KABUPATEN PENUKAL ABAB LEMATANG ILIR</option><option value=\"1674\">KOTA LUBUKLINGGAU</option><option value=\"1673\">KOTA PAGAR ALAM</option><option value=\"1671\">KOTA PALEMBANG</option><option value=\"1672\">KOTA PRABUMULIH</option>",
                "17": "<option value=\"\">-</option><option value=\"1701\">KABUPATEN BENGKULU SELATAN</option><option value=\"1709\">KABUPATEN BENGKULU TENGAH</option><option value=\"1703\">KABUPATEN BENGKULU UTARA</option><option value=\"1704\">KABUPATEN KAUR</option><option value=\"1708\">KABUPATEN KEPAHIANG</option><option value=\"1707\">KABUPATEN LEBONG</option><option value=\"1706\">KABUPATEN MUKOMUKO</option><option value=\"1702\">KABUPATEN REJANG LEBONG</option><option value=\"1705\">KABUPATEN SELUMA</option><option value=\"1771\">KOTA BENGKULU</option>",
                "18": "<option value=\"\">-</option><option value=\"1801\">KABUPATEN LAMPUNG BARAT</option><option value=\"1803\">KABUPATEN LAMPUNG SELATAN</option><option value=\"1805\">KABUPATEN LAMPUNG TENGAH</option><option value=\"1804\">KABUPATEN LAMPUNG TIMUR</option><option value=\"1806\">KABUPATEN LAMPUNG UTARA</option><option value=\"1811\">KABUPATEN MESUJI</option><option value=\"1809\">KABUPATEN PESAWARAN</option><option value=\"1813\">KABUPATEN PESISIR BARAT</option><option value=\"1810\">KABUPATEN PRINGSEWU</option><option value=\"1802\">KABUPATEN TANGGAMUS</option><option value=\"1812\">KABUPATEN TULANG BAWANG BARAT</option><option value=\"1808\">KABUPATEN TULANGBAWANG</option><option value=\"1807\">KABUPATEN WAY KANAN</option><option value=\"1871\">KOTA BANDAR LAMPUNG</option><option value=\"1872\">KOTA METRO</option>",
                "19": "<option value=\"\">-</option><option value=\"1901\">KABUPATEN BANGKA</option><option value=\"1903\">KABUPATEN BANGKA BARAT</option><option value=\"1905\">KABUPATEN BANGKA SELATAN</option><option value=\"1904\">KABUPATEN BANGKA TENGAH</option><option value=\"1902\">KABUPATEN BELITUNG</option><option value=\"1906\">KABUPATEN BELITUNG TIMUR</option><option value=\"1971\">KOTA PANGKAL PINANG</option>",
                "21": "<option value=\"\">-</option><option value=\"2102\">KABUPATEN BINTAN</option><option value=\"2101\">KABUPATEN KARIMUN</option><option value=\"2105\">KABUPATEN KEPULAUAN ANAMBAS</option><option value=\"2104\">KABUPATEN LINGGA</option><option value=\"2103\">KABUPATEN NATUNA</option><option value=\"2171\">KOTA B A T A M</option><option value=\"2172\">KOTA TANJUNG PINANG</option>",
                "31": "<option value=\"\">-</option><option value=\"3101\">KABUPATEN KEPULAUAN SERIBU</option><option value=\"3174\">KOTA JAKARTA BARAT</option><option value=\"3173\">KOTA JAKARTA PUSAT</option><option value=\"3171\">KOTA JAKARTA SELATAN</option><option value=\"3172\">KOTA JAKARTA TIMUR</option><option value=\"3175\">KOTA JAKARTA UTARA</option>",
                "32": "<option value=\"\">-</option><option value=\"3204\">KABUPATEN BANDUNG</option><option value=\"3217\">KABUPATEN BANDUNG BARAT</option><option value=\"3216\">KABUPATEN BEKASI</option><option value=\"3201\">KABUPATEN BOGOR</option><option value=\"3207\">KABUPATEN CIAMIS</option><option value=\"3203\">KABUPATEN CIANJUR</option><option value=\"3209\">KABUPATEN CIREBON</option><option value=\"3205\">KABUPATEN GARUT</option><option value=\"3212\">KABUPATEN INDRAMAYU</option><option value=\"3215\">KABUPATEN KARAWANG</option><option value=\"3208\">KABUPATEN KUNINGAN</option><option value=\"3210\">KABUPATEN MAJALENGKA</option><option value=\"3218\">KABUPATEN PANGANDARAN</option><option value=\"3214\">KABUPATEN PURWAKARTA</option><option value=\"3213\">KABUPATEN SUBANG</option><option value=\"3202\">KABUPATEN SUKABUMI</option><option value=\"3211\">KABUPATEN SUMEDANG</option><option value=\"3206\">KABUPATEN TASIKMALAYA</option><option value=\"3273\">KOTA BANDUNG</option><option value=\"3279\">KOTA BANJAR</option><option value=\"3275\">KOTA BEKASI</option><option value=\"3271\">KOTA BOGOR</option><option value=\"3277\">KOTA CIMAHI</option><option value=\"3274\">KOTA CIREBON</option><option value=\"3276\">KOTA DEPOK</option><option value=\"3272\">KOTA SUKABUMI</option><option value=\"3278\">KOTA TASIKMALAYA</option>",
                "33": "<option value=\"\">-</option><option value=\"3304\">KABUPATEN BANJARNEGARA</option><option value=\"3302\">KABUPATEN BANYUMAS</option><option value=\"3325\">KABUPATEN BATANG</option><option value=\"3316\">KABUPATEN BLORA</option><option value=\"3309\">KABUPATEN BOYOLALI</option><option value=\"3329\">KABUPATEN BREBES</option><option value=\"3301\">KABUPATEN CILACAP</option><option value=\"3321\">KABUPATEN DEMAK</option><option value=\"3315\">KABUPATEN GROBOGAN</option><option value=\"3320\">KABUPATEN JEPARA</option><option value=\"3313\">KABUPATEN KARANGANYAR</option><option value=\"3305\">KABUPATEN KEBUMEN</option><option value=\"3324\">KABUPATEN KENDAL</option><option value=\"3310\">KABUPATEN KLATEN</option><option value=\"3319\">KABUPATEN KUDUS</option><option value=\"3308\">KABUPATEN MAGELANG</option><option value=\"3318\">KABUPATEN PATI</option><option value=\"3326\">KABUPATEN PEKALONGAN</option><option value=\"3327\">KABUPATEN PEMALANG</option><option value=\"3303\">KABUPATEN PURBALINGGA</option><option value=\"3306\">KABUPATEN PURWOREJO</option><option value=\"3317\">KABUPATEN REMBANG</option><option value=\"3322\">KABUPATEN SEMARANG</option><option value=\"3314\">KABUPATEN SRAGEN</option><option value=\"3311\">KABUPATEN SUKOHARJO</option><option value=\"3328\">KABUPATEN TEGAL</option><option value=\"3323\">KABUPATEN TEMANGGUNG</option><option value=\"3312\">KABUPATEN WONOGIRI</option><option value=\"3307\">KABUPATEN WONOSOBO</option><option value=\"3371\">KOTA MAGELANG</option><option value=\"3375\">KOTA PEKALONGAN</option><option value=\"3373\">KOTA SALATIGA</option><option value=\"3374\">KOTA SEMARANG</option><option value=\"3372\">KOTA SURAKARTA</option><option value=\"3376\">KOTA TEGAL</option>",
                "34": "<option value=\"\">-</option><option value=\"3402\">KABUPATEN BANTUL</option><option value=\"3403\">KABUPATEN GUNUNG KIDUL</option><option value=\"3401\">KABUPATEN KULON PROGO</option><option value=\"3404\">KABUPATEN SLEMAN</option><option value=\"3471\">KOTA YOGYAKARTA</option>",
                "35": "<option value=\"\">-</option><option value=\"3526\">KABUPATEN BANGKALAN</option><option value=\"3510\">KABUPATEN BANYUWANGI</option><option value=\"3505\">KABUPATEN BLITAR</option><option value=\"3522\">KABUPATEN BOJONEGORO</option><option value=\"3511\">KABUPATEN BONDOWOSO</option><option value=\"3525\">KABUPATEN GRESIK</option><option value=\"3509\">KABUPATEN JEMBER</option><option value=\"3517\">KABUPATEN JOMBANG</option><option value=\"3506\">KABUPATEN KEDIRI</option><option value=\"3524\">KABUPATEN LAMONGAN</option><option value=\"3508\">KABUPATEN LUMAJANG</option><option value=\"3519\">KABUPATEN MADIUN</option><option value=\"3520\">KABUPATEN MAGETAN</option><option value=\"3507\">KABUPATEN MALANG</option><option value=\"3516\">KABUPATEN MOJOKERTO</option><option value=\"3518\">KABUPATEN NGANJUK</option><option value=\"3521\">KABUPATEN NGAWI</option><option value=\"3501\">KABUPATEN PACITAN</option><option value=\"3528\">KABUPATEN PAMEKASAN</option><option value=\"3514\">KABUPATEN PASURUAN</option><option value=\"3502\">KABUPATEN PONOROGO</option><option value=\"3513\">KABUPATEN PROBOLINGGO</option><option value=\"3527\">KABUPATEN SAMPANG</option><option value=\"3515\">KABUPATEN SIDOARJO</option><option value=\"3512\">KABUPATEN SITUBONDO</option><option value=\"3529\">KABUPATEN SUMENEP</option><option value=\"3503\">KABUPATEN TRENGGALEK</option><option value=\"3523\">KABUPATEN TUBAN</option><option value=\"3504\">KABUPATEN TULUNGAGUNG</option><option value=\"3579\">KOTA BATU</option><option value=\"3572\">KOTA BLITAR</option><option value=\"3571\">KOTA KEDIRI</option><option value=\"3577\">KOTA MADIUN</option><option value=\"3573\">KOTA MALANG</option><option value=\"3576\">KOTA MOJOKERTO</option><option value=\"3575\">KOTA PASURUAN</option><option value=\"3574\">KOTA PROBOLINGGO</option><option value=\"3578\">KOTA SURABAYA</option>",
                "36": "<option value=\"\">-</option><option value=\"3602\">KABUPATEN LEBAK</option><option value=\"3601\">KABUPATEN PANDEGLANG</option><option value=\"3604\">KABUPATEN SERANG</option><option value=\"3603\">KABUPATEN TANGERANG</option><option value=\"3672\">KOTA CILEGON</option><option value=\"3673\">KOTA SERANG</option><option value=\"3671\">KOTA TANGERANG</option><option value=\"3674\">KOTA TANGERANG SELATAN</option>",
                "51": "<option value=\"\">-</option><option value=\"5103\">KABUPATEN BADUNG</option><option value=\"5106\">KABUPATEN BANGLI</option><option value=\"5108\">KABUPATEN BULELENG</option><option value=\"5104\">KABUPATEN GIANYAR</option><option value=\"5101\">KABUPATEN JEMBRANA</option><option value=\"5107\">KABUPATEN KARANG ASEM</option><option value=\"5105\">KABUPATEN KLUNGKUNG</option><option value=\"5102\">KABUPATEN TABANAN</option><option value=\"5171\">KOTA DENPASAR</option>",
                "52": "<option value=\"\">-</option><option value=\"5206\">KABUPATEN BIMA</option><option value=\"5205\">KABUPATEN DOMPU</option><option value=\"5201\">KABUPATEN LOMBOK BARAT</option><option value=\"5202\">KABUPATEN LOMBOK TENGAH</option><option value=\"5203\">KABUPATEN LOMBOK TIMUR</option><option value=\"5208\">KABUPATEN LOMBOK UTARA</option><option value=\"5204\">KABUPATEN SUMBAWA</option><option value=\"5207\">KABUPATEN SUMBAWA BARAT</option><option value=\"5272\">KOTA BIMA</option><option value=\"5271\">KOTA MATARAM</option>",
                "53": "<option value=\"\">-</option><option value=\"5307\">KABUPATEN ALOR</option><option value=\"5306\">KABUPATEN BELU</option><option value=\"5311\">KABUPATEN ENDE</option><option value=\"5309\">KABUPATEN FLORES TIMUR</option><option value=\"5303\">KABUPATEN KUPANG</option><option value=\"5308\">KABUPATEN LEMBATA</option><option value=\"5321\">KABUPATEN MALAKA</option><option value=\"5313\">KABUPATEN MANGGARAI</option><option value=\"5315\">KABUPATEN MANGGARAI BARAT</option><option value=\"5319\">KABUPATEN MANGGARAI TIMUR</option><option value=\"5318\">KABUPATEN NAGEKEO</option><option value=\"5312\">KABUPATEN NGADA</option><option value=\"5314\">KABUPATEN ROTE NDAO</option><option value=\"5320\">KABUPATEN SABU RAIJUA</option><option value=\"5310\">KABUPATEN SIKKA</option><option value=\"5301\">KABUPATEN SUMBA BARAT</option><option value=\"5317\">KABUPATEN SUMBA BARAT DAYA</option><option value=\"5316\">KABUPATEN SUMBA TENGAH</option><option value=\"5302\">KABUPATEN SUMBA TIMUR</option><option value=\"5304\">KABUPATEN TIMOR TENGAH SELATAN</option><option value=\"5305\">KABUPATEN TIMOR TENGAH UTARA</option><option value=\"5371\">KOTA KUPANG</option>",
                "61": "<option value=\"\">-</option><option value=\"6102\">KABUPATEN BENGKAYANG</option><option value=\"6108\">KABUPATEN KAPUAS HULU</option><option value=\"6111\">KABUPATEN KAYONG UTARA</option><option value=\"6106\">KABUPATEN KETAPANG</option><option value=\"6112\">KABUPATEN KUBU RAYA</option><option value=\"6103\">KABUPATEN LANDAK</option><option value=\"6110\">KABUPATEN MELAWI</option><option value=\"6104\">KABUPATEN MEMPAWAH</option><option value=\"6101\">KABUPATEN SAMBAS</option><option value=\"6105\">KABUPATEN SANGGAU</option><option value=\"6109\">KABUPATEN SEKADAU</option><option value=\"6107\">KABUPATEN SINTANG</option><option value=\"6171\">KOTA PONTIANAK</option><option value=\"6172\">KOTA SINGKAWANG</option>",
                "62": "<option value=\"\">-</option><option value=\"6204\">KABUPATEN BARITO SELATAN</option><option value=\"6212\">KABUPATEN BARITO TIMUR</option><option value=\"6205\">KABUPATEN BARITO UTARA</option><option value=\"6211\">KABUPATEN GUNUNG MAS</option><option value=\"6203\">KABUPATEN KAPUAS</option><option value=\"6209\">KABUPATEN KATINGAN</option><option value=\"6201\">KABUPATEN KOTAWARINGIN BARAT</option><option value=\"6202\">KABUPATEN KOTAWARINGIN TIMUR</option><option value=\"6207\">KABUPATEN LAMANDAU</option><option value=\"6213\">KABUPATEN MURUNG RAYA</option><option value=\"6210\">KABUPATEN PULANG PISAU</option><option value=\"6208\">KABUPATEN SERUYAN</option><option value=\"6206\">KABUPATEN SUKAMARA</option><option value=\"6271\">KOTA PALANGKA RAYA</option>",
                "63": "<option value=\"\">-</option><option value=\"6311\">KABUPATEN BALANGAN</option><option value=\"6303\">KABUPATEN BANJAR</option><option value=\"6304\">KABUPATEN BARITO KUALA</option><option value=\"6306\">KABUPATEN HULU SUNGAI SELATAN</option><option value=\"6307\">KABUPATEN HULU SUNGAI TENGAH</option><option value=\"6308\">KABUPATEN HULU SUNGAI UTARA</option><option value=\"6302\">KABUPATEN KOTA BARU</option><option value=\"6309\">KABUPATEN TABALONG</option><option value=\"6310\">KABUPATEN TANAH BUMBU</option><option value=\"6301\">KABUPATEN TANAH LAUT</option><option value=\"6305\">KABUPATEN TAPIN</option><option value=\"6372\">KOTA BANJAR BARU</option><option value=\"6371\">KOTA BANJARMASIN</option>",
                "64": "<option value=\"\">-</option><option value=\"6405\">KABUPATEN BERAU</option><option value=\"6402\">KABUPATEN KUTAI BARAT</option><option value=\"6403\">KABUPATEN KUTAI KARTANEGARA</option><option value=\"6404\">KABUPATEN KUTAI TIMUR</option><option value=\"6411\">KABUPATEN MAHAKAM HULU</option><option value=\"6401\">KABUPATEN PASER</option><option value=\"6409\">KABUPATEN PENAJAM PASER UTARA</option><option value=\"6471\">KOTA BALIKPAPAN</option><option value=\"6474\">KOTA BONTANG</option><option value=\"6472\">KOTA SAMARINDA</option>",
                "65": "<option value=\"\">-</option><option value=\"6502\">KABUPATEN BULUNGAN</option><option value=\"6501\">KABUPATEN MALINAU</option><option value=\"6504\">KABUPATEN NUNUKAN</option><option value=\"6503\">KABUPATEN TANA TIDUNG</option><option value=\"6571\">KOTA TARAKAN</option>",
                "71": "<option value=\"\">-</option><option value=\"7101\">KABUPATEN BOLAANG MONGONDOW</option><option value=\"7110\">KABUPATEN BOLAANG MONGONDOW SELATAN</option><option value=\"7111\">KABUPATEN BOLAANG MONGONDOW TIMUR</option><option value=\"7107\">KABUPATEN BOLAANG MONGONDOW UTARA</option><option value=\"7103\">KABUPATEN KEPULAUAN SANGIHE</option><option value=\"7104\">KABUPATEN KEPULAUAN TALAUD</option><option value=\"7102\">KABUPATEN MINAHASA</option><option value=\"7105\">KABUPATEN MINAHASA SELATAN</option><option value=\"7109\">KABUPATEN MINAHASA TENGGARA</option><option value=\"7106\">KABUPATEN MINAHASA UTARA</option><option value=\"7108\">KABUPATEN SIAU TAGULANDANG BIARO</option><option value=\"7172\">KOTA BITUNG</option><option value=\"7174\">KOTA KOTAMOBAGU</option><option value=\"7171\">KOTA MANADO</option><option value=\"7173\">KOTA TOMOHON</option>",
                "72": "<option value=\"\">-</option><option value=\"7202\">KABUPATEN BANGGAI</option><option value=\"7201\">KABUPATEN BANGGAI KEPULAUAN</option><option value=\"7211\">KABUPATEN BANGGAI LAUT</option><option value=\"7207\">KABUPATEN BUOL</option><option value=\"7205\">KABUPATEN DONGGALA</option><option value=\"7203\">KABUPATEN MOROWALI</option><option value=\"7212\">KABUPATEN MOROWALI UTARA</option><option value=\"7208\">KABUPATEN PARIGI MOUTONG</option><option value=\"7204\">KABUPATEN POSO</option><option value=\"7210\">KABUPATEN SIGI</option><option value=\"7209\">KABUPATEN TOJO UNA-UNA</option><option value=\"7206\">KABUPATEN TOLI-TOLI</option><option value=\"7271\">KOTA PALU</option>",
                "73": "<option value=\"\">-</option><option value=\"7303\">KABUPATEN BANTAENG</option><option value=\"7310\">KABUPATEN BARRU</option><option value=\"7311\">KABUPATEN BONE</option><option value=\"7302\">KABUPATEN BULUKUMBA</option><option value=\"7316\">KABUPATEN ENREKANG</option><option value=\"7306\">KABUPATEN GOWA</option><option value=\"7304\">KABUPATEN JENEPONTO</option><option value=\"7301\">KABUPATEN KEPULAUAN SELAYAR</option><option value=\"7317\">KABUPATEN LUWU</option><option value=\"7325\">KABUPATEN LUWU TIMUR</option><option value=\"7322\">KABUPATEN LUWU UTARA</option><option value=\"7308\">KABUPATEN MAROS</option><option value=\"7309\">KABUPATEN PANGKAJENE DAN KEPULAUAN</option><option value=\"7315\">KABUPATEN PINRANG</option><option value=\"7314\">KABUPATEN SIDENRENG RAPPANG</option><option value=\"7307\">KABUPATEN SINJAI</option><option value=\"7312\">KABUPATEN SOPPENG</option><option value=\"7305\">KABUPATEN TAKALAR</option><option value=\"7318\">KABUPATEN TANA TORAJA</option><option value=\"7326\">KABUPATEN TORAJA UTARA</option><option value=\"7313\">KABUPATEN WAJO</option><option value=\"7371\">KOTA MAKASSAR</option><option value=\"7373\">KOTA PALOPO</option><option value=\"7372\">KOTA PAREPARE</option>",
                "74": "<option value=\"\">-</option><option value=\"7406\">KABUPATEN BOMBANA</option><option value=\"7401\">KABUPATEN BUTON</option><option value=\"7415\">KABUPATEN BUTON SELATAN</option><option value=\"7414\">KABUPATEN BUTON TENGAH</option><option value=\"7409\">KABUPATEN BUTON UTARA</option><option value=\"7404\">KABUPATEN KOLAKA</option><option value=\"7411\">KABUPATEN KOLAKA TIMUR</option><option value=\"7408\">KABUPATEN KOLAKA UTARA</option><option value=\"7403\">KABUPATEN KONAWE</option><option value=\"7412\">KABUPATEN KONAWE KEPULAUAN</option><option value=\"7405\">KABUPATEN KONAWE SELATAN</option><option value=\"7410\">KABUPATEN KONAWE UTARA</option><option value=\"7402\">KABUPATEN MUNA</option><option value=\"7413\">KABUPATEN MUNA BARAT</option><option value=\"7407\">KABUPATEN WAKATOBI</option><option value=\"7472\">KOTA BAUBAU</option><option value=\"7471\">KOTA KENDARI</option>",
                "75": "<option value=\"\">-</option><option value=\"7501\">KABUPATEN BOALEMO</option><option value=\"7504\">KABUPATEN BONE BOLANGO</option><option value=\"7502\">KABUPATEN GORONTALO</option><option value=\"7505\">KABUPATEN GORONTALO UTARA</option><option value=\"7503\">KABUPATEN POHUWATO</option><option value=\"7571\">KOTA GORONTALO</option>",
                "76": "<option value=\"\">-</option><option value=\"7601\">KABUPATEN MAJENE</option><option value=\"7603\">KABUPATEN MAMASA</option><option value=\"7604\">KABUPATEN MAMUJU</option><option value=\"7606\">KABUPATEN MAMUJU TENGAH</option><option value=\"7605\">KABUPATEN MAMUJU UTARA</option><option value=\"7602\">KABUPATEN POLEWALI MANDAR</option>",
                "81": "<option value=\"\">-</option><option value=\"8104\">KABUPATEN BURU</option><option value=\"8109\">KABUPATEN BURU SELATAN</option><option value=\"8105\">KABUPATEN KEPULAUAN ARU</option><option value=\"8108\">KABUPATEN MALUKU BARAT DAYA</option><option value=\"8103\">KABUPATEN MALUKU TENGAH</option><option value=\"8102\">KABUPATEN MALUKU TENGGARA</option><option value=\"8101\">KABUPATEN MALUKU TENGGARA BARAT</option><option value=\"8106\">KABUPATEN SERAM BAGIAN BARAT</option><option value=\"8107\">KABUPATEN SERAM BAGIAN TIMUR</option><option value=\"8171\">KOTA AMBON</option><option value=\"8172\">KOTA TUAL</option>",
                "82": "<option value=\"\">-</option><option value=\"8201\">KABUPATEN HALMAHERA BARAT</option><option value=\"8204\">KABUPATEN HALMAHERA SELATAN</option><option value=\"8202\">KABUPATEN HALMAHERA TENGAH</option><option value=\"8206\">KABUPATEN HALMAHERA TIMUR</option><option value=\"8205\">KABUPATEN HALMAHERA UTARA</option><option value=\"8203\">KABUPATEN KEPULAUAN SULA</option><option value=\"8207\">KABUPATEN PULAU MOROTAI</option><option value=\"8208\">KABUPATEN PULAU TALIABU</option><option value=\"8271\">KOTA TERNATE</option><option value=\"8272\">KOTA TIDORE KEPULAUAN</option>",
                "91": "<option value=\"\">-</option><option value=\"9101\">KABUPATEN FAKFAK</option><option value=\"9102\">KABUPATEN KAIMANA</option><option value=\"9105\">KABUPATEN MANOKWARI</option><option value=\"9111\">KABUPATEN MANOKWARI SELATAN</option><option value=\"9110\">KABUPATEN MAYBRAT</option><option value=\"9112\">KABUPATEN PEGUNUNGAN ARFAK</option><option value=\"9108\">KABUPATEN RAJA AMPAT</option><option value=\"9107\">KABUPATEN SORONG</option><option value=\"9106\">KABUPATEN SORONG SELATAN</option><option value=\"9109\">KABUPATEN TAMBRAUW</option><option value=\"9104\">KABUPATEN TELUK BINTUNI</option><option value=\"9103\">KABUPATEN TELUK WONDAMA</option><option value=\"9171\">KOTA SORONG</option>",
                "94": "<option value=\"\">-</option><option value=\"9415\">KABUPATEN ASMAT</option><option value=\"9409\">KABUPATEN BIAK NUMFOR</option><option value=\"9413\">KABUPATEN BOVEN DIGOEL</option><option value=\"9436\">KABUPATEN DEIYAI</option><option value=\"9434\">KABUPATEN DOGIYAI</option><option value=\"9435\">KABUPATEN INTAN JAYA</option><option value=\"9403\">KABUPATEN JAYAPURA</option><option value=\"9402\">KABUPATEN JAYAWIJAYA</option><option value=\"9420\">KABUPATEN KEEROM</option><option value=\"9408\">KABUPATEN KEPULAUAN YAPEN</option><option value=\"9430\">KABUPATEN LANNY JAYA</option><option value=\"9428\">KABUPATEN MAMBERAMO RAYA</option><option value=\"9431\">KABUPATEN MAMBERAMO TENGAH</option><option value=\"9414\">KABUPATEN MAPPI</option><option value=\"9401\">KABUPATEN MERAUKE</option><option value=\"9412\">KABUPATEN MIMIKA</option><option value=\"9404\">KABUPATEN NABIRE</option><option value=\"9429\">KABUPATEN NDUGA</option><option value=\"9410\">KABUPATEN PANIAI</option><option value=\"9417\">KABUPATEN PEGUNUNGAN BINTANG</option><option value=\"9433\">KABUPATEN PUNCAK</option><option value=\"9411\">KABUPATEN PUNCAK JAYA</option><option value=\"9419\">KABUPATEN SARMI</option><option value=\"9427\">KABUPATEN SUPIORI</option><option value=\"9418\">KABUPATEN TOLIKARA</option><option value=\"9426\">KABUPATEN WAROPEN</option><option value=\"9416\">KABUPATEN YAHUKIMO</option><option value=\"9432\">KABUPATEN YALIMO</option><option value=\"9471\">KOTA JAYAPURA</option>"
            };

            const provinceSelect = document.getElementById('f_k3m');
            const districtSelect = document.getElementById('f_r9p');

            async function loadDistricts(provinceId) {
                if (!provinceId) {
                    districtSelect.innerHTML = '<option value="">- Pilih Provinsi Dulu -</option>';
                    return;
                }

                // 1. Cek dari Kamus Statis Lengkap (Persis TNBTS ID)
                if (DISTRICT_DATA[provinceId]) {
                    districtSelect.innerHTML = DISTRICT_DATA[provinceId];
                    return;
                }

                // 2. Open Source API Fallback (emsifa Wilayah Indonesia API)
                districtSelect.innerHTML = '<option value="">Memuat data Kota/Kabupaten...</option>';
                try {
                    const response = await fetch(`https://emsifa.github.io/api-wilayah-indonesia/api/regencies/${provinceId}.json`);
                    if (response.ok) {
                        const regencies = await response.json();
                        let optionsHtml = '<option value="">- Pilih Kota/Kabupaten -</option>';
                        regencies.forEach(reg => {
                            optionsHtml += `<option value="${reg.id}">${reg.name.toUpperCase()}</option>`;
                        });
                        districtSelect.innerHTML = optionsHtml;
                    } else {
                        districtSelect.innerHTML = '<option value="">- KABUPATEN/KOTA LAINNYA -</option>';
                    }
                } catch (err) {
                    districtSelect.innerHTML = '<option value="">- KABUPATEN/KOTA LAINNYA -</option>';
                }
            }

            provinceSelect.addEventListener('change', (e) => loadDistricts(e.target.value));

            let memberCount = 0;
            const maxMembers = 9;
            const membersContainer = document.getElementById('membersContainer');
            const addMemberBtn = document.getElementById('addMemberBtn');
            const memberCounterEl = document.getElementById('memberCounter');

            function updateMemberCounter() {
                memberCounterEl.textContent = memberCount;
                if (memberCount >= maxMembers) {
                    addMemberBtn.classList.add('opacity-50', 'cursor-not-allowed');
                } else {
                    addMemberBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
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
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm">
                                ${idx + 1}
                            </div>
                            <h3 class="font-bold text-slate-700">Data Anggota</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="form-label-premium">Nama Lengkap</label>
                                <input type="text" class="form-input-premium mx_a" placeholder="Nama Anggota">
                            </div>
                            <div>
                                <label class="form-label-premium">Tanggal Lahir</label>
                                <input type="text" class="form-input-premium datepicker-member mx_b" placeholder="YYYY-MM-DD">
                            </div>
                            
                            <div>
                                <label class="form-label-premium">Jenis Identitas</label>
                                <select class="form-input-premium mx_d">
                                    <option value="">- Pilih -</option>
                                    <option value="1" selected>KTP</option>
                                    <option value="2">SIM</option>
                                    <option value="3">Passport</option>
                                    <option value="4">KTM</option>
                                    <option value="5">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-premium">Nomor Identitas</label>
                                <input type="text" class="form-input-premium mx_e" placeholder="No. Identitas">
                            </div>

                            <div>
                                <label class="form-label-premium">Jenis Kelamin</label>
                                <select class="form-input-premium mx_c">
                                    <option value="">- Pilih -</option>
                                    <option value="1">Laki Laki</option>
                                    <option value="2">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-premium">No HP</label>
                                <input type="text" class="form-input-premium mx_f" placeholder="08xxxxxxxxxx">
                            </div>

                            <div class="md:col-span-2">
                                <label class="form-label-premium">Alamat</label>
                                <input type="text" class="form-input-premium mx_j" placeholder="Alamat Anggota">
                            </div>

                            <div>
                                <label class="form-label-premium">Pekerjaan</label>
                                <select class="form-input-premium mx_h">
                                    <option value="">- Pilih -</option>
                                    <option value="1">PNS</option>
                                    <option value="2" selected>Swasta</option>
                                    <option value="5">Pelajar</option>
                                    <option value="6">Mahasiswa</option>
                                    <option value="3">Lainnya</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-premium">No HP Keluarga</label>
                                <input type="text" class="form-input-premium mx_g" placeholder="08xxxxxxxxxx">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label class="form-label-premium">Negara</label>
                                <select class="form-input-premium mx_i">
                                    <option value="99" selected>Indonesia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                `;
            }

            addMemberBtn.addEventListener('click', () => {
                if (memberCount >= maxMembers) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Rombongan Penuh!',
                        text: 'Anda hanya bisa menambahkan maksimal 9 anggota (total 10 orang termasuk Ketua).',
                        confirmButtonColor: '#10b981'
                    });
                    return;
                }

                membersContainer.insertAdjacentHTML('beforeend', getMemberTemplate(memberCount));
                const newMemberCard = membersContainer.lastElementChild;
                flatpickr(newMemberCard.querySelector('.datepicker-member'), { dateFormat: "Y-m-d", disableMobile: "true" });

                memberCount++;
                updateMemberCounter();
            });

            membersContainer.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-member-btn');
                if (removeBtn) {
                    const card = removeBtn.closest('.member-card');
                    card.remove();
                    memberCount--;
                    updateMemberCounter();

                    const cards = membersContainer.querySelectorAll('.member-card');
                    cards.forEach((c, i) => {
                        c.setAttribute('data-member-index', i);
                        c.querySelector('.w-8').textContent = i + 1;
                    });
                }
            });

            function calculateAge(birthdateStr) {
                if (!birthdateStr) return 0;
                const birthDate = new Date(birthdateStr);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age;
            }

            function saveFormToCache() {
                const formData = {
                    x_c: {
                        d_dp: document.getElementById('f_d2x').value,
                        d_ar: document.getElementById('f_p6w').value,
                        n_m: document.getElementById('f_x7a').value,
                        i_t: document.getElementById('f_h1b').value,
                        i_n: document.getElementById('f_v8n').value,
                        g_d: document.getElementById('f_t5g').value,
                        b_d: document.getElementById('f_j4d').value,
                        h_p: document.getElementById('f_m2q').value,
                        a_d: document.getElementById('f_w6s').value,
                        p_r: document.getElementById('f_k3m').value,
                        d_t: document.getElementById('f_r9p').value,
                        c_r: document.getElementById('f_c8z').value,
                        p_y: document.getElementById('f_q7y').value,
                        t_hp: document.getElementById('tracking_hp').value,
                        t_pn: document.getElementById('tracking_pin').value
                    },
                    x_m: Array.from(document.querySelectorAll('.member-card')).map(card => ({
                        m_nm: card.querySelector('.mx_a').value,
                        m_bd: card.querySelector('.mx_b').value,
                        m_it: card.querySelector('.mx_d').value,
                        m_in: card.querySelector('.mx_e').value,
                        m_gd: card.querySelector('.mx_c').value,
                        m_hp: card.querySelector('.mx_f').value,
                        m_ad: card.querySelector('.mx_j').value,
                        m_jb: card.querySelector('.mx_h').value,
                        m_fh: card.querySelector('.mx_g').value,
                        m_cr: card.querySelector('.mx_i').value
                    }))
                };
                if (OBF_KEY) {
                    localStorage.setItem('semeruBookingDraft', obfuscate(formData));
                }
            }

            function loadFormFromCache() {
                const cached = localStorage.getItem('semeruBookingDraft');
                if (cached && OBF_KEY) {
                    try {
                        let formData = deobfuscate(cached);
                        if (!formData || !formData.x_c) return;

                        document.getElementById('f_d2x').value = formData.x_c.d_dp || '';
                        document.getElementById('f_p6w').value = formData.x_c.d_ar || '';
                        document.getElementById('f_x7a').value = formData.x_c.n_m || '';
                        document.getElementById('f_h1b').value = formData.x_c.i_t || '';
                        document.getElementById('f_v8n').value = formData.x_c.i_n || '';
                        document.getElementById('f_t5g').value = formData.x_c.g_d || '';
                        document.getElementById('f_j4d').value = formData.x_c.b_d || '';
                        document.getElementById('f_m2q').value = formData.x_c.h_p || '';
                        document.getElementById('f_w6s').value = formData.x_c.a_d || '';
                        document.getElementById('f_c8z').value = formData.x_c.c_r || '99';
                        document.getElementById('f_k3m').value = formData.x_c.p_r || '';
                        loadDistricts(formData.x_c.p_r);
                        document.getElementById('f_q7y').value = formData.x_c.p_y || '';
                        document.getElementById('tracking_hp').value = formData.x_c.t_hp || '';
                        document.getElementById('tracking_pin').value = formData.x_c.t_pn || '';

                        membersContainer.innerHTML = '';
                        memberCount = 0;

                        if (formData.x_m && formData.x_m.length > 0) {
                            formData.x_m.forEach((m, idx) => {
                                membersContainer.insertAdjacentHTML('beforeend', getMemberTemplate(idx));
                                const card = membersContainer.lastElementChild;
                                card.querySelector('.mx_a').value = m.m_nm || '';
                                card.querySelector('.mx_b').value = m.m_bd || '';
                                card.querySelector('.mx_d').value = m.m_it || '1';
                                card.querySelector('.mx_e').value = m.m_in || '';
                                card.querySelector('.mx_c').value = m.m_gd || '';
                                card.querySelector('.mx_f').value = m.m_hp || '';
                                card.querySelector('.mx_j').value = m.m_ad || '';
                                card.querySelector('.mx_h').value = m.m_jb || '2';
                                card.querySelector('.mx_g').value = m.m_fh || '';
                                card.querySelector('.mx_i').value = m.m_cr || '99';
                                flatpickr(card.querySelector('.datepicker-member'), { dateFormat: "Y-m-d", disableMobile: "true" });
                            });
                            memberCount = formData.x_m.length;
                            updateMemberCounter();
                        }
                    } catch (e) {
                        console.error('Failed to parse cache', e);
                    }
                }
            }

            document.getElementById('bookingForm').addEventListener('input', saveFormToCache);
            document.getElementById('bookingForm').addEventListener('change', saveFormToCache);

            fetch(API_BASE_URL + '/config.php')
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data.obf_key) {
                        OBF_KEY = res.data.obf_key;
                        loadFormFromCache();
                    }
                })
                .catch(err => console.error("Gagal mengambil konfigurasi keamanan.", err));

            document.getElementById('resetBtn').addEventListener('click', () => {
                Swal.fire({
                    title: 'Ulangi dari Awal?',
                    text: 'Yakin ingin menghapus semuanya?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        localStorage.removeItem('semeruBookingDraft');
                        window.location.reload();
                    }
                });
            });

            function validateCustomer() {
                const requiredFields = [
                    { id: 'f_x7a', name: 'Nama Ketua' },
                    { id: 'f_h1b', name: 'Jenis Identitas' },
                    { id: 'f_v8n', name: 'Nomor Identitas' },
                    { id: 'f_t5g', name: 'Jenis Kelamin' },
                    { id: 'f_j4d', name: 'Tanggal Lahir' },
                    { id: 'f_m2q', name: 'No HP' },
                    { id: 'f_w6s', name: 'Alamat Lengkap' },
                    { id: 'f_q7y', name: 'Metode Pembayaran' },
                    { id: 'tracking_hp', name: 'No HP Tracking' },
                    { id: 'tracking_pin', name: 'PIN Tracking' }
                ];

                for (let field of requiredFields) {
                    const el = document.getElementById(field.id);
                    if (!el.value.trim()) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ada yang Terlewat',
                            text: `Mohon isi bagian "${field.name}" yang masih kosong pada Data Ketua.`,
                            confirmButtonColor: '#10b981'
                        });
                        el.focus();
                        return false;
                    }
                }

                const memberCards = document.querySelectorAll('.member-card');
                if (memberCards.length < 1) {
                    Swal.fire({ icon: 'error', title: 'Pendaki Kurang', text: 'Minimal harus ada 2 orang (1 Ketua dan 1 Anggota). Silakan tambah anggota.', confirmButtonColor: '#10b981' });
                    return false;
                }

                for (let i = 0; i < memberCards.length; i++) {
                    const card = memberCards[i];
                    const mName = card.querySelector('.mx_a').value;
                    const mBirthdate = card.querySelector('.mx_b').value;
                    const mIdentity = card.querySelector('.mx_e').value;

                    if (!mName || !mBirthdate || !mIdentity) {
                        Swal.fire({ icon: 'error', title: 'Data Anggota Belum Lengkap', text: `Mohon lengkapi Nama, Tanggal Lahir, dan Nomor Identitas pada Anggota ke-${i + 1}.`, confirmButtonColor: '#10b981' });
                        return false;
                    }
                }

                return true;
            }

            function collectData(bookingId, targetDate, arrivalDate) {
                const customer = {
                    n_m: document.getElementById('f_x7a').value,
                    h_p: document.getElementById('f_m2q').value,
                    i_n: document.getElementById('f_v8n').value,
                    b_d: document.getElementById('f_j4d').value,
                    a_d: document.getElementById('f_w6s').value,
                    p_r: document.getElementById('f_k3m').value,
                    d_t: document.getElementById('f_r9p').value,
                    g_d: document.getElementById('f_t5g').value,
                    i_t: document.getElementById('f_h1b').value,
                    c_r: document.getElementById('f_c8z').value,
                    b_k: document.getElementById('f_q7y').value,
                    t_hp: document.getElementById('tracking_hp').value,
                    t_pn: document.getElementById('tracking_pin').value
                };

                const members = [];
                document.querySelectorAll('.member-card').forEach(card => {
                    members.push({
                        m_nm: card.querySelector('.mx_a').value,
                        m_bd: card.querySelector('.mx_b').value,
                        m_gd: card.querySelector('.mx_c').value,
                        m_it: card.querySelector('.mx_d').value,
                        m_in: card.querySelector('.mx_e').value,
                        m_hp: card.querySelector('.mx_f').value,
                        m_al: card.querySelector('.mx_j').value,
                        m_jb: card.querySelector('.mx_h').value,
                        m_hk: card.querySelector('.mx_g').value,
                        m_cr: card.querySelector('.mx_i').value
                    });
                });

                return { b_id: bookingId, t_dt: targetDate, a_dt: arrivalDate, x_c: customer, x_m: members };
            }

            async function finalizeAction(bookings) {
                const submitBtn = document.getElementById('bookingBtn');
                const originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim data...';

                try {
                    const response = await fetch('./api/bookings.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _p: obfuscate(bookings[0]) })
                    });

                    const result = await response.json();

                    if (response.ok && result.success) {
                        localStorage.removeItem('semeruBookingDraft');
                        Swal.fire({
                            icon: 'success',
                            title: 'Pendaftaran Sukses!',
                            html: `<p>Yeay! Data pendaftaran berhasil tersimpan di database <strong>auto</strong>.</p><p class="mt-2 font-mono bg-slate-100 px-3 py-2 rounded-lg text-sm">Kode Pendaftaran: <strong>${result.booking_id}</strong></p>`,
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(result.message || 'Terjadi kesalahan sistem.');
                    }
                } catch (err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi Kendala',
                        text: err.message || 'Gagal terhubung ke API.',
                        confirmButtonColor: '#10b981'
                    });
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            }

            document.getElementById('bookingBtn').addEventListener('click', () => {
                const terms = document.getElementById('termsCheckbox');
                if (!terms.checked) {
                    Swal.fire({ icon: 'warning', text: 'Tolong centang kotak persetujuan Persyaratan di bagian bawah.', confirmButtonColor: '#10b981' });
                    return;
                }

                if (!validateCustomer()) return;

                const targetDate = document.getElementById('f_d2x').value;
                const arrivalDate = document.getElementById('f_p6w').value;

                if (!targetDate || !arrivalDate) {
                    Swal.fire({ icon: 'error', text: 'Mohon pilih tanggal keberangkatan dan kepulangan Anda.', confirmButtonColor: '#10b981' });
                    return;
                }

                const totalMembers = document.querySelectorAll('.member-card').length;

                Swal.fire({
                    title: 'Konfirmasi Data',
                    html: `
                        <div class="text-left text-sm text-slate-600 space-y-3 mb-4 mt-2">
                            <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                                <p class="mb-1"><strong>Ketua Kelompok:</strong> ${document.getElementById('f_x7a').value}</p>
                                <p class="mb-1"><strong>Keberangkatan:</strong> ${targetDate}</p>
                                <p class="mb-1"><strong>Kepulangan:</strong> ${arrivalDate}</p>
                                <p class="mb-1"><strong>Total Rombongan:</strong> ${totalMembers + 1} Orang</p>
                            </div>
                        </div>
                    `,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Simpan ke Database',
                    cancelButtonText: 'Periksa Lagi'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const bookings = [collectData("BOOKING_" + Date.now(), targetDate, arrivalDate)];
                        finalizeAction(bookings);
                    }
                });
            });

        });
    </script>
</body>

</html>
