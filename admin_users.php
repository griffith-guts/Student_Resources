<?php
session_start();
require_once 'config.php';
requireAdmin();

$conn = getConnection();
$message = '';
$msg_type = '';

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_id = intval($_POST['delete_user_id']);
    // Never allow deleting admin
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->bind_param("i", $delete_id);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if ($res && $res['role'] === 'admin') {
        $message  = 'Cannot delete admin account.';
        $msg_type = 'error';
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $message  = 'User deleted successfully.';
            $msg_type = 'success';
        } else {
            $message  = 'Failed to delete user.';
            $msg_type = 'error';
        }
        $stmt->close();
    }
}

// Fetch all students
$result  = $conn->query("SELECT id, name, roll_number, department, semester, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC");
$users   = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Manage Users</div>
                <div class="topbar-subtitle"><?= count($users) ?> student(s) registered</div>
            </div>
            <span class="admin-badge">🔐 Administrator</span>
        </div>

        <div class="content-area">
            <?php if ($message): ?>
                <div class="alert alert-<?= $msg_type === 'success' ? 'success' : 'error' ?>">
                    <?= $msg_type === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <?php if (empty($users)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <p>No students registered yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Full Name</th>
                                    <th>Roll Number</th>
                                    <th>Department</th>
                                    <th>Semester</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $i => $user): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($user['name']) ?></strong></td>
                                    <td><span class="badge badge-amber"><?= htmlspecialchars($user['roll_number']) ?></span></td>
                                    <td><?= htmlspecialchars($user['department']) ?></td>
                                    <td><span class="badge badge-blue">Sem <?= $user['semester'] ?></span></td>
                                    <td style="color:var(--text-muted); font-size:0.82rem"><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline"
                                              onsubmit="return confirm('Delete user <?= htmlspecialchars(addslashes($user['name'])) ?>? This will also remove all their textbooks and papers.')">
                                            <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-danger">🗑 Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
