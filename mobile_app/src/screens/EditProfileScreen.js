import React, { useState, useEffect } from 'react';
import { View, StyleSheet, ScrollView, Alert, TouchableOpacity } from 'react-native';
import { Text, Button, TextInput, IconButton, Portal, Modal } from 'react-native-paper';
import { Calendar } from 'react-native-calendars';
import api from '../services/api';

export default function EditProfileScreen({ navigation }) {
    const [editForm, setEditForm] = useState({ id_card_number: '', name: '', phone: '', email: '', birth_place: '', birth_date: '', address: '' });
    const [isSaving, setIsSaving] = useState(false);
    const [isCalendarVisible, setCalendarVisible] = useState(false);

    useEffect(() => {
        fetchUserProfile();
    }, []);

    const fetchUserProfile = async () => {
        try {
            const response = await api.get('/me');
            setEditForm({
                id_card_number: response.data.id_card_number || '',
                name: response.data.name || '',
                birth_place: response.data.birth_place || '',
                birth_date: response.data.birth_date || '',
                address: response.data.address || '',
                phone: response.data.phone || '',
                email: response.data.email || '',
            });
        } catch (error) {
            console.error(error);
            Alert.alert("Error", "Failed to load profile details.");
        }
    };

    const handleSaveProfile = async () => {
        setIsSaving(true);
        try {
            await api.put('/me', editForm);
            Alert.alert("Success", "Profile updated successfully!", [
                { text: "OK", onPress: () => navigation.goBack() }
            ]);
        } catch (error) {
            Alert.alert("Error", error.response?.data?.message || "Failed to update profile");
        } finally {
            setIsSaving(false);
        }
    };

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <IconButton icon="arrow-left" size={24} onPress={() => navigation.goBack()} />
                <Text variant="titleLarge" style={styles.title}>Edit Profile</Text>
                <View style={{ width: 48 }} />
            </View>

            <ScrollView style={styles.content} contentContainerStyle={{ paddingBottom: 40 }}>
                <TextInput
                    label="ID Card Number (NIK)"
                    value={editForm.id_card_number}
                    mode="outlined"
                    disabled
                    style={styles.input}
                    theme={{ roundness: 12 }}
                />

                <TextInput
                    label="Full Name"
                    value={editForm.name}
                    onChangeText={t => setEditForm({ ...editForm, name: t })}
                    mode="outlined"
                    style={styles.input}
                    theme={{ roundness: 12 }}
                />

                <TextInput
                    label="Birth Place"
                    value={editForm.birth_place}
                    onChangeText={t => setEditForm({ ...editForm, birth_place: t })}
                    mode="outlined"
                    style={styles.input}
                    theme={{ roundness: 12 }}
                />

                <TouchableOpacity onPress={() => setCalendarVisible(true)}>
                    <TextInput
                        label="Birth Date (YYYY-MM-DD)"
                        value={editForm.birth_date}
                        mode="outlined"
                        style={styles.input}
                        editable={false}
                        right={<TextInput.Icon icon="calendar" />}
                        theme={{ roundness: 12 }}
                    />
                </TouchableOpacity>

                <TextInput
                    label="Address"
                    value={editForm.address}
                    onChangeText={t => setEditForm({ ...editForm, address: t })}
                    mode="outlined"
                    multiline
                    style={styles.input}
                    theme={{ roundness: 12 }}
                />

                <TextInput
                    label="Phone Number"
                    value={editForm.phone}
                    onChangeText={t => setEditForm({ ...editForm, phone: t })}
                    mode="outlined"
                    keyboardType="phone-pad"
                    style={styles.input}
                    theme={{ roundness: 12 }}
                />

                <TextInput
                    label="Email"
                    value={editForm.email}
                    onChangeText={t => setEditForm({ ...editForm, email: t })}
                    mode="outlined"
                    keyboardType="email-address"
                    autoCapitalize="none"
                    style={[styles.input, { marginBottom: 32 }]}
                    theme={{ roundness: 12 }}
                />

                <Button
                    mode="contained"
                    onPress={handleSaveProfile}
                    loading={isSaving}
                    buttonColor="#9348cc"
                    style={styles.saveBtn}
                >
                    Save Changes
                </Button>
            </ScrollView>

            <Portal>
                <Modal visible={isCalendarVisible} onDismiss={() => setCalendarVisible(false)} contentContainerStyle={styles.modalContent}>
                    <View style={{ flexDirection: 'row', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
                        <Text variant="titleMedium" style={{ fontWeight: 'bold' }}>Select Birth Date</Text>
                        <IconButton icon="close" size={24} onPress={() => setCalendarVisible(false)} style={{ margin: 0 }} />
                    </View>
                    <Calendar
                        onDayPress={(day) => {
                            setEditForm({ ...editForm, birth_date: day.dateString });
                            setCalendarVisible(false);
                        }}
                        markedDates={{
                            [editForm.birth_date]: { selected: true, disableTouchEvent: true, selectedColor: '#9348cc' }
                        }}
                    />
                </Modal>
            </Portal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f1f5f9' },
    header: { flexDirection: 'row', alignItems: 'center', justifyContent: 'space-between', backgroundColor: '#fff', elevation: 2, paddingVertical: 8, paddingHorizontal: 4 },
    title: { fontWeight: 'bold', color: '#0f172a' },
    content: { padding: 20 },
    input: { marginBottom: 16, backgroundColor: '#fff' },
    saveBtn: { borderRadius: 25, paddingVertical: 6 },
    modalContent: { backgroundColor: 'white', padding: 20, margin: 20, borderRadius: 12 }
});
