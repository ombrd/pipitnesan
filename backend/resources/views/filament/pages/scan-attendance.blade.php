<x-filament-panels::page>
    <div>
        <!-- Include HTML5-QRCode script -->
        <script src="{{ asset('js/html5-qrcode.min.js') }}"></script>

        <div class="max-w-xl mx-auto space-y-8">
            <div class="p-6 bg-white shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 text-center">
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white mb-4">
                Scan Member Card
            </h2>
            <p class="text-gray-500 mb-6" id="scan-instructions">
                Scan physical membership barcode via physical scanner OR use the camera below.
            </p>

            <!-- Camera Scanner Viewport -->
            <div id="reader" style="width: 100%; max-width: 500px; margin: 0 auto 2rem auto; border-radius: 0.5rem; overflow: hidden;" class="mx-auto rounded-xl"></div>
            
            <form wire:submit="scanBarcode">
                {{ $this->form }}
                <button id="submit-barcode" type="submit" class="hidden">Scan</button>
            </form>
            </div>

            @if($scannedMember)
                <div class="p-6 bg-white shadow-sm rounded-xl ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mt-6 text-left">
                <h3 class="text-lg font-bold mb-4">Last Scanned Member:</h3>
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="h-16 w-16 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 text-2xl font-bold">
                            {{ substr($scannedMember->name, 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">{{ $scannedMember->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $scannedMember->member_number }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status:</span>
                            <span class="font-medium {{ $scannedMember->status === 'active' ? 'text-success-600' : 'text-danger-600' }}">
                                {{ ucfirst($scannedMember->status) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Active Until:</span>
                            <span class="font-medium">
                                {{ $scannedMember->active_until ? $scannedMember->active_until->format('d M Y') : 'N/A' }}
                            </span>
                        </div>
                        @if($scannedMember->branch)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Branch:</span>
                                <span class="font-medium">{{ $scannedMember->branch->name }}</span>
                            </div>
                        @endif
                    </div>

                    @if($scannedMember->status !== 'active')
                        <div class="p-4 rounded-lg bg-danger-50 text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                            Warning: This member's status is {{ $scannedMember->status }}. Please advise them to renew their membership.
                        </div>
                    @endif
                </div>
                </div>
            @endif
        </div>

        <script>
        document.addEventListener('livewire:initialized', () => {

            let html5QrcodeScanner = null;
            let isScanning = false;
            let lastScannedCode = null;
            let lastScanTime = 0;

            function onScanSuccess(decodedText, decodedResult) {
                const now = Date.now();
                
                // Prevent aggressive rescanning of the same code within 3 seconds
                if (decodedText === lastScannedCode && (now - lastScanTime) < 3000) {
                    return;
                }

                if (isScanning) return;
                isScanning = true;
                lastScannedCode = decodedText;
                lastScanTime = now;
                
                console.log(`Code matched = ${decodedText}`, decodedResult);
                
                if (html5QrcodeScanner) {
                    html5QrcodeScanner.clear().catch(error => {
                        console.error("Failed to clear html5QrcodeScanner. ", error);
                    });
                }

                // Set the value of the Livewire input
                const barcodeInput = document.querySelector('input[id*="barcode"]');
                if (barcodeInput) {
                    // Update state properly
                    @this.set('data.barcode', decodedText);

                    // Trigger the form submission
                    setTimeout(() => {
                        document.getElementById('submit-barcode').click();
                    }, 100);
                }
            }

            function onScanFailure(error) {
                // handle scan failure, usually better to ignore and keep scanning
            }

            function initScanner() {
                if(document.getElementById('reader')) {
                    html5QrcodeScanner = new Html5QrcodeScanner(
                        "reader",
                        { fps: 10, qrbox: {width: 250, height: 250} },
                        /* verbose= */ false
                    );
                    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
                }
            }

            Livewire.on('scan-success', () => {
                // Keep the member data visible for 3 seconds, then tell server to clear it
                setTimeout(() => {
                    @this.clearScan();
                }, 3000);
            });

            Livewire.on('focus-barcode', () => {
                isScanning = false;
                setTimeout(() => {
                    const barcodeInput = document.querySelector('input[id*="barcode"]');
                    if (barcodeInput) barcodeInput.focus();
                    initScanner(); // Re-initialize totally
                }, 100);
            });
            
            // Initial focus & scanner mount
            setTimeout(() => {
                const barcodeInput = document.querySelector('input[id*="barcode"]');
                if(barcodeInput) barcodeInput.focus();
                
                // Give Livewire time to render fully before mounting scanner
                setTimeout(initScanner, 500);
            }, 500);
        });
        </script>
    </div>
</x-filament-panels::page>
