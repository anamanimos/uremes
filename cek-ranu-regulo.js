require('dotenv').config();
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const { sendTelegramMessage } = require('./telegram');

puppeteer.use(StealthPlugin());

/**
 * Automasi Presisi Cek Kuota Ranu Regulo (Berkemah / Berkunjung)
 */
async function checkRanuReguloQuota() {
    const targetUrl = process.env.SEMERU_URL || 'https://bromotenggersemeru.id/';
    const isHeadless = process.env.HEADLESS === 'true';
    const targetMonthText = process.env.TARGET_MONTH || 'Agustus 2026';
    const jenisKunjungan = process.env.VISIT_TYPE || 'Berkemah';

    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const botUserDataDir = path.join(__dirname, 'semeru_profile');

    console.log(`\n==================================================`);
    console.log(`🕒 Waktu Cek: ${new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}`);
    console.log(`🏕️ Memulai Cek Kuota Ranu Regulo...`);
    console.log(`🌐 Website: ${targetUrl}`);
    console.log(`📅 Periode Target: ${targetMonthText}`);
    console.log(`🏕️ Jenis Kunjungan: ${jenisKunjungan}`);
    console.log(`==================================================`);

    if (!fs.existsSync(chromePath)) {
        console.error(`❌ Chrome tidak ditemukan di: ${chromePath}`);
        throw new Error('Google Chrome tidak terinstall di lokasi standar Windows.');
    }

    const browser = await puppeteer.launch({
        executablePath: chromePath,
        userDataDir: botUserDataDir,
        headless: isHeadless,
        defaultViewport: null,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-blink-features=AutomationControlled',
            '--start-maximized'
        ]
    });
    
    const pages = await browser.pages();
    const page = pages.length > 0 ? pages[0] : await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });

    try {
        // Step 1: Navigasi ke Website
        console.log(`📍 Navigasi ke website: ${targetUrl}`);
        await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 45000 }).catch(() => {});
        await new Promise(r => setTimeout(r, 3000));

        // Step 2: Cek Status Login Akun
        const accountStatus = await page.evaluate(() => {
            const bodyTxt = document.body.innerText || '';
            const isLoggedIn = bodyTxt.includes('Sign out') || bodyTxt.includes('Booking Saya');
            
            let name = 'Guest';
            document.querySelectorAll('a, span, div').forEach(el => {
                const txt = el.innerText ? el.innerText.trim() : '';
                if (txt.includes('Choirul') || txt.includes('Sign out') || txt.includes('Booking Saya')) {
                    name = txt.replace('Sign out', '').trim() || 'Logged In User';
                }
            });

            return { isLoggedIn, name };
        });

        if (accountStatus.isLoggedIn) {
            console.log(`👤 Status Akun: LOGGED IN (${accountStatus.name || 'Terhubung'}) ✅`);
        } else {
            console.log(`⚠️ Status Akun: Guest (Belum Login). Jalankan 'npm run login' jika ingin login 1-kali.`);
        }

        // Step 3: Berpindah ke Tab "Ranu Regulo" (#pills-three-example2-tab)
        console.log('📌 Berpindah ke Tab "Ranu Regulo"...');
        await page.evaluate(() => {
            const tabBtn = document.querySelector('#pills-three-example2-tab') || document.querySelector('a[href="#pills-three-example2"]');
            if (tabBtn) {
                tabBtn.click();
                if (window.jQuery) window.jQuery(tabBtn).tab('show');
            }
        });

        await new Promise(r => setTimeout(r, 1500));

        // Step 4: Isi Form Ranu Regulo & Submit
        console.log(`⚙️ Mengisi Form Ranu Regulo (Periode: "${targetMonthText}", Kunjungan: "${jenisKunjungan}")...`);
        const formSubmitted = await page.evaluate((targetM, targetV) => {
            const form = document.querySelector('#regulo-form-kapasitas') || document.querySelector('#pills-three-example2 form');
            if (!form) return false;

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
            if (submitBtn) {
                submitBtn.click();
                return true;
            }
            return false;
        }, targetMonthText, jenisKunjungan);

        if (formSubmitted) {
            console.log('🔍 Tombol Search Ranu Regulo diklik.');
        }

        console.log('⏳ Menunggu hasil kuota dimuat...');
        await new Promise(r => setTimeout(r, 4500));

        // Step 5: Ekstraksi Tabel Kuota
        console.log('📊 Menganalisis data tabel kuota...');
        const tableRows = await page.evaluate(() => {
            const results = [];
            const rows = Array.from(document.querySelectorAll('table tbody tr'));
            rows.forEach(tr => {
                const tds = tr.querySelectorAll('td, th');
                if (tds.length >= 2) {
                    const dateStr = tds[0].innerText.trim();
                    const quotaStr = tds[1].innerText.trim();
                    const num = parseInt(quotaStr.replace(/\D/g, ''), 10);
                    if (dateStr && !isNaN(num)) {
                        results.push({
                            date: dateStr,
                            quota: num
                        });
                    }
                }
            });
            return results;
        });

        // Screenshot Verifikasi
        const screenshotPath = 'ranuregulo-quota-screenshot.png';
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`📸 Screenshot hasil disimpan di: ${screenshotPath}`);

        // Step 6: Filter Kuota > 0
        const availableItems = tableRows.filter(item => item.quota > 0);

        console.log(`\n📋 Ringkasan Kuota Ranu Regulo (${jenisKunjungan} - ${targetMonthText}):`);
        console.log(`📊 Total Tanggal Ditemukan: ${tableRows.length}`);
        
        if (availableItems.length > 0) {
            console.log(`🎉 KUOTA RANU REGULO TERSEDIA! (Ditemukan ${availableItems.length} tanggal):`);
            availableItems.forEach((item, idx) => {
                console.log(`   ${idx + 1}. ${item.date} => Sisa Kuota: ${item.quota}`);
            });

            // Kirim pesan Telegram
            const nowStr = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
            let telegramMsg = `🏕️ <b>KUOTA RANU REGULO TERSEDIA!</b>\n\n`;
            telegramMsg += `👤 <b>Status Akun:</b> ${accountStatus.isLoggedIn ? 'Logged In ✅' : 'Guest'}\n`;
            telegramMsg += `📌 <b>Jenis Kunjungan:</b> ${jenisKunjungan}\n`;
            telegramMsg += `📅 <b>Bulan/Periode:</b> ${targetMonthText}\n`;
            telegramMsg += `🕒 <b>Waktu Cek:</b> ${nowStr}\n`;
            telegramMsg += `🌐 <b>Link Booking:</b> <a href="${targetUrl}">${targetUrl}</a>\n\n`;
            telegramMsg += `📌 <b>Detail Ketersediaan Kuota:</b>\n`;
            
            availableItems.slice(0, 10).forEach(item => {
                telegramMsg += `• <b>${item.date}</b>: Sisa <b>${item.quota}</b> kuota\n`;
            });

            if (availableItems.length > 10) {
                telegramMsg += `<i>...dan ${availableItems.length - 10} tanggal lainnya.</i>\n`;
            }

            telegramMsg += `\n🚨 <i>Segera lakukan booking sebelum kuota habis!</i>`;

            await sendTelegramMessage(telegramMsg);

        } else {
            console.log(`🔴 Kuota Ranu Regulo (${jenisKunjungan} - ${targetMonthText}) saat ini Penuh / Belum Dibuka.`);
        }

        return { success: true, destination: 'Ranu Regulo', visitType: jenisKunjungan, month: targetMonthText, availableQuota: availableItems, accountStatus };

    } catch (error) {
        console.error('❌ Error saat mengecek kuota Ranu Regulo:', error.message);
        await page.screenshot({ path: 'ranuregulo-error-screenshot.png' }).catch(() => {});
        return { success: false, error: error.message };
    } finally {
        console.log('🔒 Menutup Browser...');
        await browser.close();
    }
}

if (require.main === module) {
    checkRanuReguloQuota()
        .then(() => console.log('🏁 Pengecekan Ranu Regulo Selesai.'))
        .catch(err => console.error('❌ Gagal:', err));
}

module.exports = { checkRanuReguloQuota };
