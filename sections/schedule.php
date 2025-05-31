
<?php
// Get faculty schedule
$schedule = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM schedules WHERE faculty_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching schedule: " . $e->getMessage();
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6">My Schedule</h2>
    
    <div class="mb-6">
        <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
            <i class="fas fa-plus mr-2"></i> Request Schedule Change
        </button>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full bg-card rounded-lg overflow-hidden">
            <thead class="bg-gray-200 dark:bg-gray-700">
                <tr>
                    <th class="py-2 px-4">Course</th>
                    <th class="py-2 px-4">Section</th>
                    <th class="py-2 px-4">Date</th>
                    <th class="py-2 px-4">Time</th>
                    <th class="py-2 px-4">Room</th>
                    <th class="py-2 px-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedule as $item): ?>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <td class="py-2 px-4"><?= htmlspecialchars($item['subject']) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($item['section']) ?></td>
                    <td class="py-2 px-4"><?= date('M d, Y', strtotime($item['date'])) ?></td>
                    <td class="py-2 px-4"><?= date('h:i A', strtotime($item['time_in'])) . ' - ' . date('h:i A', strtotime($item['time_out'])) ?></td>
                    <td class="py-2 px-4"><?= htmlspecialchars($item['room']) ?></td>
                    <td class="py-2 px-4">
                        <button class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
// Get student schedule
$schedule = [];
try {
    $stmt = $pdo->prepare("SELECT s.* FROM schedules s 
                          JOIN schedule_sections ss ON s.id = ss.schedule_id
                          JOIN users u ON u.section_id = ss.section_id
                          WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching schedule: " . $e->getMessage();
}
?>

<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-6">My Class Schedule</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($schedule as $item): ?>
        <div class="bg-card p-4 rounded-lg shadow">
            <h3 class="text-xl font-semibold mb-2"><?= htmlspecialchars($item['subject']) ?></h3>
            <p class="mb-1"><i class="fas fa-calendar-alt mr-2"></i> <?= date('M d, Y', strtotime($item['date'])) ?></p>
            <p class="mb-1"><i class="fas fa-clock mr-2"></i> <?= date('h:i A', strtotime($item['time_in'])) . ' - ' . date('h:i A', strtotime($item['time_out'])) ?></p>
            <p class="mb-1"><i class="fas fa-door-open mr-2"></i> <?= htmlspecialchars($item['room']) ?></p>
            <p class="mb-1"><i class="fas fa-user-tie mr-2"></i> <?= htmlspecialchars($item['teacher']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</div>