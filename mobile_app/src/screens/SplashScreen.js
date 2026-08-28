import React, { useEffect } from 'react';
import { View, Text, StyleSheet, Animated } from 'react-native';
import axios from 'axios';
import { initSessionTable, getTokens, saveTokens, clearTokens } from '../utils/tokenStorage';
import { API_BASE_URL } from '../config/api';

/**
 * Deskripsi singkat:
 * Layar Splash yang muncul saat aplikasi pertama kali dibuka.
 * Menangani dua tugas utama:
 * 1. Menampilkan animasi logo selama proses pengecekan sesi berlangsung.
 * 2. Memeriksa sesi yang tersimpan di SQLite dan mencoba memperbarui Access Token
 *    menggunakan Refresh Token agar pengguna tidak perlu login ulang setelah app ditutup.
 *
 * Alur navigasi:
 * - Ada Refresh Token valid di storage → GET /refresh → berhasil → navigasi ke MainTabs
 * - Refresh Token tidak ada atau gagal digunakan → navigasi ke Onboarding
 *
 * Parameter:
 * @param {Object} navigation Objek navigasi dari React Navigation.
 *
 * Return value:
 * @return {React.Component} Render elemen UI untuk layar Splash.
 */
export default function SplashScreen({ navigation }) {
    const fadeAnim = new Animated.Value(0);

    useEffect(() => {
        // Fade in animation untuk logo
        Animated.timing(fadeAnim, {
            toValue: 1,
            duration: 1500,
            useNativeDriver: true,
        }).start();

        // Jalankan pengecekan sesi di background
        checkSession();
    }, []);

    /**
     * Deskripsi singkat:
     * Memeriksa apakah ada token tersimpan dan mencoba memperbarui Access Token.
     * Jika berhasil, pengguna langsung diarahkan ke HomeScreen (MainTabs).
     * Jika tidak, diarahkan ke Onboarding (alur pertama kali atau setelah logout).
     *
     * Return value:
     * @return {Promise<void>}
     */
    const checkSession = async () => {
        try {
            // Pastikan tabel session sudah siap (termasuk kolom refresh_token)
            await initSessionTable();

            const { accessToken, refreshToken } = await getTokens();

            if (refreshToken) {
                // Ada Refresh Token → coba perbarui Access Token
                try {
                    const response = await axios.post(
                        `${API_BASE_URL}/refresh`,
                        { refresh_token: refreshToken },
                        { headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' } }
                    );

                    const newAccessToken = response.data.access_token;
                    const newRefreshToken = response.data.refresh_token;

                    // Simpan token baru ke global state dan SQLite
                    global.userToken = newAccessToken;
                    global.refreshToken = newRefreshToken;
                    await saveTokens(newAccessToken, newRefreshToken);

                    // Langsung ke halaman utama — pengguna sudah dianggap login
                    navigation.replace('MainTabs');
                    return;

                } catch (refreshErr) {
                    // Refresh Token tidak valid atau kedaluwarsa → hapus sesi dan minta login baru
                    console.log('[SplashScreen] Refresh token expired, clearing session.');
                    await clearTokens();
                }
            }

            // Tidak ada Refresh Token atau refresh gagal → ke Onboarding
            // Delay minimal agar animasi splash sempat muncul
            const delay = ms => new Promise(res => setTimeout(res, ms));
            await delay(2500);
            navigation.replace('Onboarding');

        } catch (error) {
            console.error('[SplashScreen] checkSession error:', error);
            navigation.replace('Onboarding');
        }
    };

    return (
        <View style={styles.container}>
            <Animated.View style={{ opacity: fadeAnim, alignItems: 'center' }}>
                <Text style={styles.logoText}>Pipitnesan</Text>
                <Text style={styles.subText}>Gym Management</Text>
            </Animated.View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#0f172a', // dark blue/gray
        alignItems: 'center',
        justifyContent: 'center',
    },
    logoText: {
        color: '#ef4444', // merah (identitas brand: hitam/merah/putih)
        fontSize: 42,
        fontWeight: 'bold',
        letterSpacing: 2,
    },
    subText: {
        color: '#cbd5e1', // slate 300
        fontSize: 16,
        marginTop: 8,
        letterSpacing: 1,
    },
});
