<?php
/**
 * admin/edit.php — Tambah / Edit Destinasi + Pengaturan Tema
 * Wisata Curug Kalipagu
 *
 * Letakkan file ini di: htdocs/wisata_kalipagu/admin/edit.php
 */

session_start();
require_once '../koneksi.php';

// === Proteksi: wajib login ===
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$settings = get_settings($pdo);
$id       = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit  = $id > 0;
$errors   = [];
$success  = false;

// === Default nilai form destinasi ===
$dest = [
    'title'       => '',
    'slug'        => '',
    'subtitle'    => '',
    'description' => '',
    'history'     => '',
    'highlights'  => '',
    'location'    => '',
    'altitude'    => '',
    'best_time'   => '',
    'image_path'  => 'assets/images/placeholder.jpg',
    'sort_order'  => 0,
    'is_active'   => 1,
];

// === Jika edit: ambil data dari DB ===
if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM destinations WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $row  = $stmt->fetch();
    if (!$row) {
        $_SESSION['flash'] = 'error|Destinasi tidak ditemukan.';
        header('Location: index.php');
        exit;
    }
    $dest = $row;
}

// === Helper: buat slug dari title ===
function make_slug(string $title): string {
    $slug = mb_strtolower($title, 'UTF-8');
    $slug = preg_replace('/[^a-z0-9\s\-]/', '', $slug);
    $slug = preg_replace('/[\s\-]+/', '-', $slug);
    return trim($slug, '-');
}

// ============================================================
// === PROSES POST — Tab Destinasi ===
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'destinasi') {

    $title       = trim($_POST['title']       ?? '');
    $subtitle    = trim($_POST['subtitle']    ?? '');
    $description = trim($_POST['description'] ?? '');
    $history     = trim($_POST['history']     ?? '');
    $highlights  = trim($_POST['highlights']  ?? '');
    $location    = trim($_POST['location']    ?? '');
    $altitude    = trim($_POST['altitude']    ?? '');
    $best_time   = trim($_POST['best_time']   ?? '');
    $sort_order  = (int)($_POST['sort_order'] ?? 0);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    $slug        = $title ? make_slug($title) : '';

    // Validasi
    if (!$title)       $errors[] = 'Judul destinasi wajib diisi.';
    if (!$description) $errors[] = 'Deskripsi singkat wajib diisi.';
    if (!$history)     $errors[] = 'Sejarah & filosofi wajib diisi.';

    // Upload gambar
    $image_path = $dest['image_path'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file     = $_FILES['image'];
        $allowed  = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $max_size = 3 * 1024 * 1024;

        if (!in_array($file['type'], $allowed)) {
            $errors[] = 'Format gambar harus JPG, PNG, atau WebP.';
        } elseif ($file['size'] > $max_size) {
            $errors[] = 'Ukuran gambar maksimal 3MB.';
        } else {
            $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename   = 'dest_' . ($slug ?: uniqid()) . '.' . strtolower($ext);
            $upload_dir = '../assets/images/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $upload_path = $upload_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                $image_path = 'assets/images/' . $filename;
            } else {
                $errors[] = 'Gagal mengupload gambar. Periksa permission folder.';
            }
        }
    }

    // Simpan ke DB
    if (empty($errors)) {
        if ($is_edit) {
            $stmt = $pdo->prepare("
                UPDATE destinations SET
                    title=?, slug=?, subtitle=?, description=?,
                    history=?, highlights=?, location=?,
                    altitude=?, best_time=?, image_path=?,
                    sort_order=?, is_active=?
                WHERE id=?
            ");
            $stmt->execute([
                $title, $slug, $subtitle, $description,
                $history, $highlights, $location,
                $altitude, $best_time, $image_path,
                $sort_order, $is_active, $id
            ]);
            $_SESSION['flash'] = 'success|Destinasi "' . $title . '" berhasil diperbarui.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO destinations
                    (title, slug, subtitle, description, history, highlights,
                     location, altitude, best_time, image_path, sort_order, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $title, $slug, $subtitle, $description, $history, $highlights,
                $location, $altitude, $best_time, $image_path, $sort_order, $is_active
            ]);
            $_SESSION['flash'] = 'success|Destinasi "' . $title . '" berhasil ditambahkan.';
        }
        header('Location: index.php');
        exit;
    }

    // Pertahankan input user jika ada error
    $dest = array_merge($dest, compact('title','subtitle','description','history','highlights','location','altitude','best_time','sort_order','is_active'));
}

