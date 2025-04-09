<?php
// sample.php
// A simple PHP script

$name = "Master Howie";

?>

<!DOCTYPE html>
<html>
<head>
    <title>Sample PHP Page</title>
</head>
<body>
    <h1>Updated Welcome to PHP!</h1>


    <p><?php echo "Hello, " . $name . "!"; ?></p>

    <p>Today is <?php echo date("l, F jS Y"); ?>.</p>

    <form method="post">
        <label for="favColor">What's your favorite color?</label>
        <input type="text" name="favColor" id="favColor" required>
        <button type="submit">Submit</button>
    </form>

    <?php
    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $color = htmlspecialchars($_POST["favColor"]);
        echo "<p>Your favorite color is <strong>$color</strong>! Nice choice.</p>";
    }
    ?>
</body>
</html>
