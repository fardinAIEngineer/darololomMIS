<?php
$oldOr = static fn(string $key, mixed $fallback = ''): mixed => old($key, $subject[$key] ?? $fallback);
?>

<div class="section-title">
    <h2><?= e($subject ? 'ویرایش مضمون' : 'ثبت مضمون جدید') ?></h2>
</div>

<div class="news-thumb">
    <div class="news-info">
        <form method="post" action="<?= e($formAction) ?>" class="module-form">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>نام مضمون</label>
                <input type="text" name="name" class="form-control" value="<?= e((string) $oldOr('name')) ?>" required>
            </div>

            <div class="form-group">
                <label>سطح آموزشی</label>
                <select name="level_id" class="form-control" required>
                    <option value="">انتخاب کنید</option>
                    <?php foreach ($levels as $level): ?>
                        <option value="<?= e((string) $level['id']) ?>" <?= (string) $oldOr('level_id') === (string) $level['id'] ? 'selected' : '' ?>>
                            <?= e($level['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>سمستر</label>
                <select name="semester" class="form-control">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <option value="<?= $i ?>" <?= (string) $oldOr('semester', 1) === (string) $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label>دوره</label>
                <select name="period_id" class="form-control">
                    <option value="">—</option>
                    <?php foreach ($periods as $item): ?>
                        <option value="<?= e((string) $item['id']) ?>" <?= (string) $oldOr('period_id') === (string) $item['id'] ? 'selected' : '' ?>>
                            <?= e((string) $item['number']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="section-btn btn btn-default" type="submit">ذخیره</button>
            <a class="btn btn-default" href="<?= e(url('/subjects')) ?>">انصراف</a>
        </form>
    </div>
</div>
