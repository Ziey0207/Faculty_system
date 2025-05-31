<?php
session_start();
require 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Get user details
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$name = $_SESSION['name'];
$username = $_SESSION['username'];

// Get user preferences
$preferences = [
    'theme' => 'system',
    'notifications_email' => true,
    'notifications_push' => true,
    'results_per_page' => 10
];

try {
    $stmt = $pdo->prepare("SELECT * FROM preferences WHERE user_id = ? AND user_type = ?");
    $stmt->execute([$user_id, $user_type]);
    $pref_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($pref_data) {
        $preferences = [
            'theme' => $pref_data['theme'],
            'notifications_email' => (bool)$pref_data['notifications_email'],
            'notifications_push' => (bool)$pref_data['notifications_push'],
            'results_per_page' => (int)$pref_data['results_per_page']
        ];
    }
    $_SESSION['preferences'] = $preferences;
} catch (PDOException $e) {
    error_log("Preferences error: " . $e->getMessage());
}

// Handle logout
if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Handle preference saving
if (isset($_POST['save_preferences'])) {
    try {
        $theme = $_POST['theme'] ?? 'system';
        $notifications_email = isset($_POST['notifications_email']) ? 1 : 0;
        $notifications_push = isset($_POST['notifications_push']) ? 1 : 0;
        $results_per_page = (int)($_POST['results_per_page'] ?? 10);
        
        // Check if preferences already exist
        $check_stmt = $pdo->prepare("SELECT id FROM preferences WHERE user_id = ? AND user_type = ?");
        $check_stmt->execute([$user_id, $user_type]);
        
        if ($check_stmt->rowCount() > 0) {
            // Update existing preferences
            $update_stmt = $pdo->prepare("UPDATE preferences SET 
                theme = ?, 
                notifications_email = ?, 
                notifications_push = ?, 
                results_per_page = ? 
                WHERE user_id = ? AND user_type = ?");
                
            $update_stmt->execute([
                $theme,
                $notifications_email,
                $notifications_push,
                $results_per_page,
                $user_id,
                $user_type
            ]);
        } else {
            // Insert new preferences
            $insert_stmt = $pdo->prepare("INSERT INTO preferences 
                (user_id, user_type, theme, notifications_email, notifications_push, results_per_page) 
                VALUES (?, ?, ?, ?, ?, ?)");
                
            $insert_stmt->execute([
                $user_id,
                $user_type,
                $theme,
                $notifications_email,
                $notifications_push,
                $results_per_page
            ]);
        }
        
        // Update session preferences
        $_SESSION['preferences'] = [
            'theme' => $theme,
            'notifications_email' => (bool)$notifications_email,
            'notifications_push' => (bool)$notifications_push,
            'results_per_page' => $results_per_page
        ];
        
        // Redirect to prevent form resubmission
        header("Location: ?section=settings&saved=1");
        exit();
        
    } catch (PDOException $e) {
        error_log("Save preferences error: " . $e->getMessage());
        $save_error = "Failed to save preferences. Please try again.";
    }
}

// Load active section
$active_section = 'dashboard';
if (isset($_GET['section'])) {
    $allowed_sections = match ($user_type) {
        'super_admin' => ['dashboard', 'user_management', 'system_config', 'reports', 'settings'],
        'admin' => ['dashboard', 'user_management', 'reports', 'settings'],
        'faculty' => ['dashboard', 'schedule', 'attendance', 'settings'],
        'student' => ['dashboard', 'schedule', 'courses', 'settings'],
        default => ['dashboard', 'settings']
    };
    
    if (in_array($_GET['section'], $allowed_sections)) {
        $active_section = $_GET['section'];
    }
}

// Include components
require 'components/header.php';
require 'components/sidebar.php';

// Load content based on active section
if ($active_section === 'dashboard') {
    // Load role-specific dashboard
    $dashboard_file = "dashboards/{$user_type}.php";
    if (file_exists($dashboard_file)) {
        require $dashboard_file;
    } else {
        echo "<div class='text-center py-10'>Dashboard not available for your role.</div>";
    }
} else {
    // Load section
    $section_file = "sections/{$active_section}.php";
    if (file_exists($section_file)) {
        require $section_file;
    } else {
        echo "<div class='text-center py-10'>Section not found.</div>";
    }
}

// Include footer
echo '</div>'; // Close main container
require 'components/footer.php';
?>