<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

include 'components/connect.php';

session_start();
$message = [];

if (!$conn) {
    $message[] = 'Database connection failed!';
    header('location:login.php');
    exit;
}

$user_id = $_COOKIE['user_id'] ?? '';
$tutor_id = $_COOKIE['tutor_id'] ?? '';

if (empty($user_id) && empty($tutor_id)) {
    $message[] = 'Please log in to view your profile.';
    header('location:login.php');
    exit;
}

$is_tutor = !empty($tutor_id) && empty($user_id); // Only tutor if tutor_id is set and user_id is not
$current_id = $is_tutor ? $tutor_id : $user_id;
$user_type = $is_tutor ? 'tutor' : 'user';
$table = $is_tutor ? 'tutors' : 'users';

try {
    $select_profile = $conn->prepare("SELECT * FROM `$table` WHERE id = ?");
    $select_profile->execute([$current_id]);
    $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

    if (!$fetch_profile) {
        $message[] = 'User not found!';
        header('location:login.php');
        exit;
    }

    if ($fetch_profile['approval_status'] !== 'approved') {
        $message[] = 'Your account is pending approval!';
    }
} catch (PDOException $e) {
    $message[] = 'Error fetching profile: ' . htmlspecialchars($e->getMessage());
    error_log('Profile fetch error: ' . $e->getMessage());
    header('location:login.php');
    exit;
}

$total_likes = 0;
$total_comments = 0;
$total_bookmarked = 0;
$total_playlists = 0;
$total_contents = 0;

try {
    $select_likes = $conn->prepare("SELECT COUNT(*) FROM `likes` WHERE user_id = ?");
    $select_likes->execute([$current_id]);
    $total_likes = $select_likes->fetchColumn() ?: 0;

    $select_comments = $conn->prepare("SELECT COUNT(*) FROM `comments` WHERE user_id = ?");
    $select_comments->execute([$current_id]);
    $total_comments = $select_comments->fetchColumn() ?: 0;

    $select_bookmark = $conn->prepare("SELECT COUNT(*) FROM `bookmark` WHERE user_id = ?");
    $select_bookmark->execute([$current_id]);
    $total_bookmarked = $select_bookmark->fetchColumn() ?: 0;

    if ($is_tutor) {
        $select_playlists = $conn->prepare("SELECT COUNT(*) FROM `playlist` WHERE tutor_id = ?");
        $select_playlists->execute([$tutor_id]);
        $total_playlists = $select_playlists->fetchColumn() ?: 0;

        $select_contents = $conn->prepare("SELECT COUNT(*) FROM `content` WHERE tutor_id = ?");
        $select_contents->execute([$tutor_id]);
        $total_contents = $select_contents->fetchColumn() ?: 0;
    }
} catch (PDOException $e) {
    $message[] = 'Error fetching metrics: ' . htmlspecialchars($e->getMessage());
    error_log('Metrics fetch error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php 
if ($is_tutor) {
    include 'components/tutor_header.php'; // Renamed to avoid "admin" confusion
} else {
    include 'components/user_header.php';
}
?>

<section class="profile">
    <h1 class="heading"><?= $is_tutor ? 'Tutor Profile & Reports' : 'Student Profile & Reports'; ?></h1>
    <?php if (!empty($message)): ?>
        <?php foreach ($message as $msg): ?>
            <div class="message"><span><?= htmlspecialchars($msg); ?></span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($fetch_profile['approval_status'] === 'approved'): ?>
    <div class="tabs">
        <button class="tab-btn active" data-tab="details">Profile Details</button>
        <button class="tab-btn" data-tab="reports">Reports</button>
    </div>

    <div class="tab-content details active">
        <div class="user">
            <img src="uploaded_files/<?= htmlspecialchars($fetch_profile['image'] ?? 'default.png'); ?>" alt="<?= htmlspecialchars($fetch_profile['name'] ?? 'User'); ?>">
            <h3><?= htmlspecialchars($fetch_profile['name'] ?? 'Unknown'); ?></h3>
            <p><?= $is_tutor ? htmlspecialchars($fetch_profile['profession'] ?? 'Tutor') : 'Student'; ?></p>
            <p>Status: <?= ucfirst(htmlspecialchars($fetch_profile['approval_status'] ?? 'Unknown')); ?></p>
            <a href="update.php" class="inline-btn">Update Profile</a>
        </div>
        <div class="box-container">
            <?php if ($is_tutor): ?>
            <div class="box">
                <div class="flex">
                    <i class="fas fa-list"></i>
                    <div>
                        <h3><?= $total_playlists; ?></h3>
                        <span>Total Playlists</span>
                    </div>
                </div>
                <a href="playlists.php" class="inline-btn">View Playlists</a>
            </div>
            <div class="box">
                <div class="flex">
                    <i class="fas fa-video"></i>
                    <div>
                        <h3><?= $total_contents; ?></h3>
                        <span>Total Videos</span>
                    </div>
                </div>
                <a href="contents.php" class="inline-btn">View Contents</a>
            </div>
            <?php else: ?>
            <div class="box">
                <div class="flex">
                    <i class="fas fa-bookmark"></i>
                    <div>
                        <h3><?= $total_bookmarked; ?></h3>
                        <span>Saved Playlists</span>
                    </div>
                </div>
                <a href="bookmarks.php" class="inline-btn">View Playlists</a>
            </div>
            <?php endif; ?>
            <div class="box">
                <div class="flex">
                    <i class="fas fa-heart"></i>
                    <div>
                        <h3><?= $total_likes; ?></h3>
                        <span>Liked Tutorials</span>
                    </div>
                </div>
                <a href="likes.php" class="inline-btn">View Liked</a>
            </div>
            <div class="box">
                <div class="flex">
                    <i class="fas fa-comment"></i>
                    <div>
                        <h3><?= $total_comments; ?></h3>
                        <span>Video Comments</span>
                    </div>
                </div>
                <a href="comments.php" class="inline-btn">View Comments</a>
            </div>
        </div>
    </div>

    <div class="tab-content reports">
        <h2><?= $is_tutor ? 'Your Teaching Report' : 'Your Learning Report'; ?></h2>
        <p class="empty">Detailed reports are currently unavailable.</p>
    </div>
    <?php endif; ?>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.tab-btn');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.querySelector(`.${tab.dataset.tab}`).classList.add('active');
        });
    });
});
</script>
</body>
</html>