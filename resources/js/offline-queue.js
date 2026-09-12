/**
 * EduManage Offline Queue
 * 
 * Handles offline queuing for Attendance and Mark Entry screens.
 * Stores changes in localStorage when offline, syncs via background
 * fetch when connectivity returns. Shows a visible sync status indicator.
 */
(function () {
    'use strict';

    const QUEUE_KEY = 'edumanage_offline_queue';
    const SYNC_STATUS_KEY = 'edumanage_sync_status';

    // ─── Queue Management ───────────────────────────────────────────────

    function getQueue() {
        try {
            return JSON.parse(localStorage.getItem(QUEUE_KEY) || '[]');
        } catch {
            return [];
        }
    }

    function saveQueue(queue) {
        localStorage.setItem(QUEUE_KEY, JSON.stringify(queue));
        updateSyncIndicator();
    }

    function addToQueue(entry) {
        const queue = getQueue();
        entry.id = Date.now() + '_' + Math.random().toString(36).slice(2, 8);
        entry.createdAt = new Date().toISOString();
        entry.status = 'pending';
        queue.push(entry);
        saveQueue(queue);
        return entry.id;
    }

    function removeFromQueue(id) {
        const queue = getQueue().filter((e) => e.id !== id);
        saveQueue(queue);
    }

    function getPendingCount() {
        return getQueue().filter((e) => e.status === 'pending').length;
    }

    // ─── Sync Indicator ─────────────────────────────────────────────────

    function updateSyncIndicator() {
        const count = getPendingCount();
        const indicator = document.getElementById('offline-sync-indicator');
        const countEl = document.getElementById('offline-sync-count');

        if (!indicator) return;

        if (count > 0) {
            indicator.classList.remove('hidden');
            indicator.classList.add('flex');
            if (countEl) countEl.textContent = count;
        } else {
            indicator.classList.add('hidden');
            indicator.classList.remove('flex');
        }
    }

    // ─── Network Status ─────────────────────────────────────────────────

    function isOnline() {
        return navigator.onLine;
    }

    function showOfflineBanner() {
        let banner = document.getElementById('offline-banner');
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'offline-banner';
            banner.className = 'fixed top-0 left-0 right-0 z-[100] bg-yellow-500 text-white text-center py-2 text-sm font-medium';
            banner.textContent = '📡 You are offline. Changes will be saved locally and synced when connected.';
            document.body.prepend(banner);
        }
        banner.style.display = 'block';
    }

    function hideOfflineBanner() {
        const banner = document.getElementById('offline-banner');
        if (banner) banner.style.display = 'none';
    }

    // ─── Sync Engine ────────────────────────────────────────────────────

    async function syncQueue() {
        if (!isOnline()) return;

        const queue = getQueue();
        const pending = queue.filter((e) => e.status === 'pending');
        if (pending.length === 0) return;

        for (const entry of pending) {
            try {
                entry.status = 'syncing';
                saveQueue(queue);

                const response = await fetch(entry.url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': getCsrfToken(),
                        'Accept': 'text/html,application/xhtml+xml',
                    },
                    body: JSON.stringify(entry.payload),
                });

                if (response.ok) {
                    removeFromQueue(entry.id);
                } else {
                    entry.status = 'failed';
                    entry.lastError = `HTTP ${response.status}`;
                    saveQueue(queue);
                }
            } catch (err) {
                entry.status = 'failed';
                entry.lastError = err.message;
                saveQueue(queue);
            }
        }

        // Refresh the page data after successful sync
        if (getPendingCount() === 0) {
            window.location.reload();
        }
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    // ─── Livewire Hooks ─────────────────────────────────────────────────

    function initLivewireHooks() {
        // Intercept Livewire save calls on attendance and mark entry pages
        if (typeof Livewire === 'undefined') return;

        document.addEventListener('livewire:initialized', () => {
            // Monitor network status
            window.addEventListener('online', () => {
                hideOfflineBanner();
                syncQueue();
            });

            window.addEventListener('offline', () => {
                showOfflineBanner();
            });

            // Initial state
            if (!isOnline()) {
                showOfflineBanner();
            }

            updateSyncIndicator();
        });
    }

    // ─── Public API (exposed on window for inline script usage) ─────────

    window.EduManageOffline = {
        /**
         * Queue an attendance save for later sync.
         * @param {object} payload - The Livewire component payload
         * @returns {string} Queue entry ID
         */
        queueAttendance(payload) {
            if (isOnline()) return null;
            return addToQueue({
                type: 'attendance',
                url: '/livewire/message/attendance-mark',
                payload: payload,
            });
        },

        /**
         * Queue a mark entry save for later sync.
         * @param {object} payload - The Livewire component payload
         * @returns {string} Queue entry ID
         */
        queueMarkEntry(payload) {
            if (isOnline()) return null;
            return addToQueue({
                type: 'mark-entry',
                url: '/livewire/message/mark-entry',
                payload: payload,
            });
        },

        /**
         * Get number of unsynced changes.
         */
        getPendingCount,

        /**
         * Manually trigger sync.
         */
        sync: syncQueue,

        /**
         * Check if online.
         */
        isOnline,

        /**
         * Get all queued items (for debugging).
         */
        getQueue,
    };

    initLivewireHooks();
})();
