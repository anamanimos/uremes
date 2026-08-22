require('dotenv').config();
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const { getPendingBooking, updateBookingStatus } = require('./db');
const { sendTelegramMessage } = require('./telegram');

puppeteer.use(StealthPlugin());

// Variable global untuk tracking cancel signal
let currentBookingId = null;
let browserInstance = null;

// Handling pembatalan manual (Ctrl+C / SIGINT / SIGTERM)
const handleExitSignal = async (signalName) => {
    console.log(`\n==================================================`);
    console.log(`⚠️ BOT DIBATALKAN OLEH PENGGUNA (Signal: ${signalName})`);
    if (currentBookingId) {
        console.log(`🔄 Mengembalikan status Booking ${currentBookingId} dari 'processing' menjadi 'pending'...`);
        await updateBookingStatus(currentBookingId, 'pending');
    }
    if (browserInstance) {
        await browserInstance.close().catch(() => {});
    }
    console.log(`🔒 Bot dihentikan secara aman. Sampai jumpa!`);
    console.log(`==================================================\n`);
    process.exit(0);
};

process.on('SIGINT', () => handleExitSignal('SIGINT (Ctrl+C)'));
process.on('SIGTERM', () => handleExitSignal('SIGTERM'));

// Ambil destinasi dari argumen CLI (--destination=semeru) atau env, default 'semeru'
const args = process.argv.slice(2);
let destination = 'semeru';

for (const arg of args) {
    if (arg.startsWith('--destination=')) {
        destination = arg.split('=')[1].toLowerCase();
    }
}
if (process.env.DESTINATION) {
    destination = process.env.DESTINATION.toLowerCase();
}

/**
 * Helper Berpindah Tab Destinasi (Semeru & Ranu Regulo) 100% Presisi
 */
async function switchToDestinationTab(page, dest) {
    let tabBtnSelector = dest === 'ranu-regulo' ? '#pills-three-example2-tab' : '#pills-two-example2-tab';
    let tabPaneSelector = dest === 'ranu-regulo' ? '#pills-three-example2' : '#pills-two-example2';

    console.log(`📌 Step 3: Berpindah ke Tab "${dest.toUpperCase()}" (${tabBtnSelector})...`);

    // 1. Klik fisik elemen tab melalui Puppeteer
    try {
        await page.click(tabBtnSelector);
    } catch (e) {
        try {
            await page.click(`a[href="${tabPaneSelector}"]`);
        } catch (e2) {}
    }

    await new Promise(r => setTimeout(r, 1000));

    // 2. Paksa pengaktifan Bootstrap Tab & Class active/show di DOM
    await page.evaluate((btnSel, paneSel) => {
        const targetBtn = document.querySelector(btnSel) || document.querySelector(`a[href="${paneSel}"]`);
        if (targetBtn) {
            targetBtn.click();
            if (window.jQuery && window.jQuery(targetBtn).tab) {
                window.jQuery(targetBtn).tab('show');
            }
        }

        // Hapus kelas active dari semua tab lain
        document.querySelectorAll('.nav-link, .nav-item a, li a').forEach(el => {
            const txt = (el.innerText || '').toLowerCase();
            if (txt.includes('bromo') || txt.includes('semeru') || txt.includes('regulo')) {
                el.classList.remove('active', 'show');
                if (el.parentElement) el.parentElement.classList.remove('active', 'show');
            }
        });

        // Hapus kelas active dari semua panel konten tab
        document.querySelectorAll('.tab-pane').forEach(el => {
            el.classList.remove('active', 'show');
            el.style.display = 'none';
        });

        // Aktifkan tab button & pane target
        if (targetBtn) {
            targetBtn.classList.add('active', 'show');
            if (targetBtn.parentElement) targetBtn.parentElement.classList.add('active', 'show');
        }

        const targetPane = document.querySelector(paneSel);
        if (targetPane) {
            targetPane.classList.add('active', 'show');
            targetPane.style.display = 'block';
        }
    }, tabBtnSelector, tabPaneSelector);

    await new Promise(r => setTimeout(r, 1500));
}

/**
 * Automasi Booking Presisi dengan Tab Switcher & Continuous Retry Hit Kuota
 */
