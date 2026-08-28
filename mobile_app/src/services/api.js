import axios from 'axios';
import { Alert } from 'react-native';
import NetInfo from '@react-native-community/netinfo';
import { API_BASE_URL } from '../config/api';
import { resetToLogin } from '../navigation/navigationRef';
import { saveTokens, clearTokens } from '../utils/tokenStorage';

// Endpoint otentikasi yang tidak boleh memicu auto-logout saat mengembalikan 401.
const AUTH_ENDPOINTS = ['/login', '/register', '/logout', '/refresh'];

// Flag untuk mencegah multiple logout dialog muncul sekaligus
let isHandlingSessionExpired = false;

// Flag untuk mencegah multiple refresh request berjalan bersamaan
let isRefreshing = false;

// Antrian request yang gagal karena 401, menunggu hasil refresh selesai
let pendingQueue = [];

/**
 * Deskripsi singkat:
 * Memproses semua request yang sedang antri setelah refresh token selesai.
 *
 * Parameter:
 * @param {Error|null} error       Error jika refresh gagal, null jika berhasil.
 * @param {string|null} newToken   Access Token baru jika refresh berhasil.
 */
function processQueue(error, newToken = null) {
    pendingQueue.forEach(({ resolve, reject }) => {
        if (error) {
            reject(error);
        } else {
            resolve(newToken);
        }
    });
    pendingQueue = [];
}

/**
 * Deskripsi singkat:
 * Instance Axios yang dikonfigurasi untuk melakukan permintaan HTTP ke server backend.
 * Base URL terpusat di src/config/api.js dan dapat diubah tanpa menyentuh file ini.
 *
 * Konfigurasi:
 * - baseURL: URL endpoint API (dari src/config/api.js).
 * - headers: Header default seperti Content-Type dan Accept.
 */
const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
});

// Header khusus untuk melewati halaman peringatan browser Ngrok (hanya relevan saat memakai tunnel).
if (API_BASE_URL.includes('ngrok')) {
    api.defaults.headers['ngrok-skip-browser-warning'] = '69420';
}

/**
 * Interceptor Permintaan (Request):
 * 1. Mengecek status koneksi internet sebelum mengirim permintaan.
 * 2. Menyisipkan token Authorization (Bearer) jika tersedia di global.userToken.
 */
api.interceptors.request.use(
    async (config) => {
        const networkState = await NetInfo.fetch();
        if (!networkState.isConnected) {
            return Promise.reject(new Error('No internet connection. Please check your network and try again.'));
        }

        if (global.userToken) {
            config.headers.Authorization = `Bearer ${global.userToken}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

/**
 * Interceptor Tanggapan (Response):
 * 1. Meneruskan tanggapan jika sukses.
 * 2. Menangkap error jaringan atau server (500+) dan memberikan pesan error yang ramah.
 * 3. Menangani error 401 dengan strategi bertahap:
 *    a. Jika ada Refresh Token → coba refresh Access Token secara otomatis → retry request.
 *    b. Jika refresh gagal atau tidak ada Refresh Token → bersihkan sesi → arahkan ke Login.
 */
api.interceptors.response.use(
    (response) => {
        return response;
    },
    async (error) => {
        // Tangani error jaringan atau server
        if (error.message === 'Network Error') {
            return Promise.reject(new Error('System is currently busy. Please try again later.'));
        }

        if (error.response && error.response.status >= 500) {
            return Promise.reject(new Error('System is currently busy. Please try again later.'));
        }

        const originalRequest = error.config;
        const requestUrl = originalRequest?.url || '';
        const is401 = error.response?.status === 401;
        const isAuthEndpoint = AUTH_ENDPOINTS.some(ep => requestUrl.includes(ep));

        // Jika 401 pada endpoint yang dilindungi dan bukan retry kedua
        if (is401 && !isAuthEndpoint && !originalRequest._retry) {
            // Jika sedang dalam proses refresh, antrikan request ini
            if (isRefreshing) {
                return new Promise((resolve, reject) => {
                    pendingQueue.push({ resolve, reject });
                }).then(newToken => {
                    originalRequest.headers.Authorization = `Bearer ${newToken}`;
                    return api(originalRequest);
                }).catch(err => Promise.reject(err));
            }

            originalRequest._retry = true;
            isRefreshing = true;

            try {
                const currentRefreshToken = global.refreshToken;

                if (!currentRefreshToken) {
                    throw new Error('No refresh token available');
                }

                // Panggil endpoint /refresh dengan Refresh Token
                // Tidak menggunakan instance api karena interceptor ini sendiri yang memanggilnya
                const { data } = await axios.post(
                    `${API_BASE_URL}/refresh`,
                    { refresh_token: currentRefreshToken },
                    { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } }
                );

                const newAccessToken = data.access_token;
                const newRefreshToken = data.refresh_token;

                // Update global state dan storage dengan token baru
                global.userToken = newAccessToken;
                global.refreshToken = newRefreshToken;
                await saveTokens(newAccessToken, newRefreshToken);

                // Update header default axios untuk request berikutnya
                api.defaults.headers.common['Authorization'] = `Bearer ${newAccessToken}`;

                // Beritahu semua request yang sedang antri bahwa token sudah diperbarui
                processQueue(null, newAccessToken);

                // Retry request original dengan token baru
                originalRequest.headers.Authorization = `Bearer ${newAccessToken}`;
                return api(originalRequest);

            } catch (refreshError) {
                // Refresh gagal → session benar-benar berakhir
                processQueue(refreshError, null);

                if (!isHandlingSessionExpired) {
                    isHandlingSessionExpired = true;
                    await clearTokens();
                    Alert.alert(
                        'Session Expired',
                        'Your session has expired. Please log in again.',
                        [
                            {
                                text: 'OK',
                                onPress: () => {
                                    isHandlingSessionExpired = false;
                                    resetToLogin();
                                },
                            },
                        ]
                    );
                }
                return Promise.reject(refreshError);
            } finally {
                isRefreshing = false;
            }
        }

        return Promise.reject(error);
    }
);

/**
 * Deskripsi singkat:
 * Membersihkan seluruh sesi lokal: token di memori (global.userToken, global.refreshToken)
 * dan token tersimpan di tabel SQLite `session`.
 *
 * Return value:
 * @return {Promise<void>}
 */
export async function clearLocalSession() {
    await clearTokens();
}

export default api;
