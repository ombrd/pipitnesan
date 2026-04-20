import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, Alert, ActivityIndicator } from 'react-native';
import { Text, Card, Button, Avatar, List, Modal, Portal, Dialog, IconButton } from 'react-native-paper';
import { Calendar } from 'react-native-calendars';
import { format } from 'date-fns';
import api from '../services/api';

/**
 * Deskripsi singkat:
 * Komponen layar Dashboard utama (Home) yang menampilkan daftar cabang, trainer, dan jadwal booking.
 * Memberikan fitur untuk memilih cabang, trainer, dan melakukan reservasi jadwal latihan.
 *
 * Parameter:
 * @param {Object} navigation Objek navigasi dari React Navigation.
 *
 * Return value:
 * @return {React.Component} Render elemen UI untuk layar Home.
 */
export default function HomeScreen({ navigation }) {
    const [branches, setBranches] = useState([]);
    const [selectedBranch, setSelectedBranch] = useState(null);
    const [trainers, setTrainers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedTrainer, setSelectedTrainer] = useState(null);
    const [selectedDate, setSelectedDate] = useState(format(new Date(), 'yyyy-MM-dd'));
    const [schedules, setSchedules] = useState([]);
    const [calendarVisible, setCalendarVisible] = useState(false);
    const [bookingDialogVisible, setBookingDialogVisible] = useState(false);
    const [selectedScheduleId, setSelectedScheduleId] = useState(null);

    useEffect(() => {
        fetchBranches();
    }, []);

    /**
     * Deskripsi singkat:
     * Mengambil daftar cabang yang tersedia dari server backend.
     * Secara default akan memilih cabang pertama setelah data berhasil diambil.
     */
    const fetchBranches = async () => {
        try {
            const response = await api.get('/branches');
            setBranches(response.data.data);
            if (response.data.data.length > 0) {
                handleBranchSelect(response.data.data[0]);
            }
        } catch (error) {
            console.error('Failed to fetch branches', error);
        } finally {
            setLoading(false);
        }
    };

    const handleBranchSelect = async (branch) => {
        setSelectedBranch(branch);
        setSelectedTrainer(null);
        setSchedules([]);
        fetchTrainers(branch.id);
    };

    /**
     * Deskripsi singkat:
     * Mengambil daftar Personal Trainer (PT) yang bertugas di cabang tertentu.
     *
     * Parameter:
     * @param {number} branchId ID cabang yang dipilih.
     */
    const fetchTrainers = async (branchId) => {
        try {
            const response = await api.get(`/pts?branch_id=${branchId}`);
            setTrainers(response.data.data);
            if (response.data.data.length > 0) {
                // Select first trainer by default
                handleTrainerSelect(response.data.data[0]);
            }
        } catch (error) {
            console.error('Failed to fetch PTs', error);
        } finally {
            setLoading(false);
        }
    };

    const handleTrainerSelect = async (trainer) => {
        setSelectedTrainer(trainer);
        fetchSchedules(trainer.id, selectedDate);
    };

    const handleDateSelect = async (day) => {
        setSelectedDate(day.dateString);
        setCalendarVisible(false);
        if (selectedTrainer) {
            fetchSchedules(selectedTrainer.id, day.dateString);
        }
    };

    const fetchSchedules = async (trainerId, date) => {
        try {
            const response = await api.get(`/pts/${trainerId}/schedules?date=${date}`);
            setSchedules(response.data.data);
        } catch (error) {
            console.error('Failed to fetch schedules', error);
        }
    };

    const promptBookSchedule = (scheduleId) => {
        setSelectedScheduleId(scheduleId);
        setBookingDialogVisible(true);
    }

    /**
     * Deskripsi singkat:
     * Melakukan proses booking jadwal PT ke server backend.
     * Akan menampilkan notifikasi sukses/gagal setelah proses selesai.
     *
     * Contoh penggunaan:
     * confirmBookSchedule() dipicu setelah user menekan tombol konfirmasi di Dialog.
     */
    const confirmBookSchedule = async () => {
        setBookingDialogVisible(false);
        try {
            await api.post('/pts/book', { pt_schedule_id: selectedScheduleId });
            Alert.alert("Success", "Schedule booked successfully!");
            // Refresh counts
            if (selectedTrainer) {
                fetchSchedules(selectedTrainer.id, selectedDate);
            }
        } catch (error) {
            Alert.alert("Error", error.response?.data?.error || error.response?.data?.message || "Failed to book");
        }
    }

    if (loading) {
        return (
            <View style={[styles.container, { justifyContent: 'center' }]}>
                <ActivityIndicator size="large" color="#9348cc" />
            </View>
        );
    }

    return (
        <ScrollView style={styles.container}>
            <View style={styles.topHeader}>
                <Text variant="headlineSmall" style={styles.title}>Welcome to Pipitnesan</Text>
            </View>

            <Text variant="titleMedium" style={styles.sectionTitle}>Select Branch</Text>
            <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.ptList}>
                {branches.map(branch => (
                    <Card
                        key={branch.id}
                        style={[styles.ptCard, selectedBranch?.id === branch.id && styles.ptCardSelected]}
                        onPress={() => handleBranchSelect(branch)}
                    >
                        <Card.Content style={styles.ptCardContent}>
                            <List.Icon icon="map-marker-outline" color={selectedBranch?.id === branch.id ? '#9348cc' : '#334155'} />
                            <Text variant="bodyMedium" style={{ marginTop: 2, fontWeight: 'bold' }}>{branch.name}</Text>
                        </Card.Content>
                    </Card>
                ))}
            </ScrollView>

            <Text variant="titleMedium" style={styles.sectionTitle}>Active Personal Trainers</Text>
            {trainers.length === 0 ? (
                <Text style={{ color: '#64748b', marginBottom: 24 }}>No trainers available here.</Text>
            ) : (
                <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.ptList}>
                    {trainers.map(pt => (
                        <Card
                            key={pt.id}
                            style={[styles.ptCard, selectedTrainer?.id === pt.id && styles.ptCardSelected]}
                            onPress={() => handleTrainerSelect(pt)}
                        >
                            <Card.Content style={styles.ptCardContent}>
                                <Avatar.Text size={50} label={pt.name.substring(0, 2).toUpperCase()} style={{ backgroundColor: '#e9d5ff' }} />
                                <Text variant="bodyMedium" style={{ marginTop: 8, fontWeight: 'bold' }}>{pt.name}</Text>
                            </Card.Content>
                        </Card>
                    ))}
                </ScrollView>
            )}

            <View style={styles.calendarHeader}>
                <Text variant="titleMedium" style={styles.sectionTitle}>Schedules for {selectedDate}</Text>
                <Button mode="text" onPress={() => setCalendarVisible(true)}>Change Date</Button>
            </View>

            {schedules.length === 0 ? (
                <Card style={styles.card}>
                    <Card.Content>
                        <Text style={{ color: '#64748b' }}>No schedules available for this day.</Text>
                    </Card.Content>
                </Card>
            ) : (
                schedules.map(schedule => (
                    <Card key={schedule.id} style={styles.card}>
                        <List.Item
                            title={`${schedule.time_start.substring(0, 5)} - ${schedule.time_end.substring(0, 5)}`}
                            description={`Quota remaining: ${schedule.remaining_quota ?? schedule.quota}`}
                            left={props => <List.Icon {...props} icon="clock-outline" />}
                            right={props => (
                                <Button
                                    mode="contained"
                                    buttonColor="#9348cc"
                                    style={{ borderRadius: 25 }}
                                    onPress={() => promptBookSchedule(schedule.id)}>
                                    Book
                                </Button>
                            )}
                        />
                    </Card>
                ))
            )}

            <Portal>
                <Modal visible={calendarVisible} onDismiss={() => setCalendarVisible(false)} contentContainerStyle={styles.modalContent}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                        <Text variant="titleMedium" style={{ fontWeight: 'bold' }}>Select Date</Text>
                        <IconButton icon="close" size={24} onPress={() => setCalendarVisible(false)} style={{ margin: 0 }} />
                    </View>
                    <Calendar
                        onDayPress={handleDateSelect}
                        markedDates={{
                            [selectedDate]: { selected: true, disableTouchEvent: true, selectedColor: '#9348cc' }
                        }}
                        minDate={format(new Date(), 'yyyy-MM-dd')}
                    />
                </Modal>
            </Portal>

            <Portal>
                <Dialog visible={bookingDialogVisible} onDismiss={() => setBookingDialogVisible(false)}>
                    <Dialog.Title>Confirm Booking</Dialog.Title>
                    <Dialog.Content>
                        <Text variant="bodyMedium">Are you sure you want to book this schedule slot with {selectedTrainer?.name} on {selectedDate}?</Text>
                    </Dialog.Content>
                    <Dialog.Actions>
                        <Button onPress={() => setBookingDialogVisible(false)}>Cancel</Button>
                        <Button onPress={confirmBookSchedule} mode="contained" buttonColor="#9348cc" style={{ borderRadius: 25 }}>Yes, Book It</Button>
                    </Dialog.Actions>
                </Dialog>
            </Portal>

        </ScrollView>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f1f5f9', padding: 16 },
    topHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'flex-start', marginBottom: 24 },
    title: { fontWeight: 'bold', color: '#0f172a', flex: 1 },
    sectionTitle: { fontWeight: 'bold', marginBottom: 8, color: '#334155' },
    card: { marginBottom: 12, backgroundColor: '#fff' },
    ptList: { marginBottom: 24 },
    ptCard: { marginRight: 12, width: 120, backgroundColor: '#fff' },
    ptCardSelected: { borderColor: '#9348cc', borderWidth: 2 },
    ptCardContent: { alignItems: 'center' },
    calendarHeader: { flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 },
    modalContent: { backgroundColor: 'white', padding: 20, margin: 20, borderRadius: 12 }
});
