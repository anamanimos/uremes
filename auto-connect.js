require('dotenv').config();
const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const { sendTelegramMessage } = require('./telegram');

puppeteer.use(StealthPlugin());

/**
 * Cara 2: Menghubungkan Puppeteer ke Chrome Acumena yang sedang terbuka (Remote Debugging Port 9222)
 */
async function checkSemeruViaConnectedChrome() {
    const targetUrl = process.env.SEMERU_URL || 'https://bromotenggersemeru.id/';
    const monthToSelect = process.env.TARGET_MONTH || '';

    console.log(`\n==================================================`);
    console.log(`🕒 Waktu Cek: ${new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}`);
    console.log(`🔗 Menghubungkan ke Chrome (Port 9222)...`);
    console.log(`==================================================`);

    try {
        const browser = await puppeteer.connect({
            browserURL: 'http://127.0.0.1:9222',
            defaultViewport: null
        });

        console.log('✅ Terhubung ke Chrome!');
        const pages = await browser.pages();
        const page = pages.length > 0 ? pages[0] : await browser.newPage();

        console.log(`📍 Navigasi ke website: ${targetUrl}`);
        await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await new Promise(r => setTimeout(r, 2000));

        // Step 1: Click Semeru Tab
        console.log('📌 Memilih tab "Semeru"...');
        await page.evaluate(() => {
            const allElements = Array.from(document.querySelectorAll('*'));
            const semeruEl = allElements.find(el => {
                const text = el.innerText ? el.innerText.trim() : '';
                return text === 'Semeru' && el.children.length <= 2;
            });
            if (semeruEl) (semeruEl.closest('button, a, li, div') || semeruEl).click();
        });

        await new Promise(r => setTimeout(r, 1500));

        // Step 2: Open Month Dropdown
        console.log('📅 Membuka dropdown Periode...');
        await page.evaluate(() => {
            const elements = Array.from(document.querySelectorAll('input, div, span, select, .select2-selection'));
            const targetEl = elements.find(el => {
                const ph = el.getAttribute('placeholder') || '';
                const txt = el.innerText || '';
                return ph.includes('Bulan / Tahun') || ph.includes('Bulan') || txt.includes('Bulan / Tahun');
            });
            if (targetEl) targetEl.click();
        });

        await new Promise(r => setTimeout(r, 1000));

        // Step 3: Select Month
        console.log(`🗓️ Memilih opsi bulan: "${monthToSelect}"...`);
        const selectedMonthText = await page.evaluate((targetM) => {
            const options = Array.from(document.querySelectorAll('li, div, option, span, a'));
            let match = null;
            if (targetM) match = options.find(opt => opt.innerText && opt.innerText.trim().toLowerCase().includes(targetM.toLowerCase()));
            if (!match) {
                match = options.find(opt => {
                    const txt = opt.innerText ? opt.innerText.trim() : '';
                    return /\b(Januari|Februari|Maret|April|Mei|Juni|Juli|Agustus|September|Oktober|November|Desember)\s+20\d\d\b/i.test(txt);
                });
            }
            if (match) {
                const txt = match.innerText.trim();
                match.click();
                return txt;
            }
            return null;
        }, monthToSelect);

        console.log(`✅ Bulan terpilih: "${selectedMonthText || 'Bulan Default'}"`);
        await new Promise(r => setTimeout(r, 1000));

        // Step 4: Click Search
        console.log('🔍 Mengklik tombol "Search"...');
        await page.evaluate(() => {
            const buttons = Array.from(document.querySelectorAll('button, a, div, input[type="submit"]'));
            const searchBtn = buttons.find(btn => {
                const txt = btn.innerText ? btn.innerText.trim() : '';
                return txt.toLowerCase().includes('search') || txt.toLowerCase().includes('cari');
            });
            if (searchBtn) searchBtn.click();
        });

        await new Promise(r => setTimeout(r, 4000));

        // Step 5: Check Quota
        console.log('📊 Menganalisis ketersediaan kuota pendakian...');
        const extractedData = await page.evaluate(() => {
            const results = [];
            const quotaElements = Array.from(document.querySelectorAll('.card, tr, .quota-item, .list-group-item, div[class*="kuota"]'));
            quotaElements.forEach(el => {
                const text = el.innerText ? el.innerText.trim() : '';
                if (text && (text.toLowerCase().includes('kuota') || text.toLowerCase().includes('tersedia') || text.toLowerCase().includes('sisa') || text.toLowerCase().includes('penuh'))) {
                    if (text.length < 300) results.push(text.replace(/\s+/g, ' '));
                }
            });
            if (results.length === 0) return document.body.innerText.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            return results;
        });

        const availableList = extractedData.filter(line => {
            const lower = line.toLowerCase();
            return (lower.includes('tersedia') || lower.includes('sisa') || lower.includes('kuota')) &&
                   !lower.includes('0 kuota') && !lower.includes('sisa 0') && !lower.includes('penuh') && !lower.includes('habis');
        });

        console.log(`\n📋 Ringkasan Hasil Pengecekan (${selectedMonthText || 'Bulan Terpilih'}):`);
        if (availableList.length > 0) {
            console.log(`🎉 KUOTA TERSEDIA DITEMUKAN! (${availableList.length} entri):`);
            availableList.forEach((item, idx) => console.log(`   ${idx + 1}. ${item}`));

            const nowStr = new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' });
            let telegramMsg = `🌋 <b>KUOTA PENDAKIAN SEMERU TERSEDIA!</b>\n\n` +
                `📅 <b>Bulan/Periode:</b> ${selectedMonthText || 'Terpilih'}\n` +
                `🕒 <b>Waktu Cek:</b> ${nowStr}\n` +
                `🌐 <b>Link Booking:</b> <a href="${targetUrl}">${targetUrl}</a>\n\n` +
                `📌 <b>Detail Kuota:</b>\n`;
            availableList.slice(0, 10).forEach(item => { telegramMsg += `• ${item}\n`; });
            telegramMsg += `\n🚨 <i>Segera pesan sebelum kuota habis!</i>`;
            await sendTelegramMessage(telegramMsg);
        } else {
            console.log(`🔴 Kuota Pendakian untuk ${selectedMonthText || 'Bulan Terpilih'} saat ini Penuh / Belum Dibuka.`);
        }

        browser.disconnect();
        console.log('🏁 Pengecekan Selesai.');

    } catch (err) {
        console.error('❌ Gagal terhubung ke Chrome port 9222:', err.message);
        console.log('💡 Jalankan Chrome dengan perintah:');
        console.log('   & "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe" --remote-debugging-port=9222');
    }
}

checkSemeruViaConnectedChrome();
