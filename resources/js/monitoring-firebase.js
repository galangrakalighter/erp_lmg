import { initializeApp, getApps } from 'firebase/app';
import { getAuth, signInWithCustomToken } from 'firebase/auth';
import { addDoc, collection, getFirestore, onSnapshot, query, where } from 'firebase/firestore';

let firebaseApp;

function appFor(config) {
    firebaseApp ??= getApps()[0] ?? initializeApp(config);

    return firebaseApp;
}

// Firestore accepts plain JSON only. RTCSessionDescription is a browser
// object, therefore copy its serialisable fields before writing a signal.
function serialiseSignalPayload(payload) {
    if (payload && typeof payload === 'object' && 'type' in payload && 'sdp' in payload) {
        return {
            type: payload.type,
            sdp: payload.sdp,
        };
    }

    return payload;
}

/**
 * Real-time WebRTC signalling for Monitoring. Firebase only carries offers,
 * answers and ICE candidates; the screen and camera continue browser-to-browser
 * through WebRTC/TURN and are never stored in Firebase.
 */
async function connect({ config, token, sessionId, onSignal }) {
    const app = appFor(config);
    const auth = getAuth(app);
    const credential = await signInWithCustomToken(auth, token);
    const db = getFirestore(app);
    const uid = credential.user.uid;
    const signals = collection(db, 'monitoring_sessions', String(sessionId), 'signals');
    const processed = new Set();
    let isInitialSnapshot = true;
    let resolveReady;
    let rejectReady;
    const listenerReady = new Promise((resolve, reject) => {
        resolveReady = resolve;
        rejectReady = reject;
    });

    const unsubscribe = onSnapshot(
        query(signals, where('recipient_uid', '==', uid)),
        snapshot => {
            const changes = snapshot.docChanges();

            // Existing documents belong to a previous browser connection. A
            // fresh "ready" signal will start the new offer/answer exchange.
            if (isInitialSnapshot) {
                changes.forEach(change => processed.add(change.doc.id));
                isInitialSnapshot = false;
                // Do not let a viewer send "ready" until the target listener
                // is actually subscribed. This removes the lost-first-signal
                // race that made a new admin connection wait unnecessarily.
                resolveReady();
                return;
            }

            changes.forEach(change => {
                if (change.type !== 'added' || processed.has(change.doc.id)) return;
                processed.add(change.doc.id);
                Promise.resolve(onSignal({ id: change.doc.id, ...change.doc.data() }))
                    .catch(error => console.error('Firebase monitoring signal error:', error));
            });
        },
        error => {
            console.error('Firebase monitoring listener error:', error);
            if (isInitialSnapshot) rejectReady(error);
        },
    );

    await listenerReady;

    return {
        send(type, payload, recipientUserId) {
            return addDoc(signals, {
                sender_uid: uid,
                sender_user_id: Number(config.__erpUserId),
                recipient_uid: `erp_${recipientUserId}`,
                type,
                payload: serialiseSignalPayload(payload),
                created_at_ms: Date.now(),
            });
        },
        disconnect() {
            unsubscribe();
        },
    };
}

window.ERPMonitoringFirebase = { connect };
window.dispatchEvent(new Event('erp-monitoring-firebase-ready'));
