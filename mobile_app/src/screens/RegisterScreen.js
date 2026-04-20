import React, { useState, useEffect } from 'react';
import { View, StyleSheet, Alert, ScrollView, ImageBackground, TouchableOpacity, ActivityIndicator } from 'react-native';
import { Text, TextInput, Button, Dialog, Portal, Card, List, IconButton, Modal } from 'react-native-paper';
import { Calendar } from 'react-native-calendars';
import api from '../services/api';

/**
 * Deskripsi singkat:
 * Komponen layar Registrasi yang menangani pendaftaran member baru.
 * Mencakup pemilihan paket promosi, input data pribadi, dan pemilihan cabang home base.
 *
 * Parameter:
 * @param {Object} navigation Objek navigasi dari React Navigation.
 *
 * Return value:
 * @return {React.Component} Render elemen UI untuk layar Registrasi.
 */
export default function RegisterScreen({ navigation }) {
    const [form, setForm] = useState({
        name: '',
        phone: '',
        id_card_number: '',
        email: '',
        password: '',
        birth_place: '',
        birth_date: '',
        address: '',
        branch_id: null
    });

    const [loading, setLoading] = useState(false);
    const [errorDialogVisible, setErrorDialogVisible] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');

    const [promotions, setPromotions] = useState([]);
    const [branches, setBranches] = useState([]);
    const [selectedPromotion, setSelectedPromotion] = useState(null);
    const [fetchingData, setFetchingData] = useState(true);

    const [branchModalVisible, setBranchModalVisible] = useState(false);
    const [calendarVisible, setCalendarVisible] = useState(false);
    const [selectedBranchObj, setSelectedBranchObj] = useState(null);

    useEffect(() => {
        const fetchInitialData = async () => {
            try {
                const [promoRes, branchRes] = await Promise.all([
                    api.get('/promotions'),
                    api.get('/branches')
                ]);
                setPromotions(promoRes.data.data);
                setBranches(branchRes.data.data);
            } catch (error) {
                console.error("Failed to fetch initial data", error);
            } finally {
                setFetchingData(false);
            }
        };
        fetchInitialData();
    }, []);

    /**
     * Deskripsi singkat:
     * Menangani proses pendaftaran member baru dengan mengirimkan data form ke API /register.
     * Setelah sukses, token akan disimpan dan user diarahkan ke dashboard utama.
     *
     * Contoh penggunaan:
     * handleRegister() dipicu saat tombol "REGISTER & PAY" ditekan.
     */
    const handleRegister = async () => {
        if (!form.name || !form.email || !form.password || !form.id_card_number || !form.branch_id || !form.birth_place || !form.birth_date || !form.address) {
            setErrorMessage("Please fill all required fields before registering.");
            setErrorDialogVisible(true);
            return;
        }

        setLoading(true);
        try {
            const payload = { ...form, promotion_id: selectedPromotion.id };
            const response = await api.post('/register', payload);

            global.userToken = response.data.access_token;
            Alert.alert('Welcome!', `Registered successfully and Activated! You have chosen the ${selectedPromotion.name} package.`);
            navigation.replace('MainTabs');

        } catch (err) {
            const errorMsg = err.response?.data?.errors
                ? Object.values(err.response.data.errors).flat().join('\n')
                : (err.response?.data?.message || err.message || 'Registration failed');
            setErrorMessage(errorMsg);
            setErrorDialogVisible(true);
        } finally {
            setLoading(false);
        }
    };

    const handleDateSelect = (day) => {
        setForm({ ...form, birth_date: day.dateString });
        setCalendarVisible(false);
    };

    const selectBranch = (branch) => {
        setSelectedBranchObj(branch);
        setForm({ ...form, branch_id: branch.id });
        setBranchModalVisible(false);
    };

    return (
        <ImageBackground
            source={require('../assets/login/loginBackground.png')}
            style={styles.backgroundImage}
            imageStyle={{ opacity: 0.15 }}
        >
            <ScrollView contentContainerStyle={styles.container}>
                <View style={[styles.header, { flexDirection: 'row', alignItems: 'center' }]}>
                    {selectedPromotion && (
                        <IconButton
                            icon="arrow-left"
                            size={28}
                            onPress={() => setSelectedPromotion(null)}
                            style={{ position: 'absolute', left: 0, top: 0, margin: 0 }}
                            iconColor="#9348cc"
                        />
                    )}
                    <View style={{ flex: 1, alignItems: 'center' }}>
                        <Text variant="displaySmall" style={styles.title}>{selectedPromotion ? 'Join Us' : 'Select Package'}</Text>
                        <Text variant="bodyLarge" style={styles.subtitle}>{selectedPromotion ? 'Complete your details to finish registration' : 'Choose a membership promotion to start'}</Text>
                    </View>
                </View>

                {!selectedPromotion ? (
                    <View style={styles.formContainer}>
                        {fetchingData ? <ActivityIndicator size="large" color="#9348cc" /> : (
                            promotions.map(promo => (
                                <Card key={promo.id} style={styles.promoCard} onPress={() => setSelectedPromotion(promo)}>
                                    <List.Item
                                        title={promo.name}
                                        titleStyle={{ fontWeight: 'bold', fontSize: 18 }}
                                        description={`${promo.duration_days} days commitment\nRp ${parseFloat(promo.price).toLocaleString('id-ID')}`}
                                        descriptionNumberOfLines={2}
                                        left={props => <List.Icon {...props} icon="star-circle" color="#eab308" />}
                                        right={props => <List.Icon {...props} icon="chevron-right" />}
                                    />
                                </Card>
                            ))
                        )}
                        <View style={styles.footer}>
                            <Text style={{ color: '#000' }}>Already a member? </Text>
                            <TouchableOpacity onPress={() => navigation.goBack()}>
                                <Text style={{ color: '#9348cc', fontWeight: 'bold' }}>Login</Text>
                            </TouchableOpacity>
                        </View>
                    </View>
                ) : (
                    <View style={styles.formContainer}>
                        <Card style={{ marginBottom: 20, backgroundColor: '#fdf4ff', borderColor: '#d8b4fe', borderWidth: 1 }}>
                            <Card.Content>
                                <Text style={{ fontWeight: 'bold', color: '#9348cc', fontSize: 16, marginBottom: 4 }}>Selected Package: {selectedPromotion.name}</Text>
                                <Text style={{ color: '#475569', fontSize: 14 }}>Commitment: {selectedPromotion.duration_days} Days</Text>
                                <Text style={{ color: '#475569', fontSize: 14 }}>Price: Rp {parseFloat(selectedPromotion.price).toLocaleString('en-US')}</Text>
                            </Card.Content>
                        </Card>

                        <TextInput label="ID Card Number (NIK) *" value={form.id_card_number} onChangeText={t => setForm({ ...form, id_card_number: t })} mode="outlined" style={styles.input} keyboardType="numeric" theme={{ roundness: 25, colors: { primary: '#9348cc' } }} />
                        <TextInput label="Full Name *" value={form.name} onChangeText={t => setForm({ ...form, name: t })} mode="outlined" style={styles.input} theme={{ roundness: 25, colors: { primary: '#9348cc' } }} />
                        <TextInput label="Birth Place *" value={form.birth_place} onChangeText={t => setForm({ ...form, birth_place: t })} mode="outlined" style={styles.input} theme={{ roundness: 25, colors: { primary: '#9348cc' } }} />

                        <TouchableOpacity onPress={() => setCalendarVisible(true)}>
                            <TextInput
                                label="Birth Date (YYYY-MM-DD) *"
                                value={form.birth_date}
                                mode="outlined"
                                style={styles.input}
                                editable={false}
                                right={<TextInput.Icon icon="calendar" />}
                                theme={{ roundness: 25, colors: { primary: '#9348cc' } }}
                            />
                        </TouchableOpacity>

                        <TextInput label="Address *" value={form.address} onChangeText={t => setForm({ ...form, address: t })} mode="outlined" style={styles.input} multiline numberOfLines={3} theme={{ roundness: 25, colors: { primary: '#9348cc' } }} />

                        <TouchableOpacity onPress={() => setBranchModalVisible(true)}>
                            <TextInput
                                label="Home Branch *"
                                value={selectedBranchObj ? `${selectedBranchObj.name} - ${selectedBranchObj.address}` : ''}
                                mode="outlined"
                                style={styles.input}
                                editable={false}
                                right={<TextInput.Icon icon="chevron-down" />}
                                theme={{ roundness: 25, colors: { primary: '#9348cc' } }}
                            />
                        </TouchableOpacity>

                        <TextInput label="Phone Number *" value={form.phone} onChangeText={t => setForm({ ...form, phone: t })} mode="outlined" style={styles.input} keyboardType="phone-pad" theme={{ roundness: 25, colors: { primary: '#9348cc' } }} />
                        <TextInput label="Email *" value={form.email} onChangeText={t => setForm({ ...form, email: t })} mode="outlined" style={styles.input} autoCapitalize="none" keyboardType="email-address" theme={{ roundness: 25, colors: { primary: '#9348cc' } }} />
                        <TextInput label="Password *" value={form.password} onChangeText={t => setForm({ ...form, password: t })} mode="outlined" style={styles.input} secureTextEntry theme={{ roundness: 25, colors: { primary: '#9348cc' } }} />

                        <TouchableOpacity
                            style={styles.formButton}
                            onPress={handleRegister}
                            disabled={loading}
                        >
                            <Text style={styles.actionButtonText}>
                                {loading ? "PROCESSING..." : "REGISTER & PAY"}
                            </Text>
                        </TouchableOpacity>

                        <View style={styles.footer}>
                            <Button mode="text" onPress={() => setSelectedPromotion(null)} textColor="#64748b">
                                Change Package
                            </Button>
                        </View>
                    </View>
                )}
            </ScrollView>

            <Portal>
                {/* Error Dialog */}
                <Dialog visible={errorDialogVisible} onDismiss={() => setErrorDialogVisible(false)}>
                    <Dialog.Title>Registration Failed</Dialog.Title>
                    <Dialog.Content>
                        <Text variant="bodyMedium">{errorMessage}</Text>
                    </Dialog.Content>
                    <Dialog.Actions>
                        <Button onPress={() => setErrorDialogVisible(false)}>OK</Button>
                    </Dialog.Actions>
                </Dialog>

                {/* Date Picker Modal */}
                <Modal visible={calendarVisible} onDismiss={() => setCalendarVisible(false)} contentContainerStyle={styles.modalContent}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                        <Text variant="titleMedium" style={{ fontWeight: 'bold' }}>Select Birth Date</Text>
                        <IconButton icon="close" size={24} onPress={() => setCalendarVisible(false)} style={{ margin: 0 }} />
                    </View>
                    <Calendar
                        onDayPress={handleDateSelect}
                        markedDates={{
                            [form.birth_date]: { selected: true, selectedColor: '#9348cc' }
                        }}
                        theme={{
                            todayTextColor: '#9348cc',
                            arrowColor: '#9348cc',
                        }}
                    />
                </Modal>

                {/* Branch Selection Modal */}
                <Modal visible={branchModalVisible} onDismiss={() => setBranchModalVisible(false)} contentContainerStyle={styles.modalContent}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                        <Text variant="titleMedium" style={{ fontWeight: 'bold' }}>Select Home Branch</Text>
                        <IconButton icon="close" size={24} onPress={() => setBranchModalVisible(false)} style={{ margin: 0 }} />
                    </View>
                    <ScrollView style={{ maxHeight: 300 }}>
                        {branches.map(branch => (
                            <List.Item
                                key={branch.id}
                                title={branch.name}
                                description={branch.address}
                                onPress={() => selectBranch(branch)}
                                left={props => <List.Icon {...props} icon="map-marker-outline" color="#9348cc" />}
                                style={{ borderBottomWidth: 1, borderBottomColor: '#f1f5f9' }}
                            />
                        ))}
                    </ScrollView>
                </Modal>
            </Portal>
        </ImageBackground>
    );
}

