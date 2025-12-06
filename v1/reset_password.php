<?php
// v1/reset_password.php
require('db_connect.php');
session_start();

$step = 1;
$message = '';
$email = '';
$question = '';
$user_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step1'])) {
        // Step 1: Verify Email
        $email = trim($_POST['email']);
        $stmt = $pdo->prepare("SELECT id, security_question FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['security_question']) {
                $step = 2;
                $question = $user['security_question'];
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_email'] = $email;
            } else {
                $message = "Please contact support at admin@cleaning.com (No security question set).";
            }
        } else {
            $message = "Email not found.";
        }
    } elseif (isset($_POST['step2'])) {
        // Step 2: Verify Answer
        $answer = $_POST['security_answer'];
        $user_id = $_SESSION['reset_user_id'];

        $stmt = $pdo->prepare("SELECT security_answer_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $hash = $stmt->fetchColumn();

        if (password_verify($answer, $hash)) {
            $step = 3;
        } else {
            $step = 2;
            // Re-fetch question to display it again
            $stmt = $pdo->prepare("SELECT security_question FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $question = $stmt->fetchColumn();
            $message = "Incorrect answer.";
        }
    } elseif (isset($_POST['step3'])) {
        // Step 3: Reset Password
        $password = $_POST['password'];
        $user_id = $_SESSION['reset_user_id'];

        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            if ($stmt->execute([$hash, $user_id])) {
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_email']);
                header("Location: login.php?msg=password_reset");
                exit();
            } else {
                $message = "Error updating password.";
            }
        } else {
            $step = 3;
            $message = "Password cannot be empty.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if ($message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <form method="post">
                <div class="form-group">
                    <label>Enter your Email</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" name="step1">Next</button>
                <a href="login.php" class="btn btn-secondary">Back to Login</a>
            </form>
        <?php elseif ($step === 2): ?>
            <form method="post">
                <div class="form-group">
                    <label>Security Question: <strong><?php echo htmlspecialchars($question); ?></strong></label>
                    <input type="password" name="security_answer" placeholder="Your Answer" required>
                </div>
                <button type="submit" name="step2">Verify</button>
            </form>
        <?php elseif ($step === 3): ?>
            <form method="post">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" name="step3">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
