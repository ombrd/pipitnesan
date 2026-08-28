import React, { useState, useEffect } from 'react';
import { View, FlatList, StyleSheet } from 'react-native';
import { Text, Card, ActivityIndicator, Searchbar, List, Avatar } from 'react-native-paper';
import api from '../services/api';

export default function ExercisesScreen() {
    const [exercises, setExercises] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');

    useEffect(() => {
        fetchExercises();
    }, []);

    const fetchExercises = async () => {
        try {
            const response = await api.get('/gym/exercises');
            setExercises(response.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const filteredExercises = exercises.filter(ex => 
        ex.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        ex.category.toLowerCase().includes(searchQuery.toLowerCase())
    );

    if (loading) return <View style={styles.center}><ActivityIndicator color="#dc2626" size="large" /></View>;

    return (
        <View style={styles.container}>
            <Searchbar
                placeholder="Search exercises..."
                onChangeText={setSearchQuery}
                value={searchQuery}
                style={styles.search}
            />
            <FlatList
                data={filteredExercises}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={{ paddingBottom: 20 }}
                renderItem={({ item }) => (
                    <Card style={styles.card}>
                        <List.Item
                            title={item.name}
                            description={item.category}
                            left={props => <Avatar.Icon {...props} icon="dumbbell" backgroundColor="#fee2e2" color="#dc2626" size={48} />}
                            titleStyle={{ fontWeight: 'bold' }}
                        />
                        <Card.Content>
                            <Text variant="bodySmall" style={styles.instrHeader}>Description:</Text>
                            <Text variant="bodyMedium" style={styles.text} numberOfLines={2}>{item.description || 'No description provided.'}</Text>
                            {item.instructions && (
                                <>
                                    <Text variant="bodySmall" style={[styles.instrHeader, { marginTop: 8 }]}>Instructions:</Text>
                                    <Text variant="bodyMedium" style={styles.text} numberOfLines={2}>{item.instructions}</Text>
                                </>
                            )}
                        </Card.Content>
                    </Card>
                )}
            />
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, padding: 16, backgroundColor: '#f8fafc' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    search: { marginBottom: 16, borderRadius: 12, backgroundColor: '#fff' },
    card: { marginBottom: 12, backgroundColor: '#fff', borderRadius: 12, elevation: 2 },
    instrHeader: { fontWeight: 'bold', color: '#64748b', marginBottom: 2 },
    text: { color: '#334155' }
});
