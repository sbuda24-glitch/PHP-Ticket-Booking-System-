<?php
require_once __DIR__ . '/config.php';
$booking = $_SESSION['last_booking'] ?? null;
if (!$booking) redirect('index.php');
unset($_SESSION['last_booking']);
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Booking confirmed | Ticketly</title><link rel="stylesheet" href="style.css"></head>
<body><header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a></header><main class="confirmation-page"><div class="success-icon">✓</div><div class="eyebrow muted">IT'S OFFICIAL</div><h1>You're on <span>the list.</span></h1><p class="confirm-intro">Thanks, <?= e($booking['name']) ?>. Your demo booking has been recorded.</p><section class="ticket"><div class="ticket-top"><span>TICKETLY</span><span>BOOKING CONFIRMATION</span></div><h2><?= e($booking['event']) ?></h2><div class="ticket-info"><div><small>REFERENCE</small><strong><?= e($booking['reference']) ?></strong></div><div><small>TICKETS</small><strong><?= (int)$booking['quantity'] ?></strong></div><div><small>TOTAL</small><strong><?= money($booking['total']) ?></strong></div></div><div class="ticket-foot">Keep this reference for your records. No payment was processed.</div></section><a class="button button-primary" href="index.php">Back to events ↗</a></main><footer><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><span>© <?= date('Y') ?> Ticketly</span></footer></body></html>
