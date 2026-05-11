<?php
/**
 * admin/login.php — Halaman Login Admin
 * Wisata Curug Kalipagu
 *
 * Letakkan file ini di: htdocs/wisata_kalipagu/admin/login.php
 */

session_start();
require_once '../koneksi.php';

// Redirect jika sudah login
if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// === Proses Login ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        // Cari user di database
        $stmt = $pdo->prepare("SELECT id, username, password, full_name, role FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil — set session
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_role'] = $user['role'];

            // Regenerate session ID untuk keamanan
            session_regenerate_id(true);

            header('Location: index.php');
            exit;
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}

$settings = get_settings($pdo);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin — <?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --clr-primary: <?= e($settings['color_primary'] ?? '#2D6A4F') ?>;
            --clr-dark:    <?= e($settings['color_dark']    ?? '#1B4332') ?>;
            --clr-accent:  <?= e($settings['color_accent']  ?? '#B7E4C7') ?>;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, var(--clr-dark) 0%, #0a1f14 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Dekorasi latar belakang */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            pointer-events: none;
        }
        body::before {
            width: 400px; height: 400px;
            background: var(--clr-primary);
            top: -100px; left: -100px;
        }
        body::after {
            width: 300px; height: 300px;
            background: var(--clr-accent);
            bottom: -80px; right: -80px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
        }

        /* ---- BRAND ---- */
        .login-brand {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-brand .icon {
            font-size: 2.75rem;
            margin-bottom: 0.75rem;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(183,228,199,0.4));
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-6px); }
        }
        .login-brand h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.45rem;
            color: #fff;
            margin-bottom: 0.3rem;
            letter-spacing: 0.01em;
        }
        .login-brand .badge {
            display: inline-block;
            font-size: 0.68rem;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: var(--clr-accent);
            opacity: 0.8;
            border: 1px solid rgba(183,228,199,0.25);
            border-radius: 20px;
            padding: 0.2rem 0.75rem;
        }

        /* ---- CARD ---- */
        .login-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2.25rem;
            box-shadow: 0 24px 64px rgba(0,0,0,0.35);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card-title span { opacity: 0.5; font-weight: 400; font-size: 0.875rem; }

        /* ---- FORM ---- */
        .form-group { margin-bottom: 1.25rem; }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.55);
            margin-bottom: 0.5rem;
        }

        .input-wrap {
            position: relative;
        }
        .input-wrap svg {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,0.3);
            pointer-events: none;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 2.6rem;
            background: rgba(255,255,255,0.07);
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
            transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
            outline: none;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: var(--clr-accent);
            background: rgba(255,255,255,0.11);
            box-shadow: 0 0 0 3px rgba(183,228,199,0.12);
        }
        input::placeholder { color: rgba(255,255,255,0.25); }

        /* Toggle password visibility */
        .toggle-pw {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255,255,255,0.35);
            cursor: pointer;
            padding: 0.2rem;
            transition: color 0.2s;
            line-height: 0;
        }
        .toggle-pw:hover { color: rgba(255,255,255,0.7); }

        /* ---- ERROR ---- */
        .error-msg {
            background: rgba(239,68,68,0.12);
            border: 1px solid rgba(239,68,68,0.3);
            color: #fca5a5;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: shake 0.35s ease;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25%       { transform: translateX(-6px); }
            75%       { transform: translateX(6px); }
        }

        /* ---- BUTTON ---- */
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            background: var(--clr-primary);
            color: #fff;
            font-family: inherit;
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
            letter-spacing: 0.04em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.25rem;
            box-shadow: 0 4px 20px rgba(45,106,79,0.4);
        }
        .btn-login:hover {
            background: #40916C;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(45,106,79,0.45);
        }
        .btn-login:active { transform: translateY(0); box-shadow: none; }

        /* Loading state */
        .btn-login.loading { pointer-events: none; opacity: 0.7; }
        .spinner {
            display: none;
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        .btn-login.loading .spinner { display: inline-block; }
        .btn-login.loading .btn-text { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ---- DIVIDER ---- */
        .divider {
            text-align: center;
            margin: 1.5rem 0 1rem;
            position: relative;
            color: rgba(255,255,255,0.2);
            font-size: 0.75rem;
        }
        .divider::before, .divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: calc(50% - 30px);
            height: 1px;
            background: rgba(255,255,255,0.1);
        }
        .divider::before { left: 0; }
        .divider::after  { right: 0; }

        /* ---- FOOTER ---- */
        .login-footer {
            text-align: center;
            margin-top: 1.75rem;
        }
        .login-footer a {
            font-size: 0.8rem;
            color: rgba(255,255,255,0.35);
            text-decoration: none;
            transition: color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .login-footer a:hover { color: var(--clr-accent); }

        .copyright {
            text-align: center;
            margin-top: 1.25rem;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.2);
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Brand -->
    <div class="login-brand">
        <span class="icon">🌊</span>
        <h1><?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?></h1>
        <span class="badge">Panel Admin</span>
    </div>

    <!-- Card -->
    <div class="login-card">
        <p class="card-title">
            Masuk ke Dashboard
            <span>/ Login</span>
        </p>

        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="error-msg" role="alert">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= e($error) ?>
        </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="POST" action="" novalidate id="loginForm">

            <!-- Username -->
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                    </svg>
                    <input type="text"
                           id="username"
                           name="username"
                           value="<?= e($_POST['username'] ?? '') ?>"
                           placeholder="Masukkan username"
                           autocomplete="username"
                           required
                           autofocus>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Masukkan password"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="toggle-pw" onclick="togglePassword()" aria-label="Tampilkan/sembunyikan password">
                        <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-login" id="loginBtn">
                <div class="spinner" aria-hidden="true"></div>
                <span class="btn-text">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Masuk ke Dashboard
                </span>
            </button>

        </form>
    </div>

    <!-- Footer -->
    <div class="login-footer">
        <a href="../index.php">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Kembali ke Website
        </a>
    </div>

    <p class="copyright">© <?= date('Y') ?> <?= e($settings['site_title'] ?? 'Wisata Curug Kalipagu') ?></p>

</div><!-- /login-wrapper -->

<script>
    // Toggle show/hide password
    function togglePassword() {
        const input   = document.getElementById('password');
        const icon    = document.getElementById('eyeIcon');
        const isHidden = input.type === 'password';

        input.type = isHidden ? 'text' : 'password';
        icon.innerHTML = isHidden
            ? `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
               <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
               <line x1="1" y1="1" x2="23" y2="23"/>`
            : `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }

    // Loading state saat submit
    document.getElementById('loginForm').addEventListener('submit', function () {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('loading');
    });
</script>

</body>
</html>
