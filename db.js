const mysql = require('mysql2/promise');

/**
 * Konfigurasi Database MySQL Laragon ('auto')
 * dateStrings: true memastikan kolom DATE/DATETIME dikembalikan sebagai string murni YYYY-MM-DD tanpa geseran zona waktu
 */
const dbConfig = {
    host: 'localhost',
    user: 'root',
    password: '',
    database: 'auto',
    dateStrings: true
};

async function getConnection() {
    return await mysql.createConnection(dbConfig);
}

/**
 * Mereset status booking yang tertahan di 'processing' kembali menjadi 'pending'
 */
async function resetStaleProcessingBookings() {
    let conn;
    try {
        conn = await getConnection();
        const [result] = await conn.execute(
            `UPDATE bookings SET status = 'pending' WHERE status = 'processing' AND deleted_at IS NULL`
        );
        if (result.affectedRows > 0) {
            console.log(`🔄 Mengembalikan ${result.affectedRows} booking 'processing' yang tertahan menjadi 'pending'.`);
        }
    } catch (err) {
        console.error('❌ Gagal mereset status processing:', err.message);
    } finally {
        if (conn) await conn.end();
    }
}

/**
 * Mengambil 1 data booking terlama yang berstatus 'pending' atau 'failed' dan BELUM di-soft delete
 */
async function getPendingBooking() {
    let conn;
    try {
        await resetStaleProcessingBookings();

        conn = await getConnection();

        // 1. Ambil 1 data booking terlama berstatus 'pending' atau 'failed' (deleted_at IS NULL)
        const [bookings] = await conn.execute(
            `SELECT * FROM bookings WHERE status IN ('pending', 'failed') AND deleted_at IS NULL ORDER BY created_at ASC LIMIT 1`
        );

        if (bookings.length === 0) {
            return null;
        }

        const booking = bookings[0];
        const bookingId = booking.booking_id;

        // 2. Ambil data Ketua
        const [ketuaRows] = await conn.execute(
            `SELECT * FROM ketua WHERE booking_id = ? LIMIT 1`,
            [bookingId]
        );
        const ketua = ketuaRows[0] || null;

        // 3. Ambil data Anggota
        const [memberRows] = await conn.execute(
            `SELECT * FROM members WHERE booking_id = ? ORDER BY member_index ASC`,
            [bookingId]
        );

        return {
            bookingId,
            targetDate: booking.target_date,
            arrivalDate: booking.arrival_date,
            status: booking.status,
            ketua,
            members: memberRows
        };
    } catch (err) {
        console.error('❌ Error koneksi DB auto:', err.message);
        return null;
    } finally {
        if (conn) await conn.end();
    }
}

/**
 * Memperbarui status booking (misal: 'completed', 'failed', 'processing', 'pending')
 */
async function updateBookingStatus(bookingId, status) {
    let conn;
    try {
        conn = await getConnection();
        await conn.execute(
            `UPDATE bookings SET status = ? WHERE booking_id = ?`,
            [status, bookingId]
        );
        console.log(`✅ Status Booking ${bookingId} diperbarui menjadi: ${status}`);
    } catch (err) {
        console.error(`❌ Gagal update status DB ${bookingId}:`, err.message);
    } finally {
        if (conn) await conn.end();
    }
}

module.exports = {
    getPendingBooking,
    updateBookingStatus,
    resetStaleProcessingBookings
};
