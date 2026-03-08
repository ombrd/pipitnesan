import React, { useState } from 'react';
import { View, StyleSheet, Alert, TouchableOpacity } from 'react-native';
import { Text, List, Button, Avatar, Divider, Dialog, Portal } from 'react-native-paper';
import { useFocusEffect } from '@react-navigation/native';
import api from '../services/api';

export default function ProfileScreen({ navigation }) {
    const [userData, setUserData] = useState(null);
    const [isLogoutDialogVisible, setLogoutDialogVisible] = useState(false);

    useFocusEffect(
        React.useCallback(() => {
            fetchUserProfile();
        }, [])
    );

    const fetchUserProfile = async () => {
        try {
            const response = await api.get('/me');
            setUserData(response.data);
        } catch (error) {
            console.error(error);
        }
    };

    const handleClearCache = () => {
        Alert.alert(
            "Clear Cache",
            "Are you sure you want to clear local SQLite caches?",
            [
                { text: "Cancel", style: "cancel" },
                {
                    text: "OK", onPress: () => {
                        // Logic to drop sqlite tables or delete DB file
                        Alert.alert("Success", "Local cache cleared.");
                    }
                }
            ]
        );
    };

    const handleLogout = () => {
        setLogoutDialogVisible(true);
    };

    const confirmLogout = async () => {
        setLogoutDialogVisible(false);
        try {
            await api.post('/logout');
        } catch (e) {
            console.log('Logout API error, forcing local logout:', e);
        }
        global.userToken = null;
        navigation.reset({
            index: 0,
            routes: [{ name: 'Login' }],
        });
    };

    return (
        <View style={styles.container}>
            <View style={styles.header}>
                <Avatar.Text size={80} label={userData?.name?.substring(0, 2)?.toUpperCase() || 'NA'} style={styles.avatar} />
                <Text variant="headlineSmall" style={styles.name}>{userData?.name || 'Loading...'}</Text>
                <Text variant="bodyMedium" style={styles.status}>{userData?.status === 'active' ? 'Active Member' : 'Inactive'}</Text>
            </View>

            <List.Section style={styles.section}>
                <List.Subheader>Account Settings</List.Subheader>
                <List.Item title="Edit Profile" left={() => <List.Icon icon="account-edit" />} onPress={() => navigation.navigate('EditProfile')} />
                <Divider />
                <List.Item title="Clear Local Cache" description="Free up space by deleting SQLite caches" left={() => <List.Icon icon="database-refresh" />} onPress={handleClearCache} />
                <Divider />
            </List.Section>

            <Button mode="contained" buttonColor="#ef4444" onPress={handleLogout} style={styles.logoutBtn}>
                Logout
            </Button>



            <Portal>
                <Dialog visible={isLogoutDialogVisible} onDismiss={() => setLogoutDialogVisible(false)}>
                    <Dialog.Title>Logout</Dialog.Title>
                    <Dialog.Content>
                        <Text variant="bodyMedium">Are you sure you want to log out of your account?</Text>
                    </Dialog.Content>
                    <Dialog.Actions>
                        <Button onPress={() => setLogoutDialogVisible(false)}>Cancel</Button>
                        <Button onPress={confirmLogout} textColor="#ef4444">Logout</Button>
                    </Dialog.Actions>
                </Dialog>
            </Portal>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, backgroundColor: '#f1f5f9' },
    header: { alignItems: 'center', padding: 32, backgroundColor: '#fff' },
    avatar: { backgroundColor: '#9348cc', marginBottom: 16 },
    name: { fontWeight: 'bold', color: '#0f172a' },
    status: { color: '#7B68EE', fontWeight: 'bold', marginTop: 4 },
    section: { backgroundColor: '#fff', marginTop: 16 },
    logoutBtn: { margin: 24, borderRadius: 8 },
    modalContent: { backgroundColor: 'white', padding: 24, margin: 20, borderRadius: 12 }
});
