<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user logic
    } elseif (isset($_POST['edit_user'])) {
        // Edit user logic
    } elseif (isset($_POST['delete_user'])) {
        // Delete user logic
    } elseif (isset($_POST['approve_user'])) {
        // Approve user logic
    }
}

// Get all users
$users = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6">User Management</h2>
    
    <!-- Add User Button -->
    <div class="mb-6">
        <button class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded">
            <i class="fas fa-plus mr-2"></i> Add New User
        </button>
    </div>
    
    <!-- User Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-card rounded-lg overflow-hidden">
            <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                    <th class="py-2 px-4">ID</th>
                    <th class="py-2 px-4">Name</th>
                    <th class="py-2 px-4">Email</th>
                    <th class="py-2 px-4">Role</th>
                    <th class="py-2 px-4">Status</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="py-2 px-4"><?= $user['id'] ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars(ucfirst($user['type'])) ?></td>
                    <td class="py-2 px-4">
                        <span class="px-2 py-1 rounded-full text-xs <?= $user['is_active'] ? 'bg-success text-white' : 'bg-warning text-gray-800' ?>">
                            <?= $user['is_active'] ? 'Active' : 'Pending' ?>
                        </span>
                    </td>
                    <td class="py-2 px-4 flex space-x-2">
                        <button class="bg-primary hover:bg-primary-hover text-white px-3 py-1 rounded">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="bg-danger hover:bg-danger-hover text-white px-3 py-1 rounded">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php if (!$user['is_active']): ?>
                        <button class="bg-success hover:bg-success-hover text-white px-3 py-1 rounded">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add/Edit User Modal -->
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden" id="userModal">
        <div class="bg-card rounded-lg shadow-xl w-full max-w-md p-6">
            <h3 class="text-xl font-bold mb-4">Add New User</h3>
            <form method="POST">
                <div class="mb-4">
                    <label class="block mb-2">User Type</label>
                    <select class="w-full p-2 border rounded bg-card" name="user_type">
                        <option value="admin">Admin</option>
                        <option value="faculty">Faculty</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">First Name</label>
                    <input type="text" name="first_name" class="w-full p-2 border rounded bg-card" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Last Name</label>
                    <input type="text" name="last_name" class="w-full p-2 border rounded bg-card" required>
                </div>
                <div class="mb-4">
                    <label class="block mb-2">Email</label>
                    <input type="email" name="email" class="w-full p-2 border rounded bg-card" required>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" class="bg-secondary hover:bg-secondary-hover text-white px-4 py-2 rounded" id="closeModal">
                        Cancel
                    </button>
                    <button type="submit" name="add_user" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded">
                        Save User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Modal handling
    document.querySelector('[aria-label="Add New User"]').addEventListener('click', () => {
        document.getElementById('userModal').classList.remove('hidden');
    });
    
    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('userModal').classList.add('hidden');
    });
</script>