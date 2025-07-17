    <?php
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "Assignment_Assets";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection fail:" . $conn->connect_error);
    }
    session_start();
    // $email = "admin123@gmail.com";
    // $password = password_hash("admin168", PASSWORD_DEFAULT);

    // $query = "INSERT INTO users (email, password) VALUES (?, ?)";
    // $stmt = $conn->prepare($query);
    // $stmt->bind_param("ss", $email, $password);
    // $stmt->execute();

    // Session configuration (add this if not present)
    // session_start([
    //     'cookie_lifetime' => 86400, // 1 day
    //     'cookie_secure'   => false, // Set to true if using HTTPS
    //     'cookie_httponly' => true,
    //     'use_strict_mode' => true
    // ]);

    // // Verify session is writable
    // if (session_status() !== PHP_SESSION_ACTIVE) {
    //     die('Session initialization failed');
    // }
    ?>