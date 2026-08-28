/**
 * tokenStorage.js
 * ---------------
 * Modul utilitas untuk membaca, menyimpan, dan menghapus Access Token & Refresh Token
 * dari database SQLite lokal (tabel `session`).
 *
 * Pola pemakaian:
 *   - Simpan dua token setelah login berhasil  → saveTokens(accessToken, refreshToken)
 *   - Baca token saat app dibuka               → getTokens()
 *   - Hapus semua token saat logout            → clearTokens()
 *
 * Skema tabel `session`:
 *   CREATE TABLE IF NOT EXISTS session (
 *     id            INTEGER PRIMARY KEY AUTOINCREMENT,
 *     token         TEXT,          -- Access Token (JWT)
 *     refresh_token TEXT           -- Refresh Token opaque
 *   );
 */

import SQLite from 'react-native-sqlite-storage';

const db = SQLite.openDatabase(
    { name: 'pipitnesan.db', location: 'default' },
    () => { },
    error => console.error('[tokenStorage] DB open error:', error)
);

/**
 * Memastikan tabel `session` sudah ada dan memiliki kolom `refresh_token`.
 * Dipanggil sekali saat modul pertama kali digunakan.
 * Aman dipanggil berulang kali (idempotent).
 *
 * Return value:
 * @return {Promise<void>}
 */
export function initSessionTable() {
    return new Promise((resolve, reject) => {
        db.transaction(tx => {
            // Buat tabel jika belum ada
            tx.executeSql(
                'CREATE TABLE IF NOT EXISTS session (id INTEGER PRIMARY KEY AUTOINCREMENT, token TEXT, refresh_token TEXT)',
                [],
                () => {
                    // Tambah kolom refresh_token jika migrasi dari skema lama (hanya ada kolom token)
                    tx.executeSql(
                        'ALTER TABLE session ADD COLUMN refresh_token TEXT',
                        [],
                        () => resolve(),   // Berhasil tambah kolom
                        () => resolve()    // Kolom sudah ada → abaikan error
                    );
                },
                (_, error) => {
                    console.error('[tokenStorage] Failed to create session table:', error);
                    reject(error);
                }
            );
        });
    });
}

/**
 * Deskripsi singkat:
 * Menyimpan Access Token dan Refresh Token ke dalam tabel `session`.
 * Menghapus entri lama terlebih dahulu agar hanya ada satu baris aktif.
 *
 * Parameter:
 * @param {string} accessToken   JWT Access Token yang diterima dari backend.
 * @param {string} refreshToken  Refresh Token opaque yang diterima dari backend.
 *
 * Return value:
 * @return {Promise<void>}
 */
export function saveTokens(accessToken, refreshToken) {
    return new Promise((resolve, reject) => {
        db.transaction(tx => {
            tx.executeSql('DELETE FROM session', [], () => {
                tx.executeSql(
                    'INSERT INTO session (token, refresh_token) VALUES (?, ?)',
                    [accessToken, refreshToken],
                    () => resolve(),
                    (_, error) => {
                        console.error('[tokenStorage] Failed to save tokens:', error);
                        reject(error);
                    }
                );
            });
        });
    });
}

/**
 * Deskripsi singkat:
 * Membaca Access Token dan Refresh Token dari tabel `session`.
 *
 * Return value:
 * @return {Promise<{accessToken: string|null, refreshToken: string|null}>}
 *   Object berisi kedua token. Nilai null jika tidak ada data tersimpan.
 */
export function getTokens() {
    return new Promise((resolve) => {
        db.transaction(tx => {
            tx.executeSql(
                'SELECT token, refresh_token FROM session LIMIT 1',
                [],
                (_, results) => {
                    if (results.rows.length > 0) {
                        const row = results.rows.item(0);
                        resolve({
                            accessToken: row.token || null,
                            refreshToken: row.refresh_token || null,
                        });
                    } else {
                        resolve({ accessToken: null, refreshToken: null });
                    }
                },
                (_, error) => {
                    console.error('[tokenStorage] Failed to get tokens:', error);
                    resolve({ accessToken: null, refreshToken: null });
                }
            );
        });
    });
}

/**
 * Deskripsi singkat:
 * Menghapus semua token dari tabel `session` dan membersihkan global state.
 * Dipanggil saat logout atau saat refresh token gagal (sesi tidak valid).
 *
 * Return value:
 * @return {Promise<void>}
 */
export function clearTokens() {
    global.userToken = null;
    global.refreshToken = null;
    return new Promise((resolve) => {
        db.transaction(tx => {
            tx.executeSql('DELETE FROM session', [], () => resolve(), () => resolve());
        });
    });
}
