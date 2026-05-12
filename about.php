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
   <title>About</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="about">
   <div class="row">
      <div class="image">
         <img src="images/about-img.svg" alt="About Us">
      </div>
      <div class="content">
         <h3>Why Choose Us?</h3>
         <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Neque nobis distinctio, nisi consequatur ad sequi, rem odit fugiat assumenda eligendi iure aut sunt ratione, tempore porro expedita quisquam.</p>
         <a href="courses.php" class="inline-btn">Our Courses</a>
      </div>
   </div>
   <div class="box-container">
      <div class="box">
         <i class="fas fa-graduation-cap"></i>
         <div>
            <h3>+1k</h3>
            <span>Online Courses</span>
         </div>
      </div>
      <div class="box">
         <i class="fas fa-user-graduate"></i>
         <div>
            <h3>+25k</h3>
            <span>Brilliant Students</span>
         </div>
      </div>
      <div class="box">
         <i class="fas fa-chalkboard-user"></i>
         <div>
            <h3>+5k</h3>
            <span>Expert Teachers</span>
         </div>
      </div>
      <div class="box">
         <i class="fas fa-briefcase"></i>
         <div>
            <h3>100%</h3>
            <span>Job Placement</span>
         </div>
      </div>
   </div>
</section>

<section class="reviews">
   <h1 class="heading">Student Reviews</h1>
   <div class="box-container">
      <?php
      $reviews = [
         ['image' => 'images/pic-2.jpg', 'name' => 'John Deo'],
         ['image' => 'images/pic-3.jpg', 'name' => 'Jane Smith'],
         ['image' => 'images/pic-4.jpg', 'name' => 'Alice Johnson'],
         ['image' => 'images/pic-5.jpg', 'name' => 'Bob Wilson'],
         ['image' => 'images/pic-6.jpg', 'name' => 'Emma Davis'],
         ['image' => 'images/pic-7.jpg', 'name' => 'Michael Brown']
      ];
      foreach ($reviews as $review) {
      ?>
      <div class="box">
         <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Illo fugiat, quaerat voluptate odio consectetur assumenda fugit maxime unde at ex?</p>
         <div class="user">
            <img src="<?= htmlspecialchars($review['image']); ?>" alt="<?= htmlspecialchars($review['name']); ?>">
            <div>
               <h3><?= htmlspecialchars($review['name']); ?></h3>
               <div class="stars">
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star"></i>
                  <i class="fas fa-star-half-alt"></i>
               </div>
            </div>
         </div>
      </div>
      <?php } ?>
   </div>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>