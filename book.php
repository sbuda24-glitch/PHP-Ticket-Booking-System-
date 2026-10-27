<?php
require_once __DIR__ . '/config.php';

$eventId = filter_input(INPUT_GET, 'event', FILTER_VALIDATE_INT) ?: filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
if (!$eventId) redirect('index.php');

$stmt = db()->prepare('SELECT * FROM events WHERE id = ? AND event_date >= NOW()');
$stmt->bind_param('i', $eventId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
if (!$event) { http_response_code(404); exit('Event not found or booking is closed. <a href="index.php">Go back</a>'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)($_POST['customer_name'] ?? ''));
    $email = trim((string)($_POST['customer_email'] ?? ''));
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    if ($name === '' || mb_strlen($name) > 120) $error = 'Please enter your name (up to 120 characters).';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) $error = 'Please enter a valid email address.';
    elseif (!$quantity || $quantity < 1 || $quantity > 10) $error = 'Choose between 1 and 10 tickets.';
    else {
        $conn = db();
        try {
            $conn->begin_transaction();
            $lock = $conn->prepare('SELECT price, tickets_remaining FROM events WHERE id = ? FOR UPDATE');
            $lock->bind_param('i', $eventId); $lock->execute();
            $lockedEvent = $lock->get_result()->fetch_assoc();
            if (!$lockedEvent || (int)$lockedEvent['tickets_remaining'] < $quantity) {
                $conn->rollback();
                $error = 'Sorry, there are not enough tickets left. Please choose a smaller quantity.';
            } else {
                $total = (float)$lockedEvent['price'] * $quantity;
                $reference = 'TK' . strtoupper(bin2hex(random_bytes(4)));
                $insert = $conn->prepare('INSERT INTO bookings (reference_code, event_id, customer_name, customer_email, quantity, total_amount) VALUES (?, ?, ?, ?, ?, ?)');
                $insert->bind_param('sissid', $reference, $eventId, $name, $email, $quantity, $total);
                $insert->execute();
                $update = $conn->prepare('UPDATE events SET tickets_remaining = tickets_remaining - ? WHERE id = ?');
                $update->bind_param('ii', $quantity, $eventId); $update->execute();
                $conn->commit();
                $_SESSION['last_booking'] = ['reference' => $reference, 'name' => $name, 'event' => $event['title'], 'quantity' => $quantity, 'total' => $total];
                redirect('confirmation.php');
            }
        } catch (Throwable $ex) {
            $conn->rollback();
            $error = 'We could not complete the booking. Please try again.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Book tickets | Ticketly</title><link rel="stylesheet" href="style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><nav><a href="index.php#events">← All events</a></nav></header>
<main class="form-page"><div class="form-intro"><div class="eyebrow muted">YOUR NIGHT OUT STARTS HERE</div><h1>Reserve your <span>tickets.</span></h1><p>You're a few details away from making it official.</p></div>
<div class="booking-layout"><section class="booking-summary"><div class="summary-label">YOU'RE BOOKING</div><div class="summary-art"><span>✳</span></div><h2><?= e($event['title']) ?></h2><p>⌖ <?= e($event['venue']) ?></p><p>◷ <?= e((new DateTime($event['event_date']))->format('D, d M Y · H:i')) ?></p><div class="summary-price"><?= money($event['price']) ?> <small>/ ticket</small></div><p class="availability"><?= (int)$event['tickets_remaining'] ?> tickets remaining</p></section>
<form class="booking-form" method="post"><h2>Your details</h2><p class="form-help">We'll use these details for your booking reference.</p>
<?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
<label for="customer_name">Full name</label><input id="customer_name" name="customer_name" required maxlength="120" autocomplete="name" value="<?= e((string)($_POST['customer_name'] ?? '')) ?>" placeholder="e.g. Sibusiso Khumalo">
<label for="customer_email">Email address</label><input id="customer_email" type="email" name="customer_email" required maxlength="190" autocomplete="email" value="<?= e((string)($_POST['customer_email'] ?? '')) ?>" placeholder="you@example.com">
<label for="quantity">Number of tickets</label><select id="quantity" name="quantity"><?php for ($n = 1; $n <= min(10, (int)$event['tickets_remaining']); $n++): ?><option value="<?= $n ?>" <?= (int)($_POST['quantity'] ?? 1) === $n ? 'selected' : '' ?>><?= $n ?> ticket<?= $n > 1 ? 's' : '' ?></option><?php endfor; ?></select>
<div class="total-line"><span>Payment due at venue (demo)</span><strong id="total"><?= money($event['price']) ?></strong></div><button class="button button-primary full-button" type="submit" <?= (int)$event['tickets_remaining'] < 1 ? 'disabled' : '' ?>>Confirm booking <span>↗</span></button><small class="fine-print">Demo project only. No online payment is collected.</small></form></div></main>
<footer><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><span>© <?= date('Y') ?> Ticketly</span></footer>
<script>const quantity=document.querySelector('#quantity');const total=document.querySelector('#total');const price=<?= json_encode((float)$event['price']) ?>;function updateTotal(){total.textContent='R'+(price*Number(quantity.value)).toLocaleString('en-ZA',{minimumFractionDigits:2,maximumFractionDigits:2});}quantity.addEventListener('change',updateTotal);updateTotal();</script>
</body></html>
