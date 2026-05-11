<?php
/**
 * index.php — Halaman Utama (Landing Page)
 * Wisata Curug Kalipagu Baturraden Banyumas
 */

require_once 'koneksi.php';

// === Ambil semua settings dari database ===
$settings = get_settings($pdo);

// === Ambil semua destinasi aktif, urut berdasarkan sort_order ===
$stmt = $pdo->query("
    SELECT id, slug, title, subtitle, description, history,
           highlights, location, altitude, best_time, image_path
    FROM destinations
    WHERE is_active = 1
    ORDER BY sort_order ASC
");
$destinations = $stmt->fetchAll();

// === Helper: warna dari settings ===
$clr = [
    'primary'   => $settings['color_primary']   ?? '#2D6A4F',
    'secondary' => $settings['color_secondary'] ?? '#40916C',
    'accent'    => $settings['color_accent']    ?? '#B7E4C7',
    'dark'      => $settings['color_dark']      ?? '#1B4332',
    'text'      => $settings['color_text']      ?? '#081C15',
];

// === Hero headline — ganti newline jadi <br> ===
$hero_headline = nl2br(e($settings['hero_headline'] ?? 'Jelajahi Keajaiban Curug Kalipagu'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Wisata Curug Kalipagu - Enam air terjun menakjubkan di kaki Gunung Slamet, Baturraden, Banyumas, Jawa Tengah.">
    <meta name="keywords" content="curug kalipagu, baturraden, banyumas, wisata air terjun, curug bayan, curug jenggala, pancuran pitu">
    <meta property="og:title" content="<?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?>">
    <meta property="og:description" content="Jelajahi 6 air terjun menakjubkan di kaki Gunung Slamet">
    <title><?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?> — <?= e($settings['site_tagline'] ?? 'Baturraden · Banyumas') ?></title>

    <!-- Preconnect for Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Dynamic CSS Variables from DB settings -->
    <style>
        :root {
            --clr-primary:   <?= e($clr['primary']) ?>;
            --clr-secondary: <?= e($clr['secondary']) ?>;
            --clr-accent:    <?= e($clr['accent']) ?>;
            --clr-dark:      <?= e($clr['dark']) ?>;
            --clr-text:      <?= e($clr['text']) ?>;
        }
    </style>

    <!-- Favicon placeholder -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌊</text></svg>">
</head>
<body>

<!-- ============================================================
     NAVBAR
     ============================================================ -->
<header>
    <nav class="navbar" role="navigation" aria-label="Navigasi utama">
        <div class="container">
            <a href="#home" class="nav-logo" aria-label="Beranda">
                <span class="logo-main"><?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?></span>
                <span class="logo-sub"><?= e($settings['site_tagline'] ?? 'Baturraden · Banyumas') ?></span>
            </a>

            <ul class="nav-links" role="list">
                <li><a href="#home">Beranda</a></li>
                <li><a href="#tentang">Tentang</a></li>
                <li><a href="#destinasi">Destinasi</a></li>
                <li><a href="#tips">Tips Wisata</a></li>
                <li><a href="#kontak">Kontak</a></li>
            </ul>

            <button class="nav-hamburger" aria-label="Buka menu" aria-expanded="false">
                <span></span><span></span><span></span>
            </button>
        </div>
    </nav>
</header>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section id="home" class="hero">
    <div class="hero-bg" role="img" aria-label="Pemandangan air terjun Kalipagu"></div>

    <div class="container">
        <p class="hero-eyebrow">Baturraden · Banyumas · Jawa Tengah</p>

        <h1 class="hero-headline">
            <?= $hero_headline ?>
        </h1>

        <p class="hero-sub">
            <?= e($settings['hero_subheadline'] ?? 'Enam permata tersembunyi di kaki Gunung Slamet — menanti untuk dijelajahi.') ?>
        </p>

        <div class="hero-actions">
            <a href="#destinasi" class="btn-primary">
                <?= e($settings['hero_cta_text'] ?? 'Mulai Petualangan') ?>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path d="M5 12h14M12 5l7 7-7 7"/>
                </svg>
            </a>
            <a href="#tentang" class="btn-ghost">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
                </svg>
                Lebih Lanjut
            </a>
        </div>
    </div>

    <!-- Scroll hint -->
    <div class="hero-scroll-hint" aria-hidden="true">
        <span>Gulir ke bawah</span>
        <div class="scroll-line"></div>
    </div>

    <!-- Stats bar -->
    <div class="hero-stats" aria-label="Statistik kawasan wisata">
        <div class="stat-item">
            <div class="stat-num" data-target="<?= count($destinations) ?>"><?= count($destinations) ?></div>
            <div class="stat-label">Air Terjun</div>
        </div>
        <div class="stat-item">
            <div class="stat-num" data-target="3265">0</div>
            <div class="stat-label">Meter dpl (G. Slamet)</div>
        </div>
        <div class="stat-item">
            <div class="stat-num" data-target="12">0</div>
            <div class="stat-label">Km dari Purwokerto</div>
        </div>
    </div>
</section>

<!-- ============================================================
     ABOUT SECTION
     ============================================================ -->
<section id="tentang" class="about section">
    <div class="container">
        <div class="about-inner">
            <div class="about-content">
                <p class="about-label reveal">Tentang Kawasan</p>
                <h2 class="about-title reveal reveal-delay-1">
                    Keajaiban Alam di<br><em>Kaki Gunung Slamet</em>
                </h2>
                <p class="about-text reveal reveal-delay-2">
                    <?= e($settings['about_text'] ?? 'Kawasan Curug Kalipagu terletak di Desa Ketenger, Kecamatan Baturraden, Kabupaten Banyumas, Jawa Tengah. Berada di kaki Gunung Slamet, kawasan ini menyimpan kekayaan alam dan budaya yang luar biasa.') ?>
                </p>

                <div class="about-features reveal reveal-delay-3">
                    <div class="feature-chip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        Ramah Keluarga
                    </div>
                    <div class="feature-chip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                        Buka Setiap Hari
                    </div>
                    <div class="feature-chip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Akses Mudah
                    </div>
                    <div class="feature-chip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        Lingkungan Terjaga
                    </div>
                </div>

                <a href="#destinasi" class="card-link reveal reveal-delay-4" style="margin-top:2rem; font-size:0.95rem;">
                    Jelajahi 6 Destinasi
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="about-visual reveal reveal-delay-2">
                <div class="about-img-wrap">
                    <img src="assets/images/about.jpg"
                         alt="Pemandangan hutan dan air terjun di kawasan Kalipagu"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <div class="img-placeholder" style="display:none;height:100%;">
                        <span>🏞️</span><span>Kawasan Kalipagu</span>
                    </div>
                </div>
                <div class="about-badge">
                    <div class="badge-num">6</div>
                    <div class="badge-text">Curug Unggulan</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     DESTINATIONS SECTION
     ============================================================ -->
<section id="destinasi" class="destinations section">
    <div class="container">
        <div class="section-header">
            <span class="section-label reveal">Destinasi Wisata</span>
            <h2 class="section-title reveal reveal-delay-1">
                Enam <em>Curug Menakjubkan</em><br>di Kalipagu
            </h2>
            <p class="section-desc reveal reveal-delay-2">
                Setiap curug menyimpan keunikan, cerita, dan filosofi tersendiri.
                Klik kartu destinasi untuk mengetahui sejarah lengkapnya.
            </p>
        </div>

        <!-- Destination Cards Grid -->
        <div class="dest-grid">
            <?php foreach ($destinations as $idx => $dest):
                $delay = ($idx % 6) + 1;
                // Determine altitude label
                $altitude_label = $dest['altitude'] ? $dest['altitude'] : 'Lihat di lokasi';
            ?>
            <article class="dest-card reveal reveal-delay-<?= $delay ?>"
                     role="button"
                     tabindex="0"
                     aria-label="Buka detail <?= e($dest['title']) ?>"
                     data-title="<?= e($dest['title']) ?>"
                     data-subtitle="<?= e($dest['subtitle'] ?? '') ?>"
                     data-description="<?= e($dest['description']) ?>"
                     data-history="<?= e($dest['history']) ?>"
                     data-highlights="<?= e($dest['highlights'] ?? '') ?>"
                     data-location="<?= e($dest['location'] ?? '') ?>"
                     data-altitude="<?= e($dest['altitude'] ?? '') ?>"
                     data-best-time="<?= e($dest['best_time'] ?? '') ?>"
                     data-image="<?= e($dest['image_path']) ?>"
                     onkeydown="if(event.key==='Enter'||event.key===' ')this.click()">

                <div class="card-img">
                    <img src="<?= e($dest['image_path']) ?>"
                         alt="Foto <?= e($dest['title']) ?>"
                         loading="lazy"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                    <div class="img-placeholder" style="display:none;">
                        <span>🌊</span>
                        <span><?= e($dest['title']) ?></span>
                    </div>
                    <div class="card-num" aria-hidden="true"><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></div>
                    <div class="card-badge" aria-label="Ketinggian <?= e($altitude_label) ?>"><?= e($altitude_label) ?></div>
                </div>

                <div class="card-body">
                    <p class="card-subtitle"><?= e($dest['subtitle'] ?? 'Curug Kalipagu') ?></p>
                    <h3 class="card-title"><?= e($dest['title']) ?></h3>
                    <p class="card-desc"><?= e($dest['description']) ?></p>

                    <?php if ($dest['location']): ?>
                    <div class="card-meta">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span><?= e($dest['location']) ?></span>
                    </div>
                    <?php endif; ?>

                    <span class="card-link" aria-hidden="true">
                        Baca Sejarah
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </span>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     NATURE STRIP (auto-scrolling images)
     ============================================================ -->
<div class="nature-strip" aria-hidden="true">
    <div class="nature-strip-inner">
        <?php foreach ($destinations as $dest): ?>
        <div class="strip-item">
            <img src="<?= e($dest['image_path']) ?>"
                 alt="<?= e($dest['title']) ?>"
                 loading="lazy"
                 onerror="this.style.background='#1B4332'; this.style.display='block'">
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ============================================================
     TIPS / INFO SECTION
     ============================================================ -->
<section id="tips" class="info-section section">
    <div class="container">
        <div class="section-header">
            <span class="section-label reveal">Panduan Wisatawan</span>
            <h2 class="section-title reveal reveal-delay-1">Tips Sebelum<br><em>Berkunjung</em></h2>
        </div>

        <div class="info-grid">
            <div class="info-card reveal">
                <div class="info-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/>
                    </svg>
                </div>
                <h3>Waktu Terbaik Berkunjung</h3>
                <p>Musim kemarau (April–Oktober) ideal karena jalur lebih kering. Datang pagi hari (07.00–09.00) untuk mendapat cahaya terbaik dan suasana sepi. Hindari musim hujan lebat karena debit air bisa berbahaya.</p>
            </div>

            <div class="info-card reveal reveal-delay-1">
                <div class="info-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                    </svg>
                </div>
                <h3>Perlengkapan yang Wajib Dibawa</h3>
                <p>Sepatu trekking anti-slip, pakaian ganti, jas hujan ringan, air minum cukup, dan sunscreen. Bawa kantong plastik untuk sampah pribadi. Kamera atau ponsel dalam tas waterproof sangat disarankan.</p>
            </div>

            <div class="info-card reveal reveal-delay-2">
                <div class="info-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <h3>Keselamatan & Etika Alam</h3>
                <p>Selalu ikuti jalur resmi dan jangan mendekati tepi air terjun. Dilarang membuang sampah sembarangan. Hormati kepercayaan lokal di area tertentu. Beritahu orang lain tentang rencana perjalanan Anda.</p>
            </div>

            <div class="info-card reveal reveal-delay-3">
                <div class="info-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                </div>
                <h3>Cara Menuju Kalipagu</h3>
                <p>Dari Purwokerto ±12 km ke arah Baturraden, lanjut ke Desa Ketenger. Tersedia ojek lokal dari parkiran utama. Koordinat GPS: -7.3124, 109.2284. Parkir luas tersedia di pintu masuk kawasan.</p>
            </div>

            <div class="info-card reveal reveal-delay-4">
                <div class="info-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                <h3>Tiket & Biaya</h3>
                <p>Tiket masuk kawasan terjangkau. Tersedia paket guide lokal berpengalaman yang sangat direkomendasikan, terutama untuk trek ke curug yang tersembunyi. Hubungi pengelola untuk info tarif terkini.</p>
            </div>

            <div class="info-card reveal reveal-delay-5">
                <div class="info-icon" aria-hidden="true">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
                <h3>Wisata Budaya</h3>
                <p>Setiap bulan Suro, ada ritual sederhana di Curug Mertelu. Kunjungi desa Ketenger untuk mencicipi kuliner khas Banyumas seperti mendoan, getuk goreng, dan soto Sokaraja setelah berwisata.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA / CONTACT SECTION
     ============================================================ -->
<section id="kontak" class="cta-section">
    <div class="container">
        <p class="cta-eyebrow reveal">Hubungi Kami</p>
        <h2 class="cta-title reveal reveal-delay-1">
            Siap Menjelajahi<br>Keindahan Kalipagu?
        </h2>
        <p class="cta-subtitle reveal reveal-delay-2">
            Tim kami siap membantu Anda merencanakan perjalanan yang tak terlupakan.
            Hubungi kami untuk informasi lebih lanjut.
        </p>

        <div class="cta-contacts reveal reveal-delay-3">
            <?php if (!empty($settings['contact_email'])): ?>
            <div class="contact-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                </svg>
                <a href="mailto:<?= e($settings['contact_email']) ?>"><?= e($settings['contact_email']) ?></a>
            </div>
            <?php endif; ?>
            <?php if (!empty($settings['contact_phone'])): ?>
            <div class="contact-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2.22l3-.01a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.18 6.18l1.06-.94a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <a href="tel:<?= e($settings['contact_phone']) ?>"><?= e($settings['contact_phone']) ?></a>
            </div>
            <?php endif; ?>
            <div class="contact-item">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>
                </svg>
                <span>Desa Ketenger, Baturraden, Banyumas</span>
            </div>
        </div>

        <a href="#destinasi" class="btn-primary reveal reveal-delay-4" style="display:inline-flex; margin-top:1rem;">
            Lihat Destinasi
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
</section>

<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="footer">
    <div class="container">
        <p><?= e($settings['footer_text'] ?? '© 2025 Wisata Curug Kalipagu. Seluruh hak cipta dilindungi.') ?>
            &nbsp;·&nbsp;
            <a href="admin/login.php">Admin</a>
        </p>
    </div>
</footer>

<!-- ============================================================
     DESTINATION MODAL (Detail Page)
     ============================================================ -->
<div id="destModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title" style="display:none;">
    <div class="modal-box">
        <!-- Modal Hero Image -->
        <div class="modal-hero">
            <img id="modal-img" src="" alt="" loading="lazy">
            <div class="modal-hero-overlay" aria-hidden="true"></div>
            <div class="modal-hero-text">
                <p class="modal-hero-sub" id="modal-subtitle"></p>
                <h2 class="modal-hero-title" id="modal-title"></h2>
            </div>
            <button class="modal-close" aria-label="Tutup modal">✕</button>
        </div>

        <!-- Modal Content -->
        <div class="modal-body">
            <!-- Meta chips -->
            <div class="modal-chips" id="modal-chips" aria-label="Informasi destinasi"></div>

            <!-- Description -->
            <div class="modal-section">
                <p class="modal-section-label">Tentang Destinasi</p>
                <p class="modal-desc" id="modal-desc"></p>
            </div>

            <!-- History -->
            <div class="modal-section">
                <p class="modal-section-label">Sejarah & Filosofi</p>
                <h3>Kisah di Balik Keindahan</h3>
                <p class="modal-history" id="modal-history"></p>
            </div>

            <!-- Highlights -->
            <div class="modal-section" id="modal-highlights-section">
                <p class="modal-section-label">Keunggulan</p>
                <div class="modal-highlights" id="modal-highlights"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     BACK TO TOP BUTTON
     ============================================================ -->
<button id="backToTop"
        aria-label="Kembali ke atas"
        style="
            position:fixed; bottom:2rem; right:2rem; z-index:800;
            width:44px; height:44px; border-radius:50%;
            background:var(--clr-primary); color:#fff;
            box-shadow:0 4px 16px rgba(45,106,79,.4);
            display:flex; align-items:center; justify-content:center;
            opacity:0; visibility:hidden; transform:translateY(12px);
            transition:all .3s ease; border:none; cursor:pointer;
        ">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
        <path d="M18 15l-6-6-6 6"/>
    </svg>
</button>

<!-- JavaScript -->
<script src="assets/js/script.js"></script>

<!-- Inline: Back to top visibility toggle (extends script.js) -->
<script>
    // Extend btn visibility with CSS via JS (since btn has inline styles)
    const btt = document.getElementById('backToTop');
    if (btt) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 500) {
                btt.style.opacity = '1';
                btt.style.visibility = 'visible';
                btt.style.transform = 'translateY(0)';
            } else {
                btt.style.opacity = '0';
                btt.style.visibility = 'hidden';
                btt.style.transform = 'translateY(12px)';
            }
        }, { passive: true });
    }
</script>

</body>
</html>