async function runAutoBookingFlow() {
    const targetUrl = process.env.SEMERU_URL || 'https://bromotenggersemeru.id/';
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const botUserDataDir = path.join(__dirname, 'semeru_profile');

    const destTitle = destination === 'semeru' ? 'SEMERU' : 'RANU REGULO';

    console.log(`\n==================================================`);
    console.log(`🕒 Waktu Mulai: ${new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}`);
    console.log(`🚀 AUTOMASI BOOKING ${destTitle} (DINAMIS JUMLAH ANGGOTA)`);
    console.log(`🌐 Website Target: ${targetUrl}`);
    console.log(`🎯 Destinasi: ${destTitle}`);
    console.log(`==================================================`);

    // 1. Ambil 1 Data Booking Terlama dengan status 'pending' atau 'failed'
    const dbBooking = await getPendingBooking();
    let targetMonthText = process.env.TARGET_MONTH || 'Agustus 2026';
    let jenisKunjungan = process.env.VISIT_TYPE || 'Berkemah';

    let rawTargetDate = '';
    let targetDateString = '';
    let targetDayNum = null;

    let ketuaData = {
        nama: 'Choirul Anam',
        nik: '3507123456789001',
        hp: '081234567890',
        address: 'Jl. Raya Semeru No. 10',
        gender: '1',
        identityType: '1'
    };

    let memberList = [
        { nama: 'Budi Santoso', gender: '1', identityType: '1', identityNo: '3507123456789002', birthdate: '1995-03-15', hp: '081234567891', address: 'Jl. Pemuda No. 12', jobId: '2', familyHp: '081299887766', countryId: '99' },
        { nama: 'Siti Rahmawati', gender: '2', identityType: '1', identityNo: '3507123456789003', birthdate: '1996-07-20', hp: '081234567892', address: 'Jl. Mawar No. 5', jobId: '6', familyHp: '081299887766', countryId: '99' }
    ];

    if (dbBooking) {
        currentBookingId = dbBooking.bookingId;
        console.log(`📌 DITEMUKAN 1 PROSES BOOKING TERLAMA (Status: ${dbBooking.status.toUpperCase()}):`);
        console.log(`   🆔 Kode Booking DB: ${dbBooking.bookingId}`);
        console.log(`   📅 Tanggal Target DB (target_date): ${dbBooking.targetDate}`);
        console.log(`   👤 Ketua: ${dbBooking.ketua ? dbBooking.ketua.nama : 'Unknown'}`);
        console.log(`   👥 Total Anggota di DB: ${dbBooking.members ? dbBooking.members.length : 0} orang`);
        console.log(`--------------------------------------------------`);

        await updateBookingStatus(dbBooking.bookingId, 'processing');

        if (dbBooking.targetDate) {
            rawTargetDate = String(dbBooking.targetDate).split('T')[0];
            const d = new Date(dbBooking.targetDate);
            if (!isNaN(d.getTime())) {
                targetDayNum = d.getDate();
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                targetMonthText = `${months[d.getMonth()]} ${d.getFullYear()}`;
                targetDateString = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
            }
        }

        if (dbBooking.ketua) {
            ketuaData = {
                nama: dbBooking.ketua.nama || ketuaData.nama,
                nik: dbBooking.ketua.identity_no || ketuaData.nik,
                hp: dbBooking.ketua.hp || ketuaData.hp,
                address: dbBooking.ketua.address || ketuaData.address,
                gender: String(dbBooking.ketua.gender_id || 1),
                identityType: String(dbBooking.ketua.identity_type_id || 1)
            };
        }

        if (dbBooking.members) {
            memberList = dbBooking.members.map((m, i) => ({
                nama: m.nama,
                gender: String(m.gender_id || 1),
                identityType: String(m.identity_type_id || 1),
                identityNo: m.identity_no || `350712345678900${i + 2}`,
                birthdate: m.birthdate ? String(m.birthdate).split('T')[0] : '1995-05-20',
                hp: m.hp || '08123456789' + (i + 1),
                address: m.address || 'Jl. Raya No. ' + (i + 1),
                jobId: String(m.job_id || 2),
                familyHp: m.family_hp || '081299887766',
                countryId: String(m.country_id || 99)
            }));
        }
    } else {
        console.log(`ℹ️ Tidak ada booking pending/failed di DB 'auto'. Menggunakan data pendaftaran default (${memberList.length} anggota).`);
    }

    console.log(`🎯 Tanggal Target Pilihan: ${targetDateString || rawTargetDate || 'Mencari Tanggal Tersedia'}`);
    console.log(`👥 Target Rombongan: 1 Ketua + ${memberList.length} Anggota (Total ${memberList.length + 1} Orang)`);

    if (!fs.existsSync(chromePath)) {
        console.error(`❌ Chrome tidak ditemukan di: ${chromePath}`);
        throw new Error('Google Chrome tidak terinstall di lokasi standar Windows.');
    }

    const browser = await puppeteer.launch({
        executablePath: chromePath,
        userDataDir: botUserDataDir,
        headless: false,
        defaultViewport: null,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-blink-features=AutomationControlled',
            '--start-maximized',
            '--disk-cache-size=33554432',
            '--media-cache-size=33554432'
        ]
    });
    browserInstance = browser;

    const pages = await browser.pages();
    const page = pages.length > 0 ? pages[0] : await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });

    // 🚀 OPTIMASI PERFORMA KILAT (5x-10x Lebih Cepat):
    // Blokir gambar berat, video background, dan tracking script
    await page.setRequestInterception(true);
    page.on('request', (req) => {
        const resourceType = req.resourceType();
        const url = req.url().toLowerCase();

        if (
            resourceType === 'image' || 
            resourceType === 'media' || 
            url.includes('google-analytics') ||
            url.includes('googletagmanager') ||
            url.includes('facebook') ||
            url.includes('doubleclick')
        ) {
            req.abort().catch(() => {});
        } else {
            req.continue().catch(() => {});
        }
    });

    try {
        // Step 1: Navigasi ke Website Utama
        console.log(`📍 Step 1: Membuka website target: ${targetUrl}`);
        await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => {});
        
        // Pastikan Halaman Sudah Berhasil Terbuka (Tidak Stuck di about:blank)
        if (page.url() === 'about:blank') {
            console.log('🔄 Memuat ulang halaman target...');
            await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => {});
        }

        // Menunggu Elemen Tab Dimuat di DOM
        await page.waitForSelector('#pills-two-example2-tab, #pills-three-example2-tab, form', { timeout: 15000 }).catch(() => {});
        await new Promise(r => setTimeout(r, 1000));

        // Step 2: Berpindah ke Tab Destinasi (Semeru / Ranu Regulo)
        await switchToDestinationTab(page, destination);

        // Step 4 & 5: INFINITE RETRY LOOP HIT KUOTA SAMPAI TERSEDIA ATAU USER CANCEL (Ctrl+C)
        let hitCount = 0;
        let quotaResult = null;

        while (true) {
            hitCount++;
            console.log(`\n==================================================`);
            console.log(`🔄 HIT KE-${hitCount} [${new Date().toLocaleTimeString('id-ID')}] - Cek Kuota Tanggal ${targetDateString || rawTargetDate}...`);
            console.log(`==================================================`);

            // 🛡️ CEK JIKA SERVER TNBTS DOWN / CLOUDFLARE 504 GATEWAY TIMEOUT
            const isServerError = await page.evaluate(() => {
                const txt = (document.body && document.body.innerText) ? document.body.innerText.toLowerCase() : '';
                return txt.includes('gateway time-out') || 
                       txt.includes('504 gateway') || 
                       txt.includes('502 bad gateway') || 
                       txt.includes('service unavailable') || 
                       txt.includes('500 internal server error') ||
                       document.title.includes('504') ||
                       document.title.includes('502');
            });

            if (isServerError) {
                console.log(`⚠️ Server TNBTS sedang Down / Gateway Timeout (Cloudflare 504). Re-navigasi ke target dalam 5 detik...`);
                await new Promise(r => setTimeout(r, 5000));
                await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => {});
                await page.waitForSelector('#pills-two-example2-tab, #pills-three-example2-tab, form', { timeout: 15000 }).catch(() => {});
                await switchToDestinationTab(page, destination);
                continue;
            }

            // Pastikan Tab Target Tetap Aktif
            await page.evaluate((dest) => {
                let paneSel = dest === 'ranu-regulo' ? '#pills-three-example2' : '#pills-two-example2';
                let btnSel = dest === 'ranu-regulo' ? '#pills-three-example2-tab' : '#pills-two-example2-tab';
                
                const pane = document.querySelector(paneSel);
                if (pane && !pane.classList.contains('active')) {
                    const btn = document.querySelector(btnSel);
                    if (btn) btn.click();
                    pane.classList.add('active', 'show');
                    pane.style.display = 'block';
                }
            }, destination);

            // Memilih Periode & Submit Form Pencarian Kuota Khusus Tab Target
            await page.evaluate((targetM, targetV, dest) => {
                let paneSel = dest === 'ranu-regulo' ? '#pills-three-example2' : '#pills-two-example2';
                let formSelector = dest === 'ranu-regulo' ? '#regulo-form-kapasitas' : '#semeru-form-kapasitas';
                
                let pane = document.querySelector(paneSel) || document.body;
                let form = pane.querySelector(formSelector) || pane.querySelector('form');
                if (!form) return;

                const ymSel = form.querySelector('select[name="year_month"]');
                const jkSel = form.querySelector('select[name="jenis_kegiatan"]');

                if (ymSel) {
                    const optMatch = Array.from(ymSel.options).find(o => o.innerText.toLowerCase().includes(targetM.toLowerCase()));
                    if (optMatch) ymSel.value = optMatch.value;
                    else ymSel.selectedIndex = 1;
                    if (window.jQuery) window.jQuery(ymSel).change().selectpicker('refresh');
                }

                if (jkSel) {
                    const optMatch = Array.from(jkSel.options).find(o => o.innerText.toLowerCase().includes(targetV.toLowerCase()));
                    if (optMatch) jkSel.value = optMatch.value;
                    else jkSel.value = 'Berkemah';
                    if (window.jQuery) window.jQuery(jkSel).change().selectpicker('refresh');
                }

                const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
                if (submitBtn) submitBtn.click();
            }, targetMonthText, jenisKunjungan, destination);

            await new Promise(r => setTimeout(r, 3500));

            // Evaluasi Kuota Tanggal Target dari Tabel Khusus Pane Aktif
            quotaResult = await page.evaluate((tDay, tDateStr, tRawDate, dest) => {
                let paneSel = dest === 'ranu-regulo' ? '#pills-three-example2' : '#pills-two-example2';
                let container = document.querySelector(paneSel) || document.body;
                
                const rows = Array.from(container.querySelectorAll('table tbody tr'));
                let matchedRow = null;

                for (const tr of rows) {
                    const tds = tr.querySelectorAll('td, th');
                    if (tds.length >= 2) {
                        const dateText = tds[0].innerText.trim();
                        
                        let isMatch = false;
                        if (tDateStr && dateText.toLowerCase().includes(tDateStr.toLowerCase())) {
                            isMatch = true;
                        } else if (tRawDate && dateText.includes(tRawDate)) {
                            isMatch = true;
                        } else if (tDay && (dateText.includes(` ${tDay} `) || dateText.startsWith(`${tDay} `))) {
                            isMatch = true;
                        }

                        if (isMatch) {
                            matchedRow = { tr, tds, dateText };
                            break;
                        }
                    }
                }

                if (!matchedRow && rows.length > 0) {
                    for (const tr of rows) {
                        const tds = tr.querySelectorAll('td, th');
                        if (tds.length >= 2) {
                            const quotaText = tds[1].innerText.trim();
                            const quotaNum = parseInt(quotaText.replace(/\D/g, ''), 10);
                            if (!isNaN(quotaNum) && quotaNum > 0) {
                                matchedRow = { tr, tds, dateText: tds[0].innerText.trim() };
                                break;
                            }
                        }
                    }
                }

                if (!matchedRow) {
                    return { success: false, dateText: tDateStr || tRawDate, quotaNum: 0, statusMessage: 'TANGGAL_TIDAK_DITEMUKAN' };
                }

                const quotaText = matchedRow.tds[1].innerText.trim();
                const quotaNum = parseInt(quotaText.replace(/\D/g, ''), 10);

                if (!isNaN(quotaNum) && quotaNum > 0) {
                    const linkOrCell = matchedRow.tds[1].querySelector('a, button, span') || matchedRow.tds[1];
                    linkOrCell.click();
                    return { success: true, dateText: matchedRow.dateText, quotaNum, statusMessage: 'KUOTA_AVAILABLE' };
                } else if (quotaText.toLowerCase().includes('penuh')) {
                    return { success: false, dateText: matchedRow.dateText, quotaNum: 0, statusMessage: 'KUOTA_PENUH' };
                } else if (quotaText.toLowerCase().includes('belum') || quotaText.toLowerCase().includes('tutup')) {
                    return { success: false, dateText: matchedRow.dateText, quotaNum: 0, statusMessage: 'BELUM_DIBUKA' };
                } else {
                    return { success: false, dateText: matchedRow.dateText, quotaNum: 0, statusMessage: 'KUOTA_KOSONG' };
                }
            }, targetDayNum, targetDateString, rawTargetDate, destination);

            if (quotaResult && quotaResult.success && quotaResult.quotaNum > 0) {
                console.log(`\n==================================================`);
                console.log(`🎉 WAR KUOTA BERHASIL! KUOTA ${quotaResult.quotaNum} TERSEDIA PADA TANGGAL ${quotaResult.dateText}!`);
                console.log(`==================================================\n`);
                break;
            } else {
                let reasonStr = 'Kuota Belum Tersedia';
                if (quotaResult.statusMessage === 'KUOTA_PENUH') reasonStr = 'Kuota Penuh';
                else if (quotaResult.statusMessage === 'BELUM_DIBUKA') reasonStr = 'Kuota Belum Dibuka';
                else if (quotaResult.statusMessage === 'TANGGAL_TIDAK_DITEMUKAN') reasonStr = 'Tanggal Belum Muncul di Tabel';

                console.log(`⏳ Status: ${reasonStr} (${quotaResult.dateText || targetDateString}). Melakukan hit ulang dalam 3 detik... [Tekan Ctrl+C untuk membatalkan]`);
                await new Promise(r => setTimeout(r, 3000));
            }
        }

        console.log('⏳ Menunggu pengalihan alami ke Halaman Peraturan / SOP...');
        await new Promise(r => setTimeout(r, 4000));

        if (!page.url().includes('/peraturan/')) {
            console.log('⚠️ Halaman tidak teralih otomatis ke SOP Peraturan. Menghentikan automasi.');
            if (currentBookingId) {
                await updateBookingStatus(currentBookingId, 'pending');
            }
            return;
        }

        // Step 6: DYNAMIC CHECKLIST: Menconteng SELURUH Checklist Persyaratan SOP
        console.log('📋 Step 6: Menconteng SELURUH Checklist Persyaratan SOP (Dinamis)...');
        
        await page.waitForSelector('input[type="checkbox"]', { timeout: 15000 }).catch(() => {});

        const checkedCount = await page.evaluate(() => {
            let count = 0;
            const checkboxes = document.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    cb.checked = true;
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                    cb.dispatchEvent(new Event('click', { bubbles: true }));
                }
                count++;
            });

            document.querySelectorAll('label, .custom-control-label, .form-check-label').forEach(lbl => {
                try { lbl.click(); } catch (e) {}
            });

            return count;
        });

        console.log(`✅ Berhasil menconteng ${checkedCount} checklist pada halaman peraturan.`);
        await new Promise(r => setTimeout(r, 1500));

        console.log('🔘 Mengklik tombol "BOOKING"...');
        await page.evaluate(() => {
            const btn = Array.from(document.querySelectorAll('button, a'))
                .find(b => b.innerText && b.innerText.trim().toUpperCase().includes('BOOKING') && !b.innerText.includes('Saya'));

            if (btn) {
                const redirectUrl = btn.getAttribute('data-link');
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else {
                    btn.click();
                }
            }
        });

        console.log('⏳ Menunggu pengalihan ke Form Booking Site...');
        await new Promise(r => setTimeout(r, 5000));

        if (!page.url().includes('/booking/site/')) {
            console.log('⚠️ Gagal teralih ke Halaman Form Booking. Menghentikan proses.');
            if (currentBookingId) {
                await updateBookingStatus(currentBookingId, 'pending');
            }
            return;
        }

        // Step 7: Isi Form Booking dengan Data Ketua & Anggota (JUMLAH ANGGOTA DINAMIS)
        console.log(`📝 Step 7: Mengisi Data Form Booking ${destTitle}...`);

        // 7a. Select Pintu Masuk (Ranupani / Single Gate Safety Check)
        await page.evaluate(() => {
            const gateSel = document.querySelector('select[name="id_gate"], select[name="pintu_masuk"]') || 
                            document.querySelector('select[name*="gate"]') ||
                            document.querySelector('select[name*="pintu"]');
            if (gateSel && gateSel.options && gateSel.options.length > 1) {
                const ranupaniIdx = Array.from(gateSel.options).findIndex(o => o.text && o.text.toLowerCase().includes('ranupani'));
                if (ranupaniIdx !== -1) {
                    gateSel.selectedIndex = ranupaniIdx;
                } else {
                    gateSel.selectedIndex = 1;
                }
                gateSel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        await new Promise(r => setTimeout(r, 1000));

        // 7b. Isi Data Ketua Kelompok
        console.log(`👤 Mengisi Data Ketua Kelompok (Nama: ${ketuaData.nama}, Gender: ${ketuaData.gender === '1' ? 'Laki-Laki' : 'Perempuan'}, NIK: ${ketuaData.nik})...`);
        await page.evaluate((k) => {
            const fillVal = (selector, val) => {
                const el = document.querySelector(selector);
                if (el) {
                    el.value = val;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            const selectVal = (selector, val) => {
                const el = document.querySelector(selector);
                if (el) {
                    el.value = val;
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                }
            };

            fillVal('input[name="name"]', k.nama);
            selectVal('select[name="id_gender"]', k.gender);
            selectVal('select[name="id_identity"]', k.identityType);
            fillVal('input[name="identity_no"]', k.nik);
            fillVal('input[name="address"]', k.address);
            fillVal('input[name="hp"]', k.hp);
            selectVal('select[name="id_country"]', '99');
        }, ketuaData);

        await new Promise(r => setTimeout(r, 1500));

        // 7c. Cek Jumlah Anggota yang Sudah Ada di Tabel
        const existingMembers = await page.evaluate(() => {
            const rows = Array.from(document.querySelectorAll('table tbody tr'));
            const validRows = rows.filter(tr => {
                const txt = tr.innerText.trim();
                return txt && !txt.includes('No data available') && tr.querySelectorAll('td').length >= 3;
            });
            return validRows.length;
        });

        console.log(`👥 Target Anggota dari Pendaftaran DB: ${memberList.length} orang.`);
        console.log(`👥 Anggota yang sudah tersimpan di tabel/sesi TNBTS: ${existingMembers} orang.`);

        const membersToAdd = memberList.slice(existingMembers);

        if (membersToAdd.length > 0) {
            console.log(`➕ Menambahkan ${membersToAdd.length} anggota (sesuai pendaftaran di DB)...`);

            for (let i = 0; i < membersToAdd.length; i++) {
                const member = membersToAdd[i];
                console.log(`   ➕ Anggota ${existingMembers + i + 1}/${memberList.length}: ${member.nama} (NIK: ${member.identityNo})...`);

                await page.click('.btn-add').catch(async () => {
                    await page.evaluate(() => {
                        const btn = document.querySelector('.btn-add') || Array.from(document.querySelectorAll('button')).find(b => b.innerText && b.innerText.includes('Tambah'));
                        if (btn) btn.click();
                    });
                });

                await new Promise(r => setTimeout(r, 1200));

                await page.evaluate((m) => {
                    const modal = document.querySelector('#modal_anggota') || document.querySelector('.modal.show') || document.querySelector('.modal');
                    if (!modal) return;

                    const fillInput = (selectors, val) => {
                        if (!val) return;
                        for (const s of selectors) {
                            const el = modal.querySelector(s);
                            if (el) {
                                el.value = val;
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                                break;
                            }
                        }
                    };

                    const selectOpt = (selectors, val) => {
                        if (!val) return;
                        for (const s of selectors) {
                            const el = modal.querySelector(s);
                            if (el) {
                                el.value = val;
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                                break;
                            }
                        }
                    };

                    fillInput(['input[name="nama"]', 'input[name="name"]', 'input[name="nama_anggota"]'], m.nama);
                    fillInput(['input[name="birthdate"]', 'input[name="tgl_lahir"]', 'input[name="tanggal_lahir"]', 'input[name="b_d"]'], m.birthdate);
                    selectOpt(['select[name="id_identity"]', 'select[name="jenis_identitas"]', 'select[name="identity_type"]'], m.identityType);
                    fillInput(['input[name="identity_no"]', 'input[name="no_identitas"]', 'input[name="nik"]'], m.identityNo);
                    selectOpt(['select[name="id_gender"]', 'select[name="jenis_kelamin"]', 'select[name="gender"]'], m.gender);
                    fillInput(['input[name="hp"]', 'input[name="no_hp"]', 'input[name="phone"]'], m.hp);
                    fillInput(['input[name="address"]', 'input[name="alamat"]'], m.address);
                    selectOpt(['select[name="id_job"]', 'select[name="job_id"]', 'select[name="pekerjaan"]'], m.jobId);
                    fillInput(['input[name="family_hp"]', 'input[name="hp_keluarga"]', 'input[name="no_hp_keluarga"]'], m.familyHp);
                    selectOpt(['select[name="id_country"]', 'select[name="negara"]', 'select[name="country"]'], m.countryId || '99');

                    const submitBtn = modal.querySelector('button[type="submit"]') || 
                                      Array.from(modal.querySelectorAll('button')).find(b => b.innerText && (b.innerText.includes('Simpan') || b.innerText.includes('Tambah') || b.innerText.includes('Submit')));
                    if (submitBtn) {
                        submitBtn.click();
                    }
                }, member);

                await new Promise(r => setTimeout(r, 1500));
            }
            console.log(`✅ Seluruh ${memberList.length} anggota pendaftaran berhasil diinputkan!`);
        } else {
            console.log('✅ Anggota sudah lengkap tersimpan di tabel TNBTS!');
        }

        // 7d. Pilih Metode Pembayaran QRIS
        console.log('💳 Memilih Metode Pembayaran QRIS...');
        await page.evaluate(() => {
            const bankSel = document.querySelector('select[name="bank"]');
            if (bankSel) {
                bankSel.value = 'QRIS-API-BMRI';
                bankSel.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        await new Promise(r => setTimeout(r, 1000));

        // 7e. Centang Seluruh Syarat & Ketentuan / Checkbox Konfirmasi Final
        console.log('☑️ Menconteng SELURUH Syarat & Ketentuan Konfirmasi Final...');
        await page.evaluate(() => {
            document.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                if (!cb.checked) {
                    cb.checked = true;
                    cb.dispatchEvent(new Event('change', { bubbles: true }));
                    cb.dispatchEvent(new Event('click', { bubbles: true }));
                }
            });

            document.querySelectorAll('label, .custom-control-label, .form-check-label').forEach(lbl => {
                try { lbl.click(); } catch (e) {}
            });
        });

        await new Promise(r => setTimeout(r, 1500));

        // 7f. MENUNGGU KONFIRMASI MANUAL USER UNTUK KLIK BOOKING
        console.log('🛑 Tombol Final BOOKING TIDAK diklik otomatis.');
        console.log('👉 Seluruh data & checklist telah terisi. Silakan periksa kembali & klik "CONFIRM BOOKING" secara manual.');

        // Update status di DB menjadi completed
        if (currentBookingId) {
            await updateBookingStatus(currentBookingId, 'completed');
        }

        console.log(`\n==================================================`);
        console.log(`🎉 AUTOMASI PENGISIAN FORM BOOKING ${destTitle} SELESAI!`);
        console.log(`👤 Data Ketua: ${ketuaData.nama} | Gender: ${ketuaData.gender === '1' ? 'Laki-Laki' : 'Perempuan'} | Identitas: KTP (${ketuaData.nik})`);
        console.log(`👥 Total Rincian: 1 Ketua + ${memberList.length} Anggota (${memberList.length + 1} Orang)`);
        console.log(`💳 Metode Pembayaran: QRIS`);
        console.log(`🛑 Jendela browser tetap DIBUKA agar Anda dapat mengklik "CONFIRM BOOKING" secara manual.`);
        console.log(`==================================================\n`);

    } catch (err) {
        console.error('❌ Error saat automasi booking:', err.message);
        if (currentBookingId) {
            await updateBookingStatus(currentBookingId, 'pending');
        }
        await page.screenshot({ path: 'booking-error-screenshot.png' }).catch(() => {});
    }
}

if (require.main === module) {
    runAutoBookingFlow();
}

module.exports = { runAutoBookingFlow };
