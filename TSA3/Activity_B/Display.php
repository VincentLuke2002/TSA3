<?php

require_once 'Database.php';

$user = getCurrentUser();
$conn = getConnection();


$concerts = $conn->query("
    SELECT c.*, 
           MIN(tc.price) as min_price,
           MAX(tc.price) as max_price,
           SUM(tc.available_quantity) as seats_left
    FROM concerts c
    LEFT JOIN ticket_categories tc ON tc.concert_id = c.id
    GROUP BY c.id
    ORDER BY c.concert_date ASC
")->fetch_all(MYSQLI_ASSOC);

$conn->close();

function formatDate($d) {
    return date('D, M j, Y', strtotime($d));
}
function formatTime($t) {
    return date('g:i A', strtotime($t));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LANY Concert Tickets</title>
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
      <?php if ($user): ?>
        <a href="Display.php" class="nav-link active">Shows</a>
        <a href="Bookings.php" class="nav-link">My Tickets</a>
        <span class="nav-user">
          <span class="user-avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></span>
          <?= htmlspecialchars($user['full_name']) ?>
        </span>
        <a href="Logout.php" class="btn-outline-sm">Sign Out</a>
      <?php else: ?>
        <a href="Login.php" class="nav-link">Sign In</a>
        <a href="RegistrationPage.php" class="btn-primary-sm">Get Started</a>
      <?php endif; ?>
    </div>
  </div>
</nav>


<header class="hero">
  <div class="hero-bg">
    <div class="hero-glow g1"></div>
    <div class="hero-glow g2"></div>
    <div class="hero-glow g3"></div>
    <div class="hero-noise"></div>
  </div>
  <div class="hero-content">
    <p class="hero-eyebrow">◈ &nbsp; OFFICIAL TICKETING &nbsp; ◈</p>
    <h1 class="hero-title">
      <span class="hero-title-lany">LANY</span>
      <span class="hero-title-live">LIVE</span>
    </h1>
    <p class="hero-tagline">Paul Klein and the band. Live. In the Philippines.</p>
    <div class="hero-stats">
      <div class="stat"><span class="stat-num"><?= count($concerts) ?></span><span class="stat-label">Shows</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num">PH</span><span class="stat-label">Exclusive</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num">2025</span><span class="stat-label">Season</span></div>
    </div>
    <a href="#shows" class="btn-hero">Browse Shows ↓</a>
  </div>
  <div class="hero-scroll-indicator">
    <span></span>
  </div>
</header>


<section class="shows-section" id="shows">
  <div class="section-inner">
    <div class="section-header">
      <h2 class="section-title">Upcoming Shows</h2>
      <p class="section-sub">Choose your night — every show is different</p>
    </div>

    <div class="concerts-grid">
      <?php foreach ($concerts as $c): ?>
      <article class="concert-card">
        <div class="concert-card-glow"></div>

        <div class="concert-header-strip">
          <span class="concert-tag">LANY LIVE</span>
          <?php if ($c['seats_left'] < 500): ?>
            <span class="seats-badge urgent">⚡ <?= $c['seats_left'] ?> left</span>
          <?php else: ?>
            <span class="seats-badge"><?= number_format($c['seats_left']) ?> seats available</span>
          <?php endif; ?>
        </div>

        <div class="concert-body">
          <h3 class="concert-title"><?= htmlspecialchars($c['title']) ?></h3>

          <div class="concert-meta">
            <div class="meta-item">
              <span class="meta-icon">📅</span>
              <span><?= formatDate($c['concert_date']) ?></span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">🕗</span>
              <span>Doors open <?= formatTime($c['concert_time']) ?></span>
            </div>
            <div class="meta-item">
              <span class="meta-icon">📍</span>
              <span><?= htmlspecialchars($c['venue']) ?></span>
            </div>
          </div>

          <p class="concert-desc"><?= htmlspecialchars(substr($c['description'], 0, 130)) ?>…</p>

          <div class="concert-price-row">
            <div class="price-range">
              <span class="price-label">From</span>
              <span class="price-value">₱<?= number_format($c['min_price'], 0) ?></span>
              <span class="price-to">to ₱<?= number_format($c['max_price'], 0) ?></span>
            </div>
            <a href="BookTicket.php?concert_id=<?= $c['id'] ?>" class="btn-book">
              <?= isLoggedIn() ? 'Book Tickets' : 'Sign In to Book' ?>
              <span class="btn-arrow">→</span>
            </a>
          </div>
        </div>

        <div class="ticket-perforations">
          <?php for ($i = 0; $i < 12; $i++): ?><span></span><?php endfor; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<section class="info-band">
  <div class="info-inner">
    <div class="info-item">
      <span class="info-icon">🎫</span>
      <div>
        <strong>Official Tickets</strong>
        <p>100% authentic, no hidden fees</p>
      </div>
    </div>
    <div class="info-item">
      <span class="info-icon">🔒</span>
      <div>
        <strong>Secure Booking</strong>
        <p>Your data is always protected</p>
      </div>
    </div>
    <div class="info-item">
      <span class="info-icon">📲</span>
      <div>
        <strong>Instant Confirmation</strong>
        <p>Booking code sent immediately</p>
      </div>
    </div>
    <div class="info-item">
      <span class="info-icon">🎵</span>
      <div>
        <strong>LANY Official</strong>
        <p>Authorized ticketing partner</p>
      </div>
    </div>
  </div>
</section>


<footer class="site-footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <span class="brand-logo">◈</span>
      <span class="footer-name">LANY TICKETS</span>
    </div>
    <p class="footer-copy">© 2025 LANY Ticketing System. All rights reserved.</p>
  </div>
</footer>

<script>

document.querySelector('.btn-hero')?.addEventListener('click', e => {
  e.preventDefault();
  document.querySelector('#shows').scrollIntoView({ behavior: 'smooth' });
});
</script>

</body>
</html>