const styles = StyleSheet.create({
    backgroundImage: {
        flex: 1,
        backgroundColor: '#f1f5f9'
    },
    container: {
        flexGrow: 1,
        paddingVertical: 50,
        justifyContent: 'center',
    },
    formContainer: {
        paddingHorizontal: 20,
    },
    header: {
        marginBottom: 30,
        paddingHorizontal: 20,
        position: 'relative'
    },
    title: {
        fontWeight: 'bold',
        color: '#9348cc',
        fontSize: 32,
    },
    subtitle: {
        color: '#64748b',
        marginTop: 8,
        textAlign: 'center'
    },
    promoCard: {
        marginBottom: 16,
        backgroundColor: '#fff',
        borderLeftWidth: 4,
        borderLeftColor: '#9348cc',
    },
    input: {
        marginBottom: 16,
        backgroundColor: '#fff',
    },
    formButton: {
        backgroundColor: "rgba(123,104,238,0.9)",
        height: 55,
        alignItems: "center",
        justifyContent: "center",
        borderRadius: 35,
        marginVertical: 10,
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.25,
        shadowRadius: 3.84,
        elevation: 5,
    },
    actionButtonText: {
        fontSize: 20,
        fontWeight: "600",
        color: "white",
        letterSpacing: 0.5,
    },
    footer: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 20
    },
    modalContent: {
        backgroundColor: 'white',
        padding: 20,
        margin: 20,
        borderRadius: 12,
    }
});
