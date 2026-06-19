<?php
// Activity_B/BookTicket.php
require_once 'Database.php';

if (!isLoggedIn()) {
    header('Location: Login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$user = getCurrentUser();
$concert_id = intval($_GET['concert_id'] ?? 0);

$conn = getConnection();

// Fetch concert
$stmt = $conn->prepare("SELECT * FROM concerts WHERE id = ?");
$stmt->bind_param('i', $concert_id);
$stmt->execute();
$concert = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$concert) {
    header('Location: Display.php');
    exit;
}

// Fetch categories
$cats = $conn->prepare("SELECT * FROM ticket_categories WHERE concert_id = ? ORDER BY price ASC");
$cats->bind_param('i', $concert_id);
$cats->execute();
$categories = $cats->get_result()->fetch_all(MYSQLI_ASSOC);
$cats->close();

$error = '';
$success_booking = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cat_id  = intval($_POST['category_id'] ?? 0);
    $qty     = intval($_POST['quantity'] ?? 1);

    if (!$cat_id || $qty < 1 || $qty > 10) {
        $error = 'Please select a valid category and quantity (1–10).';
    } else {
    
        $cv = $conn->prepare("SELECT * FROM ticket_categories WHERE id=? AND concert_id=?");
        $cv->bind_param('ii', $cat_id, $concert_id);
        $cv->execute();
        $cat = $cv->get_result()->fetch_assoc();
        $cv->close();

        if (!$cat) {
            $error = 'Invalid ticket category.';
        } elseif ($cat['available_quantity'] < $qty) {
            $error = "Only {$cat['available_quantity']} ticket(s) available in that category.";
        } else {
            $total = $cat['price'] * $qty;
            $code  = generateBookingCode();

            
            $ins = $conn->prepare("INSERT INTO bookings (user_id, concert_id, category_id, quantity, total_price, booking_code) VALUES (?,?,?,?,?,?)");
            $ins->bind_param('iiiids', $user['id'], $concert_id, $cat_id, $qty, $total, $code);

            if ($ins->execute()) {
                
                $upd = $conn->prepare("UPDATE ticket_categories SET available_quantity = available_quantity - ? WHERE id = ?");
                $upd->bind_param('ii', $qty, $cat_id);
                $upd->execute();
                $upd->close();

                $success_booking = [
                    'code'     => $code,
                    'category' => $cat['category_name'],
                    'quantity' => $qty,
                    'total'    => $total,
                ];
            } else {
                $error = 'Booking failed. Please try again.';
            }
            $ins->close();
        }
    }
    
    $cats2 = $conn->prepare("SELECT * FROM ticket_categories WHERE concert_id = ? ORDER BY price ASC");
    $cats2->bind_param('i', $concert_id);
    $cats2->execute();
    $categories = $cats2->get_result()->fetch_all(MYSQLI_ASSOC);
    $cats2->close();
}

$conn->close();

