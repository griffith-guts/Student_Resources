<?php
session_start();
require_once 'config.php';
requireLogin();

$conn = getConnection();
$videos_result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
$videos = $videos_result->fetch_all(MYSQLI_ASSOC);
$conn->close();

// Convert YouTube watch URLs to embed URLs
function getEmbedUrl($url) {
    $url = trim($url);
    // Handle youtu.be short URLs
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_\-]+)/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    // Handle standard youtube.com/watch?v= URLs
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    // Already an embed URL
    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }
    return $url;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Resources — Student Resource System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Video Resources</div>
                <div class="topbar-subtitle"><?= count($videos) ?> video(s) available</div>
            </div>
        </div>

        <div class="content-area">
            <?php if (empty($videos)): ?>
                <div class="card">
                    <div class="empty-state">
                        <div class="empty-icon">🎬</div>
                        <p>No videos added yet. Ask your admin to add educational video resources.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="video-grid">
                    <?php foreach ($videos as $video):
                        $embed_url = getEmbedUrl($video['youtube_link']);
                    ?>
                    <div class="video-card">
                        <div class="video-embed">
                            <iframe 
                                src="<?= htmlspecialchars($embed_url) ?>" 
                                title="<?= htmlspecialchars($video['title']) ?>"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <div class="video-info">
                            <div class="video-title"><?= htmlspecialchars($video['title']) ?></div>
                            <div style="font-size:0.78rem; color:var(--text-muted); margin-top:4px">
                                Added <?= date('d M Y', strtotime($video['created_at'])) ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
