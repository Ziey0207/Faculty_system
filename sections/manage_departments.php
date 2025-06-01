<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_department'])) {
        // Add department logic
    } elseif (isset($_POST['edit_department'])) {
        // Edit department logic
    } elseif (isset($_POST['delete_department'])) {
        // Delete department logic
    }
}

// Get all departments
$departments = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM departments");
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching departments: " . $e->getMessage();
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6">Department Management</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Department Stats -->
        <div class="stats-card bg-card p-6 rounded-lg shadow">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-secondary">Total Departments</p>
                    <p class="text-3xl font-bold"><?= count($departments) ?></p>
                </div>
                <i class="fas fa-building text-4xl opacity-30"></i>
            </div>
        </div>
        
        <!-- Faculty Stats -->
        <div class="stats-card bg-card p-6 rounded-lg shadow">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-secondary">Active Faculty</p>
                    <p class="text-3xl font-bold">42</p>
                </div>
                <i class="fas fa-chalkboard-teacher text-4xl opacity-30"></i>
            </div>
        </div>
        
        <!-- Courses Stats -->
        <div class="stats-card bg-card p-6 rounded-lg shadow">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-secondary">Available Courses</p>
                    <p class="text-3xl font-bold">28</p>
                </div>
                <i class="fas fa-book text-4xl opacity-30"></i>
            </div>
        </div>
    </div>
    
    <!-- Department Table -->
    <div class="mt-8 overflow-x-auto">
        <table class="min-w-full bg-card rounded-lg overflow-hidden">
            <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                    <th class="py-2 px-4">ID</th>
                    <th class="py-2 px-4">Department Name</th>
                    <th class="py-2 px-4">Head</th>
                    <th class="py-2 px-4">Faculty Count</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $dept): ?>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="py-2 px-4"><?= $dept['id'] ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($dept['name']) ?></td>
                    <td class="py-2 px-4">Dr. Jane Smith</td>
                    <td class="py-2 px-4">15</td>
                    <td class="py-2 px-4">
                        <button class="bg-primary hover:bg-primary-hover text-white px-3 py-1 rounded mr-2">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="bg-danger hover:bg-danger-hover text-white px-3 py-1 rounded">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>