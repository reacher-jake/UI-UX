<?php
include 'components/connect.php';

session_start();
$user_id = $_COOKIE['user_id'] ?? '';

if (!$conn) {
   die('Database connection failed!');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Courses</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="courses">
   <h1 class="heading">All Courses</h1>
   <div class="box-container">
      <?php
      try {
         $select_courses = $conn->prepare("SELECT * FROM `playlist` WHERE status = ? ORDER BY date DESC");
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
         error_log('Courses fetch error: ' . $e->getMessage());
      }
      ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>