import React from 'react';
import { NavigationContainer } from '@react-navigation/native';
import { createNativeStackNavigator } from '@react-navigation/native-stack';
import { Provider as PaperProvider, MD3LightTheme } from 'react-native-paper';

import SplashScreen from './src/screens/SplashScreen';
import OnboardingScreen from './src/screens/OnboardingScreen';
import LoginScreen from './src/screens/LoginScreen';
import RegisterScreen from './src/screens/RegisterScreen';
import MainTabs from './src/navigation/MainTabs';
import EditProfileScreen from './src/screens/EditProfileScreen';
import GymGoalsScreen from './src/screens/GymGoalsScreen';
import TrainingPlansScreen from './src/screens/TrainingPlansScreen';
import ExercisesScreen from './src/screens/ExercisesScreen';
import RecommendationScreen from './src/screens/RecommendationScreen';
import TrainingHistoryScreen from './src/screens/TrainingHistoryScreen';

const Stack = createNativeStackNavigator();

const theme = {
  ...MD3LightTheme,
  colors: {
    ...MD3LightTheme.colors,
    primary: '#0ca5e9', // Sky 500
    secondary: '#334155', // Slate 700
  },
};

export default function App() {
  return (
    <PaperProvider theme={theme}>
      <NavigationContainer>
        <Stack.Navigator initialRouteName="Splash" screenOptions={{ headerShown: false }}>
          <Stack.Screen name="Splash" component={SplashScreen} />
          <Stack.Screen name="Onboarding" component={OnboardingScreen} />
          <Stack.Screen name="Login" component={LoginScreen} />
          <Stack.Screen name="Register" component={RegisterScreen} />
          <Stack.Screen name="MainTabs" component={MainTabs} />
          <Stack.Screen name="EditProfile" component={EditProfileScreen} />
          <Stack.Screen name="GymGoals" component={GymGoalsScreen} options={{ headerShown: true, title: 'Gym Goals' }} />
          <Stack.Screen name="TrainingPlans" component={TrainingPlansScreen} options={{ headerShown: true, title: 'Training Plans' }} />
          <Stack.Screen name="Exercises" component={ExercisesScreen} options={{ headerShown: true, title: 'Exercises' }} />
          <Stack.Screen name="Recommendation" component={RecommendationScreen} options={{ headerShown: true, title: 'Recommendations' }} />
          <Stack.Screen name="TrainingHistory" component={TrainingHistoryScreen} options={{ headerShown: true, title: 'Training History' }} />
        </Stack.Navigator>
      </NavigationContainer>
    </PaperProvider>
  );
}
