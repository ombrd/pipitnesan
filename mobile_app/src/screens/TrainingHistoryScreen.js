import React, { useState, useEffect } from 'react';
import { View, FlatList, StyleSheet } from 'react-native';
import { Text, Card, ActivityIndicator, List, Avatar } from 'react-native-paper';
import api from '../services/api';
import { format } from 'date-fns';

export default function TrainingHistoryScreen() {
    const [history, setHistory] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetchHistory();
    }, []);

    const fetchHistory = async () => {
        try {
            const response = await api.get('/gym/history');
            setHistory(response.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    if (loading) return <View style={styles.center}><ActivityIndicator color="#9348cc" size="large" /></View>;

    return (
        <View style={styles.container}>
            <FlatList
                data={history}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={{ paddingBottom: 20 }}
                ListEmptyComponent={<Text style={styles.empty}>No training history found. Start your first plan to see it here!</Text>}
                renderItem={({ item }) => (
                    <Card style={styles.card}>
                        <List.Item
                            title={item.exercise?.name || item.training_plan?.name || 'Activity Logged'}
                            description={`${item.training_plan?.name ? 'Plan: ' + item.training_plan.name : ''}\n${format(new Date(item.created_at), 'dd MMM yyyy, HH:mm')}`}
                            descriptionNumberOfLines={2}
                            left={props => (
                                <Avatar.Icon 
                                    {...props} 
                                    icon={item.exercise ? "run" : "clipboard-list"} 
                                    backgroundColor="#f8fafc" 
                                    color="#9348cc" 
                                    size={44} 
                                />
                            )}
                            titleStyle={{ fontWeight: 'bold' }}
                        />
                        {item.notes && (
                            <Card.Content style={styles.notesContainer}>
                                <Text variant="bodySmall" style={styles.notesLabel}>Notes:</Text>
                                <Text variant="bodyMedium" style={styles.notesText}>{item.notes}</Text>
                            </Card.Content>
                        )}
                    </Card>
                )}
            />
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, padding: 16, backgroundColor: '#f8fafc' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    card: { marginBottom: 12, backgroundColor: '#fff', borderRadius: 12, elevation: 1 },
    notesContainer: { paddingTop: 0, paddingBottom: 12 },
    notesLabel: { color: '#64748b', fontWeight: 'bold' },
    notesText: { color: '#334155', fontStyle: 'italic' },
    empty: { textAlign: 'center', marginTop: 60, color: '#64748b', fontSize: 16 }
});
