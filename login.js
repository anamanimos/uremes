require('dotenv').config();
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');

puppeteer.use(StealthPlugin());

/**
 * Script Setup Login 1-Kali ke bromotenggersemeru.id via Google
 */
async function setupGoogleLogin() {
    const targetUrl = process.env.SEMERU_URL || 'https://bromotenggersemeru.id/';
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const botUserDataDir = path.join(__dirname, 'semeru_profile');

    console.log(`\n==================================================`);
    console.log(`🔑 SETUP LOGIN 1-KALI GOOGLE ACCOUNTS`);
    console.log(`🌐 Website: ${targetUrl}`);
    console.log(`📂 Folder Profil Terisolasi: ${botUserDataDir}`);
    console.log(`==================================================`);

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
            '--start-maximized'
        ]
    });

    const pages = await browser.pages();
    const page = pages.length > 0 ? pages[0] : await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });

    try {
        console.log(`📍 Membuka website: ${targetUrl}`);
        await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 45000 }).catch(() => {});
        await new Promise(r => setTimeout(r, 3000));

        // Cek apakah akun sudah terdeteksi login
        let loginStatus = { isLoggedIn: false, userName: '' };
        try {
            loginStatus = await page.evaluate(() => {
                const bodyTxt = (document.body && document.body.innerText) ? document.body.innerText : '';
                const isSignOut = bodyTxt.includes('Sign out') || bodyTxt.includes('Booking Saya');
                
                const headerBtn = document.querySelector('a[href*="logout"], a[href*="booking"], .dropdown-toggle');
                const userName = (headerBtn && headerBtn.textContent) ? headerBtn.textContent.trim() : '';

                return { isLoggedIn: isSignOut, userName };
            });
        } catch (e) {}

        if (loginStatus.isLoggedIn) {
            console.log(`\n==================================================`);
            console.log(`🎉 ANDA SUDAH LOGGED IN!`);
            console.log(`👤 Akun Terdeteksi: ${loginStatus.userName || 'Choirul Anam / Logged In'}`);
            console.log(`✅ Sesi login tersimpan secara permanen di folder semeru_profile.`);
            console.log(`==================================================\n`);
            await new Promise(r => setTimeout(r, 3000));
            await browser.close();
            return;
        }

        console.log(`\n==================================================`);
        console.log(`⚠️ AKUN BELUM LOGGED IN.`);
        console.log(`👉 Silakan klik "Sign in atau Register" -> "Sign in dengan Google" pada jendela Chrome yang terbuka.`);
        console.log(`⏳ Script sedang menunggu Anda menyelesaikan Sign In Google...`);
        console.log(`==================================================\n`);

        // Buka modal login jika belum terbuka
        try {
            await page.evaluate(() => {
                const loginTrigger = Array.from(document.querySelectorAll('a, button, span')).find(el => {
                    const txt = (el && el.textContent) ? el.textContent.trim() : '';
                    return txt.includes('Sign in') || txt.includes('Register');
                });
                if (loginTrigger) loginTrigger.click();
            });
        } catch (e) {}

        // Loop memantau sampai pengguna selesai login (hingga 5 menit)
        let attempts = 0;
        while (attempts < 150) {
            await new Promise(r => setTimeout(r, 2000));
            attempts++;

            try {
                const currentStatus = await page.evaluate(() => {
                    const bodyTxt = (document.body && document.body.innerText) ? document.body.innerText : '';
                    const isSignOut = bodyTxt.includes('Sign out') || bodyTxt.includes('Booking Saya');
                    
                    let name = '';
                    document.querySelectorAll('a, span, div').forEach(el => {
                        try {
                            const txt = (el && el.textContent) ? el.textContent.trim() : '';
                            if (txt.includes('Sign out') || txt.includes('Booking Saya')) {
                                name = txt;
                            }
                        } catch (err) {}
                    });

                    return { isLoggedIn: isSignOut, name };
                });

                if (currentStatus && currentStatus.isLoggedIn) {
                    console.log(`\n==================================================`);
                    console.log(`🎉 LOGIN BERHASIL TERDETEKSI!`);
                    console.log(`👤 Status: ${currentStatus.name || 'Logged In'}`);
                    console.log(`✅ Sesi login Google berhasil disimpan di folder semeru_profile!`);
                    console.log(`==================================================\n`);
                    await new Promise(r => setTimeout(r, 3000));
                    break;
                }
            } catch (err) {
                // Diamkan error saat jendela berpindah / navigasi Google OAuth berlangsung
            }
        }

    } catch (err) {
        console.error('❌ Error saat proses login:', err.message);
    } finally {
        await browser.close().catch(() => {});
        console.log('🔒 Jendela Login Ditutup.');
    }
}

setupGoogleLogin();
