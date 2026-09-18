<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$user = current_user();
$vehicleCount = $pdo->query('SELECT COUNT(*) AS c FROM vehicles')->fetch()['c'];
$activeCount = $pdo->query("SELECT COUNT(*) AS c FROM vehicles WHERE status = 'Active'")->fetch()['c'];
$runningCount = $pdo->query("SELECT COUNT(*) AS c FROM trips WHERE status = 'Ongoing'")->fetch()['c'];
$notifications = $pdo->query('SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5')->fetchAll();

$badgeFor = [
    'Info' => 'info',
    'Alert' => 'warning',
    'Delay' => 'warning',
    'Breakdown' => 'danger',
    'RouteChange' => 'secondary',
];
$iconFor = [
    'Info' => 'bi-info-circle-fill',
    'Alert' => 'bi-exclamation-triangle-fill',
    'Delay' => 'bi-clock-fill',
    'Breakdown' => 'bi-tools',
    'RouteChange' => 'bi-signpost-2-fill',
];

$page_title = 'Home';
$extra_head = '<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">'
    . '<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>';
require __DIR__ . '/includes/header.php';
?>

<div class="hero-banner mb-4">
    <h1 class="fw-bold mb-2"><i class="bi bi-bus-front-fill me-2"></i>DUET Vehicle Tracking System</h1>
    <p class="fs-5 mb-4">Track DUET buses, office cars, and ambulances live on the map — anytime, anywhere.</p>
    <?php if ($user): ?>
        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($user['role']) ?>/dashboard.php" class="btn btn-light btn-lg fw-semibold">
            <i class="bi bi-speedometer2 me-1"></i> Go to Dashboard
        </a>
    <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-light btn-lg fw-semibold me-2">Login</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline-light btn-lg fw-semibold">Register</a>
    <?php endif; ?>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card card-stat p-3 d-flex flex-row align-items-center gap-3">
            <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-truck"></i></div>
            <div>
                <h6 class="text-muted mb-0">Total Vehicles</h6>
                <h2 class="mb-0"><?= (int) $vehicleCount ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3 d-flex flex-row align-items-center gap-3">
            <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i></div>
            <div>
                <h6 class="text-muted mb-0">Active Now</h6>
                <h2 class="mb-0"><?= (int) $activeCount ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stat p-3 d-flex flex-row align-items-center gap-3">
            <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-signpost-split"></i></div>
            <div>
                <h6 class="text-muted mb-0">Running Trips</h6>
                <h2 class="mb-0"><?= (int) $runningCount ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="card card-stat p-3 mb-4">
    <div class="section-heading"><i class="bi bi-geo-alt-fill"></i><h5 class="mb-0">Live Vehicle Map</h5></div>
    <div id="liveMap"></div>
</div>

<div class="card card-stat p-3">
    <div class="section-heading"><i class="bi bi-bell-fill"></i><h5 class="mb-0">Latest Notifications</h5></div>
    <?php if (!$notifications): ?>
        <p class="text-muted mb-0">No notifications yet.</p>
    <?php else: ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($notifications as $n): ?>
                <?php $color = $badgeFor[$n['type']] ?? 'secondary'; ?>
                <li class="list-group-item d-flex align-items-start gap-3">
                    <div class="notification-icon bg-<?= $color ?> bg-opacity-10 text-<?= $color ?>">
                        <i class="bi <?= $iconFor[$n['type']] ?? 'bi-bell' ?>"></i>
                    </div>
                    <div>
                        <strong><?= htmlspecialchars($n['title']) ?></strong> — <?= htmlspecialchars($n['message']) ?>
                        <span class="text-muted small d-block"><?= htmlspecialchars($n['created_at']) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<script src="<?= BASE_URL ?>/assets/js/live-map.js"></script>
<script>initLiveMap('liveMap', '<?= BASE_URL ?>');</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
