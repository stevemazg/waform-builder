<?php
// Helper: render a single question card (server-side, for edit.php initial load)
if (!function_exists('renderQuestion')) {
    function renderQuestion(int $index, array $q, array $questionTypes): void {
        $qId          = htmlspecialchars($q['id']          ?? 'q' . ($index + 1));
        $title        = htmlspecialchars($q['title']       ?? '');
        $hint         = htmlspecialchars($q['hint']        ?? '');
        $type         = $q['type'] ?? 'text';
        $placeholder  = htmlspecialchars($q['placeholder'] ?? '');
        $required     = $q['required'] !== false;
        $options      = isset($q['options']) ? implode("\n", $q['options']) : '';
        ?>
        <div class="question-card" data-index="<?= $index ?>">
            <div class="question-card-header">
                <span class="q-number">Question <?= $index + 1 ?></span>
                <div class="q-card-actions">
                    <button type="button" class="btn btn-sm btn-ghost q-move-up" title="Move up">↑</button>
                    <button type="button" class="btn btn-sm btn-ghost q-move-down" title="Move down">↓</button>
                    <button type="button" class="btn btn-sm btn-danger q-remove" title="Remove question">Remove</button>
                </div>
            </div>
            <input type="hidden" name="q_id[]" value="<?= $qId ?>">
            <div class="form-row">
                <div class="form-group" style="flex:2;">
                    <label>Question Title <span class="required">*</span></label>
                    <input type="text" name="q_title[]" class="form-control" value="<?= $title ?>" placeholder="e.g. Monthly Rent or Mortgage" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Type</label>
                    <select name="q_type[]" class="form-control q-type-select">
                        <?php foreach ($questionTypes as $val => $label): ?>
                            <option value="<?= htmlspecialchars($val) ?>" <?= $type === $val ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:2;">
                    <label>Hint / Description</label>
                    <textarea name="q_hint[]" class="form-control" rows="2" placeholder="Optional helper text"><?= $hint ?></textarea>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Placeholder</label>
                    <input type="text" name="q_placeholder[]" class="form-control" value="<?= $placeholder ?>" placeholder="e.g. 0.00">
                    <div style="margin-top:10px;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="q_required_<?= $index ?>" value="1" <?= $required ? 'checked' : '' ?>> Required
                        </label>
                    </div>
                </div>
            </div>
            <div class="choice-options" style="display:<?= $type === 'choice' ? 'block' : 'none' ?>;">
                <label>Options <small>(one per line)</small></label>
                <textarea name="q_options[]" class="form-control" rows="4" placeholder="Option A&#10;Option B&#10;Option C"><?= htmlspecialchars($options) ?></textarea>
            </div>
        </div>
        <?php
    }
}
