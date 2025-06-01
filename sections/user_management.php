<?php
// Authorization check - only admins can access this section
if (!in_array($_SESSION['user_type'], ['super_admin', 'admin'])) {
    header('Location: dashboard.php?section=dashboard');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_user'])) {
        $user_id = (int)$_POST['user_id'];
        $user_type = $_POST['user_type'];
        $approver_id = $_SESSION['user_id'];
        
        try {
            $stmt = $pdo->prepare("UPDATE $user_type SET is_approved = 1, approved_by = ? WHERE id = ?");
            $stmt->execute([$approver_id, $user_id]);
            
            // Redirect to prevent form resubmission
        echo '<script>window.location.href = "dashboard.php?section=user_management&action=approved";</script>';
        exit();
        } catch (PDOException $e) {
            $error = "Approval failed: " . $e->getMessage();
        }
    } elseif (isset($_POST['toggle_status'])) {
        $user_id = (int)$_POST['user_id'];
        $user_type = $_POST['user_type'];
        $current_status = (int)$_POST['current_status'];
        $new_status = $current_status ? 0 : 1;
        
        try {
            $stmt = $pdo->prepare("UPDATE $user_type SET is_active = ? WHERE id = ?");
            $stmt->execute([$new_status, $user_id]);
            
            // Redirect to prevent form resubmission
echo '<script>window.location.href = "dashboard.php?section=user_management&action=status_changed";</script>';
exit();
        } catch (PDOException $e) {
            $error = "Status update failed: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        $user_id = (int)$_POST['user_id'];
        $user_type = $_POST['user_type'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM $user_type WHERE id = ?");
            $stmt->execute([$user_id]);
            
            // Redirect to prevent form resubmission
echo '<script>window.location.href = "dashboard.php?section=user_management&action=deleted";</script>';
exit();
        } catch (PDOException $e) {
            $error = "Deletion failed: " . $e->getMessage();
        }
    }
}

// Handle success messages
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'approved':
            $success = "User approved successfully!";
            break;
        case 'status_changed':
            $success = "User status updated!";
            break;
        case 'deleted':
            $success = "User deleted successfully!";
            break;
    }
}

// Determine which user types current admin can manage
$canManageAdmins = ($_SESSION['user_type'] === 'super_admin');
$tables = [];

if ($canManageAdmins) {
    // Super admin can manage all users
    $tables = [
        ['table' => 'admins', 'condition' => "role != 'super_admin'"],
        ['table' => 'faculty', 'condition' => "1"],
        ['table' => 'users', 'condition' => "1"]
    ];
} else {
    // Regular admin can only manage faculty and users
    $tables = [
        ['table' => 'faculty', 'condition' => "1"],
        ['table' => 'users', 'condition' => "1"]
    ];
}

