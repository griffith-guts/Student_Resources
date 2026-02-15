<?php
session_start();
require_once 'config.php';
requireAdmin();

$conn = getConnection();
$message  = '';
$msg_type = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $id   = intval($_POST['delete_id']);
        $stmt = $conn->prepare("DELETE FROM textbooks WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message  = $stmt->affected_rows > 0 ? 'Textbook deleted.' : 'Not found.';
        $msg_type = $stmt->affected_rows > 0 ? 'success' : 'error';
        $stmt->close();
    } elseif (isset($_POST['approve_id'])) {
        $id   = intval($_POST['approve_id']);
        $stmt = $conn->prepare("UPDATE textbooks SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message  = 'Textbook approved.';
        $msg_type = 'success';
        $stmt->close();
    }
}

// Fetch all textbooks
$result   = $conn->query("SELECT t.*, u.name AS uploader, u.roll_number FROM textbooks t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC");
$books    = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Textbooks — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Manage Textbooks</div>
                <div class="topbar-subtitle"><?= count($books) ?> textbook(s) in total</div>
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
                <?php if (empty($books)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <p>No textbooks listed yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Subject</th>
                                    <th>Author</th>
                                    <th>Semester</th>
                                    <th>Posted By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($books as $i => $book): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                                    <td><span class="badge badge-amber"><?= htmlspecialchars($book['subject']) ?></span></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td>Sem <?= $book['semester'] ?></td>
                                    <td><?= htmlspecialchars($book['uploader']) ?><br>
                                        <small style="color:var(--text-muted)"><?= htmlspecialchars($book['roll_number']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $book['status'] === 'approved' ? 'badge-green' : 'badge-amber' ?>">
                                            <?= ucfirst($book['status']) ?>
                                        </span>
                                    </td>
                                    <td style="display:flex; gap:6px; flex-wrap:wrap">
                                        <?php if ($book['status'] !== 'approved'): ?>
                                        <form method="POST" style="display:inline">
                                            <input type="hidden" name="approve_id" value="<?= $book['id'] ?>">
                                            <button type="submit" class="btn btn-success">✅ Approve</button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" style="display:inline"
                                              onsubmit="return confirm('Delete this textbook?')">
                                            <input type="hidden" name="delete_id" value="<?= $book['id'] ?>">
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
