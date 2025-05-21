<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>QR Code Scanner</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.4/html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            text-align: center;
        }
        #reader {
            width: 300px;
            margin: auto;
        }
        #result {
            margin-top: 20px;
            font-size: 1.2em;
            font-weight: bold;
        }
        #rescan-btn {
            display: none;
            margin-top: 15px;
        }
    </style>
</head>
<body>

    <h1>QR Code Scanner</h1>
    <div id="reader"></div>
    <div id="result">Scan a QR Code</div>
    <button id="rescan-btn" onclick="startScanner()">Scan Again</button>

    <script>
        let scanner;

        function startScanner() {
            document.getElementById('result').textContent = 'Scanning...';
            document.getElementById('rescan-btn').style.display = 'none';

            if (scanner) {
                scanner.clear();
            }

            scanner = new Html5Qrcode("reader");
            scanner.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: 250
                },
                qrCodeMessage => {
                    document.getElementById('result').textContent = `Result: ${qrCodeMessage}`;
                    scanner.stop();
                    document.getElementById('rescan-btn').style.display = 'inline-block';
                },
                errorMessage => {
                    // console.log(`Scan error: ${errorMessage}`);
                }
            ).catch(err => {
                document.getElementById('result').textContent = 'Error starting camera: ' + err;
            });
        }

        startScanner();
    </script>
</body>
</html>
