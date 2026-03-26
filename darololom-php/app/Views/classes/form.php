<?php
$oldOr = static fn(string $key, mixed $fallback = ''): mixed => old($key, $classItem[$key] ?? $fallback);

$semesterIdToNumber = [];
foreach ($semesters as $item) {
    $semesterIdToNumber[(int) $item['id']] = (int) $item['number'];
}

$existingAaliClass = '';
if (!empty($classItem['semester_id'])) {
    $semesterNumber = $semesterIdToNumber[(int) $classItem['semester_id']] ?? 0;
    if (in_array((int) $semesterNumber, [13, 14], true)) {
        $existingAaliClass = (string) $semesterNumber;
    }
}

$selectedAaliClass = (string) old('aali_class', $existingAaliClass);
?>

<div class="section-title">
    <h2><?= e($classItem ? 'ویرایش صنف' : 'ثبت صنف جدید') ?></h2>
</div>

<div class="news-thumb">
    <div class="news-info">
        <form method="post" action="<?= e($formAction) ?>" class="module-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>نام صنف</label>
                <input type="text" name="name" class="form-control" value="<?= e((string) $oldOr('name')) ?>" required>
            </div>

            <div class="form-group">
                <label>سطح آموزشی</label>
                <select name="level_id" id="level_id" class="form-control" required>
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?= e((string) $level['id']) ?>" data-level-code="<?= e((string) ($level['code'] ?? '')) ?>" <?= (string) $oldOr('level_id') === (string) $level['id'] ? 'selected' : '' ?>>
                            <?= e($level['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" id="aali_class_group">
                <label>صنف عالی</label>
                <select name="aali_class" id="aali_class_select" class="form-control">
                    <option value="">—</option>
                    <option value="13" <?= $selectedAaliClass === '13' ? 'selected' : '' ?>>صنف ۱۳</option>
                    <option value="14" <?= $selectedAaliClass === '14' ? 'selected' : '' ?>>صنف ۱۴</option>
                </select>
                <small class="field-help">برای سطح عالی فقط صنف ۱۳ یا صنف ۱۴ قابل انتخاب است.</small>
            </div>

            <div class="form-group" id="period_group">
                <label>دوره</label>
                <select name="period_id" id="period_id_select" class="form-control">
                    <option value="">—</option>
                    <?php foreach ($periods as $item): ?>
                        <?php if ((int) ($item['number'] ?? 0) < 1 || (int) ($item['number'] ?? 0) > 6) { continue; } ?>
                        <option value="<?= e((string) $item['id']) ?>" <?= (string) $oldOr('period_id') === (string) $item['id'] ? 'selected' : '' ?>>
                            <?= e((string) $item['number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="field-help">برای ابتداییه و متوسطه فقط دوره‌های ۱ تا ۶ نمایش داده می‌شود.</small>
            </div>

            <div class="full form-actions class-form-actions">
                <button class="section-btn btn btn-default class-save-btn" type="submit">
                    <i class="fa fa-check-circle"></i>
                    ذخیره صنف
                </button>
                <a class="btn btn-default class-cancel-btn" href="<?= e(url('/classes')) ?>">
                    <i class="fa fa-arrow-right"></i>
                    انصراف
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const levelSelect = document.getElementById('level_id');
    const aaliGroup = document.getElementById('aali_class_group');
    const aaliSelect = document.getElementById('aali_class_select');
    const periodGroup = document.getElementById('period_group');
    const periodSelect = document.getElementById('period_id_select');

    if (!levelSelect || !aaliGroup || !aaliSelect || !periodGroup || !periodSelect) {
        return;
    }

    function selectedLevelCode() {
        const opt = levelSelect.options[levelSelect.selectedIndex];
        return opt ? (opt.dataset.levelCode || '') : '';
    }

    function updateConditionalDropdowns() {
        const levelCode = selectedLevelCode();
        const isAali = levelCode === 'aali';
        const isBase = levelCode === 'moteseta' || levelCode === 'ebtedai';

        aaliGroup.style.display = isAali ? '' : 'none';
        aaliSelect.required = isAali;

        periodGroup.style.display = isBase ? '' : 'none';
        periodSelect.required = isBase;

        if (isAali) {
            periodSelect.value = '';
        } else if (isBase) {
            aaliSelect.value = '';
        } else {
            aaliSelect.value = '';
            periodSelect.value = '';
        }
    }

    levelSelect.addEventListener('change', updateConditionalDropdowns);
    updateConditionalDropdowns();
})();
</script>
