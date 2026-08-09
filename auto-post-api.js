require('dotenv').config();

/**
 * Automasi Post WordPress Menggunakan REST API (Metode Terbaik & Tercepat)
 * 
 * Syarat: Buat "Application Password" di WP Admin -> Users -> Profile -> Application Passwords
 */
async function createPostViaRestApi({ title, content, status = 'publish' }) {
    const wpUrl = process.env.WP_URL || 'https://sevencols.com';
    const username = process.env.WP_USERNAME || 'cranam21';
    
    // Kunci Password Aplikasi (Application Password) atau Password Utama jika plugin basic auth diaktifkan
    const appPassword = process.env.WP_APP_PASSWORD || process.env.WP_PASSWORD || 'Anamsukses12';

    const apiUrl = `${wpUrl.replace(/\/$/, '')}/wp-json/wp/v2/posts`;
    const authHeader = 'Basic ' + Buffer.from(`${username}:${appPassword}`).toString('base64');

    console.log('🚀 Membuka koneksi REST API WordPress...');
    console.log(`🌐 Endpoint: ${apiUrl}`);

    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': authHeader
            },
            body: JSON.stringify({
                title: title,
                content: content,
                status: status // 'publish' atau 'draft'
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(`WordPress API Error (${response.status}): ${data.message || JSON.stringify(data)}`);
        }

        console.log('✅ Post Berhasil Dibuat via REST API!');
        console.log(`📌 ID Post: ${data.id}`);
        console.log(`🔗 Link Artikel: ${data.link}`);
        return data;

    } catch (error) {
        console.error('❌ Gagal membuat post via REST API:', error.message);
        throw error;
    }
}

// Jalankan contoh jika script dipanggil langsung
if (require.main === module) {
    const samplePost = {
        title: 'Artikel via WordPress REST API ' + new Date().toLocaleDateString('id-ID'),
        content: '<p>Ini adalah artikel yang diposting secara instan menggunakan Node.js dan <strong>WordPress REST API</strong>.</p>',
        status: 'publish'
    };

    createPostViaRestApi(samplePost)
        .then(() => console.log('✅ Selesai!'))
        .catch(() => console.log('💡 Tip: Jika REST API membutuhkan Password Aplikasi, buat Application Password di WordPress Admin > Users > Profile.'));
}

module.exports = { createPostViaRestApi };
