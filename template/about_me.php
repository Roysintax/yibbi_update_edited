<?php
// Load database connection first
include '../admin/koneksi.php';

// Load site settings for header, meta, etc.
include '../inc/site_settings.php';

// Load appearance settings for theming
include '../inc/appearance_settings.php';

// Create PDO connection for the legalitas section
try {
    $pdo = new PDO("mysql:host=$_host;dbname=$_db;charset=utf8mb4", $_user, $_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch legalitas data
    $stmt = $pdo->prepare("SELECT * FROM KategoriLegalitasAbout ORDER BY id ASC");
    $stmt->execute();
    $legalitas_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $legalitas_data = [];
    // Optionally log error: error_log("Database error: " . $e->getMessage());
}

// Icon mapping for different jenis_akta
$icon_mapping = [
    'Akta Notaris' => 'fas fa-building-columns',
    'SK Kemenkumham' => 'fas fa-gavel',
    'NPWP' => 'fas fa-file-invoice',
    'Izin Operasional' => 'fas fa-certificate',
    'NIB' => 'fas fa-id-card',
    'Terdaftar di Baznas' => 'fas fa-landmark'
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - <?php echo htmlspecialchars($site_settings['site_name'] ?? 'Yayasan Y-ibbi'); ?></title>
    
    <!-- favicon -->
    <?php if (!empty($site_settings['favicon'])): ?>
        <link rel="shortcut icon" type="image/x-icon" href="../<?php echo htmlspecialchars($site_settings['favicon']); ?>">
    <?php else: ?>
        <link rel="shortcut icon" type="image/x-icon" href="../assets/images/x-icon/01.png">
    <?php endif; ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/icofont.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="about_me.css">
    
    <!-- Dynamic Appearance Styles -->
    <?php if (isset($appearance_settings)): ?>
    <?php echo getAppearanceCss($appearance_settings, $btn_radius ?? '8px', $font_family ?? 'Poppins'); ?>
    <?php endif; ?>
</head>

<body>

    <!-- Header Section Starts Here -->
    <?php include '../inc/header.php'; ?>
    <!-- Header Section Ends Here-->

    <!-- Section 1: Tentang Kami -->
    <section id="tentang" class="about-section">
        <div class="container">
            <div class="about-wrapper">
                <div class="about-content">
                    <div class="section-badge">
                        <i class="fas fa-info-circle"></i>
                        <span>Profil Yayasan</span>
                    </div>
                    <h1 class="about-title">Tentang Kami</h1>
                    <div class="title-divider"></div>
                    <p class="about-lead">
                        Yayasan Y-ibbi adalah lembaga sosial keagamaan yang bergerak dalam bidang
                        pendidikan, dakwah, dan pemberdayaan masyarakat.
                    </p>
                    <p class="about-text">
                        Kami berkomitmen untuk memberikan kontribusi nyata bagi kemajuan umat melalui
                        berbagai program yang berlandaskan nilai-nilai keislaman. Dengan semangat
                        kebersamaan dan kepedulian, kami terus berupaya menjadi wadah bagi siapa saja
                        yang ingin berbagi dan berkontribusi untuk kebaikan bersama.
                    </p>
                    <p class="about-text">
                        Bersama-sama kita wujudkan masyarakat yang lebih baik, bermartabat, dan
                        sejahtera lahir batin. Setiap langkah kecil yang kita ambil adalah bagian
                        dari perubahan besar yang kita impikan.
                    </p>

                    <div class="about-highlights">
                        <div class="highlight-item">
                            <div class="highlight-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="highlight-info">
                                <span class="highlight-number">10+</span>
                                <span class="highlight-label">Tahun Berdiri</span>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="highlight-info">
                                <span class="highlight-number">500+</span>
                                <span class="highlight-label">Penerima Manfaat</span>
                            </div>
                        </div>
                        <div class="highlight-item">
                            <div class="highlight-icon">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                            <div class="highlight-info">
                                <span class="highlight-number">25+</span>
                                <span class="highlight-label">Program Aktif</span>
                            </div>
                        </div>
                    </div>

                    <a href="#kontak" class="btn-primary">
                        <span>Bergabung Bersama Kami</span>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="about-visual">
                    <div class="visual-frame">
                        <div class="frame-decoration top-left"></div>
                        <div class="frame-decoration top-right"></div>
                        <div class="frame-decoration bottom-left"></div>
                        <div class="frame-decoration bottom-right"></div>
                        <img src="https://images.unsplash.com/photo-1609599006353-e629aaabfeae?w=600&h=750&fit=crop"
                            alt="Yayasan Y-ibbi">
                    </div>
                    <div class="floating-card">
                        <div class="floating-icon">
                            <i class="fas fa-quote-left"></i>
                        </div>
                        <p>"Berbagi untuk Sesama, Bersama Menuju Keberkahan"</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section 2: Legalitas (Dynamic from Database) -->
    <section id="legalitas" class="legalitas-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge center">
                    <i class="fas fa-shield-halved"></i>
                    <span>Kepercayaan & Transparansi</span>
                </div>
                <h2 class="section-title">Legalitas Kami</h2>
                <div class="title-divider center"></div>
                <p class="section-desc">
                    Yayasan Y-ibbi telah terdaftar secara resmi dan memiliki dokumen legalitas
                    yang lengkap sebagai bentuk komitmen kami terhadap transparansi dan akuntabilitas.
                </p>
            </div>

            <div class="legalitas-grid">
                <?php if (!empty($legalitas_data)): ?>
                    <?php foreach ($legalitas_data as $legalitas): ?>
                        <div class="legalitas-card">
                            <div class="card-header">
                                <div class="card-icon">
                                    <i class="<?php echo htmlspecialchars($icon_mapping[$legalitas['jenis_akta']] ?? 'fas fa-file-alt'); ?>"></i>
                                </div>
                                <div class="card-badge"><?php echo htmlspecialchars($legalitas['status']); ?></div>
                            </div>
                            <h3 class="card-title"><?php echo htmlspecialchars($legalitas['jenis_akta']); ?></h3>
                            <p class="card-desc">
                                <?php echo htmlspecialchars($legalitas['deskripsi']); ?>
                            </p>
                            <div class="card-meta">
                                <i class="fas fa-hashtag"></i>
                                <span><?php echo htmlspecialchars($legalitas['nomor_akta']); ?></span>
                            </div>
                            <button class="btn-view" onclick="alert('Detail <?php echo htmlspecialchars($legalitas['jenis_akta']); ?>')">
                                <i class="fas fa-eye"></i>
                                <span>Lihat Detail</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback static content if database is empty -->
                    <div class="legalitas-card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-building-columns"></i>
                            </div>
                            <div class="card-badge">Resmi</div>
                        </div>
                        <h3 class="card-title">Akta Notaris</h3>
                        <p class="card-desc">
                            Akta Pendirian Yayasan yang disahkan oleh Notaris sesuai dengan
                            peraturan perundang-undangan yang berlaku.
                        </p>
                        <div class="card-meta">
                            <i class="fas fa-hashtag"></i>
                            <span>No. 123/2014</span>
                        </div>
                        <button class="btn-view" onclick="alert('Detail Akta Notaris')">
                            <i class="fas fa-eye"></i>
                            <span>Lihat Detail</span>
                        </button>
                    </div>
                    <p class="text-center text-muted">Data legalitas belum tersedia di database.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section 3: Aktivitas / Galeri -->
    <section id="aktivitas" class="aktivitas-section">
        <div class="container">
            <div class="section-header">
                <div class="section-badge center">
                    <i class="fas fa-images"></i>
                    <span>Dokumentasi Kegiatan</span>
                </div>
                <h2 class="section-title">Aktivitas Kami</h2>
                <div class="title-divider center"></div>
                <p class="section-desc">
                    Berikut adalah dokumentasi berbagai kegiatan yang telah kami laksanakan
                    bersama masyarakat dalam upaya mewujudkan misi sosial keagamaan.
                </p>
            </div>

            <div class="gallery-grid">
                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=500&h=400&fit=crop"
                        alt="Bakti Sosial">
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <div class="gallery-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <h4>Bakti Sosial</h4>
                            <p>Kegiatan sosial untuk masyarakat</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=500&h=400&fit=crop"
                        alt="Kajian Rutin">
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <div class="gallery-icon">
                                <i class="fas fa-book-open"></i>
                            </div>
                            <h4>Kajian Rutin</h4>
                            <p>Pengajian dan pembelajaran</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=500&h=400&fit=crop"
                        alt="Program Pendidikan">
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <div class="gallery-icon">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            <h4>Program Pendidikan</h4>
                            <p>Beasiswa dan bimbingan belajar</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1469571486292-0ba58a3f068b?w=500&h=400&fit=crop"
                        alt="Santunan Yatim">
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <div class="gallery-icon">
                                <i class="fas fa-children"></i>
                            </div>
                            <h4>Santunan Yatim</h4>
                            <p>Bantuan untuk anak yatim</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1593113598332-cd288d649433?w=500&h=400&fit=crop"
                        alt="Distribusi Donasi">
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <div class="gallery-icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h4>Distribusi Donasi</h4>
                            <p>Penyaluran bantuan sembako</p>
                        </div>
                    </div>
                </div>

                <div class="gallery-item">
                    <img src="https://images.unsplash.com/photo-1577896851231-70ef18881754?w=500&h=400&fit=crop"
                        alt="Pelatihan Keterampilan">
                    <div class="gallery-overlay">
                        <div class="gallery-content">
                            <div class="gallery-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <h4>Pelatihan</h4>
                            <p>Pemberdayaan keterampilan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section start here -->
    <?php include '../inc/footer.php'; ?>
    <!-- Footer Section end here -->

    <!-- scrollToTop start here -->
    <a href="#" class="scrollToTop"><i class="icofont-bubble-up"></i><span class="pluse_1"></span><span
            class="pluse_2"></span></a>
    <!-- scrollToTop ending here -->

    <!-- JavaScript -->
    <script src="../assets/js/jquery.js"></script>
    <script src="../assets/js/fontawesome.min.js"></script>
    <script src="../assets/js/waypoints.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/swiper.min.js"></script>
    <script src="../assets/js/circularProgressBar.min.js"></script>
    <script src="../assets/js/isotope.pkgd.min.js"></script>
    <script src="../assets/js/lightcase.js"></script>
    <script src="../assets/js/functions.js"></script>
    
    <script>
        // Animate on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.legalitas-card, .gallery-item, .highlight-item').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>

</html>
