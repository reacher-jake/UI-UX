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
   <title>Teachers</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="teachers">
   <h1 class="heading">Expert Tutors</h1>
   <form action="search_tutor.php" method="post" class="search-tutor">
      <input type="text" name="search_tutor" maxlength="100" placeholder="Search tutor..." required>
      <button type="submit" name="search_tutor_btn" class="fas fa-search"></button>
   </form>
   <div class="box-container">
      <div class="box offer">
         <h3>Become a Tutor</h3>
         <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Laborum, magnam!</p>
         <a href="admin/register.php" class="inline-btn">Get Started</a>
      </div>
      <?php
      try {
         $select_tutors = $conn->prepare("SELECT * FROM `tutors` WHERE approval_status = ?");
         $select_tutors->execute(['approved']);
         if ($select_tutors->rowCount() > 0) {
            while ($fetch_tutor = $select_tutors->fetch(PDO::FETCH_ASSOC)) {
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
      ?>
      <div class="box">
         <div class="tutor">
            <img src="uploaded_files/<?= htmlspecialchars($fetch_tutor['image']); ?>" alt="<?= htmlspecialchars($fetch_tutor['name']); ?>">
            <div>
               <h3><?= htmlspecialchars($fetch_tutor['name']); ?></h3>
               <span><?= htmlspecialchars($fetch_tutor['profession']); ?></span>
            </div>
         </div>
         <p>Playlists: <span><?= $total_playlists; ?></span></p>
         <p>Total Videos: <span><?= $total_contents; ?></span></p>
         <p>Total Likes: <span><?=

 $total_likes; ?></span></p>
         <p>Total Comments: <span><?= $total_comments; ?></span></p>
         <form action="tutor_profile.php" method="post">
            <input type="hidden" name="tutor_email" value="<?= htmlspecialchars($fetch_tutor['email']); ?>">
            <input type="submit" value="View Profile" name="tutor_fetch" class="inline-btn">
         </form>
      </div>
      <?php
            }
         } else {
            echo '<p class="empty">No tutors found!</p>';
         }
      } catch (PDOException $e) {
         echo '<p class="empty">Error fetching tutors: ' . htmlspecialchars($e->getMessage()) . '</p>';
         error_log('Teachers fetch error: ' . $e->getMessage());
      }
      ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>