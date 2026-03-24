<?php
session_start();
require_once 'includes/conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'pending') {
    header("Location: index.php");
    exit;
}

// 🔽 Fetch active programs
$programs = $conn->query(
    "SELECT id, program_code, program_name 
     FROM programs 
     WHERE is_active = 1 
     ORDER BY sort_order ASC, program_name ASC"
);
?>

<form method="POST" action="save-role.php">
    <h2>Complete Your Profile</h2>

    <!-- ROLE -->
    <label>Role</label>
    <select name="role" required>
        <option value="">-- Select Role --</option>
        <option value="faculty">Faculty</option>
        <option value="admin">Admin</option>
    </select>

    <!-- PROGRAM -->
    <label>Program</label>
    <select name="program_id" required>
        <option value="">-- Select Program --</option>
        <?php while ($row = $programs->fetch_assoc()): ?>
            <option value="<?= $row['id']; ?>">
                <?= $row['program_code'] . ' - ' . $row['program_name']; ?>
            </option>
        <?php endwhile; ?>
    </select>

    <!-- BRANCH -->
    <label>Branch</label>
    <select name="branch" required>
        <option value="">-- Select Branch --</option>
        <option value="HOLY CROSS COLLEGE STA. ROSA, NUEVA ECIJA, INC.">
            HOLY CROSS COLLEGE STA. ROSA, NUEVA ECIJA, INC.
        </option>
        <option value="CONCEPCION HOLY CROSS COLLEGE, INC.">
            CONCEPCION HOLY CROSS COLLEGE, INC.
        </option>
    </select>

    <button type="submit">Continue</button>
</form>