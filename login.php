<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', 'C:/xampp/htdocs/online_tutor_website/admin/error.log');

function unique_id() {
    return bin2hex(random_bytes(10));
}

include 'components/connect.php';

if (!$conn) {
    die('Database connection failed!');
}

session_start();
$message = [];

if (isset($_POST['submit'])) {
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $pass = $_POST['pass'];
    $user_type = filter_var($_POST['user_type'], FILTER_SANITIZE_STRING);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message[] = 'Invalid email format!';
        error_log('Invalid email format: ' . $email);
    } elseif (empty($email) || empty($pass) || empty($user_type)) {
        $message[] = 'All fields are required!';
        error_log('Validation failed: Missing fields for email=' . $email);
    } else {
        try {
            if ($user_type === 'tutor') {
                $select_tutor = $conn->prepare("SELECT * FROM `tutors` WHERE LOWER(email) = LOWER(?)");
                $select_tutor->execute([$email]);
                $row = $select_tutor->fetch(PDO::FETCH_ASSOC);

                if ($select_tutor->rowCount() > 0) {
                    if (password_verify($pass, $row['password'])) {
                        if ($row['approval_status'] !== 'approved') {
                            $message[] = 'Your account is not approved!';
                            error_log('Login failed: Account not approved for email=' . $email);
                        } else {
                            setcookie('tutor_id', $row['id'], time() + 60*60*24*30, '/', '', false, true);
                            $activity_id = unique_id();
                            $insert_activity = $conn->prepare("INSERT INTO `user_activity` (id, tutor_id, login_time) VALUES (?, ?, NOW())");
                            $insert_activity->execute([$activity_id, $row['id']]);
                            error_log('Login successful for tutor: ' . $email);
                            header('location:dashboard.php');
                            exit;
                        }
                    } else {
                        $message[] = 'Incorrect password!';
                        error_log('Password verification failed for tutor: ' . $email);
                    }
                } else {
                    $message[] = 'No tutor account found with this email!';
                    error_log('No tutor account found for email: ' . $email);
                }
            } else {
                $select_user = $conn->prepare("SELECT * FROM `users` WHERE LOWER(email) = LOWER(?)");
                $select_user->execute([$email]);
                $row = $select_user->fetch(PDO::FETCH_ASSOC);

                if ($select_user->rowCount() > 0) {
                    if (password_verify($pass, $row['password'])) {
                        if ($row['approval_status'] !== 'approved') {
                            $message[] = 'Your account is not approved!';
                            error_log('Login failed: Account not approved for email=' . $email);
                        } else {
                            setcookie('user_id', $row['id'], time() + 60*60*24*30, '/', '', false, true);
                            error_log('Login successful for user: ' . $email);
                            header('location:home.php');
                            exit;
                        }
                    } else {
                        $message[] = 'Incorrect password!';
                        error_log('Password verification failed for user: ' . $email);
                    }
                } else {
                    $message[] = 'No student account found with this email!';
                    error_log('No student account found for email=' . $email);
                }
            }
        } catch (PDOException $e) {
            $message[] = 'Login error: ' . $e->getMessage();
            error_log('Login error for email: ' . $email . ': ' . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body style="padding-left: 0;">
<?php
if (!empty($message)) {
    foreach ($message as $msg) {
        echo '<div class="message form"><span>' . htmlspecialchars($msg) . '</span><i class="fas fa-times" onclick="this.parentElement.remove();"></i></div>';
    }
}
?>
<section class="form-container">
    <form action="" method="post" class="login">
        <h3>Login</h3>
        <p>User type <span>*</span></p>
        <select name="user_type" class="box" required>
            <option value="" disabled selected>-- Select user type</option>
            <option value="tutor">Tutor</option>
            <option value="user">Student</option>
        </select>
        <p>Your email <span>*</span></p>
        <input type="email" name="email" placeholder="Enter your email" maxlength="50" required class="box">
        <p>Your password <span>*</span></p>
        <input type="password" name="pass" placeholder="Enter your password" maxlength="50" required class="box">
        <p class="link">Don't have an account? <a href="register.php">Register now</a></p>
        <input type="submit" name="submit" value="Login now" class="btn">
    </form>
</section>
<script src="../js/admin_script.js"></script>
</body>
</html>