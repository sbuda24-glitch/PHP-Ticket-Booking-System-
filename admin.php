<?php
require_once __DIR__ . '/config.php';

if (isset($_GET['logout'])) { unset($_SESSION['admin_logged_in']); redirect('admin.php'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = (string)($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if (hash_equals(ADMIN_USERNAME, $username) && password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true); $_SESSION['admin_logged_in'] = true; redirect('admin.php');
    }
    $error = 'Invalid username or password.';
}
if (empty($_SESSION['admin_logged_in'])):
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin login | Ticketly</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><nav><a href="index.php">← Public site</a></nav></header><main class="login-page"><div class="eyebrow muted">RESTRICTED AREA</div><h1>Admin <span>access.</span></h1><p>Sign in to manage events and review bookings.</p><?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><form class="booking-form" method="post"><label>Username</label><input name="username" required autocomplete="username"><label>Password</label><input type="password" name="password" required autocomplete="current-password"><button class="button button-primary full-button" name="login" value="1">Sign in ↗</button><small class="fine-print">Demo credentials are documented in README.md.</small></form></main></body></html>
<?php
exit;
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_event'])) {
    $title = trim((string)($_POST['title'] ?? '')); $venue = trim((string)($_POST['venue'] ?? ''));
    $date = trim((string)($_POST['event_date'] ?? '')); $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $tickets = filter_input(INPUT_POST, 'total_tickets', FILTER_VALIDATE_INT); $description = trim((string)($_POST['description'] ?? ''));
    $parsedDate = DateTime::createFromFormat('Y-m-d\TH:i', $date);
    if ($title === '' || $venue === '' || !$parsedDate || !$price || $price < 0 || !$tickets || $tickets < 1 || $description === '') {
        $error = 'Please complete all event fields with valid values.';
    } else {
        $dateSql = $parsedDate->format('Y-m-d H:i:s');
        $stmt = db()->prepare('INSERT INTO events (title, venue, event_date, price, total_tickets, tickets_remaining, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->bind_param('sssdiis', $title, $venue, $dateSql, $price, $tickets, $tickets, $description);
        $stmt->execute();
        redirect('admin.php?added=1');
    }
}
$events = db()->query('SELECT id, title, venue, event_date, price, total_tickets, tickets_remaining FROM events ORDER BY event_date DESC')->fetch_all(MYSQLI_ASSOC);
$bookings = db()->query('SELECT b.reference_code, b.customer_name, b.customer_email, b.quantity, b.total_amount, b.booked_at, e.title AS event_title FROM bookings b JOIN events e ON e.id=b.event_id ORDER BY b.booked_at DESC LIMIT 100')->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin dashboard | Ticketly</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><nav><a href="index.php">Public site</a><a class="nav-button" href="admin.php?logout=1">Log out</a></nav></header><main class="admin-page"><div class="eyebrow muted">TICKETLY CONTROL ROOM</div><h1>Admin <span>dashboard.</span></h1><?php if (isset($_GET['added'])): ?><div class="success-banner">Event added successfully.</div><?php endif; ?><?php if ($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?>
<div class="admin-columns"><section class="panel"><h2>Add an event</h2><form method="post" class="admin-form"><label>Event title</label><input name="title" required maxlength="150"><label>Venue</label><input name="venue" required maxlength="150"><label>Date and time</label><input type="datetime-local" name="event_date" required><label>Ticket price (R)</label><input type="number" name="price" min="0" step="0.01" required><label>Ticket capacity</label><input type="number" name="total_tickets" min="1" required><label>Description</label><textarea name="description" required rows="3"></textarea><button class="button button-primary" name="add_event" value="1">Add event +</button></form></section>
<section class="panel"><h2>Events <span class="count"><?= count($events) ?></span></h2><div class="table-wrap"><table><thead><tr><th>Event</th><th>Date</th><th>Tickets left</th></tr></thead><tbody><?php foreach ($events as $ev): ?><tr><td><?= e($ev['title']) ?><small><?= e($ev['venue']) ?></small></td><td><?= e((new DateTime($ev['event_date']))->format('d M Y')) ?></td><td><?= (int)$ev['tickets_remaining'] ?>/<?= (int)$ev['total_tickets'] ?></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<section class="panel booking-list"><h2>Recent bookings <span class="count"><?= count($bookings) ?></span></h2><div class="table-wrap"><table><thead><tr><th>Reference</th><th>Customer</th><th>Event</th><th>Qty</th><th>Total</th></tr></thead><tbody><?php foreach ($bookings as $b): ?><tr><td><?= e($b['reference_code']) ?></td><td><?= e($b['customer_name']) ?><small><?= e($b['customer_email']) ?></small></td><td><?= e($b['event_title']) ?></td><td><?= (int)$b['quantity'] ?></td><td><?= money($b['total_amount']) ?></td></tr><?php endforeach; ?></tbody></table></div></section></main><footer><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><span>Admin dashboard · Demo project</span></footer></body></html>
