<?php
session_start();
require_once 'config.php';
requireAdmin();

$conn = getConnection();
$message  = '';
$msg_type = '';

// Handle add / delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_video'])) {
        $title = sanitize($_POST['title'] ?? '');
        $link  = sanitize($_POST['youtube_link'] ?? '');

        if (empty($title) || empty($link)) {
            $message  = 'Title and YouTube link are required.';
            $msg_type = 'error';
        } elseif (!preg_match('/(youtube\.com|youtu\.be)/', $link)) {
            $message  = 'Please enter a valid YouTube URL.';
            $msg_type = 'error';
        } else {
            $stmt = $conn->prepare("INSERT INTO videos (title, youtube_link) VALUES (?, ?)");
            $stmt->bind_param("ss", $title, $link);
            if ($stmt->execute()) {
                $message  = 'Video added successfully!';
                $msg_type = 'success';
            } else {
                $message  = 'Failed to add video.';
                $msg_type = 'error';
            }
            $stmt->close();
        }
    } elseif (isset($_POST['delete_video_id'])) {
        $id   = intval($_POST['delete_video_id']);
        $stmt = $conn->prepare("DELETE FROM videos WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $message  = $stmt->affected_rows > 0 ? 'Video deleted.' : 'Not found.';
        $msg_type = $stmt->affected_rows > 0 ? 'success' : 'error';
        $stmt->close();
    }
}

$result = $conn->query("SELECT * FROM videos ORDER BY created_at DESC");
$videos = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();

function getEmbedUrl($url) {
    if (preg_match('/youtu\.be\/([a-zA-Z0-9_\-]+)/', $url, $m))
        return 'https://www.youtube.com/embed/' . $m[1];
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $url, $m))
        return 'https://www.youtube.com/embed/' . $m[1];
    if (strpos($url, 'youtube.com/embed/') !== false)
        return $url;
    return $url;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Videos — Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar">
            <div>
                <div class="topbar-title">Video Resources</div>
                <div class="topbar-subtitle">Add and manage YouTube educational videos</div>
            </div>
            <span class="admin-badge">🔐 Administrator</span>
        </div>

        <div class="content-area">
            <?php if ($message): ?>
                <div class="alert alert-<?= $msg_type === 'success' ? 'success' : 'error' ?>">
                    <?= $msg_type === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Add Video Form -->
            <div class="card" style="max-width:600px; margin-bottom:2rem">
                <div class="card-title">🎬 Add YouTube Video</div>
                <div class="card-subtitle">Paste a YouTube link to embed a video for students</div>

                <form method="POST" action="admin_videos.php">
                    <div class="form-group">
                        <label>Video Title</label>
                        <input type="text" name="title" placeholder="e.g. Data Structures - Complete Tutorial"
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>YouTube Link</label>
                        <input type="url" name="youtube_link"
                               placeholder="e.g. https://www.youtube.com/watch?v=xxxxxx"
                               value="<?= htmlspecialchars($_POST['youtube_link'] ?? '') ?>" required>
                        <small style="color:var(--text-muted); font-size:0.8rem; margin-top:4px; display:block">
                            Accepts: youtube.com/watch?v= or youtu.be/ links
                        </small>
                    </div>
                    <button type="submit" name="add_video" class="btn btn-primary" style="width:100%; justify-content:center">
                        ➕ Add Video
                    </button>
                </form>
            </div>

            <!-- Video List -->
            <div class="card">
                <div class="card-title">📋 All Videos (<?= count($videos) ?>)</div>
                <?php if (empty($videos)): ?>
                    <div class="empty-state" style="padding:2rem">
                        <div class="empty-icon">🎬</div>
                        <p>No videos added yet. Add the first one above!</p>
                    </div>
                <?php else: ?>
                    <div class="video-grid" style="margin-top:1.5rem">
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
                            <div class="video-info" style="display:flex; align-items:center; justify-content:space-between; gap:1rem">
                                <div class="video-title"><?= htmlspecialchars($video['title']) ?></div>
                                <form method="POST" onsubmit="return confirm('Delete this video?')" style="flex-shrink:0">
                                    <input type="hidden" name="delete_video_id" value="<?= $video['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding:6px 12px; font-size:0.8rem">🗑</button>
                                </form>
                            </div>
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
