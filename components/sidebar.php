  
<!-- Sidebar -->
<div class="sidebar text-white w-64 min-h-screen py-8 px-4 fixed" >
    <div class="flex items-center justify-center mb-8">
        <i class="fas fa-graduation-cap text-3xl mr-3"></i>
        <h1 class="text-xl font-bold">School System</h1>
    </div>
    
    <div class="text-center mb-8">
        <div class="relative mx-auto mb-4">
            <div class="bg-gray-200 border-2 border-dashed rounded-full w-20 h-20 mx-auto"></div>
            <div class="user-badge 
                <?php 
                switch($user_type) {
                    case 'super_admin': echo 'bg-purple-600'; break;
                    case 'admin': echo 'bg-green-600'; break;
                    case 'faculty': echo 'bg-yellow-600'; break;
                    case 'student': echo 'bg-red-600'; break;
                    default: echo 'bg-blue-600';
                }
                ?> text-white">
                <i class="
                    <?php 
                    switch($user_type) {
                        case 'super_admin': echo 'fas fa-crown'; break;
                        case 'admin': echo 'fas fa-user-shield'; break;
                        case 'faculty': echo 'fas fa-chalkboard-teacher'; break;
                        case 'student': echo 'fas fa-user-graduate'; break;
                        default: echo 'fas fa-user';
                    }
                    ?>">
                </i>
            </div>
        </div>
        <h3 class="text-lg font-bold"><?= htmlspecialchars($name) ?></h3>
        <p class="text-gray-400 text-sm">@<?= htmlspecialchars($username) ?></p>
        <p class="mt-1 text-sm uppercase font-semibold 
            <?php 
            switch($user_type) {
                case 'super_admin': echo 'text-purple-400'; break;
                case 'admin': echo 'text-green-400'; break;
                case 'faculty': echo 'text-yellow-400'; break;
                case 'student': echo 'text-red-400'; break;
                default: echo 'text-blue-400';
            }
            ?>">
            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $user_type))) ?>
        </p>
    </div>
    
    <nav class="space-y-1">
        <a href="?section=dashboard" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-home mr-3"></i>
            <span>Dashboard</span>
        </a>
        
        <?php if (in_array($user_type, ['super_admin', 'admin'])): ?>
        <a href="?section=user_management" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'user_management' ? 'active' : '' ?>">
            <i class="fas fa-users-cog mr-3"></i>
            <span>User Management</span>
        </a>
        <?php endif; ?>
        
        <?php if (in_array($user_type, ['super_admin', 'admin'])): ?>
        <a href="?section=system_config" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'system_config' ? 'active' : '' ?>">
            <i class="fas fa-cogs mr-3"></i>
            <span>System Configuration</span>
        </a>
        <?php endif; ?>
        
        <?php if (in_array($user_type, ['faculty', 'student'])): ?>
        <a href="?section=schedule" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'schedule' ? 'active' : '' ?>">
            <i class="fas fa-calendar-alt mr-3"></i>
            <span>Schedule</span>
        </a>
        <?php endif; ?>
        
        <?php if ($user_type === 'faculty'): ?>
        <a href="?section=attendance" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'attendance' ? 'active' : '' ?>">
            <i class="fas fa-clipboard-list mr-3"></i>
            <span>Attendance</span>
        </a>
        <?php endif; ?>
        
        <?php if ($user_type === 'student'): ?>
        <a href="?section=courses" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'courses' ? 'active' : '' ?>">
            <i class="fas fa-book mr-3"></i>
            <span>My Courses</span>
        </a>
        <?php endif; ?>
        
        <a href="?section=reports" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'reports' ? 'active' : '' ?>">
            <i class="fas fa-chart-bar mr-3"></i>
            <span>Reports</span>
        </a>
        
        <a href="?section=settings" class="nav-link flex items-center px-4 py-3 rounded-lg <?= $active_section === 'settings' ? 'active' : '' ?>">
            <i class="fas fa-cog mr-3"></i>
            <span>Settings</span>
        </a>
    </nav>
    
    <form method="POST" class="mt-8">
        <button type="submit" name="logout" class="w-full flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg">
            <i class="fas fa-sign-out-alt mr-3"></i>
            <span>Logout</span>
        </button>
    </form>
</div>

    <!-- Main Content -->
    <div class="main-content ml-0 md:ml-64 transition-all duration-300">
        <!-- Updated Header -->
        <header class="shadow" style="background-color: var(--header-bg);">
        <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
            <h1 class="text-2xl font-bold">
                <?= ucfirst(str_replace('_', ' ', $user_type)) ?> Dashboard
            </h1>
            <div class="flex items-center space-x-4">
                <button id="theme-toggle" class="p-2 rounded-full bg-gray-100 dark:bg-gray-700">
                    <i class="fas fa-sun dark:hidden"></i>
                    <i class="fas fa-moon hidden dark:block"></i>
                </button>
                <button class="p-2 rounded-full bg-gray-100 dark:bg-gray-700">
                    <i class="fas fa-bell text-gray-600 dark:text-gray-300"></i>
                </button>
                <div class="relative">
                    <button class="flex items-center space-x-2">
                        <div class="bg-gray-200 border-2 border-dashed rounded-full w-10 h-10"></div>
                        <span class="hidden md:inline"><?= htmlspecialchars($name) ?></span>
                    </button>
                </div>
            </div>
        </div>
    </header>
    <!-- Content Area -->
    <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">