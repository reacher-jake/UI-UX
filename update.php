<?php
include 'components/connect.php';

session_start();
$user_id = $_COOKIE['user_id'] ?? '';
$message = [];

if (!$conn) {
   die('Database connection failed!');
}

if (empty($user_id)) {
   header('location:login.php');
   exit;
}

try {
   $select_user = $conn->prepare("SELECT * FROM `users` WHERE id = ? LIMIT 1");
   $select_user->execute([$user_id]);
   $fetch_user = $select_user->fetch(PDO::FETCH_ASSOC);
   if (!$fetch_user) {
      $message[] = 'User not found!';
      header('location:login.php');
      exit;
   }
} catch (PDOException $e) {
   $message[] = 'Error fetching user: ' . htmlspecialchars($e->getMessage());
   error_log('User fetch error: ' . $e->getMessage());
   header('location:login.php');
   exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
   $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_STRING);
   $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
   $old_pass = $_POST['old_pass'] ?? '';
   $new_pass = $_POST['new_pass'] ?? '';
   $cpass = $_POST['cpass'] ?? '';

   if (!empty($name)) {
      try {
         $update_name = $conn->prepare("UPDATE `users` SET name = ? WHERE id = ?");
         $update_name->execute([$name, $user_id]);
         $message[] = 'Username updated successfully!';
      } catch (PDOException $e) {
         $message[] = 'Error updating name: ' . htmlspecialchars($e->getMessage());
         error_log('Name update error: ' . $e->getMessage());
      }
   }

   if (!empty($email)) {
      try {
         $select_email = $conn->prepare("SELECT * FROM `users` WHERE email = ? AND id != ?");
         $select_email->execute([$email, $user_id]);
         if ($select_email->rowCount() > 0) {
            $message[] = 'Email already taken!';
         } else {
            $update_email = $conn->prepare("UPDATE `users` SET email = ? WHERE id = ?");
            $update_email->execute([$email, $user_id]);
            $message[] = 'Email updated successfully!';
         }
      } catch (PDOException $e) {
         $message[] = 'Error updating email: ' . htmlspecialchars($e->getMessage());
         error_log('Email update error: ' . $e->getMessage());
      }
   }

   if (!empty($_FILES['image']['name'])) {
      $image = filter_var($_FILES['image']['name'], FILTER_SANITIZE_STRING);
      $ext = pathinfo($image, PATHINFO_EXTENSION);
      $rename = bin2hex(random_bytes(10)) . '.' . $ext;
      $image_size = $_FILES['image']['size'];
      $image_tmp_name = $_FILES['image']['tmp_name'];
      $image_folder = 'uploaded_files/' . $rename;

      if ($image_size > 2000000) {
         $message[] = 'Image size too large!';
      } else {
         try {
            $update_image = $conn->prepare("UPDATE `users` SET image = ? WHERE id = ?");
            $update_image->execute([$rename, $user_id]);
            if (move_uploaded_file($image_tmp_name, $image_folder)) {
               if ($fetch_user['image'] != 'default.jpg' && $fetch_user['image'] != $rename) {
                  @unlink('uploaded_files/' . $fetch_user['image']);
               }
               $message[] = 'Image updated successfully!';
            } else {
               $message[] = 'Failed to upload image!';
            }
         } catch (PDOException $e) {
            $message[] = 'Error updating image: ' . htmlspecialchars($e->getMessage());
            error_log('Image update error: ' . $e->getMessage());
         }
      }
   }

   if (!empty($old_pass)) {
      if (!password_verify($old_pass, $fetch_user['password'])) {
         $message[] = 'Old password not matched!';
      } elseif ($new_pass !== $cpass) {
         $message[] = 'Confirm password not matched!';
      } elseif (empty($new_pass)) {
         $message[] = 'Please enter a new password!';
      } else {
         try {
            $hashed_password = password_hash($new_pass, PASSWORD_DEFAULT);
            $update_pass = $conn->prepare("UPDATE `users` SET password = ? WHERE id = ?");
            $update_pass->execute([$hashed_password, $user_id]);
            $message[] = 'Password updated successfully!';
         } catch (PDOException $e) {
            $message[] = 'Error updating password: ' . htmlspecialchars($e->getMessage());
            error_log('Password update error: ' . $e->getMessage());
         }
      }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Update Profile</title>
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
   <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'components/user_header.php'; ?>

<section class="form-container" style="min-height: calc(100vh - 19rem);">
   <h1 class="heading">Update Profile</h1>
   <?php foreach ($message as $msg): ?>
      <div class="message"><span><?= htmlspecialchars($msg); ?></span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>
   <?php endforeach; ?>
   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="col">
            <p>Your Name</p>
            <input type="text" name="name" placeholder="<?= htmlspecialchars($fetch_user['name']); ?>" maxlength="100" class="box">
            <p>Your Email</p>
            <input type="email" name="email" placeholder="<?= htmlspecialchars($fetch_user['email']); ?>" maxlength="100" class="box">
            <p>Update Picture</p>
            <input type="file" name="image" accept="image/*" class="box">
         </div>
         <div class="col">
            <p>Old Password</p>
            <input type="password" name="old_pass" placeholder="Enter your old password" maxlength="50" class="box">
            <p>New Password</p>
            <input type="password" name="new_pass" placeholder="Enter your new password" maxlength="50" class="box">
            <p>Confirm Password</p>
            <input type="password" name="cpass" placeholder="Confirm your new password" maxlength="50" class="box">
         </div>
      </div>
      <input type="submit" name="submit" value="Update Profile" class="btn">
   </form>
</section>

<?php include 'components/footer.php'; ?>
<script src="js/script.js"></script>
</body>
</html>