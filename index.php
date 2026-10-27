<?php
require_once __DIR__ . '/config.php';

$search = trim((string)($_GET['q'] ?? ''));
$sql = "SELECT id, title, venue, event_date, price, tickets_remaining, description FROM events WHERE event_date >= NOW()";
$params = [];
$types = '';
if ($search !== '') {
    $sql .= " AND (title LIKE ? OR venue LIKE ?)";
    $like = '%' . $search . '%';
    $params = [$like, $like];
    $types = 'ss';
}
$sql .= " ORDER BY event_date ASC";
$stmt = db()->prepare($sql);
if ($params) $stmt->bind_param($types, ...$params);
$stmt->execute();
$events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Ticketly | Find your next experience</title><link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar"><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><nav><a href="#events">Explore events</a><a class="nav-button" href="admin.php">Admin</a></nav></header>
<main>
<section class="hero">
  <div class="eyebrow"><span class="pulse"></span> YOUR NEXT GREAT MEMORY STARTS HERE</div>
  <h1>Make plans.<br><span>Make memories.</span></h1>
  <p>Discover experiences worth showing up for. Find your event, reserve your tickets, and let the good times begin.</p>
  <a class="button button-primary" href="#events">Explore events <span>↘</span></a>
  <div class="hero-orb orb-one"></div><div class="hero-orb orb-two"></div>
  <div class="hero-stamp">LIVE<br>YOUR<br>WAY <span>✳</span></div>
</section>
<section class="events-section" id="events">
  <div class="section-heading"><div><div class="eyebrow muted">THE GOOD STUFF</div><h2>Upcoming <span>events</span></h2></div>
  <form class="search-form" method="get"><input name="q" value="<?= e($search) ?>" placeholder="Search events or venues..." aria-label="Search events"><button type="submit">Search</button></form></div>
  <?php if (!$events): ?><div class="empty-state"><h3>No events found</h3><p>Try another search, or check back soon for new events.</p><a href="index.php">Clear search</a></div>
  <?php else: ?><div class="event-grid">
    <?php foreach ($events as $i => $event): $date = new DateTime($event['event_date']); ?>
    <article class="event-card">
      <div class="event-art art-<?= $i % 3 ?>"><span class="art-tag"><?= ['MUSIC & CULTURE','TECH & INNOVATION','COMEDY & ENTERTAINMENT'][$i % 3] ?></span><span class="art-symbol"><?= ['✳','⌘','☻'][$i % 3] ?></span><span class="art-number">0<?= $i + 1 ?></span></div>
      <div class="event-content"><div class="event-date"><?= e($date->format('D, d M Y')) ?> <span>•</span> <?= e($date->format('H:i')) ?></div><h3><?= e($event['title']) ?></h3><p class="venue">⌖ <?= e($event['venue']) ?></p><p class="description"><?= e($event['description']) ?></p>
      <div class="event-bottom"><div><strong><?= money($event['price']) ?></strong><small>per ticket · <?= (int)$event['tickets_remaining'] ?> left</small></div>
      <?php if ((int)$event['tickets_remaining'] > 0): ?><a class="button button-dark" href="book.php?event=<?= (int)$event['id'] ?>">Book tickets <span>↗</span></a><?php else: ?><span class="sold-out">Sold out</span><?php endif; ?></div></div>
    </article>
    <?php endforeach; ?>
  </div><?php endif; ?>
</section>
<section class="bottom-cta"><div><div class="eyebrow">GOOD TIMES ARE CALLING</div><h2>Bring your people.<br><span>We'll save your spot.</span></h2></div><a class="button button-primary" href="#events">Find an event ↗</a></section>
</main>
<footer><a class="brand" href="index.php"><span class="brand-mark">T</span> ticketly<span class="dot">.</span></a><span>Built for experiences. Made for memories.</span><span>© <?= date('Y') ?> Ticketly</span></footer>
</body></html>
