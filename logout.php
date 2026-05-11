<?php
/**
 * admin/logout.php — Proses Logout Admin
 * Wisata Curug Kalipagu
 *
 * Letakkan file ini di: htdocs/wisata_kalipagu/admin/logout.php
 */

session_start();

// Hapus semua data session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hancurkan session sepenuhnya
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit;
