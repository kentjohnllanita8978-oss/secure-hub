import React, { useEffect, useState } from 'react';
import {
  View, Text, StyleSheet, TouchableOpacity,
  ScrollView, StatusBar, Modal,
} from 'react-native';

import IntegrationScreen  from './IntegrationScreen';
import NotificationScreen from './NotificationScreen';
import SettingsScreen     from './SettingsScreen';
import LoginModal         from './LoginModal';
import { integrationApi } from '../src/api/integrations';

// ─── Logo Header ────────────────────────────────────────────────────────────
function LogoHeader() {
  return (
    <View style={styles.logoRow}>
      <View style={styles.hexOuter}><Text style={styles.hexLock}>🔒</Text></View>
      <View style={styles.shield}>
        <View style={styles.stl} /><View style={styles.str} />
        <View style={styles.sbl} /><View style={styles.sbr} />
        <View style={styles.shieldOverlay}><Text style={styles.shieldCheck}>✓</Text></View>
      </View>
      <View>
        <Text style={styles.brandName}>Secure<Text style={styles.brandTs}>TS</Text></Text>
        <Text style={styles.brandSub}>ThreatShield</Text>
      </View>
    </View>
  );
}

// ─── Dashboard Home (guest-friendly) ────────────────────────────────────────
function DashboardHome({ user }) {
  return (
    <>
      <View style={styles.welcomeBanner}>
        <Text style={styles.welcomeTitle}>
          {user ? `Welcome, ${user.name || 'User'}! 👋` : 'Welcome to SecureTS 👋'}
        </Text>
        <Text style={styles.welcomeSub}>
          {user ? 'All linked accounts are protected.' : 'Sign in to activate your Integration Hub.'}
        </Text>
      </View>
      <ScrollView
        style={styles.feed}
        contentContainerStyle={styles.feedContent}
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.emptyState}>
          <Text style={{ fontSize: 52, marginBottom: 14 }}>🛡️</Text>
          <Text style={styles.emptyTitle}>No Alerts</Text>
          <Text style={styles.emptyText}>
            All systems operational. Login events will appear here once you're connected.
          </Text>
        </View>
      </ScrollView>
    </>
  );
}

function ApprovalPopup({ user }) {
  const [event, setEvent] = useState(null);

  useEffect(() => {
    if (!user) {
      setEvent(null);
      return;
    }
    let cancelled = false;
    const tick = () => {
      integrationApi.pending()
        .then((r) => { if (!cancelled) setEvent(r.data?.[0] ?? null); })
        .catch(() => {});
    };
    tick();
    const id = setInterval(tick, 4000);
    return () => { cancelled = true; clearInterval(id); };
  }, [user]);

  const respond = async (decision) => {
    if (!event) return;
    try {
      await integrationApi.respond(event.id, decision);
      setEvent(null);
    } catch (_) {}
  };

  if (!event) return null;

  return (
    <Modal visible transparent animationType="fade">
      <View style={styles.popupBackdrop}>
        <View style={styles.popupCard}>
          <Text style={styles.popupTitle}>Login request</Text>
          <Text style={styles.popupApp}>{event.integration?.name ?? 'Linked app'}</Text>
          <Text style={styles.popupMeta}>
            {event.device ? event.device + ' · ' : ''}
            {event.ip_address ? 'IP ' + event.ip_address : ''}
          </Text>
          <View style={styles.popupActions}>
            <TouchableOpacity style={styles.popupApprove} onPress={() => respond('approved')}>
              <Text style={styles.popupApproveText}>Approve</Text>
            </TouchableOpacity>
            <TouchableOpacity style={styles.popupDecline} onPress={() => respond('declined')}>
              <Text style={styles.popupDeclineText}>Decline</Text>
            </TouchableOpacity>
          </View>
        </View>
      </View>
    </Modal>
  );
}

// ─── Guest Gate (shown over Integration/Alerts when not logged in) ───────────
function GuestGate({ tabName, onLoginPress }) {
  return (
    <View style={styles.gateWrap}>
      <Text style={{ fontSize: 56, marginBottom: 18 }}>🔒</Text>
      <Text style={styles.gateTitle}>Sign in required</Text>
      <Text style={styles.gateSub}>
        {tabName} is only available to signed-in users.
      </Text>
      <TouchableOpacity style={styles.gateBtn} onPress={onLoginPress} activeOpacity={0.85}>
        <Text style={styles.gateBtnText}>Sign In</Text>
      </TouchableOpacity>
    </View>
  );
}

