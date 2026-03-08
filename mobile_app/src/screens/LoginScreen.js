import React, { useState, useEffect } from 'react';
import { View, StyleSheet, Alert, Dimensions, ImageBackground, TouchableOpacity } from 'react-native';
import { Text, TextInput, Button, Dialog, Portal } from 'react-native-paper';

const { width, height } = Dimensions.get("window");
import ReactNativeBiometrics from 'react-native-biometrics';
import SQLite from 'react-native-sqlite-storage';
import api from '../services/api';

const db = SQLite.openDatabase({ name: 'pipitnesan.db', location: 'default' }, () => { }, error => console.log(error));

export default function LoginScreen({ navigation }) {
    const [memberNumber, setMemberNumber] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [biometryType, setBiometryType] = useState(null);
    const [errorDialogVisible, setErrorDialogVisible] = useState(false);
    const [errorMessage, setErrorMessage] = useState('');

    const rnBiometrics = new ReactNativeBiometrics();

    useEffect(() => {
        db.transaction(tx => {
            tx.executeSql('CREATE TABLE IF NOT EXISTS session (id INTEGER PRIMARY KEY AUTOINCREMENT, token TEXT)');
        });

        rnBiometrics.isSensorAvailable()
            .then((resultObject) => {
                const { available, biometryType } = resultObject;
                if (available) {
                    setBiometryType(biometryType);
                }
            })
            .catch(error => console.log('biometrics error', error));
    }, []);

    const handleLogin = async () => {
        if (!memberNumber || !password) {
            setErrorMessage("Please enter both Member Number and Password.");
            setErrorDialogVisible(true);
            return;
        }

        setLoading(true);
        try {
            const response = await api.post('/login', {
                member_number: memberNumber,
                password
            });

            global.userToken = response.data.access_token;

            db.transaction(tx => {
                tx.executeSql('DELETE FROM session', [], () => {
                    tx.executeSql('INSERT INTO session (token) VALUES (?)', [response.data.access_token]);
                });
            });

            navigation.replace('MainTabs');

        } catch (err) {
            setErrorMessage(err.response?.data?.error || err.response?.data?.message || err.message || 'Login failed');
            setErrorDialogVisible(true);
        } finally {
            setLoading(false);
        }
    };

    const handleBiometricAuth = async () => {
        try {
            const { success } = await rnBiometrics.simplePrompt({ promptMessage: 'Confirm fingerprint to login' });

            if (success) {
                db.transaction(tx => {
                    tx.executeSql('SELECT token FROM session LIMIT 1', [], (tx, results) => {
                        if (results.rows.length > 0) {
                            global.userToken = results.rows.item(0).token;
                            navigation.replace('MainTabs');
                        } else {
                            Alert.alert('Authentication Failed', 'No saved session found. Please log in with your password first.');
                        }
                    });
                });
            } else {
                Alert.alert('Authentication', 'Biometric login cancelled');
            }
        } catch (error) {
            Alert.alert('Error', 'Biometrics failed');
        }
    };

    return (
        <ImageBackground
            source={require('../assets/login/loginBackground.png')}
            style={styles.backgroundImage}
            imageStyle={{ opacity: 0.15 }} // Subtle background pattern
        >
            <View style={styles.container}>
                <View style={styles.header}>
                    <Text variant="displaySmall" style={styles.title}>Welcome Back</Text>
                    <Text variant="bodyLarge" style={styles.subtitle}>Sign in to StorySail Gym</Text>
                </View>

                <View style={styles.formContainer}>
                    <TextInput
                        label="Member Number"
                        value={memberNumber}
                        onChangeText={setMemberNumber}
                        mode="outlined"
                        style={styles.input}
                        autoCapitalize="none"
                        theme={{ roundness: 25, colors: { primary: '#9348cc' } }}
                    />

                    <TextInput
                        label="Password"
                        value={password}
                        onChangeText={setPassword}
                        mode="outlined"
                        style={styles.input}
                        secureTextEntry
                        theme={{ roundness: 25, colors: { primary: '#9348cc' } }}
                    />

                    <TouchableOpacity
                        style={styles.formButton}
                        onPress={handleLogin}
                        disabled={loading}
                    >
                        <Text style={styles.actionButtonText}>
                            {loading ? "PROCESSING..." : "LOG IN"}
                        </Text>
                    </TouchableOpacity>

                    {biometryType && (
                        <TouchableOpacity
                            style={styles.googleButton}
                            onPress={handleBiometricAuth}
                        >
                            <Text style={styles.googleButtonText}>
                                Quick Login ({biometryType})
                            </Text>
                        </TouchableOpacity>
                    )}

                    <View style={styles.footer}>
                        <Text style={{ color: '#000' }}>Don't have an account? </Text>
                        <TouchableOpacity onPress={() => navigation.navigate('Register')}>
                            <Text style={{ color: '#9348cc', fontWeight: 'bold' }}>Register</Text>
                        </TouchableOpacity>
                    </View>
                </View>
            </View>

            <Portal>
                <Dialog visible={errorDialogVisible} onDismiss={() => setErrorDialogVisible(false)}>
                    <Dialog.Title>Login Failed</Dialog.Title>
                    <Dialog.Content>
                        <Text variant="bodyMedium">{errorMessage}</Text>
                    </Dialog.Content>
                    <Dialog.Actions>
                        <Button onPress={() => setErrorDialogVisible(false)}>OK</Button>
                    </Dialog.Actions>
                </Dialog>
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
        flex: 1,
        justifyContent: 'center',
    },
    formContainer: {
        paddingHorizontal: 20,
    },
    header: {
        alignItems: 'center',
        marginBottom: 40,
    },
    title: {
        fontWeight: 'bold',
        color: '#9348cc',
        fontSize: 32,
    },
    subtitle: {
        color: '#64748b',
        marginTop: 8,
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
    googleButton: {
        backgroundColor: "#fff",
        height: 55,
        alignItems: "center",
        justifyContent: "center",
        borderRadius: 35,
        marginVertical: 10,
        borderWidth: 1,
        borderColor: 'rgba(0,0,0,0.1)',
        shadowColor: "#000",
        shadowOffset: { width: 0, height: 4 },
        shadowOpacity: 0.15,
        shadowRadius: 3.84,
        elevation: 3,
        flexDirection: "row",
    },
    googleButtonText: {
        fontSize: 16,
        fontWeight: "600",
        color: "#9348cc",
        letterSpacing: 0.5,
    },
    footer: {
        flexDirection: 'row',
        alignItems: 'center',
        justifyContent: 'center',
        marginTop: 40
    }
});
