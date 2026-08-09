<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Load all forms
$forms = [];
foreach (glob(DATA_DIR . '*.json') as $file) {
    $data = json_decode(file_get_contents($file), true);
    if ($data) {
        $forms[] = $data;
    }
}

// Sort by modified desc
usort($forms, fn($a, $b) => strcmp($b['modified'] ?? '', $a['modified'] ?? ''));

$success = $_GET['success'] ?? '';
$deleted = $_GET['deleted'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – <?= htmlspecialchars(APP_NAME) ?></title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <div class="header-brand">
            <div class="wa-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="white" width="22" height="22">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
            </div>
            <span class="brand-name"><?= htmlspecialchars(APP_NAME) ?></span>
            <span class="brand-version">v<?= APP_VERSION ?></span>
        </div>
        <div class="header-actions">
            <a href="edit.php" class="btn btn-primary">+ New Form</a>
            <a href="logout.php" class="btn btn-ghost">Logout</a>
        </div>
    </div>
</header>

<main class="container" style="padding-top:32px;padding-bottom:60px;">
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($deleted): ?>
        <div class="alert alert-success">Form "<?= htmlspecialchars($deleted) ?>" deleted.</div>
    <?php endif; ?>

    <div class="page-title-row">
        <h2 class="page-title">My Forms</h2>
        <span class="badge-count"><?= count($forms) ?> form<?= count($forms) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($forms)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h3>No forms yet</h3>
            <p>Create your first WhatsApp form to get started.</p>
            <a href="edit.php" class="btn btn-primary">+ Create Form</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="forms-table">
                <thead>
                    <tr>
                        <th>Form Name</th>
                        <th>Slug</th>
                        <th>WhatsApp</th>
                        <th>Questions</th>
                        <th>Modified</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($form['name'] ?? '') ?></strong></td>
                        <td><code><?= htmlspecialchars($form['slug'] ?? '') ?></code></td>
                        <td><?= htmlspecialchars($form['whatsapp'] ?? '') ?></td>
                        <td><?= count($form['questions'] ?? []) ?></td>
                        <td><?= htmlspecialchars($form['modified'] ?? '') ?></td>
                        <td class="actions-cell">
                            <a href="form.php?f=<?= urlencode($form['slug'] ?? '') ?>" target="_blank" class="btn btn-sm btn-ghost" title="View public form">👁 View</a>
                            <a href="edit.php?slug=<?= urlencode($form['slug'] ?? '') ?>" class="btn btn-sm btn-secondary" title="Edit form">✏️ Edit</a>
                            <a href="copy.php?slug=<?= urlencode($form['slug'] ?? '') ?>" class="btn btn-sm btn-ghost" title="Duplicate form">📋 Copy</a>
                            <a href="delete.php?slug=<?= urlencode($form['slug'] ?? '') ?>" class="btn btn-sm btn-danger" title="Delete form" onclick="return confirm('Delete this form? This cannot be undone.')">🗑 Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
