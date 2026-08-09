<?php
require_once 'config.php';
// NO session check — public page

$f = trim($_GET['f'] ?? '');
$f = preg_replace('/[^a-z0-9\-]/', '', $f);

if (!$f) {
    http_response_code(404);
    die('Form not found.');
}

$file = DATA_DIR . $f . '.json';
if (!file_exists($file)) {
    http_response_code(404);
    die('Form not found.');
}

$form = json_decode(file_get_contents($file), true);
if (!$form || empty($form['name'])) {
    http_response_code(404);
    die('Invalid form.');
}

$questions  = $form['questions'] ?? [];
$totalQ     = count($questions);
$hasIntro   = !empty(trim($form['intro'] ?? ''));
$hasLogo    = !empty(trim($form['logo_url'] ?? ''));
$waNumber   = preg_replace('/\D/', '', $form['whatsapp'] ?? '');
$formName   = $form['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#25D366">
    <title><?= htmlspecialchars($formName) ?></title>
    <link rel="stylesheet" href="assets/form.css">
</head>
<body>

<!-- Back button -->
<button class="back-btn" id="backBtn" aria-label="Go back" style="display:none;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="15 18 9 12 15 6"></polyline>
    </svg>
</button>

<!-- Progress bar -->
<div class="progress-bar-wrap" id="progressWrap" style="display:none;">
    <div class="progress-bar-fill" id="progressFill"></div>
</div>

<!-- ===== WELCOME SCREEN ===== -->
<div class="screen active" id="screen-welcome">
    <div class="screen-inner welcome-inner">
        <div class="logo-wrap">
            <?php if ($hasLogo): ?>
                <img src="<?= htmlspecialchars($form['logo_url']) ?>" alt="Logo" class="form-logo">
            <?php else: ?>
                <div class="form-logo-text"><?= htmlspecialchars($formName) ?></div>
            <?php endif; ?>
        </div>
        <h1 class="welcome-title"><?= htmlspecialchars($formName) ?></h1>
        <?php if ($hasIntro): ?>
            <p class="welcome-intro"><?= nl2br(htmlspecialchars(trim($form['intro']))) ?></p>
        <?php endif; ?>
        <?php if (!empty($questions)): ?>
            <p class="welcome-meta"><?= $totalQ ?> question<?= $totalQ !== 1 ? 's' : '' ?> · Takes about <?= max(1, round($totalQ * 0.5)) ?> min</p>
        <?php endif; ?>
        <button class="btn-next" id="startBtn">Start →</button>
        <?php if (!empty($form['privacy_url']) || !empty($form['terms_url'])): ?>
            <div class="legal-links">
                <?php if (!empty($form['privacy_url'])): ?>
                    <a href="<?= htmlspecialchars($form['privacy_url']) ?>" target="_blank" rel="noopener">Privacy Policy</a>
                <?php endif; ?>
                <?php if (!empty($form['terms_url'])): ?>
                    <a href="<?= htmlspecialchars($form['terms_url']) ?>" target="_blank" rel="noopener">Terms</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== QUESTION SCREENS ===== -->
<?php foreach ($questions as $i => $q):
    $qType       = $q['type']        ?? 'text';
    $qTitle      = $q['title']       ?? '';
    $qHint       = $q['hint']        ?? '';
    $qRequired   = $q['required']    !== false;
    $qPlaceholder= $q['placeholder'] ?? '';
    $qOptions    = $q['options']     ?? [];
    $inputName   = 'answer_' . $i;
?>
<div class="screen" id="screen-q<?= $i ?>" data-question="<?= $i ?>">
    <div class="screen-inner question-inner">
        <div class="q-meta">Question <?= $i + 1 ?> of <?= $totalQ ?></div>
        <h2 class="q-title"><?= htmlspecialchars($qTitle) ?><?= $qRequired ? '' : ' <span class="optional">(optional)</span>' ?></h2>
        <?php if ($qHint): ?>
            <p class="q-hint"><?= htmlspecialchars($qHint) ?></p>
        <?php endif; ?>

        <div class="input-wrap">
        <?php if ($qType === 'text'): ?>
            <input type="text" class="q-input" name="<?= $inputName ?>" data-q="<?= $i ?>"
                   placeholder="<?= htmlspecialchars($qPlaceholder ?: 'Type your answer...') ?>"
                   autocomplete="off" autocorrect="off">

        <?php elseif ($qType === 'amount'): ?>
            <div class="input-group">
                <span class="input-prefix">£</span>
                <input type="number" class="q-input" name="<?= $inputName ?>" data-q="<?= $i ?>"
                       placeholder="<?= htmlspecialchars($qPlaceholder ?: '0.00') ?>"
                       min="0" step="0.01" inputmode="decimal">
            </div>

        <?php elseif ($qType === 'number'): ?>
            <input type="number" class="q-input" name="<?= $inputName ?>" data-q="<?= $i ?>"
                   placeholder="<?= htmlspecialchars($qPlaceholder ?: '0') ?>"
                   inputmode="numeric">

        <?php elseif ($qType === 'textarea'): ?>
            <textarea class="q-input q-textarea" name="<?= $inputName ?>" data-q="<?= $i ?>"
                      rows="4" placeholder="<?= htmlspecialchars($qPlaceholder ?: 'Type your answer...') ?>"></textarea>

        <?php elseif ($qType === 'yesno'): ?>
            <div class="yesno-wrap">
                <button type="button" class="choice-btn yesno-btn" data-q="<?= $i ?>" data-value="Yes">Yes</button>
                <button type="button" class="choice-btn yesno-btn" data-q="<?= $i ?>" data-value="No">No</button>
            </div>
            <input type="hidden" class="q-input" name="<?= $inputName ?>" data-q="<?= $i ?>" value="">

        <?php elseif ($qType === 'choice'): ?>
            <div class="choices-wrap">
                <?php foreach ($qOptions as $opt): ?>
                    <button type="button" class="choice-btn choice-option" data-q="<?= $i ?>" data-value="<?= htmlspecialchars($opt) ?>">
                        <?= htmlspecialchars($opt) ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" class="q-input" name="<?= $inputName ?>" data-q="<?= $i ?>" value="">

        <?php endif; ?>
        </div>

        <?php if (!in_array($qType, ['yesno', 'choice'])): ?>
            <button class="btn-next" data-q="<?= $i ?>" onclick="advanceQuestion(<?= $i ?>, <?= $qRequired ? 'true' : 'false' ?>)">Next →</button>
        <?php endif; ?>

        <?php if ($i === $totalQ - 1 && !in_array($qType, ['yesno', 'choice'])): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const lastNext = document.querySelector('#screen-q<?= $i ?> .btn-next');
                    if (lastNext) lastNext.textContent = 'Finish ✓';
                });
            </script>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- ===== COMPLETION SCREEN ===== -->
