<?php
/**
 * Anggota (Members) Page
 * Displays foundation members dynamically from database
 * Accessed via: index.php?page=anggota
 * 
 * Database tables used:
 * - member_categories
 * - members
 * - member_stats
 * - member_page_settings
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
 * Get page setting value by key
 */
function getPageSetting(PDO $pdo, string $key, string $default = ''): string {
    try {
        $stmt = $pdo->prepare("SELECT setting_value FROM member_page_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? htmlspecialchars($result) : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Get featured leader (Pimpinan)
 */
function getFeaturedLeader(PDO $pdo): ?array {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, mc.name as category_name 
            FROM members m
            LEFT JOIN member_categories mc ON m.category_id = mc.id
            WHERE m.is_featured = 1 AND m.is_active = 1
            ORDER BY m.display_order ASC
            LIMIT 1
        ");
        $stmt->execute();
        return $stmt->fetch() ?: null;
    } catch (PDOException $e) {
        return null;
    }
}

/**
 * Get team members (non-featured)
 */
function getTeamMembers(PDO $pdo, int $categoryId = null): array {
    try {
        $sql = "
            SELECT m.*, mc.name as category_name 
            FROM members m
            LEFT JOIN member_categories mc ON m.category_id = mc.id
            WHERE m.is_featured = 0 AND m.is_active = 1
        ";
        
        $params = [];
        if ($categoryId) {
            $sql .= " AND m.category_id = ?";
            $params[] = $categoryId;
        }
        
        $sql .= " ORDER BY m.display_order ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Get statistics
 */
function getMemberStats(PDO $pdo): array {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM member_stats 
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
 * Get member photo with fallback
 */
function getMemberPhoto(string $photo = null): string {
    $default = 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop';
    
    if (empty($photo)) {
        return $default;
    }
    
    if (filter_var($photo, FILTER_VALIDATE_URL)) {
        return $photo;
    }
    
    if (file_exists($photo)) {
        return $photo;
    }
    
    return $default;
}

// ==========================================
// FETCH DATA
// ==========================================
$pageSettings = [];
$leader = null;
$members = [];
$stats = [];

if ($pdo) {
    // Page Settings
    $pageSettings = [
        'badge' => getPageSetting($pdo, 'page_badge', 'Tim Kami'),
        'title' => getPageSetting($pdo, 'page_title', 'Anggota Yayasan'),
        'description' => getPageSetting($pdo, 'page_description', 'Kenali orang-orang hebat di balik setiap program dan kegiatan yayasan kami.'),
        'leadership_title' => getPageSetting($pdo, 'leadership_title', 'Pimpinan Yayasan'),
        'team_title' => getPageSetting($pdo, 'team_title', 'Pengurus Inti'),
        'cta_title' => getPageSetting($pdo, 'cta_title', 'Bergabung Bersama Kami'),
        'cta_description' => getPageSetting($pdo, 'cta_description', 'Jadilah bagian dari keluarga besar yayasan.'),
        'cta_button_text' => getPageSetting($pdo, 'cta_button_text', 'Hubungi Kami'),
        'cta_button_url' => getPageSetting($pdo, 'cta_button_url', 'index.php?page=contact'),
    ];
    
    // Get Data
    $leader = getFeaturedLeader($pdo);
    $members = getTeamMembers($pdo);
    $stats = getMemberStats($pdo);
}
?>

<!-- Load Page Specific CSS -->
<link rel="stylesheet" href="template/anggota.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Anggota Section -->
<section class="anggota-section">
    <div class="container">
        <!-- Section Header -->
        <div class="section-header">
            <span class="section-badge">
                <i class="fas fa-users"></i>
                <?= $pageSettings['badge'] ?? 'Tim Kami' ?>
            </span>
            <h1><?= $pageSettings['title'] ?? 'Anggota Yayasan' ?></h1>
            <p><?= $pageSettings['description'] ?? '' ?></p>
        </div>

        <?php if ($leader): ?>
        <!-- Leadership Section -->
        <div class="leadership-section">
            <div class="leadership-header">
                <h2><?= $pageSettings['leadership_title'] ?? 'Pimpinan Yayasan' ?></h2>
            </div>

            <!-- Featured Leader Card -->
            <div class="leader-card-featured">
                <div class="leader-image-featured">
                    <img src="<?= getMemberPhoto($leader['photo']) ?>" 
                         alt="<?= htmlspecialchars($leader['position']) ?>">
                </div>
                <div class="leader-info-featured">
                    <span class="position"><?= htmlspecialchars($leader['position']) ?></span>
                    <h3><?= htmlspecialchars($leader['name']) ?></h3>
                    <?php if (!empty($leader['bio'])): ?>
                    <p class="bio"><?= htmlspecialchars($leader['bio']) ?></p>
                    <?php endif; ?>
                    
                    <div class="leader-social">
                        <?php if (!empty($leader['linkedin_url'])): ?>
                        <a href="<?= htmlspecialchars($leader['linkedin_url']) ?>" target="_blank" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($leader['email'])): ?>
                        <a href="mailto:<?= htmlspecialchars($leader['email']) ?>" aria-label="Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($leader['whatsapp'])): ?>
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $leader['whatsapp']) ?>" target="_blank" aria-label="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Divider -->
        <div class="section-divider">
            <div class="line"></div>
            <div class="icon"><i class="fas fa-star"></i></div>
            <div class="line"></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($members)): ?>
        <!-- Team Section -->
        <div class="team-section">
            <div class="team-header">
                <h2><?= $pageSettings['team_title'] ?? 'Pengurus Inti' ?></h2>
            </div>

            <div class="team-grid">
                <?php foreach ($members as $member): ?>
                <!-- Member Card -->
                <div class="member-card">
                    <div class="member-image">
                        <img src="<?= getMemberPhoto($member['photo']) ?>" 
                             alt="<?= htmlspecialchars($member['position']) ?>">
                        <div class="member-overlay">
                            <div class="member-overlay-social">
                                <?php if (!empty($member['linkedin_url'])): ?>
                                <a href="<?= htmlspecialchars($member['linkedin_url']) ?>" target="_blank">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($member['email'])): ?>
                                <a href="mailto:<?= htmlspecialchars($member['email']) ?>">
                                    <i class="fas fa-envelope"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="member-info">
                        <h3><?= htmlspecialchars($member['name']) ?></h3>
                        <span class="position"><?= htmlspecialchars($member['position']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($stats)): ?>
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="stats-grid">
                <?php foreach ($stats as $stat): ?>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="<?= htmlspecialchars($stat['icon']) ?>"></i>
                    </div>
                    <div class="stat-number"><?= htmlspecialchars($stat['number']) ?></div>
                    <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Join CTA -->
        <div class="join-cta">
            <h2><?= $pageSettings['cta_title'] ?? 'Bergabung Bersama Kami' ?></h2>
            <p><?= $pageSettings['cta_description'] ?? '' ?></p>
            <a href="<?= $pageSettings['cta_button_url'] ?? 'index.php?page=contact' ?>" class="btn-primary">
                <i class="fas fa-paper-plane"></i>
                <?= $pageSettings['cta_button_text'] ?? 'Hubungi Kami' ?>
            </a>
        </div>
    </div>
</section>
