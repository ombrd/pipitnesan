import React, { useState, useEffect } from 'react';
import { View, FlatList, StyleSheet } from 'react-native';
import { Text, Card, Button, Checkbox, ActivityIndicator, Searchbar } from 'react-native-paper';
import api from '../services/api';

export default function GymGoalsScreen({ navigation }) {
    const [goals, setGoals] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selectedGoals, setSelectedGoals] = useState([]);
    const [searchQuery, setSearchQuery] = useState('');

    useEffect(() => {
        fetchGoals();
    }, []);

    const fetchGoals = async () => {
        try {
            const response = await api.get('/gym/goals');
            setGoals(response.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const toggleGoal = (id) => {
        setSelectedGoals(prev => 
            prev.includes(id) ? prev.filter(g => g !== id) : [...prev, id]
        );
    };

    const getRecommendations = () => {
        if (selectedGoals.length === 0) return;
        navigation.navigate('Recommendation', { goalIds: selectedGoals });
    };

    const filteredGoals = goals.filter(goal => 
        goal.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
        goal.category.toLowerCase().includes(searchQuery.toLowerCase())
    );

    if (loading) return <View style={styles.center}><ActivityIndicator color="#dc2626" size="large" /></View>;

    return (
        <View style={styles.container}>
            <Text variant="headlineSmall" style={styles.header}>Choose Your Goals</Text>
            <Text variant="bodyMedium" style={styles.subHeader}>Select the goals you want to achieve to get personalized training plans.</Text>
            
            <Searchbar
                placeholder="Search goals..."
                onChangeText={setSearchQuery}
                value={searchQuery}
                style={styles.search}
            />
            
            <FlatList
                data={filteredGoals}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={{ paddingBottom: 80 }}
                renderItem={({ item }) => (
                    <Card style={[styles.card, selectedGoals.includes(item.id) && styles.selectedCard]} onPress={() => toggleGoal(item.id)}>
                        <Card.Content style={styles.cardContent}>
                            <View style={{ flex: 1 }}>
                                <Text variant="titleMedium" style={styles.goalName}>{item.name}</Text>
                                <Text variant="bodySmall" style={styles.category}>{item.category}</Text>
                            </View>
                            <Checkbox
                                status={selectedGoals.includes(item.id) ? 'checked' : 'unchecked'}
                                onPress={() => toggleGoal(item.id)}
                                color="#dc2626"
                            />
                        </Card.Content>
                    </Card>
                )}
            />
            
            <View style={styles.footer}>
                <Button 
                    mode="contained" 
                    onPress={getRecommendations}
                    disabled={selectedGoals.length === 0}
                    style={styles.button}
                    buttonColor="#dc2626"
                >
                    Find Training Plans ({selectedGoals.length})
                </Button>
            </View>
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, padding: 16, backgroundColor: '#f8fafc' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { fontWeight: 'bold', color: '#0f172a', marginBottom: 4 },
    subHeader: { color: '#64748b', marginBottom: 20 },
    search: { marginBottom: 16, borderRadius: 12, backgroundColor: '#fff' },
    card: { marginBottom: 12, backgroundColor: '#fff', borderRadius: 12, elevation: 2 },
    selectedCard: { borderColor: '#dc2626', borderWidth: 1 },
    cardContent: { flexDirection: 'row', alignItems: 'center', paddingVertical: 12 },
    goalName: { fontWeight: '600' },
    category: { color: '#64748b', marginTop: 2 },
    footer: { position: 'absolute', bottom: 0, left: 0, right: 0, padding: 16, backgroundColor: '#f8fafc' },
    button: { paddingVertical: 6, borderRadius: 12 }
});