// ─── Main Screen ─────────────────────────────────────────────────────────────
export default function DashboardScreen({
  user, activeTab, onTabChange, onLoginSuccess, onLogout,
}) {
  const [loginModalVisible, setLoginModalVisible] = useState(false);

  const isLoggedIn = !!user;

  const openLogin = () => setLoginModalVisible(true);
  const closeLogin = () => setLoginModalVisible(false);

  const handleLoginSuccess = (userData) => {
    closeLogin();
    onLoginSuccess?.(userData);
  };

  const renderContent = () => {
    switch (activeTab) {
      case 'dashboard':
        return <DashboardHome user={user} />;

      case 'integrations':
        return isLoggedIn
          ? <IntegrationScreen />
          : <GuestGate tabName="Integration Hub" onLoginPress={openLogin} />;

      case 'notifications':
        return isLoggedIn
          ? <NotificationScreen />
          : <GuestGate tabName="Login Approvals" onLoginPress={openLogin} />;

      case 'settings':
        return (
          <SettingsScreen
            user={user}
            onLoginPress={openLogin}
            onLogout={onLogout}
            onOpenIntegrations={() => onTabChange('integrations')}
            onOpenApprovals={() => onTabChange('notifications')}
          />
        );

      default:
        return <DashboardHome user={user} />;
    }
  };

  return (
    <View style={styles.container}>
      <StatusBar barStyle="light-content" backgroundColor="#0F172A" />

      {/* Header — only on dashboard tab */}
      {activeTab === 'dashboard' && (
        <View style={styles.header}>
          <LogoHeader />
        </View>
      )}

      {/* Tab content */}
      <View style={{ flex: 1 }}>
        {renderContent()}
      </View>

      {/* ── Bottom Navigation ── */}
      <View style={styles.bottomNav}>
        {[
          { key: 'dashboard',     icon: '⊞',  label: 'Home' },
          { key: 'integrations',  icon: '🔗',  label: 'Apps' },
          { key: 'notifications', icon: '🔔',  label: 'Alerts' },
          { key: 'settings',      icon: '⚙️',  label: 'Settings' },
        ].map(tab => (
          <TouchableOpacity
            key={tab.key}
            id={`nav${tab.key.charAt(0).toUpperCase() + tab.key.slice(1)}`}
            style={styles.navItem}
            onPress={() => onTabChange(tab.key)}
            activeOpacity={0.7}
          >
            <View style={[
              styles.navSquare,
              activeTab === tab.key && styles.navSquareActive,
            ]}>
              <Text style={styles.navIcon}>{tab.icon}</Text>
            </View>
            <Text style={[
              styles.navLabel,
              activeTab === tab.key && styles.navLabelActive,
            ]}>
              {tab.label}
            </Text>
          </TouchableOpacity>
        ))}
      </View>

      {isLoggedIn && <ApprovalPopup user={user} />}

      {/* ── Floating Login Modal ── */}
      <LoginModal
        visible={loginModalVisible}
        onClose={closeLogin}
        onLoginSuccess={handleLoginSuccess}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: { flex: 1, backgroundColor: '#0A0F1E' },

  // Header
  header: {
    backgroundColor: '#0F172A',
    paddingTop: 52, paddingBottom: 14, paddingHorizontal: 20,
    borderBottomWidth: 1, borderBottomColor: 'rgba(56,102,230,0.3)',
  },
  logoRow: { flexDirection: 'row', alignItems: 'center', gap: 8 },
  hexOuter: {
    width: 30, height: 30, backgroundColor: '#1E293B',
    borderRadius: 6, alignItems: 'center', justifyContent: 'center',
  },
  hexLock: { fontSize: 14 },
  shield: {
    width: 26, height: 30, borderRadius: 3,
    overflow: 'hidden', flexDirection: 'row', flexWrap: 'wrap', position: 'relative',
  },
  stl: { width: 13, height: 15, backgroundColor: '#C0392B' },
  str: { width: 13, height: 15, backgroundColor: '#ECF0F1' },
  sbl: { width: 13, height: 15, backgroundColor: '#27AE60' },
  sbr: { width: 13, height: 15, backgroundColor: '#2980B9' },
  shieldOverlay: { ...StyleSheet.absoluteFillObject, alignItems: 'center', justifyContent: 'center' },
  shieldCheck: { color: 'white', fontSize: 12, fontWeight: '700' },
  brandName: { fontSize: 15, fontWeight: '800', color: 'white' },
  brandTs:   { color: '#3866E6' },
  brandSub:  { fontSize: 8, color: '#64748B', fontWeight: '500' },

  // Welcome
  welcomeBanner: {
    backgroundColor: '#0F172A',
    paddingHorizontal: 20, paddingVertical: 16,
    borderBottomWidth: 1, borderBottomColor: 'rgba(255,255,255,0.06)',
  },
  welcomeTitle: { color: 'white', fontSize: 20, fontWeight: '700', marginBottom: 4 },
  welcomeSub:   { color: '#3866E6', fontSize: 13, fontWeight: '500' },

  // Feed
  feed: { flex: 1 },
  feedContent: { flexGrow: 1, padding: 20 },
  emptyState: {
    flex: 1, alignItems: 'center', justifyContent: 'center', marginTop: '25%',
  },
  emptyTitle: { color: '#F8FAFC', fontSize: 18, fontWeight: '700', marginBottom: 8 },
  emptyText:  { color: '#64748B', fontSize: 13, textAlign: 'center', lineHeight: 20 },

  // Guest gate
  gateWrap: {
    flex: 1, alignItems: 'center', justifyContent: 'center', padding: 40,
  },
  gateTitle: { color: '#F8FAFC', fontSize: 20, fontWeight: '800', marginBottom: 10 },
  gateSub:   { color: '#94A3B8', fontSize: 14, textAlign: 'center', lineHeight: 22, marginBottom: 28 },
  gateBtn: {
    backgroundColor: '#3D5AF1', borderRadius: 12,
    paddingVertical: 14, paddingHorizontal: 40, alignItems: 'center',
  },
  gateBtnText: { color: '#fff', fontSize: 15, fontWeight: '700' },

  // Bottom nav
  bottomNav: {
    flexDirection: 'row',
    backgroundColor: '#0F172A',
    borderTopWidth: 1,
    borderTopColor: 'rgba(56,102,230,0.3)',
    paddingBottom: 28, paddingTop: 10,
    justifyContent: 'space-evenly', alignItems: 'center',
  },
  navItem:  { alignItems: 'center', flex: 1, gap: 4 },
  navSquare: {
    width: 44, height: 38,
    backgroundColor: 'rgba(255,255,255,0.08)',
    borderRadius: 8, alignItems: 'center', justifyContent: 'center',
  },
  navSquareActive: { backgroundColor: '#3866E6' },
  navIcon:  { fontSize: 17 },
  navLabel: { fontSize: 10, color: '#64748B', fontWeight: '500' },
  navLabelActive: { color: '#818CF8' },

  popupBackdrop: {
    flex: 1, backgroundColor: 'rgba(0,0,0,0.72)',
    alignItems: 'center', justifyContent: 'center', padding: 24,
  },
  popupCard: {
    width: '100%', maxWidth: 360, backgroundColor: '#1E293B',
    borderRadius: 16, padding: 22, borderWidth: 1, borderColor: 'rgba(234,179,8,0.35)',
  },
  popupTitle: { color: '#FACC15', fontSize: 12, fontWeight: '700', letterSpacing: 1, marginBottom: 8 },
  popupApp: { color: '#F8FAFC', fontSize: 18, fontWeight: '700', marginBottom: 6 },
  popupMeta: { color: '#94A3B8', fontSize: 13, marginBottom: 18 },
  popupActions: { flexDirection: 'row', gap: 8 },
  popupApprove: {
    flex: 1, backgroundColor: 'rgba(34,197,94,0.15)', borderWidth: 1,
    borderColor: 'rgba(34,197,94,0.3)', borderRadius: 10, padding: 12, alignItems: 'center',
  },
  popupApproveText: { color: '#4ADE80', fontWeight: '700' },
  popupDecline: {
    flex: 1, backgroundColor: 'rgba(239,68,68,0.15)', borderWidth: 1,
    borderColor: 'rgba(239,68,68,0.3)', borderRadius: 10, padding: 12, alignItems: 'center',
  },
  popupDeclineText: { color: '#F87171', fontWeight: '700' },
});
