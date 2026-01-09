<?php
/**
 * Galeri (Gallery) Page
 * Displays gallery items dynamically from database
 * Accessed via: index.php?page=galeri
 * 
 * Database tables used:
 * - gallery_categories
 * - gallery_items
 * - gallery_settings
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
 * Get gallery setting value by key
 */
function getGallerySetting(PDO $pdo, string $key, string $default = ''): string {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM gallery_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? htmlspecialchars($result) : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Get all gallery categories
 */
function getGalleryCategories(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM gallery_categories 
            WHERE is_active = 1 
            ORDER BY display_order ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get all gallery items with category info
 */
function getGalleryItems(PDO $pdo, ?int $categoryId = null): array {
    try {
        $sql = "
            SELECT g.*, gc.name as category_name, gc.slug as category_slug 
            FROM gallery_items g
            LEFT JOIN gallery_categories gc ON g.category_id = gc.id
            WHERE g.is_active = 1
        ";
        
        $params = [];
        if ($categoryId) {
            $sql .= " AND g.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY g.event_date DESC, g.display_order ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get gallery image with fallback
 */
function getGalleryImage(string $image = null, string $title = ''): string {
    $defaults = [
        'https://images.unsplash.com/photo-1577896851231-70ef18881754?w=600&h=600&fit=crop',
        'https://images.unsplash.com/photo-1593113598332-cd288d649433?w=600&h=600&fit=crop',
        'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=600&fit=crop',
        'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=600&h=600&fit=crop',
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
 * Format date to Indonesian format
 */
function formatDateID(string $date): string {
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    return "$day $month $year";
}

// ==========================================
// FETCH DATA
// ==========================================
$pageSettings = [];
$categories = [];
$items = [];

if ($pdo) {
    // Page Settings
    $pageSettings = [
        'badge' => getGallerySetting($pdo, 'page_badge', 'Dokumentasi'),
        'title' => getGallerySetting($pdo, 'page_title', 'Galeri Kegiatan'),
        'description' => getGallerySetting($pdo, 'page_description', 'Kumpulan momen berharga dari berbagai kegiatan dan program yayasan.'),
        'stat_photos' => getGallerySetting($pdo, 'stat_photos', '150+'),
        'stat_photos_label' => getGallerySetting($pdo, 'stat_photos_label', 'Dokumentasi Foto'),
        'stat_events' => getGallerySetting($pdo, 'stat_events', '50+'),
        'stat_events_label' => getGallerySetting($pdo, 'stat_events_label', 'Kegiatan Terdokumentasi'),
        'stat_categories' => getGallerySetting($pdo, 'stat_categories', '4'),
        'stat_categories_label' => getGallerySetting($pdo, 'stat_categories_label', 'Kategori Kegiatan'),
        'stat_year' => getGallerySetting($pdo, 'stat_year', date('Y')),
        'stat_year_label' => getGallerySetting($pdo, 'stat_year_label', 'Tahun Aktif'),
    ];
    
    // Get Data
    $categories = getGalleryCategories($pdo);
    $items = getGalleryItems($pdo);
}
?>

<!-- Load Page Specific CSS -->
<link rel="stylesheet" href="template/galeri.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Galeri Section -->
<section class="galeri-section">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <span class="section-badge">
                <i class="fas fa-images"></i>
                <?= $pageSettings['badge'] ?? 'Dokumentasi' ?>
            </span>
            <h1><?= $pageSettings['title'] ?? 'Galeri Kegiatan' ?></h1>
            <p><?= $pageSettings['description'] ?? '' ?></p>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-container">
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">Semua</button>
                <?php foreach ($categories as $category): ?>
                <button class="filter-tab" data-filter="<?= htmlspecialchars($category['slug']) ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="gallery-grid">
            <?php foreach ($items as $item): ?>
            <!-- Gallery Item -->
            <div class="gallery-item" data-category="<?= htmlspecialchars($item['category_slug'] ?? 'other') ?>">
                <img src="<?= getGalleryImage($item['image'], $item['title']) ?>" 
                     alt="<?= htmlspecialchars($item['title']) ?>">
                <div class="gallery-overlay">
                    <div class="gallery-info">
                        <span class="gallery-category"><?= htmlspecialchars($item['category_name'] ?? 'Lainnya') ?></span>
                        <h3 class="gallery-title"><?= htmlspecialchars($item['title']) ?></h3>
                        <span class="gallery-date">
                            <?= $item['event_date'] ? formatDateID($item['event_date']) : '' ?>
                        </span>
                    </div>
                </div>
                <div class="gallery-zoom">
                    <i class="fas fa-expand"></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Gallery Stats -->
        <div class="gallery-stats">
            <div class="stat-item">
                <div class="stat-number"><?= $pageSettings['stat_photos'] ?></div>
                <div class="stat-label"><?= $pageSettings['stat_photos_label'] ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $pageSettings['stat_events'] ?></div>
                <div class="stat-label"><?= $pageSettings['stat_events_label'] ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $pageSettings['stat_categories'] ?></div>
                <div class="stat-label"><?= $pageSettings['stat_categories_label'] ?></div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?= $pageSettings['stat_year'] ?></div>
                <div class="stat-label"><?= $pageSettings['stat_year_label'] ?></div>
            </div>
        </div>
    </div>
</section>

<!-- Lightbox Modal -->
<div class="lightbox" id="lightbox">
    <div class="lightbox-content">
        <button class="lightbox-close" id="lightboxClose">
            <i class="fas fa-times"></i>
        </button>
        <button class="lightbox-nav lightbox-prev" id="lightboxPrev">
            <i class="fas fa-chevron-left"></i>
        </button>
        <img src="" alt="" id="lightboxImage">
        <button class="lightbox-nav lightbox-next" id="lightboxNext">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="lightbox-caption">
            <h3 id="lightboxTitle"></h3>
            <p id="lightboxDate"></p>
        </div>
    </div>
</div>

<script>
    // Filter functionality
    const filterTabs = document.querySelectorAll('.filter-tab');
    const galleryItems = document.querySelectorAll('.gallery-item');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            filterTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            tab.classList.add('active');

            const filter = tab.dataset.filter;

            galleryItems.forEach(item => {
                if (filter === 'all' || item.dataset.category === filter) {
                    item.style.display = 'block';
                    setTimeout(() => {
                        item.style.opacity = '1';
                        item.style.transform = 'scale(1)';
                    }, 10);
                } else {
                    item.style.opacity = '0';
                    item.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        item.style.display = 'none';
                    }, 300);
                }
            });
        });
    });

    // Lightbox functionality
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightboxImage');
    const lightboxTitle = document.getElementById('lightboxTitle');
    const lightboxDate = document.getElementById('lightboxDate');
    const lightboxClose = document.getElementById('lightboxClose');
    const lightboxPrev = document.getElementById('lightboxPrev');
    const lightboxNext = document.getElementById('lightboxNext');

    let currentIndex = 0;
    let visibleItems = [];

    function updateVisibleItems() {
        visibleItems = Array.from(galleryItems).filter(item => 
            item.style.display !== 'none'
        );
    }

    function openLightbox(index) {
        updateVisibleItems();
        currentIndex = index;
        const item = visibleItems[currentIndex];
        const img = item.querySelector('img');
        const title = item.querySelector('.gallery-title').textContent;
        const date = item.querySelector('.gallery-date').textContent;

        lightboxImage.src = img.src;
        lightboxTitle.textContent = title;
        lightboxDate.textContent = date;
        lightbox.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        document.body.style.overflow = '';
    }

    function navigateLightbox(direction) {
        updateVisibleItems();
        currentIndex += direction;
        if (currentIndex >= visibleItems.length) currentIndex = 0;
        if (currentIndex < 0) currentIndex = visibleItems.length - 1;

        const item = visibleItems[currentIndex];
        const img = item.querySelector('img');
        const title = item.querySelector('.gallery-title').textContent;
        const date = item.querySelector('.gallery-date').textContent;

        lightboxImage.style.opacity = '0';
        setTimeout(() => {
            lightboxImage.src = img.src;
            lightboxTitle.textContent = title;
            lightboxDate.textContent = date;
            lightboxImage.style.opacity = '1';
        }, 200);
    }

    // Event listeners
    galleryItems.forEach((item, index) => {
        item.addEventListener('click', () => {
            updateVisibleItems();
            const visibleIndex = visibleItems.indexOf(item);
            openLightbox(visibleIndex);
        });
    });

    lightboxClose.addEventListener('click', closeLightbox);
    lightboxPrev.addEventListener('click', () => navigateLightbox(-1));
    lightboxNext.addEventListener('click', () => navigateLightbox(1));

    // Close on backdrop click
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            closeLightbox();
        }
    });

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('active')) return;
        
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowLeft') navigateLightbox(-1);
        if (e.key === 'ArrowRight') navigateLightbox(1);
    });
</script>
