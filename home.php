<?php
include 'components/connect.php';

session_start();
$user_id = $_COOKIE['user_id'] ?? '';

if (!$conn) {
   die('Database connection failed!');
}

try {
   $select_likes = $conn->prepare("SELECT COUNT(*) FROM `likes` WHERE user_id = ?");
   $select_likes->execute([$user_id]);
   $total_likes = $select_likes->fetchColumn() ?: 0;

   $select_comments = $conn->prepare("SELECT COUNT(*) FROM `comments` WHERE user_id = ?");
   $select_comments->execute([$user_id]);
   $total_comments = $select_comments->fetchColumn() ?: 0;

   $select_bookmark = $conn->prepare("SELECT COUNT(*) FROM `bookmark` WHERE user_id = ?");
   $select_bookmark->execute([$user_id]);
   $total_bookmarked = $select_bookmark->fetchColumn() ?: 0;
} catch (PDOException $e) {
   $message[] = 'Error fetching metrics: ' . htmlspecialchars($e->getMessage());
   error_log('Home metrics error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Home</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="quick-select">
   <h1 class="heading">Quick Options</h1>
   <div class="box-container">
      <?php if ($user_id): ?>
      <div class="box">
         <h3 class="title">Likes and Comments</h3>
         <p>Total Likes: <span><?= $total_likes; ?></span></p>
         <a href="likes.php" class="inline-btn">View Likes</a>
         <p>Total Comments: <span><?= $total_comments; ?></span></p>
         <a href="comments.php" class="inline-btn">View Comments</a>
         <p>Saved Playlists: <span><?= $total_bookmarked; ?></span></p>
         <a href="bookmark.php" class="inline-btn">View Bookmarks</a>
      </div>
      <?php else: ?>
      <div class="box" style="text-align: center;">
         <h3 class="title">Please Login or Register</h3>
         <div class="flex-btn" style="padding-top: .5rem;">
            <a href="login.php" class="option-btn">Login</a>
            <a href="register.php" class="option-btn">Register</a>
         </div>
      </div>
      <?php endif; ?>
      <div class="box">
         <h3 class="title">Top Categories</h3>
         <div class="flex">
            <a href="search_course.php?category=development"><i class="fas fa-code"></i><span>Development</span></a>
            <a href="search_course.php?category=business"><i class="fas fa-chart-simple"></i><span>Business</span></a>
            <a href="search_course.php?category=design"><i class="fas fa-pen"></i><span>Design</span></a>
            <a href="search_course.php?category=marketing"><i class="fas fa-chart-line"></i><span>Marketing</span></a>
            <a href="search_course.php?category=music"><i class="fas fa-music"></i><span>Music</span></a>
            <a href="search_course.php?category=photography"><i class="fas fa-camera"></i><span>Photography</span></a>
            <a href="search_course.php?category=software"><i class="fas fa-cog"></i><span>Software</span></a>
            <a href="search_course.php?category=science"><i class="fas fa-vial"></i><span>Science</span></a>
         </div>
      </div>
      <div class="box">
         <h3 class="title">Popular Topics</h3>
         <div class="flex">
            <a href="search_course.php?topic=html"><i class="fab fa-html5"></i><span>HTML</span></a>
            <a href="search_course.php?topic=css"><i class="fab fa-css3"></i><span>CSS</span></a>
            <a href="search_course.php?topic=javascript"><i class="fab fa-js"></i><span>JavaScript</span></a>
            <a href="search_course.php?topic=react"><i class="fab fa-react"></i><span>React</span></a>
            <a href="search_course.php?topic=php"><i class="fab fa-php"></i><span>PHP</span></a>
            <a href="search_course.php?topic=bootstrap"><i class="fab fa-bootstrap"></i><span>Bootstrap</span></a>
         </div>
      </div>
      <div class="box tutor">
         <h3 class="title">Become a Tutor</h3>
         <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Ipsa, laudantium.</p>
         <a href="admin/register.php" class="inline-btn">Get Started</a>
      </div>
   </div>
</section>

<section class="courses">
   <h1 class="heading">Latest Courses</h1>
   <div class="box-container">
      <?php
      try {
         $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE status = ? ORDER BY date DESC LIMIT 6");
         $select_courses->execute(['active']);
         if ($select_courses->rowCount() > 0) {
            while ($fetch_course = $select_courses->fetch(PDO::FETCH_ASSOC)) {
               $course_id = $fetch_course['id'];
               $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE id = ?");
               $select_tutor->execute([$fetch_course['tutor_id']]);
               $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
               if ($fetch_tutor) {
      ?>
      <div class="box">
         <div class="tutor">
            <img src="uploaded_files/<?= htmlspecialchars($fetch_tutor['image']); ?>" alt="<?= htmlspecialchars($fetch_tutor['name']); ?>">
            <div>
               <h3><?= htmlspecialchars($fetch_tutor['name']); ?></h3>
               <span><?= htmlspecialchars($fetch_course['date']); ?></span>
            </div>
         </div>
         <img src="uploaded_files/<?= htmlspecialchars($fetch_course['thumb']); ?>" class="thumb" alt="<?= htmlspecialchars($fetch_course['title']); ?>">
         <h3 class="title"><?= htmlspecialchars($fetch_course['title']); ?></h3>
         <a href="playlist.php?get_id=<?= urlencode($course_id); ?>" class="inline-btn">View Playlist</a>
      </div>
      <?php
               }
            }
         } else {
            echo '<p class="empty">No courses added yet!</p>';
         }
      } catch (PDOException $e) {
         echo '<p class="empty">Error fetching courses: ' . htmlspecialchars($e->getMessage()) . '</p>';
         error_log('Home courses fetch error: ' . $e->getMessage());
      }
      ?>
   </div>
   <div class="more-btn">
      <a href="courses.php" class="inline-option-btn">View More</a>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>