function formatDate($d) { return date('D, M j, Y', strtotime($d)); }
function formatTime($t) { return date('g:i A', strtotime($t)); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Tickets — <?= htmlspecialchars($concert['title']) ?></title>
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
      <a href="Bookings.php" class="nav-link">My Tickets</a>
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

<main class="book-main">
  <div class="book-inner">

    <?php if ($success_booking): ?>
    
    <div class="success-overlay">
      <div class="success-card">
        <div class="success-icon">✓</div>
        <h2>Booking Confirmed!</h2>
        <p class="success-sub">Your tickets are secured. See you at the show!</p>

        <div class="ticket-stub">
          <div class="stub-left">
            <span class="stub-artist">LANY</span>
            <span class="stub-show"><?= htmlspecialchars($concert['title']) ?></span>
            <span class="stub-date"><?= formatDate($concert['concert_date']) ?></span>
            <span class="stub-venue"><?= htmlspecialchars($concert['venue']) ?></span>
          </div>
          <div class="stub-divider">
            <?php for ($i = 0; $i < 8; $i++): ?><span></span><?php endfor; ?>
          </div>
          <div class="stub-right">
            <span class="stub-label">BOOKING CODE</span>
            <span class="stub-code"><?= $success_booking['code'] ?></span>
            <span class="stub-label">CATEGORY</span>
            <span class="stub-cat"><?= htmlspecialchars($success_booking['category']) ?></span>
            <span class="stub-label">QTY × TOTAL</span>
            <span class="stub-total"><?= $success_booking['quantity'] ?>× &nbsp; ₱<?= number_format($success_booking['total'], 2) ?></span>
          </div>
        </div>

        <div class="success-actions">
          <a href="Bookings.php" class="btn-primary">View All My Tickets</a>
          <a href="Display.php" class="btn-outline">Back to Shows</a>
        </div>
      </div>
    </div>

    <?php else: ?>
    
    <div class="book-grid">

      
      <aside class="book-sidebar">
        <div class="concert-info-card">
          <p class="info-tag">LANY LIVE</p>
          <h1 class="info-title"><?= htmlspecialchars($concert['title']) ?></h1>
          <div class="info-details">
            <div class="info-row">
              <span class="info-icon">📅</span>
              <div>
                <strong><?= formatDate($concert['concert_date']) ?></strong>
                <span><?= formatTime($concert['concert_time']) ?></span>
              </div>
            </div>
            <div class="info-row">
              <span class="info-icon">📍</span>
              <div>
                <strong>Venue</strong>
                <span><?= htmlspecialchars($concert['venue']) ?></span>
              </div>
            </div>
          </div>
          <p class="info-desc"><?= htmlspecialchars($concert['description']) ?></p>
          <div class="ticket-perforations">
            <?php for ($i = 0; $i < 10; $i++): ?><span></span><?php endfor; ?>
          </div>
        </div>
      </aside>

      
      <div class="book-form-wrap">
        <h2 class="form-heading">Select Your Tickets</h2>

        <?php if ($error): ?>
          <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" class="book-form" id="bookForm">

          
          <div class="category-list">
            <?php foreach ($categories as $cat): ?>
            <label class="category-option <?= $cat['available_quantity'] === 0 ? 'sold-out' : '' ?>">
              <input type="radio" name="category_id" value="<?= $cat['id'] ?>"
                     data-price="<?= $cat['price'] ?>"
                     data-name="<?= htmlspecialchars($cat['category_name']) ?>"
                     <?= $cat['available_quantity'] === 0 ? 'disabled' : '' ?>
                     <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'checked' : '' ?>>
              <div class="cat-card">
                <div class="cat-left">
                  <span class="cat-check">◯</span>
                  <div>
                    <strong class="cat-name"><?= htmlspecialchars($cat['category_name']) ?></strong>
                    <span class="cat-avail">
                      <?= $cat['available_quantity'] > 0
                          ? number_format($cat['available_quantity']) . ' available'
                          : 'SOLD OUT' ?>
                    </span>
                  </div>
                </div>
                <span class="cat-price">₱<?= number_format($cat['price'], 0) ?></span>
              </div>
            </label>
            <?php endforeach; ?>
          </div>

          
          <div class="qty-row">
            <label class="qty-label">Number of Tickets</label>
            <div class="qty-control">
              <button type="button" class="qty-btn" id="qtyMinus">−</button>
              <input type="number" name="quantity" id="qtyInput" value="<?= intval($_POST['quantity'] ?? 1) ?>" min="1" max="10" readonly>
              <button type="button" class="qty-btn" id="qtyPlus">+</button>
            </div>
            <span class="qty-note">Max 10 per booking</span>
          </div>

          
          <div class="order-summary" id="orderSummary" style="display:none">
            <h4>Order Summary</h4>
            <div class="summary-row">
              <span id="summaryCategory">—</span>
              <span>×&nbsp;<span id="summaryQty">1</span></span>
            </div>
            <div class="summary-total-row">
              <span>Total</span>
              <span class="summary-total" id="summaryTotal">₱0</span>
            </div>
          </div>

          <button type="submit" class="btn-primary btn-full btn-book-confirm" id="submitBtn" disabled>
            Confirm Booking →
          </button>
          <p class="book-note">By booking you agree to our terms. No refunds on confirmed tickets.</p>
        </form>
      </div>

    </div>
    <?php endif; ?>

  </div>
</main>

<script>
const radios = document.querySelectorAll('input[name="category_id"]');
const qtyInput = document.getElementById('qtyInput');
const qtyMinus = document.getElementById('qtyMinus');
const qtyPlus  = document.getElementById('qtyPlus');
const summaryDiv = document.getElementById('orderSummary');
const submitBtn  = document.getElementById('submitBtn');

let selectedPrice = 0;
let selectedName  = '';

function updateSummary() {
  const qty = parseInt(qtyInput.value) || 1;
  if (selectedPrice > 0) {
    document.getElementById('summaryCategory').textContent = selectedName;
    document.getElementById('summaryQty').textContent = qty;
    document.getElementById('summaryTotal').textContent = '₱' + (selectedPrice * qty).toLocaleString('en-PH');
    summaryDiv.style.display = 'block';
    submitBtn.disabled = false;
  }
}

radios.forEach(r => {
  r.addEventListener('change', () => {
    document.querySelectorAll('.category-option').forEach(el => el.classList.remove('selected'));
    r.closest('.category-option').classList.add('selected');
    r.closest('.category-option').querySelector('.cat-check').textContent = '◉';
    selectedPrice = parseFloat(r.dataset.price);
    selectedName  = r.dataset.name;
    updateSummary();
  });
});

qtyMinus.addEventListener('click', () => {
  let v = parseInt(qtyInput.value);
  if (v > 1) { qtyInput.value = v - 1; updateSummary(); }
});
qtyPlus.addEventListener('click', () => {
  let v = parseInt(qtyInput.value);
  if (v < 10) { qtyInput.value = v + 1; updateSummary(); }
});


const checkedRadio = document.querySelector('input[name="category_id"]:checked');
if (checkedRadio) {
  selectedPrice = parseFloat(checkedRadio.dataset.price);
  selectedName  = checkedRadio.dataset.name;
  checkedRadio.closest('.category-option').classList.add('selected');
  updateSummary();
}
</script>

</body>
</html>