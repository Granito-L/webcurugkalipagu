<?php
/**
 * admin/index.php — Dashboard Admin
 * Wisata Curug Kalipagu
 *
 * Letakkan file ini di: htdocs/wisata_kalipagu/admin/index.php
 */

session_start();
require_once '../koneksi.php';

// === Proteksi: wajib login ===
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$settings = get_settings($pdo);

// === Ambil flash message dari session ===
$flash = null;
if (!empty($_SESSION['flash'])) {
    [$flash_type, $flash_msg] = explode('|', $_SESSION['flash'], 2);
    $flash = ['type' => $flash_type, 'msg' => $flash_msg];
    unset($_SESSION['flash']);
}

// === Ambil semua destinasi, urut sort_order ===
$stmt        = $pdo->query("SELECT id, title, subtitle, location, altitude, is_active, sort_order, image_path FROM destinations ORDER BY sort_order ASC");
$destinations = $stmt->fetchAll();

// === Statistik ringkas ===
$total_aktif   = array_sum(array_column($destinations, 'is_active'));
$total_nonaktif = count($destinations) - $total_aktif;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin — <?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --clr-primary:   <?= e($settings['color_primary']   ?? '#2D6A4F') ?>;
            --clr-dark:      <?= e($settings['color_dark']      ?? '#1B4332') ?>;
            --clr-accent:    <?= e($settings['color_accent']    ?? '#B7E4C7') ?>;
            --clr-mist:      #E8F5E9;
            --sidebar-w:     240px;
        }

        body { font-family: 'DM Sans', sans-serif; background: #f1f5f1; color: #1a2e1f; display: flex; min-height: 100vh; }

        /* ---- SIDEBAR ---- */
        .sidebar {
            width: var(--sidebar-w); background: var(--clr-dark);
            min-height: 100vh; position: fixed; top: 0; left: 0;
            display: flex; flex-direction: column; z-index: 100;
        }
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
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        .topbar { background: #fff; border-bottom: 1px solid #e5e7e3; padding: 1rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar h1  { font-size: 1.1rem; font-weight: 600; color: #1a2e1f; }
        .btn-add    { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.55rem 1.1rem; background: var(--clr-primary); color: #fff; border-radius: 8px; font-size: 0.875rem; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn-add:hover { background: var(--clr-dark); }

        .content { padding: 1.75rem 2rem; }

        /* ---- FLASH ---- */
        .flash { padding: 0.85rem 1.1rem; border-radius: 8px; font-size: 0.875rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem; }
        .flash.success { background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; }
        .flash.error   { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; }

        /* ---- STAT CARDS ---- */
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .stat-card { background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem; border: 1px solid #e9edeb; }
        .stat-card .stat-num  { font-size: 2rem; font-weight: 700; color: var(--clr-primary); line-height: 1; }
        .stat-card .stat-label { font-size: 0.78rem; color: #6b8072; margin-top: 0.3rem; }

        /* ---- TABLE ---- */
        .table-card { background: #fff; border-radius: 12px; border: 1px solid #e9edeb; overflow: hidden; }
        .table-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e9edeb; display: flex; align-items: center; justify-content: space-between; }
        .table-header h2 { font-size: 1rem; font-weight: 600; }

        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f8faf8; font-size: 0.72rem; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: #6b8072; padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e9edeb; }
        tbody tr { transition: background 0.15s; }
        tbody tr:hover { background: #f8faf8; }
        tbody td { padding: 1rem; font-size: 0.875rem; border-bottom: 1px solid #f0f0f0; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }

        .dest-img { width: 52px; height: 40px; object-fit: cover; border-radius: 6px; background: #e5e7eb; display: block; }
        .dest-title  { font-weight: 600; color: #1a2e1f; }
        .dest-sub    { font-size: 0.78rem; color: #6b8072; margin-top: 0.15rem; }

        .badge { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.72rem; font-weight: 600; }
        .badge.active   { background: #d1fae5; color: #065f46; }
        .badge.inactive { background: #f3f4f6; color: #9ca3af; }

        .order-badge { display: inline-block; background: var(--clr-mist); color: var(--clr-dark); border-radius: 4px; padding: 0.2rem 0.5rem; font-size: 0.78rem; font-weight: 600; }

        .actions { display: flex; gap: 0.5rem; }
        .btn-edit { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.85rem; background: var(--clr-mist); color: var(--clr-dark); border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; transition: background 0.2s; }
        .btn-edit:hover { background: var(--clr-accent); }
        .btn-del  { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.85rem; background: #fee2e2; color: #991b1b; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: background 0.2s; font-family: inherit; }
        .btn-del:hover { background: #fecaca; }

        .empty-state { text-align: center; padding: 3rem; color: #9ca3af; }
        .empty-state p { margin-top: 0.5rem; font-size: 0.875rem; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .main { margin-left: 0; }
            .content { padding: 1rem; }
            .topbar { padding: 1rem; }
            table thead { display: none; }
            tbody td { display: block; padding: 0.4rem 1rem; }
            tbody td:first-child { padding-top: 1rem; }
            tbody td:last-child  { padding-bottom: 1rem; }
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
        <a href="index.php" class="active">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Dashboard
        </a>
        <a href="edit.php">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Destinasi
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
        <h1>📋 Daftar Destinasi Curug</h1>
        <a href="edit.php" class="btn-add">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Destinasi
        </a>
    </div>

    <div class="content">

        <!-- Flash Message -->
        <?php if ($flash): ?>
        <div class="flash <?= $flash['type'] ?>" role="alert">
            <?= $flash['type'] === 'success' ? '✅' : '⚠️' ?>
            <?= e($flash['msg']) ?>
        </div>
        <?php endif; ?>

        <!-- Stat Cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-num"><?= count($destinations) ?></div>
                <div class="stat-label">Total Curug</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $total_aktif ?></div>
                <div class="stat-label">Aktif Ditampilkan</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $total_nonaktif ?></div>
                <div class="stat-label">Disembunyikan</div>
            </div>
        </div>

        <!-- Destinations Table -->
        <div class="table-card">
            <div class="table-header">
                <h2>Semua Destinasi</h2>
                <span style="font-size:0.8rem; color:#9ca3af"><?= count($destinations) ?> entri</span>
            </div>

            <?php if (empty($destinations)): ?>
            <div class="empty-state">
                <span style="font-size:2.5rem">🌿</span>
                <p>Belum ada destinasi. <a href="edit.php" style="color:var(--clr-primary)">Tambahkan sekarang</a>.</p>
            </div>
            <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th style="width:60px">Foto</th>
                        <th>Nama Curug</th>
                        <th>Lokasi</th>
                        <th>Ketinggian</th>
                        <th>Status</th>
                        <th>Urutan</th>
                        <th style="width:140px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($destinations as $i => $dest): ?>
                    <tr>
                        <td style="color:#9ca3af; font-size:0.8rem"><?= $i + 1 ?></td>
                        <td>
                            <?php if (!empty($dest['image_path'])): ?>
                            <img class="dest-img"
                                 src="../<?= e($dest['image_path']) ?>"
                                 alt="<?= e($dest['title']) ?>"
                                 onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'52\' height=\'40\'><rect width=\'52\' height=\'40\' fill=\'%23e5e7eb\'/><text x=\'26\' y=\'25\' text-anchor=\'middle\' font-size=\'18\'>🌊</text></svg>'">
                            <?php else: ?>
                            <div class="dest-img" style="display:flex;align-items:center;justify-content:center;font-size:1.2rem;">🌊</div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="dest-title"><?= e($dest['title']) ?></div>
                            <?php if (!empty($dest['subtitle'])): ?>
                            <div class="dest-sub"><?= e($dest['subtitle']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="color:#6b8072; font-size:0.82rem"><?= e($dest['location'] ?? '—') ?></td>
                        <td style="color:#6b8072; font-size:0.82rem"><?= e($dest['altitude'] ?? '—') ?></td>
                        <td>
                            <?php if ($dest['is_active']): ?>
                            <span class="badge active">● Aktif</span>
                            <?php else: ?>
                            <span class="badge inactive">○ Nonaktif</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="order-badge"><?= (int)$dest['sort_order'] ?></span></td>
                        <td>
                            <div class="actions">
                                <a href="edit.php?id=<?= $dest['id'] ?>" class="btn-edit">
                                    ✏️ Edit
                                </a>
                                <a href="?hapus=<?= $dest['id'] ?>"
                                   class="btn-del"
                                   onclick="return confirm('Hapus \"<?= e(addslashes($dest['title'])) ?>\"? Tindakan ini tidak dapat dibatalkan.')">
                                    🗑
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

    </div><!-- /content -->
</main>

<?php
// === Proses hapus destinasi (di bagian bawah setelah render, agar flash bekerja) ===
if (isset($_GET['hapus'])) {
    $hapus_id = (int)$_GET['hapus'];
    if ($hapus_id > 0) {
        // Ambil nama dulu untuk pesan flash
        $chk = $pdo->prepare("SELECT title FROM destinations WHERE id = ? LIMIT 1");
        $chk->execute([$hapus_id]);
        $row = $chk->fetch();
        if ($row) {
            $pdo->prepare("DELETE FROM destinations WHERE id = ?")->execute([$hapus_id]);
            $_SESSION['flash'] = 'success|Destinasi "' . $row['title'] . '" berhasil dihapus.';
        }
    }
    header('Location: index.php');
    exit;
}
?>

</body>
</html>
