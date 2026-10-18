import React, { useState } from 'react';
import { View, StyleSheet, StatusBar } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';

import SplashScreen     from './components/SplashScreen';
import OnboardingScreen from './components/OnboardingScreen';
import DashboardScreen  from './components/DashboardScreen';
import { getStoredToken, setStoredToken } from './src/api/client';

/**
 * App Navigation Flow:
 *   1. SplashScreen     – always shown for 5 s on launch
 *   2. OnboardingScreen – shown ONCE (first launch only)
 *   3. DashboardScreen  – main tabs, works in guest mode
 *                         Login is accessed from:
 *                           • Settings tab  (Sign In button when guest)
 *                           • Floating LoginModal on Integration / Alerts tabs
 */
export default function App() {
  // 'splash' | 'onboarding' | 'main'
  const [phase, setPhase] = useState('splash');
  const [user,  setUser]  = useState(null);
  const [activeTab, setActiveTab] = useState('dashboard');

  // ── After splash ─────────────────────────────────────────────────────────────
  const handleSplashFinish = async () => {
    try {
      // Restore persisted session (if any)
      const token = await getStoredToken();
      if (token) {
        const savedUser = await AsyncStorage.getItem('user_data');
        if (savedUser) setUser(JSON.parse(savedUser));
      }
      // Show onboarding on first ever launch
      const onboardingDone = await AsyncStorage.getItem('onboarding_complete');
      setPhase(onboardingDone === 'true' ? 'main' : 'onboarding');
    } catch (_) {
      setPhase('main');
    }
  };

  // ── After onboarding ─────────────────────────────────────────────────────────
  const handleOnboardingFinish = () => setPhase('main');

  // ── Called by LoginModal / SettingsScreen after successful Sanctum login ──────
  const handleLoginSuccess = async (userData) => {
    setUser(userData);
    try { await AsyncStorage.setItem('user_data', JSON.stringify(userData)); } catch (_) {}
  };

  // ── Logout ───────────────────────────────────────────────────────────────────
  const handleLogout = async () => {
    try {
      await setStoredToken(null);
      await AsyncStorage.removeItem('user_data');
    } catch (_) {}
    setUser(null);
    setActiveTab('dashboard');
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#0F172A" />

      {phase === 'splash' && (
        <SplashScreen onFinish={handleSplashFinish} />
      )}

      {phase === 'onboarding' && (
        <OnboardingScreen onFinish={handleOnboardingFinish} />
      )}

      {phase === 'main' && (
        <DashboardScreen
          user={user}
          activeTab={activeTab}
          onTabChange={setActiveTab}
          onLoginSuccess={handleLoginSuccess}
          onLogout={handleLogout}
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0A0F1E' },
});
