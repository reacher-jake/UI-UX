<?php
// components/tutor_header.php
if (!isset($_COOKIE['tutor_id'])) {
    header('location:login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<header class="header">
    <section class="flex">
        <a href="dashboard.php" class="logo">Tutor Dashboard</a>
        <form action="search_course.php" method="post" class="search-form">
            <input type="text" name="search_course" placeholder="Search courses..." required maxlength="100">
            <button type="submit" class="fas fa-search"></button>
        </form>
        <div class="icons">
            <div id="menu-btn" class="fas fa-bars"></div>
            <div id="search-btn" class="fas fa-search"></div>
            <div id="user-btn" class="fas fa-user"></div>
        </div>
        <div class="profile">
            <img src="uploaded_files/<?= htmlspecialchars($fetch_profile['image'] ?? 'default.png'); ?>" alt="">
            <h3><?= htmlspecialchars($fetch_profile['name'] ?? 'Tutor'); ?></h3>
            <p><?= htmlspecialchars($fetch_profile['profession'] ?? 'Tutor'); ?></p>
            <a href="profile.php" class="btn">View Profile</a>
            <a href="logout.php" class="delete-btn">Logout</a>
        </div>
    </section>
</header>

<div class="side-bar">
    <div class="profile">
        <img src="uploaded_files/<?= htmlspecialchars($fetch_profile['image'] ?? 'default.png'); ?>" alt="">
        <h3><?= htmlspecialchars($fetch_profile['name'] ?? 'Tutor'); ?></h3>
        <p><?= htmlspecialchars($fetch_profile['profession'] ?? 'Tutor'); ?></p>
        <a href="profile.php" class="btn">View Profile</a>
    </div>
    <nav class="navbar">
        <a href="dashboard.php"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="playlists.php"><i class="fas fa-list"></i><span>Playlists</span></a>
        <a href="contents.php"><i class="fas fa-video"></i><span>Contents</span></a>
        <a href="comments.php"><i class="fas fa-comment"></i><span>Comments</span></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a>
    </nav>
</div>