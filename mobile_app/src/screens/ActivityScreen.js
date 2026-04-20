import React, { useState, useEffect } from 'react';
import { View, StyleSheet, useWindowDimensions, FlatList, ActivityIndicator } from 'react-native';
import { Text, SegmentedButtons, Card, List, Button, Dialog, Portal, TextInput, IconButton } from 'react-native-paper';
import { TabView, SceneMap } from 'react-native-tab-view';
import api from '../services/api';
import { format } from 'date-fns';

const EmptyState = ({ message }) => (
    <View style={styles.scene}>
        <Text style={styles.emptyText}>{message}</Text>
    </View>
);

/**
 * Deskripsi singkat:
 * Komponen Tab yang menampilkan daftar riwayat booking Personal Trainer (PT) milik user.
 * Pengguna dapat melihat status booking dan membatalkan pesanan yang akan datang (future booking).
 */
const BookingsTab = () => {
    const [bookings, setBookings] = useState([]);
    const [loading, setLoading] = useState(true);
    const [cancelDialogVisible, setCancelDialogVisible] = useState(false);
    const [cancelReason, setCancelReason] = useState('');
    const [selectedBookingId, setSelectedBookingId] = useState(null);
    const [isCancelling, setIsCancelling] = useState(false);

    const fetchBookings = async () => {
        try {
            const response = await api.get('/bookings');
            setBookings(response.data.data);
        } catch (error) {
            console.error("Failed to fetch bookings", error);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchBookings();
    }, []);

    const promptCancel = (bookingId) => {
        setSelectedBookingId(bookingId);
        setCancelReason('');
        setCancelDialogVisible(true);
    };

    /**
     * Deskripsi singkat:
     * Melakukan pembatalan booking PT ke server backend dengan alasan tertentu.
     * Setelah sukses, daftar booking akan diperbarui secara otomatis.
     *
     * Contoh penggunaan:
     * confirmCancel() dipicu saat user menekan tombol konfirmasi pada dialog pembatalan.
     */
    const confirmCancel = async () => {
        if (!cancelReason.trim()) {
            return alert("Please provide a reason for cancellation.");
        }
        setIsCancelling(true);
        try {
            await api.post(`/pts/book/${selectedBookingId}/cancel`, { cancel_reason: cancelReason });
            setCancelDialogVisible(false);
            fetchBookings(); // Refresh list to reflect cancelled status
        } catch (error) {
            alert(error.response?.data?.error || "Failed to cancel booking");
        } finally {
            setIsCancelling(false);
        }
    };

    if (loading) return <View style={styles.scene}><ActivityIndicator color="#9348cc" /></View>;
    if (bookings.length === 0) return <EmptyState message="You have no PT bookings." />;

    const isFuture = (dateStr) => {
        if (!dateStr) return false;
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const schedDate = new Date(dateStr);
        return schedDate >= today;
    };

    return (
        <View style={{ flex: 1 }}>
            <FlatList
                data={bookings}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={styles.listContent}
                renderItem={({ item }) => (
                    <Card style={styles.card}>
                        <List.Item
                            title={`${item.schedule?.trainer?.name || 'Trainer'} - ${item.schedule?.date}`}
                            description={`Status: ${item.status}\nTime: ${item.schedule?.time_start?.substring(0, 5)} - ${item.schedule?.time_end?.substring(0, 5)}`}
                            left={props => <List.Icon {...props} icon="calendar-check" color={item.status === 'booked' ? '#f59e0b' : (item.status === 'cancelled' ? '#ef4444' : '#10b981')} />}
                        />
                        {(item.status === 'booked' && isFuture(item.schedule?.date)) && (
                            <Card.Actions>
                                <Button mode="outlined" textColor="#ef4444" style={{ borderColor: '#ef4444' }} onPress={() => promptCancel(item.id)}>
                                    Cancel Booking
                                </Button>
                            </Card.Actions>
                        )}
                        {item.status === 'cancelled' && item.cancel_reason && (
                            <Card.Content>
                                <Text style={{ color: '#ef4444', fontSize: 12, marginTop: 4 }}>Reason: {item.cancel_reason}</Text>
                            </Card.Content>
                        )}
                    </Card>
                )}
            />

            <Portal>
                <Dialog visible={cancelDialogVisible} onDismiss={() => setCancelDialogVisible(false)}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', paddingHorizontal: 24, paddingTop: 20 }}>
                        <Text variant="titleLarge" style={{ fontWeight: 'bold' }}>Cancel Booking</Text>
                        <IconButton icon="close" size={24} onPress={() => setCancelDialogVisible(false)} style={{ margin: 0 }} />
                    </View>
                    <Dialog.Content style={{ marginTop: 16 }}>
                        <Text variant="bodyMedium" style={{ marginBottom: 12 }}>Please provide a reason for cancelling this PT session. Note that you can only cancel &gt; 1 hour before the start time.</Text>
                        <TextInput
                            label="Reason for cancellation"
                            value={cancelReason}
                            onChangeText={setCancelReason}
                            mode="outlined"
                            multiline
                            numberOfLines={3}
                            theme={{ colors: { primary: '#ef4444' } }}
                        />
                    </Dialog.Content>
                    <Dialog.Actions>
                        <Button onPress={() => setCancelDialogVisible(false)}>Back</Button>
                        <Button onPress={confirmCancel} loading={isCancelling} textColor="#ef4444">Confirm Cancel</Button>
                    </Dialog.Actions>
                </Dialog>
            </Portal>
        </View>
    );
};

