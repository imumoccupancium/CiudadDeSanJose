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
        const value = this.buffer.trim().toUpperCase();
        if (value.startsWith('IN:') || value.startsWith('OUT:')) {
            console.log("Scanner Detected Prefixed Code:", value);
            this.onScan(this.buffer.trim());
        } else {
            console.log("Scanner Ignored raw input (to be handled by Python background script):", value);
        }
        this.buffer = '';
    }
}

// Global detection
const scanner = new QRScannerDetector((code) => {
    // Determine action/device from prefix if present
    let action = null;
    let deviceName = 'Web Scanner';
    let token = code;

    if (code.toUpperCase().startsWith('IN:')) {
        action = 'IN';
        token = code.substring(3);
        deviceName = 'Entry Gate Scanner';
    } else if (code.toUpperCase().startsWith('OUT:')) {
        action = 'OUT';
        token = code.substring(4);
        deviceName = 'Exit Gate Scanner';
    }

    // Define a Toast configuration
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true
    });

    Toast.fire({
        icon: 'info',
        title: 'QR Code Detected',
        text: 'Processing: ' + token
    });

    // Process scan update
    $.ajax({
        url: 'api/auto_scan.php',
        method: 'POST',
        data: { 
            token: token,
            action: action,
            device_name: deviceName
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                const user = response.user;
                
                Toast.fire({
                    icon: 'success',
                    title: `Welcome, ${user.name}!`,
                    text: `${user.status} - ID: ${user.id}`
                });

                // Auto-refresh the homeowner table if it's visible
                if (typeof $ !== 'undefined' && $('#homeownersTable').length) {
                    $('#homeownersTable').DataTable().ajax.reload(null, false);
                }
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Access Denied',
                    text: response.message
                });
            }
        },
        error: function () {
            Toast.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to communicate with the verification server.'
            });
        }
    });
});
