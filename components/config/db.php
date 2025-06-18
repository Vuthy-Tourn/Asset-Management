    <?php
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "Assignment_Assets";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection fail:" . $conn->connect_error);
    }
    ?>