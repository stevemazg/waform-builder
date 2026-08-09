<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$slug = trim($_GET['slug'] ?? '');
$slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

if (!$slug) {
    header('Location: index.php');
    exit;
}

$file = DATA_DIR . $slug . '.json';
if (!file_exists($file)) {
    header('Location: index.php?error=' . urlencode('Form not found.'));
    exit;
}

$form = json_decode(file_get_contents($file), true);
if (!$form) {
    header('Location: index.php?error=' . urlencode('Could not read form.'));
    exit;
}

// Create a new slug: {slug}-copy, or {slug}-copy-2, etc.
$newSlug = $slug . '-copy';
$counter = 1;
while (file_exists(DATA_DIR . $newSlug . '.json')) {
    $counter++;
    $newSlug = $slug . '-copy-' . $counter;
}

$form['name']     = 'Copy of ' . $form['name'];
$form['slug']     = $newSlug;
$form['created']  = date('Y-m-d');
$form['modified'] = date('Y-m-d');

// Re-generate question IDs
foreach ($form['questions'] as &$q) {
    $q['id'] = 'q' . uniqid();
}
unset($q);

$json = json_encode($form, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
file_put_contents(DATA_DIR . $newSlug . '.json', $json);

header('Location: edit.php?slug=' . urlencode($newSlug));
exit;
