
<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_user'])) {
        // Approval logic
    } elseif (isset($_POST['delete_user'])) {
        // Deletion logic
    }
}

// Get all pending users
$pendingUsers = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE is_approved = 0");
    $stmt->execute();
    $pendingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching pending users: " . $e->getMessage();
}

// Get all approved users
$approvedUsers = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE is_approved = 1");
    $stmt->execute();
    $approvedUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching approved users: " . $e->getMessage();
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6">User Management</h2>
    
    <!-- Pending Approvals -->
    <div class="mb-8">
        <h3 class="text-xl font-semibold mb-4">Pending Approvals</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-card rounded-lg overflow-hidden">
                <thead class="bg-gray-200 dark:bg-gray-700">
                    <tr>
                        <th class="py-2 px-4">Name</th>
                        <th class="py-2 px-4">Email</th>
                        <th class="py-2 px-4">Type</th>
                        <th class="py-2 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingUsers as $user): ?>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-2 px-4"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars(ucfirst($user['type'])) ?></td>
                        <td class="py-2 px-4">
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="approve_user" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mr-2">Approve</button>
                                <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">Reject</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- User List -->
    <div>
        <h3 class="text-xl font-semibold mb-4">All Users</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-card rounded-lg overflow-hidden">
                <thead class="bg-gray-200 dark:bg-gray-700">
                    <tr>
                        <th class="py-2 px-4">Name</th>
                        <th class="py-2 px-4">Email</th>
                        <th class="py-2 px-4">Type</th>
                        <th class="py-2 px-4">Status</th>
                        <th class="py-2 px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($approvedUsers as $user): ?>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <td class="py-2 px-4"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars(ucfirst($user['type'])) ?></td>
                        <td class="py-2 px-4"><?= $user['is_active'] ? 'Active' : 'Inactive' ?></td>
                        <td class="py-2 px-4">
                            <a href="?section=edit_user&id=<?= $user['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded mr-2">Edit</a>
                            <form method="POST" class="inline-block">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <button type="submit" name="toggle_status" class="<?= $user['is_active'] ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' ?> text-white px-3 py-1 rounded">
                                    <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>