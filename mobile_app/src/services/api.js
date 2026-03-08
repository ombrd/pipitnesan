import axios from 'axios';
import { Alert } from 'react-native';
import NetInfo from '@react-native-community/netinfo';
// In a real app we'd import AsyncStorage here to get the token automatically

const api = axios.create({
    baseURL: 'https://karol-uninfringed-gaye.ngrok-free.dev/api', // Replace with production URL later
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'ngrok-skip-browser-warning': '69420'
    }
});

// Mock interceptor for JWT
api.interceptors.request.use(
    async (config) => {
        const networkState = await NetInfo.fetch();
        if (!networkState.isConnected) {
            return Promise.reject(new Error('No internet connection. Please check your network and try again.'));
        }

        if (global.userToken) {
            config.headers.Authorization = `Bearer ${global.userToken}`;
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

api.interceptors.response.use(
    (response) => {
        return response;
    },
    (error) => {
        if (error.message === 'Network Error') {
            return Promise.reject(new Error('System is currently busy. Please try again later.'));
        }

        if (error.response && error.response.status >= 500) {
            return Promise.reject(new Error('System is currently busy. Please try again later.'));
        }

        if (error.response && error.response.status === 401) {
            // handle logout
        }
        return Promise.reject(error);
    }
);

export default api;
