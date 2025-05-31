<?php
session_start();

// Database configuration (replace with your actual credentials)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "school_management";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$input_username = $_POST['username'];
$input_password = $_POST['password'];

// Prepare and execute query
$stmt = $conn->prepare("SELECT id, username, password FROM super_admins WHERE username = ?");
$stmt->bind_param("s", $input_username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    
    // Verify password (in real application, use password_verify() with hashed passwords)
    if ($input_password === $row['password']) {
        // Authentication successful
        $_SESSION['superadmin_logged_in'] = true;
        $_SESSION['superadmin_id'] = $row['id'];
        $_SESSION['superadmin_username'] = $row['username'];
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    }
}

// Authentication failed
header("Location: index.html?error=1");
exit();

$stmt->close();
$conn->close();
?>