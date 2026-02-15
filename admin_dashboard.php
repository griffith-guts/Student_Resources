<?php
session_start();
require_once 'config.php';
requireAdmin();

$conn = getConnection();

// Fetch stats
$stats = [];
$r = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE role='student'"); $stats['students'] = $r->fetch_assoc()['cnt'];
$r = $conn->query("SELECT COUNT(*) as cnt FROM textbooks"); $stats['textbooks'] = $r->fetch_assoc()['cnt'];
$r = $conn->query("SELECT COUNT(*) as cnt FROM question_papers"); $stats['papers'] = $r->fetch_assoc()['cnt'];
$r = $conn->query("SELECT COUNT(*) as cnt FROM videos"); $stats['videos'] = $r->fetch_assoc()['cnt'];

// Recent registrations
$recent = $conn->query("SELECT name, roll_number, department, created_at FROM users WHERE role='student' ORDER BY created_at DESC LIMIT 5");
$recent_users = $recent->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Admin Dashboard</div>
                <div class="topbar-subtitle">System overview and management</div>
            </div>
            <span class="admin-badge">🔐 Administrator</span>
        </div>

        <div class="content-area">
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon amber">👥</div>
                    <div>
                        <div class="stat-value"><?= $stats['students'] ?></div>
                        <div class="stat-label-card">Registered Students</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon green">📚</div>
                    <div>
                        <div class="stat-value"><?= $stats['textbooks'] ?></div>
                        <div class="stat-label-card">Total Textbooks</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue">📄</div>
                    <div>
                        <div class="stat-value"><?= $stats['papers'] ?></div>
                        <div class="stat-label-card">Question Papers</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon purple">🎬</div>
                    <div>
                        <div class="stat-value"><?= $stats['videos'] ?></div>
                        <div class="stat-label-card">Video Resources</div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card" style="margin-bottom:2rem">
                <div class="card-title">⚡ Admin Actions</div>
                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1rem; margin-top:1rem">
                    <a href="admin_users.php" class="btn btn-outline" style="justify-content:center">👥 Manage Users</a>
                    <a href="admin_textbooks.php" class="btn btn-outline" style="justify-content:center">📚 Manage Textbooks</a>
                    <a href="admin_videos.php" class="btn btn-primary" style="justify-content:center">🎬 Add Videos</a>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="card">
                <div class="section-header">
                    <div>
                        <div class="card-title">👥 Recent Registrations</div>
                        <div class="card-subtitle">Last 5 students who joined</div>
                    </div>
                    <a href="admin_users.php" class="btn btn-outline btn-sm">View All</a>
                </div>
                <?php if (empty($recent_users)): ?>
                    <div class="empty-state" style="padding:2rem">
                        <p>No students registered yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Roll Number</th>
                                    <th>Department</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_users as $u): ?>
                                <tr>
                                    <td><?= htmlspecialchars($u['name']) ?></td>
                                    <td><span class="badge badge-amber"><?= htmlspecialchars($u['roll_number']) ?></span></td>
                                    <td><?= htmlspecialchars($u['department']) ?></td>
                                    <td style="color:var(--text-muted); font-size:0.82rem"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
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
