<?php
session_start();
require_once 'config.php';
requireLogin();

$conn = getConnection();

// Get filter values
$filter_subject  = sanitize($_GET['subject'] ?? '');
$filter_semester = intval($_GET['semester'] ?? 0);

// Build query with filters
$sql = "SELECT t.*, u.name AS uploader, u.roll_number 
        FROM textbooks t 
        JOIN users u ON t.user_id = u.id 
        WHERE t.status = 'approved'";

$params = [];
$types  = '';

if (!empty($filter_subject)) {
    $sql .= " AND t.subject LIKE ?";
    $params[] = '%' . $filter_subject . '%';
    $types   .= 's';
}
if ($filter_semester > 0) {
    $sql .= " AND t.semester = ?";
    $params[] = $filter_semester;
    $types   .= 'i';
}

$sql .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$textbooks = $result->fetch_all(MYSQLI_ASSOC);

// Get distinct subjects for dropdown
$sub_result = $conn->query("SELECT DISTINCT subject FROM textbooks WHERE status='approved' ORDER BY subject");
$subjects = [];
while ($row = $sub_result->fetch_assoc()) $subjects[] = $row['subject'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Textbooks — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Textbooks</div>
                <div class="topbar-subtitle"><?= count($textbooks) ?> book(s) found</div>
            </div>
            <a href="add_textbook.php" class="btn btn-primary btn-sm">+ Add Textbook</a>
        </div>

        <div class="content-area">
            <!-- Filter Bar -->
            <form method="GET" action="view_textbooks.php">
                <div class="filter-bar">
                    <div class="form-group">
                        <label>Filter by Subject</label>
                        <input type="text" name="subject" placeholder="Type subject name..."
                               value="<?= htmlspecialchars($filter_subject) ?>">
                    </div>
                    <div class="form-group">
                        <label>Filter by Semester</label>
                        <select name="semester">
                            <option value="">All Semesters</option>
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <option value="<?= $i ?>" <?= $filter_semester === $i ? 'selected' : '' ?>>
                                    Semester <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div style="display:flex; gap:0.5rem; align-items:flex-end">
                        <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                        <a href="view_textbooks.php" class="btn btn-outline btn-sm">✕ Clear</a>
                    </div>
                </div>
            </form>

            <!-- Textbooks Table -->
            <div class="card">
                <?php if (empty($textbooks)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📚</div>
                        <p>No textbooks found. <a href="add_textbook.php" style="color:var(--amber)">Add the first one!</a></p>
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
                                    <th>Contact</th>
                                    <th>Listed By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($textbooks as $i => $book): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($book['title']) ?></strong></td>
                                    <td><span class="badge badge-amber"><?= htmlspecialchars($book['subject']) ?></span></td>
                                    <td><?= htmlspecialchars($book['author']) ?></td>
                                    <td><span class="badge badge-blue">Sem <?= $book['semester'] ?></span></td>
                                    <td>
                                        <a href="tel:<?= htmlspecialchars($book['contact']) ?>" style="color:var(--amber); text-decoration:none">
                                            📞 <?= htmlspecialchars($book['contact']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($book['uploader']) ?></td>
                                    <td style="color:var(--text-muted); font-size:0.82rem">
                                        <?= date('d M Y', strtotime($book['created_at'])) ?>
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
