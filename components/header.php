<!DOCTYPE html>
<html lang="en" class="<?= $preferences['theme'] === 'system' ? 
    (isset($_COOKIE['darkMode']) ? ($_COOKIE['darkMode'] === 'true' ? 'dark' : '') : 
    (isset($_SERVER['HTTP_DARK_MODE']) ? 'dark' : '')) : 
    ($preferences['theme'] === 'dark' ? 'dark' : '') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($user_type) ?> Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-primary: #f3f4f6;
            --text-primary: #111827;
            --card-bg: #ffffff;
            --sidebar-bg: #1e293b;
            --header-bg: #ffffff;
            --form-text: #0a1819; /* Light mode text */

        }
        
        .dark {
            --bg-primary: #0f172a;
            --text-primary: #e5f4f5;
            --card-bg: #1e293b;
            --sidebar-bg: #0f172a;
            --header-bg: #1e293b;
            --form-text: #e5f4f5; /* Dark mode text */

        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            transition: background-color 0.3s, color 0.3s;
        }

    textarea, input[type="text"], input[type="email"], input[type="password"], input[type="tel"], select, select option {
        background-color: var(--card-bg);
        color: var(--form-text);
        border: 1px solid var(--text-primary);
        padding: 0.5rem;
        border-radius: 0.375rem;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    
    
    .stats-card select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 1px var(--primary);
    }
        
        .user-badge {
            position: absolute;
            top: -12px;
            right: -12px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }
        
        .dashboard-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar {
            background-color: var(--sidebar-bg);
            transition: all 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -300px;
                top: 0;
                height: 100vh;
                z-index: 100;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
        
        .nav-link {
            transition: all 0.2s ease;
        }
        
        .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 4px solid;
        }
        
        .stats-card {
            background-color: var(--card-bg);
        }

    </style>
        <script>
        // Set initial theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const theme = '<?= $preferences['theme'] ?>';
            const isSystemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            
            if (theme === 'dark' || (theme === 'system' && isSystemDark)) {
                document.documentElement.classList.add('dark');
            }
        });
    </script>
</head>
<body class="min-h-screen">
    <!-- Mobile Menu Button -->
<button id="mobile-menu-button" class="md: fixed top-4 left-4 z-50 bg-card p-2 rounded-lg shadow-lg">
    <i class="fas fa-bars text-text text-xl"></i>
</button>
    
    