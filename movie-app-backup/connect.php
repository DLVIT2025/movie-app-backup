<?php
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Database connection using mysqli just like the tutorial
    // Let's connect to users_db first, fall back to movie_db
    $conn = new mysqli('localhost', 'root', '', 'users_db');
    
    if($conn->connect_error){
        $conn = new mysqli('localhost', 'root', '', 'movie_db');
    }

    if($conn->connect_error){
        echo "$conn->connect_error";
        die("Connection Failed : ". $conn->connect_error);
    } else {
        // First, check if the email already exists to prevent duplicate entry errors
        $check_stmt = $conn->prepare("select id from users where email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<script>
                    alert('This email is already registered! Please sign in or use a different email.');
                    window.location.href = 'index.php';
                  </script>";
        } else {
            // Hash the password so the rest of the application still works perfectly
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Generate a random ID since your 'users' table 'id' column isn't set to AUTO_INCREMENT
            $id = mt_rand(1, 99999999);

            $stmt = $conn->prepare("insert into users(id, name, email, password) values(?, ?, ?, ?)");
            $stmt->bind_param("isss", $id, $name, $email, $hashed_password);
            $execval = $stmt->execute();
            
            if($execval) {
                echo "<script>
                        alert('Registration successfully...');
                        window.location.href = 'index.php';
                      </script>";
            } else {
                echo "<script>
                        alert('Error during registration...');
                        window.location.href = 'index.php';
                      </script>";
            }
            $stmt->close();
        }
        $check_stmt->close();
        $conn->close();
    }
?>
