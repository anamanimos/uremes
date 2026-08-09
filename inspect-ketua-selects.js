const puppeteer = require('puppeteer-extra');
const StealthPlugin = require('puppeteer-extra-plugin-stealth');
const path = require('path');
puppeteer.use(StealthPlugin());

async function inspectKetuaSelects() {
    const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    const botUserDataDir = path.join(__dirname, 'semeru_profile');

    const browser = await puppeteer.launch({
        executablePath: chromePath,
        userDataDir: botUserDataDir,
        headless: false,
        args: ['--no-sandbox', '--disable-setuid-sandbox', '--start-maximized']
    });

    const page = (await browser.pages())[0] || await browser.newPage();
    await page.setViewport({ width: 1366, height: 768 });

    console.log('📍 Accessing Booking Site Form...');
    await page.goto('https://bromotenggersemeru.id/booking/site/ranu-regulo?date_depart=2026-08-09&jenis_kegiatan=Berkemah', { waitUntil: 'domcontentloaded' });
    await new Promise(r => setTimeout(r, 4000));

    const ketuaSelects = await page.evaluate(() => {
        const result = {};
        document.querySelectorAll('select').forEach(sel => {
            const name = sel.name || sel.id;
            result[name] = Array.from(sel.options).map(o => ({
                value: o.value,
                text: o.innerText.trim()
            }));
        });
        return result;
    });

    console.log('📋 ALL SELECT OPTIONS:', JSON.stringify(ketuaSelects, null, 2));
    await browser.close();
}

inspectKetuaSelects();