// ============================================================
// === PROSES POST — Tab Pengaturan Tema / Settings ===
// ============================================================
$settings_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'settings') {

    // Daftar setting yang boleh diubah dari form ini
    $allowed_settings = [
        'site_title', 'site_tagline',
        'color_primary', 'color_secondary', 'color_accent', 'color_dark', 'color_text',
        'hero_headline', 'hero_subheadline', 'hero_cta_text',
        'about_text',
        'contact_email', 'contact_phone',
        'footer_text',
    ];

    $stmt_upsert = $pdo->prepare("
        INSERT INTO settings (setting_key, setting_val)
        VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_val = VALUES(setting_val)
    ");

    foreach ($allowed_settings as $key) {
        if (isset($_POST[$key])) {
            $val = trim($_POST[$key]);
            // Validasi warna hex
            if (str_starts_with($key, 'color_') && !preg_match('/^#[0-9A-Fa-f]{6}$/', $val)) {
                $errors[] = 'Format warna "' . $key . '" tidak valid. Gunakan format HEX (#RRGGBB).';
                continue;
            }
            $stmt_upsert->execute([$key, $val]);
        }
    }

    if (empty($errors)) {
        $settings = get_settings($pdo); // Refresh settings
        $settings_success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $is_edit ? 'Edit' : 'Tambah' ?> Destinasi — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --clr-primary: <?= e($settings['color_primary'] ?? '#2D6A4F') ?>;
            --clr-dark:    <?= e($settings['color_dark']    ?? '#1B4332') ?>;
            --clr-accent:  <?= e($settings['color_accent']  ?? '#B7E4C7') ?>;
            --clr-mist:    #E8F5E9;
            --sidebar-w:   240px;
        }

        body { font-family: 'DM Sans', sans-serif; background: #f1f5f1; color: #1a2e1f; display: flex; min-height: 100vh; }

        /* ---- SIDEBAR ---- */
        .sidebar { width: var(--sidebar-w); background: var(--clr-dark); min-height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 100; }
        .sidebar-brand { padding: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-brand .brand-name { font-family: 'Playfair Display', serif; font-size: 0.95rem; color: #fff; line-height: 1.3; }
        .sidebar-brand .brand-sub  { font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--clr-accent); opacity: 0.7; margin-top: 0.2rem; }
        .sidebar-nav   { flex: 1; padding: 1rem 0; }
        .nav-section   { font-size: 0.6rem; font-weight: 600; letter-spacing: 0.2em; text-transform: uppercase; color: rgba(255,255,255,0.3); padding: 1rem 1.25rem 0.5rem; }
        .sidebar-nav a { display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem 1.25rem; font-size: 0.875rem; font-weight: 500; color: rgba(255,255,255,0.65); text-decoration: none; border-radius: 6px; margin: 0.1rem 0.75rem; transition: all 0.2s; }
        .sidebar-nav a:hover, .sidebar-nav a.active { background: rgba(255,255,255,0.08); color: #fff; }
        .sidebar-footer { padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.08); }
        .admin-info   { font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-bottom: 0.75rem; line-height: 1.4; }
        .admin-info strong { color: rgba(255,255,255,0.9); display: block; }
        .btn-logout   { display: block; text-align: center; padding: 0.5rem; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; border-radius: 6px; font-size: 0.8rem; text-decoration: none; transition: all 0.2s; }
        .btn-logout:hover { background: rgba(239,68,68,0.25); color: #fff; }

        /* ---- MAIN ---- */
        .main    { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
        .topbar  { background: #fff; border-bottom: 1px solid #e5e7e3; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar h1 { font-size: 1.1rem; font-weight: 600; }
        .content { padding: 1.75rem 2rem; max-width: 900px; }

        /* ---- TABS ---- */
        .tabs      { display: flex; gap: 0; border-bottom: 2px solid #e9edeb; margin-bottom: 2rem; }
        .tab-btn   { padding: 0.75rem 1.4rem; font-size: 0.875rem; font-weight: 600; color: #6b8072; background: none; border: none; border-bottom: 2px solid transparent; margin-bottom: -2px; cursor: pointer; transition: all 0.2s; font-family: inherit; }
        .tab-btn.active, .tab-btn:hover { color: var(--clr-primary); border-bottom-color: var(--clr-primary); }
        .tab-pane  { display: none; }
        .tab-pane.active { display: block; }

        /* ---- ALERTS ---- */
        .error-list  { background: #fee2e2; border: 1px solid #fca5a5; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; }
        .error-list h4 { color: #991b1b; font-size: 0.875rem; margin-bottom: 0.5rem; }
        .error-list ul { list-style: none; padding: 0; }
        .error-list li { font-size: 0.82rem; color: #b91c1c; padding: 0.2rem 0; }
        .error-list li::before { content: '• '; }
        .success-msg { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 0.85rem 1.1rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.5rem; }

        /* ---- FORM CARDS ---- */
        .form-card { background: #fff; border-radius: 12px; border: 1px solid #e9edeb; padding: 1.5rem; margin-bottom: 1.25rem; }
        .form-card-title { font-size: 0.95rem; font-weight: 700; color: #1a2e1f; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f0f0f0; }
        .form-grid  { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-grid.cols-2 { grid-template-columns: 1fr 1fr; }
        .form-grid.cols-3 { grid-template-columns: 1fr 1fr 1fr; }
        .form-group { display: flex; flex-direction: column; gap: 0.35rem; }
        .form-group.full { grid-column: 1 / -1; }
        label { font-size: 0.78rem; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase; color: #4b6b5a; }
        label.required::after { content: ' *'; color: #dc2626; }
        input[type="text"], input[type="email"], input[type="tel"], input[type="number"],
        textarea, select {
            padding: 0.7rem 0.9rem; border: 1.5px solid #d1d9d3; border-radius: 8px;
            font-family: inherit; font-size: 0.9rem; color: #1a2e1f;
            background: #fff; transition: border-color 0.2s, box-shadow 0.2s; outline: none;
        }
        input:focus, textarea:focus, select:focus { border-color: var(--clr-primary); box-shadow: 0 0 0 3px rgba(45,106,79,0.1); }
        textarea { resize: vertical; min-height: 120px; }
        textarea.tall { min-height: 180px; }
        .hint { font-size: 0.75rem; color: #9ca3af; margin-top: 0.15rem; }

        /* Color preview */
        .color-wrap { display: flex; gap: 0.5rem; align-items: center; }
        .color-wrap input[type="color"] { width: 40px; height: 38px; padding: 2px; border: 1.5px solid #d1d9d3; border-radius: 6px; cursor: pointer; }
        .color-wrap input[type="text"] { flex: 1; }

        /* Checkbox */
        .checkbox-wrap { display: flex; align-items: center; gap: 0.6rem; cursor: pointer; font-size: 0.9rem; }
        .checkbox-wrap input[type="checkbox"] { width: 16px; height: 16px; accent-color: var(--clr-primary); }

        /* Image upload */
        .img-upload-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 120px; border: 2px dashed #d1d9d3; border-radius: 10px; cursor: pointer; transition: border-color 0.2s; background: #fafafa; padding: 1.5rem; }
        .img-upload-wrap:hover { border-color: var(--clr-primary); }
        .upload-lbl { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; color: #9ca3af; }
        .upload-lbl .icon { font-size: 1.75rem; }
        input[type="file"] { display: none; }
        .img-preview { max-height: 100px; display: none; border-radius: 8px; margin-bottom: 0.5rem; object-fit: cover; }
        .img-preview.has-img { display: block; }

        /* Action buttons */
        .form-actions { display: flex; align-items: center; gap: 1rem; margin-top: 0.5rem; padding: 1.25rem 0; }
        .btn-save   { padding: 0.75rem 1.75rem; background: var(--clr-primary); color: #fff; font-family: inherit; font-size: 0.95rem; font-weight: 600; border: none; border-radius: 8px; cursor: pointer; transition: background 0.2s; }
        .btn-save:hover { background: var(--clr-dark); }
        .btn-cancel { padding: 0.75rem 1.25rem; color: #6b8072; font-size: 0.9rem; text-decoration: none; border: 1px solid #d1d9d3; border-radius: 8px; transition: all 0.2s; }
        .btn-cancel:hover { background: #f0f0f0; }

        @media (max-width: 768px) {
            .main { margin-left: 0; }
            .content { padding: 1rem; }
            .form-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- ============================================================
     SIDEBAR
     ============================================================ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-name">🌊 <?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?></div>
        <div class="brand-sub">Panel Admin</div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">Menu</div>
        <a href="index.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="edit.php" class="active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <?= $is_edit ? 'Edit Destinasi' : 'Tambah Destinasi' ?>
        </a>
        <div class="nav-section">Website</div>
        <a href="../index.php" target="_blank">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Lihat Website
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="admin-info">
            <strong><?= e($_SESSION['admin_name'] ?? 'Admin') ?></strong>
            <?= e(ucfirst($_SESSION['admin_role'] ?? 'admin')) ?>
        </div>
        <a href="logout.php" class="btn-logout" onclick="return confirm('Yakin ingin keluar?')">🚪 Logout</a>
    </div>
</aside>

<!-- ============================================================
     MAIN CONTENT
     ============================================================ -->
<main class="main">
    <div class="topbar">
        <h1><?= $is_edit ? '✏️ Edit Destinasi' : '➕ Tambah Destinasi Baru' ?></h1>
        <a href="index.php" style="font-size:0.85rem; color:var(--clr-primary); text-decoration:none;">← Kembali ke Daftar</a>
    </div>

    <div class="content">

        <!-- TAB NAVIGATION -->
        <div class="tabs" role="tablist">
            <button class="tab-btn active" onclick="switchTab('destinasi', this)" role="tab">📍 Data Destinasi</button>
            <button class="tab-btn" onclick="switchTab('pengaturan', this)" role="tab">⚙️ Pengaturan Website</button>
        </div>

        <!-- =====================================================
             TAB 1 — DESTINASI
             ===================================================== -->
        <div id="tab-destinasi" class="tab-pane active">

            <?php if (!empty($errors)): ?>
            <div class="error-list" role="alert">
                <h4>⚠️ Harap perbaiki kesalahan berikut:</h4>
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="form_type" value="destinasi">

                <!-- Informasi Dasar -->
                <div class="form-card">
                    <h2 class="form-card-title">📍 Informasi Dasar</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="required" for="title">Judul Destinasi</label>
                            <input type="text" id="title" name="title"
                                   value="<?= e($dest['title']) ?>"
                                   placeholder="contoh: Curug Bayan" required>
                        </div>
                        <div class="form-group">
                            <label for="subtitle">Subtitle / Tagline</label>
                            <input type="text" id="subtitle" name="subtitle"
                                   value="<?= e($dest['subtitle'] ?? '') ?>"
                                   placeholder="contoh: Air Terjun Pertanda Kesuburan">
                        </div>
                        <div class="form-group full">
                            <label class="required" for="description">Deskripsi Singkat</label>
                            <textarea id="description" name="description"
                                      placeholder="Deskripsi singkat yang ditampilkan di kartu destinasi..." required><?= e($dest['description']) ?></textarea>
                            <span class="hint">Maks. 3–4 kalimat. Tampil di kartu destinasi homepage.</span>
                        </div>
                    </div>
                </div>

                <!-- Sejarah & Filosofi -->
                <div class="form-card">
                    <h2 class="form-card-title">📜 Sejarah & Filosofi</h2>
                    <div class="form-group">
                        <label class="required" for="history">Konten Sejarah & Filosofi</label>
                        <textarea id="history" name="history" class="tall"
                                  placeholder="Ceritakan sejarah, legenda lokal, filosofi, dan informasi mendalam tentang destinasi ini..." required><?= e($dest['history']) ?></textarea>
                        <span class="hint">Teks ini tampil di modal detail. Gunakan baris baru untuk paragraf baru.</span>
                    </div>
                    <div class="form-group" style="margin-top:1.25rem;">
                        <label for="highlights">Keunggulan / Highlights</label>
                        <input type="text" id="highlights" name="highlights"
                               value="<?= e($dest['highlights'] ?? '') ?>"
                               placeholder="Kolam alami segar|Trek 30 menit|Flora endemik|Spot foto terbaik">
                        <span class="hint">Pisahkan setiap poin dengan tanda pipe ( | ).</span>
                    </div>
                </div>

                <!-- Informasi Praktis -->
                <div class="form-card">
                    <h2 class="form-card-title">ℹ️ Informasi Praktis</h2>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label for="location">Lokasi / Alamat</label>
                            <input type="text" id="location" name="location"
                                   value="<?= e($dest['location'] ?? '') ?>"
                                   placeholder="contoh: Desa Ketenger, Baturraden">
                        </div>
                        <div class="form-group">
                            <label for="altitude">Ketinggian Air Terjun</label>
                            <input type="text" id="altitude" name="altitude"
                                   value="<?= e($dest['altitude'] ?? '') ?>"
                                   placeholder="contoh: ± 22 meter">
                        </div>
                        <div class="form-group">
                            <label for="best_time">Waktu Terbaik Berkunjung</label>
                            <input type="text" id="best_time" name="best_time"
                                   value="<?= e($dest['best_time'] ?? '') ?>"
                                   placeholder="contoh: April – Oktober">
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Urutan Tampil (0 = pertama)</label>
                            <input type="number" id="sort_order" name="sort_order"
                                   value="<?= (int)$dest['sort_order'] ?>" min="0" max="99">
                        </div>
                    </div>
                </div>

                <!-- Gambar -->
                <div class="form-card">
                    <h2 class="form-card-title">🖼️ Gambar Destinasi</h2>
                    <?php if ($is_edit && !empty($dest['image_path'])): ?>
                    <p class="hint" style="margin-bottom:0.75rem;">
                        Gambar saat ini: <code><?= e($dest['image_path']) ?></code> — Upload baru untuk mengganti.
                    </p>
                    <img src="../<?= e($dest['image_path']) ?>"
                         alt="Preview <?= e($dest['title']) ?>"
                         style="max-height:120px; border-radius:8px; margin-bottom:1rem; object-fit:cover;"
                         onerror="this.style.display='none'">
                    <?php endif; ?>

                    <label class="img-upload-wrap" for="image">
                        <img id="imgPreview" class="img-preview" src="#" alt="Preview">
                        <div class="upload-lbl">
                            <span class="icon">📷</span>
                            <span>Klik untuk pilih gambar</span>
                            <span style="font-size:0.75rem;">JPG, PNG, WebP · Maks. 3MB</span>
                        </div>
                    </label>
                    <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp">
                    <p class="hint" style="margin-top:0.75rem;">
                        Letakkan gambar di folder <code>assets/images/</code> jika ingin mengisi secara manual.
                    </p>
                </div>

                <!-- Status -->
                <div class="form-card">
                    <h2 class="form-card-title">⚙️ Status Publikasi</h2>
                    <label class="checkbox-wrap">
                        <input type="checkbox" name="is_active" value="1" <?= $dest['is_active'] ? 'checked' : '' ?>>
                        <span>Tampilkan destinasi ini di website (aktif)</span>
                    </label>
                </div>

                <!-- Action -->
                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        💾 <?= $is_edit ? 'Simpan Perubahan' : 'Tambah Destinasi' ?>
                    </button>
                    <a href="index.php" class="btn-cancel">Batal</a>
                </div>
            </form>
        </div><!-- /tab-destinasi -->


        <!-- =====================================================
             TAB 2 — PENGATURAN WEBSITE / TEMA
             ===================================================== -->
        <div id="tab-pengaturan" class="tab-pane">

            <?php if ($settings_success): ?>
            <div class="success-msg">✅ Pengaturan berhasil disimpan. <a href="../index.php" target="_blank" style="color:var(--clr-primary)">Lihat website →</a></div>
            <?php endif; ?>
            <?php if (!empty($errors) && !$settings_success): ?>
            <div class="error-list" role="alert">
                <h4>⚠️ Ada kesalahan:</h4>
                <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
            </div>
            <?php endif; ?>

            <form method="POST" action="?settings=1" novalidate>
                <input type="hidden" name="form_type" value="settings">

                <!-- Identitas Website -->
                <div class="form-card">
                    <h2 class="form-card-title">🌐 Identitas Website</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="s_site_title">Nama Website / Judul</label>
                            <input type="text" id="s_site_title" name="site_title"
                                   value="<?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?>"
                                   placeholder="Wisata Curug Kalipagu">
                        </div>
                        <div class="form-group">
                            <label for="s_site_tagline">Tagline Website</label>
                            <input type="text" id="s_site_tagline" name="site_tagline"
                                   value="<?= e($settings['site_tagline'] ?? 'Baturraden · Banyumas') ?>"
                                   placeholder="Baturraden · Banyumas">
                        </div>
                    </div>
                </div>

                <!-- Warna Tema -->
                <div class="form-card">
                    <h2 class="form-card-title">🎨 Warna Tema</h2>
                    <div class="form-grid cols-3">
                        <?php
                        $colors = [
                            'color_primary'   => ['label' => 'Warna Utama (Primary)',  'default' => '#2D6A4F'],
                            'color_secondary' => ['label' => 'Warna Sekunder',          'default' => '#40916C'],
                            'color_accent'    => ['label' => 'Warna Aksen',             'default' => '#B7E4C7'],
                            'color_dark'      => ['label' => 'Warna Gelap',             'default' => '#1B4332'],
                            'color_text'      => ['label' => 'Warna Teks',              'default' => '#081C15'],
                        ];
                        foreach ($colors as $key => $meta):
                            $val = e($settings[$key] ?? $meta['default']);
                        ?>
                        <div class="form-group">
                            <label for="s_<?= $key ?>"><?= $meta['label'] ?></label>
                            <div class="color-wrap">
                                <input type="color" id="picker_<?= $key ?>"
                                       value="<?= $val ?>"
                                       oninput="document.getElementById('s_<?= $key ?>').value = this.value">
                                <input type="text" id="s_<?= $key ?>" name="<?= $key ?>"
                                       value="<?= $val ?>"
                                       pattern="^#[0-9A-Fa-f]{6}$"
                                       placeholder="#2D6A4F"
                                       oninput="if(/^#[0-9A-Fa-f]{6}$/.test(this.value)) document.getElementById('picker_<?= $key ?>').value=this.value">
                            </div>
                            <span class="hint">Format HEX, contoh: <?= $meta['default'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Hero Section -->
                <div class="form-card">
                    <h2 class="form-card-title">🖼️ Teks Hero / Banner</h2>
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="s_hero_headline">Judul Utama Hero</label>
                            <textarea id="s_hero_headline" name="hero_headline" style="min-height:80px;"
                                      placeholder="Jelajahi Keajaiban Curug Kalipagu"><?= e($settings['hero_headline'] ?? '') ?></textarea>
                            <span class="hint">Gunakan baris baru untuk efek multi-baris di hero.</span>
                        </div>
                        <div class="form-group full">
                            <label for="s_hero_subheadline">Subjudul Hero</label>
                            <input type="text" id="s_hero_subheadline" name="hero_subheadline"
                                   value="<?= e($settings['hero_subheadline'] ?? '') ?>"
                                   placeholder="Enam permata tersembunyi di kaki Gunung Slamet...">
                        </div>
                        <div class="form-group">
                            <label for="s_hero_cta_text">Teks Tombol CTA</label>
                            <input type="text" id="s_hero_cta_text" name="hero_cta_text"
                                   value="<?= e($settings['hero_cta_text'] ?? 'Mulai Petualangan') ?>"
                                   placeholder="Mulai Petualangan">
                        </div>
                    </div>
                </div>

                <!-- About -->
                <div class="form-card">
                    <h2 class="form-card-title">📖 Teks "Tentang Kawasan"</h2>
                    <div class="form-group">
                        <label for="s_about_text">Deskripsi Tentang</label>
                        <textarea id="s_about_text" name="about_text" class="tall"
                                  placeholder="Kawasan Curug Kalipagu terletak di..."><?= e($settings['about_text'] ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Kontak -->
                <div class="form-card">
                    <h2 class="form-card-title">📞 Informasi Kontak</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="s_contact_email">Email</label>
                            <input type="email" id="s_contact_email" name="contact_email"
                                   value="<?= e($settings['contact_email'] ?? '') ?>"
                                   placeholder="info@curug-kalipagu.id">
                        </div>
                        <div class="form-group">
                            <label for="s_contact_phone">Nomor Telepon / WhatsApp</label>
                            <input type="tel" id="s_contact_phone" name="contact_phone"
                                   value="<?= e($settings['contact_phone'] ?? '') ?>"
                                   placeholder="+62 812 3456 7890">
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="form-card">
                    <h2 class="form-card-title">🔻 Teks Footer</h2>
                    <div class="form-group">
                        <label for="s_footer_text">Copyright / Footer</label>
                        <input type="text" id="s_footer_text" name="footer_text"
                               value="<?= e($settings['footer_text'] ?? '© 2025 Wisata Curug Kalipagu. Seluruh hak cipta dilindungi.') ?>"
                               placeholder="© 2025 Wisata Curug Kalipagu...">
                    </div>
                </div>

                <!-- Action -->
                <div class="form-actions">
                    <button type="submit" class="btn-save">💾 Simpan Pengaturan</button>
                    <a href="../index.php" target="_blank" class="btn-cancel">👁 Preview Website</a>
                </div>
            </form>
        </div><!-- /tab-pengaturan -->

    </div><!-- /content -->
</main>

<script>
    // === Tab switching ===
    function switchTab(id, btn) {
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + id).classList.add('active');
        btn.classList.add('active');
    }

    // Buka tab pengaturan otomatis jika baru simpan settings
    <?php if ($settings_success || (isset($_GET['settings']) && !empty($errors))): ?>
    switchTab('pengaturan', document.querySelectorAll('.tab-btn')[1]);
    <?php endif; ?>

    // === Preview gambar sebelum upload ===
    const fileInput = document.getElementById('image');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                const preview = document.getElementById('imgPreview');
                preview.src = e.target.result;
                preview.classList.add('has-img');
            };
            reader.readAsDataURL(file);
        });
    }
</script>

</body>
</html>
