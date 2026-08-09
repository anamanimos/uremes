# 🌋 Automasi Booking & Cek Kuota TNBTS (Semeru & Ranu Regulo)

Project Node.js ini memantau ketersediaan kuota pendakian & camping di portal resmi ([`https://bromotenggersemeru.id`](https://bromotenggersemeru.id)) serta mengisikan form booking secara otomatis.

---

## ⚡ Fitur Utama

1. **Auto Booking Ranu Regulo (10 Orang)**:
   * Mengklik angka kuota pada tanggal terpilih secara otomatis.
   * Menconteng 3 persyaratan kunjungan (SOP) & mengklik tombol **BOOKING**.
   * Memilih Pintu Masuk (*Coban Trisula*).
   * Mengisi **Data Ketua Kelompok**.
   * Membuka modal & menginput **9 Data Anggota** tambahan (Total 1 Ketua + 9 Anggota = 10 Orang).
   * Memilih Metode Pembayaran **QRIS**.
   * Menconteng Syarat & Ketentuan Final.
   * **Menghentikan bot sebelum tombol Confirm Booking**, membiarkan jendela Chrome tetap terbuka agar Anda tinggal memeriksa & mengklik **CONFIRM BOOKING** secara manual.

2. **Login 1-Kali Google**:
   * Sesi login akun Google Anda tersimpan permanen di folder [`semeru_profile`](file:///d:/laragon/www/auto/semeru_profile).

---

## ⚙️ Perintah Utama

| Perintah | Deskripsi |
| :--- | :--- |
| `npm run auto-booking` | **Automasi Lengkap Booking Ranu Regulo (10 Orang)** sampai siap klik Confirm Booking |
| `npm run login` | Setup login 1-kali via akun Google |
| `npm run ranu-regulo` | Cek kuota Ranu Regulo & kirim alert Telegram |
| `npm start` | Cek kuota Semeru & kirim alert Telegram |
| `npm run cron` | Pemantauan otomatis berkala setiap X menit |

---

## 🛠️ Konfigurasi `.env`

```env
# URL Website Target
SEMERU_URL=https://bromotenggersemeru.id/

# Periode Target Pengecekan / Booking
TARGET_MONTH=Agustus 2026

# Jenis Kunjungan Ranu Regulo ("Berkemah" atau "Berkunjung")
VISIT_TYPE=Berkemah

# Telegram Bot Config
TELEGRAM_BOT_TOKEN=YOUR_TELEGRAM_BOT_TOKEN
TELEGRAM_CHAT_ID=YOUR_TELEGRAM_CHAT_ID

# Pengaturan Browser (false = tampilkan layar Chrome)
HEADLESS=false
```