/**
 * Deskripsi singkat:
 * Komponen Tab yang menampilkan daftar riwayat kunjungan (visit history) user ke gym.
 * Data diambil dari server dan disusun berdasarkan tanggal kunjungan terbaru.
 */
const VisitsTab = () => {
    const [visits, setVisits] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        const fetchVisits = async () => {
            try {
                const response = await api.get('/visits');
                setVisits(response.data.data);
            } catch (error) {
                console.error("Failed to fetch visits", error);
            } finally {
                setLoading(false);
            }
        };
        fetchVisits();
    }, []);

    if (loading) return <View style={styles.scene}><ActivityIndicator color="#9348cc" /></View>;
    if (visits.length === 0) return <EmptyState message="You haven't visited the gym yet." />;

    return (
        <FlatList
            data={visits}
            keyExtractor={item => item.id.toString()}
            contentContainerStyle={styles.listContent}
            renderItem={({ item }) => (
                <Card style={styles.card}>
                    <List.Item
                        title="Gym Entry Checked"
                        description={format(new Date(item.created_at), 'dd MMM yyyy, HH:mm')}
                        left={props => <List.Icon {...props} icon="door-open" color="#9348cc" />}
                    />
                </Card>
            )}
        />
    );
};

const renderScene = SceneMap({
    first: BookingsTab,
    second: VisitsTab,
});

/**
 * Deskripsi singkat:
 * Komponen layar Aktivitas utama yang menggunakan TabView untuk navigasi antara Riwayat Booking dan Riwayat Kunjungan.
 * Mengintegrasikan SegmentedButtons untuk kontrol tab yang lebih modern.
 *
 * Return value:
 * @return {React.Component} Render elemen UI untuk layar Aktivitas.
 */
export default function ActivityScreen() {
    const layout = useWindowDimensions();
    const [index, setIndex] = useState(0);
    const [routes] = useState([
        { key: 'first', title: 'PT Bookings' },
        { key: 'second', title: 'Visit History' },
    ]);

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <Text variant="headlineSmall" style={styles.title}>My Activity</Text>
                <SegmentedButtons
                    value={routes[index].key}
                    onValueChange={val => setIndex(routes.findIndex(r => r.key === val))}
                    buttons={[
                        { value: 'first', label: 'Bookings' },
                        { value: 'second', label: 'Visits' }
                    ]}
                />
            </View>

            <TabView
                navigationState={{ index, routes }}
                renderScene={renderScene}
                onIndexChange={setIndex}
                initialLayout={{ width: layout.width }}
                renderTabBar={() => null} // Hide native tab view bar since we use segmented buttons
            />
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f1f5f9' },
    header: { padding: 16, backgroundColor: '#fff', paddingBottom: 24, elevation: 4 },
    title: { fontWeight: 'bold', marginBottom: 16, color: '#0f172a' },
    scene: { flex: 1, alignItems: 'center', justifyContent: 'center' },
    emptyText: { color: '#64748b' },
    listContent: { padding: 16 },
    card: { marginBottom: 12, backgroundColor: '#fff' }
});
