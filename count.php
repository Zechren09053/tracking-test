<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manual Countdown</title>
    <style>
        body { font-family: Georgia, serif; text-align: center; margin-top: 50px; }
        #countdown { font-size: 3em; margin-top: 20px; }
        button { padding: 10px 20px; font-size: 1.2em; }
    </style>
</head>
<body>

<h1>Start Countdown</h1>
<button onclick="startCountdown()">Start from 10</button>
<div id="countdown">10</div>

<script>
    function startCountdown() {
        var count = 10;
        document.getElementById("countdown").textContent = count;

        var countdown = setInterval(function () {
            count--;
            document.getElementById("countdown").textContent = count;

            if (count <= 0) {
                clearInterval(countdown);
                document.getElementById("countdown").textContent = "Time's up!";
            }
        }, 1000);
    }
</script>

</body>
</html>
