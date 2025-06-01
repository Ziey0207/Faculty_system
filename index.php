<?php
session_start();
require 'db_connection.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

// AJAX validation endpoint - Enhanced with detailed error messages
if (isset($_SERVER['REQUEST_METHOD']) == 'POST' && isset($_POST['validate'])) {
    $field = $_POST['field'];
    $value = trim($_POST['value']);
    $user_type = $_POST['user_type'] ?? 'student';
    
    header('Content-Type: application/json');
    
    // Empty value should be considered valid (required fields handled separately)
    if (empty($value)) {
        echo json_encode(['valid' => true, 'message' => '']);
        exit();
    }
    
    try {
        $exists = false;
        $message = '';
        
        switch($field) {
            case 'first_name':
            case 'last_name':
                // Check if name contains only letters and spaces
                if (!preg_match('/^[a-zA-Z\s]+$/', $value)) {
                    echo json_encode([
                        'valid' => false, 
                        'message' => 'Name should contain only letters and spaces'
                    ]);
                } else {
                    echo json_encode(['valid' => true, 'message' => '']);
                }
                exit();
                
            case 'email':
                // Validate email format
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    echo json_encode([
                        'valid' => false, 
                        'message' => 'Invalid email format. Must be like user@example.com'
                    ]);
                    exit();
                }
                
                // Check all tables for email existence
                $tables = ['users', 'faculty', 'admins'];
                foreach($tables as $table) {
                    $stmt = $pdo->prepare("SELECT id FROM $table WHERE email = ?");
                    $stmt->execute([$value]);
                    if ($stmt->rowCount() > 0) {
                        $exists = true;
                        break;
                    }
                }
                $message = $exists ? 'Email already registered to another user' : '';
                break;
                
            case 'contact':
                // Validate phone number format (international format)
                if (!preg_match('/^\+?[0-9\s\-\(\)]{7,20}$/', $value)) {
                    echo json_encode([
                        'valid' => false, 
                        'message' => 'Invalid contact number. Use international format like +1234567890'
                    ]);
                    exit();
                }
                
                // Check all tables for contact existence
                $tables = ['users', 'faculty', 'admins'];
                foreach($tables as $table) {
                    $stmt = $pdo->prepare("SELECT id FROM $table WHERE contact_number = ?");
                    $stmt->execute([$value]);
                    if ($stmt->rowCount() > 0) {
                        $exists = true;
                        break;
                    }
                }
                $message = $exists ? 'Contact number already registered to another user' : '';
                break;
                
            case 'username':
                // Determine which table to check based on user type
                switch($user_type) {
                    case 'admin':
                        $table = 'admins';
                        break;
                    case 'faculty':
                        $table = 'faculty';
                        break;
                    case 'student':
                    case 'staff':
                        $table = 'users';
                        break;
                    default:
                        $table = 'users';
                }
                
                $stmt = $pdo->prepare("SELECT id FROM $table WHERE username = ?");
                $stmt->execute([$value]);
                $exists = $stmt->rowCount() > 0;
                $message = $exists ? 'Username already taken. Please choose another' : '';
                break;
        }
        
        echo json_encode(['valid' => !$exists, 'message' => $message]);
        
    } catch (PDOException $e) {
        error_log("Validation error: " . $e->getMessage());
        echo json_encode(['valid' => false, 'message' => 'System error. Please try again later.']);
    }
    exit();
}



// Handle login
if (isset($_SERVER['REQUEST_METHOD']) == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $user_type = $_POST['user_type'];
    
    // Validate input
    if (empty($username) || empty($password)) {
        $login_error = "Please enter both username and password";
    } else {
        // Determine which table to query based on user type
        switch($user_type) {
            case 'super_admin':
            case 'admin':
                $table = 'admins';
                $role_field = 'role';
                break;
            case 'faculty':
                $table = 'faculty';
                $role_field = '';
                break;
            case 'student':
            case 'staff':
                $table = 'users';
                $role_field = 'type';
                break;
            default:
                $table = 'users';
                $role_field = 'type';
        }
        
        try {
            // Check if user exists and is active
            $sql = "SELECT * FROM $table WHERE username = ? AND is_active = 1 AND is_approved = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // PLAIN TEXT PASSWORD COMPARISON
            if ($user && $password === $user['password']) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
                
                // Set user type based on actual role
                if ($role_field && isset($user[$role_field])) {
                    $_SESSION['user_type'] = $user[$role_field];
                } else {
                    $_SESSION['user_type'] = $user_type;
                }
                
                // For admins, check if super admin
                if ($table === 'admins') {
                    $_SESSION['is_super_admin'] = ($user['role'] === 'super_admin');
                }
                
                header('Location: dashboard.php');
                exit();
            } else {
                $login_error = "Invalid username or password, or account inactive";
            }
        } catch (PDOException $e) {
            $login_error = "Database error: " . $e->getMessage();
        }
    }
}

