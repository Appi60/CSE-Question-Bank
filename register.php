<?php
session_start();

$errors = [];

// Connect to MySQL database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = filter_var(trim($_POST['full_name']), FILTER_SANITIZE_STRING);
    $phone_number = filter_var(trim($_POST['phone_number']), FILTER_SANITIZE_STRING);
    $college_name = filter_var(trim($_POST['college_name']), FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (!preg_match("/^[a-zA-Z\s]+$/", $full_name)) {
        $errors[] = "Full name must contain only alphabets and spaces.";
    }

    if (!preg_match("/^[0-9]+$/", $phone_number)) {
        $errors[] = "Phone number must contain only numbers.";
    }

    if (!preg_match("/^[a-zA-Z\s]+$/", $college_name)) {
        $errors[] = "College name must contain only alphabets and spaces.";
    }

    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Check if phone number already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone_number = ?");
        $stmt->bind_param("s", $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = "Phone number is already registered.";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into the database
            $stmt = $conn->prepare("INSERT INTO users (full_name, phone_number, college_name, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $phone_number, $college_name, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registration successful!";
                header("Location: login.php"); // Redirect to login page after registration
                exit();
            } else {
                $errors[] = "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 400px; margin: 0px auto; padding: 20px; background-color: #fff; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        input { width: 100%; padding: 10px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc; }
        button { width: 100%; padding: 10px; background-color: rgb(40, 78, 183); color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color:rgb(69, 157, 160); }
        .warning { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h2>Registration Form</h2>

    <?php if (!empty($errors)): ?>
        <div class="warning">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <input type="text" name="full_name" placeholder="Full Name" required>
        <input type="text" name="phone_number" placeholder="Phone Number" required>
        <input type="text" name="college_name" placeholder="College Name" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</div>

</body>
</html>




