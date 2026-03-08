import React, { useEffect, useState } from 'react';
import { View, StyleSheet, TouchableOpacity, Alert, Image } from 'react-native';
import { Text } from 'react-native-paper';
import { createBottomTabNavigator } from '@react-navigation/bottom-tabs';
import { useSafeAreaInsets } from 'react-native-safe-area-context';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';
import EvilIcons from 'react-native-vector-icons/EvilIcons';
import AntDesign from 'react-native-vector-icons/AntDesign';
import { format } from 'date-fns';
import api from '../services/api';

// Placeholder screens for individual tabs
import HomeScreen from '../screens/HomeScreen';
import ActivityScreen from '../screens/ActivityScreen';
import CheckInScreen from '../screens/CheckInScreen';
import ProfileScreen from '../screens/ProfileScreen';

const Tab = createBottomTabNavigator();

function Header() {
    const [userData, setUserData] = useState(null);

    useEffect(() => {
        const fetchUserData = async () => {
            try {
                const response = await api.get('/me');
                setUserData(response.data);
            } catch (error) {
                console.error('Failed to fetch user in header', error);
            }
        };
        fetchUserData();
    }, []);

    return (
        <View style={styles.header}>
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                <Image source={require('../assets/icon.png')} style={{ width: 28, height: 28, marginRight: 8, borderRadius: 6 }} />
                <Text variant="titleMedium" style={styles.headerTitle}>Pipitnesan</Text>
            </View>
            <View style={{ flexDirection: 'row', alignItems: 'center' }}>
                {userData && (
                    <View style={styles.statusBadge}>
                        <Text style={styles.statusText}>{userData.status === 'active' ? '✨ Active Member' : 'Inactive'}</Text>
                        <Text style={styles.activeUntilText}>Valid until: {userData.active_until ? format(new Date(userData.active_until), 'dd MMM yyyy') : 'N/A'}</Text>
                    </View>
                )}
                <TouchableOpacity onPress={() => Alert.alert('Notifications', 'No new notifications')} style={{ marginLeft: 12 }}>
                    <EvilIcons name="bell" size={30} color="#0f172a" />
                </TouchableOpacity>
            </View>
        </View>
    );
}

export default function MainTabs() {
    const insets = useSafeAreaInsets();

    return (
        <View style={{ flex: 1 }}>
            <Header />
            <Tab.Navigator
                screenOptions={({ route }) => ({
                    headerShown: false,
                    tabBarActiveTintColor: '#000',
                    tabBarInactiveTintColor: 'gray',
                    tabBarStyle: {
                        backgroundColor: '#fff',
                        borderTopWidth: 1,
                        borderTopColor: 'rgba(0,0,0,0.1)',
                        paddingBottom: 5 + insets.bottom,
                        paddingTop: 5,
                        height: 60 + insets.bottom
                    },
                    tabBarIcon: ({ color, size }) => {
                        if (route.name === 'Home') return <AntDesign name="home" size={26} color={color} style={{ marginBottom: -3 }} />;
                        if (route.name === 'My Activity') return <Icon name="text-box-outline" size={26} color={color} />; // Note: EvilIcons 'search' is for Explore, sticking to Activity note
                        if (route.name === 'Check IN') return <Icon name="qrcode" size={26} color={color} />; // Keeping QR code since it functionally makes sense over a pencil.
                        if (route.name === 'Profile') return <EvilIcons name="user" size={32} color={color} style={{ marginLeft: -1, marginBottom: -3 }} />;

                        return <Icon name="view-dashboard" size={size} color={color} />;
                    },
                })}
            >
                <Tab.Screen name="Home" component={HomeScreen} />
                <Tab.Screen name="My Activity" component={ActivityScreen} />
                <Tab.Screen name="Check IN" component={CheckInScreen} />
                <Tab.Screen name="Profile" component={ProfileScreen} />
            </Tab.Navigator>
        </View>
    );
}

const styles = StyleSheet.create({
    header: {
        height: 60,
        backgroundColor: '#fff',
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'space-between',
        paddingHorizontal: 16,
        paddingTop: 16,
        borderBottomWidth: 1,
        borderBottomColor: 'rgba(0,0,0,0.05)'
    },
    headerTitle: {
        color: '#9348cc',
        fontWeight: 'bold',
        fontSize: 22
    },
    statusBadge: {
        backgroundColor: '#e9d5ff',
        paddingHorizontal: 10,
        paddingVertical: 5,
        borderRadius: 8,
        alignItems: 'flex-end',
        justifyContent: 'center'
    },
    statusText: { color: '#9348cc', fontWeight: 'bold', fontSize: 11 },
    activeUntilText: { color: '#64748b', fontSize: 10, marginTop: 1 }
});
