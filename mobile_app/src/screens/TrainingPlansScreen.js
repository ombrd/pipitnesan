import React, { useState, useEffect } from 'react';
import { View, FlatList, StyleSheet } from 'react-native';
import { Text, Card, ActivityIndicator, Searchbar, Chip } from 'react-native-paper';
import api from '../services/api';

export default function TrainingPlansScreen() {
    const [plans, setPlans] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchQuery, setSearchQuery] = useState('');

    useEffect(() => {
        fetchPlans();
    }, []);

    const fetchPlans = async () => {
        try {
            const response = await api.get('/gym/training-plans');
            setPlans(response.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const filteredPlans = plans.filter(plan => 
        plan.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        plan.category.toLowerCase().includes(searchQuery.toLowerCase())
    );

    if (loading) return <View style={styles.center}><ActivityIndicator color="#dc2626" size="large" /></View>;

    return (
        <View style={styles.container}>
            <Searchbar
                placeholder="Search plans..."
                onChangeText={setSearchQuery}
                value={searchQuery}
                style={styles.search}
            />
            <FlatList
                data={filteredPlans}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={{ paddingBottom: 20 }}
                renderItem={({ item }) => (
                    <Card style={styles.card}>
                        <Card.Title 
                            title={item.name} 
                            subtitle={item.category}
                            titleStyle={{ fontWeight: 'bold' }}
                        />
                        <Card.Content>
                            <Text variant="bodyMedium" style={styles.description}>{item.description || 'No description available.'}</Text>
                            <View style={styles.chipContainer}>
                                <Chip icon="dumbbell" style={styles.chip} textStyle={{ color: '#dc2626' }}>{item.exercises?.length || 0} Exercises</Chip>
                                <Chip icon="target" style={styles.chip} textStyle={{ color: '#dc2626' }}>{item.goals?.length || 0} Goals</Chip>
                            </View>
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
    description: { color: '#334155' },
    chipContainer: { flexDirection: 'row', marginTop: 12 },
    chip: { marginRight: 8, backgroundColor: '#fee2e2', borderRadius: 8 }
});
