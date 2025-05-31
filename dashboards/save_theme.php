<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit('Unauthorized');
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get current theme settings
$current_theme = [];
try {
    $stmt = $pdo->prepare("SELECT theme_settings FROM preferences WHERE user_id = ? AND user_type = ?");
    $stmt->execute([$user_id, $user_type]);
    $settings = $stmt->fetchColumn();
    
    if ($settings) {
        $current_theme = json_decode($settings, true);
    }
} catch (PDOException $e) {
    // Log error but continue
    error_log("Theme settings error: " . $e->getMessage());
}

// Update theme settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme_mode = $_POST['theme_mode'];
    $new_settings = [
        'primary' => $_POST['primary'] ?? '',
        'accent' => $_POST['accent'] ?? '',
        'background' => $_POST['background'] ?? '',
        'card' => $_POST['card'] ?? ''
    ];
    
    // Validate colors
    if (!preg_match('/^#[a-f0-9]{6}$/i', $new_settings['primary']) ||
        !preg_match('/^#[a-f0-9]{6}$/i', $new_settings['accent']) ||
        !preg_match('/^#[a-f0-9]{6}$/i', $new_settings['background']) ||
        !preg_match('/^#[a-f0-9]{6}$/i', $new_settings['card'])) {
        header('HTTP/1.1 400 Bad Request');
        exit('Invalid color format');
    }
    
    // Update settings
    $current_theme[$theme_mode] = $new_settings;
    $json_settings = json_encode($current_theme);
    
    try {
        $table = match ($user_type) {
            'super_admin', 'admin' => 'admins',
            'faculty' => 'faculty',
            default => 'users'
        };
        
        // Update preferences
        $stmt = $pdo->prepare("UPDATE preferences SET theme_settings = ? WHERE user_id = ? AND user_type = ?");
        $stmt->execute([$json_settings, $user_id, $user_type]);
        
        // Update session
        $_SESSION['theme_settings'] = $current_theme;
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        header('HTTP/1.1 500 Internal Server Error');
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit();
}

// Return current settings
header('Content-Type: application/json');
echo json_encode($current_theme);
?>