<div class="screen" id="screen-complete">
    <div class="screen-inner complete-inner">
        <div class="complete-check">✓</div>
        <h2 class="complete-title">All done!</h2>
        <p class="complete-sub">Review your answers below, then tap the button to send them via WhatsApp.</p>

        <div class="answers-summary" id="answersSummary"></div>

        <button class="btn-whatsapp" id="sendWhatsApp">
            <svg viewBox="0 0 24 24" fill="white" width="22" height="22" style="flex-shrink:0;">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            Send via WhatsApp
        </button>

        <?php if (!empty($form['privacy_url']) || !empty($form['terms_url'])): ?>
            <div class="legal-links" style="margin-top:28px;">
                <?php if (!empty($form['privacy_url'])): ?>
                    <a href="<?= htmlspecialchars($form['privacy_url']) ?>" target="_blank" rel="noopener">Privacy Policy</a>
                <?php endif; ?>
                <?php if (!empty($form['terms_url'])): ?>
                    <a href="<?= htmlspecialchars($form['terms_url']) ?>" target="_blank" rel="noopener">Terms</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Form data from PHP
const FORM_NAME    = <?= json_encode($formName) ?>;
const WA_NUMBER    = <?= json_encode($waNumber) ?>;
const TOTAL_Q      = <?= (int)$totalQ ?>;
const QUESTIONS    = <?= json_encode(array_map(fn($q) => [
    'title'    => $q['title']    ?? '',
    'type'     => $q['type']     ?? 'text',
    'required' => $q['required'] !== false,
], $questions)) ?>;

// State
let currentScreen = 'welcome';
let history       = ['welcome'];
const answers     = {};

// ---- Navigation helpers ----
function showScreen(id) {
    document.querySelectorAll('.screen').forEach(s => s.classList.remove('active','slide-out'));
    const el = document.getElementById('screen-' + id);
    if (el) {
        el.classList.add('active');
        // Scroll to top of screen
        window.scrollTo(0, 0);
        // Focus first input
        setTimeout(() => {
            const inp = el.querySelector('input:not([type=hidden]), textarea');
            if (inp) inp.focus();
        }, 320);
    }
    currentScreen = id;
    updateUI();
}