// Get all pending users across allowed tables
$pendingUsers = [];
try {
    foreach ($tables as $tableInfo) {
        $table = $tableInfo['table'];
        $condition = $tableInfo['condition'];
        
        $sql = "SELECT *, '$table' as user_type, created_at 
                FROM $table 
                WHERE is_approved = 0 AND $condition";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $pendingUsers = array_merge($pendingUsers, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (PDOException $e) {
    $error = "Error fetching pending users: " . $e->getMessage();
}

// Get all approved users across allowed tables
$approvedUsers = [];
try {
    foreach ($tables as $tableInfo) {
        $table = $tableInfo['table'];
        $condition = $tableInfo['condition'];
        
        $sql = "SELECT *, '$table' as user_type, created_at, updated_at, approved_by 
                FROM $table 
                WHERE is_approved = 1 AND $condition";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $approvedUsers = array_merge($approvedUsers, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (PDOException $e) {
    $error = "Error fetching approved users: " . $e->getMessage();
}

// Preload approver names
$approverNames = [];
if (!empty($approvedUsers)) {
    $approverIds = array_filter(array_column($approvedUsers, 'approved_by'));
    if (!empty($approverIds)) {
        $stmt = $pdo->prepare("SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM admins WHERE id IN (" . implode(',', $approverIds) . ")");
        $stmt->execute();
        $approvers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($approvers as $approver) {
            $approverNames[$approver['id']] = $approver['name'];
        }
    }
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6">User Management</h2>
    
    <?php if (isset($success)): ?>
        <div class="bg-green-50 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p><?= $success ?></p>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p><?= $error ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Pending Approvals -->
    <div class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Pending Approvals</h3>
            <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm">
                <?= count($pendingUsers) ?> pending
            </span>
        </div>
        
        <?php if (empty($pendingUsers)): ?>
            <div class="text-center py-8 bg-card rounded-lg">
                <i class="fas fa-check-circle text-4xl text-green-500 mb-4"></i>
                <p>No pending approvals</p>
                <p class="text-sm text-secondary mt-2">All user registrations are approved</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-card rounded-lg overflow-hidden">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th class="py-2 px-4 w-1/4">Name</th>
                            <th class="py-2 px-4 w-1/4">Email</th>
                            <th class="py-2 px-4 w-1/6">Type</th>
                            <th class="py-2 px-4 w-1/6">Registered</th>
                            <th class="py-2 px-4 w-1/4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingUsers as $user): ?>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2 px-4"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="py-2 px-4">
                                <?php 
                                $type = $user['user_type'];
                                if ($type === 'admins') echo 'Admin';
                                elseif ($type === 'faculty') echo 'Faculty';
                                else echo ucfirst($user['type']); 
                                ?>
                            </td>
                            <td class="py-2 px-4"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td class="py-2 px-4">
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="user_type" value="<?= $user['user_type'] ?>">
                                    <button type="submit" name="approve_user" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded mr-2">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                    <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- User List -->
    <div>
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">All Users</h3>
            <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm">
                <?= count($approvedUsers) ?> users
            </span>
        </div>
        
        <?php if (empty($approvedUsers)): ?>
            <div class="text-center py-8 bg-card rounded-lg">
                <i class="fas fa-users text-4xl text-blue-500 mb-4"></i>
                <p>No users found</p>
                <p class="text-sm text-secondary mt-2">Approved users will appear here</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-card rounded-lg overflow-hidden">
                    <thead class="bg-gray-200 dark:bg-gray-700">
                        <tr>
                            <th class="py-2 px-4 w-1/5">Name</th>
                            <th class="py-2 px-4 w-1/5">Email</th>
                            <th class="py-2 px-4 w-1/8">Type</th>
                            <th class="py-2 px-4 w-1/8">Status</th>
                            <th class="py-2 px-4 w-1/6">Approved By</th>
                            <th class="py-2 px-4 w-1/6">Created</th>
                            <th class="py-2 px-4 w-1/6">Updated</th>
                            <th class="py-2 px-4 w-1/5">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvedUsers as $user): 
                            $isAdmin = ($user['user_type'] === 'admins');
                            $canModify = ($canManageAdmins || !$isAdmin);
                        ?>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <td class="py-2 px-4"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                            <td class="py-2 px-4"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="py-2 px-4">
                                <span class="px-2 py-1">
                                    <?php 
                                    if ($isAdmin) echo 'Admin';
                                    elseif ($user['user_type'] === 'faculty') echo 'Faculty';
                                    else echo ucfirst($user['type']); 
                                    ?>
                                </span>
                            </td>
                            <td class="py-2 px-4">
                                <span class="px-2 py-1">
                                    <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td class="py-2 px-4">
                                <?php 
                                if ($user['approved_by'] && isset($approverNames[$user['approved_by']])) {
                                    echo htmlspecialchars($approverNames[$user['approved_by']]);
                                } elseif ($user['is_approved']) {
                                    echo "System";
                                } else {
                                    echo "-";
                                }
                                ?>
                            </td>
                            <td class="py-2 px-4"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td class="py-2 px-4"><?= date('M d, Y', strtotime($user['updated_at'])) ?></td>
                            <td class="py-2 px-4">
                                <?php if ($canModify): ?>
                                <form method="POST" class="inline-block">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="user_type" value="<?= $user['user_type'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $user['is_active'] ?>">
                                    <button type="submit" name="toggle_status" class="<?= $user['is_active'] ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600' ?> text-white px-3 py-1 rounded mr-2">
                                        <?= $user['is_active'] ? 'Deactivate' : 'Activate' ?>
                                    </button>
                                    <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-gray-500 text-sm">No actions</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>