// Handle registration
if (isset($_SERVER['REQUEST_METHOD']) == 'POST' && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $address = trim($_POST['address'] ?? ''); 
    $username = trim($_POST['reg_username'] ?? '');
    $password = trim($_POST['reg_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $user_type = $_POST['reg_user_type'] ?? '';
    
    // Validate input
    $errors = [];
    if (empty($first_name)) $errors[] = "First name is required";
    if (empty($last_name)) $errors[] = "Last name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($contact)) $errors[] = "Contact number is required";
    if (empty($gender)) $errors[] = "Gender is required";
    if (empty($username)) $errors[] = "Username is required";
    if (empty($password)) $errors[] = "Password is required";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    
    if (empty($errors)) {
        // Determine which table to insert into
        switch($user_type) {
            case 'admin':
                $table = 'admins';
                $role = 'admin';
                $is_approved = 0; // Requires approval
                break;
            case 'faculty':
                $table = 'faculty';
                $role = ''; // Not needed for faculty
                $is_approved = 0; // Requires approval
                break;
            case 'student':
            case 'staff':
                $table = 'users';
                $role = $user_type; // 'student' or 'staff'
                $is_approved = 0; // Requires approval
                break;
            default:
                $table = 'users';
                $role = 'student';
                $is_approved = 0; // Requires approval
        }
        
        // Check if username already exists
        try {
            $stmt = $pdo->prepare("SELECT id FROM $table WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->rowCount() > 0) {
                $errors[] = "Username already exists";
            } else {
                // PLAIN TEXT PASSWORD (should be hashed in production)
                $plain_password = $password;
                
                // Prepare SQL based on table structure
                if ($table === 'admins') {
                    $insert_sql = "INSERT INTO admins (
                        first_name, last_name, middle_name, email, contact_number, 
                        gender, address, username, password, role, is_approved, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
                    $params = [
                        $first_name, $last_name, $middle_name, $email, $contact,
                        $gender, $address, $username, $plain_password, $role, $is_approved
                    ];
                } 
                elseif ($table === 'faculty') {
                    // Generate faculty ID number (you might have a better system)
                    $id_no = 'FAC-' . strtoupper(substr($first_name, 0, 1)) . substr($last_name, 0, 3) . rand(100, 999);
                    
                    $insert_sql = "INSERT INTO faculty (
                        id_no, first_name, last_name, middle_name, email, contact_number, 
                        gender, address, username, password, is_approved, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
                    $params = [
                        $id_no, $first_name, $last_name, $middle_name, $email, $contact,
                        $gender, $address, $username, $plain_password, $is_approved
                    ];
                } 
                else { // users table
                    $insert_sql = "INSERT INTO users (
                        first_name, last_name, middle_name, email, contact_number, 
                        gender, address, username, password, type, is_approved, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                    
                    $params = [
                        $first_name, $last_name, $middle_name, $email, $contact,
                        $gender, $address, $username, $plain_password, $role, $is_approved
                    ];
                }
                
                $stmt = $pdo->prepare($insert_sql);
                $stmt->execute($params);
                
                if ($stmt->rowCount() > 0) {
                    $register_success = "Registration submitted! Your account requires admin approval before you can login.";
                } else {
                    $errors[] = "Registration failed. Please try again.";
                }
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    if (!empty($errors)) {
        $register_error = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        text: 'var(--text)',
                        background: 'var(--background)',
                        primary: 'var(--primary)',
                        secondary: 'var(--secondary)',
                        accent: 'var(--accent)',
                        card: 'var(--card)',
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        :root {
            --text: #0a1819;
            --background: #f2fafa;
            --primary: #4ab2b6;
            --secondary: #a0acd9;
            --accent: #7873c7;
            --card: #ffffff;
        }
        
        .dark {
            --text: #e5f4f5;
            --background: #060f0f;
            --primary: #49b3b6;
            --secondary: #26335f;
            --accent: #3d378b;
            --card: #0f172a;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            transition: background-color 0.3s, color 0.3s;
        }
        
        .card {
            background-color: var(--card);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            overflow: hidden;
            border-radius: 12px;
            border: 1px solid rgba(var(--text-rgb), 0.1);
        }

        .card-header-text {
            color: rgba(255, 255, 255, 0.9);
        }
        
        /* Add contrast for better readability in dark mode */
        .dark .card {
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-link.active {
            border-bottom: 3px solid var(--primary);
            color: var(--primary);
            font-weight: 600;
        }
        
        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text);
            opacity: 0.7;
        }
        
        .error-message {
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        
        .validation-message {
            font-size: 0.875rem;
            margin-top: 0.25rem;
            min-height: 1.25rem;
        }
        
        .input-valid .valid-icon {
            display: block;
        }
        
        .input-invalid .invalid-icon {
            display: block;
        }
        
        .spinner {
            border: 2px solid rgba(243, 243, 243, 0.3);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .user-type-btn {
            transition: all 0.3s ease;
            opacity: 0.7;
        }
        
        .user-type-btn.active {
            opacity: 1;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .theme-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: var(--secondary);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .validation-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
        }
        
        .valid-icon {
            color: #10b981;
            display: none;
        }
        
        .invalid-icon {
            color: #ef4444;
            display: none;
        }
        
        .submit-disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        
        .input-container {
            position: relative;
            margin-bottom: 1.5rem;
        }
    </style>
        <script>
        // Set initial theme based on system preference
        document.addEventListener('DOMContentLoaded', function() {
            const systemPrefersDark = window.matchMedia && 
                window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (systemPrefersDark) {
                document.documentElement.classList.add('dark');
                // Update theme toggle icon
                const themeIcon = document.getElementById('theme-toggle').querySelector('i');
                themeIcon.classList.replace('fa-moon', 'fa-sun');
            }
        });
    </script>
</head>
<body class="bg-background text-text">
    <button id="theme-toggle" class="theme-toggle">
        <i class="fas fa-moon"></i>
    </button>
    
    <div class="w-full max-w-6xl">
        <div class="card rounded-xl">
            <div class="bg-gradient-to-r from-primary to-accent py-8 px-8 text-white text-center">
                <div class="flex flex-col items-center">
                    <i class="fas fa-graduation-cap text-5xl mb-4"></i>
                    <h1 class="text-3xl font-bold">School Management System</h1>
                    <p class="text-blue-100 mt-2">Complete User Management Solution</p>
                </div>
            </div>
            
            <div class="border-b border-gray-200 dark:border-gray-700">
                <ul class="flex flex-wrap -mb-px text-base font-medium text-center">
                    <li class="flex-1">
                        <a href="#" id="login-tab" class="nav-link active inline-block w-full py-4 border-b-2">Login</a>
                    </li>
                    <li class="flex-1">
                        <a href="#" id="register-tab" class="nav-link inline-block w-full py-4 border-b-2">Register</a>
                    </li>
                </ul>
            </div>
            
            <div id="login-form" class="p-8">
                <?php if (isset($login_error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 error-message" role="alert">
                        <p><?php echo $login_error; ?></p>
                        <p class="text-sm mt-2">If you've just registered, your account may be pending approval.</p>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($register_success)): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p><?php echo $register_success; ?></p>
                        <p class="text-sm mt-2">You'll receive an email notification when your account is approved.</p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="login" value="1">
                    
                    <div class="mb-6">
                        <label for="username" class="block text-sm font-medium mb-1">Username</label>
                        <div class="relative">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" class="w-full pl-10 pr-3 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="Enter your username" required>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium mb-1">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="••••••••" required>
                            <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label for="user-type" class="block text-sm font-medium mb-1">Login as</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <button type="button" class="user-type-btn bg-purple-600 text-white py-2 px-4 rounded-lg transition active" data-type="super_admin">
                                <i class="fas fa-crown mr-2"></i> Super Admin
                            </button>
                            <button type="button" class="user-type-btn bg-green-600 text-white py-2 px-4 rounded-lg transition" data-type="admin">
                                <i class="fas fa-user-shield mr-2"></i> Admin
                            </button>
                            <button type="button" class="user-type-btn bg-yellow-600 text-white py-2 px-4 rounded-lg transition" data-type="faculty">
                                <i class="fas fa-chalkboard-teacher mr-2"></i> Faculty
                            </button>
                            <button type="button" class="user-type-btn bg-red-600 text-white py-2 px-4 rounded-lg transition" data-type="student">
                                <i class="fas fa-user-graduate mr-2"></i> Student | Staff
                            </button>
                        </div>
                        <input type="hidden" id="user_type" name="user_type" value="super_admin">
                    </div>
                    
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" id="remember" name="remember" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded">
                            <label for="remember" class="ml-2 block text-sm">Remember me</label>
                        </div>
                        <div>
                            <a href="#" class="text-sm font-medium text-primary hover:text-accent">Forgot password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary hover:bg-accent text-white font-bold py-3 px-4 rounded-lg transition">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login to Dashboard
                    </button>
                </form>
            </div>
            
            <div id="register-form" class="hidden p-8">
                <?php if (isset($register_error)): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 error-message" role="alert">
                        <p><?php echo $register_error; ?></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" id="registrationForm">
                    <input type="hidden" name="register" value="1">
                    
                    <div class="mb-6">
                        <label for="reg_user_type" class="block text-sm font-medium mb-1">Register as</label>
                        <select id="reg_user_type" name="reg_user_type" class="w-full py-3 px-4 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card">
                            <option value="admin">Admin (Dean/Department Head)</option>
                            <option value="faculty">Faculty Member</option>
                            <option value="staff">Staff Member</option>
                            <option value="student">Student</option>
                        </select>
                    </div>
                    
                    <div class="form-grid mb-6">
                        <!-- First Name -->
                        <div class="input-container">
                            <label for="first_name" class="block text-sm font-medium mb-1">First Name *</label>
                            <div class="relative">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="first_name" name="first_name" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="John" required>
                                <i class="fas fa-check-circle validation-icon valid-icon"></i>
                                <i class="fas fa-times-circle validation-icon invalid-icon"></i>
                                <span class="validation-loading absolute right-3 top-3 hidden">
                                    <i class="spinner"></i>
                                </span>
                            </div>
                            <div class="validation-message text-red-500" id="first_name_message"></div>
                        </div>
                        
                        <!-- Last Name -->
                        <div class="input-container">
                            <label for="last_name" class="block text-sm font-medium mb-1">Last Name *</label>
                            <div class="relative">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="last_name" name="last_name" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="Doe" required>
                                <i class="fas fa-check-circle validation-icon valid-icon"></i>
                                <i class="fas fa-times-circle validation-icon invalid-icon"></i>
                                <span class="validation-loading absolute right-3 top-3 hidden">
                                    <i class="spinner"></i>
                                </span>
                            </div>
                            <div class="validation-message text-red-500" id="last_name_message"></div>
                        </div>
                        
                        <!-- Middle Name -->
                        <div>
                            <label for="middle_name" class="block text-sm font-medium mb-1">Middle Name</label>
                            <div class="relative">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="middle_name" name="middle_name" class="w-full pl-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="(Optional)">
                            </div>
                        </div>
                        
                        <!-- Gender -->
                        <div class="input-container">
                            <label for="gender" class="block text-sm font-medium mb-1">Gender *</label>
                            <div class="relative">
                                <i class="fas fa-venus-mars input-icon"></i>
                                <select id="gender" name="gender" class="w-full pl-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="form-grid mb-6">
                        <!-- Email -->
                        <div class="md:col-span-2 input-container">
                            <label for="email" class="block text-sm font-medium mb-1">Email Address *</label>
                            <div class="relative">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" id="email" name="email" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="you@example.com" required>
                                <i class="fas fa-check-circle validation-icon valid-icon"></i>
                                <i class="fas fa-times-circle validation-icon invalid-icon"></i>
                                <span class="validation-loading absolute right-3 top-3 hidden">
                                    <i class="spinner"></i>
                                </span>
                            </div>
                            <div class="validation-message text-red-500" id="email_message"></div>
                        </div>
                        
                        <!-- Contact Number -->
                        <div class="input-container">
                            <label for="contact" class="block text-sm font-medium mb-1">Contact Number *</label>
                            <div class="relative">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="tel" id="contact" name="contact" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="+1234567890" required>
                                <i class="fas fa-check-circle validation-icon valid-icon"></i>
                                <i class="fas fa-times-circle validation-icon invalid-icon"></i>
                                <span class="validation-loading absolute right-3 top-3 hidden">
                                    <i class="spinner"></i>
                                </span>
                            </div>
                            <div class="validation-message text-red-500" id="contact_message"></div>
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="mb-6">
                        <label for="address" class="block text-sm font-medium mb-1">Address <span class="text-sm text-gray-500">(Optional)</span></label>
                        <div class="relative">
                            <i class="fas fa-home input-icon top-4"></i>
                            <textarea id="address" name="address" rows="3" class="w-full pl-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="Enter your full address (optional)"></textarea>
                        </div>
                    </div>
                    
                    <!-- Account Credentials -->
                    <div class="form-grid mb-6">
                        <!-- Username -->
                        <div class="input-container">
                            <label for="reg_username" class="block text-sm font-medium mb-1">Username *</label>
                            <div class="relative">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="reg_username" name="reg_username" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="Choose a username" required>
                                <i class="fas fa-check-circle validation-icon valid-icon"></i>
                                <i class="fas fa-times-circle validation-icon invalid-icon"></i>
                                <span class="validation-loading absolute right-3 top-3 hidden">
                                    <i class="spinner"></i>
                                </span>
                            </div>
                            <div class="validation-message text-red-500" id="reg_username_message"></div>
                        </div>
                        
                        <!-- Password -->
                        <div class="input-container">
                            <label for="reg_password" class="block text-sm font-medium mb-1">Password *</label>
                            <div class="relative">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="reg_password" name="reg_password" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="••••••••" required>
                                <button type="button" class="toggleRegPassword absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="input-container">
                            <label for="confirm_password" class="block text-sm font-medium mb-1">Confirm Password *</label>
                            <div class="relative">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="confirm_password" name="confirm_password" class="w-full pl-10 pr-10 py-3 rounded-lg border border-gray-300 dark:border-gray-600 focus:border-primary focus:ring-1 focus:ring-primary bg-card" placeholder="••••••••" required>
                                <button type="button" class="toggleConfirmPassword absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="validation-message text-red-500" id="confirm_password_message"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Terms Agreement -->
                    <div class="flex items-center mb-6">
                        <input type="checkbox" id="terms" name="terms" class="h-4 w-4 text-primary focus:ring-primary border-gray-300 dark:border-gray-600 rounded" required>
                        <label for="terms" class="ml-2 block text-sm">
                            I agree to the <a href="#" class="text-primary hover:text-accent">Terms and Conditions</a>
                        </label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" id="submit-btn" class="w-full bg-primary hover:bg-accent text-white font-bold py-3 px-4 rounded-lg transition submit-disabled" disabled>
                        <i class="fas fa-user-plus mr-2"></i> Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');
        const themeIcon = themeToggle.querySelector('i');

                // Get initial theme state
        const systemPrefersDark = window.matchMedia && 
            window.matchMedia('(prefers-color-scheme: dark)').matches;
        let isDark = document.documentElement.classList.contains('dark') || systemPrefersDark;
        
        // Update icon based on initial state
        if (isDark) {
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        }
        
        themeToggle.addEventListener('click', function() {
            isDark = !isDark;
            document.documentElement.classList.toggle('dark', isDark);
            
            // Update icon
            themeIcon.classList.toggle('fa-moon', !isDark);
            themeIcon.classList.toggle('fa-sun', isDark);
        });
        
        // Tab switching
        const loginTab = document.getElementById('login-tab');
        const registerTab = document.getElementById('register-tab');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        loginTab.addEventListener('click', function() {
            loginTab.classList.add('active');
            registerTab.classList.remove('active');
            loginForm.classList.remove('hidden');
            registerForm.classList.add('hidden');
        });

        registerTab.addEventListener('click', function() {
            registerTab.classList.add('active');
            loginTab.classList.remove('active');
            registerForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        });

        // Password visibility toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }

        // Registration password visibility
        document.querySelectorAll('.toggleRegPassword, .toggleConfirmPassword').forEach(button => {
            button.addEventListener('click', function() {
                const isConfirm = this.classList.contains('toggleConfirmPassword');
                const input = isConfirm ? document.getElementById('confirm_password') : document.getElementById('reg_password');
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        });

        // User type selection (login)
        document.querySelectorAll('.user-type-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.user-type-btn').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');
                document.getElementById('user_type').value = this.dataset.type;
            });
        });

        // Debounce function to limit validation requests
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this, args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    func.apply(context, args);
                }, wait);
            };
        }

        // Validation fields setup
        const validationFields = {
            first_name: {
                element: document.getElementById('first_name'),
                message: document.getElementById('first_name_message'),
                loader: document.querySelector('#first_name ~ .validation-loading'),
                container: document.getElementById('first_name').closest('.input-container'),
                type: 'first_name'
            },
            last_name: {
                element: document.getElementById('last_name'),
                message: document.getElementById('last_name_message'),
                loader: document.querySelector('#last_name ~ .validation-loading'),
                container: document.getElementById('last_name').closest('.input-container'),
                type: 'last_name'
            },
            email: {
                element: document.getElementById('email'),
                message: document.getElementById('email_message'),
                loader: document.querySelector('#email ~ .validation-loading'),
                container: document.getElementById('email').closest('.input-container'),
                type: 'email'
            },
            contact: {
                element: document.getElementById('contact'),
                message: document.getElementById('contact_message'),
                loader: document.querySelector('#contact ~ .validation-loading'),
                container: document.getElementById('contact').closest('.input-container'),
                type: 'contact'
            },
            reg_username: {
                element: document.getElementById('reg_username'),
                message: document.getElementById('reg_username_message'),
                loader: document.querySelector('#reg_username ~ .validation-loading'),
                container: document.getElementById('reg_username').closest('.input-container'),
                type: 'username'
            }
        };

        // Validate field function
        function validateField(fieldName, value, userType) {
            const field = validationFields[fieldName];
            
            // Reset validation state
            field.container.classList.remove('input-valid', 'input-invalid');
            field.message.textContent = '';
            
            // Skip validation if empty
            if (!value.trim()) {
                field.loader.classList.add('hidden');
                return;
            }
            
            // Show loading spinner
            field.loader.classList.remove('hidden');
            
            // Create form data
            const formData = new FormData();
            formData.append('validate', '1');
            formData.append('field', field.type);
            formData.append('value', value);
            
            // Add user type for username validation
            if (fieldName === 'reg_username') {
                formData.append('user_type', userType);
            }
            
            // Send validation request
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                field.loader.classList.add('hidden');
                
                if (data.valid) {
                    field.container.classList.add('input-valid');
                    field.container.classList.remove('input-invalid');
                } else {
                    field.container.classList.add('input-invalid');
                    field.container.classList.remove('input-valid');
                    field.message.textContent = data.message;
                }
                checkFormValidity();
            })
            .catch(error => {
                field.loader.classList.add('hidden');
                field.message.textContent = 'Validation failed. Please try again.';
                console.error('Validation error:', error);
            });
        }

        // Check overall form validity
        function checkFormValidity() {
            const submitBtn = document.getElementById('submit-btn');
            let isValid = true;
            
            // Check validation fields
            Object.keys(validationFields).forEach(fieldName => {
                const field = validationFields[fieldName];
                const value = field.element.value.trim();
                
                // Required field validation
                if (value && field.container.classList.contains('input-invalid')) {
                    isValid = false;
                }
                
                // Empty required field
                if (!value && field.element.required) {
                    isValid = false;
                }
            });
            
            // Check password match
            const password = document.getElementById('reg_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const confirmMessage = document.getElementById('confirm_password_message');
            
            if (password && confirmPassword && password !== confirmPassword) {
                confirmMessage.textContent = 'Passwords do not match';
                isValid = false;
            } else {
                confirmMessage.textContent = '';
            }
            
            // Check required fields
            const requiredFields = [
                'first_name', 'last_name', 'email', 'contact', 
                'gender', 'reg_username', 'reg_password', 'confirm_password'
            ];
            
            requiredFields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field && !field.value.trim()) {
                    isValid = false;
                }
            });
            
            // Check terms agreement
            if (!document.getElementById('terms').checked) {
                isValid = false;
            }
            
            // Update button state
            submitBtn.disabled = !isValid;
            submitBtn.classList.toggle('submit-disabled', !isValid);
        }
        
        // Set up validation for each field
        Object.keys(validationFields).forEach(fieldName => {
            const field = validationFields[fieldName];
            const debouncedValidation = debounce(() => {
                const userType = document.getElementById('reg_user_type').value;
                validateField(fieldName, field.element.value, userType);
            }, 300);
            
            field.element.addEventListener('input', debouncedValidation);
        });
        
        // Update validation when user type changes (for username)
        document.getElementById('reg_user_type').addEventListener('change', function() {
            const usernameField = validationFields.reg_username;
            if (usernameField.element.value) {
                validateField('reg_username', usernameField.element.value, this.value);
            }
        });
        
        // Password match validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            checkFormValidity();
        });
        
        // Terms agreement change
        document.getElementById('terms').addEventListener('change', checkFormValidity);
        
        // Form submission handler
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (document.getElementById('submit-btn').disabled) {
                e.preventDefault();
                alert('Please fix validation errors before submitting.');
            }
        });
        
        // Initialize form validation
        checkFormValidity();
    });
    </script>
</body>
</html>