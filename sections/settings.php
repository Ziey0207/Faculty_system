<?php
$saved = isset($_GET['saved']);
$save_error = $save_error ?? null;
?>

<div class="stats-card rounded-xl p-6 shadow">
    <?php if ($saved): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">Your preferences have been saved.</span>
        </div>
    <?php endif; ?>
    
    <?php if ($save_error): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= $save_error ?></span>
        </div>
    <?php endif; ?>
    
    <h2 class="text-2xl font-bold mb-6">Account Settings</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Profile Settings -->
        <div class="md:col-span-2">
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4">Profile Information</h3>
                <form>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">First Name</label>
                            <input type="text" value="<?= htmlspecialchars($name) ?>" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Last Name</label>
                            <input type="text" value="Doe" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" value="john.doe@example.com" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Contact Number</label>
                        <input type="tel" value="+1234567890" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg">
                        Update Profile
                    </button>
                </form>
            </div>
            
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4">Change Password</h3>
                <form>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Current Password</label>
                        <input type="password" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">New Password</label>
                            <input type="password" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Confirm Password</label>
                            <input type="password" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                        </div>
                    </div>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg">
                        Change Password
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Preferences -->
        <div>
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4">Preferences</h3>
                <form method="POST">
                    <input type="hidden" name="save_preferences" value="1">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Theme</label>
                        <select name="theme" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="light" <?= $preferences['theme'] === 'light' ? 'selected' : '' ?>>Light</option>
                            <option value="dark" <?= $preferences['theme'] === 'dark' ? 'selected' : '' ?>>Dark</option>
                            <option value="system" <?= $preferences['theme'] === 'system' ? 'selected' : '' ?>>System Default</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="notifications_email" class="rounded text-blue-600" 
                                <?= $preferences['notifications_email'] ? 'checked' : '' ?>>
                            <span class="ml-2">Email notifications</span>
                        </label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="notifications_push" class="rounded text-blue-600" 
                                <?= $preferences['notifications_push'] ? 'checked' : '' ?>>
                            <span class="ml-2">Push notifications</span>
                        </label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Results per page</label>
                        <select name="results_per_page" class="w-full px-3 py-2 rounded-lg border border-gray-300 focus:border-primary focus:ring-1 focus:ring-primary">
                            <option value="5" <?= $preferences['results_per_page'] === 5 ? 'selected' : '' ?>>5</option>
                            <option value="10" <?= $preferences['results_per_page'] === 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= $preferences['results_per_page'] === 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= $preferences['results_per_page'] === 50 ? 'selected' : '' ?>>50</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg">
                        Save Preferences
                    </button>
                </form>
            </div>
            
            <div>
                <h3 class="text-lg font-semibold mb-4">Danger Zone</h3>
                <div class="border border-red-300 rounded-lg p-4 bg-red-50">
                    <h4 class="font-medium text-red-800 mb-2">Delete Account</h4>
                    <p class="text-sm text-red-600 mb-3">Permanently delete your account and all associated data.</p>
                    <button class="bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg text-sm">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>

        <div>
                    <h2 class="text-2xl font-bold mb-6">Theme Customization</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
        <div>
            <h3 class="text-lg font-semibold mb-4">Light Mode Colors</h3>
            <form method="POST" action="save_theme.php">
                <input type="hidden" name="theme_mode" value="light">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Primary Color</label>
                    <input type="color" name="primary" value="<?= $theme_settings['light']['primary'] ?? '#4ab2b6' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Accent Color</label>
                    <input type="color" name="accent" value="<?= $theme_settings['light']['accent'] ?? '#7873c7' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Background</label>
                    <input type="color" name="background" value="<?= $theme_settings['light']['background'] ?? '#f2fafa' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Card Background</label>
                    <input type="color" name="card" value="<?= $theme_settings['light']['card'] ?? '#ffffff' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg">
                    Save Light Theme
                </button>
            </form>
        </div>
        
        <div>
            <h3 class="text-lg font-semibold mb-4">Dark Mode Colors</h3>
            <form method="POST" action="save_theme.php">
                <input type="hidden" name="theme_mode" value="dark">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Primary Color</label>
                    <input type="color" name="primary" value="<?= $theme_settings['dark']['primary'] ?? '#49b3b6' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Accent Color</label>
                    <input type="color" name="accent" value="<?= $theme_settings['dark']['accent'] ?? '#3d378b' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Background</label>
                    <input type="color" name="background" value="<?= $theme_settings['dark']['background'] ?? '#060f0f' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1">Card Background</label>
                    <input type="color" name="card" value="<?= $theme_settings['dark']['card'] ?? '#0f172a' ?>" 
                           class="w-full h-10 cursor-pointer">
                </div>
                
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg">
                    Save Dark Theme
                </button>
            </form>
        </div>
        </div>
        </div>

</div>
    