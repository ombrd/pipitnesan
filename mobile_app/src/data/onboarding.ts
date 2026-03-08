import { AnimationObject } from "lottie-react-native";

export interface OnboardingData {
    id: number;
    animation: AnimationObject;
    text: string;
    textColor: string;
    backgroundColor: string;
}

const data: OnboardingData[] = [
    {
        id: 1,
        animation: require("../assets/onboarding/onboarding1.json"),
        text: "Welcome to Pipitnesan",
        textColor: "#F15937",
        backgroundColor: "#faeb8a",
    },
    {
        id: 2,
        animation: require("../assets/onboarding/onboarding2.json"),
        text: "Find Your Best Form",
        textColor: "#000",
        backgroundColor: "#ffa3ce",
    },
    {
        id: 3,
        animation: require("../assets/onboarding/onboarding3.json"),
        text: "Let's Get Started!",
        textColor: "#1e2169",
        backgroundColor: "#bae4fd",
    },
];

export default data;
