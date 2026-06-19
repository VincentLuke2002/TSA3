<?php

require_once 'Database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (!$full_name || !$username || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $conn = getConnection();
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=? OR email=?");
        $stmt->bind_param('ss', $username, $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $ins = $conn->prepare("INSERT INTO users (full_name, username, email, password) VALUES (?,?,?,?)");
            $ins->bind_param('ssss', $full_name, $username, $email, $hashed);
            if ($ins->execute()) {
                $success = 'Account created! You can now log in.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $ins->close();
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — LANY Tickets</title>
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

    <h2 class="auth-title">Create Account</h2>
    <p class="auth-desc">Join to book your LANY experience</p>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?> <a href="Login.php">Log in →</a></div>
    <?php endif; ?>

    <form method="POST" class="auth-form">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" placeholder="Your full name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Choose a username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="your@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" placeholder="Min. 8 characters" required>
        </div>
        <div class="form-group">
          <label>Confirm Password</label>
          <input type="password" name="confirm_password" placeholder="Enter password" required>
        </div>
      </div>
      <button type="submit" class="btn-primary btn-full">Create Account</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="Login.php">Sign in</a></p>
  </div>
</div>

</body>
</html>