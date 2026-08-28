import { createNavigationContainerRef } from '@react-navigation/native';

/**
 * Referensi global untuk NavigationContainer, agar kode di luar komponen React
 * (misalnya interceptor 401 di services/api.js) tetap dapat melakukan navigasi.
 */
export const navigationRef = createNavigationContainerRef();

/**
 * Deskripsi singkat:
 * Mereset seluruh navigation stack ke layar Login.
 * Dipakai untuk auto-logout ketika token JWT sudah expired (F-MOB-07).
 */
export function resetToLogin() {
    if (navigationRef.isReady()) {
        navigationRef.reset({
            index: 0,
            routes: [{ name: 'Login' }],
        });
    }
}
