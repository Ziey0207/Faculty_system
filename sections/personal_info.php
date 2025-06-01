<?php
// Get faculty info
$faculty = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM faculty WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $faculty = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching faculty information: " . $e->getMessage();
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6">Personal Information</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="md:col-span-1">
            <div class="stats-card bg-card p-6 rounded-lg shadow text-center">
                <div class="bg-gray-200 border-2 border-dashed rounded-full w-32 h-32 mx-auto mb-4"></div>
                <h3 class="text-xl font-bold"><?= htmlspecialchars($faculty['first_name'] . ' ' . $faculty['last_name']) ?></h3>
                <p class="text-secondary">Associate Professor</p>
                <p class="mt-2"><i class="fas fa-envelope mr-2"></i> <?= htmlspecialchars($faculty['email']) ?></p>
                <p class="mt-2"><i class="fas fa-phone mr-2"></i> <?= htmlspecialchars($faculty['contact_number']) ?></p>
                <button class="mt-4 w-full bg-primary hover:bg-primary-hover text-white py-2 rounded">
                    <i class="fas fa-upload mr-2"></i> Upload Photo
                </button>
            </div>
        </div>
        
        <!-- Information Form -->
        <div class="md:col-span-2">
            <div class="stats-card bg-card p-6 rounded-lg shadow">
                <h3 class="text-lg font-semibold mb-4">Edit Information</h3>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2">First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($faculty['first_name']) ?>" class="w-full p-2 border rounded bg-card" required>
                        </div>
                        <div>
                            <label class="block mb-2">Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($faculty['last_name']) ?>" class="w-full p-2 border rounded bg-card" required>
                        </div>
                        <div>
                            <label class="block mb-2">Email</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($faculty['email']) ?>" class="w-full p-2 border rounded bg-card" required>
                        </div>
                        <div>
                            <label class="block mb-2">Contact Number</label>
                            <input type="tel" name="contact_number" value="<?= htmlspecialchars($faculty['contact_number']) ?>" class="w-full p-2 border rounded bg-card">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block mb-2">Address</label>
                            <textarea name="address" class="w-full p-2 border rounded bg-card" rows="3"><?= htmlspecialchars($faculty['address']) ?></textarea>
                        </div>
                        <div class="text-sm text-gray-500 mt-2">
                            Last updated: <?= date('M d, Y h:i A', strtotime($user['updated_at'])) ?>
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" name="update_info" class="bg-primary hover:bg-primary-hover text-white px-4 py-2 rounded">
                            Update Information
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>