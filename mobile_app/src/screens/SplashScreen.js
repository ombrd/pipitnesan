import React, { useEffect } from 'react';
import { View, Text, StyleSheet, Animated } from 'react-native';

export default function SplashScreen({ navigation }) {
  const fadeAnim = new Animated.Value(0);

  useEffect(() => {
    // Fade in animation for the logo text
    Animated.timing(fadeAnim, {
      toValue: 1,
      duration: 1500,
      useNativeDriver: true,
    }).start();

    // Navigate to Onboarding after 2.5 seconds
    const timer = setTimeout(() => {
      navigation.replace('Onboarding');
    }, 2500);

    return () => clearTimeout(timer);
  }, [navigation, fadeAnim]);

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
    color: '#38bdf8', // light blue
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
