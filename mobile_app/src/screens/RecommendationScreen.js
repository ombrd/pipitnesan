import React, { useState, useEffect } from 'react';
import { View, FlatList, StyleSheet, Alert } from 'react-native';
import { Text, Card, Button, ActivityIndicator, Chip } from 'react-native-paper';
import api from '../services/api';

export default function RecommendationScreen({ route, navigation }) {
    const { goalIds } = route.params;
    const [plans, setPlans] = useState([]);
    const [loading, setLoading] = useState(true);
    const [selecting, setSelecting] = useState(null);

    useEffect(() => {
        fetchRecommendations();
    }, []);

    const fetchRecommendations = async () => {
        try {
            const response = await api.post('/gym/recommendations', { goal_ids: goalIds });
            setPlans(response.data);
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    };

    const handleSelectPlan = async (planId) => {
        setSelecting(planId);
        try {
            await api.post('/gym/select-plan', { training_plan_id: planId });
            Alert.alert("Success", "Training plan selected successfully!");
            navigation.navigate('Home');
        } catch (error) {
            Alert.alert("Error", "Failed to select plan.");
        } finally {
            setSelecting(null);
        }
    };

    if (loading) return <View style={styles.center}><ActivityIndicator color="#dc2626" size="large" /></View>;

    return (
        <View style={styles.container}>
            <Text variant="headlineSmall" style={styles.header}>Recommended Plans</Text>
            <Text variant="bodyMedium" style={styles.subHeader}>Based on your selected goals, we recommend these training plans.</Text>

            <FlatList
                data={plans}
                keyExtractor={item => item.id.toString()}
                contentContainerStyle={{ paddingBottom: 20 }}
                ListEmptyComponent={<Text style={styles.empty}>No matching plans found. Try selecting different goals.</Text>}
                renderItem={({ item }) => (
                    <Card style={styles.card}>
                        <Card.Title 
                            title={item.name} 
                            subtitle={item.category}
                            right={(props) => <Text style={styles.exerciseCount}>{item.exercises?.length || 0} Exercises</Text>}
                            rightStyle={{ marginRight: 16 }}
                            titleStyle={{ fontWeight: 'bold' }}
                        />
                        <Card.Content>
                            <Text variant="bodyMedium" style={styles.description}>{item.description}</Text>
                            <View style={styles.goalsContainer}>
                                {item.goals.map(goal => (
                                    <Chip key={goal.id} style={styles.goalChip} textStyle={{ fontSize: 10, color: '#dc2626' }}>{goal.name}</Chip>
                                ))}
                            </View>
                        </Card.Content>
                        <Card.Actions style={styles.actions}>
                            <Button 
                                mode="contained" 
                                onPress={() => handleSelectPlan(item.id)}
                                loading={selecting === item.id}
                                disabled={selecting !== null}
                                buttonColor="#dc2626"
                                style={styles.selectButton}
                            >
                                Select This Plan
                            </Button>
                        </Card.Actions>
                    </Card>
                )}
            />
        </View>
    );
}

const styles = StyleSheet.create({
    container: { flex: 1, padding: 16, backgroundColor: '#f8fafc' },
    center: { flex: 1, justifyContent: 'center', alignItems: 'center' },
    header: { fontWeight: 'bold', color: '#0f172a', marginBottom: 4 },
    subHeader: { color: '#64748b', marginBottom: 20 },
    card: { marginBottom: 16, backgroundColor: '#fff', borderRadius: 16, elevation: 3 },
    exerciseCount: { color: '#64748b', fontSize: 12, fontWeight: '500' },
    description: { color: '#334155', lineHeight: 20 },
    goalsContainer: { flexDirection: 'row', flexWrap: 'wrap', marginTop: 12 },
    goalChip: { marginRight: 6, marginBottom: 6, height: 26, backgroundColor: '#fee2e2', borderRadius: 8 },
    actions: { paddingHorizontal: 16, paddingBottom: 16 },
    selectButton: { flex: 1, borderRadius: 10 },
    empty: { textAlign: 'center', marginTop: 60, color: '#64748b', fontSize: 16 }
});
