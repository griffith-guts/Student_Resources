<?php
session_start();
require_once 'config.php';
requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// Get stats
$stats = [];

$r = $conn->query("SELECT COUNT(*) as cnt FROM textbooks WHERE user_id = $user_id");
$stats['my_books'] = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) as cnt FROM textbooks WHERE status = 'approved'");
$stats['all_books'] = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) as cnt FROM question_papers WHERE user_id = $user_id");
$stats['my_papers'] = $r->fetch_assoc()['cnt'];

$r = $conn->query("SELECT COUNT(*) as cnt FROM question_papers");
$stats['all_papers'] = $r->fetch_assoc()['cnt'];

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Dashboard</div>
                <div class="topbar-subtitle">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>!</div>
            </div>
            <div class="admin-badge" style="background:rgba(247,183,49,0.12);border-color:rgba(247,183,49,0.25);color:var(--amber)">
                <?= htmlspecialchars($_SESSION['department']) ?> · Sem <?= htmlspecialchars($_SESSION['semester']) ?>
            </div>
        </div>

        <div class="content-area">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon amber">📚</div>
                    <div>
                        <div class="stat-value"><?= $stats['all_books'] ?></div>
                        <div class="stat-label-card">Total Textbooks</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">📖</div>
                    <div>
                        <div class="stat-value"><?= $stats['my_books'] ?></div>
                        <div class="stat-label-card">My Listings</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">📄</div>
                    <div>
                        <div class="stat-value"><?= $stats['all_papers'] ?></div>
                        <div class="stat-label-card">Question Papers</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">📤</div>
                    <div>
                        <div class="stat-value"><?= $stats['my_papers'] ?></div>
                        <div class="stat-label-card">My Uploads</div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card" style="margin-bottom:2rem">
                <div class="card-title">Quick Actions</div>
                <div class="card-subtitle">Jump straight into the tools you need</div>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-top:1rem">
                    <a href="add_textbook.php" class="btn btn-primary">📚 Add Textbook</a>
                    <a href="view_textbooks.php" class="btn btn-outline">🔍 Browse Textbooks</a>
                    <a href="upload_paper.php" class="btn btn-outline">📤 Upload Question Paper</a>
                    <a href="view_papers.php" class="btn btn-outline">📄 View Papers</a>
                    <a href="planner.php" class="btn btn-outline">🗓️ Study Planner</a>
                    <a href="videos.php" class="btn btn-outline">🎬 Video Resources</a>
                </div>
            </div>

            <!-- Roll Number Info -->
            <div class="card">
                <div class="card-title">Your Profile</div>
                <table style="width:100%; border-collapse:collapse">
                    <tr>
                        <td style="padding:10px 0; color:var(--text-muted); width:160px; font-size:0.9rem">Full Name</td>
                        <td style="padding:10px 0; color:var(--text-light); font-size:0.9rem"><?= htmlspecialchars($_SESSION['user_name']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:var(--text-muted); font-size:0.9rem; border-top:1px solid rgba(255,255,255,0.05)">Roll Number</td>
                        <td style="padding:10px 0; color:var(--amber); font-size:0.9rem; border-top:1px solid rgba(255,255,255,0.05); font-family:'Syne',sans-serif; font-weight:700"><?= htmlspecialchars($_SESSION['roll_number']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:var(--text-muted); font-size:0.9rem; border-top:1px solid rgba(255,255,255,0.05)">Department</td>
                        <td style="padding:10px 0; color:var(--text-light); font-size:0.9rem; border-top:1px solid rgba(255,255,255,0.05)"><?= htmlspecialchars($_SESSION['department']) ?></td>
                    </tr>
                    <tr>
                        <td style="padding:10px 0; color:var(--text-muted); font-size:0.9rem; border-top:1px solid rgba(255,255,255,0.05)">Semester</td>
                        <td style="padding:10px 0; color:var(--text-light); font-size:0.9rem; border-top:1px solid rgba(255,255,255,0.05)">Semester <?= htmlspecialchars($_SESSION['semester']) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
