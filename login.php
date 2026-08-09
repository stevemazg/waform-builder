<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Incorrect password. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        body { display:flex; align-items:center; justify-content:center; min-height:100vh; }
        .login-card { background:#fff; border-radius:12px; box-shadow:0 2px 20px rgba(0,0,0,.1); padding:40px; width:100%; max-width:380px; }
        .login-card h1 { margin:0 0 8px; font-size:24px; color:#111; }
        .login-card p.sub { margin:0 0 28px; color:#888; font-size:14px; }
        .login-logo { display:flex; align-items:center; gap:10px; margin-bottom:28px; }
        .login-logo .wa-icon { width:40px; height:40px; background:#25D366; border-radius:10px; display:flex; align-items:center; justify-content:center; }
        .login-logo .wa-icon svg { fill:white; width:24px; height:24px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-logo">
        <div class="wa-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
        </div>
        <span style="font-size:20px;font-weight:700;color:#111"><?= htmlspecialchars(APP_NAME) ?></span>
    </div>
    <h1>Admin Login</h1>
    <p class="sub">Enter your password to continue</p>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Enter admin password" autofocus required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
    </form>
</div>
</body>
</html>
