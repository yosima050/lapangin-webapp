/**
 * LapangIn Web POS - Kasir QR Code Scanner & Token Manual Handler (Sprint 4)
 * Mengintegrasikan html5-qrcode untuk pemindaian kamera dan verifikasi tiket instan.
 */

class KasirScanner {
    constructor() {
        this.html5QrCode = null;
        this.isScanning = false;
        this.currentCameraId = null;
        this.cameras = [];
        this.isProcessing = false;
        this.audioCtx = null;

        // Elements
        this.readerContainerId = 'qr-reader';
        this.cameraSelect = document.getElementById('camera-select');
        this.btnStart = document.getElementById('btn-start-camera');
        this.btnStop = document.getElementById('btn-stop-camera');
        this.cameraBadge = document.getElementById('camera-status-badge');
        this.manualForm = document.getElementById('form-token-manual');
        this.manualInput = document.getElementById('manual-token-input');
        this.modal = document.getElementById('booking-modal');

        this.init();
    }

    init() {
        // Inisialisasi Audio Context untuk efek suara beep saat scan berhasil
        this.initAudio();

        // Setup manual token input (uppercase & 6 karakter)
        if (this.manualInput) {
            this.manualInput.addEventListener('input', (e) => {
                e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 6);
            });
        }

        // Setup form manual token submit
        if (this.manualForm) {
            this.manualForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const token = this.manualInput ? this.manualInput.value.trim() : '';
                if (!token) {
                    this.showNotification('Harap masukkan token manual!', 'warning');
                    return;
                }
                this.lookupTicket(token);
            });
        }

        // Setup shortcut keyboard (F2 untuk fokus input token manual, Esc untuk tutup modal)
        document.addEventListener('keydown', (e) => {
            if (e.key === 'F2') {
                e.preventDefault();
                if (this.manualInput) {
                    this.manualInput.focus();
                    this.manualInput.select();
                }
            } else if (e.key === 'Escape') {
                this.closeModal();
            }
        });

        // Setup html5-qrcode cameras jika pustaka tersedia
        this.initCameraDevices();

        // Setup event listener tombol kamera
        if (this.btnStart) {
            this.btnStart.addEventListener('click', () => this.startScanning());
        }
        if (this.btnStop) {
            this.btnStop.addEventListener('click', () => this.stopScanning());
        }
        if (this.cameraSelect) {
            this.cameraSelect.addEventListener('change', (e) => {
                this.currentCameraId = e.target.value;
                if (this.isScanning) {
                    this.stopScanning().then(() => this.startScanning());
                }
            });
        }

        // Setup modal close buttons
        const closeButtons = document.querySelectorAll('[data-close-modal]');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => this.closeModal());
        });
    }

    initAudio() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.audioCtx = new AudioContext();
            }
        } catch (e) {
            console.warn('AudioContext tidak didukung:', e);
        }
    }

    playBeep() {
        if (!this.audioCtx) return;
        try {
            if (this.audioCtx.state === 'suspended') {
                this.audioCtx.resume();
            }
            const osc = this.audioCtx.createOscillator();
            const gain = this.audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, this.audioCtx.currentTime); // 880Hz A5
            gain.gain.setValueAtTime(0.2, this.audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, this.audioCtx.currentTime + 0.15);
            osc.connect(gain);
            gain.connect(this.audioCtx.destination);
            osc.start();
            osc.stop(this.audioCtx.currentTime + 0.15);
        } catch (e) {
            console.warn('Gagal membunyikan beep:', e);
        }
    }

    async initCameraDevices() {
        if (typeof Html5Qrcode === 'undefined') {
            console.warn('Pustaka Html5Qrcode belum dimuat.');
            return;
        }

        try {
            this.cameras = await Html5Qrcode.getCameras();
            if (this.cameras && this.cameras.length > 0) {
                if (this.cameraSelect) {
                    this.cameraSelect.innerHTML = '';
                    this.cameras.forEach((camera, index) => {
                        const opt = document.createElement('option');
                        opt.value = camera.id;
                        opt.text = camera.label || `Kamera ${index + 1}`;
                        this.cameraSelect.appendChild(opt);
                    });
                }
                // Pilih kamera belakang/environment secara default jika tersedia
                const backCamera = this.cameras.find(c => c.label && /back|belakang|rear|environment/i.test(c.label));
                this.currentCameraId = backCamera ? backCamera.id : this.cameras[0].id;
                if (this.cameraSelect) {
                    this.cameraSelect.value = this.currentCameraId;
                }

                // Otomatis nyalakan kamera
                this.startScanning();
            } else {
                this.updateCameraStatus('Tidak Ada Kamera', 'offline');
            }
        } catch (err) {
            console.warn('Gagal membaca perangkat kamera:', err);
            const isPermissionDenied = err.name === 'NotAllowedError'
                || String(err).includes('NotAllowedError')
                || String(err).includes('Permission denied');
            if (isPermissionDenied) {
                this.updateCameraStatus('Izin Kamera Diblokir', 'offline');
                this.showNotification('Izin kamera diblokir browser. Klik ikon Gembok di bilah URL, ubah Kamera ke "Izinkan", lalu refresh.', 'error');
            } else {
                this.updateCameraStatus('Kamera Tidak Ditemukan', 'offline');
            }
        }
    }

    async startScanning() {
        if (typeof Html5Qrcode === 'undefined') return;
        const readerElem = document.getElementById(this.readerContainerId);
        if (!readerElem) return;

        if (this.isScanning) {
            return;
        }

        try {
            if (!this.html5QrCode) {
                this.html5QrCode = new Html5Qrcode(this.readerContainerId);
            }

            const cameraId = this.currentCameraId || (this.cameraSelect ? this.cameraSelect.value : null);
            const cameraConfig = cameraId ? { deviceId: { exact: cameraId } } : { facingMode: "environment" };

            const config = {
                fps: 12,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
            };

            await this.html5QrCode.start(
                cameraConfig,
                config,
                (decodedText, decodedResult) => {
                    this.onScanSuccess(decodedText, decodedResult);
                },
                (errorMessage) => {
                    // scanning in progress, no QR code yet
                }
            );

            this.isScanning = true;
            this.updateCameraStatus('Kamera Aktif & Memindai', 'active');
            if (this.btnStart) this.btnStart.classList.add('hidden');
            if (this.btnStop) this.btnStop.classList.remove('hidden');
        } catch (err) {
            console.error('Error saat memulai scanner:', err);
            this.updateCameraStatus('Izin Kamera Diblokir', 'offline');

            const isPermissionDenied = err.name === 'NotAllowedError'
                || String(err).includes('NotAllowedError')
                || String(err).includes('Permission denied');

            if (isPermissionDenied) {
                this.showNotification('Izin kamera diblokir browser. Klik ikon Gembok di bilah URL, ubah Kamera ke "Izinkan", lalu refresh.', 'error');
                const readerElem = document.getElementById(this.readerContainerId);
                if (readerElem) {
                    readerElem.innerHTML = `
                        <div class="p-6 text-center flex flex-col items-center justify-center gap-3 bg-red-50 text-red-900 rounded-2xl border border-red-200">
                            <span class="material-symbols-outlined text-4xl text-red-600">videocam_off</span>
                            <div class="space-y-1">
                                <h3 class="font-bold text-sm">Izin Akses Kamera Ditolak Browser</h3>
                                <p class="text-xs text-red-700 max-w-sm">
                                    Browser memblokir kamera untuk domain ini. Klik ikon <strong>Gembok 🔒</strong> di sebelah kiri alamat web URL (address bar), ubah <strong>Camera</strong> menjadi <strong>Allow (Izinkan)</strong>, lalu muat ulang halaman.
                                </p>
                            </div>
                            <button type="button" onclick="location.reload()" class="px-4 py-2 bg-red-600 text-white rounded-xl text-xs font-bold hover:bg-red-700 transition">
                                Muat Ulang Halaman
                            </button>
                        </div>
                    `;
                }
            } else {
                this.showNotification('Tidak dapat mengakses kamera: ' + (err.message || err), 'error');
            }
        }
    }

    async stopScanning() {
        if (this.html5QrCode && this.isScanning) {
            try {
                await this.html5QrCode.stop();
                this.isScanning = false;
                this.updateCameraStatus('Kamera Dimatikan', 'offline');
                if (this.btnStart) this.btnStart.classList.remove('hidden');
                if (this.btnStop) this.btnStop.classList.add('hidden');
            } catch (err) {
                console.error('Error saat mematikan scanner:', err);
            }
        }
    }

    updateCameraStatus(text, status) {
        if (!this.cameraBadge) return;
        this.cameraBadge.innerHTML = '';

        const dot = document.createElement('span');
        dot.className = `w-2 h-2 rounded-full ${status === 'active' ? 'bg-[#416900] animate-pulse' : 'bg-[#ba1a1a]'}`;

        const label = document.createElement('span');
        label.textContent = text;

        this.cameraBadge.appendChild(dot);
        this.cameraBadge.appendChild(label);

        if (status === 'active') {
            this.cameraBadge.className = 'flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#acf847] text-[#102000] text-[11px] font-extrabold';
        } else {
            this.cameraBadge.className = 'flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#ffdad6] text-[#ba1a1a] text-[11px] font-bold';
        }
    }

    onScanSuccess(decodedText, decodedResult) {
        if (this.isProcessing) return;
        this.isProcessing = true;

        this.playBeep();

        // Ambil clean code (jika kode berbentuk URL penuh, ambil path terakhir atau parameter)
        let cleanCode = decodedText.trim();
        try {
            if (cleanCode.startsWith('http://') || cleanCode.startsWith('https://')) {
                const url = new URL(cleanCode);
                cleanCode = url.searchParams.get('code') || url.pathname.split('/').pop() || cleanCode;
            }
        } catch (e) {
            // keep cleanCode as is
        }

        // Tampilkan notifikasi scan
        this.showNotification(`QR Terdeteksi: ${cleanCode}`, 'info');

        // Lakukan lookup tiket ke server
        this.lookupTicket(cleanCode);
    }

    async lookupTicket(code) {
        this.showLoading(true);

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch(`/kasir/ticket/lookup?code=${encodeURIComponent(code)}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken || '',
                }
            });

            const data = await response.json();

            this.showLoading(false);

            if (response.ok && data.success) {
                this.openModal(data);
            } else {
                const errorMsg = (data.message && !data.message.includes('SQLSTATE')) 
                    ? data.message 
                    : 'Tiket tidak ditemukan! Pastikan QR Code atau 6 karakter token manual sudah sesuai.';
                this.showNotification(errorMsg, 'error');
                // Beri jeda 2 detik sebelum mengizinkan scan berikutnya
                setTimeout(() => {
                    this.isProcessing = false;
                }, 2000);
            }
        } catch (err) {
            this.showLoading(false);
            console.error('Error saat lookup tiket:', err);
            this.showNotification('Terjadi kesalahan jaringan saat memvalidasi tiket.', 'error');
            setTimeout(() => {
                this.isProcessing = false;
            }, 2000);
        }
    }

    openModal(data) {
        if (!this.modal) return;
        const j = data.jadwal;

        const setText = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.textContent = val ?? '-';
        };

        // Populate fields secara aman
        setText('modal-booking-code', j.kode_qr || j.token_manual || j.id);
        setText('modal-token-manual', j.token_manual || '-');
        setText('modal-customer-name', j.nama_penyewa || '-');
        setText('modal-customer-phone', j.no_hp || '-');
        setText('modal-court-name', j.nama_lapangan || '-');
        setText('modal-court-category', j.kategori || '-');
        setText('modal-schedule-date', j.tanggal || '-');
        setText('modal-schedule-time', `${j.jam_mulai} - ${j.jam_selesai} WIB`);

        // Financials
        setText('modal-total-price', this.formatRupiah(j.total_harga));
        setText('modal-dp-paid', this.formatRupiah(j.dp_paid));
        setText('modal-remaining-due', this.formatRupiah(j.sisa_tagihan));

        // Form checkin action
        const checkinForm = document.getElementById('modal-checkin-form');
        if (checkinForm) {
            checkinForm.action = data.checkin_url || `/kasir/checkin/${j.id}`;
        }

        // Sisa tagihan input
        const amountInput = document.getElementById('modal-input-amount');
        if (amountInput) {
            amountInput.value = j.sisa_tagihan ?? 0;
        }

        // Setup Cetak Struk Thermal Button
        const btnPrintStruk = document.getElementById('modal-btn-print-struk');
        if (btnPrintStruk) {
            if (j.id) {
                btnPrintStruk.href = `/kasir/struk/${j.id}`;
                btnPrintStruk.classList.remove('hidden');
            } else {
                btnPrintStruk.classList.add('hidden');
            }
        }

        // Setup Warning Box & Status Badges
        const statusBadge = document.getElementById('modal-status-badge');
        const warningContainer = document.getElementById('modal-warning-container');
        const warningTitle = document.getElementById('modal-warning-title');
        const warningMessage = document.getElementById('modal-warning-message');
        const btnConfirmCheckin = document.getElementById('modal-btn-confirm-checkin');
        const paymentSection = document.getElementById('modal-payment-section');

        if (warningContainer) warningContainer.classList.add('hidden');
        if (btnConfirmCheckin) {
            btnConfirmCheckin.removeAttribute('disabled');
            btnConfirmCheckin.classList.remove('opacity-50', 'cursor-not-allowed');
        }

        if (data.is_cancelled || j.status === 'dibatalkan' || j.status === 'cancelled') {
            // TIKET DIBATALKAN
            if (statusBadge) {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-[#ba1a1a] text-white border border-red-700 shadow-xs';
                statusBadge.textContent = 'RESERVASI DIBATALKAN';
            }

            if (warningContainer) {
                warningContainer.classList.remove('hidden');
                warningContainer.className = 'p-4 rounded-xl bg-red-100 border-2 border-red-400 flex items-start gap-3 text-red-950';
            }
            if (warningTitle) warningTitle.textContent = 'PERINGATAN: RESERVASI SUDAH DIBATALKAN!';
            if (warningMessage) {
                const dendaText = j.catatan_kasir ? ` Catatan: ${j.catatan_kasir}` : '';
                warningMessage.textContent = `Reservasi ini telah dibatalkan. Slot jadwal telah dilepas kembali ke publik. Dilarang melayani check-in untuk tiket ini.${dendaText}`;
            }

            if (btnConfirmCheckin) {
                btnConfirmCheckin.setAttribute('disabled', 'true');
                btnConfirmCheckin.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (paymentSection) paymentSection.classList.add('hidden');
        } else if (data.is_duplicate) {
            // TIKET DUPLIKAT (SUDAH DIPAKAI)
            if (statusBadge) {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-[#ffdad6] text-[#ba1a1a] border border-[#ba1a1a]/30';
                statusBadge.textContent = 'DUPLIKAT / SUDAH DIGUNAKAN';
            }

            if (warningContainer) {
                warningContainer.classList.remove('hidden');
                warningContainer.className = 'p-4 rounded-xl bg-[#ffdad6] border border-[#ba1a1a] flex items-start gap-3 text-[#ba1a1a]';
            }
            if (warningTitle) warningTitle.textContent = 'Tiket Sudah Pernah Di-Check In!';
            if (warningMessage) warningMessage.textContent = 'Tiket ini telah ditukarkan sebelumnya di kasir. Reservasi sudah berstatus lunas / terverifikasi. Tidak dapat melakukan check-in ganda.';

            if (btnConfirmCheckin) {
                btnConfirmCheckin.setAttribute('disabled', 'true');
                btnConfirmCheckin.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (paymentSection) paymentSection.classList.add('hidden');
        } else if (data.is_expired) {
            // TIKET KEDALUWARSA
            if (statusBadge) {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-[#ffdad6] text-[#ba1a1a] border border-[#ba1a1a]/30';
                statusBadge.textContent = 'TIKET KEDALUWARSA';
            }

            if (warningContainer) {
                warningContainer.classList.remove('hidden');
                warningContainer.className = 'p-4 rounded-xl bg-[#ffdad6] border border-[#ba1a1a] flex items-start gap-3 text-[#ba1a1a]';
            }
            if (warningTitle) warningTitle.textContent = 'Tiket Telah Kedaluwarsa!';
            if (warningMessage) warningMessage.textContent = `Tanggal jadwal tiket (${j.tanggal}) telah lewat atau reservasi telah dibatalkan. Tiket ini tidak lagi berlaku untuk check-in.`;

            if (btnConfirmCheckin) {
                btnConfirmCheckin.setAttribute('disabled', 'true');
                btnConfirmCheckin.classList.add('opacity-50', 'cursor-not-allowed');
            }
            if (paymentSection) paymentSection.classList.add('hidden');
        } else {
            // TIKET VALID
            if (statusBadge) {
                statusBadge.className = 'px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-[#acf847] text-[#102000] border border-[#416900]/30';
                statusBadge.textContent = 'TIKET VALID';
            }

            if (paymentSection) paymentSection.classList.remove('hidden');
        }

        // Tampilkan modal
        this.modal.classList.remove('hidden');
    }

    closeModal() {
        if (!this.modal) return;
        this.modal.classList.add('hidden');
        // Reset processing lock
        setTimeout(() => {
            this.isProcessing = false;
        }, 1000);
    }

    formatRupiah(number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number || 0).replace('Rp', 'Rp ');
    }

    showLoading(isLoading) {
        const spinner = document.getElementById('scanner-loading-spinner');
        if (spinner) {
            if (isLoading) {
                spinner.classList.remove('hidden');
            } else {
                spinner.classList.add('hidden');
            }
        }
    }

    showNotification(message, type = 'info') {
        const toast = document.getElementById('scanner-toast');
        if (!toast) return;

        const text = document.getElementById('scanner-toast-text');
        const icon = document.getElementById('scanner-toast-icon');

        text.textContent = message;

        if (type === 'error') {
            toast.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-[#ba1a1a] text-white shadow-xl transition-all';
            icon.textContent = 'error';
        } else if (type === 'warning') {
            toast.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-[#9a5b00] text-white shadow-xl transition-all';
            icon.textContent = 'warning';
        } else {
            toast.className = 'fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl bg-[#1a146b] text-white shadow-xl transition-all';
            icon.textContent = 'info';
        }

        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('hidden');
        }, 4000);
    }
}

// Inisialisasi Scanner saat dokumen siap
document.addEventListener('DOMContentLoaded', () => {
    window.kasirScanner = new KasirScanner();
});

