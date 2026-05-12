<?php
include 'components/connect.php';

session_start();
$user_id = $_COOKIE['user_id'] ?? '';

if (!$conn) {
   die('Database connection failed!');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tutor_fetch'])) {
   $tutor_email = filter_var($_POST['tutor_email'] ?? '', FILTER_SANITIZE_EMAIL);
   try {
      $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ?");
      $select_tutor->execute([$tutor_email]);
      $fetch_tutor = $select_tutor->fetch(PDO::FETCH_ASSOC);
      if (!$fetch_tutor) {
         header('location:teachers.php');
         exit;
      }
      $tutor_id = $fetch_tutor['id'];

      $count_playlists = $conn->prepare("SELECT COUNT(*) FROM `playlist` WHERE tutor_id = ?");
      $count_playlists->execute([$tutor_id]);
      $total_playlists = $count_playlists->fetchColumn() ?: 0;

      $count_contents = $conn->prepare("SELECT COUNT(*) FROM `content` WHERE tutor_id = ?");
      $count_contents->execute([$tutor_id]);
      $total_contents = $count_contents->fetchColumn() ?: 0;

      $count_likes = $conn->prepare("SELECT COUNT(*) FROM `likes` WHERE tutor_id = ?");
      $count_likes->execute([$tutor_id]);
      $total_likes = $count_likes->fetchColumn() ?: 0;

      $count_comments = $conn->prepare("SELECT COUNT(*) FROM `comments` WHERE tutor_id = ?");
      $count_comments->execute([$tutor_id]);
      $total_comments = $count_comments->fetchColumn() ?: 0;
   } catch (PDOException $e) {
      $message[] = 'Error fetching tutor: ' . htmlspecialchars($e->getMessage());
      error_log('Tutor profile fetch error: ' . $e->getMessage());
      header('location:teachers.php');
      exit;
   }
} else {
   header('location:teachers.php');
   exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Tutor Profile</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="tutor-profile">
   <h1 class="heading">Profile Details</h1>
   <?php foreach ($message as $msg): ?>
      <div class="message"><span><?= htmlspecialchars($msg); ?></span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>
   <?php endforeach; ?>
   <div class="details">
      <div class="tutor">
         <img src="uploaded_files/<?= htmlspecialchars($fetch_tutor['image']); ?>" alt="<?= htmlspecialchars($fetch_tutor['name']); ?>">
         <h3><?= htmlspecialchars($fetch_tutor['name']); ?></h3>
         <span><?= htmlspecialchars($fetch_tutor['profession']); ?></span>
      </div>
      <div class="flex">
         <p>Total Playlists: <span><?= $total_playlists; ?></span></p>
         <p>Total Videos: <span><?= $total_contents; ?></span></p>
         <p>Total Likes: <span><?= $total_likes; ?></span></p>
         <p>Total Comments: <span><?= $total_comments; ?></span></p>
      </div>
   </div>
</section>

<section class="courses">
   <h1 class="heading">Latest Courses</h1>
   <div class="box-container">
      <?php
      try {
         $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE tutor_id = ? AND status = ? ORDER BY date DESC");
         $select_courses->execute([$tutor_id, 'active']);
         if ($select_courses->rowCount() > 0) {
            while ($fetch_course = $select_courses->fetch(PDO::FETCH_ASSOC)) {
               $course_id = $fetch_course['id'];
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
         } else {
            echo '<p class="empty">No courses added yet!</p>';
         }
      } catch (PDOException $e) {
         echo '<p class="empty">Error fetching courses: ' . htmlspecialchars($e->getMessage()) . '</p>';
         error_log('Tutor courses fetch error: ' . $e->getMessage());
      }
      ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>