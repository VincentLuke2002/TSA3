<?php
// ACTIVITY A - Login.php


require_once 'Data.php';

if (isLoggedIn()) {
    header('Location: Display.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $error = 'Please fill in all fields.';
    } elseif (!isset($_SESSION['registered_user'])) {
        $error = 'No account found with that username or email.';
    } else {
        $row = $_SESSION['registered_user'];

        if ($username !== $row['username'] && $username !== $row['email']) {
            $error = 'No account found with that username or email.';
        } elseif (!password_verify($password, $row['password'])) {
            $error = 'Incorrect password.';
        } else {
            $_SESSION['logged_in_user'] = [
                'username'  => $row['username'],
                'full_name' => $row['full_name'],
                'email'     => $row['email'],
            ];
            header('Location: Display.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — LANY Tickets</title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body class="auth-body">

<div class="auth-bg">
  <div class="auth-glow glow-1"></div>
  <div class="auth-glow glow-2"></div>
</div>

<div class="auth-container">
  <a href="Display.php" class="auth-back">← Back to shows</a>

  <div class="auth-card">
    <div class="auth-brand">
      <span class="brand-logo">◈</span>
      <h1 class="brand-name">LANY</h1>
      <p class="brand-sub">TICKETING</p>
    </div>

    <h2 class="auth-title">Welcome Back</h2>
    <p class="auth-desc">Sign in to your account</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <div class="form-group">
        <label>Username or Email</label>
        <input type="text" name="username" placeholder="Enter username or email" value="<?= htmlspecialchars($_REQUEST['username'] ?? '') ?>" required autofocus>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn-primary btn-full">Sign In</button>
    </form>

    <p class="auth-switch">Don't have an account? <a href="RegistrationPage.php">Create one</a></p>
  </div>
</div>

</body>
</html>
