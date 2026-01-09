<?php
/**
 * Detail Program Page
 * Menampilkan detail program dari database campaign_programs
 * Diakses via: index.php?page=detail_program&id=X
 */

// Get program ID from URL
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// PDO Connection
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=yayasan_cms;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Database connection failed");
}

// Fetch program data
$program = null;
if ($program_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM campaign_programs WHERE id = ? AND is_active = 1");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
}

// If no program found, show fallback
if (!$program) {
    $program = [
        'id' => 0,
        'image' => 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=1920&h=1080&fit=crop',
        'category' => 'Program',
        'title' => 'Program Tidak Ditemukan',
        'description' => 'Program yang Anda cari tidak ditemukan atau sudah tidak aktif.',
        'amount_raised' => 0,
        'goal_amount' => 100000,
        'beneficiaries_count' => 0,
        'donators_count' => 0,
        'created_at' => date('Y-m-d H:i:s')
    ];
}

// Fetch related programs (excluding current)
$stmt_related = $pdo->prepare("
    SELECT * FROM campaign_programs 
    WHERE is_active = 1 AND id != ? 
    ORDER BY order_position ASC, created_at DESC 
    LIMIT 3
");
$stmt_related->execute([$program_id]);
$related_programs = $stmt_related->fetchAll(PDO::FETCH_ASSOC);

// Helper functions
function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

function getProgressPercent($raised, $goal) {
    if ($goal <= 0) return 0;
    return min(100, round(($raised / $goal) * 100));
}

function getProgramImage($image) {
    // Default fallback image
    $default = 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=800&h=500&fit=crop';
    
    if (empty($image)) {
        return $default;
    }
    
    // Check if absolute URL
    if (filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }
    
    // Check if file exists in local uploads
    $local_path = $image;
    if (file_exists($local_path)) {
        return $local_path;
    }
    
    return $image;
}

function formatDate($date) {
    $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $timestamp = strtotime($date);
    return date('d', $timestamp) . ' ' . $months[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
}

// Calculate values
$progress_percent = getProgressPercent($program['amount_raised'], $program['goal_amount']);
$hero_image = getProgramImage($program['image']);

// Prepare Gallery Images (From DB or Fallback)
$gallery_items = [];

// Add main image first
$gallery_items[] = [
    'src' => $hero_image,
    'caption' => 'Kegiatan Program'
];

// Parse gallery_images from DB if exists (assuming comma separated or JSON)
if (!empty($program['gallery_images'])) {
    // Implement parsing logic here if needed later
} else {
    // Fallback static images for visuals if no gallery data
    $defaults = [
        ['src' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=400&h=300&fit=crop', 'caption' => 'Penyaluran Bantuan'],
        ['src' => 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=400&h=300&fit=crop', 'caption' => 'Penerima Manfaat'],
        ['src' => 'https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=400&h=300&fit=crop', 'caption' => 'Kegiatan Sosial']
    ];
    $gallery_items = array_merge($gallery_items, $defaults);
}
?>

<!-- CSS untuk halaman detail -->
<link rel="stylesheet" href="template/detail.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

<!-- Hero Article Header -->
<header class="article-hero">
    <div class="hero-overlay"></div>
    <img src="<?= htmlspecialchars($hero_image) ?>" alt="<?= htmlspecialchars($program['title']) ?>" class="hero-image">
    <div class="hero-content">
        <div class="article-meta-top">
            <span class="category-badge"><?= htmlspecialchars($program['category'] ?? 'Program') ?></span>
            <span class="reading-time"><i class="fas fa-clock"></i> 5 min read</span>
        </div>
        <h1 class="article-title"><?= htmlspecialchars($program['title']) ?></h1>
        <p class="article-subtitle">
            <?= htmlspecialchars($program['description'] ?? 'Program ini bertujuan untuk membantu masyarakat yang membutuhkan.') ?>
        </p>
        <div class="article-author">
            <img src="assets/images/logo.png" alt="Yayasan" class="author-avatar" onerror="this.src='https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop'">
            <div class="author-info">
                <span class="author-name">Tim Yayasan Y-ibbi</span>
                <span class="publish-date"><?= formatDate($program['created_at']) ?></span>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <i class="fas fa-chevron-down"></i>
    </div>
</header>

<!-- Article Content - 2 Column Layout -->
<article class="article-container">
    <!-- Main Article Content -->
    <main class="article-main">
        <!-- Lead Paragraph -->
        <p class="lead-paragraph">
            <?= htmlspecialchars($program['description'] ?? 'Program unggulan kami untuk membantu masyarakat yang membutuhkan.') ?>
            Dengan dukungan para donatur, kami berkomitmen untuk menyalurkan bantuan secara tepat sasaran dan transparan.
        </p>

        <!-- Quote Block -->
        <blockquote class="article-quote">
            <p>"Setiap kebaikan yang kita lakukan akan kembali kepada kita berlipat ganda."</p>
            <cite>— Pengurus Yayasan Y-ibbi</cite>
        </blockquote>

        <!-- Section: Latar Belakang -->
        <section class="content-section">
            <h2>Latar Belakang Program</h2>
            <p>
                Indonesia memiliki jutaan masyarakat yang membutuhkan bantuan dalam berbagai aspek kehidupan. 
                Berdasarkan data yang ada, banyak keluarga kurang mampu yang kesulitan memenuhi kebutuhan dasar mereka.
                Program ini hadir sebagai bentuk kepedulian kami terhadap sesama.
            </p>

            <figure class="article-figure">
                <img src="<?= htmlspecialchars($hero_image) ?>" alt="<?= htmlspecialchars($program['title']) ?>">
                <figcaption>Dokumentasi kegiatan program <?= htmlspecialchars($program['category'] ?? 'sosial') ?>.</figcaption>
            </figure>

            <p>
                Yayasan Y-ibbi memahami pentingnya memberikan bantuan yang tepat sasaran dan berkelanjutan.
                Oleh karena itu, kami hadir dengan program yang komprehensif dan terukur untuk memastikan
                setiap bantuan dapat memberikan dampak positif yang nyata.
            </p>
        </section>

        <!-- Stats Section - Dampak Program -->
        <section class="impact-stats">
            <h3>Dampak Program Kami</h3>
            <div class="stats-grid">
                <div class="impact-item">
                    <span class="impact-number"><?= $progress_percent ?>%</span>
                    <span class="impact-label">Tercapai</span>
                </div>
                <div class="impact-item">
                    <span class="impact-number"><?= number_format($program['amount_raised']/1000000, 1) ?>Jt</span>
                    <span class="impact-label">Terkumpul</span>
                </div>
                <div class="impact-item">
                    <span class="impact-number"><?= number_format($program['goal_amount']/1000000, 0) ?>Jt</span>
                    <span class="impact-label">Target</span>
                </div>
                <div class="impact-item">
                    <span class="impact-number"><?= ($program['beneficiaries_count'] ?? 0) > 0 ? $program['beneficiaries_count'] . '+' : '0' ?></span>
                    <span class="impact-label">Penerima</span>
                </div>
            </div>
        </section>

        <!-- Tujuan Program -->
        <section class="content-section">
            <h2>Tujuan Program</h2>
            <div class="program-types">
                <div class="program-type-card">
                    <div class="type-icon"><i class="fas fa-hands-helping"></i></div>
                    <h4>Bantuan Langsung</h4>
                    <p>Memberikan bantuan langsung kepada penerima manfaat yang membutuhkan.</p>
                </div>
                <div class="program-type-card">
                    <div class="type-icon"><i class="fas fa-users"></i></div>
                    <h4>Pemberdayaan</h4>
                    <p>Memberdayakan masyarakat untuk mandiri dan produktif.</p>
                </div>
                <div class="program-type-card">
                    <div class="type-icon"><i class="fas fa-heart"></i></div>
                    <h4>Kemanusiaan</h4>
                    <p>Menyalurkan kepedulian dan kasih sayang kepada sesama.</p>
                </div>
            </div>
        </section>

        <!-- Proses Pelaksanaan -->
        <section class="content-section">
            <h2>Proses Pelaksanaan</h2>
            <p>
                Program ini dilaksanakan dengan proses yang transparan dan akuntabel untuk memastikan
                setiap bantuan sampai kepada yang berhak menerimanya.
            </p>
            <ol class="numbered-list">
                <li>Pendataan dan verifikasi calon penerima manfaat</li>
                <li>Pengumpulan dan pengelolaan dana donasi</li>
                <li>Penyaluran bantuan secara langsung</li>
                <li>Pelaporan dan dokumentasi kegiatan</li>
            </ol>
        </section>

        <!-- Testimonial -->
        <section class="testimonial-section">
            <div class="testimonial-card">
                <img src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop"
                    alt="Penerima Manfaat" class="testimonial-avatar">
                <blockquote>
                    "Terima kasih kepada Yayasan Y-ibbi dan para donatur yang telah membantu kami. Semoga Allah membalas kebaikan kalian."
                </blockquote>
                <cite>
                    <strong>Penerima Manfaat</strong>
                    <span>Program <?= htmlspecialchars($program['category'] ?? 'Sosial') ?></span>
                </cite>
            </div>
        </section>

        <!-- Tags -->
        <div class="article-tags">
            <span class="tag-label">Tags:</span>
            <a href="#" class="tag"><?= htmlspecialchars($program['category'] ?? 'Program') ?></a>
            <a href="#" class="tag">Donasi</a>
            <a href="#" class="tag">Kemanusiaan</a>
            <a href="#" class="tag">Sosial</a>
        </div>
    </main>

    <!-- RIGHT SIDEBAR - Gallery/Documentation -->
    <aside class="gallery-sidebar">
        <div class="gallery-card">
            <h3><i class="fas fa-images"></i> Dokumentasi Kegiatan</h3>
            <div class="sidebar-gallery">
                <?php foreach ($gallery_items as $item): ?>
                <div class="sidebar-gallery-item">
                    <img src="<?= htmlspecialchars($item['src']) ?>" alt="<?= htmlspecialchars($item['caption']) ?>">
                    <span class="gallery-caption"><?= htmlspecialchars($item['caption']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="#" class="btn-view-all">
                <i class="fas fa-th"></i> Lihat Semua Foto
            </a>
        </div>
        
        <!-- Side Note -->
        <div class="sidebar-note">
            <h5><i class="fas fa-lightbulb"></i> Tahukah Anda?</h5>
            <p>100% donasi Anda akan disalurkan langsung kepada penerima manfaat yang membutuhkan.</p>
        </div>
    </aside>
</article>

<!-- Donation Section - Full Width Below Article -->
<section class="donation-section">
    <div class="container">
        <div class="donation-wrapper">
            <div class="donation-info">
                <h2><i class="fas fa-hand-holding-heart"></i> Dukung Program Ini</h2>
                <p>Dengan donasi Anda, kami bisa membantu lebih banyak masyarakat yang membutuhkan. Setiap rupiah yang Anda berikan adalah amanah yang akan kami salurkan dengan baik.</p>
            </div>
            <div class="donation-card-bottom">
                <div class="donation-header">
                    <span class="donation-label">Dana Terkumpul</span>
                    <span class="donation-amount"><?= formatRupiah($program['amount_raised']) ?></span>
                </div>
                <div class="donation-progress">
                    <div class="progress-bar-bottom">
                        <div class="progress-fill" style="width: <?= $progress_percent ?>%;"></div>
                    </div>
                    <div class="progress-stats-bottom">
                        <span><?= $progress_percent ?>% tercapai</span>
                        <span>Target: <?= formatRupiah($program['goal_amount']) ?></span>
                    </div>
                </div>
                <div class="donation-stats-row">
                    <div class="stat-item-inline">
                        <i class="fas fa-users"></i>
                        <div>
                            <span class="stat-number"><?= ($program['beneficiaries_count'] ?? 0) > 0 ? $program['beneficiaries_count'] . '+' : '0' ?></span>
                            <span class="stat-label">Penerima</span>
                        </div>
                    </div>
                    <div class="stat-item-inline">
                        <i class="fas fa-heart"></i>
                        <div>
                            <span class="stat-number"><?= ($program['donators_count'] ?? 0) > 0 ? $program['donators_count'] . '+' : '0' ?></span>
                            <span class="stat-label">Donatur</span>
                        </div>
                    </div>
                    <div class="stat-item-inline">
                        <i class="fas fa-calendar"></i>
                        <div>
                            <span class="stat-number"><?= date('Y') - date('Y', strtotime($program['created_at'])) + 1 ?></span>
                            <span class="stat-label">Tahun</span>
                        </div>
                    </div>
                <div class="donation-actions">
                    <a href="index.php?page=donasi&program_id=<?= $program['id'] ?>" class="btn-donate-bottom">
                        <i class="fas fa-hand-holding-heart"></i>
                        Donasi Sekarang
                    </a>
                    <div class="share-section-inline">
                        <span>Bagikan:</span>
                        <a href="#" class="share-btn facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" class="share-btn copy"><i class="fas fa-link"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Programs -->
<?php if (!empty($related_programs)): ?>
<section class="related-programs">
    <div class="container">
        <h2>Program Lainnya</h2>
        <div class="related-grid">
            <?php foreach ($related_programs as $related): ?>
            <article class="related-card">
                <a href="index.php?page=detail_program&id=<?= $related['id'] ?>">
                    <img src="<?= htmlspecialchars(getProgramImage($related['image'])) ?>" alt="<?= htmlspecialchars($related['title']) ?>">
                    <div class="related-content">
                        <span class="related-category"><?= htmlspecialchars($related['category'] ?? 'Program') ?></span>
                        <h3><?= htmlspecialchars($related['title']) ?></h3>
                        <span class="related-amount"><?= formatRupiah($related['amount_raised']) ?> terkumpul</span>
                    </div>
                </a>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
