<?php
session_start();
require_once 'config.php';
requireLogin();

$conn = getConnection();

// Filters
$filter_subject = sanitize($_GET['subject'] ?? '');
$filter_year    = intval($_GET['year'] ?? 0);

$sql = "SELECT qp.*, u.name AS uploader, u.roll_number 
        FROM question_papers qp 
        JOIN users u ON qp.user_id = u.id 
        WHERE 1=1";

$params = [];
$types  = '';

if (!empty($filter_subject)) {
    $sql .= " AND qp.subject LIKE ?";
    $params[] = '%' . $filter_subject . '%';
    $types   .= 's';
}
if ($filter_year > 0) {
    $sql .= " AND qp.year = ?";
    $params[] = $filter_year;
    $types   .= 'i';
}

$sql .= " ORDER BY qp.year DESC, qp.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$papers = $result->fetch_all(MYSQLI_ASSOC);

// Get distinct years
$yr_result = $conn->query("SELECT DISTINCT year FROM question_papers ORDER BY year DESC");
$years = [];
while ($row = $yr_result->fetch_assoc()) $years[] = $row['year'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Papers — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Question Papers</div>
                <div class="topbar-subtitle"><?= count($papers) ?> paper(s) available</div>
            </div>
            <a href="upload_paper.php" class="btn btn-primary btn-sm">+ Upload Paper</a>
        </div>

        <div class="content-area">
            <!-- Filters -->
            <form method="GET" action="view_papers.php">
                <div class="filter-bar">
                    <div class="form-group">
                        <label>Filter by Subject</label>
                        <input type="text" name="subject" placeholder="Search subject..."
                               value="<?= htmlspecialchars($filter_subject) ?>">
                    </div>
                    <div class="form-group">
                        <label>Filter by Year</label>
                        <select name="year">
                            <option value="">All Years</option>
                            <?php foreach ($years as $yr): ?>
                                <option value="<?= $yr ?>" <?= $filter_year === (int)$yr ? 'selected' : '' ?>>
                                    <?= $yr ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="display:flex; gap:0.5rem; align-items:flex-end">
                        <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
                        <a href="view_papers.php" class="btn btn-outline btn-sm">✕ Clear</a>
                    </div>
                </div>
            </form>

            <!-- Papers List -->
            <div class="card">
                <?php if (empty($papers)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📄</div>
                        <p>No question papers found. <a href="upload_paper.php" style="color:var(--amber)">Upload the first one!</a></p>
                    </div>
                <?php else: ?>
                    <div class="paper-list">
                        <?php foreach ($papers as $paper): ?>
                        <div class="paper-item">
                            <div class="paper-meta">
                                <div class="paper-subject">📄 <?= htmlspecialchars($paper['subject']) ?></div>
                                <div class="paper-year">
                                    Year: <strong><?= $paper['year'] ?></strong> · 
                                    Uploaded by: <?= htmlspecialchars($paper['uploader']) ?> ·
                                    <?= date('d M Y', strtotime($paper['created_at'])) ?>
                                </div>
                            </div>
                            <a href="<?= htmlspecialchars($paper['file_path']) ?>" 
                               class="download-btn" 
                               download 
                               target="_blank">
                                ⬇️ Download
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
