<?php
$servername = "127.0.0.1";
$username = "root";
$password = "";

// The user initially had users_db, but mentioned movie_db later. Let's try both.
$dbnames = ["users_db", "movie_db"];
$conn = null;
$connected_db = "";

foreach ($dbnames as $dbname) {
    $conn = @mysqli_connect($servername, $username, $password, $dbname);
    if ($conn) {
        $connected_db = $dbname;
        break;
    }
}

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error() . "<br>Make sure your database is named 'users_db' or 'movie_db' and XAMPP MySQL is running.");
}

echo "<h3>✅ Connected successfully to database: <b>$connected_db</b></h3>";

// Let's check the users table to help debug login issues
$result = mysqli_query($conn, "SELECT id, name, email, password FROM users");

if ($result) {
    echo "<h4>Users in the database:</h4>";
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password (Hashed or Plain)</th></tr>";
    
    if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['password']) . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No users found. Try registering on the website first!</td></tr>";
    }
    echo "</table>";
    echo "<br><p><i>Note: The login API has been updated to accept both plain-text passwords (if you entered them manually here) and securely hashed passwords.</i></p>";
} else {
    echo "<p>Table 'users' does not exist in $connected_db. Please create it with columns: id, name, email, password.</p>";
}

mysqli_close($conn);
?>
