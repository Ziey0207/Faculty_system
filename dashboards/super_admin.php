<!-- Welcome Banner -->
<div class="bg-gradient-to-r from-purple-600 to-indigo-700 rounded-xl p-6 text-white mb-8">
    <div class="flex flex-col md:flex-row justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold mb-2">Welcome, Super Admin <?= htmlspecialchars($name) ?>!</h2>
            <p class="text-purple-100">You have full control over the system.</p>
        </div>
        <div class="mt-4 md:mt-0">
            <button class="bg-white text-purple-600 hover:bg-purple-50 font-bold py-2 px-6 rounded-lg">
                System Report
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg mr-4">
                <i class="fas fa-users text-blue-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500">Total Users</p>
                <h3 class="text-2xl font-bold">1,865</h3>
            </div>
        </div>
    </div>
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg mr-4">
                <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500">System Health</p>
                <h3 class="text-2xl font-bold">98%</h3>
            </div>
        </div>
    </div>
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-lg mr-4">
                <i class="fas fa-database text-purple-600 text-2xl"></i>
            </div>
            <div>
                <p class="text-gray-500">Database Usage</p>
                <h3 class="text-2xl font-bold">64%</h3>
            </div>
        </div>
    </div>
</div>

<!-- Action Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="text-purple-600 text-3xl mb-4">
            <i class="fas fa-users-cog"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">User Management</h3>
        <p class="text-gray-600 mb-4">Manage all system users and permissions</p>
        <a href="?section=user_management" class="inline-block bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg">
            Manage Users
        </a>
    </div>
    
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="text-green-600 text-3xl mb-4">
            <i class="fas fa-cogs"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">System Configuration</h3>
        <p class="text-gray-600 mb-4">Configure global system settings</p>
        <a href="?section=system_config" class="inline-block bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg">
            Configure System
        </a>
    </div>
    
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="text-indigo-600 text-3xl mb-4">
            <i class="fas fa-chart-bar"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">Reports & Analytics</h3>
        <p class="text-gray-600 mb-4">Access detailed system reports</p>
        <a href="?section=reports" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg">
            View Reports
        </a>
    </div>
    
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="text-blue-600 text-3xl mb-4">
            <i class="fas fa-user-circle"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">My Profile</h3>
        <p class="text-gray-600 mb-4">Update your personal information</p>
        <a href="?section=settings" class="inline-block bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
            Edit Profile
        </a>
    </div>
    
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="text-yellow-600 text-3xl mb-4">
            <i class="fas fa-history"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">Audit Logs</h3>
        <p class="text-gray-600 mb-4">Review system activity logs</p>
        <button class="bg-yellow-600 hover:bg-yellow-700 text-white py-2 px-4 rounded-lg">
            View Logs
        </button>
    </div>
    
    <div class="dashboard-card stats-card rounded-xl p-6 shadow">
        <div class="text-red-600 text-3xl mb-4">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h3 class="text-xl font-bold mb-2">Security Settings</h3>
        <p class="text-gray-600 mb-4">Configure security policies</p>
        <button class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg">
            Configure
        </button>
    </div>
</div>