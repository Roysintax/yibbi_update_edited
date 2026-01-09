<?php
/**
 * Events Page - YouTube-Inspired Layout
 * Displays events dynamically from database
 * Accessed via: index.php?page=events
 * 
 * Database tables used:
 * - events
 * - events_header
 */

// ==========================================
// DATABASE CONNECTION (PDO)
// ==========================================
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=yayasan_cms;charset=utf8mb4",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    $pdo = null;
}

// ==========================================
// HELPER FUNCTIONS
// ==========================================

/**
 * Get events header settings
 */
function getEventsHeader(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("SELECT * FROM events_header WHERE id = 1 LIMIT 1");
        $stmt->execute();
        return $stmt->fetch() ?: [];
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get featured event
 */
function getFeaturedEvent(PDO $pdo): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            WHERE is_featured = 1 AND is_active = 1 
            ORDER BY event_date DESC 
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get all active events
 */
function getAllEvents(PDO $pdo, int $excludeId = 0): array {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            WHERE is_active = 1 AND id != ?
            ORDER BY event_date DESC
        ");
        $stmt->execute([$excludeId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get upcoming event with countdown
 */
function getCountdownEvent(PDO $pdo): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM events 
            WHERE countdown_enabled = 1 
            AND is_active = 1 
            AND countdown_date >= NOW()
            ORDER BY countdown_date ASC 
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get event image with fallback
 */
function getEventImage(string $image = null): string {
    $defaults = [
        'https://images.unsplash.com/photo-1540575467063-178a50da2fd8?w=800&h=450&fit=crop',
        'https://images.unsplash.com/photo-1523580494863-6f3031224c94?w=800&h=450&fit=crop',
        'https://images.unsplash.com/photo-1511578314322-379afb476865?w=800&h=450&fit=crop',
    ];
    
    if (empty($image)) {
        return $defaults[array_rand($defaults)];
    }
    
    if (filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }
    
    if (file_exists($image)) {
        return $image;
    }
    
    return $defaults[array_rand($defaults)];
}

/**
 * Format date to Indonesian
 */
function formatDateID(string $date): array {
    $months = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
    ];
    
    $monthsFull = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($date);
    return [
        'day' => date('j', $timestamp),
        'month' => $months[(int)date('n', $timestamp)],
        'month_full' => $monthsFull[(int)date('n', $timestamp)],
        'year' => date('Y', $timestamp),
        'full' => date('j', $timestamp) . ' ' . $monthsFull[(int)date('n', $timestamp)] . ' ' . date('Y', $timestamp)
    ];
}

/**
 * Format time
 */
function formatTime(string $time): string {
    return date('H:i', strtotime($time)) . ' WIB';
}

/**
 * Check if event is upcoming
 */
function isUpcoming(string $date): bool {
    return strtotime($date) >= strtotime('today');
}

// ==========================================
// FETCH DATA
// ==========================================
$header = [];
$featured = null;
$events = [];
$countdown = null;

if ($pdo) {
    $header = getEventsHeader($pdo);
    $featured = getFeaturedEvent($pdo);
    $events = getAllEvents($pdo, $featured['id'] ?? 0);
    $countdown = getCountdownEvent($pdo);
}
?>

<!-- Load Page Specific CSS -->
<link rel="stylesheet" href="template/events.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Events Section -->
<section class="events-section">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <span class="section-badge">
                <i class="fas fa-calendar-alt"></i>
                <?= htmlspecialchars($header['subtitle'] ?? 'Acara yang akan datang') ?>
            </span>
            <h1><?= htmlspecialchars($header['title'] ?? 'Jadwal Acara & Kegiatan') ?></h1>
            <p>Ikuti berbagai kegiatan dan acara yang kami selenggarakan untuk meningkatkan keimanan dan kebersamaan umat.</p>
        </div>

        <?php if ($featured): ?>
        <!-- Featured Event (YouTube Hero) -->
        <div class="featured-event">
            <div class="featured-card">
                <div class="featured-thumbnail">
                    <img src="<?= getEventImage($featured['image']) ?>" alt="<?= htmlspecialchars($featured['title']) ?>">
                    <?php if ($featured['countdown_enabled']): ?>
                    <span class="featured-badge">
                        <i class="fas fa-circle"></i>
                        SEGERA
                    </span>
                    <?php endif; ?>
                    <span class="featured-duration">
                        <?= formatTime($featured['event_time'] ?? '00:00') ?>
                    </span>
                </div>
                <div class="featured-content">
                    <div class="featured-meta">
                        <span class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <?= formatDateID($featured['event_date'])['full'] ?>
                        </span>
                        <span class="meta-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($featured['location'] ?? 'TBA') ?>
                        </span>
                    </div>
                    <h2 class="featured-title"><?= htmlspecialchars($featured['title']) ?></h2>
                    <p class="featured-desc"><?= htmlspecialchars($featured['description'] ?? '') ?></p>
                    <div class="featured-actions">
                        <a href="#" class="btn-watch">
                            <i class="fas fa-info-circle"></i>
                            Lihat Detail
                        </a>
                        <button class="btn-share" title="Bagikan">
                            <i class="fas fa-share"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Filter Chips -->
        <div class="filter-container">
            <div class="filter-chips">
                <button class="filter-chip active" data-filter="all">Semua</button>
                <button class="filter-chip" data-filter="upcoming">Akan Datang</button>
                <button class="filter-chip" data-filter="past">Sudah Berlalu</button>
                <button class="filter-chip" data-filter="featured">Unggulan</button>
            </div>
        </div>

        <!-- Events Grid -->
        <?php if (!empty($events)): ?>
        <div class="events-grid">
            <?php foreach ($events as $event): 
                $dateInfo = formatDateID($event['event_date']);
                $upcoming = isUpcoming($event['event_date']);
            ?>
            <!-- Event Card -->
            <div class="event-card" 
                 data-status="<?= $upcoming ? 'upcoming' : 'past' ?>"
                 data-featured="<?= $event['is_featured'] ? 'true' : 'false' ?>">
                <div class="event-thumbnail">
                    <img src="<?= getEventImage($event['image']) ?>" alt="<?= htmlspecialchars($event['title']) ?>">
                    
                    <!-- Date Badge -->
                    <div class="event-date-badge">
                        <span class="day"><?= $dateInfo['day'] ?></span>
                        <span class="month"><?= $dateInfo['month'] ?></span>
                    </div>
                    
                    <!-- Duration/Time Badge -->
                    <span class="duration-badge"><?= formatTime($event['event_time'] ?? '00:00') ?></span>
                    
                    <?php if ($event['countdown_enabled'] && $upcoming): ?>
                    <span class="live-badge">LIVE</span>
                    <?php endif; ?>
                    
                    <!-- Hover Overlay -->
                    <div class="thumbnail-overlay">
                        <div class="play-button">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </div>
                </div>
                
                <div class="event-info">
                    <div class="event-avatar">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <div class="event-details">
                        <h3 class="event-title"><?= htmlspecialchars($event['title']) ?></h3>
                        <p class="event-meta">
                            <?= $dateInfo['full'] ?>
                        </p>
                        <p class="event-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($event['location'] ?? 'TBA') ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <!-- No Events -->
        <div class="no-events">
            <div class="no-events-icon">
                <i class="fas fa-calendar-xmark"></i>
            </div>
            <h3>Belum Ada Acara</h3>
            <p>Saat ini belum ada acara yang dijadwalkan. Pantau terus halaman ini untuk update terbaru.</p>
        </div>
        <?php endif; ?>

        <?php if ($countdown): ?>
        <!-- Countdown Banner -->
        <div class="countdown-banner">
            <div class="countdown-info">
                <h3><?= htmlspecialchars($countdown['title']) ?></h3>
                <p><?= formatDateID($countdown['event_date'])['full'] ?> • <?= htmlspecialchars($countdown['location'] ?? 'TBA') ?></p>
            </div>
            <div class="countdown-timer" data-target="<?= $countdown['countdown_date'] ?>">
                <div class="countdown-item">
                    <span class="countdown-number" id="countdown-days">00</span>
                    <span class="countdown-label">Hari</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="countdown-hours">00</span>
                    <span class="countdown-label">Jam</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="countdown-minutes">00</span>
                    <span class="countdown-label">Menit</span>
                </div>
                <div class="countdown-item">
                    <span class="countdown-number" id="countdown-seconds">00</span>
                    <span class="countdown-label">Detik</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
// Filter functionality
const filterChips = document.querySelectorAll('.filter-chip');
const eventCards = document.querySelectorAll('.event-card');

filterChips.forEach(chip => {
    chip.addEventListener('click', () => {
        filterChips.forEach(c => c.classList.remove('active'));
        chip.classList.add('active');
        
        const filter = chip.dataset.filter;
        
        eventCards.forEach(card => {
            let show = false;
            
            if (filter === 'all') {
                show = true;
            } else if (filter === 'upcoming') {
                show = card.dataset.status === 'upcoming';
            } else if (filter === 'past') {
                show = card.dataset.status === 'past';
            } else if (filter === 'featured') {
                show = card.dataset.featured === 'true';
            }
            
            if (show) {
                card.style.display = 'block';
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 10);
            } else {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.display = 'none';
                }, 300);
            }
        });
    });
});

// Countdown functionality
const countdownTimer = document.querySelector('.countdown-timer');
if (countdownTimer) {
    const targetDate = new Date(countdownTimer.dataset.target).getTime();
    
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = targetDate - now;
        
        if (distance < 0) {
            document.getElementById('countdown-days').textContent = '00';
            document.getElementById('countdown-hours').textContent = '00';
            document.getElementById('countdown-minutes').textContent = '00';
            document.getElementById('countdown-seconds').textContent = '00';
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        document.getElementById('countdown-days').textContent = String(days).padStart(2, '0');
        document.getElementById('countdown-hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('countdown-minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('countdown-seconds').textContent = String(seconds).padStart(2, '0');
    }
    
    updateCountdown();
    setInterval(updateCountdown, 1000);
}
</script>
