require('dotenv').config();
const cron = require('node-cron');
const { checkSemeruQuota } = require('./index');

const cronSchedule = process.env.CRON_SCHEDULE || '*/15 * * * *';

console.log(`==================================================`);
console.log(`🤖 Semeru Quota Checker Scheduler (Cron) Aktif!`);
console.log(`⏱️  Jadwal Cron: "${cronSchedule}"`);
console.log(`==================================================`);
console.log(`ℹ️  Script ini akan otomatis mengecek kuota pendakian Semeru.`);
console.log(`📱 Notifikasi Telegram akan dikirim saat kuota TERSEDIA.\n`);

// Jalankan pengecekan pertama kali saat script dinyalakan
console.log('🚀 Menjalankan pengecekan pertama...');
checkSemeruQuota();

// Daftarkan jadwal Cron
cron.schedule(cronSchedule, () => {
    console.log(`\n⏰ [Cron Trigger] Menjalankan pengecekan kuota terjadwal...`);
    checkSemeruQuota();
});
