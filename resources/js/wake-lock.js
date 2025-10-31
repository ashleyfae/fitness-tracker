/**
 * Screen Wake Lock Manager
 * Prevents the screen from going to sleep during workout sessions
 */
export class WakeLock {
    constructor() {
        this.wakeLock = null;
        this.isSupported = 'wakeLock' in navigator;
    }

    /**
     * Request wake lock to keep screen on
     */
    async request() {
        if (!this.isSupported) {
            console.warn('Wake Lock API is not supported in this browser');
            return false;
        }

        try {
            this.wakeLock = await navigator.wakeLock.request('screen');

            // Clear reference when wake lock is released
            // (wake lock is automatically released when page is hidden)
            this.wakeLock.addEventListener('release', () => {
                console.log('Wake Lock released');
                this.wakeLock = null;
            });

            console.log('Wake Lock acquired - screen will stay on');
            return true;
        } catch (err) {
            console.error('Failed to acquire Wake Lock:', err);
            return false;
        }
    }

    /**
     * Release the wake lock
     */
    async release() {
        if (this.wakeLock) {
            try {
                await this.wakeLock.release();
                this.wakeLock = null;
                console.log('Wake Lock manually released');
            } catch (err) {
                console.error('Failed to release Wake Lock:', err);
            }
        }
    }

    /**
     * Re-acquire wake lock when page becomes visible
     * (wake lock is automatically released when page is hidden/minimized)
     */
    handleVisibilityChange() {
        if (document.visibilityState === 'visible' && !this.wakeLock) {
            this.request();
        }
    }

    /**
     * Initialize wake lock with automatic re-acquisition on visibility change
     */
    init() {
        this.request();

        // Re-acquire wake lock when returning to the page
        document.addEventListener('visibilitychange', () => {
            this.handleVisibilityChange();
        });
    }
}
