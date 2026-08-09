require('dotenv').config();
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const { sendTelegramMessage } = require('./telegram');

puppeteer.use(StealthPlugin());

/**
 * Automasi Cek Kuota Pendakian TNBTS (Semeru / Ranu Regulo / Bromo)
 */
async function checkTNBTSSemetoQuota() {
    const targetUrl = process.env.SEMERU_URL || 'https://bromotenggersemeru.id/';
    const isHeadless = process.env.HEADLESS === 'true';
    const targetMonthText = process.env.TARGET_MONTH || 'Agustus 2026';
    const destination = process.env.TARGET_DESTINATION || 'Semeru'; // "Semeru", "Ranu Regulo", atau "Bromo"
    const jenisKunjungan = process.env.VISIT_TYPE || 'Berkemah';

    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const botUserDataDir = path.join(__dirname, 'semeru_profile');

    console.log(`\n==================================================`);
    console.log(`🕒 Waktu Cek: ${new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}`);
    console.log(`🏔️ Memulai Cek Kuota ${destination}...`);
    console.log(`🌐 Website: ${targetUrl}`);
    console.log(`📅 Periode Target: ${targetMonthText}`);
    if (destination.toLowerCase().includes('regulo')) {
        console.log(`🏕️ Jenis Kunjungan: ${jenisKunjungan}`);
    }
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

        // Step 2: Berpindah ke Tab Sesuai Destinasi
        console.log(`📌 Berpindah ke Tab "${destination}"...`);
        const tabSelector = destination.toLowerCase().includes('regulo')
            ? '#pills-three-example2-tab'
            : (destination.toLowerCase().includes('semeru') ? '#pills-two-example2-tab' : '#pills-one-example2-tab');

        const tabPaneSelector = destination.toLowerCase().includes('regulo')
            ? '#pills-three-example2'
            : (destination.toLowerCase().includes('semeru') ? '#pills-two-example2' : '#pills-one-example2');

        await page.evaluate((tSel) => {
            const tabBtn = document.querySelector(tSel);
            if (tabBtn) {
                tabBtn.click();
                if (window.jQuery) window.jQuery(tabBtn).tab('show');
            }
        }, tabSelector);

        await new Promise(r => setTimeout(r, 1500));

        // Step 3: Isi Form & Submit
        console.log(`⚙️ Mengisi Form ${destination} (Periode: "${targetMonthText}")...`);
        const formSubmitted = await page.evaluate((pSel, targetM, targetV, isRegulo) => {
            const container = document.querySelector(pSel) || document;
            const form = container.querySelector('form') || document.querySelector('form');
            if (!form) return false;

            const ymSel = form.querySelector('select[name="year_month"]');
            const jkSel = form.querySelector('select[name="jenis_kegiatan"]');

            if (ymSel) {
                const optMatch = Array.from(ymSel.options).find(o => o.innerText.toLowerCase().includes(targetM.toLowerCase()));
                if (optMatch) ymSel.value = optMatch.value;
                else ymSel.selectedIndex = 1;
                if (window.jQuery) window.jQuery(ymSel).change().selectpicker('refresh');
            }

            if (isRegulo && jkSel) {
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
        }, tabPaneSelector, targetMonthText, jenisKunjungan, destination.toLowerCase().includes('regulo'));

        if (formSubmitted) {
            console.log(`🔍 Tombol Search ${destination} diklik.`);
        }

        console.log('⏳ Menunggu hasil kuota dimuat...');
        await new Promise(r => setTimeout(r, 4500));

        // Step 4: Ekstraksi Tabel Kuota
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
        const screenshotPath = `${destination.toLowerCase().replace(/\s+/g, '')}-quota-screenshot.png`;
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`📸 Screenshot hasil disimpan di: ${screenshotPath}`);

        // Step 5: Filter Kuota > 0
        const availableItems = tableRows.filter(item => item.quota > 0);

        console.log(`\n📋 Ringkasan Kuota ${destination} (${targetMonthText}):`);
        console.log(`📊 Total Tanggal Ditemukan: ${tableRows.length}`);

        if (availableItems.length > 0) {
            console.log(`🎉 KUOTA ${destination.toUpperCase()} TERSEDIA! (Ditemukan ${availableItems.length} tanggal):`);
            availableItems.forEach((item, idx) => {
                console.log(`   ${idx + 1}. ${item.date} => Sisa Kuota: ${item.quota}`);
            });

            // Kirim pesan Telegram
            const nowStr = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
            let telegramMsg = `🌋 <b>KUOTA ${destination.toUpperCase()} TERSEDIA!</b>\n\n`;
            if (destination.toLowerCase().includes('regulo')) {
                telegramMsg += `📌 <b>Jenis Kunjungan:</b> ${jenisKunjungan}\n`;
            }
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
            console.log(`🔴 Kuota ${destination} (${targetMonthText}) saat ini Penuh / Belum Dibuka.`);
        }

        return { success: true, destination, month: targetMonthText, availableQuota: availableItems };

    } catch (error) {
        console.error(`❌ Error saat mengecek kuota ${destination}:`, error.message);
        await page.screenshot({ path: 'error-screenshot.png' }).catch(() => {});
        return { success: false, error: error.message };
    } finally {
        console.log('🔒 Menutup Browser...');
        await browser.close();
    }
}

if (require.main === module) {
    checkTNBTSSemetoQuota()
        .then(() => console.log('🏁 Pengecekan Selesai.'))
        .catch(err => console.error('❌ Gagal:', err));
}

module.exports = { checkTNBTSSemetoQuota };
