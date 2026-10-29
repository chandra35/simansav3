import { Html5Qrcode } from 'html5-qrcode';

class CardQrScanner {
    constructor(elementId, onDecoded, onError) {
        this.elementId = elementId;
        this.onDecoded = onDecoded;
        this.onError = onError;
        this.scanner = new Html5Qrcode(elementId, { verbose: false });
        this.running = false;
    }

    async start() {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Browser tidak mendukung akses kamera.');
        }

        const cameras = await Html5Qrcode.getCameras();
        if (!cameras.length) {
            throw new Error('Kamera tidak ditemukan. Pastikan kamera tersambung dan izinnya diizinkan.');
        }

        const rearCamera = cameras.find((camera) => /back|rear|environment|belakang/i.test(camera.label));
        const cameraId = (rearCamera || cameras[0]).id;

        await this.scanner.start(
            cameraId,
            { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.333334 },
            (decodedText) => this.onDecoded(decodedText),
            () => {},
        );
        this.running = true;
    }

    async stop() {
        if (!this.running) {
            return;
        }

        await this.scanner.stop();
        this.scanner.clear();
        this.running = false;
    }
}

window.CardQrScanner = CardQrScanner;
