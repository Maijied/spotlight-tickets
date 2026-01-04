<?php
require_once __DIR__ . '/../config/config.php';
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    // Basic auth check for scanner
    header('Location: admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Gatekeeper - <?php echo EVENT_NAME; ?></title>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #000; color: #fff; font-family: monospace; text-align: center; }
        #reader { width: 100%; max-width: 500px; margin: 0 auto; border: 2px solid #333; }
        .result-box { margin-top: 20px; padding: 20px; background: #111; min-height: 150px; border-top: 2px solid #555; }
        .status { font-size: 2rem; font-weight: bold; margin-bottom: 10px; }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .details { font-size: 1.2rem; color: #ccc; }
        .btn { padding: 10px 20px; background: #333; color: #fff; border: 1px solid #555; cursor: pointer; margin-top: 10px; }
    </style>
</head>
<body>
    <h2 style="color: #f97316;">GLOW GATEKEEPER</h2>
    
    <div id="reader"></div>
    
    <div class="result-box" id="result-box">
        <div class="status" id="status-text">READY TO SCAN</div>
        <div class="details" id="details-text">Point camera at ticket QR</div>
        <div id="actions"></div>
    </div>

    <script>
        const html5QrCode = new Html5Qrcode("reader");
        let isScanning = true;

        function onScanSuccess(decodedText, decodedResult) {
            if(!isScanning) return;
            isScanning = false;
            
            // Play beep
            // const audio = new Audio('beep.mp3'); audio.play();

            document.getElementById('status-text').innerText = "VERIFYING...";
            document.getElementById('status-text').className = "status warning";

            // Call API
            fetch('../api/checkin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'qrcode=' + encodeURIComponent(decodedText)
            })
            .then(response => response.json())
            .then(data => {
                const statusEl = document.getElementById('status-text');
                const detailsEl = document.getElementById('details-text');
                const actionsEl = document.getElementById('actions');

                if (data.status === 'valid') {
                    statusEl.innerText = "ACCESS GRANTED";
                    statusEl.className = "status success";
                    detailsEl.innerHTML = `
                        ${data.booking.name}<br>
                        ${data.booking.tier} | Seat: ${data.booking.quantity}<br>
                        <span style="color:#aaa">${data.booking.txnid}</span>
                    `;
                    // Auto reset after 3s
                    setTimeout(resetScanner, 3000);
                } else if (data.status === 'pending') {
                    statusEl.innerText = "PAYMENT PENDING";
                    statusEl.className = "status warning";
                    detailsEl.innerText = `Booking found but NOT confirmed.\nName: ${data.booking.name}`;
                    actionsEl.innerHTML = `<button class="btn" onclick="approveInstant('${decodedText}')">Approve & Admit</button> <button class="btn" onclick="resetScanner()">Cancel</button>`;
                } else if (data.status === 'used') {
                    statusEl.innerText = "ALREADY USED";
                    statusEl.className = "status error";
                    detailsEl.innerText = `This ticket was already scanned at ${data.scanned_at}`;
                    setTimeout(resetScanner, 4000);
                } else {
                    statusEl.innerText = "ACCESS DENIED";
                    statusEl.className = "status error";
                    detailsEl.innerText = data.message || "Invalid QR Code";
                    setTimeout(resetScanner, 3000);
                }
            })
            .catch(err => {
                console.error(err);
                resetScanner();
            });
        }

        function resetScanner() {
            isScanning = true;
            document.getElementById('status-text').innerText = "READY TO SCAN";
            document.getElementById('status-text').className = "status";
            document.getElementById('details-text').innerText = "Point camera at ticket QR";
            document.getElementById('actions').innerHTML = "";
        }

        function approveInstant(txnId) {
            if(!confirm("Collect payment and approve instantly?")) return;
            
            fetch('../api/admin_approve.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'txn_id=' + encodeURIComponent(txnId)
            }).then(() => {
                alert("Approved!");
                resetScanner();
            });
        }

        html5QrCode.start(
            { facingMode: "environment" }, 
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            (errorMessage) => { /* ignore parse errors */ }
        ).catch(err => {
            document.getElementById('details-text').innerText = "Camera access failed";
        });
    </script>
</body>
</html>
