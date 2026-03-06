/**
 * Hardware Scanner Integration Fragment
 * 
 * This script detects fast keyboard input (typical of hardware QR scanners)
 * and triggers a callback when a scan is completed.
 * It's designed to work even when no input field is focused.
 */

class QRScannerDetector {
    constructor(onScanCallback) {
        this.onScan = onScanCallback;
        this.buffer = '';
        this.lastTime = 0;
        this.timeout = null;
        this.config = {
            minChars: 5,        // Minimum characters for a valid QR code
            maxTimeBetween: 50, // Max ms between characters (scanner speed)
            terminator: 'Enter' // Common suffix for scanners
        };

        this.init();
    }

    init() {
        window.addEventListener('keydown', (e) => {
            const currentTime = new Date().getTime();
            const timeDiff = currentTime - this.lastTime;
            
            // If it's a "printable" character or the terminator
            if (e.key.length === 1 || e.key === this.config.terminator) {
                // If it's the first character or fast enough, it's a scanner
                if (timeDiff < this.config.maxTimeBetween || this.buffer === '') {
                    if (e.key === this.config.terminator) {
                        if (this.buffer.length >= this.config.minChars) {
                            this.triggerScan();
                        }
                        this.buffer = '';
                    } else {
                        this.buffer += e.key;
                        
                        // Clear any existing timeout
                        if (this.timeout) clearTimeout(this.timeout);
                        
                        // Set timeout to clear buffer if input stops (meaning it wasn't a full scan)
                        this.timeout = setTimeout(() => {
                            if (this.buffer.length >= this.config.minChars) {
                                this.triggerScan();
                            }
                            this.buffer = '';
                        }, 50); // Shorter timeout for faster response
                    }
                } else {
                    // Too slow, probably manual typing
                    this.buffer = '';
                }
                this.lastTime = currentTime;
            }
        });
    }

    triggerScan() {
        const value = this.buffer.trim();
        if (value) {
            console.log("Scanner Detected Code:", value);
            this.onScan(value);
        }
        this.buffer = '';
    }
}

// Global detection
const scanner = new QRScannerDetector((code) => {
    // Show a small feedback to the user
    Swal.fire({
        title: 'QR Code Detected',
        text: 'Processing: ' + code,
        timer: 1000,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Process scan update
    $.ajax({
        url: 'api/auto_scan.php',
        method: 'POST',
        data: { token: code },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            
            if (response.success) {
                const user = response.user;
                const isInside = (user.status === 'INSIDE');
                
                // Show Success Modal with status update
                Swal.fire({
                    title: `Welcome, ${user.name}!`,
                    html: `
                        <div class="text-center">
                            <h2 class="display-6 fw-bold mb-3 ${isInside ? 'text-primary' : 'text-warning'}">
                                <i class="bi ${isInside ? 'bi-box-arrow-in-right' : 'bi-box-arrow-right'} me-2"></i>
                                ${user.status}
                            </h2>
                            <p class="text-muted">Resident ID: <strong>${user.id}</strong></p>
                            <p class="small text-muted mb-0">Record updated at ${new Date().toLocaleTimeString()}</p>
                        </div>
                    `,
                    icon: 'success',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });

                // Auto-refresh the homeowner table if it's visible
                if (typeof $ !== 'undefined' && $('#homeownersTable').length) {
                    $('#homeownersTable').DataTable().ajax.reload(null, false);
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: response.message,
                    timer: 3000
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Failed to communicate with the verification server.', 'error');
        }
    });
});
