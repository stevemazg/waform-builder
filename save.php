<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

function slugify(string $str): string {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9]+/', '-', $str);
    return trim($str, '-');
}

function redirect_error(string $msg, string $slug = ''): void {
    $qs = $slug ? '?slug=' . urlencode($slug) : '';
    $_SESSION['form_error'] = $msg;
    header('Location: edit.php' . $qs);
    exit;
}

$name         = trim($_POST['name']        ?? '');
$slug         = trim($_POST['slug']        ?? '');
$whatsapp     = trim($_POST['whatsapp']    ?? '');
$logo_url     = trim($_POST['logo_url']    ?? '');
$privacy_url  = trim($_POST['privacy_url'] ?? '');
$terms_url    = trim($_POST['terms_url']   ?? '');
$intro        = trim($_POST['intro']       ?? '');
$origSlug     = trim($_POST['original_slug'] ?? '');
$created      = trim($_POST['created']     ?? date('Y-m-d'));

// Validation
if ($name === '') {
    redirect_error('Form name is required.', $origSlug);
}
if ($slug === '') {
    $slug = slugify($name);
}
if (!preg_match('/^[a-z0-9\-]+$/', $slug)) {
    redirect_error('Slug may only contain lowercase letters, numbers, and hyphens.', $origSlug);
}
if ($whatsapp === '') {
    redirect_error('WhatsApp number is required.', $origSlug);
}
if (!preg_match('/^\d+$/', $whatsapp)) {
    redirect_error('WhatsApp number must contain digits only (no +, spaces, or dashes).', $origSlug);
}

// Check slug conflict (if creating new or renaming)
$targetFile = DATA_DIR . $slug . '.json';
if ($origSlug !== $slug && file_exists($targetFile)) {
    redirect_error("A form with slug '{$slug}' already exists. Choose a different slug.", $origSlug);
}

// Build questions
$questions = [];
$titles       = $_POST['q_title']       ?? [];
$hints        = $_POST['q_hint']        ?? [];
$types        = $_POST['q_type']        ?? [];
$placeholders = $_POST['q_placeholder'] ?? [];
$ids          = $_POST['q_id']          ?? [];
$optionsAll   = $_POST['q_options']     ?? [];

$optIdx = 0;
foreach ($titles as $i => $title) {
    $title = trim($title);
    if ($title === '') continue;

    $qType = $types[$i] ?? 'text';
    $qOpts = [];
    if ($qType === 'choice') {
        $rawOpts = $optionsAll[$optIdx] ?? '';
        $optIdx++;
        $qOpts = array_values(array_filter(array_map('trim', explode("\n", $rawOpts))));
    }

    $reqKey = 'q_required_' . $i;
    $required = isset($_POST[$reqKey]) && $_POST[$reqKey] === '1';

    $questions[] = [
        'id'          => $ids[$i] ?? ('q' . ($i + 1)),
        'title'       => $title,
        'hint'        => trim($hints[$i] ?? ''),
        'type'        => $qType,
        'required'    => $required,
        'placeholder' => trim($placeholders[$i] ?? ''),
        'options'     => $qOpts,
    ];
}

$form = [
    'name'        => $name,
    'slug'        => $slug,
    'whatsapp'    => $whatsapp,
    'logo_url'    => $logo_url,
    'privacy_url' => $privacy_url,
    'terms_url'   => $terms_url,
    'intro'       => $intro,
    'created'     => $created,
    'modified'    => date('Y-m-d'),
    'questions'   => $questions,
];

// Save file
$json = json_encode($form, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (file_put_contents($targetFile, $json) === false) {
    redirect_error('Failed to save form. Check data/ directory permissions.', $origSlug);
}

// Handle slug rename: delete old file
if ($origSlug && $origSlug !== $slug) {
    $oldFile = DATA_DIR . $origSlug . '.json';
    if (file_exists($oldFile)) {
        unlink($oldFile);
    }
}

header('Location: index.php?success=' . urlencode("Form '{$name}' saved successfully."));
exit;
