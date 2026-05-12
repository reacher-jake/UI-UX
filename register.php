<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/online_tutor_website/error.log');

include 'components/connect.php';

if (!$conn) {
   die('Database connection failed!');
}

session_start();
$message = [];

function unique_id() {
   return bin2hex(random_bytes(10));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
   $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
   $password = $_POST['password'] ?? '';
   $confirm_password = $_POST['confirm_password'] ?? '';
   $role = $_POST['role'] ?? '';
   $profession = filter_var($_POST['profession'] ?? '', FILTER_SANITIZE_STRING); // For tutors

   error_log('Registration attempt: email=' . $email . ', role=' . $role);

   if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
      $message[] = 'All required fields must be filled!';
      error_log('Validation failed: Missing fields for email=' . $email);
   } elseif ($role === 'tutor' && empty($profession)) {
      $message[] = 'Profession is required for tutors!';
      error_log('Validation failed: Missing profession for tutor email=' . $email);
   } elseif ($password !== $confirm_password) {
      $message[] = 'Passwords do not match!';
      error_log('Validation failed: Passwords do not match for email=' . $email);
   } else {
      try {
         // Check email in both tables to ensure uniqueness
         $select_user = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
         $select_user->execute([$email]);
         $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE email = ?");
         $select_tutor->execute([$email]);

         if ($select_user->rowCount() > 0 || $select_tutor->rowCount() > 0) {
            $message[] = 'Email already registered!';
            error_log('Validation failed: Email already registered: ' . $email);
         } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            if ($hashed_password === false) {
               $message[] = 'Failed to hash password!';
               error_log('Password hashing failed for email: ' . $email);
            } else {
               $id = unique_id();
               $image = 'default.jpg';
               $approval_status = 'pending';

               if ($role === 'student') {
                  $insert_user = $conn->prepare("INSERT INTO `users` (id, name, email, password, image, approval_status) VALUES (?, ?, ?, ?, ?, ?)");
                  $result = $insert_user->execute([$id, $name, $email, $hashed_password, $image, $approval_status]);
                  $table = 'users';
               } elseif ($role === 'tutor') {
                  $insert_tutor = $conn->prepare("INSERT INTO `tutors` (id, name, profession, email, password, image, approval_status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                  $result = $insert_tutor->execute([$id, $name, $profession, $email, $hashed_password, $image, $approval_status]);
                  $table = 'tutors';
               }

               if ($result) {
                  $message[] = 'Registration successful! Please wait for approval.';
                  error_log('Registered ' . $role . ': ' . $email);
                  header('location:login.php');
                  exit;
               } else {
                  $message[] = 'Failed to save to database!';
                  error_log('Insert failed for email: ' . $email);
               }
            }
         }
      } catch (PDOException $e) {
         $message[] = 'Registration error: ' . htmlspecialchars($e->getMessage());
         error_log('Registration error for email: ' . $email . ': ' . $e->getMessage());
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="form-container">
   <h1 class="heading">Register</h1>
   <?php foreach ($message as $msg): ?>
      <div class="message"><span><?= htmlspecialchars($msg); ?></span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>
   <?php endforeach; ?>
   <form action="" method="post">
      <p>Role <span>*</span></p>
      <select name="role" class="box" required>
         <option value="student">Student</option>
         <option value="tutor">Tutor</option>
      </select>
      <p>Name <span>*</span></p>
      <input type="text" name="name" class="box" required>
      <p>Email <span>*</span></p>
      <input type="email" name="email" class="box" required>
      <p>Profession (Required for Tutors)</p>
      <input type="text" name="profession" class="box">
      <p>Password <span>*</span></p>
      <input type="password" name="password" class="box" required>
      <p>Confirm Password <span>*</span></p>
      <input type="password" name="confirm_password" class="box" required>
      <input type="submit" name="submit" value="Register" class="btn">
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </form>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>