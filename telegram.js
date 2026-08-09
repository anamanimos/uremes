require('dotenv').config();

/**
 * Kirim pesan notifikasi ke Telegram menggunakan Telegram Bot API
 * 
 * @param {string} message - Pesan HTML / Text yang akan dikirim
 * @returns {Promise<boolean>}
 */
async function sendTelegramMessage(message) {
    const botToken = process.env.TELEGRAM_BOT_TOKEN;
    const chatId = process.env.TELEGRAM_CHAT_ID;

    if (!botToken || !chatId || botToken === 'YOUR_TELEGRAM_BOT_TOKEN') {
        console.warn('⚠️ TELEGRAM_BOT_TOKEN atau TELEGRAM_CHAT_ID belum diisi di file .env');
        console.log('📱 Pesan yang akan dikirim (Simulasi):');
        console.log('--------------------------------------------------');
        console.log(message);
        console.log('--------------------------------------------------');
        return false;
    }

    const url = `https://api.telegram.org/bot${botToken}/sendMessage`;

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                chat_id: chatId,
                text: message,
                parse_mode: 'HTML',
                disable_web_page_preview: false
            })
        });

        const data = await response.json();

        if (!data.ok) {
            throw new Error(`Telegram API Error: ${data.description || JSON.stringify(data)}`);
        }

        console.log('📲 Notifikasi Telegram BERHASIL dikirim!');
        return true;

    } catch (error) {
        console.error('❌ Gagal mengirim pesan Telegram:', error.message);
        return false;
    }
}

// Uji coba jika script dipanggil langsung
if (require.main === module) {
    console.log('🧪 Memulai pengujian notifikasi Telegram...');
    const testMessage = `🤖 <b>Tes Bot Notifikasi Kuota Semeru</b>\n\n` +
        `Halo! Bot Telegram berhasil terhubung.\n` +
        `Waktu Tes: ${new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })}\n\n` +
        `<i>Bot ini siap mengirim notifikasi jika kuota pendakian Semeru tersedia.</i>`;

    sendTelegramMessage(testMessage).then(success => {
        if (!success) {
            console.log('\n💡 <b>Petunjuk Setup Telegram Bot</b>:');
            console.log('1. Buka Telegram & cari @BotFather');
            console.log('2. Kirim perintah /newbot dan ikuti instruksi hingga mendapat BOT TOKEN.');
            console.log('3. Cari bot Anda di Telegram dan tekan START.');
            console.log('4. Cari @userinfobot di Telegram untuk mendapatkan CHAT ID Anda.');
            console.log('5. Masukkan TOKEN & CHAT_ID ke dalam file .env');
        }
    });
}

module.exports = { sendTelegramMessage };
