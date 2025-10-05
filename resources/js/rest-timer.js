/**
 * Rest Timer Handler
 */
export class RestTimer {
    constructor() {
        this.secondsRemaining = 0;
        this.intervalId = null;
        this.modalElement = null;
        this.audioContext = null;
    }

    start(seconds, exerciseName) {
        if (this.intervalId) {
            this.stop();
        }

        this.secondsRemaining = seconds;
        this.showModal(exerciseName);
        this.intervalId = setInterval(() => this.tick(), 1000);
    }

    stop() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
        this.hideModal();
    }

    tick() {
        this.secondsRemaining--;
        this.updateDisplay();

        if (this.secondsRemaining <= 0) {
            this.complete();
        }
    }

    complete() {
        this.alert();
        this.stop();
    }

    showModal(exerciseName) {
        this.modalElement = document.getElementById('rest-timer-modal');
        this.updateDisplay();
        this.modalElement.classList.add('active');
    }

    hideModal() {
        if (this.modalElement) {
            this.modalElement.classList.remove('active');
        }
    }

    updateDisplay() {
        const minutes = Math.floor(this.secondsRemaining / 60);
        const seconds = this.secondsRemaining % 60;
        const display = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        document.getElementById('rest-timer-display').textContent = display;
    }

    alert() {
        // Try vibration first (mobile)
        if ('vibrate' in navigator) {
            navigator.vibrate([200, 100, 200]);
        }

        // Try audio beep (fallback)
        try {
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }
            const oscillator = this.audioContext.createOscillator();
            oscillator.connect(this.audioContext.destination);
            oscillator.frequency.value = 800;
            oscillator.start();
            oscillator.stop(this.audioContext.currentTime + 0.2);
        } catch(e) {
            // Audio not supported, silent fallback
        }
    }
}
