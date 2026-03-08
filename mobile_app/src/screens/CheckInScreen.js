import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ActivityIndicator } from 'react-native';
import { Text, Button } from 'react-native-paper';
import QRCode from 'react-native-qrcode-svg';
import api from '../services/api';

export default function CheckInScreen() {
    const [qrData, setQrData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [timeLeft, setTimeLeft] = useState(0);

    const fetchQRCode = async () => {
        setLoading(true);
        try {
            const response = await api.get('/qr/generate');
            const data = response.data;

            if (data.qr_code_data) {
                setQrData(data.qr_code_data);
                setTimeLeft(60); // 1 minute expiry
            }
        } catch (error) {
            console.error('Failed to fetch QR', error);
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
                    <ActivityIndicator size="large" color="#9348cc" />
                ) : qrData ? (
                    <QRCode
                        value={qrData}
                        size={250}
                        color="black"
                        backgroundColor="white"
                    />
                ) : (
                    <Text>Error loading QR Code</Text>
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
                buttonColor="#9348cc"
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
    timerText: {
        fontSize: 16,
        fontWeight: 'bold',
        color: '#7B68EE',
        marginBottom: 24
    },
    refreshBtn: {
        borderRadius: 25,
        width: 200
    }
});
