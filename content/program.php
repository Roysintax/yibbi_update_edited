<?php
/**
 * Program Page Content
 * Displays campaign programs from database using PDO
 * Clean code structure with separated concerns
 */

// ============================================
// DATABASE QUERY
// ============================================
try {
    $pdo = new PDO("mysql:host=$_host;dbname=$_db;charset=utf8mb4", $_user, $_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch all active programs
    $stmt = $pdo->prepare("SELECT * FROM campaign_programs WHERE is_active = 1 ORDER BY order_position ASC");
    $stmt->execute();
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $programs = [];
}

// ============================================
// HELPER FUNCTIONS
// ============================================

/**
 * Format currency to Indonesian Rupiah
 */
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

/**
 * Get program image URL with fallback
 */
function getProgramImage($image, $default = 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=400&fit=crop') {
    return !empty($image) ? htmlspecialchars($image) : $default;
}

/**
 * Render a program card (vertical style)
 */
function renderProgramCard($program, $size = 'vertical') {
    $image = getProgramImage($program['image']);
    $title = htmlspecialchars($program['title']);
    $category = htmlspecialchars($program['category'] ?? 'Program');
    $amount = formatRupiah($program['amount_raised']);
    $link = 'index.php?page=detail_program&id=' . $program['id'];
    
    ob_start();
    ?>
    <article class="program-card card-<?php echo $size; ?>">
        <div class="card-image">
            <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
            <div class="card-overlay">
                <div class="overlay-content">
                    <span class="overlay-label">Dana Terkumpul</span>
                    <span class="overlay-amount"><?php echo $amount; ?></span>
                </div>
            </div>
        </div>
        <div class="card-content">
            <div class="card-category"><?php echo $category; ?></div>
            <h3><?php echo $title; ?></h3>
            <div class="card-footer">
                <div class="card-stats">
                    <i class="fas fa-heart"></i>
                    <span>Donatur</span>
                </div>
                <a href="<?php echo $link; ?>" class="card-btn">Donasi</a>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

/**
 * Render a horizontal program card (small style)
 */
function renderSmallCard($program) {
    $image = getProgramImage($program['image']);
    $title = htmlspecialchars($program['title']);
    $category = htmlspecialchars($program['category'] ?? 'Program');
    $amount = formatRupiah($program['amount_raised']);
    $link = 'index.php?page=detail_program&id=' . $program['id'];
    
    ob_start();
    ?>
    <article class="program-card card-small">
        <div class="card-horizontal">
            <div class="card-image">
                <img src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
                <div class="card-overlay">
                    <div class="overlay-content">
                        <span class="overlay-label">Dana Terkumpul</span>
                        <span class="overlay-amount"><?php echo $amount; ?></span>
                    </div>
                </div>
            </div>
            <div class="card-content">
                <div class="card-category"><?php echo $category; ?></div>
                <h3><?php echo $title; ?></h3>
                <a href="<?php echo $link; ?>" class="card-btn-small">Donasi <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}

// Get featured program (first one) and remaining programs
$featured = !empty($programs) ? array_shift($programs) : null;
?>

<!-- Load CSS -->
<link rel="stylesheet" href="template/program.css">

<!-- Program Section -->
<section class="program-section">
    <div class="container">
        
        <!-- Section Header -->
        <div class="section-header">
            <span class="section-label">Berbagi Kebaikan</span>
            <h1>Program Kami</h1>
            <p>Bergabunglah dalam berbagai program sosial keagamaan kami untuk membantu sesama yang membutuhkan.</p>
        </div>

        <?php if ($featured): ?>
        <!-- Featured Program -->
        <div class="featured-program">
            <div class="featured-image">
                <img src="<?php echo getProgramImage($featured['image']); ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                <div class="featured-overlay">
                    <div class="donation-info">
                        <span class="donation-label">Dana Terkumpul</span>
                        <span class="donation-amount"><?php echo formatRupiah($featured['amount_raised']); ?></span>
                    </div>
                </div>
                <span class="featured-badge">Program Unggulan</span>
            </div>
            <div class="featured-content">
                <div class="program-category"><?php echo htmlspecialchars($featured['category'] ?? 'Program'); ?></div>
                <h2><?php echo htmlspecialchars($featured['title']); ?></h2>
                <p>Program unggulan kami untuk membantu masyarakat yang membutuhkan.</p>
                <div class="program-meta">
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span>Penerima Manfaat</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-calendar"></i>
                        <span>Aktif</span>
                    </div>
                </div>
                <a href="index.php?page=detail_program&id=<?php echo $featured['id']; ?>" class="btn-donate">
                    <span>Donasi Sekarang</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <?php if (count($programs) >= 3): ?>
        <!-- Mixed Layout Grid -->
        <div class="program-mixed-grid">
            <?php echo renderProgramCard($programs[0], 'large'); ?>
            <?php echo renderSmallCard($programs[1]); ?>
            <?php echo renderSmallCard($programs[2]); ?>
        </div>
        <?php endif; ?>

        <?php if (count($programs) >= 6): ?>
        <!-- Bottom Row - 3 Equal Cards -->
        <div class="program-grid-bottom">
            <?php 
            for ($i = 3; $i < min(6, count($programs)); $i++): 
                echo renderProgramCard($programs[$i], 'vertical');
            endfor; 
            ?>
        </div>
        <?php endif; ?>

        <?php if (count($programs) >= 9): ?>
        <!-- Reversed Mixed Layout Grid -->
        <div class="program-mixed-grid reversed">
            <?php echo renderSmallCard($programs[6]); ?>
            <?php echo renderSmallCard($programs[7]); ?>
            <?php echo renderProgramCard($programs[8], 'large'); ?>
        </div>
        <?php endif; ?>

        <?php if (empty($programs) && !$featured): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Belum Ada Program</h3>
            <p>Program akan segera ditampilkan. Silakan kunjungi kembali nanti.</p>
        </div>
        <?php endif; ?>

        <!-- Call to Action -->
        <div class="cta-section">
            <div class="cta-content">
                <h3>Ingin Mengajukan Program Baru?</h3>
                <p>Kami terbuka untuk kolaborasi dan program-program baru yang bermanfaat bagi masyarakat.</p>
            </div>
            <a href="https://wa.me/<?php echo htmlspecialchars($site_settings['whatsapp_number'] ?? '6281234567890'); ?>?text=Halo,%20saya%20ingin%20mengajukan%20program%20baru"
                class="btn-cta" target="_blank">
                <i class="fab fa-whatsapp"></i>
                <span>Hubungi Kami</span>
            </a>
        </div>
    </div>
</section>

<style>
/* Empty State Style */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 16px;
    margin: 40px 0;
}
.empty-state i {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 20px;
}
.empty-state h3 {
    color: #333;
    margin-bottom: 10px;
}
.empty-state p {
    color: #666;
}
</style>
