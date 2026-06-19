<?php
// Activity_B/Bookings.php
require_once 'Database.php';

if (!isLoggedIn()) {
    header('Location: Login.php');
    exit;
}

$user = getCurrentUser();
$conn = getConnection();

$stmt = $conn->prepare("
    SELECT b.*, c.title, c.venue, c.concert_date, c.concert_time,
           tc.category_name, tc.price
    FROM bookings b
    JOIN concerts c ON c.id = b.concert_id
    JOIN ticket_categories tc ON tc.id = b.category_id
    WHERE b.user_id = ?
    ORDER BY b.booked_at DESC
");
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

function formatDate($d) { return date('D, M j, Y', strtotime($d)); }
function formatTime($t) { return date('g:i A', strtotime($t)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Tickets — LANY</title>
<link rel="stylesheet" href="style.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="Display.php" class="nav-brand">
      <span class="brand-logo">◈</span>
      <span>LANY <span class="nav-sub">TICKETS</span></span>
    </a>
    <div class="nav-links">
      <a href="Display.php" class="nav-link">Shows</a>
      <a href="Bookings.php" class="nav-link active">My Tickets</a>
      <span class="nav-user">
        <span class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
        <?= htmlspecialchars($user['full_name']) ?>
      </span>
      <a href="Logout.php" class="btn-outline-sm">Sign Out</a>
    </div>
  </div>
</nav>

<div class="page-bg">
  <div class="page-glow pg1"></div>
  <div class="page-glow pg2"></div>
</div>

<main class="bookings-main">
  <div class="bookings-inner">
    <div class="bookings-header">
      <h1 class="bookings-title">My Tickets</h1>
      <p class="bookings-sub">All your LANY bookings in one place</p>
    </div>

    <?php if (empty($bookings)): ?>
    <div class="empty-state">
      <span class="empty-icon">🎫</span>
      <h3>No tickets yet</h3>
      <p>You haven't booked any tickets. Find a show and get yours!</p>
      <a href="Display.php" class="btn-primary">Browse Shows</a>
    </div>
    <?php else: ?>
    <div class="tickets-list">
      <?php foreach ($bookings as $b): ?>
      <div class="my-ticket <?= $b['status'] === 'cancelled' ? 'ticket-cancelled' : '' ?>">
        <div class="my-ticket-main">
          <div class="my-ticket-left">
            <span class="my-ticket-artist">LANY</span>
            <h3 class="my-ticket-show"><?= htmlspecialchars($b['title']) ?></h3>
            <div class="my-ticket-meta">
              <span>📅 <?= formatDate($b['concert_date']) ?> · <?= formatTime($b['concert_time']) ?></span>
              <span>📍 <?= htmlspecialchars($b['venue']) ?></span>
            </div>
          </div>
          <div class="my-ticket-right">
            <span class="ticket-status-badge status-<?= $b['status'] ?>"><?= ucfirst($b['status']) ?></span>
          </div>
        </div>

        <div class="my-ticket-perf">
          <?php for ($i = 0; $i < 14; $i++): ?><span></span><?php endfor; ?>
        </div>

        <div class="my-ticket-stub">
          <div class="stub-info">
            <div>
              <span class="stub-label">BOOKING CODE</span>
              <strong class="stub-code-sm"><?= $b['booking_code'] ?></strong>
            </div>
            <div>
              <span class="stub-label">CATEGORY</span>
              <strong><?= htmlspecialchars($b['category_name']) ?></strong>
            </div>
            <div>
              <span class="stub-label">QUANTITY</span>
              <strong><?= $b['quantity'] ?> ticket<?= $b['quantity'] > 1 ? 's' : '' ?></strong>
            </div>
            <div>
              <span class="stub-label">TOTAL PAID</span>
              <strong class="price-accent">₱<?= number_format($b['total_price'], 2) ?></strong>
            </div>
          </div>
          <div class="stub-booked">
            Booked on <?= date('M j, Y', strtotime($b['booked_at'])) ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</main>

<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <span class="brand-logo">◈</span>
      <span class="footer-name">LANY TICKETS</span>
    </div>
    <p class="footer-copy">© 2025 LANY Ticketing System. All rights reserved.</p>
  </div>
</footer>

</body>
</html>