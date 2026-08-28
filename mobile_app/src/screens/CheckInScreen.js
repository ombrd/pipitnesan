import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ActivityIndicator } from 'react-native';
import { Text, Button } from 'react-native-paper';
import QRCode from 'react-native-qrcode-svg';
import api from '../services/api';

/**
 * Deskripsi singkat:
 * Komponen layar Check-In yang menghasilkan QR Code dinamis untuk absensi member.
 * QR Code ini memiliki masa berlaku terbatas (60 detik) dan akan diperbarui secara otomatis.
 *
 * Return value:
 * @return {React.Component} Render elemen UI untuk layar Check-In.
 */
export default function CheckInScreen() {
    const [qrData, setQrData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);
    const [timeLeft, setTimeLeft] = useState(0);

    /**
     * Deskripsi singkat:
     * Mengambil data token QR Code baru dari server backend.
     * Token ini nantinya akan di-render menjadi QR Code menggunakan library react-native-qrcode-svg.
     * Pesan error dari backend (mis. "Membership is not active") ditampilkan ke user
     * agar penyebab kegagalan jelas, bukan sekadar "Error loading QR Code".
     *
     * Contoh penggunaan:
     * fetchQRCode() dipicu saat layar pertama kali dibuka atau saat timer habis.
     */
    const fetchQRCode = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await api.get('/qr/generate');
            const data = response.data;

            if (data.qr_code_data) {
                setQrData(data.qr_code_data);
                setTimeLeft(60); // 1 minute expiry
            }
        } catch (error) {
            console.error('Failed to fetch QR', error);
            setError(
                error?.response?.data?.error ||
                error?.message ||
                'Failed to load QR Code'
            );
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchQRCode();
    }, []);

    useEffect(() => {
        if (timeLeft <= 0) {
            if (qrData) {
                // Auto refresh
                fetchQRCode();
            }
            return;
        }

        const intervalId = setInterval(() => {
            setTimeLeft((prev) => prev - 1);
        }, 1000);

        return () => clearInterval(intervalId);
    }, [timeLeft, qrData]);

    return (
        <View style={styles.container}>
            <Text variant="headlineSmall" style={styles.title}>Check IN</Text>
            <Text variant="bodyLarge" style={styles.subtitle}>Scan this at the receptionist</Text>

            <View style={styles.qrContainer}>
                {loading ? (
                    <ActivityIndicator size="large" color="#dc2626" />
                ) : qrData ? (
                    <QRCode
                        value={qrData}
                        size={250}
                        color="black"
                        backgroundColor="white"
                    />
                ) : (
                    <View style={styles.errorContainer}>
                        <Text variant="titleMedium" style={styles.errorTitle}>
                            Unable to generate QR Code
                        </Text>
                        {error === 'Membership is not active' ? (
                            <>
                                <Text style={styles.errorText}>
                                    Your membership is not active.
                                </Text>
                                <Text style={styles.errorText}>
                                    Please renew your membership at the receptionist to check in.
                                </Text>
                            </>
                        ) : (
                            <Text style={styles.errorText}>{error}</Text>
                        )}
                    </View>
                )}
            </View>

            {qrData && (
                <Text style={styles.timerText}>Code refreshes in: {timeLeft}s</Text>
            )}

            <Button
                mode="contained"
                onPress={fetchQRCode}
                style={styles.refreshBtn}
                disabled={loading}
                buttonColor="#dc2626"
            >
                Manual Refresh
            </Button>
        </View>
    );
}

const styles = StyleSheet.create({
    container: {
        flex: 1,
        backgroundColor: '#f1f5f9',
        alignItems: 'center',
        padding: 24,
    },
    title: { fontWeight: 'bold', color: '#0f172a' },
    subtitle: { color: '#64748b', marginBottom: 32 },
    qrContainer: {
        width: 300,
        height: 300,
        backgroundColor: '#fff',
        borderRadius: 24,
        alignItems: 'center',
        justifyContent: 'center',
        shadowColor: '#000',
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.1,
        shadowRadius: 10,
        elevation: 5,
        marginBottom: 24
    },
    errorContainer: {
        alignItems: 'center',
        paddingHorizontal: 24,
    },
    errorTitle: {
        fontWeight: 'bold',
        color: '#0f172a',
        marginBottom: 8,
        textAlign: 'center',
    },
    errorText: {
        color: '#64748b',
        textAlign: 'center',
        marginBottom: 4,
    },
    timerText: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#dc2626',
        marginBottom: 24
    },
    refreshBtn: {
        borderRadius: 25,
        width: 200
    }
});