function updateUI() {
    const backBtn     = document.getElementById('backBtn');
    const progressWrap= document.getElementById('progressWrap');
    const progressFill= document.getElementById('progressFill');

    const isWelcome   = currentScreen === 'welcome';
    const isComplete  = currentScreen === 'complete';

    backBtn.style.display = (isWelcome) ? 'none' : 'flex';

    if (!isWelcome && !isComplete) {
        progressWrap.style.display = 'block';
        const qNum = parseInt(currentScreen.replace('q', ''), 10);
        const pct  = Math.round(((qNum + 1) / TOTAL_Q) * 100);
        progressFill.style.width = pct + '%';
    } else if (isComplete) {
        progressWrap.style.display = 'block';
        progressFill.style.width = '100%';
    } else {
        progressWrap.style.display = 'none';
    }
}

function goBack() {
    if (history.length > 1) {
        history.pop();
        const prev = history[history.length - 1];
        showScreen(prev);
    }
}

function navigateTo(screenId) {
    history.push(screenId);
    showScreen(screenId);
}

// ---- Advance question ----
function advanceQuestion(qIndex, required) {
    const screen  = document.getElementById('screen-q' + qIndex);
    const input   = screen.querySelector('.q-input');
    const value   = input ? input.value.trim() : '';

    if (required && value === '') {
        shakeInput(screen);
        return;
    }
    if (input) answers[qIndex] = value;

    if (qIndex + 1 < TOTAL_Q) {
        navigateTo('q' + (qIndex + 1));
    } else {
        showCompletion();
    }
}

function shakeInput(screen) {
    const wrap = screen.querySelector('.input-wrap');
    if (!wrap) return;
    wrap.classList.add('shake');
    setTimeout(() => wrap.classList.remove('shake'), 600);
}

// ---- Choice / YesNo selection ----
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.choice-btn');
    if (!btn) return;
    const qIndex = parseInt(btn.dataset.q, 10);
    const value  = btn.dataset.value;
    const screen = document.getElementById('screen-q' + qIndex);

    // Deselect siblings
    screen.querySelectorAll('.choice-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');

    // Set hidden input
    const hidden = screen.querySelector('input[type=hidden].q-input');
    if (hidden) hidden.value = value;
    answers[qIndex] = value;

    // Auto-advance after brief visual feedback
    setTimeout(() => {
        if (qIndex + 1 < TOTAL_Q) {
            navigateTo('q' + (qIndex + 1));
        } else {
            showCompletion();
        }
    }, 350);
});

// ---- Enter key advances text/number/amount inputs ----
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Enter') return;
    const screen = document.querySelector('.screen.active');
    if (!screen || !screen.dataset.question) return;
    const qIndex = parseInt(screen.dataset.question, 10);
    const q      = QUESTIONS[qIndex];
    if (q && !['textarea','yesno','choice'].includes(q.type)) {
        e.preventDefault();
        advanceQuestion(qIndex, q.required);
    }
});

// ---- Completion ----
function showCompletion() {
    buildSummary();
    navigateTo('complete');
}

function buildSummary() {
    const container = document.getElementById('answersSummary');
    container.innerHTML = '';
    QUESTIONS.forEach((q, i) => {
        const ans = (answers[i] || '').trim();
        if (!ans && !q.required) return;
        const row = document.createElement('div');
        row.className = 'summary-row';
        const label = document.createElement('div');
        label.className = 'summary-label';
        label.textContent = q.title;
        const val = document.createElement('div');
        val.className = 'summary-value';
        val.textContent = q.type === 'amount' ? '£' + ans : ans;
        row.appendChild(label);
        row.appendChild(val);
        container.appendChild(row);
    });
}

function buildWhatsAppMessage() {
    let msg = '*' + FORM_NAME + '*\n\n';
    QUESTIONS.forEach((q, i) => {
        const ans = (answers[i] || '').trim();
        if (!ans) return;
        const display = q.type === 'amount' ? '£' + ans : ans;
        msg += q.title + ': ' + display + '\n';
    });
    return msg.trim();
}

// ---- Send via WhatsApp ----
document.getElementById('sendWhatsApp').addEventListener('click', function() {
    const msg = buildWhatsAppMessage();
    const url = 'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(msg);
    window.open(url, '_blank');
});

// ---- Back button ----
document.getElementById('backBtn').addEventListener('click', goBack);

// ---- Start button ----
document.getElementById('startBtn').addEventListener('click', function() {
    if (TOTAL_Q > 0) {
        navigateTo('q0');
    }
});

// ---- Init ----
updateUI();
</script>
</body>
</html>
