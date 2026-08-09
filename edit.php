<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$slug = $_GET['slug'] ?? '';
$form = [
    'name' => '',
    'slug' => '',
    'whatsapp' => '',
    'logo_url' => '',
    'privacy_url' => '',
    'terms_url' => '',
    'intro' => '',
    'created' => date('Y-m-d'),
    'modified' => date('Y-m-d'),
    'questions' => []
];
$isEdit = false;

if ($slug) {
    $file = DATA_DIR . preg_replace('/[^a-z0-9\-]/', '', $slug) . '.json';
    if (file_exists($file)) {
        $form = json_decode(file_get_contents($file), true);
        $isEdit = true;
    }
}

$questionTypes = [
    'text'     => 'Short Text',
    'amount'   => 'Amount (£)',
    'number'   => 'Number',
    'textarea' => 'Long Text',
    'yesno'    => 'Yes / No',
    'choice'   => 'Multiple Choice',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $isEdit ? 'Edit' : 'New' ?> Form – <?= htmlspecialchars(APP_NAME) ?></title>
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
            <a href="index.php" class="brand-name" style="text-decoration:none;color:inherit;"><?= htmlspecialchars(APP_NAME) ?></a>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-ghost">← Dashboard</a>
        </div>
    </div>
</header>

<main class="container" style="padding-top:32px;padding-bottom:80px;max-width:760px;">
    <h2 class="page-title" style="margin-bottom:24px;"><?= $isEdit ? 'Edit Form' : 'Create New Form' ?></h2>

    <form method="POST" action="save.php" id="formBuilder">
        <?php if ($isEdit): ?>
            <input type="hidden" name="original_slug" value="<?= htmlspecialchars($form['slug']) ?>">
            <input type="hidden" name="created" value="<?= htmlspecialchars($form['created'] ?? date('Y-m-d')) ?>">
        <?php endif; ?>

        <!-- Form Settings -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-header">Form Settings</div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group" style="flex:2;">
                        <label for="name">Form Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($form['name']) ?>" placeholder="e.g. Essential Living Expenditure" required>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label for="slug">Slug <span class="required">*</span></label>
                        <input type="text" id="slug" name="slug" class="form-control" value="<?= htmlspecialchars($form['slug']) ?>" placeholder="e.g. essential-living" pattern="[a-z0-9\-]+" required>
                        <small class="form-hint">Lowercase letters, numbers, hyphens only</small>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label for="whatsapp">WhatsApp Number <span class="required">*</span></label>
                        <input type="text" id="whatsapp" name="whatsapp" class="form-control" value="<?= htmlspecialchars($form['whatsapp']) ?>" placeholder="447700000000" required>
                        <small class="form-hint">Include country code, no +, no spaces</small>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label for="logo_url">Logo URL</label>
                        <input type="url" id="logo_url" name="logo_url" class="form-control" value="<?= htmlspecialchars($form['logo_url'] ?? '') ?>" placeholder="https://example.com/logo.png">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label for="privacy_url">Privacy Policy URL</label>
                        <input type="url" id="privacy_url" name="privacy_url" class="form-control" value="<?= htmlspecialchars($form['privacy_url'] ?? '') ?>" placeholder="https://example.com/privacy">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label for="terms_url">Terms URL</label>
                        <input type="url" id="terms_url" name="terms_url" class="form-control" value="<?= htmlspecialchars($form['terms_url'] ?? '') ?>" placeholder="https://example.com/terms">
                    </div>
                </div>
                <div class="form-group">
                    <label for="intro">Intro Text <span class="form-hint-inline">(shown on welcome screen before first question)</span></label>
                    <textarea id="intro" name="intro" class="form-control" rows="3" placeholder="Please fill in this form as accurately as possible."><?= htmlspecialchars($form['intro'] ?? '') ?></textarea>
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="card" style="margin-bottom:24px;">
            <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;">
                <span>Questions</span>
                <button type="button" class="btn btn-sm btn-primary" id="addQuestion">+ Add Question</button>
            </div>
            <div class="card-body" id="questionsContainer">
                <?php if (empty($form['questions'])): ?>
                    <div class="empty-questions" id="emptyQuestionsMsg">
                        <p style="text-align:center;color:#aaa;padding:24px 0;">No questions yet. Click "+ Add Question" to begin.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($form['questions'] as $i => $q): ?>
                        <?php include_once __DIR__ . '/question_template.php'; renderQuestion($i, $q, $questionTypes); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div style="display:flex;gap:12px;align-items:center;">
            <button type="submit" class="btn btn-primary btn-lg">💾 Save Form</button>
            <a href="index.php" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</main>

<script>
const questionTypes = <?= json_encode($questionTypes) ?>;
let questionCount = <?= count($form['questions']) ?>;

function renderQuestion(index, q) {
    q = q || {};
    const qId = q.id || ('q' + (Date.now()));
    const type = q.type || 'text';
    const required = q.required !== false;

    const choiceOptions = (q.options || []).join('\n');
    const optionsSection = `
        <div class="choice-options" style="display:${type === 'choice' ? 'block' : 'none'};">
            <label>Options <small>(one per line)</small></label>
            <textarea name="q_options[]" class="form-control" rows="4" placeholder="Option A&#10;Option B&#10;Option C">${escHtml(choiceOptions)}</textarea>
        </div>`;

    const typeOptions = Object.entries(questionTypes).map(([val, label]) =>
        `<option value="${val}" ${type === val ? 'selected' : ''}>${label}</option>`
    ).join('');

    return `
    <div class="question-card" data-index="${index}">
        <div class="question-card-header">
            <span class="q-number">Question ${index + 1}</span>
            <div class="q-card-actions">
                <button type="button" class="btn btn-sm btn-ghost q-move-up" title="Move up">↑</button>
                <button type="button" class="btn btn-sm btn-ghost q-move-down" title="Move down">↓</button>
                <button type="button" class="btn btn-sm btn-danger q-remove" title="Remove question">Remove</button>
            </div>
        </div>
        <input type="hidden" name="q_id[]" value="${escHtml(qId)}">
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label>Question Title <span class="required">*</span></label>
                <input type="text" name="q_title[]" class="form-control" value="${escHtml(q.title || '')}" placeholder="e.g. Monthly Rent or Mortgage" required>
            </div>
            <div class="form-group" style="flex:1;">
                <label>Type</label>
                <select name="q_type[]" class="form-control q-type-select">${typeOptions}</select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group" style="flex:2;">
                <label>Hint / Description</label>
                <textarea name="q_hint[]" class="form-control" rows="2" placeholder="Optional helper text shown below the question title">${escHtml(q.hint || '')}</textarea>
            </div>
            <div class="form-group" style="flex:1;">
                <label>Placeholder</label>
                <input type="text" name="q_placeholder[]" class="form-control" value="${escHtml(q.placeholder || '')}" placeholder="e.g. 0.00">
                <div style="margin-top:10px;">
                    <label class="checkbox-label">
                        <input type="checkbox" name="q_required_${index}" value="1" ${required ? 'checked' : ''}> Required
                    </label>
                </div>
            </div>
        </div>
        ${optionsSection}
    </div>`;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function renumberCards() {
    document.querySelectorAll('.question-card').forEach((card, i) => {
        card.dataset.index = i;
        card.querySelector('.q-number').textContent = 'Question ' + (i + 1);
        const reqCb = card.querySelector('input[type=checkbox]');
        if (reqCb) reqCb.name = 'q_required_' + i;
    });
    const empty = document.getElementById('emptyQuestionsMsg');
    if (empty) empty.style.display = document.querySelectorAll('.question-card').length ? 'none' : 'block';
}

document.getElementById('addQuestion').addEventListener('click', function() {
    const empty = document.getElementById('emptyQuestionsMsg');
    if (empty) empty.style.display = 'none';
    const container = document.getElementById('questionsContainer');
    const div = document.createElement('div');
    div.innerHTML = renderQuestion(questionCount, {});
    container.appendChild(div.firstElementChild);
    questionCount++;
    renumberCards();
    attachHandlers(container.lastElementChild);
});

function attachHandlers(card) {
    card.querySelector('.q-remove').addEventListener('click', function() {
        if (confirm('Remove this question?')) {
            card.remove();
            renumberCards();
        }
    });
    card.querySelector('.q-move-up').addEventListener('click', function() {
        const prev = card.previousElementSibling;
        if (prev && prev.classList.contains('question-card')) {
            card.parentNode.insertBefore(card, prev);
            renumberCards();
        }
    });
    card.querySelector('.q-move-down').addEventListener('click', function() {
        const next = card.nextElementSibling;
        if (next && next.classList.contains('question-card')) {
            card.parentNode.insertBefore(next, card);
            renumberCards();
        }
    });
    const typeSelect = card.querySelector('.q-type-select');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            const choiceSection = card.querySelector('.choice-options');
            if (choiceSection) choiceSection.style.display = this.value === 'choice' ? 'block' : 'none';
        });
    }
}

document.querySelectorAll('.question-card').forEach(attachHandlers);

// Auto-generate slug from name
const nameInput = document.getElementById('name');
const slugInput = document.getElementById('slug');
let slugEdited = <?= $isEdit ? 'true' : 'false' ?>;

nameInput.addEventListener('input', function() {
    if (!slugEdited) {
        slugInput.value = nameInput.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});
slugInput.addEventListener('input', function() {
    slugEdited = true;
    slugInput.value = slugInput.value.toLowerCase().replace(/[^a-z0-9\-]/g, '');
});
</script>
</body>
</html>
